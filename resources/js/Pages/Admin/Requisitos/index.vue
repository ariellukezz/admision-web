<template>
  <Head title="Requisitos" />

  <AuthenticatedLayout>
    <div class="req-container" style="height: calc(100vh - 103px); display: flex; flex-direction: column;">

      <!-- Header -->
      <div class="req-header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 class="req-title">Requisitos</h1>
            <p class="req-subtitle">Gestión de documentos requeridos por modalidad</p>
          </div>

          <div class="flex gap-3">
            <div class="relative">
              <input
                v-model="buscar"
                type="text"
                placeholder="Buscar requisito..."
                class="req-search"
              />
              <span class="req-search-icon">🔍</span>
            </div>

            <button @click="showModal" class="req-btn-new">
              <span>+</span> Nuevo
            </button>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="flex-1 overflow-auto px-8 py-6">
        <table class="w-full req-table">
          <thead class="sticky top-0">
            <tr>
              <th class="w-20">Orden</th>
              <th>Requisito</th>
              <th>Modalidades</th>
              <th>Programas</th>
              <th>Documentos aceptados</th>
              <th class="w-28 text-center">Obligatorio</th>
              <th class="w-24 text-center">Estado</th>
              <th class="w-36 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in requisitosFiltrados" :key="item.id">
              <td class="text-center">
                <span class="req-order-badge">{{ item.orden }}</span>
              </td>
              <td>
                <span class="req-name">{{ item.nombre }}</span>
              </td>
              <td>
                <div class="flex flex-wrap gap-1">
                  <span v-for="m in item.modalidades" :key="m.id" class="req-tag req-tag-cyan">
                    {{ m.nombre }}
                  </span>
                </div>
              </td>
              <td>
                <div class="flex flex-wrap gap-1">
                  <span v-for="p in item.programas" :key="p.id" class="req-tag req-tag-purple">
                    {{ p.nombre_corto || p.nombre }}
                  </span>
                  <span v-if="!item.programas?.length" class="req-muted">Todos</span>
                </div>
              </td>
              <td>
                <div class="flex flex-wrap gap-1">
                  <span v-for="td in item.tipos_documento" :key="td.id" class="req-tag req-tag-blue">
                    {{ td.nombre }}
                  </span>
                  <span v-if="!item.tipos_documento?.length" class="req-muted">Sin configurar</span>
                </div>
              </td>
              <td class="text-center">
                <span :class="item.obligatorio ? 'req-tag-red' : 'req-tag-gray'">
                  {{ item.obligatorio ? 'Obligatorio' : 'Opcional' }}
                </span>
              </td>
              <td class="text-center">
                <span :class="item.estado ? 'req-tag-green' : 'req-tag-gray'">
                  {{ item.estado ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td>
                <div class="flex items-center justify-center gap-2">
                  <button @click="abrirEditar(item)" class="req-action-btn" title="Editar">✏️</button>
                  <button @click="abrirConfigTipos(item)" class="req-action-btn" title="Configurar documentos">⚙️</button>
                  <button @click="confirmarEliminar(item)" class="req-action-btn req-action-danger" title="Eliminar">🗑️</button>
                </div>
              </td>
            </tr>
            <tr v-if="!requisitosFiltrados.length">
              <td colspan="8" class="req-empty">
                No hay requisitos registrados
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Crear/Editar Requisito -->
    <div v-if="visible" class="req-modal-overlay" @click.self="visible = false">
      <div class="req-modal">
        <div class="req-modal-header">
          <h3 class="req-modal-title">{{ requisito.id ? 'Editar requisito' : 'Nuevo requisito' }}</h3>
          <p class="req-modal-subtitle">{{ requisito.id ? 'Modifique la información del requisito' : 'Complete los campos para crear un nuevo requisito' }}</p>
        </div>

        <div class="p-6">
          <div class="space-y-5">
            <div>
              <label class="req-label">Nombre del requisito</label>
              <input
                v-model="requisito.nombre"
                type="text"
                placeholder="Ej: Certificado de estudios"
                class="req-input"
              />
            </div>

            <div>
              <label class="req-label">Modalidades</label>
              <div class="req-checkbox-list">
                <label v-for="mod in modalidades" :key="mod.value" class="req-checkbox-item">
                  <input type="checkbox" :value="mod.value" v-model="requisito.modalidades" class="rounded">
                  <span>{{ mod.label }}</span>
                </label>
              </div>
            </div>

            <div>
              <label class="req-label">Programas específicos <span class="req-label-optional">(opcional, dejar vacío = aplica a todos)</span></label>
              <div class="req-checkbox-list">
                <label v-for="prog in programas" :key="prog.value" class="req-checkbox-item">
                  <input type="checkbox" :value="prog.value" v-model="requisito.programas" class="rounded">
                  <span>{{ prog.label }}</span>
                </label>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="req-label">Orden de prioridad</label>
                <input
                  v-model.number="requisito.orden"
                  type="number"
                  min="1"
                  class="req-input"
                />
              </div>

              <div class="space-y-3 pt-6">
                <label class="flex items-center justify-between cursor-pointer">
                  <span class="req-toggle-label">Documento obligatorio</span>
                  <div class="relative inline-block w-10 mr-2 align-middle select-none">
                    <input type="checkbox" v-model="requisito.obligatorio" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-2 appearance-none cursor-pointer"/>
                    <label class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 cursor-pointer"></label>
                  </div>
                </label>

                <label v-if="requisito.id" class="flex items-center justify-between cursor-pointer">
                  <span class="req-toggle-label">Estado activo</span>
                  <div class="relative inline-block w-10 mr-2 align-middle select-none">
                    <input type="checkbox" v-model="requisito.estado" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-2 appearance-none cursor-pointer"/>
                    <label class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 cursor-pointer"></label>
                  </div>
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="req-modal-footer">
          <button @click="cancelar" class="req-btn-cancel">Cancelar</button>
          <button @click="guardar" class="req-btn-save" :disabled="guardando">
            {{ guardando ? 'Guardando...' : (requisito.id ? 'Actualizar' : 'Crear requisito') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Configurar Tipos de Documento -->
    <div v-if="visibleTipos" class="req-modal-overlay" @click.self="visibleTipos = false">
      <div class="req-modal" style="max-width: 500px;">
        <div class="req-modal-header">
          <h3 class="req-modal-title">Documentos aceptados</h3>
          <p class="req-modal-subtitle">{{ requisitoConfig.nombre }}</p>
        </div>

        <div class="p-6">
          <div class="mb-6">
            <label class="req-label">Agregar tipo de documento</label>
            <select
              v-model="tipoSeleccionado"
              @change="agregarTipo"
              class="req-input"
            >
              <option :value="null">Seleccionar tipo</option>
              <option v-for="td in tiposDisponibles" :key="td.value" :value="td.value">
                {{ td.label }}
              </option>
            </select>
          </div>

          <div>
            <div class="flex justify-between items-center mb-3">
              <span class="req-section-label">Configurados ({{ requisitoConfig.tipos_documento?.length || 0 }})</span>
            </div>

            <div v-if="requisitoConfig.tipos_documento?.length" class="space-y-2 max-h-64 overflow-y-auto">
              <div v-for="td in requisitoConfig.tipos_documento" :key="td.id" class="req-config-item">
                <span class="req-config-name">{{ td.nombre }}</span>
                <button @click="quitarTipo(td.id)" class="req-action-btn req-action-danger">🗑️</button>
              </div>
            </div>

            <div v-else class="req-empty-box">
              <p class="req-muted">No hay documentos configurados</p>
            </div>
          </div>
        </div>

        <div class="req-modal-footer">
          <button @click="visibleTipos = false" class="req-btn-cancel">Cancelar</button>
          <button @click="guardarTiposDocumento" class="req-btn-save" :disabled="guardandoTipos">
            {{ guardandoTipos ? 'Guardando...' : 'Guardar configuración' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Confirmación Eliminar -->
    <div v-if="confirmarEliminarVisible" class="req-modal-overlay" @click.self="confirmarEliminarVisible = false">
      <div class="req-modal" style="max-width: 400px;">
        <div class="req-modal-header">
          <h3 class="req-modal-title">Confirmar eliminación</h3>
        </div>
        <div class="p-6">
          <p class="req-modal-text">¿Estás seguro de eliminar "{{ eliminarRecord?.nombre }}"? Esta acción no se puede deshacer.</p>
        </div>
        <div class="req-modal-footer">
          <button @click="confirmarEliminarVisible = false" class="req-btn-cancel">Cancelar</button>
          <button @click="ejecutarEliminar" class="req-btn-danger">Eliminar</button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.req-container {
  background: var(--card-bg, #ffffff);
  border: 1px solid var(--card-border, #e2e8f0);
  color: var(--card-text, #1e293b);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(0,0,0,0.06);
}
.req-header {
  border-bottom: 1px solid var(--card-border, #e2e8f0);
  padding: 24px 32px;
  background: var(--card-bg, #ffffff);
}
.req-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--card-text, #1e293b);
  letter-spacing: -0.02em;
}
.req-subtitle {
  font-size: 0.85rem;
  color: var(--card-muted, #64748b);
  margin-top: 4px;
}
.req-search {
  padding-left: 40px;
  padding-right: 16px;
  padding-top: 8px;
  padding-bottom: 8px;
  border: 1px solid var(--card-border, #e2e8f0);
  border-radius: 8px;
  font-size: 0.875rem;
  width: 256px;
  background: var(--card-bg, #ffffff);
  color: var(--card-text, #1e293b);
  outline: none;
  transition: all 0.2s;
}
.req-search::placeholder { color: var(--card-muted, #94a3b8); }
.req-search:focus {
  border-color: var(--primary-color, #3b82f6);
  box-shadow: 0 0 0 2px rgba(59,130,246,0.1);
}
.req-search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  opacity: 0.5;
}
.req-btn-new {
  padding: 8px 20px;
  background: var(--primary-color, #2563eb);
  color: #ffffff;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 8px;
  border: none;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
}
.req-btn-new:hover { opacity: 0.9; }

/* Table */
.req-table {
  border-collapse: collapse;
}
.req-table thead tr th {
  text-align: left;
  padding: 12px 16px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--card-muted, #64748b);
  border-bottom: 1px solid var(--card-border, #e2e8f0);
  background: var(--table-header-bg, #f8fafc);
}
.req-table tbody tr {
  border-bottom: 1px solid var(--card-border, #e2e8f0);
  transition: background 0.15s;
}
.req-table tbody tr:hover {
  background: var(--hover-bg, rgba(0,0,0,0.02));
}
.req-table tbody tr td {
  padding: 12px 16px;
  font-size: 0.875rem;
  color: var(--card-text, #1e293b);
}
.req-order-badge {
  display: inline-block;
  width: 28px;
  height: 28px;
  line-height: 28px;
  text-align: center;
  background: var(--icon-bg, #f1f5f9);
  border-radius: 6px;
  color: var(--card-muted, #64748b);
  font-size: 0.75rem;
  font-weight: 500;
}
.req-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--card-text, #1e293b);
}
.req-muted {
  font-size: 0.75rem;
  color: var(--card-muted, #94a3b8);
}
.req-tag {
  padding: 2px 8px;
  font-size: 0.75rem;
  border-radius: 6px;
  font-weight: 500;
  display: inline-block;
  margin: 2px;
}
.req-tag-cyan { background: rgba(6,182,212,0.1); color: #06b6d4; }
.req-tag-purple { background: rgba(139,92,246,0.1); color: #8b5cf6; }
.req-tag-blue { background: rgba(59,130,246,0.1); color: #3b82f6; }
.req-tag-red { background: rgba(239,68,68,0.1); color: #ef4444; padding: 4px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 500; }
.req-tag-gray { background: var(--icon-bg, rgba(0,0,0,0.05)); color: var(--card-muted, #64748b); padding: 4px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 500; }
.req-tag-green { background: rgba(16,185,129,0.1); color: #10b981; padding: 4px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 500; }

.req-action-btn {
  padding: 6px;
  border-radius: 6px;
  border: none;
  background: transparent;
  cursor: pointer;
  transition: all 0.15s;
  font-size: 0.875rem;
}
.req-action-btn:hover { background: var(--hover-bg, rgba(0,0,0,0.05)); }
.req-action-danger:hover { background: rgba(239,68,68,0.1); }

.req-empty {
  padding: 48px 16px;
  text-align: center;
  color: var(--card-muted, #94a3b8);
  font-size: 0.875rem;
}

/* Modal */
.req-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}
.req-modal {
  border-radius: 16px;
  box-shadow: 0 25px 50px rgba(0,0,0,0.25);
  width: 100%;
  max-width: 640px;
  margin: 0 16px;
  overflow: hidden;
  background: var(--card-bg, #ffffff);
  color: var(--card-text, #1e293b);
}
.req-modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid var(--card-border, #e2e8f0);
}
.req-modal-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--card-text, #1e293b);
}
.req-modal-subtitle {
  font-size: 0.85rem;
  color: var(--card-muted, #64748b);
  margin-top: 4px;
}
.req-modal-text {
  font-size: 0.875rem;
  color: var(--card-muted, #64748b);
}
.req-modal-footer {
  padding: 16px 24px;
  background: var(--table-header-bg, #f8fafc);
  border-top: 1px solid var(--card-border, #e2e8f0);
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}
.req-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--card-text, #1e293b);
  margin-bottom: 6px;
}
.req-label-optional {
  font-size: 0.75rem;
  font-weight: 400;
  color: var(--card-muted, #94a3b8);
}
.req-input {
  width: 100%;
  padding: 8px 16px;
  border: 1px solid var(--card-border, #e2e8f0);
  border-radius: 8px;
  font-size: 0.875rem;
  background: var(--card-bg, #ffffff);
  color: var(--card-text, #1e293b);
  outline: none;
  transition: all 0.2s;
}
.req-input::placeholder { color: var(--card-muted, #94a3b8); }
.req-input:focus {
  border-color: var(--primary-color, #3b82f6);
  box-shadow: 0 0 0 2px rgba(59,130,246,0.1);
}
.req-checkbox-list {
  border: 1px solid var(--card-border, #e2e8f0);
  border-radius: 8px;
  padding: 8px;
  max-height: 128px;
  overflow-y: auto;
}
.req-checkbox-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 8px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.15s;
  font-size: 0.875rem;
  color: var(--card-text, #1e293b);
}
.req-checkbox-item:hover { background: var(--hover-bg, rgba(0,0,0,0.03)); }
.req-toggle-label {
  font-size: 0.875rem;
  color: var(--card-text, #1e293b);
}
.req-section-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--card-muted, #64748b);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.req-config-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid var(--card-border, #e2e8f0);
  background: var(--table-header-bg, #f8fafc);
}
.req-config-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--card-text, #1e293b);
}
.req-empty-box {
  text-align: center;
  padding: 32px;
  border-radius: 8px;
  border: 1px solid var(--card-border, #e2e8f0);
  background: var(--table-header-bg, #f8fafc);
}
.req-btn-cancel {
  padding: 8px 16px;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--card-muted, #64748b);
  border-radius: 8px;
  border: none;
  background: transparent;
  cursor: pointer;
  transition: all 0.15s;
}
.req-btn-cancel:hover { background: var(--hover-bg, rgba(0,0,0,0.05)); }
.req-btn-save {
  padding: 8px 16px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #ffffff;
  background: var(--primary-color, #2563eb);
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.15s;
}
.req-btn-save:hover { opacity: 0.9; }
.req-btn-danger {
  padding: 8px 16px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #ffffff;
  background: #ef4444;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.15s;
}
.req-btn-danger:hover { opacity: 0.9; }

/* Toggle */
.toggle-checkbox:checked {
  right: 0;
  border-color: var(--primary-color, #1f2937);
}
.toggle-checkbox:checked + .toggle-label {
  background-color: var(--primary-color, #1f2937);
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
/* Dark/hybrid overrides for req page */
.theme-dark .req-table tbody tr:hover td,
.theme-hybrid .req-table tbody tr:hover td {
  background: transparent !important;
}
.theme-dark .req-table tbody tr:hover,
.theme-hybrid .req-table tbody tr:hover {
  background: var(--hover-bg) !important;
}
.theme-hybrid .req-table tbody tr:hover {
  background: rgba(0, 0, 0, 0.04) !important;
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
