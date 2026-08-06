<!DOCTYPE html>
<html>
<head>
    <title>DETALLE - PREINSCRIPCION</title>
    <style>
            @page{
                margin: 3.2cm 1cm 2.5cm 1cm;
                font-family: Arial, Helvetica, sans-serif;
                background:pink;
            
            }
           #header{
                position: fixed;
                height: 100px;
                width: 100%;
                top: -2.6cm;
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
        .page-break {
            page-break-before: always;
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
                            <div>SEGUNDAS ESPECIALIDADES </div>
                        </div>
                    </td>
                    <td v-align="top" align="right" style="text-align:right; border:none;">
                        {{-- <div style="margin-top: 5px">
                            <img src="{{ public_path('imagenes/logoDAD.png')}}"  width="75">
                        </div> --}}
                    </td>
                </tr>
            </table>
        </div>
        <div style="border-bottom: solid 1px #dddddd; margin-top:0px;">
        </div>
    </div>

    <div>
    @if (count($datos) > 0 )    
        @foreach ($datos as $item)
        <div style="width: 100%; text-align:center; font-size:10pt;">
            <div>
                <div><span style="font-weight:bold; font-size:1.4em; letter-spacing: .07rem; text-transform:uppercase">{{ $proceso->nombre }} </span></div>
                    POSTULANTES PREINSCRITOS EN LA <span style="text-transform: uppercase"> {{ $item['programa'] }} </span>
                <div> FECHA Y HORA: {{ date('d/m/Y H:m:s')}} </div>
            </div>
        </div>

        <div style="margin-top:10px; font-size: .8rem;">
            <table style="width: 100%" class="l-table">
                <thead>
                    <tr style="background:#e4e4e4;">
                        <th><div style="text-align: center;">N°</div></th>
                        <th><div style="text-align: center;">N° DOC</div></th>
                        <th><div style="text-align: left;">APELLIDOS Y NOMBRES</div></th>
                        <th><div style="text-align: center;">CELULAR</div></th>
                        <th><div style="text-align: center;">EMAIL</div></th>
                    </tr>
                </thead>
                @if (count($item['data']) > 0 )
                <tbody>
                    @foreach ($item['data'] as $index=>$ite)
                    <tr>
                        <td><div style="text-align: center;">{{ $index + 1 }}</div> </td>
                        <td><div style="text-align: center;">{{ $ite['dni']}}</div> </td>
                        <td><div style="text-align: left; text-transform:uppercase;"> {{ $ite['paterno'] }} {{ $ite['materno'] }}, {{ $ite['nombres'] }}</div> </td>
                        <td><div style="text-align: center;">{{ $ite['celular']}}</div> </td>
                        <td><div style="text-align: left; font-style: italic;">{{ strtolower($ite['email'])}}</div> </td>
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

            </table>
        </div>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
        </div>
        @endforeach
        @endif
    </div>
    <div id="footer">

    </div>

    <script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                $pdf->text(410, 560, "Pág $PAGE_NUM de $PAGE_COUNT", $font, 10);
            ');
        }
    </script>
</body>
</html>