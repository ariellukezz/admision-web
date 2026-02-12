<template>
  <div class="bg-gray-50 min-h-screen p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
      <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Datos Personales</h1>
            <p class="text-gray-600 mt-1">Complete todos los campos requeridos (*)</p>
          </div>
          <div class="hidden md:block">
            <span class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
              Paso 1 de 3
            </span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-100">
          <div class="p-4 md:p-6">
            <div class="flex items-start bg-yellow-50 border border-yellow-100 rounded-lg p-4">
              <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.698-.833-2.464 0L4.196 16.5c-.77.833.192 2.5 1.732 2.5z"/>
              </svg>
              <div>
                <h3 class="text-sm font-medium text-yellow-800">Declaración Jurada</h3>
                <p class="text-sm text-yellow-700 mt-1">
                  La información proporcionada tiene carácter de declaración jurada y debe coincidir exactamente con sus documentos oficiales.
                </p>
              </div>
            </div>
          </div>
        </div>


        <div class="p-4 md:p-6">
          <a-form
            ref="formDatosPersonales"
            :model="datospersonales"
            layout="vertical"
            class="space-y-8"
          >
            <div>
              <div class="flex items-center mb-6">
                <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                <h2 class="text-lg font-semibold text-gray-900">Información Personal</h2>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                  <a-form-item
                    label="Primer Apellido"
                    name="primerapellido"
                    :rules="[{ required: true, message: 'Ingrese su primer apellido' }]"
                    class="mb-0"
                  >
                    <a-input
                      :value="datospersonales.primerapellido"
                      @input="(e) => { pimerapellidoInput(e); $emit('update:primerapellido', e.target.value) }"
                      placeholder="Primer apellido"
                      class="w-full"
                    />
                  </a-form-item>
                </div>
                <div>
                  <a-form-item
                    label="Segundo Apellido"
                    name="segundo_apellido"
                    class="mb-0"
                  >
                    <a-input
                      :value="datospersonales.segundo_apellido"
                      @input="(e) => { pimerapellidoInput(e); $emit('update:segundo_apellido', e.target.value) }"
                      placeholder="Segundo apellido"
                      class="w-full"
                    />
                  </a-form-item>
                </div>

                <div>
                  <a-form-item
                    label="Nombres"
                    name="nombres"
                    :rules="[{ required: true, message: 'Ingrese sus nombres' }]"
                    class="mb-0"
                  >
                    <a-input
                      :value="datospersonales.nombres"
                      @input="(e) => { nombresInput(e); $emit('update:nombres', e.target.value) }"
                      placeholder="Nombres"
                      class="w-full"
                    />
                  </a-form-item>
                </div>
              </div>
            </div>

            <div>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                  <a-form-item
                    label="Género"
                    name="sexo"
                    :rules="[{ required: true, message: 'Seleccione su género' }]"
                    class="mb-0"
                  >
                    <a-select
                      :value="datospersonales.sexo"
                      @change="(val) => $emit('update:sexo', val)"
                      placeholder="Seleccionar"
                      size="large"
                      class="w-full"
                    >
                      <a-select-option value="1">Masculino</a-select-option>
                      <a-select-option value="2">Femenino</a-select-option>
                    </a-select>
                  </a-form-item>
                </div>

                <div>
                  <a-form-item
                    label="Estado Civil"
                    name="estado_civil"
                    :rules="[{ required: true, message: 'Seleccione estado civil' }]"
                    class="mb-0"
                  >
                    <a-select
                      :value="datospersonales.estado_civil"
                      @change="(val) => $emit('update:estado_civil', val)"
                      placeholder="Seleccionar"
                      class="w-full"
                      size="large"
                    >
                      <a-select-option value="1">Soltero</a-select-option>
                      <a-select-option value="2">Casado</a-select-option>
                      <a-select-option value="3">Viudo</a-select-option>
                      <a-select-option value="4">Divorciado</a-select-option>
                    </a-select>
                  </a-form-item>
                </div>

                <div>
                  <a-form-item
                    label="Fecha de Nacimiento"
                    name="fec_nacimiento"
                    :rules="[
                      { required: true, message: 'Seleccione fecha de nacimiento' },
                      { validator: validateFechaNacimiento }
                    ]"
                    class="mb-0"
                  >
                    <a-date-picker
                      :value="datospersonales.fec_nacimiento"
                      @change="(val) => $emit('update:fec_nacimiento', val)"
                      format="DD/MM/YYYY"
                      placeholder="DD/MM/AAAA"
                      class="w-full"
                      size="large"
                    />
                  </a-form-item>
                </div>
              </div>
            </div>

            <div>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="col-span-1 md:col-span-1">
                  <a-form-item
                    label="Celular"
                    name="celular"
                    :rules="[
                      { required: true, message: 'Ingrese su celular' },
                      { min: 9, message: 'Celular inválido' },
                      { validator: validateCelular }
                    ]"
                    class="mb-0"
                  >
                    <a-input
                      :value="datospersonales.celular"
                      @input="(e) => { celularInput(e); $emit('update:celular', e.target.value) }"
                      :maxlength="9"
                      placeholder="Número de celular"
                      class="w-full"
                    />
                  </a-form-item>
                </div>

                <div class="col-span-1 md:col-span-2">
                  <a-form-item
                    label="Correo Electrónico"
                    name="correo"
                    :rules="[
                      { required: true, message: 'Ingrese su correo' },
                      { type: 'email', message: 'Correo inválido' },
                      { validator: validateCorreo }
                    ]"
                    class="mb-0"
                  >
                    <a-input
                      :value="datospersonales.correo"
                      @input="(e) => { correoInput(e); $emit('update:correo', e.target.value) }"
                      type="email"
                      placeholder="correo@ejemplo.com"
                      class="w-full"
                    />
                  </a-form-item>
                </div>
              </div>
            </div>

            <div>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                <div class="col-span-1 md:col-span-2">
                  <a-form-item
                    label="Dirección Actual"
                    name="direccion"
                    :rules="[{ required: true, message: 'Ingrese su dirección' }]"
                    class="mb-0"
                  >
                    <a-input
                      :value="datospersonales.direccion"
                      @input="(e) => $emit('update:direccion', e.target.value)"
                      placeholder="Dirección completa"
                      class="w-full"
                    />
                  </a-form-item>
                </div>

                <div class="col-span-1">
                  <a-form-item
                    label="Año de Egreso de la I.E. Superior"
                    name="egreso"
                    :rules="[{ required: true, message: 'Ingrese año de egreso' }]"
                    class="mb-0"
                  >
                    <a-input
                      :value="datospersonales.egreso"
                      @input="(e) => $emit('update:egreso', e.target.value)"
                      placeholder="Año de egreso"
                      class="w-full"
                    />
                  </a-form-item>
                </div>
              </div>
            </div>

            <div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-8">
                <div class="col-span-1">
                  <a-form-item
                    label="Ubigeo de Nacimiento"
                    name="nacimiento"
                    :rules="[{ required: true, message: 'Seleccione ubigeo de nacimiento' }]"
                    class="mb-0"
                  >
                    <a-auto-complete
                      :value="datospersonales.nacimiento"
                      @change="(val) => $emit('update:nacimiento', val)"
                      :options="ubigeosNacimiento"
                      @select="onSelectNacimiento"
                      allowClear
                    >
                      <a-input
                        placeholder="Lugar"
                        :value="buscarNacimiento"
                        @input="(e) => $emit('update:buscarNacimiento', e.target.value)"
                        class="h-12 w-full"
                      >
                        <template #suffix>
                          <a-tooltip title="Extra information">
                            <down-outlined />
                          </a-tooltip>
                        </template>
                      </a-input>
                    </a-auto-complete>
                  </a-form-item>
                </div>

                <div class="col-span-1">
                  <a-form-item
                    label="Ubigeo de Residencia"
                    name="residencia"
                    :rules="[{ required: true, message: 'Seleccione ubigeo de residencia' }]"
                    class="mb-0"
                  >
                    <a-auto-complete
                      :value="datospersonales.residencia"
                      @change="(val) => $emit('update:residencia', val)"
                      :options="residencias"
                      @select="onSelectResidencias"
                      allowClear
                    >
                      <a-input
                        placeholder="Lugar"
                        :value="buscarResidencia"
                        @input="(e) => $emit('update:buscarResidencia', e.target.value)"
                        class="h-12 w-full"
                      >
                        <template #suffix>
                          <a-tooltip title="Extra information">
                            <down-outlined />
                          </a-tooltip>
                        </template>
                      </a-input>
                    </a-auto-complete>
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
import { defineProps, ref, onMounted } from 'vue'
import { DownOutlined } from '@ant-design/icons-vue'

const formDatosPersonales = ref()

const props = defineProps({
  setFormRef: Function,
  datospersonales: Object,
  ubigeosNacimiento: Array,
  residencias: Array,
  buscarNacimiento: Object,
  buscarResidencia: Object,
  pimerapellidoInput: Function,
  nombresInput: Function,
  validateFechaNacimiento: Function,
  validateCelular: Function,
  validateCorreo: Function,
  correoInput: Function,
  onSelectNacimiento: Function,
  onSelectResidencias: Function,
  celularInput: Function,
})

onMounted(() => {
  if (props.setFormRef) {
    props.setFormRef(formDatosPersonales.value)
  }
})

defineEmits([
  'update:primerapellido',
  'update:segundo_apellido',
  'update:nombres',
  'update:sexo',
  'update:estado_civil',
  'update:fec_nacimiento',
  'update:celular',
  'update:correo',
  'update:direccion',
  'update:egreso',
  'update:nacimiento',
  'update:residencia',
  'update:buscarNacimiento',
  'update:buscarResidencia',
])
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
