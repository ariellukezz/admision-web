<template>
  <div class="form-field">
    <label v-if="label" class="field-label">{{ label }}</label>
    <div class="select-wrap">
      <div v-if="$slots.icon" class="select-icon">
        <slot name="icon" />
      </div>
      <select
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        :class="['field-select', { 'has-icon': $slots.icon, 'has-error': error }]"
      >
        <option value="" disabled>{{ placeholder }}</option>
        <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
      </select>
      <div class="select-arrow">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
      </div>
    </div>
    <span v-if="error" class="field-error">{{ error }}</span>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  placeholder: String,
  modelValue: [String, Number],
  options: { type: Array, default: () => [] },
  error: String
});

defineEmits(['update:modelValue']);
</script>

<style scoped>
.form-field { display: flex; flex-direction: column; gap: .375rem; }

.field-label {
  font-size: .6875rem;
  font-weight: 600;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: .5px;
}

.select-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.select-icon {
  position: absolute;
  left: 12px;
  width: 18px;
  height: 18px;
  color: #718096;
  pointer-events: none;
  z-index: 1;
  display: flex;
  align-items: center;
}

.field-select {
  width: 100%;
  height: 48px;
  padding: 0 36px 0 12px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  background: #fff;
  font-size: .9375rem;
  color: #1A202C;
  outline: none;
  transition: border-color .2s, box-shadow .2s;
  box-sizing: border-box;
  font-family: inherit;
  appearance: none;
  -webkit-appearance: none;
  cursor: pointer;
}
.field-select.has-icon { padding-left: 40px; }
.field-select:focus { border-color: #3B6AA0; box-shadow: 0 0 0 3px rgba(59,106,160,.12); }
.field-select.has-error { border-color: #E53E3E; }
.field-select:invalid { color: #A0AEC0; }

.select-arrow {
  position: absolute;
  right: 12px;
  width: 16px;
  height: 16px;
  color: #718096;
  pointer-events: none;
}

.field-error { font-size: .75rem; color: #E53E3E; }
</style>
