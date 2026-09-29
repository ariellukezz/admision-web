<!--
  ============================================================================
  Solicitudes de revisión — la bandeja de entrada del revisor.
  ----------------------------------------------------------------------------
  Composición: nada de rejilla de tarjetas. Un encabezado editorial en el
  margen, pestañas subrayadas y la tabla a sangre de borde a borde. Las zonas
  se separan por filetes y por capas de papel, no por marcos.
  El estado se dice con un punto y una palabra; la antigüedad, con la franja
  izquierda de cada fila, porque es la prioridad real del puesto.
  ============================================================================
-->
<template>
  <Head title="Solicitudes de revisión" />
  <AuthenticatedLayout pagina="Solicitudes">
    <div class="sr">

      <!-- Encabezado ---------------------------------------------------- -->
      <header class="sr-head">
        <div class="sr-head-copy">
          <h1 class="sr-title">Solicitudes de revisión</h1>
          <p class="sr-lede">
            Atiende primero las más antiguas y las que ya han insistido.
            La franja de la izquierda de cada fila marca cuánto lleva esperando.
          </p>
        </div>

        <div class="sr-head-tools">
          <RevSearch v-model="busqueda" placeholder="Nombre o DNI" class="sr-search" @search="buscar" />
          <RevButton
            variant="secondary"
            icon="filter"
            :class="{ 'is-on': filtrosAbiertos || hayFiltros }"
            @click="filtrosAbiertos = !filtrosAbiertos"
          >Filtros<span v-if="nFiltros" class="sr-filter-count rev-num">{{ nFiltros }}</span></RevButton>
          <RevButton variant="ghost" icon="refresh" icon-only aria-label="Actualizar" :loading="reloading" @click="recargar" />
        </div>
      </header>

      <!-- Pestañas ------------------------------------------------------- -->
      <nav class="sr-tabs" aria-label="Estado de las solicitudes">
        <button
          v-for="opt in filtroOptions"
          :key="opt.value"
          type="button"
          class="sr-tab"
          :class="{ 'is-active': filtro === opt.value }"
          :aria-current="filtro === opt.value ? 'true' : undefined"
          @click="cambiarFiltro(opt.value)"
        >
          {{ opt.label }}
          <span v-if="filtro === opt.value && solicitudes.total" class="sr-tab-count rev-num">{{ solicitudes.total }}</span>
        </button>
      </nav>

      <!-- Filtros secundarios: ocultos hasta que hacen falta -------------- -->
      <transition name="rev-expand">
        <div v-if="filtrosAbiertos" class="sr-filters">
          <div class="sr-filters-inner">
            <label class="sr-filter">
              <span class="sr-filter-label">Modalidad</span>
              <a-select
                v-model:value="modalidadId"
                :options="modalidadOptions"
                style="min-width: 220px"
                placeholder="Todas"
                allow-clear
                @change="aplicarFiltros"
              />
            </label>

            <label class="sr-filter">
              <span class="sr-filter-label">Solicitada entre</span>
              <span class="sr-range">
                <a-date-picker v-model:value="desdeDay" format="YYYY-MM-DD" placeholder="Desde" @change="aplicarFiltros" />
                <RevIcon name="arrow-right" size="xs" class="sr-range-sep" />
                <a-date-picker v-model:value="hastaDay" format="YYYY-MM-DD" placeholder="Hasta" @change="aplicarFiltros" />
              </span>
            </label>

            <RevButton v-if="hayFiltros" variant="ghost" size="sm" icon="close" class="sr-filters-clear" @click="limpiarFiltros">
              Limpiar
            </RevButton>
          </div>
        </div>
      </transition>

      <!-- Tabla a sangre -------------------------------------------------- -->
      <div class="sr-bleed">
        <table class="sr-table">
          <thead>
            <tr>
              <th class="sr-th sr-th-lead">Postulante</th>
              <th class="sr-th" style="width: 180px">Modalidad</th>
              <th class="sr-th" style="width: 152px">Esperando</th>
              <th class="sr-th" style="width: 112px">Intentos</th>
              <th class="sr-th" style="width: 144px">Documentos</th>
              <th class="sr-th" style="width: 128px">Estado</th>
              <th class="sr-th sr-th-trail" style="width: 196px"><span class="rev-sr-only">Acciones</span></th>
            </tr>
          </thead>

          <tbody v-if="solicitudes.data?.length">
            <tr v-for="s in filas" :key="s.solicitud_id" class="sr-row" :class="{ 'is-active': s.enCurso }">
              <td class="sr-td sr-td-lead">
                <div class="sr-person" :style="{ borderLeftColor: s.urgenciaColor }">
                  <span class="sr-avatar">{{ s.iniciales }}</span>
                  <span class="sr-person-copy">
                    <span class="sr-person-name">{{ s.nombre_completo }}</span>
                    <span class="sr-person-dni rev-mono">{{ s.nro_doc }}</span>
                  </span>
                </div>
              </td>

              <td class="sr-td sr-modalidad">{{ s.modalidad || '—' }}</td>

              <td class="sr-td">
                <div class="sr-when" :class="s.urgenciaClase">{{ s.revision_solicitada_at_diff }}</div>
                <div class="sr-when-abs rev-mono">{{ s.revision_solicitada_at }}</div>
              </td>

              <td class="sr-td">
                <span class="rev-state" :class="{ 'is-warn': s.veces_revision_solicitada > 1 }">
                  {{ s.veces_revision_solicitada > 1 ? `${s.veces_revision_solicitada}ª vez` : '1ª vez' }}
                </span>
              </td>

              <td class="sr-td">
                <div class="sr-docs rev-mono">{{ s.documentos_verificados }}/{{ s.documentos_subidos }}</div>
                <div class="sr-ticks">
                  <span
                    v-for="i in Math.max(s.documentos_subidos, 1)"
                    :key="i"
                    class="sr-tick"
                    :class="{ 'is-done': i <= s.documentos_verificados }"
                  />
                </div>
              </td>

              <td class="sr-td">
                <span class="rev-state" :class="s.estadoClase">{{ s.estadoLabel }}</span>
              </td>

              <td class="sr-td sr-td-trail">
                <div class="sr-actions">
                  <RevButton
                    v-if="!s.revision_iniciada_at && !s.revision_finalizada_at"
                    variant="secondary"
                    size="sm"
                    icon="play"
                    :loading="iniciandoId === s.solicitud_id"
                    @click="iniciarRevision(s)"
                  >Iniciar</RevButton>

                  <RevButton
                    :as="Link"
                    :href="`/revisor/postulante/${s.nro_doc}?solicitud=${s.solicitud_id}`"
                    :variant="s.enCurso ? 'primary' : 'secondary'"
                    size="sm"
                  >{{ s.enCurso ? 'Continuar' : 'Ver documentos' }}</RevButton>
                </div>
              </td>
            </tr>
          </tbody>

          <tbody v-else>
            <tr>
              <td colspan="7" class="sr-td-empty">
                <RevEmptyState
                  v-if="hayFiltros || busqueda"
                  variant="filtered"
                  title="Sin resultados"
                  description="Ninguna solicitud coincide con los filtros aplicados. Prueba a ampliar el rango de fechas o a limpiar la búsqueda."
                >
                  <template #actions>
                    <RevButton variant="secondary" size="sm" icon="close" @click="limpiarFiltros">Limpiar filtros</RevButton>
                  </template>
                </RevEmptyState>

                <RevEmptyState
                  v-else-if="filtro === 'pendientes'"
                  variant="success"
                  icon="check-circle"
                  title="Bandeja vacía"
                  description="No queda ninguna solicitud por atender. Las nuevas aparecerán aquí en cuanto un postulante pida la revisión de sus documentos."
                />

                <RevEmptyState
                  v-else
                  title="Aún no hay solicitudes"
                  description="Cuando los postulantes soliciten la revisión de sus documentos, aparecerán en esta bandeja."
                />
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pie ------------------------------------------------------------ -->
        <div v-if="solicitudes.data?.length" class="sr-foot">
          <span class="sr-foot-meta">
            Mostrando <strong class="rev-num">{{ solicitudes.data.length }}</strong>
            de <strong class="rev-num">{{ solicitudes.total }}</strong>
          </span>
          <a-pagination
            v-if="solicitudes.last_page > 1"
            v-model:current="pagina"
            :total="solicitudes.total"
            :pageSize="solicitudes.per_page"
            :show-size-changer="false"
            show-less-items
            @change="cambiarPagina"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { message } from 'ant-design-vue'
import dayjs from 'dayjs'
import { useNotificaciones } from '@/composables/useFcm'
import RevButton from '@/Components/Revisor/RevButton.vue'
import RevSearch from '@/Components/Revisor/RevSearch.vue'
import RevIcon from '@/Components/Revisor/RevIcon.vue'
import RevEmptyState from '@/Components/Revisor/RevEmptyState.vue'

const props = defineProps({
  solicitudes: { type: Object, required: true },
  busqueda: { type: String, default: '' },
  filtro: { type: String, default: 'pendientes' },
  desde: { type: String, default: null },
  hasta: { type: String, default: null },
  modalidad_id: { type: [Number, String], default: null },
  modalidades: { type: Array, default: () => [] },
})

const busqueda = ref(props.busqueda)
const filtro = ref(props.filtro || 'pendientes')
const modalidadId = ref(props.modalidad_id || null)
const desdeDay = ref(props.desde ? dayjs(props.desde) : null)
const hastaDay = ref(props.hasta ? dayjs(props.hasta) : null)
const pagina = ref(props.solicitudes.current_page || 1)
const iniciandoId = ref(null)
const reloading = ref(false)
const filtrosAbiertos = ref(!!(props.desde || props.hasta || props.modalidad_id))
let fcmListener = null

const filtroOptions = [
  { label: 'Pendientes', value: 'pendientes' },
  { label: 'Atendidas', value: 'atendidas' },
  { label: 'Todas', value: 'todas' },
]

const modalidadOptions = computed(() => (props.modalidades || []).map((m) => ({ value: m.value, label: m.label })))
const nFiltros = computed(() => [desdeDay.value, hastaDay.value, modalidadId.value].filter(Boolean).length)
const hayFiltros = computed(() => nFiltros.value > 0 || !!busqueda.value)

/* ── Derivados de presentación ───────────────────────────────────────────
   La antigüedad es el criterio de prioridad del puesto, así que se codifica
   dos veces: en el color del texto y en la franja lateral de la fila.     */
const urgencia = (row) => {
  const txt = (row.revision_solicitada_at_diff || '').toLowerCase()
  if (/(mes|semana)/.test(txt)) return 'critical'
  if (/d[íi]a/.test(txt)) return 'warning'
  return 'fresh'
}

const filas = computed(() => (props.solicitudes.data || []).map((s) => {
  const u = urgencia(s)
  const enCurso = !!s.revision_iniciada_at && !s.revision_finalizada_at
  return {
    ...s,
    enCurso,
    iniciales: (s.nombre_completo || '').trim().split(/\s+/).slice(0, 2).map((w) => w.charAt(0)).join('').toUpperCase() || '—',
    urgenciaClase: `is-${u}`,
    urgenciaColor: u === 'critical' ? 'var(--rev-danger)'
      : u === 'warning' ? 'var(--rev-warning)'
      : 'transparent',
    estadoLabel: s.revision_finalizada_at ? 'Atendida' : enCurso ? 'En curso' : 'Pendiente',
    estadoClase: s.revision_finalizada_at ? 'is-ok' : enCurso ? 'is-live' : 'is-warn',
  }
}))

const iniciarRevision = async (s) => {
  if (iniciandoId.value) return
  iniciandoId.value = s.solicitud_id
  try {
    const res = await axios.post(`/revisor/iniciar-revision/${s.nro_doc}`, { solicitud_id: s.solicitud_id })
    if (res.data?.success !== false) {
      message.success('Revisión iniciada')
      router.reload({ only: ['solicitudes'], preserveScroll: true, preserveState: true })
    } else {
      message.error(res.data?.message || 'No se pudo iniciar la revisión')
    }
  } catch (e) {
    message.error(e.response?.data?.message || e.response?.data?.mensaje || 'No se pudo iniciar la revisión')
  } finally {
    iniciandoId.value = null
  }
}

const params = () => ({
  busqueda: busqueda.value,
  filtro: filtro.value,
  desde: desdeDay.value ? dayjs(desdeDay.value).format('YYYY-MM-DD') : null,
  hasta: hastaDay.value ? dayjs(hastaDay.value).format('YYYY-MM-DD') : null,
  modalidad_id: modalidadId.value || null,
})

const buscar = () => router.get('/revisor/solicitudes-revision', params(), { preserveState: true })

const cambiarFiltro = (value) => {
  if (filtro.value === value) return
  filtro.value = value
  pagina.value = 1
  router.get('/revisor/solicitudes-revision', { ...params(), filtro: value, page: 1 }, { preserveState: true })
}

const aplicarFiltros = () => {
  pagina.value = 1
  router.get('/revisor/solicitudes-revision', { ...params(), page: 1 }, { preserveState: true })
}

const limpiarFiltros = () => {
  desdeDay.value = null
  hastaDay.value = null
  modalidadId.value = null
  busqueda.value = ''
  pagina.value = 1
  router.get('/revisor/solicitudes-revision', { filtro: filtro.value, page: 1 }, { preserveState: true })
}

const cambiarPagina = (page) => router.get('/revisor/solicitudes-revision', { ...params(), page }, { preserveState: true })

const recargar = () => {
  reloading.value = true
  router.reload({
    only: ['solicitudes'],
    preserveScroll: true,
    preserveState: true,
    onFinish: () => { reloading.value = false },
  })
}

onMounted(() => {
  const fcm = useNotificaciones()
  fcmListener = () => router.reload({ only: ['solicitudes'], preserveScroll: true, preserveState: true })
  fcm.fcmEventTarget.addEventListener('fcm-message', fcmListener)
})

onUnmounted(() => {
  if (fcmListener) {
    const fcm = useNotificaciones()
    fcm.fcmEventTarget.removeEventListener('fcm-message', fcmListener)
  }
})
</script>

<style scoped>
.sr { display: flex; flex-direction: column; }

/* ── Encabezado editorial ───────────────────────────────────────────────── */
.sr-head {
  display: flex; align-items: flex-end; justify-content: space-between;
  gap: var(--rev-s-9); flex-wrap: wrap;
  padding: var(--rev-s-6) 0 var(--rev-s-8);
}
.sr-head-copy { min-width: 0; }
.sr-title {
  margin: 0;
  font-family: var(--rev-display);
  font-size: var(--rev-fs-display-lg);
  font-weight: 400;
  letter-spacing: -.018em;
  line-height: 1.04;
  color: var(--rev-ink);
}
.sr-lede {
  margin: 12px 0 0;
  max-width: 62ch;
  font-size: var(--rev-fs-md);
  line-height: 1.65;
  color: var(--rev-ink-3);
}
.sr-head-tools { display: flex; align-items: center; gap: var(--rev-s-4); flex: none; }
.sr-search { width: 232px; }
.sr-head-tools :deep(.is-on) {
  border-color: var(--rev-line-strong);
  background: var(--rev-surface-2);
  color: var(--rev-ink);
}
.sr-filter-count {
  margin-left: 6px; padding: 0 5px;
  border-radius: var(--rev-r-xs);
  background: var(--rev-surface-3);
  font-size: var(--rev-fs-2xs); font-weight: 650;
}

/* ── Pestañas subrayadas ────────────────────────────────────────────────── */
.sr-tabs {
  display: flex; align-items: center; gap: var(--rev-s-8);
  border-bottom: 1px solid var(--rev-line);
}
.sr-tab {
  position: relative;
  display: inline-flex; align-items: center; gap: 8px;
  padding: 0 0 14px;
  border: 0; background: transparent; cursor: pointer;
  font-family: var(--rev-font); font-size: var(--rev-fs-md); font-weight: 400;
  color: var(--rev-ink-3);
  transition: color var(--rev-t-fast) var(--rev-ease);
}
.sr-tab:hover { color: var(--rev-ink-2); }
.sr-tab:focus-visible { outline: none; box-shadow: var(--rev-ring); border-radius: var(--rev-r-sm); }
.sr-tab.is-active { color: var(--rev-ink); font-weight: 600; }
.sr-tab.is-active::after {
  content: ""; position: absolute; left: 0; right: 0; bottom: -1px;
  height: 2px; background: var(--rev-ink);
}
.sr-tab-count {
  padding: 1px 6px; border-radius: var(--rev-r-xs);
  background: var(--rev-surface-2); color: var(--rev-ink-2);
  font-size: var(--rev-fs-xs); font-weight: 500;
}

/* ── Filtros secundarios ────────────────────────────────────────────────── */
.sr-filters { overflow: hidden; border-bottom: 1px solid var(--rev-line); }
.sr-filters-inner {
  display: flex; align-items: flex-end; gap: var(--rev-s-7);
  flex-wrap: wrap;
  padding: var(--rev-s-6) 0;
}
.sr-filter { display: flex; flex-direction: column; gap: 6px; }
.sr-filter-label {
  font-size: var(--rev-fs-2xs); font-weight: 600;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-ink-3);
}
.sr-range { display: flex; align-items: center; gap: 8px; }
.sr-range-sep { color: var(--rev-ink-4); }
.sr-filters-clear { margin-bottom: 4px; }

/* ── Tabla a sangre: sale de los márgenes del lienzo ────────────────────── */
.sr-bleed {
  margin: 0 calc(-1 * var(--rev-gutter));
  overflow-x: auto;
}
.sr-table { width: 100%; min-width: 1120px; border-collapse: collapse; table-layout: fixed; }

.sr-th {
  position: sticky; top: 0; z-index: 2;
  text-align: left;
  padding: 16px var(--rev-s-6);
  background: var(--rev-surface);
  border-bottom: 1px solid var(--rev-line);
  font-size: var(--rev-fs-2xs); font-weight: 600;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-ink-3);
  white-space: nowrap;
}
.sr-th-lead  { padding-left: var(--rev-gutter); }
.sr-th-trail { padding-right: var(--rev-gutter); }

.sr-row { transition: background var(--rev-t-fast) var(--rev-ease); }
.sr-row:hover { background: var(--rev-n-25); }
.sr-row.is-active { background: var(--rev-surface-2); }

.sr-td {
  padding: 18px var(--rev-s-6);
  border-bottom: 1px solid var(--rev-line-soft);
  vertical-align: middle;
}
.sr-td-lead  { padding: 0; }
.sr-td-trail { padding-right: var(--rev-gutter); text-align: right; }

/* Persona: la franja lateral lleva la antigüedad */
.sr-person {
  display: flex; align-items: center; gap: var(--rev-s-5);
  padding: 18px var(--rev-s-6) 18px calc(var(--rev-gutter) - 3px);
  border-left: 3px solid transparent;
  min-width: 0;
}
.sr-avatar {
  flex: none; display: grid; place-items: center;
  width: 32px; height: 32px; border-radius: var(--rev-r-md);
  background: var(--rev-surface-2); color: var(--rev-ink-2);
  font-size: var(--rev-fs-2xs); font-weight: 600; letter-spacing: .02em;
}
.sr-person-copy { display: flex; flex-direction: column; min-width: 0; line-height: 1.35; }
.sr-person-name {
  font-size: var(--rev-fs-md); font-weight: 500; color: var(--rev-ink);
  text-transform: capitalize;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.sr-person-dni { font-size: var(--rev-fs-xs); color: var(--rev-ink-3); }

.sr-modalidad {
  font-size: var(--rev-fs-sm); color: var(--rev-ink-2);
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

/* Antigüedad */
.sr-when { font-size: var(--rev-fs-md); font-weight: 500; color: var(--rev-ink); }
.sr-when.is-warning  { color: var(--rev-warning); }
.sr-when.is-critical { color: var(--rev-danger); }
.sr-when-abs { font-size: var(--rev-fs-xs); color: var(--rev-ink-3); margin-top: 2px; }

/* Documentos: un tick por documento, como la banda de progreso de la ficha */
.sr-docs { font-size: var(--rev-fs-sm); color: var(--rev-ink); }
.sr-ticks { display: flex; gap: 2px; margin-top: 7px; }
.sr-tick {
  height: 3px; width: 9px; border-radius: 1px;
  background: var(--rev-line);
  transition: background var(--rev-t-base) var(--rev-ease);
}
.sr-tick.is-done { background: var(--rev-success); }

.sr-actions { display: inline-flex; align-items: center; gap: var(--rev-s-3); }

.sr-td-empty { padding: 0; border-bottom: 0; }

/* ── Pie ─────────────────────────────────────────────────────────────────── */
.sr-foot {
  display: flex; align-items: center; justify-content: space-between;
  gap: var(--rev-s-6);
  padding: 16px var(--rev-gutter);
  border-top: 1px solid var(--rev-line);
  background: var(--rev-surface-2);
}
.sr-foot-meta { font-size: var(--rev-fs-sm); color: var(--rev-ink-3); }
.sr-foot-meta strong { color: var(--rev-ink); font-weight: 600; }

@media (max-width: 900px) {
  .sr-head { align-items: stretch; }
  .sr-head-tools { width: 100%; }
  .sr-search { flex: 1 1 auto; width: auto; }
  .sr-title { font-size: var(--rev-fs-display); }
}
</style>
