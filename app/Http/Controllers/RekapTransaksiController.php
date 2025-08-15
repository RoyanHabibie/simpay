<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Mobil\RekapTransaksiExport;

class RekapTransaksiController extends Controller
{
    public function index(Request $r)
    {
        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $status = $r->input('status', 'semua'); // semua|tunai|kredit

        $data = $this->baseQuery($awal, $akhir, $status)->get();

        $ringkas = [
            'hari' => $data->count(),
            'totalTrans' => $data->sum('totalTrans'),
            'totalBarang' => $data->sum('totalBarang'),
            'totalJasa' => $data->sum('totalJasa'),
            'totalPend' => $data->sum(fn($r) => $r->totalBarang + $r->totalJasa),
        ];

        return view('laporan.mobil.rekap', compact('awal', 'akhir', 'status', 'data', 'ringkas'));
    }

    public function exportPdf(Request $r)
    {
        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $status = $r->input('status', 'semua');

        $rows = $this->baseQuery($awal, $akhir, $status)->get();

        // dataset harian biasanya kecil; tetap bisa naikkan opsi aman
        Pdf::setOptions([
            'dpi' => 96,
            'defaultFont' => 'DejaVu Sans',
            'isFontSubsettingEnabled' => true,
            'tempDir' => storage_path('app/dompdf'),
        ]);

        return Pdf::loadView('laporan.mobil.rekap_pdf', [
            'awal' => $awal,
            'akhir' => $akhir,
            'status' => $status,
            'rows' => $rows
        ])->setPaper('a4', 'portrait')
            ->download("rekap-transaksi-jeret-{$awal}-sd-{$akhir}.pdf");
    }

    public function exportExcel(Request $r)
    {
        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $status = $r->input('status', 'semua');

        return Excel::download(
            new RekapTransaksiExport($awal, $akhir, $status),
            "rekap-transaksi-jeret-{$awal}-sd-{$akhir}.xlsx"
        );
    }

    private function baseQuery(string $awal, string $akhir, string $status)
    {
        // Subtotal per transaksi (barang & jasa)
        $subBrg = DB::table('transbrg')
            ->selectRaw('idtrans, SUM(totjual) as barang')
            ->groupBy('idtrans');
        $subJasa = DB::table('transjasa')
            ->selectRaw('idtrans, SUM(hrgjasa) as jasa')
            ->groupBy('idtrans');

        $q = DB::table('transaksi as t')
            ->leftJoinSub($subBrg, 'tb', 'tb.idtrans', '=', 't.idtrans')
            ->leftJoinSub($subJasa, 'tj', 'tj.idtrans', '=', 't.idtrans')
            ->whereBetween('t.tgltrans', [$awal, $akhir]);

        if ($status !== 'semua') {
            $q->where('t.status', $status);
        }

        return $q->selectRaw("
                t.tgltrans as tgl,
                COUNT(t.idtrans) as totalTrans,
                SUM(COALESCE(tb.barang,0)) as totalBarang,
                SUM(COALESCE(tj.jasa,0)) as totalJasa
            ")
            ->groupBy('t.tgltrans')
            ->orderBy('t.tgltrans', 'asc');
    }
}
