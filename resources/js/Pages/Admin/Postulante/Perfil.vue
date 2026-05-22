<template>
    <Head title="Postulantes" />
    <AuthenticatedLayout style="">

        <div class="flex  overflow-hidden shadow-sm sm:rounded-lg  justify-center">
            <div class="bg-white p-5 container">
                <div class="p-2">
                    <a-row class="p-4" :gutter="[16, 8]" style="border: solid #d9d9d9 1px; border-radius: 9px;">
                        <a-col :xs="24" :sm="12" :md="8" :lg="6">
                            <div style="border-radius:100%; width: 100%; overflow:hidden;">
                                <div style="margin-bottom: -30px; margin-top:-30px">
                                    <!-- <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQKTXDwTTnsPx88rP_7Ljrt1SShsD9xbdnmn0prjfe1Ww&s"> -->
                                    <a-image :src="foto" :preview="false" />
                                </div>
                            </div>
                        </a-col>
                        <a-col :xs="24" :sm="12" :md="16" :lg="18">
                            <div class="flex justify-between">
                                <div class="ml-4">
                                    <div class="flex mt-5">
                                        <span style="font-weight: bold; font-size: 2.0rem;">{{ info.nombres }}</span>
                                    </div>
                                    <div style="margin-top:0px;">
                                        <span style="font-size:1.1rem; color:gray; ">{{ info.email }}</span>
                                    </div>
                                    <div style="margin-top:5px;">
                                        <USerOutlined />
                                        <span style="font-size:1.0rem; font-weight:light; color:gray; ">{{ info.celular
                                        }}</span>
                                    </div>
                                    <div style="margin-top:5px;">
                                        <USerOutlined />
                                        <span style="font-size:1.0rem; font-weight:light; color:gray; "> {{
                                            infoColegio.colegio }} - {{ infoColegio.distrito }} </span>
                                        <USerOutlined />
                                        <span style="font-size:1.0rem; font-weight:light; color:gray; "></span>
                                    </div>

                                    <div style="margin-top:5px;">
                                        <USerOutlined />
                                        <span style="font-size:1.0rem; font-weight:light; color:gray; ">{{ info.departamento
                                        }} - {{ info.provincia }} - {{ info.distrito }}</span>
                                    </div>

                                    <!-- Vinculación de usuario -->
                                    <div style="margin-top:8px; padding:6px 10px; border-radius:8px; border:1px solid #d9d9d9; background:#fafafa;">
                                        <div style="font-size:.75rem; color:#8c8c8c; margin-bottom:4px;">Cuenta vinculada</div>
                                        <div v-if="usuarioVinculado" style="display:flex; align-items:center; gap:8px;">
                                            <span style="font-size:.875rem; font-weight:600; color:#389e0d;">✓ {{ usuarioVinculado.name }} (DNI: {{ usuarioVinculado.dni }})</span>
                                            <span style="font-size:.75rem; color:#8c8c8c;">{{ usuarioVinculado.email }}</span>
                                        </div>
                                        <div v-else style="display:flex; align-items:center; gap:8px;">
                                            <span style="font-size:.8125rem; color:#cf1322;">✗ Sin cuenta vinculada</span>
                                            <a-button size="small" type="primary" @click="mostrarVincular = true">Vincular</a-button>
                                        </div>
                                    </div>

                                    <div class="flex" style="margin-top:5px;">
                                        <div class="mr-3" style="display:flex; align-items:center;">
                                            <FormOutlined /> {{ preinscripciones }}
                                        </div>
                                        <div class="mr-3" style="display:flex; align-items:center;">
                                            <DeleteOutlined /> {{ inscripciones }}
                                        </div>
                                        <div class="mr-3" style="display:flex; align-items:center;">
                                            <UserOutlined /> {{ control_biometrico }}
                                        </div>
                                        <div class="mr-3" style="display:flex; align-items:center;">
                                            <DownloadOutlined /> 0
                                        </div>
                                    </div>

                                </div>
                                <div class="mt-6">
                                    <a-button type="primary" @click="abrir()">
                                        Editar
                                        <template #icon>
                                            <DownloadOutlined />
                                        </template>
                                    </a-button>

                                </div>
                            </div>
                            <div>

                            </div>
                        </a-col>
                    </a-row>
                </div>

                <div style="">
                    <a-row :gutter="[16, 8]">
                        <a-col :xs="24" :sm="24" :md="16" :lg="18">
                            <a-row :gutter="[16, 8]" class="mt-4">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-card class="item-area">
                                        <div class="flex justify-between" style="height: 30px; align-content: center;">
                                            <div class="flex justify-center" style="align-items: center;">
                                                <div style="margin-top: 0px;">
                                                    <div style="border-radius: 50%; overflow: hidden; width:40px;">
                                                        <img
                                                            src="https://www.atzegi.eus/modulos/usuariosFtp/conexion/imagenes14254a.jpg">
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div><span style="font-size: 1.5rem; font-weight: bold;">{{
                                                        inscripciones }}</span></div>
                                                    <div style="margin-top:-10px;">
                                                        <span style="color:gray;">Inscripciones</span>
                                                    </div>

                                                </div>
                                            </div>
                                            <div>
                                                <!-- <RightOutlined /> -->
                                            </div>
                                        </div>
                                    </a-card>
                                </a-col>

                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-card class="item-area">
                                        <div class="flex justify-between" style="height: 30px; align-content: center;">
                                            <div class="flex justify-center" style="align-items: center;">
                                                <div style="margin-top: 0px;">
                                                    <div style="border-radius: 50%; overflow: hidden; width:40px;">
                                                        <img
                                                            src="https://w7.pngwing.com/pngs/607/452/png-transparent-woman-support-chat-icon.png">
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div><span style="font-size: 1.5rem; font-weight: bold;"> {{
                                                        preinscripciones }} </span></div>
                                                    <div style="margin-top:-10px;">
                                                        <span style="color:gray;">Preinscripciones</span>
                                                    </div>

                                                </div>
                                            </div>
                                            <div>
                                                <!-- <RightOutlined /> -->
                                            </div>
                                        </div>
                                    </a-card>
                                </a-col>

                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-card class="item-area">
                                        <div class="flex justify-between" style="height: 30px; align-content: center;">
                                            <div class="flex justify-center" style="align-items: center;">
                                                <div style="margin-top: 0px;">
                                                    <div style="border-radius: 50%; overflow: hidden; width:40px;">
                                                        <img src="https://cdn-icons-png.flaticon.com/512/1083/1083522.png">
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div><span style="font-size: 1.5rem; font-weight: bold;"> {{
                                                        control_biometrico }} </span></div>
                                                    <div style="margin-top:-10px;">
                                                        <span style="color:gray;">Ingresos</span>
                                                    </div>

                                                </div>
                                            </div>
                                            <div>
                                                <!-- <RightOutlined /> -->
                                            </div>
                                        </div>
                                    </a-card>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-card class="item-area">
                                        <div class="flex justify-between" style="height: 30px; align-content: center;">
                                            <div class="flex justify-center" style="align-items: center;">
                                                <div style="margin-top: 0px;">
                                                    <div style="border-radius: 50%; overflow: hidden; width:40px;">
                                                        <img
                                                            src="https://c8.alamy.com/compes/2ah7akx/la-educacion-icono-en-color-la-graduacion-grado-academico-persona-academica-en-la-tapa-ilustracion-vectorial-aislado-2ah7akx.jpg">
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div><span style="font-size: 1.5rem; font-weight: bold;">0</span></div>
                                                    <div style="margin-top:-10px;">
                                                        <span style="color:gray;">Carreras terminadas</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <!-- <RightOutlined /> -->
                                            </div>
                                        </div>
                                    </a-card>
                                </a-col>

                            </a-row>
                            <div class=" bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 mt-4 "
                                style="border: solid #d9d9d9 1px;">
                                <Grafico />
                            </div>
                        </a-col>

                        <a-col :xs="24" :sm="24" :md="8" :lg="6">
                            <div class=" bg-white overflow-hidden shadow-sm sm:rounded-lg p-5 mt-4"
                                style="border: solid 1px #d9d9d9; height: 524px;">
                                <div>
                                    <a-card size="small" v-for="item in pro" :key="item.id_proceso"
                                        :class="seleccionado == item.id_proceso ? 'item-inscripciones-seleccionado' : 'item-inscripciones'"
                                        @click="seleccionado = item.id_proceso">
                                        <div class="flex justify-between" style="height: 30px; align-content: center;">
                                            <div class="flex justify-center" style="align-items: center;">
                                                <div style="margin-top: -5px;">
                                                    <BookOutlined />
                                                </div>
                                                <div class="ml-3">
                                                    <span>{{ item.proceso }}</span>
                                                    <!-- <div style="margin-top: -10px; color:gray;"><span style="font-size: .7rem;"> {{ item.codigo }} </span></div> -->
                                                </div>
                                            </div>
                                            <div>
                                                <RightOutlined />
                                            </div>
                                        </div>
                                    </a-card>
                                </div>

                            </div>
                        </a-col>
                    </a-row>
                    <!-- <div style="display:none;" v-if="seleccionado == null">
            {{ seleccionado = pro[0].id_proceso }}
        </div> -->
                </div>

                <div class="p-2">
                    <a-row class="p-4" :gutter="[16, 8]" style="border: solid #d9d9d9 1px; border-radius: 9px;">
                        <div>
                            <h1 style="font-weight: bold; font-size: 1.1rem;">Actividad Reciente</h1>
                        </div>
                        <a-table :dataSource="dataSource" :columns="columns" style="width: 100%;">
                            <template #bodyCell="{ column, record }">
                                <template v-if="column.dataIndex === 'acciones'">
                                    <div class="" style="justify-content: flex-start;">
                                        <span class="mr-2">{{ record.usuario }} </span>
                                            <span> <a-tag v-if="record.acciones == 'insertó'" color="green"> {{ record.acciones }} </a-tag></span>
                                            <span> <a-tag v-if="record.acciones == 'actualizó'" color="blue"> {{ record.acciones }} </a-tag> </span>
                                            <span> <a-tag v-if="record.acciones == 'eliminó'" color="red"> {{ record.acciones }} </a-tag> </span>
                                            el registro <span class="mr-2 ml-2" style="font-weight: bold;"> N° {{ record.registro }} </span> en la tabla {{ record.tabla }}
                                    </div>
                                </template>
                            </template>
                        </a-table>
                    </a-row>
                </div>

             </div>






        </div>

        <a-modal v-model:visible="open" title="Editar Datos">

        </a-modal>

        <!-- Modal Vincular Usuario -->
        <a-modal v-model:visible="mostrarVincular" title="Vincular Postulante con Usuario" @ok="vincularUsuario" :confirmLoading="vinculando" okText="Vincular" cancelText="Cancelar">
            <div style="margin-bottom:12px;">
                <p style="font-size:.8125rem; color:#595959;">Para vincular, busca un usuario por DNI. Se asignará el DNI del postulante a la cuenta del usuario, permitiéndole ver sus datos.</p>
            </div>
            <div style="display:flex; gap:8px; margin-bottom:16px;">
                <a-input v-model:value="dniBuscar" placeholder="Ingresar DNI del usuario" style="flex:1" @pressEnter="buscarUsuario" />
                <a-button type="primary" @click="buscarUsuario" :loading="buscandoUsuario">Buscar</a-button>
            </div>
            <div v-if="usuarioEncontrado" style="padding:12px; border:1px solid #d9d9d9; border-radius:8px; background:#f6ffed;">
                <div style="font-weight:600;">{{ usuarioEncontrado.nombre }}</div>
                <div style="font-size:.8125rem; color:#595959;">DNI: {{ usuarioEncontrado.dni }} | Email: {{ usuarioEncontrado.email }}</div>
                <div v-if="usuarioEncontrado.ya_vinculado" style="font-size:.75rem; color:#d48806; margin-top:4px;">
                    ⚠ Este usuario ya tiene un postulante vinculado (ID: {{ usuarioEncontrado.ya_vinculado }}). Vincularlo reasignará la conexión.
                </div>
                <div v-else style="font-size:.75rem; color:#389e0d; margin-top:4px;">
                    ✓ Disponible para vincular
                </div>
            </div>
            <div v-if="errorBusqueda" style="padding:12px; border:1px solid #ffa39e; border-radius:8px; background:#fff2f0;">
                <span style="color:#cf1322; font-size:.8125rem;">{{ errorBusqueda }}</span>
            </div>
        </a-modal>


        <a-modal v-model:visible="open" title="Modal">
            sdfasdf
        </a-modal>

    </AuthenticatedLayout>
</template>

<script setup>
import { reactive, ref, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { FormOutlined, DeleteOutlined, SearchOutlined, DownloadOutlined, UserOutlined, RightOutlined, BookOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import dayjs from 'dayjs';
import { format } from 'date-fns';
import axios from 'axios';
import Grafico from './components/grafico.vue';

const open = ref(false);
const mostrarVincular = ref(false);
const dniBuscar = ref('');
const usuarioEncontrado = ref(null);
const errorBusqueda = ref('');
const buscandoUsuario = ref(false);
const vinculando = ref(false);

const abrir = () => {
    open.value = true;
    console.log("Esta funcionando");
}

const props = defineProps({
    foto: String,
    info: Object,
    infoColegio: Object,
    preinscripciones: Number,
    inscripciones: Number,
    control_biometrico: Number,
    pro: Array,
    usuarioVinculado: Object,
})

const seleccionado = ref(null);

const buscarUsuario = async () => {
    if (!dniBuscar.value) return;
    buscandoUsuario.value = true;
    errorBusqueda.value = '';
    usuarioEncontrado.value = null;
    try {
        const res = await axios.post('/admin/buscar-usuario-dni', { dni: dniBuscar.value });
        if (res.data.estado) {
            usuarioEncontrado.value = res.data.datos;
        } else {
            errorBusqueda.value = res.data.mensaje;
        }
    } catch (e) {
        errorBusqueda.value = 'Error al buscar usuario';
    } finally {
        buscandoUsuario.value = false;
    }
};

const vincularUsuario = async () => {
    if (!usuarioEncontrado.value || !props.info?.id_postulante) return;
    vinculando.value = true;
    try {
        const res = await axios.post('/admin/vincular-postulante-usuario', {
            postulante_id: props.info.id_postulante,
            user_id: usuarioEncontrado.value.id,
        });
        if (res.data.estado) {
            notification.success({ message: res.data.mensaje });
            mostrarVincular.value = false;
            window.location.reload();
        } else {
            notification.error({ message: res.data.mensaje });
        }
    } catch (e) {
        notification.error({ message: 'Error al vincular' });
    } finally {
        vinculando.value = false;
    }
};


const dataSource = ref([
    {
        usuario_id: 1,
        usuario: 'Ariel Luque',
        acciones: 'insertó',
        registro:2322,
        tabla: 'filial',
        direccion: '127.168.12.1',
        fecha:'2023-11-03 08:55:20'
    },
    {
        usuario_id: 1,
        usuario: 'Luque King',
        acciones: 'actualizó',
        registro:233,
        tabla: 'filial',
        direccion: '127.85.0.1',
        fecha:'2023-11-03 10:55:20'
    },

    {
        usuario_id: 1,
        usuario: 'Madara',
        acciones: 'eliminó',
        registro:126,
        tabla: 'filial',
        direccion: '10.10.120.1',
        fecha:'2023-11-07 10:15:20'
    },
    {
        usuario_id: 1,
        usuario: 'king',
        acciones: 'eliminó',
        registro:219,
        tabla: 'filial',
        direccion: '127.140.192.1',
        fecha:'2023-11-12 08:55:20'
    },


]);

const columns = ref([
    {
        title: 'Actividad',
        dataIndex: 'acciones',
        key: 'age',
        align: 'left'
    },
    {
        title: 'Dirección',
        dataIndex: 'direccion',
        key: 'address',
    },

    {
        title: 'Fecha y hora',
        dataIndex: 'fecha',
        key: 'address',
    },
]);


</script>
<style scoped>
.item-inscripciones {
    cursor: pointer;
    background: white;
    align-items: center;
    border-bottom: 1px solid #d9d9d9;
    border-radius: 8px;
}

.item-inscripciones-seleccionado {
    cursor: pointer;
    background: #cdcdcd92;
    color: black;
    border-radius: 7px;
}

.item-area {
    cursor: pointer;
    background: white;
    align-items: center;
    border: 1px solid #d9d9d9;
    border-radius: 8px;
}

.item-area-seleccionado {
    cursor: pointer;
    background: red;
    color: white;
    border-radius: 7px;
}

.container {
    max-width: 1200px;
}</style>






