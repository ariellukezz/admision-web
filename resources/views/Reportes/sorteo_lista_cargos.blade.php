<style>
    .cargo-block { margin-bottom: 14px; }
    .cargo-header {
        background: #334155;
        color: #fff;
        font-size: 9.5pt;
        font-weight: 700;
        padding: 6px 10px;
        border-radius: 4px 4px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .cargo-header .cargo-meta {
        font-size: 7.5pt;
        font-weight: 400;
        opacity: 0.85;
    }
    .cargo-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-family: 'dejavusanscondensed', sans-serif;
    }
    .cargo-table thead th {
        background: #f2f7fa;
        color: #0f172a;
        font-weight: 600;
        font-size: 7.5pt;
        text-align: center;
        padding: 5px 4px;
        border-bottom: 1px solid #cbd5e1;
        letter-spacing: 0.3pt;
        text-transform: uppercase;
    }
    .cargo-table tbody td {
        border-bottom: 1px solid #e2e8f0;
        padding: 4px 4px;
        font-size: 8pt;
        color: #1e293b;
        vertical-align: middle;
        word-wrap: break-word;
    }
    .cargo-table tbody tr:nth-child(even) { background-color: #f8fafc; }
    .col-num { width: 24px; text-align: center; }
    .col-dni { width: 60px; text-align: center; font-weight: 500; }
    .col-nombre { text-align: left; }
    .col-tipo { width: 80px; text-align: center; font-size: 7.5pt; }
    .col-cond { width: 60px; text-align: center; font-size: 7.5pt; }
    .col-dep { width: 100px; font-size: 7.5pt; }
    .col-estado { width: 55px; text-align: center; }
    .badge-estado { padding: 1px 6px; border-radius: 8px; font-size: 6.5pt; font-weight: 600; }
    .badge-anulado { background: #fee2e2; color: #dc2626; }
    .badge-observado { background: #fef3c7; color: #d97706; }
    .badge-activo { background: #dcfce7; color: #16a34a; }
    .anulados-title {
        font-size: 8pt;
        font-weight: 600;
        color: #dc2626;
        padding: 8px 0 4px 0;
        text-transform: uppercase;
    }
</style>

@foreach ($configs as $config)
    @php
        $cargoNombre = $config->cargo;
        $items = $activosPorCargo->get($cargoNombre, collect());
        $anulados = $anuladosPorCargo->get($cargoNombre, collect());
    @endphp

    <div class="cargo-block">
        <div class="cargo-header">
            <span>{{ $cargoNombre }}</span>
            <span class="cargo-meta">
                Vacantes: {{ $config->cantidad }} |
                Asignados: {{ $config->asignados }} |
                Disponibles: {{ $config->disponibles }}
            </span>
        </div>

        @if ($items->isEmpty())
            <div style="text-align:center; padding:10px; font-size:8pt; color:#94a3b8; border:1px solid #e2e8f0; border-top:none;">
                Sin participantes asignados
            </div>
        @else
            <table class="cargo-table">
                <thead>
                    <tr>
                        <th class="col-num">N°</th>
                        <th class="col-dni">DNI</th>
                        <th class="col-nombre">Apellidos y Nombres</th>
                        <th class="col-tipo">Tipo</th>
                        <th class="col-cond">Condición</th>
                        <th class="col-dep">Dependencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $i => $item)
                        <tr>
                            <td class="col-num">{{ $i + 1 }}</td>
                            <td class="col-dni">{{ $item->dni ?? '' }}</td>
                            <td class="col-nombre">{{ $item->paterno ?? '' }} {{ $item->materno ?? '' }}, {{ $item->nombres ?? '' }}</td>
                            <td class="col-tipo">{{ $item->tipo_personal ?? '' }}</td>
                            <td class="col-cond">{{ $item->condicion ?? '' }}</td>
                            <td class="col-dep">{{ $item->dependencia ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if ($anulados->isNotEmpty())
            <div class="anulados-title">Anulados ({{ $anulados->count() }})</div>
            <table class="cargo-table">
                <thead>
                    <tr>
                        <th class="col-num">N°</th>
                        <th class="col-dni">DNI</th>
                        <th class="col-nombre">Apellidos y Nombres</th>
                        <th class="col-tipo">Tipo</th>
                        <th class="col-cond">Condición</th>
                        <th class="col-dep">Dependencia</th>
                        <th class="col-estado">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($anulados as $i => $item)
                        <tr>
                            <td class="col-num">{{ $i + 1 }}</td>
                            <td class="col-dni">{{ $item->dni ?? '' }}</td>
                            <td class="col-nombre">{{ $item->paterno ?? '' }} {{ $item->materno ?? '' }}, {{ $item->nombres ?? '' }}</td>
                            <td class="col-tipo">{{ $item->tipo_personal ?? '' }}</td>
                            <td class="col-cond">{{ $item->condicion ?? '' }}</td>
                            <td class="col-dep">{{ $item->dependencia ?? '' }}</td>
                            <td class="col-estado">
                                @if ($item->anulado)
                                    <span class="badge-estado badge-anulado">ANULADO</span>
                                @elseif ($item->observado)
                                    <span class="badge-estado badge-observado">OBSERVADO</span>
                                @else
                                    <span class="badge-estado badge-activo">ACTIVO</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endforeach
