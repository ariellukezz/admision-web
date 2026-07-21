@php
    $logoIzq = public_path('imagenes/logotiny.png');
    $logoDer = public_path('imagenes/logoDAD.jpg');
    $izqWidth = $logoWidth ?? 65;
    $derWidth = $izqWidth + 10;
    $izqTdWidth = $izqWidth + 5;
    $derTdWidth = $izqWidth + 15;
@endphp

<div id="header">
    <table class="header-logos">
        <tr>
            <td align="right" width="{{ $izqTdWidth }}">
                @if (file_exists($logoIzq))
                    <img src="{{ $logoIzq }}" width="{{ $izqWidth }}"/>
                @endif
            </td>
            <td style="width:100%;text-align:center">
                <div class="header-title">
                    <h2 style="font-size: 20pt; font-weight:400;">UNIVERSIDAD NACIONAL DEL ALTIPLANO DE PUNO</h2>
                    @isset($subtitle1)
                        <h3 style="font-size:20pt; font-weight:400; letter-spacing: 1.2pt;">{{ $subtitle1 }}</h3>
                    @endisset
                    @isset($subtitle2)
                        <h3 style="font-size:20pt; font-weight:400; letter-spacing: 1.1pt;">{{ $subtitle2 }}</h3>
                    @endisset
                </div>
            </td>
            <td align="left" width="{{ $derTdWidth }}">
                @if (file_exists($logoDer))
                    <img src="{{ $logoDer }}" width="{{ $derWidth }}"/>
                @endif
            </td>
        </tr>
    </table>
    <div class="header-divider"></div>
    <div class="header-sub">
        <h4>{{ $documentTitle }}</h4>
        <p>{{ $documentSubtitle }}</p>
    </div>
</div>

<div id="footer">Pág. {PAGENO} de {nb}</div>
