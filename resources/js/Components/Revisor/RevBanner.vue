<!--
  RevBanner — mensaje contextual dentro del flujo de trabajo.
  Explica el estado actual y, cuando procede, qué hacer a continuación.
-->
<template>
  <div v-if="visible" class="rev-banner" :class="[`t-${tone}`]" role="status">
    <span class="rev-banner-mark"><RevIcon :name="markIcon" size="lg" /></span>
    <div class="rev-banner-copy">
      <strong v-if="title" class="rev-banner-title">{{ title }}</strong>
      <span v-if="$slots.default || description" class="rev-banner-text"><slot>{{ description }}</slot></span>
    </div>
    <div v-if="$slots.actions" class="rev-banner-actions"><slot name="actions" /></div>
    <button v-if="dismissible" class="rev-banner-close" type="button" aria-label="Cerrar aviso" @click="visible = false">
      <RevIcon name="close" size="sm" />
    </button>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import RevIcon from './RevIcon.vue'

const props = defineProps({
  tone: { type: String, default: 'info' },   // info | success | warning | danger | neutral
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  icon: { type: String, default: '' },
  dismissible: { type: Boolean, default: false },
})
const visible = ref(true)
const fallback = { info: 'info', success: 'check-circle', warning: 'alert', danger: 'alert-circle', neutral: 'info' }
const markIcon = computed(() => props.icon || fallback[props.tone] || 'info')
</script>

<style scoped>
.rev-banner {
  display: flex; align-items: flex-start; gap: var(--rev-s-5);
  padding: 11px var(--rev-s-5);
  border: 1px solid transparent;
  border-radius: var(--rev-r-lg);
  animation: rev-rise var(--rev-t-base) var(--rev-ease-out) both;
}
.rev-banner-mark { flex: none; margin-top: 1px; }
.rev-banner-copy { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1 1 auto; }
.rev-banner-title { font-size: var(--rev-fs-md); font-weight: 650; letter-spacing: -.005em; }
.rev-banner-text { font-size: var(--rev-fs-md); line-height: 1.5; opacity: .88; }
.rev-banner-actions { display: flex; align-items: center; gap: var(--rev-s-3); flex: none; margin-top: -1px; }
.rev-banner-close {
  flex: none; display: grid; place-items: center; width: 22px; height: 22px;
  border: 0; background: transparent; color: inherit; opacity: .55;
  border-radius: var(--rev-r-sm); cursor: pointer;
  transition: opacity var(--rev-t-fast) var(--rev-ease), background var(--rev-t-fast) var(--rev-ease);
}
.rev-banner-close:hover { opacity: 1; background: rgba(0,0,0,.05); }

.t-info    { background: var(--rev-info-bg);    border-color: var(--rev-info-border);    color: var(--rev-info-ink); }
.t-success { background: var(--rev-success-bg); border-color: var(--rev-success-border); color: var(--rev-success-ink); }
.t-warning { background: var(--rev-warning-bg); border-color: var(--rev-warning-border); color: var(--rev-warning-ink); }
.t-danger  { background: var(--rev-danger-bg);  border-color: var(--rev-danger-border);  color: var(--rev-danger-ink); }
.t-neutral { background: var(--rev-n-50);       border-color: var(--rev-line);           color: var(--rev-ink-2); }
</style>
