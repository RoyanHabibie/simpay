<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;              // <- ganti
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;     // <- untuk atur chunk
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class BarangExport implements FromQuery, WithHeadings, WithMapping, WithCustomChunkSize, WithStrictNullComparison
{
    public function __construct(private string $cabang, private string $keyword) {}

    public function headings(): array
    {
        return ['Items','Grup','Merk','Lokasi','Qty','Harga List','Harga Modal','Harga Agen','Harga Ecer'];
    }

    public function query(): Builder
    {
        [$table, $_] = $this->resolveTableAndTitle($this->cabang);

        $q = DB::table($table)->select([
            'items', 'grup', 'merk', 'lokasi', 'qty', 'hrglist', 'hrgmodal', 'hrgagen', 'hrgecer'
        ]);

        if (Schema::hasColumn($table, 'isDeleted')) {
            $q->where('isDeleted', 0);
        }

        if ($this->keyword !== '') {
            $like = "%{$this->keyword}%";
            $q->where(function ($w) use ($like) {
                $w->where('items', 'like', $like)
                  ->orWhere('grup',  'like', $like)
                  ->orWhere('merk',  'like', $like);
            });
        }

        // Urutan yang sama dengan tampilan
        $q->orderBy('grup')->orderBy('merk')->orderBy('items');

        return $q;
    }

    public function map($row): array
    {
        return [
            (string)$row->items,
            (string)$row->grup,
            (string)$row->merk,
            (string)($row->lokasi ?? ''),
            (int)($row->qty ?? 0),
            (float)($row->hrglist ?? 0),
            (float)($row->hrgmodal ?? 0),
            (float)($row->hrgagen ?? 0),
            (float)($row->hrgecer ?? 0),
        ];
    }

    public function chunkSize(): int
    {
        // 1000–2000 aman; sesuaikan jika server kuat
        return 2000;
    }

    private function resolveTableAndTitle(string $cabang): array
    {
        $key = strtolower($cabang);
        return match ($key) {
            'pusat'         => ['barang',      'Pusat (Motor)'],
            'jeret'         => ['barang_jeret','Mobil (Jeret)'],
            'jayanti timur' => ['barang_jt',   'Jayanti Timur (Motor)'],
            default         => ['barang',      ucfirst($cabang)],
        };
    }
}
