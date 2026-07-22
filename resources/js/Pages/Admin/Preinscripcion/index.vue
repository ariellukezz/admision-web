<template>
    <Head title="Documentos"/>
    <AuthenticatedLayout>
    <div class="pre-container">

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
            </a-select>

        </div>
        <div class="flex justify-between" style="position: relative;" >
        <a-input type="text" placeholder="Buscar" v-model:value="buscar" class="pre-search" style="max-width: 300px; padding-left: 10px;">
            <template #prefix><search-outlined /></template>
        </a-input>

        </div>
    </row>

    <div class="pre-table-card">
    <a-table
        :columns="columnsInscripcion"
        :data-source="inscripciones"
        :pagination="false"
        size="small"
        :scroll="{ y: 'calc(100vh - 320px)' }"
        >
        <template #bodyCell="{ column, index, record }">

            <template v-if="column.dataIndex === 'dni'" >
                <a-tag color="#476175" style="padding-top: 3px;">
                    <span style="font-size: 1rem; font-weight: bold;">{{ record.dni }}</span>
                </a-tag>
            </template>

            <template v-if="column.dataIndex === 'postulante'" >
                <span class="pre-name">{{ record.paterno }} {{ record.materno }}, {{ record.nombres }}</span>
            </template>

            <template v-if="column.dataIndex === 'programa'" >
                <span class="pre-programa">{{ record.programa }}</span>
            </template>

            <template v-if="column.dataIndex === 'estado'" >
                <a-tag v-if="record.estado === 0" color="#476175">INSCRITO</a-tag>
                <a-tag v-else color="#b01030">NO INSCRITO</a-tag>
            </template>

            <template v-if="column.dataIndex === 'acciones'">
                <div style="display: flex; gap: 2px;">
                    <a-button size="small" @click="generarSolicitud(record.dni, record.id_proceso)" class="pre-action-btn">
                        <PrinterOutlined/>
                    </a-button>
                    <a-button size="small" @click="abrirEditar(record)" class="pre-action-btn" style="color: #000080;">
                        <form-outlined/>
                    </a-button>
                    <a-button size="small" @click="eliminar(record)" class="pre-action-btn" style="color: #ff4d4f;">
                        <delete-outlined/>
                    </a-button>
                </div>
            </template>
        </template>

    </a-table>
    </div>
    <a-pagination v-model:current="pagina" :total="totalRegistros" v-model:pageSize="pageSize" show-less-items />

    </div>

    </AuthenticatedLayout>

    <div>
        <a-modal v-model:open="visible" title="Modificar Pre inscripción" style="margin-top: -40px;">
            <a-form
                ref="formRef"
                name="custom-validation"
                :model="formState"
                v-bind="layout"
                @finish="handleFinish"
                @validate="handleValidate"
                @finishFailed="handleFinishFailed"
                >
                <a-form-item has-feedback name="nombre">
                    <label>Postulante</label>
                    <a-input type="text" v-model:value="postulante.nombre"/>
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
                            <a-select-option :value='45'>PSICOLOGÍA</a-select-option>
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
                            <a-select-option :value='9'>CEPREUNA</a-select-option>
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
import { FormOutlined, PrinterOutlined, DeleteOutlined, SearchOutlined, SaveOutlined} from '@ant-design/icons-vue';
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
    let res = await axios.post( "get-preinscripciones-admin?page="+pagina.value , { term: buscar.value, paginashoja: pageSize.value, programa: programa.value } );
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
    axios.post("actualizar-preinscripcion", post).then((result) => {
        getInscripciones()
        notificacion('success',result.data.titulo, result.data.mensaje);
        visible.value = false;
    });
}

const cambiarSexo = (postulante, sexo) => {
    let post = {
        id_postulante: postulante,
        sexo: sexo
    };
    axios.post("actualizar-sexo-postulante", post).then((result) => {
        getInscripciones()
        notificacion('success',result.data.titulo, result.data.mensaje);
        visible.value = false;
    });
}

const eliminar = (item) => {
    console.log("eliminar");
    axios.post("eliminar-preinscripcion/",{id: item.id}).then((result) => {
    getInscripciones();
    notificacion('warning', result.data.titulo, result.data.mensaje );
    });
}

const columnsInscripcion = [
    { title: 'DNI', dataIndex: 'dni', align:'center'},
    { title: 'Postulante', dataIndex: 'postulante'},
    { title: 'Programa', dataIndex:'programa'},
    { title: 'Modalidad', dataIndex:'modalidad', align:'center'},
    { title: 'Estado', dataIndex: 'estado', align:' '},
    { title: 'Acciones', dataIndex: 'acciones', width:'100px', align:'center'},
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

const generarSolicitud =  (dni, pro) => {
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = baseUrl+'/admin/pdf-solicitud/'+dni;
    document.body.appendChild(iframe);
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
}
getInscripciones()
</script>

<style scoped>
.pre-container {
    background: var(--content-bg, #f1f5f9);
    padding: 16px;
    height: calc(100vh - 98px);
    overflow-y: auto;
    border-radius: 8px;
}
.pre-search {
    max-width: 300px;
    padding-left: 10px;
}
.pre-table-card {
    background: var(--card-bg, #ffffff);
    border-radius: 8px;
    border: 1px solid var(--card-border, #e2e8f0);
    padding: 8px;
    margin-bottom: 12px;
}
.pre-name {
    font-size: 0.95rem;
    color: var(--card-text, #1e293b);
}
.pre-programa {
    color: var(--card-text, #1e293b);
}
.pre-action-btn {
    background: var(--card-bg, #ffffff) !important;
    height: 28px;
    border: 1px solid var(--card-border, #d9d9d9);
    display: flex;
    align-items: center;
    color: gray;
}
</style>

<!-- Dark/Hybrid theme overrides -->
<style>
.theme-dark .pre-container,
.theme-hybrid .pre-container {
    background: var(--content-bg) !important;
}
.theme-dark .pre-table-card,
.theme-hybrid .pre-table-card {
    background: var(--card-bg) !important;
    border-color: var(--card-border) !important;
}
.theme-dark .pre-name,
.theme-dark .pre-programa,
.theme-hybrid .pre-name,
.theme-hybrid .pre-programa {
    color: var(--card-text) !important;
}
.theme-dark .pre-action-btn,
.theme-hybrid .pre-action-btn {
    background: var(--card-bg) !important;
    border-color: var(--card-border) !important;
}

/* Table dark overrides */
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
</style>
