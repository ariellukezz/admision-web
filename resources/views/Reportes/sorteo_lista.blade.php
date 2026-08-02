<style>
    .sorteo-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
        table-layout: fixed;
        font-family: 'dejavusanscondensed', sans-serif;
    }
    .sorteo-section-title {
        font-size: 11pt;
        font-weight: 600;
        color: #0f172a;
        padding: 10px 0 6px 0;
        text-align: center;
        letter-spacing: 0.5pt;
        text-transform: uppercase;
    }
    .sorteo-section-title small {
        font-size: 8pt;
        font-weight: 400;
        color: #64748b;
        text-transform: none;
    }
    .sorteo-table thead th {
        background: #f2f7fa;
        color: #0f172a;
        font-weight: 600;
        font-size: 7.5pt;
        text-align: center;
        padding: 7px 4px;
        border-bottom: 1px solid #cbd5e1;
        letter-spacing: 0.3pt;
        text-transform: uppercase;
    }
    .sorteo-table tbody td {
        border-bottom: 1px solid #e2e8f0;
        padding: 5px 4px;
        font-size: 8pt;
        color: #1e293b;
        vertical-align: middle;
        word-wrap: break-word;
    }
    .sorteo-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }
    .col-num { width: 24px; text-align: center; }
    .col-dni { width: 60px; text-align: center; font-weight: 500; }
    .col-nombre { text-align: left; }
    .col-tipo { width: 85px; text-align: center; font-size: 7.5pt; }
    .col-cargo { width: 90px; text-align: center; font-weight: 500; }
    .col-cond { width: 60px; text-align: center; font-size: 7.5pt; }
    .col-dep { width: 100px; font-size: 7.5pt; }
    .col-estado { width: 60px; text-align: center; }
    .badge-estado {
        padding: 1px 8px;
        border-radius: 10px;
        font-size: 7pt;
        font-weight: 600;
    }
    .badge-anulado { background: #fee2e2; color: #dc2626; }
    .badge-observado { background: #fef3c7; color: #d97706; }
    .badge-activo { background: #dcfce7; color: #16a34a; }
    .empty-msg {
        text-align: center;
        margin-top: 40px;
        font-size: 11pt;
        color: #94a3b8;
        font-weight: 300;
    }
</style>

@if ($items->isEmpty())
    <div class="empty-msg">No se encontraron registros.</div>
@else
    <div class="sorteo-section-title">
        {{ $titulo }}
        <br><small>({{ $items->count() }} participantes)</small>
    </div>
    <table class="sorteo-table">
        <thead>
            <tr>
                <th class="col-num">N°</th>
                <th class="col-dni">DNI</th>
                <th class="col-nombre">Apellidos y Nombres</th>
                <th class="col-tipo">Tipo</th>
                <th class="col-cargo">Cargo</th>
                <th class="col-cond">Condición</th>
                <th class="col-dep">Dependencia</th>
                @if ($mostrarEstado)
                <th class="col-estado">Estado</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i => $item)
                <tr>
                    <td class="col-num">{{ $i + 1 }}</td>
                    <td class="col-dni">{{ $item->dni ?? '' }}</td>
                    <td class="col-nombre">{{ $item->paterno ?? '' }} {{ $item->materno ?? '' }}, {{ $item->nombres ?? '' }}</td>
                    <td class="col-tipo">{{ $item->tipo_personal ?? '' }}</td>
                    <td class="col-cargo">{{ $item->cargo ?? '' }}</td>
                    <td class="col-cond">{{ $item->condicion ?? '' }}</td>
                    <td class="col-dep">{{ $item->dependencia ?? '' }}</td>
                    @if ($mostrarEstado)
                    <td class="col-estado">
                        @if ($item->anulado)
                            <span class="badge-estado badge-anulado">ANULADO</span>
                        @elseif ($item->observado)
                            <span class="badge-estado badge-observado">OBSERVADO</span>
                        @else
                            <span class="badge-estado badge-activo">ACTIVO</span>
                        @endif
                    </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
