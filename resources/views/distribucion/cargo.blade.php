<!DOCTYPE html>
<html lang="es">
<head>
        <script type="text/php">
            if (typeof pageNumber !== 'undefined') {
                var pageNumber = '<page>' + pageNumber + '</page>';
                if (pageNumber > 1) {
                    var pageCount = '<pagecount>' + pageCount + '</pagecount>';
                    pageNumber += ' / ' + pageCount;
                }
                var y = 15;
                pdf.setFontSize(14);
                pdf.text(pageNumber, 270, y);
            }
        </script>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Cargo en PDF</title>
        <style>
            @page{
                margin: 3.5cm 0.5cm 2.5cm 0.5cm;
                font-family: Arial, Helvetica, sans-serif;
            }
            #header{
                position: fixed;
                height: 100px;
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
                background: #cdcdcd;
            }


        </style>
</head>
<body>
    <div id="header">
        <div style="width: 100%; text-align:center;">
            <table style="width: 100%">
                <tr style="">
                    <td align="right">
                        <img src="{{ public_path('imagenes/logotiny.png')}}"  width="65"
                    </td>
                    <td style="width: 350px;">
                        <div style="text-align: center">
                            <div>UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
                            <div>VICERRECTORADO ACADÉMICO</div>
                            <div>DIRECCIÓN DE ADMISIÓN </div>
                        </div>
                    </td>
                    <td v-align="top" align="left">
                        <img src="{{ public_path('imagenes/logoDAD.png')}}"  width="65">
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div style="text-align: center">
                            <div style="margin-top:0px; font-weight:bold; fort-size:18px;">EXAMEN SIMULACRO</div>
                        </div>

                    </td>
                </tr>
            </table>

        </div>

    </div>
    <div id="footer">

    </div>
    <div class="container">
        <table style="width: 100%;  border-collapse: collapse;">
            <thead style="background-color: #f3f3f3; ">
                <tr style=" border-collapse: collapse; background:white; ">
                    <th style="border: solid 1px black; background: #cdcdcd4D; height:22px;"> N°</th>
                    <th style="border: solid 1px black; background: #cdcdcd4D; height:22px;"> DNI </th>
                    <th style="border: solid 1px black; background: #cdcdcd4D; height:22px;"> APELLIDO Y NOMBRES</th>
                    <th style="border: solid 1px black; background: #cdcdcd4D; height:22px;"> HUELLA DER.</th>
                    <th style="border: solid 1px black; background: #cdcdcd4D; height:22px;"> HUELLA IZQ</th>
                    <th style="border: solid 1px black; background: #cdcdcd4D; height:22px;"> FIRMA</th>
                </tr>
            </thead>
            <tbody>
            @for($i = 0; $i< 100; $i++)
                <tr style="background-color: white;">
                    <td style="border: solid 1px black; width:30px; height:100px;">
                        {{$i}}
                    </td>
                    <td style="border: solid 1px black; width:80px;">
                        70757838
                    </td>>x;">
                        JAUN DLLSFDS SDFASDFAS  ASDFASDFASDFSADF 
                    </td>
                    <td style="border: solid 1px black; width:100px;">
                        
                    </td>
                    <td style="border: solid 1px black; width:100px;">
                        
                    </td>
                    <td style="border: solid 1px black; width:90px;">
                        
                    </td>
                </tr>
            @endfor
            </tbody>
        </table>

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