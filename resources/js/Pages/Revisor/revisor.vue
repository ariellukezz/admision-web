<template>
<Head title="Dashboard Revisor" />
<AuthenticatedLayout pagina="Dashboard">

<div class="dashboard-container">

  <!-- KPI CARDS -->
  <div class="kpi-row">
    <div class="kpi-card">
      <div class="kpi-icon" style="background: #dbeafe;">
        <UserAddOutlined style="color: #3b82f6;" />
      </div>
      <div class="kpi-info">
        <span class="kpi-label">Preinscritos</span>
        <span class="kpi-value">{{ resumen.preinscritos }}</span>
        <span class="kpi-sub">
          <ArrowUpOutlined style="color: #22c55e; font-size: 11px;" />
          <span style="color: #22c55e; font-weight: 600;">{{ resumen.preinscritos_hoy }}</span>
          <span style="color: #94a3b8;">hoy</span>
        </span>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon" style="background: #dcfce7;">
        <SolutionOutlined style="color: #22c55e;" />
      </div>
      <div class="kpi-info">
        <span class="kpi-label">Inscritos</span>
        <span class="kpi-value">{{ resumen.inscritos }}</span>
        <span class="kpi-sub">
          <ArrowUpOutlined style="color: #22c55e; font-size: 11px;" />
          <span style="color: #22c55e; font-weight: 600;">{{ resumen.inscritos_hoy }}</span>
          <span style="color: #94a3b8;">hoy</span>
        </span>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon" style="background: #fef3c7;">
        <SafetyOutlined style="color: #f59e0b;" />
      </div>
      <div class="kpi-info">
        <span class="kpi-label">Ctrl. Biométrico</span>
        <span class="kpi-value">{{ resumen.biometricos }}</span>
        <span class="kpi-sub">
          <span style="color: #3b82f6; font-weight: 600;">{{ biometrico.porcentaje }}%</span>
          <span style="color: #94a3b8;">del total</span>
        </span>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon" style="background: #fee2e2;">
        <FileProtectOutlined style="color: #ef4444;" />
      </div>
      <div class="kpi-info">
        <span class="kpi-label">Docs. por Verificar</span>
        <span class="kpi-value">{{ resumen.documentos_pendientes }}</span>
        <span class="kpi-sub">
          <CheckCircleOutlined style="color: #22c55e; font-size: 11px;" />
          <span style="color: #22c55e; font-weight: 600;">{{ resumen.documentos_verificados }}</span>
          <span style="color: #94a3b8;">verificados</span>
        </span>
      </div>
    </div>
  </div>

  <!-- SECONDARY KPI ROW -->
  <div class="kpi-row">
    <div class="kpi-card kpi-card-sm">
      <div class="kpi-info">
        <span class="kpi-label">Comprobantes Pendientes</span>
        <span class="kpi-value-sm">{{ resumen.comprobantes_pendientes }}</span>
      </div>
      <a-progress :percent="comprobantePercent" :show-info="false" stroke-color="#3b82f6" size="small" />
    </div>
    <div class="kpi-card kpi-card-sm">
      <div class="kpi-info">
        <span class="kpi-label">Comprobantes Verificados</span>
        <span class="kpi-value-sm">{{ resumen.comprobantes_verificados }}</span>
      </div>
      <a-progress :percent="comprobantePercent" :show-info="false" stroke-color="#22c55e" size="small" />
    </div>
    <div class="kpi-card kpi-card-sm">
      <div class="kpi-info">
        <span class="kpi-label">Biométrico Hoy</span>
        <span class="kpi-value-sm">{{ resumen.biometricos_hoy }}</span>
      </div>
    </div>
    <div class="kpi-card kpi-card-sm">
      <div class="kpi-info">
        <span class="kpi-label">Inscritos Hoy</span>
        <span class="kpi-value-sm">{{ resumen.inscritos_hoy }}</span>
      </div>
    </div>
  </div>

  <!-- BIOMETRICO PROGRESS -->
  <div class="bio-progress-card" v-if="biometrico.total_inscritos > 0">
    <div class="bio-header">
      <div>
        <h3 class="bio-title">Control Biométrico</h3>
        <p class="bio-subtitle">{{ biometrico.con_biometrico }} de {{ biometrico.total_inscritos }} inscritos registrados</p>
      </div>
      <div class="bio-badge">
        <SafetyOutlined />
        {{ biometrico.porcentaje }}%
      </div>
    </div>
    <a-progress :percent="biometrico.porcentaje" :stroke-color="{ '0%': '#3b82f6', '100%': '#8b5cf6' }" :show-info="false" stroke-linecap="round" size="large" />
    <div class="bio-stats">
      <div class="bio-stat"><CheckCircleOutlined style="color: #22c55e;" /><span>Registrados: <strong>{{ biometrico.con_biometrico }}</strong></span></div>
      <div class="bio-stat"><CloseCircleOutlined style="color: #ef4444;" /><span>Pendientes: <strong>{{ biometrico.sin_biometrico }}</strong></span></div>
    </div>
  </div>

  <!-- EMPTY STATE -->
  <div class="empty-card" v-if="resumen.inscritos === 0 && resumen.preinscritos === 0">
    <div class="empty-icon"><InboxOutlined style="font-size: 48px; color: #cbd5e1;" /></div>
    <h3>Sin datos para el proceso actual</h3>
    <p>Los gráficos se mostrarán cuando existan preinscripciones o inscripciones para este proceso.</p>
  </div>

  <!-- TIMELINE + GENERO POR AREA -->
  <div class="charts-row" v-if="resumen.inscritos > 0">
    <div class="chart-card chart-card-lg">
      <div class="chart-header"><h3>Inscripciones últimos 30 días</h3></div>
      <div class="chart-body">
        <Line v-if="timeline.length > 0" :data="timelineData" :options="timelineOptions" />
        <div v-else class="chart-empty">Sin inscripciones en los últimos 30 días</div>
      </div>
    </div>
    <div class="chart-card chart-card-md">
      <div class="chart-header"><h3>Género por Área</h3></div>
      <div class="chart-body">
        <Bar v-if="generoArea.length > 0" :data="generoAreaData" :options="barOptions" />
        <div v-else class="chart-empty">Sin datos de género</div>
      </div>
    </div>
  </div>

  <!-- AREA + MODALIDAD -->
  <div class="charts-row" v-if="resumen.inscritos > 0">
    <div class="chart-card chart-card-md">
      <div class="chart-header"><h3>Inscritos por Área</h3></div>
      <div class="chart-body">
        <Doughnut v-if="areas.length > 0" :data="areaData" :options="doughnutOptions" />
        <div v-else class="chart-empty">Sin datos de áreas</div>
      </div>
    </div>
    <div class="chart-card chart-card-md">
      <div class="chart-header"><h3>Distribución por Modalidad</h3></div>
      <div class="chart-body">
        <Doughnut v-if="modalidades.length > 0" :data="modalidadData" :options="doughnutOptions" />
        <div v-else class="chart-empty">Sin datos de modalidades</div>
      </div>
    </div>
  </div>

  <!-- TOP PROGRAMAS + BIOMETRICO POR AREA -->
  <div class="charts-row" v-if="resumen.inscritos > 0">
    <div class="chart-card chart-card-lg">
      <div class="chart-header"><h3>Top Programas con más Inscritos</h3></div>
      <div class="chart-body" style="height: 360px;">
        <Bar v-if="programas.length > 0" :data="programaData" :options="programaBarOptions" />
        <div v-else class="chart-empty">Sin datos de programas</div>
      </div>
    </div>
    <div class="chart-card chart-card-md">
      <div class="chart-header"><h3>Biométrico por Área</h3></div>
      <div class="chart-body">
        <Bar v-if="biometrico.por_area && biometrico.por_area.length > 0" :data="biometricoAreaData" :options="barOptions" />
        <div v-else class="chart-empty">Sin datos biométricos</div>
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
  UserAddOutlined, SolutionOutlined, SafetyOutlined,
  ArrowUpOutlined, CheckCircleOutlined, CloseCircleOutlined,
  InboxOutlined, FileProtectOutlined
} from '@ant-design/icons-vue'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, BarElement, CategoryScale, Title, LinearScale, PointElement, LineElement, Filler } from 'chart.js'
import { Pie, Bar, Line, Doughnut } from 'vue-chartjs'
import axios from 'axios'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, ArcElement, Tooltip, Legend, PointElement, LineElement, Filler)

const COLORS = { blue: '#3b82f6', green: '#22c55e', amber: '#f59e0b', purple: '#a855f7', red: '#ef4444', cyan: '#06b6d4', pink: '#ec4899', indigo: '#6366f1', orange: '#f97316', teal: '#14b8a6' }
const PALETTE = [COLORS.blue, COLORS.green, COLORS.amber, COLORS.purple, COLORS.red, COLORS.cyan, COLORS.pink, COLORS.indigo, COLORS.orange, COLORS.teal]

const resumen = ref({ inscritos: 0, inscritos_hoy: 0, preinscritos: 0, preinscritos_hoy: 0, biometricos: 0, biometricos_hoy: 0, documentos_pendientes: 0, documentos_verificados: 0, comprobantes_pendientes: 0, comprobantes_verificados: 0 })
const biometrico = ref({ total_inscritos: 0, con_biometrico: 0, sin_biometrico: 0, porcentaje: 0, por_area: [] })
const generoArea = ref([])
const areas = ref([])
const programas = ref([])
const timeline = ref([])
const modalidades = ref([])

const comprobantePercent = computed(() => {
  const total = resumen.value.comprobantes_pendientes + resumen.value.comprobantes_verificados
  return total > 0 ? Math.round((resumen.value.comprobantes_verificados / total) * 100) : 0
})

const areaData = computed(() => ({
  labels: areas.value.map(d => d.area || 'Sin área'),
  datasets: [{ data: areas.value.map(d => d.cant), backgroundColor: PALETTE.slice(0, areas.value.length), borderWidth: 0 }]
}))

const generoAreaData = computed(() => {
  const areaLabels = [...new Set(generoArea.value.map(d => d.area || 'Sin área'))].sort()
  const hombres = areaLabels.map(a => generoArea.value.find(d => (d.area || 'Sin área') === a && d.sexo === 'M')?.cant || 0)
  const mujeres = areaLabels.map(a => generoArea.value.find(d => (d.area || 'Sin área') === a && d.sexo === 'F')?.cant || 0)
  return { labels: areaLabels, datasets: [{ label: 'Varones', data: hombres, backgroundColor: COLORS.blue, borderRadius: 6 }, { label: 'Mujeres', data: mujeres, backgroundColor: COLORS.pink, borderRadius: 6 }] }
})

const programaData = computed(() => ({
  labels: programas.value.map(d => d.nombre?.length > 30 ? d.nombre.substring(0, 30) + '…' : d.nombre),
  datasets: [{ data: programas.value.map(d => d.cant), backgroundColor: programas.value.map(d => d.area === 'INGENIERÍAS' ? COLORS.blue : d.area === 'BIOMÉDICAS' ? COLORS.green : COLORS.purple), borderRadius: 6 }]
}))

const timelineData = computed(() => ({
  labels: timeline.value.map(d => d.fecha?.substring(5) || ''),
  datasets: [{ label: 'Inscritos', data: timeline.value.map(d => d.cant), borderColor: COLORS.blue, backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: COLORS.blue }]
}))

const modalidadData = computed(() => ({
  labels: modalidades.value.map(d => d.nombre || 'Sin modalidad'),
  datasets: [{ data: modalidades.value.map(d => d.cant), backgroundColor: PALETTE.slice(0, modalidades.value.length), borderWidth: 0 }]
}))

const biometricoAreaData = computed(() => ({
  labels: (biometrico.value.por_area || []).map(d => d.area || 'Sin área'),
  datasets: [{ data: (biometrico.value.por_area || []).map(d => d.cant), backgroundColor: PALETTE.slice(0, (biometrico.value.por_area || []).length), borderRadius: 6 }]
}))

const barOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
const programaBarOptions = { ...barOptions, indexAxis: 'y', plugins: { legend: { display: false } } }
const doughnutOptions = { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } } } }
const timelineOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }

const fetchAll = async () => {
  try {
    const [r1, r2, r3, r4, r5, r6, r7, r8] = await Promise.all([
      axios.get('/revisor/dashboard/resumen').catch(() => null),
      axios.get('/revisor/dashboard/biometrico-resumen').catch(() => null),
      axios.get('/revisor/dashboard/inscripciones-por-area').catch(() => null),
      axios.get('/revisor/dashboard/genero-por-area').catch(() => null),
      axios.get('/revisor/dashboard/inscritos-por-programa').catch(() => null),
      axios.get('/revisor/dashboard/timeline-inscripciones').catch(() => null),
      axios.get('/revisor/dashboard/modalidad-distribucion').catch(() => null),
      axios.get('/revisor/dashboard/verificaciones-pendientes').catch(() => null),
    ])
    if (r1?.data?.success) resumen.value = r1.data.datos
    if (r2?.data?.success) biometrico.value = r2.data.datos
    if (r3?.data?.success) areas.value = r3.data.datos
    if (r4?.data?.success) generoArea.value = r4.data.datos
    if (r5?.data?.success) programas.value = r5.data.datos
    if (r6?.data?.success) timeline.value = r6.data.datos
    if (r7?.data?.success) modalidades.value = r7.data.datos
  } catch (e) { console.error('Error cargando dashboard:', e) }
}

onMounted(() => { fetchAll() })
</script>

<style scoped>
.dashboard-container { padding: 16px; }

.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 14px; }
.kpi-card { background: white; border-radius: 14px; padding: 18px 20px; border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 14px; transition: box-shadow 0.2s; }
.kpi-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.05); }
.kpi-card-sm { flex-direction: column; align-items: flex-start; gap: 8px; }
.kpi-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.kpi-info { display: flex; flex-direction: column; width: 100%; }
.kpi-label { font-size: 12px; color: #94a3b8; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 26px; font-weight: 700; color: #1e293b; line-height: 1.2; }
.kpi-value-sm { font-size: 22px; font-weight: 700; color: #1e293b; line-height: 1.2; }
.kpi-sub { font-size: 12px; color: #94a3b8; display: flex; align-items: center; gap: 4px; margin-top: 2px; }

.bio-progress-card { background: white; border-radius: 14px; padding: 18px 20px; border: 1px solid #f1f5f9; margin-bottom: 14px; }
.bio-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.bio-title { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0; }
.bio-subtitle { font-size: 12px; color: #94a3b8; margin: 3px 0 0; }
.bio-badge { background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; padding: 5px 12px; border-radius: 18px; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 5px; }
.bio-stats { display: flex; gap: 20px; margin-top: 10px; }
.bio-stat { display: flex; align-items: center; gap: 5px; font-size: 12px; color: #64748b; }

.empty-card { background: white; border-radius: 14px; padding: 40px 20px; border: 1px solid #f1f5f9; text-align: center; margin-bottom: 14px; }
.empty-card h3 { color: #64748b; font-size: 16px; font-weight: 600; margin: 14px 0 6px; }
.empty-card p { color: #94a3b8; font-size: 13px; }

.charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
.chart-card { background: white; border-radius: 14px; border: 1px solid #f1f5f9; overflow: hidden; }
.chart-header { padding: 14px 18px 0; }
.chart-header h3 { font-size: 14px; font-weight: 700; color: #1e293b; margin: 0; }
.chart-body { padding: 10px 14px 14px; height: 270px; }
.chart-empty { display: flex; align-items: center; justify-content: center; height: 100%; color: #94a3b8; font-size: 13px; }

@media (max-width: 1024px) { .kpi-row { grid-template-columns: repeat(2, 1fr); } .charts-row { grid-template-columns: 1fr; } }
@media (max-width: 640px) { .kpi-row { grid-template-columns: 1fr; } }

:deep(.ant-progress-bg) { border-radius: 8px !important; }
</style>
