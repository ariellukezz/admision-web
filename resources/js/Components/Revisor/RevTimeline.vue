<!--
  RevTimeline — trazabilidad. Cada hito lleva marca semántica, título,
  detalle opcional y sello de tiempo. El hilo conecta, no decora.
-->
<template>
  <ol class="rev-tl">
    <li v-for="(it, i) in items" :key="it.id ?? i" class="rev-tl-item" :class="[`t-${it.tone || 'neutral'}`, { 'is-current': it.current }]">
      <span class="rev-tl-mark"><RevIcon :name="it.icon || defaultIcon(it)" size="xs" /></span>
      <div class="rev-tl-body">
        <div class="rev-tl-head">
          <span class="rev-tl-title">{{ it.title }}</span>
          <time v-if="it.time" class="rev-tl-time">{{ it.time }}</time>
        </div>
        <p v-if="it.detail" class="rev-tl-detail">{{ it.detail }}</p>
        <div v-if="it.meta" class="rev-tl-meta">{{ it.meta }}</div>
        <slot name="item" :item="it" :index="i" />
      </div>
    </li>
  </ol>
</template>

<script setup>
import RevIcon from './RevIcon.vue'
defineProps({ items: { type: Array, default: () => [] } })
const defaultIcon = (it) => ({ success: 'check', warning: 'alert', danger: 'close', info: 'info', neutral: 'clock' }[it.tone] || 'clock')
</script>

<style scoped>
.rev-tl { list-style: none; margin: 0; padding: 0; }
.rev-tl-item { position: relative; display: flex; gap: var(--rev-s-5); padding: 0 0 var(--rev-s-6) 0; }
.rev-tl-item::before {
  content: ""; position: absolute; left: 10px; top: 21px; bottom: 0;
  width: 1px; background: var(--rev-line);
}
.rev-tl-item:last-child { padding-bottom: 0; }
.rev-tl-item:last-child::before { display: none; }

.rev-tl-mark {
  flex: none; display: grid; place-items: center;
  width: 21px; height: 21px; border-radius: 50%;
  background: var(--rev-n-100); color: var(--rev-ink-3);
  border: 1px solid var(--rev-line); z-index: 1;
}
.t-success .rev-tl-mark { background: var(--rev-success-bg); color: var(--rev-success); border-color: var(--rev-success-border); }
.t-warning .rev-tl-mark { background: var(--rev-warning-bg); color: var(--rev-warning); border-color: var(--rev-warning-border); }
.t-danger  .rev-tl-mark { background: var(--rev-danger-bg);  color: var(--rev-danger);  border-color: var(--rev-danger-border); }
.t-info    .rev-tl-mark { background: var(--rev-info-bg);    color: var(--rev-primary-600); border-color: var(--rev-info-border); }
.is-current .rev-tl-mark { box-shadow: 0 0 0 3px var(--rev-primary-100); }

.rev-tl-body { min-width: 0; flex: 1 1 auto; padding-top: 1px; }
.rev-tl-head { display: flex; align-items: baseline; justify-content: space-between; gap: var(--rev-s-5); }
.rev-tl-title { font-size: var(--rev-fs-md); font-weight: 600; color: var(--rev-ink); }
.rev-tl-time { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); white-space: nowrap; font-variant-numeric: tabular-nums; }
.rev-tl-detail { margin: 2px 0 0; font-size: var(--rev-fs-md); color: var(--rev-ink-3); line-height: 1.5; }
.rev-tl-meta { margin-top: 4px; font-size: var(--rev-fs-sm); color: var(--rev-ink-4); }
</style>
