<template>
<div class="flex justify-center">
    <div style="width: 100%; background: #cdcdcd; max-width: 1000px; margin-top:20px;">    
        <a-row style="display:flex; justify-content:center;" class="pb-0">
            <a-col :span="24">
                <a-row :gutter="16" style="display:fleX; justify-content:center;">
                    <a-col v-for="item in anteriores" :key="item" :xs="24" :sm="24" :md="24" :lg="24"
                        style="margin-bottom: 10px;"
                    >
                        <div
                            @click="toggleSelection(item)"
                            :class="{ 'selected': item.selected }"
                            style="height:80px; border-radius:5px; cursor:pointer; border:solid 1px #d9d9d9; align-items: center; "
                            class="flex p-4">
                            <div style="display:flex; justify-content: space-between; width: 100%; align-items: center;">
                                <div style="width: calc(100% - 50px);">
                                    <span style="font-size:.8rem; text-transform: capitalize;">{{ item.career }}</span>
                                </div>
                                <div class="flex justify-center" style="width: 50px; height: 60px; align-items: center;">
                                    <img src="../../../../assets/imagenes/Veterinaria.png" width="50px"/>
                                </div>
                            </div>
                        </div>
                    </a-col>
                </a-row>
            </a-col>
        </a-row>

        <div><pre> {{ participante }} </pre></div>

        <p>Elementos seleccionados: {{ selectedItems }}</p>

        <div><pre> {{ anteriores }} </pre></div>

        <div>
            <a-button @click="registrarPrevias()">Registrar</a-button>
        </div>

    </div>                       
</div>
</template>
        
<script setup>
import { computed, ref, defineProps } from 'vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';


const participante = ref(null);
const temp = ref('76701665');
//const temp = ref('73810784');
const anteriores = ref([
    { codigo:'150570', career:'Ing. de Sistemas', cod_car:39 },
    { codigo:'164098', career:'Enfermería', cod_car:23 },
    { codigo:'202341', career:'Nutricion', cod_car:27 },
]);


const getDataPrisma = async () => {
    participante.value = null;
    const response = await axios.get('/get-data-prisma/'+temp.value );
    if(response.estado === true){
        participante.value = response.data.datos;
    }
    getCarrerasPrevias();
}

const getCarrerasPrevias = async () => {
    console.log(temp.value);
    if(participante.value != null){
        const response = await axios.post('https://service2.unap.edu.pe/TieneCarrerasPrevias/', {
        doc_: participante.value.dni,
        nom_: participante.value.nombre,
        app_: participante.value.paterno,
        apm_: participante.value.materno
        }, { headers: { 'Content-Type': 'application/json'}  });
        anteriores.value = response.data;
    } else {
        const response = await axios.post('https://service2.unap.edu.pe/TieneCarrerasPrevias/', {
        doc_: temp.value,
        nom_: 'DIRECCIÓN',
        app_: 'ADMISIÓN',
        apm_: 'UNAP'
        }, { headers: { 'Content-Type': 'application/json'}  });
        anteriores.value = response.data;
    }

}


getDataPrisma()

const notificacion = (type, titulo, mensaje) => { notification[type]({ message: titulo,description: mensaje});};

const registrarPrevias = async () => {
    axios.post("/registrar-carreras-previas",{"carreras": selectedItems.value, dni:temp.value })
    .then((response) => {
        confirmarcion.value = response.data.estado;
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
    });
}

const toggleSelection = (item) => {
    item.selected = !item.selected;
};

const selectedItems = computed(() => {
    if(anteriores.value){
        return anteriores.value.filter((item) => item.selected);
    }
});

</script>
<style scoped>
.selected { background-color: #e0e0e06d; }
.deshabilitado { opacity: 0.5;  pointer-events: none; cursor: not-allowed;}
</style>