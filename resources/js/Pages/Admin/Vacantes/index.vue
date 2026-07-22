<template>
<Head title="Procesos"/>
<AuthenticatedLayout>
<div class="overflow-hidden shadow-sm sm:rounded-lg p-4" style="height: calc(100vh - 98px); background: var(--card-bg); border: 1px solid var(--card-border); color: var(--card-text);">
<row class="flex justify-between mb-4" >
    <div class="mr-3">
        <a-select
            ref="select"
            v-model:value="modalidad"
            :options="modalidades"
            @focus="focus"
            @change="handleChange"
            >
        </a-select>

    </div>
    <div class="flex justify-between" style="position: relative;" >
    <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px;">

        <template #prefix> <search-outlined /> </template>
    </a-input>
    </div>
</row>

<div style="">
    <a-table
        :columns="columnsProgramas"
        :data-source="programas"
        :pagination="false"
        size="small"
        :scroll="{ x: 380, y: 'calc(100vh - 240px)' }"
        >
        <template #bodyCell="{ column, index, record }">

            <template v-if="column.dataIndex === 'vacantes'">
              <div @dblclick="enableEdit(record.id_programa)">
                <a-input-number
                  v-if="editingId === record.id_programa"
                  v-model:value="record.vacantes"
                  :min="0"
                  @pressEnter="save(record)"
                  @blur="save(record)"
                  autofocus
                />
                <span v-else>
                  {{ record.vacantes || '0' }}
                </span>
              </div>
            </template>

            <template v-if="column.dataIndex === 'estado'">
                <div class="flex" style="justify-content: center;">
                    <div v-if="record.estado == 1">
                        <a-tag color="blue">Activo</a-tag>
                    </div>
                    <div v-else-if="record.estado == 0">
                        <a-tag color="red">Inactivo</a-tag>
                    </div>
                    <div v-else>
                        <a-tag color="#cdcdcd">Sin asignar</a-tag>
                    </div>
                </div>
            </template>

            <template v-if="column.dataIndex === 'acciones'">
              <div style="display: flex; gap: 2px;">
                <a-button @click="abrirEditar(record)" size="small" style="background: var(--card-bg, #ffffff); height: 28px; border: 1px solid var(--card-border, #d9d9d9); color: #1890ff; display: flex; align-items: center;">
                  <form-outlined />
                </a-button>
                <a-button @click="eliminar(record)" size="small" style="background: var(--card-bg, #ffffff); height: 28px; border: 1px solid var(--card-border, #d9d9d9); color: #ff4d4f; display: flex; align-items: center;">
                  <delete-outlined />
                </a-button>
              </div>
            </template>
        </template>
    </a-table>
</div>
<div class="flex" style="justify-content: flex-end;">
    <a-pagination v-model:current="pagina" simple page-size="50" :total="totalpaginas" />
</div>

</div>

</AuthenticatedLayout>

</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { watch, computed, ref, unref, reactive } from 'vue';
import { EyeOutlined, FormOutlined, EditOutlined, DeleteOutlined, SearchOutlined, CheckOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';


const modalVacates = ref(false);
const buscar = ref("");
const pagina = ref(1)
const totalpaginas = ref(null)
const modalidades = ref([])
const modalidad = ref(1)

const visible = ref(false);
const buscarDep = ref("")
const programas = ref([])
const programa = ref({
    id:null,
    codigo:"",
    nombre:"",
    vacantes:"",
    estado:true,
})

const showModalPrograma = () => {
    visible.value = true;
};

watch(buscar, ( newValue, oldValue ) => { getProgramas() })
watch(modalidad, ( newValue, oldValue ) => { getProgramas() })

watch(visible, ( newValue, oldValue ) => {
if(visible.value == false && programa.value.id != null ){
    programa.value.id = null;
    programa.value.codigo = null;
    programa.value.nombre = null;
    programa.value.estado = true;
}
})

watch(pagina, ( newValue, oldValue ) => {
    getProgramas()
})

const abrirEditar = (item) => {

    visible.value = true;
    programa.value.id = item.id;
    programa.value.codigo = item.codigo;
    programa.value.nombre = item.nombre;
    programa.value.nivel_academico = item.nivel_academico;
    programa.value.tipo_autorizacion = item.tipo_autorizacion;
    programa.value.id_facultad = item.id_fac;
    if(item.estado == 1){ programa.value.estado = true }
    else { programa.value.estado = false}
    programa.value.area = item.area
}


const getModalidades =  async ( ) => {
    let res = await axios.get("get-modalidades-activas");
    modalidades.value = res.data.datos;
}


const getProgramas =  async (term = "") => {
    let res = await axios.post( "get-vacantes-admin?page=" + pagina.value, { modalidad: modalidad.value, term: buscar.value } );
    programas.value = res.data.datos.data;
    totalpaginas.value = res.data.datos.total;
}

const eliminar = (item) => {
    axios.post("delete-vacante",{id_vacante:item.id_vacante }).then((result) => {
    getProgramas();
    notificacion('warning', 'PROGRAMA ELIMINADO', result.data.mensaje );
    });
}

const columnsProgramas = [
    { title: 'Cod', dataIndex: 'codigo_sunedu', width:'60px', align:'center', responsive: ['md'],},
    { title: 'Nombre', dataIndex: 'programa'},
    { title: 'Vacantes', dataIndex: 'vacantes',  width:'160px', align:'center', responsive: ['md'],},
    { title: 'Estado', dataIndex: 'estado', align:'center', width:'80px', responsive: ['md'],},
    { title: 'Opciones', dataIndex: 'acciones', width:"80px", align:'center'},
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

const notificacion = (type, titulo, mensaje) => {
    notification[type]({
    message: titulo,
    description: mensaje,
    });
};

const verDetalle = (item) => {
    console.log("Detalle:", item);
};

getModalidades()
getProgramas()


const editingId = ref(null);
const editableData = ref({});

const enableEdit = (id) => {
  editingId.value = id;
};

// const save = async (record) => {
//   editingId.value = null;
//   try {
//     await axios.post("save-numero-vacantes", {
//       id_vacante: record.id_vacante,
//       vacantes: record.vacantes
//     });
//   } catch (error) {
//     getProgramas(); // Recargar si hay error
//   }
// };

const form  = ref({
    id_modalidad: modalidad.value,
    id_programa: null,
    estado: null,
    vacantes: null,
    id_vacante : null
});

const save = (item) => {
    editingId.value = null;
    form.value = item;
    delete editableData.value[item.id_programa];

    axios.post("save-numero-vacantes", {
        id_vacante: item.id_vacante,
        id_programa: item.id_programa,
        vacantes: item.vacantes,
        id_modalidad: modalidad.value
    })
    .then(() => {
        notification.success({ message: "Registro actualizado" });
        getProgramas();
    })
    .catch(() => {
        notification.error({ message: "Error al actualizar" });
    });
};


</script>

<style >
::-webkit-scrollbar {
width: 9px;
height: 12px;
}

::-webkit-scrollbar-track {
background: #f1f1f1;
border-radius: 10px;
}

::-webkit-scrollbar-thumb {
background: #888;
border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
background: #555;
}

/* Estilo para un scroll específico */
.scroll-container {
overflow-y: auto;
scrollbar-width: thin; /* Firefox */
scrollbar-color: #888 #f1f1f1; /* Firefox */
}

/* Estilo para el scroll específico en Webkit (Chrome, Safari) */
.scroll-container::-webkit-scrollbar {
width: 12px;
height: 12px;
}

.scroll-container::-webkit-scrollbar-track {
background: #f1f1f1;
border-radius: 10px;
}

.scroll-container::-webkit-scrollbar-thumb {
background: #888;
border-radius: 10px;
}

.scroll-container::-webkit-scrollbar-thumb:hover {
background: #555;
}



.editable-cell {
  position: relative;
  .editable-cell-input-wrapper,
  .editable-cell-text-wrapper {
    padding-right: 24px;
  }

  .editable-cell-text-wrapper {
    padding: 5px 24px 5px 5px;
  }

  .editable-cell-icon,
  .editable-cell-icon-check {
    position: absolute;
    right: 0;
    width: 20px;
    cursor: pointer;
  }

  .editable-cell-icon {
    margin-top: 4px;
    display: none;
  }

  .editable-cell-icon-check {
    line-height: 28px;
  }

  .editable-cell-icon:hover,
  .editable-cell-icon-check:hover {
    color: #108ee9;
  }

  .editable-add-btn {
    margin-bottom: 8px;
  }
}
.editable-cell:hover .editable-cell-icon {
  display: inline-block;
}

/* ── Dark / Hybrid theme table overrides ─────────── */
.theme-dark .ant-table,
.theme-hybrid .ant-table {
    background: transparent !important;
    color: var(--card-text) !important;
}
.theme-dark .ant-table-thead > tr > th,
.theme-hybrid .ant-table-thead > tr > th {
    background: var(--table-header-bg) !important;
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
}
.theme-dark .ant-table-tbody > tr > td,
.theme-hybrid .ant-table-tbody > tr > td {
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
    background: var(--card-bg) !important;
}
.theme-dark .ant-table-tbody > tr:hover > td,
.theme-hybrid .ant-table-tbody > tr:hover > td {
    background: var(--hover-bg) !important;
}
.theme-dark .ant-table-tbody > tr:nth-child(even) > td,
.theme-hybrid .ant-table-tbody > tr:nth-child(even) > td {
    background: var(--row-even) !important;
}

</style>