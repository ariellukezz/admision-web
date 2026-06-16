<template>
  <Head title="Registro de Postulante" />

  <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-xl rounded-2xl" style="min-height: calc(100vh - 103px);">

      <!-- Header -->
      <div class="border-b border-gray-100 px-8 py-6 bg-white rounded-t-2xl">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Registro de Postulante</h1>
        <p class="text-sm text-gray-500 mt-1">Registre un postulante directamente sin verificación por correo</p>
      </div>

      <!-- Form -->
      <div class="px-8 py-6">
        <a-form :model="form" layout="vertical">
          <a-row :gutter="16">

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Doc</label>
              <a-select v-model:value="form.tipo_doc" class="w-full">
                <a-select-option :value="1">DNI</a-select-option>
                <a-select-option :value="3">Carné Ext.</a-select-option>
              </a-select>
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">N° Documento *</label>
              <a-input v-model:value="form.nro_doc" :maxlength="form.tipo_doc === 1 ? 8 : 12" placeholder="Ingrese el número" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Ap. Paterno *</label>
              <a-input v-model:value="form.primer_apellido" placeholder="Primer apellido" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Ap. Materno</label>
              <a-input v-model:value="form.segundo_apellido" placeholder="Segundo apellido" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Ap. Casada</label>
              <a-input v-model:value="form.apellido_casada" placeholder="Apellido de casada" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Nombres *</label>
              <a-input v-model:value="form.nombres" placeholder="Nombres completos" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Sexo *</label>
              <a-select v-model:value="form.sexo" placeholder="Seleccionar...">
                <a-select-option value="1">Masculino</a-select-option>
                <a-select-option value="2">Femenino</a-select-option>
              </a-select>
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">F. Nacimiento *</label>
              <a-date-picker v-model:value="form.fec_nacimiento" format="DD/MM/YYYY" style="width: 100%;" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Ubigeo Nacimiento *</label>
              <a-input v-model:value="form.ubigeo_nacimiento" placeholder="Código ubigeo" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Ubigeo Residencia *</label>
              <a-input v-model:value="form.ubigeo_residencia" placeholder="Código ubigeo" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Celular *</label>
              <a-input v-model:value="form.celular" placeholder="Número de celular" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Correo *</label>
              <a-input v-model:value="form.email" placeholder="correo@ejemplo.com" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Estado Civil</label>
              <a-select v-model:value="form.estado_civil" placeholder="Seleccionar...">
                <a-select-option value="1">Soltero</a-select-option>
                <a-select-option value="2">Casado</a-select-option>
              </a-select>
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
              <a-input v-model:value="form.direccion" placeholder="Dirección" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Año Egreso</label>
              <a-input v-model:value="form.anio_egreso" placeholder="Ej: 2025" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Colegio (ID)</label>
              <a-input v-model:value="form.id_colegio" placeholder="ID del colegio" />
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Proceso *</label>
              <a-select v-model:value="form.proceso" placeholder="Seleccionar proceso...">
                <a-select-option v-for="p in procesos" :key="p.id" :value="p.id">{{ p.nombre }}</a-select-option>
              </a-select>
            </a-col>

            <a-col :xs="24" :sm="12" :md="6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
              <a-input v-model:value="form.observaciones" placeholder="Observaciones" />
            </a-col>

          </a-row>

          <!-- Actions -->
          <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button @click="limpiar" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
              Limpiar
            </button>
            <button @click="guardar" :disabled="guardando" class="px-6 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
              {{ guardando ? 'Guardando...' : 'Registrar Postulante' }}
            </button>
          </div>
        </a-form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { reactive, ref, onMounted } from 'vue';
import { notification } from 'ant-design-vue';
import dayjs from 'dayjs';
import { format } from 'date-fns';
import axios from 'axios';

const guardando = ref(false);
const procesos = ref([]);

const form = reactive({
  id: null,
  tipo_doc: 1,
  nro_doc: '',
  primer_apellido: '',
  segundo_apellido: '',
  apellido_casada: '',
  nombres: '',
  sexo: '',
  fec_nacimiento: '',
  ubigeo_nacimiento: '',
  ubigeo_residencia: '',
  celular: '',
  email: '',
  estado_civil: '',
  direccion: '',
  anio_egreso: '',
  observaciones: '',
  id_colegio: '',
  proceso: null,
});

const limpiar = () => {
  Object.assign(form, {
    id: null,
    tipo_doc: 1,
    nro_doc: '',
    primer_apellido: '',
    segundo_apellido: '',
    apellido_casada: '',
    nombres: '',
    sexo: '',
    fec_nacimiento: '',
    ubigeo_nacimiento: '',
    ubigeo_residencia: '',
    celular: '',
    email: '',
    estado_civil: '',
    direccion: '',
    anio_egreso: '',
    observaciones: '',
    id_colegio: '',
    proceso: null,
  });
};

const guardar = async () => {
  if (!form.nro_doc || !form.nombres || !form.primer_apellido || !form.email || !form.proceso) {
    notification.warning({ message: 'Datos incompletos', description: 'Complete los campos obligatorios (*).' });
    return;
  }

  guardando.value = true;
  try {
    // 1. Save postulante
    const fecNac = form.fec_nacimiento ? format(new Date(form.fec_nacimiento), 'yyyy-MM-dd') : null;

    const res = await axios.post('/save-postulante-admin', {
      id: form.id,
      tipo_doc: form.tipo_doc,
      nro_doc: form.nro_doc,
      primer_apellido: form.primer_apellido,
      segundo_apellido: form.segundo_apellido,
      apellido_casada: form.apellido_casada,
      nombres: form.nombres,
      sexo: form.sexo,
      fec_nacimiento: fecNac,
      ubigeo_nacimiento: form.ubigeo_nacimiento,
      ubigeo_residencia: form.ubigeo_residencia,
      celular: form.celular,
      correo: form.email,
      estado_civil: form.estado_civil,
      direccion: form.direccion,
      egreso: form.anio_egreso,
      observaciones: form.observaciones,
      colegio: form.id_colegio,
    });

    if (res.data.estado === true) {
      const postulanteId = res.data.datos.id;

      // 2. Register initial step (Paso 1)
      await axios.post('/save-pasos-preinscripcion', {
        id: null,
        nombre: 'Registro de datos preinscripcion',
        nro: 1,
        avance: 16,
        postulante: postulanteId,
        proceso: form.proceso,
      });

      notification.success({
        message: 'Postulante registrado',
        description: `${form.nombres} ${form.primer_apellido} registrado correctamente.`,
      });
      limpiar();
    }
  } catch (e) {
    notification.error({
      message: 'Error',
      description: e.response?.data?.mensaje || 'No se pudo registrar el postulante.',
    });
  } finally {
    guardando.value = false;
  }
};

const getProcesos = async () => {
  try {
    const res = await axios.get('/admin/configuracion-citacion/procesos');
    procesos.value = res.data.datos || [];
  } catch {
    // fallback: use select-proceso endpoint
    try {
      const res = await axios.get('/get-select-proceso');
      procesos.value = res.data.datos || [];
    } catch {
      procesos.value = [];
    }
  }
};

onMounted(() => {
  getProcesos();
});
</script>
