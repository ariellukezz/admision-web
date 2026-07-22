<template>
    <Head title="Administrativo"/>
    <AuthenticatedLayout>
    <div class="overflow-hidden shadow-sm sm:rounded-lg p-4" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">    

    <row class="flex justify-between mb-4" >
        <div class="mr-3">
            <a-button type="success" @click="abrirModal">Nuevo</a-button>
        </div>
        <div class="flex justify-between" style="position: relative;" >
        <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; padding-left: 30px;"/>
        <div class="mr-2" style="position: absolute; left: 8px; top: 3px; "><search-outlined /></div>
        </div>
    </row>

    <a-modal v-model:visible="modalAdministrativo"  style="margin-top: -40px; width:100%; display:flex; justify-content: center;" :footer="false">
        <div style="width:100%; margin-top:-5px;">
            <h1 style="font-weight:bold; font-size:1.2rem;">{{ form.id === null? 'Nuevo administrativo': 'Editar administrativo' }}</h1>            
        </div>

        <a-tabs v-model:activeKey="activeKey" type="card" size="small">
            <a-tab-pane key="1" tab="Datos del Administrativo">            
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
                                            :rules="[{ required: true, message: 'Escoja el tipo', trigger: 'change' },]">
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
                                <a-form-item name="condicion" :rules="[{ required: true, message: 'Ingrese la condición del administrativo' }]">
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
                                <label>Dependencia <span style="color:red;">*</span></label>
                                <a-form-item name="dependencia" :rules="[{ required: true, message: 'Seleccione la dependencia'}]">
                                <a-select v-model:value="form.dependencia" placeholder="Seleccionar dependencia">
                                    <a-select-option :value="1">RECTORADO</a-select-option>
                                    <a-select-option :value="2">VICERRECTORADO</a-select-option>
                                    <a-select-option :value="3">DIRECCIÓN DE ADMISIÓN</a-select-option>
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
        :columns="columnsAdministrativos" 
        :data-source="administrativos"
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

            <template v-if="column.dataIndex === 'nombres'">
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
                    v-if="administrativos.length"
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
    <div class="mt-2 flex justify-end">
        <a-pagination  v-model:current="pagina" :total="totalRegistros"  v-model:pageSize="pageSize" show-less-items />
        <div class="ml-2">
            <a-select v-model:value="pageSize" placeholder="Seleccionar dependencia">
                <a-select-option :value="10">10</a-select-option>
                <a-select-option :value="20">20</a-select-option>
                <a-select-option :value="100">100</a-select-option>
            </a-select>
        </div>
    </div>

    
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

const modalAdministrativo = ref(false);
const activeKey = ref("1")

const administrativos = ref([]);
const totalRegistros = ref(null)
const pageSize = ref(10)
const buscar = ref("");
const pagina = ref(1)

const formDatos = ref();
const form = reactive({ id: null, tipo_doc: 1, nro_doc: '', codigo:'', nombres: '', paterno: '', materno: '', condicion:'', dependencia:null, sexo: 1, direccion: '', fec_nac: '', observacion: '', estado:true });

const dniInput = (event) => { form.nro_doc = event.target.value.replace(/\D/g, ''); };

const save = async () => {
    try {
        const values = await formDatos.value.validateFields();
        const response = await axios.post('save-administrativo', form);
        if (response.status === 202) {
            console.log(response.data.errors);
        } else {
            getAdministrativos();
            notificacion('success', response.data.titulo, response.data.mensaje); // Cambia los valores aquí
            modalAdministrativo.value = false;
            limpiar()
        }
    } catch (error) {
        console.error(error);
    }
}


const getAdministrativos =  async ( ) => {
    let res = await axios.post( "get-administrativos?page="+pagina.value , { term: buscar.value, paginashoja: pageSize.value } );
    administrativos.value = res.data.datos.data;
    totalRegistros.value = res.data.datos.total;
}

const eliminar = (item) => { axios.post("eliminar-administrativo", {"id":item.id}).then((result) => { getAdministrativos(); notificacion('warning', result.data.titulo, result.data.mensaje ); }); }

const inscripcion = ref({  id:null,  codigo:"",  id_posulante:"",  id_programa:"",  id_modalidad:"", estado: true,  observacion:"",})


const cambiarSexo = (administrativo, sexo) => {
    let post = { id_postulante: administrativo, sexo: sexo };
    axios.post("actualizar-sexo-administrativo", post).then((result) => {
        getAdministrativos()
        limpiar()
        notificacion('success',result.data.titulo, result.data.mensaje);
    });
}

const notificacion = (type, titulo, mensaje) => { notification[type]({ message: titulo,description: mensaje});};
const abrirModal = () => { limpiar(); modalAdministrativo.value = true; }
const Cancelar = () => { modalAdministrativo.value = false;  limpiar(); }
const abrirEditar = (item) => {
    form.id =  item.id,
    form.tipo_doc = 1, 
    form.nro_doc = item.dni, 
    form.codigo = item.codigo, 
    form.nombres = item.nombres, 
    form.paterno = item.paterno, 
    form.materno = item.materno, 
    form.condicion = item.condicion, 
    form.dependencia = item.dependencia, 
    form.sexo = parseInt(item.sexo) 
    form.direccion = item.direccion 
    form.fec_nac = item.fec_nac
    form.observacion = item.observacion
    if(item.estado === 1){ form.estado = true } else { form.estado = false } 
    modalAdministrativo.value = true;

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
    form.dependencia = null, 
    form.sexo = 1, 
    form.direccion = null 
    form.fec_nac = null
    form.observacion = null
    form.estado = true
}

watch(pagina, ( newValue, oldValue ) => { getAdministrativos(); })
watch(buscar, ( newValue, oldValue ) => { getAdministrativos() })
watch(pageSize, ( newValue, oldValue ) => { getAdministrativos() })

const columnsAdministrativos = [
    { title: 'DNI', dataIndex: 'dni', align:'center', width:'90px',},
    { title: 'Codigo', dataIndex: 'codigo', align:'center' },
    { title: 'Administrativo', dataIndex: 'nombres'},
    { title: 'Sexo', dataIndex: 'sexo', align:'center' },
    { title: 'Dependencia', dataIndex:'dependencia_name'},
    { title: 'Condición', dataIndex: 'tipo_empleo', align:'center'},
    { title: 'Estado', dataIndex: 'estado', align:'center'},
    // { title: 'Observación', dataIndex: 'observacion'},
    { title: 'Acciones', dataIndex: 'acciones', width:'160px', align:'center'},
];

getAdministrativos()
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
