<template>
  <div class="flex gap-2 w-full">
    <a-select
      v-model:value="selectedValue"
      :options="options"
      :loading="loadingSelect"
      placeholder="Seleccionar tipo"
      allow-clear
      show-search
      option-filter-prop="label"
      class="flex-1"
      @change="onChange"
    />
    <a-button @click="openManager" title="Gestionar tipos">
      <template #icon><SettingOutlined /></template>
    </a-button>

    <!-- Modal de gestión de tipos -->
    <a-modal
      v-model:open="managerVisible"
      centered
      title="Gestionar Tipos de Personal"
      width="90%"
      :style="{ maxWidth: '560px' }"
      :footer="null"
      destroy-on-close
    >
      <!-- Formulario inline -->
      <div class="flex gap-2 mb-4">
        <a-input
          v-model:value="form.nombre"
          placeholder="Nombre del tipo"
          @pressEnter="saveTipo"
        />
        <a-button
          v-if="!form.id"
          type="primary"
          style="background: #2563eb; border: none; border-radius: 6px;"
          :loading="saving"
          @click="saveTipo"
        >
          <template #icon><PlusOutlined /></template>
          Agregar
        </a-button>
        <a-button v-else type="primary" :loading="saving" @click="saveTipo">
          Actualizar
        </a-button>
        <a-button v-if="form.id" @click="resetForm">Cancelar</a-button>
      </div>

      <a-input
        v-if="form.id"
        v-model:value="form.descripcion"
        placeholder="Descripción (opcional)"
        class="mb-4"
      />

      <!-- Lista -->
      <a-table
        :columns="cols"
        :data-source="tipos"
        size="small"
        :pagination="{ pageSize: 8 }"
        :loading="loadingTable"
        row-key="id"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'descripcion'">
            <span class="text-sm text-gray-500">{{ record.descripcion || '—' }}</span>
          </template>
          <template v-if="column.dataIndex === 'acciones'">
            <a-space size="small">
              <a-button size="small" type="text" class="text-blue-600" @click="editTipo(record)">
                <template #icon><EditOutlined /></template>
              </a-button>
              <a-popconfirm title="¿Eliminar?" @confirm="deleteTipo(record)">
                <a-button size="small" type="text" danger>
                  <template #icon><DeleteOutlined /></template>
                </a-button>
              </a-popconfirm>
            </a-space>
          </template>
        </template>
      </a-table>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import {
  SettingOutlined,
  PlusOutlined,
  EditOutlined,
  DeleteOutlined
} from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const props = defineProps({
  value: { type: [Number, null], default: null },
  initialOptions: { type: Array, default: () => [] }
})

const emit = defineEmits(['update:value'])

const selectedValue = ref(props.value)
const options = ref([...props.initialOptions])
const loadingSelect = ref(false)
const loadingTable = ref(false)
const saving = ref(false)
const managerVisible = ref(false)
const tipos = ref([])

const form = ref({
  id: null,
  nombre: '',
  descripcion: ''
})

const cols = [
  { title: 'Nombre', dataIndex: 'nombre', key: 'nombre', ellipsis: true },
  { title: 'Descripción', dataIndex: 'descripcion', key: 'descripcion', ellipsis: true },
  { title: '', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 100 }
]

const onChange = (val) => {
  selectedValue.value = val
  emit('update:value', val)
}

watch(() => props.value, (val) => {
  selectedValue.value = val
})

const syncOptionsFromTipos = () => {
  options.value = tipos.value.map(t => ({ value: t.id, label: t.nombre }))
}

const loadTipos = async () => {
  loadingTable.value = true
  try {
    const res = await axios.post('/admin/tipo-personal/get-tipos', { term: '' })
    tipos.value = res.data.datos.data
    syncOptionsFromTipos()
  } catch {
    notif('error', 'Error', 'No se pudieron cargar los tipos')
  } finally {
    loadingTable.value = false
  }
}

const openManager = () => {
  managerVisible.value = true
  loadTipos()
}

const editTipo = (record) => {
  form.value = {
    id: record.id,
    nombre: record.nombre,
    descripcion: record.descripcion || ''
  }
}

const resetForm = () => {
  form.value = { id: null, nombre: '', descripcion: '' }
}

const saveTipo = async () => {
  if (!form.value.nombre?.trim()) {
    notif('warning', 'Campo requerido', 'Ingrese el nombre')
    return
  }
  saving.value = true
  try {
    const res = await axios.post('/admin/save-tipo-personal', {
      id: form.value.id || null,
      nombre: form.value.nombre,
      descripcion: form.value.descripcion || null
    })
    notif('success', res.data.titulo, res.data.mensaje)
    resetForm()
    await loadTipos()
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.values(error.response.data.errors).forEach(err => {
        notif('error', 'Error', err[0])
      })
    } else {
      notif('error', 'Error', 'No se pudo guardar')
    }
  } finally {
    saving.value = false
  }
}

const deleteTipo = async (record) => {
  try {
    const res = await axios.get(`/admin/eliminar-tipo-personal/${record.id}`)
    notif('warning', res.data.titulo, res.data.mensaje)
    if (selectedValue.value === record.id) {
      selectedValue.value = null
      emit('update:value', null)
    }
    await loadTipos()
  } catch {
    notif('error', 'Error', 'No se pudo eliminar')
  }
}

const notif = (type, titulo, mensaje) => {
  notification[type]({ message: titulo, description: mensaje, placement: 'topRight' })
}

onMounted(() => {
  if (options.value.length === 0) {
    loadTipos()
  }
})
</script>
