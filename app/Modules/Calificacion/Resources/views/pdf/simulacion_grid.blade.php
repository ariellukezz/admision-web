@php
    $testTypeColors = [
        'P' => '#ff7875', 'Q' => '#73d13d', 'R' => '#40a9ff',
        'S' => '#ff9c6e', 'T' => '#b37feb',
    ];
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVuSans, sans-serif; font-size: 8pt; margin: 0; padding: 0; }
    .header { text-align: center; margin-bottom: 15px; }
    .header h1 { font-size: 14pt; margin: 3px 0; color: #333; }
    .header p { font-size: 9pt; color: #666; margin: 2px 0; }
    .classroom-block { margin-bottom: 20px; page-break-inside: avoid; }
    .ch { background: #6366f1; color: #fff; padding: 6px 10px; border-radius: 4px; margin-bottom: 8px; }
    .ch h3 { margin: 0; font-size: 10pt; }
    .ch .cap { float: right; font-size: 8pt; }
    .ch .area { font-size: 8pt; margin-left: 8px; background: rgba(255,255,255,0.2); padding: 1px 6px; border-radius: 3px; }
    table.grid { border-collapse: collapse; margin: 0 auto; }
    table.grid td { width: 105px; height: 62px; border: 1px solid #999; vertical-align: top; padding: 3px; text-align: center; }
    .cell-dni { font-weight: bold; font-size: 7pt; color: #333; }
    .cell-name { font-size: 6pt; color: #555; line-height: 1.2; }
    .cell-code { font-size: 8pt; font-weight: bold; font-family: monospace; }
    .cell-type { display: inline-block; width: 16px; height: 16px; border-radius: 3px; font-size: 8pt; font-weight: bold; line-height: 16px; text-align: center; color: #fff; }
    .legend-box { border: 1px solid #ccc; border-radius: 6px; padding: 10px; margin-top: 15px; }
    .legend-box h4 { margin: 0 0 8px 0; font-size: 9pt; }
    .legend-item { display: inline-block; margin-right: 12px; font-size: 7pt; }
    .legend-color { display: inline-block; width: 12px; height: 12px; border-radius: 2px; vertical-align: middle; margin-right: 3px; }
</style>
</head>
<body>
<div class="header">
    <h1>{{ $nombre }}</h1>
    <p>{{ $totalStudents }} postulantes | {{ count($aulas) }} aulas | {{ date('d/m/Y H:i:s') }}</p>
</div>

@foreach ($aulas as $aula)
    @php
        $byPos = [];
        foreach ($aula['assignments'] as $a) {
            $byPos[$a['posicion']] = $a;
        }
    @endphp
    <div class="classroom-block">
        <div class="ch">
            <h3>{{ $aula['nombre'] }}
                @if (!empty($aula['area']))<span class="area">{{ $aula['area'] }}</span>@endif
                <span class="cap">Cap: {{ count($aula['assignments']) }}/{{ $aula['capacidad'] }}</span>
            </h3>
        </div>
        <table class="grid">
            @for ($row = 1; $row <= 8; $row++)
                <tr>
                    @for ($col = 1; $col <= 5; $col++)
                        @php $pos = ($col - 1) * 8 + $row; @endphp
                        @php $a = $byPos[$pos] ?? null; @endphp
                        @if ($a)
                            @php
                                $typeColor = $testTypeColors[$a['tipo_examen']] ?? '#999';
                                $typeLetter = $a['tipo_examen'] ?: '-';
                            @endphp
                            <td>
                                <div class="cell-dni">{{ $a['n_documento'] }}</div>
                                <div class="cell-name">{{ $a['nombre'] }}</div>
                                <div class="cell-code">{{ $a['codigo'] }}</div>
                                <div class="cell-type" style="background: {{ $typeColor }}">{{ $typeLetter }}</div>
                            </td>
                        @else
                            <td style="background:#f5f5f5"></td>
                        @endif
                    @endfor
                </tr>
            @endfor
        </table>
    </div>
@endforeach

<div class="legend-box">
    <h4>Tipos de Examen</h4>
    <span class="legend-item"><span class="legend-color" style="background:#ff7875"></span>P</span>
    <span class="legend-item"><span class="legend-color" style="background:#73d13d"></span>Q</span>
    <span class="legend-item"><span class="legend-color" style="background:#40a9ff"></span>R</span>
    <span class="legend-item"><span class="legend-color" style="background:#ff9c6e"></span>S</span>
    <span class="legend-item"><span class="legend-color" style="background:#b37feb"></span>T</span>
</div>
</body>
</html>
