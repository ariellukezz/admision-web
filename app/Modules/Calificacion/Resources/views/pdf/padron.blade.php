@php
    ini_set('memory_limit', '2016M');
    set_time_limit(300);
    ini_set('max_execution_time', 300);
    $logoIzq = public_path('imagenes/logotiny.png');
    $logoDer = public_path('imagenes/logoDAD.jpg');
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 1cm 1cm 1cm 1cm;
        header: html_padronHeader;
        footer: html_padronFooter;
    }
    body {
        font-family: Helvetica, Arial, sans-serif;
        margin: 0; padding: 0;
        font-size: 8pt;
        color: #222;
        line-height: 1.3;
    }

    /* ─── Cabecera de aula ─── */
    .classroom-header {
        color: #fff;
        padding: 4px 12px;
        border-radius: 4px;
        margin: 10px 0 8px 0;
        text-align: center;
    }
    .classroom-header h3 { margin: 0; font-size: 14pt; font-weight: bold; }
    .classroom-header p { margin: 0; font-size: 12pt; }

    /* ─── Tabla de estudiantes ─── */
    .student-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 3px;
    }
    .student-table td {
        border: none;
        border-bottom: 1px solid #000;
        padding: 2px 4px;
        vertical-align: middle;
    }
    .student-table img { display: block; margin: 0 auto; }
</style>
</head>
<body>

<htmlpageheader name="padronHeader">
    <table style="width:100%;border:none;border-collapse:collapse;">
        <tr>
            <td style="border:none;padding:0;text-align:right;width:55px;">
                EXAMEN CEPREUNA 2026-II
            </td> 
        </tr>
    </table>
    {{-- <div style="border-bottom:0.5pt solid #333; margin-top:4px;"></div> --}}
</htmlpageheader>

<htmlpagefooter name="padronFooter">
    <table width="100%" style="border-collapse: collapse;">
        <tr>
            <td width="50%" style="text-align: left; font-size: 8pt; color: #555;">
                {{-- Grupo #{{ $filterGroupId }} | --}}
                {{ date('d/m/Y H:i:s') }}
            </td>
            <td width="50%" style="text-align: right; font-size: 8pt; color: #888;">
                Pág. {PAGENO} de {nb}
            </td>
        </tr>
    </table>
</htmlpagefooter>

</body>
</html>
