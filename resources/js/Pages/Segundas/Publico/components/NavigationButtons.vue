<template>
  <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
    <div class="max-w-6xl mx-auto px-4 py-4">
      <div class="flex items-center justify-between">
        <div>
          <a-button
            @click="handlePrevious"
            class="h-12 px-6 font-medium"
          >
            <LeftOutlined />
            Anterior
          </a-button>
        </div>

        <div>
          <a-button
            v-if="showNextButton"
            type="primary"
            @click="emit('next')"
            class="h-12 px-6 font-medium"
            size="large"
          >
            Siguiente
            <RightOutlined />
          </a-button>

          <a-button
            v-if="showVerifyButton"
            type="primary"
            @click="emit('verify')"
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
import { computed } from 'vue'
import { LeftOutlined, RightOutlined, EyeOutlined } from '@ant-design/icons-vue'

const props = defineProps({
  pagina_pre: Number,
})

const emit = defineEmits(['previous', 'next', 'verify'])

const showNextButton = computed(() => {
  return props.pagina_pre === 1 || props.pagina_pre === 2
})

const showVerifyButton = computed(() => {
  return props.pagina_pre === 6
})

const handlePrevious = () => {
  if (props.pagina_pre === 1) {
    emit('previous', 0)
  } else {
    emit('previous')
  }
}
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
