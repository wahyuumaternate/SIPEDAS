<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapan Data Tunggal Sosial dan Ekonomi Nasional</title>
    <style>
        @page { margin: 24px 24px; }
        body { font-family: sans-serif; font-size: 8px; color: #111827; }
        h1 { font-size: 12px; text-align: center; margin: 0 0 2px; text-transform: uppercase; }
        p.periode { font-size: 11px; font-weight: bold; text-align: center; margin: 0 0 10px; text-transform: uppercase; }
        p.cetak { color: #6b7280; margin: 0 0 6px; text-align: right; }
        table.tabel-dtsen { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .tabel-dtsen th.kol-kecamatan { width: 9%; }
        .tabel-dtsen th.kol-kelurahan { width: 11%; }
        .tabel-dtsen th.kol-jumlah { width: 5%; }
        .tabel-dtsen th.kol-kategori { width: 8.75%; }
        .tabel-dtsen th, .tabel-dtsen td { border: 1px solid #6b7280; padding: 3px 4px; }
        .tabel-dtsen th { background: #f3f4f6; font-weight: bold; text-align: center; text-transform: uppercase; }
        .tabel-dtsen thead { display: table-header-group; }
        .tabel-dtsen tr { page-break-inside: avoid; }
        .tabel-dtsen td.angka { text-align: right; }
        .tabel-dtsen .tengah { text-align: center; }
        .tabel-dtsen td.kecamatan { font-weight: bold; border-bottom: none; }
        .tabel-dtsen td.lanjutan { border-top: none; }
        .tabel-dtsen tr.baris-jumlah td { background: #f8cba2; font-weight: bold; }
        .tabel-dtsen tr.baris-total td { background: #e26b0a; color: #ffffff; font-weight: bold; }
        .ttd { width: 260px; margin-left: auto; margin-top: 12px; text-align: center; font-size: 10px; page-break-inside: avoid; }
        .ttd .ruang { height: 45px; }
    </style>
</head>
<body>
    <h1>Rekapan Data Tunggal Sosial dan Ekonomi Nasional</h1>
    @if ($periode !== '')
        <p class="periode">Periode {{ $periode }}</p>
    @endif
    <p class="cetak">Dicetak {{ now()->translatedFormat('d F Y H:i') }}</p>

    @include('laporan._tabulasi-dtsen', ['tabulasi' => $tabulasi])

    <div class="ttd">
        <div>Kepala Dinas Sosial</div>
        <div>Kota Ternate</div>
        <div class="ruang"></div>
        <div>(.......................................................)</div>
        <div>NIP. ..................................................</div>
    </div>
</body>
</html>
