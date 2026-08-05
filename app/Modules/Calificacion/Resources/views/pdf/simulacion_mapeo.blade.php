<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVuSans, sans-serif; font-size: 9pt; margin: 0; padding: 0; }
    .header { text-align: center; margin-bottom: 15px; }
    .header h1 { font-size: 14pt; margin: 3px 0; color: #333; }
    .header p { font-size: 9pt; color: #666; margin: 2px 0; }
    table.mapeo { border-collapse: collapse; width: 100%; }
    table.mapeo th { background: #1e293b; color: #f1f5f9; padding: 6px 8px; font-size: 8pt; text-align: left; }
    table.mapeo td { border: 1px solid #ddd; padding: 5px 8px; font-size: 8pt; }
    table.mapeo tr:nth-child(even) { background: #f8fafc; }
    .badge { background: #e8eaf6; color: #0f3460; font-family: monospace; font-weight: bold; padding: 2px 6px; border-radius: 3px; font-size: 8pt; }
    .virtual { background: #eef2ff; color: #4338ca; font-weight: 600; padding: 2px 6px; border-radius: 3px; font-size: 8pt; }
    .piso { background: #f1f5f9; color: #64748b; padding: 1px 6px; border-radius: 3px; font-size: 7pt; font-weight: 600; }
    .summary { margin-top: 15px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; }
    .summary table { width: 100%; }
    .summary td { padding: 4px 10px; font-size: 9pt; }
    .summary .label { color: #64748b; font-size: 8pt; text-transform: uppercase; }
    .summary .value { font-size: 13pt; font-weight: bold; color: #0f172a; }
    .warning { color: #cf1322; font-weight: bold; }
</style>
</head>
<body>
<div class="header">
    <h1>{{ $nombre }}</h1>
    <p>{{ count($mapeo) }} aulas mapeadas | {{ date('d/m/Y H:i:s') }}</p>
</div>

<table class="mapeo">
    <thead>
        <tr>
            <th style="width: 40px;">#</th>
            <th style="width: 90px;">Aula Virtual</th>
            <th style="width: 100px;">Área</th>
            <th style="width: 110px;">Ambiente</th>
            <th style="width: 100px;">Aula Física</th>
            <th style="width: 40px;">Piso</th>
            <th style="width: 70px; text-align: center;">Postulantes</th>
            <th style="width: 40px; text-align: center;">Cap.</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($mapeo as $i => $m)
            <tr>
                <td>{{ $m['codigo_asignado'] }}</td>
                <td><span class="virtual">{{ $m['aula_virtual'] }}</span></td>
                <td>{{ $m['area_virtual'] ?? '—' }}</td>
                <td>{{ $m['ambiente_nombre'] ?? '—' }}</td>
                <td>{{ $m['aula_fisica'] ?? '—' }}</td>
                <td>@if (!empty($m['piso']))<span class="piso">P{{ $m['piso'] }}</span>@endif</td>
                <td style="text-align: center;"><strong>{{ $m['estudiantes'] }}</strong></td>
                <td style="text-align: center;">{{ $m['capacidad'] }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background: #f1f5f9; font-weight: bold;">
            <td colspan="6" style="text-align: right;">TOTAL</td>
            <td style="text-align: center;">{{ $totalEstudiantes }}</td>
            <td style="text-align: center;">{{ $totalCapacidad }}</td>
        </tr>
    </tfoot>
</table>

<div class="summary">
    <table>
        <tr>
            <td class="label">Total Postulantes</td>
            <td class="label">Total Aulas</td>
            <td class="label">Capacidad Total</td>
            <td class="label">Estado</td>
        </tr>
        <tr>
            <td class="value">{{ $totalEstudiantes }}</td>
            <td class="value">{{ count($mapeo) }}</td>
            <td class="value">{{ $totalCapacidad }}</td>
            <td class="value">
                @if ($totalCapacidad < $totalEstudiantes)
                    <span class="warning">INSUFICIENTE</span>
                @else
                    <span style="color: #52c41a;">CONFORME</span>
                @endif
            </td>
        </tr>
    </table>
</div>
</body>
</html>
