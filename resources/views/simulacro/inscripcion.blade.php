<!DOCTYPE html>
<html>
<head>
    <title>Constancia de inscripción simulacro</title>
    <style>
        .{
            margin: 0px;
            padding: 0px;
        }
        @font-face {
            font-family: 'Garet Book';
            src: url('{{ asset('fonts/Garet-Book.ttf') }}') format('truetype');
            font-weight: normal;
        }
        @font-face {
            font-family: 'Garet Heavy';
            src: url('{{ asset('fonts/Garet-Heavy.ttf') }}') format('truetype');
            font-weight: normal;
        }
        .individual-letters {
            font-size: 36px;
            font-weight: bold;
            color: white; /* Color del texto */
            -webkit-text-stroke: 2px black;
        }
    </style>
    
</head>
<body style="font-family: Helvetica, sans-serif; margin-top:-3px">
    <div style="position: relative;">
        <img src="{{ $fondo }}" alt="foto" width="100%"> 
    </div>
    {{-- <div style="position: absolute; width:900px; top:700px; left:100px; ">
        <div style=" transform: rotate(-45deg); transform-origin: 0% 100%; text-align: center; font-weight:bold; padding: 10px; font-size:100pt;">
            <div class="individual-letters">
                <span>H</span>
                <span>e</span>
                <span>l</span>
                <span>l</span>
                <span>o</span>
              </div>
        </div>

    </div> --}}
    <div style="position:absolute; top:26px; width:100%;">
        <div style="text-align:center; font-weight:bold; font-size:14pt;">
            <div><span>UNIVERSIDAD NACIONAL DEL ALTIPLANO</span></div>
            <div><span>VICERRECTORADO ACADÉMICO</span></div>
            <div><span>DIRECCIÓN DE ADMISIÓN</span></div>
        </div>

        <div style="text-align:center; margin-top:30px; font-weight:bold; font-size:16pt; font-family: Times, serif">
            <div><span>CONSTANCIA DE INSCRIPCIÓN</span></div>
            <div><span>EXAMEN SIMULACRO 2023</span></div>
        </div>
        <div style="font-size:11pt; font-family: 'Helvetica'; margin-top:10px; font-weigth:normal; margin-left:1.2cm; margin-right:1.2cm; line-height:0.9rem; letter-spacing:0.088rem; line-height:1.7rem;">
            <table>
                <tr>
                    <td>MODALIDAD</td>
                    <td><div style="width: 36px; text-align:center;">:</div></td>
                    <td>EXAMEN SIMULACRO</td>
                </tr>
                <tr>
                    <td>PROGRAMA DE ESTUDIOS</td>
                    <td><div style="width: 36px; text-align:center;">:</div></td>
                    <td>{{$datos->programa}}</td>
                </tr>
                <tr>
                    <td>NRO. DE DOCUMENTO</td>
                    <td><div style="width: 36px; text-align:center;">:</div></td>
                    <td>{{$datos->nro_doc}}</td>
                </tr>
                <tr>
                    <td>PRIMER APELLIDO</td>
                    <td><div style="width: 36px; text-align:center;">:</div></td>
                    <td>{{$datos->paterno}}</td>
                </tr>
                <tr>
                    <td>SEGUNDO APELLIDO</td>
                    <td><div style="width: 36px; text-align:center;">:</div></td>
                    <td>{{$datos->materno}}</td>
                </tr>
                <tr>
                    <td>PRENOMBRES</td>
                    <td><div style="width: 36px; text-align:center;">:</div></td>
                    <td>{{$datos->nombres}}</td>
                </tr>
            </table>
        </div>
        <div style="text-align:justify; margin-top:10px; margin-left:1.2cm; margin-right:1cm; font-size:12pt; font-size:12pt; font-family: 'Helvetica';">
            <div>
                <p style="letter-spacing:0.088rem; line-height:1.7rem;">
                    Ha sido debidamente inscrito(a) para participar en el <span style="font-weight: bold; font-style:italic; font-family: 'Helvetica'">“Simulacro de Admisión 2023”</span>, a
                    llevarse a cabo el día sábado 11 de noviembre del año en curso a 11:00 horas, dentro del
                    recinto universitario.
                </p>
            </div>
        </div>

        <div style="text-align:justify; margin-top:10px; margin-left:1.2cm; margin-right:1cm; font-size:12pt; font-family: 'Helvetica';">
            <div>
                <span style="font-weight:bold;">DECLARACIÓN JURADA</span>
            </div>
            <div>
                <p style="letter-spacing:0.088rem; line-height:1.7rem;">
                    El que suscribe, declara bajo juramento que la información proporcionada durante el
                    proceso de inscripción es veraz y de mi entera responsabilidad; así mismo, acepto una
                    rigurosa revisión física y exhaustiva para acceder a la Ciudad Universitaria.
                </p>
            </div>
        </div>

        <div style="text-align:justify; margin-top:10px; margin-left:1.2cm; margin-right:1cm; font-size:12pt; font-family: 'Helvetica';">
            <div>
                <span style="font-weight:bold;">RECOMENDACIONES PARA PRESENTARSE EN EL DÍA DEL EXAMEN SIMULACRO</span>
            </div>
            <div style="letter-spacing:0.088rem; line-height:1.7rem; margin-left:.7cm">
                <ul>
                    <li>Documento Nacional de Identidad original (DNI).</li>
                    <li>Constancia de Inscripción.</li>
                    <li>Lápiz 2B, borrador y tajador.</li>
                    <li>De preferencia vestir con buzo sin capucha ni metales.</li>
                    <li>Visible el cuello y las orejas.</li>
                    <li>Cabello recogido (mujeres) y cabello corto (varones).</li>
                </ul>
            </div>
        </div>

        <div style="text-align:justify; margin-top:10px; margin-left:1.2cm; margin-right:1cm; font-size:12pt; font-family: 'Helvetica';">
            <div>
                <span style="font-weight:bold;">IMPORTANTE:</span>
            </div>
            <div>
                <p style="letter-spacing:0.088rem; line-height:1.7rem;">
                    La participación en este simulacro <span style="font-weight:bold;">NO OTORGA DERECHO ALGUNO A LA OBTENCIÓN DE
                    UNA VACANTE</span> en la Universidad Nacional del Altiplano - Puno.
                </p>
            </div>
        </div>

        <div style="text-align:justify; margin-top:40px; margin-left:1.2cm; margin-right:1cm; font-size:12pt; font-family: 'Helvetica';">
            <table style="width: 100%;">
                <tr>
                    <td align="center" rowspan="1"> 
                        <div style="margin-left: 50px; margin-top:-15px;">
                            <img src="{{ public_path('imagenes/logoDAD.png')}}"  width="65">
                        </div>
                    </td>
                    <td>
                        <div style="margin-left: -5px; margin-right: 60px;">
                            <div>DIRECCIÓN DE ADMISIÓN</div>
                            <div>UNA PUNO</div>
                        </div>      
                    </td>
                    <td>
                        <div style="text-align: center; margin-top:-20px;">
                            <div>_________________</div>
                            <div>POSTULANTE</div>
                        </div>
                    </td>
                    <td style="width:120px;">
                        <div style="text-align: center; font-size:.8rem;">
                            <div style="margin-left:20px; width: 80px; height:100px; border:solid 1px black;"></div>
                            <div>Indice derecho</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</body>
</html>