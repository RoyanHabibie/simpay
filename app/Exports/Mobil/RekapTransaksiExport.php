<?php

namespace App\Exports\Mobil;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;

class RekapTransaksiExport implements FromQuery, WithHeadings, WithMapping, WithCustomChunkSize
{
    public function __construct(private string $awal, private string $akhir, private string $status)
    {
    }

    public function headings(): array
    {
        return ['Tanggal', 'Jumlah Transaksi', 'Total Barang', 'Total Jasa', 'Total Pendapatan'];
    }

    public function query()
    {
        $subBrg = DB::table('transbrg')
            ->selectRaw('idtrans, SUM(totjual) as barang')->groupBy('idtrans');

        $subJasa = DB::table('transjasa')
            ->selectRaw('idtrans, SUM(hrgjasa) as jasa')->groupBy('idtrans');

        $q = DB::table('transaksi as t')
            ->leftJoinSub($subBrg, 'tb', 'tb.idtrans', '=', 't.idtrans')
            ->leftJoinSub($subJasa, 'tj', 'tj.idtrans', '=', 't.idtrans')
            ->whereBetween('t.tgltrans', [$this->awal, $this->akhir]);

        if ($this->status !== 'semua') {
            $q->where('t.status', $this->status);
        }

        return $q->selectRaw("
                t.tgltrans as tgl,
                COUNT(t.idtrans) as totalTrans,
                SUM(COALESCE(tb.barang,0)) as totalBarang,
                SUM(COALESCE(tj.jasa,0)) as totalJasa
            ")
            ->groupBy('t.tgltrans')
            ->orderBy('t.tgltrans', 'asc');
    }

    public function map($row): array
    {
        $totalPend = (float) $row->totalBarang + (float) $row->totalJasa;
        return [
            (string) $row->tgl,
            (int) $row->totalTrans,
            (float) $row->totalBarang,
            (float) $row->totalJasa,
            $totalPend,
        ];
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
