<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ficha de Prueba</title>
  <style>
    @@page { margin-top: 5mm; margin-bottom: 5mm; margin-left: 5mm; margin-right: 5mm; }
    body { font-family: 'Arial Narrow', Arial, sans-serif; font-size: 11px; margin: 0; padding: 0; color: #0f172a; }

    .header { text-align: center; margin-bottom: 6px; padding: 8px 0; background: #1e293b; color: #f1f5f9; border-radius: 4px; }
    .header h3 { margin: 0; font-size: 15px; letter-spacing: 0.5px; }
    .header h4 { margin: 2px 0 0; font-size: 10px; color: #94a3b8; }

    .postulante-card { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 10px; margin-bottom: 6px; display: table; width: 100%; }
    .postulante-card .row { display: table-row; }
    .postulante-card .cell { display: table-cell; padding: 2px 6px; font-size: 10px; }
    .postulante-card .lbl { font-weight: bold; color: #475569; width: 65px; font-size: 9px; }

    .mult-card { background: #1e293b; border-radius: 4px; padding: 6px 10px; margin-bottom: 6px; }
    .mult-card table { width: 100%; border-collapse: collapse; }
    .mult-card td { color: #f1f5f9; text-align: center; padding: 3px 4px; font-size: 9px; }
    .mult-card .lbl { color: #94a3b8; font-size: 8px; text-transform: uppercase; letter-spacing: 0.3px; }
    .mult-card .val { font-weight: bold; font-size: 13px; }

    table.detalle { width: 100%; border-collapse: collapse; }
    table.detalle th { background: #334155; color: #f1f5f9; font-size: 9px; padding: 4px 2px; border: 1px solid #475569; text-align: center; text-transform: uppercase; letter-spacing: 0.3px; }
    table.detalle td { padding: 4px 3px; border: 1px solid #e2e8f0; font-size: 11px; text-align: center; }
    table.detalle td.num { font-weight: bold; background: #f8fafc; }
    table.detalle tr.total td { background: #059669; color: #fff; font-weight: bold; font-size: 13px; padding: 7px 3px; border-color: #047857; }

    table.two-cols { width: 100%; border-collapse: collapse; }
    table.two-cols td { vertical-align: top; padding: 0 2px; border: none; }

    .ok { background: #dcfce7; color: #166534; }
    .fail { background: #fee2e2; color: #991b1b; }
    .blank { background: #e2e8f0; color: #64748b; }
    .exc { background: #fef3c7; color: #92400e; }

    .no-calificado { background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; border-radius: 4px; padding: 6px 10px; margin-bottom: 6px; text-align: center; font-weight: bold; font-size: 12px; }
    .sin-respuestas { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; border-radius: 4px; padding: 6px 10px; margin-bottom: 6px; text-align: center; font-weight: bold; font-size: 12px; }
  </style>
</head>
<body>

  <div class="header">
    <h3>FICHA DE CALIFICACIÓN — PRUEBA {{ $prueba->nombre }}</h3>
    @if($prueba->descripcion)<h4>{{ $prueba->descripcion }}</h4>@endif
  </div>

  @if(!$calificado)
  <div class="no-calificado">⚠ POSTULANTE NO CALIFICADO — Mostrando respuestas sin cálculo de puntaje</div>
  @endif

  @if(!$tieneRes)
  <div class="sin-respuestas">No se encontraron respuestas (RES) para este postulante</div>
  @endif

  <div class="postulante-card">
    <div class="row">
      <div class="cell lbl">DNI</div><div class="cell">{{ $dni }}</div>
      <div class="cell lbl">Litho</div><div class="cell">{{ $ide->litho }}</div>
      <div class="cell lbl">Aula</div><div class="cell">{{ $ide->aula }}</div>
      <div class="cell lbl">Tipo</div><div class="cell">{{ $ide->tipo }}</div>
    </div>
    @if($postulante)
    <div class="row">
      <div class="cell lbl">Postulante</div>
      <div class="cell" style="font-weight: bold;">
        {{ trim(($postulante->primer_apellido ?? '') . ' ' . ($postulante->segundo_apellido ?? '')) }}{{ isset($postulante->nombres) ? ', ' . $postulante->nombres : '' }}
      </div>
    </div>
    @endif
  </div>

  <div class="mult-card">
    <table>
      <tr>
        <td class="lbl">Multiplicador</td>
        <td class="lbl">Correcta</td>
        <td class="lbl">Incorrecta</td>
        <td class="lbl">Blanco</td>
        <td class="lbl"># Correctas</td>
        <td class="lbl"># Incorrectas</td>
        <td class="lbl"># Blancos</td>
      </tr>
      <tr>
        <td class="val">{{ $multiplicador?->nombre ?? 'N/A' }}</td>
        <td class="val" style="color:#86efac;">{{ $valCorrecta }}</td>
        <td class="val" style="color:#fca5a5;">{{ $valIncorrecta }}</td>
        <td class="val" style="color:#cbd5e1;">{{ $valBlanco }}</td>
        <td class="val" style="color:#86efac;">{{ $correctas }}</td>
        <td class="val" style="color:#fca5a5;">{{ $incorrectas }}</td>
        <td class="val" style="color:#cbd5e1;">{{ $blancos }}</td>
      </tr>
    </table>
  </div>

  @php $half = ceil(count($detalle) / 2); @endphp
  <table class="two-cols">
    <tr>
      <td style="width: 50%;">
        <table class="detalle">
          <thead>
            <tr>
              <th style="width: 20px;">N°</th>
              <th style="width: 26px;">Marcó</th>
              <th style="width: 26px;">Clave</th>
              <th style="width: 38px;">Pond.</th>
              <th>Resultado</th>
              <th style="width: 30px;">Factor</th>
              <th style="width: 60px;">Puntos</th>
            </tr>
          </thead>
          <tbody>
            @for ($i = 0; $i < $half; $i++)
              @php $d = $detalle[$i]; @endphp
              @php
                $cls = str_starts_with($d['estado'], 'EXC') ? 'exc' : strtolower($d['estado']);
                $factor = str_starts_with($d['estado'], 'EXC') ? 'EXC' : ($d['estado'] === 'Correcto' ? "{$d['pond']}×{$valCorrecta}" : ($d['estado'] === 'Blanco' ? "{$d['pond']}×{$valBlanco}" : "{$d['pond']}×{$valIncorrecta}"));
              @endphp
              <tr>
                <td class="num">{{ $d['num'] }}</td>
                <td>{{ $d['resp'] === ' ' ? '—' : $d['resp'] }}</td>
                <td>{{ $d['clave'] === ' ' ? '—' : $d['clave'] }}</td>
                <td>{{ number_format($d['pond'], 1) }}</td>
                <td class="{{ $cls }}">{{ $d['estado'] }}</td>
                <td class="{{ $cls }}">{{ $factor }}</td>
                <td class="{{ $cls }}" style="font-weight: bold;">{{ number_format($d['puntos'], 7) }}</td>
              </tr>
            @endfor
            <tr class="total">
              <td colspan="6" style="text-align: right;">PUNTAJE TOTAL =</td>
              <td>{{ $calificado ? number_format((float) $puntajeAlmacenado, 3) : 'N/A' }}</td>
            </tr>
          </tbody>
        </table>
      </td>
      <td style="width: 50%;">
        <table class="detalle">
          <thead>
            <tr>
              <th style="width: 20px;">N°</th>
              <th style="width: 26px;">Marcó</th>
              <th style="width: 26px;">Clave</th>
              <th style="width: 38px;">Pond.</th>
              <th>Resultado</th>
              <th style="width: 30px;">Factor</th>
              <th style="width: 60px;">Puntos</th>
            </tr>
          </thead>
          <tbody>
            @for ($i = $half; $i < count($detalle); $i++)
              @php $d = $detalle[$i]; @endphp
              @php
                $cls = str_starts_with($d['estado'], 'EXC') ? 'exc' : strtolower($d['estado']);
                $factor = str_starts_with($d['estado'], 'EXC') ? 'EXC' : ($d['estado'] === 'Correcto' ? "{$d['pond']}×{$valCorrecta}" : ($d['estado'] === 'Blanco' ? "{$d['pond']}×{$valBlanco}" : "{$d['pond']}×{$valIncorrecta}"));
              @endphp
              <tr>
                <td class="num">{{ $d['num'] }}</td>
                <td>{{ $d['resp'] === ' ' ? '—' : $d['resp'] }}</td>
                <td>{{ $d['clave'] === ' ' ? '—' : $d['clave'] }}</td>
                <td>{{ number_format($d['pond'], 1) }}</td>
                <td class="{{ $cls }}">{{ $d['estado'] }}</td>
                <td class="{{ $cls }}">{{ $factor }}</td>
                <td class="{{ $cls }}" style="font-weight: bold;">{{ number_format($d['puntos'], 7) }}</td>
              </tr>
            @endfor
          </tbody>
        </table>
      </td>
    </tr>
  </table>

</body>
</html>
