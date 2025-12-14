<template>
    <div>
        <div class="mt-0 mb-4">
            <div class="class flex justify-between">
                <div class="mt-0 mb-4">
                    <a-button @click="visible = true">Calificar</a-button>
                </div>
                <div class="flex" style="gap:5px">
                    <div v-if="resultados != []" class="mt-0 mb-4">
                        <a-button style="width: 140px; background:crimson; border:none; color:white;" @click="descargar()">Descargar</a-button>
                    </div>

                    <div class="mt-0 mb-4">
                        <a-button style="width: 140px; background:green; border:none; color:white;" @click="descargarExcel()" >Descargar Excel</a-button>
                    </div>
                </div>
            </div>
        </div>


        <a-table :dataSource="resultados" :columns="columns" size="small" :pagination="false">
            <template #bodyCell="{ column, index, record }">
                <template v-if="column.dataIndex === 'nro'">
                    <span>{{ index + 1 }}</span>
                </template>
                <template v-if="column.dataIndex === 'puntaje'">
                    <div v-if="record.puntaje !== null && record.puntaje !== undefined">
                        <span>{{ formatPuntaje(record.puntaje) }}</span>
                    </div>

                </template>
            </template>

        </a-table>


        <a-modal title="Modal de calificación" v-model:open="visible" :footer="false">

            <div class="mt-4 mb-2">
                <label>Area</label>
                <a-select ref="select" v-model:value="area" style="width: 100%">
                    <a-select-option value="BIOMEDICAS">BIOMEDICAS</a-select-option>
                    <a-select-option value="SOCIALES">SOCIALES</a-select-option>
                    <a-select-option value="INGENIERIAS">INGENIERIAS</a-select-option>
                </a-select>
            </div>

            <div style="display:flex; gap:16px;" class="mt-4">
                <div>
                    <label>Correctas</label>
                    <a-input ref="select" v-model:value="correctas" style="width: 100%">
                        <template #suffix><Icono/> </template>
                    </a-input>
                </div>
                <div>
                    <label>Incorrectas</label>
                    <a-input ref="select" v-model:value="incorrectas" style="width: 100%">
                        <template #suffix><Icono/> </template>
                    </a-input>
                </div>
                <div>
                    <label>En Blanco</label>
                    <a-input ref="select" v-model:value="blanco" style="width: 100%">
                        <template #suffix><Icono/> </template>
                    </a-input>
                </div>
            </div>

            <div class="mt-3">
                <label>Ponderacion</label>
                <div>
                    <a-auto-complete
                        v-model:value="ponderacion"
                        :options="ponderaciones"
                        @select="onSelectPonderacion"
                        style="width:100%;"
                        >
                        <a-input
                            placeholder="Ponderación ..."
                            v-model:value="buscarPonderacion"
                        >
                        <template #suffix>
                            <DownOutlined/>
                        </template>
                        </a-input>
                    </a-auto-complete>
                </div>

            </div>

            <!-- <div>{{ props.proceso }}</div> -->

            <div class="flex justify-end mt-4">
                <div class="mr-2">
                    <a-button style="margin-left: 6px; border-radius: 4px;">Cancelar</a-button>
                </div>
                <div>
                    <a-button type="primary" @click="califar()" style="background: #476175; border:none; border-radius: 4px;">Calificar</a-button>
                </div>
            </div>
        </a-modal>

    </div>
</template>

<script setup>
import { watch, computed, defineProps, ref, unref } from 'vue';
import { DownOutlined, SearchOutlined, EyeOutlined, FormOutlined, DeleteOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const props = defineProps(['proceso']);
const area = ref("");
const ponderacion = ref(null);
const buscarPonderacion = ref("");
const resultados = ref([]);

const onSelectPonderacion = (value, option) => { ponderacion.value = option; };
const notificacion = (type, titulo, mensaje) => { notification[type]({ message: titulo, description: mensaje, }); };

watch(buscarPonderacion, ( newValue, oldValue ) => {  getPonderaciones(); })

const ponderaciones = ref([]);
const totalRegistros = ref(0);
const pagina = ref(1);
const paginasize = ref(10);
const buscar = ref("");
const correctas = ref(1);
const incorrectas = ref(0);
const blanco = ref(0);


const puestos = ref([]);
const codigos_puesto = ref([]);
const unidades = ref([]);
const selectPuesto = ref(null);
const selectCodigo = ref(null);
const selectUnidad = ref(null);


const getPonderaciones =  async () => {
    let res = await axios.post("calificacion/get-ponderaciones-select?page=" + pagina.value, {
         term: buscarPonderacion.value, paginasize: paginasize.value } );
    ponderaciones.value = res.data.datos.data;
    totalRegistros.value = res.data.datos.total;
}

const califar =  async () => {
    let res = await axios.post("/calificar-examen",
    {
        id_simulacro: props.proceso,
        id_ponderacion: ponderacion.value.key,
        correctas: correctas.value,
        incorrectas: incorrectas.value,
        blanco: blanco.value,
        area: area.value
    } );
    visible.value = false;
    getPuntajes();
}

const getPuntajes =  async () => {
    let res = await axios.post("/get-puntajes-examen", { id_simulacro: props.proceso } );
    resultados.value = res.data.datos;
}

getPuntajes();
const visible = ref(false);
getPonderaciones();

const formatPuntaje = (puntaje) =>  {
    return Number(puntaje).toFixed(2);
}


const columns = ref([
    { title: 'N°', dataIndex: 'nro', align:'center'},
    { title: 'DNI',dataIndex: 'dni'},
    { title: 'Ap. Paterno', dataIndex: 'paterno'},
    { title: 'Ap. Materno', dataIndex: 'materno'},
    { title: 'Nombres', dataIndex: 'nombres' },
    { title: 'Puntaje', dataIndex: 'puntaje', align:'center'},

]);

const getSelect = async () => {
    axios.get("/calificacion/get-select-puestos/"+props.proceso)
    .then((response) => {
        puestos.value = response.data.puestos;
        codigos_puesto.value = response.data.codigos_puesto;
        unidades.value = response.data.unidades;
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
    });
}

getSelect();

const descargar = async (items) => {
    try {
    const response = await axios.post('/get-pdf-resultados/'+props.proceso,
    {},
    {
        responseType: 'blob'
    });

    if (response.status !== 200) {
        throw new Error('Error al obtener el archivo');
    }

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    const fecha = new Date();
    const formatoFecha = `${fecha.getDate().toString().padStart(2, '0')}-${(fecha.getMonth() + 1).toString().padStart(2, '0')}-${fecha.getFullYear()}_${fecha.getHours().toString().padStart(2, '0')}-${fecha.getMinutes().toString().padStart(2, '0')}-${fecha.getSeconds().toString().padStart(2, '0')}`;
    const nombreArchivo = `${formatoFecha}_resultados.pdf`;
    link.setAttribute('download', nombreArchivo);
    document.body.appendChild(link);
    link.click();

    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    } catch (error) {
    console.error('Error al descargar el archivo:', error);
    }
};

const descargarExcel = async () => {
  try {
    const response = await axios.get('calificacion/descargar-excel', {
      params: { descargar: 1, id_proceso: props.proceso },
      responseType: 'blob'
    });

    if (response.status !== 200) {
      throw new Error('Error al obtener el archivo');
    }

    const fecha = new Date();
    const formatoFecha = `${fecha.getDate().toString().padStart(2, '0')}-${(fecha.getMonth() + 1).toString().padStart(2, '0')}-${fecha.getFullYear()}_${fecha.getHours().toString().padStart(2, '0')}-${fecha.getMinutes().toString().padStart(2, '0')}-${fecha.getSeconds().toString().padStart(2, '0')}`;
    const nombreArchivo = `${formatoFecha}_lista_postulantes.xlsx`;

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', nombreArchivo);
    document.body.appendChild(link);
    link.click();

    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error al descargar el archivo:', error);
  }
};
</script>
