<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $cabangList = [
            'pusat' => 'barang',
            'jeret' => 'barang_jeret',
            'jayanti timur' => 'barang_jt'
        ];

        $stats = [];

        foreach ($cabangList as $nama => $tabel) {
            $data = DB::table($tabel)->where('isDeleted', 0);

            $stats[$nama] = [
                'total_barang' => $data->count(),
                'total_qty' => $data->sum('qty'),
                'total_nilai' => $data->sum(DB::raw('qty * hrgmodal')),
                'stok_kritis' => $data->whereColumn('qty', '<', 'min')->count(),
                'grup_unik' => $data->distinct('grup')->count('grup'),
                'merk_unik' => $data->distinct('merk')->count('merk'),
            ];
        }

        return view('dashboard.index', compact('stats'));
    }
}
