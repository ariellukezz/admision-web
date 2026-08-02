<table style="width: 100%; border: none; font-family: 'dejavusanscondensed', sans-serif;">
    <tr>
        <td style="width: 70px; text-align: left; border: none; padding: 4px 6px;">
            <img src="{{ public_path('imagenes/logotiny.png') }}" width="55">
        </td>
        <td style="text-align: center; border: none; padding: 4px 6px;">
            <div style="font-size: 11pt; font-weight: 500; color: #0f172a;">Universidad Nacional del Altiplano de Puno</div>
            <div style="font-size: 10pt; color: #334155;">Dirección de Admisión</div>
            <div style="margin-top: 4px;">
                <span style="font-size: 10pt; font-weight: 600; color: #1e293b; background: #f1f5f9; padding: 2px 12px; border-radius: 4px;">
                    {{ $titulo }} — {{ $proceso->nombre ?? 'Proceso de Admisión' }}
                </span>
            </div>
        </td>
        <td style="width: 70px; text-align: right; border: none; padding: 4px 6px;">
            <img src="{{ public_path('imagenes/logoDAD.png') }}" width="60">
        </td>
    </tr>
</table>
<div style="border-bottom: 1.5px solid #1e293b; margin: 6px 0 0 0;"></div>
@if ($sorteo->nombre ?? null)
<div style="font-size: 8pt; color: #64748b; text-align: center; margin-top: 2px;">
    Sorteo: {{ $sorteo->nombre }}@if ($sorteo->descripcion ?? null) — {{ $sorteo->descripcion }}@endif
</div>
@endif
