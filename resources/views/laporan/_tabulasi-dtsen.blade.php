{{-- Tabel tabulasi DTSEN, dipakai di halaman laporan dan export PDF. --}}
@php($kategori = \App\Http\Controllers\LaporanController::KATEGORI_DTSEN)
<table class="tabel-dtsen {{ $tableClass ?? '' }}">
    <thead>
        <tr>
            <th rowspan="2" class="kol-kecamatan">Kecamatan</th>
            <th rowspan="2" class="kol-kelurahan">Kelurahan</th>
            <th rowspan="2" class="angka kol-jumlah">Jumlah KK</th>
            <th rowspan="2" class="angka kol-jumlah">Jumlah Jiwa</th>
            @foreach ($kategori as $label)
                <th colspan="2" class="tengah kol-kategori">{{ $label }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($kategori as $label)
                <th class="angka">KK</th>
                <th class="angka">Jiwa</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse ($tabulasi['kecamatans'] as $kecamatan)
            @foreach ($kecamatan['desa'] as $desa)
                <tr>
                    <td class="kecamatan {{ $loop->first ? '' : 'lanjutan' }}">{{ $loop->first ? $kecamatan['nama'] : '' }}</td>
                    <td>{{ $desa['nama'] }}</td>
                    <td class="angka">{{ number_format($desa['nilai']['jumlah']['kk']) }}</td>
                    <td class="angka">{{ number_format($desa['nilai']['jumlah']['jiwa']) }}</td>
                    @foreach (array_keys($kategori) as $kunci)
                        <td class="angka">{{ number_format($desa['nilai'][$kunci]['kk']) }}</td>
                        <td class="angka">{{ number_format($desa['nilai'][$kunci]['jiwa']) }}</td>
                    @endforeach
                </tr>
            @endforeach
            <tr class="baris-jumlah">
                <td colspan="2" class="tengah">JUMLAH</td>
                <td class="angka">{{ number_format($kecamatan['jumlah']['jumlah']['kk']) }}</td>
                <td class="angka">{{ number_format($kecamatan['jumlah']['jumlah']['jiwa']) }}</td>
                @foreach (array_keys($kategori) as $kunci)
                    <td class="angka">{{ number_format($kecamatan['jumlah'][$kunci]['kk']) }}</td>
                    <td class="angka">{{ number_format($kecamatan['jumlah'][$kunci]['jiwa']) }}</td>
                @endforeach
            </tr>
        @empty
            <tr><td colspan="{{ 4 + count($kategori) * 2 }}" class="tengah">Belum ada data.</td></tr>
        @endforelse
    </tbody>
    @if ($tabulasi['kecamatans']->isNotEmpty())
        <tfoot>
            <tr class="baris-total">
                <td colspan="2" class="tengah">TOTAL</td>
                <td class="angka">{{ number_format($tabulasi['total']['jumlah']['kk']) }}</td>
                <td class="angka">{{ number_format($tabulasi['total']['jumlah']['jiwa']) }}</td>
                @foreach (array_keys($kategori) as $kunci)
                    <td class="angka">{{ number_format($tabulasi['total'][$kunci]['kk']) }}</td>
                    <td class="angka">{{ number_format($tabulasi['total'][$kunci]['jiwa']) }}</td>
                @endforeach
            </tr>
        </tfoot>
    @endif
</table>
