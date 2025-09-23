<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $r)
    {
        // Normalisasi periode (default: 30 hari terakhir s.d. hari ini)
        $awal = $r->filled('awal')
            ? Carbon::parse($r->input('awal'))->toDateString()
            : now()->subDays(30)->toDateString();

        $akhir = $r->filled('akhir')
            ? Carbon::parse($r->input('akhir'))->toDateString()
            : now()->toDateString();

        if ($awal > $akhir) {
            [$awal, $akhir] = [$akhir, $awal];
        }

        // Aggregated stats per cabang
        $stats = $this->getStatsPerCabang();

        // KPI pergerakan per cabang (periode)
        $kpi = [
            'pusat' => $this->kpiKeluar('keluar', 'barang', $awal, $akhir),
            'jeret' => $this->kpiMobil($awal, $akhir),
            'jayanti timur' => $this->kpiKeluar('keluar_jt', 'barang_jt', $awal, $akhir),
            'ruko' => $this->kpiKeluar('keluar_ruko', 'barang_ruko', $awal, $akhir),
        ];

        // Tren harian qty (periode)
        $tren = [
            'pusat' => $this->trenHarian('keluar', 'tgl', 'qty', $awal, $akhir),
            'jeret' => $this->trenHarianMobil($awal, $akhir),
            'jayanti timur' => $this->trenHarian('keluar_jt', 'tgl', 'qty', $awal, $akhir),
            'ruko' => $this->trenHarian('keluar_ruko', 'tgl', 'qty', $awal, $akhir),
        ];

        // Daftar top movers & stok kritis per cabang
        $fastMovers = [
            'pusat' => $this->topKeluar('keluar', 'barang', $awal, $akhir, 5),
            'jeret' => $this->topKeluarMobil($awal, $akhir, 5),
            'jayanti timur' => $this->topKeluar('keluar_jt', 'barang_jt', $awal, $akhir, 5),
            'ruko' => $this->topKeluar('keluar_ruko', 'barang_ruko', $awal, $akhir, 5),
        ];
        $kritis = [
            'pusat' => $this->stokKritis('barang', 5),
            'jeret' => $this->stokKritis('barang_jeret', 5),
            'jayanti timur' => $this->stokKritis('barang_jt', 5),
            'ruko' => $this->stokKritis('barang_ruko', 5),
        ];

        return view('dashboard.index', compact('stats', 'awal', 'akhir', 'kpi', 'tren', 'fastMovers', 'kritis'));
    }

    public function getStatsPerCabang(): array
    {
        $cabangList = [
            'pusat' => 'barang',
            'jeret' => 'barang_jeret',
            'jayanti timur' => 'barang_jt',
            'ruko' => 'barang_ruko',
        ];

        $stats = [];

        foreach ($cabangList as $nama => $tabel) {
            $base = DB::table($tabel)->where('isDeleted', 0);

            // Agregat utama dalam satu query
            $agg = (clone $base)
                ->selectRaw('
                    COUNT(*)                            as total_barang,
                    COALESCE(SUM(qty), 0)               as total_qty,
                    COALESCE(SUM(qty * hrgmodal), 0)    as total_nilai,
                    COUNT(DISTINCT grup)                as grup_unik,
                    COUNT(DISTINCT merk)                as merk_unik
                ')
                ->first();

            // Stok kritis dihitung terpisah (perlu whereColumn)
            $stokKritis = (clone $base)
                ->whereColumn('qty', '<', 'min')
                ->count();

            $stats[$nama] = [
                'total_barang' => (int) $agg->total_barang,
                'total_qty' => (int) $agg->total_qty,
                'total_nilai' => (float) $agg->total_nilai,
                'stok_kritis' => (int) $stokKritis,
                'grup_unik' => (int) $agg->grup_unik,
                'merk_unik' => (int) $agg->merk_unik,
            ];
        }

        return $stats;
    }

    private function kpiKeluar(string $tblKeluar, string $tblBarang, string $awal, string $akhir): array
    {
        $base = DB::table("$tblKeluar as k")
            ->whereBetween('k.tgl', [$awal, $akhir]);

        $agg = (clone $base)
            ->selectRaw('
                COALESCE(SUM(k.qty), 0)           as qty,
                COALESCE(SUM(k.qty * k.hrg), 0)   as nilai,
                COUNT(DISTINCT k.idbarang)        as aktif
            ')
            ->first();

        $underMin = DB::table($tblBarang)
            ->where('isDeleted', 0)
            ->whereColumn('qty', '<', 'min')
            ->count();

        return [
            'qty' => (int) $agg->qty,
            'nilai' => (float) $agg->nilai,
            'aktif' => (int) $agg->aktif,
            'underMin' => (int) $underMin,
        ];
    }

    private function kpiMobil(string $awal, string $akhir): array
    {
        // Dari transaksi mobil (Jeret): transbrg + transaksi
        $q = DB::table('transbrg as d')
            ->join('transaksi as t', 't.idtrans', '=', 'd.idtrans')
            ->whereBetween('t.tgltrans', [$awal, $akhir]);

        $agg = (clone $q)
            ->selectRaw('
                COALESCE(SUM(d.qty), 0)     as qty,
                COALESCE(SUM(d.totjual), 0) as nilai,
                COUNT(DISTINCT d.idbarang)  as aktif
            ')
            ->first();

        $underMin = DB::table('barang_jeret')
            ->where('isDeleted', 0)
            ->whereColumn('qty', '<', 'min')
            ->count();

        return [
            'qty' => (int) $agg->qty,
            'nilai' => (float) $agg->nilai,
            'aktif' => (int) $agg->aktif,
            'underMin' => (int) $underMin,
        ];
    }

    private function trenHarian(string $tblKeluar, string $tglCol, string $qtyCol, string $awal, string $akhir): array
    {
        $rows = DB::table($tblKeluar)
            ->selectRaw("$tglCol as tgl, SUM($qtyCol) as qty")
            ->whereBetween($tglCol, [$awal, $akhir])
            ->groupBy($tglCol)
            ->orderBy($tglCol)
            ->get();

        return [
            'labels' => $rows->pluck('tgl'),
            'data' => $rows->pluck('qty'),
        ];
    }

    private function trenHarianMobil(string $awal, string $akhir): array
    {
        $rows = DB::table('transbrg as d')
            ->join('transaksi as t', 't.idtrans', '=', 'd.idtrans')
            ->selectRaw('t.tgltrans as tgl, SUM(d.qty) as qty')
            ->whereBetween('t.tgltrans', [$awal, $akhir])
            ->groupBy('t.tgltrans')
            ->orderBy('t.tgltrans')
            ->get();

        return [
            'labels' => $rows->pluck('tgl'),
            'data' => $rows->pluck('qty'),
        ];
    }

    private function topKeluar(string $tblKeluar, string $tblBarang, string $awal, string $akhir, int $limit = 5)
    {
        return DB::table("$tblKeluar as k")
            ->join("$tblBarang as b", 'b.id', '=', 'k.idbarang')
            ->whereBetween('k.tgl', [$awal, $akhir])
            ->groupBy('k.idbarang', 'b.items')
            ->selectRaw('b.items, SUM(k.qty) as qty')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();
    }

    private function topKeluarMobil(string $awal, string $akhir, int $limit = 5)
    {
        return DB::table('transbrg as d')
            ->join('transaksi as t', 't.idtrans', '=', 'd.idtrans')
            ->join('barang_jeret as b', 'b.id', '=', 'd.idbarang')
            ->whereBetween('t.tgltrans', [$awal, $akhir])
            ->groupBy('d.idbarang', 'b.items')
            ->selectRaw('b.items, SUM(d.qty) as qty')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();
    }

    private function stokKritis(string $tblBarang, int $limit = 5)
    {
        return DB::table($tblBarang)
            ->where('isDeleted', 0)
            ->whereColumn('qty', '<', 'min')
            ->orderByRaw('(`min` - `qty`) DESC') // <= ini kunci
            ->select('items', 'grup', 'merk', 'qty', 'min')
            ->limit($limit)
            ->get();
    }
}
