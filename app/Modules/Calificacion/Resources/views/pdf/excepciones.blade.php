@php
    $logoIzq = public_path('imagenes/logotiny.png');
    $logoDer = public_path('imagenes/logoDAD.jpg');
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 3.5cm 1.5cm 1.5cm 1.5cm;
        header: html_excHeader;
        footer: html_excFooter;
    }
    body {
        font-family: Helvetica, Arial, sans-serif;
        margin: 0; padding: 0;
        font-size: 9pt;
        color: #1a1a1a;
        line-height: 1.3;
    }
    .info-box {
        background: #fff4e6;
        border: 1px solid #ffd591;
        border-radius: 4px;
        padding: 8px 12px;
        margin-bottom: 14px;
        font-size: 9pt;
    }
    .info-box strong { color: #874d00; }
    .exc-table {
        width: 100%;
        border-collapse: collapse;
    }
    .exc-table thead th {
        background: #1e293b;
        color: #fff;
        border: 1px solid #1e293b;
        padding: 7px 8px;
        font-size: 9pt;
        font-weight: bold;
        text-align: center;
        text-transform: uppercase;
    }
    .exc-table tbody td {
        border: 1px solid #bbb;
        padding: 5px 8px;
        vertical-align: middle;
        font-size: 9pt;
    }
    .exc-table tbody tr:nth-child(even) td { background-color: #f8f8f8; }
    .exc-table .center { text-align: center; }
    .tag {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 8pt;
        font-weight: bold;
        color: #fff;
    }
    .tag-todas { background: #389e0d; }
    .tag-multiples { background: #1890ff; }
    .tag-anulada { background: #cf1322; }
    .tag-asignar { background: #d48806; }
    .signature {
        margin-top: 50px;
        text-align: center;
        font-size: 9pt;
        color: #333;
    }
    .signature-line {
        border-top: 1px solid #333;
        width: 250px;
        margin: 0 auto 4px auto;
    }
</style>
</head>
<body>

<htmlpageheader name="excHeader">
    <table style="width:100%;border:none;border-collapse:collapse;">
        <tr>
            <td style="border:none;padding:0;text-align:right;width:55px;">
                @if (file_exists($logoIzq))
                    <img src="{{ $logoIzq }}" width="50"/>
                @endif
            </td>
            <td style="width:100%;text-align:center;border:none;padding:0;">
                <div style="text-align:center;">
                    <div style="font-size:10pt;font-weight:bold;margin:1px 0;">UNIVERSIDAD NACIONAL DEL ALTIPLANO DE PUNO</div>
                    <div style="font-size:9pt;font-weight:bold;margin:1px 0;">VICERRECTORADO ACADÉMICO</div>
                    <div style="font-size:9pt;font-weight:bold;margin:1px 0;">DIRECCIÓN DE ADMISIÓN</div>
                </div>
            </td>
            <td style="border:none;padding:0;text-align:left;width:65px;">
                @if (file_exists($logoDer))
                    <img src="{{ $logoDer }}" width="60"/>
                @endif
            </td>
        </tr>
    </table>
    <div style="border-bottom:1.5px solid #333;margin-top:4px;"></div>
    <div style="text-align:center;margin-top:4px;">
        <div style="font-size:11pt;font-weight:bold;text-transform:uppercase;margin:2px 0;color:#1a1a1a;">Reporte General de Excepciones</div>
        <div style="font-size:8pt;color:#555;margin:1px 0;">{{ date('d/m/Y H:i:s') }}</div>
    </div>
</htmlpageheader>

<htmlpagefooter name="excFooter">
    <div style="text-align:center;font-size:8pt;color:#888;">Pág. {PAGENO} de {nb}</div>
</htmlpagefooter>

<div class="info-box">
    <strong>Total de excepciones:</strong> {{ $total }}
    @if ($idProceso)
        &nbsp;|&nbsp; <strong>Proceso:</strong> #{{ $idProceso }}
    @endif
</div>

<table class="exc-table">
    <thead>
        <tr>
            <th style="width:40px">N°</th>
            <th style="width:60px">Pregunta</th>
            <th>Acción</th>
            <th>Claves Válidas</th>
            <th style="width:80px">Puntaje</th>
            <th>Cód. Examen</th>
            <th>Tipo</th>
            <th>Observación</th>
        </tr>
    </thead>
    <tbody>
        @php $n = 1; @endphp
        @foreach ($excepciones as $exc)
            <tr>
                <td class="center">{{ $n }}</td>
                <td class="center">{{ $exc->nro_pregunta }}</td>
                <td class="center">
                    @php
                        $labels = [
                            'todas_validas' => ['Todas Válidas', 'tag-todas'],
                            'multiples_validas' => ['Múltiples Válidas', 'tag-multiples'],
                            'anulada' => ['Anulada', 'tag-anulada'],
                            'asignar_puntaje' => ['Asignar Puntaje', 'tag-asignar'],
                        ];
                        $label = $labels[$exc->accion] ?? [$exc->accion, 'tag-anulada'];
                    @endphp
                    <span class="tag {{ $label[1] }}">{{ $label[0] }}</span>
                </td>
                <td class="center">{{ $exc->claves_validas ?: '-' }}</td>
                <td class="center">{{ number_format($exc->puntaje, 3) }}</td>
                <td class="center">{{ $exc->cod_examen ?: '-' }}</td>
                <td class="center">{{ $exc->tipo ?: '-' }}</td>
                <td>{{ $exc->observacion ?: '-' }}</td>
            </tr>
            @php $n++; @endphp
        @endforeach
    </tbody>
</table>

<div class="signature">
    <div class="signature-line"></div>
    Responsable de Calificación
</div>

</body>
</html>
