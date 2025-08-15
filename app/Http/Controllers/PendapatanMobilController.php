<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Exports\Mobil\PendapatanExport;

class PendapatanMobilController extends Controller
{
    public function index(Request $r)
    {
        $bulan = (int) $r->input('bulan', now()->month); // 1..12
        $tahun = (int) $r->input('tahun', now()->year);

        [$awal, $akhir] = $this->rangeBulan($tahun, $bulan);
        $data = $this->hitung($awal, $akhir); // tanpa status

        return view('laporan.mobil.pendapatan', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'awal' => $awal->toDateString(),
            'akhir' => $akhir->toDateString(),
        ] + $data);
    }

    public function exportPdf(Request $r)
    {
        $bulan = (int) $r->input('bulan', now()->month);
        $tahun = (int) $r->input('tahun', now()->year);
        [$awal, $akhir] = $this->rangeBulan($tahun, $bulan);

        $data = $this->hitung($awal, $akhir);

        Pdf::setOptions([
            'dpi' => 96,
            'defaultFont' => 'DejaVu Sans',
            'isFontSubsettingEnabled' => true,
            'tempDir' => storage_path('app/dompdf'),
        ]);

        return Pdf::loadView('laporan.mobil.pendapatan_pdf', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'awal' => $awal->toDateString(),
            'akhir' => $akhir->toDateString(),
        ] + $data)->setPaper('a4', 'portrait')
            ->download("pendapatan-jeret-{$tahun}-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . ".pdf");
    }

    public function exportExcel(Request $r)
    {
        $bulan = (int) $r->input('bulan', now()->month);
        $tahun = (int) $r->input('tahun', now()->year);
        [$awal, $akhir] = $this->rangeBulan($tahun, $bulan);

        $data = $this->hitung($awal, $akhir);

        return Excel::download(
            new PendapatanExport($bulan, $tahun, $data),
            "pendapatan-jeret-{$tahun}-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . ".xlsx"
        );
    }

    private function rangeBulan(int $tahun, int $bulan): array
    {
        $start = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();
        return [$start, $end];
    }

    /**
     * Hitung komponen laporan (tanpa status):
     * - Barang  : SUM(transbrg.totjual)
     * - Jasa    : SUM(transjasa.hrgjasa WHERE jenis != 'steam')
     * - Steam   : SUM(transjasa.hrgjasa WHERE jenis = 'steam')
     * - Belanja : SUM(belanja.SpendPrice) per Status (operasional / non-operasional / gaji)
     */
    private function hitung(Carbon $awal, Carbon $akhir): array
    {
        // Pendapatan barang
        $barang = (float) DB::table('transbrg as d')
            ->join('transaksi as t', 't.idtrans', '=', 'd.idtrans')
            ->whereBetween('t.tgltrans', [$awal->toDateString(), $akhir->toDateString()])
            ->sum('d.totjual');

        // Jasa (non steam)
        $jasa = (float) DB::table('transjasa as j')
            ->join('transaksi as t', 't.idtrans', '=', 'j.idtrans')
            ->whereBetween('t.tgltrans', [$awal->toDateString(), $akhir->toDateString()])
            ->whereRaw("LOWER(COALESCE(j.jenis,'')) <> 'steam'")
            ->sum('j.hrgjasa');

        // Steam
        $steam = (float) DB::table('transjasa as j')
            ->join('transaksi as t', 't.idtrans', '=', 'j.idtrans')
            ->whereBetween('t.tgltrans', [$awal->toDateString(), $akhir->toDateString()])
            ->whereRaw("LOWER(COALESCE(j.jenis,'')) = 'steam'")
            ->sum('j.hrgjasa');

        $pendapatanSubtotal = $barang + $jasa + $steam;

        // Pengeluaran
        $ops = (float) DB::table('belanja')
            ->whereBetween('SpendDate', [$awal->toDateString(), $akhir->toDateString()])
            ->whereRaw("LOWER(COALESCE(Status,'')) = 'operasional'")
            ->sum('SpendPrice');

        $nonops = (float) DB::table('belanja')
            ->whereBetween('SpendDate', [$awal->toDateString(), $akhir->toDateString()])
            ->whereRaw("LOWER(COALESCE(Status,'')) IN ('non operasional','non-operasional','non_operasional')")
            ->sum('SpendPrice');

        $gaji = (float) DB::table('belanja')
            ->whereBetween('SpendDate', [$awal->toDateString(), $akhir->toDateString()])
            ->whereRaw("LOWER(COALESCE(Status,'')) = 'gaji'")
            ->sum('SpendPrice');

        $pengeluaranSubtotal = $ops + $nonops + $gaji;
        $total = $pendapatanSubtotal - $pengeluaranSubtotal;

        return compact(
            'barang',
            'jasa',
            'steam',
            'pendapatanSubtotal',
            'ops',
            'nonops',
            'gaji',
            'pengeluaranSubtotal',
            'total'
        );
    }
}
