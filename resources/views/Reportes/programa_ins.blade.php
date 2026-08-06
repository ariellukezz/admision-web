<!DOCTYPE html>
<html>
<head>
    <title>REPORTE - INSCRITOS - PROGRAMA {{ date('d/m/Y H:i:s') }}</title>
    <style>
        @page {
            margin: 4.2cm 1cm 2.5cm 1cm;
            font-family: Arial, Helvetica, sans-serif;
        }
        #header {
            position: fixed;
            width: 100%;
            top: -4cm;
            left: 0cm;
        }
        #footer {
            text-align: center;
            width: 100%;
            position: fixed;
            left: 0cm;
            bottom: -2.0cm;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        tr:hover {
            background-color: #f7f7f7;
        }
        .vertical-text {
            writing-mode: vertical-rl;
            text-align: center;
            white-space: nowrap;
            padding: 5px;
            font-weight: bold;
            width: 26px;
        }
</style>
</head>
<body style="font-family: Helvetica, sans-serif; margin-top:-3px">
    <div id="header">
        <table style="width: 100%">
            <tr>
                <td align="right" style="border:none;" width="50">
                    <img src="{{ public_path('imagenes/logotiny.png') }}" width="65"/>
                </td>
                <td style="width: 532px; border:none;">
                    <div style="text-align: center; font-size:10pt;">
                        <div style="font-size: 1.1rem; font-weight:bold">UNIVERSIDAD NACIONAL DEL ALTIPLANO VICERRECTORADO ACADÉMICO</div>
                        <div style="font-size: 1rem; ">VICERRECTORADO ACADÉMICO</div>
                        <div>DIRECCIÓN DE ADMISIÓN</div>
                    </div>
                </td>
                <td align="right" style="border:none;">
                    <img src="{{ public_path('imagenes/logoDAD.png') }}" width="75"/>
                </td>
            </tr>
        </table>
        <div style="border-bottom: solid 1px #dddddd; margin-top:0px;"></div>
        <div>
            <div style="text-align: center">
                <span style="font-weight:bold; font-size:1rem; text-transform:uppercase"> RESUMEN DE INSCRIPCIONES DIARIAS - EXAMEN {{ $proceso->nombre }}</span>
            </div>
            <div style="font-size:10pt; text-align:center;" > FECHA Y HORA : {{ date('d/m/Y H:i:s') }}</div>
        </div>
        <div style=" font-size:10pt; margin-top:10px;">
            {{-- <div style="text-align:left; letter-spacing: 0.03rem;">RESUMEN DE INSCRIPCIONES DIARIAS</div> --}}
            {{-- <div style="text-align:left;" > FECHA Y HORA : {{ date('d/m/Y H:i:s') }}</div> --}}
        </div>
    </div>
    <div id="footer"></div>
    <div style="font-size: .7rem;">
        <table>
            <thead>
                <tr style="background:#c9c9c9;">
                    <th rowspan="2">N°</th>
                    <th rowspan="2">COD</th>
                    <th rowspan="2">PROGRAMA DE ESTUDIOS</th>
                    @php
                        setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'Spanish');
                        $meses = [];
                        foreach ($fechas as $fec) {
                            $mes = date('m', strtotime($fec));
                            $meses[$mes][] = $fec;
                        }
                    @endphp
                    @foreach ($meses as $mes => $dias)
                        <th colspan="{{ count($dias) }}" style="text-align: center;">
                            {{ ucfirst(strftime('%B', mktime(0, 0, 0, $mes, 1))) }}
                        </th>
                    @endforeach
                    <th rowspan="2">TOTAL</th>
                </tr>
                <tr style="background:#cdcdcd">
                    @foreach ($meses as $dias)
                        @foreach ($dias as $fec)
                            <th class="vertical-text">{{ date('d', strtotime($fec)) }}</th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            @if (count($res) > 0)
                <tbody>
                    @foreach ($res as $index => $item)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td style="text-align: center; font-weight:bold;">{{ $item->codigo }}</td>
                            <td>{{ $item->programa }}</td>
                            @foreach ($fechas as $fec)
                                <td style="text-align: center;">{{ $item->{'ins_' . $fec} }}</td>
                            @endforeach
                            <td style="text-align: center;">{{ $item->inscripciones }}</td>
                        </tr>
                    @endforeach
                </tbody>
            @else
                <tbody>
                    <tr>
                        <td colspan="{{ count($fechas) + 4 }}" style="text-align: center;">No se encontraron datos</td>
                    </tr>
                </tbody>
            @endif
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">Totales:</td>
                @foreach ($fechas as $fec)
                    <td style="text-align: center; font-weight: bold;">{{ $totales[0]->{'ins_' . $fec} }}</td>
                @endforeach
                <td style="text-align: center; font-weight: bold;">{{ $totales[0]->total_inscripciones }}</td>
            </tr>
        </table>
    </div>
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "light");
                $pdf->text(270, 810, "Pág $PAGE_NUM de $PAGE_COUNT", $font, 9);
            ');
        }
    </script>
</body>
</html>

