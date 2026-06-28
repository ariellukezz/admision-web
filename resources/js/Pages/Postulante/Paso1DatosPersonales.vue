<template>
  <Head title="Datos Personales" />
  <PostulanteLayout :currentStep="1" stepTitle="Datos Personales" :stepIcon="iconUser">
    <template #subtitle>Ingrese su número de DNI para buscar sus datos o complete el formulario manualmente.</template>

    <div class="page-content">
      <div class="mobile-only">
        <h2 class="page-title">Datos Personales</h2>
        <p class="page-subtitle">Ingrese su número de DNI para buscar sus datos.</p>
      </div>

      <form @submit.prevent="submit" class="form">
        <DniSearchField
          v-model="form.nro_doc"
          :loading="searching"
          :error="form.errors.nro_doc"
          @search="searchDni"
          class="field-full"
        />

        <FormInput label="Primer Apellido" v-model="form.primer_apellido" placeholder="Ej. Quispe" :error="form.errors.primer_apellido">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.787-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg></template>
        </FormInput>

        <FormInput label="Segundo Apellido" v-model="form.segundo_apellido" placeholder="Ej. Mamani" :error="form.errors.segundo_apellido">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.787-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg></template>
        </FormInput>

        <FormInput label="Nombres" v-model="form.nombres" placeholder="Ej. Juan Carlos" :error="form.errors.nombres" class="field-full">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg></template>
        </FormInput>

        <FormInput label="Fecha de Nacimiento" type="date" v-model="form.fec_nacimiento" :error="form.errors.fec_nacimiento">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg></template>
        </FormInput>

        <FormSelect label="Sexo" v-model="form.sexo" placeholder="Seleccionar..." :options="sexoOptions" :error="form.errors.sexo">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.787-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg></template>
        </FormSelect>

        <UbigeoSearch
          label="Lugar de Nacimiento"
          placeholder="Buscar distrito, departamento o provincia..."
          v-model="form.ubigeo_nacimiento"
          :initial-value="p?.ubigeo_nacimiento || ''"
          :error="form.errors.ubigeo_nacimiento"
          class="field-full"
        >
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg></template>
        </UbigeoSearch>

        <div class="spacer"></div>
      </form>
    </div>

    <template #actions>
      <StepActions next-label="Continuar" @back="$inertia.visit(route('postulante.dashboard'))" @next="submit" :show-back="true" :loading="form.processing" />
    </template>
  </PostulanteLayout>
</template>

<script setup>
import PostulanteLayout from '@/Layouts/PostulanteLayout.vue';
import FormInput from './components/FormInput.vue';
import FormSelect from './components/FormSelect.vue';
import DniSearchField from './components/DniSearchField.vue';
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
const iconUser = '<svg viewBox="0 0 24 24" fill="none" stroke="#F6AD55" stroke-width="1.5"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>';

const sexoOptions = [
  { value: '1', label: 'Masculino' },
  { value: '2', label: 'Femenino' }
];

const form = useForm({
  nro_doc: p?.nro_doc || '',
  tipo_doc: p?.tipo_doc || 1,
  primer_apellido: p?.primer_apellido || '',
  segundo_apellido: p?.segundo_apellido || '',
  nombres: p?.nombres || '',
  fec_nacimiento: p?.fec_nacimiento || '',
  ubigeo_nacimiento: p?.ubigeo_nacimiento || '',
  sexo: p?.sexo || '',
  estado_civil: p?.estado_civil || '',
  mayor_edad: true,
});

const searching = ref(false);

const searchDni = async () => {
  if (!form.nro_doc || form.nro_doc.length !== 8) return;
  searching.value = true;
  try {
    const res = await axios.get(route('postulante.api.reniec', form.nro_doc));
    if (res.data.success && res.data.datos) {
      const d = res.data.datos;
      form.primer_apellido = d.ap_paterno || '';
      form.segundo_apellido = d.ap_materno || '';
      form.nombres = d.nombres || '';
      form.fec_nacimiento = d.fecha_nacimiento || '';
      form.sexo = d.sexo || '';
      form.estado_civil = d.estado_civil || '';
      form.ubigeo_nacimiento = d.ubigeo_nacimiento || '';
      form.mayor_edad = res.data.mayor_edad ?? true;
    }
  } catch (e) {
  } finally {
    searching.value = false;
  }
};

const submit = () => {
  form.post(route('postulante.paso1'));
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
