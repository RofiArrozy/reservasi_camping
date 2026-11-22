{{--
    QR DUMMY: pola kotak-kotak mirip QR code yang dibangkitkan dari sebuah nilai (hash).
    Bukan QR sungguhan dan tidak dapat dipindai; hanya sebagai ilustrasi tiket (sesuai ketentuan tugas).
--}}
@props(['value', 'size' => 160])

@php
    $n = 25;

    // Alirkan bit pseudo-acak yang deterministik dari nilai (SHA-256 berulang).
    $stream = '';
    for ($i = 0; strlen($stream) < $n * $n; $i++) {
        $stream .= hash('sha256', $value . '|' . $i);
    }
    $bits = '';
    foreach (str_split($stream) as $hex) {
        $bits .= str_pad(base_convert($hex, 16, 2), 4, '0', STR_PAD_LEFT);
    }

    $reserved = fn (int $r, int $c): bool =>
        ($r < 8 && $c < 8) || ($r < 8 && $c >= $n - 8) || ($r >= $n - 8 && $c < 8);

    $svg = '';
    for ($r = 0; $r < $n; $r++) {
        for ($c = 0; $c < $n; $c++) {
            if (! $reserved($r, $c) && $bits[$r * $n + $c] === '1') {
                $svg .= '<rect x="' . $c . '" y="' . $r . '" width="1" height="1"/>';
            }
        }
    }

    // Tiga penanda sudut (finder pattern)
    foreach ([[0, 0], [0, $n - 7], [$n - 7, 0]] as [$fr, $fc]) {
        $svg .= '<rect x="' . $fc . '" y="' . $fr . '" width="7" height="7"/>'
              . '<rect x="' . ($fc + 1) . '" y="' . ($fr + 1) . '" width="5" height="5" fill="#fff"/>'
              . '<rect x="' . ($fc + 2) . '" y="' . ($fr + 2) . '" width="3" height="3"/>';
    }
@endphp

<svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 {{ $n + 4 }} {{ $n + 4 }}"
     width="{{ $size }}" height="{{ $size }}" role="img" aria-label="QR dummy"
     shape-rendering="crispEdges" {{ $attributes }}>
    <rect x="-2" y="-2" width="{{ $n + 4 }}" height="{{ $n + 4 }}" fill="#fff"/>
    <g fill="#064e3b">{!! $svg !!}</g>
</svg>
