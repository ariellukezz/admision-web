<template>
  <div class="step-actions">
    <button v-if="showBack" type="button" class="btn-back" @click="$emit('back')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
      Atrás
    </button>
    <button type="button" class="btn-next" :disabled="disabled" @click="$emit('next')">
      <span v-if="loading" class="btn-loading">
        <span class="spinner"></span> Procesando...
      </span>
      <span v-else class="btn-content">
        {{ nextLabel }}
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
      </span>
    </button>
  </div>
</template>

<script setup>
defineProps({
  nextLabel: { type: String, default: 'Continuar' },
  showBack: { type: Boolean, default: false },
  disabled: Boolean,
  loading: Boolean
});

defineEmits(['back', 'next']);
</script>

<style scoped>
.step-actions {
  display: flex;
  gap: .75rem;
  width: 100%;
}

.btn-back {
  display: flex;
  align-items: center;
  gap: .375rem;
  padding: 0 1.25rem;
  height: 48px;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  background: #fff;
  color: #4A5568;
  font-size: .9375rem;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s;
}
.btn-back svg { width: 16px; height: 16px; }
.btn-back:hover { background: #F7FAFC; border-color: #CBD5E0; }

.btn-next {
  flex: 1;
  height: 48px;
  border: none;
  border-radius: 8px;
  background: #3B6AA0;
  color: #fff;
  font-size: .9375rem;
  font-weight: 600;
  cursor: pointer;
  transition: all .25s ease;
  letter-spacing: .3px;
}
.btn-next:hover:not(:disabled) {
  background: #2D5A8E;
  box-shadow: 0 4px 12px rgba(59,106,160,.35);
  transform: translateY(-1px);
}
.btn-next:disabled { opacity: .5; cursor: not-allowed; }

.btn-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
}
.btn-content svg { width: 18px; height: 18px; }

.btn-loading { display: flex; align-items: center; justify-content: center; gap: .5rem; }
.spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
