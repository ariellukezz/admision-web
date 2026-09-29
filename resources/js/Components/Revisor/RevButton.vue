<!--
  RevButton — única gramática de acción del módulo.
  variant: primary | secondary | subtle | ghost | danger | success
  Toda acción destructiva o irreversible usa `danger`. Nunca dos primarios juntos.
-->
<template>
  <component
    :is="tag"
    class="rev-btn"
    :class="[`v-${variant}`, `s-${size}`, { 'is-icon': iconOnly, 'is-loading': loading, 'is-block': block }]"
    :type="tag === 'button' ? type : undefined"
    :href="href"
    :disabled="tag === 'button' ? (disabled || loading) : undefined"
    :aria-busy="loading ? 'true' : undefined"
    :aria-disabled="disabled || loading ? 'true' : undefined"
  >
    <RevIcon v-if="loading" name="loader" :size="iconSize" spin />
    <RevIcon v-else-if="icon" :name="icon" :size="iconSize" />
    <span v-if="!iconOnly" class="rev-btn-label"><slot /></span>
    <RevIcon v-if="trailingIcon && !loading" :name="trailingIcon" :size="iconSize" />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import RevIcon from './RevIcon.vue'

const props = defineProps({
  variant: { type: String, default: 'secondary' },
  size: { type: String, default: 'md' },      // sm | md | lg
  icon: { type: String, default: '' },
  trailingIcon: { type: String, default: '' },
  iconOnly: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  block: { type: Boolean, default: false },
  href: { type: String, default: '' },
  type: { type: String, default: 'button' },
  as: { type: [String, Object], default: null },
})

const tag = computed(() => props.as || (props.href ? 'a' : 'button'))
const iconSize = computed(() => (props.size === 'sm' ? 'sm' : props.size === 'lg' ? 'lg' : 'md'))
</script>

<style scoped>
.rev-btn {
  display: inline-flex; align-items: center; justify-content: center;
  gap: 6px;
  font-family: var(--rev-font);
  font-weight: 560;
  letter-spacing: -.005em;
  white-space: nowrap;
  border: 1px solid transparent;
  border-radius: var(--rev-r-md);
  cursor: pointer;
  text-decoration: none;
  user-select: none;
  transition: background var(--rev-t-fast) var(--rev-ease),
              border-color var(--rev-t-fast) var(--rev-ease),
              color var(--rev-t-fast) var(--rev-ease),
              box-shadow var(--rev-t-fast) var(--rev-ease);
}
.rev-btn:focus-visible { outline: none; box-shadow: var(--rev-ring); }
.rev-btn:not(:disabled):active { transform: translateY(.5px); }
.rev-btn:disabled, .rev-btn[aria-disabled="true"] { cursor: not-allowed; }
.rev-btn.is-block { width: 100%; }
.rev-btn-label { display: inline-block; }

/* tallas */
.s-sm { height: 26px; padding: 0 9px;  font-size: var(--rev-fs-sm); border-radius: var(--rev-r-sm); }
.s-md { height: 32px; padding: 0 12px; font-size: var(--rev-fs-md); }
.s-lg { height: 38px; padding: 0 16px; font-size: var(--rev-fs-lg); }
.is-icon.s-sm { width: 26px; padding: 0; }
.is-icon.s-md { width: 32px; padding: 0; }
.is-icon.s-lg { width: 38px; padding: 0; }

/* primario — reservado a la acción principal de la pantalla */
.v-primary { background: var(--rev-primary-600); color: #fff; border-color: var(--rev-primary-600); }
.v-primary:not(:disabled):hover { background: var(--rev-primary-700); border-color: var(--rev-primary-700); }
.v-primary:not(:disabled):active { background: var(--rev-primary-800); border-color: var(--rev-primary-800); }
.v-primary:disabled, .v-primary[aria-disabled="true"] { background: var(--rev-primary-200); border-color: var(--rev-primary-200); color: #fff; }

/* secundario — la acción de uso diario */
.v-secondary { background: var(--rev-surface); color: var(--rev-ink-2); border-color: var(--rev-line-strong); box-shadow: var(--rev-sh-xs); }
.v-secondary:not(:disabled):hover { background: var(--rev-n-25); border-color: var(--rev-n-300); color: var(--rev-ink); }
.v-secondary:not(:disabled):active { background: var(--rev-n-100); }
.v-secondary:disabled, .v-secondary[aria-disabled="true"] { background: var(--rev-n-50); color: var(--rev-ink-4); border-color: var(--rev-line); box-shadow: none; }

/* sutil — dentro de tablas y barras densas */
.v-subtle { background: var(--rev-n-100); color: var(--rev-ink-2); border-color: transparent; }
.v-subtle:not(:disabled):hover { background: var(--rev-n-150); color: var(--rev-ink); }
.v-subtle:disabled, .v-subtle[aria-disabled="true"] { background: var(--rev-n-50); color: var(--rev-ink-4); }

/* fantasma — acciones terciarias, iconos de fila */
.v-ghost { background: transparent; color: var(--rev-ink-3); border-color: transparent; }
.v-ghost:not(:disabled):hover { background: var(--rev-n-100); color: var(--rev-ink); }
.v-ghost:disabled, .v-ghost[aria-disabled="true"] { color: var(--rev-ink-4); }

/* semánticos */
.v-danger { background: var(--rev-surface); color: var(--rev-danger-ink); border-color: var(--rev-danger-border); }
.v-danger:not(:disabled):hover { background: var(--rev-danger-bg); border-color: var(--rev-danger); }
.v-danger:focus-visible { box-shadow: var(--rev-ring-danger); }
.v-danger:disabled, .v-danger[aria-disabled="true"] { background: var(--rev-n-50); color: var(--rev-ink-4); border-color: var(--rev-line); }

.v-success { background: var(--rev-success); color: #fff; border-color: var(--rev-success); }
.v-success:not(:disabled):hover { background: var(--rev-success-ink); border-color: var(--rev-success-ink); }
.v-success:disabled, .v-success[aria-disabled="true"] { background: var(--rev-success-border); border-color: var(--rev-success-border); color: #fff; }

.is-loading { pointer-events: none; }
</style>
