<template>
<Head title="Dashboard" />
<AuthenticatedLayout>

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
        <span class="kpi-value">{{ biometrico.con_biometrico }}</span>
        <span class="kpi-sub">
          <span style="color: #3b82f6; font-weight: 600;">{{ biometrico.porcentaje }}%</span>
          <span style="color: #94a3b8;">del total</span>
        </span>
      </div>
    </div>

    <div class="kpi-card">
      <div class="kpi-icon" style="background: #f3e8ff;">
        <TeamOutlined style="color: #a855f7;" />
      </div>
      <div class="kpi-info">
        <span class="kpi-label">Ingresantes Total</span>
        <span class="kpi-value">{{ biometrico.total_ingresantes }}</span>
        <span class="kpi-sub" v-if="biometrico.fechas_ingreso?.length">
          <span style="color: #64748b;">{{ fechasIngresoCortas }}</span>
        </span>
      </div>
    </div>
  </div>

  <!-- PROGRESS BAR BIOMETRICO -->
  <div class="bio-progress-card" v-if="biometrico.total_ingresantes > 0">
    <div class="bio-header">
      <div>
        <h3 class="bio-title">Control Biométrico</h3>
        <p class="bio-subtitle">
          {{ biometrico.con_biometrico }} de {{ biometrico.total_ingresantes }} ingresantes registrados
        </p>
      </div>
      <div class="bio-badge">
        <SafetyOutlined />
        {{ biometrico.porcentaje }}%
      </div>
    </div>
    <a-progress
      :percent="biometrico.porcentaje"
      :stroke-color="{ '0%': '#3b82f6', '100%': '#8b5cf6' }"
      :show-info="false"
      stroke-linecap="round"
      size="large"
    />
    <div class="bio-stats">
      <div class="bio-stat">
        <CheckCircleOutlined style="color: #22c55e;" />
        <span>Registrados: <strong>{{ biometrico.con_biometrico }}</strong></span>
      </div>
      <div class="bio-stat">
        <CloseCircleOutlined style="color: #ef4444;" />
        <span>Pendientes: <strong>{{ biometrico.sin_biometrico }}</strong></span>
      </div>
    </div>
  </div>

  <!-- EMPTY STATE -->
  <div class="empty-card" v-if="resumen.inscritos === 0 && resumen.preinscritos === 0">
    <div class="empty-icon">
      <InboxOutlined style="font-size: 48px; color: #cbd5e1;" />
    </div>
    <h3>Sin datos para el proceso actual</h3>
    <p>Los gráficos se mostrarán cuando existan preinscripciones o inscripciones para este proceso.</p>
  </div>

  <!-- ROW: TIMELINE + GENERO POR AREA -->
  <div class="charts-row" v-if="resumen.inscritos > 0">
    <div class="chart-card chart-card-lg">
      <div class="chart-header">
        <h3>Inscripciones por fecha</h3>
      </div>
      <div class="chart-body">
        <Line v-if="timeline.length > 0" :data="timelineData" :options="timelineOpts" />
        <div v-else class="chart-empty">Sin inscripciones en el periodo del proceso</div>
      </div>
    </div>

    <div class="chart-card chart-card-md">
      <div class="chart-header">
        <h3>Inscripciones por Género y Área</h3>
      </div>
      <div class="chart-body">
        <Bar v-if="generoArea.length > 0" :data="generoAreaData" :options="barOpts" />
        <div v-else class="chart-empty">Sin datos de género</div>
      </div>
    </div>
  </div>

  <!-- ROW: POSTULANTES POR AREA + MODALIDAD -->
  <div class="charts-row" v-if="resumen.inscritos > 0">
    <div class="chart-card chart-card-md">
      <div class="chart-header">
        <h3>Postulantes por Área</h3>
      </div>
      <div class="chart-body">
        <Doughnut v-if="areas.length > 0" :data="areaData" :options="doughnutOpts" />
        <div v-else class="chart-empty">Sin datos de áreas</div>
      </div>
    </div>

    <div class="chart-card chart-card-md">
      <div class="chart-header">
        <h3>Distribución por Modalidad</h3>
      </div>
      <div class="chart-body">
        <Doughnut v-if="modalidades.length > 0" :data="modalidadData" :options="doughnutOpts" />
        <div v-else class="chart-empty">Sin datos de modalidades</div>
      </div>
    </div>
  </div>

  <!-- ROW: TOP PROGRAMAS + TIPO COLEGIO -->
  <div class="charts-row" v-if="resumen.inscritos > 0">
    <div class="chart-card chart-card-lg">
      <div class="chart-header">
        <h3>Top Programas con más Inscritos</h3>
      </div>
      <div class="chart-body" style="height: 360px;">
        <Bar v-if="programas.length > 0" :data="programaData" :options="programaBarOpts" />
        <div v-else class="chart-empty">Sin datos de programas</div>
      </div>
    </div>

    <div class="chart-card chart-card-md">
      <div class="chart-header">
        <h3>Tipo de Colegio</h3>
      </div>
      <div class="chart-body">
        <Pie v-if="tipoColegio.length > 0" :data="tipoColegioData" :options="pieOpts" />
        <div v-else class="chart-empty">Sin datos de colegios</div>
      </div>
    </div>
  </div>

  <!-- ROW: BIOMETRICO POR AREA + MEJORES INSCRIPTORES -->
  <div class="charts-row" v-if="resumen.inscritos > 0">
    <div class="chart-card chart-card-md">
      <div class="chart-header">
        <h3>Biométrico por Área</h3>
      </div>
      <div class="chart-body">
        <Bar v-if="biometrico.por_area && biometrico.por_area.length > 0" :data="biometricoAreaData" :options="barOpts" />
        <div v-else class="chart-empty">Sin datos biométricos</div>
      </div>
    </div>

    <div class="chart-card chart-card-md">
      <div class="chart-header">
        <h3>Mejores Inscriptores</h3>
      </div>
      <div class="inscriptores-list">
        <div v-for="(ins, index) in inscriptores" :key="index" class="inscriptor-item">
          <div class="inscriptor-avatar" :style="{ background: avatarColors[index % avatarColors.length] }">
            {{ ins.paterno ? ins.paterno[0].toUpperCase() : '?' }}
          </div>
          <div class="inscriptor-info">
            <span class="inscriptor-name">{{ ins.name }} {{ ins.paterno }}</span>
            <span class="inscriptor-count">{{ ins.cant }} inscritos</span>
          </div>
          <div class="inscriptor-bar-bg">
            <div class="inscriptor-bar-fill" :style="{ width: (ins.cant / maxInscriptores * 100) + '%' }"></div>
          </div>
        </div>
        <div v-if="inscriptores.length === 0" class="chart-empty">Sin datos</div>
      </div>
    </div>
  </div>

</div>

</AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import {
  UserAddOutlined, SolutionOutlined, TeamOutlined, SafetyOutlined,
  ArrowUpOutlined, CheckCircleOutlined, CloseCircleOutlined,
  InboxOutlined
} from '@ant-design/icons-vue'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, BarElement, CategoryScale, Title, LinearScale, PointElement, LineElement, Filler } from 'chart.js'
import { Pie, Bar, Line, Doughnut } from 'vue-chartjs'
import axios from 'axios'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, ArcElement, Tooltip, Legend, PointElement, LineElement, Filler)

const COLORS = {
  blue: '#3b82f6', green: '#22c55e', amber: '#f59e0b', purple: '#a855f7',
  red: '#ef4444', cyan: '#06b6d4', pink: '#ec4899', indigo: '#6366f1',
  orange: '#f97316', teal: '#14b8a6'
}
const PALETTE = [COLORS.blue, COLORS.green, COLORS.amber, COLORS.purple, COLORS.red, COLORS.cyan, COLORS.pink, COLORS.indigo, COLORS.orange, COLORS.teal]
const avatarColors = ['#dbeafe', '#dcfce7', '#fef3c7', '#f3e8ff', '#ffe4e6', '#e0f2fe', '#fce7f3', '#ede9fe']

const datalabelPlugin = {
  id: 'customDatalabel',
  afterDatasetDraw(chart) {
    const { ctx } = chart
    const total = chart.data.datasets.reduce((sum, ds) => sum + ds.data.reduce((a, b) => a + (b || 0), 0), 0)
    const meta = chart.getDatasetMeta(chart._drawDatasetIndex ?? 0)
    const dataset = chart.data.datasets[meta.index ?? 0]
    if (!dataset || meta.hidden) return
    meta.data.forEach((element, index) => {
      const value = dataset.data[index]
      if (value == null) return
      ctx.save()
      ctx.font = 'bold 11px Inter, sans-serif'
      ctx.fillStyle = '#475569'
      ctx.textAlign = 'center'
      ctx.textBaseline = 'bottom'
      const chartType = chart.config.type
      if (chartType === 'bar') {
        if (chart.options.indexAxis === 'y') {
          ctx.textAlign = 'left'
          ctx.textBaseline = 'middle'
          ctx.fillText(value, element.x + 6, element.y)
        } else {
          ctx.fillText(value, element.x, element.y - 4)
        }
      } else if (chartType === 'line') {
        ctx.fillText(value, element.x, element.y - 8)
      } else if (chartType === 'doughnut' || chartType === 'pie') {
        const meta2 = element.getProps(['startAngle', 'endAngle', 'innerRadius', 'outerRadius', 'x', 'y'])
        const midAngle = (meta2.startAngle + meta2.endAngle) / 2
        const radius = (meta2.innerRadius + meta2.outerRadius) / 2
        const x = meta2.x + Math.cos(midAngle) * radius
        const y = meta2.y + Math.sin(midAngle) * radius
        ctx.textBaseline = 'middle'
        ctx.fillStyle = '#ffffff'
        ctx.font = 'bold 12px Inter, sans-serif'
        const pct = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : value
        ctx.fillText(pct, x, y)
      }
      ctx.restore()
    })
  }
}

ChartJS.register(datalabelPlugin)

const resumen = ref({ preinscritos: 0, inscritos: 0, biometricos: 0, postulantes: 0, preinscritos_hoy: 0, inscritos_hoy: 0, biometricos_hoy: 0 })
const biometrico = ref({ total_ingresantes: 0, con_biometrico: 0, sin_biometrico: 0, porcentaje: 0, por_area: [], fechas_ingreso: [] })
const generoArea = ref([])
const areas = ref([])
const programas = ref([])
const timeline = ref([])
const modalidades = ref([])
const tipoColegio = ref([])
const inscriptores = ref([])

const maxInscriptores = computed(() => {
  if (inscriptores.value.length === 0) return 1
  return Math.max(...inscriptores.value.map(i => i.cant))
})

const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic']

const fechasIngresoCortas = computed(() => {
  const fechas = biometrico.value.fechas_ingreso || []
  if (!fechas.length) return '—'
  const parse = f => { const p = f.split('-'); return { d: +p[2], m: +p[1], y: p[0] } }
  const parsed = fechas.map(parse)
  const meses = [...new Set(parsed.map(f => f.m))]
  const years = [...new Set(parsed.map(f => f.y))]
  if (meses.length > 1) {
    const first = parsed[0]
    return `${first.d} ${MESES[first.m - 1]} ${first.y} y más...`
  }
  const dias = parsed.map(f => f.d)
  let txt = dias.slice(0, 3).join(', ')
  if (dias.length > 3) txt += '...'
  txt += ` ${MESES[meses[0] - 1]} ${years[0]}`
  return txt
})

// ─── DATA COMPUTED ────────────────────────────────────────────────
const areaData = computed(() => ({
  labels: areas.value.map(d => d.area || 'Sin área'),
  datasets: [{ data: areas.value.map(d => d.cant), backgroundColor: PALETTE.slice(0, areas.value.length), borderWidth: 0 }]
}))

const generoAreaData = computed(() => {
  const areaLabels = [...new Set(generoArea.value.map(d => d.area || 'Sin área'))].sort()
  const hombres = areaLabels.map(a => generoArea.value.find(d => (d.area || 'Sin área') === a && d.sexo == 1)?.cant || 0)
  const mujeres = areaLabels.map(a => generoArea.value.find(d => (d.area || 'Sin área') === a && d.sexo == 2)?.cant || 0)
  return {
    labels: areaLabels,
    datasets: [
      { label: 'Varones', data: hombres, backgroundColor: COLORS.blue, borderRadius: 6 },
      { label: 'Mujeres', data: mujeres, backgroundColor: COLORS.pink, borderRadius: 6 },
    ]
  }
})

const programaData = computed(() => ({
  labels: programas.value.map(d => d.nombre?.length > 30 ? d.nombre.substring(0, 30) + '…' : d.nombre),
  datasets: [{
    data: programas.value.map(d => d.cant),
    backgroundColor: programas.value.map(d => d.area === 'INGENIERÍAS' ? COLORS.blue : d.area === 'BIOMÉDICAS' ? COLORS.green : COLORS.purple),
    borderRadius: 6,
  }]
}))

const timelineData = computed(() => ({
  labels: timeline.value.map(d => {
    if (!d.fecha) return ''
    const parts = d.fecha.split('-')
    return `${parts[2]}/${parts[1]}`
  }),
  datasets: [{
    label: 'Inscritos',
    data: timeline.value.map(d => d.cant),
    borderColor: COLORS.blue,
    backgroundColor: 'rgba(59,130,246,0.1)',
    fill: true,
    tension: 0.4,
    pointRadius: 3,
    pointBackgroundColor: COLORS.blue,
  }]
}))

const modalidadData = computed(() => ({
  labels: modalidades.value.map(d => d.nombre || 'Sin modalidad'),
  datasets: [{ data: modalidades.value.map(d => d.cant), backgroundColor: PALETTE.slice(0, modalidades.value.length), borderWidth: 0 }]
}))

const tipoColegioData = computed(() => ({
  labels: tipoColegio.value.map(d => d.tipo || 'Sin tipo'),
  datasets: [{
    data: tipoColegio.value.map(d => d.cant),
    backgroundColor: [COLORS.blue, COLORS.amber, COLORS.green, COLORS.purple],
    borderWidth: 0
  }]
}))

const biometricoAreaData = computed(() => {
  const areas = (biometrico.value.por_area || [])
  return {
    labels: areas.map(d => d.area || 'Sin área'),
    datasets: [
      { label: 'Total Ingresantes', data: areas.map(d => d.ingresantes), backgroundColor: COLORS.amber, borderRadius: 6 },
      { label: 'Con Biométrico', data: areas.map(d => d.biometrico), backgroundColor: COLORS.blue, borderRadius: 6 },
      { label: 'Sin Biométrico', data: areas.map(d => d.ingresantes - d.biometrico), backgroundColor: COLORS.red, borderRadius: 6 },
    ]
  }
})

// ─── CHART OPTIONS ────────────────────────────────────────────────
const baseScales = {
  y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
  x: { grid: { display: false } }
}

const barOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'top', labels: { padding: 12, usePointStyle: true, boxWidth: 8 } } }, scales: { ...baseScales } }
const programaBarOpts = { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } } }
const doughnutOpts = { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } } } }
const pieOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } } } }
const timelineOpts = computed(() => {
  const maxVal = timeline.value.length ? Math.max(...timeline.value.map(d => d.cant)) : 0
  const padding = maxVal < 500 ? 50 : Math.ceil(maxVal * 0.08 / 500) * 500
  return { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, suggestedMax: maxVal + padding, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
})

// ─── FETCH DATA ───────────────────────────────────────────────────
const fetchAll = async () => {
  try {
    const promises = [
      axios.get('/admin/dashboard/resumen-general').catch(() => null),
      axios.get('/admin/dashboard/biometrico-resumen').catch(() => null),
      axios.get('/admin/dashboard/postulantes-por-area').catch(() => null),
      axios.get('/admin/dashboard/genero-por-area').catch(() => null),
      axios.get('/admin/dashboard/inscritos-por-programa').catch(() => null),
      axios.get('/admin/dashboard/timeline-inscripciones').catch(() => null),
      axios.get('/admin/dashboard/modalidad-distribucion').catch(() => null),
      axios.get('/admin/dashboard/tipo-colegio-distribucion').catch(() => null),
      axios.get('/admin/get-mejores-inscriptores').catch(() => null),
    ]

    const [r1, r2, r3, r4, r5, r6, r7, r8, r9] = await Promise.all(promises)

    if (r1?.data?.success) resumen.value = r1.data.datos
    if (r2?.data?.success) biometrico.value = r2.data.datos
    if (r3?.data?.success) areas.value = r3.data.datos
    if (r4?.data?.success) generoArea.value = r4.data.datos
    if (r5?.data?.success) programas.value = r5.data.datos
    if (r6?.data?.success) timeline.value = r6.data.datos
    if (r7?.data?.success) modalidades.value = r7.data.datos
    if (r8?.data?.success) tipoColegio.value = r8.data.datos
    if (r9?.data?.estado) inscriptores.value = r9.data.inscriptores || []
  } catch (e) {
    console.error('Error cargando dashboard:', e)
  }
}

onMounted(() => { fetchAll() })
</script>

<style scoped>
.dashboard-container { padding: 4px 8px 24px; }

.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 16px; }
.kpi-card { background: white; border-radius: 16px; padding: 20px 24px; border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 16px; transition: box-shadow 0.2s; }
.kpi-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.kpi-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
.kpi-info { display: flex; flex-direction: column; }
.kpi-label { font-size: 13px; color: #94a3b8; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 28px; font-weight: 700; color: #1e293b; line-height: 1.2; }
.kpi-sub { font-size: 12px; color: #94a3b8; display: flex; align-items: center; gap: 4px; margin-top: 2px; }

.bio-progress-card { background: white; border-radius: 16px; padding: 20px 24px; border: 1px solid #f1f5f9; margin-bottom: 16px; }
.bio-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.bio-title { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0; }
.bio-subtitle { font-size: 13px; color: #94a3b8; margin: 4px 0 0; }
.bio-badge { background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; padding: 6px 14px; border-radius: 20px; font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 6px; }
.bio-stats { display: flex; gap: 24px; margin-top: 12px; }
.bio-stat { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #64748b; }

.empty-card { background: white; border-radius: 16px; padding: 48px 24px; border: 1px solid #f1f5f9; text-align: center; margin-bottom: 16px; }
.empty-card h3 { color: #64748b; font-size: 18px; font-weight: 600; margin: 16px 0 8px; }
.empty-card p { color: #94a3b8; font-size: 14px; }

.charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.chart-card { background: white; border-radius: 16px; border: 1px solid #f1f5f9; overflow: hidden; }
.chart-header { padding: 16px 20px 0; }
.chart-header h3 { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0; }
.chart-body { padding: 12px 16px 16px; height: 280px; }

.chart-empty { display: flex; align-items: center; justify-content: center; height: 100%; color: #94a3b8; font-size: 13px; text-align: center; }

.inscriptores-list { padding: 12px 20px 16px; }
.inscriptor-item { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.inscriptor-avatar { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 15px; color: #475569; flex-shrink: 0; }
.inscriptor-info { display: flex; flex-direction: column; min-width: 100px; }
.inscriptor-name { font-size: 13px; font-weight: 600; color: #1e293b; }
.inscriptor-count { font-size: 11px; color: #94a3b8; }
.inscriptor-bar-bg { flex: 1; height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden; }
.inscriptor-bar-fill { height: 100%; background: linear-gradient(90deg, #3b82f6, #8b5cf6); border-radius: 3px; transition: width 0.6s ease; }

@media (max-width: 1024px) { .kpi-row { grid-template-columns: repeat(2, 1fr); } .charts-row { grid-template-columns: 1fr; } }
@media (max-width: 640px) { .kpi-row { grid-template-columns: 1fr; } }

:deep(.ant-progress-bg) { border-radius: 10px !important; }
</style>
