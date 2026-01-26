<template>
  <div class="pdf-verification-container">
    <!-- Header con Breadcrumb -->
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

    <!-- Tarjetas de Resumen -->
    <a-row :gutter="16" class="summary-cards">
      <a-col :xs="24" :sm="12" :md="6">
        <a-card class="summary-card" :bordered="false">
          <div class="card-content">
            <div class="card-icon" style="background: #e6f4ff;">
              <FileTextOutlined style="color: #1890ff; font-size: 24px;" />
            </div>
            <div class="card-stats">
              <h3>{{ data.codigo_inscripcion }}</h3>
              <p>Código de Inscripción</p>
            </div>
          </div>
        </a-card>
      </a-col>

      <a-col :xs="24" :sm="12" :md="6">
        <a-card class="summary-card" :bordered="false">
          <div class="card-content">
            <div class="card-icon" :style="{ background: estadoGradient }">
              <SafetyCertificateOutlined style="color: #fff; font-size: 24px;" />
            </div>
            <div class="card-stats">
              <h3 :style="{ color: estadoColor }">{{ estadoTexto }}</h3>
              <p>Estado de Verificación</p>
            </div>
          </div>
        </a-card>
      </a-col>

      <a-col :xs="24" :sm="12" :md="6">
        <a-card class="summary-card" :bordered="false">
          <div class="card-content">
            <div class="card-icon" style="background: #f6ffed;">
              <EditOutlined style="color: #52c41a; font-size: 24px;" />
            </div>
            <div class="card-stats">
              <h3>{{ data.total_firmas || 0 }}</h3>
              <p>Firmas Detectadas</p>
            </div>
          </div>
        </a-card>
      </a-col>

      <a-col :xs="24" :sm="12" :md="6">
        <a-card class="summary-card" :bordered="false">
          <div class="card-content">
            <div class="card-icon" style="background: #fff7e6;">
              <CalendarOutlined style="color: #fa8c16; font-size: 24px;" />
            </div>
            <div class="card-stats">
              <h3>{{ data.fecha_verificacion || '--/--/----' }}</h3>
              <p>Fecha de Verificación</p>
            </div>
          </div>
        </a-card>
      </a-col>
    </a-row>

    <!-- Contenido Principal -->
    <a-row :gutter="24" class="main-content">
      <!-- Panel del PDF -->
      <a-col :xs="24" :md="14">
        <a-card
          class="pdf-viewer-card"
          :title="`Documento: ${data.codigo_inscripcion}.pdf`"
          :bordered="false"
        >
          <template #extra>
            <a-space>
              <a-tooltip title="Disminuir zoom">
                <a-button shape="circle" @click="zoomOut" size="small">
                  <ZoomOutOutlined />
                </a-button>
              </a-tooltip>
              <span class="zoom-level">{{ Math.round(zoomLevel * 100) }}%</span>
              <a-tooltip title="Aumentar zoom">
                <a-button shape="circle" @click="zoomIn" size="small">
                  <ZoomInOutlined />
                </a-button>
              </a-tooltip>
              <a-divider type="vertical" />
              <a-button type="primary" @click="descargarPdf" class="download-btn">
                <DownloadOutlined />
                Descargar PDF
              </a-button>
            </a-space>
          </template>

          <div class="pdf-viewer-wrapper">
            <a-spin :spinning="loadingPdf" size="large" tip="Cargando documento...">
              <iframe
                v-if="pdfUrl"
                ref="pdfFrame"
                :src="pdfUrl"
                class="pdf-iframe"
                :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top left' }"
                @load="onPdfLoaded"
                @error="onPdfError"
              ></iframe>

              <div v-if="pdfError && !loadingPdf" class="pdf-error-state">
                <FileExclamationOutlined style="font-size: 48px; color: #ff4d4f;" />
                <h3>Error al cargar el PDF</h3>
                <p>{{ pdfErrorMessage }}</p>
                <a-button type="primary" @click="cargarPdf">Reintentar carga</a-button>
              </div>

              <div v-if="!pdfUrl && !loadingPdf && !pdfError" class="pdf-empty-state">
                <FileSearchOutlined style="font-size: 48px; color: #d9d9d9;" />
                <h3>Documento no disponible</h3>
                <p>No se pudo cargar el documento PDF</p>
              </div>
            </a-spin>
          </div>
        </a-card>
      </a-col>

      <!-- Panel de Verificación de Firmas -->
      <a-col :xs="24" :md="10">
        <a-card class="signatures-card" title="Análisis de Firmas Digitales" :bordered="false">
          <div class="signatures-list">
            <div v-if="!data.firmas || data.firmas.length === 0" class="no-signatures">
              <EditOutlined style="font-size: 48px; color: #d9d9d9;" />
              <p>No se detectaron firmas digitales</p>
            </div>

            <!-- Firmas Electrónicas Mejoradas -->
            <a-collapse v-else accordion>
              <a-collapse-panel
                v-for="(firma, index) in data.firmas"
                :key="index"
                :header="`Firma #${index + 1}: ${firma.firmante || 'Desconocido'}`"
                class="signature-panel"
              >
                <div class="signature-card">
                  <div class="signature-header" :style="{ borderLeftColor: getFirmaColor(firma.estado_servicio) }">
                    <SafetyCertificateOutlined style="font-size:24px; margin-right:8px;" />
                    <div>
                      <h4>{{ firma.estado_servicio }}</h4>
                      <span class="signature-date">{{ firma.fecha_firma }}</span>
                    </div>
                  </div>

                  <div class="signature-body">
                    <div class="signature-info">
                      <p><strong>Nombre:</strong> {{ firma.firmante }}</p>
                      <p><strong>Email:</strong> {{ firma.email || 'No especificado' }}</p>
                      <p><strong>Algoritmo:</strong> {{ firma.algoritmo }}</p>
                      <p><strong>Campo de firma:</strong> <code>{{ firma.campo_firma || '-' }}</code></p>
                    </div>

                    <div class="certificate-info">
                      <h5>Certificado Digital</h5>
                      <p><strong>Emisor:</strong> {{ firma.certificado.emisor }}</p>
                      <p><strong>Sujeto:</strong> {{ firma.certificado.sujeto }}</p>
                      <p><strong>Número de Serie:</strong> <code>{{ firma.certificado.numero_serie }}</code></p>
                      <p><strong>Validez:</strong> {{ firma.certificado.valido_desde }} → {{ firma.certificado.valido_hasta }}</p>
                    </div>

                    <div v-if="firma.advertencias?.length" class="signature-warnings">
                      <a-alert
                        type="warning"
                        :message="`Advertencia${firma.advertencias.length > 1 ? 's' : ''}`"
                        :description="firma.advertencias.join(' | ')"
                        show-icon
                      />
                    </div>
                  </div>
                </div>
              </a-collapse-panel>
            </a-collapse>
          </div>

          <!-- Acciones -->
          <div class="verification-actions">
            <a-button type="primary" block size="large" @click="descargarPdf" class="action-btn">
              <DownloadOutlined />
              Descargar Certificado de Verificación
            </a-button>
            <a-button block size="large" @click="cargarPdf" class="action-btn">
              <ReloadOutlined />
              Reanalizar Documento
            </a-button>
          </div>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import {
  FileTextOutlined,
  SafetyCertificateOutlined,
  EditOutlined,
  CalendarOutlined,
  DownloadOutlined,
  ZoomInOutlined,
  ZoomOutOutlined,
  ClockCircleOutlined,
  FileExclamationOutlined,
  FileSearchOutlined,
  ReloadOutlined
} from '@ant-design/icons-vue'

const props = defineProps({
  codigo: { type: String, required: true, default: 'EX25380044' }
})

const data = ref({
  codigo_inscripcion: '',
  estado: '',
  total_firmas: 0,
  fecha_verificacion: '',
  firmas: []
})

const pdfFrame = ref(null)
const pdfUrl = ref('')
const zoomLevel = ref(1.0)
const loadingPdf = ref(false)
const pdfError = ref(false)
const pdfErrorMessage = ref('')

const estadoTexto = computed(() => {
  const e = data.value.estado?.toUpperCase()
  if (['VÁLIDO','VALIDO','FIRMADO_VÁLIDO'].includes(e)) return 'Documento Válido'
  if (['INVÁLIDO','INVALIDO','FIRMADO_INVÁLIDO'].includes(e)) return 'Documento Inválido'
  if (['INDETERMINADO','FIRMADO_INDETERMINADO'].includes(e)) return 'Verificación Indeterminada'
  return 'No Verificado'
})

const estadoColor = computed(() => {
  const e = data.value.estado?.toUpperCase()
  if (['VÁLIDO','VALIDO','FIRMADO_VÁLIDO'].includes(e)) return '#52c41a'
  if (['INVÁLIDO','INVALIDO','FIRMADO_INVÁLIDO'].includes(e)) return '#ff4d4f'
  if (['INDETERMINADO','FIRMADO_INDETERMINADO'].includes(e)) return '#fa8c16'
  return '#1890ff'
})

const estadoGradient = computed(() => `linear-gradient(135deg, ${estadoColor.value} 0%, ${estadoColor.value}80 100%)`)
const estadoAlertType = computed(() => {
  const e = data.value.estado?.toUpperCase()
  if (['VÁLIDO','VALIDO','FIRMADO_VÁLIDO'].includes(e)) return 'success'
  if (['INVÁLIDO','INVALIDO','FIRMADO_INVÁLIDO'].includes(e)) return 'error'
  if (['INDETERMINADO','FIRMADO_INDETERMINADO'].includes(e)) return 'warning'
  return 'info'
})
const estadoDescripcion = computed(() => {
  const e = data.value.estado?.toUpperCase()
  if (['VÁLIDO','VALIDO','FIRMADO_VÁLIDO'].includes(e)) return 'Todas las firmas digitales son válidas y los certificados están vigentes.'
  if (['INVÁLIDO','INVALIDO','FIRMADO_INVÁLIDO'].includes(e)) return 'Se detectaron firmas inválidas o certificados caducados.'
  if (['INDETERMINADO','FIRMADO_INDETERMINADO'].includes(e)) return 'Las firmas son técnicamente válidas pero requieren verificación adicional.'
  return 'El documento aún no ha sido verificado.'
})

const getFirmaColor = (estado) => {
  const e = estado?.toUpperCase()
  if (['VÁLIDA','VALIDO','VALIDA'].includes(e)) return '#52c41a'
  if (['INVÁLIDA','INVALIDO','INVALIDA'].includes(e)) return '#ff4d4f'
  if (e?.includes('INDETERMINADA') || e?.includes('INDETERMINADO')) return '#fa8c16'
  return '#1890ff'
}

const cargarDatos = async () => {
  try {
    const res = await axios.get(`/verificar/${props.codigo}`)
    if (res.data.success) data.value = res.data.data || res.data
    else data.value = { codigo_inscripcion: props.codigo, estado: 'ERROR', total_firmas: 0, fecha_verificacion: new Date().toLocaleDateString('es-PE'), firmas: [] }
  } catch {
    data.value = { codigo_inscripcion: props.codigo, estado: 'ERROR_DE_CONEXION', total_firmas: 0, fecha_verificacion: new Date().toLocaleDateString('es-PE'), firmas: [] }
  }
}

const cargarPdf = async () => {
  loadingPdf.value = true
  pdfError.value = false
  pdfErrorMessage.value = ''
  try {
    pdfUrl.value = `/inscripcion/${props.codigo}/pdf`
    if (pdfFrame.value) pdfFrame.value.src = pdfUrl.value
  } catch (err) {
    pdfError.value = true
    pdfErrorMessage.value = err.message || 'Error desconocido'
    pdfUrl.value = ''
  }
}

const onPdfLoaded = () => { loadingPdf.value = false; pdfError.value = false }
const onPdfError = () => { loadingPdf.value = false; pdfError.value = true; pdfErrorMessage.value = 'No se pudo cargar el PDF' }

const zoomIn = () => { if (zoomLevel.value < 2.0) zoomLevel.value += 0.1 }
const zoomOut = () => { if (zoomLevel.value > 0.3) zoomLevel.value -= 0.1 }
const descargarPdf = () => window.open(`/inscripcion/${props.codigo}/pdf`, '_blank')

onMounted(async () => { await Promise.all([cargarDatos(), cargarPdf()]) })
onUnmounted(() => { if (pdfFrame.value) pdfFrame.value.src = '' })
</script>

<style scoped>
.pdf-verification-container {
  padding: 24px;
  background: #f9f9f9;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* Header */
.verification-header { margin-bottom: 24px; }
.header-title h1 { font-size: 26px; font-weight: 600; margin: 4px 0; color: #1c1c1e; }
.header-title .subtitle { color: #8e8e93; font-size: 14px; }

/* Tarjetas */
.summary-cards { margin-bottom: 24px; }
.summary-card {
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  transition: all 0.25s ease;
}
.summary-card:hover { transform: translateY(-2px); }

/* PDF Viewer */
.pdf-viewer-wrapper { min-height: 600px; position: relative; overflow: hidden; }
.pdf-iframe { width: 100%; height: 600px; border: none; border-radius: 12px; }

/* Zoom */
.zoom-level { font-weight: 500; color: #666; min-width: 50px; text-align: center; }
.download-btn { border-radius: 12px; font-weight: 500; }

/* Firmas Electrónicas */
.signature-card { border: 1px solid #f0f0f0; border-radius: 12px; background: #fff; padding: 16px; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.signature-header { display: flex; align-items: center; border-left: 4px solid #1890ff; padding-left: 8px; margin-bottom: 12px; }
.signature-header h4 { margin:0; font-size:16px; font-weight:600; }
.signature-date { font-size:12px; color:#888; }
.signature-body { display: flex; flex-direction: column; gap:12px; }
.signature-info p, .certificate-info p { margin: 2px 0; font-size: 13px; }
.certificate-info { background:#f6f6f6; padding:8px 12px; border-radius:8px; }
.signature-warnings { margin-top:8px; }

/* Responsive */
@media (max-width: 768px) {
  .pdf-iframe { height: 400px; }
}
</style>
