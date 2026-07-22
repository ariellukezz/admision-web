<template>
<Head title="Colegios"/>    
<Layout>
<div class="mb-4" style="width:100%;">
<div class="p-6 mb-4"  style="background: var(--card-bg, #ffffff); border-radius: 12px; overflow: hidden; box-shadow: rgba(0, 0, 0, 0.1) 0px 20px 25px -5px, rgba(0, 0, 0, 0.03) 0px 10px 10px -5px;">

    <div class="flex justify-between">
        <div> 
            <a-button type="primary" @click="modal = true" style="border-radius: 6px; background: #476175; border: none;">Nuevo</a-button>
        </div>
        <div class="flex justify-between" style="position: relative;">
            <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; border-radius: 6px;">
                <template #prefix>
                    <search-outlined />
                </template>
            </a-input> 
        </div>
    </div>


    <div style="margin-bottom: -20px;">
        <a-row :gutter="[16, 8]" class="mt-4">
            <a-col :xs="24" :sm="12" :md="12" :lg="6">
                <label>Departamento</label>
                    <a-form-item>
                        <a-auto-complete
                            :options="departamentos"
                            @select="onSelectDepartamentos"
                        >
                            <a-input
                                placeholder="Seleccione un departamento"
                                v-model:value="buscarDep"
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
            <a-col :xs="24" :sm="12" :md="12" :lg="6">
                <label>Provincia</label>
                <a-form-item>
                        <a-auto-complete
                            :options="provincias"
                            @select="onSelectProvincias"
                        >
                            <a-input
                                placeholder="Seleccione un departamento"
                                v-model:value="buscarProv"
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
            <a-col :xs="24" :sm="12" :md="12" :lg="6">
                <label>Distrito</label>
                <a-form-item>
                    <a-auto-complete
                        :options="distritos"
                        @select="onSelectDistritos"
                    >
                        <a-input
                            placeholder="Seleccione un departamento"
                            v-model:value="buscarDist"
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
            <a-col :xs="24" :sm="12" :md="12" :lg="6">
                <label>Gestión</label>
                <a-form-item style="border-radius:none; overflow: hidden;">
                    <a-select v-model:value="gestion" placeholder="Seleccione la gestión"  style="border-radius: 10px !important;">
                        <a-select-option :value="null">TODAS</a-select-option>
                        <a-select-option value="PÚBLICA DE GESTIÓN DIRECTA">PÚBLICA DE GESTIÓN DIRECTA</a-select-option>
                        <a-select-option value="PRIVADA">PRIVADA</a-select-option>    
                        <a-select-option value="PÚBLICA DE GESTIÓN PRIVADA">PÚBLICA DE GESTIÓN PRIVADA</a-select-option>    
                        <a-select-option value="EXTRANJERO">EXTRANJERA</a-select-option>    
                    </a-select>
                </a-form-item>
            </a-col>
        </a-row>
    </div>
</div>  

<div style="background: var(--card-bg, #ffffff); border-radius: 12px; overflow: hidden; box-shadow: rgba(0, 0, 0, 0.1) 0px 20px 25px -5px, rgba(0, 0, 0, 0.03) 0px 10px 10px -5px;">
    <div>
        <a-table :dataSource="colegios" size="" :columns="columns" :pagination="false" :scroll="{ y: 'calc(100vh - 320px)' }">
            <template #bodyCell="{ column, record }">
                <template v-if="column.dataIndex === 'acciones'">
                    <div style="display: flex; gap: 2px;">
                    <a-button size="small" style="background: var(--card-bg, #ffffff); height: 28px; border: 1px solid #d9d9d9; color: green; display: flex; align-items: center;">
                        <EyeOutlined/>
                    </a-button>

                    <a-button size="small" @click="abrirEditar(record)" style="background: var(--card-bg, #ffffff); height: 28px; border: 1px solid #d9d9d9; color: #1890ff; display: flex; align-items: center;">
                        <form-outlined/>
                    </a-button>
                    <a-popconfirm
                        title="¿Estas seguro de eliminar?"
                        @confirm="eliminar(record)"
                        disabled
                        >
                        <a-button size="small" @click="abrirEditar(record)" style="background: var(--card-bg, #ffffff); height: 28px; border: 1px solid #d9d9d9; color: crimson; display: flex; align-items: center;">
                            <delete-outlined disabled/>
                         </a-button>
                    </a-popconfirm>
                </div>
                </template>    
            </template>
        </a-table>
    </div>
    <div class="flex justify-between mb-6 mt-2 pr-4">
        <div>
            <a-pagination v-model:current="pagina" simple :total="totalRegistros" show-less-items />
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

<a-modal v-model:visible="modal" :footer="false" centered style="width:100%; max-width: 900px;">
    <div style="display: flex;">
        <span v-if="form.id === null" style="font-weight: bold; font-size: 1.1rem;">Nuevo Colegio</span>
        <span v-else style="font-weight: bold; font-size: 1.1rem;">Editar Colegio</span>
    </div>

    <div class="flex justify-center">
    <row style="display:flex; justify-content:center;">
        <a-col :span="24">
            <a-form
                ref="formDatos"
                name="form"
                :model="form" :rules="formRules">
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
                        <label>Forma<span style="color:red;">*</span></label>
                        <a-form-item name="nivel" :rules="[{ required: true, message: 'Seleccione el nivel' }]">
                            <a-select
                                v-model:value="form.forma"
                                style="width: 100%">
                                <a-select-option value="ESCOLARIZADA">ESCOLARIZADA</a-select-option>
                                <a-select-option value="NO ESCOLARIZADA">NO ESCOLARIZADA</a-select-option>
                                <a-select-option value="NO APLICA">NO APLICA</a-select-option>
                                <a-select-option value="PRESENCIAL">PRESENCIAL</a-select-option>
                                <a-select-option value="A DISTANCIA">A DISTANCIA</a-select-option>
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
                                            @keypress="handleKeyPress"
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
import Layout from '@/Layouts/AuthenticatedLayout.vue'
import { watch, computed, ref, reactive } from 'vue';
import { TeamOutlined, FormOutlined, DownOutlined, PrinterOutlined, DeleteOutlined, SearchOutlined, EyeOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';
    
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
    forma:'',
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
            getColegios();
        }
    } catch (error) {
        console.error(error);
        loading.value = false;
    }

}

const getColegios = async () => {
    axios.post("get-colegios?page="+pagina.value, 
        {
            "term": buscar.value, 
            paginashoja: pageSize.value, 
            dep: dep.value, 
            ges: gestion.value,
            prov: prov.value,
            dist: dist.value
        })
    .then((response) => {
        colegios.value = response.data.datos.data;
        totalRegistros.value = response.data.datos.total;
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
  });
}

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

watch(pagina, ( newValue, oldValue ) => { getColegios(); })
watch(pageSize, ( newValue, oldValue ) => { getColegios(); })
watch(buscar, ( newValue, oldValue ) => { getColegios(); })
watch(buscarResidencia, ( newValue, oldValue ) => {  if(newValue.length >= 3){ getUbigeosResidencia() }})
watch(buscarDep, ( newValue, oldValue ) => {  if(newValue.length >= 3){ getDepartamentos() }})
watch(buscarProv, ( newValue, oldValue ) => {  if(newValue.length >= 3){ getProvincias() }})
watch(buscarDist, ( newValue, oldValue ) => {  if(newValue.length >= 3){ getDistritos() }})
watch(gestion, ( newValue, oldValue ) => {  if(newValue.length >= 3){ getColegios() }})


const onSelectResidencias = (value, option) => { form.ubigeo = option.key; };
const onSelectDepartamentos = (value, option) => { dep.value = option.key; getColegios(); getProvincias(); };
const onSelectProvincias = (value, option) => { prov.value = option.key; getColegios(); getDistritos(); };
const onSelectDistritos = (value, option) => { dist.value = option.key; getColegios() };

getDepartamentos()
getColegios()   
getUbigeosResidencia()

const abrirEditar = (item) => {
    modal.value = true;
    form.id = item.id
    form.cod_modular = item.cod_modular
    form.nombre = item.nombre
    form.cod_local = item.cod_local
    form.gestion = item.gestion
    form.forma = item.forma
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
        title: 'Codigo',
        dataIndex: 'id',
        align:'center'
    },
    {
        title: 'Cod_Mod',
        dataIndex: 'cod_modular',
    },
    {
        title: 'Nombre del Colegio',
        dataIndex: 'nombre',
        responsive: ['xs','sm','md','lg'],
    },
    {
        title: 'Gestión',
        dataIndex: 'gestion',
        responsive: ['md'],
    },
    {
        title: 'Ubicación',
        dataIndex: 'lugar',
        responsive: ['lg'],
    },
    {
        title: 'Dirección',
        dataIndex: 'direccion',
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


</script>

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