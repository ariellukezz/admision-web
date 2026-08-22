<template>
<Head title="Apoderado"/>
<AuthenticatedLayout>
<div class="m-4">
    <div class="mb-4">
        <div class="flex justify-end">
            <a-button style="display:none;" type="primary" @click="showModal()">Abrir</a-button>
            <a-button :loading="exportandoExcel" @click="exportarExcel" style="margin-right: 8px;">
                <template #icon><FileExcelOutlined /></template>
                Exportar Excel
            </a-button>
            <a-input placeholder="Buscar" v-model:value="buscar"  style="max-width: 300px;">
                <template #suffix>
                <search-outlined />
                </template>
            </a-input>
        </div>
    </div>

    <div>
    <a-table :columns="columns" :data-source="apoderados" :pagination="false" size="small">
    <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'tipo_apoderado'">
            <div>
                <div v-if="1 == record.tipo_apoderado">
                    <a-tag color="blue">PADRE</a-tag>
                </div>
                <div v-if="record.tipo_apoderado == 2">
                    <a-tag color="pink">MADRE</a-tag>
                </div>
                <div v-if="record.tipo_apoderado == 3">
                    <a-tag color="orange">TUTOR</a-tag>
                </div>
            </div>
        </template>

        <template v-if="column.dataIndex === 'nombres'">
            <span style="text-transform: uppercase;">
            {{ record.nombres }} {{ record.paterno }} {{ record.materno }}
            </span>
        </template>
        <template v-if="column.dataIndex === 'postulante'">
            <span style="text-transform: uppercase;">
            {{record.dni_postulante}} - {{ record.postulante }} {{ record.primer_apellido }} {{ record.segundo_apellido }}
            </span>
        </template>

        <template v-else-if="column.dataIndex === 'action'">
            <a-button type="primary" @click="abrirModal(record)" size="small">
                <template #icon><form-outlined/></template>
            </a-button>
            <a-divider type="vertical" />
            <a-button type="danger" shape="" size="small">
                <template #icon><delete-outlined/></template>
            </a-button>
        </template>
    </template>
    </a-table>

    <div class="flex justify-end mt-1">
        <a-pagination v-model:current="page" :total="total" v-model:pageSize="items_page" show-less-items />
        <div style="scale: .9;">
            <a-select v-model:value="items_page" placeholder="Seleccione" style="font-size: 0.6rem; width: 100px;">
                <a-select-option :value="1">1/Pag</a-select-option>
                <a-select-option :value="10">10/Pag</a-select-option>
                <a-select-option :value="20">20/Pag</a-select-option>
                <a-select-option :value="50">50/Pag</a-select-option>
                <a-select-option :value="100">100/Pag</a-select-option>
            </a-select>
            </div>
        </div>
    </div>


      <a-modal v-model:visible="modal" class="card-size" style="margin-top: -30px; ">
        <template #title>
          <div class="custom-title">
            <h3 style="font-size: 1.2rem; margin-top: 5px; font-weight: 600;">Apoderado Nuevo</h3>
          </div>
        </template>
        <a-form
          ref="formRef"
          name="apoderadoForm"
          :model="formState"
          :rules="rules"
          :layout="layout"
          class=""
          @finish="handleFinish"
          @finishFailed="handleFinishFailed"

        >
        <div class="custom-form">
            <a-row :gutter="[0, 0]">
            <a-col :span="8" class="ant-col-xs-24 col-item">
              <a-form-item label="Tipo de Documento" name="tipo_doc">
                <a-select v-model:value="formState.tipo_doc" placeholder="Seleccione" style="min-width: 210px;">
                  <a-select-option :value="1">DNI</a-select-option>
                  <a-select-option :value="2">Carnet Ext.</a-select-option>
                </a-select>
              </a-form-item>
            </a-col>
            <a-col :span="8" class="ant-col-xs-24 col-item">
              <a-form-item label="Nro. Documento" name="nro_documento">
                <a-input v-model:value="formState.nro_documento" />
              </a-form-item>
            </a-col>
            <a-col :span="8" class="ant-col-xs-24 col-item">
              <a-form-item label="Apellido Paterno" name="paterno">
                <a-input v-model:value="formState.paterno" />
              </a-form-item>
            </a-col>
          </a-row>
          <a-row :gutter="[0, 0]">
            <a-col :span="8" class="ant-col-xs-24 col-item">
                <a-form-item label="Tipo de Apoderado" name="tipo_apoderado">
                    <a-select v-model:value="formState.tipo_apoderado" placeholder="Seleccione">
                        <a-select-option :value="1">Padre</a-select-option>
                        <a-select-option :value="2">Madre</a-select-option>
                        <a-select-option :value="3">Tutor</a-select-option>
                    </a-select>
                </a-form-item>  
            </a-col>
            <a-col :span="8" class="ant-col-xs-24 col-item">
              <a-form-item label="Nombres" name="nombres">
                <a-input v-model:value="formState.nombres" />
              </a-form-item>
            </a-col>
            <a-col :span="8" class="ant-col-xs-24 col-item">
                <a-form-item label="Apellido Materno" name="materno">
                    <a-input v-model:value="formState.materno" />
                </a-form-item>
            </a-col>
          </a-row>
        </div>

        <div style="padding: 0px 10px;">
          <a-row :gutter="[20, 16]">
            <a-col :span="20" class="ant-col-xs-24 col-item">
                <a-form-item name="observacion">
                    <label>Observación:</label>
                    <a-textarea v-model:value="formState.observacion" />
                </a-form-item>
            </a-col>
          </a-row>
        </div>
        </a-form>
        <template #footer>
            <a-form-item :wrapper-col="{ span: 20, offset: 4 }">
                <div class="flex justify-end">
                <a-button type="primary" @click="submitForm()">Guardar</a-button>
                <a-button style="margin-left: 10px" @click="resetForm">Reset</a-button>
                </div>
            </a-form-item>
        </template>
      </a-modal>
    </div>
</AuthenticatedLayout>
</template>
  
<script setup>
import { ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { FormOutlined, DeleteOutlined, SearchOutlined, FileExcelOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';


const page = ref(1)
const total = ref(0)
const items_page = ref(10)

const buscar = ref("")
const apoderados = ref([])
const formRef = ref(null)
const formState = ref({
    id:null,
    tipo_doc: 1,
    nro_documento: '',
    paterno: '',
    materno: null,
    nombres: '',
    tipo_apoderado: 1,
    observacion: null,
});
  
  const rules = {
    tipo_doc: [
      { required: true, message: 'Seleccione el tipo de documento', trigger: 'change' },
    ],
    nro_documento: [
      { required: true, message: 'Ingrese el número de documento', trigger: 'blur' },
    ],
    paterno: [
      { required: true, message: 'Ingrese el apellido paterno', trigger: 'blur' },
    ],
    materno: [
      { required: true, message: 'Ingrese el apellido materno', trigger: 'blur' },
    ],
    nombres: [
      { required: true, message: 'Ingrese los nombres', trigger: 'blur' },
    ],
    tipo_apoderado: [
      { required: true, message: 'Seleccione el tipo de apoderado', trigger: 'change' },
    ],
  };
  
  const layout = {
    labelCol: { span: 24, backgroundColor:'red' },
    wrapperCol: { span: 20 },
  };
  
  const handleFinish = (values) => {
    console.log('Form submitted:', values);
  };
  
  const handleFinishFailed = (errorInfo) => {
    console.log('Form validation failed:', errorInfo);
  };
  
  const resetForm = () => {
    formRef.value.resetFields();
  };

  const submitForm = () => {
    axios
    .post('/admin/save-apoderados-admin', formState.value )
    .then(response => {
        // Procesar la respuesta del servidor en caso de éxito
        getApoderados();
        modal.value = false;
        notificacion(response.data.tipo,response.data.titulo, response.data.mensaje);
    })
    .catch(error => {
        // Manejar el error en caso de fallo
        console.error(error);
    });
  };


const getApoderados = () => {
    axios
    .post('/admin/get-apoderados-admin?page='+page.value, { pages: items_page.value, term: buscar.value})
    .then(response => {
        apoderados.value = response.data.datos.data;
        page.value = response.data.datos.current_page;
        total.value = response.data.datos.total;
        console.log(response.data);
    })
    .catch(error => {
        console.error(error);
    });

};

getApoderados()

const exportandoExcel = ref(false);

const exportarExcel = () => {
    exportandoExcel.value = true;
    const params = new URLSearchParams();
    if (buscar.value) params.append('term', buscar.value);
    window.location.href = '/admin/apoderados/exportar-excel?' + params.toString();
    setTimeout(() => {
        exportandoExcel.value = false;
    }, 2000);
};

const modal = ref(false);
const showModal = () => {
    modal.value = true;
};
const handleOk = e => {
    modal.value = false;
};

const abrirModal = (item) => {
    formState.value.id = item.id,    
    formState.value.tipo_doc= 1,
    formState.value.nro_documento= item.dni,
    formState.value.paterno= item.paterno,
    formState.value.materno= item.materno,
    formState.value.nombres= item.nombres,
    formState.value.tipo_apoderado= item.tipo_apoderado,
    formState.value.observacion= item.observacion,
    modal.value = true;

};

watch(buscar,(nuevo, antiguo)=>{
    getApoderados()
})
watch(page,(nuevo, antiguo)=>{
    getApoderados()
})
watch(items_page,(nuevo, antiguo)=>{
    getApoderados()
})

const columns = [
    { title: 'Tipo', dataIndex: 'tipo_apoderado', width:'70px', align:'center' },
    { title: 'DNI', dataIndex: 'dni'}, 
    { title: 'Apoderado', dataIndex: 'nombres'}, 
    { title: 'Postulante', dataIndex: 'postulante'}, 
    { title: 'Action', dataIndex: 'action' }
];

const notificacion = (type, titulo, mensaje) => {
    notification[type]({
    message: titulo,
    description: mensaje,
    });
};

 
</script>
<style scoped>
.custom-title {
  padding: 0px;
  justify-content: end;
}

.col-item{
    max-height: 80px;
}
.custom-form {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  grid-gap: 30px 20px;
  padding:0px 10px;
}
.card-size{
    width: 80%; 
    max-width: 580px;
}


@media (max-width: 768px) {
  .custom-form {
    scale: .9;
    grid-template-columns: repeat(2, 1fr);
    grid-gap: 20px;
    padding: 0px;
  }
  .card-size{
    scale: 0.9rem;
    width:95%;
  }
}

.flex {
  display: flex;
}

.justify-end {
  justify-content: flex-end;
}
</style>