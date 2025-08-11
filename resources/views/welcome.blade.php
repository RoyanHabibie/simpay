<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Pay Motor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen relative">
    <div class="absolute inset-0 bg-cover bg-center opacity-10" style="background-image: url('/bg.jpg');"></div>

    <div class="z-10 text-center px-4">
        <img src="{{ asset('logo.png') }}" alt="Logo" class="mx-auto mb-6 w-40 h-40">

        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Selamat Datang di Pay Motor</h1>
        <p class="text-gray-600 text-lg mb-8">Sistem Manajemen Transaksi dan Inventaris</p>

        <a href="/login" class="bg-blue-700 text-white px-6 py-3 rounded-lg text-lg hover:bg-blue-800 transition">
            Masuk Aplikasi
        </a>
    </div>
</body>

</html>
