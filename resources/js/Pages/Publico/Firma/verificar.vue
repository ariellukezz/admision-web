<template>
  <div class="pdf-verification-container">
    <!-- Header -->
    <div class="verification-header">
      <a-breadcrumb>
        <a-breadcrumb-item>Inicio</a-breadcrumb-item>
        <a-breadcrumb-item>Verificación</a-breadcrumb-item>
        <a-breadcrumb-item>{{ data.codigo_inscripcion }}</a-breadcrumb-item>
      </a-breadcrumb>

      <div class="header-title">
        <h1>Verificación de Documento Firmado</h1>
        <p class="subtitle">Análisis digital de firmas electrónicas</p>
      </div>
    </div>

    <!-- Resumen -->
    <a-row :gutter="16" class="summary-cards">
      <a-col :xs="24" :sm="12" :md="6" v-for="card in summaryCards" :key="card.label">
        <a-card class="summary-card" :bordered="false">
          <div class="card-content">
            <div class="card-icon" :style="{ background: card.bg }">
              <component :is="card.icon" :style="{ color: card.color, fontSize: '24px' }" />
            </div>
            <div class="card-stats">
              <h3>{{ card.value }}</h3>
              <p>{{ card.label }}</p>
            </div>
          </div>
        </a-card>
      </a-col>
    </a-row>

    <!-- PDF + Firmas -->
    <a-row :gutter="24" class="main-content">
      <!-- PDF Viewer -->
      <a-col :xs="24" :md="14">
        <a-card class="pdf-viewer-card" :bordered="false">
          <template #title>
            Documento: {{ data.codigo_inscripcion }}.pdf
          </template>
          <template #extra>
            <a-space>
              <a-button shape="circle" @click="zoomOut" size="small"><ZoomOutOutlined /></a-button>
              <span>{{ Math.round(zoomLevel*100) }}%</span>
              <a-button shape="circle" @click="zoomIn" size="small"><ZoomInOutlined /></a-button>
              <a-divider type="vertical"/>
              <a-button type="primary" @click="descargarPdf"><DownloadOutlined /> Descargar PDF</a-button>
            </a-space>
          </template>

          <div class="pdf-container">
            <iframe
              v-if="pdfUrl"
              ref="pdfFrame"
              :src="pdfUrl"
              :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top left' }"
              @load="onPdfLoaded"
              @error="onPdfError"
            />
            <div v-else class="pdf-placeholder">
              <FileSearchOutlined style="font-size:48px; color:#d9d9d9"/>
              <p>Documento no disponible</p>
            </div>
          </div>
        </a-card>
      </a-col>

      <!-- Firmas -->
      <a-col :xs="24" :md="10">
        <a-card class="signatures-card" title="Análisis de Firmas Digitales" :bordered="false">
          <a-alert
            :message="estadoTexto"
            :type="estadoAlertType"
            :description="estadoDescripcion"
            show-icon
          />
          <div v-if="!data.firmas?.length" class="no-signatures">
            <EditOutlined style="font-size:48px; color:#d9d9d9"/>
            <p>No se detectaron firmas digitales</p>
          </div>
          <a-collapse v-else accordion>
            <a-collapse-panel v-for="(firma, i) in data.firmas" :key="i" :header="`Firma #${i+1}: ${firma.firmante}`">
              <p><strong>Estado:</strong> <a-tag :color="getFirmaColor(firma.estado_servicio)">{{ firma.estado_servicio }}</a-tag></p>
              <p><strong>Fecha:</strong> {{ firma.fecha_firma }}</p>
              <p><strong>Email:</strong> {{ firma.email || 'No especificado' }}</p>
              <p><strong>Algoritmo:</strong> {{ firma.algoritmo }}</p>
              <p><strong>Campo:</strong> {{ firma.campo_firma }}</p>
            </a-collapse-panel>
          </a-collapse>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import {
  FileTextOutlined,
  SafetyCertificateOutlined,
  EditOutlined,
  CalendarOutlined,
  DownloadOutlined,
  ZoomInOutlined,
  ZoomOutOutlined,
  FileSearchOutlined
} from '@ant-design/icons-vue'

const props = defineProps({ codigo: String })

const data = ref({
  codigo_inscripcion: props.codigo || '',
  estado: '',
  total_firmas: 0,
  firmas: []
})

const pdfUrl = ref('')
const pdfFrame = ref(null)
const zoomLevel = ref(1)

const cargarDatos = async () => {
  try {
    const res = await axios.get(`/verificar/${props.codigo}`)
    data.value = res.data?.data || res.data || data.value
  } catch {}
}

const cargarPdf = async () => {
  pdfUrl.value = `/inscripcion/${props.codigo}/pdf`
}

const onPdfLoaded = () => {}
const onPdfError = () => { pdfUrl.value = '' }

const zoomIn = () => { if(zoomLevel.value<2) zoomLevel.value+=0.1 }
const zoomOut = () => { if(zoomLevel.value>0.3) zoomLevel.value-=0.1 }
const descargarPdf = () => window.open(`/inscripcion/${props.codigo}/pdf`, '_blank')

// Computeds para estado
const estadoTexto = computed(() => data.value.estado || 'No Verificado')
const estadoAlertType = computed(() => data.value.estado === 'VÁLIDO' ? 'success' : 'error')
const estadoDescripcion = computed(() => 'Estado de verificación del documento.')
const getFirmaColor = estado => estado === 'VÁLIDO' ? 'green' : 'red'

// Cards resumen
const summaryCards = computed(() => [
  { label: 'Código de Inscripción', value: data.value.codigo_inscripcion, icon: FileTextOutlined, bg:'#e6f4ff', color:'#1890ff' },
  { label: 'Estado', value: estadoTexto.value, icon: SafetyCertificateOutlined, bg:'#52c41a', color:'#fff' },
  { label: 'Firmas Detectadas', value: data.value.total_firmas || 0, icon: EditOutlined, bg:'#f6ffed', color:'#52c41a' },
  { label: 'Fecha Verificación', value: new Date().toLocaleDateString(), icon: CalendarOutlined, bg:'#fff7e6', color:'#fa8c16' }
])

onMounted(async () => {
  await Promise.all([cargarDatos(), cargarPdf()])
})
</script>

<style scoped>
.pdf-verification-container { padding:16px; background:#f9f9f9; font-family:sans-serif; }
.verification-header { margin-bottom:24px; }
.header-title h1 { margin:4px 0; font-size:24px; }
.header-title .subtitle { color:#8e8e93; margin:0; font-size:14px; }

.summary-cards .summary-card { border-radius:12px; margin-bottom:12px; }
.card-content { display:flex; align-items:center; gap:12px; }
.card-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; }
.card-stats h3 { margin:0; font-size:18px; }
.card-stats p { margin:0; font-size:12px; color:#666; }

.pdf-viewer-card, .signatures-card { border-radius:12px; margin-bottom:16px; }
.pdf-container { width:100%; min-height:400px; position:relative; overflow:hidden; }
.pdf-container iframe { width:100%; height:80vh; border:none; border-radius:8px; }

.pdf-placeholder { display:flex; flex-direction:column; justify-content:center; align-items:center; color:#999; height:80vh; text-align:center; }

@media(max-width:768px){
  .pdf-container iframe { height:60vh; }
}
</style>
