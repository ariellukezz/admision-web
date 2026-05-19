<template>
  <div class="huellas-component">
    <!-- Estado de conexión compacto -->
    <div class="connection-status">
      <span class="status-label">Lector de Huellas</span>
      <div class="status-info">
        <a-badge :status="connectionStatus.type === 'connected' ? 'success' : 'error'" />
        <span class="status-text">{{ connectionStatus.text }}</span>
      </div>
      <a-button
        v-if="connectionStatus.type !== 'connected'"
        type="primary"
        size="small"
        :loading="connecting"
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

    <a-row :gutter="12" class="huellas-row">
      <!-- Huella Derecha -->
      <a-col :span="12">
        <div 
          class="huella-card" 
          :class="{ 'huella-active': selectedOption === 'right'}"
          @click="focusDerecha"
        >
          <div class="huella-header">
            <span>Huella Derecha</span>
            <a-tag :color="fingerprints.right.registered ? 'green' : 'default'" size="small">
              {{ fingerprints.right.registered ? '✓ Capturada' : '○ Pendiente' }}
            </a-tag>
          </div>
          <div class="huella-image">
            <img 
              v-if="fingerprints.right.image" 
              :src="fingerprints.right.image" 
            />
            <div v-else class="huella-placeholder">
              <span style="color: #999; font-size: 1.6rem;" > Huella derecha </span>
            </div>
          </div>
          <div class="button-group">
            <a-button
              type="primary"
              size="small"
              block
              :disabled="connectionStatus.type !== 'connected' || !dni"
              :loading="capturing === 'right'"
              @click="startCapture('right')"
            >
              <ScanOutlined />
              Capturar Derecha
            </a-button>
            
          </div>
        </div>
      </a-col>

      <!-- Huella Izquierda -->
      <a-col :span="12">
        <div 
          class="huella-card" 
          :class="{ 'huella-active': selectedOption === 'left'}"
          @click="focusIzquierda"
        >
          <div class="huella-header">
            <span>Huella Izquierda</span>
            <a-tag :color="fingerprints.left.registered ? 'green' : 'default'" size="small">
              {{ fingerprints.left.registered ? '✓ Capturada' : '○ Pendiente' }}
            </a-tag>
          </div>
          <div class="huella-image">
            <img 
              v-if="fingerprints.left.image" 
              :src="fingerprints.left.image" 
            />
            <div v-else class="huella-placeholder">
              <span style="color: #999; font-size: 1.6rem;" >Huella Izquierda</span>
            </div>
          </div>
          <div class="button-group">
            <a-button
              type="primary"
              size="small"
              block
              :disabled="connectionStatus.type !== 'connected' || !dni"
              :loading="capturing === 'left'"
              @click="startCapture('left')"
            >
              <ScanOutlined />
              Capturar Izquierda
            </a-button>
           
          </div>
        </div>
      </a-col>
    </a-row>

   <div class="save-section">
      <a-button
        type="primary"
        size="large"
        block
        :class="{
          'save-button-active': selectedOption === 'save'
        }"
        @click="showConfirmModal = true"
      >
        Guardar Huellas
      </a-button>
    </div>
    />
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

</template>

<script setup>

import { ref, computed, onMounted, onUnmounted } from 'vue'
import { message } from 'ant-design-vue'
import { unpack } from 'msgpackr'
import {
  WifiOutlined,
  CloseOutlined,
  ScanOutlined
} from '@ant-design/icons-vue'
import axios from 'axios'

const props = defineProps({
  dni: { type: String, default: '' }
})

const emit = defineEmits(['huellas-actualizadas'])

const ws = ref(null)
const wsUrl = 'ws://localhost:3000/websocket'

const connecting = ref(false)
const captureTarget = ref(null)
const capturing = ref(null)

const selectedOption = ref('right')

const showConfirmModal = ref(false)
const saving = ref(false)

const tokenFijo = ref(
  '8hIcptrxisoqUtpvU1YZa9eYaZgViuS0LqI0pq6RfmT3O1QJnyAqzwKQNjzr'
)

const connectionStatus = ref({
  type: 'disconnected',
  text: 'Desconectado',
  color: 'default'
})

const fingerprints = ref({
  right: {
    registered: false,
    image: null,
    template: null,
    quality: 0
  },
  left: {
    registered: false,
    image: null,
    template: null,
    quality: 0
  }
})

const connectWebSocket = async () => {

  if (connecting.value) return

  connecting.value = true

  ws.value = new WebSocket(
    `${wsUrl}?token_conexion=${tokenFijo.value}`
  )

  ws.value.binaryType = 'arraybuffer'

  ws.value.onopen = () => {

    connectionStatus.value = {
      type: 'connected',
      text: 'Conectado',
      color: 'green'
    }

    connecting.value = false

    message.success('Lector conectado')

    ws.value.send(
      JSON.stringify({
        type: 'frontend'
      })
    )
  }

  ws.value.onmessage = async (event) => {

    try {

      let arrayBuffer

      if (event.data instanceof Blob) {
        arrayBuffer = await event.data.arrayBuffer()
      } else {
        arrayBuffer = event.data
      }

      const decoded = unpack(
        new Uint8Array(arrayBuffer)
      )

      if (
        decoded &&
        decoded.template &&
        decoded.image
      ) {
        updateFingerprint(decoded)
      }

    } catch (err) {

      console.error(err)
    }
  }

  ws.value.onerror = () => {

    connectionStatus.value = {
      type: 'error',
      text: 'Error',
      color: 'red'
    }

    message.error('Error en la conexión')

    connecting.value = false
  }

  ws.value.onclose = () => {

    connectionStatus.value = {
      type: 'disconnected',
      text: 'Desconectado',
      color: 'default'
    }
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

  const bytes = new Uint8Array(buffer)

  for (let i = 0; i < bytes.length; i++) {
    binary += String.fromCharCode(bytes[i])
  }

  return btoa(binary)
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

  if (!captureTarget.value) return

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

  const finger = captureTarget.value

  fingerprints.value[finger] = {
    registered: true,
    image: imageUrl,
    template: decodedData.template,
    quality: 85
  }

  emit('huellas-actualizadas', {
    right: fingerprints.value.right.registered,
    left: fingerprints.value.left.registered,
    data: fingerprints.value
  })

  message.success(
    `Huella ${finger === 'right' ? 'derecha' : 'izquierda'} capturada`
  )

  capturing.value = null
  captureTarget.value = null

  if (finger === 'right') {
    selectedOption.value = 'left'
  } else {
    selectedOption.value = 'save'
  }
}

const focusDerecha = () => {

  selectedOption.value = 'right'

  startCapture('right')
}

const focusIzquierda = () => {

  selectedOption.value = 'left'

  startCapture('left')
}

const handleKeydown = async (event) => {

  if (event.key === 'Tab') {

    event.preventDefault()

    if (selectedOption.value === 'right') {

      selectedOption.value = 'left'

    } else if (selectedOption.value === 'left') {

      selectedOption.value = 'save'

    } else {

      selectedOption.value = 'right'
    }

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

    if (selectedOption.value === 'right') {

      startCapture('right')

      return
    }

    if (selectedOption.value === 'left') {

      startCapture('left')

      return
    }

    if (selectedOption.value === 'save') {

      if (
        !fingerprints.value.right.registered ||
        !fingerprints.value.left.registered
      ) {

        message.warning('Debe capturar ambas huellas')

        return
      }

      showConfirmModal.value = true
    }
  }
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
}

const clearFingerprint = (finger) => {

  fingerprints.value[finger] = {
    registered: false,
    image: null,
    template: null,
    quality: 0
  }

  emit('huellas-actualizadas', {
    right: fingerprints.value.right.registered,
    left: fingerprints.value.left.registered,
    data: fingerprints.value
  })
}

const guardarHuellas = async () => {

  try {

    const huellas = []

    if (fingerprints.value.right.registered) {

      huellas.push(
        axios.post('/revisor/guardar-huella', {
          dni_postulante: props.dni,
          dedo: 'indice_derecho',
          template: fingerprints.value.right.template,
          imagen: fingerprints.value.right.image,
          calidad: fingerprints.value.right.quality
        })
      )
    }

    if (fingerprints.value.left.registered) {

      huellas.push(
        axios.post('/revisor/guardar-huella', {
          dni_postulante: props.dni,
          dedo: 'indice_izquierdo',
          template: fingerprints.value.left.template,
          imagen: fingerprints.value.left.image,
          calidad: fingerprints.value.left.quality
        })
      )
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

  fingerprints.value = {
    right: {
      registered: false,
      image: null,
      template: null,
      quality: 0
    },
    left: {
      registered: false,
      image: null,
      template: null,
      quality: 0
    }
  }

  captureTarget.value = null
  capturing.value = null

  emit('huellas-actualizadas', {
    right: false,
    left: false,
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

<style scoped>
.huellas-component {
  height: 100%;
}

.connection-status {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  background: #fafafa;
  border-radius: 6px;
  margin-bottom: 12px;
}

.status-label {
  font-size: 12px;
  font-weight: 500;
}

.status-info {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
}

.status-text {
  font-size: 12px;
}

.huellas-row {
  margin-bottom: 0px;
}

.huella-card {
  padding: 10px;
  background: #fafafa;
  border-radius: 8px;
  text-align: center;
  transition: all 0.3s ease;
  cursor: pointer;
}

.huella-card:hover {
  background: #f0f0f0;
  transform: translateY(-2px);
}

.huella-active {
  border: 2px solid #1890ff;
  background: #e6f7ff;
  box-shadow: 0 2px 8px rgba(24, 144, 255, 0.2);
}

.huella-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  font-size: 12px;
  font-weight: 500;
}

.huella-image {
  width: 100%;
  aspect-ratio: 3/4;
  background: white;
  border-radius: 6px;
  border: 1px solid #e8e8e8;
  margin-bottom: 8px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.huella-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.huella-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  color: #bfbfbf;
}

.button-group {
  margin-top: 8px;
}

.mt-1 {
  margin-top: 4px;
}

.mt-2 {
  margin-top: 8px;
}

.nav-instructions {
  margin-top: 12px;
  padding: 6px;
  text-align: center;
  font-size: 11px;
  color: #8c8c8c;
  background: #f5f5f5;
  border-radius: 4px;
}
.save-container {
  margin-top: 16px;
  display: flex;
  justify-content: center;
}

.save-button {
  min-width: 220px;
  height: 42px;
  font-weight: 600;
  transition: all .2s ease;
}

.save-active {
  border: 2px solid #1890ff !important;
  box-shadow: 0 0 0 4px rgba(24,144,255,.2);
  transform: scale(1.03);
}

.save-section {
  margin-top: 14px;
}

.save-button-active {
  border: 2px solid #1890ff !important;
  box-shadow: 0 0 12px rgba(24,144,255,.45);
  transform: scale(1.01);
}

.huella-active {
  border: 2px solid #1890ff;
  background: #e6f7ff;
  box-shadow: 0 0 12px rgba(24,144,255,.35);
  transform: translateY(-2px);
}
</style>