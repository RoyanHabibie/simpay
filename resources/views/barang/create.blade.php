@extends('layouts.app')

@section('content')
    @php $cabang = request()->route('cabang'); @endphp
    <div class="container">
        <h2 class="mb-4">Tambah Barang - {{ ucfirst($cabang) }}</h2>

        <form action="{{ route('barang.store', $cabang) }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nama Barang (Items)</label>
                <input type="text" name="items" class="form-control" value="{{ old('items') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Grup</label>
                <input type="text" name="grup" class="form-control" value="{{ old('grup') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Merk</label>
                <input type="text" name="merk" class="form-control" value="{{ old('merk') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Qty</label>
                <input type="number" name="qty" class="form-control" value="{{ old('qty', 0) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Min Stok</label>
                <input type="number" name="min" class="form-control" value="{{ old('min', 0) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Lokasi</label>
                <input type="text" name="lokasi" class="form-control" value="{{ old('lokasi') }}">
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Harga List</label>
                    <input type="number" name="hrglist" id="hrglist" class="form-control"
                        value="{{ old('hrglist', 0) }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Diskon Modal (%)</label>
                    <input type="number" id="discmodal" class="form-control" value="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga Modal</label>
                    <input type="number" name="hrgmodal" id="hrgmodal" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Diskon Agen (%)</label>
                    <input type="number" id="discagen" class="form-control" value="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga Agen</label>
                    <input type="number" name="hrgagen" id="hrgagen" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Diskon Ecer (%)</label>
                    <input type="number" id="discecer" class="form-control" value="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga Ecer</label>
                    <input type="number" name="hrgecer" id="hrgecer" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('barang.index', $cabang) }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function calculateHarga(target, discInput) {
            const hrgList = parseFloat(document.getElementById('hrglist').value) || 0;
            const disc = parseFloat(document.getElementById(discInput).value) || 0;
            const hasil = hrgList - (hrgList * (disc / 100));
            document.getElementById(target).value = hasil.toFixed(0);
        }

        ['hrglist', 'discmodal', 'discagen', 'discecer'].forEach(id => {
            document.getElementById(id).addEventListener('input', () => {
                calculateHarga('hrgmodal', 'discmodal');
                calculateHarga('hrgagen', 'discagen');
                calculateHarga('hrgecer', 'discecer');
            });
        });
    </script>
@endpush
