<template>
  <Head title="Modalidades" />

  <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">

      <div class="flex justify-between mb-4">
        <a-button type="primary" @click="showModalPrograma">
          <template #icon>
            <PlusOutlined />
          </template>
          Nuevo
        </a-button>

        <a-input
          v-model:value="buscar"
          placeholder="Buscar"
          style="width: 220px"
        >
          <template #prefix>
            <SearchOutlined />
          </template>
        </a-input>
      </div>

      <a-table
        :columns="columnsProgramas"
        :data-source="modalidades"
        :pagination="false"
        size="small"
        :loading="loading"
        row-key="id"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'estado'">
            <a-tag :color="record.estado ? 'cyan' : 'red'">
              {{ record.estado ? 'Activo' : 'Inactivo' }}
            </a-tag>
          </template>

          <template v-else-if="column.dataIndex === 'acciones'">
            <div class="flex gap-1 justify-center">
              <a-button
                size="small"
                style="background:white;border:1px solid #d9d9d9; height: 32px; width: 32px;"
                @click="abrirEditar(record)"
              >
                <EditOutlined />
              </a-button>

              <a-button
                size="small"
                :style="{
                  background: 'white',
                  width: '32px',
                  height: '32px',
                  border: '1px solid #d9d9d9',
                  color: record.estado ? '#faad14' : '#52c41a'
                }"
                @click="cambiarEstado(record)"
              >
                <PoweroffOutlined />
              </a-button>

              <a-popconfirm
                title="¿Estas seguro de eliminar?"
                @confirm="eliminar(record)"
              >
                <a-button
                  size="small"
                  style="background:white;border:1px solid #d9d9d9;color:#ff4d4f; height: 32px; width: 32px;"
                >
                  <DeleteOutlined />
                </a-button>
              </a-popconfirm>

            </div>
          </template>

        </template>
      </a-table>
      <div class="mt-2 text-right">
        <a-pagination
          v-model:current="pagina"
          :total="totalRegistros"
          show-less-items
          size="middle"

        />
      </div>

    </div>
  </AuthenticatedLayout>


  <a-modal
    v-model:open="visible"
    :title="modalidad.id ? 'Editar Modalidad' : 'Nueva Modalidad'"
    destroy-on-close
  >
    <a-form
      ref="formRef"
      :model="modalidad"
      :rules="rules"
      :label-col="{ span: 7 }"
      :wrapper-col="{ span: 14 }"
    >
      <a-form-item label="Código" name="codigo" has-feedback>
        <a-input v-model:value="modalidad.codigo" />
      </a-form-item>

      <a-form-item label="Nombre" name="nombre" has-feedback>
        <a-input v-model:value="modalidad.nombre" />
      </a-form-item>

      <a-form-item
        label="Estado"
        name="estado"
        v-if="modalidad.id"
      >
        <a-switch
          v-model:checked="modalidad.estado"
          checked-children="Activo"
          un-checked-children="Inactivo"
        />
      </a-form-item>
    </a-form>

    <template #footer>
      <a-button @click="cancelar">Cancelar</a-button>
      <a-button type="primary" :loading="guardando" @click="guardar">
        {{ modalidad.id ? 'Actualizar' : 'Guardar' }}
      </a-button>
    </template>
  </a-modal>

</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import { EditOutlined, DeleteOutlined, SearchOutlined, PlusOutlined, PoweroffOutlined} from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const buscar = ref('')
const modalidades = ref([])
const visible = ref(false)
const pagina = ref(1)
const totalRegistros = ref(0)
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
    const res = await axios.post(
      `modalidad/get-modalidades?page=${pagina.value}`,
      { term: buscar.value }
    )
    modalidades.value = res.data.datos.data
    totalRegistros.value = res.data.datos.total
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
  notification[type]({ message: titulo, description: mensaje })
}

watch(buscar, () => {
  pagina.value = 1
  getModalidades()
})

watch(pagina, () => {
  getModalidades()
})

watch(visible, v => {
  if (!v) limpiarModalidad()
})

const columnsProgramas = [
  { title: 'Código', dataIndex: 'codigo', align: 'center', width: 90 },
  { title: 'Nombre', dataIndex: 'nombre' },
  { title: 'Estado', dataIndex: 'estado', align: 'center', width: 90 },
  { title: 'Acciones', dataIndex: 'acciones', align: 'center', width: 150 }
]

getModalidades()
</script>

