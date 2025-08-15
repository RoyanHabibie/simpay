<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px
        }

        .h {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 6px
        }

        .r {
            text-align: right
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0
        }

        hr {
            border: 0;
            border-top: 1px solid #555;
            margin: 6px 0
        }
    </style>
</head>

<body>
    <div class="h">REKAP TRANSAKSI</div>
    <div>Periode: {{ $awal }} s/d {{ $akhir }} — Status: {{ ucfirst($status) }}</div>
    <br>

    <strong>Pendapatan</strong>
    <div class="row">
        <div>Barang</div>
        <div class="r">{{ number_format($barang, 0, ',', '.') }}</div>
    </div>
    <div class="row">
        <div>Jasa</div>
        <div class="r">{{ number_format($jasa, 0, ',', '.') }}</div>
    </div>
    <div class="row">
        <div>Steam</div>
        <div class="r">{{ number_format($steam, 0, ',', '.') }}</div>
    </div>
    <hr>
    <div class="row">
        <div><strong>Subtotal</strong></div>
        <div class="r"><strong>{{ number_format($pendapatanSubtotal, 0, ',', '.') }}</strong></div>
    </div>

    <br>
    <strong>Pengeluaran</strong>
    <div class="row">
        <div>Operasional</div>
        <div class="r">{{ number_format($ops, 0, ',', '.') }}</div>
    </div>
    <div class="row">
        <div>Non Operasional</div>
        <div class="r">{{ number_format($nonops, 0, ',', '.') }}</div>
    </div>
    <div class="row">
        <div>Gaji</div>
        <div class="r">{{ number_format($gaji, 0, ',', '.') }}</div>
    </div>
    <hr>
    <div class="row">
        <div><strong>Subtotal</strong></div>
        <div class="r"><strong>{{ number_format($pengeluaranSubtotal, 0, ',', '.') }}</strong></div>
    </div>

    <hr>
    <div class="row">
        <div><strong>Total</strong></div>
        <div class="r"><strong>{{ number_format($total, 0, ',', '.') }}</strong></div>
    </div>
</body>

</html>
