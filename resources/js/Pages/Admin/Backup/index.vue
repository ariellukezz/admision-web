<template>
<Head title="Respaldo de Base de Datos" />
<AuthenticatedLayout>

<div style="background: var(--content-bg, #f1f5f9); min-height: calc(100vh - 98px); padding-bottom: 24px;">

  <!-- HEADER -->
  <div class="rounded-xl shadow-md p-6 mb-4 border" style="background: var(--card-bg, #ffffff); border-color: var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-[#2d3748] flex items-center gap-3">
          <DatabaseOutlined style="font-size: 28px;" />
          Respaldo de Base de Datos
        </h1>
        <p class="text-gray-500 mt-1 text-sm">Gestión de respaldos y restauración del sistema</p>
      </div>
      <div class="flex gap-2">
        <a-button
          type="primary"
          size="large"
          :loading="creando"
          @click="crearRespaldo(false)"
          class="flex items-center gap-2"
          style="border-radius: 8px; background: #2d3748; border-color: #2d3748;"
        >
          <FileOutlined />
          Crear Respaldo SQL
        </a-button>
        <a-button
          type="primary"
          size="large"
          :loading="creandoComprimido"
          @click="crearRespaldo(true)"
          class="flex items-center gap-2"
          style="border-radius: 8px; background: #3b82f6; border-color: #3b82f6;"
        >
          <CompressOutlined />
          Crear Respaldo Comprimido
        </a-button>
      </div>
    </div>
  </div>

  <!-- INFO BD -->
  <div class="mb-4">
    <a-row :gutter="[16, 12]">
      <a-col :xs="24" :sm="12" :md="6">
        <div class="rounded-xl shadow-sm p-5 border" style="background: var(--card-bg, #ffffff); border-color: var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Base de datos</p>
              <p class="text-lg font-bold text-gray-800 mt-1">{{ dbInfo.database || '-' }}</p>
            </div>
            <div class="p-2 rounded-lg" style="background: #dbeafe;">
              <DatabaseOutlined style="color: #3b82f6; font-size: 20px;" />
            </div>
          </div>
        </div>
      </a-col>
      <a-col :xs="24" :sm="12" :md="6">
        <div class="rounded-xl shadow-sm p-5 border" style="background: var(--card-bg, #ffffff); border-color: var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Tamaño BD</p>
              <p class="text-lg font-bold text-gray-800 mt-1">{{ dbInfo.size_mb }} MB</p>
            </div>
            <div class="p-2 rounded-lg" style="background: #dcfce7;">
              <CloudOutlined style="color: #22c55e; font-size: 20px;" />
            </div>
          </div>
        </div>
      </a-col>
      <a-col :xs="24" :sm="12" :md="6">
        <div class="rounded-xl shadow-sm p-5 border" style="background: var(--card-bg, #ffffff); border-color: var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Tablas</p>
              <p class="text-lg font-bold text-gray-800 mt-1">{{ dbInfo.tables }}</p>
            </div>
            <div class="p-2 rounded-lg" style="background: #fef3c7;">
              <TableOutlined style="color: #f59e0b; font-size: 20px;" />
            </div>
          </div>
        </div>
      </a-col>
      <a-col :xs="24" :sm="12" :md="6">
        <div class="rounded-xl shadow-sm p-5 border" style="background: var(--card-bg, #ffffff); border-color: var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Espacio en disco</p>
              <p class="text-lg font-bold text-gray-800 mt-1">{{ diskFree }}</p>
              <p class="text-xs text-gray-400">de {{ diskTotal }}</p>
            </div>
            <div class="p-2 rounded-lg" style="background: #f3e8ff;">
              <HddOutlined style="color: #a855f7; font-size: 20px;" />
            </div>
          </div>
        </div>
      </a-col>
    </a-row>
  </div>

  <!-- TABLA RESPALDOS -->
  <div class="rounded-xl shadow-md border" style="background: var(--card-bg, #ffffff); border-color: var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">
    <div class="p-5 border-b border-gray-100">
      <div class="flex justify-between items-center">
        <h2 class="text-lg font-bold text-gray-800">Historial de Respaldos</h2>
        <a-badge :count="backups.length" :number-style="{ backgroundColor: '#3b82f6' }" />
      </div>
    </div>

    <a-table
      :columns="columns"
      :data-source="backups"
      :loading="loading"
      :pagination="{ pageSize: 10, showTotal: (total) => `${total} respaldos` }"
      row-key="file"
      size="middle"
      :scroll="{ x: 800, y: 'calc(100vh - 320px)' }"
    >
      <template #bodyCell="{ column, record }">

        <template v-if="column.key === 'archivo'">
          <div class="flex items-center gap-2">
            <component :is="record.compressed ? CompressOutlined : FileTextOutlined"
              :style="{ color: record.compressed ? '#3b82f6' : '#6b7280', fontSize: '18px' }" />
            <div>
              <div class="font-semibold text-gray-800 text-sm">{{ record.file }}</div>
              <div class="text-xs text-gray-400">{{ record.compressed ? 'Comprimido (.gz)' : 'SQL plano' }}</div>
            </div>
          </div>
        </template>

        <template v-if="column.key === 'size'">
          <a-tag :color="getSizeColor(record.size_bytes)" class="font-semibold">
            {{ record.size }}
          </a-tag>
        </template>

        <template v-if="column.key === 'date'">
          <div class="text-sm text-gray-700">{{ record.date }}</div>
        </template>

        <template v-if="column.key === 'acciones'">
          <a-space size="small">
            <a-tooltip title="Descargar">
              <a-button
                type="link"
                size="small"
                @click="descargar(record.file)"
                class="text-blue-600"
              >
                <DownloadOutlined />
              </a-button>
            </a-tooltip>

            <a-tooltip title="Restaurar">
              <a-popconfirm
                title="¿Está seguro de restaurar la base de datos?"
                description="Se creará un respaldo de seguridad antes de restaurar. Esta acción puede tomar varios minutos."
                @confirm="restaurar(record.file)"
                ok-text="Sí, restaurar"
                cancel-text="Cancelar"
                ok-button-props="{ danger: true }"
              >
                <a-button
                  type="link"
                  size="small"
                  class="text-orange-500"
                >
                  <UndoOutlined />
                </a-button>
              </a-popconfirm>
            </a-tooltip>

            <a-tooltip title="Eliminar">
              <a-popconfirm
                title="¿Eliminar este respaldo?"
                description="Esta acción no se puede deshacer."
                @confirm="eliminar(record.file)"
                ok-text="Sí, eliminar"
                cancel-text="Cancelar"
                ok-button-props="{ danger: true }"
              >
                <a-button
                  type="link"
                  danger
                  size="small"
                >
                  <DeleteOutlined />
                </a-button>
              </a-popconfirm>
            </a-tooltip>
          </a-space>
        </template>

      </template>
    </a-table>
  </div>

</div>

</AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import axios from 'axios'
import { message, notification } from 'ant-design-vue'
import {
  DatabaseOutlined,
  FileOutlined,
  CompressOutlined,
  DownloadOutlined,
  UndoOutlined,
  DeleteOutlined,
  FileTextOutlined,
  CloudOutlined,
  TableOutlined,
  HddOutlined
} from '@ant-design/icons-vue'

const loading = ref(false)
const creando = ref(false)
const creandoComprimido = ref(false)
const backups = ref([])
const dbInfo = ref({ database: '', size_mb: 0, tables: 0, host: '' })
const diskFree = ref('-')
const diskTotal = ref('-')

const columns = [
  {
    title: 'ARCHIVO',
    key: 'archivo',
    width: 350,
    ellipsis: true,
  },
  {
    title: 'TAMAÑO',
    dataIndex: 'size',
    key: 'size',
    width: 130,
    align: 'center',
  },
  {
    title: 'FECHA',
    dataIndex: 'date',
    key: 'date',
    width: 200,
  },
  {
    title: 'ACCIONES',
    key: 'acciones',
    width: 150,
    align: 'center',
    fixed: 'right',
  },
]

const getBackups = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/admin/backup')
    if (data.success) {
      backups.value = data.backups
      dbInfo.value = data.db_info
      diskFree.value = data.disk_free
      diskTotal.value = data.disk_total
    }
  } catch (error) {
    message.error('Error al cargar los respaldos')
  } finally {
    loading.value = false
  }
}

const crearRespaldo = async (comprimir = true) => {
  if (comprimir) {
    creandoComprimido.value = true
  } else {
    creando.value = true
  }

  notification.info({
    message: 'Creando respaldo',
    description: 'El proceso puede tardar varios minutos dependiendo del tamaño de la base de datos. Por favor espere...',
    duration: 8,
  })

  try {
    const { data } = await axios.post('/admin/backup/crear', { comprimir })
    if (data.success) {
      notification.success({
        message: 'Respaldo creado',
        description: `${data.datos.file} (${data.datos.size})`,
      })
      getBackups()
    }
  } catch (error) {
    notification.error({
      message: 'Error al crear respaldo',
      description: error.response?.data?.mensaje || 'Error inesperado',
    })
  } finally {
    creando.value = false
    creandoComprimido.value = false
  }
}

const descargar = (filename) => {
  window.open(`/admin/backup/descargar/${filename}`, '_blank')
}

const restaurar = async (filename) => {
  notification.warning({
    message: 'Restaurando base de datos',
    description: 'Este proceso puede tardar varios minutos. No cierre la ventana...',
    duration: 10,
  })

  try {
    const { data } = await axios.post('/admin/backup/restaurar', { filename })
    if (data.success) {
      notification.success({
        message: 'Base de datos restaurada',
        description: data.mensaje,
        duration: 10,
      })
      getBackups()
    }
  } catch (error) {
    notification.error({
      message: 'Error al restaurar',
      description: error.response?.data?.mensaje || 'Error inesperado',
    })
  }
}

const eliminar = async (filename) => {
  try {
    const { data } = await axios.post('/admin/backup/eliminar', { filename })
    if (data.success) {
      message.success(data.mensaje)
      getBackups()
    }
  } catch (error) {
    message.error('Error al eliminar el respaldo')
  }
}

const getSizeColor = (bytes) => {
  if (bytes >= 1073741824) return 'red'
  if (bytes >= 524288000) return 'orange'
  if (bytes >= 104857600) return 'blue'
  return 'green'
}

onMounted(() => {
  getBackups()
})
</script>

<style scoped>
:deep(.ant-table-thead > tr > th) {
  background-color: var(--table-header-bg, #f8fafc);
  font-weight: 600;
  color: var(--card-text, #374151);
}

:deep(.ant-table-tbody > tr:hover > td) {
  background-color: var(--hover-bg, #eff6ff) !important;
}

:deep(.ant-btn-primary:hover) {
  opacity: 0.9;
}
</style>

<style>
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
