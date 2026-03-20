<template>
    <Head title="Preinscripciones"/>
    <Layout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 pb-0 mb-4">    
    <row class="mb-4" >
        <div class="flex justify-between">
            <div class="ml-0 mt-1">
                <span style="font-size: 1.5rem;">Preinscripción</span> <span style="font-size: 1.4rem;"> - {{ totalRegistros }} registros</span>
            </div>
            <div class="flex">
                <div class="mr-1"><a-button @click="descargarResumen()" class="bg-blue-900" style="color: white; border:none;"><div class="flex"><div class="mr-2" style="margin-top: -3px;"><DownloadOutlined/></div> RESUMEN </div></a-button></div>
                <div class="mr-1"><a-button @click="descargarExcel()" class="bg-green-600" style="color: white; border:none;"><div class="flex"><div class="mr-2" style="margin-top: -3px;"><DownloadOutlined/></div> EXCEL </div></a-button></div>
                <div><a-button @click="descargarDetalle()" class="bg-red-600" style="color: white; border:none;"><div class="flex"><div class="mr-2" style="margin-top: -3px;"><DownloadOutlined/></div> PDF </div></a-button></div>
            </div>
        </div>

        <div class="flex mt-4 mb-6 justify-between">
            <div>
                <div class="mr-1">
                    <div><a-button class="bg-gray-400" style="color: white; border:none;"><div class="flex"><div class="mr-2" style="margin-top: -3px;" disabled><PlusOutlined/></div> Nuevo Registro </div></a-button></div>
                </div>
            </div>
            <div class="flex">
                <div class="mr-1">
                    <a-select ref="select" v-model:value="programa" placeholder="Seleccionar programa de estudios" :options="programasautorizados" allowClear></a-select>
                </div>
                <div class="flex justify-between" style="position: relative;" >
                    <a-input type="text" placeholder="Escribe para buscar..!!" v-model:value="buscar" style="max-width: 200px; padding-left: 10px;">
                        <template #prefix> <search-outlined/></template>
                    </a-input>
                </div>
            </div>
        </div>

    </row>

    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg py-4"> 
    <a-table  :columns="columnsInscripcion" :data-source="inscripciones" :pagination="false" size="small"> 
        <template #bodyCell="{ column, index, record }">

            <template v-if="column.dataIndex === 'dni'" >
                <a-tag color="#476175" style="padding-top: 3px;">
                    <span style="font-size: .81rem; font-weight: bold;">{{ record.dni }}</span>
                </a-tag>
            </template>

            <template v-if="column.dataIndex === 'postulante'" >
                <span style="font-size: 0.9rem;">{{ record.paterno }} {{ record.materno }}, {{ record.nombres }}</span>
            </template>

            <template v-if="column.dataIndex === 'modalidad'" >
                <span style="font-size: 0.8rem;">{{ record.modalidad }}</span>
            </template>

            <template v-if="column.dataIndex === 'programa'" >
                <span style="font-size: 0.8rem; text-transform: uppercase;">
                    {{ record.programa }}
                </span>
            </template>

            <template v-if="column.dataIndex === 'estado'" >
                <a-tag v-if="record.estado === 0" style="font-size: .5rem;" color="#476175">INSCRITO</a-tag>
                <a-tag v-else color="#b01030" style="font-size: .7rem;">SIN INSCRIPCIÓN</a-tag>
            </template>

            <template v-if="column.dataIndex === 'acciones'">
                <div class="flex justify-center" style="">
                    <div class="mr-1">
                        <a-button type="true" label="Inscribir" @click="abrirModalInscribir(record)" size="small" style="background:#f3f3f3; height: 30px; border: solid 1px #d9d9d9; color:gray; display: flex; align-items: center;">Inscribir</a-button>
                    </div>
                    <div class="mr-1">
                        <a-button type="true" @click="abrirEditar(record)" size="small" style="background:#f3f3f3; height: 30px; border: solid 1px #d9d9d9; color:blue; display: flex; align-items: center;"> <form-outlined/> </a-button>
                    </div>
                    <div class="">
                        <a-popconfirm
                        title="¿Estás seguro de eliminar este registro?"
                        ok-text="Sí"
                        cancel-text="No"
                        @confirm="eliminarPreinscripcion(record)"
                        >
                        <a-button 
                            type="text" 
                            danger 
                            size="small"
                            style="height: 30px; display: flex; align-items: center; gap: 4px;">
                            <delete-outlined />
                            Eliminar
                        </a-button>
                        </a-popconfirm>
                    </div>
                </div>

            </template>
        </template>
  
    </a-table> 
    <a-pagination v-model:current="pagina" :total="totalRegistros"  v-model:pageSize="pageSize" show-less-items />
    
    </div>
    
    </Layout>
    
    <div>
        <a-modal v-model:open="visible" title="Modificar Pre inscripción" style="margin-top: -40px;">
            <a-form
                ref="formRef"
                name="custom-validation"
                :model="formState"
                v-bind="layout"
                @finish="handleFinish"
                @validate="handleValidate"
                @finishFailed="handleFinishFailed"
                >
                <a-form-item has-feedback name="nombre">
                    <label>Postulante</label>
                    <a-input type="text" v-model:value="postulante.nombre"/>
                </a-form-item>
                <a-form-item has-feedback name="postulante">
                    <label>Programa</label>
                    <div class="">
                        <a-select
                            ref="select"
                            v-model:value="inscripcion.id_programa"
                            placeholder="Seleccionar programa"
                            class="selector-modalidad"
                            style="width: 100%;"
                            :options="programasselect"
                            >
                        </a-select>
                    </div>
                </a-form-item>
                <a-form-item has-feedback name="tipo">
                    <label>Modalidad</label>
                        <a-select
                            ref="select"
                            v-model:value="inscripcion.id_modalidad"
                            placeholder="Seleccionar programa"
                            class="selector-modalidad"
                            style="width: 100%;">
                            <a-select-option :value='1'>GRADUADOS Y TITULADOS</a-select-option>
                        </a-select>
                </a-form-item>
                <a-form-item has-feedback name="nombre">
                    <label>Observaciones</label>
                    <a-textarea type="text" v-model:value="inscripcion.observacion" autocomplete="off" />
                </a-form-item>
            </a-form>
    
        <template #footer>
            <a-button style="margin-left: 10px;" @click="resetForm">Cancelar</a-button>
            <a-button type="primary" @click="guardar()">Guardar</a-button>
        </template>
        </a-modal>
    </div>

    <a-modal v-model:open="modalInscribir" title="Inscripción" style="margin-top: -40px; min-width: 900px;">

        <Inscribir :dni="postulante.dni"/>

        <div>
            <div class="p-4">

                <a-card>
                <div class="mb-4">
                    <div><span style="font-weight: bold; font-size: 1.3rem;"> DATOS DE INSCRIPCION</span></div>
                </div>
                <a-form
                    ref="formRef"
                    name="custom-validation"
                    :model="formState"
                    v-bind="layout"
                    @finish="handleFinish"
                    @validate="handleValidate"
                    @finishFailed="handleFinishFailed"
                    >
                    <a-form-item has-feedback name="nombre">
                        <label>Postulante</label>
                        <a-input type="text" v-model:value="postulante.nombre"/>
                    </a-form-item>
                    <a-form-item has-feedback name="postulante">
                        <label>Programa</label>
                        <div class="">
                            <a-select
                                ref="select"
                                v-model:value="inscripcion.id_programa"
                                placeholder="Seleccionar programa"
                                class="selector-modalidad"
                                style="width: 100%;"
                                :options="programasselect"
                                >
                            </a-select>
                        </div>
                    </a-form-item>
                    <a-form-item has-feedback name="tipo">
                        <label>Modalidad</label>
                        <a-select
                                ref="select"
                                v-model:value="inscripcion.id_modalidad"
                                placeholder="Seleccionar programa"
                                class="selector-modalidad"
                                style="width: 100%;">
                                <a-select-option :value='1'>GRADUADOS Y TITULADOS</a-select-option>
                            </a-select>
                    </a-form-item>
                    <a-form-item has-feedback name="nombre">
                        <label>Observaciones</label>
                        <a-textarea type="text" v-model:value="inscripcion.observacion" autocomplete="off" />
                    </a-form-item>
                </a-form>
                </a-card>
            </div>

        </div>
        <template #footer>
            <div class="mr-4">
                <a-button style="margin-left: 10px;" @click="resetForm">Cancelar</a-button>
                <a-button type="primary" @click="guardarInscripcion()">Guardar</a-button>
            </div>

        </template>

    </a-modal>
    
</template>
        
<script setup>
import { Head } from '@inertiajs/vue3';
import Layout from '@/Layouts/segundas-especialidades/LayoutDirector.vue'
import { watch, computed, ref, unref } from 'vue';
import { FormOutlined, PlusOutlined, DownloadOutlined, DeleteOutlined, SearchOutlined, EyeOutlined} from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';
import Inscribir from './components/Perfil.vue';
const baseUrl = window.location.origin;

const programasautorizados = ref([]);
const programasselect = ref([]);
const programa = ref(null);
const buscar = ref("");
const inscripciones = ref([])
const modalInscribir = ref(false);
const visible = ref(false)
const pagina = ref(1)
const totalRegistros = ref(null)
const pageSize = ref(20)
const inscripcion = ref({ 
    id:null, 
    codigo:"", 
    id_posulante:"", 
    id_programa:"", 
    id_modalidad:"",
    estado: true, 
    observacion:"",
})
const postulante = ref({ id:"", nombre:"", dni:""})

const abrirModalInscribir = (item) => { 
    inscripcion.value.id = item.id;
    inscripcion.value.codigo = item.codigo;
    inscripcion.value.id_programa = item.id_programa;
    inscripcion.value.id_modalidad = item.id_modalidad;
    inscripcion.value.observacion = item.observacion;
    postulante.value.id = item.id_postulante;
    postulante.value.dni = item.dni;
    postulante.value.nombre = item.dni+" - "+item.nombres +" "+ item.paterno +" "+ item.materno;
    modalInscribir.value = true; 

};
const showModalPrograma = () => { visible.value = true; };

watch(buscar, ( newValue, oldValue ) => { getInscripciones() })
watch(pageSize, ( newValue, oldValue ) => { getInscripciones() })
watch(programa, ( newValue, oldValue ) => 
{ 
    if(newValue == 0) {
        programa.value = null;
    } 
    pagina.value = 1;
    getInscripciones() 
})

watch(visible, ( newValue, oldValue ) => {
    if(visible.value == false &&inscripcion.value.id != null ){
        inscripcion.value.id = null, 
        inscripcion.value.id_posulante = "", 
        inscripcion.value.id_programa = "", 
        inscripcion.value.id_modalidad = "",
        inscripcion.value.estado = true, 
        inscripcion.value.observacion = "",
        postulante.value.nombre = null,
        postulante.value.dni = null,
        postulante.value.id = null 
    }
})

watch(pagina, ( newValue, oldValue ) => { getInscripciones(); })

const abrirEditar = (item) => {
    visible.value = true;
    inscripcion.value.id = item.id;
    inscripcion.value.codigo = item.codigo;
    inscripcion.value.id_programa = item.id_programa;
    inscripcion.value.id_modalidad = item.id_modalidad;
    inscripcion.value.observacion = item.observacion;
    postulante.value.id = item.id_postulante;
    postulante.value.dni = item.dni;
    postulante.value.nombre = item.dni+" - "+item.nombres +" "+ item.paterno +" "+ item.materno;
}

const eliminarPreinscripcion = (item) => {
    axios.delete("/segundas/delete-preinscripcion-segundas/"+item.id).then((result) => {
        getInscripciones()
        notificacion('warning',result.data.titulo, result.data.mensaje);
    });
}


const getProgramasSelect =  async ( ) => {
    let res = await axios.post( "/segundas/select-programas-segundas?page="+pagina.value , { term: buscar.value, paginashoja: pageSize.value, programa: programa.value } );
    programasselect.value = res.data.datos;
}
const getProgramasAutorizados =  async ( ) => {
    let res = await axios.post( "/segundas/select-programas-segundas-autorizados?page="+pagina.value , { term: buscar.value, paginashoja: pageSize.value, programa: programa.value } );
    programasautorizados.value = res.data.datos;
}

getProgramasSelect();
getProgramasAutorizados();

const getInscripciones =  async ( ) => {
    let res = await axios.post( "/segundas/get-preinscripciones-segundas?page="+pagina.value , { term: buscar.value, paginashoja: pageSize.value, programa: programa.value } );
    inscripciones.value = res.data.datos.data;
    totalRegistros.value = res.data.datos.total;
}

const guardar = () => {
    let post = {
        id:inscripcion.value.id,
        id_postulante: postulante.value.id,
        id_programa: inscripcion.value.id_programa,
        id_modalidad: inscripcion.value.id_modalidad,
        observacion: inscripcion.value.observacion,
        dni: postulante.value.dni
    };
    axios.post("/segundas/actualizar-preinscripciones-segundas", post).then((result) => {
        getInscripciones()
        notificacion('success',result.data.titulo, result.data.mensaje);
        visible.value = false;
    });
}

const guardarInscripcion = () => {
    let post = {
        id:inscripcion.value.id,
        id_postulante: postulante.value.id,
        id_programa: inscripcion.value.id_programa,
        id_modalidad: inscripcion.value.id_modalidad,
        observacion: inscripcion.value.observacion,
        dni: postulante.value.dni
    };
    axios.post("/segundas/guardar-inscripcion-segundas", post).then((result) => {
        getInscripciones()
        notificacion('success',result.data.titulo, result.data.mensaje);
        modalInscribir.value = false;
        inscripcion.value.id = null;
        inscripcion.value.codigo = null;
        inscripcion.value.id_programa = null;
        inscripcion.value.id_modalidad = null;
        inscripcion.value.observacion = null;
        postulante.value.id = null;
        postulante.value.dni = null;
        postulante.value.nombre = null;
        modalInscribir.value = true; 
    });
}

const cambiarSexo = (postulante, sexo) => {
    let post = {
        id_postulante: postulante,
        sexo: sexo
    };
    axios.post("actualizar-sexo-postulante", post).then((result) => {
        getInscripciones()
        notificacion('success',result.data.titulo, result.data.mensaje);
        visible.value = false;
    });
}

const eliminar = (item) => {
    console.log("eliminar");
    axios.post("eliminar-preinscripcion/",{id: item.id}).then((result) => {
    getInscripciones();
    notificacion('warning', result.data.titulo, result.data.mensaje );
    });
}

const columnsInscripcion = [
    // { title: 'ID', dataIndex: 'id' },
    { title: 'DNI', dataIndex: 'dni', align:'center'},
    { title: 'Postulante', dataIndex: 'postulante'},
    { title: 'Programa', dataIndex:'programa'},
    { title: 'Modalidad', dataIndex:'modalidad', align:'center'},
    { title: 'Estado', dataIndex: 'estado', align:'center'},
    { title: 'Observación', dataIndex: 'observacion'},
    { title: 'Acciones', dataIndex: 'acciones', width:'140px', align:'center'},
];

const selectedRowKeys = ref([]); 

const onSelectChange = changableRowKeys => {
    console.log('selectedRowKeys changed: ', changableRowKeys);
    selectedRowKeys.value = changableRowKeys;
};
const rowSelection = computed(() => {
    return {
    selectedRowKeys: unref(selectedRowKeys),
    onChange: onSelectChange,
    hideDefaultSelections: true,
    };
});

const notificacion = (type, titulo, mensaje) => { notification[type]({ message: titulo, description: mensaje, }); };
getInscripciones()



const descargarDetalle = async (items) => {
  try {
    const response = await axios.post('/segundas/get-detalle-preinscripcion-segundas', 
    { term: buscar.value, programa: programa.value },
    {
      responseType: 'blob'
    });

    if (response.status !== 200) {
      throw new Error('Error al obtener el archivo');
    }

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    const fecha = new Date();
    const formatoFecha = `${fecha.getDate().toString().padStart(2, '0')}-${(fecha.getMonth() + 1).toString().padStart(2, '0')}-${fecha.getFullYear()}_${fecha.getHours().toString().padStart(2, '0')}-${fecha.getMinutes().toString().padStart(2, '0')}-${fecha.getSeconds().toString().padStart(2, '0')}`;
    const nombreArchivo = `${formatoFecha}_preinscritos.pdf`;
    link.setAttribute('download', nombreArchivo);
    document.body.appendChild(link);
    link.click();

    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error al descargar el archivo:', error);
  }
};


const descargarResumen = async (items) => {
  try {
    const response = await axios.post('/segundas/get-resumen-preinscripcion-segundas', 
    { term: buscar.value, programa: programa.value },
    {
      responseType: 'blob'
    });

    if (response.status !== 200) {
      throw new Error('Error al obtener el archivo');
    }

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    const fecha = new Date();
    const formatoFecha = `${fecha.getDate().toString().padStart(2, '0')}-${(fecha.getMonth() + 1).toString().padStart(2, '0')}-${fecha.getFullYear()}_${fecha.getHours().toString().padStart(2, '0')}-${fecha.getMinutes().toString().padStart(2, '0')}-${fecha.getSeconds().toString().padStart(2, '0')}`;
    const nombreArchivo = `${formatoFecha}_resumen.pdf`;
    link.setAttribute('download', nombreArchivo);
    document.body.appendChild(link);
    link.click();

    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error al descargar el archivo:', error);
  }
};


// const descargarExcel = async (items) => {
//   try {
//     const response = await axios.post('/export-excel', 
//     { term: buscar.value, programa: programa.value },
//     {
//       responseType: 'blob'
//     });

//     if (response.status !== 200) {
//       throw new Error('Error al obtener el archivo');
//     }

//     const url = window.URL.createObjectURL(new Blob([response.data]));
//     const link = document.createElement('a');
//     link.href = url;
//     const fecha = new Date();
//     const formatoFecha = `${fecha.getDate().toString().padStart(2, '0')}-${(fecha.getMonth() + 1).toString().padStart(2, '0')}-${fecha.getFullYear()}_${fecha.getHours().toString().padStart(2, '0')}-${fecha.getMinutes().toString().padStart(2, '0')}-${fecha.getSeconds().toString().padStart(2, '0')}`;
//     const nombreArchivo = `${formatoFecha}_resumen.xlsx`;
//     link.setAttribute('download', nombreArchivo);
//     document.body.appendChild(link);
//     link.click();

//     document.body.removeChild(link);
//     window.URL.revokeObjectURL(url);
//   } catch (error) {
//     console.error('Error al descargar el archivo:', error);
//   }
// };



const descargarExcel = async () => {
  try {
    const response = await axios.post("/export-excel", 
    { term: buscar.value, programa: programa.value },
    { responseType: "blob" });
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "preinscritos.xlsx");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error("Error al exportar:", error);
  }
};

</script>
