@extends('layouts.app')
@section('content')
    <div class="container">
        <h3 class="mb-3">Laporan Barang Keluar — Mobil (Jeret)</h3>

        @if (session('error'))
            <div class="alert alert-warning">{{ session('error') }}</div>
        @endif

        <form class="row g-2 mb-3">
            <div class="col-auto"><input type="date" name="awal" value="{{ $awal }}" class="form-control"></div>
            <div class="col-auto"><input type="date" name="akhir" value="{{ $akhir }}" class="form-control">
            </div>
            <div class="col-auto">
                <input type="text" name="cari" value="{{ $cari }}" class="form-control"
                    placeholder="items/grup/merk">
            </div>
            <div class="col-auto">
                <select name="mode" class="form-select">
                    <option value="detail" {{ $mode === 'detail' ? 'selected' : '' }}>Detail</option>
                    <option value="rekap" {{ $mode === 'rekap' ? 'selected' : '' }}>Rekap</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-primary">Tampilkan</button></div>
            <div class="col-auto ms-auto">
                <a href="{{ route('laporan.mobil.keluar.pdf', request()->all()) }}" class="btn btn-outline-danger">PDF</a>
                <a href="{{ route('laporan.mobil.keluar.excel', request()->all()) }}"
                    class="btn btn-outline-success">Excel</a>
            </div>
        </form>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Jumlah Baris</div>
                        <div class="fs-4 fw-semibold">{{ number_format($ringkas['rows']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Total Qty</div>
                        <div class="fs-4 fw-semibold">{{ number_format($ringkas['qty']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Total Nilai</div>
                        <div class="fs-4 fw-semibold">Rp {{ number_format($ringkas['total'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if ($mode === 'detail')
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <span>Detail Barang Keluar</span>
                    <small class="text-muted">Periode: {{ $awal }} s/d {{ $akhir }}</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>ID Transaksi</th>
                                <th>Barang</th>
                                <th>Grup</th>
                                <th>Merk</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($detail as $r)
                                <tr>
                                    <td class="text-nowrap">{{ $r->tgl }}</td>
                                    <td class="text-nowrap">{{ $r->idtrans }}</td>
                                    <td>{{ $r->items }}</td>
                                    <td>{{ $r->grup }}</td>
                                    <td>{{ $r->merk }}</td>
                                    <td class="text-end">{{ number_format($r->qty) }}</td>
                                    <td class="text-end">Rp {{ number_format($r->hrg, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($r->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <span>Rekap Barang Keluar (per Barang)</span>
                    <small class="text-muted">Periode: {{ $awal }} s/d {{ $akhir }}</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Barang</th>
                                <th>Grup</th>
                                <th>Merk</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga Rerata</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rekap as $r)
                                <tr>
                                    <td>{{ $r->items }}</td>
                                    <td>{{ $r->grup }}</td>
                                    <td>{{ $r->merk }}</td>
                                    <td class="text-end">{{ number_format($r->qty) }}</td>
                                    <td class="text-end">Rp {{ number_format($r->hrg_rerata, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($r->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
