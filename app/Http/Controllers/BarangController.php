<?php

namespace App\Http\Controllers;

use App\Exports\BarangExport;
use App\Models\Barang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $perPage = $request->input('per_page', 25); // default 25

        $query = Barang::where('isDeleted', 0);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('items', 'like', "%$keyword%")
                    ->orWhere('grup', 'like', "%$keyword%")
                    ->orWhere('merk', 'like', "%$keyword%");
            });
        }

        $barang = $query->orderBy('grup')->paginate($perPage)->withQueryString();
        $totalQty = $barang->sum('qty');

        return view('barang.index', compact('barang', 'keyword', 'perPage', 'totalQty'));
    }

    public function create()
    {
        return view('barang.create');
    }

    public function store(Request $request)
    {
        Barang::create($request->all());
        return redirect()->route('barang.index')->with('success', 'Barang ditambahkan');
    }

    public function edit($id)
    {
        $barang = Barang::findOrFail($id);
        return view('barang.edit', compact('barang'));
    }

    public function update(Request $request, $id)
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

        $barang = Barang::findOrFail($id);
        $barang->update($request->all());

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui');
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->isDeleted = 1;
        $barang->save();
        return redirect()->route('barang.index')->with('success', 'Barang dihapus');
    }

    public function report(Request $request)
    {
        $query = Barang::where('isDeleted', 0);

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

        return view('barang.report', compact('barang'));
    }

    public function exportPdf(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $barang = $query->orderBy('grup')->get();

        $pdf = Pdf::loadView('barang.report_pdf', compact('barang'));
        return $pdf->download('laporan-barang.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new BarangExport($request), 'laporan-barang.xlsx');
    }

    public function getFilteredQuery(Request $request)
    {
        $query = Barang::where('isDeleted', 0);

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
}
