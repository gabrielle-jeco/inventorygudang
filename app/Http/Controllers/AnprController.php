<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;

class AnprController extends Controller
{
    public function index()
    {
        return view('anpr.index');
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

        // Prepare the Python command
        $pythonScript = base_path('Automatic Number Plate Detection Recognition YOLOv8/ultralytics/yolo/v8/detect/predict.py');
        $modelPath = base_path('Automatic Number Plate Detection Recognition YOLOv8/ultralytics/yolo/v8/detect/best.pt');
        $outputDir = storage_path('app/public/anpr/results');

        // Ensure output directory exists
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // Build the command with full Python path
        $pythonPath = 'C:\\Users\\Victus\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
        
        try {
            // Create Python initialization script with plate number extraction
            $pythonInit = '
import os
import random
import numpy as np
import torch
import easyocr

# Set seeds for reproducibility
random.seed(42)
np.random.seed(42)
torch.manual_seed(42)

# Initialize EasyOCR for text extraction
reader = easyocr.Reader(["en"])

from ultralytics import YOLO
model = YOLO("' . $modelPath . '")
results = model.predict(source="' . $filePath . '", project="' . $outputDir . '", name="' . pathinfo($filename, PATHINFO_FILENAME) . '", save=True, show=False)

# Extract detected license plates
detected_plates = []
for result in results:
    boxes = result.boxes
    for box in boxes:
        # Get coordinates
        x1, y1, x2, y2 = map(int, box.xyxy[0])
        # Extract the region
        crop = result.orig_img[y1:y2, x1:x2]
        # Read text from the region
        texts = reader.readtext(crop)
        if texts:
            # Get the text with highest confidence
            text = max(texts, key=lambda x: x[2])[1]
            # Clean up the text (remove spaces and special characters)
            plate_number = "".join(c for c in text if c.isalnum())
            detected_plates.append(plate_number)

# Save detected plate numbers to a file
with open("' . $outputDir . '/' . pathinfo($filename, PATHINFO_FILENAME) . '/plates.txt", "w") as f:
    f.write("\n".join(detected_plates))
';
            
            // Save the initialization script
            $initScript = storage_path('app/public/anpr/init_script.py');
            file_put_contents($initScript, $pythonInit);

            // Create and configure process
            $process = new Process([
                $pythonPath,
                $initScript
            ]);

            // Set environment variables
            $env = [
                'PYTHONPATH' => base_path('Automatic Number Plate Detection Recognition YOLOv8'),
                'PYTHONHOME' => dirname($pythonPath),
                'PYTHONUNBUFFERED' => '1',
                'PYTHONIOENCODING' => 'utf-8',
                'TEMP' => sys_get_temp_dir(),
                'TMP' => sys_get_temp_dir()
            ];
            
            $process->setEnv($env);
            $process->setTimeout(300); // 5 minutes timeout
            $process->setWorkingDirectory(base_path('Automatic Number Plate Detection Recognition YOLOv8/ultralytics/yolo/v8/detect'));
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }

            // Get the result image/video path
            $resultPath = $outputDir . '/' . pathinfo($filename, PATHINFO_FILENAME) . '/' . $filename;
            $publicPath = 'storage/anpr/results/' . pathinfo($filename, PATHINFO_FILENAME) . '/' . $filename;

            // Read detected plate numbers
            $platesFile = $outputDir . '/' . pathinfo($filename, PATHINFO_FILENAME) . '/plates.txt';
            $detectedPlates = file_exists($platesFile) ? file($platesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

            // Search for vehicles with detected plate numbers
            $vehicles = [];
            foreach ($detectedPlates as $plateNumber) {
                $vehicle = \App\Models\Vehicle::where('plate_number', 'LIKE', '%' . $plateNumber . '%')->first();
                if ($vehicle) {
                    $vehicles[] = [
                        'plate_number' => $vehicle->plate_number,
                        'make' => $vehicle->make,
                        'model' => $vehicle->model,
                        'detected_number' => $plateNumber,
                        'matched' => true
                    ];
                } else {
                    $vehicles[] = [
                        'detected_number' => $plateNumber,
                        'matched' => false
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($vehicles) > 0 ? 'Plate detection completed successfully' : 'No plates detected',
                'result_path' => asset($publicPath),
                'vehicles' => $vehicles,
                'output' => $process->getOutput()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing the file: ' . $e->getMessage(),
                'error_details' => $process->getErrorOutput()
            ], 500);
        } finally {
            // Clean up the uploaded file and init script
            Storage::delete('public/anpr/uploads/' . $filename);
            @unlink($initScript);
            @unlink($platesFile);
        }
    }

    public function webcam()
    {
        // Prepare paths
        $pythonPath = 'C:\\Users\\Victus\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
        $modelPath = base_path('Automatic Number Plate Detection Recognition YOLOv8/ultralytics/yolo/v8/detect/best.pt');
        
        try {
            // Create Python initialization script
            $pythonInit = '
import os
import random
import numpy as np
import torch

# Set seeds for reproducibility
random.seed(42)
np.random.seed(42)
torch.manual_seed(42)

from ultralytics import YOLO
model = YOLO("' . $modelPath . '")
model.predict(source=0, show=True, conf=0.25)
';
            
            // Save the initialization script
            $initScript = storage_path('app/public/anpr/webcam_init.py');
            file_put_contents($initScript, $pythonInit);

            // Create and configure process
            $process = new Process([
                $pythonPath,
                $initScript
            ]);

            // Set environment variables
            $env = [
                'PYTHONPATH' => base_path('Automatic Number Plate Detection Recognition YOLOv8'),
                'PYTHONHOME' => dirname($pythonPath),
                'PYTHONUNBUFFERED' => '1',
                'PYTHONIOENCODING' => 'utf-8',
                'TEMP' => sys_get_temp_dir(),
                'TMP' => sys_get_temp_dir()
            ];
            
            $process->setEnv($env);
            $process->setTimeout(null); // No timeout for webcam
            $process->setWorkingDirectory(base_path('Automatic Number Plate Detection Recognition YOLOv8/ultralytics/yolo/v8/detect'));
            
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
        } finally {
            // Clean up init script
            @unlink($initScript);
        }
    }
}
