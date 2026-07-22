<template>
  <Head title="Procesos" />
  <AuthenticatedLayout>
    <div class="overflow-hidden shadow-sm sm:rounded-lg p-4" style="height: calc(100vh - 103px); background: var(--card-bg); border: 1px solid var(--card-border); color: var(--card-text);">
      <!-- Barra superior con botón y búsqueda -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
        <a-button
          type="primary"
          class="w-full md:w-auto rounded-md bg-[#476175] hover:bg-[#3a5165] transition-colors"
          @click="showModal"
        >
          Nuevo
        </a-button>

        <div class="w-full md:w-auto relative flex-1 max-w-[300px]">
          <a-input v-model:value="searchTerm" placeholder="Buscar" class="w-full pl-3">
            <template #prefix>
              <SearchOutlined class="text-gray-400" />
            </template>
          </a-input>
        </div>
      </div>

      <!-- Tabla de reglamentos -->
      <div class="mb-4">
        <a-table
          :columns="columns"
          :data-source="regulations"
          :pagination="false"
          size="small"
          :scroll="{ x: 380, y: 'calc(100vh - 240px)' }"
          class="custom-table"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'nombre'">
              <span class="text-sm">{{ record.nombre }}</span>
            </template>
            <template v-if="column.dataIndex === 'version'">
              <a-tag color="pink">V. {{ record.version }}.0</a-tag>
            </template>
            <template v-if="column.dataIndex === 'vigencia'">
              <div class="flex justify-center">
                <a-tag v-if="record.vigencia" color="blue">
                  {{ formatDateRange(record.inicio_vigencia, record.fin_vigencia) }}
                </a-tag>
                <span v-else class="text-gray-300">sin vigencia</span>
              </div>
            </template>
            <template v-if="column.dataIndex === 'estado'">
              <div class="flex justify-center">
                <a-tag :color="record.estado ? 'blue' : 'red'">
                  {{ record.estado ? 'activo' : 'inactivo' }}
                </a-tag>
              </div>
            </template>
            <template v-if="column.dataIndex === 'acciones'">
              <div class="flex gap-2">
                <a-button @click="viewDetails(record)" size="small" class="action-btn view-btn">
                  <LinkOutlined />
                </a-button>
                <a-button @click="editItem(record)" size="small" class="action-btn edit-btn">
                  <FormOutlined />
                </a-button>
                <a-button @click="deleteItem(record)" size="small" class="action-btn delete-btn">
                  <DeleteOutlined />
                </a-button>
              </div>
            </template>
          </template>
        </a-table>
      </div>

      <div class="flex justify-end">
        <a-pagination
          v-model:current="currentPage"
          simple
          :pageSize="pageSize"
          :total="totalItems"
        />
      </div>
    </div>

    <a-modal
      v-model:open="modalVisible"
      :title="reglamento.id ? 'Editar Reglamento' : 'Nuevo Reglamento'"
      width="800px"
      @cancel="resetForm"
    >
      <a-form
        ref="formRef"
        :model="reglamento"
        :rules="formRules"
        layout="vertical"
      >
        <div class="flex justify-end mb-4">
          <a-form-item label="Estado" name="estado">
            <a-switch v-model:checked="reglamento.estado" />
          </a-form-item>
        </div>

        <a-row :gutter="[16, 16]">
          <a-col :span="24">
            <a-form-item
              label="Nombre"
              name="nombre"
              :rules="[{ required: true, message: 'Este campo es obligatorio' }]"
            >
              <a-input
                v-model:value="reglamento.nombre"
                placeholder="Ingrese el nombre del reglamento"
              >
                <template #prefix><FileOutlined /></template>
              </a-input>
            </a-form-item>
          </a-col>

          <a-col :span="12">
            <a-form-item label="Inicio vigencia" name="inicio_vigencia">
              <a-date-picker
                v-model:value="reglamento.inicio_vigencia"
                format="DD/MM/YYYY"
                style="width: 100%"
                placeholder="Seleccione fecha"
              />
            </a-form-item>
          </a-col>

          <a-col :span="12">
            <a-form-item label="Fin vigencia" name="fin_vigencia">
              <a-date-picker
                v-model:value="reglamento.fin_vigencia"
                format="DD/MM/YYYY"
                style="width: 100%"
                placeholder="Seleccione fecha"
              />
            </a-form-item>
          </a-col>

          <a-col :span="24">
            <a-form-item
              label="Archivo PDF (Máx. 2MB)"
              name="file"
            >
              <a-upload
                :file-list="reglamento.file ? [reglamento.file] : []"
                :before-upload="beforeUpload"
                @remove="handleRemoveFile"
                accept=".pdf"
                :max-count="1"
              >
                <a-button>
                  <UploadOutlined />
                  Seleccionar archivo
                </a-button>
              </a-upload>
              <div v-if="pdfPreviewUrl" class="mt-4">
                <embed
                  :src="pdfPreviewUrl"
                  type="application/pdf"
                  width="100%"
                  height="300px"
                />
              </div>
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>

      <template #footer>
        <a-button @click="resetForm">Cancelar</a-button>
        <a-button type="primary" @click="submitForm">Guardar</a-button>
      </template>
    </a-modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { LinkOutlined, FormOutlined, DeleteOutlined, SearchOutlined, FileOutlined, UploadOutlined } from '@ant-design/icons-vue';
import { message, notification } from 'ant-design-vue';
import axios from 'axios';
import dayjs from 'dayjs';
import 'dayjs/locale/es';
dayjs.locale('es');

const baseUrl = window.location.origin;
const searchTerm = ref('');
const currentPage = ref(1);
const pageSize = ref(50);
const totalItems = ref(0);
const modalVisible = ref(false);
const regulations = ref([]);
const faculties = ref([]);
const pdfPreviewUrl = ref('');

const reglamento = ref({
  id: null,
  nombre: '',
  estado: true,
  inicio_vigencia: null,
  fin_vigencia: null,
  file: null
});

const formRef = ref();

const columns = [
  { title: 'Nombre', dataIndex: 'nombre', width: '40%' },
  { title: 'Versión', dataIndex: 'version', align: 'center', width: '100px' },
  { title: 'Vigencia', dataIndex: 'vigencia', align: 'center', width: '150px' },
  { title: 'Estado', dataIndex: 'estado', align: 'center', width: '100px' },
  { title: 'Acciones', dataIndex: 'acciones', align: 'center', width: '150px' },
];

const formRules = {
  nombre: [{ required: true, message: 'El nombre es obligatorio' }]
};

function debounce(fn, delay) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), delay);
  };
}

const getReglamentos = async () => {
  try {
    const response = await axios.post('get-reglamentos', {
      params: {
        search: searchTerm.value,
        page: currentPage.value,
        per_page: pageSize.value
      }
    });

    regulations.value = response.data.datos.data;
    totalItems.value = response.data.datos.total;
  } catch (error) {
    console.error('Error fetching regulations:', error);
    notification.error({
      message: 'Error',
      description: 'No se pudo cargar la lista de reglamentos'
    });
  }
};

const showModal = () => {
  modalVisible.value = true;
};

const beforeUpload = (file) => {
  const isPDF = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
  const isLt2M = file.size / 1024 / 1024 < 2;

  if (!isPDF) {
    message.error('Solo se permiten archivos PDF!');
    return false;
  }

  if (!isLt2M) {
    message.error('El archivo debe ser menor a 2MB!');
    return false;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    pdfPreviewUrl.value = e.target.result;
    reglamento.value.file = file;
  };
  reader.readAsDataURL(file);

  return false;
};

const handleRemoveFile = () => {
  pdfPreviewUrl.value = '';
  reglamento.value.file = null;
};

const editItem = (item) => {
  reglamento.value = {
    ...item,
    estado: item.estado === 1,
    id: item.id,
    inicio_vigencia: item.inicio_vigencia ? dayjs(item.inicio_vigencia) : null,
    fin_vigencia: item.fin_vigencia ? dayjs(item.fin_vigencia) : null,
  };
  pdfPreviewUrl.value = item.url;
  modalVisible.value = true;
};

const submitForm = async () => {
  try {
    await formRef.value.validate();

    const formData = new FormData();
    formData.append('nombre', reglamento.value.nombre);
    formData.append('estado', reglamento.value.estado ? 1 : 0);

    if (reglamento.value.id) {
      formData.append('id', reglamento.value.id);
    }

    if (reglamento.value.inicio_vigencia) {
      formData.append('inicio_vigencia', dayjs(reglamento.value.inicio_vigencia).format('YYYY-MM-DD'));
    }

    if (reglamento.value.fin_vigencia) {
      formData.append('fin_vigencia', dayjs(reglamento.value.fin_vigencia).format('YYYY-MM-DD'));
    }

    if (reglamento.value.file) {
      formData.append('file', reglamento.value.file);
    }

    const url = 'save-reglamento';
    const method = 'post';

    await axios({ method, url, data: formData, headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    message.success('Reglamento guardado exitosamente!');
    modalVisible.value = false;
    getReglamentos();
    resetForm();
  } catch (error) {
    console.error('Error saving regulation:', error);
    notification.error({
      message: 'Error',
      description: error.response?.data?.message || 'Ocurrió un error al guardar'
    });
  }
};

const deleteItem = async (item) => {
  try {
    await axios.get(`eliminar-reglamento/${item.id}`);
    message.success('Reglamento eliminado exitosamente!');
    getReglamentos();
  } catch (error) {
    console.error('Error deleting regulation:', error);
    notification.error({
      message: 'Error',
      description: 'No se pudo eliminar el reglamento'
    });
  }
};

const resetForm = () => {
  formRef.value?.resetFields();
  reglamento.value = {
    id: null,
    nombre: '',
    estado: true,
    inicio_vigencia: null,
    fin_vigencia: null,
    file: null
  };
  pdfPreviewUrl.value = '';
  modalVisible.value = false;
};

const formatDateRange = (start, end) => {
  if (!start || !end) return '';
  return `${dayjs(start).format('DD/MM/YY')} - ${dayjs(end).format('DD/MM/YY')}`;
};

watch(searchTerm, debounce(getReglamentos, 300));
watch(currentPage, getReglamentos);

getReglamentos();
</script>

<style scoped>
.action-btn {
  height: 28px;
  border: 1px solid #d9d9d9;
  display: flex;
  align-items: center;
  justify-content: center;
}

.view-btn {
  color: green;
  background: var(--card-bg, #ffffff);
}

.edit-btn {
  color: #1890ff;
  background: var(--card-bg, #ffffff);
}

.delete-btn {
  color: #ff4d4f;
  background: var(--card-bg, #ffffff);
}

.custom-table :deep(.ant-table) {
  font-size: 0.9rem;
}

.custom-table :deep(.ant-table-thead > tr > th) {
  background-color: var(--content-bg, #f1f5f9);
  font-weight: 500;
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
</style>