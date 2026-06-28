<template>
  <div class="bg-gray-50 min-h-screen p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
      <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Datos de Postulación</h1>
            <p class="text-gray-600 mt-1">Complete todos los campos requeridos (*)</p>
          </div>
          <div class="hidden md:block">
            <span class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
              Paso 7 de 7
            </span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-4 md:p-6">
          <a-form
            ref="formPreinscripcion"
            :model="datos_preinscripcion"
            layout="vertical"
            class="space-y-8"
          >
            <div>
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Modalidad de Postulación</h2>
              </div>

              <div v-if="datos_preinscripcion.modalidad !== 1" class="mb-6">
                <a-form-item
                  label="Modalidad"
                  name="modalidad"
                  :rules="[{ required: true, message: 'Seleccione la modalidad' }]"
                  class="mb-0"
                >
                  <a-select
                    :value="datos_preinscripcion.modalidad"
                    @change="(val) => datos_preinscripcion.modalidad = val"
                    :options="modalidades"
                    placeholder="Seleccione la modalidad"
                    size="large"
                    class="w-full"
                  ></a-select>
                </a-form-item>
              </div>

              <div v-if="datos_preinscripcion.modalidad == 1">
                <a-form-item
                  label="Modalidad"
                  name="modalidad"
                  :rules="[{ required: true, message: 'Seleccione la modalidad' }]"
                  class="mb-0"
                >
                  <a-select
                    :value="datos_preinscripcion.modalidad"
                    @change="(val) => datos_preinscripcion.modalidad = val"
                    :options="modalidades"
                    placeholder="Seleccione la modalidad"
                    size="large"
                    class="w-full"
                  ></a-select>
                </a-form-item>
              </div>

              <div v-if="id_modalidad_proceso === 3 && datos_preinscripcion.modalidad === 2" class="mt-4">
                <a-alert
                  description="¡MUY IMPORTANTE! El postulante debe elegir un programa de estudios que pertenezca al mismo área del programa que está cursando actualmente."
                  type="info"
                  show-icon
                />
              </div>

              <div v-if="id_modalidad_proceso === 3 && datos_preinscripcion.modalidad === 3" class="mt-4">
                <a-alert
                  description="¡MUY IMPORTANTE! El postulante debe ingresar los datos de la carrera o programa de estudio y la universidad de la que proviene."
                  type="info"
                  show-icon
                />
              </div>
            </div>

            <div v-if="datos_preinscripcion.modalidad == 2">
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Código de Matrícula</h2>
              </div>

              <a-form-item
                label="Código de matrícula"
                name="observacion"
                :rules="[{ required: true, message: 'Código requerido' }]"
                class="mb-0"
              >
                <a-input
                  :value="datos_preinscripcion.observacion"
                  @input="(e) => datos_preinscripcion.observacion = e.target.value"
                  placeholder="Código de matrícula actual"
                  class="w-full"
                  size="large"
                />
              </a-form-item>
            </div>

            <div v-if="carreras_previas && carreras_previas.length > 0">
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Carreras Disponibles</h2>
              </div>

              <a-row :gutter="16" style="display:flex; justify-content:center;">
                <a-col v-for="item in carreras_previas" :key="item" :xs="24" :sm="24" :md="24" :lg="24" style="margin-bottom: 10px;">
                  <div
                    @click="toggleSelection2(item)"
                    :class="{ 'selected': item.selected }"
                    style="height:80px; border-radius:8px; cursor:pointer; border:solid 1px #e5e7eb; align-items: center; transition: all 0.2s;"
                    class="flex p-4 hover:border-blue-300"
                  >
                    <div style="display:flex; justify-content: space-between; width: 100%; align-items: center;">
                      <div style="width: calc(100% - 50px);">
                        <div>
                          <span style="font-size:.8rem; text-transform: capitalize;">{{ item.nombre }}</span>
                        </div>
                        <div class="flex justify-left">
                          <span style="font-weight:bold; font-size:.8rem">cod: {{ item.codigo }}</span>
                        </div>
                      </div>
                      <div class="flex justify-center" style="width: 50px; height: 50px; align-items: center;">
                        <img src="../../../../../assets/imagenes/logotiny.png" width="45px" />
                      </div>
                    </div>
                  </div>
                </a-col>
              </a-row>
            </div>

            <div>
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Programa de Estudios</h2>
              </div>

              <a-form-item
                label="Programa de estudios"
                name="programa"
                :rules="[{ required: true, message: 'Seleccione el programa' }]"
                class="mb-0"
              >
                <a-select
                  :value="datos_preinscripcion.programa"
                  @change="(val) => datos_preinscripcion.programa = val"
                  :options="programas"
                  placeholder="Seleccione su programa"
                  size="large"
                  class="w-full"
                  show-search
                  :filter-option="filterOption"
                ></a-select>
              </a-form-item>

              <div v-if="datos_preinscripcion.programa === 38 || datos_preinscripcion.programa === 16" class="mt-4">
                <a-form-item
                  label="Código de constancia de examen médico"
                  name="codigo_medico"
                  class="mb-0"
                >
                  <a-input
                    :value="datos_preinscripcion.codigo_medico"
                    @input="(e) => datos_preinscripcion.codigo_medico = e.target.value"
                    placeholder="Código de examen médico"
                    class="w-full"
                    size="large"
                  />
                </a-form-item>
              </div>
            </div>

            <div v-if="![2, 3, 1].includes(datos_preinscripcion.modalidad)">
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Certificado</h2>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <a-form-item
                    label="Tipo de certificado"
                    name="tipo_certificado"
                    :rules="[{ required: true, message: 'Seleccione el tipo de certificado' }]"
                    class="mb-0"
                  >
                    <a-select
                      :value="datos_preinscripcion.tipo_certificado"
                      @change="(val) => datos_preinscripcion.tipo_certificado = val"
                      placeholder="Seleccionar tipo de certificado"
                      size="large"
                      class="w-full"
                    >
                      <a-select-option value='CERTIFICADO BLANCO'>CERTIFICADO BLANCO</a-select-option>
                      <a-select-option value='CERTIFICADO AMARILLO'>CERTIFICADO AMARILLO</a-select-option>
                      <a-select-option value='CONSTANCIA DE ESTUDIOS'>CONSTANCIA DE ESTUDIOS</a-select-option>
                    </a-select>
                  </a-form-item>
                </div>

                <div>
                  <a-form-item
                    label="Código de certificado"
                    name="codigo_certificado"
                    :rules="[{ required: true, message: 'Ingrese el código de certificado' }]"
                    class="mb-0"
                  >
                    <div class="flex justify-between items-center mb-1">
                      <span></span>
                      <span class="text-sm text-blue-600 cursor-pointer hover:text-blue-800" @click="$emit('showExample')">ver ejemplo</span>
                    </div>
                    <a-input
                      :value="datos_preinscripcion.codigo_certificado"
                      @input="(e) => datos_preinscripcion.codigo_certificado = e.target.value"
                      placeholder="Código de certificado"
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
  datos_preinscripcion: Object,
  setFormPreinscripcion: Function,
  modalidades: Array,
  programas: Array,
  carreras_previas: Array,
  id_modalidad_proceso: Number,
  toggleSelection2: Function,
})

defineEmits(['showExample'])

const filterOption = (input, option) => {
  return option.label?.toLowerCase().includes(input.toLowerCase())
}

const formPreinscripcion = ref(null)

onMounted(async () => {
  await nextTick()
  if (props.setFormPreinscripcion && formPreinscripcion.value) {
    props.setFormPreinscripcion(formPreinscripcion.value)
  }
})
</script>

<style scoped>
.selected { background-color: #dbeafe !important; border-color: #3b82f6 !important; }

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
