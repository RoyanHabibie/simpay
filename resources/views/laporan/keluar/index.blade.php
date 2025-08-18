@extends('layouts.app')

@section('content')
    <div class="container">
        @php
            $lokasiLabel =
                [
                    'pusat' => 'Pusat',
                    'jt' => 'Jayanti Timur',
                    'jeret' => 'Mobil - Jeret',
                ][$lokasi] ?? ucfirst($lokasi);
        @endphp

        <h3 class="mb-3">
            Laporan Barang Keluar
            <small class="text-muted">({{ $lokasiLabel }})</small>
        </h3>

        <form class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label">Tanggal awal</label>
                <input type="date" name="awal" value="{{ $awal }}" class="form-control">
            </div>
            <div class="col-auto">
                <label class="form-label">Tanggal akhir</label>
                <input type="date" name="akhir" value="{{ $akhir }}" class="form-control">
            </div>
            <div class="col-auto">
                <label class="form-label">Lokasi</label>
                <select name="lokasi" class="form-select">
                    <option value="pusat" {{ $lokasi === 'pusat' ? 'selected' : '' }}>Pusat (Motor)</option>
                    <option value="jeret" {{ $lokasi === 'jeret' ? 'selected' : '' }}>Jeret (Mobil)</option>
                    <option value="jt" {{ $lokasi === 'jt' ? 'selected' : '' }}>Jayanti Timur (Motor)</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label">Cari (items/grup/merk)</label>
                <input type="text" name="cari" value="{{ $cari }}" class="form-control"
                    placeholder="oli, pelumas, NGK...">
            </div>
            <div class="col-auto">
                <label class="form-label">Mode</label>
                <select name="mode" class="form-select">
                    <option value="detail" {{ $mode === 'detail' ? 'selected' : '' }}>Detail</option>
                    <option value="rekap" {{ $mode === 'rekap' ? 'selected' : '' }}>Rekap</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary">Tampilkan</button>
            </div>
            <div class="col-auto ms-auto">
                <a class="btn btn-outline-danger" href="{{ route('laporan.keluar.pdf', request()->all()) }}">PDF</a>
                <a class="btn btn-outline-success" href="{{ route('laporan.keluar.excel', request()->all()) }}">Excel</a>
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
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
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
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Barang</th>
                                <th>Grup</th>
                                <th>Merk</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga</th>
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
