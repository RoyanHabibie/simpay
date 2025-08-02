@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Dashboard Inventaris</h2>

        <div class="row">
            @foreach ($stats as $cabang => $data)
                <div class="col-md-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title text-primary text-capitalize">
                                <a href="{{ url("$cabang/barang") }}" class="text-decoration-none">
                                    {{ $cabang }}
                                </a>
                            </h5>
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item">Total Barang:
                                    <strong>{{ number_format($data['total_barang']) }}</strong>
                                </li>
                                <li class="list-group-item">Total Qty Stok:
                                    <strong>{{ number_format($data['total_qty']) }}</strong>
                                </li>
                                <li class="list-group-item">Total Nilai (Modal): <strong>Rp
                                        {{ number_format($data['total_nilai'], 0, ',', '.') }}</strong></li>
                                <li class="list-group-item text-danger">Barang Stok < Min: <strong>
                                        {{ $data['stok_kritis'] }}</strong></li>
                                <li class="list-group-item">Grup Unik: <strong>{{ $data['grup_unik'] }}</strong></li>
                                <li class="list-group-item">Merk Unik: <strong>{{ $data['merk_unik'] }}</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
