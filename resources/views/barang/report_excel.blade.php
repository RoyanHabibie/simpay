    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Items</th>
                <th>Grup</th>
                <th>Merk</th>
                <th>Qty</th>
                <th>Min</th>
                <th>Lokasi</th>
                <th>Harga List</th>
                <th>Harga Modal</th>
                <th>Harga Agen</th>
                <th>Harga Ecer</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($barang as $index => $b)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $b->items }}</td>
                    <td>{{ $b->grup }}</td>
                    <td>{{ $b->merk }}</td>
                    <td>{{ $b->qty }}</td>
                    <td>{{ $b->min }}</td>
                    <td>{{ $b->lokasi }}</td>
                    <td>{{ $b->hrglist }}</td>
                    <td>{{ $b->hrgmodal }}</td>
                    <td>{{ $b->hrgagen }}</td>
                    <td>{{ $b->hrgecer }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
