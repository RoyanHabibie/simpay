<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Barang Keluar ({{ $lokasi }})</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 6px;
        }

        th {
            background: #eee;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: #666;
        }
    </style>
</head>

<body>
    <h3>Laporan Barang Keluar — {{ $lokasi === 'jt' ? 'Jayanti Timur' : 'Pusat' }}</h3>
    <div class="muted">Periode: {{ $awal }} s/d {{ $akhir }}</div>
    <div class="muted">Mode: {{ strtoupper($mode) }}</div>
    <br>
    @if ($mode === 'detail')
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Barang</th>
                    <th>Grup</th>
                    <th>Merk</th>
                    <th class="right">Qty</th>
                    <th class="right">Harga</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detail as $r)
                    <tr>
                        <td>{{ $r->tgl }}</td>
                        <td>{{ $r->items }}</td>
                        <td>{{ $r->grup }}</td>
                        <td>{{ $r->merk }}</td>
                        <td class="right">{{ number_format($r->qty) }}</td>
                        <td class="right">{{ number_format($r->hrg, 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($r->total, 0, ',', '.') }}</td>
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
                    <th class="right">Qty</th>
                    <th class="right">Harga Rerata</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rekap as $r)
                    <tr>
                        <td>{{ $r->items }}</td>
                        <td>{{ $r->grup }}</td>
                        <td>{{ $r->merk }}</td>
                        <td class="right">{{ number_format($r->qty) }}</td>
                        <td class="right">{{ number_format($r->hrg_rerata, 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($r->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <br>
    <div>Total Qty: {{ number_format($ringkas['qty']) }} — Total Nilai: Rp
        {{ number_format($ringkas['total'], 0, ',', '.') }}</div>
</body>

</html>
