<template>
<Head title="Gestión de Puntajes"/>
<Layout>
  <div class="puntajes-container">
    <!-- Header -->
    <div class="puntajes-header">
      <div class="puntajes-header-content">
        <div class="puntajes-header-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 3v18h18"/>
            <path d="M18 17V9M13 17V5M8 17v-3"/>
          </svg>
        </div>
        <div>
          <h1 class="puntajes-title">Gestión de Puntajes</h1>
          <p class="puntajes-subtitle">Administre los puntajes del proceso de admisión</p>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="puntajes-filters">
      <a-row :gutter="[12, 12]">
        <a-col :xs="24" :sm="12" :md="5">
          <label class="puntajes-label">Proceso</label>
          <a-select
            v-model:value="filtros.id_proceso"
            style="width: 100%"
            placeholder="Seleccionar proceso"
            size="large"
            @change="() => loadResultados(1)"
            :options="procesos.map(p => ({ value: p.id, label: p.nombre }))"
          />
        </a-col>
        <a-col :xs="24" :sm="12" :md="5">
          <label class="puntajes-label">Programa</label>
          <a-select
            v-model:value="filtros.programa"
            style="width: 100%"
            placeholder="Todos los programas"
            size="large"
            allow-clear
            @change="() => loadResultados(1)"
            :options="programas.map(p => ({ value: p.nombre, label: p.nombre }))"
          />
        </a-col>
        <a-col :xs="24" :sm="12" :md="4">
          <label class="puntajes-label">Modalidad</label>
          <a-select
            v-model:value="filtros.modalidad"
            style="width: 100%"
            placeholder="Todas"
            size="large"
            allow-clear
            @change="() => loadResultados(1)"
            :options="modalidades.map(m => ({ value: m.nombre, label: m.nombre }))"
          />
        </a-col>
        <a-col :xs="24" :sm="12" :md="5">
          <label class="puntajes-label">Buscar</label>
          <a-input
            v-model:value="filtros.search"
            placeholder="DNI, nombres o apellidos"
            size="large"
            allow-clear
            @pressEnter="() => loadResultados(1)"
          >
            <template #prefix>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:#bbb">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
            </template>
          </a-input>
        </a-col>
        <a-col :xs="24" :sm="24" :md="5">
          <div class="puntajes-actions">
            <a-button size="large" @click="() => loadResultados(1)" class="puntajes-btn-search">
              <template #icon><SearchOutlined/></template>
              Buscar
            </a-button>
            <a-button size="large" type="primary" @click="abrirModalCrear" class="puntajes-btn-new">
              <template #icon><PlusOutlined/></template>
              Nuevo
            </a-button>
          </div>
        </a-col>
      </a-row>

      <div class="puntajes-actions-row2">
        <a-button size="large" @click="descargarPlantilla" class="puntajes-btn-template">
          <template #icon><DownloadOutlined/></template>
          Plantilla Excel
        </a-button>
        <a-upload :show-upload-list="false" :before-upload="handleUpload" accept=".xlsx,.xls,.csv">
          <a-button size="large" :loading="importing" class="puntajes-btn-import">
            <template #icon><UploadOutlined/></template>
            {{ importing ? 'Importando...' : 'Cargar Excel' }}
          </a-button>
        </a-upload>
        <a-popconfirm
          title="¿Eliminar TODOS los puntajes del proceso seleccionado?"
          ok-text="Sí, eliminar todo"
          cancel-text="No"
          @confirm="eliminarTodo"
          :disabled="!filtros.id_proceso"
        >
          <a-button size="large" danger :disabled="!filtros.id_proceso">
            <template #icon><DeleteOutlined/></template>
            Eliminar Todo
          </a-button>
        </a-popconfirm>
      </div>
    </div>

    <!-- Tabla -->
    <div class="puntajes-table-card">
      <a-table
        :columns="columns"
        :data-source="resultados.data || []"
        :loading="loading"
        :pagination="{
          current: resultados.current_page,
          pageSize: resultados.per_page,
          total: resultados.total,
          showSizeChanger: false,
          onChange: (page) => loadResultados(page)
        }"
        size="middle"
        :scroll="{ x: 'max-content' }"
        row-key="id"
        bordered
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'nombre_completo'">
            <span style="font-weight: 600;">{{ nombreCompleto(record) }}</span>
          </template>
          <template v-if="column.dataIndex === 'apto'">
            <a-tag :color="record.apto === 'SI' ? 'green' : 'orange'" style="border-radius: 6px;">
              {{ record.apto || '—' }}
            </a-tag>
          </template>
          <template v-if="column.dataIndex === 'puntaje'">
            <span style="font-weight: 700; color: #096dd9;">{{ record.puntaje ?? '—' }}</span>
          </template>
          <template v-if="column.dataIndex === 'puntaje_vocacional'">
            <span style="font-weight: 600; color: #722ed1;">{{ record.puntaje_vocacional ?? '—' }}</span>
          </template>
          <template v-if="column.dataIndex === 'puesto'">
            <span style="font-weight: 600;">{{ record.puesto ?? '—' }}</span>
          </template>
          <template v-if="column.dataIndex === 'acciones'">
            <div class="puntajes-table-actions">
              <a-button type="text" size="small" @click="abrirModalEditar(record)" style="color: #096dd9;">
                <template #icon><EditOutlined/></template>
              </a-button>
              <a-popconfirm
                title="¿Eliminar este registro?"
                ok-text="Sí"
                cancel-text="No"
                @confirm="eliminarRegistro(record.id)"
              >
                <a-button type="text" danger size="small">
                  <template #icon><DeleteOutlined/></template>
                </a-button>
              </a-popconfirm>
            </div>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Modal Crear/Editar -->
    <a-modal
      v-model:open="modalForm"
      :title="formMode === 'crear' ? 'Nuevo Registro' : 'Editar Registro'"
      :closable="true"
      :mask-closable="false"
      centered
      width="720px"
      :footer="false"
    >
      <a-form ref="formRef" :model="form" layout="vertical">
        <a-row :gutter="[16, 0]">
          <a-col :span="8">
            <a-form-item label="DNI" name="dni" :rules="[{ required: true, message: 'El DNI es obligatorio' }]">
              <a-input v-model:value="form.dni" :maxlength="8" />
            </a-form-item>
          </a-col>
          <a-col :span="8">
            <a-form-item label="Paterno" name="paterno">
              <a-input v-model:value="form.paterno" />
            </a-form-item>
          </a-col>
          <a-col :span="8">
            <a-form-item label="Materno" name="materno">
              <a-input v-model:value="form.materno" />
            </a-form-item>
          </a-col>
          <a-col :span="16">
            <a-form-item label="Nombres" name="nombres">
              <a-input v-model:value="form.nombres" />
            </a-form-item>
          </a-col>
          <a-col :span="8">
            <a-form-item label="Fecha" name="fecha">
              <a-date-picker v-model:value="form.fecha" style="width: 100%" format="DD/MM/YYYY" />
            </a-form-item>
          </a-col>
          <a-col :span="8">
            <a-form-item label="Programa" name="programa">
              <a-input v-model:value="form.programa" />
            </a-form-item>
          </a-col>
          <a-col :span="8">
            <a-form-item label="Modalidad" name="modalidad">
              <a-input v-model:value="form.modalidad" />
            </a-form-item>
          </a-col>
          <a-col :span="8">
            <a-form-item label="Área" name="area">
              <a-select v-model:value="form.area" allow-clear placeholder="Seleccionar"
                :options="[{value:'BIOMEDICAS',label:'BIOMEDICAS'},{value:'SOCIALES',label:'SOCIALES'},{value:'INGENIERIAS',label:'INGENIERIAS'}]" />
            </a-form-item>
          </a-col>
          <a-col :span="6">
            <a-form-item label="Puntaje" name="puntaje">
              <a-input-number v-model:value="form.puntaje" style="width: 100%" :precision="3" />
            </a-form-item>
          </a-col>
          <a-col :span="6">
            <a-form-item label="P. Vocacional" name="puntaje_vocacional">
              <a-input-number v-model:value="form.puntaje_vocacional" style="width: 100%" :precision="3" />
            </a-form-item>
          </a-col>
          <a-col :span="6">
            <a-form-item label="Apto" name="apto">
              <a-select v-model:value="form.apto" :options="[{value:'SI',label:'SI'},{value:'NO',label:'NO'}]" />
            </a-form-item>
          </a-col>
          <a-col :span="6">
            <a-form-item label="Puesto" name="puesto">
              <a-input-number v-model:value="form.puesto" style="width: 100%" :min="1" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="ID Inscripción" name="id_inscripcion">
              <a-input-number v-model:value="form.id_inscripcion" style="width: 100%" />
            </a-form-item>
          </a-col>
        </a-row>

        <div class="puntajes-modal-footer">
          <a-button @click="modalForm = false" size="large">Cancelar</a-button>
          <a-button type="primary" @click="guardar" :loading="saving" size="large">
            {{ formMode === 'crear' ? 'Crear' : 'Guardar' }}
          </a-button>
        </div>
      </a-form>
    </a-modal>

    <!-- Modal Import Result -->
    <a-modal v-model:open="modalResultado" :closable="true" :footer="false" centered title="Resultado de Importación" width="480px">
      <div class="puntajes-modal-result">
        <div class="puntajes-modal-icon" :class="importResultado.estado ? 'success' : 'error'">
          <CheckCircleOutlined v-if="importResultado.estado"/>
          <CloseCircleOutlined v-else/>
        </div>
        <p class="puntajes-modal-text">{{ importResultado.mensaje }}</p>
        <a-button type="primary" block @click="modalResultado = false">Aceptar</a-button>
      </div>
    </a-modal>

  </div>
</Layout>
</template>

<script setup>
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { ref, reactive, onMounted } from 'vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';
import dayjs from 'dayjs';
import {
  SearchOutlined, DownloadOutlined, UploadOutlined, DeleteOutlined,
  PlusOutlined, EditOutlined, CheckCircleOutlined, CloseCircleOutlined
} from '@ant-design/icons-vue';

const loading = ref(false);
const importing = ref(false);
const saving = ref(false);
const modalResultado = ref(false);
const modalForm = ref(false);
const formMode = ref('crear');
const formRef = ref();

const importResultado = reactive({ estado: false, mensaje: '' });

const procesos = ref([]);
const programas = ref([]);
const modalidades = ref([]);

const filtros = reactive({
  id_proceso: null,
  programa: null,
  modalidad: null,
  search: '',
});

const resultados = ref({ data: [], total: 0, current_page: 1, per_page: 50, last_page: 1 });

const formDefault = () => ({
  id: null,
  dni: '',
  id_proceso: null,
  fecha: null,
  paterno: '',
  materno: '',
  nombres: '',
  puntaje: null,
  puntaje_vocacional: null,
  apto: null,
  programa: '',
  area: null,
  modalidad: '',
  id_inscripcion: null,
  puesto: null,
});

const form = reactive(formDefault());

const columns = [
  { title: 'DNI', dataIndex: 'dni', width: 90, align: 'center' },
  { title: 'Nombre Completo', dataIndex: 'nombre_completo', width: 220 },
  { title: 'Programa', dataIndex: 'programa', width: 150 },
  { title: 'Modalidad', dataIndex: 'modalidad', width: 110 },
  { title: 'Área', dataIndex: 'area', width: 110 },
  { title: 'Puntaje', dataIndex: 'puntaje', width: 90, align: 'center' },
  { title: 'P. Vocacional', dataIndex: 'puntaje_vocacional', width: 100, align: 'center' },
  { title: 'Apto', dataIndex: 'apto', width: 70, align: 'center' },
  { title: 'Puesto', dataIndex: 'puesto', width: 70, align: 'center' },
  { title: 'Fecha', dataIndex: 'fecha', width: 100, align: 'center' },
  { title: 'Acciones', dataIndex: 'acciones', width: 90, align: 'center', fixed: 'right' },
];

const nombreCompleto = (r) => {
  return [r.paterno, r.materno, r.nombres].filter(Boolean).join(' ');
};

const loadSelectores = async () => {
  try {
    const res = await axios.get('/admin/puntajes/selectores');
    procesos.value = res.data.procesos || [];
    programas.value = res.data.programas || [];
    modalidades.value = res.data.modalidades || [];
  } catch (e) {
    console.error('Error cargando selectores:', e);
  }
};

const loadResultados = async (page = 1) => {
  loading.value = true;
  try {
    const res = await axios.get('/admin/puntajes/lista', {
      params: {
        id_proceso: filtros.id_proceso,
        programa: filtros.programa,
        modalidad: filtros.modalidad,
        search: filtros.search,
        page,
      }
    });
    resultados.value = res.data.datos || { data: [], total: 0, current_page: 1, per_page: 50, last_page: 1 };
  } catch (e) {
    console.error('Error cargando resultados:', e);
    notification.error({ message: 'Error', description: 'No se pudieron cargar los resultados' });
  } finally {
    loading.value = false;
  }
};

const descargarPlantilla = () => {
  window.open('/admin/puntajes/plantilla', '_blank');
};

const handleUpload = async (file) => {
  if (!filtros.id_proceso) {
    notification.warning({ message: 'Seleccione un proceso primero' });
    return false;
  }

  importing.value = true;
  const formData = new FormData();
  formData.append('file', file);
  formData.append('id_proceso', filtros.id_proceso);

  try {
    const res = await axios.post('/admin/puntajes/importar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      timeout: 120000,
    });
    importResultado.estado = res.data.estado;
    importResultado.mensaje = res.data.mensaje;
    modalResultado.value = true;
    loadResultados();
  } catch (e) {
    importResultado.estado = false;
    importResultado.mensaje = e.response?.data?.mensaje || 'Error al importar el archivo';
    modalResultado.value = true;
  } finally {
    importing.value = false;
  }
  return false;
};

const eliminarRegistro = async (id) => {
  try {
    const res = await axios.post('/admin/puntajes/eliminar-registro', { id });
    notification.success({ message: res.data.mensaje });
    loadResultados(resultados.value.current_page);
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudo eliminar el registro' });
  }
};

const eliminarTodo = async () => {
  try {
    const res = await axios.post('/admin/puntajes/eliminar-todo', { id_proceso: filtros.id_proceso });
    notification.success({ message: res.data.mensaje });
    loadResultados(1);
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudieron eliminar los registros' });
  }
};

const abrirModalCrear = () => {
  Object.assign(form, formDefault());
  form.id_proceso = filtros.id_proceso;
  formMode.value = 'crear';
  modalForm.value = true;
};

const abrirModalEditar = (record) => {
  Object.assign(form, formDefault());
  form.id = record.id;
  form.dni = record.dni;
  form.id_proceso = record.id_proceso;
  form.fecha = record.fecha ? dayjs(record.fecha) : null;
  form.paterno = record.paterno;
  form.materno = record.materno;
  form.nombres = record.nombres;
  form.puntaje = record.puntaje;
  form.puntaje_vocacional = record.puntaje_vocacional;
  form.apto = record.apto;
  form.programa = record.programa;
  form.area = record.area;
  form.modalidad = record.modalidad;
  form.id_inscripcion = record.id_inscripcion;
  form.puesto = record.puesto;
  formMode.value = 'editar';
  modalForm.value = true;
};

const guardar = async () => {
  try {
    await formRef.value.validateFields();
  } catch {
    return;
  }

  saving.value = true;
  const payload = { ...form };
  if (form.fecha) {
    payload.fecha = dayjs(form.fecha).format('YYYY-MM-DD');
  }
  if (!payload.id) {
    delete payload.id;
  }

  try {
    const res = await axios.post('/admin/puntajes/guardar', payload);
    notification.success({ message: res.data.mensaje });
    modalForm.value = false;
    loadResultados(resultados.value.current_page);
  } catch (e) {
    notification.error({ message: 'Error', description: e.response?.data?.mensaje || 'No se pudo guardar el registro' });
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadSelectores();
});
</script>

<style scoped>
.puntajes-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* Header */
.puntajes-header {
  background: linear-gradient(135deg, #0a3d5a 0%, #096dd9 100%);
  border-radius: 16px;
  padding: 28px 32px;
  margin-bottom: 24px;
  box-shadow: 0 4px 20px rgba(9, 109, 217, 0.15);
}
.puntajes-header-content {
  display: flex;
  align-items: center;
  gap: 16px;
}
.puntajes-header-icon {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  padding: 12px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
}
.puntajes-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #fff;
  margin: 0;
}
.puntajes-subtitle {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
  margin: 2px 0 0 0;
}

/* Filters */
.puntajes-filters {
  background: #fff;
  border-radius: 14px;
  padding: 20px 24px;
  margin-bottom: 20px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
.puntajes-label {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #555;
  margin-bottom: 6px;
}
.puntajes-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: flex-end;
  height: 100%;
  padding-top: 22px;
}
.puntajes-actions-row2 {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #f0f0f0;
}
.puntajes-btn-search, .puntajes-btn-template {
  border-radius: 10px;
  font-weight: 600;
}
.puntajes-btn-new {
  border-radius: 10px;
  font-weight: 600;
  background: linear-gradient(135deg, #52c41a, #389e0d);
  border: none;
}
.puntajes-btn-new:hover {
  filter: brightness(1.1);
}
.puntajes-btn-import {
  border-radius: 10px;
  font-weight: 600;
  background: linear-gradient(135deg, #096dd9, #0a3d5a);
  border: none;
  color: #fff;
}
.puntajes-btn-import:hover {
  filter: brightness(1.1);
  color: #fff !important;
}

/* Table */
.puntajes-table-card {
  background: #fff;
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
.puntajes-table-actions {
  display: flex;
  gap: 4px;
  justify-content: center;
}

/* Modal Form */
.puntajes-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #f0f0f0;
}

/* Modal Result */
.puntajes-modal-result {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 20px 0;
}
.puntajes-modal-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
}
.puntajes-modal-icon.success {
  background: #f6ffed;
  color: #52c41a;
}
.puntajes-modal-icon.error {
  background: #fff2f0;
  color: #ff4d4f;
}
.puntajes-modal-text {
  font-size: 0.95rem;
  color: #555;
  text-align: center;
  margin: 0;
  line-height: 1.5;
}

/* Responsive */
@media (max-width: 768px) {
  .puntajes-actions {
    flex-direction: column;
    align-items: stretch;
  }
  .puntajes-actions button {
    width: 100%;
  }
  .puntajes-actions-row2 {
    flex-direction: column;
  }
  .puntajes-actions-row2 button {
    width: 100%;
  }
}
</style>
