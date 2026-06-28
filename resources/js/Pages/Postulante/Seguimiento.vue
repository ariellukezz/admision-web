<template>
  <Head title="Seguimiento" />
  <PostulanteAuthenticatedLayout>
    <div class="page">

      <!-- Header -->
      <div class="page-header desktop-only">
        <div>
          <h1 class="page-title">Seguimiento de Postulación</h1>
          <p class="page-subtitle">Consulta el estado actual de tu proceso de admisión.</p>
        </div>
      </div>
      <div class="page-header mobile-only">
        <h1 class="page-title">Seguimiento</h1>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading-state">
        <a-spin size="large" />
        <p>Cargando seguimiento...</p>
      </div>

      <template v-else>
        <!-- Timeline -->
        <div class="timeline">
          <div v-for="(step, i) in steps" :key="i" class="tl-step" :class="{ active: i === currentStep, completed: i < currentStep }">
            <div class="tl-left">
              <div class="tl-circle">
                <svg v-if="i < currentStep" viewBox="0 0 20 20" fill="currentColor" class="check"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                <span v-else>{{ i + 1 }}</span>
              </div>
              <div v-if="i < steps.length - 1" class="tl-line" :class="{ filled: i < currentStep }"></div>
            </div>
            <div class="tl-content">
              <span class="tl-title" :class="{ muted: i > currentStep }">{{ step.title }}</span>
              <p class="tl-desc" :class="{ muted: i > currentStep }">{{ step.desc }}</p>
              <span v-if="i < currentStep" class="tl-badge done">Completado</span>
              <span v-else-if="i === currentStep" class="tl-badge current">En proceso</span>
              <span v-else class="tl-badge pending">Pendiente</span>
            </div>
          </div>
        </div>

        <!-- Revision History -->
        <div class="revision-history" v-if="revision.veces > 0">
          <div class="rh-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
            <h3>Historial de Revisión de Documentos</h3>
          </div>

          <div class="rh-summary">
            <div class="rh-stat">
              <span class="rh-stat-num">{{ revision.veces }}</span>
              <span class="rh-stat-label">Solicitudes</span>
            </div>
            <div class="rh-stat">
              <span class="rh-stat-num">{{ revisionDocsSubidos }}</span>
              <span class="rh-stat-label">Documentos subidos</span>
            </div>
            <div class="rh-stat" :class="{ ok: revision.finalizada_at, pending: !revision.finalizada_at }">
              <span class="rh-stat-num">{{ revision.finalizada_at ? '✓' : '⏳' }}</span>
              <span class="rh-stat-label">{{ revision.finalizada_at ? 'Completada' : 'En proceso' }}</span>
            </div>
          </div>

          <!-- Log de solicitudes -->
          <div class="rh-log">
            <div class="rh-log-title">Registro de solicitudes</div>
            <div v-if="revisionPasos.length > 0" class="rh-log-list">
              <div v-for="paso in revisionPasos" :key="paso.id" class="rh-log-item">
                <div class="rh-log-icon" :class="paso.nro === 7 ? 'done' : 'sent'">
                  <svg v-if="paso.nro === 7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                </div>
                <div class="rh-log-text">
                  <span class="rh-log-name">{{ paso.nombre }}</span>
                  <span class="rh-log-date">{{ paso.fecha }}</span>
                </div>
              </div>
            </div>
            <div v-else class="rh-log-empty">
              No hay registros de solicitudes de revisión.
            </div>
          </div>

          <!-- Datos de citación si la revisión fue finalizada -->
          <div v-if="revision.finalizada_at && revision.datos_citacion" class="rh-cita">
            <div class="rh-cita-title">Datos de citación presencial</div>
            <div class="cita-row"><span>Fecha:</span> <strong>{{ revision.datos_citacion.fecha }}</strong></div>
            <div class="cita-row"><span>Hora:</span> <strong>{{ revision.datos_citacion.hora_inicio }} - {{ revision.datos_citacion.hora_fin }}</strong></div>
            <div class="cita-row"><span>Lugar:</span> <strong>{{ revision.datos_citacion.lugar }}</strong></div>
            <div v-if="revision.datos_citacion.instrucciones" class="cita-row"><span>Instrucciones:</span> <strong>{{ revision.datos_citacion.instrucciones }}</strong></div>
          </div>

          <!-- Estado actual -->
          <div class="rh-current" v-if="revision.solicitada && !revision.finalizada_at">
            <div class="rh-current-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
              <strong>Solicitud de revisión activa</strong>
              <p>Solicitada el {{ revision.solicitada_at }}.<span v-if="revision.iniciada_at"> El revisor inició la revisión el {{ revision.iniciada_at }}.</span></p>
            </div>
          </div>
        </div>
      </template>

    </div>
  </PostulanteAuthenticatedLayout>
</template>

<script setup>
import PostulanteAuthenticatedLayout from '@/Layouts/PostulanteAuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const avance = ref(0);
const revision = ref({
  solicitada: false,
  solicitada_at: null,
  veces: 0,
  iniciada_at: null,
  finalizada_at: null,
  datos_citacion: null,
});
const revisionPasos = ref([]);
const revisionDocsSubidos = ref(0);

const steps = [
  { title: 'Datos personales', desc: 'Registro de datos personales, contacto, colegio y apoderado.' },
  { title: 'Subida de documentos', desc: 'Sube DNI, certificados y documentos requeridos.' },
  { title: 'Solicitud de revisión', desc: 'Solicita que un revisor verifique tus documentos.' },
  { title: 'Revisión en curso', desc: 'El revisor está verificando tus documentos.' },
  { title: 'Revisión completada', desc: 'Revisión finalizada. Revisa el resultado y la citación.' },
];

const currentStep = computed(() => {
  if (revision.value.finalizada_at) return 5;
  if (revision.value.iniciada_at) return 4;
  if (revision.value.solicitada) return 3;
  if (revision.value.veces > 0) return 3;
  if (avance.value >= 5) return 2;
  if (avance.value >= 1) return 1;
  return 0;
});

onMounted(async () => {
  try {
    const res = await axios.get('/postulante/seguimiento-data');
    if (res.data.success) {
      avance.value = res.data.datos.avance;
      revision.value = res.data.datos.revision;
      revisionDocsSubidos.value = res.data.datos.documentos_subidos;
      // Filtrar solo los pasos relacionados con revisión
      revisionPasos.value = (res.data.datos.pasos || []).filter(p => p.nombre.includes('REVISI'));
    }
  } catch (e) {
    console.error('Error cargando seguimiento:', e);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.page { display: flex; flex-direction: column; gap: 1rem; }

.page-header { display: flex; align-items: center; justify-content: space-between; }
.page-title { font-size: 1.25rem; font-weight: 700; color: #1A202C; }
.page-subtitle { font-size: .8125rem; color: #718096; margin-top: .125rem; }

.mobile-only { display: flex; }
.desktop-only { display: none; }

/* Loading */
.loading-state { display: flex; flex-direction: column; align-items: center; gap: 1rem; padding: 3rem; }
.loading-state p { color: #64748b; font-weight: 600; }

/* Timeline */
.timeline { display: flex; flex-direction: column; }
.tl-step { display: flex; gap: .875rem; min-height: 80px; }
.tl-left { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; width: 36px; }
.tl-circle { width: 36px; height: 36px; border-radius: 50%; background: #E2E8F0; color: #A0AEC0; display: flex; align-items: center; justify-content: center; font-size: .8125rem; font-weight: 700; flex-shrink: 0; }
.tl-step.completed .tl-circle { background: #ED8936; color: #fff; }
.tl-step.active .tl-circle { background: #2D4E75; color: #fff; }
.check { width: 14px; height: 14px; }
.tl-line { width: 2px; flex: 1; background: #E2E8F0; margin: 4px 0; }
.tl-line.filled { background: #ED8936; }
.tl-content { background: #fff; border: 1px solid #E2E8F0; border-radius: 12px; padding: .875rem 1rem; flex: 1; margin-bottom: .5rem; }
.tl-step.active .tl-content { border-color: rgba(59,106,160,.25); box-shadow: 0 2px 8px rgba(0,0,0,.04); }
.tl-title { font-size: .9375rem; font-weight: 700; color: #1A202C; display: block; margin-bottom: .25rem; }
.tl-title.muted { color: #A0AEC0; font-weight: 500; }
.tl-desc { font-size: .75rem; color: #718096; line-height: 1.5; margin-bottom: .5rem; }
.tl-desc.muted { color: #CBD5E0; }
.tl-badge { display: inline-block; font-size: .625rem; font-weight: 700; padding: .25rem .625rem; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; }
.tl-badge.done { background: #C6F6D5; color: #22543D; }
.tl-badge.current { background: #FEFCBF; color: #744210; }
.tl-badge.pending { background: #EDF2F7; color: #A0AEC0; }

/* Revision History */
.revision-history { background: #fff; border: 1px solid #E2E8F0; border-radius: 14px; padding: 1.25rem; }
.rh-header { display: flex; align-items: center; gap: .625rem; margin-bottom: 1rem; padding-bottom: .75rem; border-bottom: 2px solid #f1f5f9; }
.rh-header svg { width: 22px; height: 22px; color: #3b6aa0; }
.rh-header h3 { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0; }

.rh-summary { display: flex; gap: .75rem; margin-bottom: 1.25rem; }
.rh-stat { flex: 1; display: flex; flex-direction: column; align-items: center; padding: .875rem; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; }
.rh-stat-num { font-size: 1.5rem; font-weight: 800; color: #1e293b; line-height: 1; }
.rh-stat-label { font-size: .6875rem; color: #64748b; margin-top: .25rem; text-transform: uppercase; letter-spacing: .03em; }
.rh-stat.ok { background: #dcfce7; border-color: #a7f3d0; }
.rh-stat.ok .rh-stat-num { color: #16a34a; }
.rh-stat.pending { background: #fef3c7; border-color: #fcd34d; }
.rh-stat.pending .rh-stat-num { color: #d97706; }

/* Log */
.rh-log-title { font-size: .75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .03em; margin-bottom: .625rem; }
.rh-log-list { display: flex; flex-direction: column; gap: .5rem; }
.rh-log-item { display: flex; align-items: center; gap: .625rem; padding: .625rem .75rem; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; }
.rh-log-icon { width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rh-log-icon.done { background: #dcfce7; color: #16a34a; }
.rh-log-icon.sent { background: #dbeafe; color: #2563eb; }
.rh-log-icon svg { width: 16px; height: 16px; }
.rh-log-text { display: flex; flex-direction: column; }
.rh-log-name { font-size: .75rem; font-weight: 600; color: #1e293b; }
.rh-log-date { font-size: .6875rem; color: #94a3b8; }
.rh-log-empty { font-size: .8125rem; color: #94a3b8; padding: .5rem 0; font-style: italic; }

/* Citación */
.rh-cita { margin-top: 1rem; padding: .875rem; border-radius: 10px; background: #eff6ff; border: 1px solid #bfdbfe; }
.rh-cita-title { font-size: .75rem; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: .03em; margin-bottom: .5rem; }
.cita-row { font-size: .8125rem; color: #475569; padding: .125rem 0; }
.cita-row span { color: #64748b; font-weight: 500; }

/* Estado actual */
.rh-current { display: flex; align-items: center; gap: .625rem; margin-top: 1rem; padding: .875rem 1rem; border-radius: 10px; background: #fef3c7; border: 1px solid #fcd34d; }
.rh-current-icon { width: 32px; height: 32px; border-radius: 8px; background: rgba(217,119,6,.15); display: flex; align-items: center; justify-content: center; color: #d97706; flex-shrink: 0; }
.rh-current-icon svg { width: 18px; height: 18px; }
.rh-current strong { font-size: .8125rem; font-weight: 700; color: #92400e; }
.rh-current p { font-size: .75rem; color: #b45309; margin: 2px 0 0; }

/* Desktop */
@media (min-width: 768px) {
  .mobile-only { display: none; }
  .desktop-only { display: flex; }
  .timeline { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .tl-step { min-height: auto; }
  .tl-content { margin-bottom: 0; min-height: 90px; }
}
</style>
