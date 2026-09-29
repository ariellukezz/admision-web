<template>
<Head title="Procesos"/>
<AuthenticatedLayout>
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4" style="height: calc(100vh - 98px);">
    <!-- {{ buscar }} -->
    <row class="flex justify-between mb-4" >
        <div class="mr-3">
            <a-button type="primary" style="border-radius: 5px; background: #476175" @click="showModalPrograma">Agregar</a-button>
        </div>
        <div class="flex justify-between" style="position: relative;" >
        <a-input type="text" placeholder="Buscar" v-model:value="buscar" style="max-width: 300px; padding-left: 30px;"/>
        <div class="mr-2" style="position: absolute; left: 8px; top: 3px; "><search-outlined /></div>
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
                <template v-if="column.dataIndex === 'codigo'">
                    <div><span style="font-size: .9rem">{{ record.codigo }}</span></div>                
                </template>

                <template v-if="column.dataIndex === 'nombre'">
                    <div><span style="font-size: .9rem;">{{ record.nombre }}</span></div>                
                </template>
                <template v-if="column.dataIndex === 'facultad'">
                    <div><span style="font-size: .9rem;">{{ record.facultad }}</span></div>                
                </template>
                <template v-if="column.dataIndex === 'area'">
                    <div class="flex" style="justify-content: center;">
                        <a-tag style="font-size: .8rem;" color="cyan" v-if=" programas[index].area == 'BIOMÉDICAS'">{{ programas[index].area }}</a-tag>
                        <a-tag style="font-size: .8rem;" color="purple" v-if=" programas[index].area == 'SOCIALES'">{{ programas[index].area }}</a-tag>
                        <a-tag style="font-size: .8rem;" color="blue" v-if=" programas[index].area == 'INGENIERÍAS'">{{ programas[index].area }}</a-tag>
                    </div>
                </template>
                
                <template v-if="column.dataIndex === 'estado'">
                    <div class="flex" style="justify-content: center;">
                        <div v-if="1 == programas[index].estado">
                            <a-tag color="green">Si</a-tag>
                        </div>
                        <div v-if="programas[index].estado == 0">
                            <a-tag color="red">No</a-tag>
                        </div>
                    </div>
                </template>

                <template v-if="column.dataIndex === 'acciones'">
                    <a-button type="" @click="verDetalle(record)" style="border-radius:4px; background: none; color: green" size="small">
                        <template #icon><eye-outlined/></template>
                    </a-button>
                    <a-button type="" @click="abrirEditar(record)" style="border-radius:4px; background: none; color: gray" size="small">
                        <template #icon><form-outlined/></template>
                    </a-button>
                    <a-button class="" @click="eliminar(record)" style="border-radius:4px; background: none; color: red;" shape="" size="small">
                    <template #icon><delete-outlined/></template>
                    </a-button>
                </template>
            </template>
        </a-table> 
    </div>
    <div class="flex" style="justify-content: flex-end;">
        <a-pagination v-model:current="pagina" simple page-size="50" :total="totalpaginas" />
    </div>

    </div>
    
    </AuthenticatedLayout>
    
    <div>
        <a-modal v-model:visible="visible" :title="programa.id == null?'Nuevo Programa':'Editar Programa'" style="margin-top: -40px;">

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
                <a-input type="text" v-model:value="programa.codigo" autocomplete="off" />
            </a-form-item>
            <a-form-item has-feedback label="Nombre" name="nombre">
                <a-input type="text" v-model:value="programa.nombre" autocomplete="off" />
            </a-form-item>
        
            <a-form-item has-feedback label="Facultad" name="facultad">
                <a-select
                    :options="facultades"
                    ref="Tipo"
                    style="width: 100%"
                    @focus="focus"
                    @change="handleChange"
                    v-model:value="programa.id_facultad"
                    >
                </a-select>
            </a-form-item>

            <a-form-item has-feedback label="Area" name="area">
                <a-select
                    style="width: 100%"
                    @focus="focus"
                    @change="handleChange"
                    v-model:value="programa.area"
                    >
                    <a-select-option value="BIOMÉDICAS">
                        BIOMEDICAS
                    </a-select-option>
                    <a-select-option value="INGENIERÍAS">
                        INGENIERÍAS
                    </a-select-option>
                    <a-select-option value="SOCIALES">
                        SOCIALES
                    </a-select-option>
                </a-select>
            </a-form-item>
            
            <a-form-item has-feedback label="Vigente" name="estado">
                <a-switch v-model:checked="programa.estado"/>
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
    import { EyeOutlined, FormOutlined, DeleteOutlined, SearchOutlined } from '@ant-design/icons-vue';
    import { notification } from 'ant-design-vue';
    import axios from 'axios';
    
    const buscar = ref("");
    const pagina = ref(1)
    const totalpaginas = ref(null)
    const dep = ref("PUNO")
    const facultades = ref([])

    const visible = ref(false);
    const buscarDep = ref("")
    const programas = ref([])
    const programa = ref({
        id:null,
        codigo:"",
        nombre:"",
        nivel_academico:"CARRERA PROFESIONAL",
        tipo_autorizacion:"RECONOCIDO POR LIC.",
        id_facultad:1,
        estado:true,
        area:"BIOMEDICAS"
    })
    
    const showModalPrograma = () => {
        visible.value = true;
    };
    
    watch(buscar, ( newValue, oldValue ) => {
        getProgramas()
    })
    
    
    watch(buscarDep, ( newValue, oldValue ) => {
        getDepartamentos()
    })
    
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

        
    const getFacultades =  async ( ) => {
        let res = await axios.get(
        "get-facultades");
        facultades.value = res.data.datos;
    }
    
    
    const getProgramas =  async (term = "") => {
        let res = await axios.post(
        "programas/get-programas?page=" + pagina.value,
        { term: buscar.value }
        );
        programas.value = res.data.datos.data;
        totalpaginas.value = res.data.datos.total;
    }
    
    
    const guardar = () => {
        let post = {
        id: programa.value.id,
        codigo: programa.value.codigo,
        nombre: programa.value.nombre,
        nivel_academico: programa.value.nivel_academico,
        tipo_autorizacion: programa.value.tipo_autorizacion,
        estado: programa.value.estado,
        id_facultad: programa.value.id_facultad,
        area: programa.value.area,
        };
        axios.post("save-programa", post).then((result) => {
        getProgramas()
        notificacion('success',result.data.titulo, result.data.mensaje);
        visible.value = false;
        programa.value.codigo = null,
        programa.value.id = null,
        programa.value.nombre = ""
        });
    }
    
    const eliminar = (item) => {
        axios.get("eliminar-programa/"+item.id).then((result) => {
        getProgramas();
        notificacion('warning', 'PROGRAMA ELIMINADO', result.data.mensaje );
        });
    }
    
    const columnsProgramas = [
        { title: 'Cod', dataIndex: 'codigo', width:'60px', align:'center', responsive: ['md'],},
        { title: 'Nombre', dataIndex: 'nombre'},
        { title: 'Area', dataIndex: 'area', align:'center', width:"100px", responsive: ['md'],},
        { title: 'Fun.', dataIndex: 'estado', align:'center', width:'60px', responsive: ['md'],},
        { title: 'Acciones', dataIndex: 'acciones', width:"90px", align:'center'},
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
    
    getFacultades()
    getProgramas()

    
</script>

<style scoped>
/* Desplazamiento discreto y acotado a este componente.
   Antes esta hoja era global y reescribía la barra de toda la aplicación. */
.scroll-container {
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: var(--rev-n-200) transparent;
}

.scroll-container::-webkit-scrollbar { width: 9px; height: 9px; }
.scroll-container::-webkit-scrollbar-track { background: transparent; }
.scroll-container::-webkit-scrollbar-thumb {
  background: var(--rev-n-200);
  border: 2px solid transparent;
  background-clip: content-box;
  border-radius: var(--rev-r-pill);
}
.scroll-container::-webkit-scrollbar-thumb:hover {
  background: var(--rev-n-300);
  background-clip: content-box;
}
</style>
