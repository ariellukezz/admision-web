<template>
<div @click="clicIzquierdo" @contextmenu.prevent="handleContextMenu">
    <div class="pl-4" style="background: white; width: 100%; min-height: calc(100vh - 190px); border-radius: 12px;">

    <div class="flex justify-between mt-2">
      <div class="flex items-center gap-2">
        <a-select
            v-model:value="idExamen"
            style="width: 220px"
            placeholder="Seleccione examen"
            @change="onExamenChange"
        >
            <a-select-option v-for="ex in examenes" :key="ex.id" :value="ex.id">
                {{ ex.area }}
            </a-select-option>
        </a-select>
        <a-radio-group v-model:value="tabPosition" style="margin-left: 8px;">
            <a-radio-button value="contenido">Respuestas</a-radio-button>
            <a-radio-button value="archivos">Archivos</a-radio-button>
        </a-radio-group>
      </div>
      <a-input v-model:value="buscar" style="max-width: 260px; border-radius: 6px; height: 32px;" placeholder="Buscar">
          <template #prefix><SearchOutlined/></template>
      </a-input>
    </div>

    <!-- Tabla de Archivos -->
    <div v-if="tabPosition === 'archivos'" class="mt-3 mb-3">
        <a-table
            :columns="columnsArchivos"
            :data-source="archivos"
            rowKey="id"
            size="small"
            :pagination="false"
        >
            <template #bodyCell="{ column, index, record }">
                <template v-if="column.dataIndex === 'nro'">
                    <span>{{ index + 1 }}</span>
                </template>
                <template v-if="column.dataIndex === 'area'">
                    <a-tag v-if="record.area == '1' || record.area == 'Biomédicas'" color="cyan">Biomédicas</a-tag>
                    <a-tag v-else-if="record.area == '2' || record.area == 'Ingenierías'" color="orange">Ingenierías</a-tag>
                    <a-tag v-else-if="record.area == '3' || record.area == 'Sociales'" color="purple">Sociales</a-tag>
                    <a-tag v-else>{{ record.area || 'N/A' }}</a-tag>
                </template>
                <template v-if="column.dataIndex === 'acciones'">
                    <a-popconfirm title="¿Eliminar archivo y sus registros?" @confirm="eliminarArchivo(record)">
                        <a-button size="small" danger><delete-outlined/></a-button>
                    </a-popconfirm>
                </template>
            </template>
        </a-table>
    </div>

    <!-- Tabla de Respuestas -->
    <div v-if="tabPosition === 'contenido'" class="mt-3 mb-3">
        <a-table
            :columns="columnsResp"
            :data-source="respuestas"
            rowKey="id"
            size="small"
            :scroll="{ y: 'calc(100vh - 300px)' }"
            :pagination="{ pageSize: 50, size: 'small' }"
        >
            <template #bodyCell="{ column, index, record }">
                <template v-if="column.dataIndex === 'nro'">
                    <span>{{ index + 1 }}</span>
                </template>
            </template>
        </a-table>
    </div>

    <!-- Modal: Cargar RESP -->
    <a-modal v-model:open="visible" title="Cargar fichas de respuestas" :footer="null" centered style="max-width: 500px;">
      <div class="mb-4">
        <label>Área (opcional):</label>
        <a-select v-model:value="area" style="width: 100%; margin-top: 4px;" allowClear>
            <a-select-option value="Biomédicas">Biomédicas</a-select-option>
            <a-select-option value="Ingenierías">Ingenierías</a-select-option>
            <a-select-option value="Sociales">Sociales</a-select-option>
        </a-select>
      </div>
      <a-upload-dragger
        v-model:fileList="fileList"
        name="file"
        :multiple="true"
        :action="uploadUrl"
        :data="uploadData"
        @change="handleChange"
      >
        <p class="ant-upload-drag-icon"><inbox-outlined/></p>
        <p class="ant-upload-text">Haz clic o arrastra archivos RES</p>
        <p class="ant-upload-hint">Archivos .txt o .dat</p>
      </a-upload-dragger>
    </a-modal>

    </div>

    <!-- Context Menu -->
    <div v-if="showContextMenu" class="context-menu" :style="{ top: contextMenuTop + 'px', left: contextMenuLeft + 'px' }" @click.stop>
        <a-menu size="small">
            <a-menu-item @click="abrirCargar">Cargar respuestas</a-menu-item>
        </a-menu>
    </div>
</div>
</template>

<script setup>
import { defineProps, watch, ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { SearchOutlined, DeleteOutlined, InboxOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

const props = defineProps(['proceso']);

const fileList = ref([]);
const tabPosition = ref('contenido');
const buscar = ref('');
const visible = ref(false);
const area = ref(null);
const idExamen = ref(null);
const examenes = ref([]);

const archivos = ref([]);
const respuestas = ref([]);

const showContextMenu = ref(false);
const contextMenuTop = ref(0);
const contextMenuLeft = ref(0);

const uploadUrl = computed(() => '/api/calificacion/lecturas/carga-res');
const uploadData = computed(() => ({ id_examen: idExamen.value, area: area.value }));

const columnsArchivos = [
    { title: 'N°', dataIndex: 'nro', width: 50, align: 'center' },
    { title: 'Nombre', dataIndex: 'nombre' },
    { title: 'Área', dataIndex: 'area', align: 'center' },
    { title: 'Fecha', dataIndex: 'fecha', align: 'center' },
    { title: 'Registros', dataIndex: 'registros', align: 'center' },
    { title: 'Acciones', dataIndex: 'acciones', align: 'center', width: 80 },
];

const columnsResp = [
    { title: 'N°', dataIndex: 'nro', width: 50, align: 'center' },
    { title: 'N° Lectura', dataIndex: 'n_lectura', align: 'center', width: 100 },
    { title: 'Litho', dataIndex: 'litho', align: 'center', width: 100 },
    { title: 'Tipo', dataIndex: 'tipo', width: 60, align: 'center' },
    { title: 'Respuestas', dataIndex: 'respuestas' },
];

const getExamenes = async () => {
    try {
        const res = await axios.get('/api/calificacion/examenes', { params: { id_simulacro: props.proceso, paginasize: 100 } });
        examenes.value = res.data.data.data;
        if (examenes.value.length > 0) {
            idExamen.value = examenes.value[0].id;
            await getArchivos();
            await getRespuestas();
        }
    } catch (e) { console.error('Error al cargar exámenes:', e); }
};

const onExamenChange = async () => {
    await getArchivos();
    await getRespuestas();
};

const getArchivos = async () => {
    if (!idExamen.value) return;
    try {
        const res = await axios.get('/api/calificacion/lecturas/archivos', { params: { id_examen: idExamen.value, tipo: 'res' } });
        archivos.value = res.data.data;
    } catch (e) { console.error('Error al cargar archivos:', e); }
};

const getRespuestas = async () => {
    if (!idExamen.value || archivos.value.length === 0) { respuestas.value = []; return; }
    try {
        let allRes = [];
        for (const arc of archivos.value) {
            const res = await axios.get(`/api/calificacion/lecturas/archivos/${arc.id}/respuestas`);
            allRes = allRes.concat(res.data.data);
        }
        respuestas.value = allRes;
    } catch (e) { console.error('Error al cargar respuestas:', e); }
};

const handleChange = (info) => {
    const status = info.file.status;
    if (status === 'done') {
        message.success(`${info.file.name} cargado correctamente.`);
        getArchivos();
        getRespuestas();
        visible.value = false;
    } else if (status === 'error') {
        message.error(`${info.file.name} falló al subir.`);
    }
};

const abrirCargar = () => { visible.value = true; showContextMenu.value = false; };

const eliminarArchivo = async (record) => {
    try {
        await axios.delete(`/api/calificacion/lecturas/archivos/${record.id}`);
        message.success('Archivo eliminado');
        getArchivos();
        getRespuestas();
    } catch (e) { message.error('Error al eliminar'); }
};

const handleContextMenu = (event) => {
    showContextMenu.value = true;
    contextMenuTop.value = event.clientY;
    contextMenuLeft.value = event.clientX;
    event.preventDefault();
};

const clicIzquierdo = () => { showContextMenu.value = false; };

let timeoutId;
watch(buscar, () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => getRespuestas(), 300);
});

onMounted(() => { getExamenes(); });
</script>

<style scoped>
.context-menu {
    position: fixed;
    z-index: 1000;
    background: white;
    border-radius: 6px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.16);
    border: 1px solid #f0f0f0;
}
</style>
