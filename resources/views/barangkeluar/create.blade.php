@extends('layouts.app')

@section('content')
    @php
        $cabang = request()->route('cabang'); // ambil parameter dari route
    @endphp
    <div class="container">
        <h2 class="mb-4">Tambah Barang Keluar - {{ ucfirst($cabang) }}</h2>

        <form action="{{ route('barangkeluar.store', ['cabang' => $cabang]) }}" method="POST">
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

            {{-- Harga (bisa pilih atau ketik) --}}
            <div class="mb-3">
                <label class="form-label" for="hrg_input">Harga</label>
                <div class="input-group">
                    {{-- input manual --}}
                    <input type="number" id="hrg_input" class="form-control" inputmode="numeric" step="1"
                        min="0" placeholder="Atau ketik harga manual">

                    {{-- select untuk saran harga --}}
                    <select id="hrg_select" class="form-select">
                        <option value="">Pilih harga</option>
                    </select>

                </div>

                {{-- field yang dikirim ke server --}}
                <input type="hidden" name="hrg" id="hrg">
                <div class="form-text">Pilih salah satu harga satu di kanan, atau ketik manual di kiri.</div>
            </div>

            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('barangkeluar.index', ['cabang' => $cabang]) }}" class="btn btn-secondary">Batal</a>
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

                // isi field yang sudah ada
                $('#idbarang').val(id);
                $('#barangText').val(items);
                $('#grup').val(grup);
                $('#merk').val(merk);

                // ==== hanya bagian ini yang beda: isi select saran + kosongkan input ====
                const $sel = $('#hrg_select');
                $sel.empty().append('<option value="">Pilih harga</option>');
                [
                    ['List', hrglist],
                    ['Agen', hrgagen],
                    ['Ecer', hrgecer]
                ]
                .filter(x => x[1] !== undefined && x[1] !== null && x[1] !== '' && Number(x[1]) >= 0)
                    .forEach(([label, val]) => {
                        $sel.append(
                            `<option value="${val}">${label}: Rp ${Number(val).toLocaleString('id-ID')}</option>`
                        );
                    });

                // reset input & hidden
                $('#hrg_input').val('');
                $('#hrg').val('');

                // tutup modal (Bootstrap 5)
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalBarang'));
                modal.hide();
            });

            // Sinkronisasi pilih → hidden + isi input
            $('#hrg_select').on('change', function() {
                const val = $(this).val();
                $('#hrg_input').val(val);
                $('#hrg').val(val);
            });

            // Ketik manual → tulis ke hidden & kosongkan pilihan select
            $('#hrg_input').on('input', function() {
                const val = this.value;
                $('#hrg').val(val);
                if (val !== '') {
                    $('#hrg_select').val('');
                }
            });

            // Fungsi format rupiah
            function formatRupiah(angka) {
                return angka.toLocaleString('id-ID');
            }
        });
    </script>
@endpush
