<!DOCTYPE html>
<html>
<head>

    <title>PDF - REPPORTE - INSCRITOS - PREINSCRITOS - PROGRAMA</title>
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
            background-color: #f5f5f5;
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
                    REPORTE DE PREINSCRIPCIONES - INSCRIPCIONES
                <div> {{ date('d/m/Y H:m:s')}} </div>
            </div>
        </div>

    </div>
    <div id="footer">
    </div>

    <div style="margin-top:10px; font-size: .8rem;">
        <table style="width: 100%" class="l-table">
            <thead>
                <tr>
                    <th><div style="text-align: center;">N°</div></th>
                    <th><div style="text-align: center;">Cod</div></th>
                    <th><div style="text-align: left;">Programa de estudios</div></th>
                    <th><div style="text-align: center;">Inscritos</div></th>
                    <th><div style="text-align: center;">PreInscritos</div></th>
                    <th><div style="text-align: center;">Diferencia</div></th>
                </tr>
            </thead>
            @if (count($res) > 0 )
            <tbody>
                @foreach ($res as $index=>$item)
                <tr>
                    <td><div style="text-align: center;">{{ $index + 1 }}</div> </td>
                    <td><div style="text-align: center; font-weight:bold;">{{ $item->codigo}}</div> </td>
                    <td><div style="text-align: left;">{{ $item->programa}}</div> </td>
                    <td><div style="text-align: center;">{{ $item->inscripciones}}</div> </td>
                    <td><div style="text-align: center;">{{ $item->preinscripciones}}</div> </td>
                    <td><div style="text-align: center;">{{ $item->diferencia}}</div> </td>
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
                <td><div style="text-align: center; font-weight: bold;">{{ $totales[0]->total_inscripciones }}</div></td>
                <td><div style="text-align: center; font-weight: bold;">{{ $totales[0]->total_preinscripciones }}</div></td>
                <td><div style="text-align: center; font-weight: bold;">{{ $totales[0]->total_diferencia }}</div></td>
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