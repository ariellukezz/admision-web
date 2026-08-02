<style>
    .anul-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
        table-layout: fixed;
        font-family: 'dejavusanscondensed', sans-serif;
    }
    .anul-section-title {
        font-size: 11pt;
        font-weight: 600;
        color: #0f172a;
        padding: 10px 0 6px 0;
        text-align: center;
        letter-spacing: 0.5pt;
        text-transform: uppercase;
    }
    .anul-section-title small {
        font-size: 8pt;
        font-weight: 400;
        color: #64748b;
        text-transform: none;
    }
    .anul-table thead th {
        background: #fee2e2;
        color: #991b1b;
        font-weight: 600;
        font-size: 7.5pt;
        text-align: center;
        padding: 7px 4px;
        border-bottom: 1px solid #cbd5e1;
        letter-spacing: 0.3pt;
        text-transform: uppercase;
    }
    .anul-table tbody td {
        border-bottom: 1px solid #e2e8f0;
        padding: 5px 4px;
        font-size: 8pt;
        color: #1e293b;
        vertical-align: middle;
        word-wrap: break-word;
    }
    .anul-table tbody tr:nth-child(even) {
        background-color: #fef2f2;
    }
    .col-num-a { width: 24px; text-align: center; }
    .col-dni-a { width: 60px; text-align: center; font-weight: 500; }
    .col-nombre-a { text-align: left; }
    .col-cargo-a { width: 90px; text-align: center; font-weight: 500; }
    .col-motivo-a { font-size: 7.5pt; }
    .empty-msg {
        text-align: center;
        margin-top: 40px;
        font-size: 11pt;
        color: #94a3b8;
        font-weight: 300;
    }
</style>

@if ($items->isEmpty())
    <div class="empty-msg">No hay participantes anulados.</div>
@else
    <div class="anul-section-title">
        {{ $titulo }}
        <br><small>({{ $items->count() }} participantes)</small>
    </div>
    <table class="anul-table">
        <thead>
            <tr>
                <th class="col-num-a">N°</th>
                <th class="col-dni-a">DNI</th>
                <th class="col-nombre-a">Apellidos y Nombres</th>
                <th class="col-cargo-a">Cargo</th>
                <th class="col-motivo-a">Motivo de Anulación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i => $item)
                <tr>
                    <td class="col-num-a">{{ $i + 1 }}</td>
                    <td class="col-dni-a">{{ $item->dni ?? '' }}</td>
                    <td class="col-nombre-a">{{ $item->paterno ?? '' }} {{ $item->materno ?? '' }}, {{ $item->nombres ?? '' }}</td>
                    <td class="col-cargo-a">{{ $item->cargo ?? '' }}</td>
                    <td class="col-motivo-a">{{ $item->motivo_anulacion ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
