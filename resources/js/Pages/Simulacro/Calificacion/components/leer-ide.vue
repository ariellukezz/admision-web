<template>
<div style="" @click="clicIzquierdo" @contextmenu.prevent="handleContextMenu">
    <div class="pl-4" style="background: white; width: 100%; min-height: calc(100vh - 190px); border-radius: 12px;" >
      
    <div class="flex justify-between mt-2">
      <a-radio-group v-model:value="tabPosition" style="margin-left: -3px;">
        <a-radio-button value="contenido" style="border-radius: 9px 0px 0px 9px;">ide</a-radio-button>
        <a-radio-button value="archivos" style="border-radius: 0px 9px 9px 0px;">file</a-radio-button>
      </a-radio-group>
      {{ tabPosition }}
      <a-input v-model:value="buscar" style="max-width: 260px; border-radius: 6px; height: 32px;" placeholder="Buscar">
          <template #prefix>
              <span style="color: #0000009d; margin-top: -6px;"><SearchOutlined/></span>
          </template>
      </a-input>
          <!-- <div><a-button type="primary" @click="visible = true" style="background: #476175; border: none; border-radius: 5px;">subir Ides</a-button></div>
          -->
            <!-- <p>proceso: {{ proceso }}</p> -->
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
        :pagination="false"
        style="scale: .7rem;"
        > 
        <template #bodyCell="{ column, index, record }">

            <template v-if="column.dataIndex === 'nro'">
                <span>{{ index + 1 }}</span>
            </template>

            <template v-if="column.dataIndex === 'observaciones'">
                <a-tag v-if="record.dni === null" color="pink"> Sin DNI</a-tag>
                <a-tag v-if="record.aula === ''" color="purple"> Sin aula</a-tag>
                <a-tag v-if="record.len_doc !== 8 && record.dni !== null" color="green"> Dni erroneo</a-tag>
                <a-tag v-if="record.vaula === 0 && record.aula !== ''" color="blue"> Error de aula</a-tag>
                <a-tag v-if="record.tipo === ''" color="yellow"> Sin tipo</a-tag>

                <!-- <span>{{ record.dni }} {{ record.aula }} {{ record.tipo }} {{ record.len_doc }} {{ record.vdni }} {{ record.vaula }} </span> -->
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

    
    <div class="flex justify-between mt-4 mb-4 ml-1"> 
        <a-button type="primary" style="background: #476175; border:none; border-radius: 4px;" @click="descargarObservaciones(proceso)">Descargar observaciones</a-button>
    </div>

    </div>

    <a-modal v-model:visible="visible" title="Cargar fichas de identificación" @ok="okey" :centered="true" style="max-height: calc(100vh - 100px); overflow-x: scroll; cursor: pointer;">
      <div class="justify-end mb-4">
        <a-select
            class="mb-2"
            style="width: 100%;"
            v-model:value="area">
            <a-select-option :value="1">Biomédicas</a-select-option>
            <a-select-option :value="2">Ingenierías</a-select-option>    
            <a-select-option :value="3">Sociales</a-select-option>    
        </a-select>
      </div>
      <a-upload-dragger
        v-model:fileList="fileList"
        name="file"
        :multiple="true"
        :action="baseUrl + '/calificacion/carga-ide/'+proceso+'/'+area"
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
                    Cargar archivos
                </div>
            </a-menu-item>
            <a-menu-item key="2" class="selec-menu" @click="handleMenuItemClick('2')">
                <div style="margin-top: 0px;">
                    Nuevos Ides
                </div>
            </a-menu-item>
            <a-menu-item key="2" class="selec-menu" @click="handleMenuItemClick('2')">
                <div style="margin-top: 0px;">
                    selees
                </div>
            </a-menu-item>
        </a-menu>
    </div>

</div>



</template>
  
<script setup>
import { defineProps, watch, ref } from 'vue';
import axios from 'axios';
import { FolderOutlined, HomeOutlined, EnvironmentOutlined, DownOutlined, FormOutlined, DeleteOutlined, SaveOutlined, SearchOutlined } from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';
const baseUrl = window.location.origin;
const fileList = ref([]);
const tabPosition = ref('contenido')
import { notification } from 'ant-design-vue';

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
    axios.post("/get-archivos",{"term": buscar.value, "proceso": props.proceso})
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
    axios.post("/get-ides",{"term": buscar.value, "proceso": props.proceso})
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


getArchivos()
getIdes()

const columnsArchivos = [
    { title: 'N°', dataIndex: 'nro', width:'40px', align:"center"},
    { title: 'Tipo', dataIndex: 'tipo', align:'center', width:'100px'},
    { title: 'Nombre', dataIndex: 'nombre',},
    { title: 'Area', dataIndex: 'area',},
    { title: 'Fecha', dataIndex: 'fecha', align:'center'},
    { title: 'Registros', dataIndex: 'registros', align:'center'},
    { title: 'Acciones', dataIndex: 'acciones', align:'center', width:'96px'},

];

const columnsIdes = [
    { title: 'N°', dataIndex: 'nro', width:'40px', align:"center"},
    { title: 'N° lectura', dataIndex: 'camp2', align:'center'},
    { title: 'DNI', dataIndex: 'dni', align:'center'},
    { title: 'Aula', dataIndex: 'aula', width:'60px', align:"center"},
    { title: 'Tip', dataIndex: 'tipo', width:'60px', align:"center"},
    { title: 'Litho', dataIndex: 'litho', align:'center'},
    { title: 'Observaciones', dataIndex: 'observaciones', align:'center'},
    { title: 'Acciones', dataIndex: 'acciones', align:'center', width:'96px'},
];

const eliminar = (item) => {
    axios.get("eliminar-archivo/"+item.id).then((result) => {
        getArchivos();
        notificacion('error', result.data.titulo, result.data.mensaje );
    });
}

const notificacion = (type, titulo, mensaje) => { notification[type]({ message: titulo, description: mensaje, }); };

const descargarObservaciones = (pro) => {
  const url = baseUrl + "/pdf-errores/" + pro;
  const link = document.createElement('a');
  link.href = url;
  link.download = 'observaciones.pdf';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

</script>
  