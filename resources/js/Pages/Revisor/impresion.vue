<template>
<Head title="Inscripciones-impresión"/>
<AuthenticatedLayout title="Revision documentos">
<div style="height: calc(100vh - 120px); overflow-y: scroll; margin-right: -10px;">
<div>  
  <div style=" width:100%; margin: 20px 0px; margin-top: 0px;">
      <div class="flex mb-4 elemento" style="position:relative; height: 130px; border-radius: 10px 10px 10px 10px; align-items: center; justify-content: center;">
          <div>
            <div style="text-align:center;">
              <div v-if="postulante.programa"><span><strong style="font-size:1.7rem;">{{ postulante.programa }}</strong></span></div>
              <div v-else><span><strong style="font-size:1.7rem;">PROGRAMA DE ESTUDIOS</strong></span></div>
              <div v-if="postulante.modalidad" style="text-align:center; margin-top:-10px;"><span style="font-size:1rem;">( {{ postulante.modalidad }} )</span></div>
              <div v-else style="text-align:center; margin-top:10px;"><span style="font-size:1rem;">( MODALIDAD )</span></div>
            </div>

  
            <div v-if="anteriores" style="text-align:center; margin-top:10px;">
              <div v-if="anteriores.length > 0" class="px-2 py-1" style="text-align:center; margin-top:10px; background: #f3f3f3;">
                <span style="color:crimson; font-size:1rem; letter-spacing:0.3rem;">POSTULANTE A SEGUNDO PROGRAMA</span>
              </div>
            </div>

            <div v-if="observados.includes(dniseleccionado) == true" class="px-2 py-1" style="text-align:center; margin-top:10px; background: #f3f3f3;">
              <span style="color:crimson; font-size:1rem; letter-spacing:0.3rem;">NO PASO CONTROL BIOMÉTRICO - REQUIERE PAGO ADICIONAL</span>
            </div>

          </div>
        </div>
      </div>
</div>


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
          <template #option="{ value: val, label:lab }">
            <div style="height: 34px;">
              <div><span style="font-weight: 700; color: black; font-size: .7rem;">{{ val }}</span></div>
              <div style="margin-top: -10px;"><span style="font-size: .8rem; text-transform: uppercase;">{{ lab }}</span></div>
            </div>
          </template>
          </a-auto-complete>
        </div>
      </div>

      <div style=" width:100%; border: solid 1px #f3f3f3; margin: 20px 0px">
        <div class="container-principal">
          <a-col flex="1 1">
            <div class="container-postulante">
                 <div class="mr-3 container-imagen">
                    <img v-if="postulante.primer_apellido !== ''" :src="baseUrl+'/'+foto_postulante"/> 
                    <img v-else :src="baseUrl+'/fotos/postulantex.jpg'"/>
                  </div>
                  <div v-if="postulante.postulante_foto !== null" class="mr-3 container-imagen">
                    <img v-if="postulante.primer_apellido !== ''" :src="postulante.postulante_foto"/> 
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
                     <a-input :class="postulante.id_gestion == 1? 'borde-azul':'borde-naranja' && postulante.id_gestion != null" v-model:value="postulante.colegio" placeholder="Colegio" />
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
                <img v-if="postulante.primer_apellido !== ''" :src="baseUrl+'/'+huellaI_postulante"/>
                <img v-else :src="baseUrl+'/huellas/huella.jpg'"/>
              </div>
              <div class="mr-1" style="border: solid 1px #F4f4f4; width: 100%; height: 100%;">
                <img v-if="postulante.primer_apellido !== ''" :src="baseUrl+'/'+huellaD_postulante"/>
                <img v-else :src="baseUrl+'/huellas/huella.jpg'"/>
              </div>
            </div>
          </a-col>

        </div>
      </div>
      
      <div class="mb-4" style="margin-top: 0px;"><span style="font-size:1rem; font-weight:bold;"> Comprobantes de pago </span></div>
      <div class="mb-6">
        <div>
          <Vouchers :dni="dniseleccionado"/>
        </div>
      </div>

      <div class="mb-4" style="margin-top: 0px;"><span style="font-size:1rem; font-weight:bold;"> Documentos</span></div>
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
                <a-button class="mr-1" type="" @click="validar(record)" size="small">
                <template #icon><CheckOutlined/></template>
                </a-button>
                <a-button class="mr-1" type="" @click="Editar(record)" size="small">
                <template #icon><form-outlined/></template>
                </a-button>
                  <!-- <a :href="'../../'+record.url" target="_blank">
                    <a-button type=""  @click="eliminar(filiales[index])" shape="" size="small">
                        <template #icon><eye-outlined/></template>
                    </a-button>
                  </a>   -->
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
                    <a-tag color="green"> Sin inscripción </a-tag>            
                  </div>
                  <div v-else type="danger" ghost>
                    <a-tag color="pink"> Inscrito </a-tag>            
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
    </div>
</div>

<div class="flex justify-end" style="margin-left: -15px; background: white; border-top: 1px solid #cdcdcd83; height: 42px; align-items: center; margin-right: -11px;">
  <div v-if="inscripciones" class="flex">  
    <div style="margin-right: 5px;"> 
      <a-button style="border: #0e4165 solid 1px; color: #0e4165;" @click="actualizarPostulante">Actualizar Datos</a-button>          
    </div>
    <div style="margin-right: 5px;"> 
      <a-button style="border: #0e4165 solid 1px; color: #0e4165;" @click="imprimirPDF(dniseleccionado)">Imprimir constancia</a-button>          
    </div>
    <div v-if="inscripciones.length == 0">
        <a-popconfirm title="¿Seguro de inscribir?" @confirm="confirm" cancelText="NO" placement="topRight" okText="SI" @cancel="cancel">
          <a-button style="background: #0e4165; color:white">Inscribir</a-button>
        </a-popconfirm>
    </div>
    <div v-else>
      <a-button type="primary" disabled>Ya Inscrito</a-button>
    </div>
  </div>

</div>

<a-modal v-model:visible="openModal" title="Editar Código" :footer="false">
  <div>Codigo de certificado</div>
  <a-input v-model:value="doc.codigo" placeholder="Ingresar Codigo"></a-input>

  <div class="flex justify-end">
    <a-button @click="CambiarCodigo" style="background: #0e4165; color:white;">Cambiar codigo</a-button>
  </div>

</a-modal>

</AuthenticatedLayout>

</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue'
import {ref, watch} from 'vue'
import { ExclamationCircleOutlined, FormOutlined, DeleteOutlined, EyeOutlined, CheckOutlined } from '@ant-design/icons-vue';
import dayjs from 'dayjs';
import { format } from 'date-fns';
import { notification } from 'ant-design-vue';
import Vouchers from './components/voucherBN.vue'
const baseUrl = window.location.origin;
const foto_postulante = ref(null);
const huellaD_postulante = ref(null);
const huellaI_postulante = ref(null);

const doc = ref({id:null, codigo: "" });
const openModal = ref(false);
const Editar  =  async ( item ) => {
  openModal.value = true;
  doc.value = item;
  console.log(item);

}
 
const props = defineProps({  baseUrl: String });

const dni = ref("")

const dniseleccionado = ref(null)
const preinscripciones = ref(null)
const inscripciones = ref(null)

const postulantes = ref([]) 
const postulante = ref({ 
  id:"",
  nombres:"",
  postulante_foto: null,
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

const documentos = ref ([])
const anteriores = ref([]);

const getPostulantes =  async (term = "", page = 1) => {
  let res = await axios.post( "get-postulantes?page=" + page, { term: dni.value });
  postulantes.value = res.data.datos.data;
}

const getPostulantesByDni =  async () => {
  
  let res = await axios.get("get-postulante-dni/" + dniseleccionado.value);
    if(res.data.datos){
      postulante.value.id = res.data.datos.id_postulante;    
      postulante.value.nombres = res.data.datos.nombres;
      postulante.value.primer_apellido = res.data.datos.primer_apellido;
      postulante.value.segundo_apellido = res.data.datos.segundo_apellido;
      postulante.value.sexo = res.data.datos.sexo;
      postulante.value.fec_nacimiento = dayjs(res.data.datos.fec_nacimiento)
      postulante.value.colegio = res.data.datos.colegio;
      postulante.value.id_gestion = res.data.datos.id_gestion;
      postulante.value.procedencia = res.data.datos.departamento +' / '+res.data.datos.provincia + ' / '+ res.data.datos.distrito;
      postulante.value.proceso = res.data.datos.proceso;
      postulante.value.modalidad = res.data.datos.modalidad;
      postulante.value.programa = res.data.datos.programa;
      postulante.value.cod_programa = res.data.datos.cod_programa;
      postulante.value.id_programa = res.data.datos.id_programa;
      postulante.value.id_proceso = res.data.datos.id_proceso;
      if (res.data.datos.postulante_foto != null) {
          postulante.value.postulante_foto = baseUrl+'/'+res.data.datos.postulante_foto;
      } else {
          postulante.value.postulante_foto = "https://static.vecteezy.com/system/resources/previews/027/728/804/non_2x/faceless-businessman-user-profile-icon-business-leader-profile-picture-portrait-user-member-people-icon-in-flat-style-circle-button-with-avatar-photo-silhouette-free-png.png";
      }
      postulante.value.id_modalidad = res.data.datos.id_modalidad;
      postulante.value.dni_temp = res.data.datos.dni;
      foto_postulante.value = res.data.foto;
      huellaD_postulante.value = res.data.huellaD;
      huellaI_postulante.value = res.data.huellaI;
    }
}

const getDocumentos =  async () => {
  if (dniseleccionado.value.length === 8 && /^[0-9]+$/.test(dniseleccionado.value)) {
    let res = await axios.get( "get-documentos-postulante/" + dniseleccionado.value);
    documentos.value = res.data.datos;
  }
}

const getPreinscripciones =  async () => {
  if (dniseleccionado.value.length === 8 && /^[0-9]+$/.test(dniseleccionado.value)) {
    let res = await axios.get("get-preinscripciones-postulante/" + dniseleccionado.value);
    preinscripciones.value = res.data.datos;
  }
}

const getInscripciones =  async () => {
  if (dniseleccionado.value.length === 8 && /^[0-9]+$/.test(dniseleccionado.value)) {
    let res = await axios.get("get-inscripciones-postulante/" + dniseleccionado.value);
    inscripciones.value = res.data.datos;
  }
}

const getAnteriores =  async () => {
  if (dniseleccionado.value === 8 && /^[0-9]+$/.test(dniseleccionado.value)) {
    let res = await axios.get("/carreras-previas/" + dniseleccionado.value);
    anteriores.value = res.data.datos;
  }
}



const validar =  async (doc) => {
  let temp  = doc.verificado === 1 ? 0 : 1;
  console.log("ESTAO:::", temp);
  let res = await axios.post( "cambiar-estado", {id: doc.id, estado: temp });
  getDocumentos();
}

const CambiarCodigo =  async () => {
  let res = await axios.post( "cambiar-codigo", doc.value );
  doc.value = { 
    id:"",
    codigo:"",
    id_programa:"",
    dni_temp:""
  }
  openModal.value = false;
  getDocumentos();
}


const Inscribir =  async () => {
  let res = await axios.post( "inscribir", { postulante: postulante.value });

  imprimirPDF(dniseleccionado.value)
  dniseleccionado.value = "";
  dni.value = "";

  postulante.value = { 
    id:"",
    nombres:"", 
    postulante_foto: null, 
    primer_apellido:"",
    segundo_apellido:"", 
    sexo:'Masculino', 
    fec_nacimiento:"",
    colegio: "",
    id_gestion:"",
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

const observados = [
'76535958',
'75976209',
'60417373',
'72979295',
'77438840',
'71465589',
'60857514',
'72757847',
'76950626',
'70869508',
'60316888',
'60180054',
'74202757',
'71478256',
'61514136',
'60509538',
'60983672',
'60908934',
'60850613',
'75855507',
'75197157',
'73950287',
'75950305',
'75864795',
'60173861',
'70244520',
'60909658',
'60181061',
'71445229',
'77086486',
'73959508',
'71738875',
'75606038',
'75088446',
'76988648',
'77686000',
'74349169',
'75691493',
'71888467',
'76766749',
'73135799',
'73379983',
'75471068',
'60759084',
'73459137',
'73772047',
'75896114',
'74204726',
'70679850',
'60909370',
'73757959',
'61194241',
'75213792',
'76770771',
'75403251',
'74803849',
'60067936',
'77800950',
'60850137',
'71493950',
'60759183',
'74325936',
'71440923',
'60443021',
'60220628',
'61063505',
'60206586',
'60487645',
'60068729',
'60604099',
'71913989',
'61152631'
];


let timeout2;
watch(dni, ( newValue, oldValue ) => {
  clearTimeout(timeout2);
  timeout2 = setTimeout(() => { 
    if(dni.value.length == 8){
      postulante.postulante_foto = null,       
      getPostulantes();
    }
 }, 500
  );
})

let timeout;
watch(dniseleccionado, ( newValue, oldValue ) => {

  clearTimeout(timeout);  
    timeout = setTimeout(() => {
      getPreinscripciones()
      getInscripciones()
      getPostulantesByDni()
      getDocumentos()
      getAnteriores()

    }, 500);
})

///revisor/nuevo-pdf-inscripcion/73903851
// const GenerarNuevoPDF =  (dnni) => {
//     var iframe = document.createElement('iframe');
//     iframe.style.display = "none";
//     iframe.src = baseUrl+'/documentos/10/inscripciones/constancias/'+dnni+'.pdf';
//     document.body.appendChild(iframe);
//     iframe.contentWindow.focus();
//     iframe.contentWindow.print();
// }

const imprimirPDF =  (dnni) => {
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = baseUrl+'/documentos/'+postulante.value.id_proceso+'/inscripciones/constancias/'+dnni+'.pdf';
    document.body.appendChild(iframe);
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
}

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
  { title: 'Acciones', dataIndex: 'acciones', width:'78px'}  
]

const colPreinscripciones =  [
  { title: 'Programa', dataIndex: 'programa', key: 'programa',},
  { title: 'Proceso', dataIndex: 'proceso', key: 'proceso',},
  { title: 'Modalidad', dataIndex: 'modalidad', key: 'modalidad', },
  { title: 'Estado', dataIndex: 'estado', key: 'estado', },
  { title: 'Ver', dataIndex: 'acciones', },
]

const confirm = e => { Inscribir(); };
const cancel = e => { message.error('Click on No'); };

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

.borde-azul {
  border: 2px solid blue;
  border-radius: 4px;
  color:blue;
  font-weight:bold;
}

.borde-naranja {
  border: 2px solid #ff00aa;
  border-radius: 4px;
  color:#ff00dd;
  font-weight:bold;
}

.elemento {
    position: relative; 
    background: white;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat; 
    overflow: hidden;
    opacity: 1;
    color: #0e4165bd;
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
</style>