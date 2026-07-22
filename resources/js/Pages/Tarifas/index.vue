<template>
  <Head title="Tarifas" />

  <AuthenticatedLayout>
    <div class="tar-container" style="height: calc(100vh - 100px);">

      <div class="flex flex-col sm:flex-row justify-between mb-6 gap-4">
        <a-button
          type="primary"
          style="background: #2563eb; border: none; border-radius: 6px;"
          @click="showModal"
        >
          <template #icon><PlusOutlined /></template>
          Nueva Tarifa
        </a-button>

        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
          <a-select
            v-model:value="filtroProceso"
            :options="procesosOpts"
            placeholder="Todos los procesos"
            allow-clear
            show-search
            option-filter-prop="label"
            class="min-w-[180px]"
            @change="getTarifas"
          />
          <a-input-search
            v-model:value="buscar"
            placeholder="Buscar tarifas..."
            class="max-w-full sm:max-w-xs"
            @search="getTarifas"
          >
            <template #prefix>
              <SearchOutlined />
            </template>
          </a-input-search>
        </div>
      </div>

      <div class="overflow-x-auto">
        <a-table
          :columns="columns"
          :data-source="tarifas"
          :pagination="{ pageSize: 50 }"
          size="small"
          :loading="loading"
          :scroll="{ x: 'max-content', y: 'calc(100vh - 280px)' }"
          row-key="id"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'monto'">
              <span class="font-medium">
                {{ record.moneda }} {{ parseFloat(record.monto).toFixed(2) }}
              </span>
            </template>

            <template v-if="column.dataIndex === 'estado'">
              <a-tag :color="record.estado ? 'green' : 'red'">
                {{ record.estado ? 'VIGENTE' : 'NO VIGENTE' }}
              </a-tag>
            </template>

            <template v-if="column.dataIndex === 'acciones'">
              <a-space size="small">
                <a-tooltip title="Editar">
                  <a-button
                    size="small"
                    type="text"
                    class="text-blue-600 hover:text-blue-800"
                    @click="abrirEditar(record)"
                  >
                    <template #icon><EditOutlined /></template>
                  </a-button>
                </a-tooltip>

                <a-tooltip :title="record.estado ? 'Desactivar' : 'Activar'">
                  <a-button
                    size="small"
                    type="text"
                    :class="record.estado ? 'text-amber-500 hover:text-amber-700' : 'text-green-600 hover:text-green-800'"
                    @click="cambiarEstado(record)"
                  >
                    <template #icon><PoweroffOutlined /></template>
                  </a-button>
                </a-tooltip>

                <a-popconfirm
                  title="¿Está seguro de eliminar?"
                  @confirm="eliminar(record)"
                >
                  <a-tooltip title="Eliminar">
                    <a-button
                      size="small"
                      type="text"
                      danger
                    >
                      <template #icon><DeleteOutlined /></template>
                    </a-button>
                  </a-tooltip>
                </a-popconfirm>
              </a-space>
            </template>
          </template>
        </a-table>
      </div>

    </div>
  </AuthenticatedLayout>

  <a-modal
    v-model:open="visible"
    centered
    :title="tarifa.id ? 'Editar Tarifa' : 'Nueva Tarifa'"
    width="90%"
    :style="{ maxWidth: '600px' }"
    :footer="null"
    destroy-on-close
  >
    <a-form
      ref="formRef"
      :model="tarifa"
      :rules="rules"
      layout="vertical"
    >
      <a-form-item label="Concepto" name="concepto">
        <a-input
          v-model:value="tarifa.concepto"
          placeholder="Descripción de la tarifa"
          allow-clear
        />
      </a-form-item>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a-form-item label="Monto" name="monto">
          <a-input-number
            v-model:value="tarifa.monto"
            :min="0"
            :precision="2"
            class="w-full"
            placeholder="0.00"
          />
        </a-form-item>

        <a-form-item label="Moneda" name="moneda">
          <a-select v-model:value="tarifa.moneda" placeholder="Moneda">
            <a-select-option value="PEN">PEN (S/)</a-select-option>
            <a-select-option value="USD">USD ($)</a-select-option>
          </a-select>
        </a-form-item>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a-form-item label="Proceso" name="id_proceso">
          <a-select
            v-model:value="tarifa.id_proceso"
            :options="procesosOpts"
            placeholder="Opcional"
            allow-clear
            show-search
            option-filter-prop="label"
          />
        </a-form-item>

        <a-form-item label="Programa" name="id_programa">
          <a-select
            v-model:value="tarifa.id_programa"
            :options="programasOpts"
            placeholder="Opcional"
            allow-clear
            show-search
            option-filter-prop="label"
          />
        </a-form-item>

        <a-form-item label="Modalidad" name="id_modalidad">
          <a-select
            v-model:value="tarifa.id_modalidad"
            :options="modalidadesOpts"
            placeholder="Opcional"
            allow-clear
            show-search
            option-filter-prop="label"
          />
        </a-form-item>
      </div>

      <a-form-item v-if="tarifa.id" label="Estado" name="estado">
        <a-switch
          v-model:checked="tarifa.estado"
          checked-children="Activo"
          un-checked-children="Inactivo"
        />
      </a-form-item>

      <div class="flex justify-end gap-3 pt-4 border-t">
        <a-button @click="cancelar">Cancelar</a-button>
        <a-button
          type="primary"
          style="background: #2563eb; border: none; border-radius: 6px;"
          :loading="guardando"
          @click="guardar"
        >
          {{ tarifa.id ? 'Actualizar' : 'Guardar' }}
        </a-button>
      </div>
    </a-form>
  </a-modal>

</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import {
  EditOutlined,
  DeleteOutlined,
  SearchOutlined,
  PlusOutlined,
  PoweroffOutlined
} from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const buscar = ref('')
const tarifas = ref([])
const visible = ref(false)
const loading = ref(false)
const guardando = ref(false)
const formRef = ref(null)
const filtroProceso = ref(null)

const procesosOpts = ref([])
const programasOpts = ref([])
const modalidadesOpts = ref([])

const tarifa = ref({
  id: null,
  concepto: '',
  monto: null,
  moneda: 'PEN',
  id_proceso: null,
  id_programa: null,
  id_modalidad: null,
  estado: true
})

const rules = {
  concepto: [{ required: true, message: 'Ingrese el concepto' }],
  monto: [{ required: true, message: 'Ingrese el monto' }],
}

const columns = [
  { title: 'Concepto', dataIndex: 'concepto', key: 'concepto', ellipsis: true },
  { title: 'Monto', dataIndex: 'monto', key: 'monto', align: 'right', width: 130 },
  { title: 'Proceso', dataIndex: 'proceso', key: 'proceso', ellipsis: true, responsive: ['md'] },
  { title: 'Programa', dataIndex: 'programa', key: 'programa', ellipsis: true, responsive: ['lg'] },
  { title: 'Modalidad', dataIndex: 'modalidad', key: 'modalidad', ellipsis: true, responsive: ['lg'] },
  { title: 'Estado', dataIndex: 'estado', key: 'estado', align: 'center', width: 110 },
  { title: 'Acciones', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 120, fixed: 'right' },
]

const getTarifas = async () => {
  loading.value = true
  try {
    const res = await axios.post('tarifas/get-tarifas', {
      term: buscar.value,
      id_proceso: filtroProceso.value,
    })
    tarifas.value = res.data.datos.data
  } catch {
    notificacion('error', 'Error', 'No se pudieron cargar las tarifas')
  } finally {
    loading.value = false
  }
}

const getSelects = async () => {
  try {
    const [proc, prog, mod] = await Promise.all([
      axios.get('get-select-procesos'),
      axios.post('select-programas', { term: '' }),
      axios.get('get-modalidades-activas'),
    ])
    procesosOpts.value = proc.data.datos
    programasOpts.value = prog.data.datos
    modalidadesOpts.value = mod.data.datos
  } catch {
    console.error('Error al cargar selects')
  }
}

const showModal = () => {
  resetTarifa()
  visible.value = true
}

const abrirEditar = (item) => {
  tarifa.value = {
    id: item.id,
    concepto: item.concepto,
    monto: parseFloat(item.monto),
    moneda: item.moneda || 'PEN',
    id_proceso: item.id_proceso,
    id_programa: item.id_programa,
    id_modalidad: item.id_modalidad,
    estado: item.estado,
  }
  visible.value = true
}

const guardar = async () => {
  try {
    await formRef.value.validate()
  } catch {
    return
  }

  guardando.value = true
  try {
    const payload = { ...tarifa.value }
    const res = await axios.post('save-tarifa', payload)
    notificacion('success', res.data.titulo, res.data.mensaje)
    getTarifas()
    visible.value = false
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.values(error.response.data.errors).forEach(err => {
        notificacion('error', 'Error', err[0])
      })
    } else {
      notificacion('error', 'Error', 'No se pudo guardar la tarifa')
    }
  } finally {
    guardando.value = false
  }
}

const cambiarEstado = async (item) => {
  const nuevoEstado = !item.estado
  try {
    await axios.post(`cambiar-estado-tarifa/${item.id}`, {
      estado: nuevoEstado
    })
    item.estado = nuevoEstado
    notificacion('success', 'Estado actualizado', `Tarifa ${nuevoEstado ? 'activada' : 'desactivada'}`)
  } catch {
    notificacion('error', 'Error', 'No se pudo cambiar el estado')
  }
}

const eliminar = async (item) => {
  try {
    const res = await axios.get(`eliminar-tarifa/${item.id}`)
    notificacion('warning', res.data.titulo, res.data.mensaje)
    getTarifas()
  } catch {
    notificacion('error', 'Error', 'No se pudo eliminar la tarifa')
  }
}

const cancelar = () => {
  visible.value = false
}

const resetTarifa = () => {
  tarifa.value = {
    id: null,
    concepto: '',
    monto: null,
    moneda: 'PEN',
    id_proceso: null,
    id_programa: null,
    id_modalidad: null,
    estado: true
  }
  formRef.value?.resetFields()
}

const notificacion = (type, titulo, mensaje) => {
  notification[type]({ message: titulo, description: mensaje, placement: 'topRight' })
}

watch(buscar, () => {
  clearTimeout(timeoutId)
  timeoutId = setTimeout(() => {
    getTarifas()
  }, 500)
})

let timeoutId

getTarifas()
getSelects()
</script>

<style scoped>
.tar-container {
  background: var(--card-bg, #ffffff);
  border: 1px solid var(--card-border, #e2e8f0);
  color: var(--card-text, #1e293b);
  border-radius: 8px;
  padding: 16px;
  overflow: hidden;
}
:deep(.ant-btn-primary) {
  background: #2563eb !important;
  border-color: #2563eb !important;
}
:deep(.ant-btn-primary:hover) {
  background: #1d4ed8 !important;
  border-color: #1d4ed8 !important;
}
:deep(.ant-input:focus),
:deep(.ant-input-focused),
:deep(.ant-input-affix-wrapper:focus),
:deep(.ant-input-affix-wrapper-focused) {
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1) !important;
}
:deep(.ant-pagination .ant-pagination-item-active) {
  border-color: #2563eb !important;
}
:deep(.ant-pagination .ant-pagination-item-active a) {
  color: #2563eb !important;
}
</style>

<style>
.theme-dark .tar-container,
.theme-hybrid .tar-container {
  background: var(--card-bg) !important;
  border-color: var(--card-border) !important;
}
.theme-dark .ant-table,
.theme-hybrid .ant-table {
  background: transparent !important;
  color: var(--card-text) !important;
}
.theme-dark .ant-table-thead > tr > th,
.theme-hybrid .ant-table-thead > tr > th {
  background: var(--table-header-bg) !important;
  color: var(--card-text) !important;
  border-bottom: 1px solid var(--card-border) !important;
}
.theme-dark .ant-table-tbody > tr > td,
.theme-hybrid .ant-table-tbody > tr > td {
  color: var(--card-text) !important;
  border-bottom: 1px solid var(--card-border) !important;
  background: var(--card-bg) !important;
}
.theme-dark .ant-table-tbody > tr:hover > td,
.theme-hybrid .ant-table-tbody > tr:hover > td {
  background: var(--hover-bg) !important;
}
.theme-dark .ant-table-tbody > tr:nth-child(even) > td,
.theme-hybrid .ant-table-tbody > tr:nth-child(even) > td {
  background: var(--row-even) !important;
}
</style>
