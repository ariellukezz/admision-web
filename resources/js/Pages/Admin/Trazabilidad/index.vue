<template>
<Head title="Trazabilidad"/>
<AuthenticatedLayout>
<div class="overflow-hidden shadow-sm sm:rounded-lg p-4" style="height: calc(100vh - 98px); background: var(--card-bg); border: 1px solid var(--card-border); color: var(--card-text);">

  <!-- Header -->
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold text-gray-800">Trazabilidad del Sistema</h2>
    <a-button type="primary" size="small" @click="loadData">
      <template #icon><ReloadOutlined /></template> Actualizar
    </a-button>
  </div>

  <!-- Filtros -->
  <div class="flex flex-wrap gap-2 mb-4">
    <a-select v-model:value="filters.action" placeholder="Acción" allowClear style="width: 140px" size="small" @change="loadData">
      <a-select-option value="create">Crear</a-select-option>
      <a-select-option value="update">Actualizar</a-select-option>
      <a-select-option value="delete">Eliminar</a-select-option>
      <a-select-option value="view">Visualizar</a-select-option>
      <a-select-option value="download">Descargar</a-select-option>
      <a-select-option value="verify">Verificar</a-select-option>
    </a-select>

    <a-select v-model:value="filters.model_type" placeholder="Modelo" allowClear style="width: 160px" size="small" @change="loadData">
      <a-select-option value="App\Models\Documento">Documento</a-select-option>
      <a-select-option value="App\Models\Vacante">Vacante</a-select-option>
      <a-select-option value="App\Models\Inscripcion">Inscripción</a-select-option>
      <a-select-option value="App\Models\Postulante">Postulante</a-select-option>
      <a-select-option value="App\Models\User">Usuario</a-select-option>
      <a-select-option value="Route">Ruta</a-select-option>
    </a-select>

    <a-range-picker v-model:value="filters.dateRange" size="small" format="YYYY-MM-DD" @change="loadData" />

    <a-input-search v-model:value="filters.search" placeholder="Buscar..." size="small" style="width: 200px" @search="loadData" />
  </div>

  <!-- Tabla -->
  <a-table
    :columns="columns"
    :data-source="records"
    :pagination="pagination"
    :loading="loading"
    size="small"
    :scroll="{ x: 900, y: 'calc(100vh - 280px)' }"
    @change="handleTableChange"
    row-key="id"
  >
    <template #bodyCell="{ column, record }">
      <template v-if="column.dataIndex === 'created_at'">
        {{ formatDate(record.created_at) }}
      </template>

      <template v-if="column.dataIndex === 'actor'">
        <div class="flex items-center gap-1">
          <a-tag v-if="record.alias" color="purple">{{ record.alias }}</a-tag>
          <span v-else class="text-gray-700 text-xs">{{ record.actor_name }}</span>
        </div>
      </template>

      <template v-if="column.dataIndex === 'action'">
        <a-tag :color="actionColor(record.action)">{{ actionLabel(record.action) }}</a-tag>
      </template>

      <template v-if="column.dataIndex === 'model'">
        <span class="text-xs text-gray-600">{{ record.model_short }}</span>
        <span v-if="record.model_id" class="text-xs text-gray-400 ml-1">#{{ record.model_id }}</span>
      </template>

      <template v-if="column.dataIndex === 'details'">
        <a-button type="link" size="small" @click="showDetail(record)">
          <EyeOutlined />
        </a-button>
      </template>
    </template>
  </a-table>

  <!-- Modal de Detalle -->
  <a-modal v-model:open="detailVisible" title="Detalle de Auditoría" width="700px" :footer="null">
    <div v-if="detailRecord">
      <a-descriptions :column="2" size="small" bordered>
        <a-descriptions-item label="Fecha">{{ formatDate(detailRecord.created_at) }}</a-descriptions-item>
        <a-descriptions-item label="Acción">
          <a-tag :color="actionColor(detailRecord.action)">{{ actionLabel(detailRecord.action) }}</a-tag>
        </a-descriptions-item>
        <a-descriptions-item label="Actor">{{ detailRecord.actor_name }}</a-descriptions-item>
        <a-descriptions-item label="Modelo">{{ detailRecord.model_short }} #{{ detailRecord.model_id }}</a-descriptions-item>
        <a-descriptions-item label="IP" :span="2">{{ detailRecord.ip }}</a-descriptions-item>
        <a-descriptions-item label="Descripción" :span="2">{{ detailRecord.description }}</a-descriptions-item>
      </a-descriptions>

      <div v-if="detailRecord.old_values || detailRecord.new_values" class="mt-4">
        <h4 class="text-sm font-semibold mb-2">Cambios</h4>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <span class="text-xs text-red-500 font-semibold">Valor anterior</span>
            <pre class="bg-red-50 text-red-700 text-xs p-2 rounded mt-1 max-h-48 overflow-auto">{{ formatJSON(detailRecord.old_values) }}</pre>
          </div>
          <div>
            <span class="text-xs text-green-600 font-semibold">Valor nuevo</span>
            <pre class="bg-green-50 text-green-700 text-xs p-2 rounded mt-1 max-h-48 overflow-auto">{{ formatJSON(detailRecord.new_values) }}</pre>
          </div>
        </div>
      </div>
    </div>
  </a-modal>

</div>
</AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, onMounted } from 'vue';
import { ReloadOutlined, EyeOutlined } from '@ant-design/icons-vue';
import axios from 'axios';

const records = ref([]);
const loading = ref(false);
const detailVisible = ref(false);
const detailRecord = ref(null);

const filters = reactive({
  action: undefined,
  model_type: undefined,
  dateRange: undefined,
  search: '',
});

const pagination = reactive({
  current: 1,
  pageSize: 25,
  total: 0,
  showSizeChanger: false,
});

const columns = [
  { title: 'Fecha', dataIndex: 'created_at', width: '140px' },
  { title: 'Actor', dataIndex: 'actor', width: '130px' },
  { title: 'Acción', dataIndex: 'action', width: '100px', align: 'center' },
  { title: 'Modelo', dataIndex: 'model', width: '150px' },
  { title: 'Descripción', dataIndex: 'description', ellipsis: true },
  { title: 'Detalle', dataIndex: 'details', width: '70px', align: 'center' },
];

const loadData = async () => {
  loading.value = true;
  try {
    const params = {
      page: pagination.current,
      per_page: pagination.pageSize,
    };
    if (filters.action) params.action = filters.action;
    if (filters.model_type) params.model_type = filters.model_type;
    if (filters.search) params.search = filters.search;
    if (filters.dateRange && filters.dateRange[0]) params.date_from = filters.dateRange[0].format('YYYY-MM-DD');
    if (filters.dateRange && filters.dateRange[1]) params.date_to = filters.dateRange[1].format('YYYY-MM-DD');

    const res = await axios.get('/admin/trazabilidad/data', { params });
    if (res.data.estado) {
      records.value = res.data.datos.data;
      pagination.total = res.data.datos.total;
      pagination.current = res.data.datos.current_page;
    }
  } catch (e) {
    console.error('Error loading audit trail:', e);
  } finally {
    loading.value = false;
  }
};

const handleTableChange = (pag) => {
  pagination.current = pag.current;
  loadData();
};

const showDetail = (record) => {
  detailRecord.value = record;
  detailVisible.value = true;
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatJSON = (val) => {
  if (!val) return '—';
  try {
    return JSON.stringify(typeof val === 'string' ? JSON.parse(val) : val, null, 2);
  } catch {
    return val;
  }
};

const actionColor = (action) => ({
  create: 'green',
  update: 'blue',
  delete: 'red',
  view: 'cyan',
  download: 'geekblue',
  verify: 'gold',
  access: 'default',
}[action] || 'default');

const actionLabel = (action) => ({
  create: 'Crear',
  update: 'Actualizar',
  delete: 'Eliminar',
  view: 'Ver',
  download: 'Descargar',
  verify: 'Verificar',
  access: 'Acceso',
}[action] || action);

onMounted(loadData);
</script>

<style>
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
