<table>
    <thead>
        <tr>
            <th colspan="11" style="font-size: 16px; font-weight: bold; text-align: center;">
                Laporan Barang - Cabang {{ strtoupper($cabang) }}
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000;">No</th>
            <th style="font-weight: bold; border: 1px solid #000;">Items</th>
            <th style="font-weight: bold; border: 1px solid #000;">Grup</th>
            <th style="font-weight: bold; border: 1px solid #000;">Merk</th>
            <th style="font-weight: bold; border: 1px solid #000;">Qty</th>
            <th style="font-weight: bold; border: 1px solid #000;">Min</th>
            <th style="font-weight: bold; border: 1px solid #000;">Lokasi</th>
            <th style="font-weight: bold; border: 1px solid #000;">Harga List</th>
            <th style="font-weight: bold; border: 1px solid #000;">Harga Modal</th>
            <th style="font-weight: bold; border: 1px solid #000;">Harga Agen</th>
            <th style="font-weight: bold; border: 1px solid #000;">Harga Ecer</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($barang as $index => $b)
            <tr>
                <td style="border: 1px solid #000;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000;">{{ $b->items }}</td>
                <td style="border: 1px solid #000;">{{ $b->grup }}</td>
                <td style="border: 1px solid #000;">{{ $b->merk }}</td>
                <td style="border: 1px solid #000;">{{ $b->qty }}</td>
                <td style="border: 1px solid #000;">{{ $b->min }}</td>
                <td style="border: 1px solid #000;">{{ $b->lokasi }}</td>
                <td style="border: 1px solid #000;">{{ $b->hrglist }}</td>
                <td style="border: 1px solid #000;">{{ $b->hrgmodal }}</td>
                <td style="border: 1px solid #000;">{{ $b->hrgagen }}</td>
                <td style="border: 1px solid #000;">{{ $b->hrgecer }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
