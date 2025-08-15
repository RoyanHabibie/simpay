<?php

namespace App\Exports\Mobil;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;

class BarangKeluarRekapExport implements FromQuery, WithHeadings, WithMapping, WithCustomChunkSize
{
    public function __construct(private string $awal, private string $akhir, private string $status, private string $cari)
    {
    }

    public function headings(): array
    {
        return ['Barang', 'Grup', 'Merk', 'Qty', 'Harga Rerata', 'Total'];
    }

    public function query()
    {
        $q = DB::table('transbrg as d')
            ->join('transaksi as t', 't.idtrans', '=', 'd.idtrans')
            ->join('barang_jeret as b', 'b.id', '=', 'd.idbarang')
            ->whereBetween('t.tgltrans', [$this->awal, $this->akhir]);

        if ($this->status !== 'semua')
            $q->where('t.status', $this->status);

        if ($this->cari !== '') {
            $like = "%{$this->cari}%";
            $q->where(function ($w) use ($like) {
                $w->where('b.items', 'like', $like)->orWhere('b.grup', 'like', $like)->orWhere('b.merk', 'like', $like);
            });
        }

        return $q->groupBy('d.idbarang', 'b.items', 'b.grup', 'b.merk')
            ->selectRaw("
                b.items, b.grup, b.merk,
                SUM(d.qty) as qty,
                CASE WHEN SUM(d.qty)=0 THEN 0 ELSE SUM(d.totjual)/SUM(d.qty) END as hrg_rerata,
                SUM(d.totjual) as total
            ")->orderByDesc('qty');
    }

    public function map($row): array
    {
        return [
            (string) $row->items,
            (string) $row->grup,
            (string) $row->merk,
            (int) $row->qty,
            (float) $row->hrg_rerata,
            (float) $row->total,
        ];
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
