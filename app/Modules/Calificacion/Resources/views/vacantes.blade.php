<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @@page { margin: 1.5cm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 14px; }
        .header h1 { font-size: 14pt; margin: 0; }
        .header h2 { font-size: 11pt; margin: 4px 0 0 0; font-weight: normal; color: #444; }
        .header h3 { font-size: 13pt; margin: 6px 0 0 0; }
        .totals { text-align: center; font-size: 10pt; margin-bottom: 16px; padding: 8px; background: #f8fafc; border: 1px solid #cbd5e1; }
        .totals strong { color: #1e293b; }
        .totals .sep { color: #cbd5e1; margin: 0 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #1e293b; color: #f1f5f9; font-size: 9pt; padding: 6px 8px; text-align: left; }
        td { border: 1px solid #cbd5e1; padding: 5px 8px; font-size: 9pt; }
        tr:nth-child(even) td { background: #f8fafc; }
        .section-title { font-size: 11pt; font-weight: bold; color: #1e293b; margin: 16px 0 8px 0; padding: 6px 10px; background: #eef2ff; border-left: 4px solid #6366f1; }
        .section-title.etapa2 { border-left-color: #f59e0b; background: #fffbeb; }
        .footer { text-align: center; margin-top: 20px; font-size: 7pt; color: #94a3b8; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

<div class="header">
    <h1>Universidad Nacional del Altiplano</h1>
    <h2>Vicerrectorado Académico — Dirección de Admisión</h2>
    <h3>REPORTE DE VACANTES POR PROGRAMA</h3>
    <div style="font-size: 9pt; color: #666; margin-top: 4px;">{{ $calificacion->nombre ?? '' }}</div>
</div>

<div class="totals">
    <strong>Total Postulantes:</strong> {{ number_format($totalPostulantes) }}
    <span class="sep">|</span>
    <strong>20% Segunda Etapa:</strong> {{ number_format($veintePorciento) }}
    <span class="sep">|</span>
    <strong>Total Vacantes:</strong> {{ number_format($etapa1->sum('vacantes') + $etapa2->sum('vacantes')) }}
    <span class="sep">|</span>
    <strong>Etapa 1:</strong> {{ $etapa1->sum('vacantes') }} vacantes ({{ $etapa1->count() }} programas)
    <span class="sep">|</span>
    <strong>Etapa 2:</strong> {{ $etapa2->sum('vacantes') }} vacantes ({{ $etapa2->count() }} programas)
</div>

<div class="section-title">ETAPA 1 — Ingreso Directo</div>

<table>
    <thead>
        <tr>
            <th style="width: 40px;">N°</th>
            <th>Programa</th>
            <th class="text-center" style="width: 80px;">Vacantes</th>
            <th class="text-center" style="width: 90px;">Postulantes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($etapa1 as $i => $v)
            @php $post = $postulantesPorPrograma[$v->id_programa] ?? 0; @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $v->programa }}</td>
                <td class="text-center"><strong>{{ $v->vacantes }}</strong></td>
                <td class="text-center">{{ number_format($post) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" class="text-right"><strong>TOTAL</strong></td>
            <td class="text-center"><strong>{{ $etapa1->sum('vacantes') }}</strong></td>
            <td class="text-center"><strong>{{ number_format($postulantesPorPrograma->only($etapa1->pluck('id_programa')->toArray())->sum()) }}</strong></td>
        </tr>
    </tfoot>
</table>

<div class="section-title etapa2">ETAPA 2 — Segunda Etapa</div>
<p style="font-size: 8pt; color: #92400e; margin: 0 0 8px 0;">
    Los postulantes que no ingresen en la primera etapa compiten por el 20% excedente ({{ number_format($veintePorciento) }} postulantes).
</p>

@if($etapa2->count() > 0)
<table>
    <thead>
        <tr>
            <th style="width: 40px;">N°</th>
            <th>Programa</th>
            <th class="text-center" style="width: 80px;">Vacantes</th>
            <th class="text-center" style="width: 90px;">Postulantes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($etapa2 as $i => $v)
            @php $post = $postulantesPorPrograma[$v->id_programa] ?? 0; @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $v->programa }}</td>
                <td class="text-center"><strong>{{ $v->vacantes }}</strong></td>
                <td class="text-center">{{ number_format($post) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" class="text-right"><strong>TOTAL</strong></td>
            <td class="text-center"><strong>{{ $etapa2->sum('vacantes') }}</strong></td>
            <td class="text-center"><strong>{{ number_format($postulantesPorPrograma->only($etapa2->pluck('id_programa')->toArray())->sum()) }}</strong></td>
        </tr>
    </tfoot>
</table>
@else
<p style="font-size: 9pt; color: #94a3b8; text-align: center; padding: 20px;">No hay programas en segunda etapa.</p>
@endif

<div class="footer">Generado el {{ date('d/m/Y H:i:s') }}</div>

</body>
</html>
