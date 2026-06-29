<template>
  <Head title="Solicitudes de Revisión" />
  <AuthenticatedLayout pagina="Solicitudes de Revisión">
    <div class="sr-page">

      <!-- Header -->
      <div class="sr-hero">
        <div class="sr-hero-content">
          <div class="sr-hero-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
          </div>
          <div>
            <h1 class="sr-hero-title">Solicitudes de Revisión</h1>
            <p class="sr-hero-subtitle">Postulantes que han solicitado la revisión de sus documentos</p>
          </div>
        </div>
        <div class="sr-hero-stats" v-if="solicitudes.data?.length">
          <div class="sr-stat"><span class="sr-stat-num">{{ solicitudes.total }}</span><span class="sr-stat-label">Pendientes</span></div>
        </div>
      </div>

      <!-- Search -->
      <div class="sr-search">
        <a-input-search
          v-model:value="busqueda"
          placeholder="Buscar por nombre o DNI..."
          style="max-width: 400px;"
          @search="buscar"
          allow-clear
        />
      </div>

      <!-- Table -->
      <div class="sr-table-wrap" v-if="solicitudes.data?.length">
        <table class="sr-table">
          <thead>
            <tr>
              <th>Postulante</th>
              <th>DNI</th>
              <th>Modalidad</th>
              <th>Fecha solicitud</th>
              <th>Veces</th>
              <th>Documentos</th>
              <th>Estado</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in solicitudes.data" :key="s.id" class="sr-row">
              <td>
                <div class="sr-name">{{ s.nombre_completo }}</div>
              </td>
              <td><span class="sr-dni">{{ s.nro_doc }}</span></td>
              <td><span class="sr-modalidad">{{ s.modalidad }}</span></td>
              <td>
                <div class="sr-date">{{ s.revision_solicitada_at }}</div>
                <div class="sr-date-diff">{{ s.revision_solicitada_at_diff }}</div>
              </td>
              <td>
                <span class="sr-veces" :class="{ insist: s.veces_revision_solicitada > 1 }">
                  {{ s.veces_revision_solicitada }}
                  {{ s.veces_revision_solicitada > 1 ? 'veces' : 'vez' }}
                </span>
              </td>
              <td>
                <div class="sr-docs">
                  <span class="sr-docs-total">{{ s.documentos_subidos }}</span>
                  <span class="sr-docs-sep">/</span>
                  <span class="sr-docs-verified">{{ s.documentos_verificados }} ✓</span>
                </div>
              </td>
              <td>
                <span v-if="s.revision_finalizada_at" class="sr-badge completed">Ya atendida</span>
                <span v-else-if="s.revision_iniciada_at" class="sr-badge in-progress">En curso</span>
                <span v-else class="sr-badge pending">Pendiente</span>
              </td>
              <td>
                <Link :href="`/revisor/postulante/${s.nro_doc}?solicitud=${s.solicitud_id}`" class="sr-btn">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.036 12.322a1.012 1.012 0 010-.639C3.427 4.923 7.628 1.5 12 1.5c4.372 0 8.573 3.423 9.963 9.183a1.012 1.012 0 010 .639C20.577 17.077 16.372 20.5 12 20.5c-4.372 0-8.573-3.423-9.963-9.178z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  Ver documentos
                </Link>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="sr-pagination" v-if="solicitudes.last_page > 1">
          <a-pagination
            v-model:current="pagina"
            :total="solicitudes.total"
            :pageSize="solicitudes.per_page"
            @change="cambiarPagina"
            show-less-items
          />
        </div>
      </div>

      <!-- Empty -->
      <div class="sr-empty" v-else>
        <div class="sr-empty-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
        </div>
        <h3>No hay solicitudes de revisión</h3>
        <p>Cuando los postulantes soliciten revisión de sus documentos, aparecerán aquí.</p>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { useNotificaciones } from '@/composables/useFcm';

const props = defineProps({
  solicitudes: { type: Object, required: true },
  busqueda: { type: String, default: '' },
});

const busqueda = ref(props.busqueda);
const pagina = ref(props.solicitudes.current_page || 1);
let fcmListener = null;

const buscar = (value) => {
  router.get('/revisor/solicitudes-revision', { busqueda: value }, { preserveState: true });
};

const cambiarPagina = (page) => {
  router.get('/revisor/solicitudes-revision', { busqueda: busqueda.value, page }, { preserveState: true });
};

onMounted(() => {
  const fcm = useNotificaciones();
  fcmListener = () => router.reload({ only: ['solicitudes'], preserveScroll: true, preserveState: true });
  fcm.fcmEventTarget.addEventListener('fcm-message', fcmListener);
});

onUnmounted(() => {
  if (fcmListener) {
    const fcm = useNotificaciones();
    fcm.fcmEventTarget.removeEventListener('fcm-message', fcmListener);
  }
});
</script>

<style scoped>
.sr-page { display: flex; flex-direction: column; gap: 1.25rem; padding: 1rem; }

/* Hero */
.sr-hero { display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #1B3A5C, #2D5A8E); border-radius: 16px; padding: 1.5rem 1.75rem; color: #fff; }
.sr-hero-content { display: flex; align-items: center; gap: 1rem; }
.sr-hero-icon { width: 48px; height: 48px; background: rgba(255,255,255,.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sr-hero-icon svg { width: 24px; height: 24px; }
.sr-hero-title { font-size: 1.375rem; font-weight: 700; margin: 0; }
.sr-hero-subtitle { font-size: .8125rem; color: rgba(255,255,255,.7); margin-top: .25rem; }
.sr-hero-stats { display: flex; gap: 1rem; }
.sr-stat { display: flex; flex-direction: column; align-items: center; background: rgba(255,255,255,.1); border-radius: 10px; padding: .5rem 1rem; }
.sr-stat-num { font-size: 1.5rem; font-weight: 800; }
.sr-stat-label { font-size: .6875rem; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .5px; }

/* Search */
.sr-search { display: flex; justify-content: flex-end; }

/* Table */
.sr-table-wrap { background: #fff; border: 1px solid #eef2f7; border-radius: 14px; overflow: hidden; }
.sr-table { width: 100%; border-collapse: collapse; }
.sr-table th { text-align: left; padding: .875rem 1rem; background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: .75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; }
.sr-table td { padding: .875rem 1rem; border-bottom: 1px solid #f1f5f9; }
.sr-row:hover { background: #f8fafc; }
.sr-row:last-child td { border-bottom: none; }
.sr-name { font-size: .875rem; font-weight: 600; color: #1e293b; text-transform: uppercase; }
.sr-dni { font-size: .8125rem; font-weight: 700; color: #3b82f6; font-family: monospace; }
.sr-modalidad { font-size: .75rem; font-weight: 600; color: #6366f1; background: #eef2ff; padding: .125rem .5rem; border-radius: 6px; white-space: nowrap; }
.sr-date { font-size: .8125rem; color: #1e293b; font-weight: 500; }
.sr-date-diff { font-size: .6875rem; color: #94a3b8; }
.sr-veces { font-size: .75rem; font-weight: 600; color: #16a34a; background: #dcfce7; padding: .125rem .5rem; border-radius: 100px; }
.sr-veces.insist { color: #d97706; background: #fef3c7; }
.sr-docs { font-size: .8125rem; }
.sr-docs-total { font-weight: 700; color: #1e293b; }
.sr-docs-sep { color: #cbd5e1; margin: 0 .125rem; }
.sr-docs-verified { font-weight: 600; color: #16a34a; }
.sr-badge { display: inline-block; font-size: .6875rem; font-weight: 700; padding: .125rem .625rem; border-radius: 100px; text-transform: uppercase; letter-spacing: .3px; }
.sr-badge.done, .sr-badge.completed { background: #dcfce7; color: #16a34a; }
.sr-badge.pending { background: #fef3c7; color: #d97706; }
.sr-badge.in-progress { background: #dbeafe; color: #2563eb; }
.sr-btn { display: inline-flex; align-items: center; gap: .375rem; padding: .375rem .875rem; border-radius: 8px; background: #eff6ff; color: #2563eb; font-size: .75rem; font-weight: 700; text-decoration: none; transition: all .2s; }
.sr-btn svg { width: 14px; height: 14px; }
.sr-btn:hover { background: #dbeafe; transform: translateY(-1px); }
.sr-pagination { display: flex; justify-content: flex-end; padding: 1rem; }

/* Empty */
.sr-empty { background: #fff; border: 1px solid #eef2f7; border-radius: 14px; padding: 3rem 2rem; text-align: center; }
.sr-empty-icon { margin-bottom: 1rem; }
.sr-empty-icon svg { width: 56px; height: 56px; color: #e2e8f0; }
.sr-empty h3 { font-size: 1rem; font-weight: 700; color: #475569; margin: 0 0 .25rem; }
.sr-empty p { font-size: .8125rem; color: #94a3b8; margin: 0; }

@media (max-width: 768px) {
  .sr-hero { flex-direction: column; align-items: flex-start; gap: 1rem; }
  .sr-table-wrap { overflow-x: auto; }
  .sr-table { min-width: 700px; }
}
</style>
