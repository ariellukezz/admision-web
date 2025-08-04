<!DOCTYPE html>
<html>
<head>
    <title>Datos biométricos</title>
    <style>
        .div-container {
            width: 100%;
            overflow: hidden;
            /* background: pink; */
            height: 830px;
        }
        .div-container > div {
            width: 50%;
            float: left;
            box-sizing: border-box;
        }
    </style>
</head>
<body style="font-family: 'Gill Sans Extrabold', Helvetica, sans-serif; margin-left:20px; margin-top:95px; margin-right:0px;">
    <div style="margin-top: 20px;">
        <table style="width: 100%">
            <tr>
                <td align="center"><div style="text-align: center"><span style="font-weight:bold">EXAMEN {{ $data->proceso }}</span></div></td>
            </tr>

            <tr>
                @if ($data->segunda_carrera == 1)
                <td align="center"><div style="text-align: center"><span style="font-size: 1.4rem; font-weight:bold">CONSTANCIA DE INGRESO A 2DA CARRERA </span></div></td>
                @else
                <td align="center"><div style="text-align: center"><span style="font-size: 1.4rem; font-weight:bold">CONSTANCIA DE INGRESO</span></div></td>
                @endif
            </tr>
        </table>
    </div>

    <div style="text-align: justify;">
    
        <p style="line-height: 1.5rem;">
            La Dirección de Admisión de la Universidad Nacional del Altiplano de Puno, en cumplimiento pleno del 
            Reglamento General de Admisión 2025-I, <label style="font-weight:bold;">HACE CONSTAR </label> 
            que <label style="font-weight:bold;">{{$data->paterno}} {{$data->materno}} {{$data->nombres}}</label>, identificado (a) con 
            DNI N° <label style="font-weight:bold;">{{$data->dni}}</label>, es 
            <label style="font-weight:bold;">INGRESANTE APTO</label> al programa de estudios de <label style="font-weight:bold;">{{$data->programa}}</label>, 
            bajo la modalidad <label style="font-weight:bold;">{{$data->modalidad}}</label>, El ingresante está en el puesto <label style="font-weight:bold;">{{ $data->puesto }}</label> y obtuvo el puntaje de
            <label style="font-weight:bold;">{{ $data->puntaje }} </label>puntos el {{ $date }}. El (la) estudiante en mención,
            queda expedito(a) con código <label style="font-weight:bold;"> {{$data->cod_ingreso}} </label> para matricularse en el referido programa de estudios semestre 2025-II, siendo la modalidad de estudio PRESENCIAL.
        </p>

    </div>

    <div>
        <div style="margin-left: 40px;">
            <table>
                <tr align="center">
                    <td rowspan="2" colspan="2">
                        <div style="width: 140px; height:180px; border: solid 1px black; overflow:hidden; margin-right:4px; padding-right:1px;">
                            @if (!empty($fins) && file_exists($fins))
                                <img src="{{ $fins}}" alt="Mi imagen" width="160">
                            @else
                                <div style="margin-top:60px; text-align:center; font-size:.7rem;">
                                    Sin Foto
                                </div>
                            @endif
                            <div style="margin-top: -15px; heigth:10px; padding: 0px 0px;">
                                <div style="background: #d9d9d900; text-align:center; " >
                                    <div style="">
                                        <div style="font-size: .5rem; background: #d9d9d977; padding; 0px 5px; font-weight:bold; color:red;">Foto inscripción</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td rowspan="2" colspan="2">
                        <div style="width: 140px; height:180px; border: solid 1px black; overflow:hidden;  margin-right:4px; padding-right:1px;">
                            @if (!empty($fbio) && file_exists($fbio))
                                <img src="{{ $fbio }}" alt="Mi imagen" width="160">
                            @else
                                <div style="margin-top:60px; text-align:center; font-size:.7rem;">
                                    Sin Foto
                                </div>
                            @endif
                            <div style="margin-top: -25px; heigth:10px; padding: 0px 0px;">
                                <div style="background: #d9d9d900; text-align:center;">
                                    <div style="">
                                        <div style="font-size: .5rem; background: #d9d9d977; padding; 0px 5px; font-weight:bold; color:red;">Foto Biométrico</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td rowspan="2" colspan="1">
                        <div style="width: 69px; height:90px; border: solid 1px black;  margin-right:4px;">
                            @if (!empty($hinsD) && file_exists($hinsD))
                                <img src="{{ $hinsD }}" alt="Mi imagen" width="69">
                            @else
                                <div style="margin-top:35px; text-align:center; font-size:.7rem;">
                                    Sin huella
                                </div>
                            @endif
                            <div style="margin-top: -10px; heigth:10px; padding: 0px 0px;">
                                <div style="background: #d9d9d900; text-align:center; " >
                                    <div style="">
                                        <div style="font-size: .5rem; background: #d9d9d977; padding; 0px 5px; font-weight:bold; color:red;">Huella Ins. ID</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="width: 69px; height:90px; border: solid 1px black;">
                            @if (!empty($hinsI) && file_exists($hinsI))
                                <img src="{{ $hinsI }}" alt="Mi imagen" width="69">
                            @else
                                <div style="margin-top:35px; text-align:center; font-size:.7rem;">
                                    Sin huella
                                </div>
                            @endif
                            <div style="margin-top: -10px; heigth:10px; padding: 0px 0px;">
                                <div style="background: #d9d9d900; text-align:center; " >
                                    <div style="">
                                        <div style="font-size: .5rem; background: #d9d9d977; padding; 0px 5px; font-weight:bold; color:red;">Huella Ins. II</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td rowspan="2" colspan="2">
                        <div style="width: 140px; height:180px; border: solid 1px black; overflow:hidden; margin-right:4px;">
                            @if (!empty($hexaI) && file_exists($hexaI))
                                <img src="{{ $hexaI }}" alt="Mi imagen" width="140">
                            @else
                                <div style="margin-top:60px; text-align:center; font-size:.7rem;">
                                    Sin Huella
                                </div>
                            @endif
                            <div style="margin-top: -40px; heigth:10px; padding: 0px 0px;">
                                <div style="background: #d9d9d900; text-align:center; " >
                                    <div style="">
                                        <div style="font-size: .5rem; background: #d9d9d977; padding; 0px 5px; font-weight:bold; color:red;">Huella examen</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td rowspan="2" colspan="1">
                        <div style="width: 69px; height:90px; border: solid 1px black;">
                            @if (!empty($hbioD) && file_exists($hbioD))
                                <img src="{{ $hbioD }}" alt="Mi imagen" width="69">
                            @else
                                <div style="margin-top:35px; text-align:center; font-size:.7rem;">
                                    Sin huella
                                </div>
                            @endif
                            <div style="margin-top: -13px; heigth:10px; padding: 0px 0px;">
                                <div style="background: #d9d9d900; text-align:center; " >
                                    <div style="">
                                        <div style="font-size: .5rem; background: #d9d9d977; padding; 0px 5px; font-weight:bold; color:red;">Huella Ins. ID</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="width: 69px; height:90px; border: solid 1px black;">
                            @if (!empty($hbioI) && file_exists($hbioI))
                                <img src="{{ $hbioI }}" alt="Mi imagen" width="69">
                            @else
                                <div style="margin-top:35px; text-align:center; font-size:.7rem;">
                                    Sin huella
                                </div>
                            @endif
                            <div style="margin-top: -10px; heigth:10px; padding: 0px 0px;">
                                <div style="background: #d9d9d900; text-align:center; " >
                                    <div style="">
                                        <div style="font-size: .5rem; background: #d9d9d977; padding; 0px 5px; font-weight:bold; color:red;">Huella Ins. ID</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    
    <div style="text-align: justify">
        <p style="line-height: 1.5rem;">
            Así mismo, se deja constancia que ha validado su identidad a 
            través del control biométrico y acreditó los documentos personales, según 
            los requisitos exigidos en el Reglamento General de Admisión 2025-I. De 
            igual manera se proporciona al ingresante 
            un correo institucional de gran utilidad para fines académicos y administrativos.
        </p>
    </div>
    <div style="text-align: justify; margin-top: -15px;">
        <p style="line-height: 1.5rem;">
            @if ($data->correo_institucional == null)
                Use el correo institucional que ya tiene asignado<br>
                Fecha de nacimiento: <span style="font-weight: bold"> {{$fnac}} </span><br>
            @else
                Correo institucional: <span style="font-weight: bold"> {{$data->correo_institucional}} </span><br> 
                Contraseña de primer ingreso: <span style="font-weight: bold"> {{$data->dni}} </span><br>
                Fecha de nacimiento: <span style="font-weight: bold"> {{$fnac}} </span><br>

            @endif
        </p>

    </div>

    <div style="text-align: right; margin-top:-15px;">
        <p style="line-height: 1.5rem;">
            CU. {{$fimp}}
        </p>
    </div>

    {{-- <div style="text-align: center;">
        <p style="line-height: 1.5rem;">
            Atentamente,
        </p>
    </div> --}}

    <div style="margin-top:100px;">
        <table>
            <tr>
                <td>
                    <div style="text-align:center; width:380px;">
                        <div>_______________________</div>
                        <div>DIRECTOR DE ADMISIÓN</div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div style="text-align:center;">
                        <div>______________________</div>
                        <div>INGRESANTE</div>
                    </div>
                </td>
            </tr>
        </table>

    </div>
 
 
</body>
</html>