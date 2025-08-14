<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            border-bottom: 1px solid #ccc;
            padding: 6px
        }

        th {
            background: #eee;
            border-top: 1px solid #ccc
        }
    </style>
</head>

<body>
    <h3>{{ $judul }}</h3>
    @if ($keyword)
        <div>Kata kunci: <strong>{{ $keyword }}</strong></div>
    @endif
    <div>Total Qty: <strong>{{ number_format($totalQty) }}</strong></div>
    <br>
    <table>
        <thead>
            <tr>
                <th>Items</th>
                <th>Grup</th>
                <th>Merk</th>
                <th>Lokasi</th>
                <th class="right">Qty</th>
                <th class="right">Harga List</th>
                <th class="right">Harga Modal</th>
                <th class="right">Harga Agen</th>
                <th class="right">Harga Ecer</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ $r->items }}</td>
                    <td>{{ $r->grup }}</td>
                    <td>{{ $r->merk }}</td>
                    <td>{{ $r->lokasi }}</td>
                    <td class="right">{{ number_format($r->qty) }}</td>
                    <td class="right">Rp {{ number_format($r->hrglist, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($r->hrgmodal, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($r->hrgagen, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($r->hrgecer, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
