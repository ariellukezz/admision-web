<template>
  <div>
    <a-button 
      type="primary" 
      @click="showManager = true"
      class="mb-4"
    >
      <template #icon>
        <setting-outlined/>
      </template>
      Gestionar Tipos
    </a-button>

    <a-modal 
      v-model:open="showManager" 
      title="Gestión de Tipos" 
      width="90%"
      :footer="null"
      :styles="{ maxWidth: '1200px' }"
    >
      <div class="crud-container">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
          <a-input
            v-model:value="searchText"
            placeholder="Buscar tipos..."
            :style="{ width: '100%', maxWidth: '240px' }"
          >
            <template #prefix>
              <search-outlined />
            </template>
          </a-input>
          <a-button type="primary" @click="showCreateModal" class="w-full sm:w-auto">
            <template #icon>
              <plus-outlined />
            </template>
            Nuevo Tipo
          </a-button>
        </div>

        <a-table 
          :columns="columns" 
          :data-source="filteredTipos"
          :loading="loading"
          :pagination="pagination"
          row-key="id"
          :scroll="{ x: 600 }"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'actions'">
              <a-space>
                <a-button type="link" size="small" @click="editTipo(record)">
                  <edit-outlined />
                </a-button>
                <a-popconfirm
                  title="¿Estás seguro de eliminar este tipo?"
                  @confirm="deleteTipo(record.id)"
                >
                  <a-button type="link" danger size="small">
                    <delete-outlined />
                  </a-button>
                </a-popconfirm>
              </a-space>
            </template>
          </template>
        </a-table>

        <a-modal 
          v-model:open="showFormModal" 
          :title="editingTipo ? 'Editar Tipo' : 'Nuevo Tipo'"
          @ok="handleSubmit"
          @cancel="handleCancel"
          width="90%"
          :styles="{ maxWidth: '500px' }"
        >
          <a-form 
            ref="formRef"
            :model="formState"
            :rules="rules"
            layout="vertical"
          >
            <a-form-item label="Nombre" name="nombre">
              <a-input v-model:value="formState.nombre">
                <template #prefix>
                    <sin-icono/>
                </template>
            </a-input>    
            </a-form-item>
            
            <a-form-item label="Categoría" name="categoria">
              <a-select
                v-model:value="formState.categoria"
                placeholder="Seleccione categoría"
              >
                <a-select-option value="INHABILITACION INDEFINIDA">
                  INHABILITACIÓN INDEFINIDA
                </a-select-option>
                <a-select-option value="INHABILITACION TEMPORAL">
                  INHABILITACIÓN TEMPORAL
                </a-select-option>
                <a-select-option value="SANCION ECONOMICA">
                  SANCIÓN ECONÓMICA
                </a-select-option>
              </a-select>
            </a-form-item>
            
            <a-form-item label="Descripción" name="descripcion">
              <a-textarea 
                v-model:value="formState.descripcion" 
                :rows="3"
              />
            </a-form-item>
          </a-form>
          
          <template #footer>
            <a-button @click="handleCancel">Cancelar</a-button>
            <a-button 
              type="primary" 
              @click="handleSubmit"
              :loading="submitting"
            >
              {{ editingTipo ? 'Actualizar' : 'Crear' }}
            </a-button>
          </template>
        </a-modal>
      </div>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { SettingOutlined, PlusOutlined, EditOutlined, DeleteOutlined, SearchOutlined } from '@ant-design/icons-vue'
import { message } from 'ant-design-vue'
import axios from 'axios'

const showManager = ref(false)
const showFormModal = ref(false)
const loading = ref(false)
const submitting = ref(false)
const searchText = ref('')
const tipos = ref([])
const editingTipo = ref(null)

const formRef = ref()
const formState = reactive({ nombre: '', categoria: '', descripcion: ''})

const rules = {
  nombre: [{ required: true, message: 'El nombre es obligatorio' }],
  categoria: [{ required: true, message: 'La categoría es obligatoria' }]
}

const columns = [
  { title: 'ID', dataIndex: 'id', key: 'id', width: 50 },
  { title: 'Nombre', dataIndex: 'nombre', key: 'nombre' },
  { title: 'Categoría', dataIndex: 'categoria', key: 'categoria' },
  { title: 'Descripción', dataIndex: 'descripcion', key: 'descripcion', ellipsis: true },
  { title: 'Acciones', key: 'actions', width: 120, align: 'center' }
]

const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showSizeChanger: true,
  showQuickJumper: true
})

const filteredTipos = computed(() => {
  if (!searchText.value) return tipos.value
  
  return tipos.value.filter(tipo => 
    tipo.nombre.toLowerCase().includes(searchText.value.toLowerCase()) ||
    tipo.categoria.toLowerCase().includes(searchText.value.toLowerCase()) ||
    (tipo.descripcion && tipo.descripcion.toLowerCase().includes(searchText.value.toLowerCase()))
  )
})

const fetchTipos = async () => {
  loading.value = true
  try {
    const response = await axios.get('https://servicios-admision.unap.edu.pe/api/v1/observados/tipos')
    tipos.value = response.data.data || response.data
    pagination.total = tipos.value.length
  } catch (error) {
    message.error('Error al cargar los tipos')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const showCreateModal = () => {
  editingTipo.value = null
  Object.assign(formState, {
    nombre: '',
    categoria: '',
    descripcion: ''
  })
  showFormModal.value = true
}

const editTipo = (tipo) => {
  editingTipo.value = tipo
  Object.assign(formState, { ...tipo })
  showFormModal.value = true
}

const handleSubmit = async () => {
  try {
    await formRef.value.validate()
    submitting.value = true
    
    if (editingTipo.value) {
      await axios.put(`https://servicios-admision.unap.edu.pe/api/v1/observados/tipos/${editingTipo.value.id}`, formState)
      message.success('Tipo actualizado correctamente')
    } else {
      await axios.post('https://servicios-admision.unap.edu.pe/api/v1/observados/tipos', formState)
      message.success('Tipo creado correctamente')
    }
    
    await fetchTipos()
    handleCancel()
  } catch (error) {
    if (error.errorFields) return
    message.error('Error al guardar el tipo')
    console.error(error)
  } finally {
    submitting.value = false
  }
}

const deleteTipo = async (id) => {
  try {
    await axios.delete(`https://servicios-admision.unap.edu.pe/api/v1/observados/tipos/${id}`)
    message.success('Tipo eliminado correctamente')
    await fetchTipos()
  } catch (error) {
    message.error('Error al eliminar el tipo')
    console.error(error)
  }
}

const handleCancel = () => {
  showFormModal.value = false
  formRef.value?.resetFields()
  editingTipo.value = null
}

watch(showManager, (newVal) => {
  if (newVal) {
    fetchTipos()
  }
})
</script>

<style scoped>
.crud-container {
  min-height: 400px;
}
</style>