<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
                throw new \RuntimeException($process->getErrorOutput());
            }

            // Get the result path
            $resultPath = 'storage/anpr/results/' . $resultsDir . '/' . $filename;
            
            // Read detected plates from JSON
            $platesFile = $outputDir . '/plates.json';
            Log::info('ANPR Detection - Checking for plates file', ['platesFile' => $platesFile]);
            
            if (!file_exists($platesFile)) {
                throw new \RuntimeException("Plates JSON file not found at: " . $platesFile);
            }

            $jsonContent = file_get_contents($platesFile);
            if ($jsonContent === false) {
                throw new \RuntimeException("Failed to read plates JSON file");
            }

            $detectedPlates = json_decode($jsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("Failed to parse plates JSON: " . json_last_error_msg());
            }

            // Search for vehicles with detected plate numbers
            $vehicles = [];
            foreach ($detectedPlates as $plate) {
                $plateNumber = $plate['text'];
                $vehicle = \App\Models\Vehicle::where('plate_number', 'LIKE', '%' . $plateNumber . '%')->first();
                if ($vehicle) {
                    $vehicles[] = [
                        'plate_number' => $vehicle->plate_number,
                        'make' => $vehicle->make,
                        'model' => $vehicle->model,
                        'detected_number' => $plateNumber,
                        'confidence' => $plate['confidence'],
                        'matched' => true
                    ];
                } else {
                    $vehicles[] = [
                        'detected_number' => $plateNumber,
                        'confidence' => $plate['confidence'],
                        'matched' => false
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($vehicles) > 0 ? 'Plate detection completed successfully' : 'No plates detected',
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
                'error_details' => $process->getErrorOutput()
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
