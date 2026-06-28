<template>
  <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
    <div class="max-w-6xl mx-auto px-4 py-4">
      <div v-if="pagina_pre !== 8" class="mb-3">
        <a-progress :percent="avance" :stroke-color="'#2563eb'" />
      </div>
      <div class="flex items-center justify-between">
        <div>
          <a-button
            @click="$emit('previous')"
            class="h-12 px-6 font-medium"
          >
            <LeftOutlined />
            Anterior
          </a-button>
        </div>

        <div>
          <a-button
            v-if="pagina_pre >= 1 && pagina_pre <= 6"
            type="primary"
            @click="$emit('next')"
            class="h-12 px-6 font-medium"
            size="large"
          >
            Siguiente
            <RightOutlined />
          </a-button>

          <a-button
            v-if="pagina_pre === 7"
            type="primary"
            @click="$emit('verify')"
            size="large"
            class="h-12 px-6 font-medium"
          >
            <EyeOutlined />
            Verificar Datos
          </a-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { LeftOutlined, RightOutlined, EyeOutlined } from '@ant-design/icons-vue'

defineProps({
  pagina_pre: Number,
  avance: Number,
  bottom: Number,
})

defineEmits(['previous', 'next', 'verify'])
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

.fixed {
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}
</style>
