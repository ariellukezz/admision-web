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
      <a-col :span="6">
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

      <a-col :span="6">
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

      <a-col :span="6">
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

      <a-col :span="6">
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
      <a-col :span="14">
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
              <a-button
                type="primary"
                @click="descargarPdf"
                class="download-btn"
              >
                <DownloadOutlined />
                Descargar PDF
              </a-button>
            </a-space>
          </template>

          <div class="pdf-viewer-wrapper">
            <a-spin
              :spinning="loadingPdf"
              size="large"
              tip="Cargando documento..."
            >
              <!-- iFrame para mostrar el PDF -->
              <iframe
                v-if="pdfUrl"
                ref="pdfFrame"
                :src="pdfUrl"
                class="pdf-iframe"
                :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top left' }"
                @load="onPdfLoaded"
                @error="onPdfError"
              />

              <!-- Estado de carga/error -->
              <div v-if="pdfError && !loadingPdf" class="pdf-error-state">
                <FileExclamationOutlined style="font-size: 48px; color: #ff4d4f;" />
                <h3>Error al cargar el PDF</h3>
                <p>{{ pdfErrorMessage }}</p>
                <a-button type="primary" @click="cargarPdf">
                  Reintentar carga
                </a-button>
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
      <a-col :span="10">
        <a-card
          class="signatures-card"
          title="Análisis de Firmas Digitales"
          :bordered="false"
        >
          <!-- Estado de Verificación -->
          <div class="verification-status">
            <div class="status-header">
              <SafetyCertificateOutlined />
              <h3>Estado de Verificación</h3>
            </div>
            <a-alert
              :message="estadoTexto"
              :type="estadoAlertType"
              :description="estadoDescripcion"
              show-icon
              class="verification-alert"
            />
          </div>

          <!-- Lista de Firmas -->
          <div class="signatures-list">
            <div class="list-header">
              <h3>Firmas Detectadas ({{ data.firmas?.length || 0 }})</h3>
              <a-tag :color="estadoColor" class="status-tag">
                {{ data.total_firmas || 0 }} encontradas
              </a-tag>
            </div>

            <div v-if="!data.firmas || data.firmas.length === 0" class="no-signatures">
              <EditOutlined style="font-size: 48px; color: #d9d9d9;" />
              <p>No se detectaron firmas digitales</p>
            </div>

            <a-collapse
              v-else
              accordion
              :bordered="false"
              class="signatures-collapse"
            >
              <a-collapse-panel
                v-for="(firma, index) in data.firmas"
                :key="firma.indice"
                :header="`Firma #${index + 1}: ${firma.firmante || 'Desconocido'}`"
                class="signature-panel"
              >
                <div class="signature-details">
                  <!-- Estado de la firma -->
                  <div class="signature-status">
                    <a-tag :color="getFirmaColor(firma.estado_servicio)" class="status-badge">
                      {{ firma.estado_servicio }}
                    </a-tag>
                    <span class="signature-time">
                      <ClockCircleOutlined />
                      {{ firma.fecha_firma }}
                    </span>
                  </div>

                  <!-- Información del firmante -->
                  <a-descriptions
                    :column="1"
                    size="small"
                    class="signer-info"
                  >
                    <a-descriptions-item label="Algoritmo">
                      <a-tag color="blue">{{ firma.algoritmo }}</a-tag>
                    </a-descriptions-item>
                    <a-descriptions-item label="Email">
                      {{ firma.email || 'No especificado' }}
                    </a-descriptions-item>
                    <a-descriptions-item label="Campo de firma">
                      <code>{{ firma.campo_firma }}</code>
                    </a-descriptions-item>
                  </a-descriptions>

                  <!-- Información del certificado -->
                  <div class="certificate-info">
                    <h4>Certificado Digital</h4>
                    <a-descriptions :column="1" size="small" bordered>
                      <a-descriptions-item label="Emisor">
                        {{ firma.certificado.emisor }}
                      </a-descriptions-item>
                      <a-descriptions-item label="Sujeto">
                        {{ firma.certificado.sujeto }}
                      </a-descriptions-item>
                      <a-descriptions-item label="Número de Serie">
                        <code class="serial-number">{{ firma.certificado.numero_serie }}</code>
                      </a-descriptions-item>
                      <a-descriptions-item label="Validez">
                        <div class="validity-dates">
                          <span>Desde: {{ firma.certificado.valido_desde }}</span>
                          <span>Hasta: {{ firma.certificado.valido_hasta }}</span>
                        </div>
                      </a-descriptions-item>
                    </a-descriptions>
                  </div>

                  <!-- Información adicional -->
                  <div v-if="firma.advertencias && firma.advertencias.length > 0" class="signature-warnings">
                    <a-alert
                      type="warning"
                      :message="`Advertencia${firma.advertencias.length > 1 ? 's' : ''}`"
                      :description="firma.advertencias.join(' | ')"
                      show-icon
                    />
                  </div>
                </div>
              </a-collapse-panel>
            </a-collapse>
          </div>

          <!-- Acciones -->
          <div class="verification-actions">
            <a-button
              type="primary"
              block
              size="large"
              @click="descargarPdf"
              class="action-btn"
            >
              <DownloadOutlined />
              Descargar Certificado de Verificación
            </a-button>
            <a-button
              block
              size="large"
              @click="cargarPdf"
              class="action-btn"
            >
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
  if (['VÁLIDA','VALIDO','VALIDA'].includes(e)) return 'green'
  if (['INVÁLIDA','INVALIDO','INVALIDA'].includes(e)) return 'red'
  if (e?.includes('INDETERMINADA') || e?.includes('INDETERMINADO')) return 'orange'
  return 'blue'
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
    pdfUrl.value = `http://admision.test/inscripcion/${props.codigo}/pdf`
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
const descargarPdf = () => window.open(`http://admision.test/inscripcion/${props.codigo}/pdf`, '_blank')

onMounted(async () => { await Promise.all([cargarDatos(), cargarPdf()]) })
onUnmounted(() => { if (pdfFrame.value) pdfFrame.value.src = '' })
</script>

<style scoped>
.pdf-verification-container {
  padding: 32px;
  background: #f9f9f9;
  min-height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.verification-header { margin-bottom: 32px; }

.header-title h1 { font-size: 28px; font-weight: 600; margin: 8px 0 4px; color: #1c1c1e; }
.header-title .subtitle { color: #8e8e93; font-size: 15px; margin: 0; }

.summary-cards { margin-bottom: 32px; }
.summary-card {
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  transition: all 0.25s ease;
}
.summary-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.08); }

.card-content { display: flex; align-items: center; gap: 16px; }
.card-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; }
.card-stats h3 { font-size: 20px; font-weight: 600; margin: 0 0 4px 0; color: #1c1c1e; }
.card-stats p { font-size: 13px; color: #8e8e93; margin: 0; }

.pdf-viewer-card, .signatures-card { border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: none; }
.pdf-viewer-wrapper { min-height: 600px; position: relative; overflow: hidden; }
.pdf-iframe { width: 100%; height: 600px; border: none; border-radius: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.3s ease; }

.zoom-level { font-weight: 500; color: #666; min-width: 50px; text-align: center; }
.download-btn { border-radius: 12px; font-weight: 500; }

.verification-status { margin-bottom: 24px; }
.status-header { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.status-header h3 { margin: 0; font-size: 16px; font-weight: 600; }
.verification-alert { border-radius: 12px; }

.signatures-list { margin: 24px 0; }
.list-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.list-header h3 { margin: 0; font-size: 16px; font-weight: 600; }
.status-tag { font-weight: 500; border-radius: 12px; }

.no-signatures { text-align: center; padding: 48px 24px; color: #999; }

.signatures-collapse { background: transparent; }
.signature-panel :deep(.ant-collapse-header) { background: #fafafa; border-radius: 12px !important; margin-bottom: 8px; font-weight: 500; }

.signature-details { padding: 12px 0; }
.signature-status { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.status-badge { font-weight: 500; border-radius: 12px; }
.signature-time { color: #8e8e93; font-size: 13px; }

.signer-info { margin-bottom: 16px; }
.signer-info :deep(.ant-descriptions-item-label) { font-weight: 500; color: #666; }

.certificate-info { margin: 16px 0; }
.certificate-info h4 { font-size: 14px; font-weight: 600; margin-bottom: 12px; color: #1c1c1e; }
.serial-number { background: #f5f5f5; padding: 2px 6px; border-radius: 6px; font-family: monospace; font-size: 12px; word-break: break-all; }
.validity-dates { display: flex; flex-direction: column; gap: 4px; color: #666; font-size: 13px; }

.signature-warnings { margin-top: 16px; }

.verification-actions { margin-top: 24px; display: flex; flex-direction: column; gap: 12px; }
.action-btn { border-radius: 12px; font-weight: 500; height: 48px; }

.pdf-error-state, .pdf-empty-state {
  position: absolute; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  text-align: center; color: #666; background: white;
  padding: 32px; border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  width: 80%;
}
.pdf-error-state h3, .pdf-empty-state h3 { margin: 16px 0 8px 0; color: #1c1c1e; }
.pdf-error-state p, .pdf-empty-state p { margin-bottom: 16px; color: #999; }
</style>
