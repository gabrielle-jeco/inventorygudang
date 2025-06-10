<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarcodeScannerController extends Controller
{
    public function index()
    {
        return view('barcode-scanner.index');
    }

    public function getBarangByKode(Request $request)
    {
        $kodeBarang = $request->kode_barang;
        $barang = Barang::where('kode_barang', $kodeBarang)->first();

        if ($barang) {
            return response()->json([
                'success' => true,
                'data' => $barang,
                'satuan' => $barang->satuan->satuan,
                'jenis' => $barang->jenis->jenis_barang
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Barang tidak ditemukan'
        ]);
    }
} 