<template>
<Head title="Carreras previas"/>
<AuthenticatedLayout>
<div class="overflow-hidden shadow-sm sm:rounded-lg p-4" style="height: calc(100vh - 102px); background: var(--card-bg, #ffffff); color: var(--card-text, #1e293b); border: 1px solid var(--card-border, #e2e8f0);">
<row class="flex justify-between mb-4" >
    <div class="mr-3">
        <a-button type="primary" style="border-radius: 5px; background: #476175" @click="showModalPrograma">Agregar</a-button>
    </div>
    <div class="flex justify-between" style="position: relative;" >
        <a-input type="text" placeholder="buscar...." v-model:value="buscar" style="max-width: 300px; padding-left: 30px;">
            <template #prefix>
                <user-outlined />
            </template>
        </a-input>
    </div>
</row>

<div style="">
    <a-table 
        :columns="columnsProgramas" 
        :data-source="carreras_previas"
        :pagination="false"
        size="small"
        :scroll="{ x: 380, y: 'calc(100vh - 240px)' }"
        >
        <template #bodyCell="{ column, index, record }">
            <template v-if="column.dataIndex === 'codigo'">
                <div><span style="font-size: .9rem">{{ record.codigo }}</span></div>                
            </template>
            <template v-if="column.dataIndex === 'dni-postulante'">
                <div><span style="font-size: .9rem">{{ record.dni_postulante }}</span></div>                
            </template>
            <template v-if="column.dataIndex === 'nombres'">
                <div><span style="font-size: .9rem;"> {{ record.nombre }}</span></div>                
            </template>
            <template v-if="column.dataIndex === 'nombres'">
                <div><span style="font-size: .8rem;">{{ record.paterno }} {{ record.materno }} {{ record.nombres }}</span></div>                
            </template>
            <template v-if="column.dataIndex === 'facultad'">
                <div><span style="font-size: .9rem;">{{ record.facultad }}</span></div>                
            </template>
            <template v-if="column.dataIndex === 'area'">
                <div class="flex" style="justify-content: center;">
                    <a-tag style="font-size: .8rem;" color="cyan" v-if=" carreras_previas[index].area == 'BIOMÉDICAS'">{{ carreras_previas[index].area }}</a-tag>
                    <a-tag style="font-size: .8rem;" color="purple" v-if=" carreras_previas[index].area == 'SOCIALES'">{{ carreras_previas[index].area }}</a-tag>
                    <a-tag style="font-size: .8rem;" color="blue" v-if=" carreras_previas[index].area == 'INGENIERÍAS'">{{ carreras_previas[index].area }}</a-tag>
                </div>
            </template>
            
            <template v-if="column.dataIndex === 'condicion'">
                <div class="flex" style="justify-content: center;">
                    <div>
                        <a-tag color="purple">{{ record.condicion }}</a-tag>
                    </div>
                </div>
            </template>

            <template v-if="column.dataIndex === 'acciones'">
                <a-button type="" @click="verDetalle(record)" style="border-radius:4px; background: none; color: green" size="small">
                    <template #icon><eye-outlined/></template>
                </a-button>
                <a-button type="" @click="abrirEditar(record)" style="border-radius:4px; background: none; color: var(--card-muted, #64748b)" size="small">
                    <template #icon><form-outlined/></template>
                </a-button>
                <a-button class="" @click="eliminar(record.id)" style="border-radius:4px; background: none; color: red;" shape="" size="small">
                <template #icon><delete-outlined/></template>
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

<div>
    <a-modal v-model:open="visible" :title="anterior.id == null?'Nuevo Programa':'Editar Programa'" style="margin-top: -40px;">

        <a-form :model="anterior" ref="formAnterior">

            <a-form-item label="Código" name="codigo"
                :rules="[{ required: true, message: 'Campo requerido', trigger: 'change' }]">
              <a-input v-model:value="anterior.codigo" 
                placeholder="Ingrese Código">
                <template #prefix>
                  <KeyboardOutlined />
                </template>
              </a-input>
            </a-form-item>
            <a-form-item
                label="Carrera"
                name="carrera"
                :rules="[{ required: true, message: 'Campo requerido', trigger: 'change' }]"
            >
                <a-select
                v-model:value="selectedCodCar"
                placeholder="Seleccione una carrera"
                @change="handleChange"
                >
                <a-select-option
                    v-for="carrera in carreras"
                    :key="carrera.cod_car"
                    :value="carrera.cod_car"
                >
                    {{ carrera.nombre }}
                </a-select-option>
                </a-select>
            </a-form-item>

            <a-form-item label="Condición" name="condicion"
                :rules="[{ required: true, message: 'Campo requerido', trigger: 'change' }]">
              <a-input v-model:value="anterior.condicion" placeholder="Ingrese Condición">
                <template #prefix>
                  <KeyboardOutlined />
                </template>
              </a-input>
            </a-form-item>

            <a-form-item label="DNI Postulante" name="dni_postulante"
                :rules="[{ required: true, message: 'Campo requerido', trigger: 'change' }]">
              <a-input v-model:value="anterior.dni_postulante" placeholder="Ingrese DNI">
                <template #prefix>
                  <KeyboardOutlined />
                </template>
              </a-input>
            </a-form-item>

          </a-form>

    <template #footer>
        <a-button style="margin-left: 10px;" @click="resetForm">Cancelar</a-button>
        <a-button type="primary" @click="guardar()">Guardar</a-button>
    </template>
    </a-modal>
</div>

<a-modal v-model:open="modalAnterior" title="Carreras anteriores"  :footer="false" style="margin-top: -40px;">
    <div class="flex justify-center">
        <div style="width: 100%; max-width: 1000px; margin-top:20px;">    
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
        
        <div>
            <a-button @click="registrarPrevias()">Registrar</a-button>
        </div>
        </div>
    </div>
</a-modal>

</template>
    
<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { watch, computed, ref, unref, reactive } from 'vue';
import { EyeOutlined, FormOutlined, DeleteOutlined, SearchOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import { message } from 'ant-design-vue';
import axios from 'axios';

const buscar = ref("");
const pagina = ref(1)
const totalpaginas = ref(null)
const modalAnterior = ref(false)

const visible = ref(false);
const carreras_previas = ref([])
const formAnterior = ref(null);
const anterior = reactive({
    id: "",
    codigo: "",
    carrera:"",
    condicion: "",
    dni_postulante: ""
})

const showModalPrograma = () => {
    visible.value = true;
};

watch(buscar, ( newValue, oldValue ) => { getProgramas() })
watch(pagina, ( newValue, oldValue ) => { getProgramas() })

const abrirEditar = (item) => {
    visible.value = true;
    programa.value.id = item.id;
    programa.value.codigo = item.codigo;
    programa.value.nombre = item.nombre;
    programa.value.nivel_academico = item.nivel_academico;
    programa.value.tipo_autorizacion = item.tipo_autorizacion;
    programa.value.id_facultad = item.id_fac;
    if(item.estado == 1){ programa.value.estado = true }
    else { programa.value.estado = false}
    programa.value.area = item.area
}


const getProgramas =  async (term = "") => {
    let res = await axios.post(
    "get-carreras-previas-registrado?page=" + pagina.value,
    { term: buscar.value }
    );
    carreras_previas.value = res.data.datos.data;
    totalpaginas.value = res.data.datos.total;
}

const guardar = async () => {
    await formAnterior.value.validate();
    axios.post("save-carrera-previa", anterior).then((result) => {
        getProgramas()
        notificacion('success',result.data.titulo, result.data.mensaje);
        visible.value = false;  
    });
}

const eliminar = (id) => {
    axios.get("eliminar-carrera-previa/"+id).then((result) => {
        getProgramas();
        notificacion('warning', 'PROGRAMA ELIMINADO', result.data.mensaje );
    });
}

const columnsProgramas = [
    { title: 'Cod', dataIndex: 'codigo', width:'60px', align:'center', responsive: ['md'],},
    { title: 'N° doc', dataIndex: 'dni_postulante', width:"80px"},
    { title: 'Nombre', dataIndex: 'nombres'},
    { title: 'Programa', dataIndex: 'programa', align:'left', responsive: ['md'],},
    { title: 'Condicion.', dataIndex: 'condicion', width:"120px", align:'center', responsive: ['md'],},
    { title: 'Acciones', dataIndex: 'acciones', width:"90px", align:'center'},
];

const notificacion = (type, titulo, mensaje) => { notification[type]({ message: titulo, description: mensaje, });
};

const verDetalle = (item) => {  
    getDataPrisma(item.dni_postulante)
    modalAnterior.value = true;
};

const carreras = [
  { cod_car: '23', nombre: 'INGENIERÍA DE SISTEMAS' },
  { cod_car: '01', nombre: 'INGENIERÍA AGRONÓMICA' },
  { cod_car: '12', nombre: 'TURISMO' },
  { cod_car: '07', nombre: 'ADMINISTRACIÓN' },
  { cod_car: '14', nombre: 'CIENCIAS DE LA COMUNICACIÓN SOCIAL' },
  { cod_car: '11', nombre: 'SOCIOLOGÍA' },
  { cod_car: '03', nombre: 'INGENIERÍA TOPOGRÁFICA Y AGRIMENSURA' },
  { cod_car: '20', nombre: 'EDUCACIÓN PRIMARIA' },
  { cod_car: '17', nombre: 'EDUCACIÓN SECUNDARIA' },
  { cod_car: '31', nombre: 'INGENIERÍA GEOLÓGICA' },
  { cod_car: '08', nombre: 'ENFERMERÍA' },
  { cod_car: '15', nombre: 'BIOLOGÍA' },
  { cod_car: '35', nombre: 'INGENIERÍA AGRÍCOLA' },
  { cod_car: '29', nombre: 'ODONTOLOGÍA' },
  { cod_car: '28', nombre: 'NUTRICIÓN HUMANA' },
  { cod_car: '13', nombre: 'ANTROPOLOGÍA' },
  { cod_car: '56', nombre: 'ARTE' },
  { cod_car: '05', nombre: 'INGENIERÍA ECONÓMICA' },
  { cod_car: '32', nombre: 'INGENIERÍA CIVIL' },
  { cod_car: '06', nombre: 'CIENCIAS CONTABLES' },
  { cod_car: '04', nombre: 'MEDICINA VETERINARIA Y ZOOTECNIA' },
  { cod_car: '30', nombre: 'INGENIERÍA METALÚRGICA' },
  { cod_car: '24', nombre: 'INGENIERÍA ELECTRÓNICA' },
  { cod_car: '22', nombre: 'INGENIERÍA ESTADÍSTICA E INFORMÁTICA' },
  { cod_car: '34', nombre: 'CIENCIAS FÍSICO MATEMÁTICAS' },
  { cod_car: '16', nombre: 'EDUCACIÓN SECUNDARIA' },
  { cod_car: '33', nombre: 'ARQUITECTURA Y URBANISMO' },
  { cod_car: '09', nombre: 'TRABAJO SOCIAL' },
  { cod_car: '10', nombre: 'INGENIERÍA DE MINAS' },
  { cod_car: '36', nombre: 'INGENIERÍA MECÁNICA ELÉCTRICA' },
  { cod_car: '02', nombre: 'INGENIERÍA AGROINDUSTRIAL' },
  { cod_car: '26', nombre: 'INGENIERÍA QUÍMICA' },
  { cod_car: '25', nombre: 'DERECHO' },
  { cod_car: '21', nombre: 'EDUCACIÓN INICIAL' },
  { cod_car: '18', nombre: 'EDUCACIÓN FÍSICA' },
  { cod_car: '27', nombre: 'MEDICINA HUMANA' },
  { cod_car: '99', nombre: 'INGENIERÍA AGRíCOLA' }
];

const selectedCodCar = computed({
  get() {
    return anterior.carrera ? anterior.carrera.cod_car : null;
  },
  set(newVal) {
    anterior.carrera = carreras.find(item => item.cod_car === newVal) || null;
  },
});


const handleChange = (val) => {
  console.log('Seleccionado cod_car:', val);
  console.log('Objeto carrera:', anterior.carrera);
};

getProgramas()




const participante = ref(null);
const temp = ref(null);
const anteriores = ref([]);

const getDataPrisma = async ( dni ) => {
    anteriores.value = [];
    participante.value = null;
    const response = await axios.get('/get-data-prisma/'+dni);
    if(response.data.estado === true){
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
::-webkit-scrollbar {
width: 9px;
height: 12px;
}

::-webkit-scrollbar-track {
background: #f1f1f1; 
border-radius: 10px;
}

::-webkit-scrollbar-thumb {
background: #888; 
border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
background: #555; 
}

/* Estilo para el scroll específico en Webkit (Chrome, Safari) */
.scroll-container::-webkit-scrollbar {
width: 12px;
height: 12px;
}

.scroll-container::-webkit-scrollbar-track {
background: #f1f1f1; 
border-radius: 10px;
}

.scroll-container::-webkit-scrollbar-thumb {
background: #888; 
border-radius: 10px;
}

.scroll-container::-webkit-scrollbar-thumb:hover {
background: #555; 
}

</style>

<style>
.theme-dark .ant-table,
.theme-hybrid .ant-table {
    background: transparent !important;
    color: var(--card-text) !important;
}
.theme-dark .ant-table-thead > tr > th,
.theme-hybrid .ant-table-thead > tr > th {
    background: var(--table-header-bg) !important;
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
}
.theme-dark .ant-table-tbody > tr > td,
.theme-hybrid .ant-table-tbody > tr > td {
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
    background: var(--card-bg) !important;
}
.theme-dark .ant-table-tbody > tr:hover > td,
.theme-hybrid .ant-table-tbody > tr:hover > td {
    background: var(--hover-bg) !important;
}
.theme-dark .ant-table-tbody > tr:nth-child(even) > td,
.theme-hybrid .ant-table-tbody > tr:nth-child(even) > td {
    background: var(--row-even) !important;
}
</style>

<!-- <template>
<Layout>
<div class="flex justify-center">
<div style="width: 100%; max-width: 1000px; margin-top:20px;">    
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

<div>
    <a-button @click="registrarPrevias()">Registrar</a-button>
</div>

</div>                       
</div>
</Layout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import { computed, ref, defineProps } from 'vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';


const participante = ref(null);
const temp = ref(null);
const anteriores = ref([]);

const getDataPrisma = async () => {
participante.value = null;
const response = await axios.get('/get-data-prisma/'+temp.value);
if(response.data.estado === true){
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
</style> -->