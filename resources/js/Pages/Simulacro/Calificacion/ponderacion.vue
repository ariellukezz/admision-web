<template>
    <Head title="Ponderacion"/>
    <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="border-radius: 10px; width: 100%; min-height: calc(100vh - 95px);">
    
    <div class="flex justify-between mb-2" >
        <div class="mr-3">
        <a-button type="primary" @click="showModalFilial" style="background: #476175; border: none; border-radius: 5px;">Nuevo</a-button>
        </div>
        <div class="flex justify-between" style="position: relative;" >
            <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; border-radius:6px; padding-left: 10px;">
                <template #prefix><search-outlined /></template>
            </a-input>        
        </div>
    </div>

    <a-table 
        :columns="columnsFiliales" 
        :data-source="ponderaciones"
        :key="id"
        size="small"
        :pagination="false"
        style="scale: .7rem;"
        > 
        <template #bodyCell="{ column, index, record }">
    
            <template v-if="column.dataIndex === 'nro'">
                <div class="flex" style="justify-content: center;">
                    <span style="font-weight: bold;">{{ index + 1 }}</span>
                </div>
            </template>
    
            <template v-if="column.dataIndex === 'acciones'">
                <div class="acciones-container">
                    <a-tooltip title="Ver detalles" placement="top">
                        <a-button 
                            class="accion-btn ver-btn"
                            @click="abrirDetallePonderacion(record)"
                            size="small"
                            type="text"
                        >
                            <template #icon>
                                <div class="btn-icon-wrapper">
                                    <EyeOutlined class="btn-icon" />
                                </div>
                            </template>
                        </a-button>
                    </a-tooltip>

                    <a-tooltip title="Editar" placement="top">
                        <a-button 
                            class="accion-btn editar-btn"
                            @click="abrirEditarPonderacion(record)"
                            size="small"
                            type="text"
                        >
                            <template #icon>
                                <div class="btn-icon-wrapper">
                                    <FormOutlined class="btn-icon" />
                                </div>
                            </template>
                        </a-button>
                    </a-tooltip>

                    <a-tooltip title="Eliminar" placement="top">
                        <a-popconfirm
                            title="¿Estás seguro de eliminar esta ponderación?"
                            ok-text="Sí, eliminar"
                            cancel-text="Cancelar"
                            @confirm="eliminarPonderacion(record)"
                        >
                            <a-button 
                                class="accion-btn eliminar-btn"
                                size="small"
                                type="text"
                            >
                                <template #icon>
                                    <div class="btn-icon-wrapper">
                                        <DeleteOutlined class="btn-icon" />
                                    </div>
                                </template>
                            </a-button>
                        </a-popconfirm>
                    </a-tooltip>
                </div>
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
        <a-modal v-model:open="visible" :title="ponderacion.id?'Editar Ponderación':'Nueva ponderación'">
        <div class="mt-6">
            <a-form
                ref="formPonderacion"
                name="form"
                :model="ponderacion"
                v-bind="layout"
                >
                <a-form-item label="Nombre" :rules="[{ required: true, message: 'Ingrese el nombre', trigger: 'change' },]"name="nombre">
                    <a-input type="text" placeholder="Ingrese el nombre" v-model:value="ponderacion.nombre" autocomplete="off"  >
                        <template #prefix><sin-icono /></template>
                    </a-input>
                </a-form-item>

                <a-form-item class="mt-4" name="area" label="Area" :rules="[{ required: true, message: 'Seleccione un area', trigger: 'change' },]">
                    <a-select ref="select" v-model:value="ponderacion.area" style="width: 100%">
                        <a-select-option :value="1">BIOMEDICAS</a-select-option>
                        <a-select-option :value="2">INGENIERIAS</a-select-option>
                        <a-select-option :value="3">SOCIALES</a-select-option>
                    </a-select>
                </a-form-item>
        
            </a-form>
        </div>
    
        <template #footer>
            <a-button style="margin-left: 6px; border-radius: 4px;" @click="resetForm">Cancelar</a-button>
            <a-button type="primary" style="background: #476175; border:none; border-radius: 4px;" @click="guardar()">Guardar</a-button>
        </template>
        </a-modal>
    </div>



<div>
  <a-modal
    v-model:open="modalDetallePonderacion"
    title="Detalle Ponderación"
    :width="900"
    body-style="max-height: 600px; overflow-y: auto; padding: 16px;"
    centered
  >
    <a-form
      ref="formPesos"
      name="formpesos"
      :model="pesos"
      layout="horizontal"
    >
      <div v-for="(item, index) in nroItems" :key="index" class="mb-0">
        <a-row :gutter="[8, 0]" align="middle">

          <a-col :xs="24" :sm="2" :md="1" class="flex justify-center">
            <a-button type="default" size="small" style="height: 30px; width: 30px; margin-top: -22px;">{{ index + 1 }}</a-button>
          </a-col>

          <a-col :xs="24" :sm="11" :md="15">
            <a-form-item :name="'nombre_' + index">
              <a-input 
                v-model:value="pesos[index].nombre" 
                placeholder="Asignatura" 
                size=""
              >
                <template #prefix><sin-icono /></template>
              </a-input>
            </a-form-item>
          </a-col>

          <a-col :xs="12" :sm="6" :md="4">
            <a-form-item :name="'npreguntas_' + index">
              <a-input 
                v-model:value="pesos[index].n_preguntas" 
                placeholder="N° preguntas" 
                size=""
              >
                <template #prefix><sin-icono /></template>
              </a-input>
            </a-form-item>
          </a-col>

          <a-col :xs="12" :sm="5" :md="4">
            <a-form-item :name="'ponderacion_' + index">
              <a-input 
                v-model:value="pesos[index].ponderacion" 
                placeholder="Ponderación 0.000" 
                size=""
              >
                <template #prefix><sin-icono /></template>
              </a-input>
            </a-form-item>
          </a-col>

        </a-row>
      </div>
    </a-form>

    <template #footer>
      <div class="flex justify-between w-full items-center mt-2">
        <a-button type="dashed"  @click="agregarFila">+ 1 más</a-button>

        <div class="flex gap-2">
          <a-button  @click="resetForm">Cancelar</a-button>
          <a-button
            type="primary"
            style="background: #476175; border:none; border-radius: 4px;"
            @click="saveDetalle"
          >
            Guardar
          </a-button>
        </div>
      </div>
    </template>
  </a-modal>
</div>


    
</template>
    
<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutCalificador.vue'
import { watch, computed, ref, onMounted, reactive } from 'vue';
import { FormOutlined, DeleteOutlined, SearchOutlined, DownOutlined, EyeOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';

import axios from 'axios';
const loading = ref(false);
const buscar = ref("");
const visible = ref(false);
const buscarResidencia = ref("")
const pagina = ref(1);
const paginasize = ref(10);
const totalRegistros = ref(1);



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

const ponderaciones = ref([])

const getPonderaciones =  async () => {
    let res = await axios.post("get-ponderaciones?page=" + pagina.value, { term: buscar.value, paginasize: paginasize.value } );
    ponderaciones.value = res.data.datos.data;
    totalRegistros.value = res.data.datos.total;
}


const formPonderacion = ref()
const ponderacion = reactive({ id:null, nombre:"", area:null, simulacro:"" })
const buscarSimulacro = ref("")
const simulacro = ref(null)
const simulacros = ref([])

const modalDetallePonderacion = ref(false)

const getSimulacros = async () => {
    axios.post("/get-simulacros",{"term": buscarSimulacro.value})
    .then((response) => {
        simulacros.value = response.data.datos.data;
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
    });
}

const onSelect = (value, option) => { simulacro.value = option; };
let timeoutIde;
watch(buscarSimulacro, ( newValue, oldValue ) => { 
    clearTimeout(timeoutIde);
    timeoutIde = setTimeout(() => {
        getSimulacros();
    }, 500);    
})


const guardar = async () => {
    loading.value = true;
    try {
        const values = await formPonderacion.value.validateFields();
        axios.post("save-ponderacion", ponderacion).then((result) => {
            notificacion('success',result.data.titulo, result.data.mensaje);
            getPonderaciones()
            visible.value = false;
        });
        
    } catch (error) {
        console.error(error);
    }

}


const saveDetalle = async () => {
    loading.value = true;
    try {
        axios.post("save-ponderacion-detalle", {"pesos":pesos.value, "id_ponderacion": id_ponderacion.value }).then((result) => {
            notificacion('success',result.data.titulo, result.data.mensaje);
            getPonderaciones()
            visible.value = false;
        });
        
    } catch (error) {
        console.error(error);
    }

}

getSimulacros()
getPonderaciones()

const id_ponderacion = ref(null)
const nroItems = ref(1);
const pesos = ref([
    {"nombre":null, "ponderacion":null, "n_preguntas":null}
]);
const abrirDetallePonderacion = (id) => {
    modalDetallePonderacion.value = true;
    id_ponderacion.value = id;
}

const agregarFila = () => { 
    pesos.value.push({"nombre":null, "ponderacion":null, "n_preguntas":null});
    nroItems.value += 1; 
}


    
const layout = { labelCol: { span: 4, }, wrapperCol: { span: 20, } };
const columnsFiliales = [
    { title: 'N°', dataIndex: 'nro',},
    { title: 'Nombre', dataIndex: 'nombre',},
    { title: 'Area', dataIndex: 'area',},
    { title: 'Acciones', dataIndex: 'acciones', align:'center', width:'96px'},
];

</script>
<style scoped>
.acciones-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 4px;
    padding: 4px 0;
}

.accion-btn {
    width: 30px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid transparent;
}

.accion-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.btn-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.btn-icon {
    font-size: 14px;
    transition: transform 0.2s ease;
}

.accion-btn:hover .btn-icon {
    transform: scale(1.1);
}

.ver-btn {
    color: #476175;
    background: rgba(71, 97, 117, 0.08);
}

.ver-btn:hover {
    color: #476175;
    background: rgba(71, 97, 117, 0.12);
    border-color: rgba(71, 97, 117, 0.3);
}

.editar-btn {
    color: #1890ff;
    background: rgba(24, 144, 255, 0.08);
}

.editar-btn:hover {
    color: #1890ff;
    background: rgba(24, 144, 255, 0.12);
    border-color: rgba(24, 144, 255, 0.3);
}

.eliminar-btn {
    color: #ff4d4f;
    background: rgba(255, 77, 79, 0.08);
}

.eliminar-btn:hover {
    color: #ff4d4f;
    background: rgba(255, 77, 79, 0.12);
    border-color: rgba(255, 77, 79, 0.3);
}

:deep(.ant-btn) {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.ant-tooltip-inner) {
    border-radius: 4px;
    font-size: 12px;
    padding: 4px 8px;
}

@media (max-width: 768px) {
    .acciones-container {
        gap: 2px;
    }
    
    .accion-btn {
        width: 28px;
        height: 28px;
    }
    
    .btn-icon {
        font-size: 12px;
    }
}
</style>