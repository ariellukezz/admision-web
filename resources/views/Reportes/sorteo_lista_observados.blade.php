<style>
    .obs-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
        table-layout: fixed;
        font-family: 'dejavusanscondensed', sans-serif;
    }
    .obs-section-title {
        font-size: 11pt;
        font-weight: 600;
        color: #0f172a;
        padding: 10px 0 6px 0;
        text-align: center;
        letter-spacing: 0.5pt;
        text-transform: uppercase;
    }
    .obs-section-title small {
        font-size: 8pt;
        font-weight: 400;
        color: #64748b;
        text-transform: none;
    }
    .obs-table thead th {
        background: #fef3c7;
        color: #92400e;
        font-weight: 600;
        font-size: 7.5pt;
        text-align: center;
        padding: 7px 4px;
        border-bottom: 1px solid #cbd5e1;
        letter-spacing: 0.3pt;
        text-transform: uppercase;
    }
    .obs-table tbody td {
        border-bottom: 1px solid #e2e8f0;
        padding: 5px 4px;
        font-size: 8pt;
        color: #1e293b;
        vertical-align: middle;
        word-wrap: break-word;
    }
    .obs-table tbody tr:nth-child(even) {
        background-color: #fffbeb;
    }
    .col-num-o { width: 24px; text-align: center; }
    .col-dni-o { width: 60px; text-align: center; font-weight: 500; }
    .col-nombre-o { text-align: left; }
    .col-cargo-o { width: 90px; text-align: center; font-weight: 500; }
    .col-obs-o { font-size: 7.5pt; }
    .empty-msg {
        text-align: center;
        margin-top: 40px;
        font-size: 11pt;
        color: #94a3b8;
        font-weight: 300;
    }
</style>

@if ($items->isEmpty())
    <div class="empty-msg">No hay participantes observados.</div>
@else
    <div class="obs-section-title">
        {{ $titulo }}
        <br><small>({{ $items->count() }} participantes)</small>
    </div>
    <table class="obs-table">
        <thead>
            <tr>
                <th class="col-num-o">N°</th>
                <th class="col-dni-o">DNI</th>
                <th class="col-nombre-o">Apellidos y Nombres</th>
                <th class="col-cargo-o">Cargo</th>
                <th class="col-obs-o">Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i => $item)
                <tr>
                    <td class="col-num-o">{{ $i + 1 }}</td>
                    <td class="col-dni-o">{{ $item->dni ?? '' }}</td>
                    <td class="col-nombre-o">{{ $item->paterno ?? '' }} {{ $item->materno ?? '' }}, {{ $item->nombres ?? '' }}</td>
                    <td class="col-cargo-o">{{ $item->cargo ?? '' }}</td>
                    <td class="col-obs-o">{{ $item->observacion ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
