<template>
<Head title="Estudiantes UNAP"/>
<Layout>
  <div class="oti-container">
    <!-- Header -->
    <div class="oti-header">
      <div class="oti-header-content">
        <div class="oti-header-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
          </svg>
        </div>
        <div>
          <h1 class="oti-title">Consulta de estudiantes</h1>
          <p class="oti-subtitle">Consulta de estudiantes de la UNA Puno</p>
        </div>
      </div>
    </div>

    <!-- Search Card -->
    <div class="oti-search-card">
      <div class="oti-search-wrapper">
        <a-input
          v-model:value="dni"
          placeholder="Ingrese número de documento o código de matrícula"
          size="large"
          :maxlength="20"
          allow-clear
          @pressEnter="buscar"
          class="oti-search-input"
        >
          <template #prefix>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="oti-input-icon">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
          </template>
        </a-input>
        <a-button
          type="primary"
          size="large"
          :loading="loading"
          @click="buscar"
          class="oti-search-btn"
        >
          Buscar
        </a-button>
      </div>
    </div>

    <!-- Results -->
    <div v-if="loading" class="oti-loading">
      <a-spin size="large" />
      <p class="oti-loading-text">Buscando en una Puno...</p>
    </div>

    <div v-else-if="resultados.length > 0" class="oti-results">
      <div
        v-for="(est, index) in resultados"
        :key="index"
        class="oti-student-card"
        :style="{ animationDelay: index * 0.08 + 's' }"
      >
        <!-- Avatar + Info -->
        <div class="oti-student-top">
          <div class="oti-avatar">
            {{ iniciales(est) }}
          </div>
          <div class="oti-student-name">
            <h3>{{ nombreCompleto(est) }}</h3>
            <span class="oti-student-dni">DNI: {{ est.num_doc }}</span>
          </div>
          <div class="oti-student-code">
            <div class="oti-code-label">CÓDIGO</div>
            <div class="oti-code-value">{{ est.num_mat }}</div>
          </div>
        </div>

        <!-- Details -->
        <div class="oti-student-details">
          <div class="oti-detail-item">
            <div class="oti-detail-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
              </svg>
            </div>
            <div class="oti-detail-text">
              <span class="oti-detail-label">Carrera</span>
              <span class="oti-detail-value">{{ est.car_des }}</span>
            </div>
          </div>

          <div class="oti-detail-item">
            <div class="oti-detail-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
            </div>
            <div class="oti-detail-text">
              <span class="oti-detail-label">Fecha de Nacimiento</span>
              <span class="oti-detail-value">{{ formatearFecha(est.fch_nac) }}</span>
            </div>
          </div>

          <div class="oti-detail-item">
            <div class="oti-detail-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
            </div>
            <div class="oti-detail-text">
              <span class="oti-detail-label">Fecha de Ingreso</span>
              <span class="oti-detail-value">{{ formatearFecha(est.fecha_ingreso) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="buscado" class="oti-empty">
      <div class="oti-empty-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
      </div>
      <p class="oti-empty-title">Sin resultados</p>
      <p class="oti-empty-desc">No se encontraron estudiantes para el documento o código ingresado</p>
    </div>

    <!-- Initial State -->
    <div v-else class="oti-initial">
      <div class="oti-initial-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
          <polyline points="10 9 9 9 8 9"/>
        </svg>
      </div>
      <p class="oti-initial-text">Ingrese un número de documento o código de matrícula para comenzar</p>
    </div>
  </div>
</Layout>
</template>

<script setup>
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const dni = ref('');
const resultados = ref([]);
const buscado = ref(false);
const loading = ref(false);

const nombreCompleto = (est) => {
  return [est.paterno, est.materno, est.nombres].filter(Boolean).join(' ');
};

const iniciales = (est) => {
  const partes = nombreCompleto(est).split(' ');
  return partes.slice(0, 2).map(p => p.charAt(0).toUpperCase()).join('');
};

const buscar = async () => {
  if (!dni.value || dni.value.trim().length < 1) {
    notification.warning({ message: 'Ingrese un número de documento o código' });
    return;
  }

  loading.value = true;
  buscado.value = true;

  try {
    const res = await axios.post('/admin/estudiantes-oti/buscar', { dni: dni.value.trim() });
    resultados.value = res.data.estado ? res.data.datos : [];
  } catch (error) {
    console.error('Error al buscar estudiante:', error);
    notification.error({ message: 'Error', description: 'No se pudo completar la búsqueda' });
    resultados.value = [];
  } finally {
    loading.value = false;
  }
};

const formatearFecha = (fecha) => {
  if (!fecha) return '-';
  return fecha.split('-').reverse().join('-');
};
</script>

<style scoped>
.oti-container {
  width: 100%;
}

/* Header */
.oti-header {
  background: linear-gradient(135deg, #0a3d5a 0%, #096dd9 100%);
  border-radius: 16px;
  padding: 28px 32px;
  margin-bottom: 24px;
  box-shadow: 0 4px 20px rgba(9, 109, 217, 0.15);
}
.oti-header-content {
  display: flex;
  align-items: center;
  gap: 16px;
}
.oti-header-icon {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  padding: 12px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
}
.oti-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #fff;
  margin: 0;
  line-height: 1.3;
}
.oti-subtitle {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
  margin: 2px 0 0 0;
}

/* Search */
.oti-search-card {
  background: #fff;
  border-radius: 14px;
  padding: 20px 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
.oti-search-wrapper {
  display: flex;
  gap: 12px;
}
.oti-search-input {
  flex: 1;
  border-radius: 10px;
}
.oti-search-btn {
  border-radius: 10px;
  font-weight: 600;
  padding: 0 28px;
  background: linear-gradient(135deg, #096dd9, #096dd9);
  border: none;
}
.oti-search-btn:hover {
  background: linear-gradient(135deg, #0977ec, #0977ec) !important;
}
.oti-input-icon {
  color: #bbb;
}

/* Loading */
.oti-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 60px 0;
}
.oti-loading-text {
  color: #888;
  font-size: 0.9rem;
}

/* Results */
.oti-results {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Student Card */
.oti-student-card {
  background: #fff;
  border-radius: 16px;
  padding: 24px 28px;
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
  border: 1px solid #f0f0f0;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  animation: oti-fade-in 0.4s ease both;
}
.oti-student-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.1);
}

/* Card Top: Avatar + Name + Code */
.oti-student-top {
  display: flex;
  align-items: center;
  gap: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f5f5f5;
}
.oti-avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: linear-gradient(135deg, #096dd9, #0a3d5a);
  color: #fff;
  font-weight: 700;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.oti-student-name {
  flex: 1;
  min-width: 0;
}
.oti-student-name h3 {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 2px 0;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}
.oti-student-dni {
  font-size: 0.8rem;
  color: #888;
  font-weight: 500;
}
.oti-student-code {
  text-align: right;
  flex-shrink: 0;
}
.oti-code-label {
  font-size: 0.65rem;
  color: #bbb;
  font-weight: 700;
  letter-spacing: 0.1em;
}
.oti-code-value {
  font-size: 1.25rem;
  font-weight: 800;
  color: #096dd9;
  font-family: 'Consolas', monospace;
}

/* Card Details */
.oti-student-details {
  display: flex;
  gap: 32px;
  padding-top: 16px;
  flex-wrap: wrap;
}
.oti-detail-item {
  display: flex;
  align-items: center;
  gap: 10px;
}
.oti-detail-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: #f0f5ff;
  color: #096dd9;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.oti-detail-text {
  display: flex;
  flex-direction: column;
}
.oti-detail-label {
  font-size: 0.7rem;
  color: #aaa;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.oti-detail-value {
  font-size: 0.9rem;
  color: #333;
  font-weight: 600;
}

/* Empty State */
.oti-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 60px 0;
}
.oti-empty-icon {
  color: #d9d9d9;
  margin-bottom: 16px;
}
.oti-empty-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #666;
  margin: 0 0 4px 0;
}
.oti-empty-desc {
  font-size: 0.875rem;
  color: #999;
  margin: 0;
}

/* Initial State */
.oti-initial {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 60px 0;
}
.oti-initial-icon {
  color: #d9d9d9;
  margin-bottom: 16px;
}
.oti-initial-text {
  font-size: 0.9rem;
  color: #999;
  margin: 0;
}

/* Animation */
@keyframes oti-fade-in {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 640px) {
  .oti-search-wrapper {
    flex-direction: column;
  }
  .oti-search-btn {
    width: 100%;
  }
  .oti-student-top {
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }
  .oti-student-code {
    text-align: center;
  }
  .oti-student-details {
    flex-direction: column;
    gap: 16px;
  }
}
</style>
