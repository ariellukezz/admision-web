<template style="background:pink;">
  <Head title="Preinscipción"/>
  <Layout v-if="examen === 0" :nombre="props.procceso_seleccionado.nombre">
    <!-- <a-button @click="abrirModalDatos()">abrir</a-button> -->
    <a-modal v-model:visible="open" centered style="width: 100%; max-width:1200px;" :footer="false" >
      <div>
        <h1 style="font-weight:bold; font-size:1.2rem;">Datos personales</h1>
        <hr>

        <div class="datos-container" style="margin-bottom: 10px;">
          <div class="datos-column">
            <label for="name">Tipo doc: <span></span>  </label>
            <input v-if="tipo_docs[datospersonales.tipo_doc] === 'DNI'"  type="text" disabled :value="tipo_docs[datospersonales.tipo_doc]"/>
            <input v-else type="text" disabled value="CARNÉ EXTRANJERIA"/>
          </div>

          <div class="datos-column">
            <label for="name">N° Documento: <span></span>  </label>
            <input type="text" disabled :value="formState.dni"  />
          </div>

          <div class="datos-column">
            <label for="name">Primer apellido: <span></span>  </label>
            <input type="text" disabled :value="datospersonales.primerapellido"  />
          </div>

          <div class="datos-column">
            <label for="name">Segundo apellido: <span></span>  </label>
            <input type="text" disabled :value="datospersonales.segundo_apellido"  />
          </div>

          <div class="datos-column">
            <label for="name">Pre Nombres: <span></span>  </label>
            <input type="text" disabled :value="datospersonales.nombres"  />
          </div>

          <div class="datos-column">
            <label for="name">Estado civil: <span></span>  </label>
            <input type="text" disabled :value="estados_civil[datospersonales.estado_civil]"  />
          </div>

          <div class="datos-column">
            <label for="name">Sexo: <span></span> </label>
            <input type="text" disabled :value="sexos[datospersonales.sexo]"  />
          </div>

          <div class="datos-column">
            <label for="name">Correo: <span></span>  </label>
            <input type="text" disabled :value="datospersonales.correo"  />
          </div>

          <div class="datos-column">
            <label for="name">Celular: <span></span>  </label>
            <input type="text" disabled :value="datospersonales.celular"  />
          </div>

          <div class="datos-column">
            <label for="name">Fec. nacimiento: <span></span>  </label>
            <input type="text" disabled :value="temp_date"/>
          </div>

          <div class="datos-column">
            <label for="name">Ubigeo de nacimiento: <span></span>  </label>
            <input type="text" disabled :value="datospersonales.ubigeo"/>
          </div>
          <div class="datos-column">
        </div>
        </div>
      </div>

      <div>
        <h1 style="font-weight:bold; font-size:1.2rem;">Datos residencia</h1>
        <hr>

        <div class="datos-container" style="margin-bottom: 10px;">
          <div v-if="datospersonales.ubigeo_residencia" class="datos-column">
            <label for="name">Departamento: <span></span>  </label>
            <input type="text" disabled :value="datosresidencia.dep" />
          </div>

          <div v-if="datospersonales.ubigeo_residencia" class="datos-column">
            <label for="name">Provincia: <span></span>  </label>
            <input type="text" disabled :value="datosresidencia.prov"  />
          </div>

          <div v-if="datospersonales.ubigeo_residencia" class="datos-column">
            <label for="name">Distrito: <span></span>  </label>
            <input type="text" disabled :value="datosresidencia.dist"/>
          </div>

          <div class="datos-column" style="width:100%;">
            <label for="name">Dirección: <span></span>  </label>
            <input type="text" disabled :value="datosresidencia.direccion"/>
          </div>

        </div>

      </div>

      <div>
        <h1 style="font-weight:bold; font-size:1.2rem;">Datos del colegio</h1>
        <hr>

        <div class="datos-container" style="margin-bottom: 10px;">
          <div class="datos-column">
            <label for="name">Año de egreso: <span></span>  </label>
            <input type="text" disabled :value="datoscolegio.egreso"/>
          </div>
          <div class="datos-column"  v-if="datospersonales.tipo_doc === 1">
            <label for="name">Departamento: <span></span>  </label>
            <input type="text" disabled :value="datoscolegio.dep"/>
          </div>

          <div class="datos-column"  v-if="datospersonales.tipo_doc === 1">
            <label for="name">Provincia: <span></span>  </label>
            <input type="text" disabled :value="datoscolegio.prov"/>
          </div>

          <div class="datos-column" v-if="datospersonales.tipo_doc === 1">
            <label for="name">Distrito: <span></span>  </label>
            <input type="text" disabled :value="datoscolegio.dist"/>
          </div>

          <div class="datos-column">
            <label for="name">Colegio: <span></span>  </label>
            <input v-if="datospersonales.tipo_doc === 1" type="text" disabled :value="nombrecolegiox"/>
            <input v-else type="text" disabled value="COLEGIOS EXTRANJEROS"/>
          </div>

          <div class="datos-column">
          </div>

        </div>

      </div>

      <div>
        <h1 style="font-weight:bold; font-size:1.2rem;">Datos del padre</h1>

        <hr>
        <div class="datos-container" style="margin-bottom: 10px;">
          <div class="datos-column">
            <label for="name">DNI: <span></span>  </label>
            <input type="text" disabled :value="datospadre.dni"  />
          </div>

          <div class="datos-column">
            <label for="name">Nombres: <span></span>  </label>
            <input type="text" disabled :value="datospadre.nombres"  />
          </div>

          <div class="datos-column">
            <label for="name">Primer apellido: <span></span>  </label>
            <input type="text" disabled :value="datospadre.paterno"  />
          </div>

          <div class="datos-column" style="width:100%;">
            <label for="name">Segundo apellido: <span></span>  </label>
            <input type="text" disabled :value="datospadre.materno"  />
          </div>
        </div>

      </div>

      <div>
        <h1 style="font-weight:bold; font-size:1.2rem;">Datos de la madre</h1>
        <hr>
        <div class="datos-container" style="margin-bottom: 10px;">
          <div class="datos-column">
            <label for="name">DNI: <span></span>  </label>
            <input type="text" disabled :value="datosmadre.dni"  />
          </div>

          <div class="datos-column">
            <label for="name">Nombres: <span></span>  </label>
            <input type="text" disabled :value="datosmadre.nombres"  />
          </div>

          <div class="datos-column">
            <label for="name">Primer apellido: <span></span>  </label>
            <input type="text" disabled :value="datosmadre.paterno"  />
          </div>

          <div class="datos-column" style="width:100%;">
            <label for="name">Segundo apellido: <span></span>  </label>
            <input type="text" disabled :value="datosmadre.materno"  />
          </div>
        </div>

        <!-- {{ datosmadre }} -->
      </div>

      <div>
        <h1 style="font-weight:bold; font-size:1.2rem;">Datos de postulación</h1>
        <hr>

        <div class="datos-container" style="margin-bottom: 10px;">
          <div class="datos-column">
            <label for="name">Modalidad:</label>
            <select v-if="props.procceso_seleccionado.id_modalidad_proceso === 2" disabled v-model="datos_preinscripcion.modalidad">
              <option :value="9">CEPREUNA</option>
            </select>
            <select v-if="props.procceso_seleccionado.id_modalidad_proceso === 1" disabled v-model="datos_preinscripcion.modalidad">
              <option :value="8">EXAMEN GENERAL</option>
              <option :value="7">CONADIS</option>
            </select>
            <select v-if="props.procceso_seleccionado.id_modalidad_proceso === 3" disabled v-model="datos_preinscripcion.modalidad">
              <option :value="1">TITULADOS Y GRADUADOS</option>
              <option :value="2">TRASLADO INTERNO</option>
              <option :value="3">TRASLADO EXTERNO</option>
              <option :value="4">PRIMEROS PUESTOS</option>
              <option :value="5">DEPORTISTAS DESTACADOS</option>
              <option :value="6">BECAS(PRONABEC)</option>
              <option :value="12">(PIR) VICTIMAS DEL TERRORISMO</option>
            </select>
          </div>

          <div class="datos-column">
            <label for="name">Programa:</label>
            <select disabled v-model="datos_preinscripcion.programa">
              <option :value='1'>ADMINISTRACIÓN</option>
              <option :value='2'>ANTROPOLOGÍA</option>
              <option :value='3'>ARQUITECTURA Y URBANISMO</option>
              <option :value='4'>ARTE: ARTES PLÁSTICAS</option>
              <option :value='5'>ARTE: DANZA</option>
              <option :value='6'>ARTE: MÚSICA</option>
              <option :value='8'>BIOLOGÍA: ECOLOGÍA</option>
              <option :value='9'>BIOLOGÍA: MICROBIOLOGÍA Y LABORATORIO CLÍNICO</option>
              <option :value='10'>BIOLOGÍA: PESQUERÍA</option>
              <option :value='11'>CIENCIAS CONTABLES</option>
              <option :value='12'>CIENCIAS DE LA COMUNICACIÓN SOCIAL</option>
              <option :value='13'>CIENCIAS FÍSICO MATEMÁTICAS: FÍSICA</option>
              <option :value='14'>CIENCIAS FÍSICO MATEMÁTICAS: MATEMÁTICAS</option>
              <option :value='15'>DERECHO</option>
              <option :value='16'>EDUCACIÓN FÍSICA</option>
              <option :value='17'>EDUCACIÓN INICIAL</option>
              <option :value='18'>EDUCACIÓN PRIMARIA</option>
              <option :value='19'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE CIENCIA, TECNOLOGÍA Y AMBIENTE</option>
              <option :value='20'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE CIENCIAS SOCIALES</option>
              <option :value='21'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE LENGUA, LITERATURA, PSICOLOGÍA Y FILOSOFÍA</option>
              <option :value='22'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE MATEMÁTICA, FÍSICA, COMPUTACIÓN E INFORMÁTICA</option>
              <option :value='23'>ENFERMERÍA</option>
              <option :value='24'>INGENIERÍA AGRÍCOLA</option>
              <option :value='25'>INGENIERÍA AGROINDUSTRIAL</option>
              <option :value='26'>INGENIERÍA AGRONÓMICA</option>
              <option :value='27'>INGENIERÍA CIVIL</option>
              <option :value='28'>INGENIERÍA DE MINAS</option>
              <option :value='29'>INGENIERÍA DE SISTEMAS</option>
              <option :value='30'>INGENIERÍA ECONÓMICA</option>
              <option :value='31'>INGENIERÍA ELECTRÓNICA</option>
              <option :value='32'>INGENIERÍA ESTADÍSTICA E INFORMÁTICA</option>
              <option :value='33'>INGENIERÍA GEOLÓGICA</option>
              <option :value='34'>INGENIERÍA MECÁNICA ELÉCTRICA</option>
              <option :value='35'>INGENIERÍA METALÚRGICA</option>
              <option :value='36'>INGENIERÍA QUÍMICA</option>
              <option :value='37'>INGENIERÍA TOPOGRÁFICA Y AGRIMENSURA</option>
              <option :value='38'>MEDICINA HUMANA</option>
              <option :value='39'>MEDICINA VETERINARIA Y ZOOTECNIA</option>
              <option :value='40'>NUTRICIÓN HUMANA</option>
              <option :value='41'>ODONTOLOGÍA</option>
              <option :value='42'>SOCIOLOGÍA</option>
              <option :value='43'>TRABAJO SOCIAL</option>
              <option :value='44'>TURISMO</option>
              <option :value='45'>PSICOLOGÍA</option>
              <option :value='46'>INGENIERÍA ECONÓMICA - AZANGARO</option>
              <option :value='47'>INGENIERÍA DE MINAS - AZANGARO</option>
              <option :value='48'>INGENERÍA DE TELECOMUNICACIONES</option>
              <option :value='49'>CIENCIAS CONTABLES - JULI</option>
              <option :value='50'>ARQUITECTURA Y URBANISMO - JULI</option>
              <option :value='51'>INGENIERÍA AGROINDUSTRIAL - JULI</option>
            </select>
          </div>

          <div class="datos-column">
            <label for="name">Tipo de certificado:</label>
            <select disabled v-model="datos_preinscripcion.tipo_certificado">
              <option value='CERTIFICADO BLANCO'>CERTIFICADO BLANCO</option>
              <option value='CERTIFICADO AMARILLO'>CERTIFICADO AMARILLO</option>
              <option value='CONSTANCIA DE ESTUDIOS'>CONSTANCIA DE ESTUDIOS</option>
            </select>
          </div>

          <div v-if="datos_preinscripcion.programa === 38 || datos_preinscripcion.programa === 16" class="datos-column">
            <label for="name">Cod ex. medico:</label>
            <input type="text" disabled :value="datos_preinscripcion.codigo_medico"  />
          </div>

          <div class="datos-column">
            <label for="name">Cod. certificado: <span></span>  </label>
            <input type="text" disabled :value="datos_preinscripcion.codigo_certificado"  />
          </div>

          <div>

          </div>

        <!-- {{ datos_preinscripcion }} -->
      </div>
      <div class="datos-column" style="margin-top:-20px; display:flex; width:100%;">
        <div class="flex">
          <input class="checkbox mr-2" type="checkbox" :value="checkbox1" v-model="checkbox1"/>
          <span style="font-size: 1.2rem; font-weight:bold;"> Certifico que la información brindada es correcta</span>
        </div>
      </div>


    </div>
    <div>
        <div class="datos-column" style="width:100%;">
            <div v-if="checkbox1 == true" class="flex justify-end">
              <a-button html-type="submit" @click="submit" type="primary" class="boton-siguiente">GUARDAR DATOS</a-button>
            </div>
            <div v-else class="flex justify-end">
              <a-button html-type="submit" @click="submit" type="primary" disabled class="boton-siguiente">GUARDAR DATOS</a-button>
            </div>
        </div>
      </div>

    </a-modal>


    <a-modal v-model:visible="ejemplo" :footer="false">
      <span class="font-bold text-xl">{{ datos_preinscripcion.tipo_certificado }}</span>

      <a-tabs v-model:activeKey="activeKey" :size="size">
        <a-tab-pane key="1" tab="CERT. AMARILLO">
          <div style="max-height: 450px; overflow-y: scroll;">
            <img src="../../../assets/imagenes/certificados/amarillo.jpg">
          </div>
        </a-tab-pane>
        <a-tab-pane key="2" tab="CERT. BLANCO">
          <div>
            <img src="../../../assets/imagenes/certificados/blanco.jpg">
          </div>
        </a-tab-pane>
        <a-tab-pane key="3" tab="CONST. DE LOGROS">
          <div>
            <img src="../../../assets/imagenes/certificados/constancia.jpg">
          </div>
        </a-tab-pane>
      </a-tabs>

    </a-modal>


    <a-modal v-model:visible="modalUbigeo" :footer="false">
      <span class="font-bold text-xl">{{ datos_preinscripcion.tipo_certificado }}</span>

      <a-tabs v-model:activeKey="activeKey" :size="size">
        <a-tab-pane key="1" tab="DNI ELECTRÓNICO">
          <div style="max-height: 450px; overflow-y: scroll;">
            <img src="../../../assets/imagenes/dni/1.jpg">
          </div>
        </a-tab-pane>
        <a-tab-pane key="2" tab="DNI AMARILLO">
          <div>
            <img src="../../../assets/imagenes/dni/2.jpg">
          </div>
        </a-tab-pane>
        <a-tab-pane key="3" tab="DNI AZUL">
          <div>
            <img src="../../../assets/imagenes/dni/3.jpg">
          </div>
        </a-tab-pane>
      </a-tabs>

    </a-modal>

    <div style="width: 100%; min-height: calc(100vh - 390px); padding: 20px 20px; margin-top: 0px;">

      <div v-if="nc == 1"><pre> {{ presionado = 1 }} {{ nc = 0 }} </pre> </div>

      <div class="flex mt-3 justify-center align-center" style=" width: 100%; min-height: calc(100vh - 300px)">
        <a-card v-if="pagina_pre === 0"  style="width: 100%;  max-width: 350px; max-height:380px" class="pl-3 pr-3 cardInicio" >
          <div>
            <h1 style="font-size: 1.1rem;">Datos de validación</h1>

          </div>
          <a-form
              ref="formRef" :model="formState"
              name="inicio_dni"
              @finish="onFinish"
              @finishFailed="onFinishFailed"
            >
            <a-radio-group v-model:value="datospersonales.tipo_doc" class="flex justify-end" style="display: flex; width: yellow;" name="radioGroup">
              <a-radio :value="1">Dni</a-radio>
              <a-radio :value="3">Carnet Ext.</a-radio>
            </a-radio-group>

            <div style="margin-bottom: 7px;"><label>N° Documento</label></div>
            <a-form-item
                name="dni"
                :rules="[{ required: true, message: 'Por favor ingresa tu DNI', trigger: 'change' },
                { min: 8, message: 'El dni debe tener 8 digitos', trigger: 'blur',},]"
              >
              <a-input v-if="datospersonales.tipo_doc === 1" v-model:value="formState.dni" @input="dniInput" :maxlength="8" placeholder="N° Documento">
                <template #prefix>
                  <sin-icono/>
                </template>
              </a-input>
              <a-input v-else v-model:value="formState.dni" @input="dniInput" :maxlength="12" placeholder="N° Documento"/>
            </a-form-item>

            <a-card class="mt-3">
              <div class="flex justify-center" style="margin: -20px; cursor: none; pointer-events:none;">
                <span style="text-decoration:line-through; font-family:helvetica; font-weight:bold; font-size:2.2rem; letter-spacing:1rem;"> {{ codigo_aleatorio }} </span>
              </div>
            </a-card>
            <div class="mb-4">
              <div class="mt-3"><label>Código secreto</label></div>
                <a-form-item
                  name="codigo_secreto"
                  :rules="[{ required: true, message: 'Ingresa el código del cuadro', trigger: 'change' },
                    { min: 6, message: 'El ubigeo debe tener 6 caracteres', trigger: 'blur',},
                    { validator: validateCodigoSecreto, trigger: 'change' }
                    ]"
                  >
                  <a-input v-model:value="formState.codigo_secreto" :maxlength="6" placeholder="Ingresa el codigo">
                    <template #prefix>
                      <sin-icono/>
                    </template>
                  </a-input>
                </a-form-item>
              </div>
            <div style="display: flex; justify-content: center; margin-top: 20px;">
              <!-- <a-button type="primary" @click="getDatosApi()">Iniciar Postulación</a-button> -->
              <a-button type="primary" v-if="participa == 0 || codigo_aleatorio !== formState.codigo_secreto || modalcarrerasprevias == true" style="background: #cdcdcd; color: white; border:none; border-radius: 5px;"  disabled>Iniciar Postulación</a-button>
              <a-button type="primary" v-if="participa == 1 && formState.codigo_secreto === codigo_aleatorio && modalcarrerasprevias == false" style="background: teal; border:none; border-radius: 5px;" @click="getDatosPersonales()">Iniciar Postulación</a-button>
            </div>
          </a-form>
        </a-card>
      <!-- END INICIO-->

        <div v-if="pagina_pre === 1">
          <!-- {{ datospersonales }} -->

          <div style="width: 100%; margin-top: 5px; margin-bottom: -30px; ">

          <a-card style="padding-top: -5px; padding-bottom:0px;" class="cardInicio">
            <a-form
              ref="formDatosPersonales" :model="datospersonales"
              name="datosPersonales"
              @finish="onFinish"
              @finishFailed="onFinishFailed"
            >
              <div>

                <div style="margin-bottom: 0px; margin-top: 0px;">
                  <h1 style="font-size: 1.1rem;"> Datos de personales</h1>
                </div>

                <div class="flex align-center justify-end mb-1">
                  <div>
                    <div class="mb-1"> Sexo </div>
                    <a-form-item
                        name="sexo"
                        :rules="[{ required: true, message: 'Elige el sexo', trigger: 'change'},]"
                      >
                      <a-radio-group v-model:value="datospersonales.sexo" class="flex justify-end" name="radioGroup">
                        <a-radio value="1">M</a-radio>
                        <a-radio value="2">F</a-radio>
                      </a-radio-group>
                    </a-form-item>
                  </div>
                  <div style="height: 50px; border-right: solid 1px #d4d0d0 ;"></div>
                  <div class="ml-3" >
                    <div> Estado civil </div>
                    <a-form-item
                        name="estado_civil"
                        :rules="[{ required: true, message: 'Elije tu estado civil', trigger: 'change'},]"
                      >
                      <a-select
                        ref="select"
                        v-model:value="datospersonales.estado_civil"
                        style="width: 90px"
                      >
                        <a-select-option value="1">Soltero</a-select-option>
                        <a-select-option value="2">Casado</a-select-option>
                        <a-select-option value="3">Viudo</a-select-option>
                        <a-select-option value="4">Divorciado</a-select-option>
                      </a-select>
                    </a-form-item>
                  </div>

                </div>

                <a-row :gutter="[16, 0]" class="form-row mb-0" >
                  <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                      <div><label>Primer apellido (<span style="color:red;">*</span>) </label></div>
                      <a-form-item
                        name="primerapellido"
                        :rules="[{ required: true, message: 'Ingresa tu Primer Apellido', trigger: 'change'},]"
                      >
                        <a-input @input="pimerapellidoInput" v-model:value="datospersonales.primerapellido">
                          <template #prefix>
                            <sin-icono/>
                          </template>
                        </a-input>
                      </a-form-item>
                  </a-col>
                  <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                    <a-form-item>
                      <div><label>Segundo apellido</label></div>
                      <a-input v-model:value="datospersonales.segundo_apellido">
                        <template #prefix>
                          <sin-icono/>
                        </template>
                      </a-input>
                    </a-form-item>
                  </a-col>
                  <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                    <a-form-item>
                      <div><label>Pre Nombres (<span style="color:red;">*</span>)</label></div>
                      <a-form-item
                        name="nombres"
                        :rules="[{ required: true, message: 'Ingresa tus Pre Nombres', trigger: 'change'}]"
                      >
                        <a-input @input="nombresInput" v-model:value="datospersonales.nombres">
                          <template #prefix>
                            <sin-icono/>
                          </template>
                        </a-input>

                      </a-form-item>
                    </a-form-item>
                  </a-col>
                </a-row>

                <a-row :gutter="[16, 0]" class="form-row" style="margin-top: -20px;">
                  <a-col :span="24" :md="24" :lg="12" :xl="12" :xxl="16">
                    <a-form-item
                        name="correo"
                        :rules="[
                          { required: true, message: 'Ingresa un correo valido', trigger: 'change'},
                          { type: 'email', message: 'Ingresa un correo valido'},
                          { validator: validateCorreo, trigger:'blur'}
                        ]">
                      <div><label>Correo (<span style="color:red;">*</span>)</label></div>
                      <a-input type="email" @input="correoInput"  v-model:value="datospersonales.correo">
                        <template #prefix>
                          <sin-icono/>
                        </template>
                      </a-input>
                    </a-form-item>
                  </a-col>

                  <a-col :span="24" :md="24" :lg="12" :xl="12" :xxl="8">
                    <a-form-item v-if="datospersonales.tipo_doc === 1" name="ubigeo"
                        :rules="[ { required: true, message: 'Ingresa ubigeo de nacimiento', trigger: 'change'},]">
                      <div class="flex justify-between"><label>Ubigeo de nacimiento (<span style="color:red;">*</span>)</label>
                        <span style="color: crimson; cursor: pointer;" @click="modalUbigeo = true"> ver ejemplo</span>
                      </div>
                      <a-input type="text" @input="correoInput"  v-model:value="datospersonales.ubigeo">
                        <template #prefix>
                          <sin-icono/>
                        </template>
                      </a-input>
                    </a-form-item>
                    <a-form-item v-else name="ubigeo">
                      <div><label>Ubigeo de nacimiento:</label></div>
                              <a-input type="text" @input="correoInput"  v-model:value="datospersonales.ubigeo">
                        <template #prefix>
                          <sin-icono/>
                        </template>
                      </a-input>
                    </a-form-item>
                  </a-col>
                </a-row>

                <a-row :gutter="[16, 0]" class="form-row">
                  <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="12">
                    <a-form-item
                        name="celular"
                        :rules="[
                          { required: true, message: 'Ingresa tu celular', trigger: 'change'},
                          { min: 9, message: 'El Celular debe tener 9 digitos', trigger: 'blur',},
                          { validator: validateCelular, trigger:'blur'}
                        ]"
                      >
                      <div><label>Numero de celular (<span style="color:red;">*</span>)</label></div>
                      <a-input @input="celularInput" :maxlength="9" v-model:value="datospersonales.celular">
                        <template #prefix>
                          <sin-icono/>
                        </template>
                      </a-input>
                    </a-form-item>
                  </a-col>
                  <a-col :span="24" :md="24" :lg="18  " :xl="16" :xxl="12">
                    <a-form-item
                        name="fec_nacimiento"
                        :rules="[ { required: true, message: 'Ingresa tu fecha de nacimiento', trigger: 'change'},
                         { validator: validateFechaNacimiento, trigger: 'change' }]"
                      >
                      <div><label>Fec. Nacimiento (<span style="color:red;">*</span>) "dd/mm/aaaa" </label></div>
                        <a-date-picker placeholder="Selecciona tu fecha de nacimiento" style="width: 100%" v-model:value="datospersonales.fec_nacimiento" format='DD/MM/YYYY' />
                    </a-form-item>
                  </a-col>
                </a-row>

              </div>
            </a-form>
          </a-card>
        </div>
        </div>

        <div v-if="pagina_pre === 2">

          <div style="width: 100%; margin-top: 5px; ">
          <a-card class="card-datos-colegio" style="padding-top: -5px; padding-bottom:0px; min-width:360px;">

            <div>

            <a-form
              ref="formDatosResidencia" :model="datosresidencia"
              name="datosResidencia"
              @finish="onFinish"
              @finishFailed="onFinishFailed"
              >
              <!-- //DATOS DE RESIDENCIA -->
              <div  class="card-datos-colegio" style="margin-bottom: 25px; margin-top: 10px;">
                <h1 style="font-size: 1.1rem;"> Datos de residencia</h1>
              </div>

              <div style="display: none;">{{ getDepartamentos() }}</div>

              <a-row :gutter="[16, 0]" class="form-row">
                <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                  <a-form-item
                    name="pais"
                    :rules="[{ required: true, message: 'Seleccione el pais', trigger: 'change' }]"
                  >
                    <div><label>Pais:</label></div>
                      <a-select
                          ref="select"
                          v-model:value="datosresidencia.pais"
                          style="width: 100%" >
                          <a-select-option :value="125" v-if="datospersonales.tipo_doc === 1">PERÚ</a-select-option>
                          <a-select-option :value="23" v-if="datospersonales.tipo_doc !== 1">BOLIVIA</a-select-option>
                          <a-select-option :value="11" v-if="datospersonales.tipo_doc !== 1">ARGENTINA</a-select-option>
                          <a-select-option :value="184" v-if="datospersonales.tipo_doc !== 1">VENEZUELA</a-select-option>
                          <a-select-option :value="38"  v-if="datospersonales.tipo_doc !== 1">COLOMBIA</a-select-option>
                          <a-select-option :value="35" v-if="datospersonales.tipo_doc !== 1">CHILE</a-select-option>
                          <a-select-option :value="47" v-if="datospersonales.tipo_doc !== 1">ECUADOR</a-select-option>
                          <a-select-option :value="26" v-if="datospersonales.tipo_doc !== 1">BRASIL</a-select-option>
                          <a-select-option :value="104" v-if="datospersonales.tipo_doc !== 1">MÉXICO</a-select-option>
                          <a-select-option :value="182" v-if="datospersonales.tipo_doc !== 1">URUGUAY</a-select-option>
                          <a-select-option :value="124" v-if="datospersonales.tipo_doc !== 1">PARAGUAY</a-select-option>
                          <a-select-option :value="128" v-if="datospersonales.tipo_doc !== 1">PUERTO RICO</a-select-option>
                          <a-select-option :value="149" v-if="datospersonales.tipo_doc !== 1">REPUBLICA DOMINICANA</a-select-option>
                      </a-select>
                  </a-form-item>
                </a-col>
                <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                  <a-form-item
                    v-if="datospersonales.tipo_doc === 1"
                    name="dep"
                    :rules="[{ required: true, message: 'Selecciona tu departamento', trigger: 'blur'},]"
                    >
                    <div><label>Departamento:</label></div>
                    <a-auto-complete
                        v-model:value="datosresidencia.dep"
                        :options="departamentos"
                        @select="onSelectDepartamentos"
                    >
                        <a-input
                            placeholder="Departamento"
                            v-model:value="buscarDep"
                            @keypress="handleKeyPress"
                        >
                        <template #suffix>
                        <a-tooltip title="Extra information">
                          <down-outlined/>
                        </a-tooltip>
                      </template>
                      </a-input>
                    </a-auto-complete>
                  </a-form-item>
                </a-col>
                <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                  <a-form-item
                    v-if="datospersonales.tipo_doc === 1"
                    name="prov"
                    :rules="[{ required: true, message: 'Selecciona tu provincia', trigger: 'blur'},]"
                    >
                    <div><label>Provincia:</label></div>
                    <a-auto-complete
                        v-model:value="datosresidencia.prov"
                        :options="provincias"
                        @select="onSelectProvincias"
                    >
                        <a-input
                            placeholder="Provincia"
                            v-model:value="buscarProv"
                            @keypress="handleKeyPress"
                        >
                        <template #suffix>
                        <a-tooltip title="Extra information">
                          <down-outlined />
                        </a-tooltip>
                      </template>
                      </a-input>
                    </a-auto-complete>
                  </a-form-item>
                </a-col>
                <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                  <a-form-item
                    v-if="datospersonales.tipo_doc === 1"
                    name="dist" :rules="[{ required: true, message: 'Selecciona tu distrito', trigger: 'blur'},]" >
                    <div><label>Distrito:</label></div>

                    <a-auto-complete
                        v-model:value="datosresidencia.dist"
                        :options="distritos"
                        @select="onSelectDistritos"
                    >
                        <a-input
                            placeholder="Provincia"
                            v-model:value="buscarDist"
                            @keypress="handleKeyPress"
                        >
                        <template #suffix>
                        <a-tooltip title="Extra information">
                          <down-outlined />
                        </a-tooltip>
                      </template>
                      </a-input>
                    </a-auto-complete>

                  </a-form-item>
                </a-col>
              </a-row>


              <a-row :gutter="[16, 0]" class="form-row">
                <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                  <a-form-item
                    name="direccion"
                    :rules="[{ required: true, message: 'Ingresa tu dirección', trigger: 'blur'},]"
                    >
                    <div><label>Dirección:</label></div>
                    <a-input v-model:value="datosresidencia.direccion">
                      <template #prefix>
                        <sin-icono/>
                      </template>
                    </a-input>
                  </a-form-item>
                </a-col>
              </a-row>

              <a-row :gutter="[16, 0]" class="form-row" style="margin-top: 15px; ">
                <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                  <div class="flex" style="justify-content: space-between;">
                    <a-button v-if="current > 0" @click="prev()" >Anterior</a-button>
                    <a-button v-if="current < 2 " @click="saveDatosPersonales()" type="primary" >Siguiente</a-button>
                  </div>
                </a-col>
              </a-row>

            </a-form>
            </div>

          </a-card>
        </div>
        </div>

        <div v-if="pagina_pre === 3">
          <div>
            <div style="width: 100%; margin-top: 5px;">
                <a-card class="card-datos-colegio" style="min-width: 360px;">
                  <a-form
                      ref="formDatosColegio" :model="datoscolegio"
                      name="form-colegio"
                    >
                        <div>
                          <div style="margin-bottom: 25px; margin-top: 10px; ">
                            <h1 style="font-size: 1.1rem;"> Datos del colegio</h1>
                          </div>

                          <a-row :gutter="[16, 0]" class="form-row">
                            <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                              <a-form-item
                              name="egreso"
                              :rules="[{ required: true, message: 'Ingrese año de egreso', trigger: 'change' },
                              { min: 4, message: 'Debe tener 4 digitos', trigger: 'blur',},]"
                              >
                                <div><label>Año de egreso: </label></div>
                                <a-input v-model:value="datoscolegio.egreso" :maxlength="4" placeholder="Año egreso">
                                  <template #prefix>
                                    <sin-icono/>
                                  </template>
                                </a-input>
                              </a-form-item>
                            </a-col>
                            <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                              <a-form-item
                                name="pais"
                                :rules="[{ required: true, message: 'Seleccione el pain', trigger: 'change' }]"
                              >
                                <div><label>Pais:</label></div>
                                  <a-select
                                      ref="select"
                                      v-model:value="datoscolegio.pais"
                                      style="width: 100%" >
                                      <a-select-option :value="125">PERÚ</a-select-option>
                                      <a-select-option :value="23">BOLIVIA</a-select-option>
                                      <a-select-option :value="11">ARGENTINA</a-select-option>
                                      <a-select-option :value="184">VENEZUELA</a-select-option>
                                      <a-select-option :value="38">COLOMBIA</a-select-option>
                                      <a-select-option :value="35">CHILE</a-select-option>
                                      <a-select-option :value="47">ECUADOR</a-select-option>
                                      <a-select-option :value="26">BRASIL</a-select-option>
                                      <a-select-option :value="104">MÉXICO</a-select-option>
                                      <a-select-option :value="182">URUGUAY</a-select-option>
                                      <a-select-option :value="124">PARAGUAY</a-select-option>
                                      <a-select-option :value="128">PUERTO RICO</a-select-option>
                                      <a-select-option :value="149">REPUBLICA DOMINICANA</a-select-option>
                                  </a-select>
                              </a-form-item>
                            </a-col>
                          </a-row>

                          <a-row :gutter="[16, 0]" class="form-row">
                            <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                              <a-form-item v-if="datoscolegio.pais === 125">
                                <div><label>Departamento: </label></div>

                                <a-auto-complete
                                    v-model:value="datoscolegio.dep"
                                    :options="departamentoscolegio"
                                    @select="onSelectDepartamentosC"
                                    >
                                    <a-input
                                        placeholder="Departamento"
                                        v-model:value="buscarDepC"
                                        @keypress="handleKeyPress"
                                        >
                                    <template #suffix>
                                    <a-tooltip title="Extra information">
                                      <down-outlined />
                                    </a-tooltip>
                                  </template>
                                  </a-input>
                                </a-auto-complete>

                              </a-form-item>
                            </a-col>
                            <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                              <a-form-item v-if="datoscolegio.pais === 125">
                                <div><label>Provincia:</label></div>
                                <a-auto-complete
                                    v-model:value="datoscolegio.prov"
                                    :options="provinciasC"
                                    @select="onSelectProvinciasC"
                                >
                                    <a-input
                                        placeholder="Provincia"
                                        v-model:value="buscarProvC"
                                        @keypress="handleKeyPress"
                                    >
                                    <template #suffix>
                                    <a-tooltip title="Extra information">
                                      <down-outlined />
                                    </a-tooltip>
                                  </template>
                                  </a-input>
                                </a-auto-complete>
                              </a-form-item>
                            </a-col>
                            <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                              <a-form-item v-if="datoscolegio.pais === 125">
                                <div><label>Distrito:</label></div>

                                <a-auto-complete
                                    v-model:value="datoscolegio.dist"
                                    :options="distritosC"
                                    @select="onSelectDistritosC"
                                >
                                    <a-input
                                        placeholder="Provincia"
                                        v-model:value="buscarDistC"
                                        @keypress="handleKeyPress"
                                    >
                                    <template #suffix>
                                    <a-tooltip title="Extra information">
                                      <down-outlined />
                                    </a-tooltip>
                                  </template>
                                  </a-input>
                                </a-auto-complete>
                              </a-form-item>
                            </a-col>
                          </a-row>

                          <a-row :gutter="[16, 0]" class="form-row">
                            <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                              <a-form-item
                              name="id_colegio"
                              v-if="datoscolegio.pais === 125"
                              :rules="[{ required: true, message: 'Seleccione el colegio', trigger: 'change' }]"
                              >
                                <div><label>Nombre de colegio:</label></div>
                                  <a-select
                                    v-model:value="datoscolegio.id_colegio"
                                    style="min-width: 300px"
                                    :options="colegios"
                                    @change="handleChangeCole"
                                  ></a-select>

                              </a-form-item>

                              <a-form-item v-else name="colegio"
                              :rules="[{ required: true, message: 'Seleccione el colegio', trigger: 'change' }]">
                                <div><label>Nombre de colegio:</label></div>
                                <a-select
                                    ref="select"
                                    v-model:value="datoscolegio.colegio"
                                    style="width: 100%" >
                                    <a-select-option :value="200001">COLEGIO EXTRANJERO</a-select-option>
                                </a-select>

                              </a-form-item>
                            </a-col>
                          </a-row>

                        </div>
                  </a-form>
                </a-card>
              </div>
          </div>
        </div>

        <div v-if="pagina_pre === 4">

          <div style="width: 100%; margin-top: 5px; ">

            <a-card style="padding-top: -5px; padding-bottom:0px;" class="cardInicio">
                  <a-form
                    ref="formDatosPadre" :model="datospadre"
                    name="datospadre"
                    @finish="onFinish"
                    @finishFailed="onFinishFailed"
                  >
                    <div>
                      <div style="margin-bottom: 25px; margin-top: 10px;">
                          <h1 style="font-size: 1.1rem;"> Datos del padre</h1>
                      </div>

                      <!-- <a-radio-group v-model:value="datospadre.tipo_apoderado" class="flex justify-end" style="display: flex; width: yellow;" name="radioGroup">
                          <a-radio :value="1">Padre</a-radio>
                      </a-radio-group> -->

                      <a-row :gutter="[16, 0]" class="form-row">
                          <a-col :span="24" :md="26" :lg="12" :xl="12" :xxl="8">
                            <a-form-item name="dni"
                              :rules="[{ required: true, message: 'Ingresa el DNI', trigger: 'change' },
                              { min: 8, message: 'El dni debe tener 8 digitos', trigger: 'blur',},]">
                              <div><label>N° Documento</label></div>
                              <a-input ref="myDni" v-model:value="datospadre.dni" :maxlength="12" placeholder="">
                                <template #prefix>
                                  <sin-icono/>
                                </template>
                              </a-input>
                          </a-form-item>
                          </a-col>
                          <a-col :span="24" :md="24" :lg="12" :xl="12" :xxl="16">
                            <a-form-item name="nombres"
                              :rules="[{ required: true, message: 'Ingresa los nombres', trigger: 'blur' }]"
                              >
                              <div><label>Pre nombres:</label></div>
                              <a-input v-model:value="datospadre.nombres">
                                <template #prefix>
                                  <sin-icono/>
                                </template>
                              </a-input>
                            </a-form-item>
                          </a-col>

                          <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                            <a-form-item
                              name="paterno"
                              :rules="[{ required: true, message: 'Ingresa el primer apellido', trigger: 'blur' }]"
                              >
                              <div><label>Primer apellido:</label></div>
                              <a-input v-model:value="datospadre.paterno">
                                <template #prefix>
                                  <sin-icono/>
                                </template>
                              </a-input>
                          </a-form-item>
                          </a-col>
                      </a-row>

                      <a-row :gutter="[16, 0]" class="form-row">
                          <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                            <a-form-item>
                              <div><label>Segundo Apellido:</label></div>
                              <a-input v-model:value="datospadre.materno">
                                <template #prefix>
                                  <sin-icono/>
                                </template>
                              </a-input>
                          </a-form-item>
                          </a-col>
                      </a-row>

                    </div>

                  </a-form>

                </a-card>

          </div>
        </div>

        <div v-if="pagina_pre === 5">
          <div style="width: 100%; margin-top: 5px; ">

            <a-card style="padding-top: -5px; padding-bottom:0px;" class="cardInicio">
                  <a-form
                    ref="formDatosMadre" :model="datosmadre"
                    name="datosmadre"
                    @finish="onFinish"
                    @finishFailed="onFinishFailed"
                  >
                    <div>
                      <div style="margin-bottom: 25px; margin-top: 10px;">
                          <h1 style="font-size: 1.1rem;" > Datos de la madre</h1>
                      </div>

                      <a-row :gutter="[16, 0]" class="form-row">
                          <a-col :span="24" :md="24" :lg="12" :xl="12" :xxl="8">
                            <a-form-item
                              name="dni"
                              :rules="[{ required: true, message: 'Ingresa el DNI', trigger: 'change' },
                              { min: 8, message: 'El dni debe tener 8 digitos', trigger: 'blur',},]"
                              >
                              <div><label>N° Documento</label></div>
                              <a-input ref="myDni" v-model:value="datosmadre.dni" :maxlength="12" placeholder="">
                                <template #prefix>
                                  <sin-icono/>
                                </template>
                              </a-input>
                          </a-form-item>
                          </a-col>
                          <a-col :span="24" :md="24" :lg="12" :xl="12" :xxl="16">
                            <a-form-item
                              name="nombres"
                              :rules="[{ required: true, message: 'Ingresa los nombres', trigger: 'blur' }]"
                              >
                              <div><label>Pre nombres:</label></div>
                              <a-input v-model:value="datosmadre.nombres">
                                <template #prefix>
                                  <sin-icono/>
                                </template>
                              </a-input>
                            </a-form-item>
                          </a-col>

                          <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                            <a-form-item
                              name="paterno"
                              :rules="[{ required: true, message: 'Ingresa el primer apellido', trigger: 'blur' }]"
                              >
                              <div><label>Primer apellido:</label></div>
                              <a-input v-model:value="datosmadre.paterno">
                                <template #prefix>
                                  <sin-icono/>
                                </template>
                              </a-input>
                          </a-form-item>
                          </a-col>
                      </a-row>

                      <a-row :gutter="[16, 0]" class="form-row">
                          <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                            <a-form-item>
                              <div><label>Segundo Apellido:</label></div>
                              <a-input v-model:value="datosmadre.materno">
                                <template #prefix>
                                  <sin-icono/>
                                </template>
                              </a-input>
                          </a-form-item>
                          </a-col>
                      </a-row>
                    </div>
                  </a-form>
                </a-card>

          </div>
        </div>

        <div v-if="pagina_pre === 6">
          <div style="display:none;">{{  getColegios() }}</div>
          <div style="width: 100%; margin-top: 5px; ">

            <a-card style="padding-top: -5px; max-width:900px; padding-bottom:0px;" class="cardInicio">

              <a-form
                ref="formPreinscripcion" :model="datos_preinscripcion"
                name="preinscripcion"
                @finish="onFinish"
                @finishFailed="onFinishFailed"
              >

                <div>
                  <div>
                  <div class="justify-between datos-postulacion" >
                      <div style="margin-bottom: 25px; margin-top: 10px; ">
                        <h1 style="font-size: 1.1rem;"> Datos Postulación</h1>
                      </div>
                      <div class="mb-3" v-if="datos_preinscripcion.modalidad !== 1">
                        <a-form-item
                          name="modalidad"
                          :rules="[{ required: true, message: 'Seleccine la modalidad', trigger: 'change' },]"
                          >
                          <div><label>Modalidad</label></div>
                          <a-select
                            ref="select"
                            v-model:value="datos_preinscripcion.modalidad"
                            style="width: 230px"
                            :options="modalidades"
                            @focus="focus"
                            @change="handleChange"
                          ></a-select>
                        </a-form-item>
                      </div>
                    </div>
                  </div>

                  <!-- v-if="props.procceso_seleccionado.id_modalidad_proceso === 3" -->
                  <div style="margin-top:-20px; margin-bottom:20px;" v-if="props.procceso_seleccionado.id_modalidad_proceso === 3 && datos_preinscripcion.modalidad === 2">
                    <a-alert
                      description="¡MUY IMPORTANTE! El postulante debe elegir un programa de estudios que pertenezca al mismo área del programa que está cursando actualmente."
                      type="info"
                    />
                  </div>

                  <div style="margin-top:-20px; margin-bottom:20px;" v-if="props.procceso_seleccionado.id_modalidad_proceso === 3 && datos_preinscripcion.modalidad === 3">
                    <a-alert description="¡MUY IMPORTANTE! El postulante debe ingresar los datos de la carrera o programa de estudio y la universidad de la que proviene." type="info"/>
                  </div>

                  <a-row :gutter="[16, 0]" class="form-row">

                    <a-col v-if="datos_preinscripcion.modalidad == 1" :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                      <a-form-item
                          name="modalidad"
                          :rules="[{ required: true, message: 'Seleccine la modalidad', trigger: 'change' },]"
                          >
                          <div><label>Modalidad</label></div>
                          <a-select
                            ref="select"
                            v-model:value="datos_preinscripcion.modalidad"
                            style="width: 100%"
                            :options="modalidades"
                            @focus="focus"
                            @change="handleChange"
                          ></a-select>
                        </a-form-item>
                    </a-col>



                    <a-col :span="24">
                      <a-row :gutter="16" style="display:fleX; justify-content:center;">
                          <a-col v-for="item in carreras_previas" :key="item" :xs="24" :sm="24" :md="24" :lg="24"
                              style="margin-bottom: 10px;"
                          >
                              <div
                                  @click="toggleSelection2(item)"
                                  :class="{ 'selected': item.selected }"
                                  style="height:80px; border-radius:5px; cursor:pointer; border:solid 1px #d9d9d9; align-items: center; "
                                  class="flex p-4">
                                  <div style="display:flex; justify-content: space-between; width: 100%; align-items: center;">
                                      <div style="width: calc(100% - 50px);">
                                        <div>
                                          <span style="font-size:.8rem; text-transform: capitalize;">{{ item.nombre }}</span>
                                        </div>
                                        <div class="flex justify-left">
                                          <span style="font-weight:bold; font-size:.8rem">cod: {{ item.codigo }}</span>
                                        </div>
                                      </div>
                                      <div class="flex justify-center" style="width: 50px; height: 50px; align-items: center;">
                                          <img src="../../../assets/imagenes/logotiny.png" width="45px"/>
                                      </div>
                                  </div>
                              </div>
                          </a-col>
                      </a-row>
                  </a-col>

                    <a-col v-if="datos_preinscripcion.modalidad == 2" :span="24" :md="24" :lg="12" :xl="24" :xxl="24">
                      <a-form-item
                        name="observacion"
                        :rules="[{ required: true, message: 'Código requerido', trigger: 'change' },]"
                      >
                        <div><label>Código de matricula</label></div>
                        <a-input placeholder="Código de matrícula actual" v-model:value="datos_preinscripcion.observacion">
                          <template #prefix>
                            <sin-icono/>
                          </template>
                        </a-input>
                      </a-form-item>
                    </a-col>

                    <a-col :span="24" :md="24" :lg="12" :xl="24" :xxl="24">
                      <a-form-item
                      name="programa"
                      :rules="[{ required: true, message: 'Seleccine la modalidad', trigger: 'change' },]"
                      >
                        <div><label>Programa de estudios</label></div>
                        <a-select
                          ref="select"
                          v-model:value="datos_preinscripcion.programa"
                          style="width: 100%"
                          :options="programas"
                          @focus="focus"
                          @change="handleChange"
                        ></a-select>
                      </a-form-item>
                    </a-col>

                    <a-col v-if="datos_preinscripcion.programa === 38 || datos_preinscripcion.programa === 16" :span="24" :md="24" :lg="12" :xl="24" :xxl="24">
                      <a-form-item>
                        <div><label>Cod. de constancia de examen médico:</label></div>
                        <a-input placeholder="Cod Examen médico" v-model:value="datos_preinscripcion.codigo_medico">
                          <template #prefix>
                            <sin-icono/>
                          </template>
                        </a-input>
                      </a-form-item>
                    </a-col>
                    <a-col :span="24" :md="24" :lg="24" :xl="12" :xxl="12" v-if="![2, 3, 1].includes(datos_preinscripcion.modalidad)">
                      <a-form-item
                      name="tipo_certificado"
                      :rules="[{ required: true, message: 'Seleccine el tipo de certificado', trigger: 'change' },]"
                      >
                        <div class="flex justify-between" style="font-weight:bold;"><label>Tipo certificado:</label></div>
                        <a-select
                            ref="select"
                            v-model:value="datos_preinscripcion.tipo_certificado"
                            placeholder="Seleccionar tipo de certificado"
                            class="selector-modalidad" >
                            <a-select-option value='CERTIFICADO BLANCO'>CERTIFICADO BLANCO</a-select-option>
                            <a-select-option value='CERTIFICADO AMARILLO'>CERTIFICADO AMARILLO</a-select-option>
                            <a-select-option value='CONSTANCIA DE ESTUDIOS'>CONSTANCIA DE ESTUDIOS</a-select-option>
                        </a-select>
                      </a-form-item>
                    </a-col>
                    <a-col :span="24" :md="24" :lg="24" :xl="12" :xxl="12" v-if="![2, 3, 1].includes(datos_preinscripcion.modalidad)">
                      <a-form-item
                      name="codigo_certificado"
                      :rules="[{ required: true, message: 'Ingrese el cod. de certificado', trigger: 'change' },]"
                      >
                        <div class="flex justify-between"><label>Codigo certificado</label> <span @click="ejemplo = true" style="color:blue; cursor:pointer;">ver ejemplo</span></div>
                        <a-input v-model:value="datos_preinscripcion.codigo_certificado">
                          <template #prefix>
                            <sin-icono/>
                          </template>
                        </a-input>
                      </a-form-item>
                    </a-col>
                  </a-row>

                </div>
              </a-form>
            </a-card>
            </div>
        </div>

        <div v-if="pagina_pre === 7 || postulante_inscrito === 1">
          <div style="width: 100%; height:calc(100vh - 300px); display:flex; align-items:center; ">
            <a-card style="padding-top: 5px; padding-bottom:0px; background: #00808025;">
              <div class="">
                <div class="flex justify-center">
                  <div  style="text-align:center; max-width: 300px;" >
                    <div>COMPLETÓ SU PREINSCRIPCIÓN Y ESTÁ LISTO PARA SU INSCRIPCIÓN PRESENCIAL</div>
                  </div>
                </div>

                <div class="justify-center mt-4 mb-4">
                  <div class="flex justify-center mt-4 mb-4 mr-2">
                    <a-button @click="getDocs()" class="custom-button2" shape="round">
                        <template #icon>
                          <div class="flex custom-icon2">
                            <div class="mr-2"><img src="../../../assets/imagenes/iconos/icono-descargar.png" alt="icono descarga" class="icono-img"></div>
                            <div class="icono-text2">DESCARGAR SOLICITUD</div>
                          </div>
                        </template>
                      </a-button>
                  </div>

                  <div class="flex justify-center mt-4 mb-4 mr-2">
                    <a-button @click="descargaReglamento()" class="custom-button" shape="round">
                            <div>DESCARGAR REGLAMENTO</div>
                      </a-button>
                  </div>
                  <div class="flex justify-center mt-4 mb-4 mr-2">
                    <a-button @click="irDiagnostico()" class="custom-button" shape="round">
                            <div>TEST DIAGNÓSTICO</div>
                      </a-button>
                  </div>
                </div>

              </div>
            </a-card>
            </div>
        </div>

        </div>
      </div>
      <div>

      </div>
      <a-affix v-if="pagina_pre !== 0" :offset-bottom="bottom" style="margin-top: 0px;">
        <div v-if="pagina_pre !== 8" style="background: none;">
          <a-progress :percent="avance" width="90%" :format="percent => avance+'%'" stroke-color="teal"/>
        </div>

        <div class="flex" style="justify-content: space-between; background:none;" v-if="pagina_pre === 1">
          <a-button @click="prev()" class="boton-anterior">Anterior</a-button>
          <a-button @click="saveDatosPersonales()" class="boton-siguiente" style="color:white;" >Siguiente</a-button>
        </div>

        <div class="flex" style="justify-content: space-between; background:none;" v-if="pagina_pre === 2">
            <a-button @click="prev()" class="boton-anterior">Anterior</a-button>
            <a-button @click="saveDatosResidencia()" class="boton-siguiente" style="color:white;">Siguiente</a-button>
        </div>

        <div class="flex" style="justify-content: space-between; background:none;" v-if="pagina_pre === 3">
          <a-button @click="prev()" class="boton-anterior">Anterior</a-button>
          <a-button @click="savecolegio()" class="boton-siguiente" style="color:white;">Siguiente</a-button>
        </div>

        <div class="flex" style="justify-content: space-between; background:none;" v-if="pagina_pre === 4">
          <a-button @click="prev()" class="boton-anterior">Anterior</a-button>
          <a-button @click="saveApoderado()" class="boton-siguiente" style="color:white;">Siguiente</a-button>
        </div>
        <div class="flex" style="justify-content: space-between; background:none;" v-if="pagina_pre === 5">
          <a-button @click="prev()" class="boton-anterior">Anterior</a-button>
          <a-button @click="saveApoderadoM()" class="boton-siguiente" style="color:white;">Siguiente</a-button>
        </div>
        <div class="flex" style="justify-content: space-between; background:none;" v-if="pagina_pre === 6">
          <a-button @click="prev()" class="boton-anterior">Anterior</a-button>

          <a-button @click="abrirModalDatos()" class="boton-siguiente" style="color:white;">
            Verificar Datos
          </a-button>

        </div>
      </a-affix>


  <a-modal v-model:visible="modalcarrerasprevias" centered :keyboard="false" :footer="false" :closable="false" :maskClosable="false" >
    <div v-if="loading === true" class="flex justify-center" style="min-height:300px; align-items:center;">
      <div>
        <div class="flex justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-loader rotating-svg">
            <line x1="12" y1="2" x2="12" y2="6"></line>
            <line x1="12" y1="18" x2="12" y2="22"></line>
            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
            <line x1="2" y1="12" x2="6" y2="12"></line>
            <line x1="18" y1="12" x2="22" y2="12"></line>
            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
          </svg>
        </div>
        <div class="flex justify-center mt-3">
          <div style="text-align: center;">
            <div><span style="font-size:1.3rem;">Estamos revisando su información</span></div>
            <div><span style="font-size:1rem;">Tomará poco tiempo</span></div>
            <div><span></span></div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading === false"  class="flex justify-center" style="">

      <div v-if="modalSancionado === true" :class="modalsancionado === true ? 'aparecer-div':'aparecer-div-mostrar'">
          <div class="flex justify-center mt-6">
            <img src="../../../assets/imagenes/alert.png" width="125"/>
          </div>
          <div class="mt-4">
            <h2 class="text-center" style="font-size:1.4rem;">¡Importante!</h2>
            <p class="text-center mx-4" style="font-size:1.1rem;">El participante no reúne las condiciones para participar en este proceso.</p>
          </div>

          <div class="flex justify-center">
            <a-button @click="cancelarInscripcion()" style="background:teal; color:white; font-weight:bold; height:40px; width:110px; border-radius:8px; border:none;">
              Aceptar
            </a-button>
          </div>
      </div>
      <div v-else>
        <div v-if="anteriores.length >= 2" style="width: 100%; max-width: 1000px; margin-top:20px;">
          <div class="mb-4">
            <div class="flex justify-center"><span>Estimado postulante se ha detectado Que Ud. Tiene ingresos previos</span></div>
            <div class="mt-3 flex justify-left"><span>Se ha verificado que usted registra dos o más ingresos previos a nuestra universidad.
De acuerdo con las normativas vigentes, no es posible continuar con una nueva postulación.</span></div>
          </div>
          <a-row style="display:flex; justify-content:center;" class="pb-0">
              <a-col :span="24">
                  <a-row :gutter="16" style="display:fleX; justify-content:center;">
                      <a-col v-for="item in anteriores" :key="item" :xs="24" :sm="24" :md="24" :lg="24"
                          style="margin-bottom: 10px;"
                      >
                          <div
                              @click="toggleSelection(item)"
                              :class="{ 'selected': item.selected }"
                              style="height:80px; border-radius:5px; cursor:pointer; border:solid 1px #d9d9d9; align-items: center; "
                              class="flex p-4">
                              <div style="display:flex; justify-content: space-between; width: 100%; align-items: center;">
                                  <div style="width: calc(100% - 50px);">
                                    <div>
                                      <span style="font-size:.8rem; text-transform: capitalize;">{{ item.career }}</span>
                                    </div>
                                    <div class="flex justify-left">
                                      <span style="font-weight:bold; font-size:.8rem">cod: {{ item.code }}</span>
                                    </div>
                                  </div>
                                  <div class="flex justify-center" style="width: 50px; height: 50px; align-items: center;">
                                      <img src="../../../assets/imagenes/logotiny.png" width="45px"/>
                                  </div>
                              </div>
                          </div>
                      </a-col>
                  </a-row>
              </a-col>
          </a-row>
          <div class="my-2 mb-4">
            <a-alert message="Si no reconoce haber ingresado a ninguna de esas carreras presione en CANCELAR y aproximese a la DIRECCIÓN DE ADMISIÓN de la UNA PUNO" type="warning" show-icon />
          </div>
          <div class="flex justify-center" v-if="confirmacion === true">
            <div>
              <div class="mt-4">
                <span> Se ha registrado su información</span>
              </div>
              <div class="flex justify-center mt-6">
                  <a-button style="background: teal; border:none; color:white; width:120px;" @click="modalcarrerasprevias = false">Continuar</a-button>
              </div>
            </div>
          </div>
          <div>

            <div class="flex justify-end mt-6 mb-3">
              <a-button @click="cancelarInscripcion()" class="mr-2" style="color: teal; border: 1px solid teal; border-radius:5px;">Cancelar</a-button>
              <div v-if="selectedItems">
                <a-button v-if="anteriores.length >= 2" disabled style=" border: 1px solid gray; border-radius:5px;">Continuar</a-button>
                <a-button v-if="anteriores.length == []" @click="registrarPrevias()" style="color: white; background: #476175; border: 1px solid #476175; border-radius:5px;">Continuar</a-button>
              </div>
            </div>
          </div>
      </div>

      <div v-else>
        <div v-if="props.procceso_seleccionado.id_modalidad_proceso !== 2 && anteriores.length === 0 " style="width: 100%; max-width: 1000px; margin-top:20px;">
          <div class="flex justify-center">
            <div>
              <div class="mt-0 mb-3 flex justify-center" style="text-align:center;">
                <div><span style="font-size:1.4rem;">
                  VERIFICACIÓN FINALIZADA
                </span></div>
              </div>
              <div class="mt-3 mb-3 flex justify-center" style="text-align:justify;">
                <div><span style="font-size:1rem;">
                  Hemos revisado tu información y cumples con los requisitos
                  para postular.
                  Para continuar con el proceso de postulación, sigue estos pasos:</span></div>
              </div>

              <div>
                <div> 1. Presiona en "CONTINUAR". </div>
                <div> 2. Ingresa el código secreto proporcionado. </div>
                <div> 3. Presiona en "Iniciar Postulación". </div>
              </div>

              <div class="mt-4 mb-4">
                <a-alert message="!Importante! los datos registrados anteriormente se cargarán automáticamente" type="info" show-icon />
              </div>
              <div class="flex justify-center mt-4">
                  <a-button type="" style=" border:none; background: teal; color:white; width:120px; height:42px;" @click="modalcarrerasprevias = false">Continuar</a-button>
              </div>
            </div>
          </div>
          </div>

          <div v-if="props.procceso_seleccionado.id_modalidad_proceso === 2 && datacepre.nro_documento" style="width: 100%; max-width: 1000px; margin-top:20px;">

            <div class="flex justify-center">
              <div>
                <div class="mt-0 mb-3 flex justify-center" style="text-align:center;">
                  <div><span style="font-size:1.4rem;">
                    VERIFICACIÓN FINALIZADA
                  </span></div>
                </div>
                <div class="mt-3 mb-3 flex justify-center" style="text-align:justify;">
                  <div><span style="font-size:1rem;">
                    Hemos revisado tu información y cumples con los requisitos
                    para postular.
                    Para continuar con el proceso de postulación, sigue estos pasos:</span></div>
                </div>

                <div>
                  <div> 1. Presiona en "CONTINUAR". </div>
                  <div> 2. Ingresa el código secreto proporcionado. </div>
                  <div> 3. Presiona en "Iniciar Postulación". </div>
                </div>

                <div class="mt-4 mb-4">
                  <a-alert message="!Importante! los datos registrados anteriormente se cargarán automáticamente" type="info" show-icon />
                </div>
                <div class="flex justify-center mt-4">
                    <a-button type="primary" style="border:none; background: teal; color:white; width:120px; height:42px;" @click="modalcarrerasprevias = false">Continuar</a-button>
                </div>
              </div>
            </div>
        </div>
      </div>

      </div>



  </div>
  </a-modal>
</Layout>

</template>
<script setup>
import Layout from '@/Layouts/LayoutPreinscripcion.vue'
import { defineProps, watch, reactive, ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { format, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';
import { notification } from 'ant-design-vue';
import { DownOutlined } from '@ant-design/icons-vue';

const loading = ref(true);
const modalcepreaviso = ref(false);
const modalUbigeo = ref(false);
const nombrecolegiox = ref("");
const ejemplo = ref(false);
const modalDatos = ref(true);
const open = ref(false);
const abrirModalDatos = async () => {
  const values = await formPreinscripcion.value.validateFields();
  open.value = true
  cambiarFormato()
  getUbigeo();
  getApoderado()
  getApoderadoM()
  getColegios()
  getColegioSeleccionado()
}

const checkbox1 = ref(false)
const activeKey = ref('1');

const examen = ref(0);
const avance = ref(0)
const bottom = ref(2)

const pagina_pre = ref(0)
const next = () => { pagina_pre.value++; }
const prev = () => { pagina_pre.value--; }
const dni = ref("")

const formRef = ref();
const formState = reactive({ dni: '', codigo_secreto: '', });

const formDatosPersonales = ref();
const datospersonales = reactive({
  id: null,
  tipo_doc:1,
  primerapellido: "",
  segundo_apellido: "",
  nombres:"",
  estado_civil:null,
  sexo:null,
  correo:"",
  celular:'',
  fec_nacimiento:"",
  ubigeo:"",
  ubigeo_residencia:"",
});

const formDatosResidencia = ref();
const datosresidencia = reactive({
  pais:125,
  dep: null,
  prov: null,
  dist: null,
  direccion:''
});

const formDatosColegio = ref();
const datoscolegio = reactive({
  id_colegio:null,
  egreso: null,
  pais: 125,
  dep: null,
  prov: null,
  dist: null,
  colegio:'',
});

const formDatosPadre = ref();
const datospadre = reactive({
  id:null,
  tipo_apoderado: 1,
  dni: null,
  nombres: null,
  paterno: null,
  materno: null,
});

const formDatosMadre = ref();
const datosmadre = reactive({
  tipo_apoderado: 2,
  dni: null,
  nombres: null,
  paterno: null,
  materno: null,
});

const formPreinscripcion = ref();
const datos_preinscripcion = reactive({
  modalidad: null,
  programa:null,
  tipo_certificado:null,
  codigo_medico: null,
  codigo_certificado:null,
  observacion: null
})

const dniInput = (event) => { formState.dni = event.target.value.replace(/\D/g, ''); };
const ubigeoInput = (event) => { formState.ubigeo = event.target.value.replace(/\D/g, ''); };
const nombresInput = (event) => { datospersonales.nombres = event.target.value.replace(/[^A-Za-zÑñ\s]/g, '');};
const pimerapellidoInput = (event) => { datospersonales.primerapellido = event.target.value.replace(/[^A-Za-zÑñ]/g, '');};
const celularInput = (event) => { datospersonales.celular = event.target.value.replace(/\D/g, ''); };

const departamentos = ref([])
const departamentoscolegio = ref([])
const buscarDep = ref("")
const buscarDepC = ref("")
const depseleccionado = ref('')
const depseleccionadoC = ref('')
watch(buscarDep, ( newValue, oldValue ) => { getDepartamentos() })
watch(buscarDepC, ( newValue, oldValue ) => { getDepartamentosColegio() })
const onSelectDepartamentos = (value, option) => {
    depseleccionado.value = option.key;
    getProvincias();
};

const onSelectDepartamentosC = (value, option) => {
    depseleccionadoC.value = option.key;
    getProvinciasColegio();
    provseleccionadaC.value = null;
    datoscolegio.prov = null;
    datoscolegio.dist = null;
    distritosC.value = [];
    datoscolegio.colegio = null;
    colegios.value = [];
};

const provincias = ref([])
const provinciasC = ref([])
const buscarProv = ref("")
const buscarProvC = ref("")
const provseleccionada = ref(null)
const provseleccionadaC = ref(null)
watch(buscarProv, ( newValue, oldValue ) => { getProvincias() })
watch(buscarProvC, ( newValue, oldValue ) => { getProvinciasColegio() })
const onSelectProvincias = (value, option) => {
    provseleccionada.value = option.key;
    getDistritos();
};
const onSelectProvinciasC = (value, option) => {
    provseleccionadaC.value = option.key;
    datoscolegio.dist = null;
    getDistritosColegio();
    datoscolegio.colegio = null;
    colegios.value = [];
};

const distritos = ref([])
const distritosC = ref([])
const buscarDist = ref("")
const buscarDistC = ref("")
const distseleccionado = ref('')
const distseleccionadoC = ref('')
const onSelectDistritos = (value, option) => { distseleccionado.value = option.key;  };
const onSelectDistritosC = (value, option) => { distseleccionadoC.value = option.key; datoscolegio.colegio = null; getColegios(); };
const colegios = ref([]);

const getPadreApi = () => {
  const token = '70ab1cd1b9b452982370381fd0be605c85ddc8795aed972afca143fee05fde43';

  axios.get(`https://apiperu.dev/api/dni/${datospadre.dni}`, {
    headers: {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
  })
  .then(response => {
    const data = response.data;
    datospadre.paterno = data.data.apellido_paterno
    datospadre.materno = data.data.apellido_materno
    datospadre.nombres = data.data.nombres
  })
  .catch(error => {
    console.error(error);
  });
};

const getMadreApi = () => {
  const token = '70ab1cd1b9b452982370381fd0be605c85ddc8795aed972afca143fee05fde43';

  axios.get(`https://apiperu.dev/api/dni/${datosmadre.dni}`, {
    headers: {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
  })
  .then(response => {
    const data = response.data;
    datosmadre.paterno = data.data.apellido_paterno
    datosmadre.materno = data.data.apellido_materno
    datosmadre.nombres = data.data.nombres
  })
  .catch(error => {
    console.error(error);
  });
};

const desactivar = () => {
  modalcarrerasprevias.value = false;
  window.location.reload();
}



watch(() => datospadre.dni, (newValue, oldValue) => {
  if(newValue.length == 8){
    getApoderadoDNI(1);
  }
});
watch(() => datosmadre.dni, (newValue, oldValue) => {
  if(newValue.length == 8){
    getApoderadoDNI(2);
  }
});

watch(() => datos_preinscripcion.tipo_certificado, (newValue, oldValue) => {
  if(newValue === 'CERTIFICADO AMARILLO'){ activeKey.value = "1"; }
  if(newValue === 'CERTIFICADO BLANCO'){ activeKey.value = "2";  }
  if(newValue === 'CONSTANCIA DE ESTUDIOS'){ activeKey.value = "3"; }
  ejemplo.value = true;
});



watch(() => formState.dni, (newValue, oldValue) => {
  if(newValue){
    if(newValue.length == 8){
      getPasoRegistrado();
      if(selectedItems){selectedItems.value = [];}
      datacepre.value = [];
      anteriores.value = [];
    }
  }

});
const traslado_interno = ref(false);
const modalidades = ref([]);

const getModalidades =  async () => {
  let res = await axios.get( "/get-select-modalidad-proceso/"+ props.procceso_seleccionado.id);
  modalidades.value = res.data.datos;
}


watch(() => datos_preinscripcion.modalidad, (newValue, oldValue) => {
  datos_preinscripcion.programa = null;
  getProgramas();
});

watch(() => datos_preinscripcion.observacion, (newValue, oldValue) => {
  datos_preinscripcion.programa = null;
  if(newValue.length == 6 || newValue.length == 0){
    getAreaCodigo()
  }

});


const temp_area = ref(null);
const getAreaCodigo =  async () => {
  let res = await axios.get( "/get-area-by-codigo/"+datos_preinscripcion.observacion);
  temp_area.value = res.data.datos.area;
  await getProgramasArea();
}


const getProgramasArea =  async () => {
  let res = await axios.post( "/get-select-programas-proceso-area",{ "id_modalidad": datos_preinscripcion.modalidad, "id_proceso": props.procceso_seleccionado.id, "area": temp_area.value });
  programas.value = res.data.datos;
}

const programas = ref([]);
const getProgramas =  async () => {
  let res = await axios.post( "/get-select-programas-proceso",{ "id_modalidad": datos_preinscripcion.modalidad, "id_proceso": props.procceso_seleccionado.id });
  programas.value = res.data.datos;
}

const participa = ref(0);
const getDatosApi = () => {
  const token = '70ab1cd1b9b452982370381fd0be605c85ddc8795aed972afca143fee05fde43';
  axios.get(`https://apiperu.dev/api/dni/${formState.dni}`, {
    headers: {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
  })
  .then(response => {
    const data = response.data;
    datospersonales.primerapellido = data.data.apellido_paterno
    datospersonales.segundo_apellido = data.data.apellido_materno
    datospersonales.nombres = data.data.nombres
  })
  .catch(error => {
    console.error(error);
  });

  saveDNI();

};

const getDatosPersonales = async () => {
    if(pagina_pre.value == 0 ){
      const values = await formRef.value.validateFields();
    }

    let res = await axios.post( "/get-postulante-datos-personales", {nro_doc: formState.dni, ubigeo: formState.ubigeo});
      if (res.data.datos && res.data.datos.length > 0) {
        datospersonales.id = res.data.datos[0].id
        datospersonales.primerapellido = res.data.datos[0].primer_apellido
        datospersonales.segundo_apellido = res.data.datos[0].segundo_apellido
        datospersonales.nombres = res.data.datos[0].nombres
        datospersonales.estado_civil = res.data.datos[0].estado_civil
        datospersonales.sexo = res.data.datos[0].sexo
        datospersonales.correo = res.data.datos[0].correo
        datospersonales.celular = res.data.datos[0].celular
        if(res.data.datos[0].fec_nacimiento){ datospersonales.fec_nacimiento = dayjs(res.data.datos[0].fec_nacimiento) }
        datospersonales.ubigeo = res.data.datos[0].ubigeo
        datosresidencia.direccion = res.data.datos[0].direccion
        depseleccionado.value = res.data.datos[0].dep;
        datosresidencia.dep = res.data.datos[0].departamento
        provseleccionada.value = res.data.datos[0].prov;
        datosresidencia.prov = res.data.datos[0].provincia
        distseleccionado.value = res.data.datos[0].dist;
        datosresidencia.dist = res.data.datos[0].distrito
        datosresidencia.pais = res.data.datos[0].id_pais
        datospersonales.ubigeo_residencia = res.data.datos[0].ubigeo_residencia
        datospersonales.ubigeo = res.data.datos[0].ubigeo
        datosresidencia.direccion = res.data.datos[0].direccion
        pagina_pre.value = 1;
      }
      else {
        getDatosApi();
        pagina_pre.value = 1;
      }
}


const getDatosPersonales2 = async () => {

  let res = await axios.post( "/get-postulante-datos-personales2", {nro_doc: formState.dni});
    if(res.data.datos.length > 0) {
      datospersonales.id = res.data.datos[0].id
      datospersonales.tipo_doc = res.data.datos[0].tipo_doc
      datospersonales.primerapellido = res.data.datos[0].primer_apellido
      datospersonales.segundo_apellido = res.data.datos[0].segundo_apellido
      datospersonales.nombres = res.data.datos[0].nombres
      datospersonales.estado_civil = res.data.datos[0].estado_civil
      datospersonales.sexo = res.data.datos[0].sexo
      datospersonales.correo = res.data.datos[0].correo
      datospersonales.celular = res.data.datos[0].celular
      if(res.data.datos[0].fec_nacimiento){ datospersonales.fec_nacimiento = dayjs(res.data.datos[0].fec_nacimiento) }
      formState.ubigeo = res.data.datos[0].ubigeo
      datosresidencia.direccion = res.data.datos[0].direccion
      depseleccionado.value = res.data.datos[0].dep;
      datosresidencia.dep = res.data.datos[0].departamento
      provseleccionada.value = res.data.datos[0].prov;
      datosresidencia.prov = res.data.datos[0].provincia
      distseleccionado.value = res.data.datos[0].dist;
      datosresidencia.pais = res.data.datos[0].id_pais;
      datosresidencia.dist = res.data.datos[0].distrito
      datospersonales.ubigeo_residencia = res.data.datos[0].ubigeo_residencia
      datospersonales.ubigeo = res.data.datos[0].ubigeo
      datosresidencia.direccion = res.data.datos[0].direccion
    }

}

const ultimopaso = ref(null);
const getPasoRegistrado = async () => {
  ultimopaso.value = null;
  let res = await axios.get( "/get-paso-registrado/"+props.procceso_seleccionado.id+"/"+formState.dni);
  if(res.data.estado == true){
    ultimopaso.value = res.data.datos
    await getDatosPersonales2()
    if(res.data.datos.nro == 6){
      consultaInscripcion2()
    }else{
      pagina_pre.value = res.data.datos.nro;
    }
  }
  else{
    modalcarrerasprevias.value = true;
    loading.value = true;
    getSancionado();
  }
}


const savePasos =  async (namex, num, avan ) => {
  let res = await axios.post(
    "/save-pasos-preinscripcion",
    {
      id: id_pasos.value,
      nombre: namex,
      nro: num,
      avance: avan,
      postulante: datospersonales.id,
      proceso: props.procceso_seleccionado.id
    }
  );
  getPasos()
}

const saveDNI =  async () => {

  let res = await axios.post( "/save-postulante-dni", {
    tipo_doc: datospersonales.tipo_doc,
    nro_doc: formState.dni,
    ubigeo_nacimiento: formState.ubigeo,
    paterno: datospersonales.primerapellido,
    materno:datospersonales.segundo_apellido,
    nombres: datospersonales.nombres,
  });
  getDatosPersonales2()
}

const saveDatosPersonales =  async () => {
  const values = await formDatosPersonales.value.validateFields();
  let res = await axios.post(
    "/save-postulante",
    {
      tipo_doc: datospersonales.tipo_doc,
      nro_doc: formState.dni,
      id: datospersonales.id,
      primer_apellido: datospersonales.primerapellido,
      segundo_apellido: datospersonales.segundo_apellido,
      nombres: datospersonales.nombres,
      correo: datospersonales.correo,
      celular: datospersonales.celular,
      sexo: datospersonales.sexo,
      estado_civil: datospersonales.estado_civil,
      fec_nacimiento: format(new Date(datospersonales.fec_nacimiento), 'yyyy-MM-dd'),
      ubigeo_nacimiento: datospersonales.ubigeo,
      //ubigeo_residencia: datospersonales.ubigeo_residencia,
      direccion: datosresidencia.direccion
    }
  );
  if( avance_current.value < 16){ savePasos("Registro de datos personales", 1, 16) } else{ next() }
  if(res.data.estado === true ){
    notificacion(res.data.tipo, res.data.titulo, res.data.mensaje)
  }
}

const saveDatosResidencia =  async () => {
  const values = await formDatosResidencia.value.validateFields();
  if(depseleccionado.value !== null && provseleccionada.value !== null && distseleccionado.value !== null ) {
    datospersonales.ubigeo_residencia = depseleccionado.value + provseleccionada.value + distseleccionado.value
  }

  let res = await axios.post(
    "/save-postulante-residencia",
    {
      id: datospersonales.id,
      ubigeo_residencia: datospersonales.ubigeo_residencia,
      direccion: datosresidencia.direccion
    }
  );
  if(res.data.estado === true ){
    notificacion(res.data.tipo, res.data.titulo, res.data.mensaje)
  }
  if( avance_current.value < 32){ savePasos("Registro de datos de residencia", 2, 32) } else{ next() }
}


watch(pagina_pre, ( newValue, oldValue ) => {

  if(pagina_pre === 2 ){
    getDatosPersonales();
    getDepartamentos();
  }
  if(newValue === 3 ){
      getDepartamentosColegio();
      getUbigeo();
  }
  if(newValue === 4 ){
    getApoderado();

  }
  if(newValue === 5 ){
    if( datospersonales.id ){
      getApoderadoM();
    }
  }
  if(newValue === 6){
    getModalidades();
  }
})

const getDepartamentos = async () => {
  let res = await axios.post( "/get-departamentos-codigo?page=", { term: buscarDep.value});
  departamentos.value = res.data.datos.data
}
const getDepartamentosColegio = async () => {
  let res = await axios.post( "/get-departamentos-codigo?page=", { term: buscarDepC.value});
  departamentoscolegio.value = res.data.datos.data
}
const getProvincias = async (depp) => {
  let res = await axios.post( "/get-provincias-codigo?page=", {departamento: depseleccionado.value });
  provincias.value = res.data.datos
}
const getProvinciasColegio = async (depp) => {
  let res = await axios.post( "/get-provincias-codigo?page=", {departamento: depseleccionadoC.value });
  provinciasC.value = res.data.datos
}
const getDistritos = async (depp) => {
  let res = await axios.post( "/get-distritos-codigo?page=",
    { departamento: depseleccionado.value, provincia: provseleccionada.value }
  );
  distritos.value = res.data.datos
}
const getDistritosColegio = async (depp) => {
  let res = await axios.post( "/get-distritos-codigo?page=",  {  departamento: depseleccionadoC.value, provincia: provseleccionadaC.value });
  distritosC.value = res.data.datos
}
const getColegios = async () => {
  const res = await axios.post("/get-colegio-distrito", {  ubigeo_cole: depseleccionadoC.value + provseleccionadaC.value + distseleccionadoC.value });
  colegios.value = res.data.datos;
}

const getApoderadoDNI = async (tipo) => {

  if(tipo == 1){
    let res = await axios.post( "/get-apoderado-dni", {dni: datospadre.dni });
    if (res.data.estado === true ){
      datospadre.dni = res.data.datos.dni
      datospadre.nombres = res.data.datos.nombres
      datospadre.paterno = res.data.datos.paterno
      datospadre.materno = res.data.datos.materno
    } else{
      getpadreApi()
    }
  }else{
    let res = await axios.post( "/get-apoderado-dni", {dni: datosmadre.dni });
    if (res.data.estado === true ){
      datosmadre.dni = res.data.datos.dni
      datosmadre.nombres = res.data.datos.nombres
      datosmadre.paterno = res.data.datos.paterno
      datosmadre.materno = res.data.datos.materno
    } else{
      getmadreApi()
    }
  }
}
const getUbigeo = async () => {
  const res = await axios.post("/get-ubigeo-colegio", { id_postulante: datospersonales.id });
  console.log(":::", res.data);
  if(res.data){
    datoscolegio.egreso = res.data[0].egreso;
    datoscolegio.id_colegio = res.data[0].value;
    nombrecolegiox.value = res.data[0].label;
    datoscolegio.dep = res.data[0].departamento;
    depseleccionadoC.value = res.data[0].dep;
    datoscolegio.prov = res.data[0].provincia;
    provseleccionadaC.value = res.data[0].prov;
    datoscolegio.dist = res.data[0].distrito;
    distseleccionadoC.value = res.data[0].dist;
  }
}

const savecolegio = async () => {
  const values = await formDatosColegio.value.validateFields();
  let ac = 'si';
  if (avance_current.value >= 48){ ac = 'no'; }
  try {
    const response = await axios.post('/save-postulante-colegio', {
      id:  datospersonales.id,
      anio_egreso: datoscolegio.egreso,
      colegio: datoscolegio.id_colegio,
      actualizar: ac,
      proceso: props.procceso_seleccionado.id
    },);
    getPasos()
  } catch (error) {
    console.error(error);
  }
}

function validateFechaNacimiento(rule, value) {
  return new Promise((resolve, reject) => {
    if (!value) {
      reject(new Error('Por favor, selecciona tu fecha de nacimiento.'));
    } else {
      const fechaNacimiento = new Date(value);
      const fechaMinima = new Date();
      fechaMinima.setFullYear(fechaMinima.getFullYear() - 16);

      if (fechaNacimiento > fechaMinima) {
        reject(new Error('Debes tener al menos 16 años.'));
      } else {
        resolve();
      }
    }
  });
}

function validateCelular(rule, value) {
  return new Promise((resolve, reject) => {
    if (!value) {
      reject(new Error('Por favor, ingresa tu número de celular.'));
    } else {
      axios.post('/existe-celular',{ celular: value, dni:formState.dni})
        .then(response => {
          if (response.data == true) {
            reject(new Error('Este número de celular ya está registrado.'));
          } else {
            resolve();
          }
        })
        .catch(error => {
          console.error('Error al verificar el número de celular:', error);
          reject(new Error('Error al verificar el número de celular.'));
        });
    }
  });
}

function validateCorreo(rule, value) {
  return new Promise((resolve, reject) => {
    if (!value) {
      reject(new Error('Por favor, ingresa tu correo.'));
    } else {
      axios.post('/existe-correo',{ email: value, dni:formState.dni})
        .then(response => {
          if (response.data == true) {
            reject(new Error('Este correo ya está registrado.'));
          } else {
            resolve();
          }
        })
        .catch(error => {
          console.error('Error al verificar el correo:', error);
          reject(new Error('Error al verificar correo.'));
        });
    }
  });
}

function validateCodigoSecreto(rule, value) {
  return new Promise((resolve, reject) => {
    if (!value) {
      reject(new Error('Por favor, ingresa el código secreto.'));
    } else if (codigo_aleatorio.value !== formState.codigo_secreto) {
      reject(new Error('El código ingresado no coincide.'));
    } else {
      resolve();
    }
  });
}

const getApoderado = async () => {
  const res = await axios.post("/get-apoderado", { id_postulante: datospersonales.id, tipo: 1 });
  if(res.data.datos.length !== 0){
    datospadre.id = res.data.datos[0].id;
    datospadre.tipo_apoderado = res.data.datos[0].tipo_apoderado;
    datospadre.dni = res.data.datos[0].nro_documento;
    datospadre.nombres = res.data.datos[0].nombres;
    datospadre.paterno = res.data.datos[0].paterno;
    datospadre.materno = res.data.datos[0].materno;
  }
}
const getApoderadoM = async () => {
  const res = await axios.post("/get-apoderado", { id_postulante: datospersonales.id, tipo: 2 });
  if(res.data.datos.length !== 0){
    datosmadre.id = res.data.datos[0].id;
    datosmadre.dni = res.data.datos[0].nro_documento;
    datosmadre.nombres = res.data.datos[0].nombres;
    datosmadre.paterno = res.data.datos[0].paterno;
    datosmadre.materno = res.data.datos[0].materno;
  }
}
const saveApoderado = async () => {
  const values = await formDatosPadre.value.validateFields();
  let avn  = 65;
  let ac = 'si';
  if (avance_current.value >= 65){ ac = 'no'; }
  if (datospadre.tipo_apoderado === 3){ avn = 80; }
  try {
    const response = await axios.post('/save-postulante-apoderado', {
        id: datospadre.id,
        tipo_apoderado: datospadre.tipo_apoderado,
        dni: datospadre.dni,
        nombres: datospadre.nombres,
        paterno: datospadre.paterno,
        materno: datospadre.materno,
        id_postulante: datospersonales.id,
        actualizar: ac,
        proceso: props.procceso_seleccionado.id,
        name:"Registro de datos del padre o tutor",
        nro:4,
        avance: avn
    });
    getPasos();
  } catch (error) {
    // Manejar el error en caso de que la solicitud falle
    console.error(error);
  }
}

const saveApoderadoM = async () => {
  const values = await formDatosMadre.value.validateFields();
  let avn  = 80;
  let ac = 'si';
  if (avance_current.value >= 80){ ac = 'no'; }
  try {
    const response = await axios.post('/save-postulante-apoderado', {
        id: datosmadre.id,
        tipo_apoderado: datosmadre.tipo_apoderado,
        dni: datosmadre.dni,
        nombres: datosmadre.nombres,
        paterno: datosmadre.paterno,
        materno: datosmadre.materno,
        id_postulante: datospersonales.id,
        actualizar: ac,
        proceso: props.procceso_seleccionado.id,
        name:"Registro de datos de la madre",
        nro:5,
        avance: avn
    });
    getPasos();
  } catch (error) {
    console.error(error);
  }
}

const id_pasos = ref(null)
const avance_current = ref(null)

const getPasos = async ( ) => {

    let res = await axios.post( "/get-pasos-proceso",
      { postulante: datospersonales.id,
        proceso: props.procceso_seleccionado.id
      });
      if (res.data.datos.length > 0){
        avance_current.value = res.data.datos[0].avance
        id_pasos.value = null
        pagina_pre.value = res.data.datos[0].nro + 1
        avance.value = res.data.datos[0].avance
        modalcarrerasprevias.value = false;
        loading.value = false;
      }else{
        getSancionado();
        //pagina_pre.value = 1;
      }

}

const notificacion = (type, titulo, mensaje) => {
  notification[type]({
    message: titulo,
    description: mensaje,
  });
};

const emits = defineEmits(['ejecutarFuncionHijo']);


const imagen = ref(null);

const submit = async () => {
  let fd = new FormData();
  fd.append('dni', formState.dni)
  fd.append('modalidad', datos_preinscripcion.modalidad)
  fd.append('programa', datos_preinscripcion.programa)
  fd.append('tipo_certificado', datos_preinscripcion.tipo_certificado)
  fd.append('codigo_certificado', datos_preinscripcion.codigo_certificado)
  fd.append('codigo_medico', datos_preinscripcion.codigo_medico)
  fd.append('observacion', datos_preinscripcion.observacion)
  fd.append('id_postulante', datospersonales.id)
  fd.append('id_proceso', props.procceso_seleccionado.id)
  fd.append('id_anterior', id_anterior.value )
  await axios.post("/save-pre-inscripcion", fd).then(res=>{
      if( avance_current.value < 100){
        savePasos("Registro de datos preinscripcion", 6, 110)
      } else {
        next()
      }
      showToast("success","2",res.data.menssje);
  }).catch(err=>console.log(err))
  open.value = false
}

const presionado = ref(0);

const getDocs = async () => {
  if(datospersonales.tipo_doc === 1 ){
    window.open("/pdf-solicitud/"+props.procceso_seleccionado.id+"/"+formState.dni, '_blank');
  }else{
    window.open("/pdf-solicitud-extranjeros/"+props.procceso_seleccionado.id+"/"+formState.dni, '_blank');
  }
}

const irDiagnostico = async () => {
  if (datospersonales.tipo_doc !== 1) return;

  try {
    const response = await axios.get(
      "https://admision.unap.edu.pe/codigo-acceso",
      {
        params: { dni: formState.dni },
        headers: { 'Accept': 'application/json' }
      }
    );

    if (response.data?.url) {
      window.open(response.data.url);
    } else if (typeof response.data === 'string' && response.data.startsWith('http')) {
      window.open(response.data, "_blank");
    } else {
      console.error("Respuesta inesperada:", response.data);
    }

  } catch (error) {
    console.error("Error:", error);
    const fallbackUrl = `https://admision.unap.edu.pe/codigo-acceso?dni=${formState.dni}`;
    window.open(fallbackUrl, "_blank");
  }
}

const tipo_docs = { 1: 'DNI', 2: 'PASAPORTE' }
const estados_civil = { 1: 'SOLTERO', 2: 'CASADO', 3: 'VIUDO' }
const sexos = { 1: 'MASCULINO', 2: 'FEMENINO' }

const temp_date = ref(null);

const cambiarFormato = () => {
  const fecha = datospersonales.fec_nacimiento.$d;
  const formattedDate = format(fecha, "dd 'de' MMMM 'del' yyyy", { locale: es });
  temp_date.value = formattedDate;
};

const college = ref(null)

const getColegioSeleccionado = () => {
  college.value = colegios.value.find(item => item.value === datoscolegio.id_colegio);
}

const props = defineProps(['procceso_seleccionado']);

const sancionado = ref(null)
const modalSancionado = ref(false);
const id_anterior = ref(null);
const carreras_previas = ref([]);

const getSancionado =  async () => {
  participa.value = 0;
  try {
    let res = await axios.get("/get-sancionado/" + formState.dni + "/" + props.procceso_seleccionado.id);
    sancionado.value = res.data.datos;
    if(sancionado.value != null){
      datacepre.value = [];
      loading.value = false;
      modalSancionado.value = true;
      anteriores.value = []
      return;
    }else{
      consultaInscripcion()
    }
  } catch (error) {
    console.error("Error al obtener datos de sancionado", error);
  }
}

const datacepre = ref(null)
const getParticipanteCepre =  async () => {
  datacepre.value = [];
  try {
    let res = await axios.get("/get-participante-cepre/" + formState.dni);
      if (res.data.estado === true ) {
        datacepre.value = res.data.datos;
        datospersonales.tipo_doc = 1;
        datospersonales.nombres = datacepre.value.nombres;
        datospersonales.primerapellido = datacepre.value.paterno;
        datospersonales.segundo_apellido = datacepre.value.materno;
        datospersonales.sexo = datacepre.value.sexo;
        datospersonales.ubigeo_residencia = datacepre.value.codigo_distrito;
        datoscolegio.egreso = datacepre.value.anio_egreso;
        participa.value = 1;
        getDataPrisma()
        getDatosPersonales2()
      } else {
        loading.value = false;
        modalSancionado.value = true;
        return;
    }
  } catch (error) {
    console.error("Error al obtener datos del participante", error);
}
}


const codigo_aleatorio = ref(null);

const getCodigoAleatorio = async ( ) => {
  let res = await axios.get("/generar-captcha");
  codigo_aleatorio.value = res.data.captcha;
}

const postulante_inscrito = ref(0);

const consultaInscripcion = async () => {
  postulante_inscrito.value = 0;
  try {
    let res = await axios.get("/participa-proceso/"+props.procceso_seleccionado.id+"/"+formState.dni);
      if (res.data.estado === true) {
        postulante_inscrito.value = 1;
        modalcarrerasprevias.value = false;
        loading.value = false;
        pagina_pre.value = 7;
      } else {
        if(props.procceso_seleccionado.id_modalidad_proceso == 2){
          await getParticipanteCepre();
          participa.value = 1;
        }
        else{
          getDataPrisma();
          getDatosPersonales2()
          participa.value = 1;
        }
      }
  } catch (error) {
    console.error("Error al obtener datos del participante", error);
  }
}

const consultaInscripcion2 = async () => {
  postulante_inscrito.value = 0;
  try {
    let res = await axios.get("/participa-proceso/"+props.procceso_seleccionado.id+"/"+formState.dni);
      if (res.data.estado === true) {
        pagina_pre.value = 7;
      }else{
        pagina_pre.value = 6;
      }
  } catch (error) {
    console.error("Error al obtener datos del participante", error);
  }
}

getCodigoAleatorio();

const modalcarrerasprevias = ref(false);
const participante = ref(null);
const anteriores = ref([]);

const getDataPrisma = async () => {
    participante.value = null;
    const response = await axios.get('/get-data-prisma/'+formState.dni);
    if(response.data.estado === true){
        participante.value = response.data.datos;
    }
    getCarrerasPrevias();
}


const getCarrerasPrevias = async ( ) => {
    try {
        const response = await axios.post('/get-carreras-previas', {
            participante: participante.value,
            formState: formState.dni
        });
        if(response.data.mensaje === "No tiene carreras previas"){
          const data = response.data.anteriores;
          anteriores.value = [];
          loading.value = data.loading;
          modalSancionado.value = data.modalSancionado;
          confirmacion.value = data.confirmacion;
        } else{
          loading.value = false;
          anteriores.value = response.data.anteriores;
          modalSancionado.value = false;
          confirmacion.value = false;

        }
    } catch (error) {
        console.error("Error fetching data: ", error);
    }
};




const confirmacion = ref(null);

const registrarPrevias = async () => {
    confirmacion.value = null;
    axios.post("/registrar-carreras-previas",{"carreras": selectedItems.value, dni:formState.dni })
    .then((response) => {
        confirmacion.value = response.data.estado;
        modalcarrerasprevias.value = false;
    })
    .catch((error) => {
        if (error.response) {
            console.error('Error de servidor:', error.response.data);
        } else if (error.request) {
            console.error('Error de red:', error.request);
            } else { console.error('Error de configuración:', error.message); }
    });
}

const toggleSelection = (item) => {
    item.selected = !item.selected;
};

const selectedItems = computed(() => {
    if(anteriores.value){
        return anteriores.value.filter((item) => item.selected);
    }
});


const carrera_anterior = ref({});
const toggleSelection2 = (item) => {
    item.selected = !item.selected;
    if(item.selected == true){
      id_anterior.value = item.id;
      carrera_anterior.value = item;
    }else{
      id_anterior.value = null;
      carrera_anterior.value = {};
    }
};

const cancelarInscripcion = () => {
    modalcarrerasprevias.value = false;
    location.reload(true);
}

const getCarrerasPreviasPostulacion = async () => {
  const response = await axios.get('/carreras-previas/'+formState.dni);
  carreras_previas.value = response.data.datos;
}


const descargaReglamento = async () => {
  try {
        const response = await axios.get('/descargar-reglamento/'+props.procceso_seleccionado.id, {
          responseType: 'blob',
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'reglamento.pdf');
        document.body.appendChild(link);
        link.click();
      } catch (error) {
        console.error('Error al descargar el PDF', error);
      }

}

const bio = ["04","08","15", "27", "28","29"];
const ing = ["01", "02", "03", "05", "10", "22", "23", "24", "26", "30", "31", "32", "33", "34", "35", "36"]
const soc = ["06", "07", "09", "11", "12", "13", "14", "16", "17", "18", "20", "21", "25", "56"]
</script>

<style scope>
input[type=file]::file-selector-button {
  margin-right: 10px;
  border: none;
  background: blue  ;
  padding: 7px 20px;
  border-radius: 5px;
  color: #fff;
  cursor: pointer;
  transition: background .2s ease-in-out;
}

input[type=file]::file-selector-button:hover {
  background: #13136b;
}
::-webkit-scrollbar {
  width: 7px;
}

::-webkit-scrollbar-track {
  background: #f1f1f100;
}


::-webkit-scrollbar-thumb {
  background: #bbbbbb;
}

::-webkit-scrollbar-thumb:hover { background: #aaaaaa; }

.btn-vocacional{ background: #13136b; color: white; }
.btn-vocacional:hover { background: #af1f75; }

.boton-anterior { width: 175px; height: 40px; color: teal; border: teal solid 1px; font-weight: bold; }
.boton-siguiente { width: 175px; height: 40px; background: teal; border:none; }
.boton-siguiente:hover {  border-radius: 5px; background: #045454; color:white; }
.datos-postulacion { display: flex}
.selector-modalidad { width: 250px }
.vocacional {height:calc(100vh - 227px); overflow-y:scroll;}
.card-datos-colegio { padding-top: -5px;  min-width: 700px; }
@media screen and (max-width: 1200px) {
  .datos-column { width: calc(50% - 10px); }
  .card-datos-colegio { padding-top: -5px; padding-bottom:0px; min-width: 600px; }
}

@media screen and (max-width: 768px) {
  .card-datos-colegio { min-width: 300px; }
  .datos-column {
    width: 100%;
  }
}

@media (max-width: 480px), screen and (orientation: portrait) {
  .cardInicio { margin-top: -10px; border:none; }
  .boton-anterior { width: 50%; border-radius: 0%; border: none; background: #506b6c42; }
  .boton-siguiente { width: 50%; border-radius: 0%; border: none; }
  .datos-postulacion { display: block;}
  .selector-modalidad { width: 100%; margin-bottom: 10px; }
  .vocacional {height:calc(100vh - 200px); overflow-y:scroll;}
  .btn-vocacional { width:100%;}
  .card-datos-colegio { width: 200px; }
}

.datos-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
  }

  .datos-column {
    width: calc(33.33% - 10px);
    margin-bottom: 20px;
    padding: 10px;
  }

  .datos-column label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
  }

  .datos-column input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  .datos-column select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  .datos-column .checkbox{
    width: 20px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }

.selected { background-color: #e0e0e06d; }
.deshabilitado { opacity: 0.5;  pointer-events: none; cursor: not-allowed;}
@keyframes rotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.rotating-svg { animation: rotate 2s linear infinite; }
.aparecer-div { opacity: 0; transition: opacity 0.5s ease-in-out; }
.aparecer-div-mostrar { opacity: 1; }

.custom-button { background: #045c59; color: white; border: teal 2px solid; }
.custom-button:hover { background: teal; border:none; color:white; }
.custom-icon { display: flex; align-items: center; }
.icono-img { margin-top: 0px; width: 30px; }

.custom-button2 { background: white; border: none; padding: 0px 20px; }
.custom-button2:hover { background: teal; color:white; padding: 0px 20px; }
.custom-icon2 { display: flex; align-items: center; }
.icono-img2 { margin-top: 0px; width: 30px; }
</style>
