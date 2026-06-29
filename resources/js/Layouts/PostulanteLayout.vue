<template>
  <PostulanteAuthenticatedLayout :hide-mobile-nav="true">
    <div class="postulante-layout">
      <!-- ═══ STEPPER (sticky top on mobile, card on desktop) ═══ -->
      <div class="stepper-card">
        <div class="stepper-header">
          <div class="stepper-icon" v-html="stepIcon"></div>
          <h1 class="stepper-title">Paso {{ currentStep }}: {{ stepTitle }}</h1>
        </div>
        <div class="stepper-row">
          <div v-for="(step, index) in steps" :key="index" class="step-item" :class="{ active: index + 1 === currentStep, completed: index + 1 < currentStep }">
            <div class="step-circle">
              <svg v-if="index + 1 < currentStep" viewBox="0 0 20 20" fill="currentColor" class="check-icon"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              <span v-else>{{ index + 1 }}</span>
            </div>
            <span class="step-label">{{ step }}</span>
            <div v-if="index < steps.length - 1" class="step-line" :class="{ filled: index + 1 < currentStep }"></div>
          </div>
        </div>
      </div>

      <!-- ═══ FORM CONTENT (scrollable) ═══ -->
      <div class="step-scroll">
        <p class="step-subtitle"><slot name="subtitle" /></p>
        <div class="step-content"><slot /></div>
      </div>

      <!-- ═══ ACTIONS (fixed bottom on mobile, inline on desktop) ═══ -->
      <div class="step-actions" v-if="$slots.actions"><slot name="actions" /></div>
    </div>
  </PostulanteAuthenticatedLayout>
</template>

<script setup>
import PostulanteAuthenticatedLayout from '@/Layouts/PostulanteAuthenticatedLayout.vue';

defineProps({
  currentStep: { type: Number, default: 1 },
  stepTitle: { type: String, default: 'Datos Personales' },
  stepIcon: { type: String, default: '' },
  steps: {
    type: Array,
    default: () => ['Datos Personales', 'Datos de Contacto', 'Datos del Colegio', 'Apoderado', 'Verificación']
  }
});
</script>

<style scoped>
/* ═══════════════════════════
   MOBILE LAYOUT
   ═══════════════════════════ */
.postulante-layout {
  display: flex;
  flex-direction: column;
  /* Fill viewport: top-bar height + this area */
  min-height: calc(100vh - 56px); /* 56px ≈ top-bar height */
  max-height: calc(100vh - 56px);
  overflow: hidden;
}

/* Stepper at top */
.stepper-card {
  background: linear-gradient(180deg, #2D4E75 0%, #4472B3 100%);
  padding: 1rem 1rem .875rem;
  color: #fff;
  flex-shrink: 0;
}

.stepper-header {
  display: flex;
  align-items: center;
  gap: .625rem;
  margin-bottom: .875rem;
}

.stepper-icon {
  width: 24px;
  height: 24px;
  color: #F6AD55;
}

.stepper-title {
  font-size: 1.0625rem;
  font-weight: 700;
  line-height: 1.3;
}

.stepper-row {
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 0 .25rem;
}

.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  flex: 1;
  max-width: 100px;
}

.step-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .8125rem;
  font-weight: 700;
  background: #8CA3BF;
  color: #fff;
  border: 2px solid rgba(255,255,255,.3);
  transition: all .25s;
  position: relative;
  z-index: 1;
}

.step-item.active .step-circle {
  background: #fff;
  color: #1A202C;
  border-color: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,.15);
}

.step-item.completed .step-circle {
  background: #ED8936;
  border-color: #ED8936;
  color: #fff;
}

.check-icon { width: 14px; height: 14px; }

.step-label {
  font-size: .5625rem;
  color: rgba(255,255,255,.55);
  margin-top: .375rem;
  text-align: center;
  line-height: 1.2;
}

.step-item.active .step-label { color: #fff; font-weight: 600; }
.step-item.completed .step-label { color: rgba(255,255,255,.75); }

.step-line {
  position: absolute;
  top: 15px;
  left: calc(50% + 18px);
  width: calc(100% - 36px);
  height: 2px;
  background: #CBD5E0;
  z-index: 0;
}

.step-line.filled { background: #ED8936; }

/* Scrollable form area */
.step-scroll {
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  padding: 1rem 1rem 0;
  min-height: 0;
}

.step-subtitle {
  font-size: .8125rem;
  color: #718096;
  line-height: 1.5;
  margin: 0 0 .875rem;
}

.step-content {
  padding-bottom: 1rem;
}

/* Fixed bottom actions */
.step-actions {
  position: sticky;
  bottom: 0;
  z-index: 10;
  flex-shrink: 0;
  display: flex;
  gap: .75rem;
  padding: .875rem 1rem;
  background: #fff;
  border-top: 1px solid #E2E8F0;
  box-shadow: 0 -2px 8px rgba(0,0,0,.06);
}

/* ═══════════════════════════
   DESKTOP (≥768px)
   ═══════════════════════════ */
@media (min-width: 768px) {
  .postulante-layout {
    min-height: auto;
    max-height: none;
    overflow: visible;
    gap: 1.25rem;
  }

  .stepper-card {
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
  }

  .step-circle {
    width: 40px;
    height: 40px;
    font-size: .875rem;
  }

  .step-label {
    font-size: .6875rem;
    font-weight: 500;
  }

  .step-item { max-width: 160px; }

  .step-line {
    top: 19px;
    left: calc(50% + 22px);
    width: calc(100% - 44px);
  }

  .step-scroll {
    overflow: visible;
    padding: 0;
  }

  .step-subtitle {
    margin-bottom: 1rem;
  }

  .step-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 1.5rem;
    padding-bottom: 0;
  }

  .step-actions {
    position: static;
    box-shadow: none;
    padding: .75rem 0 0;
    border-top: 1px solid #E2E8F0;
    background: transparent;
  }
}
</style>
