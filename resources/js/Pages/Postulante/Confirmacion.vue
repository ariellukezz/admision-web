<template>
  <Head title="Registro Exitoso" />
  <PostulanteAuthenticatedLayout>
    <div class="confirm-content">
      <div class="success-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <h2 class="confirm-title">¡Registro Exitoso!</h2>
      <p class="confirm-subtitle">Su preinscripción ha sido registrada correctamente. Puede seguir el estado de su postulación desde el panel.</p>

      <div class="confirm-card">
        <div class="card-row">
          <span class="card-label">DNI</span>
          <span class="card-value">{{ dni }}</span>
        </div>
        <div class="card-row">
          <span class="card-label">Postulante</span>
          <span class="card-value">{{ nombre }}</span>
        </div>
        <div class="card-row">
          <span class="card-label">Programa</span>
          <span class="card-value">{{ programa }}</span>
        </div>
      </div>

      <div class="confirm-actions">
        <Link :href="route('postulante.dashboard')" class="btn-primary">Ir al Panel</Link>
        <Link :href="route('postulante.seguimiento')" class="btn-secondary">Seguir mi Postulación</Link>
      </div>
    </div>
  </PostulanteAuthenticatedLayout>
</template>

<script setup>
import PostulanteAuthenticatedLayout from '@/Layouts/PostulanteAuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  dni: { type: String, default: '' },
  nombre: { type: String, default: '' },
  programa: { type: String, default: '' }
});
</script>

<style scoped>
.confirm-content {
  text-align: center;
  padding: 2rem 0;
  max-width: 480px;
  margin: 0 auto;
}

.success-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.25rem;
}
.success-icon svg { width: 32px; height: 32px; }

.confirm-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1A202C;
  margin-bottom: .5rem;
}

.confirm-subtitle {
  font-size: .875rem;
  color: #4A5568;
  line-height: 1.6;
  max-width: 360px;
  margin: 0 auto 1.5rem;
}

.confirm-card {
  background: #fff;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  padding: 1rem 1.25rem;
  text-align: left;
  margin-bottom: 1.5rem;
}

.card-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: .5rem 0;
}
.card-row + .card-row { border-top: 1px solid #F0F4F8; }
.card-label { font-size: .8125rem; color: #718096; }
.card-value { font-size: .875rem; font-weight: 600; color: #1A202C; }

.confirm-actions {
  display: flex;
  flex-direction: column;
  gap: .75rem;
}

.btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 48px;
  border-radius: 8px;
  background: #3B6AA0;
  color: #fff;
  font-size: .9375rem;
  font-weight: 600;
  text-decoration: none;
  transition: all .2s;
}
.btn-primary:hover { background: #2D5A8E; box-shadow: 0 4px 12px rgba(59,106,160,.35); }

.btn-secondary {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 48px;
  border-radius: 10px;
  border: 1px solid #E2E8F0;
  background: #fff;
  color: #4A5568;
  font-size: .9375rem;
  font-weight: 600;
  text-decoration: none;
  transition: all .2s;
}
.btn-secondary:hover { background: #F7FAFC; border-color: #CBD5E0; }

@media (min-width: 768px) {
  .confirm-actions { flex-direction: row; gap: 1rem; }
  .btn-primary, .btn-secondary { flex: 1; }
}
</style>
