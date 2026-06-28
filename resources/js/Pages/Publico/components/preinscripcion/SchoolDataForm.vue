<template>
  <div class="bg-gray-50 min-h-screen p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
      <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Datos del Colegio</h1>
            <p class="text-gray-600 mt-1">Complete todos los campos requeridos (*)</p>
          </div>
          <div class="hidden md:block">
            <span class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
              Paso 3 de 7
            </span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-4 md:p-6">
          <a-form
            ref="formDatosColegio"
            :model="datoscolegio"
            layout="vertical"
            class="space-y-8"
          >
            <div>
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Información Académica</h2>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <a-form-item
                    label="Año de Egreso"
                    name="egreso"
                    :rules="[
                      { required: true, message: 'Ingrese año de egreso' },
                      { min: 4, message: 'Debe tener 4 dígitos' }
                    ]"
                    class="mb-0"
                  >
                    <a-input
                      :value="datoscolegio.egreso"
                      @input="(e) => datoscolegio.egreso = e.target.value"
                      :maxlength="4"
                      placeholder="Año de egreso"
                      class="w-full"
                      size="large"
                    />
                  </a-form-item>
                </div>

                <div>
                  <a-form-item
                    label="País"
                    name="pais"
                    :rules="[{ required: true, message: 'Seleccione el país' }]"
                    class="mb-0"
                  >
                    <a-select
                      :value="datoscolegio.pais"
                      @change="(val) => datoscolegio.pais = val"
                      placeholder="Seleccionar"
                      size="large"
                      class="w-full"
                    >
                      <a-select-option :value="125">PERÚ</a-select-option>
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
              </div>
            </div>

            <div v-if="datoscolegio.pais === 125">
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Ubicación del Colegio</h2>
              </div>

              <a-form-item
                label="Buscar ubigeo (código, departamento, provincia o distrito)"
                name="ubigeo_cole"
                :rules="[{ required: true, message: 'Seleccione el ubigeo del colegio' }]"
                class="mb-0"
              >
                <a-select
                  :value="ubigeoColeSeleccionado"
                  show-search
                  :options="ubigeoColeOptions"
                  :field-names="{ value: 'key', label: 'value' }"
                  :filter-option="false"
                  @search="handleSearchUbigeoCole"
                  @change="(val) => onSelectUbigeoCole(val)"
                  placeholder="Ej: 010101, Amazonas, Chachapoyas..."
                  size="large"
                  class="w-full"
                  allow-clear
                />
              </a-form-item>

              <div class="mt-6">
                <a-form-item
                  label="Nombre del Colegio"
                  name="id_colegio"
                  :rules="[{ required: true, message: 'Seleccione el colegio' }]"
                  class="mb-0"
                >
                  <a-select
                    :value="datoscolegio.id_colegio"
                    @change="(val) => onSelectColegio(val)"
                    :options="colegios"
                    :field-names="{ value: 'value', label: 'label' }"
                    placeholder="Seleccione su colegio"
                    size="large"
                    class="w-full"
                    show-search
                    :filter-option="filterOption"
                  ></a-select>
                </a-form-item>

                <div v-if="datoscolegio.direccion" class="mt-3 px-3 py-2 bg-blue-50 border border-blue-200 rounded-md">
                  <span class="text-sm text-gray-600">Dirección: </span>
                  <span class="text-sm font-medium text-gray-900">{{ datoscolegio.direccion }}</span>
                </div>
              </div>
            </div>

            <div v-else>
              <a-form-item
                label="Nombre del Colegio"
                name="colegio"
                :rules="[{ required: true, message: 'Seleccione el colegio' }]"
                class="mb-0"
              >
                <a-select
                  :value="datoscolegio.colegio"
                  @change="(val) => datoscolegio.colegio = val"
                  size="large"
                  class="w-full"
                >
                  <a-select-option :value="200001">COLEGIO EXTRANJERO</a-select-option>
                </a-select>
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
  datoscolegio: Object,
  setFormDatosColegio: Function,
  ubigeoColeOptions: Array,
  ubigeoColeSeleccionado: [String, Number, null],
  getUbigeosCole: Function,
  onSelectUbigeoCole: Function,
  onSelectColegio: Function,
  colegios: Array,
})

const formDatosColegio = ref(null)

let searchTimer = null

const handleSearchUbigeoCole = (value) => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    props.getUbigeosCole(value)
  }, 300)
}

const filterOption = (input, option) => {
  return option.label?.toLowerCase().includes(input.toLowerCase())
}

onMounted(async () => {
  await nextTick()
  if (props.setFormDatosColegio && formDatosColegio.value) {
    props.setFormDatosColegio(formDatosColegio.value)
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
