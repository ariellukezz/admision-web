<template>
  <Head title="Selección de Personal" />
  <AuthenticatedLayout>
    <div class="overflow-hidden shadow-sm sm:rounded-lg p-6" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">

      <!-- Header: Selector de sorteo + botón nuevo -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3 flex-1 min-w-[300px]">
          <a-select
            v-model:value="sorteoSeleccionado"
            :options="sorteos"
            placeholder="Seleccionar sorteo"
            style="min-width: 300px;"
            show-search
            option-filter-prop="label"
            @change="onSorteoChange"
          />
          <a-button type="primary" style="background: #2563eb; border: none; border-radius: 6px;" @click="nuevoSorteo = { nombre: '', descripcion: '', tipos: [] }; modalSorteo = true">
            <template #icon><PlusOutlined /></template>
            Nuevo Sorteo
          </a-button>
        </div>
        <div v-if="sorteoActual" class="flex items-center gap-3">
          <div>
            <span class="font-semibold text-base">{{ sorteoActual.label }}</span>
            <span v-if="sorteoActual.descripcion" class="text-sm ml-2" style="color: var(--card-muted, #64748b);">{{ sorteoActual.descripcion }}</span>
          </div>
          <a-tag :color="sorteoActual.estado ? 'green' : 'default'">{{ sorteoActual.estado ? 'Activo' : 'Inactivo' }}</a-tag>
          <a-button size="small" type="text" @click="abrirModalEditarSorteo"><EditOutlined /></a-button>
        </div>
      </div>

      <template v-if="sorteoSeleccionado">

        <!-- Cards de cargos clickeables -->
        <div class="flex flex-wrap items-stretch gap-3 mb-5">
          <div
            v-for="cc in cargosConfig"
            :key="cc.id"
            class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md min-w-[180px] flex-1"
            :style="`border-color: ${modalCargoAbierto === cc.id_cargo ? 'var(--primary-color, #3b82f6)' : 'var(--card-border, #e2e8f0)'}; border-width: 2px; ${cc.disponibles === 0 ? 'opacity: 0.75;' : ''}`"
            @click="abrirModalCargo(cc)"
          >
            <div class="flex items-center justify-between mb-2">
              <div class="text-xs uppercase tracking-wide font-semibold" style="color: var(--card-muted, #64748b);">{{ cc.cargo }}</div>
              <a-button size="small" type="text" style="color: var(--primary-color, #3b82f6);"><ArrowRightOutlined /></a-button>
            </div>
            <div class="flex items-baseline gap-1">
              <span class="text-3xl font-bold">{{ cc.asignados }}</span>
              <span class="text-sm" style="color: var(--card-muted, #64748b);">/ {{ cc.cantidad }}</span>
            </div>
            <a-progress
              :percent="cc.cantidad > 0 ? Math.round((cc.asignados / cc.cantidad) * 100) : 0"
              :stroke-color="cc.disponibles === 0 ? '#ef4444' : '#16a34a'"
              size="small"
              :show-info="false"
            />
            <div class="text-xs mt-1" :style="`color: ${cc.disponibles === 0 ? '#ef4444' : 'var(--card-muted, #64748b)'};`">
              {{ cc.disponibles }} disponibles
            </div>
          </div>

          <!-- Botón configurar cargos -->
          <div class="border rounded-lg p-4 flex items-center justify-center min-w-[180px]" style="border-color: var(--card-border, #e2e8f0); border-style: dashed;">
            <a-button type="primary" style="background: #2563eb; border: none; border-radius: 6px;" @click="modalConfig = true">
              <template #icon><SettingOutlined /></template>
              Configurar Cargos
            </a-button>
          </div>
        </div>

        <!-- Lista general -->
        <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
          <h3 class="font-semibold text-base">Lista General ({{ seleccionados.length }})</h3>
          <div class="flex flex-wrap gap-2 items-center">
            <a-select
              v-model:value="filtroCargoLista"
              placeholder="Filtrar por cargo"
              allow-clear
              style="min-width: 200px;"
              :options="cargosOptions"
              @change="getSeleccionados"
            />
            <a-select
              v-model:value="filtroOrigenLista"
              placeholder="Origen"
              allow-clear
              style="min-width: 140px;"
              :options="origenOptions"
              @change="getSeleccionados"
            />
            <a-checkbox v-model:checked="incluirAnulados" @change="getSeleccionados">Incluir anulados</a-checkbox>
            <a-button size="small" @click="getSeleccionados"><template #icon><ReloadOutlined /></template></a-button>
            <a-button size="small" @click="exportExcel"><template #icon><FileExcelOutlined /></template>Excel</a-button>
            <a-button size="small" @click="exportPdf"><template #icon><FilePdfOutlined /></template>Seleccionados</a-button>
            <a-button size="small" @click="exportObservadosPdf"><template #icon><FilePdfOutlined /></template>Obs. y Anul.</a-button>
            <a-button size="small" @click="exportResumenPdf"><template #icon><FilePdfOutlined /></template>Resumen</a-button>
            <a-button size="small" @click="exportCredencialesPdf"><template #icon><FilePdfOutlined /></template>Credenciales PDF</a-button>
            <a-button size="small" @click="verCredencialesVue"><template #icon><EyeOutlined /></template>Credenciales Vista</a-button>
          </div>
        </div>

        <a-table
          :columns="columnsSimple"
          :data-source="seleccionados"
          :pagination="{ pageSize: 50 }"
          size="small"
          :loading="loadingLista"
          row-key="id"
          :scroll="{ x: 'max-content' }"
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
            <template v-if="column.dataIndex === 'acciones'">
              <div class="flex gap-1 justify-center">
                <a-tooltip v-if="!record.observado && !record.anulado" title="Observar">
                  <a-button size="small" type="text" @click="abrirModalObservar(record)"><EyeOutlined /></a-button>
                </a-tooltip>
                <a-tooltip v-if="!record.anulado" title="Anular">
                  <a-button size="small" type="text" danger @click="abrirModalAnular(record)"><StopOutlined /></a-button>
                </a-tooltip>
                <a-tooltip v-if="record.observado || record.anulado" title="Restablecer">
                  <a-popconfirm title="¿Restablecer?" @confirm="restablecerParticipante(record)">
                    <a-button size="small" type="text"><CheckCircleOutlined /></a-button>
                  </a-popconfirm>
                </a-tooltip>
                <a-popconfirm title="¿Eliminar?" @confirm="eliminarSeleccionado(record)">
                  <a-button size="small" type="text" danger><DeleteOutlined /></a-button>
                </a-popconfirm>
              </div>
            </template>
          </template>
        </a-table>
      </template>

      <a-empty v-else description="Seleccione o cree un sorteo para comenzar" style="padding: 60px 0;" />
    </div>

    <!-- ===== MODAL POR CARGO (componente) ===== -->
    <SorteoModalCargo
      ref="modalCargoRef"
      :sorteo-id="sorteoSeleccionado"
      :sorteo-label="sorteoActual?.label"
      :sorteo-tipos-label="sorteoTiposLabel"
      :cargos-config="cargosConfig"
      @refresh="onModalCargoRefresh"
      @close="modalCargoAbierto = null"
      @observar="abrirModalObservar"
      @anular="abrirModalAnular"
      @restablecer="restablecerParticipante"
      @eliminar="eliminarSeleccionado"
    />

    <!-- ===== MODAL: Nuevo / Editar Sorteo ===== -->
    <a-modal v-model:open="modalSorteo" :title="nuevoSorteo.id ? 'Editar Sorteo' : 'Nuevo Sorteo'" @ok="saveSorteo" :confirm-loading="savingSorteo" ok-text="Guardar" cancel-text="Cancelar">
      <a-form layout="vertical">
        <a-form-item label="Nombre" required>
          <a-input v-model:value="nuevoSorteo.nombre" placeholder="Ej: Sorteo Aula 1" />
        </a-form-item>
        <a-form-item label="Descripción">
          <a-textarea v-model:value="nuevoSorteo.descripcion" :rows="2" placeholder="Descripción opcional" />
        </a-form-item>
        <a-form-item label="Tipos de personal (opcional)">
          <a-select v-model:value="nuevoSorteo.tipos" mode="multiple" :options="props.tipos" placeholder="Seleccionar tipos" allow-clear />
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- ===== MODAL: Configuración de Cargos ===== -->
    <a-modal v-model:open="modalConfig" title="Configurar Cantidad por Cargo" :footer="null" width="600px">
      <div class="flex justify-end mb-3">
        <a-button size="small" @click="exportCargosConfigPdf" :disabled="cargosConfig.length === 0">
          <template #icon><FilePdfOutlined /></template> PDF
        </a-button>
      </div>
      <div class="mb-4 flex flex-wrap gap-2 items-end">
        <div class="flex-1 min-w-[200px]">
          <label class="text-xs block mb-1">Cargo</label>
          <a-select v-model:value="configCargo" :options="props.cargos" placeholder="Seleccionar cargo" style="width: 100%;" show-search option-filter-prop="label" :disabled="!!configEditId" />
        </div>
        <div style="width: 100px;">
          <label class="text-xs block mb-1">Cantidad</label>
          <a-input-number v-model:value="configCantidad" :min="1" style="width: 100%;" />
        </div>
        <a-button type="primary" style="background: #2563eb; border: none; border-radius: 6px;" :disabled="!configCargo || !configCantidad" @click="saveConfig">
          {{ configEditId ? 'Actualizar' : 'Agregar' }}
        </a-button>
        <a-button v-if="configEditId" @click="cancelarEditConfig">Cancelar</a-button>
      </div>
      <a-table :columns="configColumns" :data-source="cargosConfig" size="small" :pagination="false" row-key="id">
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'acciones'">
            <div class="flex gap-1 justify-center">
              <a-tooltip title="Editar">
                <a-button size="small" type="text" style="color: #2563eb;" @click="editarConfig(record)"><EditOutlined /></a-button>
              </a-tooltip>
              <a-popconfirm title="¿Eliminar?" @confirm="deleteConfig(record)">
                <a-button size="small" type="text" danger><DeleteOutlined /></a-button>
              </a-popconfirm>
            </div>
          </template>
        </template>
      </a-table>
    </a-modal>

    <!-- ===== MODAL: Observar ===== -->
    <a-modal v-model:open="modalObservar" title="Observar Participante" @ok="confirmarObservar" :confirm-loading="savingObservar" ok-text="Guardar" cancel-text="Cancelar">
      <div class="mb-3">
        <p v-if="recordObservar" class="text-sm">
          <strong>{{ recordObservar.paterno }} {{ recordObservar.materno }}, {{ recordObservar.nombres }}</strong>
          <br>DNI: {{ recordObservar.dni }}
        </p>
      </div>
      <a-textarea v-model:value="observacionTexto" :rows="4" placeholder="Escriba la observación..." />
    </a-modal>

    <!-- ===== MODAL: Anular ===== -->
    <a-modal v-model:open="modalAnular" title="Anular Participante" @ok="confirmarAnular" :confirm-loading="savingAnular" ok-text="Anular" cancel-text="Cancelar" ok-type="danger">
      <div class="mb-3">
        <p v-if="recordAnular" class="text-sm">
          <strong>{{ recordAnular.paterno }} {{ recordAnular.materno }}, {{ recordAnular.nombres }}</strong>
          <br>DNI: {{ recordAnular.dni }}
        </p>
      </div>
      <a-textarea v-model:value="motivoAnulacionTexto" :rows="4" placeholder="Escriba el motivo de anulación..." />
    </a-modal>

    <!-- ===== MODAL: Credenciales (componente) ===== -->
    <SorteoCredencialesModal
      ref="credencialesModalRef"
      :sorteo-id="sorteoSeleccionado"
      :filtro-cargo="filtroCargoLista"
      :filtro-origen="filtroOrigenLista"
    />

  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SorteoModalCargo from './components/SorteoModalCargo.vue'
import SorteoCredencialesModal from './components/SorteoCredencialesModal.vue'
import { ref, computed } from 'vue'
import {
  PlusOutlined, DeleteOutlined, ReloadOutlined,
  SettingOutlined, EditOutlined, EyeOutlined, StopOutlined, CheckCircleOutlined,
  FileExcelOutlined, FilePdfOutlined, ArrowRightOutlined,
} from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const props = defineProps({
  tipos: { type: Array, default: () => [] },
  cargos: { type: Array, default: () => [] },
  sorteos: { type: Array, default: () => [] },
})

// ─── Component refs ───
const modalCargoRef = ref(null)
const credencialesModalRef = ref(null)

// ─── Origen filter options ───
const origenOptions = [
  { value: 'sorteo', label: 'SORTEO' },
  { value: 'manual', label: 'DESIGNACIÓN' },
]
// ─── Sorteo ───
const sorteos = ref([...props.sorteos])
const sorteoSeleccionado = ref(null)
const sorteoActual = computed(() => sorteos.value.find(s => s.value === sorteoSeleccionado.value) || null)
const sorteoTiposLabel = computed(() => {
  if (!sorteoActual.value?.tipos?.length) return null
  return sorteoActual.value.tipos.map(id => props.tipos.find(t => t.value === id)?.label).filter(Boolean).join(', ')
})

// ─── Cargo config ───
const cargosConfig = ref([])
const cargosOptions = computed(() => cargosConfig.value.map(cc => ({ value: cc.id_cargo, label: cc.cargo })))

// ─── Lista general ───
const seleccionados = ref([])
const loadingLista = ref(false)
const filtroCargoLista = ref(null)
const filtroOrigenLista = ref(null)
const incluirAnulados = ref(false)

// ─── Modal por cargo (tracking for highlight) ───
const modalCargoAbierto = ref(null)

// ─── Otros modales ───
const modalSorteo = ref(false)
const modalConfig = ref(false)
const modalObservar = ref(false)
const modalAnular = ref(false)

// ─── Forms ───
const nuevoSorteo = ref({ nombre: '', descripcion: '', tipos: [] })
const savingSorteo = ref(false)
const configCargo = ref(null)
const configCantidad = ref(null)
const configEditId = ref(null)
const recordObservar = ref(null)
const observacionTexto = ref('')
const savingObservar = ref(false)
const recordAnular = ref(null)
const motivoAnulacionTexto = ref('')
const savingAnular = ref(false)

// ─── Columns ───
const columnsSimple = [
  { title: 'DNI', dataIndex: 'dni', key: 'dni', width: 100 },
  { title: 'Nombres', key: 'nombre_completo', ellipsis: true, customRender: ({ record }) => `${record.paterno} ${record.materno}, ${record.nombres}` },
  { title: 'Cargo', dataIndex: 'cargo', key: 'cargo', ellipsis: true },
  { title: 'Origen', dataIndex: 'es_manual', key: 'es_manual', width: 90, align: 'center' },
  { title: 'Estado', dataIndex: 'estado', key: 'estado', width: 110, align: 'center' },
  { title: 'Acciones', dataIndex: 'acciones', key: 'acciones', width: 170, align: 'center' },
]

const configColumns = [
  { title: 'Cargo', dataIndex: 'cargo', key: 'cargo' },
  { title: 'Cantidad', dataIndex: 'cantidad', key: 'cantidad', width: 100, align: 'center' },
  { title: 'Asignados', dataIndex: 'asignados', key: 'asignados', width: 100, align: 'center' },
  { title: 'Disponibles', dataIndex: 'disponibles', key: 'disponibles', width: 110, align: 'center' },
  { title: '', dataIndex: 'acciones', key: 'acciones', width: 90, align: 'center' },
]

// ─── Methods ───
const notif = (type, titulo, mensaje) => notification[type]({ message: titulo, description: mensaje, placement: 'topRight' })

const onSorteoChange = async (val) => {
  if (!val) { cargosConfig.value = []; seleccionados.value = []; return }
  await Promise.all([getConfigCargos(), getSeleccionados()])
}

const getConfigCargos = async () => {
  if (!sorteoSeleccionado.value) return
  try {
    const res = await axios.get(`sorteo/get-config-cargos/${sorteoSeleccionado.value}`)
    cargosConfig.value = res.data.datos
  } catch { notif('error', 'Error', 'No se pudo cargar la configuración') }
}

// ─── Modal por cargo ───
const abrirModalCargo = async (cc) => {
  modalCargoAbierto.value = cc.id_cargo
  await modalCargoRef.value?.abrir(cc.id_cargo)
}

const onModalCargoRefresh = async () => {
  await Promise.all([getConfigCargos(), getSeleccionados()])
  modalCargoRef.value?.reloadLista()
}

// ─── Lista general ───
const getSeleccionados = async () => {
  if (!sorteoSeleccionado.value) return
  loadingLista.value = true
  try {
    const res = await axios.post('sorteo/get-seleccionados', {
      id_sorteo: sorteoSeleccionado.value,
      id_cargo: filtroCargoLista.value || null,
      incluir_anulados: incluirAnulados.value,
      filtro_origen: filtroOrigenLista.value || null,
    })
    seleccionados.value = res.data.datos
  } catch { notif('error', 'Error', 'No se pudo cargar la lista') }
  finally { loadingLista.value = false }
}

const eliminarSeleccionado = async (item) => {
  try {
    const res = await axios.get(`sorteo/eliminar-seleccionado/${item.id}`)
    notif('warning', res.data.titulo, res.data.mensaje)
    await Promise.all([getConfigCargos(), getSeleccionados()])
    modalCargoRef.value?.reloadLista()
  } catch { notif('error', 'Error', 'No se pudo eliminar') }
}

// ─── Sorteo CRUD ───
const abrirModalEditarSorteo = () => {
  if (!sorteoActual.value) return
  nuevoSorteo.value = {
    id: sorteoActual.value.value,
    nombre: sorteoActual.value.label,
    descripcion: sorteoActual.value.descripcion || '',
    tipos: sorteoActual.value.tipos || [],
  }
  modalSorteo.value = true
}

const saveSorteo = async () => {
  if (!nuevoSorteo.value.nombre) { notif('warning', 'Nombre requerido', 'Ingrese un nombre'); return }
  savingSorteo.value = true
  try {
    const payload = {
      nombre: nuevoSorteo.value.nombre,
      descripcion: nuevoSorteo.value.descripcion,
      tipos: nuevoSorteo.value.tipos.length > 0 ? nuevoSorteo.value.tipos : null,
    }
    if (nuevoSorteo.value.id) payload.id = nuevoSorteo.value.id
    const res = await axios.post('sorteo/save-sorteo', payload)
    if (res.data.estado === true) {
      notif('success', nuevoSorteo.value.id ? 'Sorteo actualizado' : 'Sorteo creado', res.data.mensaje)
      if (nuevoSorteo.value.id) {
        const idx = sorteos.value.findIndex(s => s.value === nuevoSorteo.value.id)
        if (idx !== -1) sorteos.value[idx] = { value: res.data.datos.id, label: res.data.datos.nombre, estado: res.data.datos.estado, descripcion: res.data.datos.descripcion, tipos: res.data.datos.tipos || [] }
      } else {
        sorteos.value.push({ value: res.data.datos.id, label: res.data.datos.nombre, estado: res.data.datos.estado, descripcion: res.data.datos.descripcion, tipos: res.data.datos.tipos || [] })
        sorteoSeleccionado.value = res.data.datos.id
      }
      modalSorteo.value = false
      const wasEditing = !!nuevoSorteo.value.id
      nuevoSorteo.value = { nombre: '', descripcion: '', tipos: [] }
      if (!wasEditing) await onSorteoChange(res.data.datos.id)
    }
  } catch { notif('error', 'Error', 'No se pudo guardar') }
  finally { savingSorteo.value = false }
}

// ─── Config ───
const saveConfig = async () => {
  if (!configCargo.value || !configCantidad.value || !sorteoSeleccionado.value) return
  try {
    const res = await axios.post('sorteo/save-config-cantidad', { id_sorteo: sorteoSeleccionado.value, id_cargo: configCargo.value, cantidad: configCantidad.value })
    notif('success', 'Configuración', res.data.mensaje)
    cancelarEditConfig()
    await getConfigCargos()
  } catch { notif('error', 'Error', 'No se pudo guardar') }
}

const editarConfig = (record) => {
  configEditId.value = record.id
  configCargo.value = record.id_cargo
  configCantidad.value = record.cantidad
}

const cancelarEditConfig = () => {
  configEditId.value = null
  configCargo.value = null
  configCantidad.value = null
}

const deleteConfig = async (item) => {
  try {
    const res = await axios.get(`sorteo/eliminar-config-cantidad/${item.id}`)
    notif('success', 'Eliminado', res.data.mensaje)
    await getConfigCargos()
  } catch { notif('error', 'Error', 'No se pudo eliminar') }
}

// ─── Observar / Anular / Restablecer ───
const abrirModalObservar = (record) => { recordObservar.value = record; observacionTexto.value = ''; modalObservar.value = true }

const confirmarObservar = async () => {
  if (!observacionTexto.value) { notif('warning', 'Observación requerida', 'Escriba una observación'); return }
  savingObservar.value = true
  try {
    const res = await axios.post('sorteo/observar-participante', { id: recordObservar.value.id, observacion: observacionTexto.value })
    notif('success', 'Observado', res.data.mensaje)
    modalObservar.value = false
    await getSeleccionados()
    modalCargoRef.value?.reloadLista()
  } catch { notif('error', 'Error', 'No se pudo observar') }
  finally { savingObservar.value = false }
}

const abrirModalAnular = (record) => { recordAnular.value = record; motivoAnulacionTexto.value = ''; modalAnular.value = true }

const confirmarAnular = async () => {
  if (!motivoAnulacionTexto.value) { notif('warning', 'Motivo requerido', 'Escriba el motivo'); return }
  savingAnular.value = true
  try {
    const res = await axios.post('sorteo/anular-participante', { id: recordAnular.value.id, motivo_anulacion: motivoAnulacionTexto.value })
    notif('warning', 'Anulado', res.data.mensaje)
    modalAnular.value = false
    await Promise.all([getConfigCargos(), getSeleccionados()])
    modalCargoRef.value?.reloadLista()
  } catch { notif('error', 'Error', 'No se pudo anular') }
  finally { savingAnular.value = false }
}

const restablecerParticipante = async (record) => {
  try {
    const res = await axios.post('sorteo/restablecer-participante', { id: record.id })
    if (res.data.estado === true) {
      notif('success', 'Restablecido', res.data.mensaje)
      await Promise.all([getConfigCargos(), getSeleccionados()])
      modalCargoRef.value?.reloadLista()
    } else { notif('warning', 'No restablecido', res.data.mensaje) }
  } catch { notif('error', 'Error', 'No se pudo restablecer') }
}

// ─── Exports ───
const exportExcel = () => {
  if (!sorteoSeleccionado.value) return
  let url = `sorteo/export-excel?id_sorteo=${sorteoSeleccionado.value}`
  if (filtroCargoLista.value) url += `&id_cargo=${filtroCargoLista.value}`
  if (filtroOrigenLista.value) url += `&filtro_origen=${filtroOrigenLista.value}`
  window.open(url, '_blank')
}
const exportPdf = () => {
  if (!sorteoSeleccionado.value) return
  let url = `sorteo/export-pdf?id_sorteo=${sorteoSeleccionado.value}`
  if (filtroCargoLista.value) url += `&id_cargo=${filtroCargoLista.value}`
  if (filtroOrigenLista.value) url += `&filtro_origen=${filtroOrigenLista.value}`
  window.open(url, '_blank')
}
const exportCargosConfigPdf = () => { if (sorteoSeleccionado.value) window.open(`sorteo/export-cargos-config-pdf?id_sorteo=${sorteoSeleccionado.value}`, '_blank') }
const exportResumenPdf = () => { if (sorteoSeleccionado.value) window.open(`sorteo/export-resumen-pdf?id_sorteo=${sorteoSeleccionado.value}`, '_blank') }
const exportObservadosPdf = () => { if (sorteoSeleccionado.value) window.open(`sorteo/export-observados-pdf?id_sorteo=${sorteoSeleccionado.value}`, '_blank') }
const exportCredencialesPdf = () => {
  if (!sorteoSeleccionado.value) return
  let url = `sorteo/export-credenciales-pdf?id_sorteo=${sorteoSeleccionado.value}`
  if (filtroCargoLista.value) url += `&id_cargo=${filtroCargoLista.value}`
  if (filtroOrigenLista.value) url += `&filtro_origen=${filtroOrigenLista.value}`
  window.open(url, '_blank')
}
const verCredencialesVue = () => { credencialesModalRef.value?.open() }
</script>

<style>
body.theme-dark .ant-modal-content, body.theme-hybrid .ant-modal-content { background: var(--card-bg) !important; }
body.theme-dark .ant-modal-header, body.theme-hybrid .ant-modal-header { background: var(--card-bg) !important; }
body.theme-dark .ant-modal-title, body.theme-hybrid .ant-modal-title { color: var(--card-text) !important; }
body.theme-dark .ant-modal-close-icon, body.theme-hybrid .ant-modal-close-icon { color: var(--card-muted) !important; }
body.theme-dark .ant-modal-body, body.theme-hybrid .ant-modal-body { color: var(--card-text) !important; }
body.theme-dark .ant-modal-footer, body.theme-hybrid .ant-modal-footer { background: var(--card-bg) !important; }
body.theme-dark .ant-form-item-label > label, body.theme-hybrid .ant-form-item-label > label { color: var(--card-text) !important; }
body.theme-dark .ant-table, body.theme-hybrid .ant-table { background: transparent !important; color: var(--card-text) !important; }
body.theme-dark .ant-table-thead > tr > th, body.theme-hybrid .ant-table-thead > tr > th { background: var(--table-header-bg) !important; color: var(--card-text) !important; border-bottom: 1px solid var(--card-border) !important; }
body.theme-dark .ant-table-tbody > tr > td, body.theme-hybrid .ant-table-tbody > tr > td { color: var(--card-text) !important; border-bottom: 1px solid var(--card-border) !important; background: var(--card-bg) !important; }
body.theme-dark .ant-table-tbody > tr:hover > td, body.theme-hybrid .ant-table-tbody > tr:hover > td { background: var(--hover-bg) !important; }
body.theme-dark .ant-input-number, body.theme-hybrid .ant-input-number { background: var(--card-bg) !important; border-color: var(--card-border) !important; color: var(--card-text) !important; }
body.theme-dark .ant-input-number-input, body.theme-hybrid .ant-input-number-input { background: var(--card-bg) !important; color: var(--card-text) !important; }
body.theme-dark .ant-alert, body.theme-hybrid .ant-alert { background: var(--card-bg) !important; }
body.theme-dark .ant-empty-description, body.theme-hybrid .ant-empty-description { color: var(--card-muted) !important; }
</style>
