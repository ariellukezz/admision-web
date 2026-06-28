<template>
  <div class="bg-gray-50 min-h-screen p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
      <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
            <p class="text-gray-600 mt-1">Complete todos los campos requeridos (*)</p>
          </div>
          <div class="hidden md:block">
            <span class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
              {{ stepLabel }}
            </span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-4 md:p-6">
          <a-form
            ref="formPadre"
            :model="datos"
            :name="name"
            layout="vertical"
            class="space-y-8"
          >
            <div>
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Información del Padre/Madre</h2>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                  <a-form-item
                    label="N° Documento"
                    name="dni"
                    :rules="[
                      { required: true, message: 'Ingresa el DNI' },
                      { min: 8, message: 'El DNI debe tener 8 dígitos' }
                    ]"
                    class="mb-0"
                  >
                    <a-input
                      :value="datos.dni"
                      @input="(e) => datos.dni = e.target.value"
                      :maxlength="12"
                      placeholder="Número de documento"
                      class="w-full"
                      size="large"
                    />
                  </a-form-item>
                </div>

                <div class="md:col-span-2">
                  <a-form-item
                    label="Pre Nombres"
                    name="nombres"
                    :rules="[{ required: true, message: 'Ingresa los nombres' }]"
                    class="mb-0"
                  >
                    <a-input
                      :value="datos.nombres"
                      @input="(e) => datos.nombres = e.target.value"
                      placeholder="Nombres"
                      class="w-full"
                      size="large"
                    />
                  </a-form-item>
                </div>
              </div>
            </div>

            <div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <a-form-item
                    label="Primer Apellido"
                    name="paterno"
                    :rules="[{ required: true, message: 'Ingresa el primer apellido' }]"
                    class="mb-0"
                  >
                    <a-input
                      :value="datos.paterno"
                      @input="(e) => datos.paterno = e.target.value"
                      placeholder="Primer apellido"
                      class="w-full"
                      size="large"
                    />
                  </a-form-item>
                </div>

                <div>
                  <a-form-item
                    label="Segundo Apellido"
                    name="materno"
                    class="mb-0"
                  >
                    <a-input
                      :value="datos.materno"
                      @input="(e) => datos.materno = e.target.value"
                      placeholder="Segundo apellido"
                      class="w-full"
                      size="large"
                    />
                  </a-form-item>
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
import { ref, onMounted, nextTick } from 'vue'

const props = defineProps({
  datos: Object,
  title: { type: String, default: 'Datos del padre' },
  name: { type: String, default: 'datospadre' },
  stepLabel: { type: String, default: 'Paso 4 de 7' },
  setFormRef: Function,
})

const formPadre = ref(null)

onMounted(async () => {
  await nextTick()
  if (props.setFormRef && formPadre.value) {
    props.setFormRef(formPadre.value)
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
