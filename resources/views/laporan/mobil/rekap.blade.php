@extends('layouts.app')
@section('content')
    <div class="container">
        <h3 class="mb-3">Rekap Transaksi — Mobil (Jeret)</h3>

        <form class="row g-2 mb-3">
            <div class="col-auto">
                <input type="date" name="awal" value="{{ $awal }}" class="form-control">
            </div>
            <div class="col-auto">
                <input type="date" name="akhir" value="{{ $akhir }}" class="form-control">
            </div>
            <div class="col-auto">
                <select name="status" class="form-select">
                    <option value="semua" {{ $status === 'semua' ? 'selected' : '' }}>Semua</option>
                    <option value="tunai" {{ $status === 'tunai' ? 'selected' : '' }}>Tunai</option>
                    <option value="kredit" {{ $status === 'kredit' ? 'selected' : '' }}>Kredit</option>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-primary">Tampilkan</button></div>
            <div class="col-auto ms-auto">
                <a href="{{ route('laporan.mobil.rekap.pdf', request()->all()) }}" class="btn btn-outline-danger">PDF</a>
                <a href="{{ route('laporan.mobil.rekap.excel', request()->all()) }}"
                    class="btn btn-outline-success">Excel</a>
            </div>
        </form>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Jumlah Hari</div>
                        <div class="fs-4">{{ $ringkas['hari'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Total Transaksi</div>
                        <div class="fs-4">{{ number_format($ringkas['totalTrans']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Total Barang</div>
                        <div class="fs-4">Rp {{ number_format($ringkas['totalBarang'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted">Total Jasa</div>
                        <div class="fs-4">Rp {{ number_format($ringkas['totalJasa'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>Rekap Harian</span>
                <small class="text-muted">Periode: {{ $awal }} s/d {{ $akhir }}</small>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-end">Jumlah Transaksi</th>
                            <th class="text-end">Total Barang</th>
                            <th class="text-end">Total Jasa</th>
                            <th class="text-end">Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $r)
                            <tr>
                                <td class="text-nowrap">{{ $r->tgl }}</td>
                                <td class="text-end">{{ number_format($r->totalTrans) }}</td>
                                <td class="text-end">Rp {{ number_format($r->totalBarang, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($r->totalJasa, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($r->totalBarang + $r->totalJasa, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
