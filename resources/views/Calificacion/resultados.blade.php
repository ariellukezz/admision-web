<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  @php
    ini_set("pcre.backtrack_limit", "10000000");
    ini_set("pcre.recursion_limit", "10000000");
    ini_set("memory_limit", "512M");
@endphp
  <title>Resultados {{$convocatoria->nombre}}</title>
  <style>
    @page {
      header: page-header;
      footer: page-footer;
      margin-top: 70px;
      margin-bottom: 40px;
      margin-left: 10mm;
      margin-right: 10mm;
    }
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      margin: 0;
      padding: 0;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }
    th {
      background-color: #f4f4f4;
    }
    .page-break {
            page-break-before: always;
        }
  </style>
</head>
<body>

    <htmlpageheader name="page-header">
        <table style="width:100%; padding-bottom:5px; border-bottom: 1px solid #d9d9d9;">
            <tr>
            <td style="text-align:left; width:60px; border:none;">
                <img src="http://admision.test/imagenes/logo_poder_judicial.png" width="64">
            </td>
            <td style="text-align:center; font-size:16px; border:none;">
                <div>CORTE SUPERIOR DE JUSTICIA DE PUNO</div>
                <div style="text-align:center; font-size:12px; border:none;">PROCESO DE SELECCIÓN PERSONAL 728 A PLAZO INDETERMINADO N° 001-2025-UE-PUNO</div>
                <div></div>
            </td>
            <td style="text-align:right; font-size:11px; width:90px; border:none;">
                <div>
                    <span>Fecha y hora</span>
                </div>
                <div>{DATE j-m-Y}</div>
                <div>{DATE H:i:s}</div>
            </td>
            </tr>
        </table>
    </htmlpageheader>

  <htmlpagefooter name="page-footer" style="width: 100%;">
    <div style="text-align: center; font-size: 12px; border-top: 1px solid #d9d9d9; padding-top: 5px;">
      Página {PAGENO} de {nbpg}
    </div>
  </htmlpagefooter>

  <div class="content">

    <div>
      @if (count($datos) > 0 )
          @foreach ($datos as $item)
          <div style="width: 100%; text-align:center; font-size:10pt;">
              <div>
                  <div><span style=" font-size:11pt; letter-spacing: .05rem; text-transform:uppercase">
                      DEPENDENCIA {{ $item['unidad'] }}    
                  </span></div>

                    <div style="font-size: 12pt; font-weight:bold;">
                      <span style="text-transform: uppercase"> {{ $item['puesto'] }} </span>
                    </div>
                    <div style="font-size: 12pt;">
                      <span style=""> CÓDIGO DE PLAZA {{ $item['cod_puesto'] }} </span>
                    </div>

              </div>

              <table style="width: 100%" class="l-table">
                <thead>

                    <tr style="background:#e4e4e4;">
                        {{-- <th><div style="text-align: center; width:50px;">PUESTO</div></th> --}}
                        <th><div style="text-align: left; width:80px;">NRO</div></th>
                        <th align="center"><div style="text-align: center; width:80px;">DNI</div></th>
                        <th><div style="text-align: left;">APELLIDOS Y NOMBRES</div></th>
                        <th><div style="text-align: left;">PUNTAJE </div></th>
                    </tr>
                </thead>
                @if (count($item['data']) > 0 )
                <tbody>
                    @foreach($item['data'] as $index => $itemEst)
                        <tr>
                            <td width="40px" align="center">{{ $index+1 }}</td>
                            <td width="90px" align="center"> {{ $itemEst['dni'] }}</td>
                            <td align="left">{{ $itemEst['paterno'] }} {{ $itemEst['materno'] }} {{ $itemEst['nombres'] }}</td>
                            <td align="center" width="60px">{{ $itemEst['puntaje'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @else
                <tbody>
                    <tr>
                        <td colspan="5">
                            <div style="text-align: center">
                                No se encontraron datos
                            </div>
                        </td>
                    </tr>
                </tbody>

                @endif

            </table>

          </div>

          {{-- <div style="margin-top:10px; font-size: .8rem;">
              <table style="width: 100%" class="l-table">
                  <thead>
                      <tr style="background:#e4e4e4;">
                          <th><div style="text-align: center;">N°</div></th>
                          <th><div style="text-align: left;">APELLIDOS Y NOMBRES</div></th>
                      </tr>
                  </thead>
                  @if (count($item['data']) > 0 )
                  <tbody>
                      @foreach ($item['data'] as $index=>$ite)
                      <tr>
                          <td><div style="text-align: center;">{{ $index + 1 }}</div> </td>
                          <td><div style="text-align: center;">{{ $ite['nombres']}}</div> </td>
                      </tr>
                      @endforeach
                  </tbody>
                  @else
                  <tbody>
                      <tr>
                          <td colspan="5">
                              <div style="text-align: center">
                                  No se encontraron datos
                              </div>
                          </td>
                      </tr>
                  </tbody>

                  @endif

              </table>
          </div> --}}

          @if (!$loop->last)
              <div class="page-break"></div>
          @endif
          </div>
          @endforeach
          @endif
      </div>
  </div>
</body>
</html>
