<template>
    <Head title="Documentos"/>
    <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">    
    <!-- {{ buscar }} -->
    <row class="flex justify-between mb-4" >
        <div class="mr-3">
            <a-select
                ref="select"
                v-model:value="programa"
                placeholder="Seleccionar programa"
                class="selector-modalidad"
                style="width: 200px;"
                >
                <a-select-option :value='0'>TODOS</a-select-option>
                <a-select-option :value='1'>ADMINISTRACIÓN</a-select-option>
                <a-select-option :value='2'>ANTROPOLOGÍA</a-select-option>
                <a-select-option :value='3'>ARQUITECTURA Y URBANISMO</a-select-option>
                <a-select-option :value='4'>ARTE: ARTES PLÁSTICAS</a-select-option>
                <a-select-option :value='5'>ARTE: DANZA</a-select-option>
                <a-select-option :value='6'>ARTE: MÚSICA</a-select-option>
                <a-select-option :value='8'>BIOLOGÍA: ECOLOGÍA</a-select-option>
                <a-select-option :value='9'>BIOLOGÍA: MICROBIOLOGÍA Y LABORATORIO CLÍNICO</a-select-option>
                <a-select-option :value='10'>BIOLOGÍA: PESQUERÍA</a-select-option>
                <a-select-option :value='11'>CIENCIAS CONTABLES</a-select-option>
                <a-select-option :value='12'>CIENCIAS DE LA COMUNICACIÓN SOCIAL</a-select-option>
                <a-select-option :value='13'>CIENCIAS FÍSICO MATEMÁTICAS: FÍSICA</a-select-option>
                <a-select-option :value='14'>CIENCIAS FÍSICO MATEMÁTICAS: MATEMÁTICAS</a-select-option>
                <a-select-option :value='15'>DERECHO</a-select-option>
                <a-select-option :value='16'>EDUCACIÓN FÍSICA</a-select-option>
                <a-select-option :value='17'>EDUCACIÓN INICIAL</a-select-option>
                <a-select-option :value='18'>EDUCACIÓN PRIMARIA</a-select-option>
                <a-select-option :value='19'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE CIENCIA, TECNOLOGÍA Y AMBIENTE</a-select-option>
                <a-select-option :value='20'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE CIENCIAS SOCIALES</a-select-option>
                <a-select-option :value='21'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE LENGUA, LITERATURA, PSICOLOGÍA Y FILOSOFÍA</a-select-option>
                <a-select-option :value='22'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE MATEMÁTICA, FÍSICA, COMPUTACIÓN E INFORMÁTICA</a-select-option>
                <a-select-option :value='23'>ENFERMERÍA</a-select-option>
                <a-select-option :value='24'>INGENIERÍA AGRÍCOLA</a-select-option>
                <a-select-option :value='25'>INGENIERÍA AGROINDUSTRIAL</a-select-option>
                <a-select-option :value='26'>INGENIERÍA AGRONÓMICA</a-select-option>
                <a-select-option :value='27'>INGENIERÍA CIVIL</a-select-option>
                <a-select-option :value='28'>INGENIERÍA DE MINAS</a-select-option>
                <a-select-option :value='29'>INGENIERÍA DE SISTEMAS</a-select-option>
                <a-select-option :value='30'>INGENIERÍA ECONÓMICA</a-select-option>
                <a-select-option :value='31'>INGENIERÍA ELECTRÓNICA</a-select-option>
                <a-select-option :value='32'>INGENIERÍA ESTADÍSTICA E INFORMÁTICA</a-select-option>
                <a-select-option :value='33'>INGENIERÍA GEOLÓGICA</a-select-option>
                <a-select-option :value='34'>INGENIERÍA MECÁNICA ELÉCTRICA</a-select-option>
                <a-select-option :value='35'>INGENIERÍA METALÚRGICA</a-select-option>
                <a-select-option :value='36'>INGENIERÍA QUÍMICA</a-select-option>
                <a-select-option :value='37'>INGENIERÍA TOPOGRÁFICA Y AGRIMENSURA</a-select-option>
                <a-select-option :value='38'>MEDICINA HUMANA</a-select-option>
                <a-select-option :value='39'>MEDICINA VETERINARIA Y ZOOTECNIA</a-select-option>
                <a-select-option :value='40'>NUTRICIÓN HUMANA</a-select-option>
                <a-select-option :value='41'>ODONTOLOGÍA</a-select-option>
                <a-select-option :value='42'>SOCIOLOGÍA</a-select-option>
                <a-select-option :value='43'>TRABAJO SOCIAL</a-select-option>
                <a-select-option :value='44'>TURISMO</a-select-option>
                <a-select-option :value='45'>PSICOLOGÍA</a-select-option>
            </a-select>

        </div>
        <div class="flex justify-between" style="position: relative;" >
        <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; padding-left: 30px;"/>
        <div class="mr-2" style="position: absolute; left: 8px; top: 3px; "><search-outlined /></div>
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
                <a-tag color="blue" style="padding-top: 3px; color:#164753; width: 116px;">
                    <span style="font-size: 1rem; font-weight: bold;">{{ record.codigo }}</span>
                </a-tag>
                <!-- <span style="font-size: 1.1rem; font-weight: bold;">{{ record.codigo }}</span> -->
            </template>

            <template v-if="column.dataIndex === 'postulante'" >
                <span style="font-size: 0.95rem;">{{ record.paterno }} {{ record.materno }}, {{ record.nombres }}</span>
            </template>

            <template v-if="column.dataIndex === 'estado'" >
                <a-tag v-if="record.estado === 0" color="blue">HABILITADO</a-tag>
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
                <a-button type="success" style="color: #164753;" @click="imprimirPDF(record.dni)" size="small">
                    <template #icon><printer-outlined/></template>
                </a-button>
                <a-divider type="vertical" />
                <a-button type="" style="color: #af7200;" @click="abrirEditar(record)" size="small">
                    <template #icon><form-outlined/></template>
                </a-button>
                <a-divider type="vertical" />
                <a-popconfirm
                    v-if="inscripciones.length"
                    title="¿Estas seguro de eliminar?"
                    disabled
                    @confirm="eliminar(record)"
                    >
                    <a-button style="color:crimson;" shape="" size="small">
                        <template #icon><delete-outlined/></template>
                    </a-button>
                </a-popconfirm>
  
            </template>
        </template>
  
    </a-table> 
    <a-pagination v-model:current="pagina" :total="totalRegistros"  v-model:pageSize="pageSize" show-less-items />
    
    </div>
    
    </AuthenticatedLayout>
    
    <div>
        <a-modal v-model:visible="visible" title="Modificar Inscripción" style="margin-top: -40px;">
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
                        <a-select
                            ref="select"
                            v-model:value="inscripcion.id_programa"
                            placeholder="Seleccionar programa"
                            class="selector-modalidad"
                            style="width: 100%;"
                            >
                            <a-select-option :value='0'>TODOS</a-select-option>
                            <a-select-option :value='1'>ADMINISTRACIÓN</a-select-option>
                            <a-select-option :value='2'>ANTROPOLOGÍA</a-select-option>
                            <a-select-option :value='3'>ARQUITECTURA Y URBANISMO</a-select-option>
                            <a-select-option :value='4'>ARTE: ARTES PLÁSTICAS</a-select-option>
                            <a-select-option :value='5'>ARTE: DANZA</a-select-option>
                            <a-select-option :value='6'>ARTE: MÚSICA</a-select-option>
                            <a-select-option :value='8'>BIOLOGÍA: ECOLOGÍA</a-select-option>
                            <a-select-option :value='9'>BIOLOGÍA: MICROBIOLOGÍA Y LABORATORIO CLÍNICO</a-select-option>
                            <a-select-option :value='10'>BIOLOGÍA: PESQUERÍA</a-select-option>
                            <a-select-option :value='11'>CIENCIAS CONTABLES</a-select-option>
                            <a-select-option :value='12'>CIENCIAS DE LA COMUNICACIÓN SOCIAL</a-select-option>
                            <a-select-option :value='13'>CIENCIAS FÍSICO MATEMÁTICAS: FÍSICA</a-select-option>
                            <a-select-option :value='14'>CIENCIAS FÍSICO MATEMÁTICAS: MATEMÁTICAS</a-select-option>
                            <a-select-option :value='15'>DERECHO</a-select-option>
                            <a-select-option :value='16'>EDUCACIÓN FÍSICA</a-select-option>
                            <a-select-option :value='17'>EDUCACIÓN INICIAL</a-select-option>
                            <a-select-option :value='18'>EDUCACIÓN PRIMARIA</a-select-option>
                            <a-select-option :value='19'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE CIENCIA, TECNOLOGÍA Y AMBIENTE</a-select-option>
                            <a-select-option :value='20'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE CIENCIAS SOCIALES</a-select-option>
                            <a-select-option :value='21'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE LENGUA, LITERATURA, PSICOLOGÍA Y FILOSOFÍA</a-select-option>
                            <a-select-option :value='22'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE MATEMÁTICA, FÍSICA, COMPUTACIÓN E INFORMÁTICA</a-select-option>
                            <a-select-option :value='23'>ENFERMERÍA</a-select-option>
                            <a-select-option :value='24'>INGENIERÍA AGRÍCOLA</a-select-option>
                            <a-select-option :value='25'>INGENIERÍA AGROINDUSTRIAL</a-select-option>
                            <a-select-option :value='26'>INGENIERÍA AGRONÓMICA</a-select-option>
                            <a-select-option :value='27'>INGENIERÍA CIVIL</a-select-option>
                            <a-select-option :value='28'>INGENIERÍA DE MINAS</a-select-option>
                            <a-select-option :value='29'>INGENIERÍA DE SISTEMAS</a-select-option>
                            <a-select-option :value='30'>INGENIERÍA ECONÓMICA</a-select-option>
                            <a-select-option :value='31'>INGENIERÍA ELECTRÓNICA</a-select-option>
                            <a-select-option :value='32'>INGENIERÍA ESTADÍSTICA E INFORMÁTICA</a-select-option>
                            <a-select-option :value='33'>INGENIERÍA GEOLÓGICA</a-select-option>
                            <a-select-option :value='34'>INGENIERÍA MECÁNICA ELÉCTRICA</a-select-option>
                            <a-select-option :value='35'>INGENIERÍA METALÚRGICA</a-select-option>
                            <a-select-option :value='36'>INGENIERÍA QUÍMICA</a-select-option>
                            <a-select-option :value='37'>INGENIERÍA TOPOGRÁFICA Y AGRIMENSURA</a-select-option>
                            <a-select-option :value='38'>MEDICINA HUMANA</a-select-option>
                            <a-select-option :value='39'>MEDICINA VETERINARIA Y ZOOTECNIA</a-select-option>
                            <a-select-option :value='40'>NUTRICIÓN HUMANA</a-select-option>
                            <a-select-option :value='41'>ODONTOLOGÍA</a-select-option>
                            <a-select-option :value='42'>SOCIOLOGÍA</a-select-option>
                            <a-select-option :value='43'>TRABAJO SOCIAL</a-select-option>
                            <a-select-option :value='44'>TURISMO</a-select-option>
                            <a-select-option :value='45'>PSICOLOGÍA<Abbr></Abbr></a-select-option>
                        </a-select>
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
import { FormOutlined, PrinterOutlined, DeleteOutlined, SearchOutlined, } from '@ant-design/icons-vue';
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
    { title: 'Codigo', dataIndex: 'codigo'},
    { title: 'DNI', dataIndex: 'dni', align:'center'},
    { title: 'Postulante', dataIndex: 'postulante'},
    { title: 'Programa', dataIndex:'programa'},
    { title: 'Modalidad', dataIndex:'modalidad', align:'center'},
    { title: 'Estado', dataIndex: 'estado', align:'center'},
    { title: 'Observación', dataIndex: 'observacion'},
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

const imprimirPDF =  (dnni) => {
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = baseUrl+'/documentos/6/inscripciones/constancias/'+dnni+'.pdf';
    document.body.appendChild(iframe);
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
}

getInscripciones()
</script>
