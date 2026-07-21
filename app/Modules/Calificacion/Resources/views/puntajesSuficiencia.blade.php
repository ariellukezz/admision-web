<!DOCTYPE html>
<html>
<head>
    <title>PDF - RESULTADOS</title>
    <style>
        @page{
            margin: 3.5cm 1cm 1.5cm 1cm;
            font-family: Helvetica, Arial, sans-serif;
        }
        #header{
            position: fixed;
            height: 100%;
            width: 100%;
            top: -3cm;
            left: 0cm;
        }
        #footer{
            text-align: center;
            width: 100%;
            position: fixed;
            left: 0cm;
            bottom: -2.5cm;
            background: red;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border-bottom: 0px solid #dddddd;
            text-align: left;
            padding:3px 5px;
        }
        tr:nth-child(even) { background-color: #f5f5f5D8; background: none; }
        tr:hover { background-color: #f7f7f7; }
    </style>

</head>
<body style=" margin-top:-3px">

    <div id="header">
        <div style="width: 100%; text-align:center;">
            <table style="width: 100%">
                <tr style="">
                    <td align="right" style="border:none;" width="50">
                        <div style="margin-top: -5px;">
                            <img src="{{ public_path('imagenes/logotiny.png')}}"  width="65"/>
                        </div>
                    </td>
                    <td style="width: 550px; border:none;">
                        <div style="text-align: left; margin-left:-10px; margin-top: 10px; font-size:10pt; width:100%; text-align:center;">
                            {{-- <div style="font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-style:italic; ">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div> --}}
                            <div style="font-size:2rem; font-family: Times, 'Times New Roman'; font-style:italic;">Universidad Nacional del Altiplano</div>
                            <div style="font-size:1rem; space-letter:.7rem;">VICERRECTORADO ACADÉMICO</div>
                            <div>COMISIÓN DE CONCURSO PÚBLICO DE CATEDRA 2024-II</div>
                        </div>
                    </td>
                    <td v-align="top" align="right" style="text-align:right; border:none;">
                        <div style="margin-top: 5px;">
                            <img src="{{ public_path('imagenes/logo_concurso.jpg')}}"  width="75">
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="border-bottom: solid .5px #000000; margin-top:0px;">
        </div>

    </div>
    <div id="footer">
    </div>

    <div style="width: 100%; text-align:center; margin-top:0px; font-size:10pt;">
        <div>
            <div style="width: 100%; text-align:center; margin-top:-10px;">
                <span style=" padding: 4px 50px; font-size: 1.5rem; font-family:'Times New Roman', Times, serif;"> ESCUELA PROFESIONAL DE  {{ $programa }} </span>
            </div>
            <div> RESULTADOS DE LA PRUEBA ESCRITA DE CONOCIMIENTOS </div>
            <div><span style="font-weight:bold; text-transform:uppercase">{{ "FECHA " }} - {{ date('d/m/Y H:m:s')}} </span></div>
        </div>
    </div>

    <table style="width: 100%; font-size: 9pt;">
        <thead style="border-bottom: .5px solid #000000; border-top: .5px solid #000000;">
            <th>N°</th>
            <th align="center"><div style="text-align:center;">DNI</div></th>
            <th>APELLIDOS Y NOMBRES</th>
            <th><div style="text-align:center;">PUNTAJE</div></th>
            <th><div style="text-align:center;">CONDICION</div></th>
        </thead>
        <tbody style="padding: top:20px;">
            @foreach ($estudiantes as $key=>$estudiante)
            <tr style="font-size:8.8pt;">
                <td style="width: 20px;"><div style="text-align: center;"> {{ $key+1 }} </div></td>
                <td style="width: 50px;"><div style="text-align: center;"> {{ $estudiante['dni'] }} </div></td>
                {{-- <td style="width: 430px;"></td> --}}
                <td> {{ $estudiante['paterno'] }} {{ $estudiante['materno'] }} {{ $estudiante['nombres'] }} </td>
                <td align="center"><div style="text-align:center;"> {{ ($estudiante['puntaje']); }} </div></td>
                <td align="center"><div style="text-align:center;"> {{ ($estudiante['condicion']); }} </div></td>
                
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="width: 100%; border-bottom:solid 1px black;">

    </div>

    <div id="footer">

    </div>

    </div>

    <div id="footer">

    </div>

    <script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                $pdf->text(270, 810, "Pág $PAGE_NUM de $PAGE_COUNT", $font, 9);
            ');
        }
    </script>



</body>
</html>