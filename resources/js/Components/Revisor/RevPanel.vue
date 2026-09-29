<!--
  RevPanel — contenedor de sección. No es "una card para todo":
  se usa cuando un bloque necesita cabecera, cuerpo propio y a veces pie.
  `flush` quita el relleno del cuerpo para alojar tablas a sangre.
-->
<template>
  <section class="rev-panel" :class="{ 'is-flush': flush, 'is-quiet': quiet, 'is-clip': clip }">
    <header v-if="title || $slots.header || $slots.actions" class="rev-panel-head">
      <div class="rev-panel-head-main">
        <slot name="header">
          <div class="rev-panel-titles">
            <h2 class="rev-panel-title">
              {{ title }}
              <span v-if="count !== null" class="rev-panel-count rev-num">{{ count }}</span>
            </h2>
            <p v-if="description" class="rev-panel-desc">{{ description }}</p>
          </div>
        </slot>
      </div>
      <div v-if="$slots.actions" class="rev-panel-actions"><slot name="actions" /></div>
    </header>

    <div class="rev-panel-body"><slot /></div>

    <footer v-if="$slots.footer" class="rev-panel-foot"><slot name="footer" /></footer>
  </section>
</template>

<script setup>
defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  count: { type: [Number, String], default: null },
  flush: { type: Boolean, default: false },
  quiet: { type: Boolean, default: false },
  clip: { type: Boolean, default: true },
})
</script>

<style scoped>
.rev-panel {
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
  display: flex; flex-direction: column;
  min-width: 0;
}
.rev-panel.is-clip { overflow: hidden; }
.rev-panel.is-quiet { background: transparent; border-style: dashed; box-shadow: none; }

.rev-panel-head {
  display: flex; align-items: center; justify-content: space-between;
  gap: var(--rev-s-5);
  padding: 14px var(--rev-s-7);
  border-bottom: 1px solid var(--rev-line);
  min-height: 54px;
}
.rev-panel-head-main { min-width: 0; }
.rev-panel-titles { min-width: 0; }
.rev-panel-title {
  display: flex; align-items: center; gap: 7px;
  margin: 0;
  font-size: var(--rev-fs-lg);
  font-weight: 640;
  letter-spacing: var(--rev-track-tight);
  color: var(--rev-ink);
  line-height: 1.25;
}
.rev-panel-count {
  font-size: var(--rev-fs-xs); font-weight: 650;
  color: var(--rev-ink-3); background: var(--rev-n-100);
  border-radius: var(--rev-r-sm); padding: 1px 6px; line-height: 16px;
}
.rev-panel-desc { margin: 3px 0 0; font-size: var(--rev-fs-sm); color: var(--rev-ink-3); }
.rev-panel-actions { display: flex; align-items: center; gap: var(--rev-s-3); flex: none; }

.rev-panel-body { padding: var(--rev-s-7); flex: 1 1 auto; min-width: 0; }
.is-flush .rev-panel-body { padding: 0; }

.rev-panel-foot {
  padding: 13px var(--rev-s-7);
  border-top: 1px solid var(--rev-line);
  background: var(--rev-surface-2);
  display: flex; align-items: center; justify-content: space-between; gap: var(--rev-s-4);
}
</style>
