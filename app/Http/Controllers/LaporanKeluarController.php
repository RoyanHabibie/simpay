<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class LaporanKeluarController extends Controller
{
    public function index(Request $r)
    {
        // Validasi ringan
        $r->validate([
            'awal' => 'nullable|date',
            'akhir' => 'nullable|date',
            'lokasi' => 'nullable|in:pusat,jt,jeret,ruko',
            'cari' => 'nullable|string|max:50',
            'mode' => 'nullable|in:detail,rekap',
            'status' => 'nullable|string|max:20', // dipakai hanya untuk mobil
        ]);

        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $lokasi = $r->input('lokasi', 'pusat');     // pusat | jt | mobil | ruko
        $cari = trim($r->input('cari', ''));
        $mode = $r->input('mode', 'detail');      // detail | rekap
        $status = $r->input('status', 'semua');     // hanya untuk mobil

        if ($awal > $akhir) {
            [$awal, $akhir] = [$akhir, $awal];
        }

        // builder (jangan get dulu)
        $detailQ = $this->buildDetailQuery($lokasi, $awal, $akhir, $cari, $status);
        $rekapQ = $this->buildRekapQuery($lokasi, $awal, $akhir, $cari, $status);

        // --- ringkasan dari builder (tanpa mixing kolom) ---
        if ($lokasi === 'jeret') {
            $rows = (clone $detailQ)->count();
            $qty = (clone $detailQ)->sum('d.qty');
            $total = (clone $detailQ)->sum('d.totjual'); // langsung totjual dari transbrg
        } else {
            $rows = (clone $detailQ)->count();
            $qty = (clone $detailQ)->sum('k.qty');
            $total = (clone $detailQ)->sum(DB::raw('k.qty * k.hrg')); // aman & ringkas
        }

        // ambil keduanya (biar kompatibel dgn view lama)
        $detail = (clone $detailQ)
            ->orderByDesc($lokasi === 'jeret' ? 't.tgltrans' : 'k.tgl')
            ->orderByDesc($lokasi === 'jeret' ? 'd.idtrans' : 'k.id')
            ->get();

        $rekap = (clone $rekapQ)->get();

        $ringkas = ['rows' => $rows, 'qty' => $qty, 'total' => $total];

        return view('laporan.keluar.index', compact(
            'awal',
            'akhir',
            'lokasi',
            'cari',
            'mode',
            'detail',
            'rekap',
            'ringkas',
            'status'
        ));
    }

    public function exportPdf(Request $r)
    {
        $data = $this->getData($r); // pakai builder yang sama
        Pdf::setOptions([
            'dpi' => 96,
            'defaultFont' => 'DejaVu Sans',
            'isFontSubsettingEnabled' => true,
            'tempDir' => storage_path('app/dompdf'),
            'isHtml5ParserEnabled' => true,
        ]);

        return Pdf::loadView('laporan.keluar.pdf', $data)
            ->setPaper('a4', $data['lokasi'] === 'mobil' ? 'landscape' : 'portrait')
            ->download('laporan-barang-keluar-' . $data['lokasi'] . '-' . $data['awal'] . '-sd-' . $data['akhir'] . '.pdf');
    }

    public function exportExcel(Request $r)
    {
        // Contoh: jika sudah punya export view-based:
        // App\Exports\KeluarExport menerima $data dan render via view
        $data = $this->getData($r);
        return Excel::download(
            new \App\Exports\KeluarExport($data),
            'laporan-barang-keluar-' . $data['lokasi'] . '-' . $data['awal'] . '-sd-' . $data['akhir'] . '.xlsx'
        );
    }

    // =========================
    // Helpers
    // =========================

    private function getData(Request $r): array
    {
        $awal = $r->input('awal', now()->toDateString());
        $akhir = $r->input('akhir', now()->toDateString());
        $lokasi = $r->input('lokasi', 'pusat');     // pusat | jt | mobil | ruko
        $cari = trim($r->input('cari', ''));
        $mode = $r->input('mode', 'detail');      // detail | rekap
        $status = $r->input('status', 'semua');     // dipakai hanya untuk mobil

        if ($awal > $akhir) {
            [$awal, $akhir] = [$akhir, $awal];
        }

        // Builder dasar (jangan get dulu)
        $detailQ = $this->buildDetailQuery($lokasi, $awal, $akhir, $cari, $status);
        $rekapQ = $this->buildRekapQuery($lokasi, $awal, $akhir, $cari, $status);

        // Ambil dataset (PDF/Excel biasanya butuh keduanya)
        $detail = (clone $detailQ)
            ->orderByDesc($lokasi === 'mobil' ? 't.tgltrans' : 'k.tgl')
            ->orderByDesc($lokasi === 'mobil' ? 'd.idtrans' : 'k.id')
            ->get();

        $rekap = (clone $rekapQ)->get();

        // Ringkasan aman (aggregate terpisah → tidak mixing kolom)
        if ($lokasi === 'mobil') {
            $rows = (clone $detailQ)->count();
            $qty = (clone $detailQ)->sum('d.qty');
            $total = (clone $detailQ)->sum('d.totjual');          // langsung dari kolom agregat
        } else {
            $rows = (clone $detailQ)->count();
            $qty = (clone $detailQ)->sum('k.qty');
            $total = (clone $detailQ)->sum(DB::raw('k.qty * k.hrg')); // ekspresi aman via sum()
        }

        $ringkas = [
            'rows' => (int) $rows,
            'qty' => (int) $qty,
            'total' => (float) $total,
        ];

        return compact('awal', 'akhir', 'lokasi', 'cari', 'mode', 'status', 'detail', 'rekap', 'ringkas');
    }

    private function resolveTables(string $lokasi): array
    {
        switch ($lokasi) {
            case 'jt':
                return ['keluar_jt', 'barang_jt'];
            case 'ruko':
                return ['keluar_ruko', 'barang_ruko'];
            case 'pusat':
                return ['keluar', 'barang'];
            default:
                // fallback pusat
                return ['keluar', 'barang'];
        }
    }

    private function buildDetailQuery(string $lokasi, string $awal, string $akhir, string $cari, string $status)
    {
        if ($lokasi === 'jeret') {
            // dari penjualan mobil
            $q = DB::table('transbrg as d')
                ->join('transaksi as t', 't.idtrans', '=', 'd.idtrans')
                ->join('barang_jeret as b', 'b.id', '=', 'd.idbarang')
                ->whereBetween('t.tgltrans', [$awal, $akhir])
                ->where('b.isDeleted', 0);

            if ($status !== 'semua' && $status !== '') {
                $q->where('t.status', $status); // contoh: 'tunai', 'piutang'
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

        // pusat / jt / ruko
        [$tblKeluar, $tblBarang] = $this->resolveTables($lokasi);
        $q = DB::table("$tblKeluar as k")
            ->join("$tblBarang as b", 'k.idbarang', '=', 'b.id')
            ->whereBetween('k.tgl', [$awal, $akhir])
            ->where('b.isDeleted', 0);

        if ($cari !== '') {
            $like = "%{$cari}%";
            $q->where(function ($w) use ($like) {
                $w->where('b.items', 'like', $like)
                    ->orWhere('b.grup', 'like', $like)
                    ->orWhere('b.merk', 'like', $like);
            });
        }

        return $q->selectRaw('
            k.id, k.tgl as tgl, k.idbarang,
            b.items, b.grup, b.merk,
            k.qty, k.hrg, (k.qty * k.hrg) as total
        ');
    }

    private function buildRekapQuery(string $lokasi, string $awal, string $akhir, string $cari, string $status)
    {
        if ($lokasi === 'jeret') {
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
                    CASE WHEN SUM(d.qty)=0 THEN 0
                         ELSE SUM(d.totjual)/SUM(d.qty) END as hrg_rerata
                ")
                ->orderByDesc('qty');
        }

        // pusat / jt / ruko
        [$tblKeluar, $tblBarang] = $this->resolveTables($lokasi);
        $q = DB::table("$tblKeluar as k")
            ->join("$tblBarang as b", 'k.idbarang', '=', 'b.id')
            ->whereBetween('k.tgl', [$awal, $akhir])
            ->where('b.isDeleted', 0);

        if ($cari !== '') {
            $like = "%{$cari}%";
            $q->where(function ($w) use ($like) {
                $w->where('b.items', 'like', $like)
                    ->orWhere('b.grup', 'like', $like)
                    ->orWhere('b.merk', 'like', $like);
            });
        }

        return $q->groupBy('k.idbarang', 'b.items', 'b.grup', 'b.merk')
            ->selectRaw("
                k.idbarang, b.items, b.grup, b.merk,
                SUM(k.qty) as qty,
                SUM(k.qty * k.hrg) as total,
                CASE WHEN SUM(k.qty)=0 THEN 0
                     ELSE ROUND(SUM(k.qty * k.hrg) / SUM(k.qty)) END as hrg_rerata
            ")
            ->orderByDesc('qty');
    }
}
