<template>
<Head title="Verificar Datos"/>
<PostulanteLayout>
<div class="paso5">

  <div class="verify-banner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="banner-icon"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div>
      <h2 class="banner-title">Verifica tu información</h2>
      <p class="banner-subtitle">Revisa que todos tus datos sean correctos. Al confirmar, no podrás modificarlos.</p>
    </div>
  </div>

  <!-- Datos Personales -->
  <div class="verify-section">
    <div class="section-head">
      <h3>1. Datos Personales</h3>
      <Link :href="route('postulante.paso1')" class="edit-link">Editar</Link>
    </div>
    <div class="verify-grid">
      <div class="v-item"><span class="v-label">Documento</span><span class="v-val">{{ postulante.nro_doc }}</span></div>
      <div class="v-item"><span class="v-label">Nombres</span><span class="v-val">{{ postulante.nombres }}</span></div>
      <div class="v-item"><span class="v-label">Ap. Paterno</span><span class="v-val">{{ postulante.primer_apellido }}</span></div>
      <div class="v-item"><span class="v-label">Ap. Materno</span><span class="v-val">{{ postulante.segundo_apellido || '—' }}</span></div>
      <div class="v-item"><span class="v-label">Nacimiento</span><span class="v-val">{{ formatDate(postulante.fec_nacimiento) }}</span></div>
      <div class="v-item"><span class="v-label">Sexo</span><span class="v-val">{{ postulante.sexo === 'M' ? 'Masculino' : 'Femenino' }}</span></div>
      <div class="v-item full"><span class="v-label">Lugar Nac.</span><span class="v-val">{{ postulante.ubigeo_nacimiento_label || '—' }}</span></div>
    </div>
  </div>

  <!-- Datos de Contacto -->
  <div class="verify-section">
    <div class="section-head">
      <h3>2. Datos de Contacto</h3>
      <Link :href="route('postulante.paso2')" class="edit-link">Editar</Link>
    </div>
    <div class="verify-grid">
      <div class="v-item"><span class="v-label">Email</span><span class="v-val">{{ postulante.email }}</span></div>
      <div class="v-item"><span class="v-label">Celular</span><span class="v-val">{{ postulante.celular }}</span></div>
      <div class="v-item full"><span class="v-label">Dirección</span><span class="v-val">{{ postulante.direccion }}</span></div>
      <div class="v-item full"><span class="v-label">Residencia</span><span class="v-val">{{ postulante.ubigeo_residencia_label || '—' }}</span></div>
    </div>
  </div>

  <!-- Colegio -->
  <div class="verify-section">
    <div class="section-head">
      <h3>3. Datos del Colegio</h3>
      <Link :href="route('postulante.paso3')" class="edit-link">Editar</Link>
    </div>
    <div class="verify-grid">
      <div class="v-item"><span class="v-label">Colegio</span><span class="v-val">{{ colegio?.nombre || '—' }}</span></div>
      <div class="v-item"><span class="v-label">Año Egreso</span><span class="v-val">{{ postulante.anio_egreso || '—' }}</span></div>
    </div>
  </div>

  <!-- Apoderados -->
  <div class="verify-section">
    <div class="section-head">
      <h3>4. Apoderados</h3>
      <Link :href="route('postulante.paso4')" class="edit-link">Editar</Link>
    </div>
    <div v-if="apoderados.length === 0" class="no-apo">Sin apoderados registrados (mayor de edad)</div>
    <div v-else class="apo-cards">
      <div v-for="apo in apoderados" :key="apo.id" class="apo-card">
        <span class="apo-badge">{{ tipoLabel(apo.tipo_apoderado) }}</span>
        <span class="apo-name">{{ apo.nombres }} {{ apo.paterno }} {{ apo.materno }}</span>
        <span class="apo-dni">DNI: {{ apo.nro_documento }}</span>
      </div>
    </div>
  </div>

  <!-- Checkbox de confirmación -->
  <div class="confirm-box">
    <label class="confirm-label">
      <input type="checkbox" v-model="acepto" class="confirm-check"/>
      <span>Confirmo que los datos son correctos y acepto que no podrán ser modificados después.</span>
    </label>
  </div>

  <!-- Acciones -->
  <div class="step-actions">
    <Link :href="route('postulante.paso4')" class="btn-back">Volver</Link>
    <button :disabled="!acepto || submitting" @click="confirmar" class="btn-confirm">
      <span v-if="submitting">Confirmando...</span>
      <span v-else>Confirmar y Guardar</span>
    </button>
  </div>

</div>
</PostulanteLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PostulanteLayout from '@/Layouts/PostulanteLayout.vue';

const props = defineProps({
  postulante: { type: Object, required: true },
  colegio: { type: Object, default: null },
  apoderados: { type: Array, default: () => [] },
});

const acepto = ref(false);
const submitting = ref(false);

const formatDate = (date) => {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const tipoLabel = (tipo) => ({ 1: 'Padre', 2: 'Madre', 3: 'Apoderado' }[tipo] || 'Apoderado');

const confirmar = () => {
  if (!acepto.value || submitting.value) return;
  submitting.value = true;
  router.post(route('postulante.paso5'), {}, {
    onFinish: () => { submitting.value = false; },
  });
};
</script>

<style scoped>
.paso5 { width: 100%; }

.verify-banner {
  display: flex; align-items: center; gap: 1rem;
  margin-bottom: 1.5rem; padding: 1.25rem;
  background: linear-gradient(135deg, #92400e 0%, #d97706 100%);
  border-radius: 16px; color: #fff;
}
.banner-icon svg { width: 36px; height: 36px; }
.banner-title { font-size: 1.25rem; font-weight: 700; }
.banner-subtitle { font-size: .8125rem; opacity: .85; margin-top: 2px; }

.verify-section {
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: .75rem;
}

.section-head {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: .75rem; padding-bottom: .5rem; border-bottom: 1px solid #f1f5f9;
}
.section-head h3 { font-size: .9375rem; font-weight: 700; color: #1e293b; margin: 0; }

.edit-link {
  font-size: .75rem; font-weight: 600; color: #3b82f6;
  text-decoration: underline; cursor: pointer;
}

.verify-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem 1.25rem; }

.v-item { display: flex; flex-direction: column; gap: 1px; }
.v-item.full { grid-column: 1 / -1; }
.v-label { font-size: .6875rem; font-weight: 500; color: #94a3b8; text-transform: uppercase; }
.v-val { font-size: .875rem; color: #1e293b; font-weight: 500; }

.no-apo { text-align: center; padding: .75rem; color: #94a3b8; font-size: .8125rem; background: #f8fafc; border-radius: 8px; }

.apo-cards { display: flex; flex-direction: column; gap: .5rem; }
.apo-card {
  display: flex; flex-wrap: wrap; align-items: center; gap: .5rem;
  padding: .625rem .875rem; border: 1px solid #e2e8f0;
  border-radius: 8px; background: #fafbfc;
}
.apo-badge {
  padding: 1px 8px; border-radius: 6px; font-size: .6875rem;
  font-weight: 600; background: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe;
}
.apo-name { font-size: .8125rem; font-weight: 600; color: #1e293b; }
.apo-dni { font-size: .75rem; color: #64748b; }

.confirm-box {
  margin: 1.25rem 0; padding: 1rem; border: 2px solid #fbbf24;
  border-radius: 12px; background: #fffbeb;
}

.confirm-label {
  display: flex; align-items: flex-start; gap: .625rem;
  font-size: .8125rem; color: #92400e; cursor: pointer; line-height: 1.5;
}

.confirm-check { margin-top: 3px; width: 18px; height: 18px; accent-color: #d97706; }

.step-actions {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 0;
}

.btn-back {
  display: inline-flex; align-items: center; justify-content: center;
  height: 44px; padding: 0 1.5rem; border-radius: 10px;
  border: 1px solid #e2e8f0; background: #fff; color: #475569;
  font-size: .875rem; font-weight: 600; text-decoration: none;
  transition: all .2s; cursor: pointer;
}
.btn-back:hover { background: #f8fafc; border-color: #cbd5e0; }

.btn-confirm {
  display: inline-flex; align-items: center; justify-content: center;
  height: 44px; padding: 0 1.5rem; border-radius: 10px;
  border: none; background: #d97706; color: #fff;
  font-size: .875rem; font-weight: 600; cursor: pointer;
  transition: all .2s;
}
.btn-confirm:hover:not(:disabled) { background: #b45309; box-shadow: 0 4px 12px rgba(217,119,6,.35); }
.btn-confirm:disabled { opacity: .5; cursor: not-allowed; }

@media (max-width: 640px) {
  .verify-grid { grid-template-columns: 1fr; }
  .verify-banner { flex-direction: column; text-align: center; }
  .step-actions { flex-direction: column; gap: .75rem; }
  .btn-back, .btn-confirm { width: 100%; }
}
</style>
