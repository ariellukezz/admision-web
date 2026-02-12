<template>
  <div class=" bg-gray-50 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
        <!-- Ícono de éxito -->
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
          <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>

        <!-- Título -->
        <h1 class="text-2xl font-bold text-gray-900 mb-4">¡Preinscripción Exitosa!</h1>

        <!-- Descripción -->
        <p class="text-gray-600 mb-8 max-w-md mx-auto">
          Su preinscripción ha sido registrada correctamente en el sistema.
          Descargue los anexos para continuar con el proceso presencial.
        </p>

        <!-- Información adicional -->
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-8 max-w-md mx-auto">
          <div class="flex items-center justify-center text-sm text-blue-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Transcurridas 24 horas podrá descargar su constancia oficial</span>
          </div>
        </div>

        <!-- Botón de descarga -->
        <a-button
          type="primary"
          size="large"
          @click="emit('download')"
          class="h-12 font-medium"
        >
          <download-outlined/>
          Descargar Anexos
        </a-button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { DownloadOutlined } from '@ant-design/icons-vue';

const emit = defineEmits(['download'])
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
</style>
