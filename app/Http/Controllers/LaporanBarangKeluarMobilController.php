<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Mobil\BarangKeluarDetailExport;
use App\Exports\Mobil\BarangKeluarRekapExport;

class LaporanBarangKeluarMobilController extends Controller
{
    public function index(Request $r)
    {
        // --- Validasi & normalisasi ---
        $r->validate([
            'awal' => 'nullable|date',
            'akhir' => 'nullable|date',
            'cari' => 'nullable|string|max:50',
            'mode' => 'nullable|in:detail,rekap',
            'per_page' => 'nullable|integer|min:5|max:200',
        ]);

        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $status = $r->input('status', 'semua');
        $cari = trim((string) $r->input('cari', ''));
        $mode = $r->input('mode', 'detail');

        // pastikan awal <= akhir
        if ($awal > $akhir) {
            [$awal, $akhir] = [$akhir, $awal];
        }

        $perPage = (int) $r->input('per_page', 50);
        $perPage = min(max($perPage, 5), 200);

        // --- Siapkan builder (jangan langsung get) ---
        $detailQ = $this->detailQuery($awal, $akhir, $status, $cari);
        $rekapQ = $this->rekapQuery($awal, $akhir, $status, $cari);

        // --- Ringkasan dari detail builder (tanpa load semua baris) ---
        $totalQty = (clone $detailQ)->sum('d.qty');
        $totalValue = (clone $detailQ)->sum('d.totjual');
        $rowsCount = (clone $detailQ)->count();

        // --- Ambil dataset sesuai mode saja ---
        if ($mode === 'detail') {
            $detail = (clone $detailQ)
                ->orderBy('t.tgltrans', 'desc')
                ->orderBy('d.idtrans', 'desc')
                ->paginate($perPage)
                ->withQueryString();
            $rekap = collect(); // kosong
        } else { // rekap
            $detail = collect(); // kosong
            $rekap = (clone $rekapQ)->get();
        }

        $ringkas = [
            'rows' => $rowsCount,   // total baris detail (bukan per halaman)
            'qty' => $totalQty,
            'total' => $totalValue,
        ];

        return view('laporan.mobil.keluar', compact(
            'awal',
            'akhir',
            'status',
            'cari',
            'mode',
            'detail',
            'rekap',
            'ringkas',
            'perPage'
        ));
    }

    public function exportPdf(Request $r)
    {
        $r->validate([
            'awal' => 'nullable|date',
            'akhir' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'cari' => 'nullable|string|max:50',
            'mode' => 'nullable|in:detail,rekap',
        ]);

        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $status = $r->input('status', 'semua');
        $cari = trim((string) $r->input('cari', ''));
        $mode = $r->input('mode', 'detail');

        if ($awal > $akhir) {
            [$awal, $akhir] = [$akhir, $awal];
        }

        // guard dataset besar untuk mode detail
        if ($mode === 'detail') {
            $cnt = $this->detailQuery($awal, $akhir, $status, $cari)->count();
            if ($cnt > 3000) {
                return back()->with('error', "Data detail terlalu banyak untuk PDF ({$cnt} baris). Gunakan Excel atau perketat filter.");
            }
        }

        // ambil hanya dataset yang diperlukan
        $detail = $mode === 'detail'
            ? $this->detailQuery($awal, $akhir, $status, $cari)->get()
            : collect();

        $rekap = $mode === 'rekap'
            ? $this->rekapQuery($awal, $akhir, $status, $cari)->get()
            : collect();

        Pdf::setOptions([
            'dpi' => 96,
            'defaultFont' => 'DejaVu Sans',
            'isFontSubsettingEnabled' => true,
            'tempDir' => storage_path('app/dompdf'),
            'isHtml5ParserEnabled' => true,
        ]);

        return Pdf::loadView('laporan.mobil.keluar_pdf', compact('awal', 'akhir', 'status', 'cari', 'mode', 'detail', 'rekap'))
            ->setPaper('a4', 'landscape')
            ->download("barang-keluar-jeret-{$awal}-sd-{$akhir}-{$mode}.pdf");
    }

    public function exportExcel(Request $r)
    {
        $r->validate([
            'awal' => 'nullable|date',
            'akhir' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'cari' => 'nullable|string|max:50',
            'mode' => 'nullable|in:detail,rekap',
        ]);

        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $status = $r->input('status', 'semua');
        $cari = trim((string) $r->input('cari', ''));
        $mode = $r->input('mode', 'detail');

        if ($awal > $akhir) {
            [$awal, $akhir] = [$akhir, $awal];
        }

        $filename = "barang-keluar-jeret-{$awal}-sd-{$akhir}-{$mode}.xlsx";

        if ($mode === 'rekap') {
            return Excel::download(new BarangKeluarRekapExport($awal, $akhir, $status, $cari), $filename);
        }
        return Excel::download(new BarangKeluarDetailExport($awal, $akhir, $status, $cari), $filename);
    }

    // =====================
    // Query builder (private)
    // =====================

    private function detailQuery(string $awal, string $akhir, string $status, string $cari)
    {
        $q = DB::table('transbrg as d')
            ->join('transaksi as t', 't.idtrans', '=', 'd.idtrans')
            ->join('barang_jeret as b', 'b.id', '=', 'd.idbarang')
            ->whereBetween('t.tgltrans', [$awal, $akhir])
            ->where('b.isDeleted', 0); // hindari barang yang sudah dihapus

        if ($status !== 'semua' && $status !== '') {
            $q->where('t.status', $status);
        }

        if ($cari !== '') {
            $like = "%{$cari}%";
            $q->where(function ($w) use ($like) {
                $w->where('b.items', 'like', $like)
                    ->orWhere('b.grup', 'like', $like)
                    ->orWhere('b.merk', 'like', $like);
            });
        }

        return $q->selectRaw("
                t.tgltrans as tgl, d.idtrans, d.idbarang,
                b.items, b.grup, b.merk,
                d.qty, d.hrgjual as hrg, d.totjual as total
            ");
    }

    private function rekapQuery(string $awal, string $akhir, string $status, string $cari)
    {
        $q = DB::table('transbrg as d')
            ->join('transaksi as t', 't.idtrans', '=', 'd.idtrans')
            ->join('barang_jeret as b', 'b.id', '=', 'd.idbarang')
            ->whereBetween('t.tgltrans', [$awal, $akhir])
            ->where('b.isDeleted', 0);

        if ($status !== 'semua' && $status !== '') {
            $q->where('t.status', $status);
        }

        if ($cari !== '') {
            $like = "%{$cari}%";
            $q->where(function ($w) use ($like) {
                $w->where('b.items', 'like', $like)
                    ->orWhere('b.grup', 'like', $like)
                    ->orWhere('b.merk', 'like', $like);
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
