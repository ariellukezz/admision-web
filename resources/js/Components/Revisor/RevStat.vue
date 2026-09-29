<!--
  RevStat — cifra de cabecera. Se lee en este orden: etiqueta, cifra, contexto.
  No lleva icono de color por defecto: el número es el protagonista.
-->
<template>
  <component :is="href ? 'a' : 'div'" :href="href" class="rev-stat" :class="{ 'is-link': href, 'is-loading': loading }">
    <div class="rev-stat-top">
      <span class="rev-stat-label">{{ label }}</span>
      <RevIcon v-if="icon" :name="icon" size="sm" class="rev-stat-icon" />
    </div>

    <div v-if="loading" class="rev-skel rev-stat-skel" />
    <div v-else class="rev-stat-value rev-num" :key="value">{{ formatted }}</div>

    <div class="rev-stat-foot">
      <span v-if="delta !== null && !loading" class="rev-stat-delta" :class="deltaTone">
        <RevIcon :name="Number(delta) >= 0 ? 'arrow-up' : 'arrow-down'" size="xs" />
        <span class="rev-num">{{ Math.abs(Number(delta)) }}</span>
      </span>
      <span v-if="hint" class="rev-stat-hint">{{ hint }}</span>
      <slot name="foot" />
    </div>
    <slot />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import RevIcon from './RevIcon.vue'

const props = defineProps({
  label: { type: String, required: true },
  value: { type: [Number, String], default: 0 },
  delta: { type: [Number, String], default: null },
  deltaGood: { type: String, default: 'up' },   // up | down | none
  hint: { type: String, default: '' },
  icon: { type: String, default: '' },
  loading: { type: Boolean, default: false },
  href: { type: String, default: '' },
})

const formatted = computed(() => {
  const n = Number(props.value)
  return Number.isFinite(n) ? n.toLocaleString('es-PE') : props.value
})
const deltaTone = computed(() => {
  if (props.deltaGood === 'none') return 'is-neutral'
  const positive = Number(props.delta) >= 0
  const good = props.deltaGood === 'up' ? positive : !positive
  return Number(props.delta) === 0 ? 'is-neutral' : good ? 'is-good' : 'is-bad'
})
</script>

<style scoped>
.rev-stat {
  display: flex; flex-direction: column;
  padding: 13px var(--rev-s-6) 12px;
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
  text-decoration: none;
  min-width: 0;
  transition: border-color var(--rev-t-base) var(--rev-ease), box-shadow var(--rev-t-base) var(--rev-ease);
}
.rev-stat.is-link:hover { border-color: var(--rev-primary-300); box-shadow: var(--rev-sh-sm); }
.rev-stat.is-link:focus-visible { outline: none; box-shadow: var(--rev-ring); }

.rev-stat-top { display: flex; align-items: center; justify-content: space-between; gap: var(--rev-s-4); }
.rev-stat-label {
  font-size: var(--rev-fs-2xs); font-weight: 650;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-ink-4);
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.rev-stat-icon { color: var(--rev-n-300); }

.rev-stat-value {
  margin-top: 5px;
  font-size: var(--rev-fs-3xl);
  font-weight: 640;
  letter-spacing: -.028em;
  line-height: 1.12;
  color: var(--rev-ink);
  animation: rev-count-in var(--rev-t-base) var(--rev-ease-out) both;
}
.rev-stat-skel { margin-top: 8px; height: 25px; width: 62%; }

.rev-stat-foot { display: flex; align-items: center; gap: 6px; margin-top: 4px; min-height: 17px; }
.rev-stat-delta { display: inline-flex; align-items: center; gap: 2px; font-size: var(--rev-fs-sm); font-weight: 650; }
.rev-stat-delta.is-good { color: var(--rev-success); }
.rev-stat-delta.is-bad { color: var(--rev-danger); }
.rev-stat-delta.is-neutral { color: var(--rev-ink-4); }
.rev-stat-hint { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>
