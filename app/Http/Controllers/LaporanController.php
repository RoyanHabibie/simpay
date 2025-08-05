<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $lokasi = $request->input('lokasi', 'motor');
        $tglAwal = $request->input('tgl_awal', now()->toDateString());
        $tglAkhir = $request->input('tgl_akhir', now()->toDateString());
        $mode = $request->input('mode', 'detail'); // detail / rekap

        $data = [];

        if ($mode === 'detail') {
            $data = $this->getDetailLaporan($lokasi, $tglAwal, $tglAkhir);
        } else {
            $data = $this->getRekapLaporan($lokasi, $tglAwal, $tglAkhir);
        }

        return view('laporan.index', compact('data', 'lokasi', 'tglAwal', 'tglAkhir', 'mode'));
    }

    protected function getDetailLaporan($lokasi, $awal, $akhir)
    {
        switch ($lokasi) {
            case 'motor':
                return DB::table('keluar as a')
                    ->join('barang as b', 'a.idbarang', '=', 'b.id')
                    ->whereBetween('a.tgl', [$awal, $akhir])
                    ->select('a.tgl', 'b.items', 'b.grup', 'b.merk', DB::raw('SUM(a.qty) as qty'), 'a.hrg', DB::raw('SUM(a.qty * a.hrg) as total'))
                    ->groupBy('a.idbarang', 'a.tgl', 'a.hrg', 'b.items', 'b.grup', 'b.merk')
                    ->orderBy('a.tgl')
                    ->get();

            case 'mobil':
                return DB::table('transaksi as a')
                    ->join('transbrg as b', 'a.idtrans', '=', 'b.idtrans')
                    ->join('barang_jeret as c', 'b.idbarang', '=', 'c.id')
                    ->whereBetween('a.tgltrans', [$awal, $akhir])
                    ->select(
                        'a.tgltrans as tgl',
                        'c.items',
                        'c.grup',
                        'c.merk',
                        DB::raw('SUM(b.qty) as qty'),
                        'b.hrgjual as hrg',
                        DB::raw('SUM(b.qty * b.hrgjual) as total')
                    )
                    ->groupBy('b.idbarang', 'a.tgltrans', 'b.hrgjual', 'c.items', 'c.grup', 'c.merk')
                    ->orderBy('a.tgltrans')
                    ->get();

            case 'jt':
                return DB::table('keluar_jt as a')
                    ->join('barang_jt as b', 'a.idbarang', '=', 'b.id')
                    ->whereBetween('a.tgl', [$awal, $akhir])
                    ->select('a.tgl', 'b.items', 'b.grup', 'b.merk', DB::raw('SUM(a.qty) as qty'), 'a.hrg', DB::raw('SUM(a.qty * a.hrg) as total'))
                    ->groupBy('a.idbarang', 'a.tgl', 'a.hrg', 'b.items', 'b.grup', 'b.merk')
                    ->orderBy('a.tgl')
                    ->get();

            default:
                return collect();
        }
    }

    protected function getRekapLaporan($lokasi, $awal, $akhir)
    {
        switch ($lokasi) {
            case 'motor':
                return DB::table('keluar as a')
                    ->join('barang as b', 'a.idbarang', '=', 'b.id')
                    ->whereBetween('a.tgl', [$awal, $akhir])
                    ->select('b.items', 'b.grup', 'b.merk', DB::raw('SUM(a.qty) as qty'))
                    ->groupBy('a.idbarang', 'b.items', 'b.grup', 'b.merk')
                    ->orderByDesc('qty')
                    ->get();

            case 'mobil':
                return DB::table('transaksi as a')
                    ->join('transbrg as b', 'a.idtrans', '=', 'b.idtrans')
                    ->join('barang_jeret as c', 'b.idbarang', '=', 'c.id')
                    ->whereBetween('a.tgltrans', [$awal, $akhir])
                    ->select(
                        'c.items',
                        'c.grup',
                        'c.merk',
                        DB::raw('SUM(b.qty) as qty')
                    )
                    ->groupBy('b.idbarang')
                    ->orderByDesc('qty')
                    ->get();

            case 'jt':
                return DB::table('keluar_jt as a')
                    ->join('barang_jt as b', 'a.idbarang', '=', 'b.id')
                    ->whereBetween('a.tgl', [$awal, $akhir])
                    ->select('b.items', 'b.grup', 'b.merk', DB::raw('SUM(a.qty) as qty'))
                    ->groupBy('a.idbarang')
                    ->orderByDesc('qty')
                    ->get();

            default:
                return collect();
        }
    }
}
