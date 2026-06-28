<template>
  <Head title="Datos de Contacto" />
  <PostulanteLayout :currentStep="2" stepTitle="Datos de Contacto" :stepIcon="iconContact">
    <template #subtitle>Correo y celular serán validados en tiempo real.</template>

    <div class="page-content">
      <div class="mobile-only">
        <h2 class="page-title">Datos de Contacto</h2>
        <p class="page-subtitle">Correo y celular serán validados en tiempo real.</p>
      </div>

      <form @submit.prevent="submit" class="form">
        <input type="hidden" v-model="form.id_postulante" />

        <FormInput label="Correo Electrónico" type="email" v-model="form.email" placeholder="correo@ejemplo.com" :error="form.errors.email" @blur="validarCorreo">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg></template>
        </FormInput>

        <FormInput label="Celular" type="tel" v-model="form.celular" placeholder="951 234 567" :error="form.errors.celular" @blur="validarCelular">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg></template>
        </FormInput>

        <UbigeoSearch
          label="Lugar de Residencia"
          placeholder="Buscar distrito, departamento o provincia..."
          v-model="form.ubigeo_residencia"
          :initial-value="p?.ubigeo_residencia || ''"
          :error="form.errors.ubigeo_residencia"
          class="field-full"
        >
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg></template>
        </UbigeoSearch>

        <FormInput label="Dirección" v-model="form.direccion" placeholder="Av. / Jr. / Calle N° ..." :error="form.errors.direccion" class="field-full">
          <template #icon><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 0h.008v.008h-.008V7.5z"/></svg></template>
        </FormInput>

        <div class="spacer"></div>
      </form>
    </div>

    <template #actions>
      <StepActions next-label="Continuar" :show-back="true" @back="$inertia.visit(route('postulante.paso1'))" @next="submit" :loading="form.processing" />
    </template>
  </PostulanteLayout>
</template>

<script setup>
import PostulanteLayout from '@/Layouts/PostulanteLayout.vue';
import FormInput from './components/FormInput.vue';
import UbigeoSearch from './components/UbigeoSearch.vue';
import StepActions from './components/StepActions.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  postulante: { type: Object, default: null },
  avance: { type: Number, default: 0 },
});

const p = props.postulante;
const iconContact = '<svg viewBox="0 0 24 24" fill="none" stroke="#F6AD55" stroke-width="1.5"><path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>';

const form = useForm({
  id_postulante: p?.id || '',
  email: p?.email || '',
  celular: p?.celular || '',
  direccion: p?.direccion || '',
  ubigeo_residencia: p?.ubigeo_residencia || '',
  dni: p?.nro_doc || '',
});

const validarCorreo = async () => {
  if (!form.email || !form.email.includes('@')) return;
  try {
    const res = await axios.post(route('postulante.api.validar-correo'), { email: form.email, dni: form.dni });
    if (res.data.existe) form.setError('email', 'Este correo ya está registrado por otro postulante');
    else form.clearErrors('email');
  } catch (e) {}
};

const validarCelular = async () => {
  if (!form.celular || form.celular.length < 9) return;
  try {
    const res = await axios.post(route('postulante.api.validar-celular'), { celular: form.celular, dni: form.dni });
    if (res.data.existe) form.setError('celular', 'Este celular ya está registrado por otro postulante');
    else form.clearErrors('celular');
  } catch (e) {}
};

const submit = () => {
  form.post(route('postulante.paso2'));
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
