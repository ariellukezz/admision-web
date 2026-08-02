<template>
  <a-modal v-model:open="visible" :title="cargoData?.cargo || 'Cargo'" :footer="null" width="1100px" @cancel="cerrar">
    <div v-if="cargoData" class="flex flex-col gap-4">

      <!-- Info del sorteo + contadores -->
      <div class="flex flex-wrap items-center justify-between gap-3 p-3 rounded-lg" style="background: var(--table-header-bg, #f8fafc); border: 1px solid var(--card-border, #e2e8f0);">
        <div>
          <div class="text-sm font-semibold">Sorteo: {{ sorteoLabel }}</div>
          <div v-if="sorteoTiposLabel" class="text-xs" style="color: var(--card-muted, #64748b);">Tipos: {{ sorteoTiposLabel }}</div>
        </div>
        <div class="flex gap-4">
          <div class="text-center">
            <div class="text-2xl font-bold" style="color: #16a34a;">{{ cargoData.asignados }}</div>
            <div class="text-xs" style="color: var(--card-muted, #64748b);">Positivos</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold" style="color: #ef4444;">{{ cargoModalAnulados }}</div>
            <div class="text-xs" style="color: var(--card-muted, #64748b);">Negativos</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold" style="color: var(--primary-color, #3b82f6);">{{ cargoData.disponibles }}</div>
            <div class="text-xs" style="color: var(--card-muted, #64748b);">Disponibles</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold">{{ cargoData.cantidad }}</div>
            <div class="text-xs" style="color: var(--card-muted, #64748b);">Total</div>
          </div>
        </div>
      </div>

      <!-- Sección de búsqueda + Card Premium -->
      <div class="flex gap-4 items-start">
        <!-- Buscador compacto -->
        <div class="flex-shrink-0" style="min-width: 300px; max-width: 340px;">
          <div class="flex items-center justify-between mb-2">
            <label class="text-sm font-medium">Buscar (DNI o nombres)</label>
            <a-checkbox v-model:checked="autoAsignar">Auto</a-checkbox>
          </div>
          <div class="flex gap-2 mb-2">
            <a-input
              v-model:value="terminoBuscar"
              placeholder="DNI o apellidos/nombres"
              :disabled="cargoData.disponibles === 0 && esSorteo"
              @pressEnter="buscarParticipante"
            />
            <a-button
              type="primary"
              style="background: #2563eb; border: none; border-radius: 6px;"
              :loading="buscando"
              @click="buscarParticipante"
            >
              <template #icon><SearchOutlined /></template>
            </a-button>
          </div>

          <!-- Checkbox Sorteo -->
          <div class="flex items-center gap-4 mb-3">
            <a-checkbox v-model:checked="esSorteo">Sorteo</a-checkbox>
            <span class="text-xs" style="color: var(--card-muted, #64748b);">
              {{ esSorteo ? 'Respeta cupos' : 'Designación (ignora cupos)' }}
            </span>
          </div>

          <div v-if="cargoData.disponibles === 0 && esSorteo" class="mb-3">
            <a-alert message="Ya no hay espacio, cupos vacantes no disponibles para este cargo." type="error" show-icon :closable="false" />
          </div>
          <div v-else class="text-xs mb-3" style="color: var(--card-muted, #64748b);">
            Cupos: <strong>{{ cargoData.disponibles }}</strong> disponibles de {{ cargoData.cantidad }}
          </div>

          <a-button
            v-if="resultadoBusqueda && !yaSeleccionado && !autoAsignar"
            type="primary"
            block
            size="large"
            :style="esSorteo ? 'background: #16a34a; border: none; border-radius: 6px;' : 'background: #7c3aed; border: none; border-radius: 6px;'"
            @click="registrarSeleccion"
          >
            <template #icon><PlusOutlined /></template>
            {{ fueAnulado ? 'Reasignar' : 'Asignar' }}
          </a-button>
        </div>

        <!-- Card Participante -->
        <div class="flex-1 min-w-0">
          <!-- Resultado único -->
          <div v-if="resultadoBusqueda" class="participant-card">
            <div class="participant-card__body">
              <div class="participant-card__photo-wrap">
                <img
                  v-if="resultadoBusqueda.foto"
                  :src="baseUrl + resultadoBusqueda.foto"
                  alt="Foto"
                  class="participant-card__photo"
                />
                <div v-else class="participant-card__photo participant-card__photo--placeholder">
                  <UserOutlined style="font-size: 38px; color: #cbd5e1;" />
                </div>
                <span class="participant-card__dot" :class="{ 'participant-card__dot--active': yaSeleccionado, 'participant-card__dot--warn': fueAnulado, 'participant-card__dot--danger': participanteEstaObservado }" />
              </div>
              <div class="participant-card__info">
                <h3 class="participant-card__name">{{ resultadoBusqueda.paterno }} {{ resultadoBusqueda.materno }}, {{ resultadoBusqueda.nombres }}</h3>
                <div class="participant-card__details">
                  <div class="participant-card__detail-item">
                    <span class="participant-card__detail-label">DNI</span>
                    <span class="participant-card__detail-value">{{ resultadoBusqueda.dni }}</span>
                  </div>
                  <div v-if="resultadoBusqueda.tipo_personal" class="participant-card__detail-item">
                    <span class="participant-card__detail-label">Tipo</span>
                    <span class="participant-card__detail-value">{{ resultadoBusqueda.tipo_personal }}</span>
                  </div>
                  <div v-if="resultadoBusqueda.condicion" class="participant-card__detail-item">
                    <span class="participant-card__detail-label">Condición</span>
                    <span class="participant-card__detail-value">{{ resultadoBusqueda.condicion }}</span>
                  </div>
                  <div v-if="resultadoBusqueda.dependencia" class="participant-card__detail-item">
                    <span class="participant-card__detail-label">Dependencia</span>
                    <span class="participant-card__detail-value">{{ resultadoBusqueda.dependencia }}</span>
                  </div>
                </div>
                <div v-if="yaSeleccionado || fueAnulado || participanteEstaObservado" class="participant-card__statuses">
                  <span v-if="yaSeleccionado && cargoAsignado" class="participant-card__status participant-card__status--success">
                    <CheckCircleOutlined /> {{ cargoAsignado }}
                  </span>
                  <span v-if="fueAnulado" class="participant-card__status participant-card__status--warning">
                    <StopOutlined /> Anulado
                  </span>
                  <span v-if="participanteEstaObservado" class="participant-card__status participant-card__status--danger">
                    <EyeOutlined /> Observado
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Múltiples resultados -->
          <div v-if="resultadosMultiples.length" class="mt-2">
            <div class="text-xs mb-2" style="color: var(--card-muted, #64748b);">Se encontraron {{ resultadosMultiples.length }} participantes. Seleccione uno:</div>
            <div class="multi-result-list">
              <div
                v-for="p in resultadosMultiples"
                :key="p.id"
                class="multi-result-item"
                @click="seleccionarResultado(p)"
              >
                <img v-if="p.foto" :src="baseUrl + p.foto" class="multi-result-foto" />
                <div v-else class="multi-result-foto multi-result-foto--placeholder"><UserOutlined /></div>
                <div class="multi-result-info">
                  <div class="multi-result-name">{{ p.paterno }} {{ p.materno }}, {{ p.nombres }}</div>
                  <div class="multi-result-dni">DNI: {{ p.dni }} <span v-if="p.tipo_personal">· {{ p.tipo_personal }}</span></div>
                </div>
              </div>
            </div>
          </div>

          <a-empty v-if="busquedaRealizada && !resultadoBusqueda && !resultadosMultiples.length" description="No se encontró participante" class="mt-3" />
        </div>
      </div>

      <!-- Lista completa del cargo -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-semibold">Asignados a este cargo ({{ seleccionadosCargoModal.length }})</span>
          <a-button size="small" type="text" @click="getSeleccionadosCargoModal"><ReloadOutlined /></a-button>
        </div>
        <a-table
          :columns="columnsModal"
          :data-source="seleccionadosCargoModal"
          size="small"
          :pagination="false"
          :loading="loadingModalLista"
          :scroll="{ y: 420 }"
          row-key="id"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'es_manual'">
              <a-tag :color="record.es_manual ? 'purple' : 'green'">{{ record.es_manual ? 'DESIGNACIÓN' : 'SORTEO' }}</a-tag>
            </template>
            <template v-if="column.dataIndex === 'estado'">
              <a-tag v-if="record.anulado" color="red">ANULADO</a-tag>
              <a-tag v-else-if="record.observado" color="orange">OBSERVADO</a-tag>
              <a-tag v-else color="green">ACTIVO</a-tag>
            </template>
            <template v-if="column.dataIndex === 'observacion'">
              <a-tooltip v-if="record.anulado" :title="record.motivo_anulacion">
                <span class="text-xs" style="color: #ef4444;">{{ record.motivo_anulacion?.substring(0, 40) }}{{ record.motivo_anulacion?.length > 40 ? '...' : '' }}</span>
              </a-tooltip>
              <a-tooltip v-else-if="record.observado" :title="record.observacion">
                <span class="text-xs" style="color: #f59e0b;">{{ record.observacion?.substring(0, 40) }}{{ record.observacion?.length > 40 ? '...' : '' }}</span>
              </a-tooltip>
              <span v-else style="color: var(--card-muted, #94a3b8);">—</span>
            </template>
            <template v-if="column.dataIndex === 'acciones'">
              <div class="flex gap-1 justify-center">
                <a-tooltip v-if="!record.observado && !record.anulado" title="Observar">
                  <a-button size="small" type="text" @click="emit('observar', record)"><EyeOutlined /></a-button>
                </a-tooltip>
                <a-tooltip v-if="!record.anulado" title="Anular">
                  <a-button size="small" type="text" danger @click="emit('anular', record)"><StopOutlined /></a-button>
                </a-tooltip>
                <a-tooltip v-if="record.observado || record.anulado" title="Restablecer">
                  <a-popconfirm title="¿Restablecer?" @confirm="emit('restablecer', record)">
                    <a-button size="small" type="text"><CheckCircleOutlined /></a-button>
                  </a-popconfirm>
                </a-tooltip>
                <a-popconfirm title="¿Eliminar?" @confirm="emit('eliminar', record)">
                  <a-button size="small" type="text" danger><DeleteOutlined /></a-button>
                </a-popconfirm>
              </div>
            </template>
          </template>
        </a-table>
      </div>
    </div>
  </a-modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
  SearchOutlined, PlusOutlined, DeleteOutlined, UserOutlined, ReloadOutlined,
  EyeOutlined, StopOutlined, CheckCircleOutlined,
} from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const props = defineProps({
  sorteoId: { type: Number, default: null },
  sorteoLabel: { type: String, default: '' },
  sorteoTiposLabel: { type: String, default: null },
  cargosConfig: { type: Array, default: () => [] },
})

const emit = defineEmits(['refresh', 'observar', 'anular', 'restablecer', 'eliminar', 'close'])

const baseUrl = window.location.origin

const visible = ref(false)
const idCargo = ref(null)
const cargoModalAnulados = ref(0)
const seleccionadosCargoModal = ref([])
const loadingModalLista = ref(false)

// Búsqueda
const terminoBuscar = ref('')
const buscando = ref(false)
const resultadoBusqueda = ref(null)
const resultadosMultiples = ref([])
const yaSeleccionado = ref(false)
const cargoAsignado = ref(null)
const participanteEstaObservado = ref(false)
const fueAnulado = ref(false)
const autoAsignar = ref(false)
const busquedaRealizada = ref(false)

// Checkbox: Sorteo (true) o Designación (false)
const esSorteo = ref(true)

const columnsModal = [
  { title: 'DNI', dataIndex: 'dni', key: 'dni', width: 90 },
  { title: 'Nombres', key: 'nombre', ellipsis: true, customRender: ({ record }) => `${record.paterno} ${record.materno}, ${record.nombres}` },
  { title: 'Tipo', dataIndex: 'tipo_personal', key: 'tipo_personal', width: 120, ellipsis: true },
  { title: 'Origen', dataIndex: 'es_manual', key: 'es_manual', width: 100, align: 'center' },
  { title: 'Estado', dataIndex: 'estado', key: 'estado', width: 100, align: 'center' },
  { title: 'Observación / Motivo', dataIndex: 'observacion', key: 'observacion', width: 200, ellipsis: true },
  { title: 'Acciones', dataIndex: 'acciones', key: 'acciones', width: 170, align: 'center' },
]

const cargoData = computed(() => {
  return props.cargosConfig.find(cc => cc.id_cargo === idCargo.value) || null
})

const abrir = async (idCargoVal) => {
  idCargo.value = idCargoVal
  visible.value = true
  limpiarBusqueda()
  await getSeleccionadosCargoModal()
}

const cerrar = () => {
  visible.value = false
  idCargo.value = null
  seleccionadosCargoModal.value = []
  limpiarBusqueda()
  emit('close')
}

const getSeleccionadosCargoModal = async () => {
  if (!idCargo.value || !props.sorteoId) return
  loadingModalLista.value = true
  try {
    const res = await axios.post('sorteo/get-seleccionados', {
      id_sorteo: props.sorteoId,
      id_cargo: idCargo.value,
      incluir_anulados: true,
    })
    seleccionadosCargoModal.value = res.data.datos
    cargoModalAnulados.value = res.data.datos.filter(d => d.anulado).length
  } catch { notification.error({ message: 'Error', description: 'No se pudo cargar la lista', placement: 'topRight' }) }
  finally { loadingModalLista.value = false }
}

const limpiarBusqueda = () => {
  terminoBuscar.value = ''
  resultadoBusqueda.value = null
  resultadosMultiples.value = []
  busquedaRealizada.value = false
  yaSeleccionado.value = false
  cargoAsignado.value = null
  participanteEstaObservado.value = false
  fueAnulado.value = false
}

// Auto-buscar cuando el DNI llega a 8 dígitos
watch(terminoBuscar, (val) => {
  if (val && val.length === 8 && /^\d{8}$/.test(val)) buscarParticipante()
})

const buscarParticipante = async () => {
  const termino = terminoBuscar.value.trim()
  if (!termino || termino.length < 3) {
    notification.warning({ message: 'Búsqueda muy corta', description: 'Ingrese al menos 3 caracteres', placement: 'topRight' })
    return
  }
  if (!idCargo.value) return

  buscando.value = true
  busquedaRealizada.value = true
  resultadoBusqueda.value = null
  resultadosMultiples.value = []
  try {
    const res = await axios.post('sorteo/buscar-participante', {
      buscar: termino,
      id_sorteo: props.sorteoId,
    })
    if (res.data.estado === true) {
      if (res.data.multiple) {
        resultadosMultiples.value = res.data.datos
      } else {
        resultadoBusqueda.value = res.data.datos
        yaSeleccionado.value = res.data.ya_seleccionado
        cargoAsignado.value = res.data.cargo_asignado || null
        participanteEstaObservado.value = res.data.observado || false
        fueAnulado.value = res.data.fue_anulado || false

        if (autoAsignar.value && !res.data.ya_seleccionado && !res.data.observado) {
          await registrarSeleccion()
        }
      }
    } else {
      notification.warning({ message: 'No encontrado', description: res.data.mensaje, placement: 'topRight' })
    }
  } catch { notification.error({ message: 'Error', description: 'No se pudo buscar', placement: 'topRight' }) }
  finally { buscando.value = false }
}

const seleccionarResultado = async (p) => {
  // Al seleccionar de la lista múltiple, verificar estado de selección
  try {
    const res = await axios.post('sorteo/buscar-participante', {
      buscar: p.dni,
      id_sorteo: props.sorteoId,
    })
    if (res.data.estado === true && !res.data.multiple) {
      resultadoBusqueda.value = res.data.datos
      yaSeleccionado.value = res.data.ya_seleccionado
      cargoAsignado.value = res.data.cargo_asignado || null
      participanteEstaObservado.value = res.data.observado || false
      fueAnulado.value = res.data.fue_anulado || false
      resultadosMultiples.value = []

      if (autoAsignar.value && !res.data.ya_seleccionado && !res.data.observado) {
        await registrarSeleccion()
      }
    }
  } catch { notification.error({ message: 'Error', description: 'No se pudo seleccionar', placement: 'topRight' }) }
}

const registrarSeleccion = async () => {
  if (!resultadoBusqueda.value || !idCargo.value || !props.sorteoId) return
  const endpoint = esSorteo.value ? 'sorteo/registrar-seleccion' : 'sorteo/registrar-manual'
  try {
    const res = await axios.post(endpoint, {
      id_participante: resultadoBusqueda.value.id,
      id_cargo: idCargo.value,
      id_sorteo: props.sorteoId,
    })
    if (res.data.estado === true) {
      notification.success({ message: 'Asignado', description: res.data.mensaje, placement: 'topRight' })
      yaSeleccionado.value = true
      cargoAsignado.value = cargoData.value?.cargo || null
      fueAnulado.value = false
      terminoBuscar.value = ''
      resultadoBusqueda.value = null
      await getSeleccionadosCargoModal()
      emit('refresh')
    } else {
      notification.warning({ message: 'No asignado', description: res.data.mensaje, placement: 'topRight' })
    }
  } catch { notification.error({ message: 'Error', description: 'No se pudo registrar', placement: 'topRight' }) }
}

const reloadLista = () => getSeleccionadosCargoModal()

defineExpose({ abrir, cerrar, reloadLista })
</script>

<style scoped>
.participant-card {
  position: relative;
  border-radius: 14px;
  overflow: hidden;
  background: var(--card-bg, #ffffff);
  border: 1px solid var(--card-border, #e2e8f0);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
  transition: box-shadow 0.3s ease;
}
.participant-card:hover {
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
}
.participant-card__body {
  display: flex;
  gap: 20px;
  padding: 20px;
}
.participant-card__photo-wrap {
  flex-shrink: 0;
  position: relative;
}
.participant-card__photo {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #f1f5f9;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}
.participant-card__photo--placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
}
.participant-card__dot {
  position: absolute;
  bottom: 4px;
  right: 4px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 3px solid var(--card-bg, #ffffff);
  background: #cbd5e1;
  transition: background 0.2s ease;
}
.participant-card__dot--active { background: #22c55e; }
.participant-card__dot--warn { background: #f59e0b; }
.participant-card__dot--danger { background: #ef4444; }
.participant-card__info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.participant-card__name {
  font-size: 17px;
  font-weight: 700;
  color: var(--card-text, #1e293b);
  margin: 0 0 12px 0;
  line-height: 1.3;
}
.participant-card__details {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px 16px;
  margin-bottom: 10px;
}
.participant-card__detail-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.participant-card__detail-label {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  color: var(--card-muted, #94a3b8);
}
.participant-card__detail-value {
  font-size: 13px;
  font-weight: 500;
  color: var(--card-text, #334155);
}
.participant-card__statuses {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: auto;
}
.participant-card__status {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
}
.participant-card__status--success {
  background: #dcfce7;
  color: #15803d;
}
.participant-card__status--warning {
  background: #fef3c7;
  color: #b45309;
}
.participant-card__status--danger {
  background: #fee2e2;
  color: #b91c1c;
}

/* Multi-result list */
.multi-result-list {
  max-height: 380px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.multi-result-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid var(--card-border, #e2e8f0);
  background: var(--card-bg, #ffffff);
  cursor: pointer;
  transition: all 0.15s ease;
}
.multi-result-item:hover {
  border-color: var(--primary-color, #3b82f6);
  background: var(--hover-bg, #eff6ff);
}
.multi-result-foto {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}
.multi-result-foto--placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
  color: #cbd5e1;
  font-size: 18px;
}
.multi-result-info {
  min-width: 0;
}
.multi-result-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--card-text, #1e293b);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.multi-result-dni {
  font-size: 11px;
  color: var(--card-muted, #64748b);
}

body.theme-dark .participant-card__photo--placeholder,
body.theme-hybrid .participant-card__photo--placeholder {
  background: var(--table-header-bg, #1e293b) !important;
}
body.theme-dark .participant-card__photo,
body.theme-hybrid .participant-card__photo {
  border-color: var(--table-header-bg, #1e293b) !important;
}
body.theme-dark .participant-card__dot,
body.theme-hybrid .participant-card__dot {
  border-color: var(--card-bg, #1e293b) !important;
}
body.theme-dark .participant-card__status--success,
body.theme-hybrid .participant-card__status--success {
  background: rgba(22, 163, 74, 0.18) !important;
  color: #4ade80 !important;
}
body.theme-dark .participant-card__status--warning,
body.theme-hybrid .participant-card__status--warning {
  background: rgba(245, 158, 11, 0.18) !important;
  color: #fbbf24 !important;
}
body.theme-dark .participant-card__status--danger,
body.theme-hybrid .participant-card__status--danger {
  background: rgba(239, 68, 68, 0.18) !important;
  color: #f87171 !important;
}
body.theme-dark .multi-result-item,
body.theme-hybrid .multi-result-item {
  background: var(--card-bg) !important;
  border-color: var(--card-border) !important;
}
body.theme-dark .multi-result-item:hover,
body.theme-hybrid .multi-result-item:hover {
  background: var(--hover-bg) !important;
}
body.theme-dark .multi-result-foto--placeholder,
body.theme-hybrid .multi-result-foto--placeholder {
  background: var(--table-header-bg) !important;
  color: var(--card-muted) !important;
}
</style>
