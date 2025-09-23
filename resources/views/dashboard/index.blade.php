@extends('layouts.app')

@section('content')
    @php
        use Illuminate\Support\Str;

        // Map nama cabang ke slug/parameter rute
        $lokasiMap = [
            'pusat' => 'pusat',
            'jeret' => 'mobil',
            'jayanti timur' => 'jayanti_timur',
            'ruko' => 'ruko',
        ];
    @endphp

    <div class="container">
        <h2 class="mb-4">Dashboard Inventaris</h2>

        {{-- Periode --}}
        <form class="row g-2 align-items-end mb-3" method="GET" action="{{ url()->current() }}">
            <div class="col-auto">
                <label class="form-label">Periode</label>
                <input type="date" name="awal" value="{{ $awal }}" class="form-control">
            </div>
            <div class="col-auto">
                <label class="form-label">&nbsp;</label>
                <input type="date" name="akhir" value="{{ $akhir }}" class="form-control">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary">Terapkan</button>
            </div>
            <div class="col-auto">
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        {{-- Kartu per cabang --}}
        <div class="row">
            @foreach ($stats as $cabang => $data)
                <div class="col-md-3">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title text-primary text-capitalize">
                                <a href="{{ url(($lokasiMap[$cabang] ?? 'pusat') . '/barang') }}"
                                    class="text-decoration-none">
                                    {{ $cabang }}
                                </a>
                            </h5>

                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item">Total Barang:
                                    <strong>{{ number_format((int) $data['total_barang']) }}</strong>
                                </li>
                                <li class="list-group-item">Total Qty Stok:
                                    <strong>{{ number_format((int) $data['total_qty']) }}</strong>
                                </li>
                                <li class="list-group-item">Total Nilai (Modal):
                                    <strong>Rp {{ number_format((float) $data['total_nilai'], 0, ',', '.') }}</strong>
                                </li>
                                <li class="list-group-item text-danger">Barang Stok &lt; Min:
                                    <strong>{{ number_format((int) $data['stok_kritis']) }}</strong>
                                </li>
                                <li class="list-group-item">Grup Unik:
                                    <strong>{{ number_format((int) $data['grup_unik']) }}</strong>
                                </li>
                                <li class="list-group-item">Merk Unik:
                                    <strong>{{ number_format((int) $data['merk_unik']) }}</strong>
                                </li>
                            </ul>

                            <hr>
                            <div class="small text-muted mb-1">
                                Pergerakan ({{ $awal }} s/d {{ $akhir }})
                            </div>

                            <div class="d-flex gap-3 flex-wrap">
                                <div>
                                    <span class="text-muted">Qty Keluar:</span>
                                    <strong>{{ number_format((int) ($kpi[$cabang]['qty'] ?? 0)) }}</strong>
                                </div>
                                <div>
                                    <span class="text-muted">Nilai:</span>
                                    <strong>Rp
                                        {{ number_format((float) ($kpi[$cabang]['nilai'] ?? 0), 0, ',', '.') }}</strong>
                                </div>
                                <div>
                                    <span class="text-muted">Item Aktif:</span>
                                    <strong>{{ number_format((int) ($kpi[$cabang]['aktif'] ?? 0)) }}</strong>
                                </div>
                                <div>
                                    <span class="text-muted">Stok &lt; Min:</span>
                                    <strong
                                        class="text-danger">{{ number_format((int) ($kpi[$cabang]['underMin'] ?? 0)) }}</strong>
                                </div>
                            </div>

                            {{-- <div class="mt-2">
                                <a href="{{ url(($lokasiMap[$cabang] ?? 'pusat') . '/barang?keyword=&per_page=50') }}"
                                    class="btn btn-sm btn-outline-primary">Lihat Barang</a>

                                @php $lok = $lokasiMap[$cabang] ?? 'pusat'; @endphp
                                <a href="{{ route('laporan.keluar', ['lokasi' => $lok, 'awal' => $awal, 'akhir' => $akhir, 'mode' => 'detail']) }}"
                                    class="btn btn-sm btn-outline-secondary">Laporan Keluar</a>
                            </div> --}}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <hr class="mt-4">
    <h5 class="mb-3">Tren 14–30 Hari Terakhir</h5>

    <div class="row">
        @foreach ($tren as $nama => $t)
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-capitalize">{{ $nama }}</strong>
                            <small class="text-muted">Qty keluar</small>
                        </div>
                        <canvas id="chart-{{ Str::slug($nama) }}" height="100"></canvas>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
    <script>
        @foreach ($tren as $nama => $t)
            new Chart(
                document.getElementById('chart-{{ Str::slug($nama) }}'), {
                    type: 'line',
                    data: {
                        labels: @json(($t['labels'] ?? collect())->values()),
                        datasets: [{
                            data: @json(($t['data'] ?? collect())->values()),
                            tension: 0.3,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        },
                        elements: {
                            point: {
                                radius: 0
                            }
                        }
                    }
                }
            );
        @endforeach
    </script>

    <div class="row mt-3">
        @foreach (['pusat', 'jeret', 'jayanti timur', 'ruko'] as $cab)
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header">
                        Top 5 Fast Movers — <span class="text-capitalize">{{ $cab }}</span>
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse(($fastMovers[$cab] ?? collect()) as $r)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $r->items }}</span>
                                <span><strong>{{ number_format((int) $r->qty) }}</strong></span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Belum ada pergerakan</li>
                        @endforelse
                    </ul>
                </div>

                <div class="card">
                    <div class="card-header">
                        Top 5 Stok Kritis — <span class="text-capitalize">{{ $cab }}</span>
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse(($kritis[$cab] ?? collect()) as $r)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $r->items }}</span>
                                <span class="text-danger">{{ (int) $r->qty }}/min {{ (int) $r->min }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aman 👍</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
@endsection
