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
        // --- Validasi ringan & pembatasan paging (hindari beban berlebih) ---
        $request->validate([
            'keyword' => 'nullable|string|max:50',
            'tgl' => 'nullable|date',
            'per_page' => 'nullable|integer|min:5|max:200',
        ]);

        $perPage = (int) $request->input('per_page', 25);
        $perPage = min(max($perPage, 5), 200);

        $keyword = trim((string) $request->input('keyword', ''));

        // --- First visit: paksa ada parameter tgl=hari ini (canonical URL) ---
        $firstVisit = !$request->hasAny(['keyword', 'tgl', 'per_page', 'page']);
        if ($firstVisit) {
            // Opsi A (disarankan): redirect supaya URL mengandung ?tgl=YYYY-MM-DD
            return redirect()->route('barangkeluar.index', [
                'tgl' => now()->toDateString(),
                'per_page' => $perPage,
            ]);

            // Opsi B (kalau tidak mau redirect): pakai default tanpa ubah URL
            // $tanggal = now()->toDateString();
        }

        // Ambil tanggal dari query (sudah divalidasi di atas)
        $tanggal = $request->input('tgl'); // format YYYY-MM-DD (hasil validasi 'date')

        // --- Base query: eager load relasi untuk hindari N+1 ---
        $base = BarangKeluar::query()->with('barang');

        if ($keyword !== '') {
            $base->whereHas('barang', function ($q) use ($keyword) {
                $q->where('items', 'like', "%{$keyword}%")
                    ->orWhere('grup', 'like', "%{$keyword}%")
                    ->orWhere('merk', 'like', "%{$keyword}%");
            });
        }

        if (!empty($tanggal)) {
            $base->whereDate('tgl', $tanggal);
        }

        // --- Totals: dihitung dari clone base query (bukan dari paginator) ---
        // catatan: (clone $base)->sum('qty') langsung compile jadi agregat SQL
        $totalQty = (clone $base)->sum('qty');
        $totalValue = (clone $base)->selectRaw('SUM(qty * hrg) as s')->value('s') ?? 0;

        // --- Listing: order stabil (tgl DESC, id DESC) + pagination aman ---
        $barangkeluar = $base->orderByDesc('tgl')->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('barangkeluar.index', [
            'barangkeluar' => $barangkeluar,
            'keyword' => $keyword,
            'tanggal' => $tanggal,   // kirim ke blade untuk isi default input date
            'perPage' => $perPage,
            'totalQty' => $totalQty,
            'totalValue' => $totalValue,
            'isDefaultToday' => $firstVisit, // dipakai kalau kamu aktifkan Opsi B
        ]);
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

        return DB::transaction(function () use ($request) {
            // Kunci baris barang yg akan diubah
            $barang = Barang::whereKey($request->idbarang)->lockForUpdate()->firstOrFail();

            // Cek stok cukup (di server, bukan hanya UI)
            if ($barang->qty < $request->qty) {
                return back()->withInput()->with('error', 'Stok barang tidak mencukupi.');
            }

            // Simpan header/detail barang keluar
            BarangKeluar::create([
                'idbarang' => $request->idbarang,
                'tgl' => $request->tgl,
                'qty' => $request->qty,
                'hrg' => $request->hrg,
            ]);

            // Kurangi stok dengan guard qty >= ? untuk jaga-jaga
            $affected = Barang::whereKey($barang->id)
                ->where('qty', '>=', $request->qty)
                ->decrement('qty', $request->qty);

            if ($affected === 0) {
                // keadaan balapan ekstrem (nyaris mustahil krn lock), tapi kita aman-kan
                throw new \RuntimeException('Gagal mengurangi stok: stok berubah.');
            }

            return redirect()->route('barangkeluar.index')->with('success', 'Data barang keluar berhasil ditambahkan.');
        }, 3); // retry 3x otomatis jika deadlock
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
        return DB::transaction(function () use ($id) {
            $barangKeluar = BarangKeluar::lockForUpdate()->findOrFail($id); // kunci record keluar-nya

            $barang = Barang::whereKey($barangKeluar->idbarang)->lockForUpdate()->firstOrFail();
            // Kembalikan stok
            $barang->increment('qty', $barangKeluar->qty);

            // Hapus data barang keluar
            $barangKeluar->delete();

            return redirect()->route('barangkeluar.index')->with('success', 'Data barang keluar berhasil dihapus.');
        }, 3);
    }
}
