<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;

class BarangExport implements FromView
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = app('App\Http\Controllers\BarangController')->getFilteredQuery($this->request);
        $barang = $query->orderBy('grup')->get();

        return view('barang.report_excel', compact('barang'));
    }
}
