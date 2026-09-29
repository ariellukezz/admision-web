<!--
  ============================================================================
  Panel general del revisor.
  ----------------------------------------------------------------------------
  Un panel para quien revisa no es un escaparate de cifras: es un parte de
  situación. Por eso el orden no es «total de inscritos» primero, sino:
    1. Carga de trabajo pendiente (lo que exige acción hoy)
    2. Cobertura del proceso (cuánto falta para cerrar)
    3. Composición y tendencia (contexto, no urgencia)
  Las distribuciones largas se dibujan como barras horizontales de una sola
  tinta con valor al extremo: se comparan mejor que un anillo y evitan
  repartir siete colores sin significado.
  ============================================================================
-->
<template>
  <Head title="Panel del revisor" />
  <AuthenticatedLayout pagina="Panel general">
    <div class="dash">

      <RevPageHeader
        title="Panel general"
        description="Estado del proceso activo y carga de trabajo pendiente de verificación."
      >
        <template #actions>
          <RevButton variant="secondary" icon="refresh" :loading="loading" @click="fetchAll">Actualizar</RevButton>
        </template>
      </RevPageHeader>

      <!-- ── 1. Carga pendiente ─────────────────────────────────────────── -->
      <section class="dash-section">
        <div class="rev-divider-labeled">Pendiente de verificación</div>
        <div class="dash-grid-4">
          <RevStat
            label="Documentos por verificar"
            :value="resumen.documentos_pendientes"
            :loading="loading"
            icon="file-alert"
            :hint="`${fmt(resumen.documentos_verificados)} ya verificados`"
            href="/revisor/solicitudes-revision"
          />
          <RevStat
            label="Comprobantes por verificar"
            :value="resumen.comprobantes_pendientes"
            :loading="loading"
            icon="credit-card"
            :hint="`${fmt(resumen.comprobantes_verificados)} ya verificados`"
          />
          <RevStat
            label="Sin control biométrico"
            :value="biometrico.sin_biometrico"
            :loading="loading"
            icon="fingerprint"
            :hint="`de ${fmt(biometrico.total_inscritos)} inscritos`"
          />
          <RevStat
            label="Inscritos hoy"
            :value="resumen.inscritos_hoy"
            :loading="loading"
            icon="user"
            hint="registrados en la jornada"
          />
        </div>
      </section>

      <!-- ── 2. Cobertura ───────────────────────────────────────────────── -->
      <section class="dash-section">
        <div class="rev-divider-labeled">Cobertura del proceso</div>
        <div class="dash-grid-3">
          <RevPanel title="Control biométrico" :description="`${fmt(biometrico.con_biometrico)} de ${fmt(biometrico.total_inscritos)} inscritos registrados`">
            <template #actions>
              <RevBadge :tone="biometrico.porcentaje >= 90 ? 'success' : biometrico.porcentaje >= 60 ? 'warning' : 'danger'">
                {{ biometrico.porcentaje }}%
              </RevBadge>
            </template>
            <RevMeter
              size="lg"
              :value="biometrico.porcentaje"
              :tone="biometrico.porcentaje >= 90 ? 'success' : 'accent'"
            />
            <div class="dash-legend">
              <span class="dash-legend-item"><i class="dot is-ok" />Registrados <strong class="rev-num">{{ fmt(biometrico.con_biometrico) }}</strong></span>
              <span class="dash-legend-item"><i class="dot is-off" />Pendientes <strong class="rev-num">{{ fmt(biometrico.sin_biometrico) }}</strong></span>
            </div>
          </RevPanel>

          <RevPanel title="Comprobantes de pago" :description="`${comprobantePercent}% verificados`">
            <RevMeter
              size="lg"
              :value="resumen.comprobantes_verificados"
              :max="(resumen.comprobantes_verificados + resumen.comprobantes_pendientes) || 1"
              :tone="comprobantePercent >= 90 ? 'success' : 'accent'"
            />
            <div class="dash-legend">
              <span class="dash-legend-item"><i class="dot is-ok" />Verificados <strong class="rev-num">{{ fmt(resumen.comprobantes_verificados) }}</strong></span>
              <span class="dash-legend-item"><i class="dot is-off" />Pendientes <strong class="rev-num">{{ fmt(resumen.comprobantes_pendientes) }}</strong></span>
            </div>
          </RevPanel>

          <RevPanel title="Embudo de admisión" description="Del registro inicial a la inscripción formal">
            <div class="dash-funnel">
              <div class="dash-funnel-row">
                <span class="dash-funnel-label">Preinscritos</span>
                <RevMeter :value="resumen.preinscritos" :max="maxEmbudo" tone="neutral" />
                <span class="dash-funnel-num rev-num">{{ fmt(resumen.preinscritos) }}</span>
              </div>
              <div class="dash-funnel-row">
                <span class="dash-funnel-label">Inscritos</span>
                <RevMeter :value="resumen.inscritos" :max="maxEmbudo" tone="accent" />
                <span class="dash-funnel-num rev-num">{{ fmt(resumen.inscritos) }}</span>
              </div>
              <div class="dash-funnel-row">
                <span class="dash-funnel-label">Con biométrico</span>
                <RevMeter :value="resumen.biometricos" :max="maxEmbudo" tone="success" />
                <span class="dash-funnel-num rev-num">{{ fmt(resumen.biometricos) }}</span>
              </div>
            </div>
          </RevPanel>
        </div>
      </section>

      <!-- ── Sin datos ──────────────────────────────────────────────────── -->
      <RevPanel v-if="sinDatos && !loading" flush>
        <RevEmptyState
          title="Sin datos para el proceso activo"
          description="Los indicadores y gráficos aparecerán cuando existan preinscripciones o inscripciones registradas en este proceso."
        />
      </RevPanel>

      <!-- ── 3. Composición y tendencia ─────────────────────────────────── -->
      <template v-else-if="!sinDatos">
        <section class="dash-section">
          <div class="rev-divider-labeled">Composición y tendencia</div>

          <div class="dash-grid-2-1">
            <RevPanel title="Inscripciones" description="Últimos 30 días">
              <div class="dash-chart" style="height: 250px">
                <Line v-if="timeline.length" :data="timelineData" :options="lineOptions" />
                <RevEmptyState v-else compact icon="chart" title="Sin inscripciones" description="No hay registros en los últimos 30 días." />
              </div>
            </RevPanel>

            <RevPanel title="Distribución por sexo" description="Inscritos por área">
              <div class="dash-chart" style="height: 250px">
                <Bar v-if="generoArea.length" :data="generoAreaData" :options="stackedOptions" />
                <RevEmptyState v-else compact icon="chart" title="Sin datos" description="No hay información de sexo por área." />
              </div>
            </RevPanel>
          </div>

          <div class="dash-grid-2">
            <RevPanel title="Inscritos por área" :description="`${areas.length} áreas`">
              <div class="dash-chart" :style="{ height: barHeight(areas.length) }">
                <Bar v-if="areas.length" :data="areaData" :options="barHOptions" />
                <RevEmptyState v-else compact icon="chart" title="Sin datos de áreas" />
              </div>
            </RevPanel>

            <RevPanel title="Inscritos por modalidad" :description="`${modalidades.length} modalidades`">
              <div class="dash-chart" :style="{ height: barHeight(modalidades.length) }">
                <Bar v-if="modalidades.length" :data="modalidadData" :options="barHOptions" />
                <RevEmptyState v-else compact icon="chart" title="Sin datos de modalidades" />
              </div>
            </RevPanel>
          </div>

          <div class="dash-grid-2-1">
            <RevPanel title="Programas con más inscritos" description="Top 10">
              <div class="dash-chart" :style="{ height: barHeight(programasTop.length, 340) }">
                <Bar v-if="programasTop.length" :data="programaData" :options="barHOptions" />
                <RevEmptyState v-else compact icon="chart" title="Sin datos de programas" />
              </div>
            </RevPanel>

            <RevPanel title="Control biométrico por área">
              <div class="dash-chart" :style="{ height: barHeight((biometrico.por_area || []).length) }">
                <Bar v-if="(biometrico.por_area || []).length" :data="biometricoAreaData" :options="barHOptions" />
                <RevEmptyState v-else compact icon="chart" title="Sin datos biométricos" />
              </div>
            </RevPanel>
          </div>
        </section>
      </template>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import {
  Chart as ChartJS, ArcElement, Tooltip, Legend, BarElement, CategoryScale,
  Title, LinearScale, PointElement, LineElement, Filler,
} from 'chart.js'
import { Bar, Line } from 'vue-chartjs'
import RevPageHeader from '@/Components/Revisor/RevPageHeader.vue'
import RevPanel from '@/Components/Revisor/RevPanel.vue'
import RevStat from '@/Components/Revisor/RevStat.vue'
import RevMeter from '@/Components/Revisor/RevMeter.vue'
import RevBadge from '@/Components/Revisor/RevBadge.vue'
import RevButton from '@/Components/Revisor/RevButton.vue'
import RevEmptyState from '@/Components/Revisor/RevEmptyState.vue'
import {
  REV_SERIES, REV_SEMANTIC, revBar, revBarH, revLine,
  revBarOptions, revBarHOptions, revLineOptions, foldOther,
} from '@/Components/Revisor/charts.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, ArcElement, Tooltip, Legend, PointElement, LineElement, Filler)

const loading = ref(true)
const resumen = ref({
  inscritos: 0, inscritos_hoy: 0, preinscritos: 0, preinscritos_hoy: 0,
  biometricos: 0, biometricos_hoy: 0, documentos_pendientes: 0,
  documentos_verificados: 0, comprobantes_pendientes: 0, comprobantes_verificados: 0,
})
const biometrico = ref({ total_inscritos: 0, con_biometrico: 0, sin_biometrico: 0, porcentaje: 0, por_area: [] })
const generoArea = ref([])
const areas = ref([])
const programas = ref([])
const timeline = ref([])
const modalidades = ref([])

const fmt = (n) => Number(n || 0).toLocaleString('es-PE')

const comprobantePercent = computed(() => {
  const total = resumen.value.comprobantes_pendientes + resumen.value.comprobantes_verificados
  return total > 0 ? Math.round((resumen.value.comprobantes_verificados / total) * 100) : 0
})

const maxEmbudo = computed(() => Math.max(resumen.value.preinscritos, resumen.value.inscritos, resumen.value.biometricos, 1))
const sinDatos = computed(() => resumen.value.inscritos === 0 && resumen.value.preinscritos === 0)

/* Altura proporcional al número de barras: evita barras gordas con 3 categorías
   y apretujadas con 15. 26px por barra + margen del eje. */
const barHeight = (n, min = 200) => `${Math.max(min, (n || 1) * 26 + 56)}px`

const programasTop = computed(() => (programas.value || []).slice(0, 10))

/* ── Datos de los gráficos ─────────────────────────────────────────────── */
const truncar = (s, n = 34) => (s && s.length > n ? s.slice(0, n) + '…' : s || 'Sin dato')

const areaOrdenada = computed(() =>
  foldOther(
    [...areas.value].sort((a, b) => b.cant - a.cant).map((d) => ({ label: d.area || 'Sin área', value: d.cant })),
    9
  )
)
const areaData = computed(() => ({
  labels: areaOrdenada.value.map((d) => truncar(d.label)),
  datasets: [revBarH(areaOrdenada.value.map((d) => d.value), REV_SEMANTIC.accent, 'Inscritos')],
}))

const modalidadOrdenada = computed(() =>
  foldOther(
    [...modalidades.value].sort((a, b) => b.cant - a.cant).map((d) => ({ label: d.nombre || 'Sin modalidad', value: d.cant })),
    9
  )
)
const modalidadData = computed(() => ({
  labels: modalidadOrdenada.value.map((d) => truncar(d.label)),
  datasets: [revBarH(modalidadOrdenada.value.map((d) => d.value), REV_SERIES[0], 'Inscritos')],
}))

const programaData = computed(() => ({
  labels: programasTop.value.map((d) => truncar(d.nombre, 40)),
  datasets: [revBarH(programasTop.value.map((d) => d.cant), REV_SEMANTIC.accent, 'Inscritos')],
}))

const biometricoAreaData = computed(() => {
  const rows = (biometrico.value.por_area || []).map((d) => ({ label: d.area || 'Sin área', value: d.cant }))
  return {
    labels: rows.map((d) => truncar(d.label)),
    datasets: [revBarH(rows.map((d) => d.value), REV_SERIES[2], 'Con biométrico')],
  }
})

/* Dos series → leyenda obligatoria; dos slots categóricos validados. */
const generoAreaData = computed(() => {
  const labels = [...new Set(generoArea.value.map((d) => d.area || 'Sin área'))].sort()
  const get = (a, sexo) => generoArea.value.find((d) => (d.area || 'Sin área') === a && d.sexo === sexo)?.cant || 0
  return {
    labels: labels.map((a) => truncar(a, 18)),
    datasets: [
      revBar(labels.map((a) => get(a, 'M')), REV_SERIES[0], 'Varones'),
      revBar(labels.map((a) => get(a, 'F')), REV_SERIES[1], 'Mujeres'),
    ],
  }
})

const timelineData = computed(() => ({
  labels: timeline.value.map((d) => (d.fecha || '').substring(5)),
  datasets: [revLine(timeline.value.map((d) => d.cant), REV_SEMANTIC.accent, 'Inscritos')],
}))

const lineOptions = revLineOptions()
const barHOptions = revBarHOptions()
const stackedOptions = revBarOptions({ legend: true, stacked: true })

/* ── Carga ──────────────────────────────────────────────────────────────── */
const fetchAll = async () => {
  loading.value = true
  try {
    const [r1, r2, r3, r4, r5, r6, r7] = await Promise.all([
      axios.get('/revisor/dashboard/resumen').catch(() => null),
      axios.get('/revisor/dashboard/biometrico-resumen').catch(() => null),
      axios.get('/revisor/dashboard/inscripciones-por-area').catch(() => null),
      axios.get('/revisor/dashboard/genero-por-area').catch(() => null),
      axios.get('/revisor/dashboard/inscritos-por-programa').catch(() => null),
      axios.get('/revisor/dashboard/timeline-inscripciones').catch(() => null),
      axios.get('/revisor/dashboard/modalidad-distribucion').catch(() => null),
    ])
    if (r1?.data?.success) resumen.value = r1.data.datos
    if (r2?.data?.success) biometrico.value = r2.data.datos
    if (r3?.data?.success) areas.value = r3.data.datos
    if (r4?.data?.success) generoArea.value = r4.data.datos
    if (r5?.data?.success) programas.value = r5.data.datos
    if (r6?.data?.success) timeline.value = r6.data.datos
    if (r7?.data?.success) modalidades.value = r7.data.datos
  } catch (e) {
    console.error('Error cargando el panel:', e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchAll)
</script>

<style scoped>
.dash { display: flex; flex-direction: column; gap: var(--rev-s-8); }
.dash-section { display: flex; flex-direction: column; gap: var(--rev-s-5); }

.dash-grid-4   { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--rev-s-5); }
.dash-grid-3   { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--rev-s-5); }
.dash-grid-2   { display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--rev-s-5); }
.dash-grid-2-1 { display: grid; grid-template-columns: 1.62fr 1fr; gap: var(--rev-s-5); }
.dash-section > * + .dash-grid-2,
.dash-section > * + .dash-grid-2-1 { margin-top: 0; }
.dash-section .dash-grid-2, .dash-section .dash-grid-2-1 { margin-top: var(--rev-s-5); }
.dash-section .rev-divider-labeled + .dash-grid-4,
.dash-section .rev-divider-labeled + .dash-grid-3,
.dash-section .rev-divider-labeled + .dash-grid-2-1 { margin-top: 0; }

.dash-chart { position: relative; width: 100%; }

.dash-legend { display: flex; align-items: center; gap: var(--rev-s-6); margin-top: var(--rev-s-5); flex-wrap: wrap; }
.dash-legend-item { display: inline-flex; align-items: center; gap: 6px; font-size: var(--rev-fs-sm); color: var(--rev-ink-3); }
.dash-legend-item strong { color: var(--rev-ink); font-weight: 680; }
.dash-legend .dot { width: 7px; height: 7px; border-radius: 50%; flex: none; }
.dash-legend .dot.is-ok  { background: var(--rev-success); }
.dash-legend .dot.is-off { background: var(--rev-n-300); }

.dash-funnel { display: flex; flex-direction: column; gap: var(--rev-s-5); }
.dash-funnel-row { display: grid; grid-template-columns: 108px 1fr 62px; align-items: center; gap: var(--rev-s-5); }
.dash-funnel-label { font-size: var(--rev-fs-sm); color: var(--rev-ink-3); }
.dash-funnel-num { font-size: var(--rev-fs-md); font-weight: 680; color: var(--rev-ink); text-align: right; }

@media (max-width: 1280px) {
  .dash-grid-4 { grid-template-columns: repeat(2, 1fr); }
  .dash-grid-3 { grid-template-columns: 1fr; }
  .dash-grid-2-1 { grid-template-columns: 1fr; }
}
@media (max-width: 820px) {
  .dash-grid-4, .dash-grid-2 { grid-template-columns: 1fr; }
  .dash-funnel-row { grid-template-columns: 92px 1fr 54px; }
}
</style>
