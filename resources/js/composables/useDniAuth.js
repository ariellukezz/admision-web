/**
 * Flujo de autenticación con DNIe para Vue 3.
 *
 * El navegador sólo hace tres cosas: pedir una sesión, abrir el enlace que lanza el
 * conector instalado en el equipo, y sondear el estado. Nunca ve el PIN, nunca habla con
 * el conector y nunca sostiene el token: cuando la sesión se completa, lo único que recibe
 * es un código de un solo uso que le entrega a su propio backend (Laravel).
 *
 * Uso:
 *
 *   const { estado, mensaje, requiereInstalacion, iniciar, cancelar } = useDniAuth({
 *     authUrl:  import.meta.env.VITE_DNIAUTH_URL,
 *     clientId: import.meta.env.VITE_DNIAUTH_CLIENT_ID,
 *     rutaCallback: '/auth/dnie/callback',
 *   })
 */
import { ref, onUnmounted } from 'vue'

const INTERVALO_SONDEO_MS = 1000
const ESPERA_ANTES_DE_SUGERIR_INSTALACION_MS = 12000

export function useDniAuth (config) {
  const { authUrl, clientId, rutaCallback = '/auth/dnie/callback' } = config

  const estado = ref('inactivo')          // inactivo | iniciando | esperandoConector | enCurso | completado | error
  const mensaje = ref('')
  const requiereInstalacion = ref(false)
  const urlDescarga = ref('')

  let cancelado = false
  let temporizador = null

  function limpiar () {
    cancelado = true
    if (temporizador) { clearTimeout(temporizador); temporizador = null }
  }

  onUnmounted(limpiar)

  async function pedirJson (url, opciones = {}) {
    const respuesta = await fetch(url, {
      credentials: 'omit',
      cache: 'no-store',
      ...opciones,
      headers: { 'Content-Type': 'application/json', ...(opciones.headers || {}) },
    })

    const datos = await respuesta.json().catch(() => null)

    if (!respuesta.ok) {
      const error = new Error(datos?.mensaje || `Error ${respuesta.status}`)
      error.codigo = datos?.codigo || 'ErrorServicio'
      throw error
    }

    return datos
  }

  /**
   * Lanza el conector navegando en el frame PRINCIPAL.
   *
   * Chrome y Edge sólo permiten abrir un protocolo externo desde el frame principal y con
   * activación del usuario: desde un iframe lo bloquean en silencio. La navegación no
   * descarga la página —el navegador cancela y muestra su diálogo de confirmación—, así que
   * el sondeo continúa sin interrupción.
   */
  function abrirConector (enlace) {
    window.location.href = enlace
  }

  async function sondear (sessionId, tokenSondeo, inicio) {
    if (cancelado) return

    let respuesta
    try {
      respuesta = await pedirJson(
        `${authUrl}/api/v1/sesiones/${encodeURIComponent(sessionId)}/estado` +
        `?sondeo=${encodeURIComponent(tokenSondeo)}`,
        { method: 'GET', headers: {} },
      )
    } catch (e) {
      estado.value = 'error'
      mensaje.value = e.codigo === 'SesionInvalida'
        ? 'La solicitud de ingreso expiró. Vuelva a intentarlo.'
        : 'Se perdió la comunicación con el servicio de autenticación.'
      return
    }

    switch (respuesta.estado) {
      case 'Pendiente':
        // Nadie ha recogido la solicitud: probablemente el conector no está instalado.
        if (Date.now() - inicio > ESPERA_ANTES_DE_SUGERIR_INSTALACION_MS) {
          requiereInstalacion.value = true
          mensaje.value = 'No se detectó el conector de DNIe en este equipo.'
        }
        break

      case 'EnCurso':
        estado.value = 'enCurso'
        requiereInstalacion.value = false
        mensaje.value = 'Confirme su identidad en la ventana del conector.'
        break

      case 'Completada':
        limpiar()
        estado.value = 'completado'
        mensaje.value = 'Verificando…'
        await entregarCodigo(respuesta.codigo)
        return

      case 'Cancelada':
        limpiar()
        estado.value = 'inactivo'
        mensaje.value = respuesta.mensaje || 'Autenticación cancelada.'
        return

      case 'Fallida':
        limpiar()
        estado.value = 'error'
        mensaje.value = respuesta.mensaje || 'No se pudo verificar su DNIe.'
        return

      case 'Expirada':
        limpiar()
        estado.value = 'error'
        mensaje.value = 'La solicitud de ingreso expiró. Vuelva a intentarlo.'
        return
    }

    temporizador = setTimeout(() => sondear(sessionId, tokenSondeo, inicio), INTERVALO_SONDEO_MS)
  }

  /**
   * El código va a NUESTRO backend (Laravel), que lo canjea server-to-server con su
   * secreto de cliente. El navegador nunca ve el token ni el secreto.
   */
  async function entregarCodigo (codigo) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content

    const respuesta = await fetch(rutaCallback, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
      },
      body: JSON.stringify({ codigo }),
    })

    if (!respuesta.ok) {
      estado.value = 'error'
      const datos = await respuesta.json().catch(() => null)
      mensaje.value = datos?.mensaje || 'No se pudo completar el inicio de sesión.'
      return
    }

    const datos = await respuesta.json()
    mensaje.value = ''
    window.location.href = datos.redirigirA || '/'
  }

  async function iniciar () {
    limpiar()
    cancelado = false
    requiereInstalacion.value = false
    estado.value = 'iniciando'
    mensaje.value = 'Preparando la solicitud…'

    let sesion
    try {
      sesion = await pedirJson(`${authUrl}/api/v1/sesiones`, {
        method: 'POST',
        body: JSON.stringify({ clientId }),
      })
    } catch (e) {
      estado.value = 'error'
      mensaje.value = e.codigo === 'ClienteNoAutorizado'
        ? 'Este sitio no está autorizado en el servicio de autenticación.'
        : 'No se pudo contactar con el servicio de autenticación.'
      return
    }

    urlDescarga.value = sesion.urlDescargaConector || ''
    estado.value = 'esperandoConector'
    mensaje.value = 'Abriendo el conector de DNIe…'

    abrirConector(sesion.enlaceConector)
    sondear(sesion.sessionId, sesion.tokenSondeo, Date.now())
  }

  function cancelar () {
    limpiar()
    estado.value = 'inactivo'
    mensaje.value = ''
    requiereInstalacion.value = false
  }

  return { estado, mensaje, requiereInstalacion, urlDescarga, iniciar, cancelar }
}
