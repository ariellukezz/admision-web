<template>
<Head title="Revisión Posterior"/>
<AuthenticatedLayout :title="props.id_proceso">
  <div>
    <a-card class="mb-0 p-0" >
      <a-row :gutter="16" class="mb-2">
        <a-col :span="24" :sm="24" :md="24" :lg="24" style="display:flex; justify-content: end;">
            <div class="mr-0">
              <label class="mr-2"> Buscar:</label>
              <a-auto-complete
                v-model:value="dniseleccionado"
                :options="postulantes"
                style="width: 300px"
                @select="onSelect"
                @search="onSearch">
                <a-input
                  ref="dniInput"
                  placeholder="Buscar"
                  v-model:value="dni"
                  @keypress="handleKeyPress"
                  style="border-radius: 8px; height: 32px;"
                />
                  <template #suffix>
                    <credit-card-outlined />
                  </template>
                  <template #option="{ value: val, label:lab }">
                    <div style="height: 34px;">
                      <div><span style="font-weight: 700; color: black; font-size: .7rem;">{{ val }}</span></div>
                      <div style="margin-top: -10px;"><span style="font-size: .8rem; text-transform: uppercase;">{{ lab }}</span></div>
                    </div>
                  </template>
              </a-auto-complete>
            </div>

        </a-col>
      </a-row>

      <a-row :gutter="16">
        <a-col :span="24" :sm="24" :md="24" :lg="24" style="border: 1px solid #d9d9d9; min-width: 600px;" class="m-0 p-0">
          <div style="margin-right: -8px; margin-left: -8px; min-width: 600px;">

            <a-tabs v-model:activeKey="activeKey" type="card" style="">

              <a-tab-pane key="7" tab="Datos" class="pl-2 pr-2">
                <div class="flex mb-3" style="align-items:center; justify-content: center; width: 100%; height: 40px; background: #cdcdcd4F; border-radius: 7px;">
                  <span style="font-weight: bold; font-size: 1.2rem;">Datos personales</span>
                </div>
                <div v-if="ingresante">
            <a-card class="elegant-profile-card">
  <a-row :gutter="[24, 16]">
    <!-- Columna de datos personales -->
    <a-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10">
      <div class="profile-data-section">
        <div class="header-section">
          <div class="dni-badge">DNI {{ ingresante.nro_doc }}</div>
        </div>
        
        <div class="form-grid">
          <a-form-item label="Primer Apellido" class="elegant-form-item">
            <a-input v-model:value="ingresante.primer_apellido" class="elegant-input">
              <template #prefix><user-outlined class="input-icon" /></template>
            </a-input>
          </a-form-item>

          <a-form-item label="Segundo Apellido" class="elegant-form-item">
            <a-input v-model:value="ingresante.segundo_apellido" class="elegant-input">
              <template #prefix><user-outlined class="input-icon" /></template>
            </a-input>
          </a-form-item>

          <a-form-item label="Nombres" class="elegant-form-item">
            <a-input v-model:value="ingresante.nombres" class="elegant-input">
              <template #prefix><solution-outlined class="input-icon" /></template>
            </a-input>
          </a-form-item>

          <a-form-item label="Puesto" class="elegant-form-item">
            <a-input v-model:value="ingresante.puesto" class="elegant-input">
              <template #prefix><idcard-outlined class="input-icon" /></template>
            </a-input>
          </a-form-item>

          <a-form-item label="Tipo Documento" class="elegant-form-item">
            <a-select v-model:value="ingresante.tipo_doc" class="elegant-select">
              <a-select-option :value="1">DNI</a-select-option>
              <a-select-option :value="2">Carné Extranjeria</a-select-option>
            </a-select>
          </a-form-item>

          <a-form-item label="Sexo" class="elegant-form-item">
            <a-select v-model:value="ingresante.sexo" class="elegant-select">
              <a-select-option value="1">Masculino</a-select-option>
              <a-select-option value="2">Femenino</a-select-option>
            </a-select>
          </a-form-item>

          <a-form-item label="Fecha Nacimiento" class="elegant-form-item">
            <a-date-picker 
              v-model:value="ingresante.fec_nacimiento"
              format="DD/MM/YYYY"
              class="elegant-date-picker"
              placeholder="Seleccionar fecha"
            />
          </a-form-item>
        </div>

        <a-button 
          type="primary" 
          @click="actualizar()" 
          class="update-button"
          size="large"
        >
          <template #icon><save-outlined /></template>
          Actualizar Datos
        </a-button>
      </div>
    </a-col>

    <!-- Columna de fotos para comparación -->
    <a-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14">
      <div class="photo-comparison-section">
        <div class="photo-card">
          <div class="photo-header">
            <camera-outlined class="photo-icon" />
            <span>FOTO ACTUAL</span>
          </div>
          <img 
            :src="fot" 
            class="profile-photo"

          />
        </div>

        <div class="photo-card">
          <div class="photo-header">
            <idcard-outlined class="photo-icon" />
            <span>FOTO DOCUMENTO</span>
          </div>
          <img 
            :src="docDniPhoto" 
            class="profile-photo"

          />
        </div>
      </div>
    </a-col>
  </a-row>
</a-card>


                  <div v-if="anteriores[0]" class="flex mb-3 mt-3" :style="anteriores[0] ? 'align-items:center; justify-content: center; width: 100%; height: 40px; background: red; border-radius: 7px;' : 'align-items:center; justify-content: center; width: 100%; height: 40px; background: #cdcdcd4F; border-radius: 7px;'">
                    <span style="font-weight: bold; font-size: 1.2rem;">Datos de ingreso anterior</span>
                  </div>

                  <div v-if="anteriores[0]">
                    <a-card>
                      <div v-for="(ant,index) in anteriores" :key="index" >
                        <Anterior :item="ant" />
                      </div>
                    </a-card>
                  </div>

                    <div class="flex mb-3 mt-3" style="align-items:center; justify-content: center; width: 100%; height: 40px; background: #cdcdcd4F; border-radius: 7px;">
                      <span style="font-weight: bold; font-size: 1.2rem;">Datos de ingreso</span>
                    </div>

                    <a-card>
                    <a-row :gutter="16">

                      <a-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Proceso</label>
                          <a-input v-model:value="ingresante.proceso">
                            <template #prefix> <sin-icono/> </template>  
                          </a-input>
                        </a-form-item>
                      </a-col>

                      <a-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Modalidad</label>
                          <a-input v-model:value="ingresante.modalidad">
                            <template #prefix> <sin-icono/> </template>  
                          </a-input>
                        </a-form-item>
                      </a-col>

                      <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Programa</label>
                          <a-input v-model:value="ingresante.programa">
                            <template #prefix> <sin-icono/> </template>  
                          </a-input>
                        </a-form-item>
                      </a-col>

                      <a-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Puntaje</label>
                          <a-input v-model:value="ingresante.puntaje">
                            <template #prefix> <sin-icono/> </template>  
                          </a-input>
                        </a-form-item>
                      </a-col>

                      <a-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Fecha ingreso</label>
                          <a-input v-model:value="ingresante.fecha">
                            <template #prefix> <sin-icono/> </template>  
                          </a-input>
                        </a-form-item>
                      </a-col>

                    </a-row>
                    </a-card>

                    <div v-if="anteriores[0]" class="flex mb-3 mt-3" style="align-items:center; justify-content: center; width: 100%; height: 40px; background: #cdcdcd4F; border-radius: 7px;">
                      <span style="font-weight: bold; font-size: 1.2rem;">Segunda Carrera</span>
                    </div>

                    <a-card class="mb-3" style="margin-bottom: 10px;">

                      <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                          <a-form-item>
                            <div style="text-align:center;">

                            </div>
                          </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                          <a-form-item>
                            <div class="mb-2">
                              <a-label>SEGUNDA CARRERA</a-label>
                              <a-select
                                ref="select"
                                v-model:value="n_carrera"
                                style="width: 100%"
                              >
                                <a-select-option :value="1">SI</a-select-option>
                                <a-select-option :value="0">NO</a-select-option>
                              </a-select>
                            </div>
                          </a-form-item>

                          <div class="mb-4">
                            <a-table :columns="colAnteriores" :dataSource="correo_anteriores" :pagination="false" :scroll="{ x: 'max-content' }">
                            </a-table>
                          </div>

                          <div class="mb-4">
                            <a-radio-group v-model:value="crear_correo" size="large" button-style="solid">
                              <a-radio-button :value="1">Crear correo</a-radio-button>
                              <a-radio-button :value="0">No crear correo</a-radio-button>
                            </a-radio-group>
                          </div>

                        </a-col>
                      </a-row>
                    </a-card>

                </div>
              </a-tab-pane>


              <a-tab-pane key="1" tab="Solicitud" class="pl-2 pr-2">
                <div>
                  <div style="width:100%; height:380px; position:relative; overflow:hidden">
                    <div v-if="dniseleccionado !== null && dniseleccionado.length === 8">
                      <iframe :src="baseUrl+'/documentos/'+id_proceso+'/preinscripcion/solicitudes/'+dniseleccionado+'.pdf'" style="top:-54px; position:absolute" width="100%" height="100%" scrolling="yes" frameborder="1" ></iframe>
                    </div>
                </div>
                </div>
              </a-tab-pane>
              <a-tab-pane key="4" tab="Const. Inscripcion">
                <div>
                  <div style="width:100%; height:380px; position:relative; overflow:hidden">
                    <div v-if="dniseleccionado !== null && dniseleccionado.length === 8">
                      <iframe :src="baseUrl+'/documentos/'+id_proceso+'/inscripciones/constancias/'+dniseleccionado+'.pdf'" style="top:-54px; position:absolute" width="100%" height="470px"   scrolling="yes" frameborder="1" ></iframe>
                    </div>
                  </div>
                </div>
              </a-tab-pane>

              <a-tab-pane key="6" tab="D. Biométricos">
              <div>
                <div class="flex justify-center mb-6" style="width: 100%;">
                  <div class="flex justify-center " style="text-align:center;">
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="12" :md="8" :lg="12">
                          <div class="p-6">
                            <img :src="baseUrl+'/documentos/'+id_proceso+'/inscripciones/fotos/'+dniseleccionado+'.jpg'"/>
                            <div class="flex justify-center"> Foto Inscripción.</div>
                          </div>
                        </a-col>
                        <a-col :xs="24" :sm="12" :md="8" :lg="12">
                          <div class="p-6">
                            <img :src="hDer"/>
                            <div class="flex justify-center"> Foto Biometrico.</div>
                          </div>
                        </a-col>
                    </a-row>
                  </div>
                </div>



                <div class="flex justify-center mb-6" style="width: 100%;">
                  <div class="flex justify-center " style="text-align:center;">
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="12" :md="8" :lg="5">
                          <div>
                            <img :src="baseUrl+'/documentos/'+id_proceso+'/inscripciones/huellas/'+dniseleccionado+'.jpg'"/>
                            <div class="flex justify-center"> H. inscripción</div>
                          </div>
                        </a-col>
                        <a-col :xs="24" :sm="12" :md="8" :lg="5">
                          <div>
                            <img :src="baseUrl+'/documentos/'+id_proceso+'/inscripciones/huellas/'+dniseleccionado+'x.jpg'"/>
                            <div class="flex justify-center"> H. inscripción</div>
                          </div>
                        </a-col>
                        <a-col :xs="24" :sm="12" :md="8" :lg="4">
                          <div>
                            <img :src="baseUrl+'/documentos/'+id_proceso+'/examen/huellas/'+dniseleccionado+'.jpg'"/>
                            <div class="flex justify-center"> H. Examen</div>
                          </div>
                        </a-col>
                        <a-col :xs="24" :sm="12" :md="8" :lg="5">
                          <div>
                            <img :src="baseUrl+'/documentos/'+id_proceso+'/control_biometrico/huellas/'+dniseleccionado+'.jpg'"/>
                            <div class="flex justify-center"> H. Biometrico</div>
                          </div>
                        </a-col>
                        <a-col :xs="24" :sm="12" :md="8" :lg="5">
                          <div>
                            <img :src="baseUrl+'/documentos/'+id_proceso+'/control_biometrico/huellas/'+dniseleccionado+'x.jpg'"/>
                            <div class="flex justify-center"> H. Biometrico</div>
                          </div>
                        </a-col>
                    </a-row>
                  </div>
                </div>

              </div>
              </a-tab-pane>

            </a-tabs>

          </div>
        </a-col>
      </a-row>
      <div class="mt-4 flex justify-end" style="margin-right: -10px;">
        <a-button type="primary" size="large" @click="abrirVentana()">Registrar</a-button>
      </div>
    </a-card>

    <div style="max-width:100%;">
      <div style="max-width:1000px">

      </div>
    </div>
    <a-modal v-model:open="modal" :closable="false" :maskClosable="false" style="width:1200px;" centered >

      <div class="flex justify-center">
        <span style="font-size:1.4rem; font-weight:bold;">Información del postulante</span>
      </div>

      <div style="margin-top:20px;">
        <h2>DNI</h2>
      </div>
      <div>
        <div style="width:100%; height:380px; position:relative; overflow:hidden">
          <div v-if="dniseleccionado !== null && dniseleccionado.length === 8">
            <iframe :src="docDni" style="top:-54px; position:absolute" width="100%" height="470px"   scrolling="yes" frameborder="1" ></iframe>
          </div>
        </div>
      </div>

      <div style="margin-top:20px;">
        <h2>Certificado de estudios</h2>
      </div>
      <div>
        <div style="width:100%; height:380px; position:relative; overflow:hidden">
          <div v-if="dniseleccionado !== null && dniseleccionado.length === 8">
            <iframe :src="docCert" style="top:-54px; position:absolute" width="100%" height="470px" scrolling="yes" frameborder="1" ></iframe>
          </div>
        </div>
      </div>

      <div style="margin-top:20px;">
        <h2>Solicitud de inscripción</h2>
      </div>
      <div>
          <div style="width:100%; height:400px; position:relative; overflow:hidden">
            <div v-if="dniseleccionado !== null && dniseleccionado.length === 8">
              <iframe :src="baseUrl+'/documentos/'+id_proceso+'/preinscripcion/solicitudes/'+dniseleccionado+'.pdf'" style="top:-54px; position:absolute" width="100%" height="100%" scrolling="yes" frameborder="1" ></iframe>
            </div>
          </div>
      </div>

      <div style="margin-top:-20px;">
        <h2>Constancia de inscripción</h2>
      </div>
      <div>
        <div style="width:100%; height:380px; position:relative; overflow:hidden">
          <div v-if="dniseleccionado !== null && dniseleccionado.length === 8">
            <iframe :src="baseUrl+'/documentos/'+id_proceso+'/inscripciones/constancias/'+dniseleccionado+'.pdf'" style="top:-54px; position:absolute" width="100%" height="470px"   scrolling="yes" frameborder="1" ></iframe>
          </div>
        </div>
      </div>

      <div class="mt-12">
        <h2>Huellas y fotos del postulante</h2>
      </div>
      <div>
        <div class="flex justify-center mb-6" style="width: 100%;">
          <div class="flex justify-center " style="text-align:center;">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="12" :md="8" :lg="12">
                  <div class="p-6">
                    <img :src="baseUrl+'/documentos/'+id_proceso+'/inscripciones/fotos/'+dniseleccionado+'.jpg'"/>
                    <div class="flex justify-center"> Foto Inscripción.</div>
                  </div>
                </a-col>
                <a-col :xs="24" :sm="12" :md="8" :lg="12">
                  <div class="p-6">
                    <img :src="fot"/>
                    <div class="flex justify-center"> Foto Biometrico.</div>
                  </div>
                </a-col>
            </a-row>
          </div>
        </div>
      </div>


      <div class="flex justify-center mb-6" style="width: 100%;">
        <div class="flex justify-center " style="text-align:center;">
          <a-row :gutter="16">
              <a-col :xs="24" :sm="12" :md="8" :lg="5">
                <div>
                  <img :src="baseUrl+'/documentos/'+id_proceso+'/inscripciones/huellas/'+dniseleccionado+'.jpg'"/>
                  <div class="flex justify-center"> H. inscripción</div>
                </div>
              </a-col>
              <a-col :xs="24" :sm="12" :md="8" :lg="5">
                <div>
                  <img :src="baseUrl+'/documentos/'+id_proceso+'/inscripciones/huellas/'+dniseleccionado+'x.jpg'"/>
                  <div class="flex justify-center"> H. inscripción</div>
                </div>
              </a-col>
              <a-col :xs="24" :sm="12" :md="8" :lg="4">
                <div>
                  <img :src="baseUrl+'/documentos/'+id_proceso+'/examen/huellas/'+dniseleccionado+'.jpg'"/>
                  <div class="flex justify-center"> H. Examen</div>
                </div>
              </a-col>
              <a-col :xs="24" :sm="12" :md="8" :lg="5">
                <div>
                  <img :src="hDer"/>
                  <div class="flex justify-center"> H. Biometrico</div>
                </div>
              </a-col>
              <a-col :xs="24" :sm="12" :md="8" :lg="5">
                <div>
                  <img :src="hIzq"/>
                  <div class="flex justify-center"> H. Biometrico</div>
                </div>
              </a-col>
          </a-row>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end mr-2 mb-3">
          <a-button type="primary" style="width:140px; background:#0a3d5a" @click="modal = false">Acpetar</a-button>
        </div>

      </template>

    </a-modal>
  </div>
</AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue'
import { watch, computed, ref, unref } from 'vue';
import { CreditCardOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';
import { defineProps } from 'vue';
import Vouchers from './components/voucher.vue'
import Anterior from './components/anteriores.vue'
import dayjs from 'dayjs';
import { format, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';

const props = defineProps({ id_proceso: { type: Number, required: true }, });
const baseUrl = window.location.origin;

const dni = ref(null);
const dniseleccionado = ref("")
const modal = ref(false);
const codigo = ref("");
const postulante = ref("");
const postulantes = ref([])
const anteriores = ref([]);
const n_carrera = ref(0)
const crear_correo = ref(1)

const checkedList = ref([]);
const correo_anteriores = ref([]);

const dniInput = ref(null)
const save = async () => {
  dniInput.value.focus()
  let res = await axios.post('save-requisito',{
    dni: dniseleccionado.value, requisitos: checkedList.value
  });
  dniseleccionado.value = null
  checkedList.value = []
}
const buscar = ref("");

const ingresante = ref({
  id:null,
  nro_doc: "",
  tipo_doc: null,
  nombres:null,
  sexo: null,
  fec_nacimiento: null,
  primer_apellido:"",
  segundo_apellido:"",
  proceso:"",
  modalidad:"",
  puntaje:"",
  programa:"",
  fecha:"",
  foto:"https://img.freepik.com/vector-premium/icono-cara-hombre-piel-clara_238404-886.jpg",
  puesto:""
})



const fot = ref("https://img.freepik.com/vector-premium/icono-cara-hombre-piel-clara_238404-886.jpg");
const docDniPhoto = ref("https://img.freepik.com/vector-premium/icono-cara-hombre-piel-clara_238404-886.jpg");

const hIzq = ref("https://previews.123rf.com/images/viktorijareut/viktorijareut1511/viktorijareut151100169/47517431-negro-silueta-de-la-ilustraci%C3%B3n-de-huellas-digitales-trama-icono-de-huella-digital-huella-digital.jpg");
const hDer = ref("https://previews.123rf.com/images/viktorijareut/viktorijareut1511/viktorijareut151100169/47517431-negro-silueta-de-la-ilustraci%C3%B3n-de-huellas-digitales-trama-icono-de-huella-digital-huella-digital.jpg");

const docDni = ref("");
const docCert = ref("");


const getIngresante =  async ( ) => {

  let res = await axios.get( "get-ingresante-general/"+dni.value );
  ingresante.value.id = res.data.datos.id
  ingresante.value.nro_doc = res.data.datos.nro_doc
  ingresante.value.tipo_doc = res.data.datos.tipo_doc
  ingresante.value.sexo = res.data.datos.sexo
  if(res.data.datos.fec_nacimiento){ ingresante.value.fec_nacimiento = dayjs(res.data.datos.fec_nacimiento) }
  ingresante.value.nombres = res.data.datos.nombres
  ingresante.value.primer_apellido = res.data.datos.primer_apellido
  ingresante.value.segundo_apellido = res.data.datos.segundo_apellido
  ingresante.value.proceso = res.data.datos.proceso
  ingresante.value.modalidad = res.data.datos.modalidad
  ingresante.value.puntaje = res.data.datos.puntaje
  ingresante.value.programa = res.data.datos.programa
  ingresante.value.puesto= res.data.datos.puesto
  ingresante.value.foto= res.data.datos.foto || "https://img.freepik.com/vector-premium/icono-cara-hombre-piel-clara_238404-886.jpg";
  if(res.data.datos.fecha){ ingresante.value.fecha = res.data.datos.fecha }
  getCarrerasPrevias();
  correo_anteriores.value = res.data.correos;
  fot.value = res.data.foto  || "https://img.freepik.com/vector-premium/icono-cara-hombre-piel-clara_238404-886.jpg";
  hIzq.value = res.data.hIzquierda;
  hDer.value = res.data.hDerecha;
  docDni.value = res.data.doc_dni;
  docCert.value = res.data.doc_certificado;
  if(res.data.datos){
    modal.value = true;
  }

 }

const actualizar = async ( ) => {
  let res = await axios.post(
    "actualizar-ingresante",{
      id: ingresante.value.id,
      tipo_doc: ingresante.value.tipo_doc,
      sexo: ingresante.value.sexo,
      fec_nacimiento: format(new Date(ingresante.value.fec_nacimiento), 'yyyy-MM-dd'),
      nombres: ingresante.value.nombres,
      paterno: ingresante.value.primer_apellido,
      materno: ingresante.value.segundo_apellido
    }
  );
  notificacion(res.data.tipo, res.data.titulo, res.data.mensaje)
  getPostulantesByDni();
}

const getPostulantesBiometrico =  async (term = "", page = 1) => {
  let res = await axios.post(
      "get-postulantes-biometrico?page=" + page,
      { term: buscar.value }
  );
  postulantes.value = res.data.datos.data;
}

let timeoutId;
watch(buscar, ( newValue, oldValue ) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        getPostulantesBiometrico();
        crear_correo
    }, 500);
})

watch(dniseleccionado, (newValue, oldValue ) => {
    if(newValue.length >= 8){
      getIngresante();
    }
})

const abrirVentana = async () => {
  let res = await axios.post("control-biometrico",
  { dni: dniseleccionado.value, n_carrera: n_carrera.value, crear_correo: crear_correo.value });
  imprimirPDF(res.data.datos);
  crear_correo.value = 0;
  dniseleccionado.value = null
}

const imprimirPDF = (dnni) => {
    const url = `${baseUrl}/documentos/${props.id_proceso}/control_biometrico/constancias/${dnni}.pdf`;
    console.log("URL del PDF:", url);
    const iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = url;

    iframe.onload = () => {
        console.log("PDF cargado, iniciando impresión...");
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    };

    document.body.appendChild(iframe);
};
const getCarrerasPrevias = async() => {
  anteriores.value = []
  n_carrera.value = 0

  try {
    if(ingresante.value.dni != null){
      const response = await axios.post('https://service2.unap.edu.pe/TieneCarrerasPrevias/',  {
        doc_:ingresante.value.nro_doc,
        nom_: "SDSFASD",
        app_: "SDSFASD",
        apm_: "SDSFASD"
      }, { headers: { 'Content-Type': 'application/json'}  });
      anteriores.value = response.data;
      if( anteriores.value[0]){
        n_carrera.value = 1;
      }
    }
    else{
      const response = await axios.post('https://service2.unap.edu.pe/TieneCarrerasPrevias/',  {
        doc_: dniseleccionado.value,
        nom_: "SDSFASD",
        app_: "SDSFASD",
        apm_: "SDSFASD"
      }, { headers: { 'Content-Type': 'application/json'}  });
      anteriores.value = response.data;
      if( anteriores.value[0]){
        n_carrera.value = 1;
      }
    }

  } catch (error) {
    console.error('Error:', error);
  }
};

const value = ref([]);
const codigos =ref([]);

const handleChange = (newValue) => {
  console.log('Valor seleccionado:', newValue);
};

getPostulantesBiometrico()

const notificacion = (type, titulo, mensaje) => {
  notification[type]({
    message: titulo,
    description: mensaje,
  });
};


const dataSource = ref([
  { key: '1', name: 'Derechos de admisión', age: '20-23-2024', address: '150.00', },
  { key: '2', name: 'Examen médico', age: '20-23-2024', address: '200.00', }
]);


const columns = ref([
  { title: 'Banco', dataIndex: 'banco', width:'110px',},
  { title: 'Concepto', dataIndex: 'name', key: 'name',},
  { title: 'Fecha', dataIndex: 'age', key: 'age', width:'190px', align:'center' },
  { title: 'Monto S/', dataIndex: 'address', key: 'address', width:'130px', align:'center' },
  { title: '', dataIndex: 'option', width:'80px', }
])

const colpostulantes = ref([
  { title: 'DNI', dataIndex: 'dni', width:'110px',},
  { title: 'Nombres', dataIndex: 'nombres'},
  { title: 'Programa', dataIndex: 'programa', key: 'name',},
  { title: 'Modalidad', dataIndex: 'modalidad', align:'center'},
  { title: 'Area', dataIndex: 'area', align:'center'},
  { title: 'Codigo', dataIndex: 'codigo', align:'center'},
  { title: '', dataIndex: 'acciones', width:'120px',}
]);

const colAnteriores = ref([
  { title: 'name', dataIndex: 'name'},
  { title: 'email', dataIndex: 'email'}
]);
</script>


<style scoped>
.btn-actualizar{
  background:#224464;
  color:white;
  width:100%;
  height: 38px;
  /* color:#1c1c8a;  */
  border-radius:5px;
  border:none;
}
.btn-actualizar:active{
  border:none;
  animation-duration: 1.5s;
  background:#2e5c85c9;
  width:100%;
  height: 38px;
  color:white;
  border-radius:5px;
}
.fondo-biometrico{
  background-image: url("../../../assets/imagenes/fondo-biometrico.jpg");
  background-repeat: no-repeat;
  background-size: cover;
  height:320px; width: 100%; position: relative; border:solid #d9d9d9 1px;
}

.header-biometrico-nombre{
  font-size: 3rem;
  font-family: 'Helvetica';
  color:white;
  font-weight: bold;
}
.header-biometrico-2da{
  font-size: 1.2rem;
  font-family: 'Helvetica';
  color:white;
  letter-spacing: .22rem;
}
.header-biometrico-programa{
  font-size: 1.2rem;
  font-family: 'Helvetica';
  color:#0a3d5a ;
  font-weight: bold;
  letter-spacing: .12rem;
}
.header-biometrico-modalidad{
  font-size: .9rem;
  font-family: 'Helvetica';
  color:black;
  letter-spacing: .22rem;
}

.header-biometrico-container-foto{
  position: absolute;
  top:60px;
  left: 20px;
  border: solid 5px #e7e7e7;
}
.biometrico-foto-imagen{
  width: 180px;
}
.header-biometrico-letras-top{
  position: absolute; bottom:30px; left: 230px;
}

.header-biometrico-letras-bot{
  position: absolute; top:10px; left: 230px;
}

.header-modalidad{
  color: black;
}


@media screen and (max-width: 600px) {
  .header-biometrico-nombre{ font-size: 1.5rem; }
  .header-biometrico-2da{
    font-size: .7rem;
  }
  .header-biometrico-programa{
    font-size: .7rem;
  }
  .header-biometrico-modalidad{
    font-size: .5rem;
  }
  .header-biometrico-container-foto{
    top:60px; left: 10px;  border: solid 2px #e7e7e7;
  }
  .biometrico-foto-imagen{
    width: 100px;
  }

  .fondo-biometrico{
    height:200px; width: 100%; position: relative; border:solid #d9d9d9 1px;
  }
  .header-biometrico-letras-top{
    position: absolute; bottom:10px; left: 125px;
  }
  .header-biometrico-letras-bot{
    position: absolute; top:5px; left: 125px;
  }
  .header-modalidad{
    display: none;
  }

}

/* Estilos generales */
.elegant-profile-card {
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  border: none;
  padding: 24px;
  background: linear-gradient(to bottom right, #fafafa, #ffffff);
}

/* Sección de datos */
.profile-data-section {
  padding: 16px;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.dni-badge {
  background: #1890ff;
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 14px;
}

/* Formulario */
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.elegant-form-item :deep(.ant-form-item-label) {
  font-weight: 500;
  color: #666;
  padding-bottom: 4px;
}

.elegant-form-item :deep(.ant-form-item-label label) {
  font-size: 13px;
}

.elegant-input {
  border-radius: 8px;
  height: 42px;
  border: 1px solid #e0e0e0;
  transition: all 0.3s cubic-bezier(0.645, 0.045, 0.355, 1);
  background-color: #f9f9f9;
}

.elegant-input:hover {
  border-color: #1890ff;
  box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.1);
}

.elegant-input:focus {
  box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
}

.input-icon {
  color: #888;
  font-size: 15px;
}

.elegant-select,
.elegant-date-picker {
  width: 100%;
  height: 42px;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  background-color: #f9f9f9;
}

/* Botón de actualizar */
.update-button {
  margin-top: 24px;
  height: 42px;
  border-radius: 8px;
  padding: 0 32px;
  font-weight: 500;
  background: linear-gradient(to right, #1890ff, #096dd9);
  border: none;
  box-shadow: 0 2px 8px rgba(24, 144, 255, 0.3);
  transition: all 0.3s;
}

.update-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(24, 144, 255, 0.4);
}

/* Sección de fotos */
.photo-comparison-section {
  display: flex;
  gap: 24px;
  height: 100%;
  padding: 16px;
}

.photo-card {
  flex: 1;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.photo-header {
  background: #f5f5f5;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  color: #555;
  font-size: 14px;
}

.photo-icon {
  color: #1890ff;
}

.profile-photo {
  width: 100%;
  height: 280px;
  object-fit: cover;
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 12px;
}

/* Responsive */
@media (max-width: 992px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .photo-comparison-section {
    flex-direction: column;
  }
  
  .profile-photo {
    height: 200px;
  }
}
</style>