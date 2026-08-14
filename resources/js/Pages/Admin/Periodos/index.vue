<template>
  <Head title="Periodos de Matrícula" />
  <AuthenticatedLayout>
    <div class="overflow-hidden shadow-sm sm:rounded-lg p-4" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border, #e2e8f0); color: var(--card-text, #1e293b); height: calc(100vh - 80px); display: flex; flex-direction: column;">
      <div class="flex justify-between items-center mb-4" style="flex-shrink: 0;">
        <span style="font-size: 1.3rem;">Periodos de Matrícula</span>
        <a-button type="primary" style="border-radius: 5px; background: #096dd9; border: none;" @click="abrirCrear">
          <template #icon><PlusOutlined /></template>
          Nuevo Periodo
        </a-button>
      </div>

      <div style="flex: 1; overflow-y: auto; padding-right: 6px;">
        <a-table
          :columns="columns"
          :data-source="periodos"
          :loading="loading"
          :pagination="{ pageSize: 20 }"
          row-key="id"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'activo'">
              <a-tag :color="record.activo ? 'green' : 'default'" style="border-radius: 4px;">
                {{ record.activo ? 'Activo' : 'Inactivo' }}
              </a-tag>
            </template>
            <template v-if="column.key === 'acciones'">
              <a-space size="small">
                <a-button
                  size="small"
                  :type="record.activo ? 'default' : 'primary'"
                  :loading="record.toggling"
                  style="border-radius: 5px;"
                  @click="toggleActivo(record)"
                >
                  {{ record.activo ? 'Desactivar' : 'Activar' }}
                </a-button>
                <a-button
                  size="small"
                  style="border-radius: 5px;"
                  @click="abrirProcesos(record)"
                >
                  Procesos ({{ record.total_procesos }})
                </a-button>
                <a-button
                  size="small"
                  style="border-radius: 5px;"
                  @click="abrirEditar(record)"
                >
                  Editar
                </a-button>
                <a-popconfirm
                  title="¿Eliminar este periodo?"
                  @confirm="eliminarPeriodo(record)"
                >
                  <a-button size="small" danger style="border-radius: 5px;">
                    <template #icon><DeleteOutlined /></template>
                  </a-button>
                </a-popconfirm>
              </a-space>
            </template>
          </template>
        </a-table>
      </div>
    </div>

    <!-- Modal Crear/Editar Periodo -->
    <a-modal
      v-model:open="modalPeriodo"
      :title="editandoId ? 'Editar Periodo' : 'Nuevo Periodo'"
      width="650px"
      :footer="null"
    >
      <a-form layout="vertical" style="margin-top: 12px;">
        <a-form-item label="Nombre" required>
          <a-input v-model:value="formPeriodo.nombre" placeholder="Ej: Matrícula 2026-I" />
        </a-form-item>
        <a-form-item label="Descripción">
          <a-textarea v-model:value="formPeriodo.descripcion" :rows="3" placeholder="Descripción del periodo" />
        </a-form-item>
        <div style="text-align: right;">
          <a-button @click="modalPeriodo = false" style="margin-right: 8px;">Cancelar</a-button>
          <a-button
            type="primary"
            :loading="guardando"
            style="border-radius: 5px; background: #096dd9; border: none;"
            @click="guardarPeriodo"
          >
            Guardar
          </a-button>
        </div>
      </a-form>
    </a-modal>

    <!-- Modal Gestionar Procesos -->
    <a-modal
      v-model:open="modalProcesos"
      :title="`Procesos - ${periodoActualNombre}`"
      width="900px"
      :footer="null"
    >
      <a-spin :spinning="loadingProcesos">
        <div style="margin-top: 12px;">
          <div style="display: flex; gap: 16px;">
            <!-- Disponibles -->
            <div style="flex: 1;">
              <div style="font-size: 0.85rem; color: var(--card-muted, #64748b); margin-bottom: 8px;">Disponibles ({{ disponibles.length }}):</div>
              <a-table
                :columns="procesosColumns"
                :data-source="disponibles"
                :pagination="false"
                size="small"
                :scroll="{ y: 300 }"
                row-key="id"
              >
                <template #bodyCell="{ column, record }">
                  <template v-if="column.key === 'accion'">
                    <a-button
                      size="small"
                      type="primary"
                      style="border-radius: 5px; background: #096dd9; border: none;"
                      @click="agregarProceso(record)"
                    >
                      <template #icon><PlusOutlined /></template>
                    </a-button>
                  </template>
                </template>
              </a-table>
            </div>

            <!-- Asignados -->
            <div style="flex: 1;">
              <div style="font-size: 0.85rem; color: var(--card-muted, #64748b); margin-bottom: 8px;">Asignados ({{ asignados.length }}):</div>
              <a-table
                :columns="procesosColumns"
                :data-source="asignados"
                :pagination="false"
                size="small"
                :scroll="{ y: 300 }"
                row-key="id"
              >
                <template #bodyCell="{ column, record }">
                  <template v-if="column.key === 'accion'">
                    <a-button
                      size="small"
                      danger
                      style="border-radius: 5px;"
                      @click="quitarProceso(record)"
                    >
                      <template #icon><MinusOutlined /></template>
                    </a-button>
                  </template>
                </template>
              </a-table>
            </div>
          </div>

          <div style="text-align: right; margin-top: 16px;">
            <a-button @click="modalProcesos = false">Cerrar</a-button>
          </div>
        </div>
      </a-spin>
    </a-modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { PlusOutlined, DeleteOutlined, MinusOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const loading = ref(false);
const periodos = ref([]);

const modalPeriodo = ref(false);
const editandoId = ref(null);
const guardando = ref(false);
const formPeriodo = ref({ nombre: '', descripcion: '' });

const modalProcesos = ref(false);
const loadingProcesos = ref(false);
const periodoActualId = ref(null);
const periodoActualNombre = ref('');
const disponibles = ref([]);
const asignados = ref([]);

const columns = [
  { title: 'Nombre', dataIndex: 'nombre', key: 'nombre' },
  { title: 'Descripción', dataIndex: 'descripcion', key: 'descripcion', ellipsis: true },
  { title: 'Procesos', dataIndex: 'total_procesos', key: 'total_procesos', width: 100, align: 'center' },
  { title: 'Estado', key: 'activo', width: 100 },
  { title: 'Acciones', key: 'acciones', width: 340 },
];

const procesosColumns = [
  { title: 'Nombre', dataIndex: 'nombre', key: 'nombre', ellipsis: true },
  { title: 'Año', dataIndex: 'anio', key: 'anio', width: 70 },
  { title: 'Ciclo', dataIndex: 'ciclo', key: 'ciclo', width: 60 },
  { title: '', key: 'accion', width: 50, align: 'center' },
];

const cargarPeriodos = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/admin/periodos-matricula/lista');
    if (res.data.estado) {
      periodos.value = res.data.data;
    }
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudieron cargar los periodos.' });
  } finally {
    loading.value = false;
  }
};

const abrirCrear = () => {
  editandoId.value = null;
  formPeriodo.value = { nombre: '', descripcion: '' };
  modalPeriodo.value = true;
};

const abrirEditar = (record) => {
  editandoId.value = record.id;
  formPeriodo.value = { nombre: record.nombre, descripcion: record.descripcion || '' };
  modalPeriodo.value = true;
};

const guardarPeriodo = async () => {
  if (!formPeriodo.value.nombre) {
    notification.warning({ message: 'Campo requerido', description: 'Ingrese el nombre del periodo.' });
    return;
  }
  guardando.value = true;
  try {
    const payload = { ...formPeriodo.value };
    if (editandoId.value) payload.id = editandoId.value;
    await axios.post('/admin/periodos-matricula/save', payload);
    notification.success({ message: 'Guardado', description: 'Periodo guardado correctamente.' });
    modalPeriodo.value = false;
    await cargarPeriodos();
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudo guardar el periodo.' });
  } finally {
    guardando.value = false;
  }
};

const toggleActivo = async (record) => {
  record.toggling = true;
  try {
    await axios.post(`/admin/periodos-matricula/toggle-activo/${record.id}`);
    notification.success({ message: 'Actualizado', description: record.activo ? 'Periodo desactivado.' : 'Periodo activado.' });
    await cargarPeriodos();
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudo cambiar el estado.' });
  } finally {
    record.toggling = false;
  }
};

const eliminarPeriodo = async (record) => {
  try {
    await axios.get(`/admin/periodos-matricula/delete/${record.id}`);
    notification.success({ message: 'Eliminado', description: 'Periodo eliminado correctamente.' });
    await cargarPeriodos();
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudo eliminar el periodo.' });
  }
};

const abrirProcesos = async (record) => {
  periodoActualId.value = record.id;
  periodoActualNombre.value = record.nombre;
  modalProcesos.value = true;
  loadingProcesos.value = true;
  try {
    const res = await axios.get(`/admin/periodos-matricula/procesos/${record.id}`);
    if (res.data.estado) {
      asignados.value = res.data.asignados;
      disponibles.value = res.data.disponibles;
    }
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudieron cargar los procesos.' });
  } finally {
    loadingProcesos.value = false;
  }
};

const agregarProceso = async (proceso) => {
  const nuevosAsignados = [...asignados.value, proceso];
  const nuevosDisponibles = disponibles.value.filter(p => p.id !== proceso.id);
  await sincronizarProcesos(nuevosAsignados, nuevosDisponibles);
};

const quitarProceso = async (proceso) => {
  const nuevosAsignados = asignados.value.filter(p => p.id !== proceso.id);
  const nuevosDisponibles = [...disponibles.value, proceso];
  await sincronizarProcesos(nuevosAsignados, nuevosDisponibles);
};

const sincronizarProcesos = async (nuevosAsignados, nuevosDisponibles) => {
  try {
    await axios.post('/admin/periodos-matricula/procesos/save', {
      id_periodo: periodoActualId.value,
      procesos: nuevosAsignados.map(p => p.id),
    });
    asignados.value = nuevosAsignados;
    disponibles.value = nuevosDisponibles;
    await cargarPeriodos();
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudieron guardar los cambios.' });
  }
};

cargarPeriodos();
</script>

<style scoped>
</style>

<style>
/* ====== MODO OSCURO / HÍBRIDO: TABLA ====== */
body.theme-dark .ant-table,
body.theme-hybrid .ant-table {
    background: var(--card-bg) !important;
    color: var(--card-text) !important;
}
body.theme-dark .ant-table-thead > tr > th,
body.theme-hybrid .ant-table-thead > tr > th {
    background: var(--table-header-bg) !important;
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
}
body.theme-dark .ant-table-tbody > tr > td,
body.theme-hybrid .ant-table-tbody > tr > td {
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
    background: var(--card-bg) !important;
}
body.theme-dark .ant-table-tbody > tr:hover > td,
body.theme-hybrid .ant-table-tbody > tr:hover > td {
    background: var(--hover-bg) !important;
}

/* Modal oscuro */
body.theme-dark .ant-modal-content,
body.theme-hybrid .ant-modal-content {
    background: var(--card-bg) !important;
}
body.theme-dark .ant-modal-header,
body.theme-hybrid .ant-modal-header {
    background: var(--card-bg) !important;
}
body.theme-dark .ant-modal-title,
body.theme-hybrid .ant-modal-title {
    color: var(--card-text) !important;
}
body.theme-dark .ant-modal-close-icon,
body.theme-hybrid .ant-modal-close-icon {
    color: var(--card-muted) !important;
}
</style>
