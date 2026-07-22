<template>
    <Head title="Sorteo"/>
    <AuthenticatedLayout>
    <div class="overflow-hidden shadow-sm sm:rounded-lg p-4" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">    


  
    <a-row class="flex" :gutter="16" style="height:500px;">
        <a-col :xs="24" :sm="12" :md="12" :lg="12" style=" display:flex;  " class="pb-3">
            <div style="width: 320px; background: #476175; height:500px; position:reactive; border-radius:12px; overflow:hidden; box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;">
                <div style="width: 320px; background: var(--card-bg, #ffffff); height:340px; position:absolute; top:160px; border-radius:20px 20px 10px 10px">

                </div>
                <div style="padding:8px; width: 200px; overflow:hidden; background: var(--card-bg, #ffffff); height:200px; position:absolute; left:66px; top:60px; border-radius:50%; border:s solid #9d9d9d; ">
                    <img src="https://i.pinimg.com/236x/6f/fb/06/6ffb06eaeaace0d90a0596fac6c6377d.jpg" style="width:200px; border-radius:50%;" height:="200px" class="img-fluid" alt="" >
                </div>
                <div style="padding:8px; height:245px; width: 320px; position:absolute; top:255px;">
                    <div class="flex justify-center mt-3">
                        <span style="font-family: 'Audiowide', sans-serif; font-size:1.5rem; color: #476175">
                           ARIEL LUQUE
                        </span>
                    </div>

                    <div class="flex justify-center mt-3">
                        <span style="font-family: 'Audiowide', sans-serif; font-size:1.0rem; text-align:center; color: #476175">
                            VIGILANTE SUPLENTE
                        </span>
                    </div>

                    <div class="flex justify-center mt-3" style="font-size:0.8; color: var(--card-muted, #64748b);">
                        <table>
                            <tr>
                                <td align="left" v-align="top">DNI</td><td>:</td><td>70757838</td>
                            </tr>
                            <tr>
                                <td align="left" v-align="top">Proc.</td><td>:</td><td>Facultad de mecánica</td>
                            </tr>
                            <tr>
                                <td align="left" v-align="top">Cond.</td><td>:</td><td>Contratado</td>
                            </tr>
                        </table>
                        <!-- <span style="font-family: sans-serif; font-size:.9rem; text-align:center;">
                            Facultad de ingenierí mecánica electrica y electrónica
                        </span> -->
                    </div>


                </div>
            </div>
        </a-col>                        
        <a-col :xs="24" :sm="12" :md="12" :lg="12" style="background:#ff000000;">
            <div style="width:100%; height:100%; display:flex; align:center; justify-content:center; align-items:center;">
                <div class="flex justify-between" style="position: relative; width:100%;">
                    <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="width: 100%; padding-left: 30px;"/>
                    <div class="mr-2" style="position: absolute; left: 8px; top: 3px; "><search-outlined /></div>
                </div>
            </div>
        </a-col>
    </a-row>

    <row class="flex justify-between mb-4" >
        <div class="mr-3">
            <a-button style="background:#476175; color:white;" @click="abrirModal">Nuevo</a-button>
        </div>
        <div class="flex justify-between" style="position: relative;" >
        <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; padding-left: 30px;"/>
        <div class="mr-2" style="position: absolute; left: 8px; top: 3px; "><search-outlined /></div>
        </div>
    </row>

    <a-modal v-model:visible="modalDocente"  style="margin-top: -40px; width:100%; display:flex; justify-content: center;" :footer="false">
        <div style="width:100%; margin-top:-5px;">
            <h1 style="font-weight:bold; font-size:1.2rem;">{{ form.id === null? 'Nuevo docente': 'Editar docente' }}</h1>            
        </div>

        <a-tabs v-model:activeKey="activeKey" type="card" size="small">
            <a-tab-pane key="1" tab="Datos del Docente">            
                <a-row style="max-width:1000px; display:flex; justify-content:center;">
                    <a-col :span="24">
                        <a-form
                            ref="formDatos"
                            name="form"
                            :model="form" :rules="formRules">
                            <a-row :gutter="16">
                            <a-col :xs="24" :sm="12" :md="24" :lg="24">
                                <div class="flex justify-end" style="">
                                    <div>
                                        <label>Estado</label>
                                        <div><a-switch v-model:checked="form.estado"/></div>
                                    </div>
                                    <a-divider type="vertical" style="height:60px;"/>
                                    <div>
                                        <label>Sexo</label>
                                        <div>
                                            <a-radio-group v-model:value="form.sexo" name="radioGroup">
                                                <a-radio :value="1">M</a-radio>
                                                <a-radio :value="2">F</a-radio>
                                            </a-radio-group>
                                        </div>
                                    </div>
                                    <a-divider type="vertical" style="height:60px" />
                                    <div>
                                        <label>Tipo Doc</label>
                                        <a-form-item
                                            name="tipo_doc"
                                            :rules="[{ required: true, message: 'Escoja el tipo', trigger: 'change' },]"
                                            >
                                            <div>
                                                <a-select
                                                    v-model:value="form.tipo_doc"
                                                    style="width: 120px">
                                                    <a-select-option :value="1">DNI</a-select-option>
                                                    <a-select-option :value="2">Carnet. Ext</a-select-option>    
                                                </a-select>

                                            </div>
                                        </a-form-item>
                                    </div>                                
                                </div>

                            </a-col>                        
                            <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                <label v-if="form.tipo_doc === 1">DNI <span style="color:red;">*</span></label>
                                <label v-else>N° CARNET EXT.</label>
                                <a-form-item 
                                    name="nro_doc" 
                                    :rules="[{ required: true, message: 'Ingrese el N° de documento'},                                            
                                    { min: 8, message: 'El dni debe tener 8 digitos', trigger: 'blur'}]"
                                >
                                <a-input v-if="form.tipo_doc === 1" v-model:value="form.nro_doc" @input="dniInput" :maxlength="8"/>
                                <a-input v-else v-model:value="form.nro_doc" @input="dniInput" :maxlength="12"/>
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                <label>Codigo</label>
                                <a-form-item>
                                <a-input v-model:value="form.codigo" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                <label>Nombres <span style="color:red;">*</span></label>
                                <a-form-item name="nombres" :rules="[{ required: true, message: 'Ingrese los nombres' }]">
                                <a-input v-model:value="form.nombres" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                <label>Ap. Paterno <span style="color:red;">*</span></label>
                                <a-form-item name="paterno" :rules="[{ required: true, message: 'Ingrese el primer apellido' }]">
                                <a-input v-model:value="form.paterno"/>
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                <label>Ap. Materno</label>
                                <a-form-item >
                                <a-input v-model:value="form.materno"/>
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12" :md="8" :lg="8">
                                <label>Condición <span style="color:red;">*</span></label>
                                <a-form-item name="condicion" :rules="[{ required: true, message: 'Ingrese la condición del docente' }]">
                                    <a-select
                                        v-model:value="form.condicion"
                                        style="width: 100%;">
                                        <a-select-option :value="1">NOMBRADO</a-select-option>
                                        <a-select-option :value="2">CONTRATADO</a-select-option>
                                        <a-select-option :value="3">CAS</a-select-option>
                                        <a-select-option :value="4">LOCACIÓN</a-select-option>
                                        <a-select-option :value="5">R. H.</a-select-option>
                                    </a-select>
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                                <label>Escuela <span style="color:red;">*</span></label>
                                <a-form-item name="escuela" :rules="[{ required: true, message: 'Seleccione la escuela' }]">
                                <a-select v-model:value="form.escuela" placeholder="Seleccionar escuela">
                                    <a-select-option :value="1">INGENIERIA AGRONOMICA</a-select-option>
                                    <a-select-option :value="2">INGENIERIA AGROINDUSTRIAL</a-select-option>
                                    <a-select-option :value="3">INGENIERIA TOPOGRAFICA Y AGRIMENSURA</a-select-option>
                                    <a-select-option :value="4">MEDICINA VETERINARIA Y ZOOTECNIA</a-select-option>
                                    <a-select-option :value="5">INGENIERIA ECONOMICA</a-select-option>
                                    <a-select-option :value="6">CIENCIAS CONTABLES</a-select-option>
                                    <a-select-option :value="7">ADMINISTRACION</a-select-option>
                                    <a-select-option :value="8">TRABAJO SOCIAL</a-select-option>
                                    <a-select-option :value="9">ENFERMERIA</a-select-option>
                                    <a-select-option :value="10">INGENIERIA DE MINAS</a-select-option>
                                    <a-select-option :value="11">HUMANIDADES</a-select-option>
                                    <a-select-option :value="12">SOCIOLOGIA</a-select-option>
                                    <a-select-option :value="13">TURISMO</a-select-option>
                                    <a-select-option :value="14">ANTROPOLOGIA</a-select-option>
                                    <a-select-option :value="15">CIENCIAS DE LA COMUNICACION SOCIAL</a-select-option>
                                    <a-select-option :value="16">ARTE</a-select-option>
                                    <a-select-option :value="17">BIOLOGIA</a-select-option>
                                    <a-select-option :value="18">EDUCACION SECUNDARIA</a-select-option>
                                    <a-select-option :value="19">EDUCACION PRIMARIA</a-select-option>
                                    <a-select-option :value="20">EDUCACION INICIAL</a-select-option>
                                    <a-select-option :value="21">EDUCACION FISICA</a-select-option>
                                    <a-select-option :value="22">INGENIERIA ESTADISTICA E INFORMATICA</a-select-option>
                                    <a-select-option :value="23">DERECHO</a-select-option>
                                    <a-select-option :value="24">INGENIERIA QUIMICA</a-select-option>
                                    <a-select-option :value="25">ODONTOLOGIA</a-select-option>
                                    <a-select-option :value="26">NUTRICION HUMANA</a-select-option>
                                    <a-select-option :value="27">INGENIERIA GEOLOGICA</a-select-option>
                                    <a-select-option :value="28">INGENIERIA METALURGICA</a-select-option>
                                    <a-select-option :value="29">INGENIERIA CIVIL</a-select-option>
                                    <a-select-option :value="30">ARQUITECTURA Y URBANISMO</a-select-option>
                                    <a-select-option :value="31">CIENCIAS FISICO MATEMATICAS</a-select-option>
                                    <a-select-option :value="32">INGENIERIA AGRICOLA</a-select-option>
                                    <a-select-option :value="33">MEDICINA HUMANA</a-select-option>
                                    <a-select-option :value="34">INGENIERIA MECANICA ELECTRICA</a-select-option>
                                    <a-select-option :value="35">INGENIERIA ELECTRONICA</a-select-option>
                                    <a-select-option :value="36">INGENIERIA DE SISTEMAS</a-select-option>
                                </a-select>
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12" :md="16" :lg="18">
                                <label>Dirección</label>
                                <a-form-item>
                                <a-input v-model:value="form.direccion" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12" :md="8" :lg="6">
                                <label>Fec. Nacimiento</label>
                                <a-form-item>
                                <a-date-picker style="width:100%;" placeholder="Seleccionar fec. nacimiento" v-model:value="form.fec_nac" format="DD/MM/YYYY"/>
                                </a-form-item>
                            </a-col>

                            <a-col :xs="24" :sm="12" :md="24" :lg="24">
                                <label>Observaciones</label>
                            <a-form-item>
                                <a-textarea v-model:value="form.observacion" />
                                </a-form-item>
                            </a-col>
                        </a-row>

                            <a-row>
                            <a-col :span="24">
                                <div class="flex justify-end">
                                    <a-button class="mr-4" @click="Cancelar()"> Cancelar </a-button>                          
                                    <a-button type="primary" @click="save">Guardar</a-button>
                                </div>
                            </a-col>
                            </a-row>
                        </a-form>
                    </a-col>
                </a-row>
            </a-tab-pane>
            <!-- <a-tab-pane key="2" tab="Medios de contacto" force-render>Content of Tab Pane 2</a-tab-pane> -->
        </a-tabs>

    </a-modal>

    <a-table 
        :columns="columnsDocentes" 
        :data-source="docentes"
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
                <span style="font-size: 0.95rem;">{{ record.paterno }} {{ record.materno }}, {{ record.nombres }}</span>
            </template>

            <template v-if="column.dataIndex === 'sexo'" >
                <a-select
                ref="select"
                v-model:value="record.sexo"
                placeholder="Seleccionar"
                style="width: 60px;"
                >
                <a-select-option value='1'><span style="color:blue">M</span></a-select-option>
                <a-select-option value='2'><span style="color:red">F</span></a-select-option>
                </a-select>
            </template>

             
            <template v-if="column.dataIndex === 'estado'" >
                <a-tag v-if="record.estado === 1" color="cyan">Habilitado</a-tag>
                <a-tag v-else color="purple">Desabilitado</a-tag>
            </template>

            <template v-if="column.dataIndex === 'acciones'">
                <a-button class="mr-1" @click="abrirEditar(record)" style="color: #006d89;" size="small">
                    <template #icon><eye-outlined/></template>
                </a-button>
                <a-button type="success" class="mr-1" style="color: #409866;" @click="cambiarSexo(record.id, record.sexo )" size="small">
                    <template #icon><SaveOutlined/></template>
                </a-button>
                <!-- <a-divider type="vertical" /> -->
                <a-button class="mr-1" @click="abrirEditar(record)" style="color: blue;" size="small">
                    <template #icon><form-outlined/></template>
                </a-button>
                <!-- <a-divider type="vertical"/> -->
                <a-popconfirm
                    v-if="docentes.length"
                    title="¿Seguro de eliminar?"
                    ok-text="Si"
                    cancel-text="No"
                    @confirm="eliminar(record)"
                    >
                    <a-button shape="" size="small" style="color: crimson;">
                        <template #icon><delete-outlined/></template>
                    </a-button>
                </a-popconfirm>
  
            </template>
        </template>
  
    </a-table> 
    <a-pagination v-model:current="pagina" :total="totalRegistros"  v-model:pageSize="pageSize" show-less-items />
    
    </div>
    
    </AuthenticatedLayout>
    
</template>
        
<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { watch, computed, ref, unref, reactive } from 'vue';
import { FormOutlined, PrinterOutlined, DeleteOutlined, SearchOutlined, SaveOutlined, EyeOutlined} from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import { Form } from 'ant-design-vue';
import axios from 'axios';
const baseUrl = window.location.origin;

const modalDocente = ref(false);
const activeKey = ref("1")

const docentes = ref([]);
const totalRegistros = ref(null)
const pageSize = ref(10)
const buscar = ref("");
const pagina = ref(1)

const formDatos = ref();
const form = reactive({ id: null, tipo_doc: 1, nro_doc: '', codigo:'', nombres: '', paterno: '', materno: '', condicion:'', escuela:null, sexo: 1, direccion: '', fec_nac: '', observacion: '', estado:true });

const dniInput = (event) => { form.nro_doc = event.target.value.replace(/\D/g, ''); };

const save = async () => {
    try {
        const values = await formDatos.value.validateFields();
        const response = await axios.post('save-docente', form);
        if (response.status === 202) {
            console.log(response.data.errors);
        } else {
            getDocentes();
            notificacion('success', response.data.titulo, response.data.mensaje); // Cambia los valores aquí
            modalDocente.value = false;
            limpiar()
        }
    } catch (error) {
        console.error(error);
    }
}


const getDocentes =  async ( ) => {
    let res = await axios.post( "get-docentes?page="+pagina.value , { term: buscar.value, paginashoja: pageSize.value } );
    docentes.value = res.data.datos.data;
    totalRegistros.value = res.data.datos.total;
}

const eliminar = (item) => { axios.post("eliminar-docente", {"id":item.id}).then((result) => { getDocentes(); notificacion('warning', result.data.titulo, result.data.mensaje ); }); }

const inscripcion = ref({  id:null,  codigo:"",  id_posulante:"",  id_programa:"",  id_modalidad:"", estado: true,  observacion:"",})


const cambiarSexo = (docente, sexo) => {
    let post = { id_postulante: docente, sexo: sexo };
    axios.post("actualizar-sexo-docente", post).then((result) => {
        getDocentes()
        limpiar()
        notificacion('success',result.data.titulo, result.data.mensaje);
    });
}

const notificacion = (type, titulo, mensaje) => { notification[type]({ message: titulo,description: mensaje});};
const abrirModal = () => { limpiar(); modalDocente.value = true; }
const Cancelar = () => { modalDocente.value = false;  limpiar(); }
const abrirEditar = (item) => {
    form.id =  item.id,
    form.tipo_doc = 1, 
    form.nro_doc = item.dni, 
    form.codigo = item.codigo, 
    form.nombres = item.nombres, 
    form.paterno = item.paterno, 
    form.materno = item.materno, 
    form.condicion = item.condicion, 
    form.escuela = item.escuela, 
    form.sexo = parseInt(item.sexo) 
    form.direccion = item.direccion 
    form.fec_nac = item.fec_nac
    form.observacion = item.observacion
    if(item.estado === 1){ form.estado = true } else { form.estado = false } 
    modalDocente.value = true;

}

const limpiar = () => {
    form.id = null,
    form.tipo_doc = 1, 
    form.nro_doc = null, 
    form.codigo = null, 
    form.nombres = null, 
    form.paterno = null, 
    form.materno = null, 
    form.condicion = null, 
    form.escuela = null, 
    form.sexo = 1, 
    form.direccion = null 
    form.fec_nac = null
    form.observacion = null
    form.estado = true
}

watch(pagina, ( newValue, oldValue ) => { getDocentes(); })
watch(buscar, ( newValue, oldValue ) => { getDocentes() })
watch(pageSize, ( newValue, oldValue ) => { getDocentes() })

const columnsDocentes = [
    { title: 'DNI', dataIndex: 'dni', align:'center', width:'90px'},
    { title: 'Codigo', dataIndex: 'codigo'},
    { title: 'Docente', dataIndex: 'postulante'},
    { title: 'Sexo', dataIndex: 'sexo', align:'center' },
    { title: 'Escuela', dataIndex:'escuela_name'},
    { title: 'Condición', dataIndex: 'tipo_empleo', align:'center'},
    { title: 'Estado', dataIndex: 'estado', align:'center'},
    // { title: 'Observación', dataIndex: 'observacion'},
    { title: 'Acciones', dataIndex: 'acciones', width:'160px', align:'center'},
];

getDocentes()
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Audiowide&display=swap');
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

