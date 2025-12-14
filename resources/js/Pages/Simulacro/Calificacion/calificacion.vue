<template>
  <Head title="Calificación" />
  <Layout>
    <div class="container">
      <div class="content-wrapper">
        <!-- Breadcrumb Navigation -->
        <a-breadcrumb class="mb-4">
          <a-breadcrumb-item>
            <HomeOutlined />
            <span class="breadcrumb-text">Simulacros</span>
          </a-breadcrumb-item>
          <a-breadcrumb-item v-for="(item, index) in breadcrumb.slice(1)" :key="item.id">
            <a @click="entrarCarpeta(item)" class="breadcrumb-link">
              {{ item.nombre }}
            </a>
          </a-breadcrumb-item>
        </a-breadcrumb>

        <!-- Main Content Area -->
        <div @click="clicIzquierdo" @contextmenu.prevent="handleContextMenu">
          
          <!-- Lista de Simulacros -->
          <div v-if="breadcrumb.length === 1">
            <!-- Header with Search and Actions -->
            <div class="page-header">
              <div class="header-left">
                <h1 class="page-title">Simulacros</h1>
              </div>
              <div class="header-right">
                <a-input-search
                  v-model:value="buscar"
                  placeholder="Buscar simulacro..."
                  style="width: 300px"
                  @search="getSimulacros"
                >
                  <template #prefix>
                    <SearchOutlined />
                  </template>
                </a-input-search>
                <a-button 
                  type="primary" 
                  class="ml-2"
                  @click="modalNuevo = true"
                >
                  <template #icon>
                    <plus-outlined />
                  </template>
                  Nuevo Proceso
                </a-button>
              </div>
            </div>

            <!-- Simulacros Table -->

              <a-table
                :columns="columnsSimulacros"
                :data-source="simulacros"
                :pagination="pagination"
                :loading="loading"
                @change="handleTableChange"
                size="middle"
              >
                <template #bodyCell="{ column, record, index }">
                  <!-- Número -->
                  <template v-if="column.dataIndex === 'nro'">
                    <span class="row-number">{{ (pagination.current - 1) * pagination.pageSize + index + 1 }}</span>
                  </template>

                  <!-- Nombre con doble click -->
                  <template v-if="column.dataIndex === 'nombre'">
                    <div class="nombre-cell" @dblclick="customRow(record)">
                      <FolderOutlined class="folder-icon" />
                      <span class="nombre-text">{{ record.nombre }}</span>
                    </div>
                  </template>

                  <!-- Fecha -->
                  <template v-if="column.dataIndex === 'fecha'">
                    <span class="date-text">{{ formatDate(record.fecha) }}</span>
                  </template>

                  <!-- Estado -->
                  <template v-if="column.dataIndex === 'estado'">
                    <a-badge
                      :status="record.estado === 1 ? 'processing' : 'error'"
                      :text="record.estado === 1 ? 'Vigente' : 'No Vigente'"
                    />
                  </template>

                  <!-- Ubigeo -->
                  <template v-if="column.dataIndex === 'ubigeo'">
                    <span class="ubigeo-text">{{ record.ubigeo_nombre || 'N/A' }}</span>
                  </template>

                  <!-- Acciones -->
                  <template v-if="column.dataIndex === 'acciones'">
                    <a-space size="small">
                      <a-tooltip title="Ver ficha">
                        <a-button 
                          type="text" 
                          size="small"
                          @click="verFicha(record)"
                        >
                          <template #icon>
                            <EyeOutlined class="action-icon" />
                          </template>
                        </a-button>
                      </a-tooltip>
                      
                      <a-tooltip title="Editar">
                        <a-button 
                          type="text" 
                          size="small"
                          @click="abrirEditar(record)"
                        >
                          <template #icon>
                            <FormOutlined class="action-icon edit-icon" />
                          </template>
                        </a-button>
                      </a-tooltip>
                      
                      <a-tooltip title="Eliminar">
                        <a-popconfirm
                          title="¿Está seguro de eliminar este simulacro?"
                          ok-text="Sí"
                          cancel-text="No"
                          @confirm="eliminar(record)"
                        >
                          <a-button 
                            type="text" 
                            size="small"
                            danger
                          >
                            <template #icon>
                              <DeleteOutlined class="action-icon" />
                            </template>
                          </a-button>
                        </a-popconfirm>
                      </a-tooltip>
                    </a-space>
                  </template>
                </template>

                <!-- Empty State -->
                <template #emptyText>
                  <div class="empty-state">
                    <FolderOutlined style="font-size: 48px; color: #d9d9d9;" />
                    <p>No hay simulacros disponibles</p>
                    <a-button type="primary" @click="modalNuevo = true">
                      Crear nuevo proceso
                    </a-button>
                  </div>
                </template>
              </a-table>

          </div>

          <!-- Detalle del Proceso Seleccionado -->
          <div v-if="breadcrumb.length === 2">
            <div class="process-header">
              <a-button 
                type="text" 
                @click="entrarCarpeta(breadcrumb[0])"
                class="back-button"
              >
                <template #icon>
                  <arrow-left-outlined />
                </template>
                Volver
              </a-button>

            </div>

            <!-- Tabs del Proceso -->
            <a-card class="tabs-card">
              <a-tabs 
                v-model:activeKey="activeKey" 
                type="card"
                @change="handleTabChange"
              >
                <a-tab-pane key="1" tab="Participantes">
                  <Participantes :proceso="procesoseleccionado" />
                </a-tab-pane>
                <a-tab-pane key="2" tab="IDEs">
                  <Ides :proceso="procesoseleccionado" />
                </a-tab-pane>
                <a-tab-pane key="3" tab="Respuestas">
                  <Resp :proceso="procesoseleccionado" />
                </a-tab-pane>
                <a-tab-pane key="4" tab="Respuestas Correctas">
                  <Patron :proceso="procesoseleccionado" />
                </a-tab-pane>
                <a-tab-pane key="5" tab="Calificación">
                  <Calificacion :proceso="procesoseleccionado" />
                </a-tab-pane>
                <a-tab-pane key="6" tab="Exepciones">
                  <Excepcion :id_proceso="procesoseleccionado" />
                </a-tab-pane>
              </a-tabs>
            </a-card>
          </div>
        </div>
      </div>
    </div>

    <!-- Context Menu -->
    <div 
      v-if="showContextMenu" 
      class="context-menu"
      :style="{ top: `${contextMenuTop}px`, left: `${contextMenuLeft}px` }"
      @click.stop
    >
      <a-menu @click="handleMenuItemClick">
        <a-menu-item key="1">
          <template #icon>
            <plus-outlined />
          </template>
          Nuevo Proceso
        </a-menu-item>
        <a-menu-item key="2">
          <template #icon>
            <upload-outlined />
          </template>
          Subir archivo
        </a-menu-item>
        <a-menu-item key="3">
          <template #icon>
            <copy-outlined />
          </template>
          Duplicar selección
        </a-menu-item>
      </a-menu>
    </div>

    <!-- Modal para Nuevo Proceso -->
    <a-modal
      v-model:open="modalNuevo"
      :title="modalTitle"
      width="500px"
      centered
      :closable="true"
      @cancel="handleModalCancel"
      :maskClosable="false"
    >
      <a-form
        ref="formDatos"
        :model="form"
        :rules="formRules"
        layout="vertical"
      >
        <a-form-item
          label="Nombre del proceso"
          name="nombre"
          required
        >
          <a-input
            v-model:value="form.nombre"
            placeholder="Ingrese el nombre del proceso"
            size="large"
          >
            <template #prefix>
              <FolderOutlined />
            </template>
          </a-input>
        </a-form-item>

        <a-form-item
          label="Ubicación"
          name="ubigeo"
          required
        >
          <a-auto-complete
            v-model:value="ubigeo"
            :options="ubigeos"
            @select="onSelectUbigeo"
            placeholder="Buscar ubicación..."
            size="large"
          >
            <template #prefix>
              <EnvironmentOutlined />
            </template>
          </a-auto-complete>
        </a-form-item>

        <a-form-item
          label="Fecha"
          name="fecha"
          required
        >
          <a-date-picker
            v-model:value="form.fecha"
            placeholder="Seleccionar fecha"
            style="width: 100%"
            size="large"
            format="DD/MM/YYYY"
          />
        </a-form-item>

        <a-form-item
          label="Estado"
          name="estado"
        >
          <a-radio-group v-model:value="form.estado">
            <a-radio :value="1">Vigente</a-radio>
            <a-radio :value="0">No vigente</a-radio>
          </a-radio-group>
        </a-form-item>
      </a-form>

      <template #footer>
        <a-space>
          <a-button @click="handleModalCancel">
            Cancelar
          </a-button>
          <a-button 
            type="primary" 
            @click="guardar"
            :loading="saving"
          >
            Guardar
          </a-button>
        </a-space>
      </template>
    </a-modal>
  </Layout>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue';
import axios from 'axios';
import { 
  HomeOutlined, 
  FolderOutlined, 
  EnvironmentOutlined, 
  SearchOutlined,
  EyeOutlined,
  FormOutlined,
  DeleteOutlined,
  PlusOutlined,
  UploadOutlined,
  CopyOutlined,
  ArrowLeftOutlined,
  CalendarOutlined
} from '@ant-design/icons-vue';
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/LayoutCalificador.vue';
import Ides from './components/leer-ide.vue';
import Resp from './components/leer-resp.vue';
import Patron from './components/leer-patron.vue';
import Participantes from './components/participantes.vue';
import Calificacion from './components/calificacion.vue';
import Excepcion from './components/excepciones.vue';
import dayjs from 'dayjs';

// Estado reactivo
const breadcrumb = ref([{ id: 0, nombre: 'Simulacros' }]);
const simulacros = ref([]);
const loading = ref(false);
const saving = ref(false);
const modalNuevo = ref(false);
const activeKey = ref('1');
const procesoseleccionado = ref(null);
const procesoDetalle = ref(null);

// Búsqueda
const buscar = ref('');
let searchTimeout;

// Ubigeos
const ubigeo = ref('');
const ubigeos = ref([]);
const buscarUbigeo = ref('');

// Context Menu
const showContextMenu = ref(false);
const contextMenuTop = ref(0);
const contextMenuLeft = ref(0);

// Paginación
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0,
  showSizeChanger: true,
  pageSizeOptions: ['10', '20', '50', '100'],
  showTotal: (total, range) => `${range[0]}-${range[1]} de ${total} simulacros`,
});

// Formulario
const formDatos = ref();
const form = reactive({
  nombre: '',
  fecha: null,
  ubigeo: null,
  estado: 1,
});

const formRules = {
  nombre: [
    { required: true, message: 'Por favor ingrese el nombre del proceso', trigger: 'blur' },
  ],
  ubigeo: [
    { required: true, message: 'Por favor seleccione una ubicación', trigger: 'change' },
  ],
  fecha: [
    { required: true, message: 'Por favor seleccione una fecha', trigger: 'change' },
  ],
};

// Computed
const modalTitle = computed(() => form.id ? 'Editar Proceso' : 'Nuevo Proceso');

// Métodos
const formatDate = (date) => {
  if (!date) return 'N/A';
  return dayjs(date).format('DD/MM/YYYY');
};

const getSimulacros = async (params = {}) => {
  loading.value = true;
  try {
    const response = await axios.post('/get-sim', {
      term: buscar.value,
      page: pagination.current,
      per_page: pagination.pageSize,
      ...params,
    });
    
    const data = response.data.datos;
    simulacros.value = data.data;
    pagination.total = data.total;
    pagination.current = data.current_page;
    pagination.pageSize = data.per_page;
  } catch (error) {
    console.error('Error al cargar simulacros:', error);
    message.error('Error al cargar los simulacros');
  } finally {
    loading.value = false;
  }
};

const getUbigeosColegio = async () => {
  if (!buscarUbigeo.value.trim()) {
    ubigeos.value = [];
    return;
  }

  try {
    const response = await axios.post('/get-ubigeo', {
      term: buscarUbigeo.value,
    });
    ubigeos.value = response.data.datos.data.map(item => ({
      value: item.nombre,
      key: item.id,
      label: item.nombre,
    }));
  } catch (error) {
    console.error('Error al cargar ubigeos:', error);
  }
};

const onSelectUbigeo = (value, option) => {
  form.ubigeo = option.key;
};

const customRow = async (record) => {
  breadcrumb.value = [
    { id: 0, nombre: 'Simulacros' },
    { id: record.id, nombre: record.nombre }
  ];
  procesoseleccionado.value = record.id;
  procesoDetalle.value = record;
  activeKey.value = '1';
};

const entrarCarpeta = (item) => {
  const index = breadcrumb.value.findIndex(b => b.id === item.id);
  if (index !== -1) {
    breadcrumb.value = breadcrumb.value.slice(0, index + 1);
  }
  
  if (item.id === 0) {
    procesoseleccionado.value = null;
    procesoDetalle.value = null;
  }
};

const guardar = async () => {
  try {
    const values = await formDatos.value.validate();
    saving.value = true;
    
    const url = form.id ? '/simulacro/update-simulacro' : '/simulacro/save-simulacro';
    const data = {
      ...form,
      fecha: form.fecha ? dayjs(form.fecha).format('YYYY-MM-DD') : null,
    };
    
    if (form.id) {
      data.id = form.id;
    }

    await axios.post(url, data);
    
    message.success(form.id ? 'Proceso actualizado exitosamente' : 'Proceso creado exitosamente');
    modalNuevo.value = false;
    resetForm();
    await getSimulacros();
  } catch (error) {
    console.error('Error al guardar:', error);
    if (error.response?.data?.message) {
      message.error(error.response.data.message);
    }
  } finally {
    saving.value = false;
  }
};

const abrirEditar = (record) => {
  form.id = record.id;
  form.nombre = record.nombre;
  form.fecha = record.fecha ? dayjs(record.fecha) : null;
  form.ubigeo = record.ubigeo_id;
  form.estado = record.estado;
  ubigeo.value = record.ubigeo_nombre || '';
  modalNuevo.value = true;
};

const eliminar = async (record) => {
  try {
    await axios.post('/simulacro/delete-simulacro', { id: record.id });
    message.success('Simulacro eliminado exitosamente');
    await getSimulacros();
  } catch (error) {
    console.error('Error al eliminar:', error);
    message.error('Error al eliminar el simulacro');
  }
};

const verFicha = (record) => {
  // Implementar lógica para ver ficha detallada
  console.log('Ver ficha:', record);
};

const handleTableChange = (pag, filters, sorter) => {
  pagination.current = pag.current;
  pagination.pageSize = pag.pageSize;
  getSimulacros({
    sortField: sorter.field,
    sortOrder: sorter.order,
    filters,
  });
};

const handleModalCancel = () => {
  modalNuevo.value = false;
  resetForm();
};

const resetForm = () => {
  form.id = null;
  form.nombre = '';
  form.fecha = null;
  form.ubigeo = null;
  form.estado = 1;
  ubigeo.value = '';
  if (formDatos.value) {
    formDatos.value.resetFields();
  }
};

const clicIzquierdo = () => {
  showContextMenu.value = false;
};

const handleContextMenu = (event) => {
  showContextMenu.value = true;
  contextMenuTop.value = event.clientY;
  contextMenuLeft.value = event.clientX;
  event.preventDefault();
};

const handleMenuItemClick = (e) => {
  showContextMenu.value = false;
  if (e.key === '1') {
    modalNuevo.value = true;
  }
};

const handleTabChange = (key) => {
  activeKey.value = key;
};

// Watchers
watch(buscar, (newValue) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    pagination.current = 1;
    getSimulacros();
  }, 500);
});

watch(buscarUbigeo, (newValue) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    getUbigeosColegio();
  }, 500);
});

// Columnas de la tabla
const columnsSimulacros = [
  {
    title: 'N°',
    dataIndex: 'nro',
    width: 60,
    align: 'center',
  },
  {
    title: 'Nombre',
    dataIndex: 'nombre',
    sorter: true,
  },
  {
    title: 'Fecha',
    dataIndex: 'fecha',
    width: 120,
    align: 'center',
    sorter: true,
  },
  {
    title: 'Ubigeo',
    dataIndex: 'ubigeo',
    width: 150,
  },
  {
    title: 'Estado',
    dataIndex: 'estado',
    width: 120,
    align: 'center',
    filters: [
      { text: 'Vigente', value: 1 },
      { text: 'No Vigente', value: 0 },
    ],
  },
  {
    title: 'Acciones',
    dataIndex: 'acciones',
    width: 150,
    align: 'center',
  },
];

// Ciclo de vida
onMounted(() => {
  getSimulacros();
});
</script>

<style scoped>
.container {
  min-height: 100vh;
}

.content-wrapper {
  padding: 20px;
}

.main-content {
  background: white;
  border-radius: 12px;
  min-height: calc(100vh - 100px);
  padding: 24px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

/* Breadcrumb */
.breadcrumb-text {
  font-weight: 500;
  color: #595959;
}

.breadcrumb-link {
  color: #1890ff;
  text-decoration: none;
  transition: color 0.3s;
}

.breadcrumb-link:hover {
  color: #40a9ff;
  text-decoration: underline;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.header-left {
  flex: 1;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  color: #1f1f1f;
  margin: 0;
}

.page-subtitle {
  color: #8c8c8c;
  margin: 4px 0 0 0;
  font-size: 14px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Table Card */
.table-card {
  border-radius: 8px;
  border: 1px solid #f0f0f0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* Table Cells */
.nombre-cell {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.nombre-cell:hover {
  background-color: #f5f5f5;
}

.folder-icon {
  color: #1890ff;
  font-size: 16px;
}

.nombre-text {
  font-weight: 500;
  color: #1f1f1f;
}

.row-number {
  color: #8c8c8c;
  font-weight: 500;
}

.date-text {
  color: #595959;
}

.ubigeo-text {
  color: #434343;
}

/* Action Icons */
.action-icon {
  color: #8c8c8c;
  transition: color 0.3s;
}

.action-icon:hover {
  color: #1890ff;
}

.edit-icon:hover {
  color: #52c41a;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 48px 0;
}

.empty-state p {
  color: #8c8c8c;
  margin: 16px 0 24px;
}

/* Process Header */
.process-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.back-button {
  color: #8c8c8c;
}

.process-info {
  flex: 1;
}

.process-title {
  font-size: 20px;
  font-weight: 600;
  color: #1f1f1f;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.process-meta {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

/* Tabs Card */
.tabs-card {
  border-radius: 8px;
  border: 1px solid #f0f0f0;
}

:deep(.ant-tabs-nav) {
  margin-bottom: 0;
}

:deep(.ant-tabs-tab) {
  padding: 12px 16px;
}

/* Context Menu */
.context-menu {
  position: fixed;
  z-index: 1000;
  background: white;
  border-radius: 6px;
  box-shadow: 0 3px 6px -4px rgba(0, 0, 0, 0.12),
              0 6px 16px 0 rgba(0, 0, 0, 0.08),
              0 9px 28px 8px rgba(0, 0, 0, 0.05);
  border: 1px solid #f0f0f0;
}

.context-menu :deep(.ant-menu-item) {
  height: 36px;
  line-height: 36px;
  margin: 0;
  padding: 0 12px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
  }
  
  .header-right {
    width: 100%;
  }
  
  .header-right :deep(.ant-input-search) {
    width: 100% !important;
  }
}
</style>