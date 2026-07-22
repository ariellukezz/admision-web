<template>
  <Head title="Años" />

  <AuthenticatedLayout>
    <div class="anio-container" style="height: calc(100vh - 100px);">

      <div class="flex flex-col sm:flex-row justify-between mb-6 gap-4">
        <a-button
          type="primary"
          style="background: #2563eb; border: none; border-radius: 6px;"
          @click="showModal"
        >
          <template #icon>
            <PlusOutlined />
          </template>
          Nuevo
        </a-button>

        <a-input-search
          v-model:value="buscar"
          placeholder="Buscar..."
          class="max-w-full sm:max-w-xs"
          @search="getAnios"
        >
          <template #prefix>
            <SearchOutlined />
          </template>
        </a-input-search>
      </div>

      <div class="overflow-x-auto">
        <a-table
          :columns="columns"
          :data-source="anios"
          :pagination="false"
          size="small"
          :loading="loading"
          row-key="id"
          :scroll="{ y: 'calc(100vh - 320px)' }"
        >
          <template #bodyCell="{ column, record }">
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

                <a-popconfirm
                  title="¿Estás seguro de eliminar?"
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

      <div class="mt-2 text-right">
        <a-pagination
          v-model:current="pagina"
          :total="totalRegistros"
          show-less-items
        />
      </div>

    </div>
  </AuthenticatedLayout>

  <a-modal
    v-model:open="visible"
    :title="anio.id ? 'Editar Año' : 'Nuevo Año'"
    centered
    width="90%"
    :style="{ maxWidth: '500px' }"
    :footer="null"
    destroy-on-close
  >
    <a-form
      ref="formRef"
      :model="anio"
      layout="vertical"
    >
      <a-form-item label="Año" name="anio" required>
        <a-input v-model:value="anio.anio" placeholder="Ej: 2026" allow-clear />
      </a-form-item>

      <a-form-item label="Nombre" name="nombre" required>
        <a-input v-model:value="anio.nombre" placeholder="Nombre del año" allow-clear />
      </a-form-item>

      <div class="flex justify-end gap-3 pt-4 border-t">
        <a-button @click="cancelar">Cancelar</a-button>
        <a-button
          type="primary"
          style="background: #2563eb; border: none; border-radius: 6px;"
          :loading="guardando"
          @click="guardar"
        >
          {{ anio.id ? 'Actualizar' : 'Guardar' }}
        </a-button>
      </div>
    </a-form>
  </a-modal>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import { EditOutlined, DeleteOutlined, SearchOutlined, PlusOutlined } from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const buscar = ref('')
const anios = ref([])
const visible = ref(false)
const pagina = ref(1)
const totalRegistros = ref(0)
const loading = ref(false)
const guardando = ref(false)

const anio = ref({
  id: null,
  anio: '',
  nombre: ''
})

const formRef = ref(null)

const getAnios = async () => {
  loading.value = true
  try {
    const res = await axios.post(
      `anio/get-anios?page=${pagina.value}`,
      { term: buscar.value }
    )
    anios.value = res.data.datos.data
    totalRegistros.value = res.data.datos.total
  } finally {
    loading.value = false
  }
}

const showModal = () => {
  visible.value = true
}

const abrirEditar = item => {
  anio.value = { ...item }
  visible.value = true
}

const guardar = async () => {
  guardando.value = true
  try {
    const payload = {
      ...anio.value,
      anio: String(anio.value.anio)
    }

    const res = await axios.post('save-anio', payload)
    notificacion('success', res.data.titulo, res.data.mensaje)
    getAnios()
    visible.value = false
  } finally {
    guardando.value = false
  }
}

const eliminar = async item => {
  const res = await axios.get(`eliminar-anio/${item.id}`)
  notificacion('warning', res.data.titulo, res.data.mensaje)
  getAnios()
}

const cancelar = () => {
  visible.value = false
}

const limpiar = () => {
  anio.value = { id: null, anio: '', nombre: '' }
}

const notificacion = (type, titulo, mensaje) => {
  notification[type]({ message: titulo, description: mensaje, placement: 'topRight' })
}

let timeoutId
watch(buscar, () => {
  pagina.value = 1
  clearTimeout(timeoutId)
  timeoutId = setTimeout(() => getAnios(), 500)
})

watch(pagina, getAnios)

watch(visible, v => {
  if (!v) limpiar()
})

const columns = [
  { title: 'Año', dataIndex: 'anio', align: 'center', width: 100 },
  { title: 'Nombre', dataIndex: 'nombre' },
  { title: 'Acciones', dataIndex: 'acciones', align: 'center', width: 100, fixed: 'right' },
]

getAnios()
</script>

<style scoped>
.anio-container {
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
.theme-dark .anio-container,
.theme-hybrid .anio-container {
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
.theme-dark .ant-table-tbody > tr:hover > td {
  background: var(--hover-bg) !important;
}
.theme-hybrid .ant-table-container .ant-table-tbody > tr.ant-table-row:hover > td {
  background: rgba(0, 0, 0, 0.04) !important;
}
.theme-dark .ant-table-tbody > tr:nth-child(even) > td,
.theme-hybrid .ant-table-tbody > tr:nth-child(even) > td {
  background: var(--row-even) !important;
}
.theme-hybrid .ant-table-container .ant-table-tbody > tr:nth-child(even) > td {
  background: rgba(0, 0, 0, 0.02) !important;
}
</style>
