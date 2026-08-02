<template>
  <Head title="Cargos" />

  <AuthenticatedLayout>
    <div class="cargo-container" style="height: calc(100vh - 100px);">

      <div class="flex flex-col sm:flex-row justify-between mb-6 gap-4">
        <a-button
          type="primary"
          style="background: #2563eb; border: none; border-radius: 6px;"
          @click="showModal"
        >
          <template #icon><PlusOutlined /></template>
          Nuevo Cargo
        </a-button>

        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
          <a-input-search
            v-model:value="buscar"
            placeholder="Buscar cargos..."
            class="max-w-full sm:max-w-xs"
            @search="getCargos"
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
          :data-source="cargos"
          :pagination="{ pageSize: 50 }"
          size="small"
          :loading="loading"
          :scroll="{ x: 'max-content', y: 'calc(100vh - 280px)' }"
          row-key="id"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'descripcion'">
              <span class="text-sm text-gray-500">{{ record.descripcion || '—' }}</span>
            </template>

            <template v-if="column.dataIndex === 'url'">
              <a
                v-if="record.url"
                :href="record.url"
                target="_blank"
                class="text-blue-600 hover:text-blue-800"
              >
                <LinkOutlined /> Ver documento
              </a>
              <span v-else class="text-gray-400">—</span>
            </template>

            <template v-if="column.dataIndex === 'estado'">
              <a-tag :color="record.estado ? 'green' : 'red'">
                {{ record.estado ? 'ACTIVO' : 'INACTIVO' }}
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
    :title="cargo.id ? 'Editar Cargo' : 'Nuevo Cargo'"
    width="90%"
    :style="{ maxWidth: '600px' }"
    :footer="null"
    destroy-on-close
  >
    <a-form
      ref="formRef"
      :model="cargo"
      :rules="rules"
      layout="vertical"
    >
      <a-form-item label="Nombre" name="nombre">
        <a-input
          v-model:value="cargo.nombre"
          placeholder="Nombre del cargo"
          allow-clear
        />
      </a-form-item>

      <a-form-item label="Descripción" name="descripcion">
        <a-textarea
          v-model:value="cargo.descripcion"
          placeholder="Descripción del cargo"
          :rows="3"
          allow-clear
        />
      </a-form-item>

      <a-form-item label="Documento (PDF)" name="file">
        <a-upload
          :before-upload="beforeUpload"
          :max-count="1"
          accept=".pdf"
          @remove="handleRemoveFile"
        >
          <a-button>
            <template #icon><UploadOutlined /></template>
            Seleccionar PDF
          </a-button>
        </a-upload>
        <div v-if="cargo.url && !fileSelected" class="mt-2 text-sm text-blue-600">
          <LinkOutlined />
          <a :href="cargo.url" target="_blank">Documento actual</a>
        </div>
      </a-form-item>

      <a-form-item v-if="cargo.id" label="Estado" name="estado">
        <a-switch
          v-model:checked="cargo.estado"
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
          {{ cargo.id ? 'Actualizar' : 'Guardar' }}
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
  PoweroffOutlined,
  UploadOutlined,
  LinkOutlined
} from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const buscar = ref('')
const cargos = ref([])
const visible = ref(false)
const loading = ref(false)
const guardando = ref(false)
const formRef = ref(null)
const fileSelected = ref(false)
const selectedFile = ref(null)

const cargo = ref({
  id: null,
  nombre: '',
  descripcion: '',
  estado: true,
  url: null,
})

const rules = {
  nombre: [{ required: true, message: 'Ingrese el nombre del cargo' }],
}

const columns = [
  { title: 'Nombre', dataIndex: 'nombre', key: 'nombre', ellipsis: true },
  { title: 'Descripción', dataIndex: 'descripcion', key: 'descripcion', ellipsis: true, responsive: ['md'] },
  { title: 'Documento', dataIndex: 'url', key: 'url', align: 'center', width: 140, responsive: ['sm'] },
  { title: 'Estado', dataIndex: 'estado', key: 'estado', align: 'center', width: 110 },
  { title: 'Acciones', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 120, fixed: 'right' },
]

const getCargos = async () => {
  loading.value = true
  try {
    const res = await axios.post('cargos/get-cargos', {
      term: buscar.value,
    })
    cargos.value = res.data.datos.data
  } catch {
    notificacion('error', 'Error', 'No se pudieron cargar los cargos')
  } finally {
    loading.value = false
  }
}

const showModal = () => {
  resetCargo()
  visible.value = true
}

const abrirEditar = (item) => {
  cargo.value = {
    id: item.id,
    nombre: item.nombre,
    descripcion: item.descripcion,
    estado: item.estado,
    url: item.url,
  }
  fileSelected.value = false
  selectedFile.value = null
  visible.value = true
}

const beforeUpload = (file) => {
  selectedFile.value = file
  fileSelected.value = true
  return false
}

const handleRemoveFile = () => {
  selectedFile.value = null
  fileSelected.value = false
}

const guardar = async () => {
  try {
    await formRef.value.validate()
  } catch {
    return
  }

  guardando.value = true
  try {
    const fd = new FormData()
    fd.append('id', cargo.value.id || '')
    fd.append('nombre', cargo.value.nombre)
    fd.append('descripcion', cargo.value.descripcion || '')
    fd.append('estado', cargo.value.estado ? 1 : 0)
    if (selectedFile.value) {
      fd.append('file', selectedFile.value)
    }

    const res = await axios.post('save-cargo', fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    notificacion('success', res.data.titulo, res.data.mensaje)
    getCargos()
    visible.value = false
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.values(error.response.data.errors).forEach(err => {
        notificacion('error', 'Error', err[0])
      })
    } else {
      notificacion('error', 'Error', 'No se pudo guardar el cargo')
    }
  } finally {
    guardando.value = false
  }
}

const cambiarEstado = async (item) => {
  const nuevoEstado = !item.estado
  try {
    await axios.post(`cambiar-estado-cargo/${item.id}`, {
      estado: nuevoEstado
    })
    item.estado = nuevoEstado
    notificacion('success', 'Estado actualizado', `Cargo ${nuevoEstado ? 'activado' : 'desactivado'}`)
  } catch {
    notificacion('error', 'Error', 'No se pudo cambiar el estado')
  }
}

const eliminar = async (item) => {
  try {
    const res = await axios.get(`eliminar-cargo/${item.id}`)
    notificacion('warning', res.data.titulo, res.data.mensaje)
    getCargos()
  } catch {
    notificacion('error', 'Error', 'No se pudo eliminar el cargo')
  }
}

const cancelar = () => {
  visible.value = false
}

const resetCargo = () => {
  cargo.value = {
    id: null,
    nombre: '',
    descripcion: '',
    estado: true,
    url: null,
  }
  fileSelected.value = false
  selectedFile.value = null
  formRef.value?.resetFields()
}

const notificacion = (type, titulo, mensaje) => {
  notification[type]({ message: titulo, description: mensaje, placement: 'topRight' })
}

let timeoutId
watch(buscar, () => {
  clearTimeout(timeoutId)
  timeoutId = setTimeout(() => {
    getCargos()
  }, 500)
})

getCargos()
</script>

<style scoped>
.cargo-container {
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
.theme-dark .cargo-container,
.theme-hybrid .cargo-container {
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
