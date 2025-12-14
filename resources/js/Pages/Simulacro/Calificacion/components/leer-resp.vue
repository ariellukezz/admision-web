<template>
    <div style="" @click="clicIzquierdo" @contextmenu.prevent="handleContextMenu">
        <div class="pl-4" style="background: white; width: 100%; min-height: calc(100vh - 190px); border-radius: 12px;" >
          
        <div class="flex justify-between mt-2">
          <a-radio-group v-model:value="tabPosition" style="margin-left: -3px;">
            <a-radio-button value="contenido" style="border-radius: 9px 0px 0px 9px;">Respuestas</a-radio-button>
            <a-radio-button value="archivos" style="border-radius: 0px 9px 9px 0px;">Archivos</a-radio-button>
          </a-radio-group>
          <a-input v-model:value="buscar" style="max-width: 260px; border-radius: 6px; height: 32px;" placeholder="Buscar">
              <template #prefix>
                  <span style="color: #0000009d; margin-top: -6px;"><SearchOutlined/></span>
              </template>
          </a-input>
        </div>
    
    
        <div v-if="tabPosition === 'archivos'" class="mt-3 mb-3" style="margin-left: -5px;">
        <a-table 
            :columns="columnsArchivos"
            :data-source="archivos"
            :key="id"
            size="small"
            :pagination="false"
            style="scale: .7rem;"
            > 
            <template #bodyCell="{ column, index, record }">
    
                <template v-if="column.dataIndex === 'nro'">
                    <span>{{ index + 1 }}</span>
                </template>
                <template v-if="column.dataIndex === 'nombre'">
                    <div style="width:100%; cursor:pointer;" @dblclick="customRow(record)" >
                        <span>{{ record.nombre }}</span>
                    </div>
                </template>
    
                <template v-if="column.dataIndex === 'area'">
                    <div class="flex" style="justify-content: center;">
                        <div v-if="1 === record.area"> <a-tag color="cyan">Biomedicas</a-tag></div>
                        <div v-if="2 === record.area"> <a-tag color="orange">Ingenierías</a-tag></div>
                        <div v-if="3 === record.area"> <a-tag color="purple">Sociales</a-tag></div>
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
        </div>
    
    
        <div v-if="tabPosition === 'contenido' " class="mt-3 mb-3" style="margin-left: -5px;">
        <a-table 
            :columns="columnsIdes"
            :data-source="identificaciones"
            :key="id"
            size="small"
            :scroll="{ y: 'calc(100vh - 300px)' }"
            :pagination="false"
            style="scale: .7rem;"
            > 
            <template #bodyCell="{ column, index, record }">
    
                <template v-if="column.dataIndex === 'nro'">
                    <span> {{ index + 1 }} </span>
                </template>
    
                <template v-if="column.dataIndex === 'observaciones'">
                    <a-tag v-if="record.dni === null && record.ide_litho !== null" color="pink"> Sin DNI</a-tag>
                    <a-tag v-if="record.len_doc !== 8 && record.ide_litho !== null && record.dni !== null" color="green"> Dni erroneo</a-tag>
                    <a-tag v-if="record.vaula === 0" color="blue"> Error de aula</a-tag>
                    <a-tag v-if="record.tipo === '' && record.ide_litho !== null" color="yellow"> Sin tipo</a-tag>
                    <a-tag v-if="record.c_tipo === 0 && record.ide_litho !== null " color="navy"> Tipo diferente</a-tag>
                    <a-tag v-if="record.c_aula === 0 && record.ide_litho !== null" color="brown"> Aula diferente</a-tag>
                    <a-tag v-if="record.ide_litho === null" color="crimson"> SIN IDE</a-tag>
    
                    <!-- <span>{{ record.dni }} {{ record.aula }} {{ record.tipo }} {{ record.len_doc }} {{ record.vdni }} {{ record.vaula }} </span> -->
                </template>
    
                <template v-if="column.dataIndex === 'acciones'">
                    <div style="display: flex; gap: 3px; padding-right: 0px;">                      
                        <a-button size="small"  @click="verFicha(record.id)" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: gray; display: flex; align-items: center;">
                            <eye-outlined/>
                        </a-button>

                        <a-button size="small"  @click="abrirEditar(record)" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: #af7200; display: flex; align-items: center;">
                            <form-outlined/>
                        </a-button>
                        <a-popconfirm
                            title="¿Estas seguro de eliminar?"
                            @confirm="eliminar(record)"
                            >
                            <a-button size="small"  @click="eliminar(record)" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: crimson; display: flex; align-items: center;">
                                    <delete-outlined/>
                            </a-button>
                        </a-popconfirm>
                    </div>

                </template> 
            </template>
        </a-table> 
        </div>
    
        <a-modal v-model:open="visible" title="Cargar fichas de respuestas" @ok="okey" :centered="true" style="max-height: calc(100vh - 100px); overflow-x: scroll; cursor: pointer;">
          <div class=" flex justify-between mb-4">
            <!-- <a-input style="width: 23%;" v-model:value="aula" placeholder="Aula"/> -->
          </div>
          <a-upload-dragger
            v-model:fileList="fileList"
            name="file"
            :multiple="true"
            :action="baseUrl + '/calificacion/carga-res/'+proceso+'/'"
            @change="handleChange"
            @drop="handleDrop"
            list-type="picture"
          >
            <p class="ant-upload-drag-icon ">
              <inbox-outlined></inbox-outlined>
            </p>
            <p class="ant-upload-text" style="width: 100%;">Haz clic o arrastra archivos a esta área para cargar</p>
            <p class="ant-upload-hint">
              Soporte para carga única o múltiple. Prohibido subir datos de la empresa u otros archivos prohibidos.
            </p>
          </a-upload-dragger>  
        </a-modal>
        </div>
    
        <div style=" position: absolute; border: solid 1px #d9d9d943; padding-top: 0px; border-radius:8px; overflow: hidden;" v-if="showContextMenu" :style="{ top: `${contextMenuTop }px`, left: `${contextMenuLeft}px`}">
            <a-menu size="small" style="margin-top: -4px; margin-bottom: -4px;" >
                <a-menu-item key="1" class="selec-menu" @click="handleMenuItemClick('1')">
                    <div style="margin-top: 0px;">
                        Cargar respuestas
                    </div>
                </a-menu-item>
                <a-menu-item key="2" class="selec-menu" @click="handleMenuItemClick('2')">
                    <div style="margin-top: 0px;">
                        Nueva respuesta
                    </div>
                </a-menu-item>
                <a-menu-item key="2" class="selec-menu" @click="handleMenuItemClick('2')">
                    <div style="margin-top: 0px;">
                        asdfas
                    </div>
                </a-menu-item>
            </a-menu>
        </div>

        <a-modal v-model:open="modalficha" :centered="true" :footer="false" style="scale: .85; width: 880px;">
            <VerFicha :id_resp="id_respuesta"/>
        </a-modal>
    
    
    </div>  
    </template>
      
<script setup>
import { defineProps, watch, ref } from 'vue';
import axios from 'axios';
import { FolderOutlined, HomeOutlined, EnvironmentOutlined, DownOutlined, FormOutlined, DeleteOutlined, SaveOutlined, SearchOutlined, EyeOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
import VerFicha from './ficha.vue'
import { notification } from 'ant-design-vue';
const baseUrl = window.location.origin;

const tabPosition = ref('contenido')
const aula = ref()
const fileList = ref([]);

const id_respuesta = ref(null);

const props = defineProps(['proceso']);

const visible = ref(false);
const area = ref(1);
// const area = ref(null); 

const handleChange = (info) => {
    const status = info.file.status;
    if (status !== 'uploading') { console.log(info.file, fileList.value); }
    if (status === 'done') {
        message.success(`${info.file.name} archivo(s) subido(s) exitosamente.`);
        getArchivos();
        getIdes();
        visible.value = false;
    } else if (status === 'error') {
        message.error(`${info.file.name} falló al subir.`);
    }
};

const okey = () => { fileList.value = null;};
const showContextMenu = ref(false);
const contextMenuTop = ref(0);
const contextMenuLeft = ref(0);

const handleContextMenu = (event) => {
    showContextMenu.value = true;
    contextMenuTop.value = event.clientY;
    contextMenuLeft.value = event.clientX;
    event.preventDefault();
};



const handleMenuItemClick = ( opcion ) => {
    if(opcion === '1'){ visible.value = true; showContextMenu.value = false;}
};

const clicIzquierdo = (event) => { showContextMenu.value = false;}

const archivos = ref([]);
const identificaciones = ref([]);
const buscar = ref("");

const getArchivos = async () => {
    axios.post("/get-archivos-res",{"term": buscar.value, "proceso": props.proceso})
    .then((response) => {
        archivos.value = response.data.datos.data;
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
    });
}

const getIdes = async () => {
    axios.post("/get-res",{"term": buscar.value, "proceso": props.proceso})
    .then((response) => {
        identificaciones.value = response.data.datos.data;
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
    });
}

let timeoutId;
watch(buscar, ( newValue, oldValue ) => { 
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        getIdes() 
    }, 300);    
})


const modalficha = ref(false);
const verFicha = (id_res) => {
    id_respuesta.value = id_res;
    modalficha.value = true;
}
    
    
getArchivos()
getIdes()

const columnsArchivos = [
    { title: 'N°', dataIndex: 'nro', width:'40px', align:"center"},
    { title: 'Tipo', dataIndex: 'tipo', align:'center', width:'100px'},
    { title: 'Nombre', dataIndex: 'nombre',},
    { title: 'Area', dataIndex: 'area',},
    { title: 'Fecha', dataIndex: 'fecha', align:'center'},
    { title: 'Registros', dataIndex: 'registros', align:'center'},
    { title: 'Acciones', dataIndex: 'acciones', align:'center', width:'96px'}
];

const columnsIdes = [
    { title: 'N°', dataIndex: 'nro', width:'40px', align:"center"},
    { title: 'N° lectura', dataIndex: 'n_lectura', align:'center', width:'80px'},
    { title: 'DNI', dataIndex: 'dni', align:'center', width:'90px'},
    { title: 'Tip', dataIndex: 'tipo', width:'60px', align:"center",width:'40px'},
    { title: 'Litho', dataIndex: 'res_litho', align:'center',width:'80px'},
    { title: 'Respuestas', dataIndex: 'respuestas', align:'left' },
    { title: 'Observaciones', dataIndex: 'observaciones', align:'center'},
    { title: 'Acciones', dataIndex: 'acciones', align:'center', width:'106px'},
];

const eliminar = (item) => {
    axios.get("eliminar-archivo/"+item.id).then((result) => {
        getArchivos();
        getIdes();
        notificacion('error', result.data.titulo, result.data.mensaje );
    });
}

const notificacion = (type, titulo, mensaje) => { notification[type]({ message: titulo, description: mensaje, }); };

</script>
      