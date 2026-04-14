<!DOCTYPE html>
<html>
<head>
    <title>RESULTADOS</title>
    <style>
        @page {
            margin: 3.5cm 1cm 2cm 1cm;
            font-family: Helvetica, Arial, sans-serif;
        }

        body {
            font-size: 10pt;
        }

        #header {
            position: fixed;
            top: -3cm;
            left: 0;
            width: 100%;
        }

        #footer {
            position: fixed;
            bottom: -1.5cm;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 9pt;
            color: #555;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            padding: 4px 6px;
        }

        thead {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        .center {
            text-align: center;
        }

        .titulo {
            text-align: center;
            margin-top: 10px;
        }

        .titulo h2 {
            margin: 0;
            font-size: 16pt;
        }

        .titulo h3 {
            margin: 2px 0;
            font-size: 12pt;
        }

    </style>
</head>

<body>

<div id="header">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="border:none;" width="80">
                <img src="{{ public_path('imagenes/logotiny.png') }}" width="70">
            </td>

            <td style="border:none; text-align:center;">
                <div style="font-size:16pt; font-family: Times New Roman;">
                    Universidad Nacional del Altiplano
                </div>
                <div style="font-size:11pt;">
                    VICERRECTORADO ACADÉMICO
                </div>
                <div style="font-size:11pt;">
                    PRORGAMA DE SEGUNDAS ESPECIALIDADES
                </div>
            </td>

            <td style="border:none; text-align:right;" width="80">
                {{-- <img src="{{ public_path('imagenes/logoDAD.jpg') }}" width="70"> --}}
            </td>
        </tr>
    </table>

    <div style="border-bottom: 1px solid #000; margin-top: 5px;"></div>
</div>

<div id="footer">
    Página {PAGENO} de {nb}
</div>

<div class="titulo">
    <h2>Resultados</h2>
    <h3>{{ $programa }}</h3>
    <div>Fecha y hora: {{ date('d/m/Y H:i:s') }}</div>
</div>

<table>
    <thead>
        <tr>
            <th class="center">N°</th>
            <th class="center">DNI</th>
            <th>APELLIDOS Y NOMBRES</th>
            <th class="center">PUNTAJE</th>
            <th class="center">APTO</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($data as $index => $item)
        <tr>
            <td class="center">{{ $index + 1 }}</td>
            <td class="center">{{ $item->nro_doc }}</td>
            <td>
                {{ $item->primer_apellido }}
                {{ $item->segundo_apellido }},
                {{ $item->nombres }}
            </td>
            <td class="center">{{ $item->puntaje }}</td>
            @if ($item->puntaje !== null)
                <td class="center">
                    @if ($item->apto == 'SI' )
                        SI
                    @else
                        NO
                    @endif
                </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>