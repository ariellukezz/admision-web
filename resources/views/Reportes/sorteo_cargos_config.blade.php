<style>
    .config-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'dejavusanscondensed', sans-serif;
        table-layout: fixed;
    }
    .config-title {
        font-size: 11pt;
        font-weight: 600;
        color: #0f172a;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5pt;
        padding: 8px 0 4px 0;
    }
    .config-subtitle {
        font-size: 8pt;
        color: #64748b;
        text-align: center;
        margin-bottom: 12px;
    }
    .config-table thead th {
        background: #f2f7fa;
        color: #0f172a;
        font-weight: 600;
        font-size: 8pt;
        text-align: center;
        padding: 8px 4px;
        border-bottom: 1px solid #cbd5e1;
        text-transform: uppercase;
        letter-spacing: 0.3pt;
    }
    .config-table tbody td {
        border-bottom: 1px solid #e2e8f0;
        padding: 7px 4px;
        font-size: 8.5pt;
        color: #1e293b;
        vertical-align: middle;
    }
    .config-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }
    .progress-bar {
        width: 100%;
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        border-radius: 4px;
    }
</style>

<div class="config-title">Configuracion de Cargos</div>
<div class="config-subtitle">Sorteo: {{ $sorteo->nombre }} - {{ $proceso->nombre ?? '' }}</div>

<table class="config-table">
    <thead>
        <tr>
            <th style="width: 30px; text-align: center;">N</th>
            <th style="text-align: left;">Cargo</th>
            <th style="width: 70px; text-align: center;">Vacantes</th>
            <th style="width: 70px; text-align: center;">Asignados</th>
            <th style="width: 70px; text-align: center;">Disponibles</th>
            <th style="width: 120px;">Progreso</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($configs as $i => $config)
            <tr>
                <td style="text-align: center;">{{ $i + 1 }}</td>
                <td style="text-align: left; font-weight: 500;">{{ $config->cargo }}</td>
                <td style="text-align: center; font-weight: 600;">{{ $config->cantidad }}</td>
                <td style="text-align: center;">{{ $config->asignados }}</td>
                <td style="text-align: center;">{{ $config->disponibles }}</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $config->disponibles == 0 ? 100 : ($config->cantidad > 0 ? round(($config->asignados / $config->cantidad) * 100) : 0) }}%; background: {{ $config->disponibles == 0 ? '#ef4444' : '#16a34a' }};"></div>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
