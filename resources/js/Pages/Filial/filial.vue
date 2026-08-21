<template>
<Head title="Filiales"/>
<AuthenticatedLayout>
<div class="filial-container">

<div class="flex justify-between mb-2" >
    <div class="mr-3">
    <a-button type="primary" @click="showModalFilial" style="background: #476175; border: none; border-radius: 5px;">Nuevo</a-button>
    <a-button :loading="exportandoExcel" @click="exportarExcel" style="margin-left: 8px;">
        <template #icon><FileExcelOutlined /></template>
        Exportar Excel
    </a-button>
    </div>
    <div class="flex justify-between" style="position: relative;" >
        <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; border-radius:6px; padding-left: 30px;"/>
    <div class="mr-2" style="position: absolute; left: 8px; top: 3px; "><search-outlined /></div>
    </div>
</div>
<a-table 
    :columns="columnsFiliales" 
    :data-source="filiales"
    :key="id"
    size="small"
    :pagination="false"
    style="scale: .7rem;"
    > 
    <template #bodyCell="{ column, record }">

        <template v-if="column.dataIndex === 'codigo'">
            <div class="flex" style="justify-content: center;">
                <div class="px-2" style="background: #e3e3e3; border-radius: 4px;"><span style="font-weight: bold;">{{ record.codigo }}</span></div>
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
            <a-button type="success" class="mr-1" style="color: #476175;" @click="cambiarSexo(record.id_postulante, record.sexo )" size="small">
                <template #icon><SaveOutlined/></template>
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

<div class="flex justify-between mt-2 pr-4" style="margin-bottom: -5px;">
        <div>
            <a-pagination v-model:current="pagina" simple :total="totalRegistros"  v-model:pageSize="paginasize" show-less-items />
        </div>
        <div clas="" style="scale: 0.9; margin-right: -20px;"> 
            <a-select
                v-model:value="paginasize"
                style="width: 90px;">
                <a-select-option :value="10">10 Reg.</a-select-option>
                <a-select-option :value="20">20 Reg.</a-select-option>    
                <a-select-option :value="50">50 Reg.</a-select-option>    
                <a-select-option :value="100">100 Reg.</a-select-option>    
            </a-select>
        </div>
    </div>

</div>


</AuthenticatedLayout>

<div>
    <a-modal v-model:visible="visible" style="margin-top: -40px; margin-bottom: -40px;" :closable="false">
        <div style="background: #476175; height: 36px; margin-left:-24px; margin-right:-24px; margin-top:-24px; margin-bottom: 28px;">
            <div class="flex justify-between ml-3 mr-1" style="height:36px; align-items: center;">
                <div><span style="font-weight: bold; color:white; font-size:1rem;">Nueva filial</span></div>
                <div><span ><a-button @click="cerramodal()" style="background:none; border:none; color:white;">X</a-button></span></div>
            </div>
        </div>

        <a-form
        ref="formFilial"
        name="filial"
        :model="filial"
        v-bind="layout"
        style="margin-bottom: -30px;"
        >
        <a-form-item 
            label="Codigo" 
            name="codigo"
            :rules="[{ required: true, message: 'Ingrese el codigo', trigger: 'change' },]"
            >
            <a-input style="border-radius:6px;" type="text" placeholder="Ingrese el codigo" v-model:value="filial.codigo" autocomplete="off" />
        </a-form-item>
        <a-form-item 
            label="Nombre" 
            :rules="[{ required: true, message: 'Ingrese el nombre', trigger: 'change' },]"
            name="nombre">
            <a-input style="border-radius:6px;" type="text" placeholder="Ingrese el nombre" v-model:value="filial.nombre" autocomplete="off" />
        </a-form-item>


        <a-form-item 
            label="lugar" 
            name="lugar"
            :rules="[{ required: true, message: 'Seleccione el lugar', trigger:'blur' }]"
            >
                <a-auto-complete
                    v-model:value="residencia"                
                    :options="residencias"
                    @select="onSelectResidencias"
                    style="border-radius:6px; overflow:hidden; border:none;"
                >
                    <a-input 
                        placeholder="Dep/prov/dist"
                        v-model:value="buscarResidencia"
                        style="border-radius:6px; "
                    >
                        <template #suffix>
                            <a-tooltip title="Extra information">
                            <down-outlined/>
                            </a-tooltip>
                        </template>
                    </a-input>
                </a-auto-complete>
        </a-form-item>

        <a-form-item 
            label="Dirección" 
            :rules="[{ required: true, message: 'Ingrese la dirección', trigger: 'change' },]"
            name="direccion">
            <a-input style="border-radius:6px;" type="text" placeholder="Ingrese el nombre" v-model:value="filial.direccion" autocomplete="off" />
        </a-form-item>

        <a-form-item 
            label="Vigente" 
            name="estado"    
            >
            <a-switch v-model:checked="filial.estado"/>
        </a-form-item>

        </a-form>

    <template #footer>
        <a-button style="margin-left: 6px; border-radius: 4px;" @click="resetForm">Cancelar</a-button>
        <a-button type="primary" style="background: #476175; border:none; border-radius: 4px;" @click="guardar()">Guardar</a-button>
    </template>
    </a-modal>
</div>

</template>
    
<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { watch, computed, ref, onMounted, reactive } from 'vue';
import { FormOutlined, DeleteOutlined, SearchOutlined, DownOutlined, SaveOutlined, FileExcelOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';

import axios from 'axios';
const loading = ref(false);
const buscar = ref("");
const filiales= ref([]);
const visible = ref(false);
const formFilial = ref();
const filial = reactive({ id:null, codigo:"", nombre:"", lugar:null, direccion:"", estado: null })

const residencia = ref(null)
const residencias = ref([])
const buscarResidencia = ref(null)
const redseleccionado = ref(null)

const pagina = ref(1);
const paginasize = ref(10);
const totalRegistros = ref(1);

const onSelectResidencias = (value, option) => {  residencia.value = option.value;  filial.lugar = option.key };

const showModalFilial = () => { visible.value = true; };
let timeoutId;
watch(buscar, ( newValue, oldValue ) => { 
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        getFiliales(); 
    }, 500);  
})
watch(pagina, ( newValue, oldValue ) => { getFiliales(); })
watch(paginasize, ( newValue, oldValue ) => { getFiliales(); })

let timeout2;
watch(buscarResidencia, ( newValue, oldValue ) => {  
    clearTimeout(timeout2);
    timeout2 = setTimeout(() => {
        getUbigeosResidencia() 
    }, 500); 
})

const guardar = async () => {
    loading.value = true;
    try {
        const values = await formFilial.value.validateFields();
        axios.post("save-filial", filial).then((result) => {
            notificacion('success',result.data.titulo, result.data.mensaje);
            getFiliales();
            visible.value = false;
            resetForm()
        });
        
    } catch (error) {
        console.error(error);
    }

}

const getUbigeosResidencia = async () => {

    axios.post("/get-ubigeo",{"term": buscarResidencia.value})
    .then((response) => {
        residencias.value = response.data.datos.data;

    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
  });
}

const abrirEditar = (item) => {

    visible.value = true;
    filial.id = item.id;
    filial.codigo = item.codigo;
    filial.nombre = item.nombre;
    filial.lugar = item.ubigeo;
    filial.direccion = item.direccion;
    residencia.value = item.lugar;
    if(item.estado == 1){ filial.estado = true }
    else { filial.estado = false}
}

const getFiliales =  async () => {
    let res = await axios.post("filiales/get-filiales?page=" + pagina.value, { term: buscar.value, paginasize: paginasize.value } );
    filiales.value = res.data.datos.data;
    totalRegistros.value = res.data.datos.total;
}

const exportandoExcel = ref(false);

const exportarExcel = () => {
    exportandoExcel.value = true;
    const params = new URLSearchParams();
    if (buscar.value) params.append('term', buscar.value);
    window.location.href = '/admin/filiales/exportar-excel?' + params.toString();
    setTimeout(() => {
        exportandoExcel.value = false;
    }, 2000);
};

const eliminar = (item) => {
    axios.get("eliminar-filial/"+item.id).then((result) => {
        getFiliales();
        notificacion('error', 'PROCESO ELIMINADO', result.data.mensaje );
    });
}

const resetForm = () => {

    filial.id = null;
    filial.codigo = "";
    filial.nombre = "";
    filial.lugar = null;
    filial.direccion = "";
    filial.estado =  "";
    residencia.value = "";
    redseleccionado.value = ""
    cerramodal();

}

const notificacion = (type, titulo, mensaje) => {
    notification[type]({
    message: titulo,
    description: mensaje,
    });
};

const cerramodal = () => { visible.value = false; }

getFiliales()
getUbigeosResidencia()

const layout = { labelCol: { span: 6, }, wrapperCol: { span: 18, } };
const columnsFiliales = [
    { title: 'Codigo', dataIndex: 'codigo',},
    { title: 'Nombre', dataIndex: 'nombre',},
    { title: 'Lugar', dataIndex: 'lugar',},
    { title: 'Dirección', dataIndex: 'direccion',},
    { title: 'Vigente', dataIndex: 'estado', align:'center', width:'100px'},
    { title: 'Acciones', dataIndex: 'acciones', align:'center', width:'96px'},
];

</script>
<style scoped>
.filial-container {
  background: var(--card-bg, #ffffff);
  border: 1px solid var(--card-border, #e2e8f0);
  color: var(--card-text, #1e293b);
  border-radius: 10px;
  min-height: calc(100vh - 92px);
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
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
.theme-dark .ant-pagination,
.theme-hybrid .ant-pagination {
    color: var(--card-text) !important;
}
.theme-dark .ant-pagination .ant-pagination-item,
.theme-hybrid .ant-pagination .ant-pagination-item {
    background: var(--card-bg) !important;
    border-color: var(--card-border) !important;
    color: var(--card-text) !important;
}
.theme-dark .ant-pagination .ant-pagination-item a,
.theme-hybrid .ant-pagination .ant-pagination-item a {
    color: var(--card-text) !important;
}
.theme-dark .ant-select-selector,
.theme-hybrid .ant-select-selector {
    background: var(--card-bg) !important;
    border-color: var(--card-border) !important;
    color: var(--card-text) !important;
}
.theme-dark .ant-input,
.theme-hybrid .ant-input {
    background: var(--card-bg) !important;
    border-color: var(--card-border) !important;
    color: var(--card-text) !important;
}
.theme-dark .ant-modal-content,
.theme-hybrid .ant-modal-content {
    background: var(--card-bg) !important;
    color: var(--card-text) !important;
}
.theme-dark .ant-modal-header,
.theme-hybrid .ant-modal-header {
    background: var(--card-bg) !important;
    border-bottom: 1px solid var(--card-border) !important;
}
.theme-dark .ant-modal-title,
.theme-hybrid .ant-modal-title {
    color: var(--card-text) !important;
}
.theme-dark .ant-form-item-label > label,
.theme-hybrid .ant-form-item-label > label {
    color: var(--card-muted) !important;
}
.theme-dark .ant-modal-close,
.theme-hybrid .ant-modal-close {
    color: var(--card-muted) !important;
}
</style>