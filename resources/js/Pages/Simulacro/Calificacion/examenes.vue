<template>
    <Head title="Exámenes"/>
    <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="border-radius: 10px; width: 100%; min-height: calc(100vh - 95px);">

    <div class="flex justify-between mb-4">
        <div class="mr-3">
            <a-button type="primary" @click="abrirCrear" style="background: #476175; border: none; border-radius: 5px;">Nuevo Examen</a-button>
        </div>
        <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; border-radius:6px; padding-left: 10px;">
            <template #prefix><search-outlined /></template>
        </a-input>
    </div>

    <a-table
        :columns="columns"
        :data-source="filtrados"
        rowKey="id"
        size="small"
        :pagination="{ pageSize: 10 }"
    >
        <template #bodyCell="{ column, index, record }">
            <template v-if="column.dataIndex === 'nro'">
                {{ index + 1 }}
            </template>
            <template v-if="column.dataIndex === 'n_preguntas'">
                {{ record.n_preguntas }} preg / {{ record.n_alternativas }} alt
            </template>
            <template v-if="column.dataIndex === 'tipos_count'">
                <a-tag color="cyan">{{ record.tipos_count }} tipo(s)</a-tag>
            </template>
            <template v-if="column.dataIndex === 'estado'">
                <a-tag :color="record.estado ? 'green' : 'default'">{{ record.estado ? 'Activo' : 'Inactivo' }}</a-tag>
            </template>
            <template v-if="column.dataIndex === 'acciones'">
                <a-space>
                    <a-tooltip title="Gestionar Tipos">
                        <a-button type="text" size="small" @click="irTipos(record)">
                            <BarsOutlined />
                        </a-button>
                    </a-tooltip>
                    <a-tooltip title="Editar">
                        <a-button type="text" size="small" @click="abrirEditar(record)">
                            <FormOutlined />
                        </a-button>
                    </a-tooltip>
                    <a-popconfirm title="¿Eliminar este examen y todo su contenido?" @confirm="eliminar(record)">
                        <a-button type="text" danger size="small">
                            <DeleteOutlined />
                        </a-button>
                    </a-popconfirm>
                </a-space>
            </template>
        </template>
    </a-table>

    <!-- Modal crear/editar examen -->
    <a-modal
        v-model:open="modal"
        :title="form.id ? 'Editar Examen' : 'Nuevo Examen'"
        @ok="guardar"
        :confirmLoading="saving"
    >
        <a-form layout="vertical" class="mt-4">
            <a-form-item label="Área / Examen" required>
                <a-input v-model:value="form.area" placeholder="Ej: BIOMÉDICAS" />
            </a-form-item>
            <div style="display:flex; gap:16px;">
                <a-form-item label="N° Preguntas" style="flex:1">
                    <a-input-number v-model:value="form.n_preguntas" :min="1" style="width: 100%" />
                </a-form-item>
                <a-form-item label="N° Alternativas" style="flex:1">
                    <a-input-number v-model:value="form.n_alternativas" :min="1" style="width: 100%" />
                </a-form-item>
            </div>
            <a-form-item label="Estado">
                <a-switch v-model:checked="form.estado" :checked-value="1" :un-checked-value="0" checked-children="Activo" un-checked-children="Inactivo" />
            </a-form-item>
        </a-form>
    </a-modal>

    </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutCalificador.vue';
import { ref, reactive, computed, onMounted } from 'vue';
import { FormOutlined, DeleteOutlined, SearchOutlined, BarsOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import axios from 'axios';

const saving = ref(false);
const buscar = ref('');
const modal = ref(false);
const examenes = ref([]);

const form = reactive({
    id: null,
    area: '',
    n_preguntas: 60,
    n_alternativas: 5,
    estado: 1,
});

const columns = [
    { title: 'N°', dataIndex: 'nro', width: 50 },
    { title: 'Examen', dataIndex: 'area' },
    { title: 'Config', dataIndex: 'n_preguntas', width: 120 },
    { title: 'Tipos', dataIndex: 'tipos_count', width: 90, align: 'center' },
    { title: 'Estado', dataIndex: 'estado', width: 80 },
    { title: 'Acciones', dataIndex: 'acciones', width: 140, align: 'center' },
];

const filtrados = computed(() => {
    if (!buscar.value) return examenes.value;
    const q = buscar.value.toLowerCase();
    return examenes.value.filter(e => (e.area || '').toLowerCase().includes(q));
});

const getExamenes = async () => {
    const res = await axios.get('/calificacion/examenes-list');
    examenes.value = res.data.datos.data;
};

const abrirCrear = () => {
    Object.assign(form, { id: null, area: '', n_preguntas: 60, n_alternativas: 5, estado: 1 });
    modal.value = true;
};

const abrirEditar = (record) => {
    Object.assign(form, {
        id: record.id,
        area: record.area,
        n_preguntas: record.n_preguntas,
        n_alternativas: record.n_alternativas,
        estado: record.estado,
    });
    modal.value = true;
};

const guardar = async () => {
    if (!form.area.trim()) { message.warning('Ingrese un nombre de examen'); return; }
    saving.value = true;
    try {
        const payload = { ...form };
        if (form.id) {
            await axios.put('/calificacion/examenes/' + form.id, payload);
            message.success('Actualizado correctamente');
        } else {
            await axios.post('/calificacion/examenes', payload);
            message.success('Creado correctamente');
        }
        modal.value = false;
        await getExamenes();
    } catch (e) {
        message.error('Error: ' + (e.response?.data?.message || e.message));
    } finally { saving.value = false; }
};

const eliminar = async (record) => {
    try {
        await axios.delete('/calificacion/examenes/' + record.id);
        message.success('Eliminado correctamente');
        await getExamenes();
    } catch (e) { message.error('Error al eliminar'); }
};

const irTipos = (record) => {
    router.visit('/calificacion/examenes/' + record.id + '/tipos');
};

onMounted(() => {
    getExamenes();
});
</script>
