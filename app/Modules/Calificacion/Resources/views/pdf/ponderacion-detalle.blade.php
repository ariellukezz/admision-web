@php
    $logoIzq = public_path('imagenes/logotiny.png');
    $logoDer = public_path('imagenes/logoDAD.jpg');
    $correcta = $multiplicador ? (float) $multiplicador->correcta : null;

    $totalPreguntas = 0;
    $totalPonderacion = 0;
    $totalPuntaje = 0;
    foreach ($detalles as $d) {
        $totalPreguntas += (int) $d->cantidad_preguntas;
        $totalPonderacion += (float) $d->subtotal;
        $totalPuntaje += (float) $d->subtotal * ($correcta ?? 0);
    }
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 3.5cm 1.5cm 1.5cm 1.5cm;
        header: html_pondHeader;
        footer: html_pondFooter;
    }
    body {
        font-family: Helvetica, Arial, sans-serif;
        margin: 0; padding: 0;
        font-size: 9pt;
        color: #1a1a1a;
        line-height: 1.3;
    }
    .info-box {
        background: #f0f4ff;
        border: 1px solid #c3d4f0;
        border-radius: 4px;
        padding: 8px 12px;
        margin-bottom: 14px;
        font-size: 9pt;
    }
    .info-box strong { color: #1e3a5f; }
    .pond-table {
        width: 100%;
        border-collapse: collapse;
    }
    .pond-table thead th {
        background: #1e293b;
        color: #fff;
        border: 1px solid #1e293b;
        padding: 7px 8px;
        font-size: 9pt;
        font-weight: bold;
        text-align: center;
        text-transform: uppercase;
    }
    .pond-table tbody td {
        border: 1px solid #bbb;
        padding: 5px 8px;
        vertical-align: middle;
        font-size: 9pt;
    }
    .pond-table tbody tr:nth-child(even) td { background-color: #f8f8f8; }
    .pond-table .num { text-align: center; font-weight: bold; width: 30px; }
    .pond-table .center { text-align: center; }
    .pond-table .puntaje { font-weight: bold; color: #4f46e5; }
    .pond-table .total-row td {
        background: #e2e8f0;
        font-weight: bold;
        font-size: 10pt;
        border: 1px solid #999;
    }
    .pond-table .total-row .puntaje-total {
        font-size: 12pt;
        color: #1e293b;
    }
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

<htmlpageheader name="pondHeader">
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
        <div style="font-size:11pt;font-weight:bold;text-transform:uppercase;margin:2px 0;color:#1a1a1a;">Detalle de Ponderación</div>
        <div style="font-size:8pt;color:#555;margin:1px 0;">{{ $ponderacion->nombre }} | {{ date('d/m/Y H:i:s') }}</div>
    </div>
</htmlpageheader>

<htmlpagefooter name="pondFooter">
    <div style="text-align:center;font-size:8pt;color:#888;">Pág. {PAGENO} de {nb}</div>
</htmlpagefooter>

@if ($multiplicador)
<div class="info-box">
    <strong>Multiplicador:</strong> {{ $multiplicador->nombre }}
    &nbsp;|&nbsp; <strong>Correcta:</strong> {{ $multiplicador->correcta }}
    &nbsp;|&nbsp; <strong>Incorrecta:</strong> {{ $multiplicador->incorrecta }}
    &nbsp;|&nbsp; <strong>Blanco:</strong> {{ $multiplicador->blanco }}
</div>
@endif

<table class="pond-table">
    <thead>
        <tr>
            <th class="num">N°</th>
            <th>Asignatura</th>
            <th>Cant. Preguntas</th>
            <th>Ponderación</th>
            <th>Subtotal</th>
            @if ($multiplicador)
                <th>Bien Cont.</th>
                <th>Puntaje</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @php $n = 1; @endphp
        @foreach ($detalles as $d)
            <tr>
                <td class="num">{{ $n }}</td>
                <td>{{ $d->asignatura }}</td>
                <td class="center">{{ $d->cantidad_preguntas }}</td>
                <td class="center">{{ number_format($d->ponderacion, 3) }}</td>
                <td class="center">{{ number_format($d->subtotal, 3) }}</td>
                @if ($multiplicador)
                    <td class="center">{{ number_format($correcta, 3) }}</td>
                    <td class="center puntaje">{{ number_format($d->subtotal * $correcta, 3) }}</td>
                @endif
            </tr>
            @php $n++; @endphp
        @endforeach
        <tr class="total-row">
            <td colspan="2" style="text-align:right;">TOTAL →</td>
            <td class="center">{{ $totalPreguntas }}</td>
            <td></td>
            <td class="center">{{ number_format($totalPonderacion, 3) }}</td>
            @if ($multiplicador)
                <td></td>
                <td class="center puntaje-total">{{ number_format($totalPuntaje, 3) }}</td>
            @endif
        </tr>
    </tbody>
</table>

<div class="signature">
    <div class="signature-line"></div>
    Responsable de Calificación
</div>

</body>
</html>
