<template>
  <Head title="Consulta RENIEC" />

  <AppLayout>
    <div class="reniec-page">

      <!-- BUSCADOR -->
      <div class="reniec-card reniec-search-card">
        <div class="mb-8">
          <h1 class="reniec-page-title">Consulta RENIEC</h1>
        </div>

        <div class="mb-4">
          <label class="reniec-label">
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
              class="px-12 font-medium reniec-btn-search"
            >
              Buscar
            </a-button>
          </div>

          <p class="reniec-hint">
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
        class="reniec-card reniec-loading-card"
      >
        <a-spin size="large" tip="Consultando información en RENIEC..." />
        <p class="mt-4 reniec-muted">Por favor espere…</p>
      </div>

      <div v-if="personaConsultada && !cargando" class="space-y-6">

        <div class="reniec-card reniec-result-card">
          <div class="reniec-result-header">
            <h2 class="reniec-result-title">
              Información del Postulante
            </h2>
          </div>

          <div class="p-6">
            <div class="flex flex-col md:flex-row gap-8">

              <!-- FOTO -->
              <div class="md:w-1/4">
                <div class="reniec-photo-box">
                  <h3 class="reniec-section-title">Fotografía</h3>

                  <div v-if="persona.foto" class="foto-wrapper">
                    <img
                      :src="'data:image/jpeg;base64,' + persona.foto"
                      alt="Foto RENIEC"
                      class="foto-reniec"
                    />
                  </div>

                  <div v-else class="foto-placeholder">
                    Sin fotografía
                  </div>
                </div>
              </div>

              <!-- DATOS -->
              <div class="md:w-3/4">
                <h3 class="reniec-section-title reniec-section-border">
                  Datos Personales
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                  <div>
                    <label class="reniec-field-label">Apellido Paterno</label>
                    <div class="dato-box-lg">
                      {{ persona.apPrimer || '-.-' }}
                    </div>
                  </div>

                  <div>
                    <label class="reniec-field-label">Apellido Materno</label>
                    <div class="dato-box-lg">
                      {{ persona.apSegundo || '-.-' }}
                    </div>
                  </div>

                  <div>
                    <label class="reniec-field-label">DNI</label>
                    <div class="dato-box-lg">
                      {{ persona.dni || '-.-' }}
                    </div>
                  </div>
                </div>

                <div class="mb-6">
                  <label class="reniec-field-label">Nombres</label>
                  <div class="dato-box-lg">
                    {{ persona.prenombres || '-.-' }}
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                  <div>
                    <label class="reniec-field-label">Estado Civil</label>
                    <div class="dato-box-lg">
                      {{ persona.estadoCivil || '-.-' }}
                    </div>
                  </div>

                  <div>
                    <label class="reniec-field-label">Ubigeo</label>
                    <div class="dato-box-lg">
                      {{ persona.ubigeo || '-.-' }}
                    </div>
                  </div>

                  <div>
                    <label class="reniec-field-label">Restricción</label>
                    <div class="dato-box-lg">
                      {{ persona.restriccion || '-.-' }}
                    </div>
                  </div>
                </div>

                <div>
                  <label class="reniec-field-label">Dirección</label>
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
        class="reniec-card reniec-empty-card"
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
.reniec-page {
  background: var(--content-bg, #f1f5f9);
}
.reniec-card {
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.06);
  border: 1px solid var(--card-border, #e2e8f0);
  background: var(--card-bg, #ffffff);
  color: var(--card-text, #1e293b);
}
.reniec-search-card {
  padding: 24px;
  margin-bottom: 16px;
}
.reniec-page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--card-text, #1e293b);
  display: flex;
  align-items: center;
  gap: 12px;
}
.reniec-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--card-text, #1e293b);
  margin-bottom: 8px;
}
.reniec-hint {
  font-size: 0.75rem;
  color: var(--card-muted, #64748b);
  margin-top: 8px;
}
.reniec-muted {
  color: var(--card-muted, #64748b);
}
.reniec-loading-card,
.reniec-empty-card {
  padding: 32px;
  text-align: center;
}
.reniec-empty-card {
  padding: 48px;
  font-size: 0.9rem;
  color: var(--card-muted, #94a3b8);
}

/* Result card */
.reniec-result-card {
  overflow: hidden;
}
.reniec-result-header {
  padding: 16px 24px;
  background: var(--card-bg, #1e293b);
  border-bottom: 1px solid var(--card-border, #334155);
}
.reniec-result-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--card-text, #e2e8f0);
}
.reniec-section-title {
  font-weight: 600;
  color: var(--card-text, #1e293b);
  margin-bottom: 16px;
}
.reniec-section-border {
  font-size: 1.125rem;
  border-bottom: 1px solid var(--card-border, #e2e8f0);
  padding-bottom: 8px;
}
.reniec-photo-box {
  border-radius: 8px;
  padding: 16px;
  border: 1px solid var(--card-border, #e2e8f0);
  text-align: center;
  background: var(--content-bg, #f1f5f9);
}
.reniec-field-label {
  font-size: 0.75rem;
  color: var(--card-muted, #64748b);
}

/* Inputs */
:deep(.ant-input-affix-wrapper),
:deep(.ant-btn-primary),
:deep(.ant-alert) {
  border-radius: 0.75rem;
}
.reniec-btn-search {
  background-color: var(--primary-color, #2563eb) !important;
  border-color: var(--primary-color, #2563eb) !important;
}
.reniec-btn-search:hover {
  opacity: 0.9;
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
  border: 1px solid var(--card-border, #e5e7eb);
}
.foto-placeholder {
  width: 100%;
  max-width: 260px;
  aspect-ratio: 3 / 4;
  margin: 0 auto;
  background: var(--icon-bg, #f1f5f9);
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--card-muted, #64748b);
}

/* Datos */
.dato-box-lg {
  background: var(--content-bg, #f1f5f9);
  padding: 1rem;
  border-radius: 0.5rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--card-text, #1e293b);
}

/* Mobile */
@media (max-width: 640px) {
  .foto-reniec,
  .foto-placeholder {
    max-width: 180px;
  }
}
</style>
