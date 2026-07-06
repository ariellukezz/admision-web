<template>
  <div class="bg-gray-50 min-h-screen p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
      <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Datos de Residencia</h1>
            <p class="text-gray-600 mt-1">Complete todos los campos requeridos (*)</p>
          </div>
          <div class="hidden md:block">
            <span class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
              Paso 2 de 7
            </span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-4 md:p-6">
          <a-form
            ref="formDatosResidencia"
            :model="datosresidencia"
            layout="vertical"
            class="space-y-8"
          >
            <div>
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Ubicación de Residencia</h2>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="col-span-1 md:col-span-3" v-if="datospersonales.tipo_doc !== 1">
                  <a-form-item
                    label="País"
                    name="pais"
                    :rules="[{ required: true, message: 'Seleccione el país' }]"
                    class="mb-0"
                  >
                    <a-select
                      :value="datosresidencia.pais"
                      @change="(val) => datosresidencia.pais = val"
                      placeholder="Seleccionar"
                      size="large"
                      class="w-full"
                    >
                      <a-select-option :value="23">BOLIVIA</a-select-option>
                      <a-select-option :value="11">ARGENTINA</a-select-option>
                      <a-select-option :value="184">VENEZUELA</a-select-option>
                      <a-select-option :value="38">COLOMBIA</a-select-option>
                      <a-select-option :value="35">CHILE</a-select-option>
                      <a-select-option :value="47">ECUADOR</a-select-option>
                      <a-select-option :value="26">BRASIL</a-select-option>
                      <a-select-option :value="104">MÉXICO</a-select-option>
                      <a-select-option :value="182">URUGUAY</a-select-option>
                      <a-select-option :value="124">PARAGUAY</a-select-option>
                      <a-select-option :value="128">PUERTO RICO</a-select-option>
                      <a-select-option :value="149">REPUBLICA DOMINICANA</a-select-option>
                    </a-select>
                  </a-form-item>
                </div>

                <div class="col-span-1 md:col-span-3" v-if="datospersonales.tipo_doc === 1">
                  <a-form-item
                    label="País"
                    name="pais"
                    :rules="[{ required: true, message: 'Seleccione el país' }]"
                    class="mb-0"
                  >
                    <a-select
                      :value="datosresidencia.pais"
                      @change="(val) => datosresidencia.pais = val"
                      placeholder="Seleccionar"
                      size="large"
                      class="w-full"
                      :disabled="true"
                    >
                      <a-select-option :value="125">PERÚ</a-select-option>
                    </a-select>
                  </a-form-item>
                </div>

                <div class="col-span-1 md:col-span-3" v-if="datospersonales.tipo_doc === 1">
                  <a-form-item
                    label="Ubigeo de Residencia"
                    name="ubigeo_res"
                    :rules="[{ required: true, message: 'Seleccione su ubigeo de residencia' }]"
                    size="large"
                    class="w-full mb-0"
                    
                  >
                    <a-select
                      :value="ubigeoResSeleccionado"
                      show-search
                      :options="ubigeoResOptions"
                      :field-names="{ value: 'key', label: 'value' }"
                      :filter-option="false"
                      @search="handleSearchUbigeoRes"
                      @dropdownVisibleChange="handleDropdownVisibleChange"
                      @change="(val) => onSelectUbigeoRes(val)"
                      placeholder="Escribe para buscar ubigeo"
                      size="large"
                      class="w-full"
                      allow-clear
                    />
                  </a-form-item>
                </div>
              </div>
            </div>

            <div>
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Dirección</h2>
              </div>

              <a-form-item
                label="Dirección Actual"
                name="direccion"
                :rules="[{ required: true, message: 'Ingrese su dirección' }]"
                class="mb-0"
              >
                <a-input
                  :value="datosresidencia.direccion"
                  @input="(e) => datosresidencia.direccion = e.target.value"
                  placeholder="Dirección completa"
                  class="w-full"
                  size="large"
                />
              </a-form-item>
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
  datosresidencia: Object,
  datospersonales: Object,
  setFormDatosResidencia: Function,
  ubigeoResOptions: Array,
  ubigeoResSeleccionado: [String, Number, null],
  getUbigeosRes: Function,
  onSelectUbigeoRes: Function,
})

const formDatosResidencia = ref(null)

let searchTimer = null

const handleSearchUbigeoRes = (value) => {
  const term = (value || '').trim()
  if (!term || term.length >= 2) {
    if (searchTimer) clearTimeout(searchTimer)
    searchTimer = setTimeout(() => {
      props.getUbigeosRes(term)
    }, 250)
  }
}

const handleDropdownVisibleChange = (open) => {
  if (open && (!props.ubigeoResOptions || props.ubigeoResOptions.length === 0)) {
    props.getUbigeosRes('')
  }
}

onMounted(async () => {
  await nextTick()
  if (props.setFormDatosResidencia && formDatosResidencia.value) {
    props.setFormDatosResidencia(formDatosResidencia.value)
  }
  if (!props.ubigeoResOptions?.length) {
    props.getUbigeosRes('')
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
