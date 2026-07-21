<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ficha Óptica — {{ $litho ?? '' }}</title>
  <style>
    @page { margin: 5mm; }
    * { box-sizing: border-box; }
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; color: #0f172a; font-size: 9px; }
    .header { border: 2px solid #0f172a; border-radius: 4px; margin-bottom: 4px; overflow: hidden; }
    .header-top { background: #0f172a; color: #f1f5f9; text-align: center; padding: 4px; }
    .header-top h3 { margin: 0; font-size: 11px; letter-spacing: 1px; }
    .header-top h4 { margin: 1px 0 0; font-size: 8px; color: #94a3b8; }
    .header-info { display: flex; }
    .header-info .field { flex: 1; border-right: 1px solid #cbd5e1; padding: 3px 6px; }
    .header-info .field:last-child { border-right: none; }
    .header-info .lbl { font-size: 6px; text-transform: uppercase; color: #64748b; }
    .header-info .val { font-weight: bold; font-size: 10px; }
    .ficha-grid { display: table; width: 100%; }
    .ficha-col { display: table-cell; width: 50%; vertical-align: top; padding: 0 2px; }
    .col-header { background: #334155; color: #f1f5f9; font-size: 7px; font-weight: bold; padding: 2px; text-align: center; text-transform: uppercase; }
    .leyenda { margin-top: 4px; font-size: 7px; color: #64748b; display: flex; gap: 10px; justify-content: center; }
    .leyenda .item { display: inline-flex; align-items: center; gap: 3px; }
  </style>
</head>
<body>

  @php
    $letras = ['A', 'B', 'C', 'D', 'E'];
    $respStr = $respuestas ?? '';
    $total = strlen($respStr);
    $maxPreg = max(60, $total);
    $half = (int) ceil($maxPreg / 2);

    $bubbleR = 6;
    $bubbleSpacing = 16;
    $bubbleStartX = 10;
    $bubbleY = 6;
    $rowHeight = 15;
    $svgWidth = $bubbleStartX + count($letras) * $bubbleSpacing + 5;
  @endphp

  <div class="header">
    <div class="header-top">
      <h3>UNIVERSIDAD NACIONAL DEL ALTIPLANO DE PUNO</h3>
      <h4>DIRECCIÓN DE ADMISIÓN — FICHA ÓPTICA DE LECTURA</h4>
    </div>
    <div class="header-info">
      <div class="field"><div class="lbl">DNI</div><div class="val">{{ $dni ?? '—' }}</div></div>
      <div class="field"><div class="lbl">Litho</div><div class="val">{{ $litho ?? '—' }}</div></div>
      <div class="field"><div class="lbl">Aula</div><div class="val">{{ $aula ?? '—' }}</div></div>
      <div class="field"><div class="lbl">Tipo</div><div class="val">{{ $tipo ?? '—' }}</div></div>
    </div>
  </div>

  <div class="ficha-grid">
    {{-- Columna izquierda --}}
    <div class="ficha-col">
      <div class="col-header">Preguntas 1 — {{ $half }}</div>
      <svg width="{{ $svgWidth }}" height="{{ $half * $rowHeight + 2 }}" xmlns="http://www.w3.org/2000/svg">
        @for ($i = 0; $i < $half; $i++)
          @php
            $resp = $i < $total ? strtoupper(trim($respStr[$i])) : ' ';
            $y = $i * $rowHeight + $bubbleY;
          @endphp
          <text x="2" y="{{ $y + 3 }}" font-size="8" font-weight="bold" fill="#334155" font-family="Arial">{{ $i + 1 }}</text>
          @foreach ($letras as $j => $letra)
            @php
              $cx = $bubbleStartX + $j * $bubbleSpacing;
              $isResp = ($resp === $letra);
              $isBlank = ($resp === ' ' || $resp === '');
            @endphp
            @if ($isResp && !$isBlank)
              <circle cx="{{ $cx }}" cy="{{ $y }}" r="{{ $bubbleR }}" fill="#1e293b" stroke="#1e293b" stroke-width="1"/>
            @elseif ($isResp && $isBlank)
              <circle cx="{{ $cx }}" cy="{{ $y }}" r="{{ $bubbleR }}" fill="none" stroke="#94a3b8" stroke-width="1" stroke-dasharray="2,2"/>
            @else
              <circle cx="{{ $cx }}" cy="{{ $y }}" r="{{ $bubbleR }}" fill="none" stroke="#475569" stroke-width="1"/>
            @endif
            <text x="{{ $cx }}" y="{{ $y + 2 }}" font-size="6" fill="#64748b" text-anchor="middle" font-family="Arial">{{ $letra }}</text>
          @endforeach
        @endfor
      </svg>
    </div>

    {{-- Columna derecha --}}
    <div class="ficha-col">
      <div class="col-header">Preguntas {{ $half + 1 }} — {{ $maxPreg }}</div>
      <svg width="{{ $svgWidth }}" height="{{ ($maxPreg - $half) * $rowHeight + 2 }}" xmlns="http://www.w3.org/2000/svg">
        @for ($i = $half; $i < $maxPreg; $i++)
          @php
            $resp = $i < $total ? strtoupper(trim($respStr[$i])) : ' ';
            $rowIdx = $i - $half;
            $y = $rowIdx * $rowHeight + $bubbleY;
          @endphp
          <text x="2" y="{{ $y + 3 }}" font-size="8" font-weight="bold" fill="#334155" font-family="Arial">{{ $i + 1 }}</text>
          @foreach ($letras as $j => $letra)
            @php
              $cx = $bubbleStartX + $j * $bubbleSpacing;
              $isResp = ($resp === $letra);
              $isBlank = ($resp === ' ' || $resp === '');
            @endphp
            @if ($isResp && !$isBlank)
              <circle cx="{{ $cx }}" cy="{{ $y }}" r="{{ $bubbleR }}" fill="#1e293b" stroke="#1e293b" stroke-width="1"/>
            @elseif ($isResp && $isBlank)
              <circle cx="{{ $cx }}" cy="{{ $y }}" r="{{ $bubbleR }}" fill="none" stroke="#94a3b8" stroke-width="1" stroke-dasharray="2,2"/>
            @else
              <circle cx="{{ $cx }}" cy="{{ $y }}" r="{{ $bubbleR }}" fill="none" stroke="#475569" stroke-width="1"/>
            @endif
            <text x="{{ $cx }}" y="{{ $y + 2 }}" font-size="6" fill="#64748b" text-anchor="middle" font-family="Arial">{{ $letra }}</text>
          @endforeach
        @endfor
      </svg>
    </div>
  </div>

  <div class="leyenda">
    <div class="item">
      <svg width="10" height="10" xmlns="http://www.w3.org/2000/svg"><circle cx="5" cy="5" r="4" fill="#1e293b" stroke="#1e293b"/></svg>
      Marcó
    </div>
    <div class="item">
      <svg width="10" height="10" xmlns="http://www.w3.org/2000/svg"><circle cx="5" cy="5" r="4" fill="none" stroke="#94a3b8" stroke-dasharray="2,2"/></svg>
      En blanco
    </div>
    <div class="item">
      <svg width="10" height="10" xmlns="http://www.w3.org/2000/svg"><circle cx="5" cy="5" r="4" fill="none" stroke="#475569"/></svg>
      Sin marcar
    </div>
  </div>

</body>
</html>
