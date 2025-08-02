@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Edit Jasa</h2>

        <form action="{{ route('jasa.update', $jasa->ServCode) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Kode Jasa (ServCode)</label>
                <input type="text" name="ServCode" class="form-control" value="{{ $jasa->ServCode }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Jasa</label>
                <input type="text" name="NameOfServ" class="form-control"
                    value="{{ old('NameOfServ', $jasa->NameOfServ) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Harga Jasa</label>
                <input type="number" name="ServPrice" class="form-control" value="{{ old('ServPrice', $jasa->ServPrice) }}"
                    required>
            </div>

            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            <a href="{{ route('jasa.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
@endsection
