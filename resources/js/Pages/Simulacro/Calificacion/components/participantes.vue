<template>
<Head title="Participantes"/>    
<div class="p-4" style="width:100%; background:white; border-radius:8px;">
    <form @submit.prevent="submit">
        <div class="flex justify-between">
            <label
            for="fileInput"
            class="inline-block bg-[var(--primary-color)] border border-[var(--primary-color)] text-white font-medium px-4 py-1 rounded-md cursor-pointer transition-all duration-300 shadow-sm hover:bg-[#cdcdcd] hover:text-gray-700 hover:shadow-lg active:scale-95"
            >
            Seleccionar archivo
            </label>
            <input id="fileInput" type="file" @change="handleFileUpload" style="display:none;" />
            <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 230px;">
                <template #prefix>
                    <search-outlined />
                </template>
            </a-input>

        </div>
    </form> 


    <a-modal v-model:open="verParticipantes" title="Participantes del proceso" @ok="handleOk" width="94%">
        <a-table 
            :dataSource="excelData" 
            centerd
            :scroll="{ y: 'calc(100vh - 300px)' }"
            :columns="columns" size="small" :pagination="false">
            <template #bodyCell="{ column, index, record }">
                <template v-if="column.dataIndex === 'nro'">
                    <span>{{ index + 1 }}</span>
                </template>
            </template>     
        </a-table> 
        <template #footer>
            <div style="text-align: right;">
                <a-button style="height:38px; border-radius: 5px; border:none; color:white; background: var(--primary-color)" @click="subirResultados"> Subir</a-button>           
            </div>
        </template>
    </a-modal>


    <div class="flex justify-end mt-2 mb-4" style="margin-right: -20px;">

    </div>
    <div style="margin-right: -20px;">
        <a-table :dataSource="participantes" :columns="columns" :scroll="{ y: 'calc(100vh - 310px)' }" size="small" :pagination="false">
            <template #bodyCell="{ column, index, record }">
                <template v-if="column.dataIndex === 'nro'">
                    <span>{{ index + 1 }}</span>
                </template>
                <template v-if="column.dataIndex === 'id_ide'">
                    <div style="scale: 0.8rem;">
                        <a-tag v-if="record.id_ide == 0" color="red" class="small-text" style="border-radius: 14px;" > sin ide </a-tag>
                        <a-tag v-if="record.area == null" color="purple"> sin area </a-tag>
                        <span v-else> </span>
                    </div>
                </template>
            </template>     
        </a-table> 
    </div>
</div>
</template>
    
<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/LayoutCalificador.vue'
import { defineProps,computed, ref, watch } from 'vue';
import * as XLSX from 'xlsx';
import { PrinterOutlined, UploadOutlined, SearchOutlined } from '@ant-design/icons-vue';
import axios from 'axios';
import { message } from 'ant-design-vue';

const verParticipantes = ref(false);
const props = defineProps(['proceso']);
const excelData = ref([]);
const progress = ref(0);
const estado = ref("");
const buscar = ref("");

const subirResultados = async () => {
  progress.value = 0;
  try {
    const response = await axios.post(
      'subir-participantes-simulacro',
      { data: excelData.value, proceso: props.proceso },
      {
        onUploadProgress: (progressEvent) => {
          if (progressEvent.lengthComputable) {
            progress.value = Math.round((progressEvent.loaded / progressEvent.total) * 100);
          }
        },
      }
    );

    progress.value = 100;
    message.success(response.data.message || 'Participantes subidos correctamente');
    verParticipantes.value = false;
    await getParticipantes();

  } catch (error) {
    progress.value = 70;
    estado.value = 'exception';
    console.error('Error:', error);

    // Mostrar mensaje de error
    const msg = error.response?.data?.error || 'Error al subir los participantes';
    message.error(msg);
  }
};


const handleFileUpload = (event) => {
    const file = event.target.files[0];
    const reader = new FileReader();

    reader.onload = (e) => {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const firstSheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheetName];
        const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

        const headers = jsonData[0]; 

        const arrayOfObjects = jsonData.slice(1).map((row) => {
        const obj = {};
        headers.forEach((header, index) => {
            obj[header] = row[index];
        });
        return obj;
        });

        verParticipantes.value = true;  
        excelData.value = arrayOfObjects;
    };

    reader.readAsArrayBuffer(file);
}
    
const participantes = ref([])

const getParticipantes = async () => {
    axios.post("/get-participantes-externo",{"term": buscar.value, "proceso": props.proceso})
    .then((response) => {
        participantes.value = response.data.datos.data;
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
        getParticipantes() 
    }, 300);    
})

getParticipantes();

const columns = ref([
    { title: 'N°', dataIndex: 'nro', align:'center', width:50 },
    { title: 'DNI', dataIndex: 'dni', width:90 },
    { title: 'Ap. Paterno', dataIndex: 'paterno' },
    { title: 'Ap. Materno', dataIndex: 'materno'},
    { title: 'Nombres', dataIndex: 'nombres' },
    { title: 'Cod puesto', dataIndex: 'cod_puesto', width:90, align:'center' },
    { title: 'Puesto', dataIndex: 'puesto' },
    { title: 'Unidad de organizacion', dataIndex: 'unidad' },    
]);

</script>

<style scoped>

input[type=file]::file-selector-button {
    margin-right: 10px;
    border: none;
    background: var(--primary-color);
    padding: 9px 20px;
    border-radius: 5px;
    color: #fff;
    cursor: pointer;
    transition: background .2s ease-in-out;
}

input[type=file]::file-selector-button:hover { background: #143253; }

.custom-file-input span {
  display: inline-block;
}
</style>