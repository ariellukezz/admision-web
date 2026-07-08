<template>
  <div class="huellas-component">
    <!-- Estado de conexión compacto -->
    <div class="connection-status">
      <div class="status-left">
        <!-- <div class="status-label">Lector de Huellas</div> -->
        <div class="status-info">
          <a-badge :status="connectionStatus.type === 'connected' ? 'success' : 'error'" />
          <span class="status-text">{{ connectionStatus.text }}</span>
        </div>
      </div>

      <div class="status-actions">
        <a-button
          type="primary"
          size="small"
          :loading="generatingCode"
          :disabled="connectionStatus.type === 'connected'"
          @click="handleGenerateCode"
        >
          <KeyOutlined />
          Generar Código
        </a-button>
        <a-button
          v-if="connectionStatus.type !== 'connected'"
          type="primary"
          size="small"
          :loading="connecting"
          :disabled="!tokenConexion"
          @click="connectWebSocket"
        >
          <WifiOutlined />
          Conectar
        </a-button>
        <a-button
          v-else
          size="small"
          danger
          @click="disconnectWebSocket"
        >
          <CloseOutlined />
          Desconectar
        </a-button>
      </div>
    </div>

    <!-- <div class="mode-panel">
      <div class="mode-title">{{ modeLabel }}</div>
      <div class="auto-capture-control">
        <div class="w-full" style="display:flex; justify-content: space-between;">
          <div>
            <span>Captura automática</span>            
          </div>
          <div>
            <a-switch v-model:checked="autoCaptureEnabled" />
          </div>
        </div>
      </div>
    </div> -->

    <div class="finger-selection">
      <template v-if="showFingerGrid">
        <div class="finger-grid">
          <div
            v-for="option in fingerOptions"
            :key="option.value"
            class="huella-card"
            :class="{ 'huella-selected': selectedFinger === option.value }"
            @click="selectFinger(option.value)"
          >
            <div class="huella-header">
              <div>
                <span>{{ option.label }}</span>
                <div class="finger-status-line">
                  <span class="finger-quality" v-if="fingerprints[option.value].registered">
                    Calidad: {{ fingerprints[option.value].quality }}%
                  </span>
                </div>
              </div>
            </div>

            <div class="huella-image">
              <img v-if="fingerprints[option.value].image" :src="fingerprints[option.value].image" />
              <div v-else class="huella-placeholder">
                <span>{{ captureTarget === option.value ? 'Coloca tu dedo...' : option.label }}</span>
              </div>
            </div>

            <div class="capture-status" v-if="captureTarget === option.value">
              <a-alert type="info" show-icon message="Esperando huella..." />
            </div>
          </div>
        </div>
      </template>

      <!-- Vista individual: selector + 1 tarjeta -->
      <template v-else>
        <a-select
          v-model:value="selectedFinger"
          :options="fingerOptions"
          size="middle"
          style="width: 100%; margin-bottom: 10px"
          @change="selectFinger"
        />
        <div class="finger-status">
          <span>Seleccionado:</span>
          <a-tag :color="fingerprints[selectedFinger].registered ? 'green' : 'default'" size="small">
            {{ fingerprints[selectedFinger].registered ? '✓ Capturada' : '○ Pendiente' }}
          </a-tag>
          <span class="finger-count">{{ capturedCount }} huella(s) capturada(s)</span>
        </div>

        <div class="huella-card huella-selected">
          <div class="huella-header">
            <div>
              <span>{{ fingerLabel }}</span>
              <div class="finger-status-line">
                <a-tag :color="fingerprintBadgeColor(selectedFinger)" size="small">
                  {{ fingerprints[selectedFinger].registered ? 'Registrada' : 'No registrada' }}
                </a-tag>
                <span class="finger-quality" v-if="fingerprints[selectedFinger].registered">
                  Calidad: {{ fingerprints[selectedFinger].quality }}%
                </span>
              </div>
            </div>
          </div>

          <div class="huella-image">
            <img v-if="fingerprints[selectedFinger].image" :src="fingerprints[selectedFinger].image" />
            <div v-else class="huella-placeholder">
              <span>{{ captureTarget === selectedFinger ? 'Coloca tu dedo en el lector...' : fingerLabel }}</span>
            </div>
          </div>

          <div class="capture-status" v-if="captureTarget === selectedFinger">
            <a-alert type="info" show-icon message="Esperando huella..." description="Mantén el dedo en el lector para capturar." />
          </div>

          <div class="button-group">
            <a-button
              type="primary"
              size="small"
              block
              :disabled="connectionStatus.type !== 'connected' || !dni"
              :loading="capturing === selectedFinger"
              @click="startCapture(selectedFinger)"
            >
              <ScanOutlined />
              Capturar {{ fingerLabel }}
            </a-button>
            <a-button
              type="default"
              size="small"
              block
              :disabled="!fingerprints[selectedFinger].registered"
              @click.stop="clearFingerprint(selectedFinger)"
              class="clear-button"
            >
              Limpiar
            </a-button>
          </div>
        </div>

        <div class="finger-summary">
          <span>Huellas registradas:</span>
          <a-space wrap>
            <a-tag
              v-for="option in fingerOptions"
              :key="option.value"
              :color="fingerprintBadgeColor(option.value)"
            >
              {{ option.label }}
            </a-tag>
          </a-space>
        </div>
      </template>
    </div>

    <div class="action-panel">
      <a-row :gutter="10" align="middle">
        <a-col :xs="24" :sm="12">
          <a-button
            type="primary"
            size="large"
            block
            @click="handleAction"
            :loading="actionLoading"
            :disabled="!actionEnabled"
          >
            <span v-if="selectedMode === 'register'"><SaveOutlined /> Guardar Huellas</span>
            <span v-else-if="selectedMode === 'verify1to1'"><CheckCircleOutlined /> Verificar 1:1</span>
            <span v-else><SearchOutlined /> Verificar 1:N</span>
          </a-button>
        </a-col>
        <a-col :xs="24" :sm="12">
          <a-button block @click="resetear" size="large">
            <ReloadOutlined />
            Limpiar
          </a-button>
        </a-col>
      </a-row>

      <div class="verification-result" v-if="verificationResult">
        <a-alert :message="verificationResult.message" :type="verificationResult.type" show-icon />
      </div>
    </div>
  </div>

  <a-modal
    v-model:open="showConfirmModal"
    title="Confirmar guardado"
    :footer="null"
    :closable="false"
    centered
  >
  <div style="text-align:center;">
    <p>¿Está seguro de guardar las huellas?</p>

    <a-space>
      <a-button @click="showConfirmModal = false">
        Cancelar
      </a-button>

      <a-button
        type="primary"
        :loading="saving"
        @click="guardarHuellas"
      >
        Guardar
      </a-button>
    </a-space>
  </div>
</a-modal>

<a-modal
  v-model:open="showCodeModal"
  title="Código de conexión"
  :footer="null"
  centered
  width="360px"
>
  <div style="text-align: center;">
    <p style="margin-bottom: 12px; color: #666;">Ingresa este código en la aplicación de escritorio:</p>
    <div style="font-size: 36px; font-weight: bold; letter-spacing: 8px; padding: 16px; background: #f5f5f5; border-radius: 8px; margin-bottom: 16px;">
      {{ codigoGenerado }}
    </div>
    <a-button type="primary" block @click="showCodeModal = false">
      Entendido
    </a-button>
  </div>
</a-modal>

</template>

<script setup>

import { ref, computed, onMounted, onUnmounted } from 'vue'
import { message } from 'ant-design-vue'
import { unpack } from 'msgpackr'
import {
  WifiOutlined,
  CloseOutlined,
  ScanOutlined,
  SaveOutlined,
  ReloadOutlined,
  CheckCircleOutlined,
  SearchOutlined,
  KeyOutlined
} from '@ant-design/icons-vue'
import axios from 'axios'

const props = defineProps({
  dni: { type: String, default: '' },
  fingerFilter: { type: Array, default: null },
  contextMode: { type: String, default: 'inscripcion' }
})

const emit = defineEmits(['huellas-actualizadas'])

const ws = ref(null)
const wsUrl = 'wss://test-admision.unap.edu.pe/ws-huellas/'

const connecting = ref(false)
const captureTarget = ref(null)
const capturing = ref(null)
const captureSessionId = ref(null)

const selectedOption = ref('right')
const selectedMode = ref('register')
const actionLoading = ref(false)
const verificationResult = ref(null)
const wsVerificationPending = ref(false)

const modeOptions = [
  { label: 'Registrar Huella', value: 'register' },
  { label: 'Verificar 1:1', value: 'verify1to1' },
  { label: 'Verificar 1:N', value: 'verify1toN' }
]

const allFingers = [
  { label: 'Índice Izquierdo', value: 'indice_izquierdo' },
  { label: 'Índice Derecho', value: 'indice_derecho' },
  { label: 'Pulgar Izquierdo', value: 'pulgar_izquierdo' },
  { label: 'Pulgar Derecho', value: 'pulgar_derecho' },
  { label: 'Medio Izquierdo', value: 'medio_izquierdo' },
  { label: 'Medio Derecho', value: 'medio_derecho' },
  { label: 'Anular Izquierdo', value: 'anular_izquierdo' },
  { label: 'Anular Derecho', value: 'anular_derecho' },
  { label: 'Meñique Izquierdo', value: 'menique_izquierdo' },
  { label: 'Meñique Derecho', value: 'menique_derecho' }
]

const fingerOptions = computed(() => {
  if (!props.fingerFilter) return allFingers
  return allFingers.filter(f => props.fingerFilter.includes(f.value))
})

const showFingerGrid = computed(() => fingerOptions.value.length === 2)

const selectedFinger = ref(fingerOptions.value[0]?.value || 'indice_izquierdo')

const fingerLabel = computed(() => {
  const option = fingerOptions.value.find(opt => opt.value === selectedFinger.value)
  return option ? option.label : 'Dedo seleccionado'
})

const capturedCount = computed(() => {
  return Object.values(fingerprints.value).filter(f => f.registered).length
})

const fingerprintBadgeColor = (finger) => {
  return fingerprints.value[finger] && fingerprints.value[finger].registered ? 'green' : 'default'
}

const autoCaptureEnabled = ref(true)

const nextUnregisteredFinger = () => {
  const next = fingerOptions.value.find(option => !fingerprints.value[option.value].registered)
  return next ? next.value : null
}

const captureStatusMessage = computed(() => {
  if (captureTarget.value) {
    const targetLabel = fingerOptions.value.find(opt => opt.value === captureTarget.value)?.label || 'dedo'
    return `Capturando ${targetLabel}... mantén el dedo en el lector.`
  }

  if (selectedMode.value === 'register') {
    return 'Modo registro activo. Puedes capturar automáticamente la siguiente huella pendiente.'
  }

  return ''
})

const scheduleNextCapture = () => {
  if (!autoCaptureEnabled.value) return
  if (selectedMode.value !== 'register') return
  if (captureTarget.value || capturing.value) return
  if (connectionStatus.value.type !== 'connected') return
  if (!props.dni) return

  const nextFinger = nextUnregisteredFinger()
  if (!nextFinger) return

  setTimeout(() => {
    if (!captureTarget.value && selectedMode.value === 'register') {
      startCapture(nextFinger)
    }
  }, 600)
}

const showConfirmModal = ref(false)
const saving = ref(false)

const codigoGenerado = ref('')
const tokenConexion = ref('')
const showCodeModal = ref(false)
const generatingCode = ref(false)

const handleGenerateCode = async () => {
  generatingCode.value = true
  try {
    const { data } = await axios.get('/revisor/generar-codigo-conexion')
    codigoGenerado.value = data.codigo_conexion
    tokenConexion.value = data.token
    showCodeModal.value = true
    connectWebSocket()
  } catch (error) {
    console.error(error)
    message.error('No se pudo generar el código de conexión')
  } finally {
    generatingCode.value = false
  }
}

const modeLabel = computed(() => {
  if (selectedMode.value === 'verify1to1') return 'Verificación 1:1'
  if (selectedMode.value === 'verify1toN') return 'Verificación 1:N'
  return 'Registro de Huellas'
})

const modeDescription = computed(() => {
  if (selectedMode.value === 'verify1to1') {
    return 'Compara la huella actual contra el DNI ingresado para una coincidencia exacta.'
  }
  if (selectedMode.value === 'verify1toN') {
    return 'Busca la huella actual en toda la base de datos para encontrar coincidencias.'
  }
  return 'Capture y registre las huellas para guardar el perfil biométrico del postulante.'
})

const actionEnabled = computed(() => {
  const hasAnyFingerprint = Object.values(fingerprints.value).some(f => f.registered)
  const currentCaptured = fingerprints.value[selectedFinger.value]?.registered

  if (selectedMode.value === 'register') {
    return (
      hasAnyFingerprint &&
      !!props.dni
    )
  }

  return (
    connectionStatus.value.type === 'connected' &&
    currentCaptured &&
    !!props.dni
  )
})

const connectionStatus = ref({
  type: 'disconnected',
  text: 'Desconectado',
  color: 'default'
})

const fingerprints = ref(Object.fromEntries(
  fingerOptions.value.map(option => [option.value, {
    registered: false,
    image: null,
    template: null,
    quality: 0
  }])
))

const connectWebSocket = async () => {

  if (connecting.value) return
  if (!tokenConexion.value) return

  connecting.value = true

  tokenConexion.value = 'HUELLA-GCF1XHPK-1UIRHTRL-TUJPL7KO-ORIGJUKC';
  try {
    ws.value = new WebSocket(
      `${wsUrl}?token_conexion=${tokenConexion.value}`
    )
  } catch (error) {
    console.error('[WS] Error al crear WebSocket:', error)
    message.error('No se pudo conectar al servidor')
    connecting.value = false
    return
  }

  ws.value.binaryType = 'arraybuffer'

  console.log('[WS] Conectando a:', `${wsUrl}?token_conexion=${tokenConexion.value}`)

  ws.value.onopen = () => {
    console.log('[WS] Conexión abierta')

    connectionStatus.value = {
      type: 'connected',
      text: 'Conectado',
      color: 'green'
    }

    connecting.value = false

    message.success('Lector conectado')

    ws.value.send(
      JSON.stringify({
        type:'frontend'
      })
    )

    console.log('[WS] Enviado: { type: "frontend" }')

    scheduleNextCapture()
  }

  ws.value.onmessage = async (event) => {
    console.log('[WS] Mensaje recibido:', event.data)
    console.log('[WS] Tipo:', typeof event.data, 'instanceof Blob:', event.data instanceof Blob, 'instanceof ArrayBuffer:', event.data instanceof ArrayBuffer)

    try {
      let jsonData = null
      let decoded = null

      if (typeof event.data === 'string') {
        jsonData = JSON.parse(event.data)
        console.log('[WS] JSON parseado:', jsonData)
      } else {
        let arrayBuffer

        if (event.data instanceof Blob) {
          arrayBuffer = await event.data.arrayBuffer()
        } else if (event.data instanceof ArrayBuffer) {
          arrayBuffer = event.data
        } else if (event.data instanceof Uint8Array) {
          arrayBuffer = event.data.buffer
        } else if (event.data && event.data.buffer) {
          arrayBuffer = event.data.buffer
        }

        if (!arrayBuffer) {
          throw new Error('Formato de datos WS no soportado')
        }

        console.log('[WS] ArrayBuffer size:', arrayBuffer.byteLength)

        try {
          decoded = unpack(new Uint8Array(arrayBuffer))
          console.log('[WS] MessagePack decodificado:', decoded)
          console.log('[WS] Keys:', Object.keys(decoded || {}))
        } catch (unpackError) {
          console.log('[WS] No era MessagePack, intentando JSON...')
          const text = new TextDecoder().decode(arrayBuffer)
          try {
            jsonData = JSON.parse(text)
            console.log('[WS] JSON desde texto:', jsonData)
          } catch (jsonError) {
            throw unpackError
          }
        }
      }

      if (jsonData) {
        if (jsonData.type === 'fingerprint_result') {
          const matchLabel = jsonData.match?.FullKey || jsonData.redis_key || jsonData.redis_user_id || jsonData.dni_postulante || jsonData.userId
          const scoreLabel = jsonData.score != null ? ` (score: ${jsonData.score})` : ''

          verificationResult.value = {
            type: jsonData.found ? 'success' : 'warning',
            message: jsonData.found
              ? `Coincidencia encontrada: ${matchLabel || 'sin detalle'}${scoreLabel}`
              : 'No se encontró coincidencia en Redis.'
          }
          wsVerificationPending.value = false
          actionLoading.value = false
        }

        if (jsonData.type === 'app_conectada') {
          connectionStatus.value = {
            type: 'connected',
            text: 'App conectada',
            color: 'green'
          }
        }

        if (jsonData.type === 'frontend_conectado') {
          connectionStatus.value = {
            type: 'connected',
            text: 'Frontend listo',
            color: 'green'
          }
        }

        if (jsonData.type === 'error') {
          console.log('[WS] Error del servidor:', jsonData.codigo, jsonData.mensaje)
        }

        if (jsonData.type === 'desconexion') {
          console.log('[WS] Desconexión:', jsonData.cliente)
          message.warning(jsonData.cliente === 'app'
            ? 'La aplicación de escritorio se desconectó'
            : 'El frontend se desconectó')
        }

        if (jsonData.type === 'conectado') {
          console.log('[WS] Servidor confirmó conexión')
        }

        if (jsonData.template || jsonData.template_base64) {
          console.log('[WS] JSON con template, actualizando huella...')
          updateFingerprint(jsonData)
        }

        return
      }

      if (decoded && (decoded.template || decoded.template_base64)) {
        console.log('[WS] Tiene template, actualizando huella...')
        updateFingerprint(decoded)
      } else {
        console.log('[WS] No tiene template. decoded:', !!decoded, 'template:', !!decoded?.template, 'template_base64:', !!decoded?.template_base64, 'image:', !!decoded?.image)
      }
    } catch (err) {
      console.error('[WS] Error procesando mensaje:', err)
      wsVerificationPending.value = false
      actionLoading.value = false
    }
  }

  ws.value.onerror = (error) => {
    console.error('[WS] Error:', error)
    connectionStatus.value = {
      type: 'error',
      text: 'Error',
      color: 'red'
    }

    message.error('Error en la conexión')

    connecting.value = false
  }

  ws.value.onclose = (event) => {
    console.log('[WS] Close:', event.code, event.reason)
    connectionStatus.value = {
      type: 'disconnected',
      text: 'Desconectado',
      color: 'default'
    }

    if (event.code !== 1000) {
      message.warning(`Conexión cerrada (${event.code})${event.reason ? ': ' + event.reason : ''}`)
    }

    connecting.value = false
  }
}

const disconnectWebSocket = () => {

  if (ws.value) {

    ws.value.close()

    ws.value = null
  }
}

const arrayBufferToBase64 = (buffer) => {

  let binary = ''
  const bytes = buffer instanceof ArrayBuffer ? new Uint8Array(buffer) : new Uint8Array(buffer)

  for (let i = 0; i < bytes.length; i++) {
    binary += String.fromCharCode(bytes[i])
  }

  return btoa(binary)
}

const normalizeTemplateToBase64 = (template) => {
  if (!template) return null

  if (typeof template === 'string') {
    return template
  }

  if (template instanceof ArrayBuffer) {
    return arrayBufferToBase64(template)
  }

  if (template instanceof Uint8Array) {
    return arrayBufferToBase64(template)
  }

  if (Array.isArray(template)) {
    return arrayBufferToBase64(new Uint8Array(template))
  }

  return null
}

const generateMockFingerprintImage = () => {

  const canvas = document.createElement('canvas')

  canvas.width = 200
  canvas.height = 300

  const ctx = canvas.getContext('2d')

  ctx.fillStyle = '#f5f5f5'
  ctx.fillRect(0, 0, 200, 300)

  ctx.strokeStyle = '#444'

  for (let i = 0; i < 20; i++) {

    ctx.beginPath()

    ctx.ellipse(
      100,
      100,
      80 - i * 3,
      90 - i * 3,
      0,
      0,
      Math.PI * 2
    )

    ctx.stroke()
  }

  return canvas.toDataURL()
}

const updateFingerprint = (decodedData) => {

  if (captureSessionId.value && decodedData.capture_id && decodedData.capture_id !== captureSessionId.value) {
    console.log('[updateFingerprint] Descartada - capture_id no coincide:', decodedData.capture_id, '!=', captureSessionId.value)
    return
  }

  const finger = captureTarget.value || selectedFinger.value

  if (!finger) {
    console.log('[updateFingerprint] Descartada - no hay dedo seleccionado')
    return
  }

  console.log('[updateFingerprint] finger:', finger, 'captureTarget:', captureTarget.value, 'selectedFinger:', selectedFinger.value)

  let imageUrl = null

  if (decodedData.image) {

    if (typeof decodedData.image === 'string') {

      imageUrl = `data:image/png;base64,${decodedData.image}`

    } else {

      imageUrl =
        `data:image/png;base64,${arrayBufferToBase64(decodedData.image)}`
    }
  }

  if (!imageUrl) {
    imageUrl = generateMockFingerprintImage()
  }

  const normalizedTemplate = normalizeTemplateToBase64(decodedData.template || decodedData.template_base64)

  fingerprints.value[finger] = {
    registered: true,
    image: imageUrl,
    template: normalizedTemplate,
    quality: 85
  }

  emit('huellas-actualizadas', {
    any: Object.values(fingerprints.value).some(f => f.registered),
    data: fingerprints.value
  })

  const fingerName = fingerOptions.value.find(opt => opt.value === finger)?.label || finger
  message.success(`Huella ${fingerName} capturada`)

  capturing.value = null
  captureTarget.value = null
  captureSessionId.value = null

  scheduleNextCapture()
}

const focusDerecha = () => {
  selectedOption.value = 'right'
  startCapture('right')
}

const focusIzquierda = () => {
  selectedOption.value = 'left'
  startCapture('left')
}

const handleModeChange = () => {
  verificationResult.value = null
  scheduleNextCapture()
}

const handleAction = async () => {
  verificationResult.value = null

  if (selectedMode.value === 'register') {
    showConfirmModal.value = true
    return
  }

  if (selectedMode.value === 'verify1to1') {
    await verifyOneToOne()
    return
  }

  await verifyOneToN()
}

const sendWsVerificationAction = async (actionName, template) => {
  if (!ws.value || ws.value.readyState !== WebSocket.OPEN) {
    return false
  }

  wsVerificationPending.value = true
  actionLoading.value = true

  ws.value.send(JSON.stringify({
    type: 'fingerprint_action',
    action: actionName,
    template
  }))

  return true
}

const verifyOneToOne = async () => {
  if (!props.dni) {
    message.warning('Ingrese el DNI para verificar 1:1')
    return
  }

  const selected = fingerprints.value[selectedFinger.value]
  if (!selected || !selected.registered) {
    message.warning('Capture la huella del dedo seleccionado para verificar')
    return
  }

  verificationResult.value = null

  const sent = await sendWsVerificationAction('compare_1_1', selected.template)
  if (sent) {
    return
  }

  actionLoading.value = true

  try {
    const finger = selectedFinger.value
    const template = selected.template

    const res = await axios.post('/revisor/verificar-huella-1-1', {
      dni_postulante: props.dni,
      dedo: finger,
      template
    })

    const matchLabel = res.data.dni_postulante || res.data.userId || res.data.match?.UserId || res.data.match?.userId || ''
    const details = matchLabel ? ` (${matchLabel})` : ''

    verificationResult.value = {
      type: res.data.match ? 'success' : 'warning',
      message: res.data.match
        ? res.data.message || `Coincidencia 1:1 encontrada${details}.`
        : res.data.message || 'No se encontró coincidencia 1:1.'
    }
  } catch (error) {
    verificationResult.value = {
      type: 'error',
      message: 'Fallo al verificar 1:1. Revise la conexión o inténtelo de nuevo.'
    }
    console.error(error)
  } finally {
    actionLoading.value = false
  }
}

const verifyOneToN = async () => {
  const selected = fingerprints.value[selectedFinger.value]
  if (!selected || !selected.registered) {
    message.warning('Capture la huella del dedo seleccionado para verificar')
    return
  }

  verificationResult.value = null

  const sent = await sendWsVerificationAction('identify_1n', selected.template)
  if (sent) {
    return
  }

  actionLoading.value = true

  try {
    const finger = selectedFinger.value
    const template = selected.template

    const res = await axios.post('/revisor/verificar-huella-1-n', {
      dedo: finger,
      template
    })

    const matchLabel = res.data.dni_postulante || res.data.userId || res.data.match?.UserId || res.data.match?.userId || ''
    const details = matchLabel ? ` (${matchLabel})` : ''

    verificationResult.value = {
      type: res.data.match ? 'success' : 'warning',
      message: res.data.match
        ? res.data.message || `Coincidencia 1:N encontrada${details}.`
        : res.data.message || 'No se encontró coincidencia 1:N.'
    }
  } catch (error) {
    verificationResult.value = {
      type: 'error',
      message: 'Fallo al verificar 1:N. Verifique la configuración y vuelva a intentar.'
    }
    console.error(error)
  } finally {
    actionLoading.value = false
  }
}

const handleKeydown = async (event) => {

  if (event.key === 'Tab') {

    event.preventDefault()

    const fingers = fingerOptions.value
    if (fingers.length < 2) return

    const currentIdx = fingers.findIndex(f => f.value === selectedFinger.value)
    const nextIdx = (currentIdx + 1) % fingers.length
    selectFinger(fingers[nextIdx].value)

    return
  }

  if (event.key === 'Enter') {

    event.preventDefault()

    if (showConfirmModal.value) {

      if (saving.value) return

      saving.value = true

      const ok = await guardarHuellas()

      saving.value = false

      if (ok) {

        showConfirmModal.value = false

        resetear()

        selectedOption.value = 'right'
      }

      return
    }

    if (selectedOption.value === 'right' || selectedOption.value === 'left') {
      const hasCaptured = Object.values(fingerprints.value).some(f => f.registered)
      if (hasCaptured) {
        showConfirmModal.value = true
        return
      }
      startCapture(selectedFinger.value)
      return
    }

    if (selectedOption.value === 'save') {
      const hasCaptured = Object.values(fingerprints.value).some(f => f.registered)
      if (!hasCaptured) {
        message.warning('Debe capturar al menos una huella antes de guardar')
        return
      }

      showConfirmModal.value = true
    }
  }
}

const sendCaptureRequest = () => {
  if (!ws.value || ws.value.readyState !== WebSocket.OPEN) {
    return
  }

  captureSessionId.value = Date.now().toString(36) + Math.random().toString(36).slice(2, 8)

  ws.value.send(
    JSON.stringify({
      type: 'request_capture',
      capture_id: captureSessionId.value
    })
  )

  console.log('[WS] Captura solicitada, capture_id:', captureSessionId.value)
}

const selectFinger = (finger) => {
  selectedFinger.value = finger

  if (connectionStatus.value.type !== 'connected') return
  if (!props.dni) return

  captureTarget.value = finger
  capturing.value = finger
  sendCaptureRequest()
}

const startCapture = (finger) => {
  if (connectionStatus.value.type !== 'connected') {
    message.error('Lector desconectado')
    return
  }

  if (!props.dni) {
    message.warning('Ingrese el DNI primero')
    return
  }

  captureTarget.value = finger
  capturing.value = finger
  selectedFinger.value = finger
  sendCaptureRequest()
}

const clearFingerprint = (finger) => {
  fingerprints.value[finger] = {
    registered: false,
    image: null,
    template: null,
    quality: 0
  }

  emit('huellas-actualizadas', {
    any: Object.values(fingerprints.value).some(f => f.registered),
    data: fingerprints.value
  })
}

const guardarHuellas = async () => {
  showConfirmModal.value = false
  if (!props.dni) {
    message.warning('Ingrese el DNI antes de guardar huellas')
    return false
  }

  try {
    const huellas = []

    Object.keys(fingerprints.value).forEach((finger) => {
      const entry = fingerprints.value[finger]
      if (entry.registered) {
        huellas.push(
          axios.post('/revisor/guardar-huella', {
            dni_postulante: props.dni,
            dedo: finger,
            template: entry.template,
            imagen: entry.image,
            calidad: entry.quality,
            contexto: props.contextMode
          })
        )
      }
    })

    if (huellas.length === 0) {
      message.warning('No hay huellas registradas para guardar')
      return false
    }

    await Promise.all(huellas)

    message.success('Huellas guardadas correctamente')

    return true

  } catch (error) {

    console.error(error)

    message.error('Error al guardar huellas')

    return false
  }
}

const resetear = () => {
  fingerprints.value = Object.fromEntries(
    fingerOptions.value.map(option => [option.value, {
      registered: false,
      image: null,
      template: null,
      quality: 0
    }])
  )

  captureTarget.value = null
  capturing.value = null

  emit('huellas-actualizadas', {
    any: false,
    data: null
  })
}

defineExpose({
  guardarHuellas,
  resetear,
  connectWebSocket,
  disconnectWebSocket
})

onMounted(() => {

  window.addEventListener(
    'keydown',
    handleKeydown
  )
})

onUnmounted(() => {

  window.removeEventListener(
    'keydown',
    handleKeydown
  )

  if (ws.value) {
    ws.value.close()
  }
})
</script>

<style scoped src="./HuellasComponent.css"></style>
