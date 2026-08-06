<!DOCTYPE html>
<html>
<head>

    <title>RATIO DE POSTULACIÓN</title>
    <style>
            @page{
                margin: 5.3cm 1cm 2.5cm 1cm;
                font-family: Arial, Helvetica, sans-serif;
            }
           #header{
                position: fixed;
                height: 100%;
                width: 100%;
                top: -4.9cm;
                left: 0cm;

            }
            #footer{
                text-align: center;
                width: 100%;
                position: fixed;
                left: 0cm;
                bottom: -2.5cm;
                background: #cdcdcd;
            }
        table {
            border-collapse: collapse;
            width: 100%;
            }

            /* Estilo para las celdas de encabezado */
            th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
            }

            /* Estilo alterno para filas para mejorar la legibilidad */
            tr:nth-child(even) {
            background-color: white;
            }

            /* Estilo al pasar el ratón sobre las filas */
            tr:hover {
            background-color: #f7f7f7;
            }
    </style>

</head>
<body style="font-family: Helvetica, sans-serif; margin-top:-3px">

    <div id="header">
        <div style="width: 100%; text-align:center;">
            <table style="width: 100%">
                <tr style="">
                    <td align="right" style="border:none;" width="50">
                        <div style="margin-top: -5px;">
                            <img src="{{ public_path('imagenes/logotiny.png')}}"  width="65"/>
                        </div>
                    </td>
                    <td style="width: 350px; border:none;">
                        <div style="text-align: left; margin-left:-10px; margin-top: 10px; font-size:10pt;">
                            <div>UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
                            <div>VICERRECTORADO ACADÉMICO</div>
                            <div>DIRECCIÓN DE ADMISIÓN </div>
                        </div>
                    </td>
                    <td v-align="top" align="right" style="text-align:right; border:none;">
                        <div style="margin-top: 5px">
                            <img src="{{ public_path('imagenes/logoDAD.png')}}"  width="75">
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="border-bottom: solid 1px #dddddd; margin-top:0px;">
        </div>
        <div style="width: 100%; text-align:center; margin-top:10px; font-size:10pt;">
            <div>
                <div><span style="font-weight:bold; font-size:1.4em; letter-spacing: .07rem; text-transform:uppercase">EXAMEN {{ $proceso->nombre }} </span></div>
                    RATIO DE POSTULACIÓN POR PROGRAMA DE ESTUDIOS
                <div> {{ date('d/m/Y H:m:s')}} </div>
            </div>
        </div>

    </div>
    <div id="footer">
    </div>

    <div style="margin-top:10px; font-size: .8rem;">
        <table style="width: 100%" class="l-table">
            <thead>
                <tr style="background: #d5d5d5">
                    <th><div style="text-align: center;">N°</div></th>
                    <th><div style="text-align: left;">Programa de estudios</div></th>
                    <th><div style="text-align: center;">Postulantes</div></th>
                    <th><div style="text-align: center;">Vacantes</div></th>
                    <th><div style="text-align: center;">Ratio</div></th>
                    <th><div style="text-align: center;">2da Etapa</div></th>
                </tr>
            </thead>
            @if (count($res) > 0 )
            <tbody>
                @foreach ($res as $index=>$item)
                @if ($index < 8     )
                <tr style="background: #f5f5f5">
                @else
                <tr>
                @endif
                    <td><div style="text-align: center;">{{ $index + 1 }}</div> </td>
                    <td><div style="text-align: left;">{{ $item->programa}}</div> </td>
                    <td><div style="text-align: center;">{{ $item->cantidad}}</div> </td>
                    <td><div style="text-align: center;">{{ $item->vacantes}}</div> </td>
                    <td><div style="text-align: center;">{{ $item->porcentaje_ocupado}}</div> </td>
                    @if ($index < 8 )
                        <td><div style="text-align: center;">{{ $item->veinte_por_ciento}}</div> </td>
                    @else
                        <td><div style="text-align: center;"> </div> </td>
                    @endif
                </tr>   
                @endforeach
            </tbody>
            @else
            <tbody>
                <tr>
                    <td colspan="5">
                        <div style="text-align: center">
                            No se encontraron datos
                        </div>
                    </td>
                </tr>   
            </tbody>

            @endif
            <tr>
                <td colspan="3"><div style="text-align: right; font-weight: bold;">Totales:</div></td>
                <td><div style="text-align: center; font-weight: bold;">{{ $totales['cantidad'] }}</div></td>
                <td><div style="text-align: center; font-weight: bold;">{{ $totales['vacantes'] }}</div></td>
                <td><div style="text-align: center; font-weight: bold;">{{ $totales['suma_veinte_por_ciento_top_8'] }}</div></td>
            </tr>

        </table>
    </div>
    <div id="footer">

    </div>

    <script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                $pdf->text(270, 800, "Pág $PAGE_NUM de $PAGE_COUNT", $font, 10);
            ');
        }
    </script>
</body>
</html>