@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Data Jasa</h2>
        <a href="{{ route('jasa.create') }}" class="btn btn-primary">+ Tambah Jasa</a>
    </div>

    <!-- Form Pencarian -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="keyword" class="form-control" placeholder="Cari nama jasa"
                value="{{ request('keyword') }}">
        </div>
        <div class="col-md-3">
            <select name="per_page" class="form-select" onchange="this.form.submit()">
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per halaman</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per halaman</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per halaman</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary">Terapkan</button>
        </div>
    </form>

    <!-- Tabel -->
    <div class="table-responsive">
        <p class="text-muted">Menampilkan {{ $jasa->firstItem() }} – {{ $jasa->lastItem() }} dari {{ $jasa->total() }} data
        </p>
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Kode Jasa</th>
                    <th>Nama Jasa</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($jasa as $index => $j)
                    <tr>
                        <td>{{ ($jasa->currentPage() - 1) * $jasa->perPage() + $index + 1 }}</td>
                        <td>{{ $j->ServCode }}</td>
                        <td>{{ $j->NameOfServ }}</td>
                        <td>Rp {{ number_format($j->ServPrice, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('jasa.edit', $j->ServCode) }}" class="btn btn-sm btn-warning"
                                data-bs-toggle="tooltip" title="Edit Jasa">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="{{ route('jasa.destroy', $j->ServCode) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Yakin ingin hapus?')"
                                    class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus Jasa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data jasa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Paginasi -->
        <div>
            {{ $jasa->links() }}
        </div>
    </div>
@endsection
