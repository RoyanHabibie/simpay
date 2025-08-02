<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;

class BarangExport implements FromView
{
    protected $request;
    protected $cabang;

    public function __construct(Request $request, $cabang)
    {
        $this->request = $request;
        $this->cabang = $cabang;
    }

    protected function getTableName()
    {
        switch ($this->cabang) {
            case 'pusat':
                return 'barang';
            case 'jeret':
                return 'barang_jeret';
            case 'jayanti timur':
                return 'barang_jt';
            default:
                abort(404, 'Cabang tidak dikenali');
        }
    }

    public function view(): View
    {
        $table = $this->getTableName();

        $query = DB::table($table)->where('isDeleted', 0);

        if ($this->request->filled('grup')) {
            $query->where('grup', 'like', '%' . $this->request->grup . '%');
        }

        if ($this->request->filled('merk')) {
            $query->where('merk', 'like', '%' . $this->request->merk . '%');
        }

        if ($this->request->filled('lokasi')) {
            $query->where('lokasi', 'like', '%' . $this->request->lokasi . '%');
        }

        if ($this->request->input('stok_kritis')) {
            $query->whereColumn('qty', '<', 'min');
        }

        $barang = $query->orderBy('grup')->get();

        return view('barang.report_excel', [
            'barang' => $barang,
            'cabang' => $this->cabang,
        ]);
    }
}
