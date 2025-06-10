<?php

use App\Http\Controllers\ActivityLogController;
use App\Models\Supplier;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JenisController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HakAksesController;
use App\Http\Controllers\LaporanBarangKeluarController;
use App\Http\Controllers\LaporanBarangMasukController;
use App\Http\Controllers\LaporanStokController;
use App\Http\Controllers\ManajemenUserController;
use App\Http\Controllers\UbahPasswordController;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Http\Controllers\BarcodeScannerController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\AnprController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::middleware('auth')->group(function () {

    Route::group(['middleware' => 'checkRole:superadmin'], function(){
        Route::get('/data-pengguna/get-data', [ManajemenUserController::class, 'getDataPengguna']);
        Route::get('/api/role/', [ManajemenUserController::class, 'getRole']);
        Route::resource('/data-pengguna', ManajemenUserController::class);

        Route::get('/hak-akses/get-data', [HakAksesController::class, 'getDataRole']);
        Route::resource('/hak-akses', HakAksesController::class);
    });

    Route::group(['middleware' => 'checkRole:superadmin,kepala gudang'], function(){
        Route::resource('/aktivitas-user', ActivityLogController::class);

    });

    Route::group(['middleware' => 'checkRole:kepala gudang,superadmin,admin gudang'], function(){
        Route::resource('/dashboard', DashboardController::class);
        Route::get('/', [DashboardController::class, 'index']);

        Route::get('/laporan-stok/get-data', [LaporanStokController::class, 'getData']);
        Route::get('/laporan-stok/print-stok', [LaporanStokController::class, 'printStok']);
        Route::get('/api/satuan/', [LaporanStokController::class, 'getSatuan']);
        Route::resource('/laporan-stok', LaporanStokController::class);

        Route::get('/laporan-barang-masuk/get-data', [LaporanBarangMasukController::class, 'getData']);
        Route::get('/laporan-barang-masuk/print-barang-masuk', [LaporanBarangMasukController::class, 'printBarangMasuk']);
        Route::get('/api/supplier/', [LaporanBarangMasukController::class, 'getSupplier']);
        Route::resource('/laporan-barang-masuk', LaporanBarangMasukController::class);

        Route::get('/laporan-barang-keluar/get-data', [LaporanBarangKeluarController::class, 'getData']);
        Route::get('/laporan-barang-keluar/print-barang-keluar', [LaporanBarangKeluarController::class, 'printBarangKeluar']);
        Route::get('/api/customer/', [LaporanBarangKeluarController::class, 'getCustomer']);
        Route::resource('/laporan-barang-keluar', LaporanBarangKeluarController::class);

        Route::get('/ubah-password', [UbahPasswordController::class,'index']);
        Route::POST('/ubah-password', [UbahPasswordController::class, 'changePassword']);
    });


    Route::group(['middleware' => 'checkRole:superadmin,admin gudang'], function(){
        Route::get('/barang/get-data', [BarangController::class, 'getDataBarang']);
        Route::resource('/barang', BarangController::class);

        Route::get('/jenis-barang/get-data', [JenisController::class, 'getDataJenisBarang']);
        Route::resource('/jenis-barang', JenisController::class);

        Route::get('/satuan-barang/get-data', [SatuanController::class, 'getDataSatuanBarang']);
        Route::resource('/satuan-barang', SatuanController::class);

        Route::get('/supplier/get-data', [SupplierController::class, 'getDataSupplier']);
        Route::resource('/supplier', SupplierController::class);

        Route::get('/customer/get-data', [CustomerController::class, 'getDataCustomer']);
        Route::resource('/customer', CustomerController::class);

        Route::get('/api/barang-masuk/', [BarangMasukController::class, 'getAutoCompleteData']);
        Route::get('/barang-masuk/get-data', [BarangMasukController::class, 'getDataBarangMasuk']);
        Route::get('/api/satuan/', [BarangMasukController::class, 'getSatuan']);
        Route::resource('/barang-masuk', BarangMasukController::class);

        Route::get('/api/barang-keluar/', [BarangKeluarController::class, 'getAutoCompleteData']);
        Route::get('/barang-keluar/get-data', [BarangKeluarController::class, 'getDataBarangKeluar']);
        Route::get('/api/satuan/', [BarangKeluarController::class, 'getSatuan']);
        Route::resource('/barang-keluar', BarangKeluarController::class);

    });

    Route::get('/barcode-scanner', [BarcodeScannerController::class, 'index'])->name('barcode.index');
    Route::get('/barcode-scanner/get-barang', [BarcodeScannerController::class, 'getBarangByKode'])->name('barcode.get-barang');

});

// Vehicle routes (only accessible by superadmin)
Route::middleware(['auth', 'checkRole:superadmin'])->group(function () {
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/data', [VehicleController::class, 'data'])->name('vehicles.data');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicles/{id}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/vehicles/{id}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
});

// ANPR routes (accessible by all authenticated users)
Route::middleware(['auth'])->group(function () {
    Route::get('/anpr', [AnprController::class, 'index'])->name('anpr.index');
    Route::post('/anpr/detect', [AnprController::class, 'detect'])->name('anpr.detect');
    Route::post('/anpr/webcam', [AnprController::class, 'webcam'])->name('anpr.webcam');
});

Route::get('/health', function () {
    return 'OK';
});

require __DIR__.'/auth.php';
