<!--
  ============================================================================
  Mi actividad — el parte personal del revisor.
  ----------------------------------------------------------------------------
  Se lee de dentro hacia fuera: primero lo que YO he hecho, después lo que
  tengo por delante, luego mi traza en el tiempo y, sólo al final, la
  comparación con el equipo. El ranking va abajo a propósito: informa, no debe
  presidir la pantalla de trabajo de nadie.
  ============================================================================
-->
<template>
  <Head title="Mi actividad" />
  <AuthenticatedLayout pagina="Mi actividad">
    <div class="act">

      <RevPageHeader
        title="Mi actividad"
        description="Resumen de tu trabajo de verificación en el proceso activo."
      >
        <template #actions>
          <RevButton variant="secondary" icon="refresh" :loading="loading" @click="fetchAll">Actualizar</RevButton>
        </template>
      </RevPageHeader>

      <!-- ── Lo que he verificado ───────────────────────────────────────── -->
      <section class="act-section">
        <div class="rev-divider-labeled">Verificado por mí</div>
        <div class="act-grid-4">
          <RevStat label="Documentos" :value="resumen.docs_verificados" :delta="resumen.docs_verificados_hoy" hint="hoy" icon="file-check" :loading="loading" />
          <RevStat label="Comprobantes" :value="resumen.comp_verificados" :delta="resumen.comp_verificados_hoy" hint="hoy" icon="credit-card" :loading="loading" />
          <RevStat label="Controles biométricos" :value="resumen.biometricos" :delta="resumen.biometricos_hoy" hint="hoy" icon="fingerprint" :loading="loading" />
          <RevStat label="Inscripciones procesadas" :value="resumen.inscripciones" :delta="resumen.inscripciones_hoy" hint="hoy" icon="user" :loading="loading" />
        </div>
      </section>

      <!-- ── Sin actividad ──────────────────────────────────────────────── -->
      <RevPanel v-if="sinActividad && !loading" flush>
        <RevEmptyState
          icon="activity"
          title="Aún sin actividad registrada"
          description="Tus estadísticas aparecerán en cuanto empieces a verificar documentos, comprobantes o a realizar controles biométricos."
        >
          <template #actions>
            <RevButton variant="primary" icon="inbox" href="/revisor/solicitudes-revision">Ir a solicitudes</RevButton>
          </template>
        </RevEmptyState>
      </RevPanel>

      <template v-else>
        <!-- ── Cola de trabajo ──────────────────────────────────────────── -->
        <section class="act-section">
          <div class="rev-divider-labeled">Cola de trabajo</div>
          <div class="act-grid-2">
            <RevPanel title="Documentos por verificar" :count="pendientes.docs_pendientes?.length || 0" flush>
              <template #actions>
                <span class="rev-meta">{{ resumen.total_docs_pendientes }} en total</span>
              </template>
              <ul v-if="pendientes.docs_pendientes?.length" class="act-queue">
                <li v-for="(doc, i) in pendientes.docs_pendientes" :key="'d' + i">
                  <span class="act-queue-mark"><RevIcon name="file" size="sm" /></span>
                  <div class="act-queue-copy">
                    <span class="act-queue-name">{{ doc.nombres }} {{ doc.paterno }}</span>
                    <span class="act-queue-detail">{{ doc.tipo_doc }} · {{ doc.programa }}</span>
                  </div>
                  <span class="act-queue-dni rev-mono">{{ doc.dni }}</span>
                </li>
              </ul>
              <RevEmptyState v-else compact variant="success" title="Nada pendiente" description="No hay documentos esperando tu verificación." />
            </RevPanel>

            <RevPanel title="Comprobantes por verificar" :count="pendientes.comps_pendientes?.length || 0" flush>
              <template #actions>
                <span class="rev-meta">{{ resumen.total_comp_pendientes }} en total</span>
              </template>
              <ul v-if="pendientes.comps_pendientes?.length" class="act-queue">
                <li v-for="(comp, i) in pendientes.comps_pendientes" :key="'c' + i">
                  <span class="act-queue-mark"><RevIcon name="credit-card" size="sm" /></span>
                  <div class="act-queue-copy">
                    <span class="act-queue-name">{{ comp.nombres }} {{ comp.paterno }}</span>
                    <span class="act-queue-detail">Op. {{ comp.nro_operacion }} · S/ {{ comp.monto }}</span>
                  </div>
                  <span class="act-queue-dni rev-mono">{{ comp.dni }}</span>
                </li>
              </ul>
              <RevEmptyState v-else compact variant="success" title="Nada pendiente" description="No hay comprobantes esperando tu verificación." />
            </RevPanel>
          </div>
        </section>

        <!-- ── Traza ───────────────────────────────────────────────────── -->
        <section class="act-section">
          <div class="rev-divider-labeled">Traza y últimas acciones</div>

          <div class="act-grid-2-1">
            <RevPanel title="Mi actividad" description="Últimos 30 días">
              <div class="act-chart" style="height: 250px">
                <Line v-if="timeline.length" :data="timelineData" :options="lineOptions" />
                <RevEmptyState v-else compact icon="chart" title="Sin actividad" description="No hay registros en los últimos 30 días." />
              </div>
            </RevPanel>

            <RevPanel title="Reparto de mi trabajo" description="Por tipo de verificación">
              <div v-if="distribucionRows.length" class="act-split">
                <div v-for="(d, i) in distribucionRows" :key="d.tipo" class="act-split-row">
                  <span class="act-split-label">
                    <i class="act-split-dot" :style="{ background: serie(i) }" />{{ d.tipo }}
                  </span>
                  <RevMeter :value="d.cant" :max="distribucionMax" tone="accent" size="sm" class="act-split-meter" />
                  <span class="act-split-num rev-num">{{ d.cant }}</span>
                  <span class="act-split-pct rev-num">{{ pct(d.cant) }}%</span>
                </div>
              </div>
              <RevEmptyState v-else compact icon="chart" title="Sin datos de actividad" />
            </RevPanel>
          </div>

          <RevPanel title="Acciones recientes" flush class="act-recent">
            <RevTimeline v-if="accionesTimeline.length" :items="accionesTimeline" class="act-timeline" />
            <RevEmptyState v-else compact title="Sin acciones recientes" description="Tus últimas verificaciones aparecerán aquí." />
          </RevPanel>
        </section>

        <!-- ── Equipo ──────────────────────────────────────────────────── -->
        <section v-if="ranking.length" class="act-section">
          <div class="rev-divider-labeled">Equipo de revisión</div>
          <RevPanel title="Ranking de revisores" description="Total de verificaciones en el proceso activo" flush>
            <ul class="act-rank">
              <li v-for="(rev, i) in ranking" :key="rev.id" :class="{ 'is-me': rev.es_yo }">
                <span class="act-rank-pos rev-num" :class="{ 'is-top': i < 3 }">{{ i + 1 }}</span>
                <div class="act-rank-copy">
                  <span class="act-rank-name">
                    {{ rev.nombre }}
                    <RevBadge v-if="rev.es_yo" tone="accent" size="sm">Tú</RevBadge>
                  </span>
                  <span class="act-rank-detail">
                    <span class="rev-num">{{ rev.docs }}</span> docs ·
                    <span class="rev-num">{{ rev.comps }}</span> comp ·
                    <span class="rev-num">{{ rev.bios }}</span> bio ·
                    <span class="rev-num">{{ rev.inscs }}</span> insc
                  </span>
                </div>
                <RevMeter :value="rev.total" :max="rankingMax" :tone="rev.es_yo ? 'accent' : 'neutral'" size="sm" class="act-rank-meter" />
                <span class="act-rank-total rev-num">{{ rev.total }}</span>
              </li>
            </ul>
          </RevPanel>
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
import { Line } from 'vue-chartjs'
import RevPageHeader from '@/Components/Revisor/RevPageHeader.vue'
import RevPanel from '@/Components/Revisor/RevPanel.vue'
import RevStat from '@/Components/Revisor/RevStat.vue'
import RevMeter from '@/Components/Revisor/RevMeter.vue'
import RevBadge from '@/Components/Revisor/RevBadge.vue'
import RevButton from '@/Components/Revisor/RevButton.vue'
import RevIcon from '@/Components/Revisor/RevIcon.vue'
import RevTimeline from '@/Components/Revisor/RevTimeline.vue'
import RevEmptyState from '@/Components/Revisor/RevEmptyState.vue'
import { REV_SERIES, revLine, revLineOptions } from '@/Components/Revisor/charts.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, ArcElement, Tooltip, Legend, PointElement, LineElement, Filler)

const loading = ref(true)
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

const sinActividad = computed(() =>
  resumen.value.docs_verificados === 0 && resumen.value.comp_verificados === 0 &&
  resumen.value.biometricos === 0 && resumen.value.inscripciones === 0
)

/* ── Reparto del trabajo: barras en lugar de anillo ──────────────────────
   Cuatro categorías en un anillo obligan a comparar arcos; en barras
   alineadas a un mismo origen la comparación es inmediata y honesta. */
const distribucionRows = computed(() => (distribucion.value || []).filter((d) => d.cant > 0))
const distribucionTotal = computed(() => distribucionRows.value.reduce((s, d) => s + Number(d.cant || 0), 0))
const distribucionMax = computed(() => Math.max(...distribucionRows.value.map((d) => Number(d.cant || 0)), 1))
const pct = (v) => (distribucionTotal.value > 0 ? Math.round((Number(v) / distribucionTotal.value) * 100) : 0)
const serie = (i) => REV_SERIES[i % REV_SERIES.length]

const rankingMax = computed(() => Math.max(...ranking.value.map((r) => Number(r.total || 0)), 1))

/* ── Línea temporal de tres series ─────────────────────────────────────── */
const timelineData = computed(() => ({
  labels: timeline.value.map((d) => (d.fecha || '').substring(5)),
  datasets: [
    revLine(timeline.value.map((d) => d.docs), REV_SERIES[0], 'Documentos'),
    revLine(timeline.value.map((d) => d.comps), REV_SERIES[1], 'Comprobantes'),
    revLine(timeline.value.map((d) => d.bios), REV_SERIES[2], 'Biométrico'),
  ],
}))
const lineOptions = {
  ...revLineOptions(),
  plugins: {
    ...revLineOptions().plugins,
    legend: {
      position: 'bottom', align: 'start',
      labels: {
        boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle',
        padding: 14, color: '#6B7787', font: { family: '"Inter", sans-serif', size: 11, weight: '500' },
      },
    },
  },
}

/* ── Acciones recientes como línea de tiempo con tono semántico ────────── */
const tonoPorTipo = {
  'Documento': 'info',
  'Comprobante': 'success',
  'Biométrico': 'warning',
  'Inscripción': 'neutral',
}
const iconoPorTipo = {
  'Documento': 'file-check',
  'Comprobante': 'credit-card',
  'Biométrico': 'fingerprint',
  'Inscripción': 'user',
}

const formatFecha = (fecha) => {
  if (!fecha) return ''
  const d = new Date(fecha)
  const diff = new Date() - d
  if (diff < 3600000) return `Hace ${Math.max(1, Math.floor(diff / 60000))} min`
  if (diff < 86400000) return `Hace ${Math.floor(diff / 3600000)} h`
  return d.toLocaleDateString('es-PE', { day: '2-digit', month: 'short' })
}

const accionesTimeline = computed(() => (acciones.value || []).map((a, i) => ({
  id: i,
  tone: tonoPorTipo[a.tipo] || 'neutral',
  icon: iconoPorTipo[a.tipo] || 'check',
  title: [a.nombres, a.paterno, a.materno].filter(Boolean).join(' '),
  detail: `${a.tipo} — ${a.detalle}`,
  time: formatFecha(a.fecha),
})))

/* ── Carga ──────────────────────────────────────────────────────────────── */
const fetchAll = async () => {
  loading.value = true
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
  } catch (e) {
    console.error('Error cargando mi actividad:', e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchAll)
</script>

<style scoped>
.act { display: flex; flex-direction: column; gap: var(--rev-s-8); }
.act-section { display: flex; flex-direction: column; gap: var(--rev-s-5); }
.act-grid-4   { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--rev-s-5); }
.act-grid-2   { display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--rev-s-5); }
.act-grid-2-1 { display: grid; grid-template-columns: 1.62fr 1fr; gap: var(--rev-s-5); }
.act-chart { position: relative; width: 100%; }

/* Cola de trabajo --------------------------------------------------------- */
.act-queue { list-style: none; margin: 0; padding: 0; max-height: 262px; overflow-y: auto; }
.act-queue li {
  display: flex; align-items: center; gap: var(--rev-s-5);
  padding: 8px var(--rev-s-6);
  border-bottom: 1px solid var(--rev-line-soft);
  transition: background var(--rev-t-fast) var(--rev-ease);
}
.act-queue li:hover { background: var(--rev-n-25); }
.act-queue li:last-child { border-bottom: 0; }
.act-queue-mark {
  flex: none; display: grid; place-items: center; width: 26px; height: 26px;
  border-radius: var(--rev-r-md); background: var(--rev-n-100); color: var(--rev-ink-3);
}
.act-queue-copy { display: flex; flex-direction: column; min-width: 0; flex: 1 1 auto; line-height: 1.3; }
.act-queue-name {
  font-size: var(--rev-fs-md); font-weight: 600; color: var(--rev-ink);
  text-transform: capitalize; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.act-queue-detail { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.act-queue-dni { font-size: var(--rev-fs-sm); color: var(--rev-ink-3); flex: none; }

/* Reparto ----------------------------------------------------------------- */
.act-split { display: flex; flex-direction: column; gap: var(--rev-s-5); }
.act-split-row { display: grid; grid-template-columns: 1fr 74px 34px 34px; align-items: center; gap: var(--rev-s-4); }
.act-split-label { display: inline-flex; align-items: center; gap: 7px; font-size: var(--rev-fs-md); color: var(--rev-ink-2); min-width: 0; }
.act-split-dot { width: 7px; height: 7px; border-radius: 50%; flex: none; }
.act-split-num { font-size: var(--rev-fs-md); font-weight: 680; color: var(--rev-ink); text-align: right; }
.act-split-pct { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); text-align: right; }

/* Acciones recientes ------------------------------------------------------ */
.act-recent :deep(.rev-panel-body) { padding: var(--rev-s-6); }
.act-timeline { max-height: 300px; overflow-y: auto; padding-right: 4px; }

/* Ranking ----------------------------------------------------------------- */
.act-rank { list-style: none; margin: 0; padding: 0; }
.act-rank li {
  display: grid; grid-template-columns: 30px 1fr 110px 48px;
  align-items: center; gap: var(--rev-s-5);
  padding: 9px var(--rev-s-6);
  border-bottom: 1px solid var(--rev-line-soft);
  transition: background var(--rev-t-fast) var(--rev-ease);
}
.act-rank li:last-child { border-bottom: 0; }
.act-rank li:hover { background: var(--rev-n-25); }
.act-rank li.is-me { background: var(--rev-primary-50); box-shadow: inset 2px 0 0 var(--rev-primary-600); }
.act-rank-pos {
  display: grid; place-items: center;
  width: 22px; height: 22px; border-radius: var(--rev-r-md);
  font-size: var(--rev-fs-sm); font-weight: 680;
  background: var(--rev-n-100); color: var(--rev-ink-3);
}
.act-rank-pos.is-top { background: var(--rev-primary-600); color: #fff; }
.act-rank-copy { display: flex; flex-direction: column; min-width: 0; line-height: 1.3; }
.act-rank-name {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: var(--rev-fs-md); font-weight: 600; color: var(--rev-ink);
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.act-rank-detail { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); }
.act-rank-total { font-size: var(--rev-fs-lg); font-weight: 680; color: var(--rev-ink); text-align: right; }

@media (max-width: 1280px) {
  .act-grid-4 { grid-template-columns: repeat(2, 1fr); }
  .act-grid-2-1 { grid-template-columns: 1fr; }
}
@media (max-width: 820px) {
  .act-grid-4, .act-grid-2 { grid-template-columns: 1fr; }
  .act-rank li { grid-template-columns: 26px 1fr 44px; }
  .act-rank-meter { display: none; }
}
</style>
