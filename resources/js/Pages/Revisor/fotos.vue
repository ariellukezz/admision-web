<template>
  <Layout :pagina="'Fotos y Huellas'">
    <a-card :bordered="false" class="compact-card">
      
      <!-- HEADER compacto con DNI -->
      <div class="compact-header">
        <div class="header-left">
          <h3 class="mb-0">Registro Biométrico</h3>
          <span class="text-muted">Captura de fotos y huellas digitales</span>
        </div>
        <div class="header-right">
          <a-input 
            v-model:value="dniCompartido"
            placeholder="DNI del estudiante"
            size="middle"
            style="width: 200px"
            @press-enter="buscarEstudiante"
          >
            <template #prefix><IdcardOutlined /></template>
          </a-input>
        </div>
      </div>

      <a-divider style="margin: 12px 0" />

      <!-- Layout de dos columnas compacto -->
      <a-row :gutter="16">
        
        <!-- COLUMNA IZQUIERDA: FOTOS -->
        <a-col :xs="24" :lg="12">
          <ComponenteFotos 
            ref="fotosRef"
            :dni="dniCompartido"
            @foto-capturada="handleFotoCapturada"
            @foto-guardada="handleFotoGuardada"
          />
        </a-col>

        <!-- COLUMNA DERECHA: HUELLAS -->
        <a-col :xs="24" :lg="12">
          <ComponenteHuellas 
            ref="huellasRef"
            :dni="dniCompartido"
            @huellas-actualizadas="handleHuellasActualizadas"
          />
        </a-col>
      </a-row>

      <!-- Botón guardar todo (compacto) -->
      <div class="compact-footer">
        <a-space>
          <a-button 
            type="primary" 
            :disabled="!puedeGuardarTodo"
            :loading="guardandoTodo"
            @click="guardarTodo"
          >
            <SaveOutlined />
            Guardar Todo
          </a-button>
          <a-button @click="resetearTodo">
            <ReloadOutlined />
            Reiniciar
          </a-button>
        </a-space>
      </div>

      <!-- Últimos atendidos (compacto) -->
      <div class="ultimos-atendidos">
        <h4 class="mb-2">Últimos atendidos</h4>
        <div class="ultimos-lista">
          <div 
            v-for="(persona, index) in ultimosAtendidos" 
            :key="index"
            class="ultimo-item"
          >
            <a-avatar :size="24" style="background-color: #1890ff">
              {{ persona.nombre?.charAt(0) || '?' }}
            </a-avatar>
            <span class="nombre">{{ persona.nombre }} {{ persona.apellido }}</span>
            <span class="dni">DNI: {{ persona.nro_doc }}</span>
            <a-tag :color="getEstadoColor(persona.estado)" size="small">
              {{ persona.estado }}
            </a-tag>
          </div>
        </div>
      </div>

    </a-card>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Layout from '@/Layouts/LayoutDocente.vue'
import ComponenteFotos from './components/CamaraComponent.vue'
import ComponenteHuellas from './components/HuellasComponent.vue'
import axios from 'axios'
import { message } from 'ant-design-vue'
import { IdcardOutlined, SaveOutlined, ReloadOutlined } from '@ant-design/icons-vue'

// Estado compartido
const dniCompartido = ref('')
const fotoActual = ref(null)
const huellasActuales = ref({ any: false, data: null })
const guardandoTodo = ref(false)

// Refs a componentes hijos
const fotosRef = ref(null)
const huellasRef = ref(null)

// Últimos atendidos
const ultimosAtendidos = ref([])

// Computed
const puedeGuardarTodo = computed(() => {
  return fotoActual.value && huellasActuales.value.any && dniCompartido.value
})

// Manejadores de eventos de componentes hijos
const handleFotoCapturada = (foto) => {
  fotoActual.value = foto
}

const handleFotoGuardada = (data) => {
  console.log('Foto guardada:', data)
  cargarUltimosAtendidos()
}

const handleHuellasActualizadas = (huellas) => {
  huellasActuales.value = huellas
}

// Guardar todo
const guardarTodo = async () => {
  if (!puedeGuardarTodo.value) {
    message.warning('Complete todos los campos primero')
    return
  }
  
  guardandoTodo.value = true
  try {
    // Guardar foto si no está guardada
    if (fotosRef.value && !fotosRef.value.fotoGuardada) {
      await fotosRef.value.guardarFoto()
    }
    
    // Guardar huellas
    if (huellasRef.value) {
      await huellasRef.value.guardarHuellas()
    }
    
    message.success('Todos los datos guardados correctamente')
    
    // Resetear después de 2 segundos
    setTimeout(() => {
      resetearTodo()
    }, 2000)
    
  } catch (error) {
    message.error('Error al guardar los datos')
    console.error(error)
  } finally {
    guardandoTodo.value = false
  }
}

const resetearTodo = () => {
  fotoActual.value = null
  huellasActuales.value = { right: false, left: false, data: null }
  if (fotosRef.value) fotosRef.value.resetear()
  if (huellasRef.value) huellasRef.value.resetear()
  message.info('Todo ha sido reiniciado')
}

const cargarUltimosAtendidos = async () => {
  try {
    const res = await axios.get('https://pixel.unap.edu.pe/fotos/ultimas5')
    if (res.data.fotos) {
      ultimosAtendidos.value = res.data.fotos.slice(0, 3).map(persona => ({
        nombre: persona.nombres || 'Sin nombre',
        apellido: `${persona.paterno || ''} ${persona.materno || ''}`.trim(),
        hora: new Date(persona.created_at).toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' }),
        estado: 'Completado',
        nro_doc: persona.nro_doc
      }))
    }
  } catch (err) {
    console.error(err)
  }
}

const buscarEstudiante = async () => {
  if (!dniCompartido.value) return
  message.info(`Buscando estudiante: ${dniCompartido.value}`)
}

const getEstadoColor = (estado) => {
  const colores = { 'Completado': 'green', 'Pendiente': 'orange', 'Error': 'red' }
  return colores[estado] || 'default'
}

onMounted(() => {
  cargarUltimosAtendidos()
})
</script>

<style scoped>
.compact-card {
  padding: 16px;
  border-radius: 14px;
  background: #ffffff;
  box-shadow: 0 14px 30px rgba(0, 0, 0, 0.04);
}

.compact-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.header-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.header-left h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.text-muted {
  font-size: 12px;
  color: #8c8c8c;
}

.compact-footer {
  margin-top: 16px;
  text-align: center;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
}

.ultimos-atendidos {
  margin-top: 16px;
  padding: 12px;
  background: #fafafa;
  border-radius: 6px;
}

.ultimos-atendidos h4 {
  font-size: 13px;
  font-weight: 600;
  margin: 0 0 8px 0;
}

.ultimos-lista {
  max-height: 120px;
  overflow-y: auto;
}

.ultimo-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 8px;
  font-size: 12px;
  border-bottom: 1px solid #f0f0f0;
}

.ultimo-item:last-child {
  border-bottom: none;
}

.ultimo-item .nombre {
  flex: 1;
  font-weight: 500;
}

.ultimo-item .dni {
  color: #8c8c8c;
  font-size: 11px;
}

.mb-0 {
  margin-bottom: 0;
}

.mb-2 {
  margin-bottom: 8px;
}
</style>