<template>
  <div class="form-field">
    <label v-if="label" class="field-label">{{ label }}</label>
    <div class="input-wrap">
      <div v-if="$slots.icon" class="input-icon">
        <slot name="icon" />
      </div>
      <input
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :placeholder="placeholder"
        :class="['field-input', { 'has-icon': $slots.icon, 'has-error': error }]"
        v-bind="$attrs"
      />
      <div v-if="$slots.right" class="input-right">
        <slot name="right" />
      </div>
    </div>
    <span v-if="error" class="field-error">{{ error }}</span>
  </div>
</template>

<script setup>
defineProps({
  label: String,
  type: { type: String, default: 'text' },
  placeholder: String,
  modelValue: [String, Number],
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

.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
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

.field-input {
  width: 100%;
  height: 48px;
  padding: 0 12px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  background: #fff;
  font-size: .9375rem;
  color: #1A202C;
  outline: none;
  transition: border-color .2s, box-shadow .2s;
  box-sizing: border-box;
  font-family: inherit;
}
.field-input.has-icon { padding-left: 40px; }
.field-input::placeholder { color: #A0AEC0; }
.field-input:focus { border-color: #3B6AA0; box-shadow: 0 0 0 3px rgba(59,106,160,.12); }
.field-input.has-error { border-color: #E53E3E; }
.field-input.has-error:focus { box-shadow: 0 0 0 3px rgba(229,62,62,.1); }

.input-right {
  position: absolute;
  right: 4px;
  display: flex;
  align-items: center;
}

.field-error { font-size: .75rem; color: #E53E3E; }
</style>
