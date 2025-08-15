@extends('layouts.app')
@section('content')
    @php
        $bulanNama = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];
    @endphp

    <div class="container">
        <h1 class="mb-3">Laporan Pendapatan <small class="text-muted">(Mobil / Jeret)</small></h1>

        {{-- Filter: Bulan & Tahun --}}
        <form class="row g-3 align-items-end mb-4">
            <div class="col-sm-3">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-select">
                    @foreach ($bulanNama as $num => $nama)
                        <option value="{{ $num }}" {{ (int) $bulan === (int) $num ? 'selected' : '' }}>
                            {{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-select">
                    @for ($y = now()->year - 5; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ (int) $tahun === (int) $y ? 'selected' : '' }}>
                            {{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-sm-3 d-flex">
                <button class="btn btn-primary align-self-end">Tampilkan</button>
            </div>
            <div class="col-sm-3 d-flex justify-content-end gap-2">
                <a href="{{ route('laporan.mobil.pendapatan.pdf', request()->all()) }}"
                    class="btn btn-outline-danger">PDF</a>
                <a href="{{ route('laporan.mobil.pendapatan.excel', request()->all()) }}"
                    class="btn btn-outline-success">Excel</a>
            </div>
        </form>

        {{-- Ringkasan --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted">Pendapatan</div>
                        <div class="fs-3 fw-semibold">Rp {{ number_format($pendapatanSubtotal, 0, ',', '.') }}</div>
                        <div class="small text-muted">Periode: {{ $awal }} s/d {{ $akhir }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted">Pengeluaran</div>
                        <div class="fs-3 fw-semibold">Rp {{ number_format($pengeluaranSubtotal, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted">Total</div>
                        <div class="fs-3 fw-semibold">Rp {{ number_format($total, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel gaya income statement --}}
        <div class="card">
            <div class="card-header fw-semibold">Rekap Transaksi</div>
            <div class="card-body">

                <h5 class="fw-bold">Pendapatan</h5>
                <div class="row mb-1">
                    <div class="col">Barang</div>
                    <div class="col-auto fw-semibold">Rp {{ number_format($barang, 0, ',', '.') }}</div>
                </div>
                <div class="row mb-1">
                    <div class="col">Jasa</div>
                    <div class="col-auto fw-semibold">Rp {{ number_format($jasa, 0, ',', '.') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col">Steam</div>
                    <div class="col-auto fw-semibold">Rp {{ number_format($steam, 0, ',', '.') }}</div>
                </div>
                <hr class="my-2">
                <div class="row mb-3">
                    <div class="col fw-bold">Subtotal</div>
                    <div class="col-auto fw-bold">Rp {{ number_format($pendapatanSubtotal, 0, ',', '.') }}</div>
                </div>

                <h5 class="fw-bold mt-4">Pengeluaran</h5>
                <div class="row mb-1">
                    <div class="col">Operasional</div>
                    <div class="col-auto fw-semibold">Rp {{ number_format($ops, 0, ',', '.') }}</div>
                </div>
                <div class="row mb-1">
                    <div class="col">Non Operasional</div>
                    <div class="col-auto fw-semibold">Rp {{ number_format($nonops, 0, ',', '.') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col">Gaji</div>
                    <div class="col-auto fw-semibold">Rp {{ number_format($gaji, 0, ',', '.') }}</div>
                </div>
                <hr class="my-2">
                <div class="row">
                    <div class="col fw-bold">Subtotal</div>
                    <div class="col-auto fw-bold">Rp {{ number_format($pengeluaranSubtotal, 0, ',', '.') }}</div>
                </div>

                <hr class="my-3">
                <div class="row">
                    <div class="col fw-bold fs-5">Total</div>
                    <div class="col-auto fw-bold fs-5">Rp {{ number_format($total, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
