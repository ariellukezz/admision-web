<!DOCTYPE html>
<html>
<head>
    <title>REPORTE - RESUMEN BIOMETRICO {{ date('d/m/Y H:i:s') }}</title>
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
                        <div style="font-size: 1.1rem; font-weight:bold">UNIVERSIDAD NACIONAL DEL ALTIPLANO DE PUNO</div>
                        <div style="font-size: 1rem; font-weight:bold;">VICERRECTORADO ACADÉMICO</div>
                        <div style="font-size: 1rem; ">DIRECCIÓN DE ADMISIÓN</div>
                    </div>
                </td>
                <td align="right" style="border:none;">
                    <img src="{{ public_path('imagenes/logoDAD.jpg') }}" width="75"/>
                </td>
            </tr>
        </table>
        <div style="border-bottom: solid 1px #dddddd; margin-top:0px;"></div>
        <div>
            <div style="text-align: center">
                <span style="font-weight:bold; font-size:1rem; text-transform:uppercase">RESUMEN DE CONTROL BIOMÉTRICO EXAMEN {{ $proceso->nombre }}</span>
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
                    <th rowspan="1" style="width: 30px; text-align:center;">N°</th>
                    @foreach ($groupByColumns as $c)
                    <th><span style="text-transform: uppercase;"> {{$c}}</span></th>
                    @endforeach
                    <th rowspan="1" style="text-align: center; width:80px;">TOTAL</th>
                </tr>
            </thead>
            @if (count($res) > 0)
                <tbody>
                    @foreach ($res as $index => $item)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            @foreach ($groupByColumns as $c)
                            <td>{{ $item->$c }}</td>
                            @endforeach
                            <td style="text-align: center">{{ $item->total }}</td>
                        </tr>
                    @endforeach
                        <tr>
                            <td colspan="{{ count($groupByColumns) + 1 }}"> <span style="font-size: 11pt; font-weight:bold;"> Total</span>  </td>
                            <td style="text-align: center; font-size: 11pt; font-weight:bold;"> {{ $total }}</td>
                        </tr>
                </tbody>
    
            @endif
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

