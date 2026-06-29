<template>
<Head title="Mi Actividad" />
<AuthenticatedLayout pagina="Mi Actividad">

<div class="dashboard-container">

  <!-- KPI CARDS PERSONALES -->
  <div class="kpi-row">
    <div class="kpi-card">
      <div class="kpi-icon" style="background: #dbeafe;">
        <FileProtectOutlined style="color: #3b82f6;" />
      </div>
      <div class="kpi-info">
        <span class="kpi-label">Documentos verificados</span>
        <span class="kpi-value">{{ resumen.docs_verificados }}</span>
        <span class="kpi-sub">
          <ArrowUpOutlined style="color: #22c55e; font-size: 11px;" />
          <span style="color: #22c55e; font-weight: 600;">{{ resumen.docs_verificados_hoy }}</span>
          <span style="color: #94a3b8;">hoy</span>
        </span>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon" style="background: #dcfce7;">
        <DollarOutlined style="color: #22c55e;" />
      </div>
      <div class="kpi-info">
        <span class="kpi-label">Comprobantes verificados</span>
        <span class="kpi-value">{{ resumen.comp_verificados }}</span>
        <span class="kpi-sub">
          <ArrowUpOutlined style="color: #22c55e; font-size: 11px;" />
          <span style="color: #22c55e; font-weight: 600;">{{ resumen.comp_verificados_hoy }}</span>
          <span style="color: #94a3b8;">hoy</span>
        </span>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon" style="background: #fef3c7;">
        <SafetyOutlined style="color: #f59e0b;" />
      </div>
      <div class="kpi-info">
        <span class="kpi-label">Controles biométricos</span>
        <span class="kpi-value">{{ resumen.biometricos }}</span>
        <span class="kpi-sub">
          <ArrowUpOutlined style="color: #22c55e; font-size: 11px;" />
          <span style="color: #22c55e; font-weight: 600;">{{ resumen.biometricos_hoy }}</span>
          <span style="color: #94a3b8;">hoy</span>
        </span>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon" style="background: #f3e8ff;">
        <SolutionOutlined style="color: #a855f7;" />
      </div>
      <div class="kpi-info">
        <span class="kpi-label">Inscripciones procesadas</span>
        <span class="kpi-value">{{ resumen.inscripciones }}</span>
        <span class="kpi-sub">
          <ArrowUpOutlined style="color: #22c55e; font-size: 11px;" />
          <span style="color: #22c55e; font-weight: 600;">{{ resumen.inscripciones_hoy }}</span>
          <span style="color: #94a3b8;">hoy</span>
        </span>
      </div>
    </div>
  </div>

  <!-- PENDIENTES GLOBALES -->
  <div class="pendientes-row" v-if="resumen.total_docs_pendientes > 0 || resumen.total_comp_pendientes > 0">
    <div class="pendiente-card">
      <div class="pendiente-info">
        <span class="pendiente-label">Docs. pendientes (global)</span>
        <span class="pendiente-value">{{ resumen.total_docs_pendientes }}</span>
      </div>
      <a-progress :percent="docsPercent" :show-info="false" stroke-color="#3b82f6" size="small" />
    </div>
    <div class="pendiente-card">
      <div class="pendiente-info">
        <span class="pendiente-label">Comprob. pendientes (global)</span>
        <span class="pendiente-value">{{ resumen.total_comp_pendientes }}</span>
      </div>
      <a-progress :percent="compsPercent" :show-info="false" stroke-color="#22c55e" size="small" />
    </div>
  </div>

  <!-- EMPTY STATE -->
  <div class="empty-card" v-if="resumen.docs_verificados === 0 && resumen.comp_verificados === 0 && resumen.biometricos === 0 && resumen.inscripciones === 0">
    <div class="empty-icon"><InboxOutlined style="font-size: 48px; color: #cbd5e1;" /></div>
    <h3>Sin actividad registrada</h3>
    <p>Tus estadísticas aparecerán cuando comiences a verificar documentos, comprobantes o realizar controles biométricos.</p>
  </div>

  <!-- ACCIONES RECIENTES + DISTRIBUCIÓN -->
  <div class="charts-row" v-if="acciones.length > 0 || distribucion.some(d => d.cant > 0)">
    <div class="chart-card chart-card-lg">
      <div class="chart-header">
        <h3>Acciones recientes</h3>
      </div>
      <div class="acciones-body">
        <div v-for="(acc, idx) in acciones" :key="idx" class="accion-item">
          <div class="accion-tipo">
            <a-tag :color="tipoColor(acc.tipo)" style="margin: 0;">{{ acc.tipo }}</a-tag>
          </div>
          <div class="accion-datos">
            <span class="accion-nombre">{{ acc.nombres }} {{ acc.paterno }} {{ acc.materno }}</span>
            <span class="accion-detalle">{{ acc.detalle }}</span>
          </div>
          <div class="accion-fecha">
            {{ formatFecha(acc.fecha) }}
          </div>
        </div>
        <div v-if="acciones.length === 0" class="chart-empty">Sin acciones recientes</div>
      </div>
    </div>

    <div class="chart-card chart-card-md">
      <div class="chart-header"><h3>Distribución de mi actividad</h3></div>
      <div class="chart-body">
        <Doughnut v-if="distribucion.some(d => d.cant > 0)" :data="distribucionData" :options="doughnutOptions" />
        <div v-else class="chart-empty">Sin datos de actividad</div>
      </div>
    </div>
  </div>

  <!-- TIMELINE + RANKING -->
  <div class="charts-row" v-if="timeline.length > 0 || ranking.length > 0">
    <div class="chart-card chart-card-lg">
      <div class="chart-header"><h3>Mi actividad últimos 30 días</h3></div>
      <div class="chart-body">
        <Line v-if="timeline.length > 0" :data="timelineData" :options="timelineOptions" />
        <div v-else class="chart-empty">Sin actividad en los últimos 30 días</div>
      </div>
    </div>

    <div class="chart-card chart-card-md">
      <div class="chart-header"><h3>Ranking de revisores</h3></div>
      <div class="ranking-body">
        <div v-for="(rev, idx) in ranking" :key="rev.id" class="ranking-item" :class="{ 'ranking-yo': rev.es_yo }">
          <div class="ranking-pos">
            <span v-if="idx < 3" class="ranking-medal" :style="{ color: ['#f59e0b', '#94a3b8', '#cd7f32'][idx] }">{{ idx + 1 }}</span>
            <span v-else class="ranking-num">{{ idx + 1 }}</span>
          </div>
          <div class="ranking-info">
            <span class="ranking-nombre">{{ rev.nombre }}</span>
            <span class="ranking-detalle">{{ rev.docs }} docs · {{ rev.comps }} comp · {{ rev.bios }} bio · {{ rev.inscs }} insc</span>
          </div>
          <div class="ranking-total">
            <span class="ranking-total-num">{{ rev.total }}</span>
          </div>
        </div>
        <div v-if="ranking.length === 0" class="chart-empty">Sin datos de ranking</div>
      </div>
    </div>
  </div>

  <!-- PENDIENTES POR VERIFICAR -->
  <div class="charts-row" v-if="pendientes.docs_pendientes?.length > 0 || pendientes.comps_pendientes?.length > 0">
    <div class="chart-card chart-card-md">
      <div class="chart-header">
        <h3>📄 Documentos por verificar</h3>
      </div>
      <div class="pendientes-body">
        <div v-for="(doc, idx) in pendientes.docs_pendientes" :key="'d'+idx" class="pendiente-item">
          <div class="pendiente-datos">
            <span class="pendiente-nombre">{{ doc.nombres }} {{ doc.paterno }}</span>
            <span class="pendiente-detalle">{{ doc.tipo_doc }} · {{ doc.programa }}</span>
          </div>
          <span class="pendiente-dni">{{ doc.dni }}</span>
        </div>
        <div v-if="pendientes.docs_pendientes?.length === 0" class="chart-empty">Sin documentos pendientes</div>
      </div>
    </div>

    <div class="chart-card chart-card-md">
      <div class="chart-header">
        <h3>💰 Comprobantes por verificar</h3>
      </div>
      <div class="pendientes-body">
        <div v-for="(comp, idx) in pendientes.comps_pendientes" :key="'c'+idx" class="pendiente-item">
          <div class="pendiente-datos">
            <span class="pendiente-nombre">{{ comp.nombres }} {{ comp.paterno }}</span>
            <span class="pendiente-detalle">Op. {{ comp.nro_operacion }} · S/ {{ comp.monto }}</span>
          </div>
          <span class="pendiente-dni">{{ comp.dni }}</span>
        </div>
        <div v-if="pendientes.comps_pendientes?.length === 0" class="chart-empty">Sin comprobantes pendientes</div>
      </div>
    </div>
  </div>

</div>

</AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import {
  FileProtectOutlined, DollarOutlined, SafetyOutlined, SolutionOutlined,
  ArrowUpOutlined, InboxOutlined
} from '@ant-design/icons-vue'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, BarElement, CategoryScale, Title, LinearScale, PointElement, LineElement, Filler } from 'chart.js'
import { Doughnut, Line } from 'vue-chartjs'
import axios from 'axios'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, ArcElement, Tooltip, Legend, PointElement, LineElement, Filler)

const COLORS = { blue: '#3b82f6', green: '#22c55e', amber: '#f59e0b', purple: '#a855f7', red: '#ef4444' }

const resumen = ref({
  docs_verificados: 0, docs_verificados_hoy: 0,
  comp_verificados: 0, comp_verificados_hoy: 0,
  biometricos: 0, biometricos_hoy: 0,
  inscripciones: 0, inscripciones_hoy: 0,
  total_docs_pendientes: 0, total_comp_pendientes: 0,
})
const timeline = ref([])
const acciones = ref([])
const distribucion = ref([])
const ranking = ref([])
const pendientes = ref({ docs_pendientes: [], comps_pendientes: [] })

const docsPercent = computed(() => {
  const total = resumen.value.docs_verificados + resumen.value.total_docs_pendientes
  return total > 0 ? Math.round((resumen.value.docs_verificados / total) * 100) : 0
})

const compsPercent = computed(() => {
  const total = resumen.value.comp_verificados + resumen.value.total_comp_pendientes
  return total > 0 ? Math.round((resumen.value.comp_verificados / total) * 100) : 0
})

const tipoColor = (tipo) => {
  const map = { 'Documento': 'blue', 'Comprobante': 'green', 'Biométrico': 'orange', 'Inscripción': 'purple' }
  return map[tipo] || 'default'
}

const formatFecha = (fecha) => {
  if (!fecha) return ''
  const d = new Date(fecha)
  const hoy = new Date()
  const diff = hoy - d
  if (diff < 3600000) return `Hace ${Math.max(1, Math.floor(diff / 60000))} min`
  if (diff < 86400000) return `Hace ${Math.floor(diff / 3600000)} h`
  return d.toLocaleDateString('es-PE', { day: '2-digit', month: 'short' })
}

const distribucionData = computed(() => ({
  labels: distribucion.value.map(d => d.tipo),
  datasets: [{
    data: distribucion.value.map(d => d.cant),
    backgroundColor: [COLORS.blue, COLORS.green, COLORS.amber, COLORS.purple],
    borderWidth: 0,
  }]
}))

const timelineData = computed(() => ({
  labels: timeline.value.map(d => d.fecha?.substring(5) || ''),
  datasets: [
    {
      label: 'Documentos',
      data: timeline.value.map(d => d.docs),
      borderColor: COLORS.blue,
      backgroundColor: 'rgba(59,130,246,0.1)',
      fill: true,
      tension: 0.4,
      pointRadius: 2,
    },
    {
      label: 'Comprobantes',
      data: timeline.value.map(d => d.comps),
      borderColor: COLORS.green,
      backgroundColor: 'rgba(34,197,94,0.1)',
      fill: true,
      tension: 0.4,
      pointRadius: 2,
    },
    {
      label: 'Biométrico',
      data: timeline.value.map(d => d.bios),
      borderColor: COLORS.amber,
      backgroundColor: 'rgba(245,158,11,0.1)',
      fill: true,
      tension: 0.4,
      pointRadius: 2,
    },
  ]
}))

const doughnutOptions = { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } } } }
const timelineOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12 } } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }

const fetchAll = async () => {
  try {
    const [r1, r2, r3, r4, r5, r6] = await Promise.all([
      axios.get('/revisor/mi-actividad/resumen').catch(() => null),
      axios.get('/revisor/mi-actividad/timeline').catch(() => null),
      axios.get('/revisor/mi-actividad/acciones-recientes').catch(() => null),
      axios.get('/revisor/mi-actividad/distribucion-actividad').catch(() => null),
      axios.get('/revisor/mi-actividad/ranking').catch(() => null),
      axios.get('/revisor/mi-actividad/pendientes').catch(() => null),
    ])
    if (r1?.data?.success) resumen.value = r1.data.datos
    if (r2?.data?.success) timeline.value = r2.data.datos
    if (r3?.data?.success) acciones.value = r3.data.datos
    if (r4?.data?.success) distribucion.value = r4.data.datos
    if (r5?.data?.success) ranking.value = r5.data.datos
    if (r6?.data?.success) pendientes.value = r6.data.datos
  } catch (e) { console.error('Error cargando mi actividad:', e) }
}

onMounted(() => { fetchAll() })
</script>

<style scoped>
.dashboard-container { padding: 16px; }

.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 14px; }
.kpi-card { background: white; border-radius: 14px; padding: 18px 20px; border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 14px; transition: box-shadow 0.2s; }
.kpi-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.05); }
.kpi-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.kpi-info { display: flex; flex-direction: column; width: 100%; }
.kpi-label { font-size: 12px; color: #94a3b8; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 26px; font-weight: 700; color: #1e293b; line-height: 1.2; }
.kpi-sub { font-size: 12px; color: #94a3b8; display: flex; align-items: center; gap: 4px; margin-top: 2px; }

.pendientes-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
.pendiente-card { background: white; border-radius: 14px; padding: 16px 20px; border: 1px solid #f1f5f9; }
.pendiente-info { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.pendiente-label { font-size: 12px; color: #94a3b8; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
.pendiente-value { font-size: 22px; font-weight: 700; color: #1e293b; }

.empty-card { background: white; border-radius: 14px; padding: 40px 20px; border: 1px solid #f1f5f9; text-align: center; margin-bottom: 14px; }
.empty-card h3 { color: #64748b; font-size: 16px; font-weight: 600; margin: 14px 0 6px; }
.empty-card p { color: #94a3b8; font-size: 13px; }

.charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
.chart-card { background: white; border-radius: 14px; border: 1px solid #f1f5f9; overflow: hidden; }
.chart-header { padding: 14px 18px 0; }
.chart-header h3 { font-size: 14px; font-weight: 700; color: #1e293b; margin: 0; }
.chart-body { padding: 10px 14px 14px; height: 270px; }
.chart-empty { display: flex; align-items: center; justify-content: center; height: 100%; color: #94a3b8; font-size: 13px; }

/* Acciones recientes */
.acciones-body { padding: 8px 14px 14px; max-height: 320px; overflow-y: auto; }
.accion-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f8fafc; }
.accion-item:last-child { border-bottom: none; }
.accion-datos { flex: 1; display: flex; flex-direction: column; }
.accion-nombre { font-size: 13px; font-weight: 600; color: #1e293b; }
.accion-detalle { font-size: 11px; color: #94a3b8; }
.accion-fecha { font-size: 11px; color: #94a3b8; white-space: nowrap; }

/* Ranking */
.ranking-body { padding: 8px 14px 14px; max-height: 320px; overflow-y: auto; }
.ranking-item { display: flex; align-items: center; gap: 10px; padding: 8px 6px; border-radius: 8px; transition: background 0.2s; }
.ranking-item:hover { background: #f8fafc; }
.ranking-yo { background: #eff6ff; border: 1px solid #bfdbfe; }
.ranking-pos { width: 28px; text-align: center; }
.ranking-medal { font-size: 18px; font-weight: 800; }
.ranking-num { font-size: 13px; font-weight: 600; color: #94a3b8; }
.ranking-info { flex: 1; display: flex; flex-direction: column; }
.ranking-nombre { font-size: 13px; font-weight: 600; color: #1e293b; }
.ranking-detalle { font-size: 11px; color: #94a3b8; }
.ranking-total { min-width: 40px; text-align: right; }
.ranking-total-num { font-size: 18px; font-weight: 700; color: #1e293b; }

/* Pendientes por verificar */
.pendientes-body { padding: 8px 14px 14px; max-height: 280px; overflow-y: auto; }
.pendiente-item { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #f8fafc; }
.pendiente-item:last-child { border-bottom: none; }
.pendiente-datos { display: flex; flex-direction: column; }
.pendiente-nombre { font-size: 13px; font-weight: 600; color: #1e293b; }
.pendiente-detalle { font-size: 11px; color: #94a3b8; }
.pendiente-dni { font-size: 12px; color: #64748b; font-weight: 500; font-family: monospace; }

@media (max-width: 1024px) { .kpi-row { grid-template-columns: repeat(2, 1fr); } .charts-row, .pendientes-row { grid-template-columns: 1fr; } }
@media (max-width: 640px) { .kpi-row { grid-template-columns: 1fr; } }

:deep(.ant-progress-bg) { border-radius: 8px !important; }
</style>
