@extends('layouts.app')

@section('content')
    @php
        $cabang = request()->route('cabang'); // ambil parameter dari route
    @endphp
    @if (session('error'))
        <div class="alert alert-warning">{{ session('error') }}</div>
    @endif
    @if (($barang->total() ?? 0) > 1000)
        <div class="alert alert-info">
            Dataset besar terdeteksi ({{ number_format($barang->total()) }} baris).
            Disarankan gunakan <strong>Export Excel</strong> agar lebih cepat & stabil.
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Data Barang - {{ ucfirst($cabang) }}</h2>
        <div class="btn-group">
            <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#bulkPriceModal">
                Update Harga Massal
            </button>
            <a href="{{ route('barang.export.pdf', array_merge(['cabang' => $cabang], request()->only('keyword'))) }}"
                class="btn btn-outline-danger">Export PDF</a>
            <a href="{{ route('barang.export.excel', array_merge(['cabang' => $cabang], request()->only('keyword'))) }}"
                class="btn btn-outline-success">Export Excel</a>
            <a href="{{ route('barang.create', $cabang) }}" class="btn btn-primary">+ Tambah Barang</a>
        </div>
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
        <p class="text-muted">Menampilkan {{ $barang->firstItem() }} – {{ $barang->lastItem() }} dari
            {{ $barang->total() }}
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
                            <a href="{{ route('barang.edit', [$cabang, $b->id]) }}" class="btn btn-sm btn-warning"
                                data-bs-toggle="tooltip" title="Edit Barang">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="{{ route('barang.destroy', [$cabang, $b->id]) }}" method="POST"
                                class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Yakin ingin hapus?')"
                                    class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus Barang">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Paginasi -->
        <div>
            {{ $barang->links() }}
        </div>
    </div>

    {{-- Modal Update Harga Massal --}}
    <div class="modal fade" id="bulkPriceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('barang.bulk.update', $cabang) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Update Harga Massal ({{ ucfirst($cabang) }})</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-2">
                        <label class="form-label">Grup (opsional)</label>
                        <select name="grup" id="bulk-grup" class="form-select">
                            <option value="">— Semua Grup —</option>
                            @foreach ($grupList as $g)
                                <option value="{{ $g }}">{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Merk (opsional)</label>
                        <select name="merk" id="bulk-merk" class="form-select">
                            <option value="">— Semua Merk —</option>
                            @foreach ($merkList as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Disc Modal (%)</label>
                            <input type="number" step="0.01" name="disc_modal" class="form-control"
                                placeholder="mis. -10 atau 5">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Disc Agen (%)</label>
                            <input type="number" step="0.01" name="disc_agen" class="form-control"
                                placeholder="mis. -5 atau 3">
                        </div>
                    </div>

                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <label class="form-label">Pembulatan ke kelipatan</label>
                            <select name="round_step" class="form-select">
                                <option value="0">Tanpa kelipatan</option>
                                <option value="1">Ke integer</option>
                                <option value="50">50</option>
                                <option value="100" selected>100</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Mode Pembulatan</label>
                            <select name="round_mode" class="form-select">
                                <option value="round" selected>Terdekat</option>
                                <option value="ceil">Ke atas</option>
                                <option value="floor">Ke bawah</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="chkKonfirmasi"
                            name="konfirmasi" required>
                        <label class="form-check-label" for="chkKonfirmasi">
                            Saya yakin menerapkan perubahan ini pada data terpilih.
                        </label>
                    </div>

                    <div class="small text-muted mt-2">
                        Catatan: Filter kosong berarti semua barang (cabang ini). Perubahan tidak dapat di-undo.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selGrup = document.getElementById('bulk-grup');
            const selMerk = document.getElementById('bulk-merk');

            selGrup?.addEventListener('change', async () => {
                const grup = selGrup.value;
                const url = @json(route('barang.merk.list', $cabang));
                selMerk.innerHTML = '<option value="">Memuat…</option>';

                try {
                    const res = await fetch(url + (grup ? ('?grup=' + encodeURIComponent(grup)) : ''));
                    const data = await res.json();
                    selMerk.innerHTML = '<option value="">— Semua Merk —</option>';
                    data.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m;
                        opt.textContent = m;
                        selMerk.appendChild(opt);
                    });
                } catch (e) {
                    selMerk.innerHTML = '<option value="">— Semua Merk —</option>';
                    console.error(e);
                }
            });
        });
    </script>
@endpush
