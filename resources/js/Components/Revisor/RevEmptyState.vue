<!--
  RevEmptyState — el vacío también se diseña. Tres variantes de tono:
  empty (aún no hay nada) · filtered (hay datos, tus filtros no) · error (algo falló).
-->
<template>
  <div class="rev-empty" :class="[`v-${variant}`, { 'is-compact': compact }]" role="status">
    <div class="rev-empty-mark"><RevIcon :name="markIcon" size="xl" /></div>
    <h3 class="rev-empty-title">{{ title }}</h3>
    <p v-if="description" class="rev-empty-desc">{{ description }}</p>
    <div v-if="$slots.actions" class="rev-empty-actions"><slot name="actions" /></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import RevIcon from './RevIcon.vue'

const props = defineProps({
  variant: { type: String, default: 'empty' },  // empty | filtered | error | success
  icon: { type: String, default: '' },
  title: { type: String, required: true },
  description: { type: String, default: '' },
  compact: { type: Boolean, default: false },
})
const fallback = { empty: 'inbox', filtered: 'filter', error: 'alert', success: 'check-circle' }
const markIcon = computed(() => props.icon || fallback[props.variant] || 'inbox')
</script>

<style scoped>
.rev-empty {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  text-align: center;
  padding: var(--rev-s-10) var(--rev-s-7);
  animation: rev-fade-in var(--rev-t-slow) var(--rev-ease) both;
}
.rev-empty.is-compact { padding: var(--rev-s-8) var(--rev-s-6); }
.rev-empty-mark {
  display: grid; place-items: center;
  width: 44px; height: 44px;
  border-radius: var(--rev-r-lg);
  margin-bottom: var(--rev-s-5);
  background: var(--rev-n-100);
  color: var(--rev-ink-4);
  border: 1px solid var(--rev-line);
}
.v-error .rev-empty-mark   { background: var(--rev-danger-bg);  color: var(--rev-danger);  border-color: var(--rev-danger-border); }
.v-success .rev-empty-mark { background: var(--rev-success-bg); color: var(--rev-success); border-color: var(--rev-success-border); }
.v-filtered .rev-empty-mark{ background: var(--rev-primary-50); color: var(--rev-primary-600); border-color: var(--rev-primary-200); }

.rev-empty-title { margin: 0; font-size: var(--rev-fs-lg); font-weight: 640; color: var(--rev-ink-2); letter-spacing: var(--rev-track-tight); }
.rev-empty-desc { margin: 5px 0 0; font-size: var(--rev-fs-md); color: var(--rev-ink-4); max-width: 46ch; line-height: 1.5; }
.rev-empty-actions { display: flex; gap: var(--rev-s-3); margin-top: var(--rev-s-6); }
</style>
