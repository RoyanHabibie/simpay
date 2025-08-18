<?php

namespace App\Http\Controllers;

use App\Exports\BarangExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;

class BarangController extends Controller
{
    public function index(Request $request, $cabang)
    {
        $keyword = $request->input('keyword');
        $perPage = $request->input('per_page', 25);
        $table = $this->getTableName($cabang);

        $query = DB::table($table)->where('isDeleted', 0);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('items', 'like', "%$keyword%")
                    ->orWhere('grup', 'like', "%$keyword%")
                    ->orWhere('merk', 'like', "%$keyword%");
            });
        }

        $barang = $query->orderBy('grup')->paginate($perPage)->withQueryString();
        $totalQty = $barang->sum('qty');

        $grupList = DB::table($table)
            ->where('isDeleted', 0)
            ->where('grup', '!=', '')
            ->distinct()->orderBy('grup')
            ->pluck('grup');

        $merkList = DB::table($table)
            ->where('isDeleted', 0)
            ->where('merk', '!=', '')
            ->distinct()->orderBy('merk')
            ->pluck('merk');

        return view('barang.index', compact('barang', 'keyword', 'perPage', 'totalQty', 'cabang', 'grupList', 'merkList'));
    }

    public function merkList(Request $r, string $cabang)
    {
        $table = $this->getTableName($cabang);

        $q = DB::table($table)
            ->where('isDeleted', 0)
            ->where('merk', '!=', '');

        if ($r->filled('grup')) {
            $q->where('grup', $r->grup);
        }

        $merk = $q->distinct()->orderBy('merk')->pluck('merk');

        return response()->json($merk);
    }

    public function create($cabang)
    {
        return view('barang.create', compact('cabang'));
    }

    public function store(Request $request, $cabang)
    {
        $request->validate([
            'items' => 'required|string|max:50',
            'grup' => 'nullable|string|max:30',
            'merk' => 'nullable|string|max:30',
            'qty' => 'required|integer|min:0',
            'min' => 'nullable|integer|min:0',
            'lokasi' => 'nullable|string|max:30',
            'hrglist' => 'nullable|numeric|min:0',
            'hrgmodal' => 'nullable|numeric|min:0',
            'hrgagen' => 'nullable|numeric|min:0',
            'hrgecer' => 'nullable|numeric|min:0',
        ]);

        $table = $this->getTableName($cabang);
        $data = $request->only([
            'items',
            'grup',
            'merk',
            'qty',
            'min',
            'lokasi',
            'hrglist',
            'hrgmodal',
            'hrgagen',
            'hrgecer'
        ]);
        $data['updated_at'] = now();

        DB::table($table)->insert($data);

        return redirect()->route('barang.index', $cabang)->with('success', 'Barang ditambahkan');
    }

    public function edit($cabang, $id)
    {
        $table = $this->getTableName($cabang);
        $barang = DB::table($table)->where('id', $id)->first();

        if (!$barang)
            abort(404, 'Data tidak ditemukan');

        return view('barang.edit', compact('barang', 'cabang'));
    }

    public function update(Request $request, $cabang, $id)
    {
        $request->validate([
            'items' => 'required|string|max:50',
            'grup' => 'nullable|string|max:30',
            'merk' => 'nullable|string|max:30',
            'qty' => 'required|integer',
            'min' => 'nullable|integer',
            'lokasi' => 'nullable|string|max:30',
            'hrglist' => 'nullable|numeric',
            'hrgmodal' => 'nullable|numeric',
            'hrgagen' => 'nullable|numeric',
            'hrgecer' => 'nullable|numeric',
        ]);

        $table = $this->getTableName($cabang);
        $data = $request->except('_token', '_method');
        $data['updated_at'] = now();

        DB::table($table)->where('id', $id)->update($data);

        return redirect()->route('barang.index', $cabang)->with('success', 'Barang berhasil diperbarui');
    }

    public function destroy($cabang, $id)
    {
        $table = $this->getTableName($cabang);

        DB::table($table)->where('id', $id)->update([
            'isDeleted' => 1,
            'updated_at' => now()
        ]);

        return redirect()->route('barang.index', $cabang)->with('success', 'Barang dihapus');
    }

    public function getFilteredQuery($cabang, Request $request)
    {
        $table = $this->getTableName($cabang);

        $query = DB::table($table)->where('isDeleted', 0);

        if ($request->filled('grup')) {
            $query->where('grup', 'like', '%' . $request->grup . '%');
        }

        if ($request->filled('merk')) {
            $query->where('merk', 'like', '%' . $request->merk . '%');
        }

        if ($request->filled('lokasi')) {
            $query->where('lokasi', 'like', '%' . $request->lokasi . '%');
        }

        if ($request->input('stok_kritis')) {
            $query->whereColumn('qty', '<', 'min');
        }

        return $query;
    }

    private function getTableName(string $cabang): string
    {
        return match (strtolower($cabang)) {
            'pusat' => 'barang',
            'jeret', 'mobil' => 'barang_jeret',
            'jayanti_timur' => 'barang_jt',
            default => abort(404, 'Cabang tidak dikenal'),
        };
    }

    public function exportPdf(Request $r, string $cabang)
    {
        $keyword = trim($r->input('keyword', ''));
        [$table, $judul] = $this->resolveTableAndTitle($cabang);

        // hitung dulu
        $count = DB::table($table)
            ->when(Schema::hasColumn($table, 'isDeleted'), fn($q) => $q->where('isDeleted', 0))
            ->when($keyword !== '', function ($q) use ($keyword) {
                $like = "%{$keyword}%";
                $q->where(fn($w) => $w->where('items', 'like', $like)->orWhere('grup', 'like', $like)->orWhere('merk', 'like', $like));
            })
            ->count();

        $limit = 1000; // sesuaikan ambang yang aman
        if ($count > $limit) {
            return back()->with('error', "Data terlalu banyak untuk PDF ({$count} baris). Gunakan Excel atau perketat pencarian.");
        }

        // ambil rows setelah lolos limit
        $rows = DB::table($table)
            ->when(Schema::hasColumn($table, 'isDeleted'), fn($q) => $q->where('isDeleted', 0))
            ->when($keyword !== '', function ($q) use ($keyword) {
                $like = "%{$keyword}%";
                $q->where(fn($w) => $w->where('items', 'like', $like)->orWhere('grup', 'like', $like)->orWhere('merk', 'like', $like));
            })
            ->selectRaw('items, grup, merk, lokasi, qty, hrglist, hrgmodal, hrgagen, hrgecer')
            ->orderBy('grup')->orderBy('merk')->orderBy('items')
            ->get();

        $totalQty = $rows->sum('qty');

        // opsi hemat memori
        ini_set('memory_limit', '512M'); // quick win
        Pdf::setOptions([
            'dpi' => 96,                       // turunkan DPI
            'defaultFont' => 'DejaVu Sans',    // 1 font saja
            'isFontSubsettingEnabled' => true, // subset font hemat memori
            'isHtml5ParserEnabled' => true,    // parser HTML5 lebih stabil
            'tempDir' => storage_path('app/dompdf'), // cache ke disk
            'chroot' => public_path(),         // batasi root (opsional)
        ]);

        return Pdf::loadView('barang.export_pdf', [
            'judul' => "Data Barang - {$judul}",
            'cabang' => $cabang,
            'keyword' => $keyword,
            'rows' => $rows,
            'totalQty' => $totalQty,
        ])
            ->setPaper('a4', 'landscape')
            ->download("data-barang-{$cabang}.pdf");
    }

    public function exportExcel(Request $r, string $cabang)
    {
        $keyword = trim($r->input('keyword', ''));
        return Excel::download(new BarangExport($cabang, $keyword), "data-barang-{$cabang}.xlsx");
    }

    /** Query dasar index/export – filter keyword + hide isDeleted */
    private function baseQuery(string $table, string $keyword)
    {
        return DB::table($table)
            ->when(Schema::hasColumn($table, 'isDeleted'), fn($q) => $q->where('isDeleted', 0))
            ->when($keyword !== '', function ($q) use ($keyword) {
                $like = "%{$keyword}%";
                $q->where(function ($w) use ($like) {
                    $w->where('items', 'like', $like)
                        ->orWhere('grup', 'like', $like)
                        ->orWhere('merk', 'like', $like);
                });
            })
            ->selectRaw('id, items, grup, merk, lokasi, qty, hrglist, hrgmodal, hrgagen, hrgecer')
            ->orderBy('grup')->orderBy('merk')->orderBy('items');
    }

    /** Map nama cabang → tabel + judul */
    private function resolveTableAndTitle(string $cabang): array
    {
        $table = $this->getTableName($cabang);
        $title = match (strtolower($cabang)) {
            'pusat' => 'Pusat (Motor)',
            'jeret', 'mobil' => 'Mobil (Jeret)',
            'jayanti_timur' => 'Jayanti Timur (Motor)',
            default => ucfirst($cabang),
        };
        return [$table, $title];
    }

    private function roundExpr(string $expr, ?int $step, string $mode = 'round'): string
    {
        // step: null/0 => round ke integer; 100/500/1000 => pembulatan ke kelipatan tsb
        if (empty($step) || $step <= 1) {
            return "ROUND($expr)";
        }
        // mode: round/ceil/floor ke kelipatan step
        return match ($mode) {
            'ceil' => "CEIL(($expr) / $step) * $step",
            'floor' => "FLOOR(($expr) / $step) * $step",
            default => "ROUND(($expr) / $step) * $step",
        };
    }

    /**
     * (Opsional) Kalau mau halaman terpisah untuk form bulk
     */
    public function bulkForm(string $cabang)
    {
        $table = $this->getTableName($cabang);
        $grupList = DB::table($table)->where('isDeleted', 0)->distinct()->orderBy('grup')->pluck('grup');
        $merkList = DB::table($table)->where('isDeleted', 0)->distinct()->orderBy('merk')->pluck('merk');

        return view('barang.bulk', compact('cabang', 'grupList', 'merkList'));
    }

    /**
     * Eksekusi update massal
     */
    public function bulkUpdate(Request $r, string $cabang)
    {
        $r->validate([
            'grup' => 'nullable|string|max:50',
            'merk' => 'nullable|string|max:50',
            'disc_modal' => 'nullable|numeric',   // persen, bisa negatif
            'disc_agen' => 'nullable|numeric',   // persen, bisa negatif
            'round_step' => 'nullable|integer|in:0,1,50,100,500,1000',
            'round_mode' => 'nullable|in:round,ceil,floor',
            'konfirmasi' => 'required|accepted',  // checkbox “saya yakin”
        ]);

        $table = $this->getTableName($cabang);
        $roundStep = (int) ($r->input('round_step', 0));
        $roundMode = $r->input('round_mode', 'round');

        // Builder dasar
        $base = DB::table($table)->where('isDeleted', 0);
        if ($r->filled('grup'))
            $base->where('grup', $r->grup);
        if ($r->filled('merk'))
            $base->where('merk', $r->merk);

        // Hitung target baris (buat info)
        $target = (clone $base)->count();
        if ($target === 0) {
            return back()->with('error', 'Tidak ada data yang cocok dengan filter.');
        }

        // Siapkan ekspresi update
        $updates = [];

        if ($r->filled('disc_modal')) {
            $f = 1 + ((float) $r->disc_modal / 100.0); // misal -10 → 0.9
            // pastikan tidak negatif: GREATEST(…,0)
            $expr = "GREATEST(" . $this->roundExpr("hrgmodal * $f", $roundStep, $roundMode) . ", 0)";
            $updates['hrgmodal'] = DB::raw($expr);
        }

        if ($r->filled('disc_agen')) {
            $f = 1 + ((float) $r->disc_agen / 100.0);
            $expr = "GREATEST(" . $this->roundExpr("hrgagen * $f", $roundStep, $roundMode) . ", 0)";
            $updates['hrgagen'] = DB::raw($expr);
        }

        // Kalau tidak ada field yang diubah
        if (empty($updates)) {
            return back()->with('error', 'Tidak ada perubahan harga yang dipilih.');
        }

        $updates['updated_at'] = now();

        // Eksekusi
        DB::beginTransaction();
        try {
            $affected = $base->update($updates); // updated_at ikut auto dengan trigger timestamp default
            DB::commit();

            return back()->with('success', "Harga berhasil diupdate pada $affected dari $target item.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }
}
