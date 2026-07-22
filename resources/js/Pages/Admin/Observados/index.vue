<template>
<Head title="Observados"/>
<AuthenticatedLayout>
<div class="overflow-hidden shadow-sm sm:rounded-lg p-4" style="height: calc(100vh - 98px); background: var(--content-bg, #f1f5f9); color: var(--card-text, #1e293b);">
  <!-- {{ buscar }} -->
  <row class="flex justify-between mb-4" >
      <div class="mr-3">
          <a-button type="primary" style="border-radius: 5px; background: #476175; border:none;" @click="showModalPrograma">Agregar</a-button>
      </div>
      <div class="flex justify-between" style="position: relative;" >
      <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; padding-left: 30px;"/>
      <div class="mr-2" style="position: absolute; left: 8px; top: 3px; "><search-outlined /></div>
      </div>
  </row>

  <div class="" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border, #e2e8f0); border-radius: 8px; padding: 8px;">

      <a-table
          :columns="columnsProgramas"
          :data-source="programas"
          :pagination="false"
          size="small"
          :scroll="{ x: 380, y: 'calc(100vh - 240px)' }"
          >
          <template #bodyCell="{ column, index, record }">

              <template v-if="column.dataIndex === 'dni'">
                  <div><span style="font-size: .9rem">{{ record.dni }}</span></div>
              </template>
              <template v-if="column.dataIndex === 'nombres'">
                  <div><span style="font-size: .9rem;">{{ record.paterno }} {{ record.materno }} {{ record.nombres }}</span></div>
              </template>
              <template v-if="column.dataIndex === 'motivo'">
                  <div><span style="font-size: .9rem;">{{ record.motivo }}</span></div>
              </template>
              <template v-if="column.dataIndex === 'id_proceso'">
                  <div><span style="font-size: .9rem;">{{ record.nombre_proceso }}</span></div>
              </template>
              <template v-if="column.dataIndex === 'area'">
                  <div class="flex" style="justify-content: center;">
                      <a-tag style="font-size: .8rem;" color="cyan" v-if=" record.area == 'BIOMÉDICAS'">{{ record.area }}</a-tag>
                      <a-tag style="font-size: .8rem;" color="purple" v-if=" record.area == 'SOCIALES'">{{ record.area }}</a-tag>
                      <a-tag style="font-size: .8rem;" color="blue" v-if=" record.area == 'INGENIERÍAS'">{{ record.area }}</a-tag>
                  </div>
              </template>
              <template v-if="column.dataIndex === 'estado'">
                  <div class="flex" style="justify-content: center;">
                      <div v-if="1 == record.estado">
                          <a-tag color="green">Si</a-tag>
                      </div>
                      <div v-if="record.estado == 0">
                          <a-tag color="red">No</a-tag>
                      </div>
                  </div>
              </template>
              <template v-if="column.dataIndex === 'acciones'">
                  <a-button disabled type="" @click="verDetalle(record)" style="border-radius:4px; background: none; color: green" size="small">
                      <template #icon><eye-outlined/></template>
                  </a-button>
                  <a-button type="" @click="abrirEditar(record)" style="border-radius:4px; background: none; color: var(--card-muted, #64748b)" size="small">
                      <template #icon><form-outlined/></template>
                  </a-button>
                  <a-button class="" @click="eliminar(record)" style="border-radius:4px; background: none; color: red;" shape="" size="small">
                  <template #icon><delete-outlined/></template>
                  </a-button>
              </template>
          </template>
      </a-table>
  </div>
  <div class="flex" style="justify-content: flex-end;">
      <a-pagination v-model:current="pagina" simple page-size="50" :total="totalpaginas" />
  </div>

  </div>


  <div>
      <a-modal v-model:visible="visible" style="margin-top: -40px;">

        <a-form ref="formObservados" :model="observado" name="observado" @finish="onFinish" @finishFailed="onFinishFailed">
          <div>

            <div style="margin-bottom: 16px; margin-top: 0px;">
              <h1 style="font-size: 1.2rem;">Observados</h1>
            </div>

            <a-row :gutter="[16, 0]" class="form-row mb-0">
              <a-col :span="24" :md="12" :lg="12" :xl="12" :xxl="12">
                <a-form-item>
                  <div><label>DNI (<span style="color:red;">*</span>)</label></div>
                  <a-form-item name="dni" :rules="[{ required: true, message: 'Ingresa el DNI', trigger: 'change'}]">
                    <a-input v-model:value="observado.dni" />
                  </a-form-item>
                </a-form-item>
              </a-col>
              <a-col :span="24" :md="12" :lg="12" :xl="12" :xxl="12">
                <a-form-item>
                  <div><label>Nombres (<span style="color:red;">*</span>)</label></div>
                  <a-form-item name="nombres" :rules="[{ required: true, message: 'Ingresa los nombres', trigger: 'change'}]">
                    <a-input v-model:value="observado.nombres" />
                  </a-form-item>
                </a-form-item>
              </a-col>
            </a-row>

            <a-row :gutter="[16, 0]" class="form-row mb-0">
              <a-col :span="24" :md="12" :lg="12" :xl="12" :xxl="12">
                <a-form-item>
                  <div><label>Paterno (<span style="color:red;">*</span>)</label></div>
                  <a-form-item name="paterno" :rules="[{ required: true, message: 'Ingresa el apellido paterno', trigger: 'change'}]">
                    <a-input v-model:value="observado.paterno" />
                  </a-form-item>
                </a-form-item>
              </a-col>
              <a-col :span="24" :md="12" :lg="12" :xl="12" :xxl="12">
                <div><label>Materno (<span style="color:red;">*</span>)</label></div>
                <a-form-item name="materno" :rules="[{ required: true, message: 'Ingresa el apellido materno', trigger: 'change'}]">
                  <a-input v-model:value="observado.materno" />
                </a-form-item>
              </a-col>
            </a-row>

            <a-row :gutter="[16, 0]" class="form-row" style="margin-top: -20px;">
              <a-col :span="24" :md="12" :lg="12" :xl="8" :xxl="8">
                <a-form-item name="motivo" :rules="[{ required: true, message: 'Ingresa un motivo', trigger: 'change'}]">
                  <div><label>Motivo (<span style="color:red;">*</span>)</label></div>
                  <a-input v-model:value="observado.motivo" />
                </a-form-item>
              </a-col>

              <a-col :span="24" :md="12" :lg="12" :xl="16" :xxl="16">
                <a-form-item name="id_proceso" :rules="[{ required: true, message: 'Ingresa el ID de proceso', trigger: 'change'}]">
                  <div class="flex justify-between"><label>Proceso (<span style="color:red;">*</span>)</label></div>
                  <a-select
                    v-model:value="observado.id_proceso"
                    :options="facultades"
                    style="width: 100%"
                    @change="handleChange"
                  >
                    <template #option="{ label, value }">
                      <span>{{ label }} (ID: {{ value }})</span>
                    </template>
                  </a-select>

                </a-form-item>
              </a-col>

              <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                <a-form-item name="observacion">
                  <div><label>Observación:</label></div>
                  <a-textarea v-model:value="observado.observacion" />
                </a-form-item>
              </a-col>
            </a-row>
          </div>
        </a-form>


      <template #footer>
          <a-button style="margin-left: 10px;" @click="resetForm">Cancelar</a-button>
          <a-button type="primary" @click="guardar()">Guardar</a-button>
      </template>
      </a-modal>
  </div>

</AuthenticatedLayout>



</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { watch, computed, ref, unref, reactive } from 'vue';
import { EyeOutlined, FormOutlined, DeleteOutlined, SearchOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const buscar = ref("");
const pagina = ref(1)
const totalpaginas = ref(null)
const dep = ref("PUNO")
const facultades = ref([])

const visible = ref(false);
const buscarDep = ref("")
const programas = ref([])

const formObservados = ref();
const observado = reactive({
  id: null,
  dni:null,
  nombres: "",
  paterno: "",
  nombres:"",
  materno:"",
  motivo:"",
  observacion:"",
  id_proceso: null
});

const showModalPrograma = () => {
    visible.value = true;
    observado.id = null;
    observado.dni = null;
    observado.nombres = null;
    observado.paterno = null;
    observado.materno = null;
    observado.motivo = null;
    observado.id_proceso = null;
};

watch(buscar, ( newValue, oldValue ) => {
    getProgramas()
})


watch(buscarDep, ( newValue, oldValue ) => {
    getDepartamentos()
})

watch(pagina, ( newValue, oldValue ) => {
    getProgramas()
})



const abrirEditar = (item) => {
    visible.value = true;
    observado.id = item.id;
    observado.dni = item.dni;
    observado.nombres = item.nombres;
    observado.paterno = item.paterno;
    observado.materno = item.materno;
    observado.motivo = item.motivo;
    observado.id_proceso = item.id_proceso;
}

const getFacultades =  async ( ) => { 
  let res = await axios.get( "get-select-procesos");
  facultades.value = res.data.datos;
}

const getProgramas =  async (term = "") => {
    let res = await axios.post( "get-observados-lista?page=" + pagina.value,{ term: buscar.value });
    programas.value = res.data.datos.data;
    totalpaginas.value = res.data.datos.total;
}

const guardar = async () => {
    const values = await formObservados.value.validateFields();
    axios.post("save-observado", observado).then((result) => {
      getProgramas()
      observado.value = { id: null, dni:null, nombres: "", paterno: "", nombres:"", materno:"", motivo:"", observacion:"", id_proceso: null};
      notificacion(result.data.tipo, result.data.titulo, "");
      observado.id = null;
      observado.dni = null;
      observado.nombres = null;
      observado.paterno = null;
      observado.materno = null;
      observado.motivo = null;
      observado.id_proceso = null;
      visible.value = false;
    });
}

const eliminar = (item) => {
  axios.get("eliminar-programa/"+item.id).then((result) => {
    getProgramas();
    notificacion('warning', 'PROGRAMA ELIMINADO', result.data.mensaje );
  });
}

const columnsProgramas = [
  { title: 'Cod', dataIndex: 'dni', width:'100px', align:'center', responsive: ['md'],},
  { title: 'Nombre', dataIndex: 'nombres'},
  { title: 'Motivo', dataIndex: 'motivo', align:'center', responsive: ['md'],},
  { title: 'Proceso', dataIndex: 'id_proceso', align:'center', width:"150px", responsive: ['md'],},
  { title: 'Acciones', dataIndex: 'acciones', width:"90px", align:'center'},
];


const notificacion = (type, titulo, mensaje) => { notification[type]({ message: titulo, description: mensaje, }); };

const verDetalle = (item) => { console.log("Detalle:", item); };

getFacultades()
getProgramas()


</script>

<style >
::-webkit-scrollbar {
width: 9px;
height: 12px;
}

::-webkit-scrollbar-track {
background: #f1f1f1;
border-radius: 10px;
}

::-webkit-scrollbar-thumb {
background: #888;
border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
background: #555;
}

/* Estilo para un scroll específico */
.scroll-container {
overflow-y: auto;
scrollbar-width: thin; /* Firefox */
scrollbar-color: #888 #f1f1f1; /* Firefox */
}

/* Estilo para el scroll específico en Webkit (Chrome, Safari) */
.scroll-container::-webkit-scrollbar {
width: 12px;
height: 12px;
}

.scroll-container::-webkit-scrollbar-track {
background: #f1f1f1;
border-radius: 10px;
}

.scroll-container::-webkit-scrollbar-thumb {
background: #888;
border-radius: 10px;
}

.scroll-container::-webkit-scrollbar-thumb:hover {
background: #555;
}

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
