<template style="background:pink;">
<Head title="Resultados"/>
<Layout :nombre="props.proceso_seleccionado.nombre">

  <!-- <a-breadcrumb>
    <a-breadcrumb-item >Inicio</a-breadcrumb-item>
    <a-breadcrumb-item><a style="color: #0992e1;" href="">Resultados</a></a-breadcrumb-item>
  </a-breadcrumb> -->

  <a-card>
    <div>
      <h1 style="font-size: 1.7rem;">Resultados <span v-if="props.proceso_seleccionado.nivel == 1">del examen</span></h1>
      <p style="text-align: justify; font-size: 1em;">
        Para consultar la relación de ingresantes del 
        <span v-if="props.proceso_seleccionado.nivel == 1">
          EXAMEN 
        </span>
        {{ props.proceso_seleccionado.nombre }},
        haga clic en el botón "Descargar" correspondiente a la fecha de su interés.
        El archivo se descargará automáticamente, y podrá abrirlo para visualizar el
        listado de ingresantes ordenado por mérito.
      </p>
    </div>

    <div v-for="documento in documentos" :key="documento" class="flex justify-between p-2" style="border-bottom: solid 1px #d9d9d9;">
      <div class="flex" style="min-height: 30px; align-items: center; ">
        <div class="mr-1" style="width: 30px; min-width: 30px;">
          <img src="../../../../../public/imagenes/iconos/image.png" alt="">
        </div>
        <div>
          <span>{{ documento.nombre }}</span>
        </div>

      </div>

      <div>
        <div class="flex">
          <div  v-if="props.admin === 1" class="mr-1">
            <a-button @click="elimarArchivo(documento.id)" size="small" style=" height: 30px; background: crimson; color: white; border: none;">
              <div class="flex">
                <div style="margin-top: -2px;">
                  <DeleteOutlined/>
                </div>
              </div>
            </a-button>
          </div>

          <div class="mr-1">
            <a-button @click="viewFile(baseUrl+'/'+documento.url)" size="small" style=" height: 30px; background: #256d7d; color: white; border: none;">
              <div class="flex">
                <div style="margin-top: -2px;">
                  <EyeOutlined/>
                </div>
              </div>
            </a-button>
          </div>
          <div>
            <a-button @click="downloadFile(baseUrl+'/'+documento.url)" size="small" style=" height: 30px; background: #088dcf; color: white; border: none;">
              <div class="flex">
                <div style="margin-top: -2px;">
                  <DownloadOutlined />
                </div>
              </div>
            </a-button>
          </div>

        </div>



      </div>
    </div>

    <div v-if="props.admin === 1" class="mt-4">
      <a-button @click="abrirModal()" style="height: 36px; background: #088dcf; color: white; border: none;">
        <div class="flex">
          <div class="mr-1">Subir archivo</div>
          <div style="margin-top: -2px;">
            <UploadOutlined/>
          </div>
        </div>
      </a-button>
    </div>


  <div v-if="props.proceso_seleccionado.nivel == 1">


    <div class="mt-6"></div>
    <h1 style="font-size: 1.7rem;">Consultar puntaje</h1>
    <div class="mt-6" style="">
      <div style="margin-top: -10px; text-align: left;">
        <div class="ml-1 mb-2">
          Para consultar el puntaje del EXAMEN {{ props.proceso_seleccionado.nombre  }}, siga estos pasos:
        </div>
        <div class="ml-4">
          <div class="mb-2">1. Ingrese su DNI en el campo de texto proporcionado.</div>
          <div class="mb-2">2. Ingrese el código mostrado en pantalla.</div>
          <div>3. Haga clic en el botón "Consultar Puntaje".</div>
        </div>
      </div>

      <div class=" flex mt-6 justify-left" style="">
        <div style="width: 100%;">
          <div style="min-width: 360px; max-width: 420px;">
            <a-form
                  ref="formRef" :model="formState"
                  name="inicio_dni"
                >
                <div style="margin-bottom: 7px;"><label>N° Documento</label></div>
                <a-form-item
                    name="dni"
                    :rules="[{ required: true, message: 'Por favor ingresa tu DNI', trigger: 'change' },
                    { min: 8, message: 'El dni debe tener 8 digitos', trigger: 'blur',},]"
                  >
                  <a-input v-model:value="formState.dni" @input="dniInput" :maxlength="8" placeholder="N° Documento"/>
                  <!-- <a-input v-model:value="formState.dni" @input="dniInput" :disabled="ingresante" :maxlength="8" placeholder="N° Documento"/> -->
                </a-form-item>
                <div class="texto-imagen">
                  {{ codigo_aleatorio }}
                </div>
                <div class="overlay"></div>
                <div class="mb-4">
                  <div class="mt-3"><label>Código</label></div>
                    <a-form-item
                      name="codigo_secreto"
                      :rules="[{ required: true, message: 'Ingresa el código del cuadro', trigger: 'change' },
                        { min: 6, message: 'El ubigeo debe tener 6 caracteres', trigger: 'blur',},
                        { validator: validateCodigoSecreto, trigger: 'change' }
                        ]"
                      >
                      <a-input v-model:value="formState.codigo_secreto" :maxlength="6" placeholder="Ingresa el codigo"/>
                    </a-form-item>
                  </div>
                <div class="mt-4" style="display: flex; justify-content: left;">
                  <a-button type="primary" v-if="codigo_aleatorio !== formState.codigo_secreto"  disabled>CONSULTAR PUNTAJE</a-button>
                  <a-button @click="getPuntaje()" style="background: goldenrod; border: none; color: white;" v-if="formState.codigo_secreto === codigo_aleatorio">CONSULTAR PUNTAJE</a-button>
                </div>
              </a-form>
          </div>
        </div>
      </div>

    </div>

    <div v-if="resultados.length > 0 " class="mt-6">
        <a-table
        :columns="columnsResultados"
        :data-source="resultados"
        :pagination="false"
        size="small"
        >
        <template #bodyCell="{ column, index, record }" >
            <template v-if="column.dataIndex === 'puntaje'">
                <div>
                    {{ record.puntaje }}
                    <!-- {{  record.puntaje.toFixed(3) }} -->
                </div>
            </template>
            <template v-if="column.dataIndex === 'fecha'">
                <div>
                    {{  voltear(record.fecha) }}
                </div>
            </template>

            <template v-if="column.dataIndex === 'condicion'">
                <div v-if="record.condicion == 'SI'">
                    <a-tag color="blue"> Ingresó </a-tag>
                </div>
                <div v-if="record.condicion == 'NO'">
                    <div class="flex justify-center">
                        <a-tag color="orange"> No ingresó </a-tag>
                    </div>
                </div>
                <div v-if="record.condicion == 'CL'">
                    <div class="flex justify-center">
                        <a-tag color="yellow"> Clasificado </a-tag>
                    </div>
                </div>
            </template>
        </template>
      </a-table>

      <div>

      </div>
    </div>

  </div>  

    <div v-if="ingresante === 3" class="mt-6">
      <a-alert
          message="! MUY IMPORTANTE !"
          description="Los postulantes que hayan alcanzado una vacante deberán subir su Certificado de Estudios y DNI en formato PDF con un peso max. de 2mb"
          type="info">
      </a-alert>
      <div class="mt-6"></div>
      <h1 style="font-size: 1.7rem;">Subir archivos </h1>
      <div>
        <Certificado :id_proceso="props.proceso_seleccionado.id" :dni="formState.dni"/>
      </div>
      <div class="mt-2">
        <Dni :id_proceso="props.proceso_seleccionado.id" :dni="formState.dni"/>
      </div>
    </div>

  </a-card>


  <a-modal v-model:visible="archivomodal" title="Archivo Resultado" :footer="false">
      <a-form
        ref="formRefArchivo"
        :model="formArchivo"
        name="formArchivo"
      >
      <a-radio-group v-model:value="formArchivo.estado"  class="flex justify-end" style="display: flex; width: yellow;" name="radioGroup">
        <a-radio :value="1">Activo</a-radio>
        <a-radio :value="0">Inactivo</a-radio>
      </a-radio-group>

      <div style="margin-bottom: 7px;"><label>Descripción</label></div>
      <a-form-item
          name="descripcion"
          :rules="[{ required: true, message: 'Ingrese la descripción', trigger: 'change' },]"
        >
        <a-input  v-model:value="formArchivo.descripcion" placeholder="Descripción"/>
      </a-form-item>

      <div class="mb-4">
        <div class="mt-3"><label>Observaciónes</label></div>
        <a-form-item name="observacion">
          <a-textarea v-model:value="formArchivo.observacion" :maxlength="100" placeholder="Observación"/>
        </a-form-item>
      </div>

      <div class="mb-4">
        <a-upload-dragger
            v-model:fileList="fileList"
            :before-upload="beforeUpload"
            :multiple="false"
          >
            <div class="flex justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
              </svg>
            </div>
            <p class="ant-upload-text">Haz clic o arrastra el archivo aquí para cargarlo</p>
            <p class="ant-upload-hint">No subir archivos no autorizados</p>
          </a-upload-dragger>
      </div>
    </a-form>

      <div class="flex justify-end">
        <div class="mr-2">
          <a-button @click="cancelar" style="height: 36px; background: none; color: #088dcf; border: #088dcf solid 1px;">
            <div class="mr-1">Cancelar</div>
        </a-button>
        </div>
        <a-button @click="save" style="height: 36px; background: #088dcf; color: white; border: none;">
            <div class="mr-1">Guardar</div>
        </a-button>
      </div>
      </a-modal>

</Layout>

</template>
<script setup>
import Layout from '@/Layouts/LayoutPuntaje.vue'
import { defineProps, watch, reactive, ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { notification } from 'ant-design-vue';
import Certificado from './components/certificado.vue';
import Dni from './components/dni.vue';
import { DownloadOutlined, UploadOutlined, EyeOutlined, DeleteOutlined, DoubleLeftOutlined } from '@ant-design/icons-vue';
import { Footer } from 'ant-design-vue/lib/layout/layout';
const baseUrl = window.location.origin;

const archivomodal = ref(false);

const props = defineProps(['proceso_seleccionado','admin']);
const codigo_secreto = ref("");

const formRefArchivo = ref();
const formArchivo = reactive({ descripcion: '', observacion: '', estado:1, file:[] });

const formRef = ref();
const formState = reactive({ dni: '', codigo_secreto: '', });
const dniInput = (event) => { formState.dni = event.target.value.replace(/\D/g, ''); };


const getPuntaje = async () => {

  ingresante.value = 0;
  try {
    const res = await axios.post(`/get-puntajes-proceso/`, {
      dni: formState.dni,
      id_proceso: props.proceso_seleccionado.id
    });

    if (res.data.estado == false) {
        notificacion("error", "No se han encontrado datos en este proceso", "");
        formState.dni = '';
        resultados.value = [];
    }else{
      if (res.data && res.data.datos) {
        resultados.value = res.data.datos;
        const ingreso = resultados.value.some(item => item.condicion === 'SI');
        ingresante.value = ingreso ? 1 : 0;
        if(ingresante.value == 0){
          formState.dni = '';
        }
        getCodigoAleatorio();

      } else {
        resultados.value = [];
      }
    }



  } catch (error) {
    console.error('Error al obtener puntajes:', error);
    resultados.value = [];
  }
};

function validateCodigoSecreto(rule, value) {
  return new Promise((resolve, reject) => {
    if (!value) {
      reject(new Error('Por favor, ingresa el código secreto.'));
    } else if (codigo_aleatorio.value !== codigo_secreto.value) {
      reject(new Error('El código ingresado no coincide.'));
    } else {
      resolve();
    }
  });
}

const maximos = ref([]);
const getMaximos = async () => {
  let res = await axios.get( "/get-puntajes-maximos-proceso/"+props.proceso_seleccionado.id);
  if(res.data.estado == true){
     maximos.value = res.data.datos;
  }
}

const abrirModal = () => {
  archivomodal.value = true;
}

const documentos = ref([]);
const getDocumentos = async () => {
  let res = await axios.post( "/get-documentos-resultados",{id_proceso: props.proceso_seleccionado.id});
  if(res.data.estado == true){
     documentos.value = res.data.datos;
  }
}

const viewFile = (path) => window.open(path, '_blank');

const downloadFile = async (path) => {
  try {
    const response = await axios({
      url: path,
      method: 'GET',
      responseType: 'blob',
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', path.split('/').pop());
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error('Error descargando archivo:', error);
  }
};

getMaximos();
getDocumentos();

const codigo_aleatorio = ref(null);

const getCodigoAleatorio = async ( ) => {
  let res = await axios.get("/generar-captcha");
  codigo_aleatorio.value = res.data.captcha;
}


const notificacion = (type, titulo, mensaje) => {
  notification[type]({
    message: titulo,
    description: mensaje,
  });
};

const resultados = ref([]);
const ingresante = ref(null);

const columnsResultados = [
  { title: 'Nombre', dataIndex: 'nombres'},
  { title: 'Programa', dataIndex:'programa', responsive: ['sm'], },
  { title: 'Puntaje', dataIndex:'puntaje', align:'center'},
  { title: 'Vocacional', dataIndex:'puntaje_vocacional', align:'center'},
  { title: 'Fecha', dataIndex: 'fecha', align:'center'},
  { title: 'Condición', dataIndex: 'condicion', align:'center'},
];

function voltear(fecha) {
  return fecha.split('-').reverse().join('-');
}

//UPDLOAD
const beforeUpload = (file) => {
  formArchivo.file = [file];
  return false;
};

const save = async () => {

  const values = await formRefArchivo.value.validateFields();
  if (formArchivo.file.length === 0) {
    alert("Por favor, selecciona un archivo primero.");
    return;
  }

  const formData = new FormData();
  formData.append("file", formArchivo.file[0]);
  formData.append("descripcion", formArchivo.descripcion);
  formData.append("estado", formArchivo.estado);
  formData.append("observacion", formArchivo.observacion);
  formData.append("id_proceso", props.proceso_seleccionado.id );

  try {
    const response = await fetch("/save-documento-resultado", {
      method: "POST", body: formData, headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content, },
    });

    if (!response.ok) {
      throw new Error("Error en la subida del archivo");
    }

    const result = await response.json();
      alert("Archivo subido con éxito: " + result.fileName);
      getDocumentos();
      cancelar();

  } catch (error) {
      alert("Error: " + error.message);
  }
};


const elimarArchivo = async ( ide ) => {
  let res = await axios.get("/delete-documento-resultados/"+ide);
  await getDocumentos();
  // notificacion("error", "Archivo eliminado", "");

}





const cancelar = () => {
  formArchivo.file = [];
  formArchivo.descripcion = '';
  formRefArchivo.estado = 1;
  formArchivo.observacion = '';
  archivomodal.value = false;
};


getCodigoAleatorio();

</script>

<style>
.texto-imagen {
    font-size: 2em;
    color: gray;
    font-family: 'Consolas', monospace;
    letter-spacing: 1rem;
    background-size: cover;
    background-clip: text;
    -webkit-background-clip: text;
    display: inline-block;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #ffffff00;
    pointer-events: none;
}
.example{
  color: #088dcf;
  color: #0c428d;
  color: #0e500e;
  color: #2e2e2e;
}
</style>