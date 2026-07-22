<template>
  <Head title="Requisitos" />

  <AuthenticatedLayout>
    <div class="overflow-hidden shadow-xl rounded-2xl" style="height: calc(100vh - 103px); display: flex; flex-direction: column; background: var(--card-bg); border: 1px solid var(--card-border); color: var(--card-text);">

      <!-- Header Premium -->
      <div class="border-b px-8 py-6 rounded-t-2xl" style="border-color: var(--card-border); background: var(--card-bg);">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Requisitos</h1>
            <p class="text-sm text-gray-500 mt-1">Gestión de documentos requeridos por modalidad</p>
          </div>
          
          <div class="flex gap-3">
            <div class="relative">
              <input
                v-model="buscar"
                type="text"
                placeholder="Buscar requisito..."
                class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm w-64 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all"
              />
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
            </div>

            <button
              @click="showModal"
              class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md flex items-center gap-2"
            >
              <span>+</span> Nuevo
            </button>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="flex-1 overflow-auto px-8 py-6">
        <table class="w-full">
          <thead class="rounded-lg sticky top-0" style="background: var(--table-header-bg, #f1f5f9);">
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider w-20">Orden</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requisito</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Modalidades</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Programas</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Documentos aceptados</th>
              <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Obligatorio</th>
              <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">Estado</th>
              <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider w-36">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in requisitosFiltrados" :key="item.id" class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors">
              <td class="py-3 px-4 text-sm text-gray-600 text-center">
                <span class="inline-block w-7 h-7 leading-7 text-center bg-gray-100 rounded-md text-gray-600 text-xs font-medium">{{ item.orden }}</span>
              </td>
              <td class="py-3 px-4">
                <span class="text-sm font-medium text-gray-900">{{ item.nombre }}</span>
              </td>
              <td class="py-3 px-4">
                <div class="flex flex-wrap gap-1">
                  <span v-for="m in item.modalidades" :key="m.id" class="px-2 py-0.5 bg-cyan-50 text-cyan-700 text-xs rounded-md font-medium">
                    {{ m.nombre }}
                  </span>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="flex flex-wrap gap-1">
                  <span v-for="p in item.programas" :key="p.id" class="px-2 py-0.5 bg-purple-50 text-purple-700 text-xs rounded-md font-medium">
                    {{ p.nombre_corto || p.nombre }}
                  </span>
                  <span v-if="!item.programas?.length" class="text-xs text-gray-400">Todos</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <div class="flex flex-wrap gap-1">
                  <span v-for="td in item.tipos_documento" :key="td.id" class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xs rounded-md font-medium">
                    {{ td.nombre }}
                  </span>
                  <span v-if="!item.tipos_documento?.length" class="text-xs text-gray-400">Sin configurar</span>
                </div>
              </td>
              <td class="py-3 px-4 text-center">
                <span :class="item.obligatorio ? 'bg-red-50 text-red-700' : 'bg-gray-50 text-gray-600'" class="px-3 py-1 text-xs rounded-md font-medium inline-block">
                  {{ item.obligatorio ? 'Obligatorio' : 'Opcional' }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <span :class="item.estado ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'" class="px-3 py-1 text-xs rounded-md font-medium inline-block">
                  {{ item.estado ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center justify-center gap-2">
                  <button 
                    @click="abrirEditar(item)" 
                    class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md transition-colors"
                    title="Editar"
                  >
                    ✏️
                  </button>
                  <button 
                    @click="abrirConfigTipos(item)" 
                    class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md transition-colors"
                    title="Configurar documentos"
                  >
                    ⚙️
                  </button>
                  <button 
                    @click="confirmarEliminar(item)" 
                    class="p-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-md transition-colors"
                    title="Eliminar"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!requisitosFiltrados.length">
              <td colspan="8" class="py-12 text-center text-gray-400 text-sm">
                No hay requisitos registrados
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Crear/Editar Requisito -->
    <div v-if="visible" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" @click.self="visible = false">
      <div class="rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden" style="background: var(--card-bg); color: var(--card-text);">
        <div class="px-6 py-5 border-b border-gray-100">
          <h3 class="text-lg font-semibold text-gray-900">{{ requisito.id ? 'Editar requisito' : 'Nuevo requisito' }}</h3>
          <p class="text-sm text-gray-500 mt-1">{{ requisito.id ? 'Modifique la información del requisito' : 'Complete los campos para crear un nuevo requisito' }}</p>
        </div>
        
        <div class="p-6">
          <div class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre del requisito</label>
              <input
                v-model="requisito.nombre"
                type="text"
                placeholder="Ej: Certificado de estudios"
                class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-300 transition-all"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Modalidades</label>
              <div class="border border-gray-200 rounded-lg p-2 max-h-32 overflow-y-auto">
                <label v-for="mod in modalidades" :key="mod.value" class="flex items-center gap-2 py-1.5 px-2 hover:bg-gray-50 rounded cursor-pointer">
                  <input type="checkbox" :value="mod.value" v-model="requisito.modalidades" class="rounded border-gray-300 text-gray-900 focus:ring-gray-500">
                  <span class="text-sm text-gray-700">{{ mod.label }}</span>
                </label>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Programas específicos <span class="text-xs font-normal text-gray-400">(opcional, dejar vacío = aplica a todos)</span></label>
              <div class="border border-gray-200 rounded-lg p-2 max-h-32 overflow-y-auto">
                <label v-for="prog in programas" :key="prog.value" class="flex items-center gap-2 py-1.5 px-2 hover:bg-gray-50 rounded cursor-pointer">
                  <input type="checkbox" :value="prog.value" v-model="requisito.programas" class="rounded border-gray-300 text-gray-900 focus:ring-gray-500">
                  <span class="text-sm text-gray-700">{{ prog.label }}</span>
                </label>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Orden de prioridad</label>
                <input
                  v-model.number="requisito.orden"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-300"
                />
              </div>

              <div class="space-y-3 pt-6">
                <label class="flex items-center justify-between cursor-pointer">
                  <span class="text-sm text-gray-700">Documento obligatorio</span>
                  <div class="relative inline-block w-10 mr-2 align-middle select-none">
                    <input type="checkbox" v-model="requisito.obligatorio" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-2 appearance-none cursor-pointer"/>
                    <label class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 cursor-pointer"></label>
                  </div>
                </label>

                <label v-if="requisito.id" class="flex items-center justify-between cursor-pointer">
                  <span class="text-sm text-gray-700">Estado activo</span>
                  <div class="relative inline-block w-10 mr-2 align-middle select-none">
                    <input type="checkbox" v-model="requisito.estado" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-2 appearance-none cursor-pointer"/>
                    <label class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 cursor-pointer"></label>
                  </div>
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
          <button @click="cancelar" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            Cancelar
          </button>
          <button @click="guardar" class="px-4 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 rounded-lg transition-colors shadow-sm" :disabled="guardando">
            {{ guardando ? 'Guardando...' : (requisito.id ? 'Actualizar' : 'Crear requisito') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Configurar Tipos de Documento -->
    <div v-if="visibleTipos" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" @click.self="visibleTipos = false">
      <div class="rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden" style="background: var(--card-bg); color: var(--card-text);">
        <div class="px-6 py-5 border-b border-gray-100">
          <h3 class="text-lg font-semibold text-gray-900">Documentos aceptados</h3>
          <p class="text-sm text-gray-500 mt-1">{{ requisitoConfig.nombre }}</p>
        </div>
        
        <div class="p-6">
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Agregar tipo de documento</label>
            <select
              v-model="tipoSeleccionado"
              @change="agregarTipo"
              class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-300" style="background: var(--card-bg); color: var(--card-text);"
            >
              <option :value="null">Seleccionar tipo</option>
              <option v-for="td in tiposDisponibles" :key="td.value" :value="td.value">
                {{ td.label }}
              </option>
            </select>
          </div>

          <div>
            <div class="flex justify-between items-center mb-3">
              <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Configurados ({{ requisitoConfig.tipos_documento?.length || 0 }})</span>
            </div>
            
            <div v-if="requisitoConfig.tipos_documento?.length" class="space-y-2 max-h-64 overflow-y-auto">
              <div v-for="td in requisitoConfig.tipos_documento" :key="td.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                <span class="text-sm font-medium text-gray-900">{{ td.nombre }}</span>
                <button @click="quitarTipo(td.id)" class="text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded transition-colors">
                  🗑️
                </button>
              </div>
            </div>

            <div v-else class="text-center py-8 bg-gray-50 rounded-lg border border-gray-100">
              <p class="text-sm text-gray-400">No hay documentos configurados</p>
            </div>
          </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
          <button @click="visibleTipos = false" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            Cancelar
          </button>
          <button @click="guardarTiposDocumento" class="px-4 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 rounded-lg transition-colors shadow-sm" :disabled="guardandoTipos">
            {{ guardandoTipos ? 'Guardando...' : 'Guardar configuración' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Confirmación Eliminar -->
    <div v-if="confirmarEliminarVisible" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50" @click.self="confirmarEliminarVisible = false">
      <div class="rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" style="background: var(--card-bg); color: var(--card-text);">
        <div class="px-6 py-5 border-b border-gray-100">
          <h3 class="text-lg font-semibold text-gray-900">Confirmar eliminación</h3>
        </div>
        <div class="p-6">
          <p class="text-sm text-gray-600">¿Estás seguro de eliminar "{{ eliminarRecord?.nombre }}"? Esta acción no se puede deshacer.</p>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
          <button @click="confirmarEliminarVisible = false" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            Cancelar
          </button>
          <button @click="ejecutarEliminar" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm">
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.toggle-checkbox:checked {
  right: 0;
  border-color: #1f2937;
}
.toggle-checkbox:checked + .toggle-label {
  background-color: #1f2937;
}
.toggle-checkbox {
  right: 4px;
  top: 0px;
  transition: all 0.2s ease;
  border-color: #d1d5db;
}
.toggle-label {
  transition: background-color 0.2s ease;
}
</style>

<style>
/* ── Dark / Hybrid theme table overrides ─────────── */
.theme-dark .ant-table,
.theme-hybrid .ant-table {
    background: transparent !important;
    color: var(--card-text) !important;
}
.theme-dark .ant-table-thead > tr > th,
.theme-hybrid .ant-table-thead > tr > th {
    background: var(--table-header-bg) !important;
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
}
.theme-dark .ant-table-tbody > tr > td,
.theme-hybrid .ant-table-tbody > tr > td {
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
    background: var(--card-bg) !important;
}
.theme-dark .ant-table-tbody > tr:hover > td,
.theme-hybrid .ant-table-tbody > tr:hover > td {
    background: var(--hover-bg) !important;
}
.theme-dark .ant-table-tbody > tr:nth-child(even) > td,
.theme-hybrid .ant-table-tbody > tr:nth-child(even) > td {
    background: var(--row-even) !important;
}
/* Plain HTML table overrides for dark/hybrid */
.theme-dark table thead tr th,
.theme-hybrid table thead tr th {
    color: var(--card-muted) !important;
    border-bottom: 1px solid var(--card-border) !important;
}
.theme-dark table tbody tr td,
.theme-hybrid table tbody tr td {
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
}
.theme-dark table tbody tr:hover td,
.theme-hybrid table tbody tr:hover td {
    background: var(--hover-bg) !important;
}
</style>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const buscar = ref('');
const modalidades = ref([]);
const programas = ref([]);
const tiposDocumento = ref([]);
const requisitos = ref([]);
const visible = ref(false);
const visibleTipos = ref(false);
const confirmarEliminarVisible = ref(false);
const guardando = ref(false);
const guardandoTipos = ref(false);
const tipoSeleccionado = ref(null);
const eliminarRecord = ref(null);

const requisito = reactive({
  id: null,
  nombre: '',
  modalidades: [],
  programas: [],
  obligatorio: true,
  orden: 1,
  estado: true,
});

const requisitoConfig = reactive({
  id: null,
  nombre: '',
  tipos_documento: [],
});

const requisitosFiltrados = computed(() => {
  if (!buscar.value) return requisitos.value;
  const term = buscar.value.toLowerCase();
  return requisitos.value.filter(r =>
    r.nombre?.toLowerCase().includes(term)
  );
});

const tiposDisponibles = computed(() => {
  const ids = new Set(requisitoConfig.tipos_documento?.map(td => td.id));
  return tiposDocumento.value.filter(td => !ids.has(td.value));
});

const showModal = () => {
  limpiarRequisito();
  visible.value = true;
};

const abrirEditar = (record) => {
  Object.assign(requisito, {
    id: record.id,
    nombre: record.nombre,
    modalidades: record.modalidades?.map(m => m.id) || [],
    programas: record.programas?.map(p => p.id) || [],
    obligatorio: record.obligatorio,
    orden: record.orden,
    estado: record.estado,
  });
  visible.value = true;
};

const abrirConfigTipos = (record) => {
  Object.assign(requisitoConfig, {
    id: record.id,
    nombre: record.nombre,
    tipos_documento: [...(record.tipos_documento || [])],
  });
  tipoSeleccionado.value = null;
  visibleTipos.value = true;
};

const confirmarEliminar = (record) => {
  eliminarRecord.value = record;
  confirmarEliminarVisible.value = true;
};

const ejecutarEliminar = async () => {
  if (!eliminarRecord.value) return;
  try {
    const result = await axios.get(`requisitos/delete/${eliminarRecord.value.id}`);
    await getRequisitos();
    notificacion('warning', 'REQUISITO ELIMINADO', result.data.mensaje);
    confirmarEliminarVisible.value = false;
    eliminarRecord.value = null;
  } catch {
    notificacion('error', 'Error', 'No se pudo eliminar');
  }
};

const agregarTipo = () => {
  if (!tipoSeleccionado.value) return;
  const tipo = tiposDocumento.value.find(td => td.value === tipoSeleccionado.value);
  if (tipo && !requisitoConfig.tipos_documento.some(td => td.id === tipo.value)) {
    requisitoConfig.tipos_documento.push({
      id: tipo.value,
      nombre: tipo.label,
      codigo: '',
    });
  }
  tipoSeleccionado.value = null;
};

const quitarTipo = (idTipo) => {
  requisitoConfig.tipos_documento = requisitoConfig.tipos_documento.filter(td => td.id !== idTipo);
};

const getRequisitos = async () => {
  try {
    const res = await axios.post('requisitos/get-all');
    requisitos.value = res.data.datos;
  } catch (error) {
    console.error('Error al obtener requisitos:', error);
  }
};

const getModalidades = async () => {
  try {
    const res = await axios.post('requisitos/get-modalidades');
    modalidades.value = res.data.datos;
  } catch (error) {
    console.error('Error al obtener modalidades:', error);
  }
};

const getProgramas = async () => {
  try {
    const res = await axios.post('requisitos/get-programas');
    programas.value = res.data.datos;
  } catch (error) {
    console.error('Error al obtener programas:', error);
  }
};

const getTiposDocumento = async () => {
  try {
    const res = await axios.get('requisitos/get-tipos-documento');
    tiposDocumento.value = res.data.datos;
  } catch (error) {
    console.error('Error al obtener tipos de documento:', error);
  }
};

const guardar = async () => {
  if (!requisito.nombre) {
    notificacion('error', 'Error', 'Ingrese el nombre del requisito');
    return;
  }
  if (!requisito.modalidades.length) {
    notificacion('error', 'Error', 'Seleccione al menos una modalidad');
    return;
  }
  if (!requisito.orden) {
    notificacion('error', 'Error', 'Ingrese el orden del requisito');
    return;
  }

  guardando.value = true;
  try {
    const response = await axios.post('requisitos/save', requisito);
    notificacion('success', response.data.titulo, response.data.mensaje);
    await getRequisitos();
    visible.value = false;
  } catch (error) {
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      Object.keys(errors).forEach(key => {
        notificacion('error', 'Error', errors[key][0]);
      });
    }
  } finally {
    guardando.value = false;
  }
};

const guardarTiposDocumento = async () => {
  guardandoTipos.value = true;
  try {
    const response = await axios.post('requisitos/save-tipos-documento', {
      id: requisitoConfig.id,
      tipos_documento: requisitoConfig.tipos_documento.map(td => td.id),
    });
    notificacion('success', response.data.titulo, response.data.mensaje);
    await getRequisitos();
    visibleTipos.value = false;
  } catch (error) {
    console.error('Error al guardar tipos:', error);
  } finally {
    guardandoTipos.value = false;
  }
};

const cancelar = () => {
  visible.value = false;
};

const limpiarRequisito = () => {
  requisito.id = null;
  requisito.nombre = '';
  requisito.modalidades = [];
  requisito.programas = [];
  requisito.obligatorio = true;
  requisito.orden = 1;
  requisito.estado = true;
};

const notificacion = (type, titulo, mensaje) => {
  notification[type]({ message: titulo, description: mensaje });
};

watch(visible, v => {
  if (!v) limpiarRequisito();
});

onMounted(() => {
  getRequisitos();
  getModalidades();
  getProgramas();
  getTiposDocumento();
});
</script>