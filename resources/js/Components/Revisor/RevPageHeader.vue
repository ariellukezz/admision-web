<!--
  RevPageHeader — cabecera de contenido. Responde a tres preguntas en este orden:
  dónde estoy (migas) · qué estoy viendo (título + estado) · qué puedo hacer (acciones).
-->
<template>
  <header class="rev-ph" :class="{ 'is-bordered': bordered }">
    <nav v-if="crumbs.length" class="rev-ph-crumbs" aria-label="Ruta">
      <template v-for="(c, i) in crumbs" :key="i">
        <component
          :is="c.href ? 'a' : 'span'"
          :href="c.href"
          class="rev-ph-crumb"
          :class="{ 'is-current': i === crumbs.length - 1 }"
          :aria-current="i === crumbs.length - 1 ? 'page' : undefined"
        >{{ c.label }}</component>
        <RevIcon v-if="i < crumbs.length - 1" name="chevron-right" size="xs" class="rev-ph-sep" />
      </template>
    </nav>

    <div class="rev-ph-main">
      <div class="rev-ph-left">
        <slot name="lead" />
        <div class="rev-ph-copy">
          <div class="rev-ph-title-row">
            <h1 class="rev-ph-title">{{ title }}</h1>
            <slot name="status" />
          </div>
          <p v-if="description" class="rev-ph-desc">{{ description }}</p>
          <div v-if="$slots.meta" class="rev-ph-meta"><slot name="meta" /></div>
        </div>
      </div>
      <div v-if="$slots.actions" class="rev-ph-actions"><slot name="actions" /></div>
    </div>
  </header>
</template>

<script setup>
import RevIcon from './RevIcon.vue'
defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  crumbs: { type: Array, default: () => [] },
  bordered: { type: Boolean, default: false },
})
</script>

<style scoped>
.rev-ph { display: flex; flex-direction: column; gap: var(--rev-s-4); }
.rev-ph.is-bordered { padding-bottom: var(--rev-s-5); border-bottom: 1px solid var(--rev-line); }

.rev-ph-crumbs { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; }
.rev-ph-crumb {
  font-size: var(--rev-fs-sm); font-weight: 550; color: var(--rev-ink-4);
  text-decoration: none; border-radius: var(--rev-r-xs);
  transition: color var(--rev-t-fast) var(--rev-ease);
}
a.rev-ph-crumb:hover { color: var(--rev-primary-700); }
.rev-ph-crumb.is-current { color: var(--rev-ink-2); font-weight: 600; }
.rev-ph-sep { color: var(--rev-n-300); }

.rev-ph-main { display: flex; align-items: flex-start; justify-content: space-between; gap: var(--rev-s-6); flex-wrap: wrap; }
.rev-ph-left { display: flex; align-items: flex-start; gap: var(--rev-s-5); min-width: 0; flex: 1 1 380px; }
.rev-ph-copy { min-width: 0; }
.rev-ph-title-row { display: flex; align-items: center; gap: var(--rev-s-4); flex-wrap: wrap; }
.rev-ph-title {
  margin: 0;
  font-family: var(--rev-display);
  font-size: var(--rev-fs-display);
  font-weight: 400;
  letter-spacing: -.018em;
  line-height: 1.08;
  color: var(--rev-ink);
}
.rev-ph-desc { margin: 10px 0 0; font-size: var(--rev-fs-md); line-height: 1.6; color: var(--rev-ink-3); max-width: 64ch; }
.rev-ph-meta { display: flex; align-items: center; gap: var(--rev-s-4); flex-wrap: wrap; margin-top: var(--rev-s-4); }
.rev-ph-actions { display: flex; align-items: center; gap: var(--rev-s-3); flex: none; }

@media (max-width: 720px) {
  .rev-ph-title { font-size: var(--rev-fs-2xl); }
  .rev-ph-actions { width: 100%; }
}
</style>
