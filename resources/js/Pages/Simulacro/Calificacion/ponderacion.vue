<template>
    <Head title="Ponderación"/>
    <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="border-radius: 10px; width: 100%; min-height: calc(100vh - 95px);">

    <div class="flex justify-between mb-4">
        <div class="mr-3">
            <a-button type="primary" @click="abrirCrear" style="background: #476175; border: none; border-radius: 5px;">Nueva Ponderación</a-button>
        </div>
        <div class="flex justify-between" style="position: relative;">
            <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; border-radius:6px; padding-left: 10px;">
                <template #prefix><search-outlined /></template>
            </a-input>
        </div>
    </div>

    <a-table
        :columns="columns"
        :data-source="ponderaciones"
        rowKey="id"
        size="small"
        :pagination="false"
    >
        <template #bodyCell="{ column, index, record }">
            <template v-if="column.dataIndex === 'nro'">
                {{ index + 1 }}
            </template>
            <template v-if="column.dataIndex === 'totales'">
                <div class="text-xs">
                    <span>{{ record.total_preguntas }} preg.</span> ·
                    <span>{{ formatNum(record.total_ponderacion) }}</span>
                </div>
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
                    <a-tooltip title="Detalle de asignaturas">
                        <a-button type="text" size="small" @click="abrirDetalle(record)">
                            <EyeOutlined />
                        </a-button>
                    </a-tooltip>
                    <a-tooltip title="Duplicar">
                        <a-button type="text" size="small" @click="duplicar(record)">
                            <CopyOutlined />
                        </a-button>
                    </a-tooltip>
                    <a-popconfirm title="¿Eliminar esta ponderación?" @confirm="eliminar(record)">
                        <a-button type="text" danger size="small">
                            <DeleteOutlined />
                        </a-button>
                    </a-popconfirm>
                </a-space>
            </template>
        </template>
    </a-table>

    <div class="flex justify-between mt-4">
        <a-pagination
            v-model:current="pagina"
            simple
            :total="totalRegistros"
            v-model:pageSize="paginasize"
            show-less-items
        />
        <a-select v-model:value="paginasize" style="width: 90px;">
            <a-select-option :value="10">10 Reg.</a-select-option>
            <a-select-option :value="20">20 Reg.</a-select-option>
            <a-select-option :value="50">50 Reg.</a-select-option>
        </a-select>
    </div>

    </div>

    <!-- MODAL CREAR/EDITAR CABECERA -->
    <a-modal
        v-model:open="modalCabecera"
        :title="form.id ? 'Editar Ponderación' : 'Nueva Ponderación'"
        @ok="guardarCabecera"
        :confirmLoading="saving"
    >
        <a-form layout="vertical" class="mt-4">
            <a-form-item label="Nombre" required>
                <a-input v-model:value="form.nombre" placeholder="Ej: CEPREUNA 2026 - Biomédicas" />
            </a-form-item>
            <a-form-item label="Estado">
                <a-switch v-model:checked="form.estado" checked-children="Activo" un-checked-children="Inactivo" />
            </a-form-item>
        </a-form>
    </a-modal>

    <!-- MODAL DETALLE (ASIGNATURAS) -->
    <a-modal
        v-model:open="modalDetalle"
        :title="`Detalle: ${ponderacionSeleccionada?.nombre || ''}`"
        :width="1000"
        :footer="null"
        centered
    >
        <div class="mb-4 flex justify-between items-center">
            <div class="text-sm text-gray-500">
                Ponderación = cantidad_preguntas × ponderación
            </div>
            <a-button type="primary" size="small" @click="guardarDetalle" :loading="savingDetalle">
                Guardar Detalle
            </a-button>
        </div>

        <a-table
            :dataSource="detalle"
            :columns="detalleColumns"
            rowKey="numero"
            size="small"
            :pagination="false"
            :scroll="{ y: 400 }"
        >
            <template #bodyCell="{ column, record, index }">
                <template v-if="column.dataIndex === 'numero'">
                    {{ index + 1 }}
                </template>
                <template v-if="column.dataIndex === 'asignatura'">
                    <a-select
                        v-model:value="record.id_asignatura"
                        style="width: 100%"
                        show-search
                        :filter-option="filterOption"
                        @change="(val) => onAsignaturaChange(index, val)"
                    >
                        <a-select-option v-for="a in asignaturas" :key="a.id" :value="a.id">{{ a.nombre }}</a-select-option>
                    </a-select>
                </template>
                <template v-if="column.dataIndex === 'cantidad_preguntas'">
                    <a-input-number v-model:value="record.cantidad_preguntas" :min="0" :max="60" style="width: 80px" @change="calcSubtotal(index)" />
                </template>
                <template v-if="column.dataIndex === 'ponderacion'">
                    <a-input-number v-model:value="record.ponderacion" :step="0.001" :precision="3" :min="0" style="width: 120px" @change="calcSubtotal(index)" />
                </template>
                <template v-if="column.dataIndex === 'subtotal'">
                    <span class="font-medium">{{ formatNum(record.subtotal) }}</span>
                </template>
                <template v-if="column.dataIndex === 'acciones'">
                    <a-button type="text" danger size="small" @click="eliminarFila(index)">
                        <DeleteOutlined />
                    </a-button>
                </template>
            </template>

            <template #footer>
                <div class="flex justify-between items-center">
                    <div class="flex gap-2">
                        <a-button size="small" @click="agregarFila">+ Agregar asignatura</a-button>
                        <a-button size="small" type="dashed" @click="cargarPlantilla">Cargar 18 asignaturas</a-button>
                    </div>
                    <div class="text-right">
                        <div>Total preguntas: <span class="font-bold" :class="totalPreguntas === 60 ? 'text-green-600' : 'text-red-600'">{{ totalPreguntas }}</span></div>
                        <div>Total ponderación: <span class="font-bold">{{ formatNum(totalPonderacion) }}</span></div>
                    </div>
                </div>
            </template>
        </a-table>
    </a-modal>

    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutCalificador.vue';
import { watch, ref, reactive, computed, onMounted } from 'vue';
import { FormOutlined, DeleteOutlined, SearchOutlined, EyeOutlined, CopyOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import axios from 'axios';

const saving = ref(false);
const savingDetalle = ref(false);
const buscar = ref('');
const pagina = ref(1);
const paginasize = ref(10);
const totalRegistros = ref(0);

const ponderaciones = ref([]);
const asignaturas = ref([]);

const modalCabecera = ref(false);
const modalDetalle = ref(false);
const ponderacionSeleccionada = ref(null);

const form = reactive({
    id: null,
    nombre: '',
    estado: true,
});

const detalle = ref([]);

const columns = [
    { title: 'N°', dataIndex: 'nro', width: 50 },
    { title: 'Nombre', dataIndex: 'nombre' },
    { title: 'Totales', dataIndex: 'totales', width: 160 },
    { title: 'Estado', dataIndex: 'estado', width: 80 },
    { title: 'Acciones', dataIndex: 'acciones', width: 180, align: 'center' },
];

const detalleColumns = [
    { title: '#', dataIndex: 'numero', width: 40 },
    { title: 'Asignatura', dataIndex: 'asignatura' },
    { title: 'Cant. Preg.', dataIndex: 'cantidad_preguntas', width: 100 },
    { title: 'Ponderación', dataIndex: 'ponderacion', width: 130 },
    { title: 'Subtotal', dataIndex: 'subtotal', width: 120 },
    { title: '', dataIndex: 'acciones', width: 50 },
];

const totalPreguntas = computed(() => detalle.value.reduce((s, d) => s + (Number(d.cantidad_preguntas) || 0), 0));
const totalPonderacion = computed(() => detalle.value.reduce((s, d) => s + (Number(d.subtotal) || 0), 0));

const filterOption = (input, option) => {
    return option.children?.[0]?.children?.toLowerCase?.().includes(input.toLowerCase());
};

const formatNum = (n) => Number(n || 0).toFixed(2);

const getPonderaciones = async () => {
    const res = await axios.get('/api/calificacion/ponderaciones', {
        params: { term: buscar.value, paginasize: paginasize.value, page: pagina.value }
    });
    ponderaciones.value = res.data.data.data;
    totalRegistros.value = res.data.data.total;
};

let timeoutId;
watch(buscar, (val) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(getPonderaciones, 400);
});
watch(pagina, getPonderaciones);
watch(paginasize, getPonderaciones);

const getAsignaturas = async () => {
    const res = await axios.get('/api/calificacion/asignaturas');
    asignaturas.value = res.data.data;
};

const abrirCrear = () => {
    Object.assign(form, { id: null, nombre: '', estado: true });
    modalCabecera.value = true;
};

const abrirEditar = (record) => {
    Object.assign(form, {
        id: record.id,
        nombre: record.nombre,
        estado: record.estado,
    });
    modalCabecera.value = true;
};

const guardarCabecera = async () => {
    if (!form.nombre.trim()) {
        message.warning('Ingrese un nombre');
        return;
    }
    saving.value = true;
    try {
        const res = await axios.post('/api/calificacion/ponderaciones', form);
        message.success(res.data.message || 'Guardado correctamente');
        modalCabecera.value = false;
        await getPonderaciones();
        if (!form.id && res.data.data?.id) {
            form.id = res.data.data.id;
            ponderacionSeleccionada.value = res.data.data;
            await abrirDetalle(res.data.data);
        }
    } catch (e) {
        message.error('Error: ' + (e.response?.data?.message || e.message));
    } finally {
        saving.value = false;
    }
};

const abrirDetalle = async (record) => {
    ponderacionSeleccionada.value = record;
    modalDetalle.value = true;
    try {
        const res = await axios.get('/api/calificacion/ponderaciones/detalle/' + record.id);
        if (res.data.data && res.data.data.length > 0) {
            detalle.value = res.data.data.map((d) => ({
                numero: d.numero,
                id_asignatura: d.id_asignatura,
                asignatura: d.asignatura,
                cantidad_preguntas: d.cantidad_preguntas,
                ponderacion: Number(d.ponderacion),
                subtotal: Number(d.subtotal),
            }));
        } else {
            cargarPlantilla();
        }
    } catch (e) {
        cargarPlantilla();
    }
};

const cargarPlantilla = () => {
    detalle.value = asignaturas.value.map((a, i) => ({
        numero: i + 1,
        id_asignatura: a.id,
        asignatura: a.nombre,
        cantidad_preguntas: 0,
        ponderacion: 0,
        subtotal: 0,
    }));
};

const agregarFila = () => {
    detalle.value.push({
        numero: detalle.value.length + 1,
        id_asignatura: null,
        asignatura: '',
        cantidad_preguntas: 0,
        ponderacion: 0,
        subtotal: 0,
    });
};

const eliminarFila = (index) => {
    detalle.value.splice(index, 1);
    detalle.value.forEach((d, i) => d.numero = i + 1);
};

const onAsignaturaChange = (index, val) => {
    const asig = asignaturas.value.find(a => a.id === val);
    if (asig) detalle.value[index].asignatura = asig.nombre;
    calcSubtotal(index);
};

const calcSubtotal = (index) => {
    const d = detalle.value[index];
    if (!d) return;
    d.subtotal = (Number(d.cantidad_preguntas) || 0) * (Number(d.ponderacion) || 0);
};

const guardarDetalle = async () => {
    savingDetalle.value = true;
    try {
        const res = await axios.post('/api/calificacion/ponderaciones/detalle', {
            id_ponderacion: ponderacionSeleccionada.value.id,
            detalles: detalle.value,
        });
        message.success(res.data.message || 'Detalle guardado');
        await getPonderaciones();
    } catch (e) {
        message.error('Error: ' + (e.response?.data?.error || e.message));
    } finally {
        savingDetalle.value = false;
    }
};

const duplicar = async (record) => {
    try {
        const res = await axios.post('/api/calificacion/ponderaciones/' + record.id + '/duplicar');
        message.success('Ponderación duplicada');
        await getPonderaciones();
    } catch (e) {
        message.error('Error al duplicar');
    }
};

const eliminar = async (record) => {
    try {
        await axios.delete('/api/calificacion/ponderaciones/' + record.id);
        message.success('Ponderación eliminada');
        await getPonderaciones();
    } catch (e) {
        message.error('Error al eliminar');
    }
};

onMounted(() => {
    getPonderaciones();
    getAsignaturas();
});
</script>
