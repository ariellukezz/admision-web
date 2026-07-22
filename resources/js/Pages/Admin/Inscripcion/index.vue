<template>
<Head title="Inscripciones"/>
<AuthenticatedLayout>
<div class="insc-container">

<!-- Botones + Buscador -->
<div class="insc-toolbar">
  <div class="insc-toolbar-left">
    <a-button class="insc-btn-excel" @click="descargarExcel">
      <template #icon><DownloadOutlined/></template>
      Excel
    </a-button>
  </div>
  <div class="insc-toolbar-right">
    <a-input
      v-model:value="buscar"
      placeholder="Buscar por DNI, nombres o apellidos"
      allow-clear
      @pressEnter="() => { pagina = 1; getInscripciones(); }"
    >
      <template #prefix>
        <SearchOutlined style="color: var(--card-muted, #bbb)"/>
      </template>
    </a-input>
  </div>
</div>

<!-- Filtros -->
<div class="insc-filters">
  <div class="insc-filters-row">
    <div class="insc-filter-item" style="flex: 2; min-width: 280px;">
      <label class="insc-label">Programa</label>
      <a-select
        v-model:value="filtroPrograma"
        style="width: 100%"
        placeholder="Todos los programas"
        size="large"
        allow-clear
        show-search
        @change="() => { pagina = 1; getInscripciones(); }"
        :options="programas"
        :filter-option="(input, option) => option.label.toLowerCase().includes(input.toLowerCase())"
      />
    </div>
    <div class="insc-filter-item" style="flex: 1; min-width: 160px;">
      <label class="insc-label">Área</label>
      <a-select
        v-model:value="filtroArea"
        style="width: 100%"
        placeholder="Todas"
        size="large"
        allow-clear
        @change="() => { pagina = 1; getInscripciones(); }"
        :options="areasOptions"
      />
    </div>
    <div class="insc-filter-item" style="flex: 1; min-width: 180px;">
      <label class="insc-label">Fecha</label>
      <a-date-picker
        v-model:value="filtroFecha"
        style="width: 100%"
        size="large"
        format="DD-MM-YYYY"
        value-format="YYYY-MM-DD"
        placeholder="Fecha específica"
        allow-clear
        @change="() => { pagina = 1; getInscripciones(); }"
      />
    </div>
    <div class="insc-filter-item" style="flex: 2; min-width: 280px;">
      <label class="insc-label">Rango de fechas</label>
      <a-range-picker
        v-model:value="filtroRango"
        style="width: 100%"
        size="large"
        format="DD-MM-YYYY"
        value-format="YYYY-MM-DD"
        :placeholder="['Desde', 'Hasta']"
        allow-clear
        @change="() => { pagina = 1; getInscripciones(); }"
      />
    </div>
    <div class="insc-filter-item" style="flex: 1; min-width: 120px;">
      <label class="insc-label">Mostrar</label>
      <a-select v-model:value="pageSize" size="large" style="width: 100%" @change="() => { pagina = 1; getInscripciones(); }">
        <a-select-option :value="20">20</a-select-option>
        <a-select-option :value="50">50</a-select-option>
        <a-select-option :value="100">100</a-select-option>
      </a-select>
    </div>
    <div class="insc-filter-item" style="display: flex; align-items: flex-end;">
      <a-button size="large" @click="limpiarFiltros">Limpiar</a-button>
    </div>
  </div>
</div>

<!-- Tabla -->
<div class="insc-table-card">
  <a-table
    :columns="columnsInscripcion"
    :data-source="inscripciones"
    :pagination="false"
    size="middle"
    :scroll="{ x: 900, y: 'calc(100vh - 420px)' }"
    row-key="id"
    :row-class-name="(_, index) => index % 2 === 1 ? 'insc-row-even' : ''"
  >
    <template #bodyCell="{ column, record }">
      <template v-if="column.dataIndex === 'codigo'">
        <a-tag color="blue" style="font-size: .8rem; font-weight: 600; border-radius: 6px;">
          {{ record.codigo }}
        </a-tag>
      </template>
      <template v-if="column.dataIndex === 'postulante'">
        <span style="font-weight: 600; color: var(--card-text, #1e293b);">{{ record.paterno }} {{ record.materno }}, {{ record.nombres }}</span>
      </template>
      <template v-if="column.dataIndex === 'estado'">
        <a-tag v-if="record.estado === 0" color="green" style="border-radius: 6px;">INSCRITO</a-tag>
        <a-tag v-else color="red" style="border-radius: 6px;">ANULADO</a-tag>
      </template>
      <template v-if="column.dataIndex === 'modalidad'">
        <a-tag style="border-radius: 6px; font-size: .75rem;" :color="record.modalidad === 'EXAMEN GENERAL' ? 'blue' : 'orange'">
          {{ record.modalidad }}
        </a-tag>
      </template>
      <template v-if="column.dataIndex === 'fecha'">
        <span style="font-size: .8rem; color: var(--card-muted, #64748b);">{{ formatearFecha(record.fecha) }}</span>
      </template>
      <template v-if="column.dataIndex === 'acciones'">
        <div class="insc-table-actions">
          <a-button type="text" size="small" @click="recargar(record.dni)" style="color: #52c41a;">
            <template #icon><RedoOutlined/></template>
          </a-button>
          <a-button type="text" size="small" @click="imprimirPDF(record.dni, record.id_proceso)" style="color: #096dd9;">
            <template #icon><PrinterOutlined/></template>
          </a-button>
          <a-button type="text" size="small" @click="abrirEditar(record)" style="color: #faad14;">
            <template #icon><FormOutlined/></template>
          </a-button>
          <a-popconfirm
            title="¿Estás seguro de eliminar?"
            ok-text="Sí"
            cancel-text="No"
            @confirm="eliminar(record)"
          >
            <a-button type="text" size="small" danger>
              <template #icon><DeleteOutlined/></template>
            </a-button>
          </a-popconfirm>
        </div>
      </template>
    </template>
  </a-table>
</div>

<!-- Paginación -->
<div class="insc-pagination">
  <a-pagination
    v-model:current="pagina"
    :total="totalRegistros"
    :page-size="pageSize"
    show-less-items
    @change="getInscripciones"
  />
</div>

<!-- Modal Editar -->
<a-modal
  v-model:open="visible"
  title="Modificar Inscripción"
  centered
  width="680px"
  :footer="false"
  class="insc-modal"
>
  <a-form ref="formRef" :model="inscripcion" layout="vertical">
    <a-row :gutter="[16, 0]">
      <a-col :span="8">
        <a-form-item label="Código">
          <a-input v-model:value="inscripcion.codigo" disabled />
        </a-form-item>
      </a-col>
      <a-col :span="8">
        <a-form-item label="DNI">
          <a-input :value="postulante.dni" disabled />
        </a-form-item>
      </a-col>
      <a-col :span="8">
        <a-form-item label="Estado">
          <a-switch
            v-model:checked="estadoSwitch"
            checked-children="Activo"
            un-checked-children="Anulado"
            style="margin-top: 6px;"
          />
        </a-form-item>
      </a-col>
      <a-col :span="24">
        <a-form-item label="Postulante">
          <a-input :value="postulante.nombre" disabled />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="Programa">
          <a-select
            v-model:value="inscripcion.id_programa"
            placeholder="Seleccionar programa"
            :options="programas"
            allow-clear
            show-search
            :filter-option="(input, option) => option.label.toLowerCase().includes(input.toLowerCase())"
          />
        </a-form-item>
      </a-col>
      <a-col :span="12">
        <a-form-item label="Modalidad">
          <a-select v-model:value="inscripcion.id_modalidad" placeholder="Seleccionar modalidad">
            <a-select-option :value="8">EXAMEN GENERAL</a-select-option>
            <a-select-option :value="7">CONADIS</a-select-option>
          </a-select>
        </a-form-item>
      </a-col>
      <a-col :span="24">
        <a-form-item label="Observaciones">
          <a-textarea v-model:value="inscripcion.observacion" :rows="3" />
        </a-form-item>
      </a-col>
    </a-row>
    <div class="insc-modal-footer">
      <a-button @click="visible = false" size="large">Cancelar</a-button>
      <a-button type="primary" @click="guardar()" size="large">Guardar</a-button>
    </div>
  </a-form>
</a-modal>

</div>
</AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue';
import {
  RedoOutlined, FormOutlined, PrinterOutlined,
  DeleteOutlined, SearchOutlined, DownloadOutlined
} from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';
import dayjs from 'dayjs';

const baseUrl = window.location.origin;

const buscar = ref("");
const inscripciones = ref([]);
const visible = ref(false);
const pagina = ref(1);
const totalRegistros = ref(0);
const pageSize = ref(20);
const estadoSwitch = ref(true);

const filtroPrograma = ref(null);
const filtroArea = ref(null);
const filtroFecha = ref(null);
const filtroRango = ref(null);

const areasOptions = [
  { value: 'BIOMEDICAS', label: 'Biomédicas' },
  { value: 'BIOMÉDICAS', label: 'Biomédicas' },
  { value: 'INGENIERIAS', label: 'Ingenierías' },
  { value: 'INGENIERÍAS', label: 'Ingenierías' },
  { value: 'SOCIALES', label: 'Sociales' },
];

const inscripcion = ref({
  id: null,
  codigo: "",
  id_postulante: "",
  id_programa: "",
  id_modalidad: "",
  estado: 0,
  observacion: "",
});

const postulante = ref({ id: "", nombre: "", dni: "" });

let timeoutId;
watch(buscar, () => {
  clearTimeout(timeoutId);
  timeoutId = setTimeout(() => { pagina.value = 1; getInscripciones(); }, 500);
});

watch(pagina, () => { getInscripciones(); });

const getInscripciones = async () => {
  try {
    let payload = {
      term: buscar.value,
      paginashoja: pageSize.value,
      programa: filtroPrograma.value,
      area: filtroArea.value,
      fecha: filtroFecha.value,
    };
    if (filtroRango.value && filtroRango.value[0]) {
      payload.fecha_inicio = filtroRango.value[0];
      payload.fecha_fin = filtroRango.value[1];
    }

    let res = await axios.post("get-inscripciones-admin?page=" + pagina.value, payload);
    inscripciones.value = res.data.datos.data;
    totalRegistros.value = res.data.datos.total;
  } catch (e) {
    console.error('Error al obtener inscripciones:', e);
  }
};

const programas = ref([]);
const getProgramas = async () => {
  try {
    let res = await axios.get("get-select-programas-proceso-admin");
    programas.value = res.data.datos;
  } catch (e) {
    console.error('Error al cargar programas:', e);
  }
};

getProgramas();
getInscripciones();

const limpiarFiltros = () => {
  filtroPrograma.value = null;
  filtroArea.value = null;
  filtroFecha.value = null;
  filtroRango.value = null;
  pagina.value = 1;
  getInscripciones();
};

const formatearFecha = (fecha) => {
  if (!fecha) return '';
  return dayjs(fecha).format('DD/MM/YYYY HH:mm');
};

const abrirEditar = (item) => {
  inscripcion.value.id = item.id;
  inscripcion.value.codigo = item.codigo;
  inscripcion.value.id_programa = item.id_programa;
  inscripcion.value.id_modalidad = item.id_modalidad;
  inscripcion.value.observacion = item.observacion;
  estadoSwitch.value = item.estado === 0;
  postulante.value.id = item.id_postulante;
  postulante.value.dni = item.dni;
  postulante.value.nombre = item.dni + " - " + item.nombres + " " + item.paterno + " " + item.materno;
  visible.value = true;
};

const guardar = () => {
  let est = estadoSwitch.value ? 0 : 3;
  let post = {
    id: inscripcion.value.id,
    id_postulante: postulante.value.id,
    id_programa: inscripcion.value.id_programa,
    id_modalidad: inscripcion.value.id_modalidad,
    observacion: inscripcion.value.observacion,
    estado: est,
    dni: postulante.value.dni,
  };
  axios.post("actualizar-inscripcion", post).then((result) => {
    getInscripciones();
    notification.success({ message: result.data.titulo, description: result.data.mensaje });
    visible.value = false;
    resetForm();
  });
};

const resetForm = () => {
  inscripcion.value = { id: null, codigo: "", id_postulante: "", id_programa: "", id_modalidad: "", estado: 0, observacion: "" };
  postulante.value = { id: "", nombre: "", dni: "" };
  estadoSwitch.value = true;
};

const eliminar = (item) => {
  axios.get("eliminar-modalidad/" + item.id).then((result) => {
    getInscripciones();
    notification.warning({ message: result.data.titulo, description: result.data.mensaje });
  });
};

const columnsInscripcion = [
  { title: 'Código', dataIndex: 'codigo', width: 120, align: 'center' },
  { title: 'DNI', dataIndex: 'dni', width: 90, align: 'center' },
  { title: 'Postulante', dataIndex: 'postulante', width: 240 },
  { title: 'Programa', dataIndex: 'programa', width: 180 },
  { title: 'Modalidad', dataIndex: 'modalidad', width: 130, align: 'center' },
  { title: 'Estado', dataIndex: 'estado', width: 100, align: 'center' },
  { title: 'Fecha', dataIndex: 'fecha', width: 130, align: 'center' },
  { title: 'Acciones', dataIndex: 'acciones', width: 120, align: 'center', fixed: 'right' },
];

const imprimirPDF = (dni, proc) => {
  var iframe = document.createElement('iframe');
  iframe.style.display = "none";
  iframe.src = baseUrl + '/documentos/' + proc + '/inscripciones/constancias/' + dni + '.pdf';
  document.body.appendChild(iframe);
  iframe.contentWindow.focus();
  iframe.contentWindow.print();
};

const recargar = (dni) => {
  var iframe = document.createElement('iframe');
  iframe.style.display = "none";
  iframe.src = baseUrl + '/admin/pdf-inscripción/' + dni;
  document.body.appendChild(iframe);
  iframe.contentWindow.focus();
  iframe.contentWindow.print();
};

const descargarExcel = async () => {
  try {
    const response = await axios.get('inscripciones/descargar-lista', {
      params: { descargar: 1 },
      responseType: 'blob',
    });
    const fecha = new Date();
    const formatoFecha = `${fecha.getDate().toString().padStart(2, '0')}-${(fecha.getMonth() + 1).toString().padStart(2, '0')}-${fecha.getFullYear()}_${fecha.getHours().toString().padStart(2, '0')}-${fecha.getMinutes().toString().padStart(2, '0')}-${fecha.getSeconds().toString().padStart(2, '0')}`;
    const nombreArchivo = `${formatoFecha}_lista_inscripciones.xlsx`;
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', nombreArchivo);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error al descargar el archivo:', error);
    notification.error({ message: 'Error', description: 'No se pudo descargar el Excel' });
  }
};
</script>

<style scoped>
.insc-container {
  width: 100%;
  margin: 0 auto;
}

/* Toolbar */
.insc-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}
.insc-toolbar-left {
  display: flex;
  gap: 8px;
}
.insc-toolbar-right {
  flex: 1;
  max-width: 360px;
}
.insc-btn-excel {
  border-radius: 10px;
  font-weight: 600;
  background: linear-gradient(135deg, #52c41a, #389e0d);
  border: none;
  color: #fff;
}
.insc-btn-excel:hover {
  filter: brightness(1.1);
  color: #fff !important;
}

/* Filters */
.insc-filters {
  background: var(--card-bg, #fff);
  border-radius: 14px;
  padding: 16px 20px;
  margin-bottom: 14px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  border: 1px solid var(--card-border, #e2e8f0);
}
.insc-filters-row {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.insc-filter-item {
  display: flex;
  flex-direction: column;
}
.insc-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--card-muted, #555);
  margin-bottom: 6px;
}

/* Table */
.insc-table-card {
  background: var(--card-bg, #fff);
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  border: 1px solid var(--card-border, #e2e8f0);
}

/* Table header dark fix */
:deep(.ant-table) {
  background: transparent !important;
  color: var(--card-text, #1e293b) !important;
}
:deep(.ant-table-thead > tr > th) {
  background: var(--table-header-bg, #f8fafc) !important;
  color: var(--card-text, #1e293b) !important;
  border-bottom: 1px solid var(--card-border, #e2e8f0) !important;
}
:deep(.ant-table-tbody > tr > td) {
  color: var(--card-text, #1e293b) !important;
  border-bottom: 1px solid var(--card-border, #e2e8f0) !important;
  background: var(--card-bg, #ffffff) !important;
}
:deep(.ant-table-tbody > tr:hover > td) {
  background: var(--hover-bg, #eff6ff) !important;
}
:deep(.insc-row-even > td) {
  background: var(--row-even, rgba(0,0,0,0.02)) !important;
}
:deep(.insc-row-even:hover > td) {
  background: var(--hover-bg, #eff6ff) !important;
}

.insc-table-actions {
  display: flex;
  gap: 4px;
  justify-content: center;
}

/* Pagination */
.insc-pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: 14px;
}

/* Modal */
.insc-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #f0f0f0;
}

/* Responsive */
@media (max-width: 768px) {
  .insc-toolbar {
    flex-direction: column;
    align-items: stretch;
  }
  .insc-toolbar-right {
    max-width: 100%;
  }
  .insc-filters-row {
    flex-direction: column;
  }
}
</style>
