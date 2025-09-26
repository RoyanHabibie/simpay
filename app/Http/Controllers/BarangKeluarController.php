<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangKeluarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $cabang)
    {
        // validasi input
        $request->validate([
            'keyword' => 'nullable|string|max:50',
            'tgl' => 'nullable|date',
            'per_page' => 'nullable|integer|min:5|max:200',
        ]);

        // mapping tabel untuk tiap cabang
        [$tableKeluar, $tableBarang] = $this->resolveTables($cabang);

        $perPage = min(max((int) $request->input('per_page', 25), 5), 200);
        $keyword = trim((string) $request->input('keyword', ''));

        // redirect awal biar URL punya param ?tgl=
        $firstVisit = !$request->hasAny(['keyword', 'tgl', 'per_page', 'page']);
        if ($firstVisit) {
            return redirect()->route('barangkeluar.index', [
                'cabang' => $cabang,
                'tgl' => now()->toDateString(),
                'per_page' => $perPage,
            ]);
        }

        $tanggal = $request->input('tgl');

        // base query barang keluar + join ke barang
        $base = DB::table($tableKeluar)
            ->join($tableBarang, $tableBarang . '.id', '=', $tableKeluar . '.idbarang')
            ->select(
                $tableKeluar . '.*',
                $tableBarang . '.items',
                $tableBarang . '.grup',
                $tableBarang . '.merk'
            );

        if ($keyword !== '') {
            $like = "%{$keyword}%";
            $base->where(function ($q) use ($like, $tableBarang) {
                $q->where($tableBarang . '.items', 'like', $like)
                    ->orWhere($tableBarang . '.grup', 'like', $like)
                    ->orWhere($tableBarang . '.merk', 'like', $like);
            });
        }

        if (!empty($tanggal)) {
            $base->whereDate($tableKeluar . '.tgl', $tanggal);
        }

        // totals
        $totalQty = DB::table($tableKeluar)
            ->when($tanggal, fn($q) => $q->whereDate('tgl', $tanggal))
            ->sum('qty');

        $totalValue = DB::table($tableKeluar)
            ->when($tanggal, fn($q) => $q->whereDate('tgl', $tanggal))
            ->selectRaw("SUM(qty * hrg) as s")
            ->value('s') ?? 0;

        // listing
        $barangkeluar = (clone $base)
            ->orderByDesc($tableKeluar . '.tgl')
            ->orderByDesc($tableKeluar . '.id')
            ->paginate($perPage)
            ->withQueryString();

        return view('barangkeluar.index', [
            'barangkeluar' => $barangkeluar,
            'keyword' => $keyword,
            'tanggal' => $tanggal,
            'perPage' => $perPage,
            'totalQty' => $totalQty,
            'totalValue' => $totalValue,
            'isDefaultToday' => $firstVisit,
            'cabang' => $cabang,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $cabang)
    {
        [$tableKeluar, $tableBarang] = $this->resolveTables($cabang);

        $barang = DB::table($tableBarang)
            ->where('isDeleted', 0)
            ->orderBy('items')
            ->get();

        return view('barangkeluar.create', compact('barang', 'cabang'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $cabang)
    {
        [$tableKeluar, $tableBarang] = $this->resolveTables($cabang);

        $request->validate([
            'idbarang' => "required|exists:{$tableBarang},id",
            'tgl' => 'required|date',
            'qty' => 'required|integer|min:1',
            'hrg' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request, $tableKeluar, $tableBarang, $cabang) {
            // kunci baris barang
            $barang = DB::table($tableBarang)->where('id', $request->idbarang)->lockForUpdate()->first();

            if (!$barang) {
                return back()->withInput()->with('error', 'Barang tidak ditemukan.');
            }

            // cek stok cukup
            if ($barang->qty < $request->qty) {
                return back()->withInput()->with('error', 'Stok barang tidak mencukupi.');
            }

            // insert ke tabel keluar
            DB::table($tableKeluar)->insert([
                'idbarang' => $request->idbarang,
                'tgl' => $request->tgl,
                'qty' => $request->qty,
                'hrg' => $request->hrg,
                'updated' => now(),
            ]);

            // update stok
            $affected = DB::table($tableBarang)
                ->where('id', $barang->id)
                ->where('qty', '>=', $request->qty)
                ->decrement('qty', $request->qty);

            if ($affected === 0) {
                throw new \RuntimeException('Gagal mengurangi stok: stok berubah.');
            }

            return redirect()
                ->route('barangkeluar.index', ['cabang' => $cabang])
                ->with('success', 'Data barang keluar berhasil ditambahkan.');
        }, 3);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $cabang, int $id)
    {
        [$tableKeluar, $tableBarang] = $this->resolveTables($cabang);

        return DB::transaction(function () use ($id, $tableKeluar, $tableBarang, $cabang) {
            // lock record barang keluar
            $keluar = DB::table($tableKeluar)->where('id', $id)->lockForUpdate()->first();
            if (!$keluar) {
                return redirect()
                    ->route('barangkeluar.index', ['cabang' => $cabang])
                    ->with('error', 'Data barang keluar tidak ditemukan.');
            }

            // lock barang terkait
            $barang = DB::table($tableBarang)->where('id', $keluar->idbarang)->lockForUpdate()->first();
            if (!$barang) {
                return redirect()
                    ->route('barangkeluar.index', ['cabang' => $cabang])
                    ->with('error', 'Barang terkait tidak ditemukan.');
            }

            // balikin stok
            DB::table($tableBarang)->where('id', $barang->id)->increment('qty', $keluar->qty);

            // hapus record keluar
            DB::table($tableKeluar)->where('id', $id)->delete();

            return redirect()
                ->route('barangkeluar.index', ['cabang' => $cabang])
                ->with('success', 'Data barang keluar berhasil dihapus.');
        }, 3);
    }

    private function resolveTables(string $cabang): array
    {
        return match ($cabang) {
            'pusat' => ['keluar', 'barang'],
            'jt' => ['keluar_jt', 'barang_jt'],
            'ruko' => ['keluar_ruko', 'barang_ruko'],
            'jeret' => abort(403, 'Barang keluar tidak tersedia untuk cabang jeret.'),
            default => abort(404, 'Cabang tidak ditemukan.'),
        };
    }
}
