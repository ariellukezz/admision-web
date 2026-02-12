<template>
  <a-modal
    :open="open"
    @update:open="$emit('close')"
    centered
    :width="800"
    :footer="null"
    :closable="true"
  >
    <div class="p-1">
      <div class="border-b border-gray-200 pb-6 mb-6">
        <div class="flex items-center">
          <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-900">Verificación Final de Datos</h2>
            <p class="text-gray-600 mt-1">Revise y confirme su información antes de enviar</p>
          </div>
        </div>
      </div>

      <div class="max-h-[60vh] overflow-y-auto pr-2">
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Datos Personales
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="(item, index) in personalDataItems"
              :key="index"
              class="bg-gray-50 rounded-lg p-4"
            >
              <div class="text-sm font-medium text-gray-500 mb-1">{{ item.label }}</div>
              <div class="text-gray-900 font-medium">{{ item.value || 'No especificado' }}</div>
            </div>
          </div>
        </div>

        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Programa Seleccionado
          </h3>

          <div class="bg-gray-50 rounded-lg p-6">
            <a-select
              :value="datos_preinscripcion.programa"
              :options="especialidades"
              placeholder="Seleccione una especialidad"
              size="large"
              class="w-full"
            >
            </a-select>
          </div>
        </div>

        <div class="mb-8">
          <div class="bg-blue-50 border border-blue-100 rounded-lg p-6">
            <a-checkbox v-model:checked="check"  class="w-full">
              <div class="flex items-start">
                <div class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg mr-4 flex-shrink-0">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900 mb-1">Declaración Jurada</h4>
                  <p class="text-gray-600 text-sm">
                    Certifico bajo declaración jurada que toda la información proporcionada es veraz,
                    completa y corresponde fielmente a mis documentos oficiales.
                  </p>
                </div>
              </div>
            </a-checkbox>
          </div>
        </div>
      </div>

      <div class="border-t border-gray-200 pt-6 mt-6">
        <div class="flex items-center justify-end gap-3">
          <a-button @click="emit('close')" class="h-10 px-6 font-medium">
            Cancelar
          </a-button>
          <a-button
            type="primary"
            @click="emit('submit')"
            :disabled="!check || !datos_preinscripcion.programa"
            class="h-10 px-6 font-medium"
          >
            Confirmar y Guardar
          </a-button>
        </div>
      </div>
    </div>
  </a-modal>
</template>

<script setup>
import { defineProps, computed, ref } from 'vue'

const check = ref(false)

const props = defineProps({
  open: Boolean,
  datospersonales: Object,
  datos_preinscripcion: Object,
  checkbox1: Boolean,
  tipo_docs: Object,
  estados_civil: Object,
  sexos: Object,
  especialidades: Array,
  formState: Object,
})

const emit = defineEmits(['close', 'submit', 'update:programa', 'update:checkbox1'])

const personalDataItems = computed(() => {
  return [
    { label: 'Tipo Documento', value: props.tipo_docs[props.datospersonales.tipo_doc] },
    { label: 'N° Documento', value: props.formState.dni },
    { label: 'Primer Apellido', value: props.datospersonales.primerapellido },
    { label: 'Segundo Apellido', value: props.datospersonales.segundo_apellido },
    { label: 'Nombres', value: props.datospersonales.nombres },
    { label: 'Estado Civil', value: props.estados_civil[props.datospersonales.estado_civil] },
    { label: 'Sexo', value: props.sexos[props.datospersonales.sexo] },
    { label: 'Correo', value: props.datospersonales.correo },
    { label: 'Celular', value: props.datospersonales.celular },
    { label: 'Fecha Nacimiento', value: props.datospersonales.fec_nacimiento },
    { label: 'Ubigeo Nacimiento', value: props.datospersonales.nacimiento },
    { label: 'Ubigeo Residencia', value: props.datospersonales.residencia },
  ]
})
</script>

<style scoped>
:deep(.ant-btn-primary) {
  background-color: #2563eb !important;
  border-color: #2563eb !important;
}

:deep(.ant-btn-primary:hover) {
  background-color: #1d4ed8 !important;
  border-color: #1d4ed8 !important;
}

:deep(.ant-btn-primary:disabled) {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
