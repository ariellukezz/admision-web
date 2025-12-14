<template>
  <div class="container">
    <!-- Header -->
    <div class="header">
      <h2>Excepciones - Proceso #{{ id_proceso }}</h2>
      <button @click="openCreateModal" class="btn btn-primary">Nueva Excepción</button>
    </div>

    <!-- Filtros -->
    <div class="filters">
      <input v-model="filters.cod_examen" placeholder="Código Examen" @keyup.enter="loadExcepciones" />
      <input v-model="filters.nro_pregunta" placeholder="N° Pregunta" type="number" @keyup.enter="loadExcepciones" />
      <select v-model="filters.tipo" @change="loadExcepciones">
        <option value="">Todos los tipos</option>
        <option value="correccion">Corrección</option>
        <option value="anulacion">Anulación</option>
        <option value="validacion">Validación</option>
      </select>
      <button @click="loadExcepciones">Buscar</button>
      <button @click="clearFilters">Limpiar</button>
    </div>

    <!-- Tabla -->
    <div v-if="loading">Cargando...</div>
    <div v-else-if="excepciones.length === 0">
      <p>No hay excepciones para este proceso. <a href="#" @click="openCreateModal">Crear la primera</a></p>
    </div>
    <table v-else>
      <thead>
        <tr>
          <th>ID</th>
          <th>N° Preg</th>
          <th>Código</th>
          <th>Acción</th>
          <th>Puntaje</th>
          <th>Tipo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in excepciones" :key="item.id">
          <td>{{ item.id }}</td>
          <td>{{ item.nro_pregunta }}</td>
          <td>{{ item.cod_examen }}</td>
          <td>{{ item.accion }}</td>
          <td>{{ item.puntaje }}</td>
          <td>{{ item.tipo }}</td>
          <td>
            <button @click="viewItem(item)">Ver</button>
            <button @click="editItem(item)">Editar</button>
            <button @click="deleteItem(item)">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Paginación -->
    <div v-if="pagination.last_page > 1" class="pagination">
      <button @click="prevPage" :disabled="pagination.current_page === 1">Anterior</button>
      <span>Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
      <button @click="nextPage" :disabled="pagination.current_page === pagination.last_page">Siguiente</button>
    </div>

    <!-- Modal Formulario -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ modalMode === 'create' ? 'Nueva' : 'Editar' }} Excepción</h3>
          <button @click="closeModal">×</button>
        </div>
        <form @submit.prevent="saveItem">
          <input type="hidden" v-model="form.id_proceso" />
          
          <div class="form-group">
            <label>N° Pregunta *</label>
            <input v-model="form.nro_pregunta" type="number" required />
          </div>
          <div class="form-group">
            <label>Código Examen *</label>
            <input v-model="form.cod_examen" required />
          </div>
          <div class="form-group">
            <label>Acción *</label>
            <select v-model="form.accion" required>
              <option value="">Seleccionar</option>
              <option value="todas_validas">Todas validas</option>
            </select>
          </div>
          <div class="form-group">
            <label>Puntaje *</label>
            <input v-model="form.puntaje" type="number" step="0.01" required />
          </div>
          <div class="form-group">
            <label>Tipo *</label>
            <select v-model="form.tipo" required>
              <option value="">Seleccionar</option>
              <option value="correccion">Corrección</option>
              <option value="anulacion">Anulación</option>
              <option value="validacion">Validación</option>
              <option value="especial">Especial</option>
            </select>
          </div>
          <div class="form-group">
            <label>Observación</label>
            <textarea v-model="form.observacion"></textarea>
          </div>
          <div class="form-group">
            <label>Claves Válidas</label>
            <input v-model="form.claves_validas" placeholder="Ej: A,B,C (separadas por comas)" />
          </div>
          <div class="modal-footer">
            <button type="button" @click="closeModal">Cancelar</button>
            <button type="submit">{{ modalMode === 'create' ? 'Crear' : 'Actualizar' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Detalles -->
    <div v-if="showDetailModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3>Detalles Excepción</h3>
          <button @click="closeDetailModal">×</button>
        </div>
        <div v-if="selectedItem" class="details">
          <p><strong>ID:</strong> {{ selectedItem.id }}</p>
          <p><strong>ID Proceso:</strong> {{ selectedItem.id_proceso }}</p>
          <p><strong>N° Pregunta:</strong> {{ selectedItem.nro_pregunta }}</p>
          <p><strong>Código Examen:</strong> {{ selectedItem.cod_examen }}</p>
          <p><strong>Acción:</strong> {{ selectedItem.accion }}</p>
          <p><strong>Puntaje:</strong> {{ selectedItem.puntaje }}</p>
          <p><strong>Tipo:</strong> {{ selectedItem.tipo }}</p>
          <p v-if="selectedItem.observacion"><strong>Observación:</strong> {{ selectedItem.observacion }}</p>
          <p v-if="selectedItem.claves_validas"><strong>Claves Válidas:</strong> {{ selectedItem.claves_validas }}</p>
        </div>
        <div class="modal-footer">
          <button @click="closeDetailModal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'

const props = defineProps({
  id_proceso: {
    type: [Number, String],
    required: true
  }
})

const excepciones = ref([])
const loading = ref(false)
const showModal = ref(false)
const showDetailModal = ref(false)
const modalMode = ref('create')
const selectedItem = ref(null)

// Filtros (sin id_proceso porque ya lo tenemos fijo)
const filters = reactive({
  cod_examen: '',
  nro_pregunta: '',
  tipo: ''
})

// Formulario (el id_proceso siempre viene del prop)
const form = reactive({
  nro_pregunta: '',
  accion: '',
  cod_examen: '',
  observacion: '',
  claves_validas: '',
  puntaje: '',
  tipo: '',
  id_proceso: props.id_proceso
})

// Paginación
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0
})

// Métodos
const loadExcepciones = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.current_page,
      id_proceso: props.id_proceso, // Siempre filtramos por este proceso
      ...Object.fromEntries(Object.entries(filters).filter(([_, v]) => v !== ''))
    }
    
    const response = await axios.get('/api/excepciones', { params })
    excepciones.value = response.data.data.data
    Object.assign(pagination, {
      current_page: response.data.data.current_page,
      last_page: response.data.data.last_page,
      total: response.data.data.total
    })
  } catch (error) {
    console.error('Error:', error)
    alert('Error al cargar datos')
  } finally {
    loading.value = false
  }
}

const clearFilters = () => {
  filters.cod_examen = ''
  filters.nro_pregunta = ''
  filters.tipo = ''
  loadExcepciones()
}

const openCreateModal = () => {
  modalMode.value = 'create'
  // Limpiar formulario pero mantener id_proceso
  Object.keys(form).forEach(key => {
    if (key !== 'id_proceso') form[key] = ''
  })
  form.id_proceso = props.id_proceso // Asegurar que tenga el valor actual
  showModal.value = true
}

const viewItem = (item) => {
  selectedItem.value = item
  showDetailModal.value = true
}

const editItem = (item) => {
  modalMode.value = 'edit'
  Object.keys(form).forEach(key => {
    form[key] = item[key] || ''
  })
  form.id = item.id
  showModal.value = true
}

const saveItem = async () => {
  try {
    const url = modalMode.value === 'create' 
      ? '/api/excepciones' 
      : `/api/excepciones/${form.id}`
    
    const method = modalMode.value === 'create' ? 'post' : 'put'
    
    // Asegurarnos de que el id_proceso es el correcto
    const dataToSend = { ...form }
    if (!dataToSend.id_proceso) {
      dataToSend.id_proceso = props.id_proceso
    }
    
    await axios[method](url, dataToSend)
    
    showModal.value = false
    loadExcepciones()
    alert('Excepción guardada correctamente')
  } catch (error) {
    console.error('Error:', error)
    if (error.response?.data?.errors) {
      // Mostrar errores de validación
      const errors = Object.values(error.response.data.errors).flat().join('\n')
      alert('Errores:\n' + errors)
    } else {
      alert(error.response?.data?.message || 'Error al guardar')
    }
  }
}

const deleteItem = async (item) => {
  if (!confirm('¿Eliminar esta excepción?')) return
  
  try {
    await axios.delete(`/api/excepciones/${item.id}`)
    loadExcepciones()
    alert('Excepción eliminada')
  } catch (error) {
    console.error('Error:', error)
    alert('Error al eliminar')
  }
}

const closeModal = () => {
  showModal.value = false
}

const closeDetailModal = () => {
  showDetailModal.value = false
}

const prevPage = () => {
  if (pagination.current_page > 1) {
    pagination.current_page--
    loadExcepciones()
  }
}

const nextPage = () => {
  if (pagination.current_page < pagination.last_page) {
    pagination.current_page++
    loadExcepciones()
  }
}

// Watcher para cuando cambie el id_proceso
watch(() => props.id_proceso, (newId) => {
  if (newId) {
    form.id_proceso = newId
    loadExcepciones()
  }
})

const puestos = ref([]);
const codigos_puesto = ref([]);
const codigos_examen = ref([]);
const selectPuesto = ref(null);
const selectCodigo = ref(null);
const selectUnidad = ref(null);

const getSelect = async () => {
    axios.get("/calificacion/get-select-puestos/"+props.id_proceso)
    .then((response) => {
        puestos.value = response.data.puestos;
        codigos_puesto.value = response.data.codigos_puesto;
        codigos_examen.value = response.data.codigos_examen;
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
    });
}


onMounted(() => {
  loadExcepciones()
  getSelect()
})
</script>

<style scoped>
.container { padding: 20px; max-width: 1200px; margin: 0 auto; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.filters { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
.filters input, .filters select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
.filters button { padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
.filters button:hover { background: #0056b3; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background: white; }
th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
th { background: #f4f4f4; font-weight: bold; }
tr:hover { background: #f9f9f9; }
button { padding: 6px 12px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; margin: 0 2px; }
button:hover { background: #f0f0f0; }
.btn-primary { background: #007bff; color: white; border: none; }
.btn-primary:hover { background: #0056b3; }
.pagination { display: flex; gap: 15px; align-items: center; justify-content: center; margin-top: 20px; }
.pagination button:disabled { opacity: 0.5; cursor: not-allowed; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: white; padding: 25px; border-radius: 8px; width: 500px; max-width: 95%; max-height: 90vh; overflow-y: auto; }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
.modal-header button { background: none; border: none; font-size: 24px; cursor: pointer; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
.form-group textarea { min-height: 80px; resize: vertical; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px; padding-top: 15px; border-top: 1px solid #eee; }
.modal-footer button { padding: 8px 20px; }
.details p { margin: 8px 0; padding: 5px 0; border-bottom: 1px solid #f0f0f0; }
.details p:last-child { border-bottom: none; }
</style>