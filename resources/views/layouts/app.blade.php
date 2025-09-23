<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SIMPAY') }}</title>

    <!-- Bootstrap 5 via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    @stack('styles')
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">SIMPAY</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            @php
                $currentCabang = request()->route('cabang');
            @endphp
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}">Dashboard</a>
                    </li>

                    <!-- Dropdown Barang -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ Str::startsWith(Route::currentRouteName(), 'barang.') ? 'active' : '' }}"
                            href="#" id="barangDropdown" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            Barang
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="barangDropdown">
                            <li><a class="dropdown-item {{ $currentCabang === 'pusat' ? 'active' : '' }}"
                                    href="{{ route('barang.index', 'pusat') }}">Barang Pusat</a></li>
                            <li><a class="dropdown-item {{ $currentCabang === 'jeret' ? 'active' : '' }}"
                                    href="{{ route('barang.index', 'jeret') }}">Barang Jeret</a></li>
                            <li><a class="dropdown-item {{ $currentCabang === 'jayanti_timur' ? 'active' : '' }}"
                                    href="{{ route('barang.index', 'jayanti_timur') }}">Barang Jayanti Timur</a></li>
                            <li><a class="dropdown-item {{ $currentCabang === 'ruko' ? 'active' : '' }}"
                                    href="{{ route('barang.index', 'ruko') }}">Barang Ruko</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('jasa.*') ? 'active' : '' }}"
                            href="{{ route('jasa.index') }}">Jasa</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('barangkeluar.*') ? 'active' : '' }}"
                            href="{{ route('barangkeluar.index') }}">Barang Keluar</a>
                    </li>

                    <!-- Dropdown Laporan -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('laporan.*') ? 'active' : '' }}"
                            href="#" data-bs-toggle="dropdown">
                            Laporan
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <!-- Motor (Pusat) -->
                            <li class="dropdown-header">Motor (Pusat)</li>
                            <li>
                                <a class="dropdown-item {{ request()->fullUrlIs('*lokasi=pusat*') ? 'active' : '' }}"
                                    href="{{ route('laporan.keluar', [
                                        'lokasi' => 'pusat',
                                        'awal' => now()->toDateString(),
                                        'akhir' => now()->toDateString(),
                                        'mode' => 'detail',
                                    ]) }}">
                                    Barang Keluar
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <!-- Mobil (Jeret) -->
                            <li class="dropdown-header">Mobil (Jeret)</li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('laporan.mobil.rekap') ? 'active' : '' }}"
                                    href="{{ route('laporan.mobil.rekap', [
                                        'awal' => now()->toDateString(),
                                        'akhir' => now()->toDateString(),
                                        'status' => 'semua',
                                    ]) }}">
                                    Rekap Transaksi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('laporan.mobil.pendapatan') ? 'active' : '' }}"
                                    href="{{ route('laporan.mobil.pendapatan', [
                                        'awal' => now()->toDateString(),
                                        'akhir' => now()->toDateString(),
                                        'status' => 'semua',
                                    ]) }}">
                                    Pendapatan
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->fullUrlIs('*lokasi=jeret*') ? 'active' : '' }}"
                                    href="{{ route('laporan.keluar', [
                                        'lokasi' => 'jeret',
                                        'awal' => now()->toDateString(),
                                        'akhir' => now()->toDateString(),
                                        'mode' => 'detail',
                                    ]) }}">
                                    Barang Keluar
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <!-- Jayanti Timur -->
                            <li class="dropdown-header">Jayanti Timur</li>
                            <li>
                                <a class="dropdown-item {{ request()->fullUrlIs('*lokasi=jt*') ? 'active' : '' }}"
                                    href="{{ route('laporan.keluar', [
                                        'lokasi' => 'jt',
                                        'awal' => now()->toDateString(),
                                        'akhir' => now()->toDateString(),
                                        'mode' => 'detail',
                                    ]) }}">
                                    Barang Keluar
                                </a>
                            </li>

                            <!-- Ruko -->
                            <li class="dropdown-header">Ruko</li>
                            <li>
                                <a class="dropdown-item {{ request()->fullUrlIs('*lokasi=ruko*') ? 'active' : '' }}"
                                    href="{{ route('laporan.keluar', [
                                        'lokasi' => 'ruko',
                                        'awal' => now()->toDateString(),
                                        'akhir' => now()->toDateString(),
                                        'mode' => 'detail',
                                    ]) }}">
                                    Barang Keluar
                                </a>
                            </li>
                        </ul>
                    </li>

                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                {{-- <li><a class="dropdown-item" href="#">Profil</a></li> --}}
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <small>&copy; {{ date('Y') }} SIMPAY. All rights reserved.</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    @stack('scripts')
</body>

</html>
