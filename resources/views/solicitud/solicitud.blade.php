<!DOCTYPE html>
<html>
<head>
<title>SOLICITUD</title>
<style>
    .{
        margin: 0px;
        padding: 0px;
    }
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #ffffff;
    }
    .marca-agua {
        position: fixed;
        bottom: -850;
        left: -490;
        width: 200%;
        height: 160%;
        opacity: 0.1;
        z-index: -1000;
        pointer-events: none;
        font-size:12px;
        line-height: 20px;
        text-align: center;
        transform: rotate(-45deg);
        transform-origin: center;
    }
    .content {
        position: relative;
        z-index: 1;
        padding: 20px;
        *{margin:2cm 2cm; padding:65px 65px 0px 90px; }
    }
</style>
</head>
<body>
<div class="marca-agua">
    @for ($in = 0; $in < 900; $in++)
    <span style="text-transform:uppercase;"> Examen {{ strtolower($data->proceso) }} </span>
    @endfor
</div>
<div class="content">
    <div style="margin-top:0px;">
        <div style="margin:auto; width:400px; text-align:center; margin-bottom:14px; font-size:9pt; font-style: italic;">
            <span>
                “{{ $data->nombre_anio}}”
            </span>
        </div>
        <div>
            <table style="width:100%;">
                <tr style="">
                    <td style="width: 50%"></td>
                    <td style="width: 50%">
                        <p style="text-align: justify; line-height:1.5rem;">
                        <span style="font-weight: bold;">SOLICITO:</span> Inscripción para postular en el <span style="font-weight: bold;"> EXAMEN {{ $data->proceso }}.</span> </p></td>
                </tr>
            </table>
        </div>
        <div style="margin-top: 14px;">
            <table>
                <tr style="">
                    <td style="width: 100%">
                        <div  style="font-weight:bold; text-align:justify;">Sr. <span style="font-weight:bold; font-size:11pt; text-transform:uppercase;">Director de la Dirección de Admisión de la Universidad Nacional del Altiplano – Puno</span></div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 0px;">
            <table style="width:100%;">
                <tr style="">
                    <td style="width: 50%"></td>
                    <td style="width: 50%">
                        <p style="text-align: justify; line-height:1.8rem;">
                            Yo, <span style="text-transform: uppercase;">{{$data->nombres}} {{$data->primer_apellido}} {{ $data->segundo_apellido }}</span>,
                            identificado(a) con {{$data->tipo_doc}} N° {{ $data->dni}},
                            con domicilio en {{$data->direccion }}
                            @if ($data->distrito == null )
                                de {{$data->pais}},
                            @else
                                del distrito de {{ $data->distrito }},
                            @endif ante usted
                            me presento y expongo:
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 12px;">
            <table>
                <tr style="">
                    <td style="width: 100%">
                        <div style="text-align: justify;">
                            <div style="line-height:1.5rem; text-align:justify;">
                                Que, al cumplir con todos los requisitos establecidos
                                por la Dirección de Admisión de la UNA Puno,
                                <strong>SOLICITO la inscripción para postular en el EXAMEN
                                {{ $data->proceso }}</strong>
                                que se llevará a cabo el {{ $data->fecha_examen }}
                                de enero de 2026. Asimismo, pongo a vuestro conocimiento que
                                postulo al programa de estudios de<span style="font-weight: bold;"> {{ $data->programa }}</span>
                                bajo la modalidad  <span style="font-weight: bold;">{{ $data->modalidad }}</span>.
                            </div>
                        </div>
                        <div  style="margin-top: 14px;">
                            Adjunto a esta solicitud los siguientes documentos:
                        </div>
                        <div style="padding-left:20px; margin-top: 14px;">
                          @if( $data->id_modalidad == 12)
                            <table>
                                <tr><td style="height: 23px;">1.</td><td>Documento de identidad (<span style="color:red;">*</span>original y copia).</td></tr>
                                <tr><td style="height: 23px;">2.</td><td>Certificado de estudios (<span style="color:red;">*</span>original y copia).</td></tr>
                                <tr><td v-align="top"><div style="margin-top:-20px;">3.</div></td><td style="height: 23px;">Otros requisitos según lo estipulado en el Art. 15 del Reglamento General de admisión 2026 conforme a la modalidad de postulación.</td></tr>
                            </table>
                          @else
                            <table>
                              <tr><td style="height: 23px;">1.</td><td>Váuchers de pago.</td></tr>
                              <tr><td style="height: 23px;">2.</td><td>Documento de identidad (<span style="color:red;">*</span>original y copia).</td></tr>
                              <tr><td style="height: 23px;">4.</td><td>Certificado de estudios (<span style="color:red;">*</span>original y copia).</td></tr>
                              <tr><td style="height: 23px;">5.</td><td>Constancia de no adeudo al CEPREUNA(<span style="color:red;">*</span>original y copia).</td></tr>
                              <tr><td v-align="top"><div style="margin-top:-20px;">6.</div></td><td style="height: 23px;">Otros requisitos según lo estipulado en el Art. 15 del Reglamento General de admisión 2026 conforme a la modalidad de postulación.</td></tr>
                          </table>
                          @endif
                        </div>
                        <div style="margin-top:10px; margin-bottom:-14px;">
                            Nota (<span style="color:red;">*</span> Solo para verificación</span>)
                        </div>

                        <div style="margin-top: 14px; text-align:justify;">
                            @if( $data->id_modalidad != 2)
                                <P style="line-height:1.5rem;">
                                @if($carreras_previas != null)
                                    Adicionalmente, deseo informar que estoy postulando a mi
                                    @if(count($carreras_previas) == 1)
                                        segunda carrera
                                    @elseif( count($carreras_previas) == 2 )
                                        tercera carrera
                                    @elseif( count($carreras_previas) == 3 )
                                        cuarta carrera
                                    @elseif( count($carreras_previas) == 4 )
                                        quinta carrera
                                    @else
                                    @endif
                                    en esta prestigiosa universidad.
                                    He tenido ingresos previos en las siguientes carreras:
                                    @foreach ($carreras_previas as $index=>$item)
                                        <div style="margin-top: 8px; margin-left:20px;">
                                            {{$index + 1}}. {{ $item->nombre }} - CODIGO: <span style="font-weight: bold;">{{ $item->codigo }} </span>
                                        </div>
                                    @endforeach
                                    <p style="margin-top: 5px; margin-bottom: -3px;"> Importante: Revisar artículo 18 del Reglamento General de Admisión 2025</p>
                                @else
                                @endif
                            @endif

                            @if( $data->id_modalidad == 2)
                                <p style="line-height:1.5rem;">
                                    Adicionalmente, deseo informar que me encuentro matriculado en:
                                    @foreach ($carreras_previas as $index=>$item)
                                        <div style="margin-top: 8px; margin-left:20px;">
                                            {{$index + 1}}. {{ $item->nombre }} - CODIGO: <span style="font-weight: bold;">{{ $item->codigo }} </span>
                                        </div>
                                    @endforeach
                                </p>

                            @endif
                        </div>


                        <div style="text-align: justify; margin-top:12px;" >

                            @if( $data->id_modalidad_proceso == 1)
                            <p style="line-height:1.5rem;">
                                Agradezco su atención a la presente y me comprometo a cumplir con la inscripción presencial
                                según el último digito de mi DNI, tal como se encuentra especificado en el cronograma.
                            </p>

                            @else

                            <p style="line-height:1.5rem;">
                                Agradezco su atención a la presente y me comprometo a cumplir con la inscripción presencial
                                según como se encuentra especificado en el cronograma.
                            </p>
                            @endif

                        </div>


                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 14px;">
            <table style="width: 100%;">
                <tr>
                    <td style=""  >
                        <div style="text-align: right; width: 100%;">
                            Puno, {{$date}}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-top:88px">

            <table style="width:100%; bakcground:orange; margin-top:0px">
                <tr>
                    <td align="center"  style="vertical-align: bottom;">
                        <div style="text-align: center;">

                            <div style="margin-bottom: 10px;">FIRMA: _______________________________</div>
                            <div style="margin-bottom: 10px;"><span style="text-transform:uppercase">Nombre y Apellidos: ____________________________________</span></div>
                            {{-- <div><span style="text-transform:uppercase">{{$data->nombres}} {{$data->primer_apellido }} {{ $data->segundo_apellido }}</span></div> --}}
                            <span> {{$data->tipo_doc}} N.° {{ $data->dni}}</span>
                        </div>

                        {{-- <div>966637192</div> --}}
                    </td>
                    {{-- <td style="width: 50%">
                        <div style=" margin-left: 160px; height: 150px; text-align:center;">
                            <span style="font-size:6pt;"><?php echo DNS2D::getBarcodeHTML('https://admision.unap.edu.pe/verificar-solicitud/'.$data->dni, 'QRCODE',4,4);?> </span>
                        </div>
                        <div style=" margin-left: 140px; height: text-align:center;">
                            <span style="font-size:6pt;"><?php echo DNS1D::getBarcodeHTML($data->dni, 'C128',2.2,44);?> </span>
                        </div>
                    </td>  --}}
                </tr>
            </table>
        </div>

    </div>
</div>
</body>
</html>
