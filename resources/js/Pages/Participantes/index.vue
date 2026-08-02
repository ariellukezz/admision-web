<template>
  <Head title="Participantes" />

  <AuthenticatedLayout>
    <div class="part-container" style="height: calc(100vh - 100px);">

      <div class="flex flex-col sm:flex-row justify-between mb-4 gap-3">
        <div class="flex flex-wrap items-center gap-2">
          <a-button
            type="primary"
            style="background: #2563eb; border: none; border-radius: 6px;"
            @click="showModal"
          >
            <template #icon><PlusOutlined /></template>
            Nuevo Participante
          </a-button>

          <a-select
            v-model:value="filtroTipo"
            placeholder="Tipo de personal"
            allow-clear
            style="min-width: 180px;"
            :options="props.tipos"
            @change="resetYBuscar"
          />

          <a-select
            v-model:value="filtroCondicion"
            placeholder="Condición"
            allow-clear
            style="min-width: 160px;"
            @change="resetYBuscar"
          >
            <a-select-option value="NOMBRADO">NOMBRADO</a-select-option>
            <a-select-option value="CONTRATADO">CONTRATADO</a-select-option>
            <a-select-option value="CAS">CAS</a-select-option>
            <a-select-option value="LOCACIÓN">LOCACIÓN</a-select-option>
            <a-select-option value="R.H.">R.H.</a-select-option>
          </a-select>
        </div>

        <a-input-search
          v-model:value="buscar"
          placeholder="Buscar por DNI, nombre o código..."
          class="max-w-full sm:max-w-xs"
          @search="resetYBuscar"
        >
          <template #prefix>
            <SearchOutlined />
          </template>
        </a-input-search>
      </div>

      <div class="overflow-x-auto">
        <a-table
          :columns="columns"
          :data-source="participantes"
          :pagination="false"
          size="small"
          :loading="loading"
          :scroll="{ x: 'max-content', y: 'calc(100vh - 320px)' }"
          row-key="id"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'foto'">
              <a-avatar
                v-if="record.foto"
                :src="baseUrl + record.foto"
                :size="40"
              />
              <a-avatar v-else :size="40" style="background-color: #cbd5e1;">
                <template #icon><UserOutlined /></template>
              </a-avatar>
            </template>

            <template v-if="column.dataIndex === 'nombre_completo'">
              <span class="font-medium">{{ record.paterno }} {{ record.materno }}, {{ record.nombres }}</span>
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

      <div class="mt-3 flex flex-wrap items-center justify-end gap-2">
        <a-select v-model:value="pageSize" style="width: 90px;" @change="resetYBuscar">
          <a-select-option :value="10">10</a-select-option>
          <a-select-option :value="20">20</a-select-option>
          <a-select-option :value="50">50</a-select-option>
          <a-select-option :value="100">100</a-select-option>
        </a-select>
        <a-pagination
          v-model:current="pagina"
          :total="totalRegistros"
          v-model:pageSize="pageSize"
          show-size-changer={false}
          show-less-items
          @change="getParticipantes"
        />
      </div>

    </div>
  </AuthenticatedLayout>

  <a-modal
    v-model:open="visible"
    centered
    :title="participante.id ? 'Editar Participante' : 'Nuevo Participante'"
    width="90%"
    :style="{ maxWidth: '640px' }"
    :footer="null"
    destroy-on-close
  >
    <a-form
      ref="formRef"
      :model="participante"
      :rules="rules"
      layout="vertical"
    >
      <div class="flex gap-4 mb-4">
        <div class="flex-shrink-0 flex flex-col items-center gap-2">
          <a-avatar
            v-if="participante.foto"
            :src="baseUrl + participante.foto"
            :size="80"
          />
          <a-avatar v-else :size="80" style="background-color: #cbd5e1;">
            <template #icon><UserOutlined /></template>
          </a-avatar>
          <a-upload
            :show-upload-list="false"
            :before-upload="beforeUploadFoto"
            accept="image/*"
          >
            <a-button size="small" :loading="subiendoFoto">
              <template #icon><CameraOutlined /></template>
              {{ participante.foto ? 'Cambiar' : 'Subir' }}
            </a-button>
          </a-upload>
          <a-button v-if="participante.foto" size="small" danger type="text" @click="quitarFoto">
            Quitar
          </a-button>
        </div>
        <div class="flex-1">
          <a-form-item label="DNI" name="dni" :rules="[{ required: true, message: 'Ingrese el DNI' }, { min: 8, message: 'Mínimo 8 dígitos' }]">
            <a-input
              v-model:value="participante.dni"
              :maxlength="8"
              placeholder="Ingrese DNI"
              @change="onDniChange"
            >
              <template #suffix>
                <a-button
                  v-if="participante.dni && participante.dni.length === 8"
                  type="link"
                  size="small"
                  :loading="buscandoReniec"
                  @click="buscarReniec"
                >
                  Buscar RENIEC
                </a-button>
              </template>
            </a-input>
          </a-form-item>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a-form-item label="Nombres" name="nombres" :rules="[{ required: true, message: 'Ingrese los nombres' }]">
          <a-input v-model:value="participante.nombres" placeholder="Nombres" />
        </a-form-item>

        <a-form-item label="Apellido Paterno" name="paterno">
          <a-input v-model:value="participante.paterno" placeholder="Apellido paterno" />
        </a-form-item>

        <a-form-item label="Apellido Materno" name="materno">
          <a-input v-model:value="participante.materno" placeholder="Apellido materno" />
        </a-form-item>

        <a-form-item label="Código de Trabajador" name="codigo_trabajador">
          <a-input v-model:value="participante.codigo_trabajador" placeholder="Código de trabajador" />
        </a-form-item>

        <a-form-item label="Tipo de Personal" name="id_tipo_personal">
          <TipoPersonalSelect v-model:value="participante.id_tipo_personal" :initial-options="props.tipos" />
        </a-form-item>

        <a-form-item label="Condición" name="condicion">
          <a-select
            v-model:value="participante.condicion"
            placeholder="Seleccionar condición"
            allow-clear
          >
            <a-select-option value="NOMBRADO">NOMBRADO</a-select-option>
            <a-select-option value="CONTRATADO">CONTRATADO</a-select-option>
            <a-select-option value="CAS">CAS</a-select-option>
            <a-select-option value="LOCACIÓN">LOCACIÓN</a-select-option>
            <a-select-option value="R.H.">R.H.</a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="Dependencia" name="dependencia">
          <a-input v-model:value="participante.dependencia" placeholder="Dependencia" />
        </a-form-item>

        <a-form-item v-if="participante.id" label="Estado" name="estado">
          <a-switch
            v-model:checked="participante.estado"
            checked-children="Activo"
            un-checked-children="Inactivo"
          />
        </a-form-item>
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t">
        <a-button @click="cancelar">Cancelar</a-button>
        <a-button
          type="primary"
          style="background: #2563eb; border: none; border-radius: 6px;"
          :loading="guardando"
          @click="guardar"
        >
          {{ participante.id ? 'Actualizar' : 'Guardar' }}
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
  UserOutlined,
  CameraOutlined
} from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'
import TipoPersonalSelect from './components/TipoPersonalSelect.vue'

const props = defineProps({
  tipos: { type: Array, default: () => [] },
})

const baseUrl = window.location.origin
const buscar = ref('')
const participantes = ref([])
const visible = ref(false)
const loading = ref(false)
const guardando = ref(false)
const buscandoReniec = ref(false)
const subiendoFoto = ref(false)
const formRef = ref(null)
const pagina = ref(1)
const pageSize = ref(50)
const totalRegistros = ref(0)
const filtroTipo = ref(null)
const filtroCondicion = ref(null)

const participante = ref({
  id: null,
  dni: '',
  nombres: '',
  paterno: '',
  materno: '',
  id_tipo_personal: null,
  codigo_trabajador: '',
  condicion: null,
  dependencia: null,
  foto: null,
  estado: true,
})

const rules = {
  dni: [{ required: true, message: 'Ingrese el DNI' }],
  nombres: [{ required: true, message: 'Ingrese los nombres' }],
}

const columns = [
  { title: '', dataIndex: 'foto', key: 'foto', align: 'center', width: 60 },
  { title: 'DNI', dataIndex: 'dni', key: 'dni', width: 100 },
  { title: 'Nombre Completo', dataIndex: 'nombre_completo', key: 'nombre_completo', ellipsis: true },
  { title: 'Tipo Personal', dataIndex: 'tipo_personal', key: 'tipo_personal', ellipsis: true, responsive: ['md'] },
  { title: 'Condición', dataIndex: 'condicion', key: 'condicion', ellipsis: true, responsive: ['md'] },
  { title: 'Dependencia', dataIndex: 'dependencia', key: 'dependencia', ellipsis: true, responsive: ['lg'] },
  { title: 'Código', dataIndex: 'codigo_trabajador', key: 'codigo_trabajador', width: 120, responsive: ['sm'] },
  { title: 'Estado', dataIndex: 'estado', key: 'estado', align: 'center', width: 110 },
  { title: 'Acciones', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 120, fixed: 'right' },
]

const getParticipantes = async () => {
  loading.value = true
  try {
    const res = await axios.post(`participantes/get-participantes?page=${pagina.value}`, {
      term: buscar.value,
      pageSize: pageSize.value,
      id_tipo_personal: filtroTipo.value,
      condicion: filtroCondicion.value,
    })
    participantes.value = res.data.datos.data
    totalRegistros.value = res.data.datos.total
  } catch {
    notificacion('error', 'Error', 'No se pudieron cargar los participantes')
  } finally {
    loading.value = false
  }
}

const resetYBuscar = () => {
  pagina.value = 1
  getParticipantes()
}

const onDniChange = () => {
  participante.value.dni = participante.value.dni.replace(/\D/g, '')
}

const buscarReniec = async () => {
  if (!participante.value.dni || participante.value.dni.length !== 8) return

  buscandoReniec.value = true
  try {
    const res = await axios.get(`participantes/buscar-reniec/${participante.value.dni}`)
    if (res.data.estado === true) {
      participante.value.nombres = res.data.nombres || participante.value.nombres
      participante.value.paterno = res.data.paterno || participante.value.paterno
      participante.value.materno = res.data.materno || participante.value.materno
      if (res.data.foto) {
        participante.value.foto = res.data.foto + '?v=' + Date.now()
      }
      notificacion('success', 'RENIEC', 'Datos cargados correctamente')
    } else {
      notificacion('warning', 'RENIEC', res.data.mensaje || 'No se encontraron datos')
    }
  } catch {
    notificacion('error', 'Error', 'No se pudo consultar RENIEC')
  } finally {
    buscandoReniec.value = false
  }
}

const beforeUploadFoto = async (file) => {
  subiendoFoto.value = true
  try {
    const fd = new FormData()
    fd.append('foto', file)
    if (participante.value.dni) {
      fd.append('dni', participante.value.dni)
    }
    const res = await axios.post('participantes/subir-foto', fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if (res.data.estado === true) {
      participante.value.foto = res.data.foto
      notificacion('success', 'Foto', 'Foto subida correctamente')
    } else {
      notificacion('error', 'Error', res.data.mensaje || 'No se pudo subir la foto')
    }
  } catch {
    notificacion('error', 'Error', 'No se pudo subir la foto')
  } finally {
    subiendoFoto.value = false
  }
  return false
}

const quitarFoto = () => {
  participante.value.foto = null
}

const showModal = () => {
  resetParticipante()
  visible.value = true
}

const abrirEditar = (item) => {
  participante.value = {
    id: item.id,
    dni: item.dni,
    nombres: item.nombres,
    paterno: item.paterno,
    materno: item.materno,
    id_tipo_personal: item.id_tipo_personal,
    codigo_trabajador: item.codigo_trabajador,
    condicion: item.condicion,
    dependencia: item.dependencia,
    foto: item.foto,
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
    const payload = { ...participante.value }
    if (payload.foto) {
      payload.foto = payload.foto.split('?')[0]
    }
    const res = await axios.post('save-participante', payload)
    notificacion('success', res.data.titulo, res.data.mensaje)
    getParticipantes()
    visible.value = false
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.values(error.response.data.errors).forEach(err => {
        notificacion('error', 'Error', err[0])
      })
    } else {
      notificacion('error', 'Error', 'No se pudo guardar el participante')
    }
  } finally {
    guardando.value = false
  }
}

const cambiarEstado = async (item) => {
  const nuevoEstado = !item.estado
  try {
    await axios.post(`cambiar-estado-participante/${item.id}`, {
      estado: nuevoEstado
    })
    item.estado = nuevoEstado
    notificacion('success', 'Estado actualizado', `Participante ${nuevoEstado ? 'activado' : 'desactivado'}`)
  } catch {
    notificacion('error', 'Error', 'No se pudo cambiar el estado')
  }
}

const eliminar = async (item) => {
  try {
    const res = await axios.get(`eliminar-participante/${item.id}`)
    notificacion('warning', res.data.titulo, res.data.mensaje)
    getParticipantes()
  } catch {
    notificacion('error', 'Error', 'No se pudo eliminar el participante')
  }
}

const cancelar = () => {
  visible.value = false
}

const resetParticipante = () => {
  participante.value = {
    id: null,
    dni: '',
    nombres: '',
    paterno: '',
    materno: '',
    id_tipo_personal: null,
    codigo_trabajador: '',
    condicion: null,
    dependencia: null,
    foto: null,
    estado: true,
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
    resetYBuscar()
  }, 500)
})

getParticipantes()
</script>

<style scoped>
.part-container {
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
.theme-dark .part-container,
.theme-hybrid .part-container {
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
