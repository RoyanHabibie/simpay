@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Tambah Jasa</h2>

        <form action="{{ route('jasa.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Kode Jasa (ServCode)</label>
                <input type="text" name="ServCode" class="form-control" value="{{ old('ServCode') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Jasa</label>
                <input type="text" name="NameOfServ" class="form-control" value="{{ old('NameOfServ') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Harga Jasa</label>
                <input type="number" name="ServPrice" class="form-control" value="{{ old('ServPrice', 0) }}" required>
            </div>

            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('jasa.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
@endsection
