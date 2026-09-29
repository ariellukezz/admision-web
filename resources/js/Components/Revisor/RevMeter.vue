<!--  RevMeter — progreso lineal con lectura numérica opcional. -->
<template>
  <div class="rev-meter" :class="[`t-${tone}`, `s-${size}`]">
    <div v-if="label || showValue" class="rev-meter-head">
      <span v-if="label" class="rev-meter-label">{{ label }}</span>
      <span v-if="showValue" class="rev-meter-value rev-num">{{ pct }}%</span>
    </div>
    <div class="rev-meter-track" role="progressbar" :aria-valuenow="pct" aria-valuemin="0" aria-valuemax="100">
      <div class="rev-meter-fill" :style="{ width: pct + '%' }" />
    </div>
    <div v-if="$slots.foot" class="rev-meter-foot"><slot name="foot" /></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({
  value: { type: [Number, String], default: 0 },
  max: { type: [Number, String], default: 100 },
  tone: { type: String, default: 'accent' },   // accent | success | warning | danger | neutral
  size: { type: String, default: 'md' },
  label: { type: String, default: '' },
  showValue: { type: Boolean, default: false },
})
const pct = computed(() => {
  const m = Number(props.max) || 100
  return Math.max(0, Math.min(100, Math.round((Number(props.value) / m) * 100)))
})
</script>

<style scoped>
.rev-meter { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
.rev-meter-head { display: flex; align-items: baseline; justify-content: space-between; gap: var(--rev-s-4); }
.rev-meter-label { font-size: var(--rev-fs-sm); font-weight: 560; color: var(--rev-ink-3); }
.rev-meter-value { font-size: var(--rev-fs-sm); font-weight: 680; color: var(--rev-ink); }
.rev-meter-track { background: var(--rev-n-150); border-radius: var(--rev-r-pill); overflow: hidden; }
.s-sm .rev-meter-track { height: 4px; }
.s-md .rev-meter-track { height: 6px; }
.s-lg .rev-meter-track { height: 9px; }
.rev-meter-fill {
  height: 100%; border-radius: var(--rev-r-pill);
  transition: width var(--rev-t-slow) var(--rev-ease-out);
}
.t-accent  .rev-meter-fill { background: var(--rev-primary-600); }
.t-success .rev-meter-fill { background: var(--rev-success); }
.t-warning .rev-meter-fill { background: var(--rev-warning); }
.t-danger  .rev-meter-fill { background: var(--rev-danger); }
.t-neutral .rev-meter-fill { background: var(--rev-n-400); }
.rev-meter-foot { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); }
</style>
