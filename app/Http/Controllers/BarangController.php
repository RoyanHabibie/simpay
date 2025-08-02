<?php

namespace App\Http\Controllers;

use App\Exports\BarangExport;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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

        return view('barang.index', compact('barang', 'keyword', 'perPage', 'totalQty', 'cabang'));
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

    public function report($cabang, Request $request)
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

        $barang = $query->orderBy('grup')->get();

        return view('barang.report', compact('barang', 'cabang'));
    }

    public function exportExcel($cabang, Request $request)
    {
        return Excel::download(new BarangExport($request, $cabang), 'laporan-barang-' . $cabang . '.xlsx');
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

    private function getTableName($cabang)
    {
        switch ($cabang) {
            case 'pusat':
                return 'barang';
            case 'jeret':
                return 'barang_jeret';
            case 'jayanti timur':
                return 'barang_jt';
            default:
                abort(404, 'Cabang tidak dikenal');
        }
    }
}
