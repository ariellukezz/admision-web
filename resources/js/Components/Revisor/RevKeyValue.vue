<!-- RevKeyValue — pares dato/valor alineados. Para fichas y paneles de detalle. -->
<template>
  <dl class="rev-kv" :class="[`l-${layout}`]">
    <template v-for="(it, i) in items" :key="i">
      <dt class="rev-kv-key">{{ it.label }}</dt>
      <dd class="rev-kv-val" :class="{ 'is-mono': it.mono, 'is-muted': it.value === null || it.value === '' }">
        <slot :name="it.key" :item="it">{{ (it.value === null || it.value === '') ? '—' : it.value }}</slot>
      </dd>
    </template>
  </dl>
</template>

<script setup>
defineProps({
  items: { type: Array, default: () => [] },   // [{key,label,value,mono}]
  layout: { type: String, default: 'rows' },   // rows | grid
})
</script>

<style scoped>
.rev-kv { margin: 0; min-width: 0; }
.l-rows { display: grid; grid-template-columns: minmax(96px, auto) 1fr; column-gap: var(--rev-s-6); row-gap: 0; }
.l-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: var(--rev-s-5) var(--rev-s-6); }

.rev-kv-key {
  font-size: var(--rev-fs-sm); font-weight: 560; color: var(--rev-ink-4);
  padding: 6px 0; white-space: nowrap;
}
.rev-kv-val {
  margin: 0; padding: 6px 0;
  font-size: var(--rev-fs-md); font-weight: 520; color: var(--rev-ink);
  min-width: 0; overflow-wrap: anywhere;
}
.l-rows .rev-kv-key, .l-rows .rev-kv-val { border-bottom: 1px solid var(--rev-line-soft); }
.l-rows > :nth-last-child(-n+2) { border-bottom: 0; }
.l-grid .rev-kv-key { padding: 0; }
.l-grid .rev-kv-val { padding: 2px 0 0; }
.rev-kv-val.is-mono { font-family: var(--rev-mono); font-size: var(--rev-fs-sm); letter-spacing: -.01em; }
.rev-kv-val.is-muted { color: var(--rev-ink-4); }
</style>
