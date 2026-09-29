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
                          <div class="photo-card" style="">
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
                              v-if="ingresante.foto"
                              :src="'../'+ingresante.foto"
                              class="profile-photo"
                            />
                            <img
                              v-else
                              class="profile-photo"
                              src="https://img.freepik.com/vector-premium/icono-cara-hombre-piel-clara_238404-886.jpg" alt="">
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

                          <!-- <div class="mb-4">
                            <a-radio-group v-model:value="crear_correo" size="large" button-style="solid">
                              <a-radio-button :value="1">Crear correo</a-radio-button>
                              <a-radio-button :value="0">No crear correo</a-radio-button>
                            </a-radio-group>
                          </div> -->

                        </a-col>
                      </a-row>
                    </a-card>

                      <a-card class="mb-4">
                         <a-card-title>
                          <div class="flex justify-between">
                            <div>
                               <strong>Correo institucional</strong>

                            </div>
                            <div class="flex justify-end">
                              <a-button
                                v-if="ingresante.correo_institucional == null"
                                type="primary"
                                @click="crearCorreo()"
                                class="update-button"
                                style="margin-top: -10px; height: 34px;"
                              >
                                <template #icon><save-outlined /></template>
                                Crear correo
                              </a-button>
                              <a-button
                                v-else
                                type="primary"
                                @click="crearCorreo()"
                                class="update-button"
                                size="large"
                              >
                                <template #icon><save-outlined /></template>
                                Crear correo
                              </a-button>
                            </div>
                          </div>
                          
                         </a-card-title>
                          <div class="mt-4 mb-6">
                            <div>
                              <div  v-if="ingresante.correo_institucional != null"> <i> {{ ingresante.correo_institucional }} </i> </div>
                              <div v-else > <i> El ingresante aún no tiene correo institucional </i> </div>
                            </div>
                          </div>

                          <div class="mt-3">
                            <div>
                              <span><strong>Otros correos registrados</strong></span>
                            </div>

                            <div class="mb-4">
                              <a-table :columns="colAnteriores" :dataSource="correo_anteriores" :pagination="false" :scroll="{ x: 'max-content' }">
                              </a-table>
                            </div>
                          </div>
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
         <a-button
              type="primary"
              @click="abrirVentana()"
              class="update-button"
              size="large"
            >
              <template #icon><save-outlined /></template>
              Registrar Ingreso
          </a-button>
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
  programa_correo:"",
  proceso:"",
  modalidad:"",
  puntaje:"",
  programa:"",
  facultad:"",
  correo_institucional:null,
  fecha:"",
  foto:"",
  puesto:""
})

                         
const crearCorreo = async () => {
  try {
    const res = await axios.post('crear_correo_institucional', {
      id: ingresante.value.id,
      apellido_paterno: ingresante.value.primer_apellido,
      apellido_materno: ingresante.value.segundo_apellido,
      nombres: ingresante.value.nombres,
      dni: ingresante.value.nro_doc,
      celular: ingresante.value.celular,
      correo_secundario: ingresante.value.email,
      facultad: ingresante.value.facultad_correo,
      escuela: ingresante.value.programa_correo,
      numero_ingresos: 1,
    });

    if (res.data.users && res.data.users.length > 0) {
      correo_anteriores.value = res.data.users;
      ingresante.value.correo_institucional = res.data.users[0].email;
    }
  } catch (error) {
    console.error('Error al crear correo:', error);
    notification('error', 'Error', 'No se pudo crear el correo institucional');
  }
};



const fot = ref("https://img.freepik.com/vector-premium/icono-cara-hombre-piel-clara_238404-886.jpg");

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
  ingresante.value.programa_correo = res.data.datos.programa_correo
  ingresante.value.facultad_correo = res.data.datos.facultad_correo
  ingresante.value.correo_institucional = res.data.datos.correo_institucional
  ingresante.value.puesto= res.data.datos.puesto
  ingresante.value.foto= res.data.datos.foto || "imagenes/sin_imagen.png";
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
  getCorreos();   

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

  } catch (error) {
    console.error('Error:', error);
  }
};


const getCorreos = async () => {
  let res = await axios.post('crear_correo_institucional',{
    id: ingresante.value.id,
    apellido_paterno: ingresante.value.primer_apellido,
    apellido_materno: ingresante.value.segundo_apellido,
    nombres: ingresante.value.nombres,
    dni: ingresante.value.nro_doc,
    celular: ingresante.value.celular,
    correo_secundario: ingresante.value.email,
    facultad: ingresante.value.facultad_correo,
    escuela: ingresante.value.programa_correo,
    numero_ingresos: 0,
  }); 
  correo_anteriores.value = res.data.users; 

}


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
/* ============================================================================
   Revisión biométrica — cabecera de identificación + ficha editable + cotejo
   de fotografías. La cabecera con imagen es deliberadamente la única pieza
   "ilustrada" del módulo: cumple la función de identificar de un vistazo al
   postulante frente a la persona que tiene delante.
   ========================================================================== */

/* Cabecera de identificación ---------------------------------------------- */
.fondo-biometrico {
  position: relative;
  width: 100%; height: 300px;
  background-image:
    linear-gradient(90deg, rgba(10,18,32,.72) 0%, rgba(10,18,32,.34) 52%, rgba(10,18,32,.06) 100%),
    url("../../../assets/imagenes/fondo-biometrico.jpg");
  background-repeat: no-repeat;
  background-size: cover;
  background-position: center;
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  overflow: hidden;
}
.header-biometrico-container-foto {
  position: absolute; top: 46px; left: 24px;
  border: 3px solid rgba(255,255,255,.92);
  border-radius: var(--rev-r-md);
  overflow: hidden;
  box-shadow: var(--rev-sh-lg);
  background: var(--rev-n-200);
}
.biometrico-foto-imagen { width: 170px; display: block; aspect-ratio: 3 / 4; object-fit: cover; }

.header-biometrico-letras-bot { position: absolute; top: 22px; left: 226px; right: 24px; }
.header-biometrico-letras-top { position: absolute; bottom: 26px; left: 226px; right: 24px; }

.header-biometrico-nombre {
  font-family: var(--rev-font);
  font-size: 2.35rem; font-weight: 700;
  letter-spacing: -.022em; line-height: 1.08;
  color: #fff;
  text-shadow: 0 1px 12px rgba(10,18,32,.4);
}
.header-biometrico-2da {
  font-family: var(--rev-font);
  font-size: var(--rev-fs-md); font-weight: 600;
  letter-spacing: .18em; text-transform: uppercase;
  color: rgba(255,255,255,.82);
}
.header-biometrico-programa {
  display: inline-block;
  font-family: var(--rev-font);
  font-size: var(--rev-fs-md); font-weight: 650;
  letter-spacing: .05em; text-transform: uppercase;
  color: #fff;
  background: rgba(255,255,255,.14);
  border: 1px solid rgba(255,255,255,.22);
  border-radius: var(--rev-r-sm);
  padding: 3px 9px;
  backdrop-filter: blur(3px);
}
.header-biometrico-modalidad {
  font-family: var(--rev-font);
  font-size: var(--rev-fs-sm); font-weight: 560;
  letter-spacing: .14em; text-transform: uppercase;
  color: rgba(255,255,255,.7);
}
.header-modalidad { color: rgba(255,255,255,.7); }

/* Ficha ------------------------------------------------------------------- */
.elegant-profile-card {
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
  padding: 0;
}
.profile-data-section { padding: var(--rev-s-6); }
.header-section {
  display: flex; align-items: center; justify-content: space-between; gap: var(--rev-s-5);
  margin-bottom: var(--rev-s-6);
  padding-bottom: var(--rev-s-5);
  border-bottom: 1px solid var(--rev-line);
}
.section-title {
  margin: 0;
  font-size: var(--rev-fs-lg); font-weight: 650;
  letter-spacing: var(--rev-track-tight); color: var(--rev-ink);
}
.dni-badge {
  font-family: var(--rev-mono);
  font-size: var(--rev-fs-sm); font-weight: 650;
  font-variant-numeric: tabular-nums;
  color: var(--rev-primary-700);
  background: var(--rev-primary-50);
  border: 1px solid var(--rev-primary-200);
  border-radius: var(--rev-r-sm);
  padding: 3px 9px;
}

/* Formulario --------------------------------------------------------------- */
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--rev-s-5) var(--rev-s-6); }
.elegant-form-item :deep(.ant-form-item-label) { padding-bottom: 3px; }
.elegant-form-item :deep(.ant-form-item-label label) {
  font-size: var(--rev-fs-sm); font-weight: 600; color: var(--rev-ink-3); height: auto;
}
.elegant-form-item :deep(.ant-form-item-label label::after) { display: none; }

.elegant-input,
.elegant-select,
.elegant-date-picker {
  width: 100%;
  border-radius: var(--rev-r-md) !important;
  border-color: var(--rev-line-strong) !important;
  background: var(--rev-surface) !important;
  transition: border-color var(--rev-t-fast) var(--rev-ease), box-shadow var(--rev-t-fast) var(--rev-ease);
}
.elegant-input { height: 34px; }
.elegant-select :deep(.ant-select-selector),
.elegant-date-picker { height: 34px !important; }
.elegant-input:hover,
.elegant-date-picker:hover { border-color: var(--rev-n-300) !important; }
.elegant-input:focus,
.elegant-input:focus-within {
  border-color: var(--rev-primary-500) !important;
  box-shadow: var(--rev-ring) !important;
}
.input-icon { color: var(--rev-ink-4); font-size: 14px; }

/* Acciones ----------------------------------------------------------------- */
.update-button,
.btn-actualizar {
  height: 34px;
  border: 1px solid var(--rev-primary-600);
  border-radius: var(--rev-r-md);
  padding: 0 16px;
  background: var(--rev-primary-600);
  color: #fff;
  font-family: var(--rev-font); font-size: var(--rev-fs-md); font-weight: 580;
  box-shadow: none;
  transition: background var(--rev-t-fast) var(--rev-ease), border-color var(--rev-t-fast) var(--rev-ease);
}
.update-button { margin-top: var(--rev-s-6); }
.btn-actualizar { width: 100%; }
.update-button:hover,
.btn-actualizar:hover { background: var(--rev-primary-700); border-color: var(--rev-primary-700); color: #fff; transform: none; }
.update-button:active,
.btn-actualizar:active { background: var(--rev-primary-800); border-color: var(--rev-primary-800); }

/* Cotejo de fotografías ---------------------------------------------------- */
.photo-comparison-section { display: flex; gap: var(--rev-s-6); padding: var(--rev-s-6); height: 100%; }
.photo-card {
  flex: 1; min-width: 0;
  display: flex; flex-direction: column;
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  overflow: hidden;
  box-shadow: var(--rev-sh-xs);
}
.photo-header {
  display: flex; align-items: center; gap: 7px;
  padding: 9px var(--rev-s-5);
  background: var(--rev-surface-2);
  border-bottom: 1px solid var(--rev-line);
  font-size: var(--rev-fs-2xs); font-weight: 680;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-ink-3);
}
.photo-icon { color: var(--rev-primary-600); }
.profile-photo {
  width: 100%; aspect-ratio: 6 / 9; object-fit: cover;
  background: var(--rev-n-100);
  display: block;
}

/* Responsivo --------------------------------------------------------------- */
@media (max-width: 992px) {
  .form-grid { grid-template-columns: 1fr; }
  .photo-comparison-section { flex-direction: column; }
}
@media (max-width: 720px) {
  .fondo-biometrico { height: 210px; }
  .header-biometrico-container-foto { top: 44px; left: 12px; border-width: 2px; }
  .biometrico-foto-imagen { width: 104px; }
  .header-biometrico-letras-bot { top: 14px; left: 128px; right: 12px; }
  .header-biometrico-letras-top { bottom: 14px; left: 128px; right: 12px; }
  .header-biometrico-nombre { font-size: 1.32rem; }
  .header-biometrico-2da { font-size: var(--rev-fs-xs); letter-spacing: .12em; }
  .header-biometrico-programa { font-size: var(--rev-fs-xs); padding: 2px 6px; }
  .header-biometrico-modalidad { font-size: var(--rev-fs-2xs); }
  .header-modalidad { display: none; }
}
</style>
