<template>
<Head title="Inscritos"/>    
<Layout>
<div class="mb-4" style="width:100%;">

    
<div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: rgba(0, 0, 0, 0.1) 0px 20px 25px -5px, rgba(0, 0, 0, 0.03) 0px 10px 10px -5px;">
    <div class="flex justify-between mt-5 mb-4 mr-4 ml-4">
        <div>
            <div>
                <a-button @click="modal = true" style="background-color:#476175; border-radius: 5px; border: none;" type="primary">nuevo</a-button>                
            </div>
        </div>
        <div class="flex justify-between" style="position: relative;">
            <div>
                <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 340px; padding-left: 30px; border-radius: 6px;"/>
                <div class="mr-2" style="position: absolute; left: 8px; top: 3px; "><search-outlined /></div>
            </div>
        </div>
    </div>

    <div>
        <a-table :dataSource="inscritos" size="" :columns="columns" :pagination="false">
                <template #bodyCell="{ column, index, record }">
                <template v-if="column.dataIndex === 'nro_doc'">                    
                    <a-tag color="#4f4f4f" style="width:80px;">{{  record.nro_doc }}</a-tag>
                </template>
                <template v-if="column.dataIndex === 'nombres'">
                    <div>
                        <span>{{ record.nombres }} {{ record.paterno }} {{ record.materno }}</span>
                    </div>
                </template>
                <template v-if="column.dataIndex === 'acciones'">
                    <a-button type="success" class="mr-1" style="color: #476175;" size="small" disabled>
                        <template #icon><EyeOutlined/></template>
                    </a-button>
                    <a-button class="mr-1" @click="abrirEditar(record)" style="color: blue;" size="small" disabled>
                        <template #icon><form-outlined/></template>
                    </a-button>
                    <a-popconfirm
                        title="¿Estas seguro de eliminar?"
                        @confirm="eliminar(record)"
                        disabled
                        >
                        <a-button  size="small" style="color: crimson;" disabled>
                            <template #icon><delete-outlined disabled/></template>
                            </a-button>
                    </a-popconfirm>
    
                </template>    
            </template>
        </a-table>
    </div>
    <div class="flex justify-between mb-6 mt-2 pr-4">
        <div>
            <a-pagination v-model:current="pagina" :page-size="pageSize" simple :total="totalRegistros" show-less-items />
        </div>
        <div clas="" style="scale: 0.9;"> 
            <a-select
                v-model:value="pageSize"
                style="width: 90px;">
                <a-select-option :value="10">10 Reg.</a-select-option>
                <a-select-option :value="20">20 Reg.</a-select-option>    
                <a-select-option :value="50">50 Reg.</a-select-option>    
                <a-select-option :value="100">100 Reg.</a-select-option>    
            </a-select>
        </div>
    </div>
</div>
</div>

<a-modal v-model:visible="modal" :footer="false" centered style="width:100%; max-width: 1000px;" :closable="false">
    <div style="display: flex; justify-content: space-between; background: #476175; margin: -24px; margin-bottom: 0px; height: 46px;">
        <div class="ml-3 mt-3">
            <span style="color: white; font-size: 1.1rem;"> Nueva inscripcion </span>
        </div>
        <div class="mr-4 mt-1">
            <span style="color: white; font-size: 1.4rem;">x</span>
        </div>
    </div>
    <!-- <div style="display: flex; background: red; width: 100%;">
        <span v-if="form.id === null" style="font-weight: bold; font-size: 1.1rem;"> Nueva inscripcion </span>
        <span v-else style="font-weight: bold; font-size: 1.1rem;">Editar Inscripcion</span>
    </div> -->

    <div class="mt-4">
        <label>Pago<span style="color:red;">*</span> {{ idpago }}</label>
        <a-form-item name="ubigeo_colegio">
            <a-auto-complete
                v-model:value="idpago"                
                :options="pagos"
                @select="onSelectPagos"
            >
            <template #option="item">
                <div class="flex justify-between">
                    <div>
                        <div>
                            <span>
                               <span style="font-weight: bold; font-size: .8rem;"> EXAMEN DE ADMISIÓN - SIMULACRO</span>  
                            </span>
                        </div>
                        <div style="margin-top: -8px;">
                            <span style="font-size: .6rem;">
                               {{ (item.value) }} - {{ (item.nombre) }}
                            </span>
                        </div>

                    </div>
                    <div>
                        <div>
                            <span style="font-size: .9rem; font-weight: bold;">
                                S/{{ parseInt(item.monto).toFixed(2) }}
                            </span>
                        </div>
                        <div style="margin-top: -8px;">
                            <span style="font-size: .6rem;"> 14-12-2023</span>
                        </div>
                     </div>

                </div>


                </template>

                <a-input
                    placeholder="Seleccionar pago"
                    v-model:value="buscarPago"
                    style="border-radius: 6px;"
                >
                    <template #suffix>
                        <a-tooltip title="Extra information">
                        <down-outlined/>
                        </a-tooltip>
                    </template>
                </a-input>
            </a-auto-complete>
        </a-form-item>
    </div>

    <div class="flex justify-center">
    <row style="display:flex; justify-content:center;">
        <a-col :span="24">
            <a-form
                ref="formDatos"
                name="form"
                :model="form">
                <a-row :gutter="16" class="mt-3" >
                    <a-col :xs="24" :sm="12" :md="8" :lg="8">
                    <label>Cod. Modular</label>
                    <a-form-item 
                        name="cod_modular" 
                        :rules="[{ required: true, message: 'Ingrese el cod modular'},                                            
                        { min: 7, message: 'Debe tener 7 digitos', trigger: 'blur'}]"
                    >
                    <a-input v-model:value="form.cod_modular" @input="codInput" :maxlength="7"/>
                    </a-form-item>
                </a-col>
                
                <a-col :xs="24" :sm="12" :md="8" :lg="16">
                    <label>Nombre del colegio<span style="color:red;">*</span></label>
                    <a-form-item name="nombre" :rules="[{ required: true, message: 'Ingrese el nombre del colegio' }]">
                    <a-input v-model:value="form.nombre"/>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="8" :lg="8">
                    <label>Cod de local<span style="color:red;">*</span></label>
                    <a-form-item name="cod_local" :rules="[{ required: true, message: 'Ingrese el codigo del local'}]">
                    <a-input v-model:value="form.cod_local"/>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="8" :lg="8">
                    <label>Nivel<span style="color:red;">*</span></label>
                    <a-form-item name="nivel" :rules="[{ required: true, message: 'Seleccione el nivel' }]">
                        <a-select
                            v-model:value="form.nivel"
                            style="width: 100%">
                            <a-select-option value="SECUNDARIA">SECUNDARIA</a-select-option>
                            <a-select-option value="BASICA ALTERNATIVA">BASICA ALTERNATIVA</a-select-option>    
                            <a-select-option value="BASICA ALTERNATIVA AVANZADO">BASICA ALTERNATIVA AVANZADO</a-select-option>    
                            <a-select-option value="BÁSICA ALTERNATIVA SECUNDARIA">BÁSICA ALTERNATIVA SECUNDARIA</a-select-option>    
                        </a-select>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="8" :lg="8">
                    <label>Gestión<span style="color:red;">*</span></label>
                    <a-form-item name="gestion" :rules="[{ required: true, message: 'Selecciona la gestión' }]">
                        <a-select
                            v-model:value="form.gestion"
                            style="width: 100%">
                            <a-select-option value="PÚBLICA DE GESTIÓN DIRECTA">PÚBLICA DE GESTIÓN DIRECTA</a-select-option>
                            <a-select-option value="PRIVADA">PRIVADA</a-select-option>    
                            <a-select-option value="PÚBLICA DE GESTIÓN PRIVADA">PÚBLICA DE GESTIÓN PRIVADA</a-select-option>    
                            <a-select-option value="EXTRANJERO">EXTRANJERA</a-select-option>    
                        </a-select>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="24" :lg="12">
                            <label>Ubicación: Dep/Prov/Dist <span style="color:red;">*</span></label>
                            <a-form-item name="ubigeo">
                                <a-auto-complete
                                    v-model:value="residencia"                
                                    :options="residencias"
                                    @select="onSelectResidencias"
                                >
                                    <a-input
                                        placeholder="Lugar"
                                        v-model:value="buscarResidencia"

                                    >
                                        <template #suffix>
                                            <a-tooltip title="Extra information">
                                            <down-outlined/>
                                            </a-tooltip>
                                        </template>
                                    </a-input>
                                </a-auto-complete>
                            </a-form-item>
                        </a-col>
                <a-col :xs="24" :sm="24" :md="24" :lg="12">
                    <label>Dirección</label>
                    <a-form-item
                        name="direccion">
                        <a-input v-model:value="form.direccion"/>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <label>Observación</label>
                    <a-form-item name="observacion">
                        <a-textarea v-model:value="form.observacion"/>
                    </a-form-item>
                </a-col>
            </a-row>
            <div class="flex justify-end">
                <div>
                    <a-button type="primary" class="mr-2" @click="modal = false" style="border-radius: 6px; background: none; color:#476175; border: 1px solid #476175;">Cancelar</a-button>
                    <a-button type="primary" @click="save()" style="border-radius: 6px; background: #476175; border: none;" :loading="loading">Guardar</a-button>
                </div>
            </div>
        </a-form>
    </a-col>
</row>

</div>
</a-modal>

</Layout>
</template>
    
<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/LayoutSimulacro.vue'
import { watch, computed, ref, reactive } from 'vue';
import { TeamOutlined, FormOutlined, DownOutlined, PrinterOutlined, DeleteOutlined, SearchOutlined, EyeOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';
    

const idpago = ref(null);
const buscarPago = ref(null);


const colegios = ref([])
const buscar = ref("")
const pagina = ref(1)
const totalRegistros = ref(null)
const pageSize = ref(10)
const modal = ref(false)

const residencia = ref(null)
const buscarResidencia = ref(null)
const residencias = ref([])
const gestion = ref(null)
const loading = ref(false)

const departamentos = ref();
const buscarDep = ref();
const dep = ref()
const provincias = ref();
const buscarProv = ref();
const prov = ref()
const distritos = ref();
const buscarDist = ref();
const dist = ref()

const codInput = (event) => { form.cod_modular = event.target.value.replace(/\D/g, ''); };
const form = reactive({  
    cod_modular: '', 
    nombre:'',
    cod_local:'',
    nivel:'', 
    gestion: null, 
    ubigeo:'',
    direccion:'',
    observacion:''
});
const formDatos = ref(null)

const save = async () => {
    loading.value = true;
    setTimeout(() => {
        console.log("Este código se ejecutará después de 2 segundos");
    }, 1500);
    try {        
        const values = await formDatos.value.validateFields();
        const response = await axios.post('save', form);
        if (response.status === 202) {
            console.log(response.data.errors);
                loading.value = false;
        } else {
            resetForm();
            modal.value = false;
            notificacion('success', response.data.titulo, response.data.mensaje);
            loading.value = false;
            // getColegios();
        }
    } catch (error) {
        console.error(error);
        loading.value = false;
    }

}

// const getColegios = async () => {
//     axios.post("get-colegios?page="+pagina.value, 
//         {
//             "term": buscar.value, 
//             paginashoja: pageSize.value, 
//             dep: dep.value, 
//             ges: gestion.value,
//             prov: prov.value,
//             dist: dist.value
//         })
//     .then((response) => {
//         colegios.value = response.data.datos.data;
//         totalRegistros.value = response.data.datos.total;
//     })
//     .catch((error) => {
//         if (error.response) {
//             console.error('Error de servidor:', error.response.data);
//         } else if (error.request) {
//             console.error('Error de red:', error.request);
//             } else { console.error('Error de configuración:', error.message); }
//     });
// }

const getUbigeosResidencia = async () => {
    axios.post("/get-ubigeo",{"term": buscarResidencia.value})
    .then((response) => {
        residencias.value = response.data.datos.data;
        console.log('Datos recibidos:', residencias);
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
    });
}

const inscritos = ref([]);
const getInscritos = async () => { 
    let res = await axios.post( "get-inscritos-simulacro?page="+pagina.value,{
        "term": buscar.value,
        paginashoja: pageSize.value
    });
    inscritos.value = res.data.datos.data
    totalRegistros.value = res.data.datos.total;
}
getInscritos()

const getDepartamentos = async () => { 
    let res = await axios.post( "/get-departamentos-codigo", { term: buscarDep.value});
    departamentos.value = res.data.datos.data
}

const getProvincias = async () => {
    let res = await axios.post( "/get-provincias-codigo?page=", {departamento: dep.value });
    provincias.value = res.data.datos
}

const getDistritos = async (depp) => {
    let res = await axios.post( "/get-distritos-codigo?page=", { departamento: dep.value, provincia: prov.value });
    distritos.value = res.data.datos
}

watch(pagina, ( newValue, oldValue ) => { getInscritos(); })
watch(pageSize, ( newValue, oldValue ) => { getInscritos(); })
watch(buscar, ( newValue, oldValue ) => { getInscritos(); })
watch(buscarResidencia, ( newValue, oldValue ) => {  if(newValue.length >= 3){ getUbigeosResidencia() }})
watch(buscarDep, ( newValue, oldValue ) => {  if(newValue.length >= 3){ getDepartamentos() }})
watch(buscarProv, ( newValue, oldValue ) => {  if(newValue.length >= 3){ getProvincias() }})
watch(buscarDist, ( newValue, oldValue ) => {  if(newValue.length >= 3){ getDistritos() }})
watch(gestion, ( newValue, oldValue ) => {  if(newValue.length >= 3){ getColegios() }})


const onSelectResidencias = (value, option) => { form.ubigeo = option.key; };
const onSelectPagos = (value, option) => {  idpago.value = option.value+"-"+option.nombre }

const onSelectDepartamentos = (value, option) => { dep.value = option.key; getColegios(); getProvincias(); };
const onSelectProvincias = (value, option) => { prov.value = option.key; getColegios(); getDistritos(); };
const onSelectDistritos = (value, option) => { dist.value = option.key; getColegios() };

getDepartamentos()
getUbigeosResidencia()

const abrirEditar = (item) => {
    modal.value = true;
    form.id = item.id
    form.cod_modular = item.cod_modular
    form.nombre = item.nombre
    form.cod_local = item.cod_local
    form.gestion = item.gestion
    form.nivel = item.nivel
    form.ubigeo = item.ubigeo
    residencia.value = item.lugar
    form.direccion = item.direccion
    form.observacion = item.observacion    
}

const resetForm = () => {
    for (const key in form) {
    form[key] = null;
    }
};

const notificacion = (type, titulo, mensaje) => {
    notification[type]({
    message: titulo,
    description: mensaje,
    });
};
const columns= ref([
    {
        title: 'Nro_Doc',
        dataIndex: 'nro_doc',
    },
    {
        title: 'Nombres',
        dataIndex: 'nombres',
        responsive: ['xs','sm','md','lg'],
    },
    {
        title: 'Programa',
        dataIndex: 'programa',
        responsive: ['md'],
    },
    {
        title: 'Colegios',
        dataIndex: 'colegio',
        responsive: ['lg'],
    },
    {
        title: 'Lugar',
        dataIndex: 'lugar',
        responsive: ['lg'],
    },
    {
        title:'Acciones',
        dataIndex: 'acciones',
        width:'120px',
        align:'center'
    }
],
);


const pagos = ref([
    {
        nombre:"JHON ARIEL LUQUE CUSACANI",
        value: "70757837",
        monto: 15.00,
        fecha: "12-12-2023"
    },
    {
        nombre:"JHON ARIEL LUQUE CUSACANI",
        value: "70757838",
        monto: 15.00,
        fecha: "12-12-2023"
    },
    {
        nombre:"JHON ARIEL LUQUE CUSACANI",
        value: "70757839",
        monto: 15.00,
        fecha: "12-12-2023"
    },

])

</script>
