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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('*barang*') ? 'active' : '' }}"
                            href="#" id="barangDropdown" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            Barang
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="barangDropdown">
                            <li>
                                <a class="dropdown-item {{ $currentCabang === 'pusat' ? 'active' : '' }}"
                                    href="{{ route('barang.index', 'pusat') }}">Barang Pusat</a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $currentCabang === 'jeret' ? 'active' : '' }}"
                                    href="{{ route('barang.index', 'jeret') }}">Barang Jeret</a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $currentCabang === 'jayanti timur' ? 'active' : '' }}"
                                    href="{{ route('barang.index', 'jayanti timur') }}">Barang Jayanti Timur</a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('jasa.*') ? 'active' : '' }}"
                            href="{{ route('jasa.index') }}">Jasa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('keluar.*') ? 'active' : '' }}"
                            href="{{ route('barangkeluar.index') }}">Barang Keluar</a>
                    </li>
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
