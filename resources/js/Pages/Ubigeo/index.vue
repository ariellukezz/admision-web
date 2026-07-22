<template>
  <Head title="Gestión de Ubigeos" />
  <AuthenticatedLayout>
    <div class="ubi-container">

      <div class="ubi-toolbar">
        <a-button type="primary" class="ubi-btn-new" @click="abrirNuevo">
          <template #icon><PlusOutlined /></template>
          Nuevo Ubigeo
        </a-button>

        <a-input-search
          v-model:value="buscar"
          placeholder="Buscar por código, departamento, provincia o distrito..."
          class="ubi-search"
          @search="getUbigeos"
          allow-clear
        >
          <template #prefix><SearchOutlined /></template>
        </a-input-search>
      </div>

      <div class="ubi-table-card">
        <a-table
          :columns="columns"
          :data-source="ubigeos"
          :pagination="{ pageSize: 50, total: totalRegistros, current: pagina }"
          size="small"
          :loading="loading"
          :scroll="{ y: 'calc(100vh - 340px)' }"
          row-key="id"
          @change="onTableChange"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'ubigeo'">
              <a-tag color="blue">{{ record.ubigeo }}</a-tag>
            </template>
            <template v-if="column.dataIndex === 'acciones'">
              <a-space size="small">
                <a-tooltip title="Editar">
                  <a-button size="small" type="text" class="ubi-action-btn" @click="abrirEditar(record)">
                    <template #icon><EditOutlined /></template>
                  </a-button>
                </a-tooltip>
                <a-popconfirm title="¿Eliminar este ubigeo?" @confirm="eliminar(record)">
                  <a-tooltip title="Eliminar">
                    <a-button size="small" type="text" danger>
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
    :title="form.id ? 'Editar Ubigeo' : 'Nuevo Ubigeo'"
    width="90%"
    :style="{ maxWidth: '520px' }"
    :footer="null"
    destroy-on-close
  >
    <a-form :model="form" layout="vertical">
      <a-form-item label="Código ubigeo" required>
        <a-input v-model:value="form.ubigeo" placeholder="Ej: 210101" :maxlength="6" />
      </a-form-item>
      <a-form-item label="Departamento">
        <a-select
          v-model:value="form.id_departamento"
          placeholder="Seleccionar departamento"
          allow-clear
          show-search
          :options="departamentos"
          :filter-option="(input, option) => option.label.toLowerCase().includes(input.toLowerCase())"
          @change="onDepartamentoChange"
        />
      </a-form-item>
      <a-form-item label="Provincia">
        <a-select
          v-model:value="form.id_provincia"
          placeholder="Seleccionar provincia"
          allow-clear
          show-search
          :options="provincias"
          :disabled="!form.id_departamento"
          :filter-option="(input, option) => option.label.toLowerCase().includes(input.toLowerCase())"
          @change="onProvinciaChange"
        />
      </a-form-item>
      <a-form-item label="Distrito">
        <a-select
          v-model:value="form.id_distrito"
          placeholder="Seleccionar distrito"
          allow-clear
          show-search
          :options="distritos"
          :disabled="!form.id_provincia"
          :filter-option="(input, option) => option.label.toLowerCase().includes(input.toLowerCase())"
        />
      </a-form-item>
      <div class="ubi-modal-footer">
        <a-button @click="visible = false">Cancelar</a-button>
        <a-button type="primary" :loading="guardando" @click="guardar">Guardar</a-button>
      </div>
    </a-form>
  </a-modal>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import { PlusOutlined, SearchOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const buscar = ref('')
const ubigeos = ref([])
const loading = ref(false)
const visible = ref(false)
const guardando = ref(false)
const pagina = ref(1)
const totalRegistros = ref(0)

const form = ref({
  id: null,
  ubigeo: '',
  id_departamento: null,
  id_provincia: null,
  id_distrito: null,
})

const departamentos = ref([])
const provincias = ref([])
const distritos = ref([])

const getUbigeos = async () => {
  loading.value = true
  try {
    const res = await axios.post('ubigeos/get-ubigeos', { term: buscar.value })
    ubigeos.value = res.data.datos.data
    totalRegistros.value = res.data.datos.total
  } catch {
    notify('error', 'Error', 'No se pudieron cargar los ubigeos')
  } finally {
    loading.value = false
  }
}

const cargarDepartamentos = async () => {
  try {
    const res = await axios.get('ubigeos/departamentos')
    departamentos.value = res.data.datos.map(d => ({ value: d.id, label: d.nombre }))
  } catch {
    console.error('Error al cargar departamentos')
  }
}

const onDepartamentoChange = async (val) => {
  form.value.id_provincia = null
  form.value.id_distrito = null
  provincias.value = []
  distritos.value = []
  if (val) {
    const res = await axios.get(`ubigeos/provincias/${val}`)
    provincias.value = res.data.datos.map(p => ({ value: p.id, label: p.nombre }))
  }
}

const onProvinciaChange = async (val) => {
  form.value.id_distrito = null
  distritos.value = []
  if (val) {
    const res = await axios.get(`ubigeos/distritos/${val}`)
    distritos.value = res.data.datos.map(d => ({ value: d.id, label: d.nombre }))
  }
}

const abrirNuevo = () => {
  form.value = { id: null, ubigeo: '', id_departamento: null, id_provincia: null, id_distrito: null }
  provincias.value = []
  distritos.value = []
  visible.value = true
}

const abrirEditar = (record) => {
  form.value = { ...record }
  visible.value = true
  if (record.id_departamento) onDepartamentoChange(record.id_departamento).then(() => {
    if (record.id_provincia) onProvinciaChange(record.id_provincia)
  })
}

const guardar = async () => {
  if (!form.value.ubigeo) {
    notify('warning', 'Validación', 'El código ubigeo es obligatorio')
    return
  }
  guardando.value = true
  try {
    const res = await axios.post('ubigeos/save', form.value)
    notify('success', res.data.titulo, res.data.mensaje)
    visible.value = false
    getUbigeos()
  } catch (e) {
    const msg = e.response?.data?.mensaje || 'No se pudo guardar'
    notify('error', 'Error', msg)
  } finally {
    guardando.value = false
  }
}

const eliminar = async (record) => {
  try {
    const res = await axios.get(`ubigeos/eliminar/${record.id}`)
    notify('warning', res.data.titulo, res.data.mensaje)
    getUbigeos()
  } catch {
    notify('error', 'Error', 'No se pudo eliminar')
  }
}

const onTableChange = (pag) => {
  pagina.value = pag.current
}

const notify = (type, titulo, mensaje) => {
  notification[type]({ message: titulo, description: mensaje, placement: 'topRight' })
}

watch(buscar, () => { getUbigeos() })

const columns = [
  { title: 'Código', dataIndex: 'ubigeo', key: 'ubigeo', align: 'center', width: 120 },
  { title: 'Departamento', dataIndex: 'departamento', key: 'departamento', ellipsis: true },
  { title: 'Provincia', dataIndex: 'provincia', key: 'provincia', ellipsis: true },
  { title: 'Distrito', dataIndex: 'distrito', key: 'distrito', ellipsis: true },
  { title: 'Acciones', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 100, fixed: 'right' },
]

getUbigeos()
cargarDepartamentos()
</script>

<style scoped>
.ubi-container {
  background: var(--content-bg, #f1f5f9);
  padding: 16px;
  height: calc(100vh - 98px);
  overflow-y: auto;
  border-radius: 8px;
}
.ubi-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  flex-wrap: wrap;
  gap: 8px;
}
.ubi-btn-new {
  background: #2563eb;
  border: none;
  border-radius: 6px;
}
.ubi-search {
  max-width: 400px;
}
.ubi-table-card {
  background: var(--card-bg, #ffffff);
  border-radius: 8px;
  border: 1px solid var(--card-border, #e2e8f0);
  padding: 8px;
}
.ubi-action-btn {
  color: var(--primary-color, #3b82f6);
}
.ubi-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 16px;
  border-top: 1px solid var(--card-border, #f0f0f0);
}
</style>

<style>
.theme-dark .ubi-container,
.theme-hybrid .ubi-container {
  background: var(--content-bg) !important;
}
.theme-dark .ubi-table-card,
.theme-hybrid .ubi-table-card {
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
