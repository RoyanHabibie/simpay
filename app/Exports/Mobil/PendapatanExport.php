<?php

namespace App\Exports\Mobil;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PendapatanExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(private string $bulan, private string $status, private array $data)
    {
    }

    public function headings(): array
    {
        return ['Bagian', 'Kategori', 'Nilai'];
    }

    public function array(): array
    {
        $d = $this->data;

        return [
            ['Pendapatan', 'Barang', (float) $d['barang']],
            ['Pendapatan', 'Jasa', (float) $d['jasa']],
            ['Pendapatan', 'Steam', (float) $d['steam']],
            ['Pendapatan', 'Subtotal', (float) $d['pendapatanSubtotal']],
            [],
            ['Pengeluaran', 'Operasional', (float) $d['ops']],
            ['Pengeluaran', 'Non Operasional', (float) $d['nonops']],
            ['Pengeluaran', 'Gaji', (float) $d['gaji']],
            ['Pengeluaran', 'Subtotal', (float) $d['pengeluaranSubtotal']],
            [],
            ['Total', '', (float) $d['total']],
        ];
    }
}
