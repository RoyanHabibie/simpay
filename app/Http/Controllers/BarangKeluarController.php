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
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $tanggal = $request->input('tgl', now()->toDateString());
        $perPage = $request->input('per_page', 25);

        $query = BarangKeluar::with('barang');

        if ($keyword) {
            $query->whereHas('barang', function ($q) use ($keyword) {
                $q->where('items', 'like', "%$keyword%");
            });
        }

        if ($request->has('tgl') && $tanggal !== '') {
            $query->whereDate('tgl', $tanggal);
        }

        // Clone query sebelum paginasi, untuk sum
        $sumQuery = clone $query;

        // Jalankan paginasi
        $barangkeluar = $query->orderByDesc('tgl')->paginate($perPage)->withQueryString();

        // Hitung total qty dan total nilai
        $totalQty = $sumQuery->sum('qty');
        $totalValue = $sumQuery->selectRaw('SUM(qty * hrg) as total')->value('total');

        return view('barangkeluar.index', compact('barangkeluar', 'keyword', 'tanggal', 'perPage', 'totalQty', 'totalValue'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $barang = DB::table('barang')->where('isDeleted', 0)->orderBy('items')->get();
        return view('barangkeluar.create', compact('barang'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'idbarang' => 'required|exists:barang,id',
            'tgl' => 'required|date',
            'qty' => 'required|integer|min:1',
            'hrg' => 'required|numeric|min:0',
        ]);

        // Proses dalam transaksi agar konsisten
        DB::beginTransaction();
        try {
            $barang = Barang::findOrFail($request->idbarang);

            // Cek stok cukup
            if ($barang->qty < $request->qty) {
                return back()->withInput()->with('error', 'Stok barang tidak mencukupi.');
            }

            // Simpan barang keluar
            BarangKeluar::create([
                'idbarang' => $request->idbarang,
                'tgl' => $request->tgl,
                'qty' => $request->qty,
                'hrg' => $request->hrg,
            ]);

            // Update stok di tabel barang
            $barang->decrement('qty', $request->qty);

            DB::commit();

            return redirect()->route('barangkeluar.index')->with('success', 'Data barang keluar berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $barangKeluar = BarangKeluar::findOrFail($id);
            $barang = Barang::findOrFail($barangKeluar->idbarang);

            // Kembalikan qty ke stok barang
            $barang->increment('qty', $barangKeluar->qty);

            // Hapus data barang keluar
            $barangKeluar->delete();

            DB::commit();

            return redirect()->route('barangkeluar.index')->with('success', 'Data barang keluar berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('barangkeluar.index')->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
