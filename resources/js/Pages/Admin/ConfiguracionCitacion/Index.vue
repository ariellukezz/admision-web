<template>
  <Head title="Configuración de Citación" />

  <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-xl rounded-2xl" style="height: calc(100vh - 103px); display: flex; flex-direction: column;">

      <!-- Header -->
      <div class="border-b border-gray-100 px-8 py-6 bg-white rounded-t-2xl">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Configuración de Citación</h1>
            <p class="text-sm text-gray-500 mt-1">Define fechas y criterios de citación presencial por proceso de admisión</p>
          </div>

          <button
            @click="showModal"
            class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md flex items-center gap-2"
          >
            <span>+</span> Nueva configuración
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="flex-1 overflow-auto px-8 py-6">
        <table class="w-full">
          <thead class="bg-gray-50 rounded-lg sticky top-0">
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Proceso</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Criterio</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Valor</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Horario</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Lugar</th>
              <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in configuraciones" :key="item.id" class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors">
              <td class="py-3 px-4">
                <span class="text-sm font-medium text-gray-900">{{ item.proceso_nombre }}</span>
              </td>
              <td class="py-3 px-4">
                <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold"
                  :class="criterioBadgeClass(item.tipo_criterio)">
                  {{ criterioLabel(item.tipo_criterio) }}
                </span>
              </td>
              <td class="py-3 px-4">
                <span class="text-sm text-gray-600">{{ item.valor || '—' }}</span>
              </td>
              <td class="py-3 px-4">
                <span class="text-sm text-gray-900 font-medium">{{ formatDate(item.fecha) }}</span>
              </td>
              <td class="py-3 px-4">
                <span class="text-sm text-gray-600">{{ item.hora_inicio }} - {{ item.hora_fin }}</span>
              </td>
              <td class="py-3 px-4">
                <span class="text-sm text-gray-600">{{ item.lugar }}</span>
              </td>
              <td class="py-3 px-4 text-center">
                <span v-if="item.estado" class="inline-block w-2 h-2 rounded-full bg-green-500" title="Activo"></span>
                <span v-else class="inline-block w-2 h-2 rounded-full bg-gray-300" title="Inactivo"></span>
              </td>
              <td class="py-3 px-4 text-center">
                <button @click="editar(item)" class="text-blue-600 hover:text-blue-800 text-sm font-medium mr-3">Editar</button>
                <button @click="confirmarEliminar(item)" class="text-red-500 hover:text-red-700 text-sm font-medium">Eliminar</button>
              </td>
            </tr>
            <tr v-if="configuraciones.length === 0">
              <td colspan="8" class="py-8 text-center text-gray-400 text-sm">No hay configuraciones de citación registradas.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Crear/Editar -->
    <a-modal
      v-model:open="modalVisible"
      :title="editando ? 'Editar Configuración' : 'Nueva Configuración de Citación'"
      width="600px"
      :footer="null"
      @cancel="cerrarModal"
    >
      <div class="flex flex-col gap-4">
        <!-- Proceso -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Proceso de admisión *</label>
          <select v-model="form.id_proceso" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
            <option value="">Seleccionar proceso...</option>
            <option v-for="p in procesos" :key="p.id" :value="p.id">{{ p.nombre }}</option>
          </select>
        </div>

        <!-- Tipo de criterio -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de criterio *</label>
          <select v-model="form.tipo_criterio" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
            <option value="mismo_dia">Mismo día (todos)</option>
            <option value="dni_digito">Por último dígito de DNI</option>
            <option value="area">Por área</option>
            <option value="modalidad">Por modalidad</option>
            <option value="programa">Por programa</option>
          </select>
        </div>

        <!-- Valor dinámico -->
        <div v-if="form.tipo_criterio !== 'mismo_dia'">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ valorLabel }} *
          </label>

          <input
            v-if="form.tipo_criterio === 'dni_digito'"
            v-model="form.valor"
            type="text"
            placeholder="Ej: 0-1, 2-3, 4-5..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
          />
          <p v-if="form.tipo_criterio === 'dni_digito'" class="text-xs text-gray-400 mt-1">
            Use rangos separados por guion (ej: "0-1" para DNI terminados en 0 o 1). También soporta 2 dígitos (ej: "00-01").
          </p>

          <select
            v-else-if="form.tipo_criterio === 'area'"
            v-model="form.valor"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
          >
            <option value="">Seleccionar área...</option>
            <option v-for="a in areas" :key="a" :value="a">{{ a }}</option>
          </select>

          <select
            v-else-if="form.tipo_criterio === 'modalidad'"
            v-model="form.valor"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
          >
            <option value="">Seleccionar modalidad...</option>
            <option v-for="m in modalidades" :key="m.id" :value="m.nombre">{{ m.nombre }}</option>
          </select>

          <select
            v-else-if="form.tipo_criterio === 'programa'"
            v-model="form.valor"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"
          >
            <option value="">Seleccionar programa...</option>
            <option v-for="p in programas" :key="p.id" :value="p.nombre">{{ p.nombre }} ({{ p.area }})</option>
          </select>
        </div>

        <!-- Fecha y horario -->
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
            <input v-model="form.fecha" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hora inicio *</label>
            <input v-model="form.hora_inicio" type="time" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hora fin *</label>
            <input v-model="form.hora_fin" type="time" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
          </div>
        </div>

        <!-- Lugar -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Lugar *</label>
          <input v-model="form.lugar" type="text" placeholder="Ej: Dirección de Admisión - UNAP" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
        </div>

        <!-- Instrucciones -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Instrucciones</label>
          <textarea v-model="form.instrucciones" rows="3" placeholder="Ej: Acercarse con documentos originales y copias simples..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 resize-y"></textarea>
        </div>

        <!-- Estado -->
        <div class="flex items-center gap-2">
          <input type="checkbox" v-model="form.estado" id="estado-config" class="rounded" />
          <label for="estado-config" class="text-sm text-gray-700">Configuración activa</label>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
          <button @click="cerrarModal" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Cancelar</button>
          <button @click="guardar" :disabled="guardando" class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
            {{ guardando ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </a-modal>

    <!-- Confirmar eliminar -->
    <a-modal v-model:open="eliminarVisible" title="Eliminar configuración" :footer="null" width="400px">
      <p class="text-sm text-gray-600">¿Está seguro de eliminar esta configuración de citación?</p>
      <div class="flex justify-end gap-3 mt-4">
        <button @click="eliminarVisible = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancelar</button>
        <button @click="eliminar" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg">Eliminar</button>
      </div>
    </a-modal>

  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, onMounted } from 'vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const configuraciones = ref([]);
const procesos = ref([]);
const modalidades = ref([]);
const programas = ref([]);
const areas = ref([]);
const modalVisible = ref(false);
const eliminarVisible = ref(false);
const editando = ref(false);
const guardando = ref(false);
const eliminarItem = ref(null);

const form = ref({
  id: null,
  id_proceso: '',
  tipo_criterio: 'mismo_dia',
  valor: '',
  fecha: '',
  hora_inicio: '',
  hora_fin: '',
  lugar: '',
  instrucciones: '',
  estado: true,
});

const valorLabel = computed(() => {
  const labels = {
    dni_digito: 'Dígito(s) de DNI',
    area: 'Área',
    modalidad: 'Modalidad',
    programa: 'Programa',
  };
  return labels[form.value.tipo_criterio] || 'Valor';
});

const criterioLabel = (tipo) => {
  const labels = {
    mismo_dia: 'Mismo día',
    dni_digito: 'DNI',
    area: 'Área',
    modalidad: 'Modalidad',
    programa: 'Programa',
  };
  return labels[tipo] || tipo;
};

const criterioBadgeClass = (tipo) => {
  const classes = {
    mismo_dia: 'bg-blue-100 text-blue-700',
    dni_digito: 'bg-purple-100 text-purple-700',
    area: 'bg-amber-100 text-amber-700',
    modalidad: 'bg-indigo-100 text-indigo-700',
    programa: 'bg-green-100 text-green-700',
  };
  return classes[tipo] || 'bg-gray-100 text-gray-700';
};

const formatDate = (date) => {
  if (!date) return '';
  const d = new Date(date + 'T00:00:00');
  return d.toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: 'numeric' });
};

const showModal = () => {
  limpiarForm();
  editando.value = false;
  modalVisible.value = true;
};

const editar = (item) => {
  form.value = {
    id: item.id,
    id_proceso: item.id_proceso,
    tipo_criterio: item.tipo_criterio,
    valor: item.valor || '',
    fecha: item.fecha,
    hora_inicio: item.hora_inicio,
    hora_fin: item.hora_fin,
    lugar: item.lugar,
    instrucciones: item.instrucciones || '',
    estado: item.estado,
  };
  editando.value = true;
  modalVisible.value = true;
};

const limpiarForm = () => {
  form.value = {
    id: null,
    id_proceso: '',
    tipo_criterio: 'mismo_dia',
    valor: '',
    fecha: '',
    hora_inicio: '',
    hora_fin: '',
    lugar: '',
    instrucciones: '',
    estado: true,
  };
};

const cerrarModal = () => {
  modalVisible.value = false;
  limpiarForm();
};

const guardar = async () => {
  if (!form.value.id_proceso || !form.value.fecha || !form.value.hora_inicio || !form.value.hora_fin || !form.value.lugar) {
    notification.warning({ message: 'Datos incompletos', description: 'Complete todos los campos obligatorios.' });
    return;
  }

  guardando.value = true;
  try {
    if (editando.value) {
      await axios.put(`/admin/configuracion-citacion/${form.value.id}`, form.value);
      notification.success({ message: 'Actualizado', description: 'Configuración actualizada correctamente.' });
    } else {
      await axios.post('/admin/configuracion-citacion', form.value);
      notification.success({ message: 'Creado', description: 'Configuración creada correctamente.' });
    }
    modalVisible.value = false;
    limpiarForm();
    await getConfiguraciones();
  } catch (e) {
    notification.error({ message: 'Error', description: e.response?.data?.mensaje || 'No se pudo guardar.' });
  } finally {
    guardando.value = false;
  }
};

const confirmarEliminar = (item) => {
  eliminarItem.value = item;
  eliminarVisible.value = true;
};

const eliminar = async () => {
  try {
    await axios.delete(`/admin/configuracion-citacion/${eliminarItem.value.id}`);
    notification.success({ message: 'Eliminado', description: 'Configuración eliminada.' });
    eliminarVisible.value = false;
    eliminarItem.value = null;
    await getConfiguraciones();
  } catch {
    notification.error({ message: 'Error', description: 'No se pudo eliminar.' });
  }
};

const getConfiguraciones = async () => {
  try {
    const res = await axios.get('/admin/configuracion-citacion/lista');
    configuraciones.value = res.data.datos || [];
  } catch {
    notification.error({ message: 'Error', description: 'No se pudieron cargar las configuraciones.' });
  }
};

const getProcesos = async () => {
  const res = await axios.get('/admin/configuracion-citacion/procesos');
  procesos.value = res.data.datos || [];
};

const getModalidades = async () => {
  const res = await axios.get('/admin/configuracion-citacion/modalidades');
  modalidades.value = res.data.datos || [];
};

const getProgramas = async () => {
  const res = await axios.get('/admin/configuracion-citacion/programas');
  programas.value = res.data.datos || [];
  areas.value = res.data.areas || [];
};

onMounted(() => {
  getConfiguraciones();
  getProcesos();
  getModalidades();
  getProgramas();
});
</script>
