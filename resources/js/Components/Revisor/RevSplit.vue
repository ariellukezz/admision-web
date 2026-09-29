<!--
  RevSplit — vista dividida maestro/detalle. La lista mantiene el contexto
  mientras el revisor trabaja en el panel derecho.
-->
<template>
  <div class="rev-split" :class="{ 'is-open': open }" :style="{ '--rev-split-w': detailWidth }">
    <div class="rev-split-master"><slot name="master" /></div>
    <transition name="rev-split-slide">
      <aside v-if="open" class="rev-split-detail" aria-live="polite"><slot name="detail" /></aside>
    </transition>
  </div>
</template>

<script setup>
defineProps({
  open: { type: Boolean, default: true },
  detailWidth: { type: String, default: '420px' },
})
</script>

<style scoped>
.rev-split { display: flex; align-items: stretch; gap: var(--rev-s-5); min-width: 0; min-height: 0; }
.rev-split-master { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }
.rev-split-detail {
  flex: 0 0 var(--rev-split-w);
  width: var(--rev-split-w);
  min-width: 0;
  display: flex; flex-direction: column;
}
.rev-split-slide-enter-active, .rev-split-slide-leave-active {
  transition: opacity var(--rev-t-base) var(--rev-ease), transform var(--rev-t-base) var(--rev-ease-out);
}
.rev-split-slide-enter-from, .rev-split-slide-leave-to { opacity: 0; transform: translateX(8px); }

@media (max-width: 1180px) {
  .rev-split { flex-direction: column; }
  .rev-split-detail { flex: 1 1 auto; width: 100%; }
}
</style>
