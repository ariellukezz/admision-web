<!-- RevSearch — campo de búsqueda con debounce y limpieza. -->
<template>
  <div class="rev-search" :class="{ 'is-focused': focused, [`s-${size}`]: true }">
    <RevIcon name="search" :size="size === 'sm' ? 'sm' : 'md'" class="rev-search-icon" />
    <input
      ref="input"
      class="rev-search-input"
      type="search"
      :value="modelValue"
      :placeholder="placeholder"
      :aria-label="placeholder"
      @input="onInput"
      @focus="focused = true"
      @blur="focused = false"
      @keyup.enter="emit('submit', modelValue)"
      @keyup.esc="clear"
    />
    <button v-if="modelValue" class="rev-search-clear" type="button" aria-label="Limpiar búsqueda" @click="clear">
      <RevIcon name="close" size="xs" />
    </button>
    <kbd v-else-if="shortcut" class="rev-search-kbd">{{ shortcut }}</kbd>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import RevIcon from './RevIcon.vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Buscar…' },
  debounce: { type: Number, default: 280 },
  size: { type: String, default: 'md' },
  shortcut: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue', 'search', 'submit'])

const focused = ref(false)
const input = ref(null)
let timer = null

const onInput = (e) => {
  const v = e.target.value
  emit('update:modelValue', v)
  clearTimeout(timer)
  timer = setTimeout(() => emit('search', v), props.debounce)
}
const clear = () => {
  emit('update:modelValue', '')
  emit('search', '')
  input.value?.focus()
}
defineExpose({ focus: () => input.value?.focus() })
</script>

<style scoped>
.rev-search {
  display: flex; align-items: center; gap: 7px;
  background: var(--rev-surface);
  border: 1px solid var(--rev-line-strong);
  border-radius: var(--rev-r-md);
  padding: 0 9px;
  min-width: 0;
  transition: border-color var(--rev-t-fast) var(--rev-ease), box-shadow var(--rev-t-fast) var(--rev-ease);
}
.s-sm { height: 26px; } .s-md { height: 32px; } .s-lg { height: 38px; }
.rev-search:hover { border-color: var(--rev-n-300); }
.rev-search.is-focused { border-color: var(--rev-primary-500); box-shadow: var(--rev-ring); }
.rev-search-icon { color: var(--rev-ink-4); flex: none; }
.rev-search-input {
  flex: 1 1 auto; min-width: 0;
  border: 0; outline: 0; background: transparent;
  font-family: var(--rev-font); font-size: var(--rev-fs-md); color: var(--rev-ink);
}
.rev-search-input::placeholder { color: var(--rev-ink-4); }
.rev-search-input::-webkit-search-cancel-button { display: none; }
.rev-search-clear {
  flex: none; display: grid; place-items: center; width: 18px; height: 18px;
  border: 0; border-radius: var(--rev-r-xs); background: var(--rev-n-100); color: var(--rev-ink-3);
  cursor: pointer; transition: background var(--rev-t-fast) var(--rev-ease);
}
.rev-search-clear:hover { background: var(--rev-n-200); color: var(--rev-ink); }
.rev-search-kbd {
  flex: none; font-family: var(--rev-font); font-size: var(--rev-fs-2xs); font-weight: 650;
  color: var(--rev-ink-4); background: var(--rev-n-100); border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-xs); padding: 1px 5px; line-height: 15px;
}
</style>
