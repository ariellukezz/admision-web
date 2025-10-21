<template>
<Head title="Calificación"/>
<Layout>  
<div class="mb-4" style="width: 100%;">
    <div class="p-4" style="background: white; width: 100%; min-height: calc(100vh - 90px); border-radius: 12px;" @click="clicIzquierdo" @contextmenu.prevent="handleContextMenu">
        <div class="ml-0 flex mb-0 mt-0" >
            <div class="flex">
                <div style="margin-top: -4px;"><HomeOutlined/></div>
                <div  v-for="it in breadcrumb" :key="it.id" @click="entrarCarpeta(it)">
                    <div class="flex" style="height:20px; align-items:center;"> <div class="carpetas-select px-1"> {{ it.nombre }} </div>  </div>
                </div>
            </div>
        </div>

        <div v-if="breadcrumb.length === 1">
            <div class="flex justify-end mt-0 mb-3" style="margin-top:-2px;">
                <a-input v-model:value="buscar" style="max-width: 300px; border-radius: 6px; height: 36px;" placeholder="Buscar">
                    <template #prefix>
                        <span style="color: #0000009d; margin-top: -6px;"><SearchOutlined/></span>
                    </template>
                </a-input>
            </div>

            <a-table 
                :columns="columnsSimulacros" 
                :data-source="simulacros"
                :key="id"
                size="small"
                :pagination="false"
                style="scale: .7rem;"
                > 
                <template #bodyCell="{ column, index, record }">

                    <template v-if="column.dataIndex === 'nro'">
                        <span>{{ index + 1 }}</span>
                    </template>
                    <template v-if="column.dataIndex === 'nombre'">
                        <div style="width:100%; cursor:pointer;" @dblclick="customRow(record)" >
                            <span>{{ record.nombre }}</span>
                        </div>
                    </template>

                    <template v-if="column.dataIndex === 'estado'">
                        <div class="flex" style="justify-content: center;">
                            <div v-if="1 === record.estado">
                                <a-tag color="cyan" >Vigente</a-tag>
                            </div>
                            <div v-else>
                                <a-tag color="red">No Vigente</a-tag>
                            </div>
                        </div>
                    </template>
                    <template v-if="column.dataIndex === 'acciones'">
                        <a-button type="gray" class="mr-1" style="color: #476175;" @click="verFicha(record.id)" size="small">
                            <template #icon><EyeOutlined/></template>
                        </a-button>
                        <a-button @click="abrirEditar(record)" class="mr-1" style="color: blue;" size="small">
                            <template #icon><form-outlined/></template>
                        </a-button>
                        <a-popconfirm
                            title="¿Estas seguro de eliminar?"
                            @confirm="eliminar(record)"
                            >
                            <a-button shape="" size="small" style="color: crimson;">
                                <template #icon><delete-outlined/></template>
                            </a-button>
                        </a-popconfirm>

                    </template> 
                </template>
            </a-table> 


            <!-- <pre>{{ simulacros }}</pre> -->
            

            <!-- <div class="flex" @contextmenu.prevent="clickderecho()">
                <div class="carpetas-select" v-for="item in carpetas" :key="item.id" @dblclick="entrarCarpeta(item)" >
                    <div>
                        <svg width="80px" height="60px" viewBox="0 0 32 32" enable-background="new 0 0 32 32" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g><path d="M27,9v5c0,0.55-0.45,1-1,1H6.74L2.96,27.29C2.83,27.72,2.43,28,2,28c-0.05,0-0.1,0-0.15-0.01    C1.36,27.92,1,27.5,1,27V5c0-0.55,0.45-1,1-1h7c0.31,0,0.61,0.15,0.8,0.4L12.5,8H26C26.55,8,27,8.45,27,9z" fill="#476175"/></g>
                            <g><path d="M30.96,14.29l-4,13C26.83,27.71,26.44,28,26,28H2c-0.32,0-0.62-0.15-0.8-0.41    c-0.19-0.25-0.25-0.58-0.16-0.88l4-13C5.17,13.29,5.56,13,6,13h24c0.32,0,0.62,0.15,0.8,0.41C30.99,13.66,31.05,13.99,30.96,14.29    z" fill="#4761ac"/></g>
                        </svg>
                    </div>
                    <div>{{ item.nombre }}</div>
                </div>
            </div> -->


            <div style=" position: absolute; border: solid 1px #d9d9d943; padding-top: 0px; border-radius:8px; overflow: hidden;" v-if="showContextMenu" :style="{ top: `${contextMenuTop }px`, left: `${contextMenuLeft}px`}">
                <a-menu size="small" style="margin-top: -4px; margin-bottom: -4px;" >
                    <a-menu-item key="1" class="selec-menu" @click="handleMenuItemClick('1')">
                        <div style="margin-top: 0px;">
                            Nuevo Proceso
                        </div>
                    </a-menu-item>
                    <a-menu-item key="2" class="selec-menu" @click="handleMenuItemClick('2')">
                        <div style="margin-top: 0px;">
                            Subir archivo
                        </div>
                    </a-menu-item>
                    <a-menu-item key="2" class="selec-menu" @click="handleMenuItemClick('2')">
                        <div style="margin-top: 0px;">
                            selees
                        </div>
                    </a-menu-item>
                </a-menu>
            </div>


            <a-modal v-model:open="modalNuevo" :closable="false" centered style="" :footer="false">

                <div style="background: #476175; height: 42px; margin-left:-24px; margin-right:-24px; margin-top:-24px;">
                    <div class="flex justify-between ml-3 mr-1" style="height:36px; align-items: center;">
                        <div class="flex pt-2" >
                            <div class="mt-0" style="margin-top: 2px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 22 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                            </div>
                            <span class="ml-2" style="font-weight: 500; color:white; font-size:1rem;">Nuevo proceso</span>
                        </div>
                        <div><span ><a-button @click="cerramodal()" style="background:none; border:none; color:white; font-size: 1.1rem;">x</a-button></span></div>
                    </div>

                </div> 

            <a-form
                ref="formDatos"
                name="form"
                :model="form" 
                :rules="formRules">
                <div class="flex">
                    <div style="width: 100%;" class="mt-4">
                        <div>
                            <a-form-item
                                    name="nombre"
                                    :rules="[ { required: true, message: 'Ingresa el nombre del proceso', trigger: 'change'},
                                    ]"
                                >
                                <label>Nombre<span style="">*</span></label>
                                <a-input v-model:value="form.nombre" style="width:100%; border-radius: 4px; height: 36px;" placeholder="Nombre del proceso">
                                    <template #prefix>
                                        <span style="color: #0000009d; margin-top: -6px;"><FolderOutlined/></span>
                                    </template>
                                </a-input>
                            </a-form-item>
                        </div>

                        <div class="mt-2" style="width: 100%;">
                            <label>Ubicación<span style="color:red;">*</span></label>
                            <a-form-item 
                                name="ubigeo"
                                :rules="[ { required: true, message: 'Seleccione la ubicación', trigger: ['change', 'blur']},]"
                            >
                                <a-auto-complete
                                    v-model:value="ubigeo"                
                                    :options="ubigeos"
                                    @select="onSelectUbigeo"
                                >
                                    <a-input
                                        placeholder="Procedencia del Colegio"
                                        v-model:value="buscarUbigeo"
                                        style=" border-radius: 4px; height: 36px;"
                                    >
                                        <template #prefix>
                                            <span style="color: #0000009d; margin-top: -6px;"><EnvironmentOutlined/></span>
                                        </template>
                                        <template #suffix>
                                            <down-outlined/>
                                        </template>
                                    </a-input>
                                </a-auto-complete>
                            </a-form-item>
                        </div>

                        <div class="mt-2" style="width: 100%;">
                            <label>Fecha<span style="color:red;">*</span></label>
                            <a-form-item
                                    name="fecha"
                                    :rules="[ { required: true, message: 'Ingresa tu fecha de nacimiento', trigger: 'change'},]"
                                >
                                <a-date-picker style="width:100%; border-radius: 4px; height: 36px;" placeholder="Seleccionar fec. nacimiento" v-model:value="form.fecha" format="DD/MM/YYYY">
                                    <template #prefix>
                                        <span style="color: #0000009d; margin-top: -6px;"><EnvironmentOutlined/></span>
                                    </template>
                                </a-date-picker>
                            </a-form-item>
                        </div>
                    </div>

                </div>

            </a-form>
            
                <div class="mt-0 mb-2">
                    <div style="margin:-24px; margin-top: -5px;" class="px-6 pt-4 pb-3 flex justify-end">
                        <div class="mr-2">
                            <a-button  style="border: 1px solid #476175; color:#476175; width:100px; height: 36px; border-radius:4px;"> Cancelar </a-button>
                        </div>
                        <div>
                            <a-button @click="guardar()" style="background: #476175; border:none; color:white; width:100px; height: 36px; border-radius:4px;"> _Guardar </a-button>
                        </div>
                    </div>
                </div>
            </a-modal>
        </div>
        
        
        <div v-if="breadcrumb.length === 2" class="mt-3">
            <a-tabs v-model:activeKey="activeKey" type="card" tab-position="top">
                <a-tab-pane key="1" tab="Participantes" style="margin: -10px;">
                    <div>
                        <Participantes :proceso="procesoseleccionado"/>
                    </div>
                </a-tab-pane>
                <a-tab-pane key="2" tab="Ides" style="margin: -10px;">
                    <div>
                        <Ides :proceso="procesoseleccionado"/>
                    </div>
                </a-tab-pane>
                <a-tab-pane key="3" tab="Respuestas" style="margin: -10px;">
                    <div>
                        <Resp :proceso="procesoseleccionado"/>
                    </div>
                </a-tab-pane>
                <a-tab-pane key="4" tab="Res correctas">
                    <div>
                        <Patron :proceso="procesoseleccionado"/>
                    </div>
                </a-tab-pane>
                <a-tab-pane key="5" tab="Calificación">
                    <Calificacion :proceso="procesoseleccionado"/>
                </a-tab-pane>
            </a-tabs>
        </div>
    </div>
</div>
</Layout>    
</template>

<script setup>
import {ref, watch, reactive} from 'vue';
import axios from 'axios';
import { FolderOutlined, HomeOutlined, EnvironmentOutlined, DownOutlined, FormOutlined, DeleteOutlined, SaveOutlined, SearchOutlined, EyeOutlined } from '@ant-design/icons-vue';
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/LayoutCalificador.vue'
import Ides from './components/leer-ide.vue'
import Resp from './components/leer-resp.vue'
import Patron from './components/leer-patron.vue'
import Participantes from './components/participantes.vue'
import Calificacion from './components/calificacion.vue'

const breadcrumb = ref([{ "id": 0, "nombre": "Simulacros"}]);

const archivos = ref([]);
const carpeta = ref(null);
const carpetas = ref([]);
const carpetaId = ref(1);
const modalNuevo = ref(false);
const nombre = ref("");

const procesoseleccionado = ref(null)

const formDatos = ref();
const form = reactive({  
    nombre:'',  
    fecha:'', 
    ubigeo:null,
});

const ubigeo = ref(null)
const ubigeoseleccionado = ref(null)
const buscarUbigeo = ref(null)
const ubigeos = ref([])
let timeoutId;
const activeKey = ref('1');

const simulacros = ref([])
const buscar = ref("")

const customRow = (record) => {
    breadcrumb.value.push({ "id": record.id, "nombre": "/ "+record.nombre});
    procesoseleccionado.value = record.id;
}


watch(ubigeoseleccionado, ( newValue, oldValue ) => { form.ubigeo = newValue })
watch(buscarUbigeo, ( newValue, oldValue ) => { 
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        getUbigeosColegio() 
    }, 500);    
})

watch(buscar, ( newValue, oldValue ) => { 
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        getSimulacros() 
    }, 400);    
})


const onSelectUbigeo = (value, option) => { ubigeoseleccionado.value = option.key; };

const getUbigeosColegio = async () => {
    axios.post("/get-ubigeo",{"term": buscarUbigeo.value})
    .then((response) => {
        ubigeos.value = response.data.datos.data;
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
  });
}


const getSimulacros = async () => {
    axios.post("/get-sim",{"term": buscar.value})
    .then((response) => {
        simulacros.value = response.data.datos.data;
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
  });
}

getSimulacros()


const guardar = async () => {
    const values = await formDatos.value.validateFields();
    const response = await axios.post("/simulacro/save-simulacro",{
        nombre: form.nombre,
        estado: 1,
        ubigeo: form.ubigeo,
        anio:'2024',
        fecha: form.fecha,
    });
    modalNuevo.value = false;
    await getSimulacros();
}

const getCarpetas = async ( ) => {
    try {
        const response = await axios.get("/raiz/ver-contenido-carpeta/"+carpetaId.value);
        carpetas.value = response.data.carpetas;
        carpeta.value = response.data.carpeta;
    } catch (error) {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
        } else {
            console.error('Error de configuración:', error.message);
        }
    }
};

const creatCarpeta = async ( ) => {
    try {
        const response = await axios.post("/carpetas/crear-carpeta",{nombre: nombre.value, carpeta_padre_id: carpetaId.value });
        getCarpetas();
        modalNuevo.value = false;
    } catch (error) {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
        } else {
            console.error('Error de configuración:', error.message);
        }
    }
};



const entrarCarpeta = async (elemento) => {
    const indice = breadcrumb.value.indexOf(elemento);
    
    if (indice !== -1) {
        breadcrumb.value.splice(indice + 1, breadcrumb.value.length - indice);
    } else {
        breadcrumb.value.push(elemento);
    }
    carpetaId.value = elemento.id;
} 

watch(carpetaId, ( newValue, oldValue ) => { getCarpetas() })

const clicIzquierdo = (event) => {
    showContextMenu.value = false;
}

const cerramodal = () => {
    modalNuevo.value = false;
}




//getCarpetas();

const showContextMenu = ref(false);
const contextMenuTop = ref(0);
const contextMenuLeft = ref(0);


const handleContextMenu = (event) => {
    
  showContextMenu.value = true;
  contextMenuTop.value = event.clientY;
  contextMenuLeft.value = event.clientX;
  event.preventDefault();
};

const handleMenuItemClick = ( opcion ) => {
    if(opcion === '1'){
        modalNuevo.value = true;
        showContextMenu.value = false;
    }else{
        console.log("2");
    }

};



const columnsSimulacros = [
    { title: 'N°', dataIndex: 'nro', width:'40px', align:"center"},
    { title: 'Nombre', dataIndex: 'nombre',},
    { title: 'Fecha', dataIndex: 'fecha', align:'center'},
    { title: 'Vigente', dataIndex: 'estado', align:'center', width:'100px'},
    { title: 'Acciones', dataIndex: 'acciones', align:'center', width:'96px'},
];

getUbigeosColegio()
</script>
<style>
.carpetas-select{
    cursor:pointer;
    text-align: center;
    background: none;
}
.carpetas-select:hover{
    text-align: center;
    background: #0f0f002c;
    border-radius: 5px;
}
.selec-menu{
    height:26px; 
}
.selec-menu:hover{
    background:#cdf3f3; 
}

</style>