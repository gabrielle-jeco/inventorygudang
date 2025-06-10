@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>ANPR Shipping Proof</h1>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form id="anpr-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="media">Upload Image/Video</label>
                        <input type="file" class="form-control" id="media" name="media" accept="image/*,video/*">
                    </div>
                    <div class="form-group">
                        <label>Or use Live Camera</label><br>
                        <button type="button" class="btn btn-primary" id="start-camera">Start Camera</button>
                        <video id="video" width="100%" height="240" autoplay style="display:none;"></video>
                        <canvas id="canvas" style="display:none;"></canvas>
                        <button type="button" class="btn btn-success mt-2" id="capture" style="display:none;">Capture Photo</button>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Detect Plate</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div id="result">
                    <div class="text-center">
                        <h4>Detection Result</h4>
                        <p>Upload or capture a photo/video to detect the number plate.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
let video = document.getElementById('video');
let canvas = document.getElementById('canvas');
let startCameraBtn = document.getElementById('start-camera');
let captureBtn = document.getElementById('capture');
let mediaInput = document.getElementById('media');
let stream;

startCameraBtn.onclick = async function() {
    stream = await navigator.mediaDevices.getUserMedia({ video: true });
    video.srcObject = stream;
    video.style.display = 'block';
    captureBtn.style.display = 'inline-block';
    canvas.style.display = 'none';
};

captureBtn.onclick = function() {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    canvas.style.display = 'block';
    // Stop the camera
    stream.getTracks().forEach(track => track.stop());
    video.style.display = 'none';
    captureBtn.style.display = 'none';
    // Convert canvas to blob and set as file input
    canvas.toBlob(function(blob) {
        let file = new File([blob], 'capture.jpg', {type: 'image/jpeg'});
        let dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        mediaInput.files = dataTransfer.files;
    }, 'image/jpeg');
};

$('#anpr-form').on('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    $('#result').html('<div class="text-center"><div class="spinner-border"></div><p>Processing...</p></div>');
    $.ajax({
        url: "{{ route('anpr.detect') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#result').html('<div class="alert alert-success"><b>Detected Plate:</b> ' + response.result + '</div>');
            } else {
                $('#result').html('<div class="alert alert-danger">Detection failed.</div>');
            }
        },
        error: function() {
            $('#result').html('<div class="alert alert-danger">Error processing the request.</div>');
        }
    });
});
</script>
@endpush
@endsection 