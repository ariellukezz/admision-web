<style>
    .resumen-container {
        font-family: 'dejavusanscondensed', sans-serif;
    }
    .resumen-cards {
        width: 100%;
        margin: 10px 0 20px 0;
    }
    .resumen-card {
        display: inline-block;
        width: 22%;
        margin: 0 1%;
        vertical-align: top;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 10px;
        text-align: center;
    }
    .resumen-card .label {
        font-size: 7.5pt;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.3pt;
        margin-bottom: 4px;
    }
    .resumen-card .value {
        font-size: 22pt;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.1;
    }
    .resumen-card .sub {
        font-size: 8pt;
        color: #94a3b8;
        margin-top: 2px;
    }
    .resumen-table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'dejavusanscondensed', sans-serif;
        table-layout: fixed;
    }
    .resumen-table thead th {
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
    .resumen-table tbody td {
        border-bottom: 1px solid #e2e8f0;
        padding: 7px 4px;
        font-size: 8.5pt;
        color: #1e293b;
        vertical-align: middle;
    }
    .resumen-table tbody tr:nth-child(even) {
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
    .resumen-title {
        font-size: 11pt;
        font-weight: 600;
        color: #0f172a;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5pt;
        padding: 8px 0 4px 0;
    }
    .resumen-subtitle {
        font-size: 8pt;
        color: #64748b;
        text-align: center;
        margin-bottom: 12px;
    }
    .col-cargo-r { text-align: left; font-weight: 500; }
    .col-num-r { width: 30px; text-align: center; }
    .col-vac-r { width: 70px; text-align: center; font-weight: 600; }
    .col-asig-r { width: 70px; text-align: center; }
    .col-disp-r { width: 70px; text-align: center; }
    .col-prog-r { width: 120px; }
</style>

<div class="resumen-container">
    <div class="resumen-title">Resumen General de Cargos</div>
    <div class="resumen-subtitle">Sorteo: {{ $sorteo->nombre }} — {{ $proceso->nombre ?? '' }}</div>

    <div class="resumen-cards">
        <div class="resumen-card">
            <div class="label">Cargos</div>
            <div class="value">{{ $configs->count() }}</div>
            <div class="sub">configurados</div>
        </div>
        <div class="resumen-card">
            <div class="label">Vacantes</div>
            <div class="value">{{ $totalVacantes }}</div>
            <div class="sub">total</div>
        </div>
        <div class="resumen-card">
            <div class="label">Asignados</div>
            <div class="value">{{ $totalAsignados }}</div>
            <div class="sub">participantes</div>
        </div>
        <div class="resumen-card">
            <div class="label">Disponibles</div>
            <div class="value">{{ $totalDisponibles }}</div>
            <div class="sub">cupos libres</div>
        </div>
    </div>

    <table class="resumen-table">
        <thead>
            <tr>
                <th class="col-num-r">N°</th>
                <th class="col-cargo-r">Cargo</th>
                <th class="col-vac-r">Vacantes</th>
                <th class="col-asig-r">Asignados</th>
                <th class="col-disp-r">Disponibles</th>
                <th class="col-prog-r">Progreso</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($configs as $i => $config)
                <tr>
                    <td class="col-num-r">{{ $i + 1 }}</td>
                    <td class="col-cargo-r">{{ $config->cargo }}</td>
                    <td class="col-vac-r">{{ $config->cantidad }}</td>
                    <td class="col-asig-r">{{ $config->asignados }}</td>
                    <td class="col-disp-r">{{ $config->disponibles }}</td>
                    <td class="col-prog-r">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $config->porcentaje }}%; background: {{ $config->disponibles == 0 ? '#ef4444' : '#16a34a' }};"></div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
