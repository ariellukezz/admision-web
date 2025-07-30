<template>
<Head title="Resumen programa diario" />
<AuthenticatedLayout>
  <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 pb-0" style="height: calc(100vh - 103px);">
    <div class="flex justify-end mb-3 mr-3">
      <a-button type="primary" style="border-radius: 5px; background: #476175; border:none;" @click="descargarDetalle()">
        Descargar
      </a-button>
    </div>

    <div style="font-size: 0.7rem">
      <a-table
        :columns="columns"
        :data-source="datos"
        :pagination="false"
        size="small"
        :rowClassName="rowClassName"
        :scroll="{ x: 200, y: 'calc(100vh - 270px)' }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'programa'">
            {{ record.programa }}
          </template>
          <template v-else-if="column.dataIndex === 'codigo'">
            {{ record.codigo }}
          </template>
          <template v-else-if="column.dataIndex === 'inscripciones'">
            {{ record.inscripciones }}
          </template>
          <template v-else>
            <div style="text-align: center">
              {{ record[column.dataIndex] }}
            </div>
          </template>
        </template>

        <template #summary>
          <a-table-summary fixed="bottom">
            <a-table-summary-row>
              <a-table-summary-cell :col-span="2" style="text-align: right; font-weight: bold;">
                Totales:
              </a-table-summary-cell>
              <!-- columnas de fechas -->
              <a-table-summary-cell
                v-for="fecha in fechas"
                :key="fecha"
                style="text-align: center; font-weight: bold;"
              >
                {{ getTotalForDate(fecha) }}
              </a-table-summary-cell>
              <a-table-summary-cell style="text-align: center; font-weight: bold;">
                {{ totalInscripciones }}
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
import { computed, ref, onMounted } from 'vue';
import axios from 'axios';

const datos = ref([]);
const fechas = ref([]);
const totales = ref([{ total_inscripciones: 0 }]);
const loading = ref(false);
const error = ref(null);
const numeroFilasAmarillas = ref(0);

onMounted(async () => {
  try {
    loading.value = true;
    const response = await axios.post('reporte-programa-diario');
    datos.value = response.data.datos || []; 
    fechas.value = response.data.fechas || [];
    totales.value = response.data.totales || [{ total_inscripciones: 0 }]; 
  } catch (err) {
    error.value = err;
    console.error('Error al cargar datos:', err);
  } finally {
    loading.value = false;
  }
});

const monthGroups = computed(() => {
  const groups = {};
  fechas.value.forEach(fecha => {
    const monthKey = fecha.substring(0, 7);
    if (!groups[monthKey]) {
      groups[monthKey] = { 
        monthName: new Date(fecha).toLocaleString('es-ES', { month: 'long' }).toUpperCase(),
        dates: [] 
      };
    }
    groups[monthKey].dates.push(fecha);
  });
  return groups;
});

const columns = computed(() => {
  const base = [
    {
      title: 'COD',
      dataIndex: 'codigo',
      key: 'codigo',
      width: 20,
      align: 'center',
      fixed: 'left',
      customHeaderCell: () => ({ style: { background: '#c9c9c9' } }),
    },
    {
      title: 'PROGRAMA DE ESTUDIOS',
      dataIndex: 'programa',
      key: 'programa',
      width: 300,
      fixed: 'left',
      customHeaderCell: () => ({ style: { background: '#e3e3e3' } }),
    }
  ];

  Object.values(monthGroups.value).forEach(group => {
    base.push({
      title: group.monthName,
      key: group.monthName,
      align: 'center',
      children: group.dates.map(fecha => ({
        title: fecha.split('-')[2],
        dataIndex: `ins_${fecha}`,
        key: `ins_${fecha}`,
        width: 30,
        customHeaderCell: () => ({ style: { background: '#e3e3e3', textAlign:'center', height: '40px', width:'40px', padding: '0 0px' } }),
      })),
      customHeaderCell: () => ({ style: { background: '#e3e3e3' } }),
    });
  });

  base.push({
    title: 'TOTAL',
    dataIndex: 'inscripciones',
    key: 'inscripciones',
    width: 40,
    align: 'center',
    fixed: 'right',
    customHeaderCell: () => ({ style: { background: '#e3e3e3' } }),
  });

  return base;
});

const rowClassName = (record, index) => {
  return index < numeroFilasAmarillas.value ? 'fila-amarilla' : '';
};

const totalInscripciones = computed(() => {
  return totales.value[0]?.total_inscripciones || 0;
});

const getTotalForDate = (fecha) => {
  return totales.value[0]?.[`ins_${fecha}`] || 0;
};

const descargarDetalle = async () => {
  try {
    const response = await axios.post('reporte-programa-diario', 
      { descargar: 1 }, 
      { responseType: 'blob' }
    );
    
    const now = new Date();
    const timestamp = [
      now.getFullYear(),
      String(now.getMonth() + 1).padStart(2, '0'),
      String(now.getDate()).padStart(2, '0'),
      String(now.getHours()).padStart(2, '0'),
      String(now.getMinutes()).padStart(2, '0'),
      String(now.getSeconds()).padStart(2, '0')
    ].join('-');
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `${timestamp}_resumen_inscripciones.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (err) {
    console.error('Error al descargar:', err);
    notification.error({
      message: 'Error',
      description: 'No se pudo descargar el archivo'
    });
  }
};
</script>

<style>
.fila-amarilla,
.fila-amarilla > td {
  background-color: #fff9cc !important;
}
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