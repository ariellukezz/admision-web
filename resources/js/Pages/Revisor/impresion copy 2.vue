<template>
<Head title="Inscripciones-impresión"/>
<AuthenticatedLayout>
<div style="">
<a-tabs v-model:activeKey="tabactive" type="card">

  {{ dniseleccionado }}
  <a-tab-pane key="1" tab="Inscripcion" >
    <div style="padding: 0px 15px; background: white; border-radius: 10px; margin-top:-10px;">
      <div class="flex justify-end pt-6" style="background:white;">
        <div>
          <label style="margin-right: 10px;"> Buscar:</label>
            <a-auto-complete
            v-model:value="dniseleccionado"
            :options="postulantes"
            style="width: 300px"
            @select="onSelect"
            @search="onSearch"
          >
          <a-input
            placeholder="Buscar"
            v-model:value="dni"
          />
          <template #option="{ value: val, label:lab }" style="background-color: blue;">
            <div style="height: 34px;">
              <div><span style="font-weight: 700; color: black; font-size: .7rem;">{{ val }}</span></div>
              <div style="margin-top: -10px;"><span style="font-size: .8rem; text-transform: uppercase;">{{ lab }}</span></div>
            </div>
          </template>
          </a-auto-complete>
        </div>
      </div>
      <div class="mb-4" style="margin-top: 0px;"><span style="font-size:1rem; font-weight:bold;"> Comprobantes de pago </span></div>
      <div class="mb-6">
        <div>
          <Vouchers :dni="dni"/>
        </div>
      </div>

      <div class="mb-4" style="margin-top: 0px;"><span style="font-size:1rem; font-weight:bold;"> Documentos </span></div>
      <div class="mb-6">
        <div>
          <a-table :dataSource="documentos" :columns="colDocumentos" size="small" :pagination="false">
            <template #bodyCell="{ column, index, record}">
              <template v-if="column.dataIndex === 'verificado'">
                <div v-if="record.verificado == 1" type="primary" ghost>
                  <a-tag color="green"> verificado </a-tag>            
                </div>
                <div v-else type="danger" ghost>
                  <a-tag color="pink"> No verificado </a-tag>            
                </div>
              </template>

              <template v-if="column.dataIndex === 'acciones'">
                <a-button type="primary" disabled @click="abrirEditar(filiales[index])" size="small">
                <template #icon><form-outlined/></template>
                </a-button>
                <a-divider type="vertical" />
                  <a :href="'../../'+record.url" target="_blank">
                    <a-button type="primary"  @click="eliminar(filiales[index])" shape="" size="small">
                        <template #icon><eye-outlined/></template>
                    </a-button>
                  </a>  
              </template>
            </template>    
          </a-table>
        </div>
      </div>


      <div class="mb-4" style="margin-top: 0px;"><span style="font-size:1rem; font-weight:bold;"> Preinscripción </span></div>
      <div class="mb-6">
        <div>
          <a-table :dataSource="preinscripciones" :columns="colPreinscripciones" size="small" :pagination="false">
            <template #bodyCell="{ column, index, record}">
              <template v-if="column.dataIndex === 'estado'">
                  <div v-if="record.estado == 1" type="primary" ghost>
                    <a-tag color="green"> Disponible </a-tag>            
                  </div>
                  <div v-else type="danger" ghost>
                    <a-tag color="pink"> Bloqueado </a-tag>            
                  </div>
              </template>

              <template v-if="column.dataIndex === 'acciones'">
                <a-button type="primary" disabled @click="abrirEditar(filiales[index])" size="small">
                  <template #icon><eye-outlined/></template>
                </a-button>
              </template>

            </template>
          </a-table>
        </div>
      </div>


      <div class="mb-4" style="margin-top: 0px;"><span style="font-size:1rem; font-weight:bold;"> Inscripciones </span></div>
      <div class="mb-6">
        <div>
          <a-table :dataSource="inscripciones" :columns="colPreinscripciones" size="small" :pagination="false">
            <template #bodyCell="{ column, index, record}">
              <template v-if="column.dataIndex === 'estado'">
                  <div v-if="record.estado == 1" type="primary" ghost>
                    <a-tag color="green"> Disponible </a-tag>            
                  </div>
                  <div v-else type="danger" ghost>
                    <a-tag color="pink"> Bloqueado </a-tag>            
                  </div>
              </template>

              <template v-if="column.dataIndex === 'acciones'">
                <a-button type="primary" disabled @click="abrirEditar(filiales[index])" size="small">
                  <template #icon><eye-outlined/></template>
                </a-button>
              </template>
            </template>
          </a-table>
        </div>
      </div>

    




      <div style=" width:100%; border: solid 1px #f3f3f3; margin: 20px 0px">
        <div class="container-principal">
          <a-col flex="1 1">
            <div class="container-postulante">
                  <div class="mr-3 container-imagen">
                    <img v-if="postulante.primer_apellido !== ''" :src="baseUrl+'/documentos/7/inscripciones/fotos/'+postulante.dni_temp+'.jpg'"/> 
                    <img v-else :src="baseUrl+'/fotos/postulantex.jpg'"/>
                  </div>
                  <!-- {{ postulante }} -->
                  <div class="datos-postulante"> 
                    <div class="mb-2">
                      <label>Primer apellido</label>
                      <a-input v-model:value="postulante.primer_apellido" placeholder="Primer apellido" />
                    </div>

                    <div class="mb-2">
                      <label>Segundo apellido</label>
                      <a-input v-model:value="postulante.segundo_apellido" placeholder="Segundo apellido" />
                    </div>

                    <div class="mb-2">
                      <label>Prenombres</label>
                      <a-input v-model:value="postulante.nombres" placeholder="Prenombres" />
                    </div>

                    <div class="flex">
                      <div class="mr-3" style="width:100%">
                        <label>Fecha Nac</label>
                        <div style="width: 100%;">
                          <a-date-picker style="width: 100%;" v-model:value="postulante.fec_nacimiento" format='DD/MM/YYYY'/>
                        </div>
                      </div>
                      <div style="width: 200px;">
                        <label>Sexo</label>
                        <a-select
                          ref="select"
                          v-model:value="postulante.sexo"
                          value="value"
                          label="label"
                          style="width: 100%"
                        >
                          <a-select-option value="1">Masculino</a-select-option>
                          <a-select-option value="2">Femenino</a-select-option>
                        </a-select>
                      </div>
                    </div>
                  </div>

            </div>

            <div>
                <div class="container-postulante mb-2">
                  <div style="width: 100%;">
                    <label>Colegio</label>
                     <a-input v-model:value="postulante.colegio" disabled placeholder="Colegio" />
                  </div>
                </div>
                <div class="container-postulante mb-2">
                  <div style="width: 100%;">
                    <label>Procedencia</label>
                     <a-input v-model:value="postulante.procedencia" disabled placeholder="Procedencia" />
                  </div>
                </div>
                <div class="container-postulante">
                  <div style="width: 100%;">
                    <label>Proceso</label>
                     <a-input v-model:value="postulante.proceso" disabled placeholder="Proceso" />
                  </div>
                </div>
            </div>
            
          </a-col>
          <a-col flex="0 1 400px">
            <div>
                <div class="mb-2">
                  <label>Modalidad</label>
                  <a-select
                    ref="select"
                    v-model:value="postulante.modalidad"
                    style="width: 100%"
                    disabled
                  >
                    <a-select-option :value="7">PERSONAS CON DISCAPACIDAD</a-select-option>
                    <a-select-option :value="8">EXAMEN GENERAL</a-select-option>
                    <a-select-option :value="9">CEPREUNA</a-select-option>
                  </a-select>
                </div>
                <div>
                  <label>Programa de estudios</label>
                  <a-select
                    ref="select"
                    v-model:value="postulante.programa"
                    style="width: 100%"
                    :disabled="true"
                  >
                    <a-select-option :value="1">Administración</a-select-option>
                    <a-select-option :value="2">Antropología</a-select-option>
                    <a-select-option :value="3">Arquitectura y Urbanismo</a-select-option>
                    <a-select-option :value="4">Arte: Artes plásticas</a-select-option>
                    <a-select-option :value="5">Arte: Danza</a-select-option>
                    <a-select-option :value="6">Arte: Musica</a-select-option>
                    <a-select-option :value="7">Biología: Ecología</a-select-option>
                    <a-select-option :value="8">Biología: Microbiología y laboratorio clínico</a-select-option>
                    <a-select-option :value="9">Biología: Pesquería</a-select-option>
                    <a-select-option :value="1">Ciencias contables</a-select-option>
                    <a-select-option :value="2">Ciencias de la comunicación</a-select-option>
                    <a-select-option :value="3">Ciencias físisco matemáticas: Física</a-select-option>
                    <a-select-option :value="4">Ciencias físisco matemáticas: Matemática</a-select-option>
                    <a-select-option :value="5">Derecho</a-select-option>
                    <a-select-option :value="6">Educación física</a-select-option>
                    <a-select-option :value="7">Educación Inicial</a-select-option>
                    <a-select-option :value="8">Educación primaria</a-select-option>
                    <a-select-option :value="9">Educ. Sec. de la espeialidad de ciencia, tecnología y ambiente</a-select-option>
                    <a-select-option :value="9">Educ. Sec. de la espeialidad de Ciencias Sociales</a-select-option>
                    <a-select-option :value="9">Educ. Sec. de la espeialidad de Lengua, Literatura, psicología y filosofía</a-select-option>
                    <a-select-option :value="9">Educ. Sec. de la espeialidad de Matemática, física, computación e informática</a-select-option>
                    <a-select-option :value="1">Enfermería</a-select-option>
                    <a-select-option :value="2">Ingeniería Agrícola</a-select-option>
                    <a-select-option :value="3">Ingeniería Agroindustrial</a-select-option>
                    <a-select-option :value="4">Ingeniería Civil</a-select-option>
                    <a-select-option :value="5">Ingeniería de Minas</a-select-option>
                    <a-select-option :value="6">Ingeniería de Sistemas</a-select-option>
                    <a-select-option :value="7">Ingeniería Económica</a-select-option>
                    <a-select-option :value="8">Ingeniería Electrónica</a-select-option>
                    <a-select-option :value="9">Ingeniería Estadística e informática</a-select-option>
                    <a-select-option :value="1">Ingeniería Geológica</a-select-option>
                    <a-select-option :value="2">Ingeniería Mecánica eléctrica</a-select-option>
                    <a-select-option :value="3">Ingeniería Metalúrgica</a-select-option>
                    <a-select-option :value="4">Ingeniería Química</a-select-option>
                    <a-select-option :value="5">Ingeniería Topográfica y Agrimensura</a-select-option>
                    <a-select-option :value="6">Medicina Humana</a-select-option>
                    <a-select-option :value="7">Medicina Veterinaria y zootecnia</a-select-option>
                    <a-select-option :value="8">Nutrición Humana</a-select-option>
                    <a-select-option :value="9">Odontología</a-select-option>
                    <a-select-option :value="1">Sociología</a-select-option>
                    <a-select-option :value="2">Trabajo Social</a-select-option>
                    <a-select-option :value="3">Turismo</a-select-option>
                  </a-select>
                </div>
            </div>
            <!-- {{ baseUrl+'/huellas/inscripcion/'+dniseleccionado+'.jpg' }} -->
            <div class="mt-4 container-huellas">
              <div class="mr-1" style="border: solid 1px #F4f4f4; width: 100%; height: 100%; overflow-y: hidden;">
                <img v-if="postulante.primer_apellido !== ''" :src="baseUrl+'/documentos/7/inscripciones/huellas/'+postulante.dni_temp+'.jpg'"/>
                <img v-else :src="baseUrl+'/huellas/huella.jpg'"/>
              </div>
              <div class="mr-1" style="border: solid 1px #F4f4f4; width: 100%; height: 100%;">
                <img v-if="postulante.primer_apellido !== ''" :src="baseUrl+'/documentos/7/inscripciones/huellas/'+postulante.dni_temp+'x.jpg'"/>
                <img v-else :src="baseUrl+'/huellas/huella.jpg'"/>
              </div>
            </div>
          </a-col>

        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; margin-top:-10px;">
        <div style="margin-right: 5px;"> 
          <a-button type="" @click="actualizarPostulante">Actualizar Datos</a-button>          
        </div>
        <div>
          <div v-if="botomm == false">
            <a-button type="primary" @click="Inscribir">Inscribir</a-button>
          </div>

          <div v-else>
            <a-button type="primary" disabled @click="Inscribir">Inscribir</a-button>
          </div>
        </div>


      </div>

    </div>
  </a-tab-pane>
  <a-tab-pane key="2" tab="Apoderados">
    <a-table :dataSource="apoderados" :columns="colApoderados">
      <template #bodyCell="{ column, index, record }">
        <template v-if="column.dataIndex === 'parentesco'">
          <div v-if="record.parentesco == 'Padre'">
            <a-tag color="blue">Padre</a-tag>            
          </div>
          <div v-if="record.parentesco == 'Madre'">
            <a-tag color="pink">Madre</a-tag>            
          </div>
        </template>
        <template v-if="column.dataIndex === 'acciones'">
          <a-button type="primary" disabled @click="abrirEditar(filiales[index])" size="small">
          <template #icon><form-outlined/></template>
          </a-button>
          <a-divider type="vertical" />
          <a-button type="danger" disabled @click="eliminar(filiales[index])" shape="" size="small">
          <template #icon><delete-outlined/></template>
          </a-button>
        </template>

      </template>    
    
    </a-table>
  </a-tab-pane>

  <a-tab-pane key="7" tab="Constancia">
      <div v-if="dniseleccionado" style="width:100%">            
        <iframe :src="baseUrl+'/documentos/7/inscripciones/constancias/'+dniseleccionado+'.pdf'" width="100%" style="height:calc(100vh - 140px)"   scrolling="yes" frameborder="1" ></iframe>
      </div>
    </a-tab-pane>
</a-tabs>


</div>
</AuthenticatedLayout>

</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue'
import {ref, watch} from 'vue'
import { ExclamationCircleOutlined, FormOutlined, DeleteOutlined, EyeOutlined } from '@ant-design/icons-vue';
import dayjs from 'dayjs';
import { format } from 'date-fns';
import { notification } from 'ant-design-vue';
import Vouchers from './components/voucherBN.vue'
const baseUrl = window.location.origin;
 
const props = defineProps({  baseUrl: String });

const botomm = ref(false);
const dni = ref("")
const tabactive = ref('1')

const dniseleccionado = ref(null)
const preinscripciones = ref(null)
const inscripciones = ref(null)

const postulantes = ref([]) 
const postulante = ref({ 
  id:"",
  nombres:"", 
  primer_apellido:"",
  segundo_apellido:"", 
  sexo: 'Masculino', 
  fec_nacimiento:"",
  colegio: "",
  procedencia: "",
  proceso: "",
  id_proceso:"",
  cod_programa:"",
  modalidad: "",
  id_modalidad:"",
  programa:"",
  id_programa:"",
  dni_temp:""
})

const apoderados = ref([])
const documentos = ref ([])

const onSelect = () => { console.log('onSelect', dniseleccionado); };

const getPostulantes =  async (term = "", page = 1) => {
  let res = await axios.post( "get-postulantes?page=" + page, { term: dni.value });
  postulantes.value = res.data.datos.data;
}

const getPostulantesByDni =  async () => {
  let res = await axios.get("get-postulante-dni/" + dniseleccionado.value);
    postulante.value.id = res.data.datos.id_postulante;    
    postulante.value.nombres = res.data.datos.nombres;
    postulante.value.primer_apellido = res.data.datos.primer_apellido;
    postulante.value.segundo_apellido = res.data.datos.segundo_apellido;
    postulante.value.sexo = res.data.datos.sexo;
    postulante.value.fec_nacimiento = dayjs(res.data.datos.fec_nacimiento)
    postulante.value.colegio = res.data.datos.colegio;
    postulante.value.procedencia = res.data.datos.departamento +' / '+res.data.datos.provincia + ' / '+ res.data.datos.distrito;
    postulante.value.proceso = res.data.datos.proceso;
    postulante.value.modalidad = res.data.datos.modalidad;
    postulante.value.programa = res.data.datos.programa;
    postulante.value.cod_programa = res.data.datos.cod_programa;
    postulante.value.id_programa = res.data.datos.id_programa;
    postulante.value.id_proceso = res.data.datos.id_proceso;
    postulante.value.id_modalidad = res.data.datos.id_modalidad;
    postulante.value.dni_temp = res.data.datos.dni;
    botomm.value = buscarPostulante(res.data.datos.id_postulante, postulante.value.id_proceso);
}

const getApoderados =  async () => {
  let res = await axios.get( "get-apoderados-postulante/" + dniseleccionado.value);
  apoderados.value = res.data.datos;
}

const getDocumentos =  async () => {
  let res = await axios.get( "get-documentos-postulante/" + dniseleccionado.value);
  documentos.value = res.data.datos;
}

const getPreinscripciones =  async () => {
  let res = await axios.get("get-preinscripciones-postulante/" + dniseleccionado.value);
  preinscripciones.value = res.data.datos;
}

const getInscripciones =  async () => {
  let res = await axios.get("get-inscripciones-postulante/" + dniseleccionado.value);
  inscripciones.value = res.data.datos;
}


const Inscribir =  async () => {
  let res = await axios.post( "inscribir", { postulante: postulante.value });
  imprimirPDF(dniseleccionado.value)
  dniseleccionado.value = "";
  dni.value = "";

  postulante.value = { 
    id:"",
    nombres:"", 
    primer_apellido:"",
    segundo_apellido:"", 
    sexo:'Masculino', 
    fec_nacimiento:"",
    colegio: "",
    procedencia: "",
    proceso: "",
    id_proceso:"",
    modalidad: "",
    id_modalidad:"",
    programa:"",
    id_programa:"",
    dni_temp:""
  }
}


let timeout2;
watch(dni, ( newValue, oldValue ) => {
  clearTimeout(timeout2);
    timeout2 = setTimeout(() => {
      getPostulantes();
    }, 500);
})

let timeout;
watch(dniseleccionado, ( newValue, oldValue ) => {
  clearTimeout(timeout);
    timeout = setTimeout(() => {
      getPreinscripciones()
      getInscripciones()
      getPostulantesByDni()
      getDocumentos()
    }, 500);
})

watch(tabactive, ( newValue, oldValue ) => {
  if (tabactive.value == 2){
    getApoderados()
  }
})

const imprimirPDF =  (dnni) => {
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = baseUrl+'/documentos/7/inscripciones/constancias/'+dnni+'.pdf';
    document.body.appendChild(iframe);
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
}

const buscarPostulante = (postul, proc ) => {
  const postulanteEnProceso = inscripciones.value.find(item => {
    return item.id_postulante === postul && item.id_proceso === proc;
  });

  if (postulanteEnProceso) {
    return true;
  } else {
    return false;
  }
};

const notificacion = (type, titulo, mensaje) => { notification[type]({ message: titulo, description: mensaje, });};

const actualizarPostulante =  async () => {
  let res = await axios.post( "actualizar-postulante", {
    id: postulante.value.id,
    nombres: postulante.value.nombres,
    primer_apellido: postulante.value.primer_apellido,
    segundo_apellido: postulante.value.segundo_apellido,
    fec_nacimiento: format(new Date(postulante.value.fec_nacimiento), 'yyyy-MM-dd'),
    sexo: postulante.value.sexo
  });

  if(res.data.estado === true ){  
    notificacion(res.data.tipo, res.data.titulo, res.data.mensaje)
  }
}

const colApoderados = [
  { title: 'DNI', dataIndex: 'nro_documento', key: 'nro_documento',},
  { title: 'Nombres', dataIndex: 'nombres', key: 'nombres',},
  { title: 'Paterno', dataIndex: 'paterno', key: 'paterno',},
  { title: 'Materno', dataIndex: 'materno', key: 'materno', },
  { title: 'Parentesco', dataIndex: 'parentesco' },
  { title: 'Acciones', dataIndex: 'acciones'}  
]

const colDocumentos =  [
  { title: 'Codigo', dataIndex: 'codigo', key: 'codigo',},
  { title: 'Documento', dataIndex: 'nombre', key: 'nombre',},
  { title: 'Tipo', dataIndex: 'tipo', key: 'tipo',},
  { title: 'estado', dataIndex: 'verificado'},
  { title: 'Acciones', dataIndex: 'acciones'}  
]
const colPreinscripciones =  [
  { title: 'Programa', dataIndex: 'programa', key: 'programa',},
  { title: 'Proceso', dataIndex: 'proceso', key: 'proceso',},
  { title: 'Modalidad', dataIndex: 'modalidad', key: 'modalidad', },
  { title: 'Estado', dataIndex: 'estado', key: 'estado', },
  { title: 'Ver', dataIndex: 'acciones', },
]

getPostulantes()
</script>

<style scoped>
.container-imagen{
  border: 1px solid #d9d9d954;
  height: 215px; width: 210px; 
  min-width:160px;
  overflow: hidden; 
}
.container-imagen img{
  height: 100%;
}
.container-principal{
  display: flex;
}

.datos-postulante{
  width: 100%;
  min-width: 200px;
}
.container-postulante{
    display: flex;
    margin-right: 12px;
}
.container-huellas {
  display: flex; width: 400px;
  justify-content: space-between;
  min-height: 20px;
}

@media (max-width: 380px){
  .container-imagen{
    display: flex;
    width: 100%;
    height: 340px;
  }
}

@media (max-width: 600px){
  .container-imagen{
    display: flex;
    width: 100%;
    justify-content: center;
  }
  .inputImpresion{
    display: block;
  }
  .container-postulante{
    display: block;
    margin-right: 0px;
  }

  .container-huellas img{
    width: 100%;
  }
}

@media (max-width: 1060px){
  .container-principal{
    display: block;
  }
  .container-huellas{
    width: 100%;
    height: 100%;
    justify-content: space-between;
  }
  .container-huellas img{
    width: 100%;
    transform: scale(0.7);
  }
}
</style>