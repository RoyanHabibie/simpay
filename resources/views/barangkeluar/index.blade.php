@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Data Barang Keluar</h2>
        <a href="{{ route('barangkeluar.create') }}" class="btn btn-primary">+ Tambah Transaksi</a>
    </div>

    <!-- Pencarian & Filter -->
    <form method="GET" class="row g-2 mb-3 align-items-center">
        <div class="col-md-3">
            <input type="text" name="keyword" class="form-control" placeholder="Cari berdasarkan item"
                value="{{ request('keyword') }}">
        </div>

        <div class="col-md-3 d-flex gap-2">
            <input type="date" name="tgl" class="form-control"
                value="{{ request()->has('tgl') ? request('tgl') : date('Y-m-d') }}">

            {{-- Tombol reset filter tanggal --}}
            @if (request()->has('tgl'))
                <a href="{{ route('barangkeluar.index', array_merge(request()->except('tgl'))) }}"
                    class="btn btn-outline-danger" title="Reset tanggal">✕</a>
            @endif
        </div>

        <div class="col-md-2">
            <select name="per_page" class="form-select" onchange="this.form.submit()">
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-secondary w-100" type="submit">Filter</button>
        </div>
    </form>

    @if ($barangkeluar->count())
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div><strong>Total Qty:</strong> {{ $totalQty }}</div>
            <div><strong>Total Nilai:</strong> Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
        </div>
    @endif

    <!-- Tabel -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Barang</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barangkeluar as $index => $item)
                    <tr>
                        <td>{{ ($barangkeluar->currentPage() - 1) * $barangkeluar->perPage() + $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tgl)->format('d-m-Y') }}</td>
                        <td>{{ $item->barang->items ?? 'Barang tidak ditemukan' }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>Rp {{ number_format($item->hrg, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->qty * $item->hrg, 0, ',', '.') }}</td>
                        <td>
                            {{-- <a href="{{ route('barangkeluar.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a> --}}
                            <form action="{{ route('barangkeluar.destroy', $item->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-2">
            {{ $barangkeluar->withQueryString()->links() }}
        </div>
    </div>
@endsection
