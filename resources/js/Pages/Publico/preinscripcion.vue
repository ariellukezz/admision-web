<template style="background:pink;">
<Head title="Preinscipción"/>
<Layout v-if="examen === 0">	
  
  <!-- <a-button @click="abrirModalDatos()">abrir</a-button> -->
  <a-modal v-model:visible="open" style="width: 100%; max-width:1200px;" :footer="false" >
    <div>
      <h1 style="font-weight:bold; font-size:1.2rem;">Datos personales</h1> 
      <hr>

      <div class="datos-container" style="margin-bottom: 10px;">
        <div class="datos-column">
          <label for="name">Tipo doc: <span></span>  </label>
          <input type="text" id="name" :value="tipo_docs[datospersonales.tipo_doc]"  />
        </div>

        <div class="datos-column">
          <label for="name">N° Documento: <span></span>  </label>
          <input type="text" id="name" :value="formState.dni"  />
        </div>

        <div class="datos-column">
          <label for="name">Primer apellido: <span></span>  </label>
          <input type="text" id="name" :value="datospersonales.primerapellido"  />
        </div>
        
        <div class="datos-column">
          <label for="name">Segundo apellido: <span></span>  </label>
          <input type="text" id="name" :value="datospersonales.segundo_apellido"  />
        </div>

        <div class="datos-column">
          <label for="name">Prenombres: <span></span>  </label>
          <input type="text" id="name" :value="datospersonales.nombres"  />
        </div>

        <div class="datos-column">
          <label for="name">Estado civil: <span></span>  </label>
          <input type="text" id="name" :value="estados_civil[datospersonales.estado_civil]"  />
        </div>

        <div class="datos-column">
          <label for="name">Sexo: <span></span> </label>
          <input type="text" id="name" :value="sexos[datospersonales.sexo]"  />
        </div>
        
        <div class="datos-column">
          <label for="name">Correo: <span></span>  </label>
          <input type="text" id="name" :value="datospersonales.correo"  />
        </div>
        
        <div class="datos-column">
          <label for="name">Celular: <span></span>  </label>
          <input type="text" id="name" :value="datospersonales.celular"  />
        </div>
        
        <div class="datos-column">
          <label for="name">Fec. nacimiento: <span></span>  </label>
          <input type="text" id="name" :value="temp_date"/>
        </div>

        <div class="datos-column">
          <label for="name">Ubigeo de nacimiento: <span></span>  </label>
          <input type="text" id="name" :value="formState.ubigeo"/>
        </div>
        <div class="datos-column">
        </div>

      </div>

      <!-- {{ formState }}
      {{ datospersonales }} -->
    </div>

    <div>
      <h1 style="font-weight:bold; font-size:1.2rem;">Datos residencia</h1> 
      <hr>

      <div class="datos-container" style="margin-bottom: 10px;">
        <div class="datos-column">
          <label for="name">Departamento: <span></span>  </label>
          <input type="text" id="name" :value="datosresidencia.dep"  />
        </div>

        <div class="datos-column">
          <label for="name">Provincia: <span></span>  </label>
          <input type="text" id="name" :value="datosresidencia.prov"  />
        </div>

        <div class="datos-column">
          <label for="name">Distrito: <span></span>  </label>
          <input type="text" id="name" :value="datosresidencia.dist"  />
        </div>
        
        <div class="datos-column" style="width:100%;">
          <label for="name">Dirección: <span></span>  </label>
          <input type="text" id="name" :value="datosresidencia.direccion"  />
        </div>

      </div>
      
      <!-- {{ datosresidencia }} -->
    </div>

    <div>
      <h1 style="font-weight:bold; font-size:1.2rem;">Datos del colegio</h1> 
      <hr>
      <div class="datos-container" style="margin-bottom: 10px;">
        <div class="datos-column">
          <label for="name">Año de egreso: <span></span>  </label>
          <input type="text" id="name" :value="datoscolegio.egreso"  />
        </div>
        <div class="datos-column">
          <label for="name">Departamento: <span></span>  </label>
          <input type="text" id="name" :value="datoscolegio.dep"  />
        </div>

        <div class="datos-column">
          <label for="name">Provincia: <span></span>  </label>
          <input type="text" id="name" :value="datoscolegio.prov"  />
        </div>

        <div class="datos-column">
          <label for="name">Distrito: <span></span>  </label>
          <input type="text" id="name" :value="datoscolegio.dist"  />
        </div>
        
        <div class="datos-column">
          <label for="name">Dirección: <span></span>  </label>
          <input type="text" id="name" :value="datosresidencia.direccion"  />
        </div>

        <div class="datos-column">
        </div>

      </div>
      {{ colegios }}
      {{ datoscolegio }}
    </div>

    <div>
      <h1>Datos del padre o tutor</h1> 
      <hr>
      {{ datospadre }}
    </div>

    <div>
      <h1>Datos de la madre</h1> 
      <hr>
      {{ datosmadre }}
    </div>

    <div>
      <h1>Datos de Postulación</h1> 
      <hr>
      {{ datos_preinscripcion }}
    </div>


  </a-modal>
  

  <a-modal v-model:visible="ejemplo" :footer="false" @ok="handleOk">
    <a-tabs v-model:activeKey="activeKey" :size="size">
      <a-tab-pane key="1" tab="CERT. AMARILLO">
        <div style="max-height:400px; overflow-y:scroll;"> <img src="../../../assets/imagenes/certificados/amarillo.jpg"> </div>
      </a-tab-pane>
      <a-tab-pane key="2" tab="CERT. BLANCO">
        <div> <img src="../../../assets/imagenes/certificados/blanco.jpg"> </div>
      </a-tab-pane>
      <a-tab-pane key="3" tab="CONST. DE LOGROS">
        <div> <img src="../../../assets/imagenes/certificados/constancia.jpg"> </div>  
      </a-tab-pane>
    </a-tabs>

  </a-modal>
	<div style="width: 100%; min-height: calc(100vh - 220px); padding: 20px 20px; margin-top: 0px;">
  <!-- 
    {{ nc }} -->
    <div v-if="nc == 1"><pre> {{ presionado = 1 }} {{ nc = 0 }} </pre> </div>

    <div class="flex mt-3 justify-center align-center" style=" width: 100%; min-height: calc(100vh - 215px)">
    <!-- INICIO -->
			<a-card v-if="pagina_pre === 0"  style="width: 100%;  max-width: 350px; max-height:360px" class="pl-3 pr-3 cardInicio" > 
        <div>
          <h1 style="font-size: 1.1rem;">Datos de validación</h1>
        </div>
         <!-- {{datospersonales}} -->
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
            <a-input v-if="datospersonales.tipo_doc === 1" v-model:value="formState.dni" @input="dniInput" :maxlength="8" placeholder="N° Documento"/>
            <a-input v-else v-model:value="formState.dni" @input="dniInput" :maxlength="12" placeholder="N° Documento"/>
          </a-form-item>
          
          <div class="mb-4" v-if="datospersonales.tipo_doc === 1">
            <div class="mt-3"><label>N° Ubigeo</label></div>
            <a-form-item
              name="ubigeo"
              :rules="[{ required: true, message: 'Por favor ingresa tu ubigeo', trigger: 'change' }, { min: 6, message: 'El ubigeo debe tener 6 caracteres', trigger: 'blur',}]"
              >
              <a-input v-model:value="formState.ubigeo" @input="ubigeoInput" :maxlength="6" placeholder="Ubigeo"/>
            </a-form-item>
          </div>
          <a-card class="mt-3"> 
            <div class="mt-2" ></div>
          </a-card>
          <div style="display: flex; justify-content: center; margin-top: 20px;">
            <a-button type="primary" @click="getDatosPersonales()">Iniciar Postulación</a-button>
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
                  <a-radio-group v-model:value="datospersonales.tipo_doc" class="flex justify-end" name="radioGroup">
                    <a-radio :value="1">M</a-radio>
                    <a-radio :value="2">F</a-radio>
                  </a-radio-group>
                </div>
                <div style="height: 50px; border-right: solid 1px #d4d0d0 ;"></div>
                <div class="ml-3" >
                  <div> Estado civil </div>
                  <a-select
                    ref="select"
                    v-model:value="datospersonales.estado_civil"
                    style="width: 90px"
                  >
                    <a-select-option :value="1">Soltero</a-select-option>
                    <a-select-option :value="2">Casado</a-select-option>
                    <a-select-option :value="3">Viudo</a-select-option>
                    <a-select-option :value="4">Divorciado</a-select-option>
                  </a-select>
                </div>

              </div>

              <a-row :gutter="[16, 0]" class="form-row mb-0" >
                <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                    <div><label>Primer apellido:</label></div>
                    <a-form-item
                      name="primerapellido"
                      :rules="[{ required: true, message: 'Ingresa tu Primer Apellido', trigger: 'change'},]"
                    >
                      <a-input @input="pimerapellidoInput" v-model:value="datospersonales.primerapellido"/>
                    </a-form-item>
                </a-col>
                <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                  <a-form-item>
                    <div><label>Segundo apellido:</label></div>
                    <a-input v-model:value="datospersonales.segundo_apellido" />
                  </a-form-item>
                </a-col>
                <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                  <a-form-item>
                    <div><label>Prenombres:</label></div>
                    <a-form-item
                      name="nombres"
                      :rules="[{ required: true, message: 'Ingresa tus Prenombres', trigger: 'change'}]"
                    >
                      <a-input @input="nombresInput" v-model:value="datospersonales.nombres" />
                    </a-form-item>
                  </a-form-item>
                </a-col>
              </a-row>

              <a-row :gutter="[16, 0]" class="form-row" style="margin-top: -20px;">
                <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                  <a-form-item
                      name="correo"
                      :rules="[
                        { required: true, message: 'Ingresa un correo valido', trigger: 'change'},
                        { type: 'email', message: 'Ingresa un correo valido'}
                      ]">
                    <div><label>Correo:</label></div>
                    <a-input type="email" @input="correoInput"  v-model:value="datospersonales.correo" />
                  </a-form-item>
                </a-col>
              </a-row>

              <a-row :gutter="[16, 0]" class="form-row">
                <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="12">
                  <a-form-item
                      name="celular"
                      :rules="[
                        { required: true, message: 'Ingresa tu celular', trigger: 'change'},
                        { min: 9, message: 'El Celular debe tener 9 digitos', trigger: 'blur',}
                      ]"
                    >
                    <div><label>Numero de celular:</label></div>
                    <a-input @input="celularInput" :maxlength="9" v-model:value="datospersonales.celular" />
                  </a-form-item>
                </a-col>
                <a-col :span="24" :md="24" :lg="18  " :xl="16" :xxl="12">
                  <a-form-item
                      name="fec_nacimiento"
                      :rules="[ { required: true, message: 'Ingresa tu fecha de nacimiento', trigger: 'change'},
                       { validator: validateFechaNacimiento, trigger: 'change' }]"
                    >
                    <div><label>Fec. Nacimiento: "DD/MM/AAAA"</label></div>
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
        <a-card style="padding-top: -5px; padding-bottom:0px;" class="cardInicio">
          <div>
          
          <a-form
            ref="formDatosResidencia" :model="datosresidencia"
            name="datosResidencia"
            @finish="onFinish"
            @finishFailed="onFinishFailed"
            >
            <!-- //DATOS DE RESIDENCIA -->
            <div style="margin-bottom: 25px; margin-top: 10px; ">
              <h1 style="font-size: 1.1rem;"> Datos de residencia</h1>
            </div>

            <div style="display: none;">{{ getDepartamentos() }}</div>

            <a-row :gutter="[16, 0]" class="form-row">
              <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                <a-form-item
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
                  <a-input v-model:value="datosresidencia.direccion" />
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
          <div style="width: 100%; margin-top: 5px; ">
              <a-card style="padding-top: -5px; padding-bottom:0px;" class="cardInicio">
                <a-form
                    ref="formDatosColegio" :model="datoscolegio"
                    name="form-colegio"
                  >
                      <div>
                        <div style="margin-bottom: 25px; margin-top: 10px; ">
                          <h1 style="font-size: 1.1rem;"> Datos del colegio</h1>
                        </div>
                    
                        <a-row :gutter="[16, 0]" class="form-row">
                          <a-col :span="24" :md="24" :lg="12" :xl="16" :xxl="8">
                            <a-form-item
                            name="egreso"
                            :rules="[{ required: true, message: 'Ingrese año de egreso', trigger: 'change' },
                            { min: 4, message: 'Debe tener 4 digitos', trigger: 'blur',},]"
                            >
                              <div><label>Año de egreso: </label></div>
                              <a-input v-model:value="datoscolegio.egreso" :maxlength="4" placeholder="Año egreso" />
                            </a-form-item>
                          </a-col>
                          <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="16">
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
                                </a-select>  
                            </a-form-item>
                          </a-col>
                        </a-row>
                    
                        <a-row :gutter="[16, 0]" class="form-row">
                          <a-col :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                            <a-form-item>
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
                            <a-form-item>
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
                            <a-form-item>
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
                            name="colegio"
                            :rules="[{ required: true, message: 'Seleccione el colegio', trigger: 'change' }]"
                            >
                              <div><label>Nombre de colegio:</label></div>
                                <a-select
                                  v-model:value="datoscolegio.colegio"
                                  style="min-width: 300px"
                                  :options="colegios"
                                  @change="handleChangeCole"
                                ></a-select>
                                
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

                    <a-radio-group v-model:value="datospadre.tipo_apoderado" class="flex justify-end" style="display: flex; width: yellow;" name="radioGroup">
                        <a-radio :value="1">Padre</a-radio>
                        <a-radio :value="3">Tutor</a-radio>
                    </a-radio-group>

                    <a-row :gutter="[16, 0]" class="form-row">
                        <a-col :span="24" :md="26" :lg="12" :xl="12" :xxl="8">
                          <a-form-item
                            name="dni"
                            :rules="[{ required: true, message: 'Ingresa el DNI', trigger: 'change' },
                            { min: 8, message: 'El dni debe tener 8 digitos', trigger: 'blur',},]"
                            >
                            <div><label>N° Documento</label></div>
                            <a-input ref="myDni" v-model:value="datospadre.dni" :maxlength="12" placeholder="" />
                        </a-form-item>
                        </a-col>
                        <a-col :span="24" :md="24" :lg="12" :xl="12" :xxl="16">
                          <a-form-item
                            name="nombres"
                            :rules="[{ required: true, message: 'Ingresa los nombres', trigger: 'blur' }]"
                            >
                            <div><label>Pre nombres:</label></div>
                            <a-input v-model:value="datospadre.nombres"/>
                          </a-form-item>
                        </a-col>

                        <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                          <a-form-item
                            name="paterno"
                            :rules="[{ required: true, message: 'Ingresa el primer apellido', trigger: 'blur' }]"
                            >
                            <div><label>Primer apellido:</label></div>
                            <a-input v-model:value="datospadre.paterno" />
                        </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="[16, 0]" class="form-row">
                        <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                          <a-form-item>
                            <div><label>Segundo Apellido:</label></div>
                            <a-input v-model:value="datospadre.materno" />
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
                            <a-input ref="myDni" v-model:value="datosmadre.dni" :maxlength="12" placeholder="" />
                        </a-form-item>
                        </a-col>
                        <a-col :span="24" :md="24" :lg="12" :xl="12" :xxl="16">
                          <a-form-item
                            name="nombres"
                            :rules="[{ required: true, message: 'Ingresa los nombres', trigger: 'blur' }]"
                            >
                            <div><label>Pre nombres:</label></div>
                            <a-input v-model:value="datosmadre.nombres"/>
                          </a-form-item>
                        </a-col>

                        <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                          <a-form-item
                            name="paterno"
                            :rules="[{ required: true, message: 'Ingresa el primer apellido', trigger: 'blur' }]"
                            >
                            <div><label>Primer apellido:</label></div>
                            <a-input v-model:value="datosmadre.paterno" />
                        </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="[16, 0]" class="form-row">
                        <a-col :span="24" :md="24" :lg="24" :xl="24" :xxl="24">
                          <a-form-item>
                            <div><label>Segundo Apellido:</label></div>
                            <a-input v-model:value="datosmadre.materno" />
                        </a-form-item>
                        </a-col>
                    </a-row>
                  </div>
                </a-form>
              </a-card>

        </div>
      </div>




      <div v-if="pagina_pre === 6">

        <div style="width: 100%; margin-top: 5px; ">

          <a-card style="padding-top: -5px; padding-bottom:0px;" class="cardInicio">

            <div>
              <div>
              <div class="justify-between datos-postulacion" >
                  <div style="margin-bottom: 25px; margin-top: 10px; ">
                    <h1 style="font-size: 1.1rem;"> Datos Postulación</h1>
                  </div>
                  <div class="mb-3">
                    <div><label>Modalidad</label></div>
                     <a-select
                        ref="select"
                        v-model:value="datos_preinscripcion.modalidad"
                        placeholder="Seleccionar modalidad"
                        class="selector-modalidad"
                      >
                        <a-select-option :value="9">CEPREUNA</a-select-option>
                      </a-select>
                  </div>
                </div>
              </div>


              <a-row :gutter="[16, 0]" class="form-row">
                <a-col :span="24" :md="24" :lg="12" :xl="24" :xxl="8">
                  <a-form-item>
                    <div><label>Programa de estudios</label></div>  
                    <a-select
                        ref="select"
                        v-model:value="datos_preinscripcion.programa"
                        placeholder="Seleccionar programa"
                        class="selector-modalidad"
                      >
                        <a-select-option :value='1'>ADMINISTRACIÓN</a-select-option>
                        <a-select-option :value='2'>ANTROPOLOGÍA</a-select-option>
                        <a-select-option :value='3'>ARQUITECTURA Y URBANISMO</a-select-option>
                        <a-select-option :value='4'>ARTE: ARTES PLÁSTICAS</a-select-option>
                        <a-select-option :value='5'>ARTE: DANZA</a-select-option>
                        <a-select-option :value='6'>ARTE: MÚSICA</a-select-option>
                        <a-select-option :value='8'>BIOLOGÍA: ECOLOGÍA</a-select-option>
                        <a-select-option :value='9'>BIOLOGÍA: MICROBIOLOGÍA Y LABORATORIO CLÍNICO</a-select-option>
                        <a-select-option :value='10'>BIOLOGÍA: PESQUERÍA</a-select-option>
                        <a-select-option :value='11'>CIENCIAS CONTABLES</a-select-option>
                        <a-select-option :value='12'>CIENCIAS DE LA COMUNICACIÓN SOCIAL</a-select-option>
                        <a-select-option :value='13'>CIENCIAS FÍSICO MATEMÁTICAS: FÍSICA</a-select-option>
                        <a-select-option :value='14'>CIENCIAS FÍSICO MATEMÁTICAS: MATEMÁTICAS</a-select-option>
                        <a-select-option :value='15'>DERECHO</a-select-option>
                        <a-select-option :value='16'>EDUCACIÓN FÍSICA</a-select-option>
                        <a-select-option :value='17'>EDUCACIÓN INICIAL</a-select-option>
                        <a-select-option :value='18'>EDUCACIÓN PRIMARIA</a-select-option>
                        <a-select-option :value='19'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE CIENCIA, TECNOLOGÍA Y AMBIENTE</a-select-option>
                        <a-select-option :value='20'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE CIENCIAS SOCIALES</a-select-option>
                        <a-select-option :value='21'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE LENGUA, LITERATURA, PSICOLOGÍA Y FILOSOFÍA</a-select-option>
                        <a-select-option :value='22'>EDUCACIÓN SECUNDARIA DE LA ESPECIALIDAD DE MATEMÁTICA, FÍSICA, COMPUTACIÓN E INFORMÁTICA</a-select-option>
                        <a-select-option :value='23'>ENFERMERÍA</a-select-option>
                        <a-select-option :value='24'>INGENIERÍA AGRÍCOLA</a-select-option>
                        <a-select-option :value='25'>INGENIERÍA AGROINDUSTRIAL</a-select-option>
                        <a-select-option :value='26'>INGENIERÍA AGRONÓMICA</a-select-option>
                        <a-select-option :value='27'>INGENIERÍA CIVIL</a-select-option>
                        <a-select-option :value='28'>INGENIERÍA DE MINAS</a-select-option>
                        <a-select-option :value='29'>INGENIERÍA DE SISTEMAS</a-select-option>
                        <a-select-option :value='30'>INGENIERÍA ECONÓMICA</a-select-option>
                        <a-select-option :value='31'>INGENIERÍA ELECTRÓNICA</a-select-option>
                        <a-select-option :value='32'>INGENIERÍA ESTADÍSTICA E INFORMÁTICA</a-select-option>
                        <a-select-option :value='33'>INGENIERÍA GEOLÓGICA</a-select-option>
                        <a-select-option :value='34'>INGENIERÍA MECÁNICA ELÉCTRICA</a-select-option>
                        <a-select-option :value='35'>INGENIERÍA METALÚRGICA</a-select-option>
                        <a-select-option :value='36'>INGENIERÍA QUÍMICA</a-select-option>
                        <a-select-option :value='37'>INGENIERÍA TOPOGRÁFICA Y AGRIMENSURA</a-select-option>
                        <a-select-option :value='38'>MEDICINA HUMANA</a-select-option>
                        <a-select-option :value='39'>MEDICINA VETERINARIA Y ZOOTECNIA</a-select-option>
                        <a-select-option :value='40'>NUTRICIÓN HUMANA</a-select-option>
                        <a-select-option :value='41'>ODONTOLOGÍA</a-select-option>
                        <a-select-option :value='42'>SOCIOLOGÍA</a-select-option>
                        <a-select-option :value='43'>TRABAJO SOCIAL</a-select-option>
                        <a-select-option :value='44'>TURISMO</a-select-option>
                    </a-select>

                  </a-form-item>
                </a-col>
                
                <a-col v-if="datos_preinscripcion.programa === 38 || datos_preinscripcion.programa === 16" :span="24" :md="24" :lg="12" :xl="8" :xxl="8">
                  <a-form-item>
                    <div><label>Cod. de examen médico:</label></div>
                    <a-input placeholder="Cod cert Examen médico" v-model:value="datos_preinscripcion.codigo_medico"/>
                  </a-form-item>
                </a-col>
                <a-col :span="24" :md="24" :lg="24" :xl="12" :xxl="8">
                  <a-form-item>
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
                <a-col :span="24" :md="24" :lg="24" :xl="12" :xxl="8">
                  <a-form-item>
                    <div class="flex justify-between"><label>Codigo certificado</label> <span @click="ejemplo = true" style="color:blue; cursor:pointer;">ver ejemplo</span></div>
                    <a-input v-model:value="datos_preinscripcion.codigo_certificado" />
                  </a-form-item>
                </a-col>
              </a-row>

            </div>

          </a-card>
          </div>
      </div>

      <div v-if="pagina_pre === 7">
        <div style="width: 100%; height:calc(100vh - 300px); display:flex; align-items:center; ">
          <a-card style="padding-top: 5px; padding-bottom:0px; background: #24c1ff25;">
            <div class="">
              <div class="flex justify-center">
                <div  style="text-align:center; max-width: 300px;" >
                  <div>FINALIZASTE CON EXITO EL REGISTRO DE TUS DATOS</div> 
                </div>
              </div>
              <div class="flex justify-center mt-4 mb-4">
                <a-button @click="getDocs()" style="background: #020b61;" type="primary"> DESCARGAR SOLICITUD </a-button>
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
      <div v-if="pagina_pre !== 8">
        <a-progress :percent="avance"/>
      </div>

      <div class="flex" style="justify-content: space-between;" v-if="pagina_pre === 1">
        <a-button @click="prev()" class="boton-anterior">Anterior</a-button>
        <a-button @click="saveDatosPersonales()" class="boton-siguiente" type="primary" >Siguiente</a-button>    
      </div>

      <div class="flex" style="justify-content: space-between;" v-if="pagina_pre === 2">
        <a-button @click="prev()" class="boton-anterior">Anterior</a-button>
        <a-button @click="saveDatosResidencia()" class="boton-siguiente" type="primary" >Siguiente</a-button>    
      </div>

      <div class="flex" style="justify-content: space-between;" v-if="pagina_pre === 3">
        <a-button @click="prev()" class="boton-anterior">Anterior</a-button>
        <a-button @click="savecolegio()" class="boton-siguiente" type="primary" >Siguiente</a-button>
      </div>  

      <div class="flex" style="justify-content: space-between;" v-if="pagina_pre === 4">
        <a-button @click="prev()" class="boton-anterior">Anterior</a-button>
        <a-button @click="saveApoderado()" class="boton-siguiente" type="primary" >Siguiente</a-button>    
      </div>
      <div class="flex" style="justify-content: space-between;" v-if="pagina_pre === 5">
        <a-button @click="prev()" class="boton-anterior">Anterior</a-button>
        <a-button @click="saveApoderadoM()" class="boton-siguiente" type="primary" >Siguiente</a-button>    
      </div>
      <div class="flex" style="justify-content: space-between;" v-if="pagina_pre === 6">
        <a-button @click="prev()" class="boton-anterior">Anterior</a-button>
        <!-- <a-button @click="modalDatos = true" class="boton-siguiente" type="primary" >Finalizar</a-button>     -->
        <a-button html-type="submit" @click="submit" type="primary" class="boton-siguiente">Finalizar</a-button>    
      </div>
    </a-affix>



</Layout>

</template>
<script setup>
import Layout from '@/Layouts/LayoutPreinscripcion.vue'    
import { watch, reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { format, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';
import { notification } from 'ant-design-vue';
import { DownOutlined } from '@ant-design/icons-vue';
 
const ejemplo = ref(false);
const modalDatos = ref(true);
const open = ref(false);
const abrirModalDatos = () => { 
  open.value = true
  cambiarFormato() 
  getUbigeo();
  getApoderado()
  getApoderadoM()
  getColegios()
  getColegioSeleccionado()
}

const activeKey = ref('1');

const examen = ref(0);
const avance = ref(0)
const bottom = ref(2)

const pagina_pre = ref(0)
const next = () => { pagina_pre.value++; }
const prev = () => { pagina_pre.value--; }
const dni = ref("70757838")

const formRef = ref();
const formState = reactive({
  dni: '75317022',
  ubigeo: '200401',
});
const formDatosPersonales = ref();
const datospersonales = reactive({
  id: null,
  tipo_doc:1,
  primerapellido: "",
  segundo_apellido: "",
  nombres:"",
  estado_civil:1,
  sexo:1,
  correo:"",
  celular:'',
  fec_nacimiento:"",
  ubigeo:"",
  ubigeo_residencia:"",
});
const formDatosResidencia = ref();
const datosresidencia = reactive({
  dep: null,
  prov: null,
  dist: null,
  direccion:''
});
const formDatosColegio = ref();
const datoscolegio = reactive({
  egreso: null,
  pais: 125,
  dep: null,
  prov: null,
  dist: null,
  colegio:'',
});

const formDatosPadre = ref();
const datospadre = reactive({
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

const dniInput = (event) => { formState.dni = event.target.value.replace(/\D/g, ''); };
const ubigeoInput = (event) => { formState.ubigeo = event.target.value.replace(/\D/g, ''); };
const nombresInput = (event) => { datospersonales.nombres = event.target.value.replace(/[^A-Za-z\s]/g, '');};
const pimerapellidoInput = (event) => { datospersonales.primerapellido = event.target.value.replace(/[^A-Za-z]/g, '');};
const celularInput = (event) => { datospersonales.celular = event.target.value.replace(/\D/g, ''); };

const departamentos = ref([])
const departamentoscolegio = ref([])
// const dep = ref(null);
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

const tes = ref(null)

const postcepre = ref(null)
const getDatosCepreuna = async () => {
  let res = await axios.get('https://sistemas.cepreuna.edu.pe/api/v1/'+formState.dni, {
    headers: { Authorization: 'cepreuna_v1_api' }
  });
  if(res.data) {
    postcepre.value = res.data[0]
  } 
}

const getDatosCepre = async () => {
  let res = await axios.get('https://sistemas.cepreuna.edu.pe/api/v1/'+formState.dni, {
    headers: {
      Authorization: 'cepreuna_v1_api'
    }
  });
  if(pagina_pre.value == 0){
    const values = await formRef.value.validateFields();
  }
  if(res.data.length > 0 && res.data[0].habilitado == 1 ) {
    datospersonales.primerapellido = res.data[0].paterno
    datospersonales.segundo_apellido = res.data[0].materno
    datospersonales.nombres = res.data[0].nombres
    datospersonales.sexo = res.data[0].sexo
    datospersonales.correo = res.data[0].correo
    datospersonales.celular = res.data[0].celular
    datospersonales.ubigeo_residencia = res.data[0].codigo_distrito
    saveDNI();

  } 
}

// const guardardatoscepre = async () => {
//   let res = await axios.post(
//     "save-datos-cepre", { 
//       paterno: datospersonales.primerapellido,
//       materno:datospersonales.segundo_apellido,
//       nombres: datospersonales.nombres,
//       e_civil: datospersonales.estado_civil,
//       sexo: datospersonales.sexo,
//       correo: datospersonales.correo,
//       celular: datospersonales.celular,
//       ubigeo: formState.ubigeo
//     }
//   );
//   getPasos()
// }


const getDatosPersonales = async () => {
  getDatosCepreuna();
  if(pagina_pre.value == 0 ){
    const values = await formRef.value.validateFields();
  }

  if(postcepre.value ){
    if(postcepre.value.habilitado == 1) {
      let res = await axios.post( "get-postulante-datos-personales", {nro_doc: formState.dni});
      if(res.data.datos.length > 0 ) {
        datospersonales.id = res.data.datos[0].id
        datospersonales.primerapellido = res.data.datos[0].primer_apellido
        datospersonales.segundo_apellido = res.data.datos[0].segundo_apellido
        datospersonales.nombres = res.data.datos[0].nombres
        //datospersonales.estado_civil = res.data.datos[0].estado_civil
        //datospersonales.sexo = res.data.datos[0].sexo
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
        datosresidencia.dist = res.data.datos[0].distrito
        datospersonales.ubigeo_residencia = res.data.datos[0].ubigeo_residencia
        datosresidencia.direccion = res.data.datos[0].direccion
        getPasos();
      } 
      else {
        getDatosCepre();
        pagina_pre.value = 1;  
      }
    }
  }

} 



const savePasos =  async (namex, num, avan ) => {
  let res = await axios.post(
    "save-pasos-preinscripcion",
    { 
      id: id_pasos.value,
      nombre: namex, 
      nro: num,
      avance: avan,
      postulante: datospersonales.id,
      proceso: 4
    }
  );
  getPasos()
}

const saveDNI =  async () => {
  let res = await axios.post( "save-postulante-dni", 
  {  
    tipo_doc: datospersonales.tipo_doc,
    nro_doc: formState.dni,
    ubigeo_nacimiento: formState.ubigeo,
    paterno: datospersonales.primerapellido,
    materno:datospersonales.segundo_apellido,
    nombres: datospersonales.nombres,
    e_civil: datospersonales.estado_civil,
    sexo: datospersonales.sexo,
    correo: datospersonales.correo,
    celular: datospersonales.celular,
    ubigeo_residencia: datospersonales.ubigeo_residencia
  });
  getDatosPersonales()
}

const saveDatosPersonales =  async () => {
  const values = await formDatosPersonales.value.validateFields();
  let res = await axios.post(
    "save-postulante",
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
      ubigeo_nacimiento: formState.ubigeo,
      // ubigeo_residencia: datospersonales.ubigeo_residencia,
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
    "save-postulante-residencia",
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
    getApoderadoM();
  }
})


const getDepartamentos = async () => { 
  let res = await axios.post( "get-departamentos-codigo?page=", { term: buscarDep.value});
  departamentos.value = res.data.datos.data
} 
const getDepartamentosColegio = async () => {
  let res = await axios.post( "get-departamentos-codigo?page=", { term: buscarDepC.value});
  departamentoscolegio.value = res.data.datos.data
}
const getProvincias = async (depp) => {
  let res = await axios.post( "get-provincias-codigo?page=", {departamento: depseleccionado.value });
  provincias.value = res.data.datos
} 
const getProvinciasColegio = async (depp) => {
  let res = await axios.post( "get-provincias-codigo?page=", {departamento: depseleccionadoC.value });
  provinciasC.value = res.data.datos
} 
const getDistritos = async (depp) => {
  let res = await axios.post( "get-distritos-codigo?page=", 
    { departamento: depseleccionado.value, provincia: provseleccionada.value }
  );
  distritos.value = res.data.datos
}
const getDistritosColegio = async (depp) => {
  let res = await axios.post( "get-distritos-codigo?page=",  {  departamento: depseleccionadoC.value, provincia: provseleccionadaC.value });
  distritosC.value = res.data.datos
}
const getColegios = async () => {
  const res = await axios.post("get-colegio-distrito", {  ubigeo_cole: depseleccionadoC.value + provseleccionadaC.value + distseleccionadoC.value });
  colegios.value = res.data.datos;
}
const getUbigeo = async () => {
  const res = await axios.post("get-ubigeo-colegio", { id_postulante: datospersonales.id });
  if(res[0] !== []){
    datoscolegio.egreso = res.data.datos[0].egreso;
    datoscolegio.colegio = res.data.datos[0].id;
    datoscolegio.dep = res.data.datos[0].departamento;
    depseleccionadoC.value = res.data.datos[0].dep;
    datoscolegio.prov = res.data.datos[0].provincia;
    provseleccionadaC.value = res.data.datos[0].prov;
    datoscolegio.dist = res.data.datos[0].distrito;
    distseleccionadoC.value = res.data.datos[0].dist;
  }
}

const savecolegio = async () => {
  const values = await formDatosColegio.value.validateFields();
  let ac = 'si'; 
  if (avance_current.value >= 48){ ac = 'no'; } 
  try {
    const response = await axios.post('save-postulante-colegio', {
      id:  datospersonales.id,
      anio_egreso: datoscolegio.egreso,
      colegio: datoscolegio.colegio,     
      actualizar: ac,
      proceso: 4
    },
    getPasos());
  } catch (error) {      
    console.error(error);
  }
}

function validateFechaNacimiento(rule, value, callback) {
  if (!value) {
    callback(new Error(''));
  } else {
    const fechaNacimiento = new Date(value);
    const fechaMinima = new Date();
    fechaMinima.setFullYear(fechaMinima.getFullYear() - 16);

    if (fechaNacimiento > fechaMinima) {
      callback(new Error('Debes tener al menos 16 años'));
    } else {
      callback();
    }
  }
}



//formDatosPadre
const getApoderado = async () => {
  const res = await axios.post("get-apoderado", { id_postulante: datospersonales.id, tipo: 1 });
  if(res[0] !== []){
    datospadre.id = res.data.datos[0].id;
    datospadre.tipo_apoderado = res.data.datos[0].tipo_apoderado;
    datospadre.dni = res.data.datos[0].nro_documento;
    datospadre.nombres = res.data.datos[0].nombres;
    datospadre.paterno = res.data.datos[0].paterno;
    datospadre.materno = res.data.datos[0].materno;
  }
}
const getApoderadoM = async () => {
  const res = await axios.post("get-apoderado", { id_postulante: datospersonales.id, tipo: 2 });
  if(res[0] !== []){
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
    const response = await axios.post('save-postulante-apoderado', {
        id: datospadre.id,
        tipo_apoderado: datospadre.tipo_apoderado,
        dni: datospadre.dni,
        nombres: datospadre.nombres,
        paterno: datospadre.paterno,
        materno: datospadre.materno,
        id_postulante: datospersonales.id,
        actualizar: ac,
        proceso: 4,
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
    const response = await axios.post('save-postulante-apoderado', {
        id: datosmadre.id,
        tipo_apoderado: datosmadre.tipo_apoderado,
        dni: datosmadre.dni,
        nombres: datosmadre.nombres,
        paterno: datosmadre.paterno,
        materno: datosmadre.materno,
        id_postulante: datospersonales.id,
        actualizar: ac,
        proceso: 4,
        name:"Registro de datos de la madre",
        nro:5,
        avance: avn
    });
    getPasos();
  } catch (error) {
    // Manejar el error en caso de que la solicitud falle
    console.error(error);
  }
}














const id_pasos = ref(null)
const avance_current = ref(null)
const getPasos = async (depp) => {
  let res = await axios.post( "get-pasos-proceso", 
    { postulante: datospersonales.id,
      proceso: 4
    });
    if (res.data.datos.length > 0){
      avance_current.value = res.data.datos[0].avance  
      id_pasos.value = null
      pagina_pre.value = res.data.datos[0].nro + 1
      avance.value = res.data.datos[0].avance 
    }else{
      pagina_pre.value = 1;
    }

}

const notificacion = (type, titulo, mensaje) => {
  notification[type]({
    message: titulo,
    description: mensaje,
  });
};

const emits = defineEmits(['ejecutarFuncionHijo']);

const funcionHijoEjecutada = () => {
  console.log('Función del componente hijo ejecutada en el componente padre');
};

const datos_preinscripcion = ref({
  modalidad: 9,
  programa:null,
  tipo_certificado:null,
  codigo_medico: null,
  codigo_certificado:null,
})

const imagen = ref(null);

const submit = async () => {
  let fd = new FormData();
  fd.append('dni', formState.dni)
  fd.append('modalidad', datos_preinscripcion.value.modalidad)
  fd.append('programa', datos_preinscripcion.value.programa)
  fd.append('tipo_certificado', datos_preinscripcion.value.tipo_certificado)
  fd.append('codigo_certificado', datos_preinscripcion.value.codigo_certificado)
  fd.append('codigo_medico', datos_preinscripcion.value.codigo_medico)
  fd.append('id_postulante', datospersonales.id)
  await axios.post("save-pre-inscripcion", fd).then(async res=>{
    if( avance_current.value < 100){ await savePasos("Registro de datos preinscripcion", 6, 110) } else{ next() }
    showToast("success","2",res.data.message);
    getResoluciones()
  }).catch(err=>console.log(err))
}
const presionado = ref(0);

const getDocs = async () => {
  window.open("/pdf-solicitud/"+formState.dni, '_blank');
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
  college.value = colegios.value.find(item => item.value === datoscolegio.colegio);
  console.log(college.value.label)
}








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

/* Track */
::-webkit-scrollbar-track {
  background: #f1f1f100; 
}
 
/* Handle */
::-webkit-scrollbar-thumb {
  background: #bbbbbb; 
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
  background: #aaaaaa; 
}

.btn-vocacional{
  background: #13136b; 
  color: white;
}

/* Handle on hover */
.btn-vocacional:hover {
  background: #1f1faf; 

}


.boton-anterior { width: 175px; height: 38px; }
.boton-siguiente { width: 175px; height: 38px; border-radius: 5px; background: #020b61 }  
.datos-postulacion { display: flex}
.selector-modalidad { width: 250px }
.vocacional {height:calc(100vh - 227px); overflow-y:scroll;}
@media (max-width: 480px), screen and (orientation: portrait) { 
  .cardInicio { margin-top: -10px; border:none; } 
  .boton-anterior { width: 50%; }
  .boton-siguiente { width: 50%; }  
  .datos-postulacion { display: block;}
  .selector-modalidad { width: 100%; margin-bottom: 10px }
  .vocacional {height:calc(100vh - 200px); overflow-y:scroll;}
  .btn-vocacional { width:100%;}
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

  @media screen and (max-width: 1200px) {
    .datos-column {
      width: calc(50% - 10px);
    }
  }

  @media screen and (max-width: 768px) {
    .datos-column {
      width: 100%;
    }
  }

</style>