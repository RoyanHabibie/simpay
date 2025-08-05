@extends('layouts.app')

@section('content')
    <h1 class="h3 mb-3">Laporan Barang</h1>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="lokasi" class="form-select">
                <option value="motor" {{ $lokasi == 'motor' ? 'selected' : '' }}>Motor</option>
                <option value="mobil" {{ $lokasi == 'mobil' ? 'selected' : '' }}>Mobil</option>
                <option value="jt" {{ $lokasi == 'jt' ? 'selected' : '' }}>Jayanti Timur</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="tgl_awal" class="form-control" value="{{ $tglAwal }}">
        </div>
        <div class="col-md-3">
            <input type="date" name="tgl_akhir" class="form-control" value="{{ $tglAkhir }}">
        </div>
        <div class="col-md-3">
            <select name="mode" class="form-select">
                <option value="detail" {{ $mode == 'detail' ? 'selected' : '' }}>Detail</option>
                <option value="rekap" {{ $mode == 'rekap' ? 'selected' : '' }}>Rekap</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">Tampilkan</button>
        </div>
    </form>

    @if ($data->count())
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    @if ($mode == 'detail')
                        <tr>
                            <th>Tanggal</th>
                            <th>Items</th>
                            <th>Grup</th>
                            <th>Merk</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Total</th>
                        </tr>
                    @else
                        <tr>
                            <th>Items</th>
                            <th>Grup</th>
                            <th>Merk</th>
                            <th>Total Qty</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @foreach ($data as $row)
                        <tr>
                            @if ($mode == 'detail')
                                <td>{{ $row->tgl }}</td>
                                <td>{{ $row->items }}</td>
                                <td>{{ $row->grup }}</td>
                                <td>{{ $row->merk }}</td>
                                <td>{{ $row->qty }}</td>
                                <td>Rp {{ number_format($row->hrg, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                            @else
                                <td>{{ $row->items }}</td>
                                <td>{{ $row->grup }}</td>
                                <td>{{ $row->merk }}</td>
                                <td>{{ $row->qty }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

@endsection
