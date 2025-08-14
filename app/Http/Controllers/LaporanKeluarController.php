<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKeluarController extends Controller
{
    public function index(Request $r)
    {
        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $lokasi = $r->input('lokasi', 'pusat'); // pusat | jt
        $cari = trim($r->input('cari', ''));
        $mode = $r->input('mode', 'detail');  // detail | rekap

        [$tblKeluar, $tblBarang] = $this->resolveTables($lokasi);

        // DETAIL
        $detail = DB::table("$tblKeluar as k")
            ->join("$tblBarang as b", 'k.idbarang', '=', 'b.id')
            ->when($cari !== '', function ($q) use ($cari) {
                $like = "%{$cari}%";
                $q->where(function ($w) use ($like) {
                    $w->where('b.items', 'like', $like)
                        ->orWhere('b.grup', 'like', $like)
                        ->orWhere('b.merk', 'like', $like);
                });
            })
            ->whereBetween('k.tgl', [$awal, $akhir])
            ->selectRaw('k.id, k.tgl, k.idbarang, b.items, b.grup, b.merk, k.qty, k.hrg, (k.qty * k.hrg) as total')
            ->orderByDesc('k.tgl')
            ->orderByDesc('k.id')
            ->get();

        // REKAP — kompatibel ONLY_FULL_GROUP_BY
        $rekap = DB::table("$tblKeluar as k")
            ->join("$tblBarang as b", 'k.idbarang', '=', 'b.id')
            ->when($cari !== '', function ($q) use ($cari) {
                $like = "%{$cari}%";
                $q->where(function ($w) use ($like) {
                    $w->where('b.items', 'like', $like)
                        ->orWhere('b.grup', 'like', $like)
                        ->orWhere('b.merk', 'like', $like);
                });
            })
            ->whereBetween('k.tgl', [$awal, $akhir])
            ->groupBy('k.idbarang', 'b.items', 'b.grup', 'b.merk')
            ->selectRaw("
                k.idbarang,
                b.items, b.grup, b.merk,
                SUM(k.qty) as qty,
                SUM(k.qty * k.hrg) as total,
                CASE WHEN SUM(k.qty)=0 THEN 0 ELSE ROUND(SUM(k.qty * k.hrg) / SUM(k.qty)) END as hrg_rerata
            ")
            ->orderByDesc('qty')
            ->get();

        $ringkas = [
            'rows' => $detail->count(),
            'qty' => $detail->sum('qty'),
            'total' => $detail->sum('total'),
        ];

        return view('laporan.keluar.index', compact('awal', 'akhir', 'lokasi', 'cari', 'mode', 'detail', 'rekap', 'ringkas'));
    }

    public function exportPdf(Request $r)
    {
        // Ambil data sama seperti index:
        $data = $this->getData($r);
        $pdf = app('dompdf.wrapper')->loadView('laporan.keluar.pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('laporan-barang-keluar-' . $data['lokasi'] . '-' . $data['awal'] . '-sd-' . $data['akhir'] . '.pdf');
    }

    public function exportExcel(Request $r)
    {
        // Pakai Maatwebsite\Excel Export sederhana berbasis view
        $data = $this->getData($r);
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\KeluarExport($data), 'laporan-barang-keluar-' . $data['lokasi'] . '-' . $data['awal'] . '-sd-' . $data['akhir'] . '.xlsx');
    }

    private function resolveTables(string $lokasi): array
    {
        return $lokasi === 'jt'
            ? ['keluar_jt', 'barang_jt']
            : ['keluar', 'barang'];
    }

    private function getData(Request $r): array
    {
        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $lokasi = $r->input('lokasi', 'pusat');
        $cari = trim($r->input('cari', ''));
        $mode = $r->input('mode', 'detail');

        [$tblKeluar, $tblBarang] = $this->resolveTables($lokasi);

        $detail = DB::table("$tblKeluar as k")
            ->join("$tblBarang as b", 'k.idbarang', '=', 'b.id')
            ->when($cari !== '', function ($q) use ($cari) {
                $like = "%{$cari}%";
                $q->where(function ($w) use ($like) {
                    $w->where('b.items', 'like', $like)
                        ->orWhere('b.grup', 'like', $like)
                        ->orWhere('b.merk', 'like', $like);
                });
            })
            ->whereBetween('k.tgl', [$awal, $akhir])
            ->selectRaw('k.id, k.tgl, k.idbarang, b.items, b.grup, b.merk, k.qty, k.hrg, (k.qty * k.hrg) as total')
            ->orderByDesc('k.tgl')
            ->orderByDesc('k.id')
            ->get();

        $rekap = DB::table("$tblKeluar as k")
            ->join("$tblBarang as b", 'k.idbarang', '=', 'b.id')
            ->when($cari !== '', function ($q) use ($cari) {
                $like = "%{$cari}%";
                $q->where(function ($w) use ($like) {
                    $w->where('b.items', 'like', $like)
                        ->orWhere('b.grup', 'like', $like)
                        ->orWhere('b.merk', 'like', $like);
                });
            })
            ->whereBetween('k.tgl', [$awal, $akhir])
            ->groupBy('k.idbarang', 'b.items', 'b.grup', 'b.merk')
            ->selectRaw("
                k.idbarang,
                b.items, b.grup, b.merk,
                SUM(k.qty) as qty,
                SUM(k.qty * k.hrg) as total,
                CASE WHEN SUM(k.qty)=0 THEN 0 ELSE ROUND(SUM(k.qty * k.hrg) / SUM(k.qty)) END as hrg_rerata
            ")
            ->orderByDesc('qty')
            ->get();

        $ringkas = [
            'rows' => $detail->count(),
            'qty' => $detail->sum('qty'),
            'total' => $detail->sum('total'),
        ];

        return compact('awal', 'akhir', 'lokasi', 'cari', 'mode', 'detail', 'rekap', 'ringkas');
    }
}
