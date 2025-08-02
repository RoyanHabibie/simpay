@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Laporan Stok Barang</h5>
            <small>Tanggal Cetak: {{ now()->format('d-m-Y H:i') }}</small>
        </div>

        <div class="mb-3 d-flex gap-2">
            <button class="btn btn-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Cetak / Simpan PDF
            </button>
            <a href="{{ route('barang.export.excel', [$cabang ?? 'pusat'] + request()->query()) }}"
                class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>

        <!-- Filter -->
        <form method="GET" class="row g-2 mb-3">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <input type="text" name="grup" class="form-control" placeholder="Filter Grup"
                        value="{{ request('grup') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="merk" class="form-control" placeholder="Filter Merk"
                        value="{{ request('merk') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="lokasi" class="form-control" placeholder="Filter Lokasi"
                        value="{{ request('lokasi') }}">
                </div>
                <div class="col-md-3 d-flex align-items-center gap-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="stok_kritis" value="1" id="stok_kritis"
                            {{ request('stok_kritis') ? 'checked' : '' }}>
                        <label class="form-check-label" for="stok_kritis">
                            <small>Tampilkan Stok < Min</small>
                        </label>
                    </div>
                    <button class="btn btn-primary btn-sm ms-auto" type="submit">Terapkan</button>
                </div>
            </div>
        </form>

        <!-- Tabel -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-dark">
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
                    @forelse ($barang as $index => $b)
                        <tr class="{{ $b->qty < $b->min ? 'table-danger' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $b->items }}</td>
                            <td>{{ $b->grup }}</td>
                            <td>{{ $b->merk }}</td>
                            <td>{{ $b->qty }}</td>
                            <td>{{ $b->min }}</td>
                            <td>{{ $b->lokasi }}</td>
                            <td>Rp {{ number_format($b->hrglist, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($b->hrgmodal, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($b->hrgagen, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($b->hrgecer, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        @media print {

            nav,
            .btn,
            form,
            footer {
                display: none !important;
            }

            table {
                font-size: 10px;
                width: 100%;
                border-collapse: collapse;
            }

            thead {
                display: table-header-group;
            }

            th,
            td {
                border: 1px solid #000 !important;
                padding: 4px;
            }

            body {
                margin: 0;
            }
        }
    </style>
@endpush
