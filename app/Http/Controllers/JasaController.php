<?php

namespace App\Http\Controllers;

use App\Models\Jasa;
use Illuminate\Http\Request;

class JasaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $perPage = $request->input('per_page', 25); // default 25

        $query = Jasa::query();

        if ($keyword) {
            $query->where('NameOfServ', 'like', "%$keyword%");
        }

        $jasa = $query->paginate($perPage)->withQueryString();

        return view('jasa.index', compact('jasa', 'keyword', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('jasa.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ServCode' => 'required|string|max:10|unique:jasa,ServCode',
            'NameOfServ' => 'required|string|max:100',
            'ServPrice' => 'required|numeric|min:0',
        ]);

        Jasa::create($request->only(['ServCode', 'NameOfServ', 'ServPrice']));

        return redirect()->route('jasa.index')->with('success', 'Data jasa berhasil ditambahkan.');
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
        $jasa = Jasa::findOrFail($id);
        return view('jasa.edit', compact('jasa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'NameOfServ' => 'required|string|max:100',
            'ServPrice' => 'required|numeric|min:0',
        ]);

        $jasa = Jasa::findOrFail($id);
        $jasa->update($request->only(['NameOfServ', 'ServPrice']));

        return redirect()->route('jasa.index')->with('success', 'Data jasa berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $jasa = Jasa::findOrFail($id);
        $jasa->delete();

        return redirect()->route('jasa.index')->with('success', 'Data jasa berhasil dihapus.');
    }
}
