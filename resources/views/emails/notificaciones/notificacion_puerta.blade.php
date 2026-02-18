<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NOTIFICACIÓN PUERTA DE INGRESO - EXAMEN EXTRAORDINARIO 2026</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
  </style>
</head>
<body style="margin: 0; padding: 0; font-family: 'Roboto', Arial, sans-serif; background-color: #f5f7fa; color: #2d3748;">

  <!-- Contenedor principal -->
  <div style="max-width: 700px; margin: 30px auto; background: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden;">

    <!-- Cabecera institucional -->
    <div style="background: #1a365d; padding: 20px 30px; display: flex; align-items: center; justify-content: space-between;">
        <div style="flex: 0 0 auto;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/cb/Logo_UNAP.png/250px-Logo_UNAP.png" alt="Logo UNA" style="height: 60px;">
        </div>
        
        <div style="flex: 1; text-align: center; color: white;">
            <h1 style="margin: 0; font-size: 18px; font-weight: 500;">UNIVERSIDAD NACIONAL DEL ALTIPLANO</h1>
            <p style="margin: 5px 0 0; font-size: 12px; opacity: 0.9;">RUC: 20145496170 | Av. Floral Nº 1153 - Puno</p>
        </div>
        
        <div style="flex: 0 0 auto;">
          <div style="color: white"> Fecha</div> 
          <div style="color: white"> de envío:</div>
          <div style="color: white"> 29/01/26</div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div style="padding: 30px;">

      <!-- Título -->
      <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px;">
        <h2 style="color: #1a365d; font-size: 24px; margin: 0 0 5px; font-weight: 600;">NOTIFICACIÓN DE PUERTA DE ENTRADA</h2>
        <p style="color: #4a5568; font-size: 16px; margin: 0; font-weight: 500;">EXAMEN DE ADMISIÓN EXTRAORDINARIO 2026</p>
      </div>

      <!-- Saludo personalizado -->
      <p style="font-size: 16px; margin-bottom: 25px;">Estimado(a) postulante,</p>

      <!-- Datos del postulante -->
      <div style="background: #f8fafc; border-left: 4px solid #2b6cb0; padding: 15px; margin-bottom: 25px; border-radius: 0 4px 4px 0;">
        <p style="font-size: 16px; font-weight: 600; color: #1a365d; margin: 0 0 10px;">{{ $nombre }} </p>
        {{-- <p style="font-size: 16px; font-weight: 600; color: #1a365d; margin: 0 0 10px;"> Jhon Ariel Luque Cusacani </p> --}}
        {{-- <p style="margin: 5px 0;"><strong style="color: #4a5568;">Programa:</strong> Ingeniería de sistemas</p> --}}
        <p style="margin: 5px 0;"><strong style="color: #4a5568;">Programa:</strong> {{ $programa }}</p>
        <p style="margin: 5px 0; margin-top:10px; font-size:16pt; "><strong style="color: #ff7700;"><span style="color: #1a365d;">PUERTA DE INGRESO:</span> {{ $puerta }}</strong ></p>
        {{-- <p style="margin: 5px 0; margin-top:10px; font-size:16pt; "><strong style="color: #ff7700;"><span style="color: #1a365d;">PUERTA DE INGRESO:</span> Puerta principal</strong ></p> --}}
      </div>

      <!-- Información importante -->
      <div style="margin-bottom: 30px;">
        <h3 style="color: #1a365d; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px;">INFORMACIÓN IMPORTANTE</h3>
        
        <div style="display: flex; margin-bottom: 15px;">
          <div style="flex: 1; padding-right: 15px;">
            <p style="margin: 0 0 5px; font-weight: 500;">📅 <strong>Fecha del examen:</strong></p>
            <p style="margin: 0; color: #2b6cb0; font-weight: 600;">Viernes, 30 de enero de 2026</p>
          </div>
          <div style="flex: 1;">
            <p style="margin: 0 0 5px; font-weight: 500;">⏰ <strong>Horario de ingreso:</strong></p>
            <p style="margin: 0; color: #2b6cb0; font-weight: 600;">08:00 a.m. - 9:30 a.m.</p>
          </div>
        </div>      
        {{-- <p style="margin: 0 0 15px;"><strong>📍 Ubicación:</strong> Campus Universitario - Puno (INGRESO POR LA PUERTA PRINCIAL)</p> --}}
        <p style="margin: 0 0 15px;"><strong>📍 Ubicación:</strong> Campus Universitario - Puno (INGRESO POR LA {{ $puerta }})</p>
      </div>

      <!-- Requisitos -->
      <div style="background: #fffaf0; border: 1px solid #fed7aa; border-radius: 6px; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #9c4221; font-size: 16px; margin: 0 0 10px;">📋 DOCUMENTOS REQUERIDOS</h3>
        <ul style="margin: 0; padding-left: 20px;">
          <li style="margin-bottom: 5px;">Documento Nacional de Identidad (DNI) <strong>original</strong></li>
          <li style="margin-bottom: 5px;">Constancia de inscripción impresa</li>
        </ul>
      </div>

      <!-- Prohibiciones -->
      <div style="margin-bottom: 30px;">
        <h3 style="color: #1a365d; font-size: 18px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px;">NORMATIVA DEL EXAMEN</h3>
        <p style="margin-bottom: 10px;">Está <strong style="color: #c53030;">terminantemente prohibido</strong> ingresar con:</p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
          <div style="display: flex; align-items: flex-start;">
            <span style="color: #c53030; margin-right: 8px;">✖</span>
            <span>Dispositivos electrónicos, (lentes inteligentes, smartwatches o celulares )</span>
          </div>
          <div style="display: flex; align-items: flex-start;">
            <span style="color: #c53030; margin-right: 8px;">✖</span>
            <span>Lápiz, borrador o tajador</span>
          </div>
          <div style="display: flex; align-items: flex-start;">
            <span style="color: #c53030; margin-right: 8px;">✖</span>
            <span>Aretes, collares o pulseras</span>
          </div>
          <div style="display: flex; align-items: flex-start;">
            <span style="color: #c53030; margin-right: 8px;">✖</span>
            <span>Prendas con capucha</span>
          </div>
          <div style="display: flex; align-items: flex-start;">
            <span style="color: #c53030; margin-right: 8px;">✖</span>
            <span>Bolsos o mochilas</span>
          </div>
          <div style="display: flex; align-items: flex-start;">
            <span style="color: #c53030; margin-right: 8px;">✖</span>
            <span>Alimentos o bebidas</span>
          </div>
        </div>
        <p style="margin: 15px 0 0; font-size: 14px; color: #718096;">
          <strong>Nota:</strong> No se proporcionará custodia para objetos personales. Quienes incumplan estas normas no podrán ingresar al examen.
        </p>
      </div>

      <!-- Recomendaciones -->
      <div style="background: #f0fff4; border: 1px solid #9ae6b4; border-radius: 6px; padding: 15px;">
        <h3 style="color: #276749; font-size: 16px; margin: 0 0 10px;">📌 RECOMENDACIONES</h3>
        <ul style="margin: 0; padding-left: 20px;">
          {{-- <li style="margin-bottom: 5px;">Presentarse con <strong>45 minutos de anticipación</strong></li> --}}
          <li style="margin-bottom: 5px;">Portar indumentaria sencilla sin capucha</li>
          <li style="margin-bottom: 5px;">Mantener visible el cuellos, orejas y el cabello recogido o corto</li>
          <li>Revisar con anticipación la ubicación de su puerta de ingreso</li>
        </ul>
      </div>
      <div style="background: #fff2f0ff; border: 1px solid #e6a09aff; border-radius: 6px; padding: 15px; margin-top:16px;">
        <h3 style="color: #672727ff; font-size: 16px; margin: 0 0 10px;">⚠️ ADVERTENCIA</h3>
        <ul style="margin: 0; padding-left: 20px;">
          <li>
          Esta prohibido arrancar hojas del examen de admisión. En caso de esta infracción, se anulará su proceso de califiación
          conforme al Art. 37 del Reglamento General de Admisión 2026-I.
          </li>
        </ul>
      </div>

    </div>
    <!-- Pie de página -->
    <div style="background: #edf2f7; padding: 20px; text-align: center; font-size: 12px; color: #4a5568;">
      <p style="margin: 0 0 5px;">© 2026 Universidad Nacional del Altiplano - Puno</p>
      <p style="margin: 0; font-size: 11px;">Todos los derechos reservados | Dirección de Admisión</p>
    </div>

  </div>

</body>
</html>