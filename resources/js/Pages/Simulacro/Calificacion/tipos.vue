<template>
    <Head :title="'Tipos: ' + (examen?.area || '')" />
    <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="border-radius: 10px; width: 100%; min-height: calc(100vh - 95px);">

    <div class="flex justify-between items-center mb-4">
        <a-space>
            <a-button type="text" @click="volver">
                <ArrowLeftOutlined />
                Volver
            </a-button>
            <h2 style="margin: 0; font-size: 18px;">Tipos: {{ examen?.area }}</h2>
        </a-space>
        <a-space>
            <a-button type="primary" @click="abrirAgregarTipo" style="background: #476175; border: none; border-radius: 5px;">
                <PlusOutlined />
                Agregar Tipo
            </a-button>
            <a-button @click="abrirCargarArchivo">
                <UploadOutlined />
                Cargar desde Archivo
            </a-button>
            <a-button @click="abrirSubirRes" style="background: #476175; border: none; border-radius: 5px;">
                <UploadOutlined />
                Cargar RES
            </a-button>
        </a-space>
    </div>

    <a-table
        :columns="tipoColumns"
        :data-source="tipos"
        rowKey="id"
        size="small"
        :pagination="false"
        :loading="loadingTipos"
    >
        <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'tipo'">
                <a-tag color="blue">{{ record.tipo || 'ÚNICO' }}</a-tag>
            </template>
            <template v-if="column.dataIndex === 'respuestas'">
                <span style="font-family: monospace; font-size: 12px;">{{ record.respuestas || '(sin clave)' }}</span>
            </template>
            <template v-if="column.dataIndex === 'archivo'">
                <a-tag v-if="record.archivo" color="purple" style="cursor: pointer;" @click="verArchivo(record)">
                    {{ record.archivo.nombre }}
                </a-tag>
                <span v-else style="color: #999;">—</span>
            </template>
            <template v-if="column.dataIndex === 'res_count'">
                <a-tag :color="record.res_count > 0 ? 'green' : 'default'">{{ record.res_count }} res</a-tag>
            </template>
            <template v-if="column.dataIndex === 'excepciones_count'">
                <a-tag color="orange">{{ record.excepciones_count }} exc.</a-tag>
            </template>
            <template v-if="column.dataIndex === 'acciones'">
                <a-space>
                    <a-tooltip title="Editar respuestas">
                        <a-button type="text" size="small" @click="editarTipoResp(record)">
                            <EditOutlined />
                        </a-button>
                    </a-tooltip>
                    <a-tooltip title="Ver RES">
                        <a-button type="text" size="small" @click="abrirRes(record)">
                            <EyeOutlined />
                        </a-button>
                    </a-tooltip>
                    <a-tooltip title="Gestionar Excepciones">
                        <a-button type="text" size="small" @click="abrirExcepciones(record)">
                            <WarningOutlined />
                        </a-button>
                    </a-tooltip>
                    <a-popconfirm title="¿Eliminar tipo?" @confirm="eliminarTipo(record)">
                        <a-button type="text" danger size="small">
                            <DeleteOutlined />
                        </a-button>
                    </a-popconfirm>
                </a-space>
            </template>
        </template>
    </a-table>

    <!-- Modal editar respuestas del tipo -->
    <a-modal
        v-model:open="modalEditarResp"
        title="Editar Respuestas del Tipo"
        @ok="guardarTipoResp"
        :confirmLoading="savingTipo"
    >
        <a-form layout="vertical" class="mt-4">
            <a-form-item label="Tipo">
                <a-input :value="tipoEditando?.tipo || 'ÚNICO'" disabled />
            </a-form-item>
            <a-form-item label="Respuestas (clave de respuestas)">
                <a-textarea v-model:value="respuestasEdit" :rows="4" placeholder="ADCBDEBCAED..." style="font-family: monospace;" />
            </a-form-item>
        </a-form>
    </a-modal>

    <!-- Modal cargar RES -->
    <a-modal
        v-model:open="modalRes"
        :title="'Cargar RES — ' + (examen?.area || '')"
        :footer="false"
        width="700px"
    >
        <div class="mt-4">
            <a-upload-dragger
                :action="'/calificacion/examenes-res/' + (tipoActual?.id || 0)"
                name="file"
                :multiple="true"
                accept=".txt,.dat"
                @change="handleResUpload"
            >
                <p class="ant-upload-drag-icon"><UploadOutlined /></p>
                <p class="ant-upload-text">Arrastra archivos RES aquí o click para seleccionar</p>
                <p class="ant-upload-hint">Formatos: .txt, .dat — Fichas de respuestas escaneadas</p>
            </a-upload-dragger>
        </div>
    </a-modal>

    <!-- Modal ver RES de un tipo -->
    <a-modal
        v-model:open="modalVerRes"
        :title="'RES: Tipo ' + (tipoActual?.tipo || 'ÚNICO')"
        :footer="false"
        width="700px"
    >
        <div class="mt-4">
            <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <a-tag color="green">{{ tipoActual?.res_count || 0 }} registros</a-tag>
                <a-popconfirm
                    v-if="tipoActual?.res_count > 0"
                    title="¿Eliminar todos los registros RES de este tipo?"
                    @confirm="limpiarRes"
                >
                    <a-button danger size="small">Limpiar RES</a-button>
                </a-popconfirm>
            </div>

            <a-table
                v-if="resData.length > 0"
                :columns="resColumns"
                :data-source="resData"
                rowKey="id"
                size="small"
                :pagination="{ pageSize: 5 }"
            />
        </div>
    </a-modal>

    <!-- Modal gestionar excepciones -->
    <a-modal
        v-model:open="modalExcepciones"
        :title="'Excepciones: Tipo ' + (tipoActual?.tipo || 'ÚNICO')"
        :footer="false"
        width="700px"
    >
        <div class="mt-4">
            <div style="display:flex; gap:8px; margin-bottom: 16px; flex-wrap: wrap;">
                <a-input-number v-model:value="nuevaExc.nro_pregunta" :min="1" placeholder="N°" style="width: 70px" />
                <a-select v-model:value="nuevaExc.accion" style="width: 180px" placeholder="Acción">
                    <a-select-option value="todas_validas">Todas válidas</a-select-option>
                    <a-select-option value="multiples_validas">Múltiples válidas</a-select-option>
                    <a-select-option value="anulada">Anulada</a-select-option>
                    <a-select-option value="asignar_puntaje">Asignar puntaje</a-select-option>
                </a-select>
                <a-input v-model:value="nuevaExc.claves_validas" placeholder="Claves (Ej: A,B,C)" style="width: 120px" />
                <a-button type="primary" @click="agregarExcepcion" style="background: #476175; border: none;">Agregar</a-button>
            </div>

            <a-table
                :columns="excColumns"
                :data-source="excepciones"
                rowKey="id"
                size="small"
                :pagination="false"
            >
                <template #bodyCell="{ column, record }">
                    <template v-if="column.dataIndex === 'accion'">
                        <a-tag :color="excColor(record.accion)">{{ excLabel(record.accion) }}</a-tag>
                    </template>
                    <template v-if="column.dataIndex === 'acciones'">
                        <a-popconfirm title="¿Eliminar excepción?" @confirm="eliminarExcepcion(record)">
                            <a-button type="text" danger size="small">
                                <DeleteOutlined />
                            </a-button>
                        </a-popconfirm>
                    </template>
                </template>
            </a-table>
        </div>
    </a-modal>

    <!-- Modal agregar tipo manualmente -->
    <a-modal
        v-model:open="modalAgregarTipo"
        title="Agregar Tipo"
        @ok="guardarNuevoTipo"
        :confirmLoading="savingTipo"
    >
        <a-form layout="vertical" class="mt-4">
            <a-form-item label="Tipo (1 letra)">
                <a-input v-model:value="nuevoTipo.tipo" maxlength="1" placeholder="Ej: P, Q, R..." style="width: 80px" />
            </a-form-item>
            <a-form-item label="Respuestas (clave de respuestas)">
                <a-textarea v-model:value="nuevoTipo.respuestas" :rows="4" placeholder="ADCBDEBCAED..." style="font-family: monospace;" />
            </a-form-item>
        </a-form>
    </a-modal>

    <!-- Modal cargar tipos desde archivo -->
    <a-modal
        v-model:open="modalCargarArchivo"
        :title="'Cargar Tipos desde Archivo — ' + (examen?.area || '')"
        :footer="false"
        width="700px"
    >
        <div class="mt-4">
            <a-upload-dragger
                :action="'/calificacion/examenes-tipos-archivo/' + (examen?.id || 0)"
                name="file"
                accept=".txt,.dat"
                @change="handleTiposArchivoUpload"
            >
                <p class="ant-upload-drag-icon"><UploadOutlined /></p>
                <p class="ant-upload-text">Arrastra archivos aquí o click para seleccionar</p>
                <p class="ant-upload-hint">Formatos: .txt, .dat — Crea tipos agrupados por letra inicial</p>
            </a-upload-dragger>
        </div>
    </a-modal>

    <!-- Modal ver archivo -->
    <a-modal
        v-model:open="modalVerArchivo"
        :title="'Archivo: ' + (archivoData.nombre || '')"
        :footer="false"
        width="800px"
    >
        <div class="mt-4">
            <pre style="background: #f5f5f5; padding: 16px; border-radius: 8px; overflow: auto; max-height: 500px; font-size: 12px; font-family: monospace; white-space: pre-wrap; word-break: break-all;">{{ archivoData.contenido || 'Cargando...' }}</pre>
        </div>
    </a-modal>

    </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutCalificador.vue';
import { ref, reactive, onMounted } from 'vue';
import { ArrowLeftOutlined, EditOutlined, DeleteOutlined, UploadOutlined, WarningOutlined, EyeOutlined, PlusOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import axios from 'axios';

const props = defineProps({
    examen: Object,
});

const loadingTipos = ref(false);
const savingTipo = ref(false);
const tipos = ref([]);
const tipoActual = ref(null);
const tipoEditando = ref(null);
const respuestasEdit = ref('');
const excepciones = ref([]);
const resData = ref([]);

const modalEditarResp = ref(false);
const modalRes = ref(false);
const modalVerRes = ref(false);
const modalExcepciones = ref(false);
const modalAgregarTipo = ref(false);
const modalCargarArchivo = ref(false);
const modalVerArchivo = ref(false);

const nuevoTipo = reactive({
    tipo: '',
    respuestas: '',
});

const archivoData = reactive({
    nombre: '',
    contenido: '',
});

const nuevaExc = reactive({
    nro_pregunta: 1,
    accion: 'todas_validas',
    claves_validas: '',
});

const tipoColumns = [
    { title: 'Tipo', dataIndex: 'tipo', width: 80, align: 'center' },
    { title: 'Respuestas', dataIndex: 'respuestas' },
    { title: 'Archivo', dataIndex: 'archivo', width: 180 },
    { title: 'RES', dataIndex: 'res_count', width: 80, align: 'center' },
    { title: 'Exc.', dataIndex: 'excepciones_count', width: 80, align: 'center' },
    { title: 'Acciones', dataIndex: 'acciones', width: 200, align: 'center' },
];

const excColumns = [
    { title: 'Pregunta', dataIndex: 'nro_pregunta', width: 80, align: 'center' },
    { title: 'Acción', dataIndex: 'accion', width: 150 },
    { title: 'Claves', dataIndex: 'claves_validas', width: 100 },
    { title: '', dataIndex: 'acciones', width: 60, align: 'center' },
];

const resColumns = [
    { title: 'Tipo', dataIndex: 'tipo', width: 60, align: 'center' },
    { title: 'Litho', dataIndex: 'litho', width: 100 },
    { title: 'Respuestas', dataIndex: 'respuestas', ellipsis: true },
    { title: 'Puntaje', dataIndex: 'puntaje', width: 90, align: 'center' },
];

const excLabel = (a) => ({ todas_validas: 'Todas válidas', multiples_validas: 'Múltiples válidas', anulada: 'Anulada', asignar_puntaje: 'Asignar puntaje' }[a] || a);
const excColor = (a) => ({ todas_validas: 'green', multiples_validas: 'blue', anulada: 'red', asignar_puntaje: 'orange' }[a] || 'default');

const volver = () => router.visit('/calificacion/examenes');

const getTipos = async () => {
    loadingTipos.value = true;
    try {
        const res = await axios.get('/calificacion/examenes-tipos/' + props.examen.id);
        tipos.value = res.data.datos;
    } catch (e) {
        message.error('Error al cargar tipos');
    } finally {
        loadingTipos.value = false;
    }
};

const editarTipoResp = (record) => {
    tipoEditando.value = record;
    respuestasEdit.value = record.respuestas || '';
    modalEditarResp.value = true;
};

const guardarTipoResp = async () => {
    savingTipo.value = true;
    try {
        await axios.post('/calificacion/examenes-tipos', {
            id: tipoEditando.value.id,
            id_examen_simulacro: props.examen.id,
            tipo: tipoEditando.value.tipo,
            respuestas: respuestasEdit.value,
        });
        message.success('Respuestas guardadas');
        modalEditarResp.value = false;
        await getTipos();
    } catch (e) {
        message.error('Error: ' + (e.response?.data?.message || e.message));
    } finally {
        savingTipo.value = false;
    }
};

const eliminarTipo = async (record) => {
    try {
        await axios.delete('/calificacion/examenes-tipos/' + record.id);
        message.success('Tipo eliminado');
        await getTipos();
    } catch (e) {
        message.error('Error al eliminar tipo');
    }
};

// --- AGREGAR TIPO MANUAL ---

const abrirAgregarTipo = () => {
    Object.assign(nuevoTipo, { tipo: '', respuestas: '' });
    modalAgregarTipo.value = true;
};

const guardarNuevoTipo = async () => {
    savingTipo.value = true;
    try {
        await axios.post('/calificacion/examenes-tipos', {
            id_examen_simulacro: props.examen.id,
            tipo: nuevoTipo.tipo || null,
            respuestas: nuevoTipo.respuestas || null,
        });
        message.success('Tipo creado correctamente');
        modalAgregarTipo.value = false;
        await getTipos();
    } catch (e) {
        message.error('Error: ' + (e.response?.data?.message || e.message));
    } finally {
        savingTipo.value = false;
    }
};

// --- CARGAR TIPOS DESDE ARCHIVO ---

const abrirCargarArchivo = () => {
    modalCargarArchivo.value = true;
};

const handleTiposArchivoUpload = async (info) => {
    const { status, response } = info.file;
    if (status === 'done') {
        message.success(response.mensaje || 'Tipos cargados');
        await getTipos();
        modalCargarArchivo.value = false;
    } else if (status === 'error') {
        message.error('Error al cargar archivo');
    }
};

// --- VER ARCHIVO ---

const verArchivo = async (record) => {
    if (!record.archivo) return;
    modalVerArchivo.value = true;
    Object.assign(archivoData, { nombre: record.archivo.nombre, contenido: '' });
    try {
        const res = await axios.get('/calificacion/examenes-archivo/' + record.archivo.id);
        archivoData.contenido = res.data.datos.contenido;
    } catch (e) {
        archivoData.contenido = 'Error al cargar el archivo';
    }
};

// --- RES ---

const abrirSubirRes = async () => {
    // Ensure tipos are loaded so we can upload to the first/default tipo
    if (tipos.value.length === 0) {
        message.warning('Primero crea un tipo o carga un archivo RES');
        return;
    }
    // Upload to the first tipo (it will be updated with tipo/respuestas from file)
    tipoActual.value = tipos.value[0];
    modalRes.value = true;
};

const abrirRes = async (record) => {
    tipoActual.value = record;
    modalVerRes.value = true;
    await getRes(record.id);
};

const getRes = async (idTipo) => {
    const res = await axios.get('/calificacion/examenes-res/' + idTipo);
    resData.value = res.data.datos.data;
};

const handleResUpload = async (info) => {
    const { status, response } = info.file;
    if (status === 'done') {
        message.success(response.mensaje || 'Archivo cargado');
        await getTipos();
        modalRes.value = false;
    } else if (status === 'error') {
        message.error('Error al cargar archivo');
    }
};

const limpiarRes = async () => {
    try {
        await axios.delete('/calificacion/examenes-res/' + tipoActual.value.id);
        message.success('Registros RES eliminados');
        await getTipos();
        await getRes(tipoActual.value.id);
        const t = tipos.value.find(t => t.id === tipoActual.value.id);
        if (t) tipoActual.value = t;
    } catch (e) {
        message.error('Error al limpiar');
    }
};

// --- EXCEPCIONES ---

const abrirExcepciones = async (record) => {
    tipoActual.value = record;
    modalExcepciones.value = true;
    await getExcepciones(record.id);
};

const getExcepciones = async (idTipo) => {
    const res = await axios.get('/calificacion/examenes-excepciones/' + idTipo);
    excepciones.value = res.data.datos;
};

const agregarExcepcion = async () => {
    try {
        await axios.post('/calificacion/examenes-excepciones', {
            id_examen_tipo: tipoActual.value.id,
            nro_pregunta: nuevaExc.nro_pregunta,
            accion: nuevaExc.accion,
            claves_validas: nuevaExc.claves_validas,
        });
        message.success('Excepción agregada');
        Object.assign(nuevaExc, { nro_pregunta: 1, accion: 'todas_validas', claves_validas: '' });
        await getExcepciones(tipoActual.value.id);
        await getTipos();
    } catch (e) {
        message.error('Error: ' + (e.response?.data?.message || e.message));
    }
};

const eliminarExcepcion = async (record) => {
    try {
        await axios.delete('/calificacion/examenes-excepciones/' + record.id);
        message.success('Excepción eliminada');
        await getExcepciones(tipoActual.value.id);
        await getTipos();
    } catch (e) {
        message.error('Error al eliminar excepción');
    }
};

onMounted(() => {
    getTipos();
});
</script>
