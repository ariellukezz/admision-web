<!--
  RevBadge — un estado, un color. Nunca decorativo.
  tone: neutral | pending | progress | success | warning | danger | info | accent
-->
<template>
  <span class="rev-badge" :class="[`t-${tone}`, `s-${size}`, { 'has-dot': dot, 'is-solid': solid }]">
    <span v-if="dot" class="rev-badge-dot" :class="{ 'is-live': pulse }" />
    <RevIcon v-if="icon" :name="icon" size="xs" />
    <slot />
  </span>
</template>

<script setup>
import RevIcon from './RevIcon.vue'
defineProps({
  tone: { type: String, default: 'neutral' },
  size: { type: String, default: 'md' },   // sm | md
  dot: { type: Boolean, default: false },
  icon: { type: String, default: '' },
  solid: { type: Boolean, default: false },
  pulse: { type: Boolean, default: false },
})
</script>

<style scoped>
.rev-badge {
  display: inline-flex; align-items: center; gap: 5px;
  font-weight: 620;
  letter-spacing: .01em;
  white-space: nowrap;
  border: 1px solid transparent;
  border-radius: var(--rev-r-sm);
  font-variant-numeric: tabular-nums;
}
.s-sm { font-size: var(--rev-fs-2xs); padding: 1px 6px; line-height: 16px; }
.s-md { font-size: var(--rev-fs-xs); padding: 2px 7px; line-height: 17px; }

.rev-badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex: none; }
.rev-badge-dot.is-live { animation: rev-pulse-soft 1.9s var(--rev-ease) infinite; }

.t-neutral  { background: var(--rev-n-100);           color: var(--rev-ink-2);        border-color: var(--rev-line); }
.t-pending  { background: var(--rev-pending-bg);      color: var(--rev-pending-ink);  border-color: var(--rev-pending-border); }
.t-progress { background: var(--rev-info-bg);         color: var(--rev-info-ink);     border-color: var(--rev-info-border); }
.t-info     { background: var(--rev-info-bg);         color: var(--rev-info-ink);     border-color: var(--rev-info-border); }
.t-accent   { background: var(--rev-primary-50);      color: var(--rev-primary-700);  border-color: var(--rev-primary-200); }
.t-success  { background: var(--rev-success-bg);      color: var(--rev-success-ink);  border-color: var(--rev-success-border); }
.t-warning  { background: var(--rev-warning-bg);      color: var(--rev-warning-ink);  border-color: var(--rev-warning-border); }
.t-danger   { background: var(--rev-danger-bg);       color: var(--rev-danger-ink);   border-color: var(--rev-danger-border); }

.is-solid { border-color: transparent; color: #fff; }
.is-solid.t-success  { background: var(--rev-success); }
.is-solid.t-warning  { background: var(--rev-warning); }
.is-solid.t-danger   { background: var(--rev-danger); }
.is-solid.t-accent,
.is-solid.t-info     { background: var(--rev-primary-600); }
.is-solid.t-neutral,
.is-solid.t-pending  { background: var(--rev-n-600); }
</style>
