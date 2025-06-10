@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Barcode Scanner</h1>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div id="reader"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div id="result">
                    <div class="text-center">
                        <h4>Hasil Scan</h4>
                        <p>Silahkan scan barcode untuk melihat detail barang</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js"></script>
<script>
    const scanner = new Html5QrcodeScanner('reader', {
        qrbox: {
            width: 250,
            height: 250,
        },
        fps: 20,
    });

    scanner.render(success, error);

    function success(result) {
        // Get data from database based on scanned barcode
        $.ajax({
            url: "{{ route('barcode.get-barang') }}",
            type: "GET",
            data: {
                kode_barang: result
            },
            success: function(response) {
                if (response.success) {
                    let data = response.data;
                    document.getElementById('result').innerHTML = `
                        <div class="card">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <img src="/storage/${data.gambar}" alt="Gambar Barang" style="max-width: 200px">
                                </div>
                                <table class="table">
                                    <tr>
                                        <td>Kode Barang</td>
                                        <td>:</td>
                                        <td>${data.kode_barang}</td>
                                    </tr>
                                    <tr>
                                        <td>Nama Barang</td>
                                        <td>:</td>
                                        <td>${data.nama_barang}</td>
                                    </tr>
                                    <tr>
                                        <td>Jenis</td>
                                        <td>:</td>
                                        <td>${response.jenis}</td>
                                    </tr>
                                    <tr>
                                        <td>Stok</td>
                                        <td>:</td>
                                        <td>${data.stok} ${response.satuan}</td>
                                    </tr>
                                    <tr>
                                        <td>Deskripsi</td>
                                        <td>:</td>
                                        <td>${data.deskripsi}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    `;
                } else {
                    document.getElementById('result').innerHTML = `
                        <div class="alert alert-danger">
                            ${response.message}
                        </div>
                    `;
                }
            }
        });
    }

    function error(err) {
        console.error(err);
    }
</script>
@endpush

@endsection 