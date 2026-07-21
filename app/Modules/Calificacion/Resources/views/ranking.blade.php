<!DOCTYPE html>
<html>
<head>
    <title>Resultados por Escuela</title>
    <style>
        @@page {
            margin: 1.5cm 1cm 1.5cm 1cm;
            header: html_pageHeader;
            footer: html_pageFooter;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            padding: 4px 6px;
        }

        thead th {
            background: #1e293b;
            color: #f1f5f9;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid #334155;
            text-align: center;
        }

        tbody td {
            border: 1px solid #cbd5e1;
            font-size: 9pt;
        }

        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .center {
            text-align: center;
        }

        .titulo {
            text-align: center;
            margin: 6px 0 12px 0;
        }

        .titulo h2 {
            margin: 0;
            font-size: 14pt;
        }

        .titulo .fecha {
            font-size: 9pt;
            color: #555;
            margin-top: 2px;
        }

        .program-header {
            background: #e2e8f0;
            font-weight: bold;
            font-size: 10pt;
            padding: 5px 6px;
            border: 1px solid #64748b;
            border-left: none;
            border-right: none;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

<!-- Header fijo para mPDF -->
<htmlpageheader name="pageHeader">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="border:none;" width="80">
                <img src="{{ public_path('imagenes/logotiny.png') }}" width="70">
            </td>
            <td style="border:none; text-align:center;">
                <div style="font-size:14pt; font-family: Times New Roman;">
                    Universidad Nacional del Altiplano
                </div>
                <div style="font-size:10pt;">
                    VICERRECTORADO ACADÉMICO
                </div>
                <div style="font-size:10pt;">
                    DIRECCIÓN DE ADMISIÓN
                </div>
            </td>
            <td style="border:none; text-align:right;" width="80">
            </td>
        </tr>
    </table>
    <div style="border-bottom: 1px solid #000; margin-bottom: 8px;"></div>
</htmlpageheader>

<!-- Footer fijo para mPDF -->
<htmlpagefooter name="pageFooter">
    <div style="text-align: center; font-size: 9pt; color: #555; border-top: 1px solid #ccc; padding-top: 4px;">
        Página {PAGENO} de {nb}
    </div>
</htmlpagefooter>

<div class="titulo">
    <h2>Resultados por Escuela</h2>
    <div class="fecha">{{ $calificacion->nombre ?? '' }} — {{ date('d/m/Y H:i:s') }}</div>
</div>

@foreach ($programas as $idx => $prog)
    @if ($idx > 0)
    <div class="page-break"></div>
    @endif

    <table>
        <thead>
            <tr>
                <th class="center" style="width: 35px;">N°</th>
                <th class="center" style="width: 75px;">DNI</th>
                <th style="text-align: left;">APELLIDOS Y NOMBRES</th>
                <th class="center" style="width: 65px;">PUNTAJE</th>
                <th class="center" style="width: 55px;">APTO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" class="program-header">
                    [{{ $prog['vacantes'] }}] — {{ $prog['nombre'] }}
                    @if(isset($prog['etapa']))
                        ({{ $prog['etapa'] == '1' ? 'Ingreso' : 'Clasificación' }})
                    @endif
                </td>
            </tr>
            @foreach ($prog['postulantes'] as $i => $post)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td class="center">{{ $post->dni }}</td>
                <td>{{ $post->paterno }} {{ $post->materno }}, {{ $post->nombres }}</td>
                <td class="center">{{ number_format($post->puntaje, 3) }}</td>
                <td class="center">
                    @if ($post->apto == 'SI')
                        SI
                    @elseif ($post->apto == 'CL')
                        CL
                    @else
                        NO
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endforeach

</body>
</html>
