<template>
  <div class="dni-field">
    <label class="field-label">DNI</label>
    <div class="dni-row">
      <div class="dni-input-wrap">
        <div class="input-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/><path d="M13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z"/></svg>
        </div>
        <input
          type="text"
          :value="modelValue"
          @input="$emit('update:modelValue', $event.target.value)"
          class="dni-input"
          placeholder="12345678"
          maxlength="8"
          @keyup.enter="$emit('search')"
          inputmode="numeric"
        />
      </div>
      <button type="button" class="dni-btn" @click="$emit('search')" :disabled="loading">
        <span v-if="loading" class="btn-spinner"></span>
        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
      </button>
    </div>
    <span v-if="error" class="field-error">{{ error }}</span>
  </div>
</template>

<script setup>
defineProps({
  modelValue: String,
  loading: Boolean,
  error: String
});

defineEmits(['update:modelValue', 'search']);
</script>

<style scoped>
.dni-field { display: flex; flex-direction: column; gap: .375rem; }

.field-label {
  font-size: .6875rem;
  font-weight: 600;
  color: #718096;
  text-transform: uppercase;
  letter-spacing: .5px;
}

.dni-row { display: flex; gap: 0; }

.dni-input-wrap {
  position: relative;
  flex: 1;
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
}

.dni-input {
  width: 100%;
  height: 48px;
  padding: 0 12px 0 40px;
  border: 1px solid #E2E8F0;
  border-right: none;
  border-radius: 8px 0 0 8px;
  background: #fff;
  font-size: .9375rem;
  color: #1A202C;
  outline: none;
  transition: border-color .2s, box-shadow .2s;
  box-sizing: border-box;
  font-family: inherit;
  letter-spacing: 1px;
}
.dni-input::placeholder { color: #A0AEC0; letter-spacing: 0; }
.dni-input:focus { border-color: #3B6AA0; box-shadow: 0 0 0 3px rgba(59,106,160,.12); }

.dni-btn {
  width: 52px;
  height: 48px;
  border: none;
  border-radius: 0 8px 8px 0;
  background: linear-gradient(135deg, #F6AD55 0%, #ED8936 100%);
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .2s ease;
  flex-shrink: 0;
  box-shadow: 0 2px 4px rgba(237,137,54,.3);
}
.dni-btn:hover:not(:disabled) { background: linear-gradient(135deg, #ED8936 0%, #DD6B20 100%); }
.dni-btn:disabled { opacity: .6; cursor: not-allowed; }
.dni-btn svg { width: 20px; height: 20px; }

.btn-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.field-error { font-size: .75rem; color: #E53E3E; }
</style>
