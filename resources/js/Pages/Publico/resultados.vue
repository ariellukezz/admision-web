<template>
<Head title="Consulta individual"/>
<Layout>
    <div  style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 190px);">
        <div class="container">
            <div class="mb-6" style="text-align: center; margin-top: 10px;">
                <h1 style="color: #000000; font-size: 1.4rem; font-weight: 700;">CONSULTAR PUNTAJE</h1>
            </div>

            <div style="width: 100%; background: #334b9988;">
                <div class="flex">
                    <a-input v-model:value="dni" type="text" Placeholder="Ingrese DNI" style="padding-left:20px;"/>
                    <a-button @click="getPuntaje()" type="primary" style="height: 38px; width: 160px;">Consultar</a-button>
                </div>
            </div>

            <div v-if="resultados.length > 0 " class="mt-6" style="-o-transition-duration: .3s;">
                <a-table
                    :columns="columnsResultados"
                    :data-source="resultados"
                    :pagination="false"
                    size="small"
                    >
                    <template #bodyCell="{ column, index, record }" >
                        <template v-if="column.dataIndex === 'puntaje'">
                            <div>
                                {{ record.puntaje }}
                                <!-- {{  record.puntaje.toFixed(3) }} -->
                            </div>
                        </template>
                        <template v-if="column.dataIndex === 'fecha'">
                            <div>
                                {{  voltear(record.fecha) }}
                            </div>
                        </template>

                        <template v-if="column.dataIndex === 'condicion'">
                            <div v-if="record.condicion == 'SI'">
                                <a-tag color="blue"> Ingresó </a-tag>
                            </div>
                            <div v-if="record.condicion == 'NO'">
                                <div class="flex justify-center">
                                    <a-tag color="orange"> No ingresó </a-tag>
                                </div>
                            </div>
                            <div v-if="record.condicion == 'CL'">
                                <div class="flex justify-center">
                                    <a-tag color="yellow"> Clasificado </a-tag>
                                </div>
                            </div>

                        </template>

                    </template>

                </a-table>
            </div>

            <div class="mt-2" v-if="ingresante === 5" style="background: white;">
                <div>
                    <a-alert
                        message="! MUY IMPORTANTE !"
                        description="Los postulantes que hayan alcanzado una vacante deberán subir su Certificado de Estudios y DNI en formato PDF con un peso max. de 2mb"
                        show-icon
                        type="info"
                    >
                    <a-button> Subir Archivos </a-button>
                    </a-alert>
                </div>
            </div>

            <div class="p-3 mt-3" v-if="ingresante === 5" style="background: white;">

                <!-- <div class="m-3 pt-0">
                    <h3 style="font-size:1.1rem;"> Formulario de registro</h3>
                </div>

                <div class="p-3">
                    <label>DNI del padre<span style="color:red;"> (*)</span></label>
                    <div class="flex">
                        <a-input v-model:value="dnipadre" type="text" Placeholder="Ingrese DNI del padre" style="padding-left:20px;"/>
                    </div>
                </div>

                <div class="p-3">
                    <label>DNI de la madre<span style="color:red;"> (*)</span></label>
                    <div class="flex">
                        <a-input v-model:value="dnimadre" type="text" Placeholder="Ingrese DNI de la madre" style="padding-left:20px;"/>
                    </div>
                </div> -->

                <div v-if="ingresante === 1">
                    <div class="ml-3 mt-4">
                        <span style="font-size: 1.1rem;"> Subir Archivos</span>
                    </div>

                    <div class="p-3 mt-5" style="background: #e3e3e33d; border-radius: 5px;">
                        <div class="mb-3">
                            <span style="font-size: 1rem;">Certificado en PDF </span>
                        </div>
                        <a-space class="mb-2" direction="vertical" style="width: 100%" size="large">
                            <a-upload
                                v-model:file-list="fileList"
                                list-type="picture"
                                :max-count="1"
                                :action="'subir-pdf/'+dni+'/'+codigo+'/1'"
                                @change="handleFileChange"
                                :capture="null"
                                >
                                <a-button style="background: #cccccc8d;">
                                    <upload-outlined></upload-outlined>
                                    Seleccionar certificado en PDF
                                </a-button>
                            </a-upload>
                        </a-space>
                    </div>

                    <div class="p-3 mt-5" style="background: #e3e3e33d; border-radius: 5px;">
                        <div class="mb-3">
                            <span style="font-size: 1.1rem;"> DNI en PDF </span>
                        </div>
                        <a-space class="mb-2" direction="vertical" style="width: 100%" size="large">
                            <a-upload
                                v-model:file-list="fileList2"
                                list-type="picture"
                                :max-count="1"
                                :action="'subir-pdf/'+dni+'/'+codigo+'/2'"
                                @change="handleFileChange2"
                                >
                                <a-button style="background: #cccccc8d;">
                                    <upload-outlined></upload-outlined>
                                    Seleccionar DNI en pdf
                                </a-button>
                            </a-upload>
                        </a-space>
                    </div>

                    <div class="mt-3" v-if="arc1 == true && arc2 == true">

                        <div style="border-radius: 8px; border:solid 1px #d9d9d9; text-align:center;">
                            <span style="font-family: 'Courier New', Courier, monospace; font-wight:bold; font-size: 2.3rem;">
                                {{ codigo }}
                            </span>
                            <div class="mb-2" style="margin-top:-10px;">
                                código secreto
                            </div>
                        </div>

                        <div class="mt-3 mb-3" >
                            <a-alert
                                message="!IMPORTANTE!"
                                description="DEBES ANOTAR EL CODIGO SECRETO PARA LA VERIFICACIÓN DE TUS DOCUMENTOS "
                                show-icon
                                type="warning"
                                >
                            </a-alert>
                        </div>

                        <div class="flex justify-center mb-4 pt-3">
                            <a-button style="width: 180px;" @click="finalizar()" type="primary">Guardar</a-button>
                        </div>

                        <!-- <div class="flex justify-center mb-4 pt-3">
                            {{ arc1}} {{ arc2 }}
                        </div> -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</Layout>

</template>
<script setup>
import Layout from '@/Layouts/LayoutResultados.vue'
import { defineProps, watch, reactive, ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const resultados = ref([]);
const dni = ref("");
const dnipadre = ref("")
const dnimadre = ref("")
const ingresante = ref(0)
const verificado = ref(false);
const codigo = ref("");

const getPuntaje = async () => {
    ingresante.value = 0;
    const res = await axios.get(`/get-puntajes/` + dni.value);
    resultados.value = res.data.datos;
    // dni.value = "";
    const ingreso = resultados.value.some(item => item.condicion === 'SI');
    if (ingreso) {
        ingresante.value = 1;
        //console.log('Al menos uno tiene la condición SI');
    } else {
        ingresante.value = 0;
        //console.log('Ninguno tiene la condición SI');
    }
    getCodigoAleatorio();
}


// const verificarPadres = async () => {
//     const res = await axios.post("/verificar-padres",{
//         dni: dni.value,
//         dnipadre: dnipadre.value,
//         dnimadre: dnimadre.value
//     });

//     verificado.value = res.data.estado;
// }


watch(dnipadre, ( newValue, oldValue ) => {
    if(dnipadre.value.length == 8 && dnipadre.value.length == 8  && dnimadre.value.length != dnipadre.value){
        verificarPadres();
        getCodigoAleatorio();
    }
});

watch(dnimadre, ( newValue, oldValue ) => {
    if(dnipadre.value.length == 8 && dnipadre.value.length == 8 && dnimadre.value != dnipadre.value ){
        verificarPadres();
        getCodigoAleatorio();
    }
});


const getCodigoAleatorio = async ( ) => {
  let res = await axios.get("/generar-captcha");
  codigo.value = res.data.captcha;
}

const arc1 = ref(false);
const arc2 = ref(false);

const columnsResultados = [
    { title: 'Nombre', dataIndex: 'nombres'},
    { title: 'Programa', dataIndex:'programa', responsive: ['sm'], },
    { title: 'Puntaje', dataIndex:'puntaje', align:'center'},
    { title: 'Vocacional', dataIndex:'puntaje_vocacional', align:'center'},
    { title: 'Fecha', dataIndex: 'fecha', align:'center'},
    { title: 'Condición', dataIndex: 'condicion', align:'center'},
];

function voltear(fecha) {
  return fecha.split('-').reverse().join('-');
}


const handleFileChange = async ({ file }) => {
  if (file.status === 'done') {
    const responseText = file.response.substring(1); // Eliminar el primer carácter
    const responseData = JSON.parse(responseText); // Convertir el texto a JSON
    arc1.value = responseData.estado; // Acceder a la propiedad 'estado'
  }
};

const handleFileChange2 = async ({ file }) => {
  if (file.status === 'done') {
    const responseText = file.response.substring(1); // Eliminar el primer carácter
    const responseData = JSON.parse(responseText); // Convertir el texto a JSON
    arc2.value = responseData.estado; // Acceder a la propiedad 'estado'
  }
};



function finalizar() {
    dni.value = "";
    dnipadre.value = "";
    dnimadre.value = "";
    verificado.value = false;
    ingresante.value = 0;
    resultados.value = [];
}


</script>

<style scope>

.container {
  width: 80%;
  max-width: 1000px; /* Ancho máximo del contenedor */
  margin: 0 auto; /* Centrar el contenedor */
  padding: 20px;
  box-sizing: border-box; /* Incluye relleno y borde en el tamaño total */

  @media screen and (max-width: 600px) {
    width: 100%;
  }
  @media screen and (min-width: 1300px) {
    max-width: 900px;
  }

}

.button-div{ background: none; }
.button-div:hover{ background: #e3e3e37e; }

</style>
