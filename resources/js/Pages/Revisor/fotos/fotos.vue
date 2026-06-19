<template>
  <Layout :pagina="'Fotos y Huellas'">
    <a-card :bordered="false" class="compact-card">
      
      <!-- HEADER compacto con DNI y contexto -->
      <div class="compact-header">
        <div class="header-left">
          <h3 class="mb-0">Registro Biométrico</h3>
          <span class="text-muted">Captura de fotos y huellas digitales</span>
        </div>
        <div class="header-right">
          <a-select
            v-model:value="contextoSeleccionado"
            :options="contextoOptions"
            size="middle"
            style="width: 180px; margin-right: 8px"
            placeholder="Contexto"
          />
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

      <!-- Layout de tres columnas: cámara + huellas -->
      <a-row :gutter="12">
        
        <!-- COLUMNA 1: CÁMARA -->
        <a-col :xs="24" :lg="14">
          <ComponenteFotos 
            ref="fotosRef"
            :dni="dniCompartido"
            @foto-capturada="handleFotoCapturada"
            @foto-guardada="handleFotoGuardada"
          />
        </a-col>

        <!-- COLUMNA 2: HUELLAS (índice derecho + índice izquierdo) -->
        <a-col :xs="24" :lg="10">
          <ComponenteHuellas 
            ref="huellasRef"
            :dni="dniCompartido"
            :finger-filter="['indice_derecho', 'indice_izquierdo']"
            :context-mode="contextoSeleccionado"
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
import ComponenteFotos from '../components/CamaraComponent/CamaraComponent.vue'
import ComponenteHuellas from '../components/HuellasComponent/HuellasComponent.vue'
import axios from 'axios'
import { message } from 'ant-design-vue'
import { IdcardOutlined, SaveOutlined, ReloadOutlined } from '@ant-design/icons-vue'

// Estado compartido
const dniCompartido = ref('')
const fotoActual = ref(null)
const huellasActuales = ref({ any: false, data: null })
const guardandoTodo = ref(false)

// Contexto de captura
const contextoSeleccionado = ref('inscripcion')
const contextoOptions = [
  { label: 'Inscripción', value: 'inscripcion' },
  { label: 'Control Biométrico', value: 'control_biometrico' },
  { label: 'Examen', value: 'examen' }
]

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

<style scoped src="./fotos.css"></style>