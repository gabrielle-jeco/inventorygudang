<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;

class AnprController extends Controller
{
    private $pythonPath = 'C:\\Users\\Victus\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
    private $modelPath;
    private $predictScript;

    public function __construct()
    {
        $this->modelPath = base_path('yolov11/finalproduct/best.pt');
        $this->predictScript = base_path('yolov11/finalproduct/predict_original.py');
    }

    public function index()
    {
        return view('anpr.index');
    }

    private function getPythonEnv()
    {
        return [
            'PYTHONPATH' => base_path('yolov11/finalproduct'),
            'PYTHONHOME' => dirname($this->pythonPath),
            'PYTHONUNBUFFERED' => '1',
            'PYTHONIOENCODING' => 'utf-8',
            'PYTHONHASHSEED' => '0',  // Disable hash randomization
            'TEMP' => sys_get_temp_dir(),
            'TMP' => sys_get_temp_dir(),
            'PATH' => getenv('PATH'),
            'SystemRoot' => getenv('SystemRoot'),
            'LOCALAPPDATA' => getenv('LOCALAPPDATA'),
            'APPDATA' => getenv('APPDATA'),
            'USERPROFILE' => getenv('USERPROFILE'),
            'CUDA_VISIBLE_DEVICES' => '0'  // Specify GPU device if available
        ];
    }

    private function cleanPlateNumber($plateNumber) {
        // Remove any non-alphanumeric characters (keeping only A-Z, a-z, 0-9)
        return preg_replace('/[^A-Za-z0-9]/', '', $plateNumber);
    }

    public function detect(Request $request)
    {
        $request->validate([
            'source' => 'required|file|mimes:jpeg,png,jpg,mp4,mov,avi|max:10240', // 10MB max
        ]);

        // Store the uploaded file
        $file = $request->file('source');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('public/anpr/uploads', $filename);
        $filePath = storage_path('app/public/anpr/uploads/' . $filename);
        
        // Create a unique results directory with timestamp
        $resultsDir = time() . '_' . pathinfo($filename, PATHINFO_FILENAME);
        $outputDir = storage_path('app/public/anpr/results/' . $resultsDir);

        Log::info('ANPR Detection - File uploaded', [
            'filename' => $filename,
            'filePath' => $filePath,
            'outputDir' => $outputDir
        ]);

        // Ensure output directory exists
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
            Log::info('ANPR Detection - Created output directory', ['outputDir' => $outputDir]);
        }

        try {
            // Create and configure process
            $process = new Process([
                $this->pythonPath,
                $this->predictScript,
                $this->modelPath,
                $filePath,
                'false',  // Don't show display
                $outputDir  // Pass the output directory
            ]);

            Log::info('ANPR Detection - Command', [
                'command' => $process->getCommandLine(),
                'workingDir' => $process->getWorkingDirectory()
            ]);

            // Set environment variables
            $process->setEnv($this->getPythonEnv());
            $process->setTimeout(300); // 5 minutes timeout
            
            // Capture output in real-time
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    Log::error('ANPR Detection - Process Error: ' . $buffer);
                } else {
                    Log::info('ANPR Detection - Process Output: ' . $buffer);
                }
            });

            if (!$process->isSuccessful()) {
                Log::error('ANPR Detection - Process failed', [
                    'exitCode' => $process->getExitCode(),
                    'errorOutput' => $process->getErrorOutput()
                ]);
                throw new \RuntimeException($process->getErrorOutput());
            }

            // Get the result path based on file type
            $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $isVideo = in_array($fileExtension, ['mp4', 'mov', 'avi']);
            
            if ($isVideo) {
                $resultPath = 'storage/anpr/results/' . $resultsDir . '/output.mp4';
            } else {
                $resultPath = 'storage/anpr/results/' . $resultsDir . '/' . $filename;
            }
            
            // Read detected plates from JSON
            $platesFile = $outputDir . '/plates.json';
            Log::info('ANPR Detection - Checking for plates file', ['platesFile' => $platesFile]);
            
            // Initialize empty plates array
            $detectedPlates = [];
            
            // Try to read and parse the JSON file
            if (file_exists($platesFile)) {
                $jsonContent = file_get_contents($platesFile);
                if ($jsonContent !== false) {
                    $detectedPlates = json_decode($jsonContent, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::warning('ANPR Detection - JSON parse error', [
                            'error' => json_last_error_msg()
                        ]);
                        $detectedPlates = []; // Reset to empty array on parse error
                    } else {
                        // Sort by confidence and take only the highest one
                        usort($detectedPlates, function($a, $b) {
                            return $b['confidence'] <=> $a['confidence'];
                        });
                        $detectedPlates = [reset($detectedPlates)]; // Keep only the first one

                        // Add full URLs for plate images
                        foreach ($detectedPlates as &$plate) {
                            if (isset($plate['plate_image'])) {
                                $plate['plate_image_url'] = asset('storage/anpr/results/' . $resultsDir . '/' . $plate['plate_image']);
                            }
                        }
                    }
                }
            }

            // Search for vehicles with detected plate numbers
            $vehicles = [];
            foreach ($detectedPlates as $plate) {
                $plateNumber = $this->cleanPlateNumber($plate['text']); // Clean the plate number
                $vehicle = \App\Models\Vehicle::where('plate_number', 'LIKE', '%' . $plateNumber . '%')->first();
                
                if ($vehicle) {
                    // Get vehicle's transaction history from both BarangMasuk and BarangKeluar
                    $barangMasuk = BarangMasuk::where('vehicle_id', $vehicle->id)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function($history) {
                            return [
                                'tanggal' => $history->created_at->format('Y-m-d H:i:s'),
                                'kode_transaksi' => $history->kode_transaksi,
                                'type' => 'masuk',
                                'nama_barang' => $history->nama_barang,
                                'jumlah' => $history->jumlah_masuk,
                                'partner' => $history->supplier->supplier
                            ];
                        });

                    $barangKeluar = BarangKeluar::where('vehicle_id', $vehicle->id)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function($history) {
                            return [
                                'tanggal' => $history->created_at->format('Y-m-d H:i:s'),
                                'kode_transaksi' => $history->kode_transaksi,
                                'type' => 'keluar',
                                'nama_barang' => $history->nama_barang,
                                'jumlah' => $history->jumlah_keluar,
                                'partner' => $history->customer->customer
                            ];
                        });

                    // Combine and sort both histories
                    $barangHistory = $barangMasuk->concat($barangKeluar)
                        ->sortByDesc('tanggal')
                        ->take(10)
                        ->values()
                        ->all();

                    $vehicles[] = [
                        'plate_number' => $vehicle->plate_number,
                        'make' => $vehicle->make,
                        'model' => $vehicle->model,
                        'detected_number' => $plateNumber,
                        'confidence' => $plate['confidence'],
                        'matched' => true,
                        'barang_history' => $barangHistory,
                        'plate_image_url' => $plate['plate_image_url'] ?? null,
                        'bbox' => $plate['bbox'] ?? null
                    ];
                } else {
                    $vehicles[] = [
                        'detected_number' => $plateNumber,
                        'confidence' => $plate['confidence'],
                        'matched' => false,
                        'plate_image_url' => $plate['plate_image_url'] ?? null,
                        'bbox' => $plate['bbox'] ?? null
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($vehicles) > 0 ? 'Plate detection completed successfully' : 'No plates detected in the image',
                'result_path' => asset($resultPath),
                'vehicles' => $vehicles
            ]);

        } catch (\Exception $e) {
            Log::error('ANPR Detection - Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing the file: ' . $e->getMessage(),
                'error_details' => $process->getErrorOutput() ?? 'No additional error details available'
            ], 500);
        } finally {
            // Clean up temporary files
            Storage::delete('public/anpr/uploads/' . $filename);
        }
    }

    public function webcam()
    {
        try {
            // Create output directory for webcam
            $timestamp = time();
            $outputDir = storage_path('app/public/anpr/results/webcam_' . $timestamp);
            
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0777, true);
            }
            
            // Create and configure process
            $process = new Process([
                $this->pythonPath,
                $this->predictScript,
                $this->modelPath,
                '0',  // Use webcam
                'true',  // Show display
                $outputDir  // Pass the output directory
            ]);

            // Set environment variables
            $process->setEnv($this->getPythonEnv());
            $process->setTimeout(null); // No timeout for webcam
            
            // Start the process
            $process->start();
            
            // Wait a short time to check for immediate errors
            sleep(2);
            
            if (!$process->isRunning()) {
                $errorOutput = $process->getErrorOutput();
                throw new \RuntimeException($errorOutput ?: 'Process failed to start');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Webcam detection started. Press Q to stop.',
                'pid' => $process->getPid()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error starting webcam detection: ' . $e->getMessage(),
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    public function stop(Request $request)
    {
        try {
            $pid = $request->input('pid');
            
            if (!$pid) {
                throw new \RuntimeException('No process ID provided');
            }

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows
                exec("taskkill /F /PID {$pid} 2>&1", $output, $returnCode);
            } else {
                // Linux/Unix
                exec("kill -9 {$pid} 2>&1", $output, $returnCode);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detection stopped successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error stopping detection:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error stopping detection: ' . $e->getMessage()
            ], 500);
        }
    }
}
