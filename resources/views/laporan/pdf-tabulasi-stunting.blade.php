<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tabulasi Data Stunting</title>
    <style>
        @page { margin: 70px 80px 60px; }
        body { font-family: times, serif; font-size: 10px; color: #000; }
        h3.judul-tabel { font-size: 12px; font-weight: bold; margin: 18px 0 10px; }
        h3.judul-tabel:first-child { margin-top: 0; }
        h4.subjudul-tabel { font-size: 12px; font-weight: bold; margin: 16px 0 10px; }
        table.tabel-ansit { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .tabel-ansit th, .tabel-ansit td { border: 1px solid #000; padding: 4px 5px; }
        .tabel-ansit th { font-weight: bold; text-align: center; vertical-align: middle; }
        .tabel-ansit thead { display: table-header-group; }
        .tabel-ansit tr { page-break-inside: avoid; }
        .tabel-ansit td { font-size: 8px; }
        .tabel-ansit td.angka { text-align: right; }
        .tabel-ansit .tengah { text-align: center; }
        .tabel-ansit th.kol-no { width: 18px; }
        .tabel-ansit th.kol-kecamatan { width: 80px; }
        .tabel-ansit th.kol-desa { width: 26%; }
        .tabel-ansit tr.baris-total td { font-size: 11px; font-weight: bold; }
        .tabel-ansit.kecil th, .tabel-ansit.kecil td { font-size: 6px; padding: 2px; }
        .tabel-ansit.kecil tr.baris-total td { font-size: 7px; }
        .grafik-radar { text-align: center; margin: 4px 0 8px; }
        p.keterangan { font-size: 9px; font-style: italic; margin-top: 12px; }
    </style>
</head>
<body>
    @include('laporan._tabulasi-stunting', [
        'tabulasi' => $tabulasi,
        'periode' => $periode,
        'tahun' => $tahun,
        'untukPdf' => true,
        'grafikRadar' => $grafikRadar ?? null,
    ])
</body>
</html>
