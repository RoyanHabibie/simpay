@extends('layouts.app')

@section('content')
    @php $cabang = request()->route('cabang'); @endphp
    <div class="container">
        <h2 class="mb-4">Edit Barang - {{ ucfirst($cabang) }}</h2>

        <form action="{{ route('barang.update', [$cabang, $barang->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nama Barang (Items)</label>
                <input type="text" name="items" class="form-control" value="{{ old('items', $barang->items) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Grup</label>
                <input type="text" name="grup" class="form-control" value="{{ old('grup', $barang->grup) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Merk</label>
                <input type="text" name="merk" class="form-control" value="{{ old('merk', $barang->merk) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Qty</label>
                <input type="number" name="qty" class="form-control" value="{{ old('qty', $barang->qty) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Min Stok</label>
                <input type="number" name="min" class="form-control" value="{{ old('min', $barang->min) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Lokasi</label>
                <input type="text" name="lokasi" class="form-control" value="{{ old('lokasi', $barang->lokasi) }}">
            </div>

            @php
                $list = (float) ($barang->hrglist ?? 0);

                $discFrom = function ($price) use ($list) {
                    $p = (float) ($price ?? 0);
                    if ($list <= 0) {
                        return 0;
                    }
                    // diskon(+) kalau price < list, markup(-) kalau price > list
                    return round((1 - $p / $list) * 100, 2);
                };

                $disc_modal_init = old('discmodal', $discFrom($barang->hrgmodal));
                $disc_agen_init = old('discagen', $discFrom($barang->hrgagen));
                $disc_ecer_init = old('discecer', $discFrom($barang->hrgecer));
            @endphp

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Harga List</label>
                    <input type="number" name="hrglist" id="hrglist" class="form-control"
                        value="{{ old('hrglist', $barang->hrglist) }}">
                </div>
            </div>

            {{-- MODAL --}}
            <div class="row mb-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Diskon Modal (%)</label>
                    <input type="number" id="discmodal" class="form-control" value="{{ $disc_modal_init }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Harga Modal</label>
                    <input type="number" name="hrgmodal" id="hrgmodal" class="form-control"
                        value="{{ old('hrgmodal', $barang->hrgmodal) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Keterangan Diskon</label>
                    <div class="form-control-plaintext" id="ketmodal"></div>
                </div>
            </div>

            {{-- AGEN --}}
            <div class="row mb-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Diskon Agen (%)</label>
                    <input type="number" id="discagen" class="form-control" value="{{ $disc_agen_init }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Harga Agen</label>
                    <input type="number" name="hrgagen" id="hrgagen" class="form-control"
                        value="{{ old('hrgagen', $barang->hrgagen) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Keterangan Diskon</label>
                    <div class="form-control-plaintext" id="ketagen"></div>
                </div>
            </div>

            {{-- ECER --}}
            <div class="row mb-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Diskon Ecer (%)</label>
                    <input type="number" id="discecer" class="form-control" value="{{ $disc_ecer_init }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Harga Ecer</label>
                    <input type="number" name="hrgecer" id="hrgecer" class="form-control"
                        value="{{ old('hrgecer', $barang->hrgecer) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Keterangan Diskon</label>
                    <div class="form-control-plaintext" id="ketecer"></div>
                </div>
            </div>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="{{ route('barang.index', $cabang) }}" class="btn btn-secondary">Batal</a>
    </form>
    </div>
@endsection

@push('scripts')
    <script>
        function formatKet(disc) {
            if (disc < 0) return `Markup ${Math.abs(disc)}%`;
            return `Diskon ${disc}%`;
        }

        function calcFromList(hrgList, disc) {
            const f = 1 - (disc / 100);
            return hrgList * f;
        }

        function recalcAll() {
            const hrgList = parseFloat(document.getElementById('hrglist').value) || 0;

            const pairs = [{
                    discId: 'discmodal',
                    priceId: 'hrgmodal',
                    ketId: 'ketmodal'
                },
                {
                    discId: 'discagen',
                    priceId: 'hrgagen',
                    ketId: 'ketagen'
                },
                {
                    discId: 'discecer',
                    priceId: 'hrgecer',
                    ketId: 'ketecer'
                },
            ];

            pairs.forEach(p => {
                const disc = parseFloat(document.getElementById(p.discId).value) || 0;
                const hasil = calcFromList(hrgList, disc);

                document.getElementById(p.priceId).value = Math.round(hasil);
                document.getElementById(p.ketId).innerText = formatKet(disc);
            });
        }

        ['hrglist', 'discmodal', 'discagen', 'discecer'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', recalcAll);
        });

        recalcAll();
    </script>
@endpush
