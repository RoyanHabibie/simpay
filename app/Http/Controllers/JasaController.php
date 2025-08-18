<?php

namespace App\Http\Controllers;

use App\Models\Jasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JasaController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'keyword' => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|min:5|max:200',
            'sort' => 'nullable|in:name,code,price',
            'dir' => 'nullable|in:asc,desc',
        ]);

        $perPage = (int) $request->input('per_page', 25);
        $perPage = min(max($perPage, 5), 200);

        $keyword = trim((string) $request->input('keyword', ''));
        $sortMap = ['name' => 'NameOfServ', 'code' => 'ServCode', 'price' => 'ServPrice'];
        $sortCol = $sortMap[$request->input('sort', 'name')] ?? 'NameOfServ';
        $dir = $request->input('dir', 'asc');

        $q = Jasa::query();

        if ($keyword !== '') {
            $like = "%{$keyword}%";
            $q->where(function ($w) use ($like) {
                $w->where('NameOfServ', 'like', $like)
                    ->orWhere('ServCode', 'like', $like);
            });
        }

        $jasa = $q->orderBy($sortCol, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('jasa.index', compact('jasa', 'keyword', 'perPage', 'sortCol', 'dir'));
    }

    public function create()
    {
        return view('jasa.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ServCode' => 'required|string|max:10|unique:jasa,ServCode',
            'NameOfServ' => 'required|string|max:100',
            'ServPrice' => 'required|numeric|min:0',
        ]);

        // (opsional) seragamkan kode jasa
        // $request->merge(['ServCode' => strtoupper($request->ServCode)]);

        Jasa::create($request->only(['ServCode', 'NameOfServ', 'ServPrice']));

        return redirect()->route('jasa.index')->with('success', 'Data jasa berhasil ditambahkan.');
    }

    public function edit(Jasa $jasa)
    {
        return view('jasa.edit', compact('jasa'));
    }

    public function update(Request $request, Jasa $jasa)
    {
        $request->validate([
            'NameOfServ' => 'required|string|max:100',
            'ServPrice' => 'required|numeric|min:0',
            // kalau suatu hari ServCode boleh diubah:
            // 'ServCode'   => 'required|string|max:10|unique:jasa,ServCode,' . $jasa->id,
        ]);

        $jasa->update($request->only(['NameOfServ', 'ServPrice']));

        return redirect()->route('jasa.index')->with('success', 'Data jasa berhasil diperbarui.');
    }

    public function destroy(Jasa $jasa)
    {
        // Cegah hapus jika sudah dipakai transaksi (transjasa)
        $dipakai = DB::table('transjasa')->where('idjasa', $jasa->id)->exists();
        if ($dipakai) {
            return back()->with('error', 'Tidak bisa dihapus: jasa sudah dipakai pada transaksi.');
        }

        // Kalau mau soft delete, aktifkan SoftDeletes di model Jasa lalu ganti ke $jasa->delete();
        $jasa->delete();

        return redirect()->route('jasa.index')->with('success', 'Data jasa berhasil dihapus.');
    }
}
