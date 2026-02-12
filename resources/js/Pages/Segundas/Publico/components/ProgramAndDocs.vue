<template>
  <div class="bg-gray-50 p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
      <!-- Encabezado -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Programa y Documentación</h1>
            <p class="text-gray-600 mt-1">Seleccione el programa y suba los documentos requeridos</p>
          </div>
          <div class="hidden md:block">
            <span class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
              Paso 3 de 3
            </span>
          </div>
        </div>
      </div>

      <!-- Tarjeta principal -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-4 md:p-6">
          <a-form
              ref="formPreinscripcion"
              :model="datos_preinscripcion"
              layout="vertical"
              class="space-y-8"
            >
            <!-- Sección: Programa -->
            <div>
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Programa de Segunda Especialidad</h2>
              </div>

              <div>
                <a-form-item
                  name="programa"
                  :rules="[{ required: true, message: 'Seleccione el programa' }]"
                  class="mb-0"
                >
                  <a-select
                    :value="datos_preinscripcion.programa"
                    @change="(val) => $emit('update:programa', val)"
                    placeholder="Seleccione su programa"
                    size="large"
                    class="w-full"
                  >
                    <a-select-option
                      v-for="programa in programas"
                      :key="programa.value"
                      :value="programa.value"
                    >
                      {{ programa.label }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </div>
            </div>

            <!-- Sección: Documentos Requeridos -->
            <div>
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Documentos Requeridos</h2>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                  <slot name="documents">
                    <!-- Aquí irá el componente Titulo -->
                  </slot>
                </div>
              </div>
            </div>
          </a-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, ref, onMounted, nextTick, watch } from 'vue'

const formPreinscripcion = ref()

const props = defineProps({
  setFormPreinscripcion: Function,
  datos_preinscripcion: Object,
  programas: Array,
})

const emit = defineEmits(['update:programa'])

// Usar onMounted para asegurar que el componente esté montado
onMounted(async () => {
  await nextTick() // Esperar al siguiente ciclo de actualización
  if (props.setFormPreinscripcion && formPreinscripcion.value) {
    props.setFormPreinscripcion(formPreinscripcion.value)
  }
})


watch(formPreinscripcion, (newVal) => {
  if (newVal && props.setFormPreinscripcion) {
    props.setFormPreinscripcion(newVal)
  }
})
</script>

<style scoped>
:deep(.ant-input),
:deep(.ant-select-selector),
:deep(.ant-picker) {
  border-radius: 6px !important;
}

:deep(.ant-input):focus,
:deep(.ant-select-selector):focus,
:deep(.ant-picker):focus {
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1) !important;
}
</style>
