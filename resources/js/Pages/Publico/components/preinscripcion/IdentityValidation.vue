<template>
  <div class="bg-gradient-to-br from-blue-50 to-gray-50 min-h-[calc(100vh-200px)] p-4 md:p-6">
    <div class="max-w-2xl mx-auto">
      <div class="mb-8 text-center">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Validación de Identidad</h1>
        <p class="text-gray-600">Complete los datos para validar su identidad</p>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 md:p-8">
          <a-form
            ref="formRef"
            :model="formState"
            layout="vertical"
            class="space-y-6"
          >
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-4">
                Tipo de Documento <span class="text-red-500">*</span>
              </label>
              <a-radio-group
                :value="datospersonales.tipo_doc"
                @change="(e) => (datospersonales.tipo_doc = e.target.value)"
                class="grid grid-cols-2 gap-2"
              >
                <a-radio-button
                  :value="1"
                  class="text-center font-semibold h-12 !flex !items-center !justify-center rounded-lg"
                >
                  DNI
                </a-radio-button>
                <a-radio-button
                  :value="3"
                  class="text-center font-semibold h-12 !flex !items-center !justify-center rounded-lg"
                >
                  Carnet Ext.
                </a-radio-button>
              </a-radio-group>
            </div>

            <div>
              <a-form-item
                label="Número de Documento"
                name="dni"
                :rules="[
                  { required: true, message: 'Ingrese su número de documento' },
                  { min: 8, max: 12, message: 'Documento inválido' }
                ]"
                class="mb-0"
              >
                <a-input
                  :value="formState.dni"
                  :maxlength="datospersonales.tipo_doc === 1 ? 8 : 12"
                  style="height: 48px;"
                  placeholder="Ingrese su número"
                  @input="(e) => { dniInput(e); formState.dni = e.target.value }"
                  class="w-full"
                >
                  <template #prefix>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                  </template>
                </a-input>
              </a-form-item>
            </div>

            <div class="relative py-2">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500 font-medium">Código de Seguridad</span>
              </div>
            </div>

            <div class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <div class="sm:col-span-2">
                  <label class="block text-sm font-semibold text-gray-900 mb-2">
                    Código mostrado
                  </label>
                  <div class="bg-gradient-to-br from-gray-100 to-gray-50 border-2 border-dashed border-gray-300 rounded-lg px-4 md:px-6 py-6 text-center hover:border-blue-300 transition-colors">
                    <div class="text-3xl md:text-4xl font-mono font-bold text-gray-800 tracking-widest select-none letter-spacing">
                      <span class="line-through opacity-80" style="font-size: 2.3rem; font-family:'Courier New', Courier, monospace">{{ codigo_aleatorio }}</span>
                    </div>
                  </div>
                </div>
                <div class="sm:col-span-1 flex items-end">
                  <a-button
                    @click="getCodigoAleatorio"
                    type="primary"
                    size="large"
                    class="w-full !h-12 !flex !items-center !justify-center rounded-lg font-semibold"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                  </a-button>
                </div>
              </div>

              <a-form-item
                label="Ingrese el código mostrado"
                name="codigo_secreto"
                :rules="[
                  { required: true, message: 'Ingrese el código de seguridad' },
                  { validator: validateCodigoSecreto }
                ]"
                class="mb-0"
              >
                <a-input
                  :value="formState.codigo_secreto"
                  :maxlength="6"
                  style="height: 48px;"
                  placeholder="Ingrese el código"
                  @input="(e) => formState.codigo_secreto = e.target.value"
                  class="w-full"
                >
                  <template #prefix>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                  </template>
                </a-input>
              </a-form-item>
            </div>

            <div class="pt-4">
              <a-button
                type="primary"
                html-type="submit"
                size="large"
                :loading="loading"
                :disabled="formState.dni.length < 8 || formState.codigo_secreto !== codigo_aleatorio || loading"
                block
                class="h-12 font-medium flex items-center justify-center"
                style="color: white;"
                @click="$emit('proceed')"
              >
                {{ loading ? 'Verificando...' : 'Iniciar Postulación' }}
              </a-button>
            </div>
          </a-form>
        </div>
      </div>
    </div>

    <!-- Modal: ¿Cargar datos registrados? -->
    <a-modal
      :open="modalCargarDatos"
      :footer="false"
      :maskClosable="false"
      :closable="false"
      centered
      width="440px"
      @cancel="rechazarCargarDatos"
    >
      <div class="text-center" style="padding: 16px 0;">
        <div class="mb-4" style="display: flex; justify-content: center;">
          <div style="width: 56px; height: 56px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center;">
            <svg class="w-7 h-7" style="color: #2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">En buena hora</h3>
        <p class="text-gray-600 mb-1" v-if="datosPrevios">
          <strong>{{ datosPrevios.nombres }} </strong>, tenemos tus datos registrados
        </p>

        <!-- Estado 1: Inicial (antes de enviar código) -->
        <template v-if="!codigoEnviado && !codigoExpirado">
          <p class="text-gray-500 text-sm mb-6">
            Para continuar y cargar tus datos, enviaremos un código de verificación a tu correo electrónico<span v-if="emailMasked">: <strong>{{ emailMasked }}</strong></span>.
          </p>
          <div class="flex gap-3 justify-center">
            <a-button size="large" @click="rechazarCargarDatos" style="min-width: 130px;">
              No, llenar manualmente
            </a-button>
            <a-button type="primary" size="large" :loading="enviandoCodigo" @click="solicitarCodigoVerificacion" style="min-width: 130px;">
              {{ enviandoCodigo ? 'Enviando...' : 'Sí, continuar' }}
            </a-button>
          </div>
        </template>

        <!-- Estado 2: Esperando código (countdown activo) -->
        <template v-if="codigoEnviado && !codigoExpirado">
          <p class="text-gray-500 text-sm mb-4">
            Ingresa el código enviado a <strong>{{ emailMasked }}</strong>
          </p>
          <div class="mb-3">
            <a-input
              :value="codigoVerificacion"
              @input="(e) => { setCodigoVerificacion(e.target.value); setCodigoError('') }"
              :maxlength="6"
              placeholder="Código de 6 dígitos"
              style="height: 48px; font-size: 18px; text-align: center; letter-spacing: 0.5rem; font-family: 'Courier New', monospace;"
            />
          </div>
          <div class="mb-4" style="font-size: 14px; color: #6b7280;">
            <span v-if="countdownSegundos > 0" style="font-weight: 600; color: #2563eb;">
              ⏱ {{ Math.floor(countdownSegundos / 60) }}:{{ String(countdownSegundos % 60).padStart(2, '0') }}
            </span>
            <a-alert v-if="codigoError" :message="codigoError" type="error" show-icon style="margin-top: 8px; text-align: left;" />
          </div>
          <div class="flex gap-3 justify-center">
            <a-button size="large" @click="rechazarCargarDatos" style="min-width: 130px;">
              No, llenar manualmente
            </a-button>
            <a-button type="primary" size="large" :loading="verificandoCodigo" :disabled="codigoVerificacion.length < 6" @click="verificarCodigoYCargar" style="min-width: 160px;">
              {{ verificandoCodigo ? 'Verificando...' : 'Verificar y cargar' }}
            </a-button>
          </div>
        </template>

        <!-- Estado 3: Código expirado -->
        <template v-if="codigoExpirado">
          <p class="text-red-500 text-sm mb-6" style="font-weight: 600;">
            ⏱ El código ha expirado
          </p>
          <div class="flex gap-3 justify-center">
            <a-button size="large" @click="rechazarCargarDatos" style="min-width: 130px;">
              No, llenar manualmente
            </a-button>
            <a-button type="primary" size="large" :loading="enviandoCodigo" @click="solicitarCodigoVerificacion" style="min-width: 130px;">
              Reenviar código
            </a-button>
          </div>
        </template>
      </div>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'

const props = defineProps({
  formState: Object,
  datospersonales: Object,
  codigo_aleatorio: [String, null],
  loading: Boolean,
  modalCargarDatos: Boolean,
  datosPrevios: [Object, null],
  enviandoCodigo: Boolean,
  verificandoCodigo: Boolean,
  codigoVerificacion: [String, null],
  codigoEnviado: Boolean,
  codigoExpirado: Boolean,
  codigoError: [String, null],
  emailMasked: [String, null],
  requiereCodigoVerificacion: Boolean,
  countdownSegundos: Number,
  setFormRef: Function,
  dniInput: Function,
  validateCodigoSecreto: Function,
  getCodigoAleatorio: Function,
  aceptarCargarDatos: Function,
  rechazarCargarDatos: Function,
  solicitarCodigoVerificacion: Function,
  verificarCodigoYCargar: Function,
  resetCodigoVerificacion: Function,
  setCodigoVerificacion: Function,
  setCodigoError: Function,
})

const emit = defineEmits(['proceed'])

const formRef = ref(null)

onMounted(async () => {
  await nextTick()
  if (props.setFormRef && formRef.value) {
    props.setFormRef(formRef.value)
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

:deep(.ant-btn-primary) {
  background-color: #2563eb !important;
  border-color: #2563eb !important;
}

:deep(.ant-btn-primary:hover) {
  background-color: #1d4ed8 !important;
  border-color: #1d4ed8 !important;
}

:deep(.ant-radio-button-wrapper-checked) {
  background-color: #2563eb !important;
  border-color: #2563eb !important;
  color: white !important;
}

:deep(.ant-radio-button-wrapper) {
  border: 2px solid #e5e7eb !important;
}

:deep(.ant-radio-button-wrapper:hover) {
  border-color: #3b82f6 !important;
}

:deep(.ant-input-lg) {
  font-size: 16px !important;
  height: 48px !important;
}

:deep(.ant-form-item-label > label) {
  color: #1f2937 !important;
  font-weight: 600 !important;
}
</style>
