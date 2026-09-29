<!--
  RevToolbar — barra de filtros. Densa, alineada, sin tarjeta propia cuando
  va pegada a una tabla (`attached`).
-->
<template>
  <div class="rev-toolbar" :class="{ 'is-attached': attached }">
    <div class="rev-toolbar-lead"><slot name="lead" /></div>
    <div class="rev-toolbar-main"><slot /></div>
    <div v-if="$slots.trail" class="rev-toolbar-trail"><slot name="trail" /></div>
  </div>
</template>

<script setup>
defineProps({ attached: { type: Boolean, default: false } })
</script>

<style scoped>
.rev-toolbar {
  display: flex; align-items: center; gap: var(--rev-s-4);
  flex-wrap: wrap;
  padding: 9px var(--rev-s-5);
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
}
.rev-toolbar.is-attached {
  border: 0; border-bottom: 1px solid var(--rev-line);
  border-radius: 0; background: transparent;
}
.rev-toolbar-lead { display: flex; align-items: center; gap: var(--rev-s-4); flex: none; }
.rev-toolbar-main { display: flex; align-items: center; gap: var(--rev-s-4); flex: 1 1 auto; flex-wrap: wrap; min-width: 0; }
.rev-toolbar-trail { display: flex; align-items: center; gap: var(--rev-s-3); flex: none; margin-left: auto; }
</style>
