@php
    $swapTypeClasses = [
        'same_classroom' => 'st-classroom',
        'cross_group' => 'st-cross',
    ];
    $swapTypeLabels = [
        'same_classroom' => 'Mismo Salón',
        'cross_group' => 'Cross-Grupo',
    ];
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVuSans, sans-serif; font-size: 9pt; }
    .header { text-align: center; margin-bottom: 20px; }
    .header h1 { font-size: 16pt; color: #333; margin: 3px 0; }
    .summary { background: #262626; color: #fff; padding: 12px; border-radius: 6px; margin-bottom: 18px; text-align: center; }
    .event-block { border: 1px solid #d9d9d9; border-radius: 6px; margin-bottom: 15px; page-break-inside: avoid; }
    .event-header { background: #fafafa; padding: 8px 10px; border-bottom: 1px solid #d9d9d9; border-radius: 6px 6px 0 0; }
    .event-header h3 { margin: 0; font-size: 10pt; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f0f0f0; padding: 5px; text-align: left; border: 1px solid #ddd; font-size: 8pt; }
    td { padding: 4px 6px; border: 1px solid #ddd; font-size: 8pt; }
    .swap-type { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 7pt; font-weight: bold; color: #fff; }
    .st-same { background: #52c41a; }
    .st-cross { background: #ff4d4f; }
    .st-classroom { background: #1890ff; }
</style>
</head>
<body>
<div class="header">
    <h1>HISTORIAL DE CAMBIOS</h1>
    <p>Grupo #{{ $filterGroupId }} - {{ date('d/m/Y H:i:s') }}</p>
</div>
<div class="summary">
    <h3>{{ count($events) }} eventos | {{ $totalSwaps }} intercambios</h3>
</div>

@foreach ($events as $e)
    @php $event = $e['event']; @endphp
    <div class="event-block">
        <div class="event-header">
            <h3>Evento #{{ $event->id }} - {{ $event->descripcion }} - {{ date('d/m/Y H:i:s', strtotime($event->created_at)) }}</h3>
            @if ($event->motivo)
                <p style="margin:3px 0 0 0;font-size:8pt;color:#666">Motivo: {{ $event->motivo }}</p>
            @endif
        </div>

        @if (!empty($e['swaps']))
            <table>
                <thead>
                    <tr><th>#</th><th>Origen</th><th>DNI</th><th>Salón</th><th>Pos</th><th>→</th><th>Destino</th><th>DNI</th><th>Salón</th><th>Pos</th><th>Tipo</th></tr>
                </thead>
                <tbody>
                    @foreach ($e['swaps'] as $i => $swap)
                        @php
                            $typeClass = $swapTypeClasses[$swap->tipo_intercambio] ?? 'st-same';
                            $typeLabel = $swapTypeLabels[$swap->tipo_intercambio] ?? 'Mismo Grupo';
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $swap->origin_name }}</td>
                            <td>{{ $swap->origin_dni }}</td>
                            <td>{{ $swap->origin_classroom_name }}</td>
                            <td>{{ $swap->origen_posicion }}</td>
                            <td style="text-align:center;font-weight:bold">→</td>
                            <td>{{ $swap->dest_name }}</td>
                            <td>{{ $swap->dest_dni }}</td>
                            <td>{{ $swap->dest_classroom_name }}</td>
                            <td>{{ $swap->destino_posicion }}</td>
                            <td><span class="swap-type {{ $typeClass }}">{{ $typeLabel }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="padding:10px;color:#999;font-size:8pt">Sin intercambios registrados</p>
        @endif
    </div>
@endforeach
</body>
</html>
