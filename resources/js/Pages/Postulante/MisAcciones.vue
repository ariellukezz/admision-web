<template>
<Head title="Mis Acciones"/>
<PostulanteAuthenticatedLayout>
<div class="mis-acciones">

  <!-- Hero -->
  <div class="hero">
    <div class="hero-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <div>
      <h1 class="hero-title">Mis Acciones</h1>
      <p class="hero-subtitle">Historial de todo lo que hiciste y lo que ocurrió con tus documentos</p>
    </div>
  </div>

  <!-- Tabs -->
  <div class="tabs">
    <button class="tab" :class="{ active: activeTab === 'mis-acciones' }" @click="switchTab('mis-acciones')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="tab-icon"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
      Mis Acciones
    </button>
    <button class="tab" :class="{ active: activeTab === 'mis-documentos' }" @click="switchTab('mis-documentos')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="tab-icon"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
      Acciones en mis Documentos
    </button>
    <button class="tab" :class="{ active: activeTab === 'historial' }" @click="switchTab('historial')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="tab-icon"><path d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
      Preinscripciones / Inscripciones
    </button>
  </div>

  <!-- Mis Acciones Tab -->
  <div v-if="activeTab === 'mis-acciones'" class="tab-content">
    <div v-if="loadingMisAcciones" class="loading-state">
      <a-spin size="large" />
    </div>
    <div v-else-if="misAcciones.length === 0" class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="empty-icon"><path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <p>No tienes acciones registradas aún</p>
    </div>
    <div v-else class="timeline">
      <div v-for="item in misAcciones" :key="item.id" class="timeline-item" :class="'action-' + item.action">
        <div class="timeline-dot" :style="{ background: actionColor(item.action) }">
          <component :is="actionIcon(item.action)" />
        </div>
        <div class="timeline-card">
          <div class="timeline-header">
            <span class="timeline-action" :style="{ color: actionColor(item.action) }">{{ actionLabel(item.action) }}</span>
            <span class="timeline-date">{{ formatDate(item.created_at) }}</span>
          </div>
          <p class="timeline-desc">{{ item.description }}</p>
          <div v-if="item.old_values || item.new_values" class="timeline-changes">
            <div v-if="item.old_values" class="change-old">
              <span class="change-label">Antes:</span>
              <span class="change-value">{{ truncateJSON(item.old_values) }}</span>
            </div>
            <div v-if="item.new_values" class="change-new">
              <span class="change-label">Después:</span>
              <span class="change-value">{{ truncateJSON(item.new_values) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="misAccionesPagination.total > misAccionesPagination.pageSize" class="pagination">
      <a-pagination
        v-model:current="misAccionesPagination.current"
        :pageSize="misAccionesPagination.pageSize"
        :total="misAccionesPagination.total"
        simple
        @change="loadMisAcciones"
      />
    </div>
  </div>

  <!-- Mis Documentos Tab -->
  <div v-if="activeTab === 'mis-documentos'" class="tab-content">
    <div class="info-banner">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="info-icon"><path d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
      <span>Los revisores se muestran con un alias anónimo. No se revela su identidad.</span>
    </div>

    <div v-if="loadingMisDocumentos" class="loading-state">
      <a-spin size="large" />
    </div>
    <div v-else-if="misDocumentosAcciones.length === 0" class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="empty-icon"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
      <p>Nadie ha interactuado con tus documentos aún</p>
    </div>
    <div v-else class="timeline">
      <div v-for="item in misDocumentosAcciones" :key="item.id" class="timeline-item" :class="'action-' + item.action">
        <div class="timeline-dot" :style="{ background: actionColor(item.action) }">
          <component :is="actionIcon(item.action)" />
        </div>
        <div class="timeline-card">
          <div class="timeline-header">
            <span class="timeline-actor">
              <span class="actor-badge" :class="{ 'alias-badge': !!item.alias }">{{ item.actor_name }}</span>
            </span>
            <span class="timeline-action" :style="{ color: actionColor(item.action) }">{{ actionLabel(item.action) }}</span>
            <span class="timeline-date">{{ formatDate(item.created_at) }}</span>
          </div>
          <p class="timeline-desc">{{ item.description }}</p>
          <div v-if="item.old_values || item.new_values" class="timeline-changes">
            <div v-if="item.old_values" class="change-old">
              <span class="change-label">Antes:</span>
              <span class="change-value">{{ truncateJSON(item.old_values) }}</span>
            </div>
            <div v-if="item.new_values" class="change-new">
              <span class="change-label">Después:</span>
              <span class="change-value">{{ truncateJSON(item.new_values) }}</span>
            </div>
          </div>
          <div v-if="item.action === 'verify' && item.new_values" class="verify-result">
            <span v-if="item.new_values.verificado === 1 || item.new_values.verificado === '1'" class="verify-ok">
              ✓ Visto bueno
            </span>
            <span v-else class="verify-reject">
              ✗ Observado
            </span>
          </div>
        </div>
      </div>
    </div>
    <div v-if="misDocumentosPagination.total > misDocumentosPagination.pageSize" class="pagination">
      <a-pagination
        v-model:current="misDocumentosPagination.current"
        :pageSize="misDocumentosPagination.pageSize"
        :total="misDocumentosPagination.total"
        simple
        @change="loadMisDocumentos"
      />
    </div>
  </div>

  <!-- Historial Tab -->
  <div v-if="activeTab === 'historial'" class="tab-content">
    <div v-if="loadingHistorial" class="loading-state">
      <a-spin size="large" />
    </div>
    <div v-else-if="!historial.vinculado" class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="empty-icon"><path d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
      <p>Tu cuenta aún no está vinculada a un postulante.</p>
      <p class="empty-hint">Un revisor o administrador debe vincular tu cuenta para ver tu historial.</p>
    </div>
    <div v-else class="historial-content">
      <div v-if="historial.postulante_nombre" class="historial-header">
        <span class="historial-badge">{{ historial.postulante_nombre }}</span>
      </div>

      <h3 class="section-title">Preinscripciones</h3>
      <div v-if="historial.preinscripciones.length === 0" class="empty-inline">No tienes preinscripciones registradas</div>
      <div v-else class="historial-table-wrap">
        <table class="historial-table">
          <thead>
            <tr><th>Programa</th><th>Modalidad</th><th>Estado</th><th>Fecha</th></tr>
          </thead>
          <tbody>
            <tr v-for="p in historial.preinscripciones" :key="p.id">
              <td>{{ p.programa }}</td>
              <td>{{ p.modalidad }}</td>
              <td><span class="estado-badge" :class="estadoClass(p.estado)">{{ estadoLabel(p.estado) }}</span></td>
              <td>{{ p.fecha }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <h3 class="section-title" style="margin-top:1.5rem">Inscripciones</h3>
      <div v-if="historial.inscripciones.length === 0" class="empty-inline">No tienes inscripciones registradas</div>
      <div v-else class="historial-table-wrap">
        <table class="historial-table">
          <thead>
            <tr><th>Código</th><th>Programa</th><th>Modalidad</th><th>Estado</th><th>Fecha</th></tr>
          </thead>
          <tbody>
            <tr v-for="i in historial.inscripciones" :key="i.id">
              <td><span class="codigo-badge">{{ i.codigo }}</span></td>
              <td>{{ i.programa }}</td>
              <td>{{ i.modalidad }}</td>
              <td><span class="estado-badge" :class="estadoClass(i.estado)">{{ estadoLabel(i.estado) }}</span></td>
              <td>{{ i.fecha }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
</PostulanteAuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import PostulanteAuthenticatedLayout from '@/Layouts/PostulanteAuthenticatedLayout.vue';
import { ref, reactive, onMounted, h } from 'vue';
import axios from 'axios';

const activeTab = ref('mis-acciones');
const loadingMisAcciones = ref(false);
const loadingMisDocumentos = ref(false);
const loadingHistorial = ref(false);
const misAcciones = ref([]);
const misDocumentosAcciones = ref([]);
const historial = reactive({ vinculado: false, postulante_id: null, postulante_nombre: '', preinscripciones: [], inscripciones: [] });

const misAccionesPagination = reactive({ current: 1, pageSize: 20, total: 0 });
const misDocumentosPagination = reactive({ current: 1, pageSize: 20, total: 0 });

const switchTab = (tab) => {
  activeTab.value = tab;
  if (tab === 'mis-acciones' && misAcciones.value.length === 0) loadMisAcciones();
  if (tab === 'mis-documentos' && misDocumentosAcciones.value.length === 0) loadMisDocumentos();
  if (tab === 'historial' && !historial.vinculado && historial.preinscripciones.length === 0) loadHistorial();
};

const loadMisAcciones = async (page) => {
  loadingMisAcciones.value = true;
  try {
    const res = await axios.get('/postulante/mis-acciones/data', {
      params: { page: page || misAccionesPagination.current, per_page: misAccionesPagination.pageSize }
    });
    if (res.data.estado) {
      misAcciones.value = res.data.datos.data;
      misAccionesPagination.total = res.data.datos.total;
      misAccionesPagination.current = res.data.datos.current_page;
    }
  } finally {
    loadingMisAcciones.value = false;
  }
};

const loadMisDocumentos = async (page) => {
  loadingMisDocumentos.value = true;
  try {
    const res = await axios.get('/postulante/mis-acciones/documentos', {
      params: { page: page || misDocumentosPagination.current, per_page: misDocumentosPagination.pageSize }
    });
    if (res.data.estado) {
      misDocumentosAcciones.value = res.data.datos.data;
      misDocumentosPagination.total = res.data.datos.total;
      misDocumentosPagination.current = res.data.datos.current_page;
    }
  } finally {
    loadingMisDocumentos.value = false;
  }
};

const loadHistorial = async () => {
  loadingHistorial.value = true;
  try {
    const res = await axios.get('/postulante/mis-acciones/historial');
    if (res.data.estado) {
      Object.assign(historial, res.data.datos);
    }
  } finally {
    loadingHistorial.value = false;
  }
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const actionColor = (action) => ({
  create: '#22c55e',
  update: '#3b82f6',
  delete: '#ef4444',
  view: '#06b6d4',
  download: '#6366f1',
  verify: '#f59e0b',
  access: '#94a3b8',
  login: '#8b5cf6',
}[action] || '#94a3b8');

const actionLabel = (action) => ({
  create: 'Procesado',
  update: 'Procesado',
  delete: 'Eliminado',
  view: 'Visualizado',
  download: 'Descargado',
  verify: 'Verificado',
  access: 'Acceso',
  login: 'Inicio de sesión',
}[action] || action);

const actionIcon = (action) => ({
  create: { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'white', 'stroke-width': 2 }, [h('path', { d: 'M12 4.5v15m7.5-7.5h-15' })]) },
  update: { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'white', 'stroke-width': 2 }, [h('path', { d: 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182' })]) },
  delete: { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'white', 'stroke-width': 2 }, [h('path', { d: 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0' })]) },
  view: { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'white', 'stroke-width': 2 }, [h('path', { d: 'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z' }), h('path', { d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' })]) },
  download: { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'white', 'stroke-width': 2 }, [h('path', { d: 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3' })]) },
  verify: { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'white', 'stroke-width': 2 }, [h('path', { d: 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.346 3.745 3.745 0 01-3.346 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.346-1.043 3.745 3.745 0 01-1.043-3.346A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.346 3.746 3.746 0 013.346-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.346 1.043 3.746 3.746 0 011.043 3.346A3.745 3.745 0 0121 12z' })]) },
  login: { render: () => h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'white', 'stroke-width': 2 }, [h('path', { d: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z' })]) },
}[action]);

const truncateJSON = (val) => {
  if (!val) return '—';
  try {
    const obj = typeof val === 'string' ? JSON.parse(val) : val;
    const str = JSON.stringify(obj);
    return str.length > 120 ? str.substring(0, 120) + '...' : str;
  } catch {
    return String(val).substring(0, 120);
  }
};

const estadoLabel = (estado) => {
  if (estado === 1 || estado === '1' || estado === true) return 'Activo';
  if (estado === 0 || estado === '0' || estado === false) return 'Pendiente';
  if (estado === 2 || estado === '2') return 'Observado';
  if (estado === 3 || estado === '3') return 'Rechazado';
  return String(estado);
};

const estadoClass = (estado) => {
  if (estado === 1 || estado === '1' || estado === true) return 'estado-ok';
  if (estado === 0 || estado === '0' || estado === false) return 'estado-pendiente';
  if (estado === 2 || estado === '2') return 'estado-observado';
  if (estado === 3 || estado === '3') return 'estado-rechazado';
  return '';
};

onMounted(loadMisAcciones);
</script>

<style scoped>
.mis-acciones { width: 100%; }

.hero {
  display: flex; align-items: center; gap: 1rem;
  margin-bottom: 1.5rem; padding: 1.25rem;
  background: linear-gradient(135deg, #1e3a5f 0%, #3b6aa0 100%);
  border-radius: 16px; color: #fff;
}
.hero-icon svg { width: 36px; height: 36px; }
.hero-title { font-size: 1.25rem; font-weight: 700; }
.hero-subtitle { font-size: .8125rem; opacity: .8; margin-top: 2px; }

.tabs {
  display: flex; gap: .5rem; margin-bottom: 1.25rem;
}
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
  background: #f0f9ff; border: 1px solid #bae6fd;
  margin-bottom: 1rem; font-size: .8125rem; color: #0369a1;
}
.info-icon { width: 18px; height: 18px; flex-shrink: 0; }

.timeline { display: flex; flex-direction: column; gap: 0; }
.timeline-item {
  display: flex; gap: .75rem; position: relative;
  padding-bottom: 1.25rem;
}
.timeline-item:not(:last-child)::before {
  content: ''; position: absolute;
  left: 17px; top: 38px; bottom: 0; width: 2px;
  background: #e2e8f0;
}

.timeline-dot {
  width: 36px; height: 36px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; z-index: 1;
}
.timeline-dot svg { width: 18px; height: 18px; }

.timeline-card {
  flex: 1; background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px; padding: .875rem 1rem;
}
.timeline-header {
  display: flex; align-items: center; gap: .5rem;
  flex-wrap: wrap; margin-bottom: .25rem;
}
.timeline-action { font-size: .75rem; font-weight: 600; }
.timeline-date { font-size: .6875rem; color: #94a3b8; margin-left: auto; }
.timeline-desc { font-size: .8125rem; color: #475569; margin: 0; }

.timeline-actor { margin-right: .25rem; }
.actor-badge {
  display: inline-block; padding: 1px 8px; border-radius: 6px;
  font-size: .6875rem; font-weight: 600;
  background: #f1f5f9; color: #475569;
}
.alias-badge {
  background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe;
}

.timeline-changes {
  margin-top: .5rem; display: flex; flex-direction: column; gap: .25rem;
}
.change-old, .change-new {
  font-size: .75rem; padding: .375rem .5rem; border-radius: 6px;
}
.change-old { background: #fef2f2; color: #991b1b; }
.change-new { background: #f0fdf4; color: #166534; }
.change-label { font-weight: 600; margin-right: .25rem; }
.change-value { font-family: monospace; font-size: .6875rem; }

.verify-result {
  margin-top: .375rem; font-size: .75rem; font-weight: 600;
}
.verify-ok { color: #16a34a; }
.verify-reject { color: #dc2626; }

.loading-state, .empty-state {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; padding: 3rem; color: #94a3b8;
}
.empty-icon { width: 48px; height: 48px; margin-bottom: .75rem; }
.empty-state p { font-size: .875rem; }

.pagination {
  display: flex; justify-content: center; margin-top: 1rem;
}

@media (max-width: 640px) {
  .hero { flex-direction: column; text-align: center; }
  .tabs { flex-direction: column; }
  .timeline-card { padding: .625rem; }
  .historial-table th, .historial-table td { font-size: .6875rem; padding: .375rem; }
}

/* Historial tab */
.historial-header { margin-bottom: 1rem; }
.historial-badge {
  display: inline-block; padding: .375rem .75rem;
  border-radius: 8px; font-size: .8125rem; font-weight: 600;
  background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd;
}
.section-title {
  font-size: .9375rem; font-weight: 700; color: #1e293b;
  margin-bottom: .5rem;
}
.empty-inline {
  padding: .75rem; text-align: center; color: #94a3b8;
  font-size: .8125rem; background: #f8fafc; border-radius: 8px;
  border: 1px dashed #e2e8f0;
}
.historial-table-wrap { overflow-x: auto; }
.historial-table {
  width: 100%; border-collapse: collapse; font-size: .8125rem;
  background: #fff; border-radius: 10px; overflow: hidden;
  border: 1px solid #e2e8f0;
}
.historial-table th {
  background: #f1f5f9; color: #475569; font-weight: 600;
  text-align: left; padding: .625rem .75rem; font-size: .75rem;
  text-transform: uppercase; letter-spacing: .025em;
}
.historial-table td {
  padding: .5rem .75rem; border-top: 1px solid #f1f5f9; color: #475569;
}
.historial-table tr:hover td { background: #f8fafc; }
.estado-badge {
  display: inline-block; padding: 1px 8px; border-radius: 6px;
  font-size: .6875rem; font-weight: 600;
}
.estado-ok { background: #f0fdf4; color: #16a34a; }
.estado-pendiente { background: #fefce8; color: #a16207; }
.estado-observado { background: #fef2f2; color: #dc2626; }
.estado-rechazado { background: #fef2f2; color: #991b1b; }
.codigo-badge {
  display: inline-block; padding: 1px 8px; border-radius: 6px;
  font-size: .75rem; font-weight: 600; font-family: monospace;
  background: #f1f5f9; color: #3b6aa0;
}
.empty-hint { font-size: .75rem !important; margin-top: .25rem; opacity: .7; }
</style>
