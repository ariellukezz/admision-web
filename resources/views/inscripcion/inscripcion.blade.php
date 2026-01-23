<!DOCTYPE html>
<html>
<head>
    <title>Constancia de inscripción</title>
    <style>
        *{ font-family: 'Roboto Condensed', sans-serif; }
        .truncate-text {
            width: 340px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: -8px;
        }
    </style>

</head>
<body style="font-family: 'Roboto Condensed'; font-weigth:200; margin-top:125px; font-size:12pt; ">
    <div>
        {{-- <div style="display:block"> <h1>Hora actual: {{ date('H:i:s') }}</h1></div> --}}
        <div>
            <table>
                <tr>
                    <td width="100"></td>
                    <td align="center">
                        <div>
                        <div style="text-align: center"><span style="font-weight:bold; letter-spacing:.11rem; "> CONSTANCIA DE INSCRIPCIÓN AL EXAMEN {{$data->proceso}}</span></div>
                        <div style="display: none;"> {{ $nro_carreras = count($carreras_previas) }}  </div>
                        @if ($nro_carreras == 1 )
                            <div style=" font-weight:bold; text-transform:uppercase; font-family:'helvetica';  font-size:10pt; letter-spacing:.16rem">(Postulante a segunda carrera)</div>
                        @elseif ($nro_carreras == 2 )
                            <div style="text-transform:uppercase; font-family:'Roboto Condensed';  font-size:10pt; letter-spacing:.06rem">(Postulante a tercera carrera)</div>
                        @elseif ($nro_carreras == 3 )
                            <div style="text-transform:uppercase; font-family:'Roboto Condensed';  font-size:10pt; letter-spacing:.06rem">(Postulante a cuarta carrera)</div>
                        @elseif ($nro_carreras == 4 )
                            <div style="text-transform:uppercase; font-family:'Roboto Condensed';  font-size:10pt; letter-spacing:.06rem">(Postulante a quinta carrera)</div>
                        @elseif ($nro_carreras == 5 )
                            <div style="text-transform:uppercase; font-family:'Roboto Condensed';  font-size:10pt; letter-spacing:.06rem">(Postulante a sexta carrera)</div>
                        @else
                            <div style="height: 14px;"></div>
                        @endif
                    </td>
                    <td width="100"></td>
                </tr>
             </table>
        </div>

        <div style="margin-top:6px;">
            <table style="font-size:10pt; font-weight:bold; width:100%;">
                <tr style="height:85px; padding:0;">
                    <td style="" width="180px" align="left" valign="top">MODALIDAD</td>
                    <td style="" width="5px" align="left" valign="top">:</td>
                    <td style="" align="left" valign="top"><div style="text-align: justify; font-weight: regular;"><span>{{$data->modalidad}}</span></div></td>
                    <td style="" rowspan="7" align="right"  width="150px" valign="top">
                        <div style="position:relative; border:solid 1px #d9d9d9; padding:1px; width:140px; overflow-hidden; margin-right:-40px;" >
                            <div style=" position:absolute; font-size: 2.5rem; top:-45px; right:-4px;">
                               <span> {{ $data->id_programa }} </span>
                            </div>

                            <div style="overflow: hidden; height:170px; width:140px; margin-right:-10px;">
                                <img src="{{ $foto }}" alt="foto" width="150">
                            </div>
                            {{-- <div ><span style="color:red; font-size:14pt;">DUPLICADO</span></div> --}}
                        </div>
                    </td>
                </tr>
                <tr style="margin-bottom:-12px;">
                    <td width="180px" align="left" valign="top"><div style="margin-top: -8px;">PROGRAMA DE ESTUDIOS</div></td>
                    <td width="5px" align="left" valign="top"><div style="margin-top: -8px;">:</div></td>
                    <td align="left" valign="top">
                        <div class="truncate-text"> {{ $data->programa }}</div></td>
                </tr>
                <tr>
                    <td width="180px" align="left" valign="top"><div style="margin-top: -16px;"> NRO. DE DOCUMENTO</div></td>
                    <td width="5px" align="left" valign="top"><div style="margin-top: -16px;">:</div></td>
                    <td align="left" valign="top"><div style="text-align: justify; font-weight: regular; margin-top: -16px;"><span style="text-transform: uppercase;">{{$data->dni}}</span></div></td>
                </tr>
                <tr>
                    <td width="180px" align="left" valign="top"><div style="margin-top: -24px;"> PRIMER APELLIDO </div></td>
                    <td width="5px" align="left" valign="top"><div style="margin-top: -24px;">:</div></td>
                    <td align="left" valign="top"><div style="text-align: justify; font-weight: regular; margin-top: -24px;"><span style="text-transform: uppercase;"> {{$data->paterno}} </span></div></td>
                </tr>
                <tr>
                    <td width="180px" align="left" valign="top"><div style="margin-top: -32px;"> SEGUNDO APELLIDO </div></td>
                    <td width="5px" align="left" valign="top"><div style="margin-top: -32px;">:</div></td>
                    <td align="left" valign="top"><div style="text-align: justify; font-weight: regular; margin-top: -32px;"><span style="text-transform: uppercase;"> {{$data->materno}} </span></div></td>
                </tr>
                <tr>
                    <td width="180px" align="left" valign="top"><div style="margin-top: -40px;"> PRE NOMBRES </div></td>
                    <td width="5px" align="left" valign="top"><div style="margin-top: -40px;">:</div></td>
                    <td align="left" valign="top"><div style="text-align: justify; font-weight: regular; margin-top: -40px;"><span style="text-transform: uppercase;"> {{ $data->nombre }} </span></div></td>
                </tr>
                <tr>
                    <td width="180px" align="left" valign="top"><div style="margin-top: -48px;"> FECHA Y HORA </div></td>
                    <td width="5px" align="left" valign="top"><div style="margin-top: -48px;">:</div></td>
                    <td align="left" valign="top"><div style="text-align: justify; font-weight: regular; margin-top: -48px;"><span style="text-transform: uppercase;"> {{$data->fecha}}</span></div></td>
                </tr>
            </table>
        </div>

        <div style="margin-top:-50px">
            <table style="font-size:11pt;  width:100%;">
                <tr style="height:85px; padding:0;">
                    <td>
                        <div>
                            <span style="font-weight:bold">
                                DECLARACIÓN JURADA
                            </span>
                        </div>
                        <div style="text-align: justify; margin-top:-10px; font-size:11pt;">
                            <p>
                                El que suscribe declara bajo juramento que la información proporcionada
                                durante el proceso de inscripción es veraz y de mi entera responsabilidad.
                                Reconozco y acepto los términos establecidos en el
                                Reglamento General de Admisión de 2026,
                                así como permitir una rigurosa revisión física exhaustiva
                                para acceder a la Ciudad Universitaria y realizar el examen de admisión.
                                En caso de obtener una vacante, me comprometo a cumplir con lo estipulado en el reglamento.
                                Como muestra de conformidad, firmo la presente constancia de inscripción.
                            </p>
                        </div>
                    </td>
                </tr>

            </table>
        </div>

        <div style="margin-top:-10px">
            <table style="font-size:11pt;  width:100%;">
                <tr>
                    <td width="400" style="" valign="top">
                        <div>
                            <span style="font-weight:bold;">
                                INSTRUCCIONES PARA EL DÍA DEL EXAMEN:
                            </span>

                            <div style="margin-top: -8px; margin-left:-5px;">
                                <ul>
                                    <li>Presentar Constancia de Inscripción y DNI (original).</li>
                                    <li>No portar dispositivos electrónicos, ni objetos metálicos.</li>
                                    <li>Presentarse con cabello recogido o corto.</li>
                                    <li>El cuello y las orejas deben estar visibles.</li>
                                </ul>
                            </div>
                        </div>
                        <div style="margin-top: -8px;">
                            <span style="font-weight:bold;">
                                HORARIO DE INGRESO ( {{ $dia1 }} ):
                            </span>
                            <div style="margin-top: -8px; margin-left:-5px;">
                                <ul>
                                    <li>Ingreso: 06:30 a 09:30 horas</li>
                                    {{-- <li>Inicio de examen: 10:00 horas</li> --}}
                                </ul>
                            </div>
                        </div>
                        <div style="margin-top: -8px; margin-left:-5px;">
                            @if($dia2)
                                <span style="font-weight: bold;">
                                    HORARIOS SOLO PARA CLASIFICADOS ( {{ $dia2 }} ):
                                </span>
                                <div style="margin-top: -8px;">
                                    <ul>
                                        <li>Ingreso: 08:00 a 09:30 horas.</li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </td>

                    <td valign="top">
                        <div style="font-size: 8pt;">
                            <div style=" width 80px; height: 101px; border:solid 1px black;">
                                <img src="{{ $huellaDerecha }}" alt="foto" width="76">
                            </div>
                            <div style="text-align: center; margin-top:6px;"><span>Indice Derecho</span></div>
                            <div style=" width 80px; height: 101px; margin-top:10px; overflow:hidden;">

                            </div>
                        </div>

                    </td>

                    <td valign="top">
                        <div style="font-size: 8pt;">
                            <div style=" width 80px; height: 101px; border:solid 1px black;">
                                <img src="{{ $huellaDerecha }}" alt="foto" width="76">
                            </div>
                            <div style="text-align: center; margin-top:6px;"><span>Indice Derecho</span></div>
                        </div>
                    </td>



                </tr>

            </table>
        </div>


        <div style="margin-top:110px">
            <table style="font-size:10pt;  width:100%;">
                <tr>

                </tr>

                <tr>
                    <td>
                        <div style="width:240px;">
                            <div style="margin-left:10px; margin-right:10px; border-top: 1px solid black; text-align:center; padding-top:5px;">
                                <span>DIRECTOR DE ADMISIÓN</span>
                            </div>
                        </div>
                    </td>
                    <td>
                     </td>
                    <td>
                        <div style="padding 10px; width:240px">
                            <div style="margin-left:0px; margin-right:10px; border-top: 1px solid black; text-align:center; padding-top:5px;">
                                <span>POSTULANTE</span>
                            </div>
                        </div>
                    </td>

                    <td style="width: 190px">
                        <div style="magin-rigth 0px; width:145px; margin-top:-40px;">
                            <div style="text-align: left;">
                                <span style="margin-right:14px; text-align:left"><?php echo DNS1D::getBarcodeHTML($data->dni ,'C128',2.5,50);?> </span>
                            </div>
                            <div style="margin-right:-59px; text-align: ; font-size:2.25rem; margin-top:-20px;">
                                {{$data->codigo}}
                            </div>
                            <div style="margin-right:-59px; text-align: center;">
                                <span style="text-transform: uppercase;">{{ strtoupper(substr($data->name, 0, 1)) }}. {{$data->upaterno}} </span>
                            </div>
                            <div style=" margin-right:-59px; text-align: center;">
                                <span style="text-transform: uppercase; font-size:0.6rem;">inscriptor</span>
                            </div>
                        </div>
                    </td>
                </tr>

            </table>

        </div>
    </div>
</body>
</html>
