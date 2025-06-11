@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Automatic Number Plate Recognition</h1>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Upload Image/Video or Use Webcam</h4>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="upload-tab" data-toggle="tab" href="#upload" role="tab" aria-controls="upload" aria-selected="true">
                            <i class="fas fa-upload"></i> Upload File
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="webcam-tab" data-toggle="tab" href="#webcam" role="tab" aria-controls="webcam" aria-selected="false">
                            <i class="fas fa-camera"></i> Use Webcam
                        </a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <!-- Upload Tab -->
                    <div class="tab-pane fade show active" id="upload" role="tabpanel" aria-labelledby="upload-tab">
                        <div class="p-4">
                            <form id="uploadForm" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label>Select Image/Video</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="source" name="source" accept="image/*,video/*">
                                        <label class="custom-file-label" for="source">Choose file</label>
                                    </div>
                                    <small class="form-text text-muted">Supported formats: JPG, PNG, MP4, MOV, AVI (Max: 10MB)</small>
                                </div>
                                <button type="submit" class="btn btn-primary" id="uploadBtn">
                                    <i class="fas fa-search"></i> Detect Plate Number
                                </button>
                            </form>
                            <div id="result" class="mt-4" style="display: none;">
                                <h5>Detection Result:</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <img id="detection-result" src="" alt="Detection Result" class="img-fluid" style="display: none;">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Detected Plate</th>
                                                        <th>Status</th>
                                                        <th>Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="detection-results">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="history-section" style="display: none;">
                                    <h5 class="mt-4">Transaction History</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Transaction Code</th>
                                                    <th>Type</th>
                                                    <th>Item</th>
                                                    <th>Quantity</th>
                                                    <th>Partner</th>
                                                </tr>
                                            </thead>
                                            <tbody id="transaction-history">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Webcam Tab -->
                    <div class="tab-pane fade" id="webcam" role="tabpanel" aria-labelledby="webcam-tab">
                        <div class="p-4 text-center">
                            <div id="webcam-container" class="mb-4" style="display: none;">
                                <video id="webcam-video" width="640" height="480" autoplay playsinline style="border: 2px solid #ccc; border-radius: 8px;"></video>
                                <canvas id="capture-canvas" style="display: none;"></canvas>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-success" id="captureBtn" style="display: none;">
                                        <i class="fas fa-camera"></i> Capture Frame
                                    </button>
                                </div>
                                <div id="capture-result" class="mt-4" style="display: none;">
                                    <h5>Capture Result:</h5>
                                    <div id="captureContent" class="text-center">
                                        <!-- Capture result will be displayed here -->
                                    </div>
                                    <div id="captureVehicleInfo" class="mt-4">
                                        <!-- Vehicle information will be displayed here -->
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary" id="startWebcam">
                                <i class="fas fa-play"></i> Start Webcam Detection
                            </button>
                            <p class="mt-3 text-muted">Press 'Q' to stop the webcam detection</p>
                            <div id="error-message" class="alert alert-danger mt-3" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    // Update file input label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Handle file upload
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        $('#uploadBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: "{{ route('anpr.detect') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#result').show();
                displayResults(response);
                Swal.fire('Success', response.message, 'success');
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'An error occurred while processing the file';
                Swal.fire('Error', errorMsg, 'error');
            },
            complete: function() {
                $('#uploadBtn').prop('disabled', false).html('<i class="fas fa-search"></i> Detect Plate Number');
            }
        });
    });

    // Handle webcam
    $('#startWebcam').on('click', async function() {
        const button = $(this);
        const webcamContainer = $('#webcam-container');
        const video = $('#webcam-video')[0];
        const captureBtn = $('#captureBtn');
        const canvas = $('#capture-canvas')[0];
        const errorDiv = $('#error-message');
        let processPid = null;

        try {
            // Reset error message
            errorDiv.hide();

            // First, request webcam access
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: true,
                audio: false
            });

            // Add key press handler for 'Q'
            const handleKeyPress = async (e) => {
                if (e.key.toLowerCase() === 'q') {
                    // Stop the detection process
                    if (processPid) {
                        try {
                            await $.ajax({
                                url: "{{ route('anpr.stop') }}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    pid: processPid
                                }
                            });
                        } catch (error) {
                            console.error('Error stopping detection:', error);
                        }
                    }
                    
                    // Stop the webcam stream
                    stream.getTracks().forEach(track => track.stop());
                    webcamContainer.hide();
                    button.prop('disabled', false).html('<i class="fas fa-play"></i> Start Webcam Detection');
                    
                    // Remove the key press handler
                    document.removeEventListener('keypress', handleKeyPress);
                }
            };

            // Add the key press handler
            document.addEventListener('keypress', handleKeyPress);

            // Show webcam preview and capture button
            video.srcObject = stream;
            webcamContainer.show();
            captureBtn.show();
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Starting Detection...');

            // Handle capture button click
            captureBtn.off('click').on('click', function() {
                // Set canvas dimensions to match video
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                // Draw current video frame to canvas
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                
                // Show processing state
                captureBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                
                // Convert canvas to blob
                canvas.toBlob(function(blob) {
                    const formData = new FormData();
                    formData.append('source', blob, 'capture.jpg');
                    formData.append('_token', '{{ csrf_token() }}');
                    
                    // Send to server for processing
                    $.ajax({
                        url: "{{ route('anpr.detect') }}",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#capture-result').show();
                            
                            // Display the processed image
                            if (response.result_path) {
                                const img = new Image();
                                img.onload = function() {
                                    $('#captureContent').html(this);
                                    $(this).addClass('img-fluid').attr('alt', 'Capture Result');
                                };
                                img.onerror = function() {
                                    $('#captureContent').html('<div class="alert alert-danger">Failed to load detection result image</div>');
                                };
                                img.src = response.result_path;
                            } else {
                                $('#captureContent').html('<div class="alert alert-warning">No detection result image available</div>');
                            }
                            
                            // Display vehicle information
                            let vehicleHtml = '<div class="table-responsive"><table class="table table-bordered mt-3">';
                            vehicleHtml += '<thead><tr><th>Detected Plate</th><th>Status</th><th>Details</th></tr></thead><tbody>';
                            
                            if (response.vehicles && response.vehicles.length > 0) {
                                response.vehicles.forEach(function(vehicle) {
                                    vehicleHtml += '<tr>';
                                    vehicleHtml += `<td>${vehicle.detected_number || 'No plate detected'}</td>`;
                                    if (vehicle.matched) {
                                        vehicleHtml += '<td><span class="badge badge-success">Found</span></td>';
                                        vehicleHtml += `<td>
                                            <strong>Plate Number:</strong> ${vehicle.plate_number}<br>
                                            <strong>Make:</strong> ${vehicle.make}<br>
                                            <strong>Model:</strong> ${vehicle.model}
                                        </td>`;
                                    } else {
                                        vehicleHtml += '<td><span class="badge badge-danger">Not Found</span></td>';
                                        vehicleHtml += '<td>No matching vehicle found in database</td>';
                                    }
                                    vehicleHtml += '</tr>';
                                });
                            } else {
                                vehicleHtml += '<tr><td colspan="3" class="text-center">No license plates detected</td></tr>';
                            }
                            
                            vehicleHtml += '</tbody></table></div>';
                            $('#captureVehicleInfo').html(vehicleHtml);
                            
                            // Reset capture button
                            captureBtn.prop('disabled', false).html('<i class="fas fa-camera"></i> Capture Frame');
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON?.message || 'An error occurred while processing the capture';
                            $('#captureContent').html(`<div class="alert alert-danger">${errorMsg}</div>`);
                            captureBtn.prop('disabled', false).html('<i class="fas fa-camera"></i> Capture Frame');
                        }
                    });
                }, 'image/jpeg', 0.9);
            });

            // Start ANPR detection
            $.ajax({
                url: "{{ route('anpr.webcam') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        processPid = response.pid;
                        // Change button text to show active detection
                        button.html('<i class="fas fa-circle text-danger"></i> Detection Running...');
                        
                        Swal.fire({
                            title: 'Detection Started',
                            text: 'Webcam detection started. Press Q to stop.',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'OK',
                            cancelButtonText: 'Stop Detection'
                        }).then((result) => {
                            if (result.isDismissed || result.isCanceled) {
                                // Stop the detection process
                                if (processPid) {
                                    $.ajax({
                                        url: "{{ route('anpr.stop') }}",
                                        type: 'POST',
                                        data: {
                                            _token: "{{ csrf_token() }}",
                                            pid: processPid
                                        }
                                    });
                                }
                                
                                // Stop the webcam stream
                                stream.getTracks().forEach(track => track.stop());
                                webcamContainer.hide();
                                button.prop('disabled', false).html('<i class="fas fa-play"></i> Start Webcam Detection');
                                
                                // Remove the key press handler
                                document.removeEventListener('keypress', handleKeyPress);
                            }
                        });
                    } else {
                        throw new Error(response.message || 'Failed to start detection');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'An error occurred while starting webcam detection';
                    errorDiv.html(errorMsg).show();
                    // Clean up on error
                    stream.getTracks().forEach(track => track.stop());
                    webcamContainer.hide();
                    button.prop('disabled', false).html('<i class="fas fa-play"></i> Start Webcam Detection');
                }
            });
        } catch (error) {
            errorDiv.html('Unable to access webcam. Please make sure you have granted camera permissions.').show();
            button.prop('disabled', false).html('<i class="fas fa-play"></i> Start Webcam Detection');
        }
    });
});

function displayResults(data) {
    const resultImg = document.getElementById('detection-result');
    const resultsTable = document.getElementById('detection-results');
    const historySection = document.getElementById('history-section');
    const historyTable = document.getElementById('transaction-history');
    
    // Display the detection image
    if (data.result_path) {
        resultImg.src = data.result_path;
        resultImg.style.display = 'block';
        resultImg.onerror = function() {
            resultImg.style.display = 'none';
            Swal.fire('Error', 'Failed to load detection result image', 'error');
        };
    } else {
        resultImg.style.display = 'none';
    }
    
    // Clear previous results
    resultsTable.innerHTML = '';
    if (historyTable) {
        historyTable.innerHTML = '';
        historySection.style.display = 'none';
    }
    
    // Display detection results
    if (data.vehicles && data.vehicles.length > 0) {
        data.vehicles.forEach(vehicle => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${vehicle.detected_number || 'No plate detected'}</td>
                <td><span class="badge badge-${vehicle.matched ? 'success' : 'warning'}">${vehicle.matched ? 'Found' : 'Not Found'}</span></td>
                <td>
                    ${vehicle.matched ? `
                        <strong>Plate Number:</strong> ${vehicle.plate_number}<br>
                        <strong>Make:</strong> ${vehicle.make}<br>
                        <strong>Model:</strong> ${vehicle.model}
                    ` : 'Vehicle not registered in system'}
                </td>
            `;
            resultsTable.appendChild(row);
            
            // If vehicle is matched and has history, display it
            if (vehicle.matched && vehicle.barang_history && vehicle.barang_history.length > 0 && historySection) {
                historySection.style.display = 'block';
                vehicle.barang_history.forEach(transaction => {
                    const historyRow = document.createElement('tr');
                    historyRow.innerHTML = `
                        <td>${transaction.tanggal}</td>
                        <td>${transaction.kode_transaksi}</td>
                        <td><span class="badge badge-${transaction.type === 'masuk' ? 'success' : 'info'}">${transaction.type === 'masuk' ? 'Receiving' : 'Shipping'}</span></td>
                        <td>${transaction.nama_barang}</td>
                        <td>${transaction.jumlah}</td>
                        <td>${transaction.partner}</td>
                    `;
                    historyTable.appendChild(historyRow);
                });
            }
        });
    } else {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="3" class="text-center">No license plates detected</td>';
        resultsTable.appendChild(row);
    }
}
</script>
@endpush
@endsection 