{{-- Tabulasi stunting format Laporan Analisis Situasi, dipakai di halaman laporan dan export PDF. --}}
@php
    $kolomDemografi = \App\Http\Controllers\LaporanController::KOLOM_DEMOGRAFI;
    $kelompokSasaran = \App\Http\Controllers\LaporanController::INDIKATOR_STUNTING;
    $angka = fn (?int $n) => $n === null ? '–' : number_format($n, 0, ',', '.');
    $persen = fn (?float $n) => $n === null ? '–' : number_format($n, 2, ',', '.');
    $judulTahun = '('.($periode !== '' ? $periode : 'Tahun '.($tahun ?? now()->year)).')';
@endphp

<h3 class="judul-tabel">Tabel 1: Data Demografi per Kecamatan di Kabupaten Kota Ternate</h3>
<div class="{{ $wrapClass ?? '' }}">
    <table class="tabel-ansit {{ $tableClass ?? '' }}">
        <thead>
            <tr>
                <th rowspan="2" class="kol-no">No</th>
                <th rowspan="2" class="kol-kecamatan">Kecamatan</th>
                <th colspan="{{ count($kolomDemografi) }}">Jumlah</th>
            </tr>
            <tr>
                @foreach ($kolomDemografi as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($tabulasi['demografi'] as $kecamatan)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $kecamatan['nama'] }}</td>
                    @foreach (array_keys($kolomDemografi) as $kunci)
                        <td class="angka">{{ $angka($kecamatan['nilai'][$kunci]) }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ 2 + count($kolomDemografi) }}" class="tengah">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<h3 class="judul-tabel">Tabel 2: Prevalensi Stunting dan Masalah Gizi Berdasarkan Perdesa/Kelurahan di Kabupaten Kota Ternate {{ $judulTahun }}</h3>
@php
    $kategoriPrevalensi = \App\Http\Controllers\LaporanController::KATEGORI_PREVALENSI;
@endphp
<div class="{{ $wrapClass ?? '' }}">
    <table class="tabel-ansit {{ $tableClass ?? '' }}">
        <thead>
            <tr>
                <th rowspan="2" class="kol-desa">Desa/Kelurahan</th>
                <th rowspan="2">Jumlah Anak Diukur</th>
                @foreach ($kategoriPrevalensi as $label)
                    <th colspan="2">{{ $label }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach ($kategoriPrevalensi as $label)
                    <th>Jumlah Kasus</th>
                    <th>Prevalensi (%)</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($tabulasi['prevalensi']['desa'] as $desa)
                <tr>
                    <td>Kelurahan {{ $desa['nama'] }}</td>
                    <td class="angka">{{ $angka($desa['diukur']) }}</td>
                    @foreach (array_keys($kategoriPrevalensi) as $kunci)
                        <td class="angka">{{ $angka($desa['kategori'][$kunci]['kasus']) }}</td>
                        <td class="angka">{{ $persen($desa['kategori'][$kunci]['persen']) }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ 2 + count($kategoriPrevalensi) * 2 }}" class="tengah">Belum ada data.</td></tr>
            @endforelse
        </tbody>
        @if ($tabulasi['prevalensi']['desa']->isNotEmpty())
            <tfoot>
                <tr class="baris-total">
                    <td>Jumlah Kabupaten</td>
                    <td class="angka">{{ $angka($tabulasi['prevalensi']['total']['diukur']) }}</td>
                    @foreach (array_keys($kategoriPrevalensi) as $kunci)
                        <td class="angka">{{ $angka($tabulasi['prevalensi']['total']['kategori'][$kunci]['kasus']) }}</td>
                        <td class="angka">{{ $persen($tabulasi['prevalensi']['total']['kategori'][$kunci]['persen']) }}</td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>
</div>
<p class="keterangan">Stunting dari tinggi badan menurut umur (TB/U), gizi buruk & gizi kurang dari berat badan menurut tinggi
    badan (BB/TB), berat badan kurang dari berat badan menurut umur (BB/U). Anak nonaktif karena usia lewat, pindah, atau
    meninggal tidak dihitung.</p>

@php
    $sumbuRadar = collect(\App\Http\Controllers\LaporanController::RADAR_STUNTING)
        ->map(function (array $sumbu, string $kunci) use ($tabulasi) {
            [$kelompok, $indikator] = explode('.', $kunci);
            $nilai = $tabulasi['capaian']['total'][$kelompok][$indikator] ?? null;

            return $nilai === null ? null : ['label' => $sumbu['label'], 'target' => (float) $sumbu['target'], 'capaian' => $nilai['persen']];
        })
        ->filter()
        ->values()
        ->all();
@endphp
<h3 class="judul-tabel">Capaian Layanan Per Kelompok Sasaran Per Kecamatan dan Agregat Kabupaten</h3>
@if (count($sumbuRadar) >= 3)
    <div class="grafik-radar">
        @if ($untukPdf ?? false)
            {{-- PDF: gambar Chart.js yang dikirim browser saat export; SVG sebagai cadangan. --}}
            <img src="{{ $grafikRadar ?? 'data:image/svg+xml;base64,'.base64_encode(view('laporan._radar-stunting-svg', ['sumbu' => $sumbuRadar])->render()) }}"
                alt="Grafik radar target dan capaian layanan" style="width: 100%; max-width: 560px; height: auto;">
        @else
            <div class="grafik-radar-kanvas">
                <canvas id="grafikRadarStunting" data-sumbu='@json($sumbuRadar)'
                    aria-label="Grafik radar target dan capaian layanan" role="img"></canvas>
            </div>
        @endif
    </div>
@endif

<h3 class="judul-tabel">Tabel 3: Capaian Layanan Kesehatan Per Kecamatan di Kabupaten Kota Ternate {{ $judulTahun }}</h3>
@foreach ($kelompokSasaran as $kunciKelompok => $kelompok)
    @php($satuKolom = count($kelompok['indikator']) === 1 && reset($kelompok['indikator']) === $kelompok['label'])
    <h4 class="subjudul-tabel">{{ $kelompok['judul'] }}</h4>
    <div class="{{ $wrapClass ?? '' }}">
        <table class="tabel-ansit {{ ($kelompok['kecil'] ?? false) ? 'kecil' : '' }} {{ $tableClass ?? '' }}">
            <thead>
                <tr>
                    <th rowspan="{{ $satuKolom ? 2 : 3 }}" class="kol-kecamatan">Kecamatan</th>
                    <th colspan="{{ count($kelompok['indikator']) * 2 }}">{{ $kelompok['label'] }}</th>
                </tr>
                @unless ($satuKolom)
                    <tr>
                        @foreach ($kelompok['indikator'] as $label)
                            <th colspan="2">{{ $label }}</th>
                        @endforeach
                    </tr>
                @endunless
                <tr>
                    @foreach ($kelompok['indikator'] as $label)
                        <th>Jml</th>
                        <th>%</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($tabulasi['capaian']['kecamatans'] as $kecamatan)
                    <tr>
                        <td>{{ $kecamatan['nama'] }}</td>
                        @foreach (array_keys($kelompok['indikator']) as $kunci)
                            @php($nilai = $kecamatan['nilai'][$kunciKelompok][$kunci])
                            <td class="angka">{{ $angka($nilai['jml'] ?? null) }}</td>
                            <td class="angka">{{ $persen($nilai['persen'] ?? null) }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ 1 + count($kelompok['indikator']) * 2 }}" class="tengah">Belum ada data.</td></tr>
                @endforelse
            </tbody>
            @if ($tabulasi['capaian']['kecamatans']->isNotEmpty())
                <tfoot>
                    <tr class="baris-total">
                        <td>KABUPATEN</td>
                        @foreach (array_keys($kelompok['indikator']) as $kunci)
                            @php($nilai = $tabulasi['capaian']['total'][$kunciKelompok][$kunci])
                            <td class="angka">{{ $angka($nilai['jml'] ?? null) }}</td>
                            <td class="angka">{{ $persen($nilai['persen'] ?? null) }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endforeach

<p class="keterangan">Keterangan: – = data belum diisi (lihat menu Stunting › Data Sasaran tahun {{ $tahun ?? now()->year }}).</p>
