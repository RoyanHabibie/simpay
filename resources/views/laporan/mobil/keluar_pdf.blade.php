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
    <h3>Barang Keluar — Mobil (Jeret)</h3>
    <div>Periode: {{ $awal }} s/d {{ $akhir }} — Status: {{ ucfirst($status) }}</div><br>

    @if ($mode === 'detail')
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>ID Transaksi</th>
                    <th>Barang</th>
                    <th>Grup</th>
                    <th>Merk</th>
                    <th class="r">Qty</th>
                    <th class="r">Harga</th>
                    <th class="r">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detail as $r)
                    <tr>
                        <td>{{ $r->tgl }}</td>
                        <td>{{ $r->idtrans }}</td>
                        <td>{{ $r->items }}</td>
                        <td>{{ $r->grup }}</td>
                        <td>{{ $r->merk }}</td>
                        <td class="r">{{ number_format($r->qty) }}</td>
                        <td class="r">{{ number_format($r->hrg, 0, ',', '.') }}</td>
                        <td class="r">{{ number_format($r->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Grup</th>
                    <th>Merk</th>
                    <th class="r">Qty</th>
                    <th class="r">Harga Rerata</th>
                    <th class="r">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rekap as $r)
                    <tr>
                        <td>{{ $r->items }}</td>
                        <td>{{ $r->grup }}</td>
                        <td>{{ $r->merk }}</td>
                        <td class="r">{{ number_format($r->qty) }}</td>
                        <td class="r">{{ number_format($r->hrg_rerata, 0, ',', '.') }}</td>
                        <td class="r">{{ number_format($r->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
