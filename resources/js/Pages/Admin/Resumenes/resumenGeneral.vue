<template>
<Head title="Resumen inscripciones"/>
<AuthenticatedLayout>

<div class="overflow-hidden shadow-sm sm:rounded-lg pt-4 pl-4 pb-0" style="height: calc(100vh - 103px); background: var(--card-bg, #ffffff); border: 1px solid var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">
  <row class="flex justify-end mb-3">
      <div class="mr-3">
          <a-button type="primary" style="border-radius: 5px; background: #476175; border:none;" @click="descargarDetalle()">Descargar</a-button>
      </div>
  </row>

  <div style="">
     <a-table
        :columns="columns"
        :data-source="datos"
        :pagination="false"
        size="small"
        :scroll="{ x: 200, y: 'calc(100vh - 236px)' }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'total'">
            {{ record.total }}
          </template>
        </template>

      <template #summary>
        <a-table-summary fixed="bottom">
          <a-table-summary-row v-if="total.length > 0 ">
            <a-table-summary-cell :col-span="2" style="text-align: left;">
                <span style="font-weight: bold; font-size: 1rem; color: #476175;">
                    Totales
                </span>
            </a-table-summary-cell>
            <a-table-summary-cell style="text-align: center;">
                <span style="font-weight: bold; color: #476175;">
                    {{ total[0].total_preinscripciones }}
                </span>
            </a-table-summary-cell>
            <a-table-summary-cell style="text-align: center;">
                <span style="font-weight: bold; color: #476175;">
                    {{ total[0].total_inscripciones }}
                </span>
            </a-table-summary-cell>
            <a-table-summary-cell style="text-align: center;">
                <span style="font-weight: bold; color: #476175;">
                    {{ total[0].total_diferencia }}
                </span>
            </a-table-summary-cell>
          </a-table-summary-row>
        </a-table-summary>
      </template>
      </a-table>
  </div>

  </div>

</AuthenticatedLayout>



</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { watch, computed, ref } from 'vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const buscar = ref("");
const pagina = ref(1)
const datos = ref([]);
const total = ref([])

const columns =  [
    { title: 'Cod', dataIndex: 'codigo', width:'50px' },
    { title: 'Programa de estudios', dataIndex: 'programa', align:'left'},
    { title: 'Preinscripciones', dataIndex: 'preinscripciones', align:'center', width:'120px'  },
    { title: 'Inscripciones', dataIndex: 'inscripciones', align:'center', width:'120px'},
    { title: 'Diferencia', dataIndex: 'diferencia', align:'center', width:'120px' },
];

const getResumen = async () => {
  try {
    let res = await axios.post("reporte-programa");
    datos.value = res.data.datos;
    total.value = res.data.totales;
  } catch (error) {
    // notification.error({ message: "Error", description: "No se pudo cargar los datos." });
  }
}
watch(pagina, () => { getResumen() });


let timeoutId;
watch(buscar, () => {
  clearTimeout(timeoutId);
  timeoutId = setTimeout(() => {
    pagina.value = 1;
    getResumen();
  }, 500);
});

const descargarDetalle = async () => {
  try {
    const response = await axios.post('reporte-programa',
      {
        descargar: 1,
      },
      {
        responseType: 'blob'
      }
    );

    if (response.status !== 200) {
      throw new Error('Error al obtener el archivo');
    }

    const fecha = new Date();
    const formatoFecha = `${fecha.getDate().toString().padStart(2, '0')}-${(fecha.getMonth() + 1).toString().padStart(2, '0')}-${fecha.getFullYear()}_${fecha.getHours().toString().padStart(2, '0')}-${fecha.getMinutes().toString().padStart(2, '0')}-${fecha.getSeconds().toString().padStart(2, '0')}`;
    const nombreArchivo = `${formatoFecha}_resumen_inscripciones.pdf`;

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
  }
};

getResumen();
</script>


<style >
::-webkit-scrollbar { 
  width: 9px; 
  height: 12px;
}

::-webkit-scrollbar-track {
  background: var(--content-bg, #f1f5f9);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Estilo para un scroll específico */
.scroll-container {
  overflow-y: auto;
  scrollbar-width: thin; /* Firefox */
  scrollbar-color: #888 var(--content-bg, #f1f5f9); /* Firefox */
}

/* Estilo para el scroll específico en Webkit (Chrome, Safari) */
.scroll-container::-webkit-scrollbar {
  width: 12px;
  height: 12px;
}

.scroll-container::-webkit-scrollbar-track {
  background: var(--content-bg, #f1f5f9);
  border-radius: 10px;
}

.scroll-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

.scroll-container::-webkit-scrollbar-thumb:hover {
  background: #555;
}

.checkbox-group-container {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;              /* espacio entre checkboxes */
  margin-bottom: 16px;   /* separación con lo de abajo */
}

.checkbox-group-container .ant-checkbox-group-item {
  margin-right: 8px;     /* opcional: espacio lateral */
  margin-bottom: 8px;    /* espacio entre filas */
}


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
