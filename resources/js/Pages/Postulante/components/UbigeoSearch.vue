<template>
  <div class="form-field">
    <label v-if="label" class="field-label">{{ label }}</label>
    <div class="ubigeo-wrap">
      <div v-if="$slots.icon" class="ubigeo-icon">
        <slot name="icon" />
      </div>
      <input
        ref="inputRef"
        type="text"
        :class="['ubigeo-input', { 'has-icon': $slots.icon, 'has-error': error, 'has-selection': selectedLabel }]"
        :placeholder="placeholder"
        :value="displayValue"
        @input="onInput"
        @focus="onFocus"
        @blur="onBlur"
        autocomplete="off"
      />
      <div v-if="loading" class="ubigeo-spinner">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2v4m0 12v4m-7.07-3.93l2.83-2.83m8.48-8.48l2.83-2.83M2 12h4m12 0h4m-3.93 7.07l-2.83-2.83M7.76 7.76L4.93 4.93"/></svg>
      </div>
      <div v-else class="ubigeo-clear" v-show="selectedLabel" @mousedown.prevent="clearSelection">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M6 18L18 6M6 6l12 12"/></svg>
      </div>
      <!-- Dropdown -->
      <div v-if="showDropdown && options.length > 0" class="ubigeo-dropdown">
        <div
          v-for="opt in options"
          :key="opt.key"
          class="ubigeo-option"
          :class="{ selected: modelValue === opt.key }"
          @mousedown.prevent="selectOption(opt)"
        >
          <span class="ubigeo-option-code">{{ opt.key }}</span>
          <span class="ubigeo-option-name">{{ opt.displayValue }}</span>
        </div>
      </div>
    </div>
    <span v-if="error" class="field-error">{{ error }}</span>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  label: String,
  placeholder: { type: String, default: 'Buscar distrito...' },
  modelValue: { type: String, default: '' },
  initialValue: { type: String, default: '' },
  error: String,
  routeName: { type: String, default: 'postulante.api.buscar-ubigeo' },
});

const emit = defineEmits(['update:modelValue', 'select']);

const inputRef = ref(null);
const searchTerm = ref('');
const options = ref([]);
const loading = ref(false);
const showDropdown = ref(false);
const selectedLabel = ref('');
let debounceTimer = null;

const fetchLabel = async (ubigeoCode) => {
  if (!ubigeoCode || ubigeoCode.length < 2) {
    selectedLabel.value = '';
    return;
  }
  try {
    const res = await axios.post(route(props.routeName), { term: ubigeoCode });
    if (res.data.success && res.data.datos.data?.length > 0) {
      const match = res.data.datos.data.find(d => d.key === ubigeoCode);
      if (match) {
        selectedLabel.value = match.value;
      }
    }
  } catch (e) {}
};

// When an initial value (ubigeo code) is provided, fetch its label on mount
onMounted(() => {
  if (props.initialValue) {
    fetchLabel(props.initialValue);
  }
});

// Watch for external modelValue changes (e.g. from RENIEC search setting ubigeo_nacimiento)
watch(() => props.modelValue, (newVal, oldVal) => {
  if (newVal && newVal !== oldVal && newVal !== '' && !selectedLabel.value) {
    fetchLabel(newVal);
  }
});

const displayValue = computed(() => {
  if (selectedLabel.value) return selectedLabel.value;
  return searchTerm.value;
});

const onInput = (e) => {
  // If there was a selected label, the input shows it — clear it on first keystroke
  if (selectedLabel.value) {
    selectedLabel.value = '';
    searchTerm.value = '';
    // Only take the last character(s) typed
    const inputVal = e.data || '';
    e.target.value = inputVal;
    if (!inputVal) {
      showDropdown.value = false;
      options.value = [];
      return;
    }
    searchTerm.value = inputVal;
  } else {
    searchTerm.value = e.target.value;
  }

  showDropdown.value = true;

  if (debounceTimer) clearTimeout(debounceTimer);
  if (searchTerm.value.length < 3) {
    options.value = [];
    return;
  }

  debounceTimer = setTimeout(async () => {
    loading.value = true;
    try {
      const res = await axios.post(route(props.routeName), { term: searchTerm.value });
      if (res.data.success) {
        options.value = (res.data.datos.data || []).map(d => ({
          key: d.key,
          value: d.key,
          displayValue: d.value.replace(d.key + ' - ', ''),
          label: d.value,
        }));
      }
    } catch (e) {
      options.value = [];
    } finally {
      loading.value = false;
    }
  }, 300);
};

const selectOption = (opt) => {
  selectedLabel.value = opt.label;
  searchTerm.value = '';
  showDropdown.value = false;
  emit('update:modelValue', opt.key);
  emit('select', opt.key, opt);
};

const clearSelection = () => {
  selectedLabel.value = '';
  searchTerm.value = '';
  options.value = [];
  showDropdown.value = false;
  emit('update:modelValue', '');
  nextTick(() => inputRef.value?.focus());
};

const onFocus = () => {
  // Select all text so user can easily type over the existing label
  nextTick(() => inputRef.value?.select());
};

const onBlur = () => {
  setTimeout(() => {
    showDropdown.value = false;
    // If user cleared the label but didn't pick a new option, restore it
    if (!selectedLabel.value && props.modelValue) {
      fetchLabel(props.modelValue);
    }
  }, 150);
};
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

.ubigeo-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.ubigeo-icon {
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

.ubigeo-input {
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
}
.ubigeo-input.has-icon { padding-left: 40px; }
.ubigeo-input:focus { border-color: #3B6AA0; box-shadow: 0 0 0 3px rgba(59,106,160,.12); }
.ubigeo-input.has-error { border-color: #E53E3E; }
.ubigeo-input.has-selection { color: #1A202C; font-weight: 500; }
.ubigeo-input::placeholder { color: #A0AEC0; font-weight: 400; }

.ubigeo-spinner {
  position: absolute;
  right: 12px;
  color: #3B6AA0;
  animation: spin 1s linear infinite;
  display: flex;
  align-items: center;
}

.ubigeo-clear {
  position: absolute;
  right: 10px;
  color: #A0AEC0;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 2px;
  border-radius: 50%;
  transition: color .2s;
}
.ubigeo-clear:hover { color: #E53E3E; }

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.ubigeo-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  max-height: 240px;
  overflow-y: auto;
  background: #fff;
  border: 1px solid #E2E8F0;
  border-radius: 0 0 8px 8px;
  box-shadow: 0 8px 24px rgba(0,0,0,.1);
  z-index: 50;
  margin-top: 2px;
}

.ubigeo-option {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: .625rem .875rem;
  cursor: pointer;
  transition: background .15s;
  border-bottom: 1px solid #F7FAFC;
}
.ubigeo-option:hover { background: #EBF8FF; }
.ubigeo-option.selected { background: #BEE3F8; }

.ubigeo-option-code {
  font-size: .6875rem;
  font-weight: 700;
  color: #3B6AA0;
}

.ubigeo-option-name {
  font-size: .8125rem;
  color: #1A202C;
  line-height: 1.4;
}

.field-error { font-size: .75rem; color: #E53E3E; }
</style>
