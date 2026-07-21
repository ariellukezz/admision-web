<template>
  <Layout>
    <Head title="Carga de Lecturas"/>
    <div class="p-4" style="background: white; width: 100%; min-height: calc(100vh - 90px); border-radius: 12px;">

    <!-- Formulario de carga -->
    <div class="upload-form mb-4" style="border: 1px solid #f0f0f0; border-radius: 10px; padding: 20px; background: #fafafa;">
      <div class="flex items-center gap-3 mb-3">
        <h3 style="margin: 0; color: #476175;">Carga de Lecturas</h3>
      </div>

      <div class="flex gap-3 mb-3" style="flex-wrap: wrap;">
        <div style="flex: 1; min-width: 220px;">
          <label style="display: block; margin-bottom: 4px; font-weight: 500;">Evaluación <span style="color: red;">*</span></label>
          <a-select
            v-model:value="form.id_examen"
            style="width: 100%"
            placeholder="Seleccione evaluación"
            :status="errors.id_examen ? 'error' : ''"
          >
            <a-select-option v-for="ex in examenes" :key="ex.id" :value="ex.id">
              {{ ex.area }}
            </a-select-option>
          </a-select>
          <div v-if="errors.id_examen" style="color: #ff4d4f; font-size: 12px; margin-top: 2px;">{{ errors.id_examen }}</div>
        </div>

        <div style="width: 140px;">
          <label style="display: block; margin-bottom: 4px; font-weight: 500;">Tipo <span style="color: red;">*</span></label>
          <a-select v-model:value="form.tipo" style="width: 100%">
            <a-select-option value="ide">IDE</a-select-option>
            <a-select-option value="res">RES</a-select-option>
          </a-select>
        </div>

        <div style="width: 160px;">
          <label style="display: block; margin-bottom: 4px; font-weight: 500;">Área</label>
          <a-select v-model:value="form.area" style="width: 100%">
            <a-select-option value="Biomédicas">Biomédicas</a-select-option>
            <a-select-option value="Ingenierías">Ingenierías</a-select-option>
            <a-select-option value="Sociales">Sociales</a-select-option>
          </a-select>
        </div>
      </div>

      <div class="mb-3">
        <label style="display: block; margin-bottom: 4px; font-weight: 500;">Archivos</label>
        <a-upload
          v-model:fileList="fileList"
          :multiple="true"
          :before-upload="() => false"
          accept=".txt,.dat"
        >
          <a-button>
            <UploadOutlined /> Seleccionar archivos
          </a-button>
        </a-upload>
        <div v-if="errors.files" style="color: #ff4d4f; font-size: 12px; margin-top: 2px;">{{ errors.files }}</div>
      </div>

      <div class="flex justify-end gap-2">
        <a-button @click="limpiarForm">Limpiar</a-button>
        <a-button type="primary" :loading="subiendo" @click="subirArchivos" style="background: #476175; border: none; border-radius: 5px;">
          <UploadOutlined /> Subir
        </a-button>
      </div>
    </div>

    <!-- Filtros de la tabla -->
    <div class="flex justify-between mb-3">
      <div class="flex items-center gap-3">
        <a-select v-model:value="filtroTipo" style="width: 130px" @change="getArchivos">
          <a-select-option value="ide">IDE</a-select-option>
          <a-select-option value="res">RES</a-select-option>
        </a-select>
        <a-select
          v-model:value="filtroExamen"
          style="width: 240px"
          placeholder="Filtrar por evaluación"
          allowClear
          @change="getArchivos"
        >
          <a-select-option v-for="ex in examenes" :key="ex.id" :value="ex.id">
            {{ ex.area }}
          </a-select-option>
        </a-select>
      </div>
      <a-input v-model:value="buscar" placeholder="Buscar" style="max-width: 200px; border-radius: 6px;">
        <template #prefix><SearchOutlined/></template>
      </a-input>
    </div>

    <!-- Tabla de Archivos -->
    <a-table
      :columns="columnsArchivos"
      :data-source="archivosFiltrados"
      rowKey="id"
      size="small"
      :pagination="false"
      class="mb-4"
    >
      <template #bodyCell="{ column, index, record }">
        <template v-if="column.dataIndex === 'nro'">{{ index + 1 }}</template>
        <template v-if="column.dataIndex === 'tipo'">
          <a-tag :color="record.tipo === 'ide' ? 'blue' : 'green'">{{ record.tipo === 'ide' ? 'IDE' : 'RES' }}</a-tag>
        </template>
        <template v-if="column.dataIndex === 'area'">
          <a-tag v-if="record.area == 'Biomédicas' || record.area == '1'" color="cyan">Biomédicas</a-tag>
          <a-tag v-else-if="record.area == 'Ingenierías' || record.area == '2'" color="orange">Ingenierías</a-tag>
          <a-tag v-else-if="record.area == 'Sociales' || record.area == '3'" color="purple">Sociales</a-tag>
          <a-tag v-else>{{ record.area || 'N/A' }}</a-tag>
        </template>
        <template v-if="column.dataIndex === 'estado'">
          <a-tag :color="record.estado ? 'green' : 'default'">{{ record.estado ? 'Activo' : 'Inactivo' }}</a-tag>
        </template>
        <template v-if="column.dataIndex === 'acciones'">
          <a-popconfirm title="¿Eliminar archivo y sus registros?" @confirm="eliminarArchivo(record)">
            <a-button size="small" danger><DeleteOutlined/></a-button>
          </a-popconfirm>
        </template>
      </template>
    </a-table>

    <!-- Tabla de Registros -->
    <div v-if="registros.length > 0" class="mt-4">
      <h3 class="mb-2" style="color: #476175;">
        {{ filtroTipo === 'ide' ? 'Identificadores' : 'Respuestas' }} ({{ registros.length }})
      </h3>
      <a-table
        :columns="columnsRegistros"
        :data-source="registros"
        rowKey="id"
        size="small"
        :scroll="{ y: 'calc(100vh - 550px)' }"
        :pagination="{ pageSize: 50, size: 'small' }"
      >
        <template #bodyCell="{ column, index, record }">
          <template v-if="column.dataIndex === 'nro'">{{ index + 1 }}</template>
          <template v-if="column.dataIndex === 'observaciones' && filtroTipo === 'ide'">
            <a-tag v-for="obs in (record.observaciones || [])" :key="obs" :color="obsColor(obs)">{{ obs }}</a-tag>
          </template>
          <template v-if="column.dataIndex === 'estado' && filtroTipo === 'ide'">
            <a-tag :color="record.estado == 1 ? 'cyan' : 'red'">{{ record.estado == 1 ? 'Sí' : 'No' }}</a-tag>
          </template>
        </template>
      </a-table>
    </div>

    </div>
  </Layout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/LayoutCalificador.vue';
import { SearchOutlined, UploadOutlined, DeleteOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

const fileList = ref([]);
const buscar = ref('');
const subiendo = ref(false);

const form = reactive({
  id_examen: null,
  tipo: 'ide',
  area: 'Biomédicas',
});

const errors = reactive({});

const filtroTipo = ref('ide');
const filtroExamen = ref(null);
const examenes = ref([]);
const archivos = ref([]);
const registros = ref([]);

const archivosFiltrados = computed(() => {
  if (!buscar.value) return archivos.value;
  const q = buscar.value.toLowerCase();
  return archivos.value.filter(a => (a.nombre || '').toLowerCase().includes(q));
});

const columnsArchivos = [
  { title: 'N°', dataIndex: 'nro', width: 50, align: 'center' },
  { title: 'Tipo', dataIndex: 'tipo', width: 80, align: 'center' },
  { title: 'Nombre', dataIndex: 'nombre' },
  { title: 'Área', dataIndex: 'area', align: 'center' },
  { title: 'Fecha', dataIndex: 'fecha', align: 'center', width: 100 },
  { title: 'Registros', dataIndex: 'registros', align: 'center', width: 90 },
  { title: 'Estado', dataIndex: 'estado', align: 'center', width: 80 },
  { title: '', dataIndex: 'acciones', width: 60, align: 'center' },
];

const columnsRegistros = computed(() => {
  if (filtroTipo.value === 'ide') {
    return [
      { title: 'N°', dataIndex: 'nro', width: 50, align: 'center' },
      { title: 'N° Lectura', dataIndex: 'camp2', align: 'center', width: 100 },
      { title: 'DNI', dataIndex: 'dni', align: 'center', width: 100 },
      { title: 'Aula', dataIndex: 'aula', width: 70, align: 'center' },
      { title: 'Tipo', dataIndex: 'tipo', width: 60, align: 'center' },
      { title: 'Litho', dataIndex: 'litho', align: 'center', width: 90 },
      { title: 'Calificar', dataIndex: 'estado', width: 80, align: 'center' },
      { title: 'Observaciones', dataIndex: 'observaciones' },
    ];
  }
  return [
    { title: 'N°', dataIndex: 'nro', width: 50, align: 'center' },
    { title: 'N° Lectura', dataIndex: 'n_lectura', align: 'center', width: 100 },
    { title: 'Litho', dataIndex: 'litho', align: 'center', width: 100 },
    { title: 'Tipo', dataIndex: 'tipo', width: 60, align: 'center' },
    { title: 'Respuestas', dataIndex: 'respuestas' },
  ];
});

const obsColor = (obs) => {
  const map = { 'Sin DNI': 'pink', 'Sin aula': 'purple', 'DNI erroneo': 'green', 'Sin tipo': 'yellow', 'No se calificará': 'red' };
  return map[obs] || 'default';
};

const getExamenes = async () => {
  try {
    const res = await axios.get('/api/calificacion/examenes', { params: { paginasize: 100 } });
    examenes.value = res.data.data.data;
  } catch (e) { console.error('Error al cargar exámenes:', e); }
};

const getArchivos = async () => {
  registros.value = [];
  const params = { tipo: filtroTipo.value };
  if (filtroExamen.value) params.id_examen = filtroExamen.value;

  try {
    const res = await axios.get('/api/calificacion/lecturas/archivos', { params });
    archivos.value = res.data.data;
    for (const arc of archivos.value) {
      const endpoint = filtroTipo.value === 'ide'
        ? `/api/calificacion/lecturas/archivos/${arc.id}/ides`
        : `/api/calificacion/lecturas/archivos/${arc.id}/respuestas`;
      const res2 = await axios.get(endpoint);
      registros.value = registros.value.concat(res2.data.data);
    }
  } catch (e) { console.error('Error al cargar archivos:', e); }
};

const validar = () => {
  let ok = true;
  errors.id_examen = '';
  errors.files = '';

  if (!form.id_examen) {
    errors.id_examen = 'Debe seleccionar una evaluación';
    ok = false;
  }

  const archivosValidos = fileList.value.filter(f => f.originFileObj);
  if (archivosValidos.length === 0) {
    errors.files = 'Debe seleccionar al menos un archivo';
    ok = false;
  }

  return ok;
};

const subirArchivos = async () => {
  if (!validar()) {
    message.warning('Complete los campos obligatorios');
    return;
  }

  const archivosValidos = fileList.value.filter(f => f.originFileObj);
  const url = form.tipo === 'ide'
    ? '/api/calificacion/lecturas/carga-ide'
    : '/api/calificacion/lecturas/carga-res';

  subiendo.value = true;
  let exito = 0;
  let fallo = 0;

  for (const f of archivosValidos) {
    const formData = new FormData();
    formData.append('file', f.originFileObj);
    formData.append('id_examen', form.id_examen);
    formData.append('area', form.area);

    try {
      await axios.post(url, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      exito++;
    } catch (e) {
      fallo++;
      console.error(`Error al subir ${f.name}:`, e.response?.data?.message || e.message);
    }
  }

  subiendo.value = false;

  if (exito > 0) {
    message.success(`${exito} archivo(s) subido(s) correctamente.`);
    limpiarForm();
    filtroTipo.value = form.tipo;
    filtroExamen.value = form.id_examen;
    await getArchivos();
  }
  if (fallo > 0) {
    message.error(`${fallo} archivo(s) fallaron al subir.`);
  }
};

const limpiarForm = () => {
  fileList.value = [];
  errors.id_examen = '';
  errors.files = '';
};

const eliminarArchivo = async (record) => {
  try {
    await axios.delete(`/api/calificacion/lecturas/archivos/${record.id}`);
    message.success('Archivo eliminado');
    getArchivos();
  } catch (e) { message.error('Error al eliminar'); }
};

onMounted(() => {
  getExamenes();
  getArchivos();
});
</script>
