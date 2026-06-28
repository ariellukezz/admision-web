<template>
<Head title="Mis Datos"/>
<PostulanteAuthenticatedLayout>
<div class="mis-datos">

  <!-- Hero -->
  <div class="hero" :class="{ 'hero-locked': confirmado }">
    <div class="hero-icon">
      <svg v-if="confirmado" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12.75L11.25 15 15 9.75m-3-7.036A9.72 9.72 0 006.75 4.5c0 4.97 4.03 9 9 9a9.72 9.72 0 003.713-.736"/><path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
    </div>
    <div>
      <h1 class="hero-title">Mis Datos</h1>
      <p class="hero-subtitle" v-if="confirmado">Tus datos fueron confirmados y no pueden modificarse</p>
      <p class="hero-subtitle" v-else>Revisa tus datos registrados</p>
    </div>
  </div>

  <!-- Sección: Datos Personales -->
  <div class="data-section">
    <div class="section-header">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="section-icon"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
      <h3>Datos Personales</h3>
    </div>
    <div class="data-grid">
      <div class="data-item">
        <span class="data-label">Tipo documento</span>
        <span class="data-value">{{ tipoDocLabel(postulante.tipo_doc) }}</span>
      </div>
      <div class="data-item">
        <span class="data-label">N° Documento</span>
        <span class="data-value">{{ postulante.nro_doc }}</span>
      </div>
      <div class="data-item">
        <span class="data-label">Primer Apellido</span>
        <span class="data-value">{{ postulante.primer_apellido }}</span>
      </div>
      <div class="data-item">
        <span class="data-label">Segundo Apellido</span>
        <span class="data-value">{{ postulante.segundo_apellido || '—' }}</span>
      </div>
      <div class="data-item" v-if="postulante.apellido_casada">
        <span class="data-label">Apellido de Casada</span>
        <span class="data-value">{{ postulante.apellido_casada }}</span>
      </div>
      <div class="data-item">
        <span class="data-label">Nombres</span>
        <span class="data-value">{{ postulante.nombres }}</span>
      </div>
      <div class="data-item">
        <span class="data-label">Fecha Nacimiento</span>
        <span class="data-value">{{ formatDate(postulante.fec_nacimiento) }}</span>
      </div>
      <div class="data-item">
        <span class="data-label">Sexo</span>
        <span class="data-value">{{ postulante.sexo === 'M' ? 'Masculino' : 'Femenino' }}</span>
      </div>
      <div class="data-item" v-if="postulante.estado_civil">
        <span class="data-label">Estado Civil</span>
        <span class="data-value">{{ estadoCivilLabel(postulante.estado_civil) }}</span>
      </div>
      <div class="data-item full">
        <span class="data-label">Lugar de Nacimiento</span>
        <span class="data-value">{{ postulante.ubigeo_nacimiento_label || '—' }}</span>
      </div>
    </div>
  </div>

  <!-- Sección: Datos de Contacto -->
  <div class="data-section">
    <div class="section-header">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="section-icon"><path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
      <h3>Datos de Contacto</h3>
    </div>
    <div class="data-grid">
      <div class="data-item">
        <span class="data-label">Email</span>
        <span class="data-value">{{ postulante.email }}</span>
      </div>
      <div class="data-item">
        <span class="data-label">Celular</span>
        <span class="data-value">{{ postulante.celular }}</span>
      </div>
      <div class="data-item full">
        <span class="data-label">Dirección</span>
        <span class="data-value">{{ postulante.direccion }}</span>
      </div>
      <div class="data-item full">
        <span class="data-label">Lugar de Residencia</span>
        <span class="data-value">{{ postulante.ubigeo_residencia_label || '—' }}</span>
      </div>
    </div>
  </div>

  <!-- Sección: Colegio -->
  <div class="data-section">
    <div class="section-header">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="section-icon"><path d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/></svg>
      <h3>Datos del Colegio</h3>
    </div>
    <div class="data-grid">
      <div class="data-item">
        <span class="data-label">Colegio</span>
        <span class="data-value">{{ colegio?.nombre || '—' }}</span>
      </div>
      <div class="data-item">
        <span class="data-label">Año de Egreso</span>
        <span class="data-value">{{ postulante.anio_egreso || '—' }}</span>
      </div>
    </div>
  </div>

  <!-- Sección: Apoderados -->
  <div class="data-section">
    <div class="section-header">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="section-icon"><path d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.744.479m.001 0a9.1 9.1 0 00.742.067M12 21c-2.17 0-4.207-.576-5.963-1.584A6.06 6.06 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772"/></svg>
      <h3>Apoderados</h3>
    </div>
    <div v-if="apoderados.length === 0" class="empty-apoderado">
      No se registraron apoderados (mayor de edad)
    </div>
    <div v-else class="apoderados-list">
      <div v-for="apo in apoderados" :key="apo.id" class="apoderado-card">
        <div class="apo-type-badge">{{ tipoApoderadoLabel(apo.tipo_apoderado) }}</div>
        <div class="data-grid">
          <div class="data-item">
            <span class="data-label">Nombres</span>
            <span class="data-value">{{ apo.nombres }}</span>
          </div>
          <div class="data-item">
            <span class="data-label">Apellido Paterno</span>
            <span class="data-value">{{ apo.paterno }}</span>
          </div>
          <div class="data-item">
            <span class="data-label">Apellido Materno</span>
            <span class="data-value">{{ apo.materno || '—' }}</span>
          </div>
          <div class="data-item">
            <span class="data-label">N° Documento</span>
            <span class="data-value">{{ apo.nro_documento }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Acciones -->
  <div class="actions" v-if="!confirmado">
    <Link :href="route('postulante.paso5')" class="btn-primary">Verificar y Confirmar</Link>
  </div>

</div>
</PostulanteAuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PostulanteAuthenticatedLayout from '@/Layouts/PostulanteAuthenticatedLayout.vue';

defineProps({
  postulante: { type: Object, required: true },
  colegio: { type: Object, default: null },
  apoderados: { type: Array, default: () => [] },
  confirmado: { type: Boolean, default: false },
  avance: { type: Number, default: 0 },
});

const formatDate = (date) => {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const tipoDocLabel = (tipo) => ({ 1: 'DNI', 2: 'Carné de Extranjería', 3: 'Pasaporte' }[tipo] || 'DNI');

const estadoCivilLabel = (code) => ({
  1: 'Soltero/a', 2: 'Casado/a', 3: 'Viudo/a', 4: 'Divorciado/a', 5: 'Conviviente Legal',
}[code] || code);

const tipoApoderadoLabel = (tipo) => ({
  1: 'Padre', 2: 'Madre', 3: 'Apoderado',
}[tipo] || 'Apoderado');
</script>

<style scoped>
.mis-datos { width: 100%; }

.hero {
  display: flex; align-items: center; gap: 1rem;
  margin-bottom: 1.5rem; padding: 1.25rem;
  background: linear-gradient(135deg, #1e3a5f 0%, #3b6aa0 100%);
  border-radius: 16px; color: #fff;
}
.hero-locked { background: linear-gradient(135deg, #276749 0%, #38a169 100%); }
.hero-icon svg { width: 36px; height: 36px; }
.hero-title { font-size: 1.25rem; font-weight: 700; }
.hero-subtitle { font-size: .8125rem; opacity: .8; margin-top: 2px; }

.data-section {
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;
}

.section-header {
  display: flex; align-items: center; gap: .5rem;
  margin-bottom: 1rem; padding-bottom: .75rem;
  border-bottom: 1px solid #f1f5f9;
}
.section-icon { width: 20px; height: 20px; color: #3b6aa0; }
.section-header h3 { font-size: .9375rem; font-weight: 700; color: #1e293b; margin: 0; }

.data-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: .75rem 1.5rem;
}

.data-item { display: flex; flex-direction: column; gap: 2px; }
.data-item.full { grid-column: 1 / -1; }
.data-label { font-size: .6875rem; font-weight: 500; color: #94a3b8; text-transform: uppercase; letter-spacing: .025em; }
.data-value { font-size: .875rem; color: #1e293b; font-weight: 500; }

.empty-apoderado {
  text-align: center; padding: 1rem; color: #94a3b8; font-size: .8125rem;
  background: #f8fafc; border-radius: 8px; border: 1px dashed #e2e8f0;
}

.apoderados-list { display: flex; flex-direction: column; gap: .75rem; }
.apoderado-card {
  padding: .875rem; border: 1px solid #e2e8f0; border-radius: 10px;
  background: #fafbfc;
}

.apo-type-badge {
  display: inline-block; padding: 2px 10px; border-radius: 6px;
  font-size: .6875rem; font-weight: 600; margin-bottom: .5rem;
  background: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe;
}

.actions {
  display: flex; justify-content: center; margin-top: 1.5rem;
}

.btn-primary {
  display: inline-flex; align-items: center; justify-content: center;
  height: 48px; padding: 0 2rem; border-radius: 10px;
  background: #3B6AA0; color: #fff; font-size: .9375rem; font-weight: 600;
  text-decoration: none; transition: all .2s;
}
.btn-primary:hover { background: #2D5A8E; box-shadow: 0 4px 12px rgba(59,106,160,.35); }

@media (max-width: 640px) {
  .data-grid { grid-template-columns: 1fr; }
  .hero { flex-direction: column; text-align: center; }
}
</style>
