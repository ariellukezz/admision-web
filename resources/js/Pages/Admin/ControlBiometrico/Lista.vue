<template>
<Head title="Control biométrico"/>
<AuthenticatedLayout>
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="height: calc(100vh - 98px);">

<!-- Botones de exportación + Buscador -->
<div class="flex justify-between items-center mb-3" style="flex-wrap: wrap; gap: 8px;">
    <div class="flex items-center gap-2">
        <a-button
            type="primary"
            style="border-radius: 5px; background: #27ae60; border: none;"
            :loading="exportandoExcel"
            @click="exportarExcel"
        >
            <template #icon><file-excel-outlined/></template>
            Excel
        </a-button>
        <a-button
            type="danger"
            style="border-radius: 5px; background: #e74c3c; border: none;"
            :loading="exportandoPdf"
            @click="exportarPdf"
        >
            <template #icon><file-pdf-outlined/></template>
            PDF
        </a-button>
    </div>

    <div class="flex" style="position: relative;">
        <a-input
            type="text"
            placeholder="Buscar"
            v-model:value="buscar"
            style="max-width: 300px; padding-left: 10px;"
            allow-clear
        >
            <template #prefix>
                <search-outlined/>
            </template>
        </a-input>
    </div>
</div>

<!-- Filtros siempre visibles -->
<div style="margin-bottom: 12px; padding: 12px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
    <div class="flex" style="gap: 12px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 180px;">
            <label style="font-size: .75rem; font-weight: 600; color: #555;">FECHA</label>
            <a-date-picker
                v-model:value="filtroFecha"
                style="width: 100%;"
                format="DD-MM-YYYY"
                value-format="YYYY-MM-DD"
                placeholder="Fecha específica"
                allow-clear
            />
        </div>

        <div style="flex: 2; min-width: 320px;">
            <label style="font-size: .75rem; font-weight: 600; color: #555;">RANGO DE FECHAS</label>
            <a-range-picker
                v-model:value="filtroRango"
                style="width: 100%;"
                format="DD-MM-YYYY"
                value-format="YYYY-MM-DD"
                :placeholder="['Desde', 'Hasta']"
                allow-clear
            />
        </div>

        <div style="flex: 1.5; min-width: 220px;">
            <label style="font-size: .75rem; font-weight: 600; color: #555;">PROGRAMA</label>
            <a-select
                v-model:value="filtroPrograma"
                style="width: 100%;"
                placeholder="Todos los programas"
                allow-clear
                show-search
                :options="programasOptions"
                :filter-option="(input, option) => option.label.toLowerCase().includes(input.toLowerCase())"
            />
        </div>

        <div style="flex: 1; min-width: 160px;">
            <label style="font-size: .75rem; font-weight: 600; color: #555;">MODALIDAD</label>
            <a-select
                v-model:value="filtroModalidad"
                style="width: 100%;"
                placeholder="Todas"
                allow-clear
                show-search
                :options="modalidadesOptions"
                :filter-option="(input, option) => option.label.toLowerCase().includes(input.toLowerCase())"
            />
        </div>

        <div style="flex: 1; min-width: 140px;">
            <label style="font-size: .75rem; font-weight: 600; color: #555;">ÁREA</label>
            <a-select
                v-model:value="filtroArea"
                style="width: 100%;"
                placeholder="Todas"
                allow-clear
                :options="areasOptions"
            />
        </div>

        <div style="display: flex; align-items: flex-end;">
            <a-button @click="limpiarFiltros">Limpiar</a-button>
        </div>
    </div>
</div>

<!-- Tabla -->
<div style="">
    <a-table
        :columns="columns"
        :data-source="programas"
        :pagination="false"
        size="small"
        :scroll="{ x: 380, y: 'calc(100vh - 340px)' }"
        :row-class-name="(_, index) => index % 2 === 1 ? 'fila-par' : ''"
        >
        <template #bodyCell="{ column, index, record }">
            <template v-if="column.dataIndex === 'codigo_ingreso'">
                <div><span style="font-size: .8rem; font-weight: 600;">{{ record.codigo_ingreso }}</span></div>
            </template>
            <template v-if="column.dataIndex === 'nro_doc'">
                <div><span style="font-size: .9rem">{{ record.nro_doc }}</span></div>
            </template>
            <template v-if="column.dataIndex === 'nombre'">
                <div><span style="font-size: .9rem;">{{ record.primer_apellido }} {{ record.segundo_apellido }}, {{ record.nombres }}</span></div>
            </template>
            <template v-if="column.dataIndex === 'modalidad'">
                <div class="flex" style="justify-content: center;">
                    <a-tag style="font-size: .8rem;" color="blue" v-if="record.modalidad == 'GENERAL'"> GENERAL </a-tag>
                    <a-tag style="font-size: .8rem;" color="orange" v-if="record.modalidad == 'CONADIS'"> CONADIS </a-tag>
                    <a-tag style="font-size: .8rem;" color="purple" v-if="record.modalidad == 'CEPREUNA'"> CEPREUNA</a-tag>
                    <a-tag style="font-size: .7rem;" color="pink" v-else-if="record.modalidad == 'BENEFICIARIOS DEL PLAN INTEGRAL DE REPARACIONES (PIR)'">LEY DE REPARACIONES</a-tag>
                    <a-tag style="font-size: .7rem;" color="pink" v-else>{{ record.modalidad }}</a-tag>
                </div>
            </template>
            <template v-if="column.dataIndex === 'estado'">
                <div class="flex" style="justify-content: center;">
                    <div v-if="1 == programas[index].estado">
                        <a-tag color="green">Si</a-tag>
                    </div>
                    <div v-if="programas[index].estado == 0">
                        <a-tag color="red">No</a-tag>
                    </div>
                </div>
            </template>
            <template v-if="column.dataIndex === 'acciones'">
                <a-button type="" @click="imprimirConstancia(record.url)" style="border-radius:4px; background: none; color: green" size="small">
                    <template #icon><printer-outlined/></template>
                </a-button>
                <a-button type="" @click="generarConstancia(record.nro_doc)" style="border-radius:4px; background: none; color: gray" size="small">
                    <template #icon><sync-outlined/></template>
                </a-button>
            </template>
        </template>
    </a-table>
</div>

<div class="flex" style="justify-content: flex-end;">
    <a-pagination v-model:current="pagina" simple page-size="50" :total="totalpaginas" />
</div>

</div>
</AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch, onMounted } from 'vue';
import {
    PrinterOutlined, SyncOutlined, SearchOutlined,
    FileExcelOutlined, FilePdfOutlined
} from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const baseUrl = window.location.origin;

const buscar = ref("");
const pagina = ref(1);
const totalpaginas = ref(null);
const programas = ref([]);

// Filtros siempre visibles
const filtroFecha = ref(null);
const filtroRango = ref(null);
const filtroPrograma = ref(null);
const filtroModalidad = ref(null);
const filtroArea = ref(null);

const programasOptions = ref([]);
const modalidadesOptions = ref([]);
const areasOptions = [
    { value: 'BIOMEDICAS', label: 'Biomédicas' },
    { value: 'BIOMÉDICAS', label: 'Biomédicas' },
    { value: 'INGENIERIAS', label: 'Ingenierías' },
    { value: 'INGENIERÍAS', label: 'Ingenierías' },
    { value: 'SOCIALES', label: 'Sociales' },
];

const exportandoExcel = ref(false);
const exportandoPdf = ref(false);

let timeoutId;
watch(buscar, () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => { pagina.value = 1; getProgramas(); }, 500);
});

watch(pagina, () => { getProgramas(); });

watch([filtroFecha, filtroRango, filtroPrograma, filtroModalidad, filtroArea], () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => { pagina.value = 1; getProgramas(); }, 300);
});

const getProgramas = async () => {
    try {
        let payload = { term: buscar.value };
        if (filtroFecha.value) payload.fecha = filtroFecha.value;
        if (filtroRango.value && filtroRango.value[0]) {
            payload.fecha_inicio = filtroRango.value[0];
            payload.fecha_fin = filtroRango.value[1];
        }
        if (filtroPrograma.value) payload.programa = filtroPrograma.value;
        if (filtroModalidad.value) payload.modalidad = filtroModalidad.value;
        if (filtroArea.value) payload.area = filtroArea.value;

        let res = await axios.post("get-control-posterior?page=" + pagina.value, payload);
        programas.value = res.data.datos.data;
        totalpaginas.value = res.data.datos.total;
    } catch (error) {
        console.error('Error al obtener datos:', error);
    }
};

const cargarProgramas = async () => {
    try {
        let res = await axios.get("control-biometrico/programas");
        programasOptions.value = res.data.datos.map(p => ({ value: p.id, label: p.nombre }));
    } catch (e) {
        console.error('Error al cargar programas:', e);
    }
};

const cargarModalidades = async () => {
    try {
        let res = await axios.get("control-biometrico/modalidades");
        modalidadesOptions.value = res.data.datos.map(m => ({ value: m.id, label: m.nombre }));
    } catch (e) {
        console.error('Error al cargar modalidades:', e);
    }
};

const limpiarFiltros = () => {
    filtroFecha.value = null;
    filtroRango.value = null;
    filtroPrograma.value = null;
    filtroModalidad.value = null;
    filtroArea.value = null;
    pagina.value = 1;
    getProgramas();
};

const construirParams = () => {
    const params = new URLSearchParams();
    if (filtroFecha.value) params.append('fecha', filtroFecha.value);
    if (filtroRango.value && filtroRango.value[0]) {
        params.append('fecha_inicio', filtroRango.value[0]);
        params.append('fecha_fin', filtroRango.value[1]);
    }
    if (filtroPrograma.value) params.append('programa', filtroPrograma.value);
    if (filtroModalidad.value) params.append('modalidad', filtroModalidad.value);
    if (filtroArea.value) params.append('area', filtroArea.value);
    return params;
};

const exportarExcel = async () => {
    exportandoExcel.value = true;
    try {
        const params = construirParams();
        window.location.href = `${baseUrl}/admin/control-biometrico/exportar-excel?${params.toString()}`;
    } catch (error) {
        notification['error']({ message: 'Error', description: 'No se pudo exportar el Excel' });
    } finally {
        setTimeout(() => { exportandoExcel.value = false; }, 2000);
    }
};

const exportarPdf = async () => {
    exportandoPdf.value = true;
    try {
        const params = construirParams();
        window.open(`${baseUrl}/admin/control-biometrico/exportar-pdf?${params.toString()}`, '_blank');
    } catch (error) {
        notification['error']({ message: 'Error', description: 'No se pudo exportar el PDF' });
    } finally {
        setTimeout(() => { exportandoPdf.value = false; }, 2000);
    }
};

const imprimirConstancia = (url) => {
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = baseUrl + url;
    document.body.appendChild(iframe);
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
};

const generarConstancia = (dni) => {
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = baseUrl + '/admin/pdf-biometrio/' + dni;
    document.body.appendChild(iframe);
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
};

const columns = [
    { title: 'Cod', dataIndex: 'codigo_ingreso', width: '65px', align: 'center', responsive: ['md'] },
    { title: 'N° Doc', dataIndex: 'nro_doc', width: '80px', align: 'center', responsive: ['md'] },
    { title: 'Nombre', dataIndex: 'nombre' },
    { title: 'Programa', dataIndex: 'programa' },
    { title: 'Modalidad', dataIndex: 'modalidad', align: 'center', width: '130px', responsive: ['md'] },
    { title: 'Puntaje', dataIndex: 'puntaje_total', align: 'center', width: '80px', responsive: ['md'] },
    { title: 'Acciones', dataIndex: 'acciones', width: '90px', align: 'center' },
];

onMounted(() => {
    getProgramas();
    cargarProgramas();
    cargarModalidades();
});
</script>

<style>
::-webkit-scrollbar { width: 9px; height: 12px; }
::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #555; }
.fila-par { background-color: #fafafa; }
</style>
