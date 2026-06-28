<template>
  <Head title="Datos del Colegio" />
  <PostulanteLayout :currentStep="3" stepTitle="Datos del Colegio" :stepIcon="iconSchool">
    <template #subtitle>Seleccione la ubicación y colegio de procedencia.</template>

    <div class="page-content">
      <div class="mobile-only">
        <h2 class="page-title">Datos del Colegio</h2>
        <p class="page-subtitle">Seleccione la ubicación y colegio de procedencia.</p>
      </div>

      <form @submit.prevent="submit" class="form">
        <input type="hidden" v-model="form.id_postulante" />

        <UbigeoSearch
          label="Ubicación del Colegio (Dep / Prov / Dist)"
          placeholder="Buscar distrito, departamento o provincia..."
          v-model="ubigeoColegio"
          :initial-value="initialUbigeo"
          :error="form.errors.id_colegio ? 'Seleccione un colegio' : ''"
          class="field-full"
          @select="onUbigeoSelected"
        >
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg></template>
        </UbigeoSearch>

        <FormSelect label="Colegio" v-model="form.id_colegio" placeholder="Seleccionar colegio" :options="colegios" :error="form.errors.id_colegio" :disabled="colegios.length === 0" class="field-full">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg></template>
        </FormSelect>

        <FormSelect label="Año de Egreso" v-model="form.anio_egreso" placeholder="Seleccionar..." :options="anioOptions" :error="form.errors.anio_egreso">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg></template>
        </FormSelect>

        <div class="spacer"></div>
      </form>
    </div>

    <template #actions>
      <StepActions next-label="Continuar" :show-back="true" @back="$inertia.visit(route('postulante.paso2'))" @next="submit" :loading="form.processing" />
    </template>
  </PostulanteLayout>
</template>

<script setup>
import PostulanteLayout from '@/Layouts/PostulanteLayout.vue';
import FormSelect from './components/FormSelect.vue';
import UbigeoSearch from './components/UbigeoSearch.vue';
import StepActions from './components/StepActions.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  postulante: { type: Object, default: null },
  avance: { type: Number, default: 0 },
});

const p = props.postulante;
const iconSchool = '<svg viewBox="0 0 24 24" fill="none" stroke="#F6AD55" stroke-width="1.5"><path d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342"/></svg>';

const anioMax = new Date().getFullYear() - 1; // año anterior como máximo
const anioOptions = Array.from({ length: anioMax - 1949 }, (_, i) => {
  const y = anioMax - i;
  return { value: String(y), label: String(y) };
});

const form = useForm({
  id_postulante: p?.id || '',
  id_colegio: p?.id_colegio || '',
  anio_egreso: p?.anio_egreso ? String(p.anio_egreso) : '',
});

const ubigeoColegio = ref('');
const colegios = ref([]);
const initialUbigeo = ref(p?.colegio_ubigeo || '');

// If postulante already has a colegio, pre-populate
if (p?.id_colegio && p?.colegio_nombre) {
  colegios.value = [{ value: p.id_colegio, label: p.colegio_nombre }];
}

// On mount, UbigeoSearch will auto-load the label from the initial value

const onUbigeoSelected = async (ubigeoCode) => {
  colegios.value = [];
  form.id_colegio = '';
  if (!ubigeoCode) return;

  try {
    const res = await axios.get(route('postulante.api.colegios', ubigeoCode));
    colegios.value = res.data.datos;
  } catch (e) {
    colegios.value = [];
  }
};

const submit = () => {
  form.post(route('postulante.paso3'));
};
</script>

<style scoped>
.page-title { font-size: 1.5rem; font-weight: 700; color: #1A202C; margin-bottom: .25rem; }
.page-subtitle { font-size: .875rem; color: #4A5568; margin-bottom: 1.25rem; line-height: 1.5; }
.form { display: flex; flex-direction: column; gap: 1rem; }
.spacer { height: 1rem; }

@media (min-width: 768px) {
  .mobile-only { display: none; }
  .field-full { grid-column: 1 / -1; }
}
</style>
