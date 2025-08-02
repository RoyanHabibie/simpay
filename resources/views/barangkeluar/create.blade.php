@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Tambah Barang Keluar</h2>

        <form action="{{ route('barangkeluar.store') }}" method="POST">
            @csrf

            {{-- Hidden ID barang --}}
            <input type="hidden" name="idbarang" id="idbarang">

            {{-- Barang Terpilih --}}
            <div class="mb-3">
                <label for="barangText" class="form-label">Barang</label>
                <div class="d-flex gap-2">
                    <input type="text" id="barangText" class="form-control" placeholder="Pilih barang" readonly>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                        data-bs-target="#modalBarang">Cari Barang</button>
                </div>
            </div>

            {{-- Grup --}}
            <div class="mb-3">
                <label for="grup" class="form-label">Grup</label>
                <input type="text" id="grup" class="form-control" readonly>
            </div>

            {{-- Merk --}}
            <div class="mb-3">
                <label for="merk" class="form-label">Merk</label>
                <input type="text" id="merk" class="form-control" readonly>
            </div>

            {{-- Tanggal --}}
            <div class="mb-3">
                <label for="tgl" class="form-label">Tanggal</label>
                <input type="date" id="tgl" name="tgl" class="form-control" value="{{ date('Y-m-d') }}">
            </div>

            {{-- Jumlah --}}
            <div class="mb-3">
                <label for="qty" class="form-label">Jumlah (Qty)</label>
                <input type="number" id="qty" name="qty" class="form-control">
            </div>

            {{-- Harga Pilihan --}}
            <div class="mb-3">
                <label for="hrg" class="form-label">Harga</label>
                <select name="hrg" id="hrg" class="form-select">
                    <option value="">Pilih harga</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('barangkeluar.index') }}" class="btn btn-secondary">Batal</a>
        </form>

        {{-- Modal Barang --}}
        <div class="modal fade" id="modalBarang" tabindex="-1" aria-labelledby="modalBarangLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content shadow">
                    <div class="modal-header">
                        <h5 class="modal-title">Pilih Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tabelBarang">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Items</th>
                                        <th>Grup</th>
                                        <th>Merk</th>
                                        <th>Qty</th>
                                        <th>List</th>
                                        <th>Agen</th>
                                        <th>Ecer</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($barang as $b)
                                        <tr>
                                            <td>{{ $b->items }}</td>
                                            <td>{{ $b->grup }}</td>
                                            <td>{{ $b->merk }}</td>
                                            <td>{{ $b->qty }}</td>
                                            <td>Rp {{ number_format($b->hrglist, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($b->hrgagen, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($b->hrgecer, 0, ',', '.') }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary pilih-barang"
                                                    data-id="{{ $b->id }}" data-items="{{ $b->items }}"
                                                    data-grup="{{ $b->grup }}" data-merk="{{ $b->merk }}"
                                                    data-hrglist="{{ $b->hrglist }}" data-hrgagen="{{ $b->hrgagen }}"
                                                    data-hrgecer="{{ $b->hrgecer }}">
                                                    Pilih
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#tabelBarang').DataTable({
                pageLength: 10,
                ordering: false,
                info: false,
                lengthChange: false,
                language: {
                    search: "Cari:",
                    zeroRecords: "Data tidak ditemukan",
                    paginate: {
                        previous: "←",
                        next: "→"
                    }
                }
            });

            // Event untuk tombol "Pilih"
            $(document).on('click', '.pilih-barang', function() {
                const id = $(this).data('id');
                const items = $(this).data('items');
                const grup = $(this).data('grup');
                const merk = $(this).data('merk');
                const hrglist = $(this).data('hrglist');
                const hrgagen = $(this).data('hrgagen');
                const hrgecer = $(this).data('hrgecer');

                // Isi field
                $('#idbarang').val(id);
                $('#barangText').val(items);
                $('#grup').val(grup);
                $('#merk').val(merk);

                // Isi select harga
                const hargaSelect = $('#hrg');
                hargaSelect.empty();
                hargaSelect.append(`<option value="">Pilih harga</option>`);
                hargaSelect.append(
                `<option value="${hrglist}">List - Rp ${formatRupiah(hrglist)}</option>`);
                hargaSelect.append(
                `<option value="${hrgagen}">Agen - Rp ${formatRupiah(hrgagen)}</option>`);
                hargaSelect.append(
                `<option value="${hrgecer}">Ecer - Rp ${formatRupiah(hrgecer)}</option>`);

                // Tutup modal (pakai Bootstrap 5)
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalBarang'));
                modal.hide();
            });

            // Fungsi format rupiah
            function formatRupiah(angka) {
                return angka.toLocaleString('id-ID');
            }
        });
    </script>
@endpush
