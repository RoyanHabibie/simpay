<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Mobil\BarangKeluarDetailExport;
use App\Exports\Mobil\BarangKeluarRekapExport;

class BarangKeluarMobilController extends Controller
{
    public function index(Request $r)
    {
        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $status = $r->input('status', 'semua');   // filter bayar
        $cari = trim($r->input('cari', ''));
        $mode = $r->input('mode', 'detail');    // detail|rekap

        // DETAIL
        $detail = $this->detailQuery($awal, $akhir, $status, $cari)->get();

        // REKAP
        $rekap = $this->rekapQuery($awal, $akhir, $status, $cari)->get();

        $ringkas = [
            'rows' => $detail->count(),
            'qty' => $detail->sum('qty'),
            'total' => $detail->sum('total'),
        ];

        return view('laporan.mobil.keluar', compact('awal', 'akhir', 'status', 'cari', 'mode', 'detail', 'rekap', 'ringkas'));
    }

    public function exportPdf(Request $r)
    {
        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $status = $r->input('status', 'semua');
        $cari = trim($r->input('cari', ''));
        $mode = $r->input('mode', 'detail');

        // guard untuk dataset besar (detail)
        if ($mode === 'detail') {
            $cnt = $this->detailQuery($awal, $akhir, $status, $cari)->count();
            if ($cnt > 3000) {
                return back()->with('error', "Data detail terlalu banyak untuk PDF ({$cnt} baris). Gunakan Excel atau perketat filter.");
            }
        }

        $detail = $this->detailQuery($awal, $akhir, $status, $cari)->get();
        $rekap = $this->rekapQuery($awal, $akhir, $status, $cari)->get();

        Pdf::setOptions(['dpi' => 96, 'defaultFont' => 'DejaVu Sans', 'isFontSubsettingEnabled' => true, 'tempDir' => storage_path('app/dompdf')]);

        return Pdf::loadView('laporan.mobil.keluar_pdf', compact('awal', 'akhir', 'status', 'cari', 'mode', 'detail', 'rekap'))
            ->setPaper('a4', 'landscape')
            ->download("barang-keluar-jeret-{$awal}-sd-{$akhir}-{$mode}.pdf");
    }

    public function exportExcel(Request $r)
    {
        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $status = $r->input('status', 'semua');
        $cari = trim($r->input('cari', ''));
        $mode = $r->input('mode', 'detail');

        $filename = "barang-keluar-jeret-{$awal}-sd-{$akhir}-{$mode}.xlsx";
        if ($mode === 'rekap') {
            return \Maatwebsite\Excel\Facades\Excel::download(new BarangKeluarRekapExport($awal, $akhir, $status, $cari), $filename);
        }
        return \Maatwebsite\Excel\Facades\Excel::download(new BarangKeluarDetailExport($awal, $akhir, $status, $cari), $filename);
    }

    private function detailQuery(string $awal, string $akhir, string $status, string $cari)
    {
        $q = DB::table('transbrg as d')
            ->join('transaksi as t', 't.idtrans', '=', 'd.idtrans')
            ->join('barang_jeret as b', 'b.id', '=', 'd.idbarang')
            ->whereBetween('t.tgltrans', [$awal, $akhir]);

        if ($status !== 'semua')
            $q->where('t.status', $status);

        if ($cari !== '') {
            $like = "%{$cari}%";
            $q->where(function ($w) use ($like) {
                $w->where('b.items', 'like', $like)->orWhere('b.grup', 'like', $like)->orWhere('b.merk', 'like', $like);
            });
        }

        return $q->selectRaw("
                t.tgltrans as tgl, d.idtrans, d.idbarang,
                b.items, b.grup, b.merk,
                d.qty, d.hrgjual as hrg, d.totjual as total
            ")
            ->orderBy('t.tgltrans', 'desc')->orderBy('d.idtrans', 'desc');
    }

    private function rekapQuery(string $awal, string $akhir, string $status, string $cari)
    {
        $q = DB::table('transbrg as d')
            ->join('transaksi as t', 't.idtrans', '=', 'd.idtrans')
            ->join('barang_jeret as b', 'b.id', '=', 'd.idbarang')
            ->whereBetween('t.tgltrans', [$awal, $akhir]);

        if ($status !== 'semua')
            $q->where('t.status', $status);

        if ($cari !== '') {
            $like = "%{$cari}%";
            $q->where(function ($w) use ($like) {
                $w->where('b.items', 'like', $like)->orWhere('b.grup', 'like', $like)->orWhere('b.merk', 'like', $like);
            });
        }

        return $q->groupBy('d.idbarang', 'b.items', 'b.grup', 'b.merk')
            ->selectRaw("
                d.idbarang, b.items, b.grup, b.merk,
                SUM(d.qty) as qty,
                SUM(d.totjual) as total,
                CASE WHEN SUM(d.qty)=0 THEN 0 ELSE SUM(d.totjual)/SUM(d.qty) END as hrg_rerata
            ")
            ->orderByDesc('qty');
    }
}
