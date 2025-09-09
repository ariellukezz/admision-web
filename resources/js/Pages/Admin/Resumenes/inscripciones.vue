<template>
<Head title="Resumen inscripciones"/>
<AuthenticatedLayout>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
    <div class="mb-4">
      <span style="font-size: 1.3rem;">Resumen inscripción</span>
    </div>
    <div class="checkbox-group-container">
      <a-checkbox-group
        v-model:value="selectedColumns"
        :options="columnOptions"
      />
    </div>    
  </div>

<div class=" mt-4 bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="height: calc(100vh - 230px);">
  <row class="flex justify-end mb-4" >
      <div class="flex gap-2">
          <a-button type="primary" style="background: #40DE7A;" @click="descargarExcel()">
            <div style="display: flex; align-items: center; gap: 6px;">
              <FileExcelOutlined/> EXCEL
            </div>
          </a-button>
          <a-button type="primary" sise="large" style="background: crimson;" @click="descargarDetalle()">
            <div style="display: flex; align-items: center; gap: 6px;">
              <FilePdfOutlined/> PDF
            </div>
          </a-button>
      </div>
  </row>

  <div style="">
     <a-table
        :columns="columns"
        :data-source="resumenes"
        :pagination="false"
        size="small"
        :scroll="{ x: 200, y: 'calc(100vh - 400px)' }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'total'">
            {{ record.total }}
          </template>
        </template>

      <template #summary>
        <a-table-summary fixed="bottom">
          <a-table-summary-row>
            <a-table-summary-cell :col-span="columns.length - 1" style="text-align: right;">
              <span style="font-weight: bold; color: #476175;">
                Total Registros:
              </span>
            </a-table-summary-cell>
            <a-table-summary-cell style="text-align: center;">
              <span style="font-weight: bold; color: #476175;">
                {{ totalGeneral }}
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
import { FileExcelOutlined, FilePdfOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const buscar = ref("");
const pagina = ref(1)
const totalpaginas = ref(0)
const resumenes = ref([]);
const selectedColumns = ref(['area']);
const totalGeneral = computed(() => {
  return resumenes.value.reduce((acc, item) => acc + item.total, 0);
});


const columnOptions = [
  { label: 'Área', value: 'area' },
  { label: 'Programa', value: 'programa' },
  { label: 'Modalidad', value: 'modalidad' },
  { label: 'Sexo', value: 'sexo' },
  { label: 'Usuario', value: 'usuario' }
];

const columns = computed(() => {
  return [
    ...selectedColumns.value.map(col => {
      const option = columnOptions.find(opt => opt.value === col);
      return {
        title: option ? option.label : col,
        dataIndex: col,
        key: col,
        align: 'left'
      };
    }),
    {
      title: 'Total',
      dataIndex: 'total',
      key: 'total',
      align: 'center',
      width: '90px',

    }
  ];
});


const getResumen = async () => {
  try {
    let res = await axios.post("resumen-inscripciones?page=" + pagina.value, {
      group_by: selectedColumns.value,
      term: buscar.value
    });
    resumenes.value = res.data.data;
    totalpaginas.value = res.data.datos.total;
  } catch (error) {
    // notification.error({ message: "Error", description: "No se pudo cargar los datos." });
  }
}


watch(selectedColumns, () => { getResumen() });
watch(pagina, () => { getResumen() });


let timeoutId;
watch(buscar, () => {
  clearTimeout(timeoutId);
  timeoutId = setTimeout(() => {
    pagina.value = 1;
    getResumen();
  }, 500);
});

const fecha = new Date();

const descargarDetalle = async () => {
  try {
    const response = await axios.post('resumen-inscripciones',
      {
        descargar: 1,
        group_by: selectedColumns.value,
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

const descargarExcel = async () => {
  try {
    const response = await axios.post('resumen-inscripciones',
      {
        descargar: 1,
        excel: 1,
        group_by: selectedColumns.value,
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
    const nombreArchivo = `${formatoFecha}_resumen_inscripciones.xlsx`;

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
  background: #f1f1f1;
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
  scrollbar-color: #888 #f1f1f1; /* Firefox */
}

/* Estilo para el scroll específico en Webkit (Chrome, Safari) */
.scroll-container::-webkit-scrollbar {
  width: 12px;
  height: 12px;
}

.scroll-container::-webkit-scrollbar-track {
  background: #f1f1f1;
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

</style>
