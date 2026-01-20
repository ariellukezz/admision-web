<template>
  <Head title="Años" />

  <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">

      <div class="flex justify-between mb-4">
        <a-button type="primary" @click="showModal">
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
        :columns="columns"
        :data-source="anios"
        :pagination="false"
        size="small"
        :loading="loading"
        row-key="id"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'acciones'">
            <div class="flex gap-1 justify-center">
              <a-button
                size="small"
                style="background:white;border:1px solid #d9d9d9;width:32px;height:32px"
                @click="abrirEditar(record)"
              >
                <EditOutlined />
              </a-button>

              <a-popconfirm
                title="¿Estás seguro de eliminar?"
                @confirm="eliminar(record)"
              >
                <a-button
                  size="small"
                  style="background:white;border:1px solid #d9d9d9;color:#ff4d4f;width:32px;height:32px"
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
        />
      </div>

    </div>
  </AuthenticatedLayout>

  <a-modal
    v-model:open="visible"
    :title="anio.id ? 'Editar Año' : 'Nuevo Año'"
    destroy-on-close
  >
    <a-form
      ref="formRef"
      :model="anio"
      :label-col="{ span: 7 }"
      :wrapper-col="{ span: 14 }"
    >
      <a-form-item label="Año" name="anio" required>
        <a-input v-model:value="anio.anio">
          <template #prefix>
            <sin-icono/>
          </template>
        </a-input>
      </a-form-item>

      <a-form-item label="Nombre" name="nombre" required>
        <a-input v-model:value="anio.nombre">
          <template #prefix>
            <sin-icono/>
          </template>
        </a-input>
      </a-form-item>
    </a-form>

    <template #footer>
      <a-button @click="cancelar">Cancelar</a-button>
      <a-button type="primary" :loading="guardando" @click="guardar">
        {{ anio.id ? 'Actualizar' : 'Guardar' }}
      </a-button>
    </template>
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
  notification[type]({ message: titulo, description: mensaje })
}

watch(buscar, () => {
  pagina.value = 1
  getAnios()
})

watch(pagina, getAnios)

watch(visible, v => {
  if (!v) limpiar()
})

const columns = [
  { title: 'Año', dataIndex: 'anio', align: 'center', width: 100 },
  { title: 'Nombre', dataIndex: 'nombre' },
  { title: 'Acciones', dataIndex: 'acciones', align: 'center', width: 120 }
]

getAnios()
</script>
