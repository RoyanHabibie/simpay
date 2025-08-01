@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Data Barang</h2>
        <a href="{{ route('barang.report') }}" class="btn btn-white">Lihat Laporan</a>
        <a href="{{ route('barang.create') }}" class="btn btn-primary">+ Tambah Barang</a>
    </div>

    <!-- Form Pencarian -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="keyword" class="form-control" placeholder="Cari items / grup / merk"
                value="{{ request('keyword') }}">
        </div>
        <div class="col-md-3">
            <select name="per_page" class="form-select" onchange="this.form.submit()">
                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 per halaman</option>
                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 per halaman</option>
                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 per halaman</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary">Cari</button>
        </div>
    </form>

    <!-- Tabel -->
    <div class="table-responsive">
        <p class="text-muted">Menampilkan {{ $barang->firstItem() }} – {{ $barang->lastItem() }} dari {{ $barang->total() }}
            data, Total Qty: <strong>{{ $totalQty }}</strong></p>
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Items</th>
                    <th>Grup</th>
                    <th>Merk</th>
                    <th>Lokasi</th>
                    <th>Qty</th>
                    <th>Harga List</th>
                    <th>Harga Modal</th>
                    <th>Harga Agen</th>
                    <th>Harga Ecer</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($barang as $index => $b)
                    <tr>
                        <td>{{ ($barang->currentPage() - 1) * $barang->perPage() + $index + 1 }}</td>
                        <td>{{ $b->items }}</td>
                        <td>{{ $b->grup }}</td>
                        <td>{{ $b->merk }}</td>
                        <td>{{ $b->lokasi }}</td>
                        <td>{{ $b->qty }}</td>
                        <td>Rp {{ number_format($b->hrglist, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($b->hrgmodal, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($b->hrgagen, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($b->hrgecer, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('barang.edit', $b->id) }}" class="btn btn-sm btn-outline-warning"
                                data-bs-toggle="tooltip" title="Edit Barang">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <form action="{{ route('barang.destroy', $b->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Yakin ingin hapus?')"
                                    class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Hapus Barang">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Paginasi -->
        <div class="align-items-center">
            <div>
                {{ $barang->links() }}
            </div>
        </div>
    </div>
@endsection
