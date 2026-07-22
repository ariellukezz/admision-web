<template>
    <Head title="Documentos"/>
    <AuthenticatedLayout>
    <div class="overflow-hidden shadow-sm sm:rounded-lg p-4" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">    
    <!-- {{ buscar }} -->
    <row class="flex justify-between mb-4" >
        <div class="mr-3">
        <a-button type="primary" disabled @click="showModalPrograma">Nuevo</a-button>
        </div>
        <div class="flex justify-between" style="position: relative;" >
        <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; padding-left: 30px;"/>
        <div class="mr-2" style="position: absolute; left: 8px; top: 3px; "><search-outlined /></div>
        </div>
    </row>

    <a-table 
        :columns="columnsDocumentos" 
        :data-source="modalidades"
        :pagination="false"
        size="small"
        :scroll="{ y: 'calc(100vh - 320px)' }"
        > 
        <template #bodyCell="{ column, index, record }">

            <template v-if="column.dataIndex === 'estado'" >
                <a-tag v-if="record.estado === 0" color="red">false</a-tag>
                <a-tag v-if="record.estado === 1" color="blue">En uso</a-tag>
            </template>

            <template v-if="column.dataIndex === 'verificado'" >
                <a-tag v-if="record.verificado === 0" color="error">no verificado</a-tag>
                <a-tag v-if="record.verificado === 1" color="success">verificado</a-tag>
            </template>

            <template v-if="column.dataIndex === 'acciones'">
                <a-button type="success" disabled style="background: green; color: white;" @click="abrirEditar(modalidades[index])" size="small">
                    <template #icon><eye-outlined/></template>
                </a-button>
                <a-divider type="vertical" />
                <a-button type="primary" @click="abrirEditar(record)" size="small">
                    <template #icon><form-outlined/></template>
                </a-button>
                <a-divider type="vertical" />
                <a-popconfirm
                    v-if="modalidades.length"
                    title="¿Estas seguro de eliminar?"
                    @confirm="eliminar(modalidades[index])"
                    >
                    <a-button type="danger" shape="" size="small">
                        <template #icon><delete-outlined/></template>
                    </a-button>
                </a-popconfirm>
  
            </template>
        </template>
  
    </a-table> 
    <a-pagination v-model:current="pagina" :total="totalRegistros" show-less-items />
    
    </div>
    
    </AuthenticatedLayout>
    
    <div>
        <a-modal v-model:visible="visible" title="Nuevo Documento" style="margin-top: -40px;">
            <!-- <pre>{{ programa}}</pre> -->
            <a-form
                ref="formRef"
                name="custom-validation"
                :model="formState"
                :rules="rules"
                v-bind="layout"
                @finish="handleFinish"
                @validate="handleValidate"
                @finishFailed="handleFinishFailed"
                >
                <a-form-item has-feedback label="Codigo" name="codigo">
                    <a-input type="text" v-model:value="documento.codigo" autocomplete="off" />
                </a-form-item>
                <a-form-item has-feedback label="Nombre" name="nombre">
                    <a-input type="text" v-model:value="documento.nombre" autocomplete="off" />
                </a-form-item>
                <a-form-item has-feedback label="Postulante" name="postulante">
                    <a-input disabled type="text" v-model:value="documento.postulante" autocomplete="off" />
                </a-form-item>  
                <a-form-item has-feedback label="Tipo" name="tipo">
                    <a-input disabled type="text" v-model:value="documento.tipo" autocomplete="off" />
                </a-form-item>
                <a-form-item has-feedback label="Observacion" name="nombre">
                    <a-input type="text" v-model:value="documento.observacion" autocomplete="off" />
                </a-form-item>
            </a-form>
    
        <template #footer>
            <a-button style="margin-left: 10px;" @click="resetForm">Cancelar</a-button>
            <a-button type="primary" @click="guardar()">Guardar</a-button>
        </template>
        </a-modal>
    </div>
    
    </template>
        
    <script setup>
    import { Head } from '@inertiajs/vue3';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
    import { watch, computed, ref, unref } from 'vue';
    import { FormOutlined, EyeOutlined, DeleteOutlined, SearchOutlined } from '@ant-design/icons-vue';
    import { notification } from 'ant-design-vue';
    import axios from 'axios';
    
    const buscar = ref("");
    const modalidades = ref([])
    const modalidadestemp = ref([])
    const visible = ref(false)
    const pagina = ref(1)
    const totalRegistros = ref(null)
    const documento = ref({ id:null, codigo:"", nombre:"", postulante:"", tipo:"", observacion:""})
    
    const showModalPrograma = () => { visible.value = true; };
    
    watch(buscar, ( newValue, oldValue ) => { getDocumentos() })
  
    watch(visible, ( newValue, oldValue ) => {
        if(visible.value == false && documento.value.id != null ){
            documento.value.id = null;
            documento.value.codigo = null;
            documento.value.nombre = null;
            documento.value.postulante = null;
            documento.value.tipo = null;
            documento.value.observacion = null;
        }
    })
  
    watch(pagina, ( newValue, oldValue ) => { getDocumentos(); })
    
    const layout = {
        labelCol: {
        span: 7
        },
        wrapperCol: {
        span: 14,
        },
    };
  
    let validateCodigo = async (_rule, value) => {
        if (value === '') {
            return Promise.reject('Ingrese el codigo del documento');
        } else {
            return Promise.resolve();
        }
    };  
    
    let validateNombre = async (_rule, value) => {
        if (value === '') {
            return Promise.reject('Ingrese el nombre del documento');
        } else {
            return Promise.resolve();
        }
    };
    
    const rules = {
    nombre: [{
        required: true,
        validator: validateNombre,
        trigger: 'change',
    }],
    codigo: [{
        required: true,
        validator: validateCodigo,
        trigger: 'change',
    }],
    
    };
    
    const handleOk = e => {
        console.log(e);
        visible.value = false;
    };
    
    const abrirEditar = (item) => {
        visible.value = true;
        documento.value.id = item.id;
        documento.value.codigo = item.codigo;
        documento.value.nombre = item.nombre;
        documento.value.postulante = item.postulante;
        documento.value.tipo = item.tipo;
        documento.value.observacion = item.observacion;
    }
    
    const getDocumentos =  async (term = "") => {
        let res = await axios.post( "get-documentos-admin?page="+pagina.value , { term: buscar.value } );
        modalidades.value = res.data.datos.data;
        totalRegistros.value = res.data.datos.total;
    }
  
    const guardar = () => {
        let post = {
            id: documento.value.id,
            codigo: documento.value.codigo,
            nombre: documento.value.nombre,
            observacion: documento.value.observacion,
            };
        axios.post("save-documento", post).then((result) => {
            getDocumentos()
            notificacion('success',result.data.titulo, result.data.mensaje);
            visible.value = false;
            documento.value.codigo = null,
            documento.value.id = null,
            documento.value.nombre = ""
        });
    }
    
    const eliminar = (item) => {
        axios.get("eliminar-modalidad/"+item.id).then((result) => {
        getDocumentos();
        notificacion('warning', result.data.titulo, result.data.mensaje );
        });
    }
    
    const columnsDocumentos = [
        { title: 'ID', dataIndex: 'id' },
        { title: 'Codigo', dataIndex: 'codigo'},
        { title: 'Nombre', dataIndex: 'nombre'},
        { title: 'Tipo', dataIndex: 'tipo'},
        { title: 'DNI P.', dataIndex: 'dni'},
        { title: 'Postulante', dataIndex: 'postulante'},
        { title: 'Estado', dataIndex: 'estado'},
        { title: 'Verificado', dataIndex: 'verificado', align:'center'},
        { title: 'Observación', dataIndex: 'observacion'},
        { title: 'Acciones', dataIndex: 'acciones'},
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
    
    getDocumentos()
    </script>

<style>
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