<template>
    <Head title="Inscripciones-impresión"/>
    <AuthenticatedLayout title="Revision documentos">
        <div class="page-wrapper">

            <!-- SEARCH BAR -->
            <div class="search-section">
                <a-auto-complete
                    v-model:value="dniseleccionado"
                    :options="postulantes"
                    style="width: 100%; max-width: 500px;"
                    @select="onSelect"
                    @search="onSearch"
                >
                    <a-input placeholder="Buscar postulante por DNI o nombre..." v-model:value="dni" size="large">
                        <template #prefix>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;color:#94a3b8;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        </template>
                    </a-input>
                    <template #option="{ value: val, label: lab }">
                        <div class="search-option">
                            <div><span class="search-dni">{{ val }}</span></div>
                            <div><span class="search-name">{{ lab }}</span></div>
                        </div>
                    </template>
                </a-auto-complete>
            </div>

            <!-- LOADING -->
            <div v-if="loading" class="loading-state">
                <a-spin size="large" tip="Cargando datos del postulante..."/>
            </div>

            <!-- CONTENT -->
            <div v-else-if="postulante.primer_apellido !== ''" class="content-wrapper">

                <!-- ALERTS -->
                <div v-if="anteriores && anteriores.length > 0" class="alert-banner alert-warning">
                    POSTULANTE A SEGUNDO PROGRAMA
                </div>
                <div v-if="observados.includes(dniseleccionado)" class="alert-banner alert-danger">
                    NO PASÓ CONTROL BIOMÉTRICO — REQUIERE PAGO ADICIONAL
                </div>

                <!-- HEADER -->
                <div class="profile-header">
                    <div class="profile-info">
                        <h1 class="profile-name">{{ postulante.primer_apellido }} {{ postulante.segundo_apellido }}</h1>
                        <h2 class="profile-prenames">{{ postulante.nombres }}</h2>
                        <div class="profile-meta">
                            <span class="profile-tag">DNI: {{ postulante.dni_temp }}</span>
                            <span class="profile-tag">{{ postulante.programa }}</span>
                            <span class="profile-tag">{{ postulante.modalidad }}</span>
                        </div>
                    </div>
                    <div class="profile-process">
                        <div class="process-box">
                            <span class="process-label">Proceso</span>
                            <span class="process-name">{{ postulante.proceso || '—' }}</span>
                        </div>
                    </div>
                </div>

                <!-- TWO COLUMN LAYOUT -->
                <div class="two-columns">

                    <!-- LEFT COLUMN -->
                    <div class="left-column">

                        <!-- Datos Personales -->
                        <div class="card">
                            <div class="card-header">Datos Personales</div>
                            <div class="form-grid">
                                <div class="field-group">
                                    <label>Primer apellido</label>
                                    <a-input v-model:value="postulante.primer_apellido" placeholder="Primer apellido" size="middle" />
                                </div>
                                <div class="field-group">
                                    <label>Segundo apellido</label>
                                    <a-input v-model:value="postulante.segundo_apellido" placeholder="Segundo apellido" size="middle" />
                                </div>
                                <div class="field-group full-width">
                                    <label>Prenombres</label>
                                    <a-input v-model:value="postulante.nombres" placeholder="Prenombres" size="middle" />
                                </div>
                                <div class="field-group">
                                    <label>Fecha de nacimiento</label>
                                    <a-date-picker style="width: 100%;" v-model:value="postulante.fec_nacimiento" format="DD/MM/YYYY" size="middle" />
                                </div>
                                <div class="field-group">
                                    <label>Sexo</label>
                                    <a-select v-model:value="postulante.sexo" style="width: 100%" size="middle">
                                        <a-select-option value="1">Masculino</a-select-option>
                                        <a-select-option value="2">Femenino</a-select-option>
                                    </a-select>
                                </div>
                                <div class="field-group">
                                    <label>Procedencia</label>
                                    <a-input v-model:value="postulante.procedencia" disabled placeholder="Procedencia" size="middle" />
                                </div>
                                <div class="field-group">
                                    <label>Proceso</label>
                                    <a-input v-model:value="postulante.proceso" disabled placeholder="Proceso" size="middle" />
                                </div>
                            </div>
                        </div>

                        <!-- Educación -->
                        <div class="card">
                            <div class="card-header">Educación</div>
                            <div class="form-grid">
                                <div class="field-group full-width">
                                    <label>Colegio</label>
                                    <a-input :class="postulante.id_gestion == 1 ? 'borde-azul' : 'borde-naranja'" v-model:value="postulante.colegio" placeholder="Colegio" size="middle" />
                                </div>
                                <div class="field-group">
                                    <label>Modalidad</label>
                                    <a-select v-model:value="postulante.modalidad" style="width: 100%" disabled size="middle">
                                        <a-select-option :value="7">PERSONAS CON DISCAPACIDAD</a-select-option>
                                        <a-select-option :value="8">EXAMEN GENERAL</a-select-option>
                                        <a-select-option :value="9">CEPREUNA</a-select-option>
                                    </a-select>
                                </div>
                                <div class="field-group">
                                    <label>Programa de estudios</label>
                                    <a-select v-model:value="postulante.programa" style="width: 100%" disabled size="middle">
                                        <a-select-option v-for="p in programas" :key="p.value" :value="p.value">
                                            {{ p.label }}
                                        </a-select-option>
                                    </a-select>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="right-column">

                        <!-- Fotos -->
                        <div class="card">
                            <div class="card-header">Fotos</div>
                            <div class="image-grid">
                                <div class="image-box">
                                    <img v-if="foto_postulante" :src="baseUrl+'/'+foto_postulante" @error="e => e.target.src = baseUrl+'/fotos/postulantex.jpg'"/>
                                    <img v-else :src="baseUrl+'/fotos/postulantex.jpg'"/>
                                    <span class="image-label">Foto Registro</span>
                                </div>
                            </div>
                        </div>

                        <!-- Huellas -->
                        <div class="card">
                            <div class="card-header">Huellas Dactilares</div>
                            <div class="image-grid">
                                <div class="image-box">
                                    <img v-if="huellaI_postulante" :src="baseUrl+'/'+huellaI_postulante" @error="e => e.target.src = baseUrl+'/huellas/huella.jpg'"/>
                                    <img v-else :src="baseUrl+'/huellas/huella.jpg'"/>
                                    <span class="image-label">Huella Izquierda</span>
                                </div>
                                <div class="image-box">
                                    <img v-if="huellaD_postulante" :src="baseUrl+'/'+huellaD_postulante" @error="e => e.target.src = baseUrl+'/huellas/huella.jpg'"/>
                                    <img v-else :src="baseUrl+'/huellas/huella.jpg'"/>
                                    <span class="image-label">Huella Derecha</span>
                                </div>
                            </div>
                        </div>

                        <!-- Comprobantes -->
                        <div class="card">
                            <div class="card-header">Comprobantes de Pago</div>
                            <Vouchers :dni="dniseleccionado"/>
                        </div>

                        <!-- Documentos -->
                        <div class="card">
                            <div class="card-header">Documentos</div>
                            <a-table :dataSource="documentos" :columns="colDocumentos" size="middle" :pagination="false">
                                <template #bodyCell="{ column, record }">
                                    <template v-if="column.dataIndex === 'verificado'">
                                        <a-tag v-if="record.verificado == 1" color="green">Verificado</a-tag>
                                        <a-tag v-else color="pink">No verificado</a-tag>
                                    </template>
                                    <template v-if="column.dataIndex === 'acciones'">
                                        <a-button class="action-btn" @click="validar(record)" size="middle">Validar</a-button>
                                        <a-button class="action-btn" @click="Editar(record)" size="middle">Editar</a-button>
                                    </template>
                                </template>
                            </a-table>
                        </div>

                        <!-- Preinscripción -->
                        <div class="card">
                            <div class="card-header">Preinscripción</div>
                            <a-table :dataSource="preinscripciones" :columns="colPreinscripciones" size="middle" :pagination="false">
                                <template #bodyCell="{ column, record }">
                                    <template v-if="column.dataIndex === 'estado'">
                                        <a-tag v-if="record.estado == 1" color="green">Disponible</a-tag>
                                        <a-tag v-else color="pink">Bloqueado</a-tag>
                                    </template>
                                    <template v-if="column.dataIndex === 'acciones'">
                                        <a-button type="primary" disabled size="middle">Ver</a-button>
                                    </template>
                                </template>
                            </a-table>
                        </div>

                        <!-- Inscripciones -->
                        <div class="card">
                            <div class="card-header">Inscripciones</div>
                            <a-table :dataSource="inscripciones" :columns="colPreinscripciones" size="middle" :pagination="false">
                                <template #bodyCell="{ column, record }">
                                    <template v-if="column.dataIndex === 'estado'">
                                        <a-tag v-if="record.estado == 1" color="green">Sin inscripción</a-tag>
                                        <a-tag v-else color="orange">Inscrito</a-tag>
                                    </template>
                                    <template v-if="column.dataIndex === 'acciones'">
                                        <a-button type="primary" disabled size="middle">Ver</a-button>
                                    </template>
                                </template>
                            </a-table>
                        </div>

                    </div>
                </div>

            </div>

            <!-- EMPTY STATE -->
            <div v-else class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                </div>
                <p>Busque un postulante para ver su ficha de inscripción</p>
            </div>

        </div>

        <!-- ACTION BAR -->
        <div class="action-bar" v-if="inscripciones">
            <a-button class="btn-outline" @click="actualizarPostulante">Actualizar Datos</a-button>
            <a-button class="btn-outline" @click="imprimirPDF(dniseleccionado)">Imprimir Constancia</a-button>
            <a-popconfirm v-if="inscripciones.length == 0" title="¿Seguro de inscribir?" @confirm="confirm" cancelText="NO" placement="topRight" okText="SI" @cancel="cancel">
                <a-button class="btn-primary">Inscribir</a-button>
            </a-popconfirm>
            <a-button v-else class="btn-disabled" disabled>Ya Inscrito</a-button>
        </div>

        <!-- MODAL -->
        <a-modal v-model:visible="openModal" title="Editar Código" :footer="false" width="500px">
            <div style="margin-bottom: 12px; font-weight: 500;">Código de certificado</div>
            <a-input v-model:value="doc.codigo" placeholder="Ingresar Código" size="large"></a-input>
            <div style="display: flex; justify-content: flex-end; margin-top: 20px; gap: 10px;">
                <a-button @click="openModal = false">Cancelar</a-button>
                <a-button @click="CambiarCodigo" class="btn-primary">Cambiar código</a-button>
            </div>
        </a-modal>

    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue'
import {ref, watch} from 'vue'
import dayjs from 'dayjs';
import { format } from 'date-fns';
import { notification } from 'ant-design-vue';
import Vouchers from './components/voucherBN.vue'

const baseUrl = window.location.origin;
const foto_postulante = ref(null);
const huellaD_postulante = ref(null);
const huellaI_postulante = ref(null);
const loading = ref(false);

const doc = ref({id:null, codigo: "" });
const openModal = ref(false);

const Editar = async (item) => {
    openModal.value = true;
    doc.value = item;
}

const dni = ref("")
const dniseleccionado = ref(null)
const preinscripciones = ref([])
const inscripciones = ref([])
const postulantes = ref([])

const postulante = ref({
    id:"",
    nombres:"",
    postulante_foto: null,
    primer_apellido:"",
    segundo_apellido:"",
    sexo: '1',
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
    dni_temp:"",
    id_gestion:""
})

const documentos = ref([])
const anteriores = ref([])

const programas = [
    { value: 1, label: 'Administración' },
    { value: 2, label: 'Antropología' },
    { value: 3, label: 'Arquitectura y Urbanismo' },
    { value: 4, label: 'Arte: Artes plásticas' },
    { value: 5, label: 'Arte: Danza' },
    { value: 6, label: 'Arte: Musica' },
    { value: 7, label: 'Biología: Ecología' },
    { value: 8, label: 'Biología: Microbiología y laboratorio clínico' },
    { value: 9, label: 'Biología: Pesquería' },
    { value: 10, label: 'Ciencias contables' },
    { value: 11, label: 'Ciencias de la comunicación' },
    { value: 12, label: 'Ciencias físisco matemáticas: Física' },
    { value: 13, label: 'Ciencias físisco matemáticas: Matemática' },
    { value: 14, label: 'Derecho' },
    { value: 15, label: 'Educación física' },
    { value: 16, label: 'Educación Inicial' },
    { value: 17, label: 'Educación primaria' },
    { value: 18, label: 'Educ. Sec. de la especialidad de ciencia, tecnología y ambiente' },
    { value: 19, label: 'Educ. Sec. de la especialidad de Ciencias Sociales' },
    { value: 20, label: 'Educ. Sec. de la especialidad de Lengua, Literatura, psicología y filosofía' },
    { value: 21, label: 'Educ. Sec. de la especialidad de Matemática, física, computación e informática' },
    { value: 22, label: 'Enfermería' },
    { value: 23, label: 'Ingeniería Agrícola' },
    { value: 24, label: 'Ingeniería Agroindustrial' },
    { value: 25, label: 'Ingeniería Civil' },
    { value: 26, label: 'Ingeniería de Minas' },
    { value: 27, label: 'Ingeniería de Sistemas' },
    { value: 28, label: 'Ingeniería Económica' },
    { value: 29, label: 'Ingeniería Electrónica' },
    { value: 30, label: 'Ingeniería Estadística e informática' },
    { value: 31, label: 'Ingeniería Geológica' },
    { value: 32, label: 'Ingeniería Mecánica eléctrica' },
    { value: 33, label: 'Ingeniería Metalúrgica' },
    { value: 34, label: 'Ingeniería Química' },
    { value: 35, label: 'Ingeniería Topográfica y Agrimensura' },
    { value: 36, label: 'Medicina Humana' },
    { value: 37, label: 'Medicina Veterinaria y zootecnia' },
    { value: 38, label: 'Nutrición Humana' },
    { value: 39, label: 'Odontología' },
    { value: 40, label: 'Sociología' },
    { value: 41, label: 'Trabajo Social' },
    { value: 42, label: 'Turismo' }
]

const getPostulantes = async (term = "", page = 1) => {
    let res = await axios.post("get-postulantes?page=" + page, { term: dni.value });
    postulantes.value = res.data.datos.data;
}

const getPostulantesByDni = async () => {
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
        postulante.value.id_modalidad = res.data.datos.id_modalidad;
        postulante.value.dni_temp = res.data.datos.dni;
        foto_postulante.value = res.data.foto;
        huellaD_postulante.value = res.data.huellaD;
        huellaI_postulante.value = res.data.huellaI;
    }
}

const getDocumentos = async () => {
    if (dniseleccionado.value && dniseleccionado.value.length === 8 && /^[0-9]+$/.test(dniseleccionado.value)) {
        let res = await axios.get("get-documentos-postulante/" + dniseleccionado.value);
        documentos.value = res.data.datos;
    }
}

const getPreinscripciones = async () => {
    if (dniseleccionado.value && dniseleccionado.value.length === 8 && /^[0-9]+$/.test(dniseleccionado.value)) {
        let res = await axios.get("get-preinscripciones-postulante/" + dniseleccionado.value);
        preinscripciones.value = res.data.datos;
    }
}

const getInscripciones = async () => {
    if (dniseleccionado.value && dniseleccionado.value.length === 8 && /^[0-9]+$/.test(dniseleccionado.value)) {
        let res = await axios.get("get-inscripciones-postulante/" + dniseleccionado.value);
        inscripciones.value = res.data.datos;
    }
}

const getAnteriores = async () => {
    if (dniseleccionado.value && dniseleccionado.value.length === 8 && /^[0-9]+$/.test(dniseleccionado.value)) {
        let res = await axios.get("/carreras-previas/" + dniseleccionado.value);
        anteriores.value = res.data.datos;
    }
}

const validar = async (doc) => {
    let temp = doc.verificado === 1 ? 0 : 1;
    let res = await axios.post("cambiar-estado", {id: doc.id, estado: temp });
    getDocumentos();
}

const CambiarCodigo = async () => {
    let res = await axios.post("cambiar-codigo", doc.value);
    doc.value = {
        id:"",
        codigo:"",
        id_programa:"",
        dni_temp:""
    }
    openModal.value = false;
    getDocumentos();
}

const Inscribir = async () => {
    let res = await axios.post("inscribir", { postulante: postulante.value });
    imprimirPDF(dniseleccionado.value)
    dniseleccionado.value = "";
    dni.value = "";
    postulante.value = {
        id:"",
        nombres:"",
        postulante_foto: null,
        primer_apellido:"",
        segundo_apellido:"",
        sexo:'1',
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
    '61001464','60068800','60066705','60171563','61320806','61064509','70279455','60324574','60658775','60065685','61094020','61093375',
    '71154109','73741501','75055639','73810787','74059827','61320079','61063914',
    '61255036','76333236','60536341','61093328','61152794','61093741','60476909','60558336','60303055','73770883',
    '60220640','74205408','60836931','60065670','60066325','61255092','60214886','61094388','61063205','60525904'
]

let timeout2;
watch(dni, (newValue, oldValue) => {
    clearTimeout(timeout2);
    timeout2 = setTimeout(() => {
        if(dni.value && dni.value.length >= 3){
            getPostulantes();
        }
    }, 500);
})

let timeout;
watch(dniseleccionado, (newValue, oldValue) => {
    clearTimeout(timeout);
    timeout = setTimeout(async () => {
        if(dniseleccionado.value && dniseleccionado.value.length === 8 && /^[0-9]+$/.test(dniseleccionado.value)){
            loading.value = true;
            await Promise.all([
                getPreinscripciones(),
                getInscripciones(),
                getPostulantesByDni(),
                getDocumentos(),
                getAnteriores()
            ]);
            loading.value = false;
        }
    }, 200);
})

const onSelect = (value) => {
    dniseleccionado.value = value;
}

const onSearch = (value) => {
    dni.value = value;
}

const imprimirPDF = (dnni) => {
    var iframe = document.createElement('iframe');
    iframe.style.display = "none";
    iframe.src = baseUrl+'/documentos/'+postulante.value.id_proceso+'/inscripciones/constancias/'+dnni+'.pdf';
    document.body.appendChild(iframe);
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
}

const notificacion = (type, titulo, mensaje) => {
    notification[type]({ message: titulo, description: mensaje });
}

const actualizarPostulante = async () => {
    let res = await axios.post("actualizar-postulante", {
        id: postulante.value.id,
        nombres: postulante.value.nombres,
        primer_apellido: postulante.value.primer_apellido,
        segundo_apellido: postulante.value.segundo_apellido,
        fec_nacimiento: postulante.value.fec_nacimiento ? format(new Date(postulante.value.fec_nacimiento), 'yyyy-MM-dd') : '',
        sexo: postulante.value.sexo
    });
    if(res.data.estado === true ){
        notificacion(res.data.tipo, res.data.titulo, res.data.mensaje)
    }
}

const colDocumentos = [
    { title: 'Codigo', dataIndex: 'codigo', key: 'codigo'},
    { title: 'Documento', dataIndex: 'nombre', key: 'nombre'},
    { title: 'Tipo', dataIndex: 'tipo', key: 'tipo'},
    { title: 'Estado', dataIndex: 'verificado'},
    { title: 'Acciones', dataIndex: 'acciones', width:'100px'}
]

const colPreinscripciones = [
    { title: 'Programa', dataIndex: 'programa', key: 'programa'},
    { title: 'Proceso', dataIndex: 'proceso', key: 'proceso'},
    { title: 'Modalidad', dataIndex: 'modalidad', key: 'modalidad'},
    { title: 'Estado', dataIndex: 'estado', key: 'estado'},
    { title: 'Ver', dataIndex: 'acciones', width:'80px'},
]

const confirm = e => { Inscribir(); };
const cancel = e => { console.log('Cancelado'); };

getPostulantes()
</script>

<style scoped>
/* LAYOUT */
.page-wrapper {
    background: #f1f5f9;
    padding: 0 16px 16px;
    min-height: calc(100vh - 140px);
}

/* SEARCH */
.search-section {
    display: flex;
    justify-content: center;
    padding: 1.5rem 0 1rem;
}

.search-option {
    padding: 4px 0;
}
.search-dni {
    font-weight: 700;
    color: #1e293b;
    font-size: .85rem;
}
.search-name {
    font-size: .8rem;
    text-transform: uppercase;
    color: #64748b;
}

/* LOADING */
.loading-state {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 60vh;
}

/* CONTENT */
.content-wrapper {
    max-width: 1200px;
    margin: 0 auto;
}

/* ALERTS */
.alert-banner {
    text-align: center;
    padding: .75rem 1.5rem;
    font-size: .85rem;
    font-weight: 600;
    border-radius: 8px;
    margin-bottom: 16px;
}
.alert-warning {
    background: #fef3c7;
    color: #b45309;
}
.alert-danger {
    background: #fee2e2;
    color: #dc2626;
}

/* PROFILE HEADER */
.profile-header {
    display: flex;
    align-items: stretch;
    background: linear-gradient(135deg, #1B3A5C, #2D5A8E);
    color: #fff;
    padding: 2rem 2.5rem;
    gap: 2rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}
.profile-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.profile-name {
    font-size: 2rem;
    font-weight: 800;
    margin: 0;
    line-height: 1.2;
}
.profile-prenames {
    font-size: 1.25rem;
    font-weight: 400;
    margin: .25rem 0 0;
    color: rgba(255,255,255,.85);
}
.profile-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    margin-top: .75rem;
}
.profile-tag {
    font-size: .8rem;
    font-weight: 500;
    padding: .3rem .75rem;
    background: rgba(255,255,255,.12);
    border-radius: 8px;
}
.profile-process {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}
.process-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: .75rem 1.25rem;
    background: rgba(255,255,255,.12);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,.1);
}
.process-label {
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: rgba(255,255,255,.6);
}
.process-name {
    font-size: 1rem;
    font-weight: 700;
    margin-top: .125rem;
}

/* TWO COLUMNS */
.two-columns {
    display: flex;
    gap: 1.5rem;
}
.left-column {
    flex: 1;
    min-width: 0;
}
.right-column {
    flex: 1.5;
    min-width: 0;
}

/* CARDS */
.card {
    background: #ffffff;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    border: 1px solid #e2e8f0;
}
.card:last-child {
    margin-bottom: 0;
}
.card-header {
    font-size: .95rem;
    font-weight: 700;
    color: #1B3A5C;
    margin: 0 0 1rem;
    padding-bottom: .5rem;
    border-bottom: 2px solid #eef2f6;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* FORM GRID */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.field-group {
    display: flex;
    flex-direction: column;
}
.field-group.full-width {
    grid-column: 1 / -1;
}
.field-group label {
    font-size: .75rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: .25rem;
}
.field-group :deep(.ant-input),
.field-group :deep(.ant-select-selector),
.field-group :deep(.ant-picker) {
    border-radius: 8px !important;
    height: 40px !important;
    font-size: .9rem !important;
}
.field-group :deep(.ant-input) {
    padding: 8px 12px !important;
}
.field-group :deep(.ant-select-selector) {
    padding: 4px 12px !important;
}

/* IMAGE GRID */
.image-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.image-box {
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
}
.image-box img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}
.image-label {
    display: block;
    text-align: center;
    padding: 6px 0;
    font-size: .75rem;
    font-weight: 600;
    color: #64748b;
    background: #f1f5f9;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* BORDERS */
.borde-azul {
    border: 2px solid #3b82f6 !important;
    border-radius: 8px;
    background: #eff6ff !important;
}
.borde-naranja {
    border: 2px solid #f97316 !important;
    border-radius: 8px;
    background: #fff7ed !important;
}

/* TABLE */
.card :deep(.ant-table) {
    font-size: .85rem;
}
.card :deep(.ant-table-thead > tr > th) {
    background: #f8fafc !important;
    font-weight: 600;
    color: #1e293b;
}
.card :deep(.ant-table-tbody > tr:hover > td) {
    background: #f1f5f9 !important;
}

/* ACTION BUTTONS */
.action-btn {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    color: #1B3A5C !important;
    font-weight: 500;
}
.action-btn:hover {
    background: #eef2f6 !important;
}

/* ACTION BAR */
.action-bar {
    position: sticky;
    bottom: 0;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: .75rem;
    padding: 1rem 1.5rem;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    z-index: 100;
}
.btn-outline {
    border: 2px solid #1B3A5C !important;
    color: #1B3A5C !important;
    font-weight: 600;
    border-radius: 8px !important;
    height: 42px !important;
    padding: 0 1.5rem !important;
}
.btn-outline:hover {
    background: #eef2f6 !important;
    border-color: #2D5A8E !important;
}
.btn-primary {
    background: #1B3A5C !important;
    border: none !important;
    color: #fff !important;
    font-weight: 600;
    border-radius: 8px !important;
    height: 42px !important;
    padding: 0 2rem !important;
}
.btn-primary:hover {
    background: #2D5A8E !important;
}
.btn-disabled {
    background: #e2e8f0 !important;
    color: #94a3b8 !important;
    border: none !important;
    font-weight: 600;
    border-radius: 8px !important;
    height: 42px !important;
    padding: 0 2rem !important;
    cursor: not-allowed;
}

/* EMPTY STATE */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 5rem 2rem;
    color: #94a3b8;
    background: #fff;
    border-radius: 16px;
    margin: 2rem auto;
    max-width: 500px;
}
.empty-icon svg {
    width: 64px;
    height: 64px;
    color: #cbd5e1;
}
.empty-state p {
    margin-top: 1rem;
    font-size: 1rem;
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .two-columns {
        flex-direction: column;
    }
    .left-column, .right-column {
        flex: 1;
    }
}
@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.5rem;
    }
    .profile-process {
        align-self: flex-start;
    }
    .form-grid {
        grid-template-columns: 1fr;
    }
    .image-grid {
        grid-template-columns: 1fr;
    }
    .profile-meta {
        flex-direction: column;
        gap: .375rem;
    }
    .action-bar {
        flex-wrap: wrap;
        justify-content: center;
    }
    .action-bar .ant-btn {
        flex: 1;
        min-width: 140px;
    }
    .page-wrapper {
        padding: 12px;
    }
    .card {
        padding: 1rem;
    }
}
@media (max-width: 480px) {
    .profile-name {
        font-size: 1.5rem;
    }
    .profile-prenames {
        font-size: 1rem;
    }
}
</style>