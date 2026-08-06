<!DOCTYPE html>
<html>
<head>
    <title>CEPREUNA</title>
    <style>

    *{margin:0;}
    .fondo{
        background-image: url('{{ public_path("imagenes/Fondovocacional.png")}}');
        background-size: cover;
        opacity: 0.9;
        font-family: 'Gill Sans Extrabold', Helvetica, sans-serif;
        *{margin:2cm 2cm; padding:65px 80px 0px 85px; }
    }           

    </style>
</head>
<body class="fondo"  style="" >
    <div style="margin-top:55px;">
        <div>
            <table style="width:100%;">
                <tr style="">
                    <td rowspan="1" style="" width="45" >
                        <div align="center" rowspan="1" >
                            <div style="width: 55px;">
                                <img src="{{ public_path('imagenes/logotiny.png')}}"  width="55">
                            </div>
                        </div>
                    </td>
                    <td align="left" style="font-size:11pt; font-weight:700;">
                        <div style="margin-top:5px;">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
                        <div>VICERECTORADO ACADÉMICO</div>
                        <div>DIRECCIÓN DE ADMISIÓN</div>
                    </td>
                    {{-- <td align="center" rowspan="1"> <img src="{{ public_path('imagenes/logoDAD.png')}}"  width="85"></td> --}}
                </tr>
            </table>    
        </div>

        <div style="margin-top: 65px; margin-bottom:28px">  
            <table style="width: 100%">
                <tr>
                    <td align="left">
                        <div style="text-align: left">
                            <span style="font-size:26pt; font-weight:bold">CONSTANCIA DE</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td align="center"><div style="text-align: left; margin-top:-8px;"><span style="font-size:28pt;">EXAMEN VOCACIONAL</span></div></td>
                </tr>
                {{-- <tr>
                    <td align="center"><div style="text-align: center"><span style="font-size:18pt; font-weight:bold">PRE - INSCRIPCIÓN</span></div></td>
                </tr> --}}
            </table>
        </div>


        <div style="margin-top:90px">
            <table style="width:100%;">
                <tr style="height:85px; padding:0;">
                    <td align="center">
                        <div style="text-align: left; margin-bottom:7px;">
                            <span style="font-size:15pt;">Constancia otorgado a:</span>
                        </div>
                    </td>

                    
                </tr>

                <tr style="height:85px; padding:0;">
                    <td align="center">
                        <div style="text-align: left">
                            <span style="font-size:24pt; font-weight:700; text-transform: uppercase;">
                                {{ $data->nombres}} {{ $data->primer_apellido }} {{ $data->segundo_apellido }}
                            </span>
                        </div>
                    </td>
                </tr>

            </table>
        </div>

        <div style="margin-top:30px">
            <table style="font-size:11pt;  width:100%;">

                <tr style="height:85px; padding:0;">
                    <td>
                        <div style="text-align: justify;">
                            <div style="margin-bottom: 0px;">
                                LA DIRECCIÓN DE ADMISIÓN DE LA UNA PUNO HACE CONSTAR:
                            </div>    
                            <p style="line-height:2rem;">
                                Que el Sr(a). <span style="font-weight: bold; text-transform: uppercase;">{{ $data->nombres}} {{ $data->primer_apellido }} {{ $data->segundo_apellido }}</span>, 
                                identificado con DNI: N° <span style="font-weight: bold;">70757838</span>, cumplió satisfactoriamente con 
                                rendir el <span style="font-weight: bold;">EXAMEN VOCACIONAL</span> del 
                                <span style="font-weight: bold;">PROGRAMA DE {{ $data->programa }},</span>
                                requisito para participar en el <span style="font-weight: bold;">EXAMEN {{ $data->proceso }}</span>.
                            </p>
                        </div>
                    </td>   
                </tr>
            </table>
        </div>

        <div style="margin-top:0px">

            <table style="width:100%; bakcground:orange; margin-top:0px">
                <tr style="">
                    <td align="left"> 
                        <div style="margin-top: -40px;"> <span style="font-weight: bold; font-size:1.1rem;" >FECHA y HORA:</span></div>
                        <span>{{ date('d-m-Y H:m:s')}}</span>
                    </td> 
                    <td style="width: 50%"> 
                        <div style=" margin-left: 160px; height: 180px; text-align:center;">
                            <span style="font-size:6pt;"><?php echo DNS2D::getBarcodeHTML('https://admision.unap.edu.pe/verificar/70757373', 'QRCODE',5.5,5.5);?> </span>
                        </div>
                        <div style=" margin-left: 140px; height: text-align:center;">
                            <span style="font-size:6pt;"><?php echo DNS1D::getBarcodeHTML('70757373', 'C128',2.6,44);?> </span>
                        </div>

                    </td> 
                </tr> 
                <tr>
                    <td align="center" style=""> 
                        <div style="text-align:left; margin-left:20px;"> <img src=""  width="150"></div>
                        <div style="margin-top: -40px"><span>Dr. JUAN CARLOS BENAVIDES</span></div>
                        <span>DIRECTOR</span>
                    </td>  
                    <td align="center">
                        <div style=" margin-left:120px; height: 140px; margin-top:15px;" ><img src="{{ public_path('imagenes/firma_ariel.png')}}"  width="170"></div> 
                        <div style="margin-left:120px; margin-top: -20px"><span>Br. ARIEL LUQUE</span></div>
                        <div style="margin-left:120px;"><span>JEFE DE COMPUTO</span></div>
                    </td> 
                </tr>
            </table>
        </div>

    </div>
</body>
</html>