<template>
  <Head title="Consulta RENIEC" />

  <AppLayout>
    <div class="min-h-screen bg-gray-50">

      <!-- BUSCADOR -->
      <div class="bg-white rounded-xl shadow-md p-6 mb-4 border border-gray-200">
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-[#2d3748] flex items-center gap-3">
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"
                clip-rule="evenodd" />
            </svg>
            Consulta RENIEC
          </h1>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Ingresar número de DNI
          </label>

          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
              <a-input
                v-model:value="terminoBusqueda"
                placeholder="Ejemplo: 12345678"
                maxlength="8"
                size="large"
                @pressEnter="buscar"
              >
                <template #prefix>
                  <SearchOutlined />
                </template>
              </a-input>
            </div>

            <a-button
              type="primary"
              size="large"
              :loading="cargando"
              @click="buscar"
              class="px-12 font-medium"
            >
              Buscar
            </a-button>
          </div>

          <p class="text-xs text-gray-500 mt-2">
            Ingrese 8 dígitos del documento de identidad
          </p>
        </div>

        <a-alert
          v-if="error"
          type="error"
          :message="error"
          show-icon
          class="mb-4"
        />
      </div>

      <div
        v-if="cargando"
        class="bg-white rounded-xl shadow-sm p-8 text-center border border-gray-200"
      >
        <a-spin size="large" tip="Consultando información en RENIEC..." />
        <p class="mt-4 text-gray-600">Por favor espere…</p>
      </div>

      <div v-if="personaConsultada && !cargando" class="space-y-6">

        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
          <div class="bg-[#2d3748] px-6 py-4">
            <h2 class="text-xl font-bold text-white">
              Información del Postulante
            </h2>
          </div>

          <div class="p-6">
            <div class="flex flex-col md:flex-row gap-8">

              <!-- FOTO -->
              <div class="md:w-1/4">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-center">
                  <h3 class="font-semibold text-gray-700 mb-4">Fotografía</h3>

                  <div v-if="persona.foto" class="foto-wrapper">
                    <img
                      :src="'data:image/jpeg;base64,' + persona.foto"
                      alt="Foto RENIEC"
                      class="foto-reniec"
                    />
                  </div>

                  <div
                    v-else
                    class="foto-placeholder"
                  >
                    Sin fotografía
                  </div>
                </div>
              </div>

              <!-- DATOS -->
              <div class="md:w-3/4">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                  Datos Personales
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                  <div>
                    <label class="text-xs text-gray-500">Apellido Paterno</label>
                    <div class="dato-box-lg">
                      {{ persona.apPrimer || '-.-' }}
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-gray-500">Apellido Materno</label>
                    <div class="dato-box-lg">
                      {{ persona.apSegundo || '-.-' }}
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-gray-500">DNI</label>
                    <div class="dato-box-lg">
                      {{ persona.dni || '-.-' }}
                    </div>
                  </div>
                </div>

                <div class="mb-6">
                  <label class="text-xs text-gray-500">Nombres</label>
                  <div class="dato-box-lg">
                    {{ persona.prenombres || '-.-' }}
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                  <div>
                    <label class="text-xs text-gray-500">Estado Civil</label>
                    <div class="dato-box-lg">
                      {{ persona.estadoCivil || '-.-' }}
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-gray-500">Ubigeo</label>
                    <div class="dato-box-lg">
                      {{ persona.ubigeo || '-.-' }}
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-gray-500">Restricción</label>
                    <div class="dato-box-lg">
                      {{ persona.restriccion || '-.-' }}
                    </div>
                  </div>
                </div>

                <div>
                  <label class="text-xs text-gray-500">Dirección</label>
                  <div class="dato-box-lg">
                    {{ persona.direccion || 'Dirección no especificada' }}
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>

      <!-- VACÍO -->
      <div
        v-if="!personaConsultada && !cargando && !error"
        class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-200"
      >
        Ingrese un DNI válido para realizar la consulta
      </div>

    </div>
  </AppLayout>
</template>


<script setup lang="js">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import axios from 'axios'

const terminoBusqueda = ref('')
const cargando = ref(false)
const error = ref('')
const personaConsultada = ref(false)

const persona = ref({
  coResultado: '',
  deResultado: '',
  foto: '',
  apPrimer: '',
  apSegundo: '',
  prenombres: '',
  estadoCivil: '',
  ubigeo: '',
  direccion: '',
  dni:'',
  restriccion: 'Ninguna'
})

const buscar = async () => {
  if (!/^\d{8}$/.test(terminoBusqueda.value)) {
    error.value = 'El DNI debe tener 8 dígitos'
    return
  }

  cargando.value = true
  error.value = ''
  personaConsultada.value = false

  try {
    const { data } = await axios.get(
      `get-datos-reniec/${terminoBusqueda.value}`
    )

    persona.value = {
      dni: terminoBusqueda.value,
      foto: data.foto_base64 || '',
      prenombres: data.nombres || '',
      apPrimer: data.ap_paterno || '',
      apSegundo: data.ap_materno || '',
      direccion: data.direccion || '',
      estadoCivil: '--',
      ubigeo: '--',
      restriccion: 'Ninguna'
    }
    terminoBusqueda.value = ''
    personaConsultada.value = true
  } catch (e) {
    error.value =
      e.response?.data?.mensaje ||
      e.message ||
      'Error inesperado'
  } finally {
    cargando.value = false
  }
}
</script>

<style scoped>
/* Inputs y botones */
:deep(.ant-input-affix-wrapper),
:deep(.ant-btn-primary),
:deep(.ant-alert) {
  border-radius: 0.75rem;
}

:deep(.ant-btn-primary) {
  background-color: #2d3748;
  border-color: #2d3748;
}

:deep(.ant-btn-primary:hover) {
  background-color: #233a59;
  border-color: #1f2937;
}

/* FOTO */
.foto-wrapper {
  width: 100%;
  display: flex;
  justify-content: center;
}

.foto-reniec {
  width: 100%;
  max-width: 260px;
  aspect-ratio: 3 / 4;
  object-fit: cover;
  border-radius: 0.75rem;
  border: 1px solid #e5e7eb;
}

/* Placeholder */
.foto-placeholder {
  width: 100%;
  max-width: 260px;
  aspect-ratio: 3 / 4;
  margin: 0 auto;
  background: #e5e7eb;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
}

/* Datos */
.dato-box {
  background: #f9fafb;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  font-weight: 600;
}

.dato-box-lg {
  background: #f3f4f6;
  padding: 1rem;
  border-radius: 0.5rem;
  font-size: 1.25rem;
  font-weight: 700;
}

/* Mobile */
@media (max-width: 640px) {
  .foto-reniec,
  .foto-placeholder {
    max-width: 180px;
  }
}

</style>
