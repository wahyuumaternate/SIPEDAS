<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        p.subjudul { margin-top: 0; color: #6b7280; margin-bottom: 16px; }
        h2 { font-size: 13px; margin-top: 18px; margin-bottom: 6px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; }
        th { background: #f3f4f6; }
        td.angka, th.angka { text-align: right; }
        .meta { color: #6b7280; margin-bottom: 12px; }
    </style>
</head>
<body>
    <h1>{{ $judul }}</h1>
    <p class="subjudul">Dicetak {{ now()->format('d F Y H:i') }} WIT &middot; Total data: {{ $totalData }}</p>

    <h2>Rekap per Kecamatan</h2>
    <table>
        <thead>
            <tr>
                <th>Kecamatan</th>
                <th class="angka">Jumlah {{ $modul === 'kemiskinan' ? 'Keluarga' : 'Anak' }}</th>
                @if ($modul === 'kemiskinan')
                    <th class="angka">Jumlah Anggota</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapKecamatan as $row)
                <tr>
                    <td>{{ $row->label }}</td>
                    <td class="angka">{{ $modul === 'kemiskinan' ? $row->jumlah_keluarga : $row->jumlah_anak }}</td>
                    @if ($modul === 'kemiskinan')
                        <td class="angka">{{ $row->jumlah_anggota }}</td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="3">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rekap per Desa/Kelurahan</h2>
    <table>
        <thead>
            <tr>
                <th>Desa/Kelurahan</th>
                <th class="angka">Jumlah {{ $modul === 'kemiskinan' ? 'Keluarga' : 'Anak' }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapDesa as $row)
                <tr>
                    <td>{{ $row->label }}</td>
                    <td class="angka">{{ $modul === 'kemiskinan' ? $row->jumlah_keluarga : $row->jumlah_anak }}</td>
                </tr>
            @empty
                <tr><td colspan="2">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rekap Status Validasi</h2>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th class="angka">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekapStatus as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="angka">{{ $row['jumlah'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($modul === 'kemiskinan')
        <h2>Rekap Kepesertaan Program</h2>
        <table>
            <thead>
                <tr>
                    <th>Program</th>
                    <th class="angka">Jumlah Penerima</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rekapProgram as $row)
                    <tr>
                        <td>{{ $row->label }}</td>
                        <td class="angka">{{ $row->jumlah }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">Belum ada data kepesertaan program.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        <h2>Rekap Jenis Kelamin</h2>
        <table>
            <thead>
                <tr>
                    <th>Jenis Kelamin</th>
                    <th class="angka">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rekapJenisKelamin as $row)
                    <tr>
                        <td>{{ ucfirst($row->label) }}</td>
                        <td class="angka">{{ $row->jumlah }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>Rekap Kategori Stunting</h2>
        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th class="angka">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rekapKategoriStunting as $row)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $row->label)) }}</td>
                        <td class="angka">{{ $row->jumlah }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">Belum ada hasil pengukuran.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>
