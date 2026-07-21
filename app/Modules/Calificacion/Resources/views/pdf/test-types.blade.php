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
        margin: 4cm 1cm 2cm 1cm;
        header: html_testTypesHeader;
        footer: html_testTypesFooter;
    }
    body { font-family: Helvetica, Arial, sans-serif; margin: 0; padding: 0; }
    .header-classroom { text-align: center; margin-top: 4px; padding: 4px 0; border: 1px solid #667eea; background: #667eea; color: #fff; border-radius: 4px; }
    .header-classroom h3 { margin: 0; font-size: 11pt; font-weight: bold; }
    .header-classroom p { margin: 0; font-size: 8pt; }
    .front-indicator { text-align: center; font-size: 9pt; font-weight: bold; color: #333; margin-bottom: 5px; letter-spacing: 2px; }
    table.grid { border-collapse: collapse; margin: 0 auto; }
    table.grid td { width: 120px; height: 75px; border: 1px solid #999; vertical-align: middle; padding: 2px; text-align: center; font-size: 42pt; font-weight: bold; }
    td.empty { background: #f5f5f5; color: #ccc; font-size: 14pt; font-weight: normal; }
    .pos-num { font-size: 7pt; color: #999; font-weight: normal; position: relative; top: -3px; }
    .footer-note { text-align: center; margin-top: 8px; font-size: 7pt; color: #999; }
</style>
</head>
<body>

<htmlpageheader name="testTypesHeader">
    <table style="width:100%;border:none;border-collapse:collapse;">
        <tr>
            <td style="border:none;padding:0;text-align:right;width:60px;">
                @if (file_exists($logoIzq))
                    <img src="{{ $logoIzq }}" width="55"/>
                @endif
            </td>
            <td style="width:100%;text-align:center;border:none;padding:0;">
                <div style="text-align:center;">
                    <div style="font-size:10pt;font-weight:bold;margin:1px 0;">UNIVERSIDAD NACIONAL DEL ALTIPLANO DE PUNO</div>
                    <div style="font-size:9pt;font-weight:bold;margin:1px 0;">VICERRECTORADO ACADÉMICO</div>
                    <div style="font-size:9pt;font-weight:bold;margin:1px 0;">DIRECCIÓN DE ADMISIÓN</div>
                </div>
            </td>
            <td style="border:none;padding:0;text-align:left;width:70px;">
                @if (file_exists($logoDer))
                    <img src="{{ $logoDer }}" width="65"/>
                @endif
            </td>
        </tr>
    </table>
    <div style="border-bottom:1px solid #ccc;margin-top:3px;"></div>
    <div style="text-align:center;margin-top:3px;">
        <div style="font-size:10pt;font-weight:bold;text-transform:uppercase;margin:1px 0;">DISTRIBUCIÓN DE TIPOS DE EXAMEN</div>
        <div style="font-size:8pt;color:#666;margin:1px 0;">Grupo #{{ $filterGroupId }} | {{ count($classrooms) }} salones | {{ date('d/m/Y H:i:s') }}</div>
    </div>
</htmlpageheader>

<htmlpagefooter name="testTypesFooter">
    <div style="text-align:center;font-size:8pt;color:#666;">Pág. {PAGENO} de {nb}</div>
</htmlpagefooter>

@foreach ($classrooms as $c)
    @php
        $byPos = [];
        foreach ($c->assignments as $a) {
            $byPos[$a->posicion] = $a;
        }
    @endphp
    <div class="">
        <h3>{{ $c->nombre }}</h3>
        <p>Capacidad: {{ $c->capacidad }}</p>
    </div>
    <div class="front-indicator">&#9650; FRENTE DEL AULA &#9650;</div>
    <table class="grid">
        @for ($row = 1; $row <= 8; $row++)
            <tr>
                @for ($col = 1; $col <= 5; $col++)
                    @php $pos = ($col - 1) * 8 + $row; @endphp
                    @php $a = $byPos[$pos] ?? null; @endphp
                    @if ($a)
                        <td>{{ $a->tipo_examen ?: '-' }}<br><span class="pos-num">P{{ $pos }}</span></td>
                    @else
                        <td class="empty">&mdash;<br><span class="pos-num">P{{ $pos }}</span></td>
                    @endif
                @endfor
            </tr>
        @endfor
    </table>
    {{-- <div class="front-indicator" style="margin-top:5px">&#9660; FRENTE DEL AULA &#9660;</div>
    <div class="footer-note">Tipos: P, Q, R, S, T</div> --}}
    <div style="page-break-after:always"></div>
@endforeach
</body>
</html>
