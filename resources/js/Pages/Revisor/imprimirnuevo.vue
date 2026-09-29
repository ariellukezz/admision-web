<template>
<Head title="Revisión de documentos"/>
<AuthenticatedLayout>
  <div>

    <div>
      <div>
        <a-input
          placeholder="Buscar..."
          v-model:value="buscar"
        />
      </div>
    </div>
    <div>
      <a-table :columns="colpostulantes" :data-source="postulantes" :pagination="false" :footer="false" size="small" style="scale: 1">
          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'nombres'">
              <div> {{ record.primer_apellido }} {{ record.segundo_apellido }} {{ record.nombres }} </div>
            </template>    
            <template v-if="column.dataIndex === 'acciones'">
                <a-button v-if="record.codigo == null" class="" style="background: #0a3d5a; color:white; height:34px; padding:0px 10px; border-radius:4px;" @click="abrirEditar(record)" size="small">
                    <template #icon><form-outlined/>  </template> Revisar
                </a-button>
                <a-button v-else style="background: purple; color:white; padding:0px 10px; border-radius:4px;" @click="abrirEditar(record)">
                    imprimir
                </a-button>
            </template>    
            <template v-if="column.dataIndex === 'codigo'">
                <span style="font-weight: bold;">{{ record.codigo }}</span>
            </template>    
          </template>
      </a-table>
    </div>


    <div class="fondo-biometrico" style="">
      <div style="height: 64%; position: relative;">
        <div class="header-biometrico-letras-top">
          <div>
            <span class="header-biometrico-nombre"> Ariel Luque </span> 
          </div>
          <div>
            <span class="header-biometrico-2da" style=""> INGRESANTE A 2DA CARRERA </span> 
          </div>
        </div>

      </div>
      <div style="background: white; height: 36%; position: relative;">
        <div class="header-biometrico-letras-bot">
          <div>
            <span class="header-biometrico-programa"> 
              INGENIERÍA ESTADISTICA E INFORMÁTICA
            </span> 
          </div>
          <div>
            <span class="header-modalidad"> 
              EXAMEN GENERAL
            </span> 
          </div>

        </div>



      </div>
      <div class="header-biometrico-container-foto">
        <img src="http://default.test/documentos/8/inscripciones/fotos/60068027.jpg" class="biometrico-foto-imagen">
      </div>
    </div>

    <!-- DATOS DEL POSTULANTE -->
    <div style="background: white;" class="mb-0 p-0">
      <div>
        <a-row :gutter="16" class="mb-3 mt-4 mx-4">
          <a-col :span="24" :sm="24" :md="24" :lg="16">
            <div class="p-4" style="background: white; border: 1px solid #d9d9d9;">
              <div class="flex justify-between pb-2" style="border-bottom: 2px #0a3d5a solid;">
                <div style="margin-top:10px;">
                  <span style="font-weight: bold; color:#0a3d5a; letter-spacing: .22rem; font-size:1.2rem;">DATOS PERSONALES</span>
                </div>
                <div>
                  <a-button style="padding:4px 20px; height:38px; background: #0a3d5a; border-radius: 5px; color:white;">
                    Editar
                  </a-button>
                </div>
              </div> 

              <div>
                <a-row :gutter="16" class="mb-3 mt-4 mx-4">
                  <a-col :span="24" :sm="24" :md="24" :lg="16">
                    <div class="mt-2">
                      <div><span style="font-weight: bold;">Nombres:</span></div>
                      <div><span style="letter-spacing: .2rem">JHON ARIEL</span></div>
                    </div>

                    <div class="mt-4">
                      <div><span style="font-weight: bold;">Apellidos:</span></div>
                      <div><span style="letter-spacing: .2rem">LUQUE CUSACANI</span></div>
                    </div>

                    <div class="mt-4">
                      <div><span style="font-weight: bold;">Fecha de nacimiento:</span></div>
                      <div><span style="letter-spacing: .2rem">28 enero de 1980</span></div>
                    </div>
                  </a-col>

                  <a-col :span="24" :sm="24" :md="24" :lg="8">
                    <div class="mt-2">
                      <div><span style="font-weight: bold;">Estado Civil:</span></div>
                      <div><span style="letter-spacing: .2rem">soltero</span></div>
                    </div>
                    <div class="mt-4">
                      <div><span style="font-weight: bold;">Sexo:</span></div>
                      <div><span style="letter-spacing: .2rem">Masculino</span></div>
                    </div>
                  </a-col>
                </a-row>
              </div>

            </div>
          </a-col>
          <a-col :span="24" :sm="24" :md="24" :lg="8">
            <div class="p-4" style="background: white; border: 1px solid #d9d9d9;">
              <div class="flex justify-between pb-2" style="border-bottom: 2px #0a3d5a solid;">
                <div style="margin-top:10px;">
                  <span style="font-weight: bold; color:#0a3d5a; letter-spacing: .22rem; font-size:1.2rem;">CONTACTO</span>
                </div>
                <div>
                  <a-button style="padding:4px 20px; height:38px; background: #0a3d5a; border-radius: 5px; color:white;">
                    Editar
                  </a-button>
                </div>
              </div>
              
              <a-row :gutter="16" class="mb-3 mt-4 mx-4">
                  <a-col :span="24" :sm="24" :md="24" :lg="24">
                    <div class="mt-2">
                      <div><span style="font-weight: bold;">Celular:</span></div>
                      <div><span style="letter-spacing: .2rem">966637192</span></div>
                    </div>

                    <div class="mt-4">
                      <div><span style="font-weight: bold;">Correo:</span></div>
                      <div><span style="letter-spacing: .2rem">Jhonar_theking@hotmail.com</span></div>
                    </div>

                    <div class="mt-4">
                      <div><span style="font-weight: bold;">Dirección:</span></div>
                      <div><span style="letter-spacing: .2rem">Av. Circunvalación Sur 845</span></div>
                    </div>
                  </a-col>
                </a-row>
            </div>
          </a-col>
        </a-row>
      </div>
    </div>


    <!-- OTROS DATOS DEL POSTULANTE -->
    <div style="background: white;" class="mb-0 p-0">
      <div>
        <a-row :gutter="16" class="mb-3 mt-4 mx-4">
          <a-col :span="24" :sm="24" :md="24" :lg="24">
            <div class="p-4" style="background: white; border: 1px solid #d9d9d9;">
              <div class="flex justify-between pb-2" style="border-bottom: 2px #0a3d5a solid;">
                <div style="margin-top:10px;">
                  <span style="font-weight: bold; color:#0a3d5a; letter-spacing: .22rem; font-size:1.2rem;">OTROS DATOS DEL POSTULANTE</span>
                </div>
                <div>
                  <a-button style="padding:4px 20px; height:38px; background: #0a3d5a; border-radius: 5px; color:white;">
                    Editar
                  </a-button>
                </div>
              </div> 

              <div>
                <a-row :gutter="16" class="mb-3 mt-4 mx-4">
                  <a-col :span="24" :sm="24" :md="24" :lg="16">
                    <div class="mt-2">
                      <div><span style="font-weight: bold;">Residencia:</span></div>
                      <div><span style="letter-spacing: .2rem">PUNO - PUNO - PUNO</span></div>
                    </div>

                    <div class="mt-4">
                      <div><span style="font-weight: bold;">Lugar de nacimiento:</span></div>
                      <div><span style="letter-spacing: .2rem">PUNO - EL COLLAO - ILAVE</span></div>
                    </div>

                    <div class="mt-4">
                      <div><span style="font-weight: bold;">Nacionalidad:</span></div>
                      <div><span style="letter-spacing: .2rem">PERUANO</span></div>
                    </div>
                  </a-col>

                  <a-col :span="24" :sm="24" :md="24" :lg="8">
                    <div class="mt-2">
                      <div><span style="font-weight: bold;">Discapacidad:</span></div>
                      <div><span style="letter-spacing: .2rem">NO</span></div>
                    </div>
                    <div class="mt-4">
                      <div><span style="font-weight: bold;">Tipo de discapacidad:</span></div>
                      <div><span style="letter-spacing: .2rem">Ninguna</span></div>
                    </div>
                  </a-col>
                </a-row>
              </div>

            </div>
          </a-col>
        </a-row>
      </div>
    </div>


    <!-- FORMACÓN ACADEMICA -->
    <div style="background: white;" class="mb-0 p-0">
      <div>
        <a-row :gutter="16" class="mb-3 mt-4 mx-4">
          <a-col :span="24" :sm="24" :md="24" :lg="24">
            <div class="p-4" style="background: white; border: 1px solid #d9d9d9;">
              <div class="flex justify-between pb-2" style="border-bottom: 2px #0a3d5a solid;">
                <div style="margin-top:10px;">
                  <span style="font-weight: bold; color:#0a3d5a; letter-spacing: .22rem; font-size:1.2rem;">FORMACIÓN ACADÉMICA</span>
                </div>
              </div> 

              <div>
                <a-row :gutter="16" class="mb-3 mt-4 mx-4">
                  <a-col :span="24" :sm="24" :md="24" :lg="24">
                    <div class="mt-2">
                      <div class="flex">
                        <div class="mr-4">
                          <img src="../../../assets/imagenes/logo-colegio.png" :width="50">
                        </div>
                        <div>
                          <div><span style="font-weight: bold;">ESTUDIOS SECUNDARIOS</span></div>
                          <div>Institución Educativa Secundaria Nuestra Señora del Carmen</div>
                          <div><span style="color: grey;">Año de egreso: </span> <span style="color: black; font-weight: bold;">2023</span> </div>
                          <div><span style="color: gray;">Lugar: </span> <span style="color: black; font-weight: bold;">Puno, El Collao, Ilave</span></div>
                        </div>
                      </div>
                    </div>


                    <div class="mt-4">
                      <div class="flex">
                        <div class="mr-4">
                          <img src="../../../assets/imagenes/logotiny.png" :width="50">
                        </div>
                        <div>
                          <div><span style="font-weight: bold;">INGENIERÍA DE SISTEMAS</span></div>
                          <div>Universidad Nacional del Altiplano de Puno</div>
                          <div><span style="color: grey;">Situación: </span> <span style="color: black; font-weight: bold;">Egresado</span> </div>
                          <div><span style="color: gray;">Código: </span> <span style="color: black; font-weight: bold;">150570</span></div>
                        </div>
                      </div>
                    </div>
                  </a-col>

                </a-row>
              </div>

            </div>
          </a-col>
        </a-row>
      </div>
    </div>

    <!-- EXPEDIENTE DE INSCRIPCIÓN -->
    <div style="background: white;" class="mb-0 p-0">
      <div>
        <a-row :gutter="[16,16]" class="mb-3 mt-4 mx-4">
          <a-col :span="24" :sm="24" :md="24" :lg="24">
            <div class="p-4" style="background: white; border: 1px solid #d9d9d9;">
              <div class="flex justify-between pb-2" style="border-bottom: 2px #0a3d5a solid;">
                <div style="margin-top:10px;">
                  <span style="font-weight: bold; color:#0a3d5a; letter-spacing: .22rem; font-size:1.2rem;">EXPEDIENTE DE INSCRIPCIÓN</span>
                </div>
              </div> 

              <div>
                <a-row :gutter="[16,16]" class="mb-3 mt-4 mx-4">
                  <a-col :span="12" :sm="24" :md="24" :lg="4">
                    <div style="background: #f3f3f3; height: 180px;">
                      <div class="flex justify-center" style="height: 130px; align-items:center;">
                        <div>
                          <img src="../../../assets/imagenes/dni.png" :width="130" :height="90"/>
                        </div>
                      </div>
                      <div class="flex justify-center" style="text-align: center; width: 100%; ">
                        <div style="width:140px;">
                          <span style="font-weight: bold;">Documento de Identidad</span>
                        </div>

                      </div>
                    </div>
                  </a-col>

                  <a-col :span="12" :sm="24" :md="24" :lg="4">
                    <div style="background: #f3f3f3; height: 180px;">
                      <div class="flex justify-center" style="height: 130px; align-items:center;">
                        <div>
                          <img src="../../../assets/imagenes/certificado.png" :width="120" :height="90"/>
                        </div>
                      </div>
                      <div class="flex justify-center" style="text-align: center; width: 100%; ">
                        <div style="width:140px;">
                          <span style="font-weight: bold;">Certificado de Estudios</span>
                        </div>

                      </div>
                    </div>
                  </a-col>


                  <a-col :span="12" :sm="24" :md="24" :lg="4">
                    <div style="background: #f3f3f3; height: 180px;">
                      <div class="flex justify-center" style="height: 130px; align-items:center;">
                        <div>
                          <img src="../../../assets/imagenes/solicitud.png" :width="120" :height="90"/>
                        </div>
                      </div>
                      <div class="flex justify-center" style="text-align: center; width: 100%; ">
                        <div style="width:140px;">
                          <span style="font-weight: bold;">Solicitud de inscripción</span>
                        </div>
                      </div>
                    </div>
                  </a-col>

                  <a-col :span="12" :sm="24" :md="24" :lg="4">
                    <div style="background: #f3f3f3; height: 180px;">
                      <div class="flex justify-center" style="height: 130px; align-items:center;">
                        <div>
                          <img src="../../../assets/imagenes/foto-inscripcion.jpg" :width="70" :height="90"/>
                        </div>
                      </div>
                      <div class="flex justify-center" style="text-align: center; width: 100%; ">
                        <div style="width:140px;">
                          <span style="font-weight: bold;">Constancia de inscripción</span>
                        </div>
                      </div>
                    </div>
                  </a-col>

                  <a-col :span="12" :sm="24" :md="24" :lg="4">
                    <div style="background: #f3f3f3; height: 180px;">
                      <div class="flex justify-center" style="height: 130px; align-items:center;">
                        <div>
                          <img src="../../../assets/imagenes/certificado.png" :width="120" :height="90"/>
                        </div>
                      </div>
                      <div class="flex justify-center" style="text-align: center; width: 100%; ">
                        <div style="width:140px;">
                          <span style="font-weight: bold;">Constancia de Ex Vocacional</span>
                        </div>
                      </div>
                    </div>
                  </a-col>

                  <a-col :span="12" :sm="24" :md="24" :lg="4">
                    <div style="background: #f3f3f3; height: 180px;">
                      <div class="flex justify-center" style="height: 130px; align-items:center;">
                        <div>
                          <img src="../../../assets/imagenes/certificado.png" :width="120" :height="90"/>
                        </div>
                      </div>
                      <div class="flex justify-center" style="text-align: center; width: 100%; ">
                        <div style="width:140px;">
                          <span style="font-weight: bold;">Constancia de ingreso</span>
                        </div>
                      </div>
                    </div>
                  </a-col>

                  <a-col :span="12" :sm="24" :md="24" :lg="4">
                    <div style="background: #f3f3f3; height: 180px;">
                      <div class="flex justify-center" style="height: 130px; align-items:center;">
                        <div>
                          <img src="../../../assets/imagenes/certificado.png" :width="120" :height="90"/>
                        </div>
                      </div>
                      <div class="flex justify-center" style="text-align: center; width: 100%; ">
                        <div style="width:140px;">
                          <span style="font-weight: bold;">Constancia de ingreso</span>
                        </div>
                      </div>
                    </div>
                  </a-col>

                </a-row>
              </div>
            </div>
          </a-col>
        </a-row>
      </div>
    </div>

    <!-- FOTOS DEL POSTULANTE -->
    <div style="background: white;" class="mb-0 p-0">
      <div>
        <a-row :gutter="[16,16]" class="mb-3 mt-4 mx-4">
          <a-col :span="24" :sm="24" :md="24" :lg="24">
            <div class="p-4" style="background: white; border: 1px solid #d9d9d9;">
              <div class="flex justify-between pb-2" style="border-bottom: 2px #0a3d5a solid;">
                <div style="margin-top:10px;">
                  <span style="font-weight: bold; color:#0a3d5a; letter-spacing: .22rem; font-size:1.2rem;">FOTOS DEL POSTULANTE</span>
                </div>
              </div> 

              <div>
                <a-row :gutter="[16,16]" class="mb-3 mt-4 mx-4">
                  <a-col :span="12" :sm="24" :md="24" :lg="4">
                    <div style="background: #f3f3f3;">
                        <img src="http://admision-web.test/documentos/8/8/inscripciones/fotos/70757838.jpg" :width="180" :height="90"/>
                    </div>
                  </a-col>

                  <a-col :span="12" :sm="24" :md="24" :lg="4">
                    <div style="background: #f3f3f3;">
                        <img src="http://admision-web.test/documentos/8/8/control_biometrico/fotos/70757838.jpg" :width="180" :height="90"/>
                    </div>
                  </a-col>

                </a-row>
              </div>
            </div>
          </a-col>
        </a-row>
      </div>
    </div>


    <!-- HUELLAS DEL POSTULANTE -->
    <div style="background: white;" class="mb-0 p-0">
      <div>
        <a-row :gutter="[16,16]" class="mb-3 mt-4 mx-4">
          <a-col :span="24" :sm="24" :md="24" :lg="24">
            <div class="p-4" style="background: white; border: 1px solid #d9d9d9;">
              <div class="flex justify-between pb-2" style="border-bottom: 2px #0a3d5a solid;">
                <div style="margin-top:10px;">
                  <span style="font-weight: bold; color:#0a3d5a; letter-spacing: .22rem; font-size:1.2rem;">HUELLAS DEL POSTULANTE</span>
                </div>
              </div> 

              <div>
                <a-row :gutter="[16,16]" class="mb-3 mt-4 mx-4">
                  <a-col :span="12" :sm="24" :md="24" :lg="4">
                    <div style="background: #f3f3f3;">
                        <img src="http://admision-web.test/documentos/8/8/inscripciones/huellas/70757838.jpg" :width="180" :height="90"/>
                    </div>
                  </a-col>

                  <a-col :span="12" :sm="24" :md="24" :lg="4">
                    <div style="background: #f3f3f3;">
                        <img src="http://admision-web.test/documentos/8/8/biometrico/huellas/70757838.jpg" :width="180" :height="90"/>
                    </div>
                  </a-col>

                  <a-col :span="12" :sm="24" :md="24" :lg="4">
                    <div style="background: #f3f3f3;">
                        <img src="http://admision-web.test/documentos/8/examen/huellas/70757838.jpg" :width="180" :height="90"/>
                    </div>
                  </a-col>

                </a-row>
              </div>
            </div>
          </a-col>
        </a-row>
      </div>
    </div>



    <div style="background: white;" class="mb-0 p-0">
      <div>
        <a-row :gutter="16" class="mb-3 mt-4 mx-4">
          <a-col :span="24" :sm="24" :md="24" :lg="24">
            <div class="p-4" style="background: white; border: 1px solid #d9d9d9;">
              <div class="flex justify-between pb-2" style="border-bottom: 2px #0a3d5a solid;">
                <div style="margin-top:10px;">
                  <span style="font-weight: bold; color:#0a3d5a; letter-spacing: .22rem; font-size:1.2rem;">DATOS DE INGRESO</span>
                </div>
                <div>
                  <a-button style="padding:4px 20px; height:38px; background: #0a3d5a; border-radius: 3px; color:white;">
                    Editar
                  </a-button>
                </div>
              </div> 

              <div>
                <a-row :gutter="16" class="mb-3 mt-4 mx-4">
                  <a-col :span="24" :sm="24" :md="24" :lg="16">
                    <div class="mt-2">
                      <div><span style="font-weight: bold;">Programa:</span></div>
                      <div><span style="letter-spacing: .2rem">INGENIERÍA DE SISTEMAS</span></div>
                    </div>

                    <div class="mt-4">
                      <div><span style="font-weight: bold;">Puesto:</span></div>
                      <div><span style="letter-spacing: .2rem">1ro</span></div>
                    </div>

                    <div class="mt-4">
                      <div><span style="font-weight: bold;">Fecha de ingreso:</span></div>
                      <div><span style="letter-spacing: .2rem">23 de marzo del 2024</span></div>
                    </div>
                  </a-col>

                  <a-col :span="24" :sm="24" :md="24" :lg="8">
                    <div class="mt-2">
                      <div><span style="font-weight: bold;">Modalidad:</span></div>
                      <div><span style="letter-spacing: .2rem">EXAMEN GENERAL</span></div>
                    </div>
                  </a-col>
                </a-row>
              </div>


              <div class="mt-6">
                <span style="font-weight: bold; color:#8B0000; letter-spacing: .05rem; font-size:1rem;">DATOS DE INGRESO ANTERIOR</span>
              </div>

              <a-row :gutter="[16,16]" class="mb-3 mt-4 mx-4">
                <a-col :span="24" :sm="24" :md="24" :lg="24">
                  <div class="p-4" style="background: #f3f3f3; border-radius: 8px;">
                    <div class="flex">
                      <div class="mr-3"> 
                        <img src="../../../assets/imagenes/logotiny.png" width="50"> 
                      </div>
                      <div class="flex justify-between" style="width: 100%;"> 
                        <div>
                          <div style="margin-top:10px;">
                            <span style="font-weight:bold; color: green;">TITULADO</span> -
                            <span style="font-weight:bold;">INGENIERÍA DE SISTEMAS</span>
                          </div>
                          <div>Codigo: 150570</div> 
                        </div>
                        <div class="mr-2" style="margin-top:12px;">
                          <a-button style="padding:4px 20px; height:38px; background: #8B0000; border-radius: 4px; color:white;">
                            Quitar
                          </a-button>
                        </div>
                      </div>
                    </div>
                  </div>
                </a-col>

                <a-col :span="24" :sm="24" :md="24" :lg="24">
                  <div class="p-4" style="background: #f3f3f3; border-radius: 8px;">
                    <div class="flex">
                      <div class="mr-3"> 
                        <img src="../../../assets/imagenes/logotiny.png" width="50"> 
                      </div>
                      <div class="flex justify-between" style="width: 100%;"> 
                        <div>
                          <div style="margin-top:10px;">
                            <span style="font-weight:bold; color: green;">TITULADO</span> -
                            <span style="font-weight:bold;">INGENIERÍA DE SISTEMAS</span>
                          </div>
                          <div>Codigo: 150570</div> 
                        </div>
                        <div class="mr-2" style="margin-top:12px;">
                          <a-button style=" padding:4px 20px; height:38px; background: #8B0000; border-radius: 4px; color:white;">
                            Quitar
                          </a-button>
                        </div>
                      </div>
                    </div>
                  </div>
                </a-col>

              </a-row>



              <div>

              </div>


            </div>
          </a-col>
        </a-row>
      </div>
    </div>


    <div style="background: white;" class="mb-0 p-0">
      <div>
        <a-row :gutter="[16,16]" class="mb-3 mt-4 mx-4">
          <a-col :span="24" :sm="24" :md="24" :lg="24">
            <div class="p-4" style="background: white; border: 1px solid #d9d9d9;">
              <div class="flex justify-between pb-2" style="border-bottom: 2px #0a3d5a solid;">
                <div style="margin-top:10px;">
                  <span style="font-weight: bold; color:#0a3d5a; letter-spacing: .22rem; font-size:1.2rem;">PAGOS REALIZADOS</span>
                </div>
              </div> 

              <div>
                <a-row :gutter="[16,16]" class="mb-3 mt-4 mx-4">
                  <a-col :span="24" :sm="24" :md="24" :lg="24">
                    <div style="">
                        
                      <a-table :columns="columns" :data-source="dataSource" :pagination="false" :footer="false">
                      </a-table>
                      <div class="flex justify-end py-4" style="margin-right:120px;">
                          <div><span style="font-weight:bold;"> Total </span></div> <div style="font-weight:bold; margin-left:100px;">S/. 450.00</div>
                      </div>
                    </div>
                  </a-col>
                </a-row>
              </div>
            </div>
          </a-col>
        </a-row>
      </div>
    </div>



    <div style="background: white;" class="mb-0 p-0">
      <div>
        <a-row :gutter="[16,16]" class="mb-3 mt-4 mx-4">
          <a-col :span="24" :sm="24" :md="24" :lg="24">
            <div class="p-4" style="background: white; border: 1px solid #d9d9d9;">
              <div class="flex justify-between pb-2" style="border-bottom: 2px #0a3d5a solid;">
                <div style="">
                  <span style="font-weight: bold; color:#0a3d5a; letter-spacing: .22rem; font-size:1.2rem;">RESUMEN</span>
                </div>
              </div> 


              <div style="">
                <a-row :gutter="[16,16]" class="mb-3 mt-4 mx-4">
                  <a-col :span="24" :sm="24" :md="24" :lg="24">
                    <div style="">
                      <div class="flex justify-end py-4">
                        <div>
                            <a-button class="mr-3" style="padding:4px 20px; height:38px; border: 2px solid #0a3d5a; border-radius: 3px; color:#0a3d5a;">
                              <span style="font-weight:bold;">Imprimir</span>
                          </a-button>
                        </div>
                        <div>
                          <a-button style="padding:4px 20px; height:38px; background: #0a3d5a; border-radius: 3px; color:white;">
                            Registrar
                          </a-button>
                        </div>
                      </div>
                    </div>
                  </a-col>
                </a-row>
              </div>
            </div>
          </a-col>
        </a-row>
      </div>
    </div>

    





    <div class="mt-6">

    </div>


    <a-card style="background: white;" class="mb-0 p-0" >
      <a-row :gutter="16" class="mb-3">
        <a-col :span="24" :sm="24" :md="24" :lg="24" style="display:flex; justify-content: space-between;">

            <div>
                <a-input
                  placeholder="Ingrese el código secreto"
                  v-model:value="codigo"
                />
            </div>

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
                  ref="dniInput"
                  placeholder="Buscar"
                  v-model:value="dni"
                  @keypress="handleKeyPress"
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

      <a-row >
        <a-col :span="24" :sm="24" :md="24" :lg="24">
          <div style="height: 40px; margin-left: -10px;">
            <a-checkbox-group v-model:value="checkedList" >
              <a-checkbox v-for="(option, index) in requisitos" :key="option.value" :value="option.value" :class="{ 'first-item': index === 0 }" class="checkbox-item">
               <span style="font-weight: bold;">{{ option.label }}</span>
              </a-checkbox>
            </a-checkbox-group> 
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
                  <a-card >
                  <a-row :gutter="16">

                      <a-col :xs="24" :sm="12" :md="12" :lg="12" :xl="12">
                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Primer Apellido</label>
                          <a-input v-model:value="ingresante.primer_apellido" />
                        </a-form-item>

                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Segundo Apellido</label>
                          <a-input v-model:value="ingresante.segundo_apellido" />
                        </a-form-item>

                        <a-form-item>
                          <div class="flex">
                            <div style="width:calc(100% - 100px);">
                              <label>Prenombres</label>
                              <a-input v-model:value="ingresante.nombres"/>
                            </div>
                            <div class="ml-3" style="width:100px;">
                              <label>Puesto</label>
                              <a-input v-model:value="ingresante.puesto"/>
                            </div>
                        </div>
                        </a-form-item>
                      </a-col>


                      <a-col :xs="24" :sm="12" :md="12" :lg="5" :xl="5">
                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Tipo documento</label>
                          <div>
                            <a-select
                              ref="tipodocumento"
                              v-model:value="ingresante.tipo_doc"
                              style="width: 100%"
                            >
                              <a-select-option :value="1">DNI</a-select-option>
                              <a-select-option :value="2">Carné Ext.</a-select-option>
                            </a-select>
                          </div>

                        </a-form-item>

                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Sexo</label>
                          <div>
                            <a-select
                              ref="sexoigresante"
                              v-model:value="ingresante.sexo"
                              style="width: 100%"
                            >
                              <a-select-option value="1">MASCULINO</a-select-option>
                              <a-select-option value="2">FEMENINO</a-select-option>
                            </a-select>
                          </div>
                        </a-form-item>

                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Fecha de nacimiento</label>
                          <a-date-picker placeholder="Selecciona tu fecha de nacimiento" style="width: 100%" v-model:value="ingresante.fec_nacimiento" format='DD/MM/YYYY' />
                        </a-form-item>
                      </a-col>

                      <a-col :xs="24" :sm="12" :md="12" :lg="2" :xl="2">
                        <div class="mb-3">
                          <img :src="baseUrl+'/documentos/7/control_biometrico/huellas/'+ingresante.nro_doc+'.jpg'" height="80"/>
                          <div class="flex justify-center"> H. Der.</div>
                        </div>

                        <div>
                          <img :src="baseUrl+'/documentos/7/control_biometrico/huellas/'+ingresante.nro_doc+'x.jpg'" height="80" />
                          <div class="flex justify-center"> H. Izq.</div>
                        </div>
                      </a-col>
                      <a-col :xs="24" :sm="12" :md="12" :lg="5" :xl="5">
                        <img :src="baseUrl+'/documentos/7/control_biometrico/fotos/'+ingresante.nro_doc+'.jpg'" width="250" />

                      </a-col>

                      <a-col :xs="24" :sm="12" :md="12" :lg="5" :xl="17">
                        <div class="flex justify-end mb-0">
                          <a-button class="btn-actualizar" @click="actualizar()"><span style="font-weight:bold;"> Actualizar Datos </span> </a-button>
                        </div>
                      </a-col>

                      <a-col :xs="24" :sm="12" :md="12" :lg="8" :xl="7">
                        <div class="flex justify-center" style="margin-top: -15px; "> <span style="padding: 0px 2px; font-size: 2.6rem; margin-top:2px; font-weight: bold;">DNI {{ ingresante.nro_doc }}</span></div>
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
                          <a-input v-model:value="ingresante.proceso" />
                        </a-form-item>
                      </a-col>

                      <a-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Modalidad</label>
                          <a-input v-model:value="ingresante.modalidad"/>
                        </a-form-item>
                      </a-col>

                      <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Programa</label>
                          <a-input v-model:value="ingresante.programa" />
                        </a-form-item>
                      </a-col>

                      <a-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Puntaje</label>
                          <a-input v-model:value="ingresante.puntaje" />
                        </a-form-item>
                      </a-col>

                      <a-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12">
                        <a-form-item :rules="[{ required: true, message: 'El nombre es obligatorio' }]">
                          <label>Fecha ingreso</label>
                          <a-input v-model:value="ingresante.fecha" />
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
                        </a-col>
                      </a-row>
                    </a-card>

                </div>
              </a-tab-pane>


              <a-tab-pane key="1" tab="Solicitud" class="pl-2 pr-2">
                <div>
                  <div style="width:100%; height:380px; position:relative; overflow:hidden">
                    <div v-if="dniseleccionado !== null && dniseleccionado.length === 8">
                      <iframe :src="baseUrl+'/documentos/7/preinscripcion/solicitudes/'+dniseleccionado+'.pdf'" style="top:-54px; position:absolute" width="100%" height="100%" scrolling="yes" frameborder="1" ></iframe>
                    </div>
                </div>
                </div>
              </a-tab-pane>
              <a-tab-pane key="4" tab="Const. Inscripcion">
                <div>
                  <div style="width:100%; height:380px; position:relative; overflow:hidden">
                    <div v-if="dniseleccionado !== null && dniseleccionado.length === 8">
                      <iframe :src="baseUrl+'/documentos/7/inscripciones/constancias/'+dniseleccionado+'.pdf'" style="top:-54px; position:absolute" width="100%" height="470px"   scrolling="yes" frameborder="1" ></iframe>
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
                            <img :src="baseUrl+'/documentos/7/inscripciones/fotos/'+dniseleccionado+'.jpg'"/>
                            <div class="flex justify-center"> Foto Inscripción.</div>
                          </div>
                        </a-col>                        
                        <a-col :xs="24" :sm="12" :md="8" :lg="12">
                          <div class="p-6">
                            <img :src="baseUrl+'/documentos/7/control_biometrico/fotos/'+dniseleccionado+'.jpg'"/>
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
                            <img :src="baseUrl+'/documentos/7/inscripciones/huellas/'+dniseleccionado+'.jpg'"/>
                            <div class="flex justify-center"> H. inscripción</div>
                          </div>
                        </a-col>                        
                        <a-col :xs="24" :sm="12" :md="8" :lg="5">
                          <div>
                            <img :src="baseUrl+'/documentos/7/inscripciones/huellas/'+dniseleccionado+'x.jpg'"/>
                            <div class="flex justify-center"> H. inscripción</div>
                          </div>
                        </a-col>
                        <a-col :xs="24" :sm="12" :md="8" :lg="4">
                          <div>
                            <img :src="baseUrl+'/documentos/7/examen/huellas/'+dniseleccionado+'.jpg'"/>
                            <div class="flex justify-center"> H. Examen</div>
                          </div>
                        </a-col>
                        <a-col :xs="24" :sm="12" :md="8" :lg="5">
                          <div>
                            <img :src="baseUrl+'/documentos/7/control_biometrico/huellas/'+dniseleccionado+'.jpg'"/>
                            <div class="flex justify-center"> H. Biometrico</div>
                          </div>
                        </a-col>
                        <a-col :xs="24" :sm="12" :md="8" :lg="5">
                          <div>
                            <img :src="baseUrl+'/documentos/7/control_biometrico/huellas/'+dniseleccionado+'x.jpg'"/>
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
        <a-button type="primary"  @click="abrirVentana()">Imprimir</a-button>
      </div>
    </a-card> -->

    <div style="max-width:100%;">
      <div style="max-width:1000px">

      </div>
    </div>
    <a-modal v-model:visible="modal" :closable="false" :maskClosable="false" style="width:1200px;" centered >

      <div class="flex justify-center">
        <span style="font-size:1.4rem; font-weight:bold;">Información del postulante</span>
      </div>

      <div style="margin-top:20px;">
        <h2>DNI</h2>
      </div>
      <div>
        <div style="width:100%; height:380px; position:relative; overflow:hidden">
          <div v-if="dniseleccionado !== null && dniseleccionado.length === 8">
            <iframe :src="baseUrl+'/documentos/7/inscripciones/dnis/'+codigo+dniseleccionado+'.pdf'" style="top:-54px; position:absolute" width="100%" height="470px"   scrolling="yes" frameborder="1" ></iframe>
          </div>
        </div>
      </div>

      <div style="margin-top:20px;">
        <h2>Certificado de estudios</h2>
      </div>
      <div>
        <div style="width:100%; height:380px; position:relative; overflow:hidden">
          <div v-if="dniseleccionado !== null && dniseleccionado.length === 8">
            <iframe :src="baseUrl+'/documentos/7/inscripciones/certificados/'+dniseleccionado+codigo+'.pdf'" style="top:-54px; position:absolute" width="100%" height="470px"   scrolling="yes" frameborder="1" ></iframe>
          </div>
        </div>
      </div>


      <div style="margin-top:20px;">
        <h2>Solicitud de inscripción</h2>
      </div>
      <div>
          <div style="width:100%; height:400px; position:relative; overflow:hidden">
            <div v-if="dniseleccionado !== null && dniseleccionado.length === 8">
              <iframe :src="baseUrl+'/documentos/7/preinscripcion/solicitudes/'+dniseleccionado+'.pdf'" style="top:-54px; position:absolute" width="100%" height="100%" scrolling="yes" frameborder="1" ></iframe>
            </div>
          </div>
      </div>

      <div style="margin-top:-20px;">
        <h2>Constancia de inscripción</h2>
      </div>
      <div>
        <div style="width:100%; height:380px; position:relative; overflow:hidden">
          <div v-if="dniseleccionado !== null && dniseleccionado.length === 8">
            <iframe :src="baseUrl+'/documentos/7/inscripciones/constancias/'+dniseleccionado+'.pdf'" style="top:-54px; position:absolute" width="100%" height="470px"   scrolling="yes" frameborder="1" ></iframe>
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
                    <img :src="baseUrl+'/documentos/7/inscripciones/fotos/'+dniseleccionado+'.jpg'"/>
                    <div class="flex justify-center"> Foto Inscripción.</div>
                  </div>
                </a-col>                        
                <a-col :xs="24" :sm="12" :md="8" :lg="12">
                  <div class="p-6">
                    <img :src="baseUrl+'/documentos/7/control_biometrico/fotos/'+dniseleccionado+'.jpg'"/>
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
                  <img :src="baseUrl+'/documentos/7/inscripciones/huellas/'+dniseleccionado+'.jpg'"/>
                  <div class="flex justify-center"> H. inscripción</div>
                </div>
              </a-col>                        
              <a-col :xs="24" :sm="12" :md="8" :lg="5">
                <div>
                  <img :src="baseUrl+'/documentos/7/inscripciones/huellas/'+dniseleccionado+'x.jpg'"/>
                  <div class="flex justify-center"> H. inscripción</div>
                </div>
              </a-col>
              <a-col :xs="24" :sm="12" :md="8" :lg="4">
                <div>
                  <img :src="baseUrl+'/documentos/7/examen/huellas/'+dniseleccionado+'.jpg'"/>
                  <div class="flex justify-center"> H. Examen</div>
                </div>
              </a-col>
              <a-col :xs="24" :sm="12" :md="8" :lg="5">
                <div>
                  <img :src="baseUrl+'/documentos/7/control_biometrico/huellas/'+dniseleccionado+'.jpg'"/>
                  <div class="flex justify-center"> H. Biometrico</div>
                </div>
              </a-col>
              <a-col :xs="24" :sm="12" :md="8" :lg="5">
                <div>
                  <img :src="baseUrl+'/documentos/7/control_biometrico/huellas/'+dniseleccionado+'x.jpg'"/>
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
import { FormOutlined, DeleteOutlined, PrinterOutlined, CreditCardOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';
import Vouchers from './components/voucher.vue'
import Anterior from './components/anteriores.vue'
import dayjs from 'dayjs';
import { format, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';

const baseUrl = window.location.origin;

const dni = ref(null);
const dniseleccionado = ref("")
const modal = ref(false);
const codigo = ref("");
const postulante = ref("");
const postulantes = ref([])
const anteriores = ref([]);
const n_carrera = ref(0)

function focusInput() { save() }
const checkedList = ref([]);
const options = [
  { label: 'Solicitud', value: 1 },
  { label: 'Vouchers', value: 2 },
  { label: 'Certificado', value: 3 }
]

const checkAll = ref(true);

const onCheckAllChange = (e) => {
  checkAll.value = e.target.checked;
  checkedList.value = e.target.checked ? options.map((option) => option.value) : [];
};

const onCheckboxChange = (checkedValues) => {
  checkedList.value = checkedValues;
  checkAll.value = checkedValues.length === options.length;
};

const requisitos = ref([]);
const getRequisitos = async () => {
  let res = await axios.get('get-requisitos');
  requisitos.value = res.data.datos;
}

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
  puesto:""
})

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
  if(res.data.datos.fecha){ ingresante.value.fecha = res.data.datos.fecha }
  getCarrerasPrevias()
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

const getPostulantes =  async (term = "", page = 1) => {
  let res = await axios.post(
      "get-postulantes?page=" + page,
      { term: dni.value }
  );
  postulantes.value = res.data.datos.data;
}

const getPostulantesBiometrico =  async (term = "", page = 1) => {
  let res = await axios.post(
      "get-postulantes-biometrico?page=" + page,
      { term: buscar.value }
  );
  postulantes.value = res.data.datos.data;
}

const getPostulantesByDni = async () => {
  let res = await axios.post("get-postulante-dni",{ dni: dniseleccionado.value });
  postulante.value.id = res.data.datos.id_postulante;
  postulante.value.dni_temp = res.data.datos.dni
}

watch(dni, (newValue, oldValue ) => {
  if(newValue.length >= 0){
    // getPostulantes();
    getPostulantesByDni()
  } 
})

let timeoutId;
watch(buscar, ( newValue, oldValue ) => { 
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        getPostulantesBiometrico(); 
    }, 500);    
})

watch(codigo, (newValue, oldValue ) => {
  if(newValue.length == 6 &&  dniseleccionado.value.length == 8 ){
    modal.value = true;
  } 
})

watch(dniseleccionado, (newValue, oldValue ) => {
    if(newValue.length >= 8){
      if(codigo.value.length == 6){
        modal.value = true;
      }
      getIngresante();
    }
})

const abrirVentana = async () => {
  let res = await axios.post("control-biometrico",
  { dni: dniseleccionado.value, n_carrera: n_carrera.value });
  imprimirPDF(res.data.datos);
  dniseleccionado.value = null
}

const imprimirPDF =  (dnni) => {
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = baseUrl+'/documentos/7/control_biometrico/constancias/'+dnni+'.pdf';
    document.body.appendChild(iframe);
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
}

const getCarrerasPrevias = async() => {
  anteriores.value = []
  n_carrera.value = 0

  try {
    if(ingresante.value.dni != null){
      const response = await axios.post('https://service2.unap.edu.pe/TieneCarrerasPrevias/',  {
        doc_:ingresante.value.nro_doc,
        nom_:ingresante.value.nombres,
        app_:ingresante.value.primer_apellido,
        apm_:ingresante.value.segundo_apellido
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

getPostulantesBiometrico()

const notificacion = (type, titulo, mensaje) => {
  notification[type]({
    message: titulo,
    description: mensaje,
  });
};


const dataSource = ref([
  {
    key: '1',
    name: 'Derechos de admisión',
    age: '20-23-2024',
    address: '150.00',
  },
  {
    key: '2',
    name: 'Examen médico',
    age: '20-23-2024',
    address: '200.00',
  }
]);


const columns = ref([
  {
    title: 'Banco',
    dataIndex: 'banco',
    width:'110px',
  },
  {
    title: 'Concepto',
    dataIndex: 'name',
    key: 'name',
  },
  {
    title: 'Fecha',
    dataIndex: 'age',
    key: 'age',
    width:'190px',
    align:'center'
  },
  {
    title: 'Monto S/',
    dataIndex: 'address',
    key: 'address',
    width:'130px',
    align:'center'

  },
  {
    title: '',
    dataIndex: 'option',
    width:'80px',

  }
])

const colpostulantes = ref([
  {
    title: 'DNI',
    dataIndex: 'dni',
    width:'110px',
  },
  {
    title: 'Nombres',
    dataIndex: 'nombres'
  },
  {
    title: 'Programa',
    dataIndex: 'programa',
    key: 'name',
  },
  {
    title: 'Modalidad',
    dataIndex: 'modalidad',
    align:'center'
  },
  {
    title: 'Area',
    dataIndex: 'area',
    align:'center'
  },
  {
    title: 'Codigo',
    dataIndex: 'codigo',
    align:'center'
  },
  {
    title: '',
    dataIndex: 'acciones',
    width:'120px',

  }
])



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
