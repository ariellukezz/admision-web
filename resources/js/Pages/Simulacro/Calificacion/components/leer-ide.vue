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
            <a-radio-button value="contenido">Identificadores</a-radio-button>
            <a-radio-button value="archivos">Archivos</a-radio-button>
        </a-radio-group>
      </div>
      <div class="flex gap-2">
        <a-input v-model:value="buscar" style="max-width: 200px; border-radius: 6px; height: 32px;" placeholder="Buscar">
            <template #prefix><SearchOutlined/></template>
        </a-input>
        <a-button type="primary" style="background: #476175; border:none; border-radius: 4px;" @click="descargarObservaciones(proceso)">Observaciones
            <template #icon><DownloadOutlined/></template>
        </a-button>
      </div>
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
                <template v-if="column.dataIndex === 'nombre'">
                    <div style="cursor:pointer;" @dblclick="verIdesArchivo(record)">
                        <span>{{ record.nombre }}</span>
                    </div>
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

    <!-- Tabla de Identificadores -->
    <div v-if="tabPosition === 'contenido'" class="mt-3 mb-3">
        <a-table
            :columns="columnsIdes"
            :data-source="identificaciones"
            rowKey="id"
            size="small"
            :scroll="{ y: 'calc(100vh - 300px)' }"
            :pagination="{ pageSize: 50, size: 'small' }"
        >
            <template #bodyCell="{ column, index, record }">
                <template v-if="column.dataIndex === 'nro'">
                    <span>{{ (pagination.current - 1) * pagination.pageSize + index + 1 }}</span>
                </template>
                <template v-if="column.dataIndex === 'observaciones'">
                    <a-tag v-for="obs in record.observaciones" :key="obs" :color="obsColor(obs)">{{ obs }}</a-tag>
                </template>
                <template v-if="column.dataIndex === 'estado'">
                    <a-tag v-if="record.estado == 1" color="cyan">Sí</a-tag>
                    <a-tag v-else color="red">No</a-tag>
                </template>
                <template v-if="column.dataIndex === 'acciones'">
                    <a-button size="small" @click="abrirEditar(record)" style="color: #af7200;">
                        <form-outlined/>
                    </a-button>
                </template>
            </template>
        </a-table>
    </div>

    <!-- Modal: Cargar fichas IDE -->
    <a-modal v-model:open="visible" title="Cargar fichas de identificación" :footer="null" centered style="max-width: 500px;">
      <div class="mb-4">
        <label>Área:</label>
        <a-select v-model:value="area" style="width: 100%; margin-top: 4px;">
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
        @drop="handleDrop"
      >
        <p class="ant-upload-drag-icon"><inbox-outlined/></p>
        <p class="ant-upload-text">Haz clic o arrastra archivos IDE</p>
        <p class="ant-upload-hint">Archivos .txt o .dat</p>
      </a-upload-dragger>
    </a-modal>

    <!-- Modal: Editar IDE -->
    <a-modal v-model:open="editar" :footer="null" centered width="520px">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Hoja #{{ ide.litho }}</h2>
            <a-tag color="processing">Lectura {{ ide.camp2 }}</a-tag>
        </div>
        <a-form layout="vertical" :model="ide" class="grid grid-cols-2 gap-4">
            <a-form-item label="DNI"><a-input v-model:value="ide.dni" size="large"/></a-form-item>
            <a-form-item label="Aula"><a-input v-model:value="ide.aula" size="large"/></a-form-item>
            <a-form-item label="Tipo"><a-input v-model:value="ide.tipo" size="large"/></a-form-item>
            <a-form-item label="Calificar">
                <a-select v-model:value="ide.estado" size="large">
                    <a-select-option :value="1">Sí</a-select-option>
                    <a-select-option :value="0">No</a-select-option>
                </a-select>
            </a-form-item>
        </a-form>
        <div class="flex justify-end mt-4 gap-2">
            <a-button @click="editar = false">Cancelar</a-button>
            <a-button type="primary" @click="actualizarIde()">Guardar cambios</a-button>
        </div>
    </a-modal>

    </div>

    <!-- Context Menu -->
    <div v-if="showContextMenu" class="context-menu" :style="{ top: contextMenuTop + 'px', left: contextMenuLeft + 'px' }" @click.stop>
        <a-menu size="small">
            <a-menu-item @click="abrirCargar">Cargar archivos</a-menu-item>
        </a-menu>
    </div>
</div>
</template>

<script setup>
import { defineProps, watch, ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { FormOutlined, DeleteOutlined, SearchOutlined, DownloadOutlined, InboxOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

const props = defineProps(['proceso']);

const fileList = ref([]);
const tabPosition = ref('contenido');
const buscar = ref('');
const visible = ref(false);
const editar = ref(false);
const area = ref('Biomédicas');
const idExamen = ref(null);
const examenes = ref([]);
const ide = ref({});

const archivos = ref([]);
const identificaciones = ref([]);
const pagination = ref({ current: 1, pageSize: 50 });

const showContextMenu = ref(false);
const contextMenuTop = ref(0);
const contextMenuLeft = ref(0);

const uploadUrl = computed(() => '/api/calificacion/lecturas/carga-ide');
const uploadData = computed(() => ({ id_examen: idExamen.value, area: area.value }));

const columnsArchivos = [
    { title: 'N°', dataIndex: 'nro', width: 50, align: 'center' },
    { title: 'Nombre', dataIndex: 'nombre' },
    { title: 'Área', dataIndex: 'area', align: 'center' },
    { title: 'Fecha', dataIndex: 'fecha', align: 'center' },
    { title: 'Registros', dataIndex: 'registros', align: 'center' },
    { title: 'Acciones', dataIndex: 'acciones', align: 'center', width: 80 },
];

const columnsIdes = [
    { title: 'N°', dataIndex: 'nro', width: 50, align: 'center' },
    { title: 'N° Lectura', dataIndex: 'camp2', align: 'center', width: 100 },
    { title: 'DNI', dataIndex: 'dni', align: 'center', width: 100 },
    { title: 'Aula', dataIndex: 'aula', width: 70, align: 'center' },
    { title: 'Tipo', dataIndex: 'tipo', width: 60, align: 'center' },
    { title: 'Litho', dataIndex: 'litho', align: 'center', width: 90 },
    { title: 'Calificar', dataIndex: 'estado', width: 80, align: 'center' },
    { title: 'Observaciones', dataIndex: 'observaciones' },
    { title: '', dataIndex: 'acciones', width: 60, align: 'center' },
];

const obsColor = (obs) => {
    const map = { 'Sin DNI': 'pink', 'Sin aula': 'purple', 'DNI erroneo': 'green', 'Sin tipo': 'yellow', 'No se calificará': 'red' };
    return map[obs] || 'default';
};

const getExamenes = async () => {
    try {
        const res = await axios.get('/api/calificacion/examenes', { params: { id_simulacro: props.proceso, paginasize: 100 } });
        examenes.value = res.data.data.data;
        if (examenes.value.length > 0) {
            idExamen.value = examenes.value[0].id;
            await getArchivos();
            await getIdes();
        }
    } catch (e) { console.error('Error al cargar exámenes:', e); }
};

const onExamenChange = async () => {
    await getArchivos();
    await getIdes();
};

const getArchivos = async () => {
    if (!idExamen.value) return;
    try {
        const res = await axios.get('/api/calificacion/lecturas/archivos', { params: { id_examen: idExamen.value, tipo: 'ide' } });
        archivos.value = res.data.data;
    } catch (e) { console.error('Error al cargar archivos:', e); }
};

const getIdes = async () => {
    if (!idExamen.value) return;
    try {
        const archivosIde = archivos.value;
        let allIdes = [];
        for (const arc of archivosIde) {
            const res = await axios.get(`/api/calificacion/lecturas/archivos/${arc.id}/ides`);
            allIdes = allIdes.concat(res.data.data);
        }
        identificaciones.value = allIdes;
    } catch (e) { console.error('Error al cargar ides:', e); }
};

const handleChange = (info) => {
    const status = info.file.status;
    if (status === 'done') {
        message.success(`${info.file.name} cargado correctamente.`);
        getArchivos();
        getIdes();
        visible.value = false;
    } else if (status === 'error') {
        message.error(`${info.file.name} falló al subir.`);
    }
};

const abrirCargar = () => { visible.value = true; showContextMenu.value = false; };
const abrirEditar = (item) => { ide.value = { ...item }; editar.value = true; };

const actualizarIde = async () => {
    try {
        await axios.post('/api/calificacion/lecturas/actualizar-ide', ide.value);
        message.success('Cambios guardados');
        editar.value = false;
        getIdes();
    } catch (e) { message.error('Error al guardar'); }
};

const eliminarArchivo = async (record) => {
    try {
        await axios.delete(`/api/calificacion/lecturas/archivos/${record.id}`);
        message.success('Archivo eliminado');
        getArchivos();
        getIdes();
    } catch (e) { message.error('Error al eliminar'); }
};

const verIdesArchivo = (record) => {
    tabPosition.value = 'contenido';
};

const descargarObservaciones = (pro) => {
    const url = `/api/calificacion/resultados/pdf-errores?id_proceso=${pro}`;
    window.open(url, '_blank');
};

const handleContextMenu = (event) => {
    showContextMenu.value = true;
    contextMenuTop.value = event.clientY;
    contextMenuLeft.value = event.clientX;
    event.preventDefault();
};

const clicIzquierdo = () => { showContextMenu.value = false; };
const handleDrop = (e) => { e.preventDefault(); };

let timeoutId;
watch(buscar, () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => getIdes(), 300);
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
