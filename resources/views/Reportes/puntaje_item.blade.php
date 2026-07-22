<style>
    .puntaje-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
        table-layout: fixed;
        font-family: 'dejavusanscondensed', sans-serif;
    }
    .puntaje-header {
        font-size: 12pt;
        font-weight: 600;
        color: #0f172a;
        padding: 10px 0 6px 0;
        text-align: center;
        background: none;
        border: none;
        letter-spacing: 0.5pt;
        text-transform: uppercase;
    }
    .puntaje-header small {
        font-size: 9pt;
        font-weight: 400;
        color: #64748b;
        text-transform: none;
        letter-spacing: 0.3pt;
    }
    .puntaje-table thead th {
        background: #f2f7fa;
        color: #0f172a;
        font-weight: 600;
        font-size: 7.5pt;
        text-align: center;
        padding: 8px;
        border: 0px solid #cbd5e1;
        border-bottom: none;
        border-top: none;
        letter-spacing: 0.3pt;
        text-transform: uppercase;
    }
    .puntaje-table thead th:last-child { border-right: none; }
    .puntaje-table thead th:first-child { border-left: none; }
    .puntaje-table tbody td {
        border-bottom: 1px solid #e2e8f0;
        padding: 5px 4px;
        font-size: 8pt;
        color: #1e293b;
        vertical-align: middle;
        word-wrap: break-word;
    }
    .puntaje-table tbody tr:nth-child(even) { background-color: #f8fafc; }
    .col-numero { width: 30px; text-align: center; }
    .col-dni { width: 70px; text-align: center; font-weight: 500; }
    .col-nombre { text-align: left; }
    .col-modalidad { width: 90px; text-align: center; font-size: 7.5pt; }
    .col-puntaje { width: 55px; text-align: center; font-weight: 600; }
    .col-vocacional { width: 55px; text-align: center; font-weight: 600; }
    .col-apto { width: 45px; text-align: center; }
    .col-puesto { width: 50px; text-align: center; font-weight: 600; }
    .badge-modalidad {
        background: #e2e8f0;
        padding: 1px 10px;
        border-radius: 12px;
        font-size: 7pt;
        font-weight: 500;
        color: #1e293b;
    }
    .puntaje-destacado { color: #096dd9; font-weight: 700; }
    .vocacional-destacado { color: #722ed1; font-weight: 600; }
    .puesto-destacado { background: #f1f5f9; padding: 1px 6px; border-radius: 4px; font-weight: 600; }
    .empty-msg {
        text-align: center;
        margin-top: 60px;
        font-size: 12pt;
        color: #94a3b8;
        font-weight: 300;
    }
</style>

@if ($postulantes->isEmpty())
    <div class="empty-msg">No se encontraron registros.</div>
@else
    <table class="puntaje-table">
        <thead>
            <tr>
                <td colspan="8" class="puntaje-header">
                    {{ $programa }}
                    <br>
                    <small>({{ count($postulantes) }} registros)</small>
                </td>
            </tr>
            <tr>
                <th class="col-numero">N°</th>
                <th class="col-dni">DNI</th>
                <th class="col-nombre">Apellidos y Nombres</th>
                <th class="col-modalidad">Modalidad</th>
                <th class="col-puntaje">Punt.</th>
                <th class="col-vocacional">Voc.</th>
                <th class="col-apto">Apto</th>
                <th class="col-puesto">Puesto</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($postulantes as $i => $p)
                <tr>
                    <td class="col-numero">{{ $i + 1 }}</td>
                    <td class="col-dni">{{ $p->dni ?? '' }}</td>
                    <td class="col-nombre">{{ $p->paterno ?? '' }} {{ $p->materno ?? '' }}, {{ $p->nombres ?? '' }}</td>
                    <td class="col-modalidad">
                        <span class="badge-modalidad">{{ $p->modalidad ?? '' }}</span>
                    </td>
                    <td class="col-puntaje">
                        <span class="puntaje-destacado">{{ number_format($p->puntaje ?? 0, 3) }}</span>
                    </td>
                    <td class="col-vocacional">
                        <span class="vocacional-destacado">{{ number_format($p->puntaje_vocacional ?? 0, 3) }}</span>
                    </td>
                    <td class="col-apto">
                        @if (($p->apto ?? '') === 'SI')
                            <span style="color: #52c41a; font-weight: 600;">SI</span>
                        @else
                            <span style="color: #fa8c16; font-weight: 600;">NO</span>
                        @endif
                    </td>
                    <td class="col-puesto">
                        <span class="puesto-destacado">{{ $p->puesto ?? '' }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
