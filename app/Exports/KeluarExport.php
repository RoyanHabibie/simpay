<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class KeluarExport implements FromView
{
    public function __construct(private array $data)
    {
    }

    public function view(): View
    {
        // Reuse view PDF biar konsisten (atau buat view excel khusus)
        return view('laporan.keluar.excel', $this->data);
    }
}
