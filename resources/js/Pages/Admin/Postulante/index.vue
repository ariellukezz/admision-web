<template>
<Head title="Postulantes"/>
<AuthenticatedLayout>
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
<!-- {{ buscar }} -->
<row class="flex justify-between mb-4" >
    <div class="mr-0 pt-2">
        <span style="font-size: 1.2rem; font-weight: bold;">Postulantes</span>
        <!-- <a-button type="primary" @click="showModalPrograma"></a-button> -->
    </div>
    <div class="flex justify-between" style="position: relative;" >
        <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px;">
            <template #prefix> <search-outlined /> </template>
        </a-input>
    </div>
</row>

<a-table
    :columns="columnsProgramas"
    :data-source="modalidades"
    :pagination="false"
    size="small"
    >
    <template #bodyCell="{ column, index, record}">
        <template v-if="column.dataIndex === 'ver_postulante'">
            <a-button @click="goPostulante(record.nro_doc)" size="small" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: #000080a0; display: flex; align-items: center;">
                <EyeOutlined />
            </a-button>
        </template>
        <template v-if="column.dataIndex === 'nombres'">
            {{ record.nombres }} {{  record.primer_apellido }} {{ record.segundo_apellido }}
        </template>

        <template v-if="column.dataIndex === 'acciones'">

        <div style="display: flex; gap: 2px;">
            <a-button @click="abrirEditar(record)" size="small" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: #1890ff; display: flex; align-items: center;">
                <form-outlined />
            </a-button>
            <link-outlined />

            <a-popconfirm
                v-if="modalidades.length"
                title="¿Estas seguro de eliminar?"
                @confirm="eliminar(modalidades[index])"
                >
                <a-button @click="eliminar(record)" size="small" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: #ff4d4f; display: flex; align-items: center;">
                    <delete-outlined/>
                </a-button>
            </a-popconfirm>
        </div>
        </template>
    </template>
</a-table>
<a-pagination v-model:current="pagina" :total="totalRegistros" show-less-items />

</div>



<div>
    <a-modal v-model:visible="visible" title="Postulante" style="margin-top: -40px; width:100%;" :footer="false">
        <a-row>
            <a-col :span="24">
                <a-card :bordered="false">
                <a-form :model="form" :rules="formRules">
                    <a-row :gutter="16">
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Tipo Doc</label>
                        <a-form-item >
                            <a-select v-model:value="form.tipo_doc">
                                <a-select-option :value="1">DNI</a-select-option>
                                <a-select-option :value="3">Carné Ext.</a-select-option>                    <!-- Opciones del select -->
                            </a-select>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>DNI</label>
                        <a-form-item :rules="[{ required: true, message: 'Por favor ingrese el número de documento' }]">
                        <a-input v-model:value="form.nro_doc">
                            <template #prefix> <search- /> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Ap. Paterno</label>
                        <a-form-item :rules="[{ required: true, message: 'Por favor ingrese el primer apellido' }]">
                        <a-input v-model:value="form.primer_apellido">
                            <template #prefix> <search- /> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Ap. Materno</label>
                        <a-form-item >
                        <a-input v-model:value="form.segundo_apellido">
                            <template #prefix> <search-/> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Ap. Casada</label>
                        <a-form-item >
                        <a-input v-model:value="form.apellido_casada">
                            <template #prefix> <search/> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Nombres</label>
                        <a-form-item :rules="[{ required: true, message: 'Por favor ingrese los nombres' }]">
                        <a-input v-model:value="form.nombres">
                            <template #prefix> <search-/> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Sexo</label>
                        <a-form-item :rules="[{ required: true, message: 'Por favor seleccione el sexo' }]">
                        <a-select v-model:value="form.sexo">
                            <a-select-option value="1">Masculino</a-select-option>
                            <a-select-option value="2">Femenino</a-select-option>
                        </a-select>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Fec. Nacimiento</label>
                        <a-form-item :rules="[{ required: true, message: 'Por favor seleccione la fecha de nacimiento' }]">
                        <a-date-picker style="width:100%;"  v-model:value="form.fec_nacimiento" format="DD/MM/YYYY"/>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Ubigeo Nacimiento</label>
                        <a-form-item :rules="[{ required: true, message: 'Por favor ingrese el ubigeo de nacimiento' }]">
                        <a-input v-model:value="form.ubigeo_nacimiento">
                            <template #prefix> <search- /> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Ubigeo residencia</label>
                        <a-form-item :rules="[{ required: true, message: 'Por favor ingrese el ubigeo de residencia' }]">
                        <a-input v-model:value="form.ubigeo_residencia">
                            <template #prefix> <search-/> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Celular</label>
                        <a-form-item :rules="[{ required: true, message: 'Por favor ingrese el número de celular' }]">
                        <a-input v-model:value="form.celular">
                            <template #prefix> <search-/> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Correo</label>
                        <a-form-item :rules="[{ required: true, message: 'Por favor ingrese el correo electrónico' }, { type: 'email', message: 'Por favor ingrese un correo electrónico válido' }]">
                        <a-input v-model:value="form.email">
                            <template #prefix> <search-outlined /> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Estado Civil</label>
                        <a-form-item >
                        <a-select v-model:value="form.estado_civil">
                            <a-select-option value="1">Soltero</a-select-option>
                            <a-select-option value="2">Casado</a-select-option>                    <!-- Opciones del select -->
                        </a-select>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Dirección</label>
                        <a-form-item :rules="[{ required: true, message: 'Por favor ingrese la dirección' }]">
                        <a-input v-model:value="form.direccion">
                            <template #prefix> <search- /> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <a-form-item>
                        <label>Año egreso</label>
                        <a-input v-model:value="form.anio_egreso">
                            <template #prefix> <search-/> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Observaciones</label>
                    <a-form-item>
                        <a-input v-model:value="form.observaciones">
                            <template #prefix> <search-/> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12" :md="8" :lg="6">
                        <label>Colegio</label>
                        <a-form-item>
                        <a-input v-model:value="form.id_colegio">
                            <template #prefix> <search- /> </template>
                        </a-input>
                        </a-form-item>
                    </a-col>
                    </a-row>
                    <a-row>
                    <a-col>
                        <div class="flex justify-end">
                            <a-button @click="visible = false"> Cancelar </a-button>
                            <a-button type="primary" @click="saveData">Guardar</a-button>
                        </div>

                    </a-col>
                    </a-row>
                </a-form>
                </a-card>
            </a-col>
            </a-row>

    </a-modal>
</div>


</AuthenticatedLayout>
</template>


<script setup>
import { reactive, ref, onMounted, watch } from 'vue';
import { message } from 'ant-design-vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { FormOutlined, DeleteOutlined, SearchOutlined, EyeOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import dayjs from 'dayjs';
import { format } from 'date-fns';
import axios from 'axios';

const dni = ref("");

const abrirEditar = (item) => {
    visible.value = true;

    form.id = item.id;
    form.nro_doc = item.nro_doc;
    form.tipo_doc = item.tipo_doc;
    form.primer_apellido = item.primer_apellido;
    form.segundo_apellido = item.segundo_apellido;
    form.apellido_casada = item.apellido_casada;
    form.nombres = item.nombres;
    form.sexo = item.sexo;
    form.fec_nacimiento = dayjs(item.fec_nacimiento);
    form.ubigeo_nacimiento = item.ubigeo_nacimiento;
    form.ubigeo_residencia = item.ubigeo_residencia;
    form.celular = item.celular;
    form.email = item.email;
    form.estado_civil = item.estado_civil;
    form.direccion = item.direccion;
    form.anio_egreso = item.anio_egreso;
    form.observaciones = item.observaciones;
    form.id_colegio = item.id_colegio;
}

const form = reactive({
    nro_doc: '',
    tipo_doc:'',
    primer_apellido: '',
    segundo_apellido: '',
    apellido_casada: '',
    nombres: '',
    sexo: '',
    fec_nacimiento: '',
    ubigeo_nacimiento: '',
    ubigeo_residencia: '',
    discapacidad: '',
    tipo_discapacidad: '',
    celular: '',
    email: '',
    estado_civil: '',
    direccion: '',
    anio_egreso: '',
    observaciones: '',
    id_colegio: '',
});

const fetchData = () => {

};

const saveData = async () => {
    let res = await axios.post("save-postulante-admin",{
      id: form.id,
      tipo_doc: form.tipo_doc,
      nro_doc: form.nro_doc,
      primer_apellido: form.primer_apellido,
      segundo_apellido: form.segundo_apellido,
      apellido_casada: form.apellido_casada,
      nombres: form.nombres,
      sexo: form.sexo,
      fec_nacimiento: format(new Date(form.fec_nacimiento), 'yyyy-MM-dd'),
      ubigeo_nacimiento: form.ubigeo_nacimiento,
      ubigeo_residencia: form.ubigeo_residencia,
      celular: form.celular,
      correo: form.email,
      estado_civil: form.estado_civil,
      direccion: form.direccion,
      egreso: form.anio_egreso,
      observaciones: form.observaciones,
      colegio:  form.id_colegio
    }
  );
  visible.value = false;
  if(res.data.estado === true ){
    notificacion(res.data.tipo, res.data.titulo, res.data.mensaje)
  }
  getModalidades();
};

const deleteData = () => {
// Lógica para eliminar los datos del servidor (DELETE request a la API de Laravel)
// utilizando el "id" del registro que deseas eliminar
};



// Obtener los datos cuando el componente se monte
onMounted(() => {
fetchData();
});

const buscar = ref("");
const modalidades = ref([])
const modalidadestemp = ref([])
const visible = ref(false)
const pagina = ref(1)
const totalRegistros = ref(null)
const modalidad = ref({ id:null, codigo:"", nombre:""})

const showModalPrograma = () => { visible.value = true; };

watch(buscar, ( newValue, oldValue ) => { getModalidades() })

watch(visible, ( newValue, oldValue ) => {
    if(visible.value == false && modalidad.value.id != null ){
       form.value = null
    }
})

const getModalidades =  async ( ) => {
    let res = await axios.post(
    "get-postulantes-admin?page="+pagina.value ,
    { term: buscar.value }
    );
    modalidades.value = res.data.datos.data;
    totalRegistros.value = res.data.datos.total;
}

const columnsProgramas = [
    { title: 'Ver', dataIndex: 'ver_postulante' },
    { title: 'DNI', dataIndex: 'nro_doc' },
    { title: 'Nombre', dataIndex: 'nombres'},
    { title: 'Celular', dataIndex: 'celular'},
    { title: 'F. Nac', dataIndex: 'fec_nacimiento'},
    { title: 'Ubigeo', dataIndex: 'ubigeo_residencia'},
    { title: 'colegios', dataIndex: 'id_colegio'},
    { title: 'Acciones', dataIndex: 'acciones'},
];

const notificacion = (type, titulo, mensaje) => {
    notification[type]({
    message: titulo,
    description: mensaje,
    });
};


watch(pagina, ( newValue, oldValue ) => { getModalidades(); })

//get-postulante-perfil

const goPostulante = async (dni) => {
    try {
        window.location.href = 'postulante-perfil/'+dni;
    } catch (error) {  console.error(error); }
};



getModalidades()

</script>


