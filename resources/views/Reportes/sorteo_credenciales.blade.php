<style>
    * { margin: 0; padding: 0; }

    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        background: #ffffff;
    }

    table { border-collapse: collapse; }

    /* ===== Grid ===== */
    .cred-grid {
        width: 210mm;
        table-layout: fixed;
        border-collapse: collapse;
    }

    .cred-grid > tr > td {
        width: 70mm;
        height: 50mm;
        padding: 2mm 1.5mm;
        vertical-align: top;
    }

    /* ===== Card estilo DNIe ===== */
    .dni-card {
        width: 67mm;
        height: 46mm;
        background: #ffffff;
        overflow: hidden;
        box-sizing: border-box;
        position: relative;
    }

    .dni-inner {
        width: 100%;
        height: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .dni-inner > tbody > tr > td {
        padding: 0;
        vertical-align: top;
    }

    /* ===== Header ===== */
    .dni-header {
        height: 6mm;
        background: #10243f;
        padding: 0.8mm 2mm !important;
        vertical-align: middle;
    }

    .dni-header-inner {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .dni-header-inner td {
        padding: 0;
        vertical-align: middle;
    }

    .dni-logo-cell {
        width: 6mm;
        text-align: center;
    }

    .dni-logo {
        width: 4.5mm;
        height: 4.5mm;
        display: block;
        margin: 0 auto;
    }

    .dni-head-text {
        text-align: center;
        padding: 0 1mm !important;
    }

    .dni-head-main {
        font-size: 4.5pt;
        font-weight: 800;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.15pt;
        line-height: 1.1;
    }

    .dni-head-sub {
        margin-top: 0.3mm;
        font-size: 3pt;
        font-weight: 500;
        color: #d7b85b;
        text-transform: uppercase;
        letter-spacing: 0.3pt;
        line-height: 1;
    }

    /* ===== Banda dorada ===== */
    .dni-gold {
        height: 0.6mm;
        line-height: 0.6mm;
        background: #c9a227;
        font-size: 0;
    }

    /* ===== Body ===== */
    .dni-body {
        height: 34.4mm;
        vertical-align: top;
        background-color: #f0f4fa;
        background-image:
            repeating-radial-gradient(circle at 0% 50%, transparent 0, transparent 4px, rgba(16,36,63,0.13) 4px, rgba(16,36,63,0.13) 6px),
            repeating-radial-gradient(circle at 100% 50%, transparent 0, transparent 3px, rgba(201,162,39,0.10) 3px, rgba(201,162,39,0.10) 5px),
            repeating-radial-gradient(circle at 50% 0%, transparent 0, transparent 2px, rgba(16,36,63,0.11) 2px, rgba(16,36,63,0.11) 4px),
            repeating-radial-gradient(circle at 50% 100%, transparent 0, transparent 5px, rgba(184,134,11,0.09) 5px, rgba(184,134,11,0.09) 7px),
            repeating-radial-gradient(circle at 50% 50%, transparent 0, transparent 3px, rgba(26,68,128,0.10) 3px, rgba(26,68,128,0.10) 5px);
    }

    .dni-body-inner {
        width: 100%;
        height: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .dni-body-inner td {
        padding: 0;
        vertical-align: top;
    }

    /* --- Foto --- */
    .dni-photo-col {
        width: 22mm;
        padding: 1.5mm 0 1.5mm 2mm !important;
        vertical-align: top;
    }

    .dni-photo-frame {
        width: 16mm;
        height: 22mm;
        overflow: hidden;
        border: 0.5pt solid #10243f;
    }

    .dni-photo {
        width: 16mm;
        height: 22mm;
        display: block;
    }

    .dni-photo-empty {
        width: 16mm;
        height: 22mm;
        display: block;
        text-align: center;
        font-size: 3.5pt;
        color: #6b7280;
        font-weight: 700;
        line-height: 22mm;
        background: #e5e7eb;
        letter-spacing: 0.3pt;
    }

    /* --- Datos --- */
    .dni-data-col {
        vertical-align: top;
        padding: 1.5mm 2mm 1.5mm 1.5mm !important;
    }

    .dni-field {
        margin-bottom: 0.6mm;
    }

    .dni-label {
        font-size: 2.8pt;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.2pt;
        line-height: 1;
    }

    .dni-value {
        margin-top: 0.3mm;
        font-size: 4.5pt;
        font-weight: 600;
        color: #10243f;
        line-height: 1.2;
    }

    .dni-value-name {
        margin-top: 0.3mm;
        font-size: 6pt;
        font-weight: 800;
        color: #10243f;
        text-transform: uppercase;
        line-height: 1.15;
    }

    .dni-value-dni {
        margin-top: 0.3mm;
        font-size: 8pt;
        font-weight: 900;
        color: #10243f;
        font-family: 'Courier New', monospace;
        letter-spacing: 0.4pt;
        line-height: 1;
    }

    .dni-value-cargo {
        margin-top: 0.3mm;
        font-size: 4pt;
        font-weight: 700;
        color: #c9a227;
        text-transform: uppercase;
        letter-spacing: 0.2pt;
        line-height: 1.1;
    }

    .dni-sep {
        height: 0.3mm;
        background: #c9a227;
        margin: 0.6mm 0;
        font-size: 0;
        line-height: 0;
    }

    /* ===== Footer ===== */
    .dni-footer {
        height: 5mm;
        background: #10243f;
        padding: 0 2mm !important;
        vertical-align: middle;
    }

    .dni-footer-inner {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .dni-footer-inner td {
        padding: 0;
        vertical-align: middle;
    }

    .dni-foot-process {
        font-size: 2.8pt;
        color: #d7b85b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.2pt;
    }

    .dni-foot-code {
        font-size: 3pt;
        color: #ffffff;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        letter-spacing: 0.3pt;
    }

    /* ===== Vacio ===== */
    .cred-empty {
        width: 100%;
        text-align: center;
        padding: 50mm 0;
        font-size: 16pt;
        color: #94a3b8;
        font-weight: 300;
    }

    /* ===== Print ===== */
    @media print {
        .dni-header { background: #10243f !important; }
        .dni-gold { background: #c9a227 !important; }
        .dni-footer { background: #10243f !important; }
                .dni-sep { background: #c9a227 !important; }
        .dni-photo-empty { background: #e5e7eb !important; }
    }
</style>


@if ($seleccionados->isEmpty())

    <div class="cred-empty">No hay participantes seleccionados.</div>

@else

    <table class="cred-grid">

        @php $i = 0; @endphp

        @foreach ($seleccionados as $p)

            @if ($i % 3 === 0)
                @if ($i > 0) </tr> @endif
                <tr>
            @endif

            <td>

                <div class="dni-card">

                    <table class="dni-inner">

                        {{-- Header --}}
                        <tr>
                            <td class="dni-header">
                                <table class="dni-header-inner">
                                    <tr>
                                        <td class="dni-logo-cell">
                                            @if (!empty($logo_tiny_base64))
                                                <img class="dni-logo" src="{{ $logo_tiny_base64 }}" />
                                            @endif
                                        </td>
                                        <td class="dni-head-text">
                                            <div class="dni-head-main">UNIVERSIDAD NACIONAL DEL ALTIPLANO</div>
                                            <div class="dni-head-sub">{{ $proceso->nombre ?? 'DIRECCIÓN DE ADMISIÓN' }}</div>
                                        </td>
                                        <td class="dni-logo-cell">
                                            @if (!empty($logo_dad_base64))
                                                <img class="dni-logo" src="{{ $logo_dad_base64 }}" />
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        {{-- Banda dorada --}}
                        <tr>
                            <td class="dni-gold">&nbsp;</td>
                        </tr>

                        {{-- Body --}}
                        <tr>
                            <td class="dni-body">
                                <table class="dni-body-inner">
                                    <tr>

                                        {{-- Foto --}}
                                        <td class="dni-photo-col">
                                            <div class="dni-photo-frame">
                                                @if (!empty($p->foto_base64))
                                                    <img class="dni-photo" src="{{ $p->foto_base64 }}" />
                                                @else
                                                    <div class="dni-photo-empty">SIN FOTO</div>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Datos --}}
                                        <td class="dni-data-col">

                                            <div class="dni-field">
                                                <div class="dni-label">Apellido Paterno</div>
                                                <div class="dni-value">{{ $p->paterno ?? '—' }}</div>
                                            </div>

                                            <div class="dni-field">
                                                <div class="dni-label">Apellido Materno</div>
                                                <div class="dni-value">{{ $p->materno ?? '—' }}</div>
                                            </div>

                                            <div class="dni-field">
                                                <div class="dni-label">Nombres</div>
                                                <div class="dni-value-name">{{ $p->nombres ?? '—' }}</div>
                                            </div>

                                            <div class="dni-sep">&nbsp;</div>

                                            <div class="dni-field">
                                                <div class="dni-label">Documento de Identidad</div>
                                                <div class="dni-value-dni">{{ $p->dni ?? '—' }}</div>
                                            </div>

                                            <div class="dni-field">
                                                <div class="dni-label">Cargo Asignado</div>
                                                <div class="dni-value-cargo">{{ $p->cargo ?? '—' }}</div>
                                            </div>

                                            <div class="dni-field">
                                                <div class="dni-label">Código · Tipo · Condición</div>
                                                <div class="dni-value">{{ $p->codigo_trabajador ?? '—' }} · {{ $p->tipo_personal ?? '—' }} · {{ $p->condicion ?? '—' }}</div>
                                            </div>

                                        </td>

                                    </tr>
                                </table>
                            </td>
                        </tr>

                        {{-- Footer --}}
                        <tr>
                            <td class="dni-footer">
                                <table class="dni-footer-inner">
                                    <tr>
                                        <td align="left">
                                            <span class="dni-foot-process">ADMISIÓN</span>
                                        </td>
                                        <td align="right">
                                            <span class="dni-foot-code">{{ $proceso->codigo_proceso ?? '' }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                    </table>

                </div>

            </td>

            @php $i++; @endphp

            @if ($i % 3 === 0 || $loop->last)
                </tr>
            @endif

        @endforeach

    </table>

@endif
