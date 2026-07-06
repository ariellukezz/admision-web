<template>
    <Head title="Asignaturas"/>
    <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="border-radius: 10px; width: 100%; min-height: calc(100vh - 95px);">

    <div class="flex justify-between mb-4">
        <div class="mr-3">
            <a-button type="primary" @click="abrirCrear" style="background: #476175; border: none; border-radius: 5px;">Nueva Asignatura</a-button>
        </div>
        <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; border-radius:6px; padding-left: 10px;">
            <template #prefix><search-outlined /></template>
        </a-input>
    </div>

    <a-table
        :columns="columns"
        :data-source="filtradas"
        rowKey="id"
        size="small"
        :pagination="false"
    >
        <template #bodyCell="{ column, index, record }">
            <template v-if="column.dataIndex === 'nro'">
                {{ index + 1 }}
            </template>
            <template v-if="column.dataIndex === 'estado'">
                <a-tag :color="record.estado ? 'green' : 'default'">{{ record.estado ? 'Activo' : 'Inactivo' }}</a-tag>
            </template>
            <template v-if="column.dataIndex === 'acciones'">
                <a-space>
                    <a-tooltip title="Editar">
                        <a-button type="text" size="small" @click="abrirEditar(record)">
                            <FormOutlined />
                        </a-button>
                    </a-tooltip>
                    <a-popconfirm title="¿Eliminar esta asignatura?" @confirm="eliminar(record)">
                        <a-button type="text" danger size="small">
                            <DeleteOutlined />
                        </a-button>
                    </a-popconfirm>
                </a-space>
            </template>
        </template>
    </a-table>

    </div>

    <a-modal
        v-model:open="modal"
        :title="form.id ? 'Editar Asignatura' : 'Nueva Asignatura'"
        @ok="guardar"
        :confirmLoading="saving"
    >
        <a-form layout="vertical" class="mt-4">
            <a-form-item label="Nombre" required>
                <a-input v-model:value="form.nombre" placeholder="Ej: Aritmética" />
            </a-form-item>
            <a-form-item label="Orden">
                <a-input-number v-model:value="form.orden" :min="0" style="width: 100%" />
            </a-form-item>
            <a-form-item label="Estado">
                <a-switch v-model:checked="form.estado" checked-children="Activo" un-checked-children="Inactivo" />
            </a-form-item>
        </a-form>
    </a-modal>

    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutCalificador.vue';
import { ref, reactive, computed, onMounted } from 'vue';
import { FormOutlined, DeleteOutlined, SearchOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import axios from 'axios';

const saving = ref(false);
const buscar = ref('');
const modal = ref(false);
const asignaturas = ref([]);

const form = reactive({
    id: null,
    nombre: '',
    orden: 0,
    estado: true,
});

const columns = [
    { title: 'N°', dataIndex: 'nro', width: 50 },
    { title: 'Nombre', dataIndex: 'nombre' },
    { title: 'Orden', dataIndex: 'orden', width: 80 },
    { title: 'Estado', dataIndex: 'estado', width: 80 },
    { title: 'Acciones', dataIndex: 'acciones', width: 120, align: 'center' },
];

const filtradas = computed(() => {
    if (!buscar.value) return asignaturas.value;
    const q = buscar.value.toLowerCase();
    return asignaturas.value.filter(a => a.nombre.toLowerCase().includes(q));
});

const getAsignaturas = async () => {
    const res = await axios.get('/calificacion/asignaturas-list');
    asignaturas.value = res.data.datos;
};

const abrirCrear = () => {
    Object.assign(form, { id: null, nombre: '', orden: 0, estado: true });
    modal.value = true;
};

const abrirEditar = (record) => {
    Object.assign(form, {
        id: record.id,
        nombre: record.nombre,
        orden: record.orden,
        estado: record.estado,
    });
    modal.value = true;
};

const guardar = async () => {
    if (!form.nombre.trim()) {
        message.warning('Ingrese un nombre');
        return;
    }
    saving.value = true;
    try {
        if (form.id) {
            await axios.put('/calificacion/asignaturas/' + form.id, form);
            message.success('Actualizado correctamente');
        } else {
            await axios.post('/calificacion/asignaturas', form);
            message.success('Creado correctamente');
        }
        modal.value = false;
        await getAsignaturas();
    } catch (e) {
        message.error('Error: ' + (e.response?.data?.message || e.message));
    } finally {
        saving.value = false;
    }
};

const eliminar = async (record) => {
    try {
        await axios.delete('/calificacion/asignaturas/' + record.id);
        message.success('Eliminado correctamente');
        await getAsignaturas();
    } catch (e) {
        message.error('Error al eliminar');
    }
};

onMounted(() => {
    getAsignaturas();
});
</script>
