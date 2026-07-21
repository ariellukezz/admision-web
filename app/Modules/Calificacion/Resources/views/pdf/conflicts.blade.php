<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVuSans, sans-serif; font-size: 9pt; }
    .header { text-align: center; margin-bottom: 20px; }
    .header h1 { font-size: 16pt; margin: 3px 0; }
    .summary { padding: 12px; border-radius: 6px; margin-bottom: 18px; text-align: center; color: #fff; }
    .summary h3 { margin: 0 0 5px 0; }
    .section { border-radius: 6px; padding: 10px; margin-bottom: 15px; page-break-inside: avoid; }
    .section-twin { border: 2px solid #cf1322; background: #fff5f5; }
    .section-college { border: 2px solid #faad14; background: #fffbe6; }
    .section-parent { border: 2px solid #531dab; background: #f9f0ff; }
    .section-title { font-weight: bold; margin-bottom: 8px; padding-bottom: 5px; }
    .title-twin { color: #cf1322; border-bottom: 2px solid #cf1322; }
    .title-college { color: #d48806; border-bottom: 2px solid #faad14; }
    .title-parent { color: #531dab; border-bottom: 2px solid #531dab; }
    table { width: 100%; border-collapse: collapse; }
    th { padding: 5px; text-align: left; border: 1px solid #ddd; font-size: 8pt; }
    td { padding: 4px 6px; border: 1px solid #ddd; font-size: 8pt; }
    .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; color: #fff; font-size: 7pt; font-weight: bold; }
</style>
</head>
<body>
<div class="header">
    <h1>REPORTE DE CONFLICTOS</h1>
    <p>Grupo #{{ $filterGroupId }} - {{ date('d/m/Y H:i:s') }}</p>
</div>
<div class="summary" style="background:#333">
    <h3>Total Conflictos: {{ $total }}</h3>
    <p>
        <span class="badge" style="background:#cf1322">Mellizos: {{ $conflicts['total_twins'] }}</span>
        <span class="badge" style="background:#faad14">Mismo Colegio: {{ $conflicts['total_same_college'] }}</span>
        <span class="badge" style="background:#531dab">Mismos Padres: {{ $conflicts['total_same_parents'] }}</span>
    </p>
</div>

@foreach ($conflicts['twin_alerts'] ?? [] as $alert)
    <div class="section section-twin">
        <div class="section-title title-twin">🔴 Mellizos/Hermanos - {{ $alert['classroom'] }} - {{ $alert['paterno'] . ' ' . $alert['materno'] }}</div>
        <table>
            <thead><tr><th>Pos</th><th>DNI</th><th>Código</th><th>Apellidos y Nombres</th><th>F. Nac.</th><th>Ubigeo</th></tr></thead>
            <tbody>
                @foreach ($alert['students'] as $s)
                    <tr>
                        <td>{{ $s['position'] }}</td>
                        <td>{{ $s['code'] }}</td>
                        <td>{{ $s['code'] }}</td>
                        <td>{{ $s['paterno'] . ' ' . $s['materno'] . ', ' . $s['nombres'] }}</td>
                        <td>{{ $s['fec_nacimiento'] ?? '-' }}</td>
                        <td>{{ $s['ubigeo_nacimiento'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach

@foreach ($conflicts['same_college_groups'] ?? [] as $group)
    <div class="section section-college">
        <div class="section-title title-college">🟡 Mismo Colegio - {{ $group['classroom_name'] }} - Cod: {{ $group['cod_modular'] }} - Egreso: {{ $group['egreso'] }}</div>
        <table>
            <thead><tr><th>Pos</th><th>DNI</th><th>Código</th><th>Apellidos y Nombres</th><th>Programa</th></tr></thead>
            <tbody>
                @foreach ($group['students'] as $s)
                    <tr>
                        <td>{{ $s['position'] }}</td>
                        <td>{{ $s['code'] }}</td>
                        <td>{{ $s['code'] }}</td>
                        <td>{{ $s['paterno'] . ' ' . $s['materno'] . ', ' . $s['nombres'] }}</td>
                        <td>{{ $s['programa_estudios'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach

@foreach ($conflicts['same_parent_alerts'] ?? [] as $alert)
    <div class="section section-parent">
        <div class="section-title title-parent">🟣 Mismos Padres - {{ $alert['classroom'] }} - {{ $alert['parent_type'] }}: {{ $alert['parent_name'] }} (DNI: {{ $alert['parent_dni'] }})</div>
        <table>
            <thead><tr><th>Pos</th><th>Código</th><th>Apellidos y Nombres</th></tr></thead>
            <tbody>
                @foreach ($alert['students'] as $s)
                    <tr>
                        <td>{{ $s['position'] }}</td>
                        <td>{{ $s['code'] }}</td>
                        <td>{{ $s['paterno'] . ' ' . $s['materno'] . ', ' . $s['nombres'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach
</body>
</html>
