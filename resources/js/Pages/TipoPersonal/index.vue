<template>
  <Head title="Tipo de Personal" />

  <AuthenticatedLayout>
    <div class="tp-container" style="height: calc(100vh - 100px);">

      <div class="flex flex-col sm:flex-row justify-between mb-6 gap-4">
        <a-button
          type="primary"
          style="background: #2563eb; border: none; border-radius: 6px;"
          @click="showModal"
        >
          <template #icon><PlusOutlined /></template>
          Nuevo Tipo
        </a-button>

        <a-input-search
          v-model:value="buscar"
          placeholder="Buscar tipos de personal..."
          class="max-w-full sm:max-w-xs"
          @search="getTipos"
        >
          <template #prefix>
            <SearchOutlined />
          </template>
        </a-input-search>
      </div>

      <div class="overflow-x-auto">
        <a-table
          :columns="columns"
          :data-source="tipos"
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
    :title="tipo.id ? 'Editar Tipo de Personal' : 'Nuevo Tipo de Personal'"
    width="90%"
    :style="{ maxWidth: '500px' }"
    :footer="null"
    destroy-on-close
  >
    <a-form
      ref="formRef"
      :model="tipo"
      :rules="rules"
      layout="vertical"
    >
      <a-form-item label="Nombre" name="nombre">
        <a-input
          v-model:value="tipo.nombre"
          placeholder="Nombre del tipo de personal"
          allow-clear
        />
      </a-form-item>

      <a-form-item label="Descripción" name="descripcion">
        <a-textarea
          v-model:value="tipo.descripcion"
          placeholder="Descripción del tipo de personal"
          :rows="3"
          allow-clear
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
          {{ tipo.id ? 'Actualizar' : 'Guardar' }}
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
  PlusOutlined
} from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const buscar = ref('')
const tipos = ref([])
const visible = ref(false)
const loading = ref(false)
const guardando = ref(false)
const formRef = ref(null)

const tipo = ref({
  id: null,
  nombre: '',
  descripcion: '',
})

const rules = {
  nombre: [{ required: true, message: 'Ingrese el nombre' }],
}

const columns = [
  { title: 'Nombre', dataIndex: 'nombre', key: 'nombre', ellipsis: true },
  { title: 'Descripción', dataIndex: 'descripcion', key: 'descripcion', ellipsis: true, responsive: ['md'] },
  { title: 'Acciones', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 120, fixed: 'right' },
]

const getTipos = async () => {
  loading.value = true
  try {
    const res = await axios.post('tipo-personal/get-tipos', {
      term: buscar.value,
    })
    tipos.value = res.data.datos.data
  } catch {
    notificacion('error', 'Error', 'No se pudieron cargar los tipos de personal')
  } finally {
    loading.value = false
  }
}

const showModal = () => {
  resetTipo()
  visible.value = true
}

const abrirEditar = (item) => {
  tipo.value = {
    id: item.id,
    nombre: item.nombre,
    descripcion: item.descripcion,
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
    const res = await axios.post('save-tipo-personal', {
      id: tipo.value.id,
      nombre: tipo.value.nombre,
      descripcion: tipo.value.descripcion,
    })
    notificacion('success', res.data.titulo, res.data.mensaje)
    getTipos()
    visible.value = false
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.values(error.response.data.errors).forEach(err => {
        notificacion('error', 'Error', err[0])
      })
    } else {
      notificacion('error', 'Error', 'No se pudo guardar el tipo de personal')
    }
  } finally {
    guardando.value = false
  }
}

const eliminar = async (item) => {
  try {
    const res = await axios.get(`eliminar-tipo-personal/${item.id}`)
    notificacion('warning', res.data.titulo, res.data.mensaje)
    getTipos()
  } catch {
    notificacion('error', 'Error', 'No se pudo eliminar el tipo de personal')
  }
}

const cancelar = () => {
  visible.value = false
}

const resetTipo = () => {
  tipo.value = {
    id: null,
    nombre: '',
    descripcion: '',
  }
  formRef.value?.resetFields()
}

const notificacion = (type, titulo, mensaje) => {
  notification[type]({ message: titulo, description: mensaje, placement: 'topRight' })
}

let timeoutId
watch(buscar, () => {
  clearTimeout(timeoutId)
  timeoutId = setTimeout(() => {
    getTipos()
  }, 500)
})

getTipos()
</script>

<style scoped>
.tp-container {
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
.theme-dark .tp-container,
.theme-hybrid .tp-container {
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
