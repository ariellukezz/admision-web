<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Seleccionados - {{ $sorteo->nombre }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; padding: 20px; font-size: 12px; color: #1e293b; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 11px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th { background: #2563eb; color: #fff; padding: 6px 8px; text-align: left; font-size: 11px; }
        table td { border: 1px solid #e2e8f0; padding: 5px 8px; font-size: 11px; }
        table tr:nth-child(even) td { background: #f8fafc; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-anulado { background: #fee2e2; color: #dc2626; }
        .badge-observado { background: #fef3c7; color: #d97706; }
        .badge-activo { background: #dcfce7; color: #16a34a; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Lista de Seleccionados</h2>
        <p><strong>Proceso:</strong> {{ $proceso->nombre ?? '' }}</p>
        <p><strong>Sorteo:</strong> {{ $sorteo->nombre }}</p>
        @if ($cargoNombre)
        <p><strong>Cargo:</strong> {{ $cargoNombre }}</p>
        @endif
        <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
        <p><strong>Total:</strong> {{ count($data) }} participantes</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>DNI</th>
                <th>Paterno</th>
                <th>Materno</th>
                <th>Nombres</th>
                <th>Tipo Personal</th>
                <th>Cargo</th>
                <th>Condición</th>
                <th>Dependencia</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['DNI'] }}</td>
                <td>{{ $row['PATERNO'] }}</td>
                <td>{{ $row['MATERNO'] }}</td>
                <td>{{ $row['NOMBRES'] }}</td>
                <td>{{ $row['TIPO PERSONAL'] }}</td>
                <td>{{ $row['CARGO'] }}</td>
                <td>{{ $row['CONDICION'] }}</td>
                <td>{{ $row['DEPENDENCIA'] }}</td>
                <td>
                    @if ($row['ESTADO'] === 'ANULADO')
                        <span class="badge badge-anulado">ANULADO</span>
                    @elseif ($row['ESTADO'] === 'Observado')
                        <span class="badge badge-observado">OBSERVADO</span>
                    @else
                        <span class="badge badge-activo">ACTIVO</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
