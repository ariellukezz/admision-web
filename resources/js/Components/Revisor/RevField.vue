<!-- RevField — etiqueta, control, ayuda y error con una sola métrica. -->
<template>
  <div class="rev-field" :class="{ 'is-invalid': !!error, 'is-inline': inline }">
    <label v-if="label" class="rev-field-label" :for="htmlFor">
      {{ label }}<span v-if="required" class="rev-field-req" aria-hidden="true">*</span>
    </label>
    <div class="rev-field-control"><slot /></div>
    <p v-if="error" class="rev-field-msg is-error"><RevIcon name="alert-circle" size="xs" />{{ error }}</p>
    <p v-else-if="hint" class="rev-field-msg">{{ hint }}</p>
  </div>
</template>

<script setup>
import RevIcon from './RevIcon.vue'
defineProps({
  label: { type: String, default: '' },
  hint: { type: String, default: '' },
  error: { type: String, default: '' },
  required: { type: Boolean, default: false },
  inline: { type: Boolean, default: false },
  htmlFor: { type: String, default: undefined },
})
</script>

<style scoped>
.rev-field { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
.rev-field.is-inline { flex-direction: row; align-items: center; gap: var(--rev-s-5); }
.rev-field-label {
  font-size: var(--rev-fs-sm); font-weight: 600; color: var(--rev-ink-2);
  display: inline-flex; align-items: center; gap: 2px;
}
.rev-field-req { color: var(--rev-danger); font-weight: 700; }
.rev-field-control { min-width: 0; }
.rev-field-msg { margin: 0; display: flex; align-items: center; gap: 4px; font-size: var(--rev-fs-sm); color: var(--rev-ink-4); }
.rev-field-msg.is-error { color: var(--rev-danger-ink); }

/* Controles nativos alineados con Ant Design */
.rev-field-control :deep(input[type="text"]),
.rev-field-control :deep(input[type="date"]),
.rev-field-control :deep(input[type="time"]),
.rev-field-control :deep(input[type="number"]),
.rev-field-control :deep(textarea) {
  width: 100%;
  font-family: var(--rev-font);
  font-size: var(--rev-fs-md);
  color: var(--rev-ink);
  background: var(--rev-surface);
  border: 1px solid var(--rev-line-strong);
  border-radius: var(--rev-r-md);
  padding: 6px 10px;
  transition: border-color var(--rev-t-fast) var(--rev-ease), box-shadow var(--rev-t-fast) var(--rev-ease);
}
.rev-field-control :deep(textarea) { padding: 8px 10px; line-height: 1.5; resize: vertical; }
.rev-field-control :deep(input:hover), .rev-field-control :deep(textarea:hover) { border-color: var(--rev-n-300); }
.rev-field-control :deep(input:focus), .rev-field-control :deep(textarea:focus) {
  outline: none; border-color: var(--rev-primary-500); box-shadow: var(--rev-ring);
}
.rev-field-control :deep(input:disabled), .rev-field-control :deep(textarea:disabled) {
  background: var(--rev-n-50); color: var(--rev-ink-4); cursor: not-allowed;
}
.is-invalid .rev-field-control :deep(input),
.is-invalid .rev-field-control :deep(textarea) { border-color: var(--rev-danger); }
</style>
