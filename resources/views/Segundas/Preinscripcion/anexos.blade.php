<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Datos Biométricos</title>

<style>

    body{
        font-family: sans-serif;
        margin:0;
        padding:0;
        font-size: 11pt;
    }

    .page{
        padding:20px;
    }

    .page-break{
        page-break-before: always;
    }

    .header{
        text-align:center;
        margin-bottom:30px;
        font-size: 10pt;
    }

    .dni-info{
        font-size:18px;
        margin:25px 0;
        text-align:center;
        padding:15px;
        background:#f5f5f5;
    }

    .fecha{
        text-align:right;
        font-size:12px;
        color:#666;
    }

    .page-number{
        text-align:center;
        font-size:10px;
        color:#999;
        margin-top:40px;
    }
    .justificado {
        text-align: justify;
        line-height: 26px;
    }

</style>
</head>

<body>

<!-- PAGINA 1 -->
<div class="page">

    <div class="header">
        <div style="margin-bottom: 16px;"> <span style="font-size: 12pt; font-weight:bold;"> ANEXO 04 INSTRUMENTO </span></div>
        <div> <span style="font-size: 12pt; font-weight:bold;"> ENTREVISTA PERSONAL </span></div>
    </div>

    <div style="margin-bottom: 20px;">
        <table>
            <tr >
                <td style="height: 30px;" colspan="2">Facultad  <span style="border-bottom: 2px dotted black; ">{{ $datos->facultad ?? '' }}</span> </td>
            </tr>
            <tr >
                <td style="height: 30px;" colspan="2">Nombre del postulante <span style="border-bottom: 2px dotted black; ">{{ $datos->nombres ?? '' }} {{ $datos->paterno ?? '' }} {{ $datos->materno ?? '' }}</span></td>
            </tr>
            <tr >
                <td style="height: 30px;" colspan="2">Programa <span style="border-bottom: 2px dotted black; ">{{ $datos->programa ?? '' }}</span></td>
            </tr>
            <tr>
                <td style="height: 30px;">Fecha <span> ..../..../....</span></td>
                <td>Hora <span> ..../..../....</span></td>
            </tr>
        </table>
    </div>

    <div>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">

            <tr>
                <td rowspan="2" style="border: 1px solid #000; padding: 10px; text-align: center; border-right: 1px solid #000; border-bottom: 1px solid #000;"><strong>N°</strong></td>
                <td rowspan="2" style="border: 1px solid #000; padding: 10px; text-align: center; border-right: 1px solid #000; border-bottom: 1px solid #000;"><strong>ASPECTOS A EVALUAR</strong></td>
                <td colspan="4" style="border: 1px solid #000; padding: 10px; text-align: center; border-bottom: 1px solid #000;"> <strong>PUNTAJE</strong></td>
            </tr>
            
            <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; width: 15%; border-right: 1px solid #000;">
                    <div style="font-size: 14px;">4</div>
                    <div style="font-size: 10px;">Excelente</div>
                </td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; width: 15%; border-right: 1px solid #000;">
                    <div style="font-size: 14px;">3</div>
                    <div style="font-size: 10px;">Bueno</div>
                </td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; width: 15%; border-right: 1px solid #000;">
                    <div style="font-size: 14px;">2</div>
                    <div style="font-size: 11px;">Regular</div>
                </td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; width: 15%;">
                    <div style="font-size: 14px;">1</div>
                    <div style="font-size: 11px;">Con Dificultades</div>
                </td>
            </tr>
            
            <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;">01</td>
                <td style="border: 1px solid #000; padding: 8px; border-right: 1px solid #000;">Tiene conocimientos básicos acerca de la especialidad.</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;"></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;"></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;"></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;"></td>
            </tr>
                <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;">02</td>
                <td style="border: 1px solid #000; padding: 8px; border-right: 1px solid #000;">Expectativas como especialista</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;"></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;"></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;"></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;"></td>
            </tr>
                <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;">03</td>
                <td style="border: 1px solid #000; padding: 8px; border-right: 1px solid #000;">Muestra serenidad y autocontrol emocional.</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;"></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;"></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;"></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;"></td>
            </tr>
                <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;">04</td>
                <td style="border: 1px solid #000; padding: 8px; border-right: 1px solid #000;">Tiene conocimientos básicos de investigación</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;"></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;"></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;"></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 8px; text-align: center; border-right: 1px solid #000;" height="45px" text-align="center" colspan="2"> <div><strong>PUNTAJE TOTAL</strong></div></td>
                <td style="border: 1px solid #000; padding: 8px; text-align: center;" colspan="4"></td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <div style="margin-bottom: 20px;">Observaciones u ocurrencias: ...........................................</div>
        <div>.................................................................................</div>
    </div>


    <div style="width: 100%; text-align:center; margin-top:80px;">FIRMA DE JURADO</div>


</div>

<div class="page page-break">



<div class="header">
    <div style="margin-bottom: 10px;"> <span style="font-size: 12pt; font-weight:bold;"> ANEXO 05</span></div>
    <div style="margin-bottom: 10px;"> <span style="font-size: 12pt;"> FORMATO PARA EL POSTULANTE </span></div>
    <div> <span style="font-size: 12pt; font-weight:bold;"> ENTREVISTA PERSONAL </span></div>
</div>

<div style="margin-bottom: 10px;">
    
        <div >
            <div style="height: 30px;" colspan="2">Senores</div>
        </div>
        <div>
            <div style="height: 30px;" colspan="2">UNIVERSIDAD NACIONAL DEL ALTIPLANO DE PUNO</span></div>
        </div>
        <div style="margin-top: -20px">
            <p class="justificado">
                Yo, <span style="border-bottom: 2px dotted #000;">{{ $datos->nombres ?? '' }} {{ $datos->paterno ?? '' }} {{ $datos->materno ?? '' }}</span>, 
                identificado con DNI Nº <span style="border-bottom: 2px dotted #000;">{{ $datos->nro_doc ?? '' }}</span>,
                con domicilio real en <span style="border-bottom: 2px dotted #000;">{{ $datos->direccion ?? '' }}</span>,
                ubicado en la Región <span style="border-bottom: 2px dotted #000;">{{ $datos->departamento ?? '' }}</span>, Provincia de 
                <span style="border-bottom: 2px dotted #000;">{{ $datos->provincia ?? '' }}</span> y Distrito de
                <span style="border-bottom: 2px dotted #000;">{{ $datos->distrito ?? '' }}</span>, mediante la presente le solicito se me considere en el Proceso de
                Admisión 2026 al Programa de 
                <span style="border-bottom: 2px dotted #000;">{{ $datos->programa ?? '' }}</span>
                de la Facultad de <span style="border-bottom: 2px dotted #000;">{{ $datos->facultad ?? '' }}</span> de la Universidad Nacional del Altiplano de Puno. 
            </p>
        </div>
        <div style="margin-top: -5px; margin-bottom: 20px;">
            <span>
                Para fines del presente proceso de Admisión, remito los siguientes datos:
            </span>
        </div>

        <div>
            <table>
                <tr>
                    <td style="height: 30px;">Teléfono/celular</td>
                    <td> :<span style="border-bottom: 2px dotted #000;">{{ $datos->celular ?? '' }}</span></td>
                </tr>
                <tr>
                    <td style="height: 30px">Correo electrónico</td>
                    <td> :<span style="border-bottom: 2px dotted #000;">{{ $datos->email ?? '' }}</span></td>
                </tr>
                <tr>
                    <td>Fecha de nacimiento</td>
                    <td> :<span style="border-bottom: 2px dotted #000;">{{ $datos->fec_nacimiento ?? '' }}</span></td>
                </tr>
                  <tr>
                    <td style="height: 30px">Ubigeo de nacimiento</td>
                    <td> :<span style="border-bottom: 2px dotted #000;">{{ $datos->ubigeo_nacimiento ?? '' }}</span></td>
                </tr>
                <tr>
                    <td style="height: 30px">Lengua nativa </td>
                    <td> :<span style="border-bottom: 2px dotted #000;">{{ $datos->lengua_nativa ?? '.....................' }}</span></td>
                </tr>
                <tr>
                    <td style="height: 30px">Idioma extranjero</td>
                    <td> :<span style="border-bottom: 2px dotted #000;">{{ $datos->idioma_extranjero ?? '.....................' }}</span></td>
                </tr>
                <tr>
                    <td style="height: 30px" colspan="2">
                        <div>
                            <span style="padding-right: 30px">Discapacidad</span> <span style="color: white;">"-----"</span> <span style=""> {{ "SI ( ___ )  NO ( ___ )"  }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="height: 30px" colspan="2">
                        <div>
                            <span style="padding-right: 30px">Entidad Bancaria donde pagó por inscripción:</span> <span style="color: white;">"-"</span> <span style=""> {{ "............................"  }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="height: 30px">Fecha de depósito</td>
                    <td> <span >:{{ "......................." }}</span></td>
                </tr>
                <tr>
                    <td style="height: 30px">Número de operación*</td>
                    <td> <span>:{{ "......................" }}</span></td>
                </tr>

            </table>
        </div>
        <div style="text-align: right"> 
            <p style="margin-top: 20px;">
                C.U. ,....... de ………………….. del 2026
            </p>
        </div>
    
    </div>

    <div style="width: 100%; text-align:center; margin-top:80px;">_______________________________________________</div>
    <div style="width: 100%; text-align:center; margin-top:20px;">Nombres Y Apellidos: ........................................</div>
    <div style="width: 100%; text-align:center; margin-top:5px;">DNI N°: ........................................</div>


</div>
</div>



<!-- PAGINA 3 -->
<div class="page page-break">
    <div class="header">
    <div style="margin-bottom: 10px; margin-top: 20px;"> <span style="font-size: 12pt; font-weight:bold;"> DECLARACIÓN JURADA</span></div>
</div>

    <div style="margin-bottom: 10px;">
    
        <div>
            <div style="margin-top: -20px">
                <p class="justificado" style="line-height: 50px;">
                    Yo, <span style="border-bottom: 2px dotted #000;">{{ $datos->nombres ?? '.....................' }} {{ $datos->paterno ?? '.....................' }} {{ $datos->materno ?? '.....................' }}</span>; 
                    identificado con DNI Nº <span style="border-bottom: 2px dotted #000;">{{ $datos->nro_doc ?? '.....................' }}</span>,
                    con domicilio real en <span style="border-bottom: 2px dotted #000;">{{ $datos->direccion ?? '.....................' }}</span>,
                    ubicado en la Región <span style="border-bottom: 2px dotted #000;">{{ $datos->departamento ?? '.....................' }}</span>, Provincia de 
                    <span style="border-bottom: 2px dotted #000;">{{ $datos->provincia ?? '.....................' }}</span> y Distrito de
                    <span style="border-bottom: 2px dotted #000;">{{ $datos->distrito ?? '.....................' }}</span>                
                </p>
            </div>
            <div style="margin-top: -30px;">
                <p style ="line-height: 50px; text-align: justify; font-size: 11.5pt; font-weight:700;">
                   <strong>DECLARO BAJO JURAMENTO: NO TENER ANTECEDENTES PENALES Y POLICIALES.</strong>
                </p>
            </div>

            <div style="margin-top: -30px">
                <p class="justificado" style="line-height: 50px;">
                    Asumo la responsabilidad administrativa, civil y/o penal por cualquier acción de verificación que
                    compruebe la falsedad o inexactitud de la presente declaración jurada, así como la adulteración de
                    los documentos que presente posteriormente a requerimiento de la entidad.
                
                </p>
            </div>

            <div style="text-align: right; margin-top: 20px;"> 
                <p style="margin-top: 20px;">
                    C.U. ,....... de ………………….. del 2026
                </p>
            </div>
        
        </div>

        <div style="width: 100%; text-align:center; margin-top:140px;">_______________________________________</div>
        <div style="width: 100%; text-align:center; margin-top:20px;">Nombres Y Apellidos: ...........................</div>
        <div style="width: 100%; text-align:center; margin-top:5px;">DNI N°: ..........................</div>



    </div>


</div>



<!-- PAGINA 4 -->
<div class="page page-break">


    <div class="header">
    <div style="margin-bottom: 40px; margin-top: 40px;"> <span style="font-size: 12pt; font-weight:bold;">CARTA DE COMPROMISO</span></div>


    <div style="margin-bottom: 10px;">
    
        <div>
            <div style="margin-top: -20px">
                <p class="justificado" style="line-height: 40px; font-size: 12pt;">
                    Yo, <span style="border-bottom: 2px dotted #000;">{{ $datos->nombres ?? '.....................' }} {{ $datos->paterno ?? '.....................' }} {{ $datos->materno ?? '.....................' }}</span>; 
                    identificado con DNI Nº <span style="border-bottom: 2px dotted #000;">{{ $datos->nro_doc ?? '.....................' }}</span>,
                    Postulante a la convocatoria de Admisión 2026 del Programa de 
                    Especialidad en <span style="border-bottom: 2px dotted #000;">{{ $datos->programa ?? '.....................' }}</span>,
                     de la Facultad de <span style="border-bottom: 2px dotted #000;">{{ $datos->facultad ?? '.....................'  }}</span>               
                </p>
            </div>
            <div style="margin-top: -30px;">
                <p style ="line-height: 50px; text-align: justify; font-size: 12pt">
                    Me comprometo con:
                </p>
            </div>

            <div style="margin-top: -20px">
                <table>
                    <tr>
                        <td width="40" style="vertical-align: top; line-height: 40px;">-</td>
                        <td style ="line-height: 40px; text-align: justify; font-size: 12pt">
                            <p style ="text-align: justify; font-size: 11.5pt;">
                                Asumir los costos y tasas establecidas por los servicios académicos y administrativos
                                prestados por el programa, según cronograma.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td width="40" style="vertical-align: top; line-height: 40px;">-</td>
                        <td style ="line-height: 40px; text-align: justify; font-size: 12pt">
                            <p style ="text-align: justify; font-size: 11.5pt;">
                                Cumplir con los Reglamentos Académicos y Administrativos del programa.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td width="40" style="vertical-align: top; line-height: 40px;">-</td>
                        <td style ="line-height: 40px; text-align: justify; font-size: 12pt">
                            <p style ="text-align: justify; font-size: 11.5pt;">
                                Disponer de los equipos y conexión a internet para el desarrollo de actividades no
                                presenciales, cuando se requiera.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td width="40" style="vertical-align: top; line-height: 40px;">-</td>
                        <td style ="line-height: 40px; text-align: justify; font-size: 12pt">
                            <p style ="text-align: justify; font-size: 11.5pt;">
                                Tener dominio de herramientas tecnológicas para el desarrollo de actividades no presenciales.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td width="40" style="vertical-align: top; line-height: 40px;">-</td>
                        <td style ="line-height: 40px; text-align: justify; font-size: 12pt">
                            <p style ="text-align: justify; font-size: 11.5pt;">
                                Asistir a la sede central de la UNA-Puno para el desarrollo de actividades académicas y
                                administrativas, cuando la universidad lo requiera.
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="text-align: right; margin-top: 20px;"> 
                <p style="margin-top: 20px;">
                    C.U. ,....... de ………………….. del 2026
                </p>
            </div>
        
        </div>

        <div style="width: 100%; text-align:center; margin-top:100px;">_______________________________________</div>
        <div style="width: 100%; text-align:center; margin-top:20px;">Nombres Y Apellidos: ...........................</div>
        <div style="width: 100%; text-align:center; margin-top:5px;">DNI N°: ..........................</div>



    </div>

</div>

</body>
</html>