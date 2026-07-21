<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ranking — {{ $prueba->nombre }}</title>
  <style>
    @@page { margin-top: 10mm; margin-bottom: 8mm; margin-left: 8mm; margin-right: 8mm; }
    body { font-family: Arial, sans-serif; font-size: 9px; margin: 0; padding: 0; color: #0f172a; }

    .header { text-align: center; margin-bottom: 8px; padding: 8px 0; background: #1e293b; color: #f1f5f9; border-radius: 4px; }
    .header h3 { margin: 0; font-size: 14px; }
    .header h4 { margin: 2px 0 0; font-size: 10px; color: #94a3b8; }

    .summary { margin-bottom: 8px; display: table; width: 100%; }
    .summary .stat { display: table-cell; text-align: center; padding: 6px 10px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; }
    .summary .stat .num { font-size: 18px; font-weight: bold; color: #1e293b; }
    .summary .stat .lbl { font-size: 8px; color: #64748b; text-transform: uppercase; }

    table.ranking { width: 100%; border-collapse: collapse; }
    table.ranking th { background: #334155; color: #f1f5f9; font-size: 8px; padding: 5px 4px; border: 1px solid #475569; text-align: center; text-transform: uppercase; letter-spacing: 0.3px; }
    table.ranking td { padding: 4px 4px; border: 1px solid #e2e8f0; font-size: 10px; text-align: center; }
    table.ranking td.num { font-weight: bold; background: #f8fafc; width: 35px; }
    table.ranking td.izq { text-align: left; }
    table.ranking tr:nth-child(even) td { background: #fafbfc; }
    table.ranking tr.top td { background: #dcfce7; font-weight: bold; }
    table.ranking tr.no-cal td { color: #94a3b8; }

    .footer { margin-top: 10px; text-align: center; font-size: 8px; color: #94a3b8; }
  </style>
</head>
<body>

  <div class="header">
    <h3>RANKING DE PRUEBA — {{ $prueba->nombre }}</h3>
    <h4>{{ $prueba->descripcion ?? '' }} · {{ date('d/m/Y H:i') }}</h4>
  </div>

  <div class="summary">
    <div class="stat">
      <div class="num">{{ $total }}</div>
      <div class="lbl">Total Postulantes</div>
    </div>
    <div class="stat">
      <div class="num" style="color: #059669;">{{ $calificados }}</div>
      <div class="lbl">Calificados</div>
    </div>
    <div class="stat">
      <div class="num" style="color: #d97706;">{{ $noCalificados }}</div>
      <div class="lbl">No Calificados</div>
    </div>
    <div class="stat">
      <div class="num" style="color: #2563eb;">{{ $promedio }}</div>
      <div class="lbl">Promedio</div>
    </div>
    <div class="stat">
      <div class="num" style="color: #7c3aed;">{{ $puntajeMaximo }}</div>
      <div class="lbl">Puntaje Máx.</div>
    </div>
    <div class="stat">
      <div class="num" style="color: #dc2626;">{{ $puntajeMinimo }}</div>
      <div class="lbl">Puntaje Mín.</div>
    </div>
  </div>

  <table class="ranking">
    <thead>
      <tr>
        <th style="width: 35px;">N°</th>
        <th style="width: 80px;">DNI</th>
        <th class="izq">Apellidos y Nombres</th>
        <th style="width: 50px;">Litho</th>
        <th style="width: 35px;">Tipo</th>
        <th style="width: 40px;">Aula</th>
        <th style="width: 70px;">Puntaje</th>
        <th style="width: 50px;">Estado</th>
      </tr>
    </thead>
    <tbody>
      @foreach($data as $i => $r)
      <tr class="{{ !$r->calificado ? 'no-cal' : ($i < 3 ? 'top' : '') }}">
        <td class="num">{{ $i + 1 }}</td>
        <td>{{ $r->dni ?? '—' }}</td>
        <td class="izq">
          @if($r->nombres)
            {{ trim(($r->primer_apellido ?? '') . ' ' . ($r->segundo_apellido ?? '')) }}{{ $r->nombres ? ', ' . $r->nombres : '' }}
          @else
            —
          @endif
        </td>
        <td>{{ $r->litho ?? '—' }}</td>
        <td>{{ $r->tipo ?? '—' }}</td>
        <td>{{ $r->aula ?? '—' }}</td>
        <td>{{ $r->calificado ? number_format((float) $r->puntaje, 3) : 'N/A' }}</td>
        <td>{{ $r->calificado ? 'CALIFICADO' : 'PENDIENTE' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    Sistema de Pruebas — Generado el {{ date('d/m/Y H:i:s') }}
  </div>

</body>
</html>
