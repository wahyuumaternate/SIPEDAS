@php
    /**
     * Grafik radar Target vs Capaian (SVG murni) agar bisa dipakai di halaman web maupun dompdf.
     * $sumbu: list<array{label: string, target: float, capaian: float}>
     */
    $lebar = 640;
    $tinggi = 480;
    $cx = $lebar / 2;
    $cy = 255;
    $r = 165;
    $n = count($sumbu);
    $titik = function (int $i, float $nilai) use ($n, $cx, $cy, $r): array {
        $sudut = -M_PI / 2 + 2 * M_PI * $i / $n;
        $jarak = $r * max(0, min($nilai, 100)) / 100;

        return [round($cx + $jarak * cos($sudut), 2), round($cy + $jarak * sin($sudut), 2)];
    };
    $poligon = fn (string $kunci) => collect($sumbu)->map(fn ($s, $i) => implode(',', $titik($i, $s[$kunci])))->implode(' ');
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $lebar }}" height="{{ $tinggi }}" viewBox="0 0 {{ $lebar }} {{ $tinggi }}" font-family="Helvetica, Arial, sans-serif">
    <rect x="0" y="0" width="{{ $lebar }}" height="{{ $tinggi }}" fill="#ffffff"/>

    {{-- Legenda --}}
    <rect x="{{ $cx - 110 }}" y="14" width="34" height="12" fill="#ff6384" fill-opacity="0.2" stroke="#ff6384" stroke-width="2"/>
    <text x="{{ $cx - 70 }}" y="25" font-size="12" fill="#555555">Target</text>
    <rect x="{{ $cx + 5 }}" y="14" width="34" height="12" fill="#36a2eb" fill-opacity="0.2" stroke="#36a2eb" stroke-width="2"/>
    <text x="{{ $cx + 45 }}" y="25" font-size="12" fill="#555555">Capaian</text>

    {{-- Kisi lingkaran 20–100 --}}
    @foreach ([20, 40, 60, 80, 100] as $skala)
        <polygon points="{{ collect(range(0, $n - 1))->map(fn ($i) => implode(',', $titik($i, $skala)))->implode(' ') }}" fill="none" stroke="#dddddd" stroke-width="1"/>
        <text x="{{ $cx + 3 }}" y="{{ $cy - $r * $skala / 100 + 3 }}" font-size="8" fill="#888888">{{ $skala }}</text>
    @endforeach

    {{-- Sumbu & label --}}
    @foreach ($sumbu as $i => $s)
        @php
            [$x, $y] = $titik($i, 100);
            [$lx, $ly] = $titik($i, 108);
            $anchor = abs($lx - $cx) < 8 ? 'middle' : ($lx > $cx ? 'start' : 'end');
            $lx += $anchor === 'start' ? 4 : ($anchor === 'end' ? -4 : 0);
            $ly += $ly < $cy - $r * 0.9 ? -6 : ($ly > $cy + $r * 0.9 ? 10 : 3);
        @endphp
        <line x1="{{ $cx }}" y1="{{ $cy }}" x2="{{ $x }}" y2="{{ $y }}" stroke="#dddddd" stroke-width="1"/>
        <text x="{{ $lx }}" y="{{ $ly }}" font-size="9" fill="#555555" text-anchor="{{ $anchor }}">{{ $s['label'] }}</text>
    @endforeach

    {{-- Target & capaian --}}
    <polygon points="{{ $poligon('target') }}" fill="#ff6384" fill-opacity="0.15" stroke="#ff6384" stroke-width="2.5"/>
    <polygon points="{{ $poligon('capaian') }}" fill="#36a2eb" fill-opacity="0.2" stroke="#36a2eb" stroke-width="2.5"/>
    @foreach ($sumbu as $i => $s)
        @php([$tx, $ty] = $titik($i, $s['target']))
        <circle cx="{{ $tx }}" cy="{{ $ty }}" r="2.5" fill="#ff6384"/>
        @php([$px, $py] = $titik($i, $s['capaian']))
        <circle cx="{{ $px }}" cy="{{ $py }}" r="2.5" fill="#36a2eb"/>
    @endforeach
</svg>
