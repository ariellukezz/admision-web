<template>
<Head title="Resultados"/>
<PostulanteAuthenticatedLayout>
<div class="resultados">

  <!-- Hero -->
  <div class="hero">
    <div class="hero-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
    </div>
    <div>
      <h1 class="hero-title">Resultados</h1>
      <p class="hero-subtitle">Consulta tus puntajes, rendimiento y listas de ingresantes</p>
    </div>
  </div>

  <!-- Tabs -->
  <div class="tabs">
    <button class="tab" :class="{ active: activeTab === 'mi-rendimiento' }" @click="switchTab('mi-rendimiento')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="tab-icon"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
      Mi Rendimiento
    </button>
    <button class="tab" :class="{ active: activeTab === 'ingresantes' }" @click="switchTab('ingresantes')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="tab-icon"><path d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.744.479m.001 0a9.1 9.1 0 00.742.067M12 21c-2.17 0-4.207-.576-5.963-1.584A6.06 6.06 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772"/></svg>
      Listas de Ingresantes
    </button>
  </div>

  <!-- Tab: Mi Rendimiento -->
  <div v-if="activeTab === 'mi-rendimiento'" class="tab-content">
    <div v-if="loadingRendimiento" class="loading-state"><a-spin size="large" /></div>

    <div v-else-if="rendimiento.length === 0" class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="empty-icon"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z"/></svg>
      <p>Aún no tienes resultados publicados</p>
    </div>

    <div v-else class="rendimiento-list">
      <div v-for="r in rendimiento" :key="r.id_proceso" class="rend-card">
        <div class="rend-header">
          <div>
            <span class="rend-proceso">{{ r.proceso }}</span>
            <span class="rend-anio">{{ r.anio }}</span>
          </div>
          <div class="rend-badges">
            <span class="rend-modalidad-badge">{{ r.modalidad_nombre || 'General' }}</span>
            <span class="rend-programa-badge">{{ r.programa_nombre || '—' }}</span>
          </div>
        </div>

        <div class="rend-stats">
          <div class="stat">
            <span class="stat-value accent">{{ r.mi_puntaje ?? '—' }}</span>
            <span class="stat-label">Mi puntaje</span>
          </div>
          <div class="stat">
            <span class="stat-value" :class="r.apto ? 'ok' : 'fail'">{{ r.apto ? 'Apto' : 'No apto' }}</span>
            <span class="stat-label">Estado</span>
          </div>
          <div class="stat">
            <span class="stat-value">{{ r.mi_puesto ?? r.mi_puesto_general ?? '—' }}</span>
            <span class="stat-label">Puesto</span>
          </div>
          <div class="stat">
            <span class="stat-value">{{ r.percentil }}%</span>
            <span class="stat-label">Percentil</span>
          </div>
        </div>

        <!-- Barra visual -->
        <div class="rend-bar-container">
          <div class="rend-bar-labels">
            <span>Min: {{ r.puntaje_min_modalidad }}</span>
            <span>Prom: {{ r.promedio_modalidad }}</span>
            <span>Max: {{ r.puntaje_max_modalidad }}</span>
          </div>
          <div class="rend-bar">
            <div class="rend-bar-fill" :style="{ width: barWidth(r) + '%' }"></div>
            <div class="rend-bar-marker" :style="{ left: barMarkerPos(r) + '%' }">▲</div>
          </div>
          <div class="rend-bar-info">
            Ingresantes en tu modalidad: <strong>{{ r.total_ingresantes_modalidad }}</strong>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tab: Listas de Ingresantes -->
  <div v-if="activeTab === 'ingresantes'" class="tab-content">
    <!-- Consultas restantes -->
    <div class="info-banner" v-if="consultasRestantes <= 2 && consultasRestantes > 0">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="info-icon"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
      Te quedan <strong>{{ consultasRestantes }}</strong> consultas a otros postulantes hoy
    </div>

    <!-- Selector de proceso por año -->
    <div v-if="!procesoSeleccionado" class="procesos-por-anio">
      <div v-for="grupo in procesos" :key="grupo.anio" class="anio-group">
        <h3 class="anio-title">{{ grupo.anio }}</h3>
        <div class="proceso-cards">
          <button v-for="p in grupo.procesos" :key="p.id" class="proceso-card" @click="seleccionarProceso(p)">
            <span class="proceso-name">{{ p.nombre }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="proceso-chevron"><path d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Lista de ingresantes del proceso seleccionado -->
    <div v-else class="ingresantes-view">
      <button class="btn-back" @click="procesoSeleccionado = null; ingresantes = []; filtroPrograma = ''; filtroModalidad = ''">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Volver a procesos
      </button>

      <h3 class="ingresantes-title">{{ procesoSeleccionado.nombre }}</h3>

      <!-- Búsqueda + Filtros -->
      <div class="filters-row">
        <div class="search-bar">
          <input v-model="searchQuery" type="text" placeholder="Buscar por DNI o nombre..." class="search-input" @keyup.enter="buscarIngresantes" />
          <button class="search-btn" @click="buscarIngresantes">Buscar</button>
        </div>
        <div class="filter-selects">
          <select v-model="filtroPrograma" @change="buscarIngresantes" class="filter-select">
            <option value="">Todos los programas</option>
            <option v-for="p in programas" :key="p.id" :value="p.id">{{ p.nombre }}</option>
          </select>
          <select v-model="filtroModalidad" @change="buscarIngresantes" class="filter-select">
            <option value="">Todas las modalidades</option>
            <option v-for="m in modalidades" :key="m.id" :value="m.id">{{ m.nombre }}</option>
          </select>
        </div>
      </div>

      <div v-if="loadingIngresantes" class="loading-state"><a-spin size="large" /></div>

      <div v-else-if="ingresantes.length === 0" class="empty-inline">No se encontraron resultados</div>

      <div v-else class="ingresantes-table-wrap">
        <table class="ingresantes-table">
          <thead>
            <tr>
              <th>#</th>
              <th>DNI</th>
              <th>Nombre</th>
              <th>Programa</th>
              <th>Modalidad</th>
              <th>Puntaje</th>
              <th>Puesto</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in ingresantes" :key="item.id" :class="{ 'is-me': item.dni_postulante === miDni }">
              <td>{{ idx + 1 }}</td>
              <td><span class="dni-val">{{ item.dni_postulante }}</span></td>
              <td>{{ item.paterno }} {{ item.materno }} {{ item.nombres }}</td>
              <td><span class="prog-badge">{{ item.programa_nombre || '—' }}</span></td>
              <td><span class="mod-badge">{{ item.modalidad_nombre || '—' }}</span></td>
              <td class="puntaje-val">{{ item.puntaje }}</td>
              <td>{{ item.puesto_general ?? item.puesto ?? '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="ingresantesPagination.total > ingresantesPagination.pageSize" class="pagination">
        <a-pagination
          v-model:current="ingresantesPagination.current"
          :pageSize="ingresantesPagination.pageSize"
          :total="ingresantesPagination.total"
          simple
          @change="cargarIngresantes"
        />
      </div>
    </div>
  </div>

</div>
</PostulanteAuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import PostulanteAuthenticatedLayout from '@/Layouts/PostulanteAuthenticatedLayout.vue';
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  miDni: { type: String, default: '' },
  misResultados: { type: Array, default: () => [] },
  procesos: { type: Array, default: () => [] },
  programas: { type: Array, default: () => [] },
  modalidades: { type: Array, default: () => [] },
  consultasRestantes: { type: Number, default: 5 },
});

const activeTab = ref('mi-rendimiento');
const loadingRendimiento = ref(false);
const loadingIngresantes = ref(false);
const rendimiento = ref([]);
const procesoSeleccionado = ref(null);
const ingresantes = ref([]);
const searchQuery = ref('');
const filtroPrograma = ref('');
const filtroModalidad = ref('');

const ingresantesPagination = reactive({ current: 1, pageSize: 50, total: 0 });

const switchTab = (tab) => {
  activeTab.value = tab;
  if (tab === 'mi-rendimiento' && rendimiento.value.length === 0) loadRendimiento();
};

const loadRendimiento = async () => {
  loadingRendimiento.value = true;
  try {
    const res = await axios.get('/postulante/mis-resultados/mi-rendimiento');
    if (res.data.estado) rendimiento.value = res.data.datos;
  } finally {
    loadingRendimiento.value = false;
  }
};

const seleccionarProceso = (proceso) => {
  procesoSeleccionado.value = proceso;
  searchQuery.value = '';
  filtroPrograma.value = '';
  filtroModalidad.value = '';
  cargarIngresantes(1);
};

const buscarIngresantes = () => {
  cargarIngresantes(1);
};

const cargarIngresantes = async (page) => {
  if (!procesoSeleccionado.value) return;
  loadingIngresantes.value = true;
  try {
    const params = {
      id_proceso: procesoSeleccionado.value.id,
      page: page || ingresantesPagination.current,
      per_page: ingresantesPagination.pageSize,
    };
    if (searchQuery.value) params.search = searchQuery.value;
    if (filtroPrograma.value) params.id_programa = filtroPrograma.value;
    if (filtroModalidad.value) params.id_modalidad = filtroModalidad.value;

    const res = await axios.get('/postulante/mis-resultados/proceso', { params });
    if (res.data.estado) {
      ingresantes.value = res.data.datos.data;
      ingresantesPagination.total = res.data.datos.total;
      ingresantesPagination.current = res.data.datos.current_page;
    }
  } catch (e) {
    if (e.response?.status === 429) {
      alert(e.response.data.mensaje);
    }
  } finally {
    loadingIngresantes.value = false;
  }
};

const barWidth = (r) => {
  if (!r.puntaje_max_modalidad || r.puntaje_max_modalidad == 0) return 0;
  return Math.min(100, ((r.mi_puntaje || 0) / r.puntaje_max_modalidad) * 100);
};

const barMarkerPos = (r) => {
  if (!r.puntaje_max_modalidad || r.puntaje_max_modalidad == 0) return 0;
  return Math.min(100, ((r.mi_puntaje || 0) / r.puntaje_max_modalidad) * 100);
};

onMounted(loadRendimiento);
</script>

<style scoped>
.resultados { width: 100%; }

.hero {
  display: flex; align-items: center; gap: 1rem;
  margin-bottom: 1.5rem; padding: 1.25rem;
  background: linear-gradient(135deg, #1e3a5f 0%, #3b6aa0 100%);
  border-radius: 16px; color: #fff;
}
.hero-icon svg { width: 36px; height: 36px; }
.hero-title { font-size: 1.25rem; font-weight: 700; }
.hero-subtitle { font-size: .8125rem; opacity: .8; margin-top: 2px; }

.tabs { display: flex; gap: .5rem; margin-bottom: 1.25rem; }
.tab {
  display: flex; align-items: center; gap: .375rem;
  padding: .625rem 1rem; border-radius: 10px;
  border: 1px solid #e2e8f0; background: #fff;
  font-size: .8125rem; font-weight: 500; color: #64748b;
  cursor: pointer; transition: all .2s;
}
.tab:hover { border-color: #3b6aa0; color: #3b6aa0; }
.tab.active { background: #3b6aa0; color: #fff; border-color: #3b6aa0; }
.tab-icon { width: 16px; height: 16px; }

.info-banner {
  display: flex; align-items: center; gap: .5rem;
  padding: .75rem 1rem; border-radius: 10px;
  background: #fffbeb; border: 1px solid #fde68a;
  margin-bottom: 1rem; font-size: .8125rem; color: #92400e;
}
.info-icon { width: 18px; height: 18px; flex-shrink: 0; }

.loading-state, .empty-state {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 3rem; color: #94a3b8;
}
.empty-icon { width: 48px; height: 48px; margin-bottom: .75rem; }
.empty-state p { font-size: .875rem; }

/* Rendimiento */
.rendimiento-list { display: flex; flex-direction: column; gap: 1rem; }

.rend-card {
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px; padding: 1rem 1.25rem;
}

.rend-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: .875rem; padding-bottom: .625rem; border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap; gap: .5rem;
}
.rend-proceso { font-size: .9375rem; font-weight: 700; color: #1e293b; }
.rend-anio { font-size: .75rem; color: #94a3b8; margin-left: .5rem; }
.rend-badges { display: flex; gap: .375rem; flex-wrap: wrap; }
.rend-modalidad-badge {
  padding: 2px 10px; border-radius: 6px; font-size: .6875rem; font-weight: 600;
  background: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe;
}
.rend-programa-badge {
  padding: 2px 10px; border-radius: 6px; font-size: .6875rem; font-weight: 600;
  background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;
}

.rend-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: .5rem; margin-bottom: 1rem; }

.stat { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: .5rem; background: #f8fafc; border-radius: 8px; }
.stat-value { font-size: 1.125rem; font-weight: 700; color: #1e293b; }
.stat-value.accent { color: #3b6aa0; }
.stat-value.ok { color: #16a34a; }
.stat-value.fail { color: #dc2626; }
.stat-label { font-size: .6875rem; color: #94a3b8; }

.rend-bar-container { padding: .5rem 0; }
.rend-bar-labels { display: flex; justify-content: space-between; font-size: .6875rem; color: #94a3b8; margin-bottom: 4px; }
.rend-bar { height: 8px; background: #f1f5f9; border-radius: 4px; position: relative; }
.rend-bar-fill { height: 100%; background: linear-gradient(90deg, #3b82f6, #3b6aa0); border-radius: 4px; transition: width .5s; }
.rend-bar-marker { position: absolute; top: -10px; transform: translateX(-50%); color: #f59e0b; font-size: 14px; transition: left .5s; }
.rend-bar-info { font-size: .6875rem; color: #94a3b8; margin-top: 6px; }

/* Procesos por año */
.procesos-por-anio { display: flex; flex-direction: column; gap: 1.25rem; }
.anio-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: .5rem; border-left: 3px solid #3b6aa0; padding-left: .5rem; }
.proceso-cards { display: flex; flex-direction: column; gap: .5rem; }

.proceso-card {
  display: flex; justify-content: space-between; align-items: center;
  padding: .875rem 1rem; background: #fff; border: 1px solid #e2e8f0;
  border-radius: 10px; cursor: pointer; transition: all .2s; text-align: left;
}
.proceso-card:hover { border-color: #3b6aa0; background: #f8fafc; }
.proceso-name { font-size: .875rem; font-weight: 600; color: #1e293b; }
.proceso-chevron { width: 16px; height: 16px; color: #cbd5e0; }

/* Ingresantes */
.ingresantes-view { display: flex; flex-direction: column; gap: .75rem; }

.btn-back {
  display: inline-flex; align-items: center; gap: .375rem;
  padding: .375rem .75rem; border: 1px solid #e2e8f0; border-radius: 8px;
  background: #fff; font-size: .8125rem; color: #475569; cursor: pointer;
}
.btn-back svg { width: 16px; height: 16px; }

.ingresantes-title { font-size: 1rem; font-weight: 700; color: #1e293b; }

/* Filters */
.filters-row { display: flex; flex-direction: column; gap: .5rem; }
.search-bar { display: flex; gap: .5rem; }
.search-input {
  flex: 1; height: 40px; padding: 0 .75rem; border: 1px solid #e2e8f0;
  border-radius: 8px; font-size: .8125rem; outline: none;
}
.search-input:focus { border-color: #3b6aa0; }
.search-btn {
  height: 40px; padding: 0 1rem; border: none; border-radius: 8px;
  background: #3b6aa0; color: #fff; font-size: .8125rem; font-weight: 600;
  cursor: pointer;
}
.filter-selects { display: flex; gap: .5rem; }
.filter-select {
  flex: 1; height: 38px; padding: 0 .5rem; border: 1px solid #e2e8f0;
  border-radius: 8px; font-size: .75rem; color: #475569;
  background: #fff; outline: none; cursor: pointer;
}
.filter-select:focus { border-color: #3b6aa0; }

.ingresantes-table-wrap { overflow-x: auto; }
.ingresantes-table { width: 100%; border-collapse: collapse; font-size: .8125rem; background: #fff; border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0; }
.ingresantes-table th { background: #f1f5f9; color: #475569; font-weight: 600; text-align: left; padding: .5rem .625rem; font-size: .75rem; text-transform: uppercase; }
.ingresantes-table td { padding: .4rem .625rem; border-top: 1px solid #f1f5f9; color: #475569; }
.ingresantes-table tr:hover td { background: #f8fafc; }
.ingresantes-table tr.is-me td { background: #f0fdf4; font-weight: 600; }

.dni-val { font-family: monospace; font-size: .75rem; }
.mod-badge { display: inline-block; padding: 1px 8px; border-radius: 6px; font-size: .6875rem; font-weight: 600; background: #eff6ff; color: #3b82f6; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.prog-badge { display: inline-block; padding: 1px 8px; border-radius: 6px; font-size: .6875rem; font-weight: 600; background: #f0fdf4; color: #16a34a; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.puntaje-val { font-weight: 700; color: #1e293b; }

.empty-inline { text-align: center; padding: .75rem; color: #94a3b8; font-size: .8125rem; background: #f8fafc; border-radius: 8px; border: 1px dashed #e2e8f0; }

.pagination { display: flex; justify-content: center; margin-top: .75rem; }

@media (max-width: 640px) {
  .hero { flex-direction: column; text-align: center; }
  .tabs { flex-direction: column; }
  .rend-stats { grid-template-columns: repeat(2, 1fr); }
  .search-bar { flex-direction: column; }
  .filter-selects { flex-direction: column; }
}
</style>
