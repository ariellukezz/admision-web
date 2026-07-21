@php
    $logoIzq = public_path('imagenes/logotiny.png');
    $logoDer = public_path('imagenes/logoDAD.jpg');
    $ordenLabel = $orderBy === 'alfabetico' ? 'Orden Alfabético' : 'Orden de Asiento';
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 3.5cm 1.5cm 1.5cm 1.5cm;
        header: html_listaHeader;
        footer: html_listaFooter;
    }
    body {
        font-family: Helvetica, Arial, sans-serif;
        margin: 0; padding: 0;
        font-size: 8pt;
        color: #1a1a1a;
        line-height: 1.2;
    }

    .aula-titulo {
        width: 100%;
        border-collapse: collapse;
        margin: 0 0 4px 0;
    }
    .aula-titulo td {
        border: none;
        border-bottom: 1px solid #333;
        padding: 0 0 3px 0;
        font-size: 9pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .aula-titulo .aula-info {
        font-size: 7.5pt;
        font-weight: normal;
        text-transform: none;
        letter-spacing: 0;
        color: #555;
        text-align: right;
    }

    .lista-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
    }
    .lista-table thead { display: table-header-group; }
    .lista-table thead th {
        background: #e8e8e8;
        color: #1a1a1a;
        border: 1px solid #999;
        padding: 4px 6px;
        font-size: 9pt;
        font-weight: bold;
        text-align: center;
        text-transform: uppercase;
    }
    .lista-table tbody td {
        border: 1px solid #bbb;
        padding: 2px 6px;
        vertical-align: middle;
        font-size: 8pt;
    }
    .lista-table tbody tr:nth-child(even) td { background-color: #f5f5f5; }

    .col-num { width: 30px; text-align: center; font-weight: bold; }
    .col-dni { width: 80px; text-align: center; font-family: 'Courier New', monospace; }
    .col-asiento { width: 70px; text-align: center; font-weight: bold; }
</style>
</head>
<body>

<htmlpageheader name="listaHeader">
    <table style="width:100%;border:none;border-collapse:collapse;">
        <tr>
            <td style="border:none;padding:0;text-align:right;width:55px;">
                @if (file_exists($logoIzq))
                    <img src="{{ $logoIzq }}" width="50"/>
                @endif
            </td>
            <td style="width:100%;text-align:center;border:none;padding:0;">
                <div style="text-align:center;">
                    <div style="font-size:10pt;font-weight:bold;margin:1px 0;">UNIVERSIDAD NACIONAL DEL ALTIPLANO DE PUNO</div>
                    <div style="font-size:9pt;font-weight:bold;margin:1px 0;">VICERRECTORADO ACADÉMICO</div>
                    <div style="font-size:9pt;font-weight:bold;margin:1px 0;">DIRECCIÓN DE ADMISIÓN</div>
                </div>
            </td>
            <td style="border:none;padding:0;text-align:left;width:65px;">
                @if (file_exists($logoDer))
                    <img src="{{ $logoDer }}" width="60"/>
                @endif
            </td>
        </tr>
    </table>
    <div style="border-bottom:1.5px solid #333;margin-top:4px;"></div>
    <div style="text-align:center;margin-top:4px;">
        <div style="font-size:11pt;font-weight:bold;text-transform:uppercase;margin:2px 0;color:#1a1a1a;">Lista de Estudiantes</div>
        <div style="font-size:8pt;color:#555;margin:1px 0;">{{ $ordenLabel }} | Grupo #{{ $filterGroupId }} | {{ date('d/m/Y H:i:s') }}</div>
    </div>
</htmlpageheader>

<htmlpagefooter name="listaFooter">
    <div style="text-align:center;font-size:8pt;color:#888;">Pág. {PAGENO} de {nb}</div>
</htmlpagefooter>

@foreach ($classrooms as $c)
    <table class="aula-titulo">
        <tr>
            <td>{{ $c->nombre }}</td>
            <td class="aula-info">Capacidad: {{ $c->capacidad }} | Asignados: {{ $c->assignments->count() }}</td>
        </tr>
    </table>

    <table class="lista-table">
        <thead>
            <tr>
                <th class="col-num">N°</th>
                <th class="col-dni">DNI</th>
                <th>Apellidos y Nombres</th>
                <th class="col-asiento">Orden de Asiento</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @foreach ($c->assignments as $a)
                <tr>
                    <td class="col-num">{{ $counter }}</td>
                    <td class="col-dni">{{ $a->n_documento }}</td>
                    <td>{{ $a->paterno . ' ' . $a->materno . ', ' . $a->nombres }}</td>
                    <td class="col-asiento">{{ $a->posicion }}</td>
                </tr>
                @php $counter++; @endphp
            @endforeach
        </tbody>
    </table>

    <div style="page-break-after:always"></div>
@endforeach
</body>
</html>
