@php
    $counter = 1;
    $fotosBase = $thumbFotosBase;
    $huellasBase = $thumbHuellasBase;
@endphp

<table class="student-table" style="margin-top: 0px;">
    <tbody>
        @foreach ($c->assignments as $a)
            @php
                $dni = $a->n_documento;
                $fotoPath = "{$fotosBase}/{$dni}.jpg";
                $huellaDerPath = "{$huellasBase}/{$dni}.jpg";
                $huellaIzqPath = "{$huellasBase}/{$dni}x.jpg";
            @endphp
            <tr>
                <td colspan="5">
                    <div class="name" style="font-size: 10pt;">{{ $a->paterno . ' ' . $a->materno . ', ' . $a->nombres }}</div>
                </td>
                <td colspan="3">
                    {{ $a->programa_estudios ?? '-'}}
                </td>
            </tr>
            <tr>
                <td class="">
                    <div style="font-size:1rem;">
                        {{ $c->nombre }}
                    </div>
                </td>

                {{-- Huella Derecha (imagen) --}}
                <td style="padding:1px; text-align:center;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td>
                                <div style="width:60px; height:60px; border:1px solid #000; padding:1px; overflow:hidden; margin:0 auto;">
                                    @if (file_exists($huellaIzqPath))
                                        <img src="{{ $huellaIzqPath }}" style="width:52px; height:60px;" alt="Huella Izq"/>
                                    @else
                                        <div style="font-size:5pt; text-align:center; line-height:60px;">Sin huella</div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:none;">
                                <span style="font-size:4pt;">Huella derecha Ins</span>
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- Huella Derecha (caja para entintar) --}}
                <td style="padding:1px; text-align:center;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="height:65px; width:60px; border:1px solid #000;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="border:none;">
                                <span style="font-size:4pt;">Huella derecha</span>
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- Huella Izquierda (imagen) --}}

                <td style="padding:1px; text-align:center;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="height:65px; width:60px;">
                                    <div style="width:55px; height:60px; border:1px solid #000; overflow:hidden; margin:0 auto;">
                                    @if (file_exists($huellaIzqPath))
                                        <img src="{{ $huellaIzqPath }}" style="width:52px; height:60px;" alt="Huella Izq"/>
                                    @else
                                        <div style="font-size:5pt; text-align:center; line-height:60px;">Sin huella</div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border:none;">
                                <span style="font-size:4pt;">Huella derecha Ins</span>
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- Huella Izquierda (caja para entintar) --}}
                <td style="padding:1px; text-align:center;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="height:65px; width:60px; border:1px solid #000;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="border:none;">
                                <span style="font-size:4pt;">Huella izquierda</span>
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- Firma --}}
                <td style="padding:1px; width:110px; text-align:center;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="height:65px; width:150px; border-bottom:1px solid #000;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="border:none;">
                                <span style="font-size:4pt; border-top:solid 0.5pt #9d9d9d;">Firma del postulante</span>
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- Foto --}}
                <td class="photo-cell" style="padding:1px;">
                    @if (file_exists($fotoPath))
                        <img src="{{ $fotoPath }}" style="width: 60px;" alt="Foto {{ $dni }}"/>
                    @else
                        <div class="no-photo">Sin<br>foto</div>
                    @endif
                </td>

                {{-- Número y tipo --}}
                <td class="num-cell" style="padding:1px;">
                    <div>
                        {{ $counter }}
                    </div>
                    <div>
                        {{ $a->tipo_examen ?: '-' }}
                    </div>
                </td>
            </tr>
            @php $counter++; @endphp
        @endforeach
    </tbody>
</table>

<div style="page-break-after:always"></div>