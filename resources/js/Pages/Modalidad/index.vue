<template>
  <Head title="Modalidades" />

  <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 md:p-6" style="height: calc(100vh - 100px);">

      <div class="flex flex-col sm:flex-row justify-between mb-6 gap-4">
        <a-button
          type="primary"
          style="background: #2563eb; border: none; border-radius: 6px;"
          @click="showModalPrograma"
        >
          <template #icon>
            <PlusOutlined />
          </template>
          Nueva Modalidad
        </a-button>

        <a-input-search
          v-model:value="buscar"
          placeholder="Buscar modalidades..."
          class="max-w-full sm:max-w-xs"
          @search="getModalidades"
        >
          <template #prefix>
            <SearchOutlined />
          </template>
        </a-input-search>
      </div>

      <a-table
        :columns="columnsModalidades"
        :data-source="modalidades"
        :pagination="{ pageSize: 50 }"
        size="small"
        :loading="loading"
        :scroll="{ y: 'calc(100vh - 280px)' }"
        row-key="id"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'estado'">
            <a-tag :color="record.estado ? 'green' : 'red'">
              {{ record.estado ? 'VIGENTE' : 'NO VIGENTE' }}
            </a-tag>
          </template>

          <template v-else-if="column.dataIndex === 'acciones'">
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
  </AuthenticatedLayout>


  <a-modal
    v-model:open="visible"
    centered
    :title="modalidad.id ? 'Editar Modalidad' : 'Nueva Modalidad'"
    width="90%"
    :style="{ maxWidth: '500px' }"
    :footer="null"
    destroy-on-close
  >
    <a-form
      ref="formRef"
      :model="modalidad"
      :rules="rules"
      layout="vertical"
    >
      <a-form-item label="Código" name="codigo">
        <a-input
          v-model:value="modalidad.codigo"
          placeholder="Código de la modalidad"
          allow-clear
        />
      </a-form-item>

      <a-form-item label="Nombre" name="nombre">
        <a-input
          v-model:value="modalidad.nombre"
          placeholder="Nombre de la modalidad"
          allow-clear
        />
      </a-form-item>

      <a-form-item v-if="modalidad.id" label="Estado" name="estado">
        <a-switch
          v-model:checked="modalidad.estado"
          checked-children="Activo"
          un-checked-children="Inactivo"
        />
      </a-form-item>

      <div class="flex justify-end gap-3 pt-4 border-t">
        <a-button @click="cancelar">Cancelar</a-button>
        <a-button
          type="primary"
          html-type="submit"
          style="background: #2563eb; border: none; border-radius: 6px;"
          :loading="guardando"
          @click="guardar"
        >
          {{ modalidad.id ? 'Actualizar' : 'Guardar' }}
        </a-button>
      </div>
    </a-form>
  </a-modal>

</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import { EditOutlined, DeleteOutlined, SearchOutlined, PlusOutlined, PoweroffOutlined } from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const buscar = ref('')
const modalidades = ref([])
const visible = ref(false)
const loading = ref(false)
const guardando = ref(false)

const modalidad = ref({
  id: null,
  codigo: '',
  nombre: '',
  estado: true
})

const formRef = ref(null)
const getModalidades = async () => {
  loading.value = true
  try {
    const res = await axios.post('modalidad/get-modalidades', {
      term: buscar.value
    })
    modalidades.value = res.data.datos.data
  } catch {
    notificacion('error', 'Error', 'No se pudieron cargar las modalidades')
  } finally {
    loading.value = false
  }
}

const showModalPrograma = () => {
  visible.value = true
}

const abrirEditar = item => {
  modalidad.value = { ...item }
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
    const payload = {
      ...modalidad.value,
      codigo: String(modalidad.value.codigo),
      estado: Boolean(modalidad.value.estado)
    }

    const res = await axios.post('save-modalidad', payload)

    notificacion('success', res.data.titulo, res.data.mensaje)
    getModalidades()
    visible.value = false
  } catch {
    notificacion('error', 'Error', 'No se pudo guardar la modalidad')
  } finally {
    guardando.value = false
  }
}

const cambiarEstado = async item => {
  const nuevoEstado = !item.estado
  try {
    await axios.post(`cambiar-estado-modalidad/${item.id}`, {
      estado: nuevoEstado
    })
    item.estado = nuevoEstado
    notificacion(
      'success',
      'Estado actualizado',
      `Modalidad ${nuevoEstado ? 'activada' : 'desactivada'}`
    )
  } catch {
    notificacion('error', 'Error', 'No se pudo cambiar el estado')
  }
}

const eliminar = async item => {
  try {
    const res = await axios.get(`eliminar-modalidad/${item.id}`)
    notificacion('warning', res.data.titulo, res.data.mensaje)
    getModalidades()
  } catch {
    notificacion('error', 'Error', 'No se pudo eliminar')
  }
}

const cancelar = () => {
  visible.value = false
}

const limpiarModalidad = () => {
  modalidad.value = { id: null, codigo: '', nombre: '', estado: true }
  formRef.value?.resetFields()
}

const notificacion = (type, titulo, mensaje) => {
  notification[type]({ message: titulo, description: mensaje, placement: 'topRight' })
}

watch(buscar, () => {
  getModalidades()
})

watch(visible, v => {
  if (!v) limpiarModalidad()
})

const columnsModalidades = [
  { title: 'Código', dataIndex: 'codigo', key: 'codigo', align: 'center', width: 100 },
  { title: 'Nombre', dataIndex: 'nombre', key: 'nombre', ellipsis: true },
  { title: 'Estado', dataIndex: 'estado', key: 'estado', align: 'center', width: 110 },
  { title: 'Acciones', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 120, fixed: 'right' },
]

getModalidades()
</script>

<style scoped>
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

