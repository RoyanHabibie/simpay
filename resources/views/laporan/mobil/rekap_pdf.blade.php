<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            border: 1px solid #444;
            padding: 6px
        }

        th {
            background: #eee
        }

        .r {
            text-align: right
        }
    </style>
</head>

<body>
    <h3>Rekap Transaksi — Mobil (Jeret)</h3>
    <div>Periode: {{ $awal }} s/d {{ $akhir }} — Status: {{ ucfirst($status) }}</div><br>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="r">Jumlah Transaksi</th>
                <th class="r">Total Barang</th>
                <th class="r">Total Jasa</th>
                <th class="r">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ $r->tgl }}</td>
                    <td class="r">{{ number_format($r->totalTrans) }}</td>
                    <td class="r">{{ number_format($r->totalBarang, 0, ',', '.') }}</td>
                    <td class="r">{{ number_format($r->totalJasa, 0, ',', '.') }}</td>
                    <td class="r">{{ number_format($r->totalBarang + $r->totalJasa, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
