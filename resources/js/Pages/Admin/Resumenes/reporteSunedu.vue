<template>
  <Head title="Reporte SUNEDU" />
  <AuthenticatedLayout>
    <div class="overflow-hidden shadow-sm sm:rounded-lg p-4 pr-0" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">
      <div class="flex justify-between items-center mb-4">
        <span style="font-size: 1.3rem;">Reporte SUNEDU — Identidad Cultural</span>
        <div class="flex gap-2 items-center">
          <a-checkbox v-model:checked="incluirIdentidad">Incluir identidad cultural</a-checkbox>
          <a-button type="primary" :loading="loading" style="border-radius: 5px; background: #096dd9; border: none;" @click="cargarDatos">
            <template #icon><ReloadOutlined /></template>
            Actualizar
          </a-button>
          <a-button type="primary" :loading="descargando" style="border-radius: 5px; background: #52c41a; border: none;" @click="descargarExcel">
            <template #icon><FileExcelOutlined /></template>
            Descargar Excel
          </a-button>
        </div>
      </div>

      <!-- Resumen rápido -->
      <div class="totales-mini" v-if="datos.length > 0">
        <div class="item">
          <span class="area">Total registros:</span>
          <span class="valor">{{ datos.length }}</span>
        </div>
        <div class="item">
          <span class="area">Ingresantes:</span>
          <span class="valor">{{ totalIngresantes }}</span>
        </div>
        <div class="item" v-if="incluirIdentidad">
          <span class="area">Con identidad cultural:</span>
          <span class="valor">{{ totalConIdentidad }}</span>
        </div>
        <div class="item" v-if="incluirIdentidad">
          <span class="area">Celular inválido:</span>
          <span class="valor" :style="{ color: totalCelularInvalido > 0 ? '#ff4d4f' : '' }">{{ totalCelularInvalido }}</span>
        </div>
        <div class="item" v-if="incluirIdentidad">
          <span class="area">Email inválido:</span>
          <span class="valor" :style="{ color: totalEmailInvalido > 0 ? '#ff4d4f' : '' }">{{ totalEmailInvalido }}</span>
        </div>
      </div>
    </div>

    <div class="mt-4 overflow-hidden shadow-sm sm:rounded-lg p-4" style="height: calc(100vh - 245px); background: var(--card-bg, #ffffff); border: 1px solid var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">
      <div class="mb-3">
        <a-input-search
          v-model:value="busqueda"
          placeholder="Buscar por DNI, nombres, programa o modalidad..."
          style="max-width: 450px;"
          allow-clear
        />
      </div>

      <a-table
        :columns="columns"
        :data-source="datosFiltrados"
        :loading="loading"
        :pagination="{ pageSize: 50, showSizeChanger: true, pageSizeOptions: ['25', '50', '100'] }"
        size="small"
        :scroll="{ x: 2200, y: 'calc(100vh - 420px)' }"
        row-key="NRO_DOCUMENTO"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'VALIDACION_CELULAR'">
            <a-tag :color="record.VALIDACION_CELULAR === 'OK' ? 'green' : 'red'">
              {{ record.VALIDACION_CELULAR }}
            </a-tag>
          </template>
          <template v-else-if="column.dataIndex === 'VALIDACION_EMAIL'">
            <a-tag :color="record.VALIDACION_EMAIL === 'OK' ? 'green' : 'red'">
              {{ record.VALIDACION_EMAIL }}
            </a-tag>
          </template>
          <template v-else-if="column.dataIndex === 'ESTADO_IDENTIDAD'">
            <a-tag :color="record.ESTADO_IDENTIDAD === 'REGISTRADO' ? 'blue' : 'orange'">
              {{ record.ESTADO_IDENTIDAD }}
            </a-tag>
          </template>
        </template>
      </a-table>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { ReloadOutlined, FileExcelOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const loading = ref(false);
const descargando = ref(false);
const datos = ref([]);
const busqueda = ref('');
const incluirIdentidad = ref(false);

const columns = computed(() => {
  const cols = [
    { title: 'Sede', dataIndex: 'CODIGO_SEDE_FILIAL', key: 'CODIGO_SEDE_FILIAL', width: 80, fixed: 'left' },
    { title: 'Proceso', dataIndex: 'PROCESO_ADMISION', key: 'PROCESO_ADMISION', width: 110 },
    { title: 'Tipo Doc.', dataIndex: 'TIPO_DOCUMENTO', key: 'TIPO_DOCUMENTO', width: 80 },
    { title: 'Nro. Documento', dataIndex: 'NRO_DOCUMENTO', key: 'NRO_DOCUMENTO', width: 110 },
    { title: 'Nombres', dataIndex: 'NOMBRES', key: 'NOMBRES', width: 150, ellipsis: true },
    { title: 'P. Apellido', dataIndex: 'PRIMER_APELLIDO', key: 'PRIMER_APELLIDO', width: 120, ellipsis: true },
    { title: 'S. Apellido', dataIndex: 'SEGUNDO_APELLIDO', key: 'SEGUNDO_APELLIDO', width: 120, ellipsis: true },
    { title: 'Sexo', dataIndex: 'SEXO', key: 'SEXO', width: 60 },
    { title: 'F. Nacimiento', dataIndex: 'FECHA_NACIMIENTO', key: 'FECHA_NACIMIENTO', width: 110 },
    { title: 'Nacionalidad', dataIndex: 'NACIONALIDAD', key: 'NACIONALIDAD', width: 100, ellipsis: true },
    { title: 'Programa', dataIndex: 'NOMBRE_PROGRAMA', key: 'NOMBRE_PROGRAMA', width: 200, ellipsis: true },
    { title: 'Facultad', dataIndex: 'CODIGO_FACULTAD_UNIDAD', key: 'CODIGO_FACULTAD_UNIDAD', width: 90 },
    { title: 'F. Registro', dataIndex: 'FECHA_REGISTRO', key: 'FECHA_REGISTRO', width: 100 },
    { title: 'Puntaje', dataIndex: 'PUNTAJE_OBTENIDO', key: 'PUNTAJE_OBTENIDO', width: 80 },
    { title: 'Modalidad', dataIndex: 'NOMBRE_MODALIDAD', key: 'NOMBRE_MODALIDAD', width: 140, ellipsis: true },
    { title: 'Ingresante', dataIndex: 'ES_INGRESANTE', key: 'ES_INGRESANTE', width: 90 },
    { title: 'F. Ingreso', dataIndex: 'FECHA_INGRESO', key: 'FECHA_INGRESO', width: 100 },
    { title: 'Correo Inst.', dataIndex: 'CORREO_INSTITUCIONAL', key: 'CORREO_INSTITUCIONAL', width: 180, ellipsis: true },
  ];

  if (incluirIdentidad.value) {
    cols.push({ title: 'Val. Celular', dataIndex: 'VALIDACION_CELULAR', key: 'VALIDACION_CELULAR', width: 130 });
    cols.push({ title: 'Val. Email', dataIndex: 'VALIDACION_EMAIL', key: 'VALIDACION_EMAIL', width: 120 });
    cols.push({ title: 'Identidad', dataIndex: 'ESTADO_IDENTIDAD', key: 'ESTADO_IDENTIDAD', width: 120 });
  }

  return cols;
});

const datosFiltrados = computed(() => {
  if (!busqueda.value) return datos.value;
  const term = busqueda.value.toLowerCase();
  return datos.value.filter(r =>
    (r.NRO_DOCUMENTO || '').toLowerCase().includes(term) ||
    (r.NOMBRES || '').toLowerCase().includes(term) ||
    (r.PRIMER_APELLIDO || '').toLowerCase().includes(term) ||
    (r.SEGUNDO_APELLIDO || '').toLowerCase().includes(term) ||
    (r.NOMBRE_PROGRAMA || '').toLowerCase().includes(term) ||
    (r.NOMBRE_MODALIDAD || '').toLowerCase().includes(term)
  );
});

const totalIngresantes = computed(() => datos.value.filter(r => r.ES_INGRESANTE == 1).length);
const totalConIdentidad = computed(() => datos.value.filter(r => r.ESTADO_IDENTIDAD === 'REGISTRADO').length);
const totalCelularInvalido = computed(() => datos.value.filter(r => r.VALIDACION_CELULAR !== 'OK').length);
const totalEmailInvalido = computed(() => datos.value.filter(r => r.VALIDACION_EMAIL !== 'OK').length);

const cargarDatos = async () => {
  loading.value = true;
  try {
    const res = await axios.post('/admin/reporte-sunedu', {
      incluir_identidad: incluirIdentidad.value,
    });
    if (res.data.estado) {
      datos.value = res.data.datos;
    } else {
      datos.value = [];
      notification.info({ message: 'Sin datos', description: 'No hay registros para este proceso.' });
    }
  } catch (error) {
    notification.error({ message: 'Error', description: 'No se pudieron cargar los datos del reporte.' });
  } finally {
    loading.value = false;
  }
};

const descargarExcel = async () => {
  descargando.value = true;
  try {
    const response = await axios.get('/admin/reporte-sunedu/exportar-excel', {
      params: { incluir_identidad: incluirIdentidad.value },
      responseType: 'blob',
    });

    if (response.status !== 200) {
      throw new Error('Error al obtener el archivo');
    }

    const fecha = new Date();
    const formatoFecha = `${fecha.getDate().toString().padStart(2, '0')}-${(fecha.getMonth() + 1).toString().padStart(2, '0')}-${fecha.getFullYear()}`;
    const nombreArchivo = `reporte_sunedu_${formatoFecha}.xlsx`;

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', nombreArchivo);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

    notification.success({ message: 'Descarga completa', description: 'El archivo Excel se ha descargado correctamente.' });
  } catch (error) {
    console.error('Error al descargar:', error);
    notification.error({ message: 'Error', description: 'No se pudo descargar el archivo Excel.' });
  } finally {
    descargando.value = false;
  }
};

cargarDatos();
</script>

<style>
/* ====== MODO OSCURO / HÍBRIDO: TABLA ====== */
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

/* Scrollbar */
::-webkit-scrollbar { width: 9px; height: 12px; }
::-webkit-scrollbar-track { background: var(--content-bg, #f1f5f9); border-radius: 10px; }
::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #555; }

/* Totales mini */
.totales-mini {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  font-size: 13px;
  color: var(--card-muted, #64748b);
  margin-top: 4px;
}
.totales-mini .item {
  background: var(--content-bg, #f1f5f9);
  padding: 4px 10px;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  gap: 4px;
}
.totales-mini .area { font-weight: 500; }
.totales-mini .valor { font-weight: 600; color: var(--card-text, #1e293b); }
</style>
