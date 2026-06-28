<template>
  <Head title="Apoderado" />
  <PostulanteLayout :currentStep="4" stepTitle="Apoderado" :stepIcon="iconApoderado">
    <template #subtitle>Paso opcional — puede omitir si no aplica.</template>

    <div class="page-content">
      <div class="mobile-only">
        <h2 class="page-title">Datos del Apoderado</h2>
        <p class="page-subtitle">Datos del padre, madre o tutor responsable del postulante.</p>
      </div>

      <form @submit.prevent="submit" class="form">
        <input type="hidden" v-model="form.id_postulante" />

        <!-- Toggle -->
        <div class="toggle-card">
          <div class="toggle-left">
            <div class="toggle-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.787-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
            </div>
            <span class="toggle-question">¿Tiene apoderado?</span>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" v-model="tieneApoderado" />
            <span class="slider"></span>
          </label>
        </div>

        <!-- Apoderados -->
        <template v-if="tieneApoderado">
          <div class="apoderado-card" v-for="(apo, idx) in apoderados" :key="idx">
            <div class="apo-header">
              <div class="apo-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
              </div>
              <span class="apo-title">Apoderado {{ idx + 1 }}</span>
              <button v-if="apoderados.length > 1" type="button" class="apo-remove" @click="apoderados.splice(idx, 1)">✕</button>
            </div>

            <FormSelect label="Tipo de Apoderado" v-model="apo.tipo_apoderado" placeholder="Seleccionar..." :options="parentescoOptions">
              <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.787-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg></template>
            </FormSelect>

            <DniSearchField
              v-model="apo.nro_documento"
              :loading="apo.searching"
              @search="searchDniApoderado(idx)"
              class="field-full"
            />

            <FormInput label="Nombres" v-model="apo.nombres" placeholder="Nombres">
              <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg></template>
            </FormInput>

            <FormInput label="Apellido Paterno" v-model="apo.paterno" placeholder="Apellido paterno">
              <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5z"/></svg></template>
            </FormInput>

            <FormInput label="Apellido Materno" v-model="apo.materno" placeholder="Apellido materno">
              <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5z"/></svg></template>
            </FormInput>
          </div>

          <button type="button" class="add-apoderado" @click="addApoderado">+ Agregar apoderado</button>
        </template>

        <div class="spacer"></div>
      </form>
    </div>

    <template #actions>
      <StepActions next-label="Finalizar" :show-back="true" @back="$inertia.visit(route('postulante.paso3'))" @next="submit" :loading="form.processing" />
    </template>
  </PostulanteLayout>
</template>

<script setup>
import PostulanteLayout from '@/Layouts/PostulanteLayout.vue';
import FormInput from './components/FormInput.vue';
import FormSelect from './components/FormSelect.vue';
import DniSearchField from './components/DniSearchField.vue';
import StepActions from './components/StepActions.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
  postulante: { type: Object, default: null },
  apoderados_existentes: { type: Array, default: () => [] },
  avance: { type: Number, default: 0 },
});

const iconApoderado = '<svg viewBox="0 0 24 24" fill="none" stroke="#F6AD55" stroke-width="1.5"><path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.787-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>';

const parentescoOptions = [
  { value: 1, label: 'Padre' },
  { value: 2, label: 'Madre' },
  { value: 3, label: 'Apoderado' },
];

// Pre-fill with existing apoderados
const tieneApoderado = ref(props.apoderados_existentes.length > 0);

const createApoderado = (existing = null) => ({
  tipo_apoderado: existing?.tipo_apoderado || '',
  nro_documento: existing?.nro_documento || '',
  nombres: existing?.nombres || '',
  paterno: existing?.paterno || '',
  materno: existing?.materno || '',
  searching: false,
});

// Initialize with existing data or empty
const apoderados = reactive(
  props.apoderados_existentes.length > 0
    ? props.apoderados_existentes.map(a => createApoderado(a))
    : [createApoderado()]
);

const addApoderado = () => {
  apoderados.push(createApoderado());
};

const searchDniApoderado = async (idx) => {
  const apo = apoderados[idx];
  if (!apo.nro_documento || apo.nro_documento.length !== 8) return;
  apo.searching = true;
  try {
    const res = await axios.get(route('postulante.api.reniec', apo.nro_documento));
    if (res.data.success && res.data.datos) {
      const d = res.data.datos;
      apo.nombres = d.nombres || '';
      apo.paterno = d.ap_paterno || '';
      apo.materno = d.ap_materno || '';
    }
  } catch (e) {} finally {
    apo.searching = false;
  }
};

const form = useForm({
  id_postulante: props.postulante?.id || '',
  tiene_apoderado: true,
  apoderados: [],
});

const submit = () => {
  form.tiene_apoderado = tieneApoderado.value;
  form.apoderados = tieneApoderado.value
    ? apoderados.map(a => ({
        tipo_apoderado: Number(a.tipo_apoderado),
        nro_documento: a.nro_documento,
        nombres: a.nombres,
        paterno: a.paterno,
        materno: a.materno,
      }))
    : [];
  form.post(route('postulante.paso4'));
};
</script>

<style scoped>
.page-title { font-size: 1.5rem; font-weight: 700; color: #1A202C; margin-bottom: .25rem; }
.page-subtitle { font-size: .875rem; color: #4A5568; margin-bottom: 1.25rem; line-height: 1.5; }
.form { display: flex; flex-direction: column; gap: 1rem; }
.spacer { height: 1rem; }

.toggle-card {
  display: flex; align-items: center; justify-content: space-between;
  background: #fff; border: 1px solid #E2E8F0; border-radius: 12px; padding: 1rem;
}
.toggle-left { display: flex; align-items: center; gap: .75rem; }
.toggle-icon { width: 40px; height: 40px; background: rgba(59,106,160,.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #3B6AA0; }
.toggle-icon svg { width: 20px; height: 20px; }
.toggle-question { font-size: .9375rem; font-weight: 600; color: #1A202C; }

.toggle-switch { position: relative; width: 48px; height: 26px; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #CBD5E0; border-radius: 26px; transition: .3s; }
.slider::before { content: ""; position: absolute; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .3s; }
.toggle-switch input:checked + .slider { background: #3B6AA0; }
.toggle-switch input:checked + .slider::before { transform: translateX(22px); }

.apoderado-card {
  background: #fff; border: 1px solid #E2E8F0; border-radius: 12px;
  padding: 1rem; display: flex; flex-direction: column; gap: 1rem;
}
.apo-header { display: flex; align-items: center; gap: .5rem; }
.apo-icon { width: 32px; height: 32px; background: rgba(59,106,160,.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #3B6AA0; }
.apo-icon svg { width: 16px; height: 16px; }
.apo-title { font-size: .9375rem; font-weight: 700; color: #1A202C; flex: 1; }
.apo-remove { background: none; border: none; color: #A0AEC0; font-size: 1rem; cursor: pointer; padding: .25rem; }
.apo-remove:hover { color: #E53E3E; }

.add-apoderado {
  display: flex; align-items: center; justify-content: center; gap: .375rem;
  width: 100%; padding: .75rem; border: 1px dashed #3B6AA0; border-radius: 10px;
  background: transparent; color: #3B6AA0; font-size: .875rem; font-weight: 600;
  cursor: pointer; transition: all .2s;
}
.add-apoderado:hover { background: rgba(59,106,160,.04); border-color: #2D5A8E; }

@media (min-width: 768px) {
  .mobile-only { display: none; }
  .field-full { grid-column: 1 / -1; }
}
</style>
