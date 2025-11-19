<template>
<Head title="Observados"/>
<AuthenticatedLayout>
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="height: calc(100vh - 98px);">

<div class="">
  <div class="mb-4">
      <span class="text-xl font-bold text-gray-800">Gestión de Observados</span>
  </div>
  <div class="flex justify-between items-center gap-2">
    <div>
      <a-radio-group v-model:value="filtroVigencia" @change="getObservados">
        <a-radio-button value="vigente">Vigente</a-radio-button>
        <a-radio-button value="no-vigente">No vigente</a-radio-button>
        <a-radio-button value="todos">Todos</a-radio-button>
      </a-radio-group>
    </div>
    <div class="flex justify-between" style="position: relative;">
      <a-input 
        type="text" 
        placeholder="Buscar por DNI o nombres..." 
        v-model:value="buscar" 
        style="max-width: 460px;"
        @change="getObservados"
      >
        <template #prefix> <search-outlined /> </template>
      </a-input>
    </div>
  </div>
</div>

<div class="flex justify-between my-4">
  <div class="flex justify-between gap-2 w-full">
    <div>
      <a-select
        v-model:value="filtroTipo"
        style="min-width: 232px"
        placeholder="Filtrar por tipo"
        @change="getObservados"
        allow-clear
      >
        <a-select-option value="indefinida">Inhabilitación indefinida</a-select-option>
        <a-select-option value="temporal">Inhabilitación temporal</a-select-option>
        <a-select-option value="economica">Sanción económica</a-select-option>
      </a-select>
    </div>
    <div class="flex gap-2">
      <div>
        <Documentos/>
      </div>
      <div>
        <Tipo/>
      </div>
      <a-button @click="showModalObservaciones()" type="primary" class="bg-blue-600 hover:bg-blue-700 text-white flex items-center gap-2">
        <PlusOutlined/>
        Nuevo Observado
      </a-button>
      <a-button type="primary" class="bg-red-600 hover:bg-red-700 text-white flex items-center gap-2">
        <file-pdf-outlined />
        PDF
      </a-button>
      <a-button type="primary" class="bg-green-600 hover:bg-green-700 text-white flex items-center gap-2">
        <file-excel-outlined />
        Excel
      </a-button>
    </div>
  </div>
</div>

<div style="">
  <a-table
    :columns="columnsObservados"
    :data-source="observados"
    :loading="loading"
    :pagination="pagination"
    size="middle"
    :scroll="{ x: 1200, y: 'calc(100vh - 280px)' }"
    class="observados-table"
  >
    <template #bodyCell="{ column, record }">
      
      <!-- Datos Personales -->
      <template v-if="column.key === 'datos_personales'">
        <div class="flex flex-col">
          <div class="font-semibold text-gray-800">
            {{ record.nombres }} {{ record.paterno }} {{ record.materno }}
          </div>
          <div class="text-sm text-gray-500">
            DNI: {{ record.dni }}
          </div>
        </div>
      </template>

      <!-- Fechas -->
      <template v-if="column.key === 'fechas'">
        <div class="flex flex-col space-y-1">
          <div class="flex items-center gap-1">
            <span class="text-xs text-gray-500">Inicio:</span>
            <span class="font-medium">{{ formatFecha(record.fecha_sancion) }}</span>
          </div>
          <div class="flex items-center gap-1" v-if="record.fecha_fin">
            <span class="text-xs text-gray-500">Fin:</span>
            <span class="font-medium">{{ formatFecha(record.fecha_fin) }}</span>
          </div>
          <div v-else class="text-xs text-gray-400">
            Sin fecha fin
          </div>
        </div>
      </template>

      <!-- Tipo Observación -->
      <template v-if="column.key === 'tipo_observacion'">
        <a-tag 
          :color="getTipoColor(record.tipo_observacion)"
          class="font-semibold px-2 py-1 text-xs"
        >
          {{ record.tipo_observacion }}
        </a-tag>
      </template>

      <!-- Categoría -->
      <template v-if="column.key === 'categoria'">
        <a-tag 
          :color="getCategoriaColor(record.categoria)"
          class="font-semibold px-2 py-1 text-xs"
        >
          {{ record.categoria }}
        </a-tag>
      </template>

      <!-- Documento -->
      <template v-if="column.key === 'documento'">
        <div class="flex items-center gap-2" v-if="record.documento">
          <file-text-outlined class="text-blue-500" />
          <span class="text-blue-600 hover:text-blue-800 cursor-pointer text-sm underline">
            {{ record.documento }}
          </span>
        </div>
        <span v-else class="text-gray-400 text-sm">Sin documento</span>
      </template>

      <!-- Estado -->
      <template v-if="column.key === 'estado'">
        <a-tag :color="getEstadoColor(record)" class="font-semibold">
          {{ getEstadoText(record) }}
        </a-tag>
      </template>

      <!-- Acciones -->
      <template v-if="column.key === 'acciones'">
        <a-space size="small">
          <a-tooltip title="Ver detalles">
            <a-button 
              type="link" 
              size="small" 
              @click="verDetalles(record)"
              class="text-blue-600 p-0"
            >
              <eye-outlined />
            </a-button>
          </a-tooltip>
          <a-tooltip title="Editar">
            <a-button 
              type="link" 
              size="small" 
              @click="editarObservado(record)"
              class="text-green-600 p-0"
            >
              <edit-outlined />
            </a-button>
          </a-tooltip>
          <a-tooltip title="Eliminar">
            <a-popconfirm
              title="¿Estás seguro de eliminar este observado?"
              @confirm="eliminarObservado(record.id)"
            >
              <a-button 
                type="link" 
                danger 
                size="small"
                class="p-0"
              >
                <delete-outlined />
              </a-button>
            </a-popconfirm>
          </a-tooltip>
        </a-space>
      </template>

    </template>
  </a-table>
</div>

<div class="flex justify-end mt-4">
  <a-pagination 
    v-model:current="pagina" 
    :page-size="pageSize"
    :total="totalObservados" 
    show-size-changer
    @change="onPageChange"
    @showSizeChange="onShowSizeChange"
  />
</div>

</div>

<!-- Modal para Nuevo/Editar Observado - FORMULARIO COMPLETO -->
<a-modal 
  v-model:open="visible" 
  :title="form.id ? 'Editar Observado' : 'Nuevo Observado'" 
  :footer="false" 
  width="90%"
  :styles="{ maxWidth: '900px' }"
  centered
>
  <div class="flex justify-center">
    <a-row style="display:flex; justify-content:center;">
      <a-col :span="24">
        <a-form
          ref="formDatos"
          name="form"
          :model="form" 
          :rules="formRules">
          
          <a-row :gutter="16" class="mt-3">
            <a-col :xs="24" :sm="12" :md="8">
              <label>DNI <span style="color:red;">*</span></label>
              <a-form-item 
                name="dni" 
                :rules="[{ required: true, message: 'Ingrese el DNI' }]">
                <a-input 
                  v-model:value="form.dni" 
                  placeholder="Número de DNI"
                  :maxlength="8"
                  size="large"
                >
                  <template #prefix>
                    <idcard-outlined />
                  </template>
                </a-input>
              </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="12" :md="8">
              <label>Nombres <span style="color:red;">*</span></label>
              <a-form-item 
                name="nombres" 
                :rules="[{ required: true, message: 'Ingrese los nombres' }]">
                <a-input 
                  v-model:value="form.nombres" 
                  placeholder="Nombres completos"
                  size="large"
                >
                  <template #prefix>
                    <user-outlined />
                  </template>
                </a-input>
              </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="12" :md="8">
              <label>Apellido Paterno <span style="color:red;">*</span></label>
              <a-form-item 
                name="paterno" 
                :rules="[{ required: true, message: 'Ingrese apellido paterno' }]">
                <a-input 
                  v-model:value="form.paterno" 
                  placeholder="Apellido paterno"
                  size="large"
                >
                  <template #prefix>
                    <user-outlined />
                  </template>
                </a-input>
              </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="12" :md="8">
              <label>Apellido Materno</label>
              <a-form-item name="materno">
                <a-input 
                  v-model:value="form.materno" 
                  placeholder="Apellido materno"
                  size="large"
                >
                  <template #prefix>
                    <user-outlined />
                  </template>
                </a-input>
              </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="12" :md="8">
              <label>Tipo <span style="color:red;">*</span></label>
              <a-form-item 
                name="tipo_id" 
                :rules="[{ required: true, message: 'Seleccione el tipo' }]">
                <a-select
                  v-model:value="form.tipo_id"
                  placeholder="Seleccione tipo"
                  style="width: 100%"
                  size="large"
                >
                  <a-select-option 
                    v-for="tipo in tipos" 
                    :key="tipo.value" 
                    :value="tipo.value">
                    {{ tipo.label }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="12" :md="8">
              <label>Concepto</label>
              <a-form-item name="concepto_id">
                <a-select
                  v-model:value="form.concepto_id"
                  placeholder="Seleccione concepto"
                  style="width: 100%"
                  size="large"
                >
                  <a-select-option 
                    v-for="concepto in conceptos" 
                    :key="concepto.id" 
                    :value="concepto.id">
                    {{ concepto.nombre }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="12" :md="8">
              <label>Documento</label>
              <a-form-item name="documento_id">
                <a-select
                  v-model:value="form.documento_id"
                  placeholder="Seleccione documento"
                  style="width: 100%"
                  size="large"
                >
                  <a-select-option 
                    v-for="documento in documentosSelect" 
                    :key="documento.value" 
                    :value="documento.value">
                    {{ documento.label }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="12" :md="12">
              <label>Fecha Sanción <span style="color:red;">*</span></label>
              <a-form-item 
                name="fecha_sancion" 
                :rules="[{ required: true, message: 'Seleccione fecha de sanción' }]">
                <a-date-picker
                  v-model:value="form.fecha_sancion"
                  style="width: 100%"
                  format="DD/MM/YYYY"
                  placeholder="Seleccione fecha"
                  size="large"
                />
              </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="12" :md="12">
              <label>Fecha Fin Sanción</label>
              <a-form-item name="fecha_fin_sancion">
                <a-date-picker
                  v-model:value="form.fecha_fin_sancion"
                  style="width: 100%"
                  format="DD/MM/YYYY"
                  placeholder="Seleccione fecha fin"
                  size="large"
                />
              </a-form-item>
            </a-col>

            <a-col :xs="24" :sm="24" :md="24">
              <label>Observaciones</label>
              <a-form-item name="observaciones">
                <a-textarea 
                  v-model:value="form.observaciones"
                  placeholder="Ingrese observaciones"
                  :rows="3"
                  size="large"
                />
              </a-form-item>
            </a-col>
          </a-row>

          <div class="flex justify-end mt-4">
            <div>
              <a-button 
                type="primary" 
                class="mr-2" 
                @click="visible = false" 
                style="border-radius: 6px; background: none; color:#476175; border: 1px solid #476175;"
                size="large"
              >
                Cancelar
              </a-button>
              <a-button 
                type="primary" 
                @click="save()" 
                style="border-radius: 6px; background: #476175; border: none;" 
                :loading="submitting"
                size="large"
              >
                {{ form.id ? 'Actualizar' : 'Guardar' }}
              </a-button>
            </div>
          </div>
        </a-form>
      </a-col>
    </a-row>
  </div>
</a-modal>

</AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { watch, computed, ref, unref, reactive, onMounted } from 'vue';
import { 
  FilePdfOutlined, 
  FileExcelOutlined, 
  FormOutlined, 
  PlusOutlined,  
  EditOutlined, 
  DeleteOutlined, 
  SearchOutlined,
  EyeOutlined,
  FileTextOutlined,
  UserOutlined,
  IdcardOutlined
} from '@ant-design/icons-vue';
import { notification, message } from 'ant-design-vue';
import axios from 'axios';
import Tipo from './components/tipo_observacion.vue'
import Documentos from './components/documentos_observacion.vue'
import dayjs from 'dayjs';

// Variables reactivas
const documentosSelect = ref([]);
const conceptos = ref([]);
const buscar = ref("");
const pagina = ref(1);
const pageSize = ref(10);
const totalObservados = ref(0);
const tipos = ref([]);
const filtroVigencia = ref('vigente');
const filtroTipo = ref(null);
const observados = ref([]);
const loading = ref(false);
const submitting = ref(false);

const form = reactive({  
  id: null,
  dni: "", 
  nombres: "",
  paterno: "",
  materno: '',
  tipo_id: null, 
  concepto_id: null,
  documento_id: null,
  fecha_sancion: "",
  fecha_fin_sancion: "",
  observaciones: ""
});

const visible = ref(false);

// Reglas de validación del formulario
const formRules = {
  dni: [{ required: true, message: 'El DNI es obligatorio' }],
  nombres: [{ required: true, message: 'Los nombres son obligatorios' }],
  paterno: [{ required: true, message: 'El apellido paterno es obligatorio' }],
  tipo_id: [{ required: true, message: 'El tipo es obligatorio' }],
  fecha_sancion: [{ required: true, message: 'La fecha de sanción es obligatoria' }]
};

// Columnas de la tabla
const columnsObservados = [
  {
    title: 'ID',
    dataIndex: 'id',
    key: 'id',
    width: 70,
    align: 'center'
  },
  {
    title: 'DATOS PERSONALES',
    key: 'datos_personales',
    width: 250,
    ellipsis: true
  },
  {
    title: 'FECHAS',
    key: 'fechas',
    width: 150,
    align: 'center'
  },
  {
    title: 'TIPO',
    key: 'tipo_observacion',
    width: 200,
    ellipsis: true
  },
  {
    title: 'CATEGORÍA',
    key: 'categoria',
    width: 180,
    ellipsis: true
  },
  {
    title: 'DOCUMENTO',
    key: 'documento',
    width: 150,
    ellipsis: true
  },
  {
    title: 'ESTADO',
    key: 'estado',
    width: 120,
    align: 'center'
  },
  {
    title: 'ACCIONES',
    key: 'acciones',
    width: 100,
    align: 'center',
    fixed: 'right'
  }
];

// Paginación
const pagination = computed(() => ({
  current: pagina.value,
  pageSize: pageSize.value,
  total: totalObservados.value,
  showSizeChanger: true,
  showQuickJumper: true,
  showTotal: (total, range) => `${range[0]}-${range[1]} de ${total} observados`
}));

// Watchers
watch([buscar, filtroVigencia, filtroTipo], () => {
  pagina.value = 1;
  getObservados();
});

watch(pagina, () => {
  getObservados();
});

// Métodos
const showModalObservaciones = () => {
  resetForm();
  visible.value = true;
};

const resetForm = () => {
  Object.assign(form, {
    id: null,
    dni: "", 
    nombres: "",
    paterno: "",
    materno: '',
    tipo_id: null, 
    concepto_id: null,
    documento_id: null,
    fecha_sancion: "",
    fecha_fin_sancion: "",
    observaciones: ""
  });
};

const getObservados = async () => {
  loading.value = true;
  try {
    let url = `https://servicios-admision.unap.edu.pe/api/v1/observados/observaciones/all?page=${pagina.value}&limit=${pageSize.value}`;
    
    // Agregar filtros
    if (buscar.value) {
      url += `&search=${buscar.value}`;
    }
    if (filtroVigencia.value !== 'todos') {
      url += `&vigencia=${filtroVigencia.value}`;
    }
    if (filtroTipo.value) {
      url += `&tipo=${filtroTipo.value}`;
    }

    const res = await axios.get(url);
    observados.value = res.data.data || [];
    totalObservados.value = res.data.count || observados.value.length;
    
  } catch (error) {
    console.error('Error al cargar observados:', error);
    message.error('Error al cargar los observados');
  } finally {
    loading.value = false;
  }
};

const getTipos = async () => {
  try {
    const res = await axios.get("https://servicios-admision.unap.edu.pe/api/v1/observados/tipos-select");
    tipos.value = res.data.data;
  } catch (error) {
    console.error('Error al cargar tipos:', error);
  }
};

const getConceptos = async () => {
  try {
    const res = await axios.get("https://servicios-admision.unap.edu.pe/api/v1/observados/conceptos-select");
    conceptos.value = res.data.data;
  } catch (error) {
    console.error('Error al cargar conceptos:', error);
  }
};

const getDocumentosSelect = async () => {
  try {
    const res = await axios.get("https://servicios-admision.unap.edu.pe/api/v1/observados/documentos-select");
    documentosSelect.value = res.data.data;
  } catch (error) {
    console.error('Error al cargar documentos:', error);
  }
};

const formatFecha = (fecha) => {
  if (!fecha) return '-';
  return dayjs(fecha).format('DD/MM/YYYY');
};

const getTipoColor = (tipo) => {
  const colors = {
    'SUPLANTACIÓN DE IDENTIDAD': 'red',
    'FALSIFICACIÓN': 'volcano',
    'OMISIÓN': 'orange',
    'default': 'blue'
  };
  return colors[tipo] || colors.default;
};

const getCategoriaColor = (categoria) => {
  const colors = {
    'INHABILITACION INDEFINIDA': 'red',
    'INHABILITACION TEMPORAL': 'orange',
    'SANCIÓN ECONÓMICA': 'purple',
    'default': 'green'
  };
  return colors[categoria] || colors.default;
};

const getEstadoColor = (record) => {
  const hoy = dayjs();
  const fechaFin = record.fecha_fin ? dayjs(record.fecha_fin) : null;
  
  if (!fechaFin) return 'blue';
  if (fechaFin.isAfter(hoy)) return 'green';
  return 'red';
};

const getEstadoText = (record) => {
  const hoy = dayjs();
  const fechaFin = record.fecha_fin ? dayjs(record.fecha_fin) : null;
  
  if (!fechaFin) return 'Vigente';
  if (fechaFin.isAfter(hoy)) return 'Vigente';
  return 'Expirado';
};

const verDetalles = (record) => {
  console.log('Ver detalles:', record);
  // Implementar lógica para ver detalles
};

const editarObservado = (record) => {
  Object.assign(form, {
    id: record.id,
    dni: record.dni,
    nombres: record.nombres,
    paterno: record.paterno,
    materno: record.materno,
    tipo_id: record.tipo_id,
    concepto_id: record.concepto_id,
    documento_id: record.documento_id,
    fecha_sancion: record.fecha_sancion,
    fecha_fin_sancion: record.fecha_fin,
    observaciones: record.observaciones
  });
  visible.value = true;
};

const eliminarObservado = async (id) => {
  try {
    await axios.delete(`https://servicios-admision.unap.edu.pe/api/v1/observados/observaciones/${id}`);
    message.success('Observado eliminado correctamente');
    getObservados();
  } catch (error) {
    console.error('Error al eliminar:', error);
    message.error('Error al eliminar el observado');
  }
};

const save = async () => {
  try {
    submitting.value = true;
    
    const formData = {
      ...form,
      fecha_sancion: form.fecha_sancion ? dayjs(form.fecha_sancion).format('YYYY-MM-DD') : null,
      fecha_fin_sancion: form.fecha_fin_sancion ? dayjs(form.fecha_fin_sancion).format('YYYY-MM-DD') : null
    };

    if (form.id) {
      // Editar
      await axios.put(`https://servicios-admision.unap.edu.pe/api/v1/observados/observaciones/${form.id}`, formData, {
        headers: { 'Content-Type': 'application/json' }
      });
      message.success('Observado actualizado correctamente');
    } else {
      // Crear
      await axios.post("https://servicios-admision.unap.edu.pe/api/v1/observados/observaciones", formData, {
        headers: { 'Content-Type': 'application/json' }
      });
      message.success('Observado creado correctamente');
    }
    
    getObservados();
    visible.value = false;
    resetForm();
    
  } catch (error) {
    console.error('Error:', error);
    message.error('Error al guardar el observado');
  } finally {
    submitting.value = false;
  }
};

const onPageChange = (page, pageSize) => {
  pagina.value = page;
  pageSize.value = pageSize;
};

const onShowSizeChange = (current, size) => {
  pageSize.value = size;
};

// Inicialización
onMounted(() => {
  getObservados();
  getTipos();
  getConceptos();
  getDocumentosSelect();
});
</script>

<style scoped>
.observados-table :deep(.ant-table-thead > tr > th) {
  background-color: #f8fafc;
  font-weight: 600;
  color: #374151;
}

.observados-table :deep(.ant-table-tbody > tr:hover > td) {
  background-color: #f3f4f6 !important;
}
</style>