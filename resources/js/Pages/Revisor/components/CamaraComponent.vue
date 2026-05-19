<template>
  <div class="fotos-component">
    <a-card :bordered="false" class="compact-card" :body-style="{ padding: '12px' }">
      <div class="card-header">
        <span class="card-title">📷 Tomar Foto</span>
        <a-tag :color="isActive ? 'green' : 'default'" size="small">
          {{ isActive ? 'Cámara activa' : 'Inactiva' }}
        </a-tag>
      </div>

      <!-- Controles de cámara compactos -->
      <div class="camera-controls">
        <a-button-group size="small">
          <a-button
            :type="isActive ? 'primary' : 'default'"
            :loading="loading"
            @click="toggleCamera"
            size="small"
          >
            <VideoCameraOutlined v-if="isActive" />
            <VideoCameraAddOutlined v-else />
            {{ isActive ? 'Apagar' : 'Encender' }}
          </a-button>
        </a-button-group>
        
        <a-select
          v-model:value="selectedCamera"
          :options="videoDevices"
          placeholder="Cámara"
          size="small"
          style="width: 150px"
          :disabled="!videoDevices.length || loading"
          @change="handleCameraChange"
        />
      </div>

      <!-- Vista previa compacta -->
      <div class="video-container">
        <div v-if="!isActive" class="video-placeholder">
          <VideoCameraOutlined />
          <span>Cámara apagada</span>
        </div>
        <video ref="videoEl" autoplay playsinline class="video-preview" :class="{ hidden: !isActive }" />
        <canvas ref="canvasEl" class="hidden" />
      </div>

      <!-- Botón capturar -->
      <a-button 
        type="primary" 
        @click="takePhoto" 
        :disabled="!isActive || !dni" 
        size="small"
        block
        class="capture-btn"
      >
        <CameraOutlined />
        Capturar Foto
      </a-button>

      <!-- Preview de foto capturada -->
      <div v-if="currentPhoto" class="photo-preview">
        <div class="preview-header">
          <span>Vista previa</span>
          <a-button type="link" size="small" @click="currentPhoto = null">✕</a-button>
        </div>
        <img :src="currentPhoto" class="preview-img" />
        <a-button 
          type="primary" 
          :disabled="!currentPhoto || isUploading"
          size="small"
          block
          @click="guardarFoto"
          :loading="isUploading"
        >
          <SaveOutlined />
          Guardar Foto
        </a-button>
      </div>

      <a-alert v-if="errorPhoto" :message="errorPhoto" type="error" show-icon class="mt-2" size="small" />
    </a-card>

    <!-- Historial de fotos compacto -->
    <a-card :bordered="false" class="compact-card mt-2" :body-style="{ padding: '8px' }">
      <div class="historial-header">📸 Historial</div>
      <div class="historial-grid">
        <div v-for="(foto, index) in fotosAnteriores" :key="index" class="historial-item">
          <img v-if="foto.url" :src="foto.url" />
          <div v-else class="empty-img"><CameraOutlined /></div>
          <span>{{ foto.anio || '-' }}</span>
        </div>
        <div v-for="i in (4 - fotosAnteriores.length)" :key="'empty-'+i" class="historial-item empty">
          <div class="empty-img">📷</div>
          <span>-</span>
        </div>
      </div>
    </a-card>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import axios from 'axios'
import { message } from 'ant-design-vue'
import { CameraOutlined, VideoCameraOutlined, VideoCameraAddOutlined, SaveOutlined } from '@ant-design/icons-vue'

const props = defineProps({
  dni: { type: String, default: '' }
})

const emit = defineEmits(['foto-capturada', 'foto-guardada'])

// Estado
const videoEl = ref(null)
const canvasEl = ref(null)
const isActive = ref(false)
const loading = ref(false)
const stream = ref(null)
const selectedCamera = ref(null)
const videoDevices = ref([])
const errorPhoto = ref(null)
const isUploading = ref(false)
const currentPhoto = ref(null)
const fotoGuardada = ref(false)
const fotosAnteriores = ref([])

// Métodos
const stopCamera = () => {
  if (stream.value) {
    stream.value.getTracks().forEach(track => track.stop())
    if (videoEl.value) videoEl.value.srcObject = null
    stream.value = null
    isActive.value = false
  }
}

const startCamera = async () => {
  if (!selectedCamera.value) return
  stopCamera()
  loading.value = true
  try {
    stream.value = await navigator.mediaDevices.getUserMedia({
      video: { deviceId: { exact: selectedCamera.value } }
    })
    videoEl.value.srcObject = stream.value
    isActive.value = true
  } catch (err) {
    message.error('No se pudo acceder a la cámara')
  } finally {
    loading.value = false
  }
}

const toggleCamera = async () => {
  if (isActive.value) stopCamera()
  else await startCamera()
}

const handleCameraChange = async () => {
  if (isActive.value) await startCamera()
}

const loadCameras = async () => {
  loading.value = true
  try {
    const tempStream = await navigator.mediaDevices.getUserMedia({ video: true })
    const devices = await navigator.mediaDevices.enumerateDevices()
    videoDevices.value = devices
      .filter(d => d.kind === 'videoinput')
      .map((d, i) => ({ label: d.label || `Cámara ${i + 1}`, value: d.deviceId }))
    if (videoDevices.value.length > 0) {
      selectedCamera.value = videoDevices.value[0].value
    }
    tempStream.getTracks().forEach(track => track.stop())
  } catch (err) {
    message.error('No se pudo acceder a las cámaras')
  } finally {
    loading.value = false
  }
}

const takePhoto = async () => {
  const video = videoEl.value
  const canvas = canvasEl.value

  if (!video || !canvas) {
    message.error('Error con la cámara')
    return
  }

  if (!props.dni) {
    message.warning('Ingrese el DNI')
    return
  }

  const ctx = canvas.getContext('2d')
  canvas.width = video.videoWidth
  canvas.height = video.videoHeight
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height)

  currentPhoto.value = canvas.toDataURL('image/jpeg', 0.97)
  fotoGuardada.value = false
  emit('foto-capturada', currentPhoto.value)
  message.success('Foto capturada')
}

const guardarFoto = async () => {
  if (!currentPhoto.value || !props.dni) return

  isUploading.value = true
  try {
    const canvas = canvasEl.value
    const blob = await new Promise((resolve) => {
      canvas.toBlob(resolve, 'image/jpeg', 0.97)
    })

    const formData = new FormData()
    formData.append('foto', blob, `${props.dni}.jpg`)
    formData.append('nro_doc', props.dni)
    formData.append('id_convocatoria', '1')

    const res = await axios.post('https://pixel.unap.edu.pe/fotos', formData)
    
    if (res.data.status === 'ok') {
      fotoGuardada.value = true
      message.success('Foto guardada')
      await cargarHistorial()
      emit('foto-guardada', { dni: props.dni, foto: currentPhoto.value })
    }
  } catch (err) {
    message.error('Error al guardar la foto')
  } finally {
    isUploading.value = false
  }
}

const cargarHistorial = async () => {
  if (!props.dni) {
    fotosAnteriores.value = []
    return
  }
  try {
    const res = await axios.get(`https://pixel.unap.edu.pe/fotos/historial/${props.dni}`)
    if (res.data.fotos) {
      fotosAnteriores.value = res.data.fotos.slice(0, 4).map(foto => ({
        url: `https://pixel.unap.edu.pe/fotos/jpg/${foto.nro_doc}?t=${Date.now()}`,
        anio: new Date(foto.created_at).getFullYear().toString()
      }))
    }
  } catch (err) {
    console.error(err)
  }
}

const resetear = () => {
  currentPhoto.value = null
  fotoGuardada.value = false
}

defineExpose({ guardarFoto, resetear, fotoGuardada })

watch(() => props.dni, () => {
  cargarHistorial()
  currentPhoto.value = null
  fotoGuardada.value = false
})

onMounted(() => {
  loadCameras()
})

onUnmounted(() => {
  stopCamera()
})
</script>

<style scoped>
.fotos-component {
  height: 100%;
}

.compact-card {
  border-radius: 8px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  padding-bottom: 6px;
  border-bottom: 1px solid #f0f0f0;
}

.card-title {
  font-weight: 600;
  font-size: 14px;
}

.camera-controls {
  display: flex;
  gap: 8px;
  margin-bottom: 10px;
}

.video-container {
  position: relative;
  margin-bottom: 10px;
  background: #f5f5f5;
  border-radius: 6px;
  overflow: hidden;
  min-height: 160px;
}

.video-preview {
  width: 100%;
  aspect-ratio: 16/9;
  object-fit: cover;
}

.video-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 160px;
  color: #bfbfbf;
  gap: 8px;
}

.video-placeholder span {
  font-size: 12px;
}

.capture-btn {
  margin-bottom: 10px;
}

.photo-preview {
  margin-top: 10px;
  padding: 8px;
  background: #fafafa;
  border-radius: 6px;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
  font-size: 12px;
  font-weight: 500;
}

.preview-img {
  width: 100%;
  aspect-ratio: 3/4;
  object-fit: cover;
  border-radius: 4px;
  margin-bottom: 8px;
}

.historial-header {
  font-weight: 500;
  font-size: 12px;
  margin-bottom: 8px;
  color: #595959;
}

.historial-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
}

.historial-item {
  text-align: center;
}

.historial-item img {
  width: 100%;
  aspect-ratio: 3/4;
  object-fit: cover;
  border-radius: 4px;
  border: 1px solid #f0f0f0;
}

.historial-item span {
  font-size: 10px;
  color: #8c8c8c;
  display: block;
  margin-top: 2px;
}

.empty-img {
  width: 100%;
  aspect-ratio: 3/4;
  background: #fafafa;
  border: 1px dashed #d9d9d9;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #bfbfbf;
  font-size: 20px;
}

.hidden {
  display: none;
}

.mt-2 {
  margin-top: 8px;
}

.mb-0 {
  margin-bottom: 0;
}
</style>