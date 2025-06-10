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
                                <div id="resultContent" class="text-center">
                                    <!-- Result will be displayed here -->
                                </div>
                                <div id="vehicleInfo" class="mt-4">
                                    <!-- Vehicle information will be displayed here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Webcam Tab -->
                    <div class="tab-pane fade" id="webcam" role="tabpanel" aria-labelledby="webcam-tab">
                        <div class="p-4 text-center">
                            <div id="webcam-container" class="mb-4" style="display: none;">
                                <video id="webcam-video" width="640" height="480" autoplay playsinline style="border: 2px solid #ccc; border-radius: 8px;"></video>
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
                let resultHtml = '';
                
                // Check if it's an image or video
                if (response.result_path.match(/\.(jpg|jpeg|png|gif)$/i)) {
                    resultHtml = `<img src="${response.result_path}" class="img-fluid" alt="Detection Result">`;
                } else if (response.result_path.match(/\.(mp4|mov|avi)$/i)) {
                    resultHtml = `
                        <video controls class="img-fluid">
                            <source src="${response.result_path}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    `;
                }
                
                $('#resultContent').html(resultHtml);

                // Display vehicle information
                let vehicleHtml = '<div class="table-responsive"><table class="table table-bordered mt-3">';
                vehicleHtml += '<thead><tr><th>Detected Plate</th><th>Status</th><th>Details</th></tr></thead><tbody>';
                
                if (response.vehicles && response.vehicles.length > 0) {
                    response.vehicles.forEach(function(vehicle) {
                        vehicleHtml += '<tr>';
                        vehicleHtml += `<td>${vehicle.detected_number}</td>`;
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
                $('#vehicleInfo').html(vehicleHtml);

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
        const errorDiv = $('#error-message');

        try {
            // Reset error message
            errorDiv.hide();

            // First, request webcam access
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: true,
                audio: false
            });

            // Show webcam preview
            video.srcObject = stream;
            webcamContainer.show();
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Starting Detection...');

            // Start ANPR detection
            $.ajax({
                url: "{{ route('anpr.webcam') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Detection Started',
                            text: response.message,
                            icon: 'success',
                            showConfirmButton: true,
                            confirmButtonText: 'Stop Detection'
                        }).then((result) => {
                            // Stop the webcam stream
                            stream.getTracks().forEach(track => track.stop());
                            webcamContainer.hide();
                            button.prop('disabled', false).html('<i class="fas fa-play"></i> Start Webcam Detection');
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
</script>
@endpush
@endsection 