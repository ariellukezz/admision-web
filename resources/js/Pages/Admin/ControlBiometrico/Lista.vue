<template>
<Head title="Control biométrico"/>
<AuthenticatedLayout>
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="height: calc(100vh - 98px);">
<!-- {{ buscar }} -->
<row class="flex justify-between mb-4" >
    <div class="mr-3">
        <a-button type="error" style="border-radius: 5px; color:white; background: cornflowerblue; border: none;" @click="showModalPrograma">FILTRAR</a-button>
    </div>
    <div class="flex">
        <div class="mr-3">
            <a-button type="error" style="border-radius: 5px; color:white; background: crimson; border: none;" @click="showModalPrograma">PDF</a-button>
            <a-button class="ml-2" type="primary" style=" border:none; border-radius: 5px; background: green;" @click="showModalPrograma">XLSX</a-button>
        </div>

        <div class="flex justify-between" style="position: relative;" >
            <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; padding-left: 10px;">
                <template #prefix>
                    <search-outlined/>
                </template>
            </a-input>
        </div>
    </div>

</row>

<div style="">
    <a-table 
        :columns="columnsProgramas" 
        :data-source="programas"
        :pagination="false"
        size="small"
        :scroll="{ x: 380, y: 'calc(100vh - 240px)' }"
        >
        <template #bodyCell="{ column, index, record }">
            <template v-if="column.dataIndex === 'codigo'">
                <div><span style="font-size: .8rem">{{ record.codigo }}</span></div>                
            </template>
            <template v-if="column.dataIndex === 'nro_doc'">
                <div><span style="font-size: .9rem">{{ record.nro_doc }}</span></div>                
            </template>

            <template v-if="column.dataIndex === 'nombre'">
                <div><span style="font-size: .9rem;">{{ record.primer_apellido }} {{ record.segundo_apellido }}, {{ record.nombres }}</span></div>                
            </template>
            <template v-if="column.dataIndex === 'modalidad'">
                <div class="flex" style="justify-content: center;">
                    <a-tag style="font-size: .8rem;" color="blue" v-if=" record.modalidad == 'EXAMEN GENERAL'"> GENERAL </a-tag>
                    <a-tag style="font-size: .8rem;" color="orange" v-if=" record.modalidad == 'CONADIS'"> CONADIS </a-tag>
                    <a-tag style="font-size: .8rem;" color="purple" v-if=" record.modalidad == 'CEPREUNA'"> CEPREUNA</a-tag>
                    <a-tag style="font-size: .8rem;" color="pink" v-if=" record.modalidad == 'BENEFICIARIOS DEL PLAN INTEGRAL DE REPARACIONES (PIR)'">LEY DE REPARACIONES</a-tag>
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
                <a-button class="" style="border-radius:4px; background: none; color: cornflowerblue;" shape="" size="small">
                <template #icon><eye-outlined/></template>
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
    <a-modal v-model:visible="visible" :title="programa.id == null?'Nuevo Programa':'Editar Programa'" style="margin-top: -40px;">
        <a-form
            ref="formRef"
            name="custom-validation"
            :model="formState"
            :rules="rules"
            v-bind="layout"
            @finish="handleFinish"
            @validate="handleValidate"
            @finishFailed="handleFinishFailed"
        >
        <a-form-item has-feedback label="Codigo" name="codigo">
            <a-input type="text" v-model:value="programa.codigo" autocomplete="off" />
        </a-form-item>
        <a-form-item has-feedback label="Nombre" name="nombre">
            <a-input type="text" v-model:value="programa.nombre" autocomplete="off" />
        </a-form-item>
    
        <a-form-item has-feedback label="Facultad" name="facultad">
            <a-select
                :options="facultades"
                ref="Tipo"
                style="width: 100%"
                @focus="focus"
                @change="handleChange"
                v-model:value="programa.id_facultad"
                >
            </a-select>
        </a-form-item>

        <a-form-item has-feedback label="Area" name="area">
            <a-select
                style="width: 100%"
                @focus="focus"
                @change="handleChange"
                v-model:value="programa.area"
                >
                <a-select-option value="BIOMÉDICAS">
                    BIOMEDICAS
                </a-select-option>
                <a-select-option value="INGENIERÍAS">
                    INGENIERÍAS
                </a-select-option>
                <a-select-option value="SOCIALES">
                    SOCIALES
                </a-select-option>
            </a-select>
        </a-form-item>
        
        <a-form-item has-feedback label="Vigente" name="estado">
            <a-switch v-model:checked="programa.estado"/>
        </a-form-item>

        </a-form>

    <template #footer>
        <a-button style="margin-left: 10px;" @click="resetForm">Cancelar</a-button>
        <a-button type="primary" @click="guardar()">Guardar</a-button>
    </template>
    </a-modal>
</div>

</template>
    
<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { watch, computed, ref, unref } from 'vue';
import { PrinterOutlined, SyncOutlined, EyeOutlined, SearchOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';
const baseUrl = window.location.origin;

const buscar = ref("");
const pagina = ref(1)
const totalpaginas = ref(null)

const visible = ref(false);
const buscarDep = ref("")
const programas = ref([])
const programa = ref({
    id:null,
    codigo:"",
    nombre:"",
    nivel_academico:"CARRERA PROFESIONAL",
    tipo_autorizacion:"RECONOCIDO POR LIC.",
    id_facultad:1,
    estado:true,
    area:"BIOMEDICAS"
})

const showModalPrograma = () => {
    visible.value = true;
};

let timeoutId;
watch(buscar, ( newValue, oldValue ) => { 
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        getProgramas()
    }, 500);  
})


watch(pagina, ( newValue, oldValue ) => {
    getProgramas()
})


const imprimirConstancia =  (url) => {
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = baseUrl+url;
    document.body.appendChild(iframe);
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
}

const generarConstancia =  (dni) => {
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = baseUrl+'/admin/pdf-biometrio/'+dni;
    document.body.appendChild(iframe);
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
}


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
    let res = await axios.post( "get-control-posterior?page=" + pagina.value, { term: buscar.value });
    programas.value = res.data.datos.data;
    totalpaginas.value = res.data.datos.total;
}

const guardar = () => {
    let post = {
    id: programa.value.id,
    codigo: programa.value.codigo,
    nombre: programa.value.nombre,
    nivel_academico: programa.value.nivel_academico,
    tipo_autorizacion: programa.value.tipo_autorizacion,
    estado: programa.value.estado,
    id_facultad: programa.value.id_facultad,
    area: programa.value.area,
    };
    axios.post("save-programa", post).then((result) => {
    getProgramas()
    notificacion('success',result.data.titulo, result.data.mensaje);
    visible.value = false;
    programa.value.codigo = null,
    programa.value.id = null,
    programa.value.nombre = ""
    });
}

const eliminar = (item) => {
    axios.get("eliminar-programa/"+item.id).then((result) => {
    getProgramas();
    notificacion('warning', 'PROGRAMA ELIMINADO', result.data.mensaje );
    });
}

const columnsProgramas = [
    { title: 'Cod', dataIndex: 'codigo_ingreso', width:'65px', align:'center', responsive: ['md'],},
    { title: 'N° Doc', dataIndex: 'nro_doc', width:'80px', align:'center', responsive: ['md'],},
    { title: 'Nombre', dataIndex: 'nombre'},
    { title: 'Programa', dataIndex: 'programa'},
    { title: 'Modalidad', dataIndex: 'modalidad', align:'center', width:"130px", responsive: ['md'],},
    { title: 'Puntaje', dataIndex: 'puntaje_total', align:'center', width:'80px', responsive: ['md'],},
    { title: 'Acciones', dataIndex: 'acciones', width:"90px", align:'center'},
];

const selectedRowKeys = ref([]); 

const onSelectChange = changableRowKeys => {
    console.log('selectedRowKeys changed: ', changableRowKeys);
    selectedRowKeys.value = changableRowKeys;
};
const rowSelection = computed(() => {
    return {
    selectedRowKeys: unref(selectedRowKeys),
    onChange: onSelectChange,
    hideDefaultSelections: true,
    };
});

const notificacion = (type, titulo, mensaje) => {
    notification[type]({
    message: titulo,
    description: mensaje,
    });
};

getProgramas()


</script>

<style >
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

/* Estilo para un scroll específico */
.scroll-container {
overflow-y: auto;
scrollbar-width: thin; /* Firefox */
scrollbar-color: #888 #f1f1f1; /* Firefox */
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