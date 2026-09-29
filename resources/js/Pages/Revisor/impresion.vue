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

    // Sancionado
    if (res.data.estado === true && !res.data.datos) {
        if (res.data.mensaje) {
            const motivo = res.data.motivo ? ` Motivo: ${res.data.motivo}` : '';
            notificacion('warning', 'Postulante observado', res.data.mensaje + motivo);
        } else {
            notificacion('warning', 'Postulante observado', 'El postulante no reúne las condiciones para participar en este proceso.');
        }
        postulante.value.primer_apellido = '';
        return;
    }

    // Sin datos (no encontrado)
    if (!res.data.datos) {
        notificacion('info', 'Sin resultados', 'No se encontraron datos del postulante.');
        return;
    }

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

    // Verificar huellas faltantes
    const erroresHuellas = [];
    if (!res.data.huellaD) erroresHuellas.push('huella derecha');
    if (!res.data.huellaI) erroresHuellas.push('huella izquierda');
    if (erroresHuellas.length > 0) {
        notificacion('error', 'Huellas faltantes', `Falta registrar: ${erroresHuellas.join(' y ')}.`);
    }

    // Verificar foto faltante
    if (!res.data.foto) {
        notificacion('warning', 'Foto faltante', 'El postulante no tiene foto registrada.');
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
    try {
        let res = await axios.post("inscribir", { postulante: postulante.value });
        if (res.data.estado === true) {
            imprimirPDF(dniseleccionado.value);
            notificacion('success', 'Inscrito', 'Postulante inscrito correctamente.');
        } else if (res.data.mensaje) {
            notificacion('error', 'Error', res.data.mensaje);
            return;
        }
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
    } catch (error) {
        const msg = error.response?.data?.mensaje || 'Ocurrió un error durante la inscripción.';
        notificacion('error', 'Error', msg);
    }
}

const observados = [
    '61063222','60366171','60850199','61466239','60538226','71183349','61244318','60068507','76736034',
    '60246642','60417118','61514207','60643835','60908363','61194215','61399931','60176425','61320850',
    '74350941','61458641','61205386','60176468','61816969','60837158','61000844','61143837','60174708',
    '60617901','61566906','60630212','73798764','61432413','60525925'
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
    const url = baseUrl+'/documentos/'+postulante.value.id_proceso+'/inscripciones/constancias/'+dnni+'.pdf';

    fetch(url, { method: 'HEAD' })
        .then(response => {
            if (!response.ok) {
                notificacion('error', 'Error', 'No se encontró la constancia. Verifique que el postulante esté inscrito.');
                return;
            }
            var iframe = document.createElement('iframe');
            iframe.style.display = "none";
            iframe.src = url;
            iframe.onload = function() {
                setTimeout(function() {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                }, 500);
            };
            document.body.appendChild(iframe);
        })
        .catch(() => {
            notificacion('error', 'Error', 'No se pudo cargar el PDF para impresión.');
        });
}

const notificacion = (type, titulo, mensaje) => {
    notification[type]({ message: titulo, description: mensaje, placement: 'topRight' });
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
/* ============================================================================
   Ficha de inscripción — dos columnas: a la izquierda lo que se edita,
   a la derecha lo que se comprueba (fotos, huellas, pagos, documentos).
   La barra de acción es fija al pie: inscribir es irreversible y no debe
   quedar escondida tras un desplazamiento.
   ========================================================================== */

.page-wrapper { display: flex; flex-direction: column; gap: var(--rev-s-6); padding-bottom: 68px; }

/* Búsqueda ---------------------------------------------------------------- */
.search-section {
  display: flex; align-items: center; gap: var(--rev-s-5);
  padding: 10px var(--rev-s-6);
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
}
.search-option { display: flex; flex-direction: column; line-height: 1.3; padding: 2px 0; }
.search-dni { font-family: var(--rev-mono); font-size: var(--rev-fs-sm); font-weight: 680; color: var(--rev-ink); }
.search-name { font-size: var(--rev-fs-sm); color: var(--rev-ink-3); text-transform: capitalize; }

/* Carga y vacío ----------------------------------------------------------- */
.loading-state,
.empty-state {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: var(--rev-s-5);
  padding: var(--rev-s-11) var(--rev-s-7);
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  text-align: center;
}
.empty-icon {
  display: grid; place-items: center;
  width: 44px; height: 44px; border-radius: var(--rev-r-lg);
  background: var(--rev-n-100); border: 1px solid var(--rev-line); color: var(--rev-ink-4);
}
.empty-icon svg { width: 22px; height: 22px; }
.empty-state p, .loading-state p { margin: 0; font-size: var(--rev-fs-md); color: var(--rev-ink-4); max-width: 44ch; }

.content-wrapper { display: flex; flex-direction: column; gap: var(--rev-s-6); }

/* Avisos ------------------------------------------------------------------ */
.alert-banner {
  display: flex; align-items: center; gap: 9px;
  padding: 10px var(--rev-s-6);
  border: 1px solid transparent;
  border-radius: var(--rev-r-lg);
  font-size: var(--rev-fs-md); font-weight: 620;
  letter-spacing: .01em;
}
.alert-banner::before {
  content: ""; flex: none;
  width: 6px; height: 6px; border-radius: 50%;
  background: currentColor;
}
.alert-warning { background: var(--rev-warning-bg); border-color: var(--rev-warning-border); color: var(--rev-warning-ink); }
.alert-danger  { background: var(--rev-danger-bg);  border-color: var(--rev-danger-border);  color: var(--rev-danger-ink); }

/* Cabecera del postulante ------------------------------------------------- */
.profile-header {
  display: flex; align-items: flex-start; justify-content: space-between; gap: var(--rev-s-6);
  padding: var(--rev-s-6);
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
}
.profile-info { min-width: 0; }
.profile-name {
  margin: 0; font-size: var(--rev-fs-2xl); font-weight: 680;
  letter-spacing: -.02em; line-height: 1.15; color: var(--rev-ink);
  text-transform: uppercase;
}
.profile-prenames {
  margin: 1px 0 0; font-size: var(--rev-fs-lg); font-weight: 520;
  color: var(--rev-ink-3); text-transform: uppercase; letter-spacing: .01em;
}
.profile-meta { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: var(--rev-s-5); }
.profile-tag {
  font-size: var(--rev-fs-xs); font-weight: 600;
  color: var(--rev-ink-2); background: var(--rev-n-100);
  border: 1px solid var(--rev-line);
  padding: 2px 8px; border-radius: var(--rev-r-sm);
}
.profile-process { flex: none; }
.process-box {
  display: flex; flex-direction: column; gap: 2px;
  padding: 8px 12px;
  background: var(--rev-primary-50);
  border: 1px solid var(--rev-primary-200);
  border-radius: var(--rev-r-md);
  min-width: 150px;
}
.process-label {
  font-size: var(--rev-fs-2xs); font-weight: 680;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-primary-600);
}
.process-name { font-size: var(--rev-fs-md); font-weight: 640; color: var(--rev-primary-800); }

/* Dos columnas ------------------------------------------------------------ */
.two-columns { display: grid; grid-template-columns: 1.15fr 1fr; gap: var(--rev-s-6); align-items: start; }
.left-column, .right-column { display: flex; flex-direction: column; gap: var(--rev-s-6); min-width: 0; }

/* Bloques ----------------------------------------------------------------- */
.card {
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
  overflow: hidden;
}
.card-header {
  padding: 10px var(--rev-s-6);
  border-bottom: 1px solid var(--rev-line);
  font-size: var(--rev-fs-md); font-weight: 650;
  letter-spacing: var(--rev-track-tight); color: var(--rev-ink);
}
.card > :not(.card-header) { padding: var(--rev-s-6); }
.card > .ant-table-wrapper { padding: 0; }

/* Formulario -------------------------------------------------------------- */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--rev-s-5); }
.field-group { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
.field-group.full-width { grid-column: 1 / -1; }
.field-group label {
  font-size: var(--rev-fs-sm); font-weight: 600; color: var(--rev-ink-3);
}

/* Señal de gestión del colegio: el color dice público/privado, no decora. */
:deep(.borde-azul)   { border-color: var(--rev-primary-400) !important; box-shadow: inset 2px 0 0 var(--rev-primary-500) !important; }
:deep(.borde-naranja){ border-color: #E9A55E !important; box-shadow: inset 2px 0 0 var(--rev-warning) !important; }

/* Imágenes ---------------------------------------------------------------- */
.image-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(128px, 1fr)); gap: var(--rev-s-5); }
.image-box {
  display: flex; flex-direction: column; align-items: center; gap: 7px;
}
.image-box img {
  width: 100%; aspect-ratio: 3 / 4; object-fit: cover;
  background: var(--rev-n-100);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-md);
}
.image-label {
  font-size: var(--rev-fs-2xs); font-weight: 650;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-ink-4);
}

/* Acciones en tabla -------------------------------------------------------- */
.action-btn {
  height: 26px !important; padding: 0 9px !important;
  font-size: var(--rev-fs-sm) !important; font-weight: 560 !important;
  border-radius: var(--rev-r-sm) !important;
  margin-right: 4px;
}

/* Barra de acción fija ----------------------------------------------------- */
.action-bar {
  position: sticky; bottom: 0; z-index: 30;
  display: flex; align-items: center; justify-content: flex-end; gap: var(--rev-s-4);
  margin: 0 calc(-1 * var(--rev-s-8)) calc(-1 * var(--rev-s-10));
  padding: 11px var(--rev-s-8);
  background: rgba(255,255,255,.92);
  backdrop-filter: saturate(180%) blur(8px);
  border-top: 1px solid var(--rev-line);
}
.action-bar :deep(.ant-btn) { height: 34px; }
.btn-outline {
  background: var(--rev-surface) !important;
  border-color: var(--rev-line-strong) !important;
  color: var(--rev-ink-2) !important;
}
.btn-outline:hover {
  border-color: var(--rev-primary-300) !important;
  color: var(--rev-primary-700) !important;
  background: var(--rev-primary-50) !important;
}
.btn-primary {
  background: var(--rev-primary-600) !important;
  border-color: var(--rev-primary-600) !important;
  color: #fff !important;
}
.btn-primary:hover { background: var(--rev-primary-700) !important; border-color: var(--rev-primary-700) !important; }
.btn-disabled {
  background: var(--rev-success-bg) !important;
  border-color: var(--rev-success-border) !important;
  color: var(--rev-success-ink) !important;
}

@media (max-width: 1180px) {
  .two-columns { grid-template-columns: 1fr; }
}
@media (max-width: 720px) {
  .profile-header { flex-direction: column; }
  .form-grid { grid-template-columns: 1fr; }
  .action-bar { margin-inline: calc(-1 * var(--rev-s-5)); padding-inline: var(--rev-s-5); flex-wrap: wrap; }
}
</style>
