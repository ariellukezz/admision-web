<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVuSans, sans-serif; font-size: 10pt; }
    .header { text-align: center; margin-bottom: 25px; }
    .header h1 { font-size: 18pt; color: #1976d2; margin: 5px 0; }
    .info { background: #667eea; color: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #e3f2fd; padding: 8px; text-align: left; border: 1px solid #90caf9; color: #1565c0; }
    td { padding: 6px 8px; border: 1px solid #ddd; font-size: 9pt; }
</style>
</head>
<body>
<div class="header">
    <h1>PARAMETROS Y ESTADISTICAS</h1>
    <p>Grupo #{{ $filterGroupId }} - {{ date('d/m/Y H:i:s') }}</p>
</div>
<div class="info">
    <h3>Informacion del Proceso</h3>
    <p>Total Estudiantes: <strong>{{ $stats['total_students'] }}</strong> | Total Salones: <strong>{{ $stats['total_classrooms'] }}</strong></p>
</div>

@if (!empty($stats['area_stats']))
    <h3>Distribucion por Area</h3>
    <table>
        <thead><tr><th>Area</th><th>Salones</th><th>Estudiantes</th><th>Cap. Min</th><th>Cap. Max</th><th>Promedio</th></tr></thead>
        <tbody>
            @foreach ($stats['area_stats'] as $a)
                <tr>
                    <td>{{ $a->area }}</td>
                    <td>{{ $a->num_classrooms }}</td>
                    <td>{{ $a->num_students }}</td>
                    <td>{{ $a->min_capacity }}</td>
                    <td>{{ $a->max_capacity }}</td>
                    <td>{{ $a->avg_capacity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if (!empty($stats['occupancy_stats']))
    <h3>Ocupacion de Salones</h3>
    <table>
        <thead><tr><th>Salon</th><th>Capacidad</th><th>Ocupados</th><th>% Ocupacion</th><th>Estado</th></tr></thead>
        <tbody>
            @foreach ($stats['occupancy_stats'] as $o)
                <tr>
                    <td>{{ $o->nombre }}</td>
                    <td>{{ $o->capacidad }}</td>
                    <td>{{ $o->contador_actual }}</td>
                    <td>{{ $o->occupancy_percentage }}%</td>
                    <td>{{ $o->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
</body>
</html>
