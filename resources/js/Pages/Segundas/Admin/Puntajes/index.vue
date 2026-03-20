<template>
<Head title="Resultados"/>
<Layout>

<div class="bg-white shadow-md rounded-xl p-5" style="height: calc( 100vh - 105px);">

    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-5">

        <a-select
            v-model:value="id_programa"
            placeholder="Seleccionar programa"
            :options="programas"
            class="w-full sm:w-auto"
            :style="{ minWidth: '320px' }"
        />

        <div class="flex gap-2">
            <a-input 
                placeholder="Buscar..." 
                v-model:value="buscar"
                class="w-full sm:max-w-xs"
            >
                <template #prefix><SearchOutlined /></template>
            </a-input>

            <a-button type="primary" @click="ordenarRanking">
                Ordenar por ranking
            </a-button>

            <a-button 
                type="primary" 
                style="background:#52c41a" 
                @click="abrirModal"
                :disabled="publicado === 1"
            >
                Publicar
            </a-button>
        </div>

    </div>

    <a-table 
        :columns="columnsProgramas" 
        :data-source="tablaOrdenada"
        :pagination="false"
        :scroll="{ y: 'calc(100vh - 240px)' }"
        row-key="id_preinscripcion"
        bordered
        size="small"
    >

        <template #bodyCell="{ column, record, index }">

            <template v-if="column.dataIndex === 'nombres'">
                {{ record.nombres }} {{ record.primer_apellido }} {{ record.segundo_apellido }}
            </template>

            <template v-if="column.dataIndex === 'puesto'">
                <b>{{ record.puesto ?? '-' }}</b>
            </template>

            <template v-if="column.dataIndex === 'puntaje'">

                <!-- 🔒 INPUT SOLO SI NO ESTA PUBLICADO -->
                <div v-if="editando === record.id_preinscripcion && publicado !== 1">
                    <a-input-number
                        v-model:value="record.puntaje"
                        :min="0"
                        :max="1000"
                        :precision="2"
                        size="small"
                        class="w-24"
                        @pressEnter="siguienteFila(index)"
                        @blur="guardar(record)"
                        :ref="el => inputRefs[index] = el"
                    />
                </div>

                <div v-else class="flex gap-2">
                    <span>
                        {{ record.puntaje !== null ? Number(record.puntaje).toFixed(2) : '-' }}
                    </span>

                    <a-button 
                        size="small" 
                        @click="activarEdicion(record, index)"
                        :disabled="publicado === 1"
                    >
                        ✏️
                    </a-button>
                </div>

            </template>

            <template v-if="column.dataIndex === 'apto'">
                <span>
                    {{
                        record.puntaje === null
                            ? 'NO'
                            : record.puesto && record.puesto <= vacantes
                                ? 'SI'
                                : 'NO'
                    }}
                </span>
            </template>

        </template>

    </a-table>

</div>

<a-modal 
    v-model:open="modalVisible" 
    title="Publicar resultados" 
    @ok="publicar"
    ok-text="Publicar"
    cancel-text="Cancelar"
    width="600px"
>

    <div class="mb-3">
        Vacantes:
        <a-input-number v-model:value="vacantesEditable" :min="1" />
    </div>

    <div style="max-height:400px; overflow:auto;">

        <div 
            v-for="(p, index) in listaOrdenada" 
            :key="p.id_preinscripcion"
            class="flex justify-between px-2 py-1 rounded"
            :style="{
                background: (p.puntaje !== null && index < vacantesEditable) ? '#f6ffed' : '#fff1f0'
            }"
        >
            <div>
                {{ p.puntaje !== null ? (index+1) : '-' }} - 
                {{ p.nombres }} {{ p.primer_apellido }} {{ p.segundo_apellido }} 
                ({{ p.puntaje !== null ? Number(p.puntaje).toFixed(2) : '-' }})
            </div>

            <div>
                <b>
                    {{
                        (p.puntaje !== null && index < vacantesEditable)
                            ? 'APTO'
                            : 'NO APTO'
                    }}
                </b>
            </div>
        </div>

    </div>

</a-modal>

</Layout>
</template>

<script setup>
import { ref, watch, nextTick, computed } from 'vue';
import { message } from 'ant-design-vue';
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/segundas-especialidades/LayoutDirector.vue'
import { SearchOutlined } from '@ant-design/icons-vue';
import axios from 'axios';

const programas = ref([]);
const buscar = ref('');
const pagina = ref(1);
const puntajes = ref([]);
const totalRegistros = ref(0);
const id_programa = ref(null);

const editando = ref(null);
const inputRefs = ref([]);

const vacantes = ref(0);
const publicado = ref(0); // 🔥 CLAVE
const vacantesEditable = ref(0);

const modalVisible = ref(false);

const getPuntajes = async () => {
    if (!id_programa.value) return;

    const res = await axios.post('/segundas/get-resultados-segundas', {
        term: buscar.value,
        pagina: pagina.value,
        id_programa: id_programa.value,
    });

    puntajes.value = res.data.datos.data;
    totalRegistros.value = res.data.datos.total;
};

watch([buscar, pagina, id_programa], getPuntajes);

const activarEdicion = (record, index) => {
    if (publicado.value === 1) return;
    editando.value = record.id_preinscripcion;
    nextTick(() => inputRefs.value[index]?.focus());
};

const guardar = async (record) => {
    if (publicado.value === 1) return;

    await axios.post('/segundas/guardar-puntaje', {
        id_pre_inscripcion: record.id_pre_inscripcion,
        puntaje: record.puntaje
    });
};

const siguienteFila = async (index) => {
    if (publicado.value === 1) return;

    const actual = puntajes.value[index];

    if (actual.puntaje !== null) {
        await guardar(actual);
    }

    const next = puntajes.value[index + 1];

    if (next) {
        activarEdicion(next, index + 1);
    } else {
        editando.value = null;
    }
};

const ordenarRanking = () => {

    const conNota = puntajes.value
        .filter(p => p.puntaje !== null)
        .sort((a,b)=>b.puntaje - a.puntaje);

    const sinNota = puntajes.value.filter(p => p.puntaje === null);

    conNota.forEach((p,i)=> p.puesto = i + 1);
    sinNota.forEach(p=> p.puesto = null);

    puntajes.value = [...conNota, ...sinNota];
};

const abrirModal = () => {
    ordenarRanking();
    vacantesEditable.value = vacantes.value;
    modalVisible.value = true;
};

const listaOrdenada = computed(() => {
    const conNota = puntajes.value
        .filter(p => p.puntaje !== null)
        .sort((a,b)=>b.puntaje - a.puntaje);

    const sinNota = puntajes.value.filter(p => p.puntaje === null);

    return [...conNota, ...sinNota];
});

const tablaOrdenada = computed(() => {
    if (publicado.value === 1) {
        return [...puntajes.value].sort((a,b) => (a.puesto ?? 9999) - (b.puesto ?? 9999));
    }
    return puntajes.value;
});

const publicar = async () => {

    const conNota = listaOrdenada.value.filter(p => p.puntaje !== null);

    const lista = conNota.map((p, index) => {

        return {
            id_pre_inscripcion: p.id_pre_inscripcion,
            puesto: index + 1,
            puntaje: Number(p.puntaje).toFixed(2),
            apto: index < vacantesEditable.value ? 'SI' : 'NO'
        };
    });
    const sinNota = listaOrdenada.value
        .filter(p => p.puntaje === null)
        .map(p => ({
            id_pre_inscripcion: p.id_pre_inscripcion,
            puesto: null,
            puntaje: null,
            apto: 'NO'
        }));

    const listaFinal = [...lista, ...sinNota];

    await axios.post('/segundas/publicar-resultados-segundas', {
        id_programa: id_programa.value,
        vacantes: vacantesEditable.value,
        lista: listaFinal
    });

    message.success('Publicado');
    modalVisible.value = false;

    getVacantes();
};
const getVacantes = async () => {
    if (!id_programa.value) return;

    const res = await axios.post('/segundas/get-vacante-programa-segundas', {
        id_programa: id_programa.value
    });

    vacantes.value = res.data.datos.vacantes;
    publicado.value = res.data.datos.publicado;
    console.log('Vacantes:', vacantes.value, 'Publicado:', publicado.value);
    if(publicado.value === 1){
        ordenarRanking();
        console.log("Ordenar");
    }   
};

watch(id_programa, () => {
    if(id_programa.value){
        getVacantes();
    }
});

const getProgramas = async () => {
    const res = await axios.get('/segundas/get-select-programas-asignados');
    programas.value = res.data.data;
};
getProgramas();

const columnsProgramas = [
    { title: 'Puesto', dataIndex: 'puesto', width: 80 },
    { title: 'Nombres', dataIndex: 'nombres', width: 250 },
    { title: 'Documento', dataIndex: 'nro_doc', width: 120 },
    { title: 'Programa', dataIndex: 'programa', width: 200 },
    { title: 'Puntaje', dataIndex: 'puntaje', width: 120 },
    { title: 'Apto', dataIndex: 'apto', width: 80 },
];
</script>