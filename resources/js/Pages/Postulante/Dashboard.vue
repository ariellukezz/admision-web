<template>
  <Head title="Panel de Postulante" />
  <PostulanteAuthenticatedLayout>
    <div class="dashboard">

      <!-- ═══ MOBILE: Header con saludo ═══ -->
      <div class="mobile-header">
        <div class="mh-text">
          <span class="mh-greeting">¡Bienvenidos!</span>
          <span class="mh-tagline" v-if="procesoNombre">{{ procesoNombre }}</span>
        </div>
      </div>

      <!-- ═══ DESKTOP: Header ═══ -->
      <div class="desktop-header">
        <h1 class="dh-title">¡Bienvenidos!</h1>
        <p class="dh-proceso" v-if="procesoNombre">{{ procesoNombre }}</p>
      </div>

      <!-- ═══ Progress Card ═══ -->
      <div class="progress-card">
        <div class="pc-top">
          <span class="pc-label">Tu avance</span>
          <span class="pc-badge">{{ currentStepLabel }}</span>
        </div>
        <div class="pc-bar">
          <div class="pc-bar-fill" :style="{ width: progressPercent + '%' }"></div>
        </div>
        <div class="pc-steps">
          <div v-for="(step, idx) in progressDots" :key="idx" class="pc-step" :class="step.cssClass">
            <div class="pc-dot" :class="step.dotClass"></div>
            <span>{{ step.label }}</span>
          </div>
        </div>
      </div>

      <!-- ═══ MOBILE: Quick Actions ═══ -->
      <div class="quick-actions">
        <Link :href="route('postulante.seguimiento')" class="qa-item">
          <div class="qa-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg></div>
          <span>Estado</span>
        </Link>
        <Link :href="route('postulante.documentos')" class="qa-item">
          <div class="qa-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg></div>
          <span>Docs</span>
        </Link>
        <Link :href="route('postulante.paso1')" class="qa-item">
          <div class="qa-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2.25 8.25h15m-13.5 7.5h15m-14.25-3.75h15M3.75 17.25h15"/></svg></div>
          <span>Datos</span>
        </Link>
        <Link :href="route('postulante.mis-resultados')" class="qa-item">
          <div class="qa-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg></div>
          <span>Perfil</span>
        </Link>
      </div>

      <!-- ═══ Timeline Steps ═══ -->
      <div class="timeline">
        <div
          v-for="(step, idx) in timelineSteps"
          :key="step.nro"
          class="tl-step"
          :class="[step.status, { last: idx === timelineSteps.length - 1 }]"
        >
          <div class="tl-left">
            <div class="tl-circle" :class="step.status">
              <svg v-if="step.status === 'done'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
              <template v-else>{{ step.nro }}</template>
            </div>
            <div v-if="idx < timelineSteps.length - 1" class="tl-line" :class="step.status"></div>
          </div>
          <div class="tl-card" :class="step.status">
            <div class="tl-card-top">
              <span class="tl-title" :class="{ muted: step.status === 'pending' }">{{ step.titulo }}</span>
              <span v-if="step.status === 'done'" class="tl-badge done">Completado</span>
              <span v-else-if="step.status === 'active'" class="tl-badge active">En progreso</span>
            </div>
            <p class="tl-desc" :class="{ muted: step.status === 'pending' }">{{ step.descripcion }}</p>

            <!-- Sub-steps for step 1 -->
            <div v-if="step.subSteps && step.status !== 'done'" class="tl-substeps">
              <div v-for="sub in step.subSteps" :key="sub.label" class="tl-sub" :class="{ done: sub.done }">
                <span class="tl-sub-dot"></span>
                <span>{{ sub.label }}</span>
              </div>
            </div>

            <Link
              v-if="step.route && step.status === 'active'"
              :href="step.route"
              class="tl-btn"
            >
              {{ step.status === 'active' && step.nro === 1 && avance > 0 ? 'Continuar' : 'Completar' }}
            </Link>
          </div>
        </div>
      </div>

      <!-- ═══ No postulante banner ═══ -->
      <div v-if="!tienePostulante" class="banner warning">
        <div class="banner-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </div>
        <div class="banner-text">
          <span class="banner-title">Aún no has registrado tus datos</span>
          <span class="banner-detail">Comienza completando tus datos personales para iniciar tu postulación.</span>
        </div>
        <Link :href="route('postulante.paso1')" class="banner-action">Iniciar registro</Link>
      </div>

      <!-- ═══ Info Banners ═══ -->
      <div class="banners">
        <div class="banner announce">
          <div class="banner-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 008.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.944 23.944 0 0118 9.75c0 1.514-.525 2.912-1.405 4.027M5.34 4.125A23.847 23.847 0 0014.175 1.59"/></svg>
          </div>
          <div class="banner-text">
            <span class="banner-title">Inscripciones abiertas</span>
            <span class="banner-detail">Hasta el 30 de junio — Admisión 2026-I</span>
          </div>
        </div>
        <div class="banner help">
          <div class="banner-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
          </div>
          <div class="banner-text">
            <span class="banner-title">¿Necesitas ayuda?</span>
            <span class="banner-detail">Comunícate con la Oficina de Admisión</span>
          </div>
        </div>
      </div>

    </div>
  </PostulanteAuthenticatedLayout>
</template>

<script setup>
import PostulanteAuthenticatedLayout from '@/Layouts/PostulanteAuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  postulante: { type: Object, default: null },
  avance: { type: Number, default: 0 },
  progressPercent: { type: Number, default: 0 },
  currentStepLabel: { type: String, default: 'Paso 1 de 5' },
  timelineSteps: { type: Array, default: () => [] },
  tienePostulante: { type: Boolean, default: false },
});

const page = usePage();
const userName = computed(() => {
  if (props.postulante?.nombres) {
    return props.postulante.nombres.split(' ')[0];
  }
  return page.props.auth.user.name?.split(' ')[0] || 'Postulante';
});
const procesoNombre = computed(() => page.props.proceso_actual?.nombre || '');

// Progress dots for the card (5 steps: Datos, Docs, Verificar, Biométrico, Inscribir)
const progressDots = computed(() => {
  const labels = ['Datos', 'Docs', 'Verificar', 'Biométrico', 'Inscribir'];
  // avance 0 = no steps done, 1-4 = form steps done
  // Map: step 1 (Datos) done when avance >= 1, step 2 (Docs) always pending for now
  const doneCount = props.avance >= 4 ? 1 : (props.avance >= 1 ? 1 : 0);
  const currentIdx = doneCount; // current step is the one after the last done

  return labels.map((label, idx) => {
    const isDone = idx < doneCount;
    const isCurrent = idx === currentIdx;
    return {
      label,
      cssClass: isDone ? 'done' : (isCurrent ? 'current' : ''),
      dotClass: isDone ? 'done' : (isCurrent ? 'current' : ''),
    };
  });
});
</script>

<style scoped>
.dashboard { display: flex; flex-direction: column; gap: 1rem; }

/* ═══════════════════════════
   MOBILE HEADER
   ═══════════════════════════ */
.mobile-header {
  background: linear-gradient(135deg, #1B3A5C 0%, #2D4E75 100%);
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: #fff;
}

.mh-text { display: flex; flex-direction: column; }
.mh-greeting { font-size: 1.75rem; font-weight: 800; line-height: 1.1; letter-spacing: -0.5px; }
.mh-tagline { font-size: .8125rem; color: rgba(255,255,255,.7); margin-top: .375rem; font-weight: 500; }

.mh-bell {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,.1);
  border-radius: 10px;
  color: rgba(255,255,255,.7);
}
.mh-bell svg { width: 20px; height: 20px; }

/* ═══════════════════════════
   DESKTOP HEADER (hidden mobile)
   ═══════════════════════════ */
.desktop-header {
  display: none;
}

/* ═══════════════════════════
   PROGRESS CARD
   ═══════════════════════════ */
.progress-card {
  background: #fff;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 1rem 1.25rem;
}

.pc-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: .75rem;
}

.pc-label { font-size: .875rem; font-weight: 700; color: #1A202C; }

.pc-badge {
  font-size: .6875rem;
  font-weight: 700;
  background: #F6AD55;
  color: #1A202C;
  padding: .25rem .625rem;
  border-radius: 20px;
}

.pc-bar {
  height: 6px;
  background: #E2E8F0;
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: .75rem;
}

.pc-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #2D4E75, #3B6AA0);
  border-radius: 3px;
  transition: width .5s ease;
}

.pc-steps { display: flex; gap: .25rem; }
.pc-step { display: flex; flex-direction: column; align-items: center; gap: .25rem; flex: 1; }
.pc-step span { font-size: .5625rem; color: #A0AEC0; font-weight: 500; text-align: center; }
.pc-step.done span { color: #2F855A; }
.pc-step.current span { color: #1A202C; font-weight: 600; }

.pc-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #CBD5E0;
}
.pc-dot.done { background: #38A169; }
.pc-dot.current { background: #3B6AA0; box-shadow: 0 0 0 3px rgba(59,106,160,.2); }

/* ═══════════════════════════
   QUICK ACTIONS (mobile grid)
   ═══════════════════════════ */
.quick-actions {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: .75rem;
}

.qa-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .375rem;
  text-decoration: none;
  color: #1A202C;
}

.qa-icon {
  width: 52px;
  height: 52px;
  background: #fff;
  border: 1px solid #E2E8F0;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2D4E75;
  transition: all .2s;
}
.qa-icon svg { width: 22px; height: 22px; }
.qa-item:hover .qa-icon { border-color: #CBD5E0; box-shadow: 0 2px 8px rgba(0,0,0,.06); }

.qa-item span {
  font-size: .6875rem;
  font-weight: 600;
  color: #4A5568;
}

/* ═══════════════════════════
   TIMELINE
   ═══════════════════════════ */
.timeline {
  display: flex;
  flex-direction: column;
}

.tl-step {
  display: flex;
  gap: .875rem;
  min-height: 90px;
}

.tl-left {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex-shrink: 0;
  width: 36px;
}

.tl-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #E2E8F0;
  color: #A0AEC0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .8125rem;
  font-weight: 700;
  flex-shrink: 0;
}

.tl-circle.done {
  background: #38A169;
  color: #fff;
}

.tl-circle.active {
  background: #2D4E75;
  color: #fff;
}

.tl-line {
  width: 2px;
  flex: 1;
  background: #E2E8F0;
  margin: 4px 0;
}

.tl-line.done {
  background: #38A169;
}

.tl-card {
  background: #fff;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: .875rem 1rem;
  flex: 1;
  margin-bottom: .5rem;
}

.tl-card.active {
  border-color: #CBD5E0;
  box-shadow: 0 2px 8px rgba(0,0,0,.04);
}

.tl-card.done {
  border-color: #C6F6D5;
  background: #F0FFF4;
}

.tl-card-top {
  margin-bottom: .25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: .5rem;
}

.tl-title {
  font-size: .9375rem;
  font-weight: 700;
  color: #1A202C;
  display: block;
}

.tl-title.muted { color: #A0AEC0; font-weight: 500; }

.tl-badge {
  font-size: .625rem;
  font-weight: 700;
  padding: .125rem .5rem;
  border-radius: 20px;
  white-space: nowrap;
}

.tl-badge.done {
  background: #C6F6D5;
  color: #276749;
}

.tl-badge.active {
  background: #FEFCBF;
  color: #975A16;
}

.tl-desc {
  font-size: .75rem;
  color: #718096;
  line-height: 1.5;
  margin-bottom: .5rem;
}

.tl-desc.muted { color: #CBD5E0; }

/* Sub-steps inside step 1 */
.tl-substeps {
  display: flex;
  flex-direction: column;
  gap: .375rem;
  margin-bottom: .5rem;
  padding-left: .25rem;
}

.tl-sub {
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .75rem;
  color: #A0AEC0;
}

.tl-sub.done {
  color: #38A169;
}

.tl-sub-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #CBD5E0;
  flex-shrink: 0;
}

.tl-sub.done .tl-sub-dot {
  background: #38A169;
}

.tl-btn {
  display: inline-flex;
  align-items: center;
  gap: .375rem;
  background: #F6AD55;
  color: #1A202C;
  font-size: .8125rem;
  font-weight: 700;
  padding: .5rem 1rem;
  border-radius: 8px;
  text-decoration: none;
  transition: all .2s;
}
.tl-btn:hover { background: #ED8936; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(246,173,85,.35); }

/* ═══════════════════════════
   BANNERS
   ═══════════════════════════ */
.banners { display: flex; flex-direction: column; gap: .75rem; }

.banner {
  display: flex;
  align-items: flex-start;
  gap: .75rem;
  padding: .875rem 1rem;
  border-radius: 10px;
}

.banner.announce {
  background: #FFF8E1;
  border: 1px solid #FFE082;
}

.banner.help {
  background: #EBF8FF;
  border: 1px solid #BEE3F8;
}

.banner.warning {
  background: #FFFBEB;
  border: 1px solid #FCD34D;
}

.banner-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  margin-top: 1px;
}
.banner-icon svg { width: 20px; height: 20px; }
.banner.announce .banner-icon { color: #D69E2E; }
.banner.help .banner-icon { color: #3B6AA0; }
.banner.warning .banner-icon { color: #D69E2E; }

.banner-text { display: flex; flex-direction: column; gap: .125rem; flex: 1; }
.banner-title { font-size: .8125rem; font-weight: 700; color: #1A202C; }
.banner-detail { font-size: .75rem; color: #4A5568; }

.banner-action {
  font-size: .75rem;
  font-weight: 700;
  color: #2D4E75;
  white-space: nowrap;
  text-decoration: none;
  padding: .375rem .75rem;
  border-radius: 6px;
  background: rgba(45,78,117,.08);
  transition: all .2s;
  align-self: center;
}

.banner-action:hover {
  background: rgba(45,78,117,.15);
}

/* ═══════════════════════════
   DESKTOP (≥768px)
   ═══════════════════════════ */
@media (min-width: 768px) {
  .mobile-header { display: none; }

  .desktop-header {
    display: block;
    margin-bottom: .5rem;
  }

  .dh-title {
    font-size: 2rem;
    font-weight: 800;
    color: #1A202C;
    letter-spacing: -0.5px;
  }

  .dh-proceso {
    font-size: .9375rem;
    color: #3b6aa0;
    margin-top: .375rem;
    font-weight: 600;
  }

  .quick-actions {
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
  }

  .qa-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
  }
  .qa-icon svg { width: 24px; height: 24px; }
  .qa-item span { font-size: .75rem; }

  /* Desktop: timeline in two columns */
  .timeline {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }

  .tl-step {
    min-height: auto;
  }

  .tl-card {
    margin-bottom: 0;
    min-height: 100px;
  }

  .banners {
    flex-direction: row;
    gap: 1rem;
  }

  .banner { flex: 1; }
}
</style>
