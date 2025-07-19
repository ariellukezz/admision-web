<template>
    <Head title="Documentos"/>
    <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">    
    <!-- {{ buscar }} -->
    <row class="flex justify-between mb-4" >
        <div class="mr-3">
            <a-select ref="select" v-model:value="programa" placeholder="Seleccionar programa de estudios" :options="programas" allowClear></a-select>


        </div>
        <div class="flex justify-between" style="position: relative;" >
        <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px;">
            <template #prefix>
                <search-outlined />
            </template>
       </a-input>
        </div>
    </row>

    <!-- {{ inscripciones }} -->
    <a-table 
        :columns="columnsInscripcion" 
        :data-source="inscripciones"
        :pagination="false"
        size="small"
        > 
        <template #bodyCell="{ column, index, record }">

            <template v-if="column.dataIndex === 'codigo'" >
                <a-tag color="blue" style="padding-top: 3px; color:#164753; width: 105px;">
                    <span style="font-size: 1rem; font-weight: bold;">{{ record.codigo }}</span>
                </a-tag>
                <!-- <span style="font-size: 1.1rem; font-weight: bold;">{{ record.codigo }}</span> -->
            </template>

            <template v-if="column.dataIndex === 'postulante'" >
                <span style="font-size: 0.95rem;">{{ record.paterno }} {{ record.materno }}, {{ record.nombres }}</span>
            </template>

            <template v-if="column.dataIndex === 'estado'" >
                <a-tag v-if="record.estado === 0" color="blue">INSCRITO</a-tag>
                <a-tag v-else color="error">ANULADO</a-tag>
            </template>

            <template v-if="column.dataIndex === 'programa'" >
                <div>
                    {{ record.programa }}
                </div>
            </template>

            <template v-if="column.dataIndex === 'verificado'" >
                <a-tag v-if="record.verificado === 0" color="error">no verificado</a-tag>
                <a-tag v-if="record.verificado === 1" color="success">verificado</a-tag>
            </template>

            <template v-if="column.dataIndex === 'acciones'">
                <div style="display: flex; gap: 2px;">
                    <a-button size="small"  @click="recargar(record.dni)" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: #23cd23; display: flex; align-items: center;">
                        <upload-outlined/>
                    </a-button>
                    <a-button size="small"  @click="imprimirPDF(record.dni, record.id_proceso)" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: #164753;; display: flex; align-items: center;">
                        <printer-outlined/>
                    </a-button>
                    <a-button size="small"  @click="abrirEditar(record)" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: #af7200; display: flex; align-items: center;">
                        <form-outlined/>
                    </a-button>
                    <a-popconfirm
                        v-if="inscripciones.length"
                        title="¿Estas seguro de eliminar?"
                        disabled
                        @confirm="eliminar(record)"
                        >
                        <a-button size="small" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: #ff4d4f; display: flex; align-items: center;">
                            <delete-outlined />
                        </a-button>
                    </a-popconfirm>
                </div>  
            </template>
        </template>
  
    </a-table> 
    <a-pagination v-model:current="pagina" :total="totalRegistros"  v-model:pageSize="pageSize" show-less-items />
    
    </div>
    
    </AuthenticatedLayout>
    
    <div>
        <a-modal v-model:open="visible" title="Modificar Inscripción" style="margin-top: -40px;">
            <!-- <pre>{{ programa}}</pre> -->
            <a-form
                ref="formRef"
                name="custom-validation"
                :model="formState"
                v-bind="layout"
                @finish="handleFinish"
                @validate="handleValidate"
                @finishFailed="handleFinishFailed"
                >
                <a-form-item has-feedback name="codigo">
                    <label>Codigo</label>
                    <a-input type="text" v-model:value="inscripcion.codigo" disabled/>
                </a-form-item>
                <a-form-item has-feedback name="nombre">
                    <label>Postulante</label>
                    <a-input type="text" v-model:value="postulante.nombre" disabled/>
                </a-form-item>
                <a-form-item has-feedback name="postulante">
                    <label>Programa</label>
                    <div class="">
                        <a-select ref="select" v-model:value="inscripcion.id_programa" placeholder="Seleccionar programa de estudios" :options="programas" allowClear></a-select>
                    </div>
                </a-form-item>
                <a-form-item has-feedback name="tipo">
                    <label>Modalidad</label>
                    <a-select
                            ref="select"
                            v-model:value="inscripcion.id_modalidad"
                            placeholder="Seleccionar programa"
                            class="selector-modalidad"
                            style="width: 100%;"
                            >
                            <a-select-option :value='8'>EXAMEN GENERAL</a-select-option>
                            <a-select-option :value='7'>CONADIS</a-select-option>
                        </a-select>
                </a-form-item>
                <a-form-item has-feedback name="nombre">
                    <label>Observaciones</label>
                    <a-textarea type="text" v-model:value="inscripcion.observacion" autocomplete="off" />
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
import { FormOutlined, PrinterOutlined, UploadOutlined, DeleteOutlined, SearchOutlined, } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';
const baseUrl = window.location.origin;

const programa = ref(null);
const buscar = ref("");
const inscripciones = ref([])
const visible = ref(false)
const pagina = ref(1)
const totalRegistros = ref(null)
const pageSize = ref(20)
const inscripcion = ref({ 
    id:null, 
    codigo:"", 
    id_posulante:"", 
    id_programa:"", 
    id_modalidad:"",
    estado: true, 
    observacion:"",
})
const postulante = ref({ id:"", nombre:"", dni:""})

const showModalPrograma = () => { visible.value = true; };

watch(buscar, ( newValue, oldValue ) => { getInscripciones() })
watch(pageSize, ( newValue, oldValue ) => { getInscripciones() })
watch(programa, ( newValue, oldValue ) => 
{ 
    if(newValue == 0) {
        programa.value = null;
    } 
    pagina.value = 1;
    getInscripciones() 
})

watch(visible, ( newValue, oldValue ) => {
    if(visible.value == false &&inscripcion.value.id != null ){
        inscripcion.value.id = null, 
        inscripcion.value.codigo = "", 
        inscripcion.value.id_posulante = "", 
        inscripcion.value.id_programa = "", 
        inscripcion.value.id_modalidad = "",
        inscripcion.value.estado = true, 
        inscripcion.value.observacion = "",
        postulante.value.nombre = null,
        postulante.value.dni = null,
        postulante.value.id = null
    }
})

watch(pagina, ( newValue, oldValue ) => { getInscripciones(); })

const abrirEditar = (item) => {
    visible.value = true;
    inscripcion.value.id = item.id;
    inscripcion.value.codigo = item.codigo;
    inscripcion.value.id_programa = item.id_programa;
    inscripcion.value.id_modalidad = item.id_modalidad;
    inscripcion.value.observacion = item.observaciones;
    postulante.value.id = item.id_postulante;
    postulante.value.dni = item.dni;
    postulante.value.nombre = item.dni+" - "+item.nombres +" "+ item.paterno +" "+ item.materno;
}

const getInscripciones =  async ( ) => {
    let res = await axios.post( "get-inscripciones-admin?page="+pagina.value , { term: buscar.value, paginashoja: pageSize.value, programa: programa.value } );
    inscripciones.value = res.data.datos.data;
    totalRegistros.value = res.data.datos.total;
}
const programas = ref([]);
const getProgramas =  async ( ) => {
    let res = await axios.get( "get-select-programas-proceso-admin");
    programas.value = res.data.datos;
}
getProgramas();


const guardar = () => {
    let post = {
        id:inscripcion.value.id,
        id_postulante: postulante.value.id,
        id_programa: inscripcion.value.id_programa,
        id_modalidad: inscripcion.value.id_modalidad,
        observacion: inscripcion.value.observacion,
        dni: postulante.value.dni
    };
    axios.post("actualizar-inscripcion", post).then((result) => {
        getInscripciones()
        notificacion('success',result.data.titulo, result.data.mensaje);
        visible.value = false;
    });
}

const eliminar = (item) => {
    axios.get("eliminar-modalidad/"+item.id).then((result) => {
    getInscripciones();
    notificacion('warning', result.data.titulo, result.data.mensaje );
    });
}

const columnsInscripcion = [
    // { title: 'ID', dataIndex: 'id' },
    { title: 'Codigo', dataIndex: 'codigo', width:"110px"},
    { title: 'DNI', dataIndex: 'dni', align:'center'},
    { title: 'Postulante', dataIndex: 'postulante'},
    { title: 'Programa', dataIndex:'programa'},
    { title: 'Modalidad', dataIndex:'modalidad', align:'center'},
    { title: 'Estado', dataIndex: 'estado', align:'center'},
    { title: 'Acciones', dataIndex: 'acciones', width:'140px', align:'center'},
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

const imprimirPDF =  (dnni, proc) => {
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = baseUrl+'/documentos/'+proc+'/inscripciones/constancias/'+dnni+'.pdf';
    document.body.appendChild(iframe);
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
}

const recargar =  (dni) => {
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = baseUrl+'/admin/pdf-inscripción/'+dni;
    document.body.appendChild(iframe);
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
}

getInscripciones()
</script>
