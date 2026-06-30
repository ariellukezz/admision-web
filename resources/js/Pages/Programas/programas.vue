<template>
<Head title="Programas"/>
<AuthenticatedLayout>
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 md:p-6" style="height: calc(100vh - 100px);">

  <div class="flex flex-col sm:flex-row justify-between mb-6 gap-4">
    <a-button
      type="primary"
      style="background: #2563eb; border: none; border-radius: 6px;"
      @click="showModalPrograma"
    >
      <template #icon><PlusOutlined /></template>
      Nuevo Programa
    </a-button>

    <a-input-search
      v-model:value="buscar"
      placeholder="Buscar programas..."
      class="max-w-full sm:max-w-xs"
      @search="getProgramas"
    >
      <template #prefix>
        <SearchOutlined />
      </template>
    </a-input-search>
  </div>

  <div class="overflow-x-auto">
    <a-table
      :columns="columnsProgramas"
      :data-source="programas"
      :pagination="{ pageSize: 50 }"
      size="small"
      :scroll="{ x: 'max-content', y: 'calc(100vh - 280px)' }"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.dataIndex === 'area'">
          <a-tag
            :color="areaColors[record.area] || 'default'"
            class="text-xs"
          >
            {{ record.area }}
          </a-tag>
        </template>

        <template v-if="column.dataIndex === 'estado'">
          <a-tag :color="record.estado == 1 ? 'green' : 'red'">
            {{ record.estado == 1 ? 'VIGENTE' : 'NO VIGENTE' }}
          </a-tag>
        </template>

        <template v-if="column.dataIndex === 'acciones'">
          <a-space size="small">
            <a-tooltip title="Editar">
              <a-button
                size="small"
                type="text"
                class="text-blue-600 hover:text-blue-800"
                @click="abrirEditar(record)"
              >
                <template #icon><FormOutlined /></template>
              </a-button>
            </a-tooltip>

            <a-popconfirm
              title="¿Está seguro de eliminar?"
              @confirm="eliminar(record)"
            >
              <a-tooltip title="Eliminar">
                <a-button
                  size="small"
                  type="text"
                  danger
                >
                  <template #icon><DeleteOutlined /></template>
                </a-button>
              </a-tooltip>
            </a-popconfirm>
          </a-space>
        </template>
      </template>
    </a-table>
  </div>

</div>

<a-modal
  v-model:open="visible"
  centered
  :title="programa.id ? 'Editar Programa' : 'Nuevo Programa'"
  width="90%"
  :style="{ maxWidth: '600px' }"
  :footer="null"
>
  <a-form
    ref="formRef"
    name="programa"
    :model="programa"
    :rules="rules"
    layout="vertical"
  >
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <a-form-item has-feedback label="Código" name="codigo">
        <a-input
          v-model:value="programa.codigo"
          placeholder="Código del programa"
          allow-clear
        />
      </a-form-item>

      <a-form-item has-feedback label="Nombre" name="nombre">
        <a-input
          v-model:value="programa.nombre"
          placeholder="Nombre del programa"
          allow-clear
        />
      </a-form-item>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <a-form-item label="Facultad" name="id_facultad">
        <a-select
          v-model:value="programa.id_facultad"
          :options="facultades"
          placeholder="Seleccionar facultad"
          allow-clear
          show-search
          :filter-option="filterOption"
        />
      </a-form-item>

      <a-form-item label="Área" name="area">
        <a-select
          v-model:value="programa.area"
          placeholder="Seleccionar área"
          allow-clear
        >
          <a-select-option value="BIOMÉDICAS">BIOMÉDICAS</a-select-option>
          <a-select-option value="INGENIERÍAS">INGENIERÍAS</a-select-option>
          <a-select-option value="SOCIALES">SOCIALES</a-select-option>
        </a-select>
      </a-form-item>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <a-form-item label="Nivel académico" name="nivel_academico">
        <a-input
          v-model:value="programa.nivel_academico"
          placeholder="Nivel académico"
          allow-clear
        />
      </a-form-item>

      <a-form-item label="Tipo de autorización" name="tipo_autorizacion">
        <a-input
          v-model:value="programa.tipo_autorizacion"
          placeholder="Tipo de autorización"
          allow-clear
        />
      </a-form-item>
    </div>

    <a-form-item label="Vigente" name="estado">
      <a-switch
        v-model:checked="programa.estado"
        checked-children="Activo"
        un-checked-children="Inactivo"
      />
    </a-form-item>

    <div class="flex justify-end gap-3 pt-4 border-t">
      <a-button @click="cancelar">Cancelar</a-button>
      <a-button
        type="primary"
        style="background: #2563eb; border: none; border-radius: 6px;"
        :loading="guardando"
        @click="guardar"
      >
        {{ programa.id ? 'Actualizar' : 'Guardar' }}
      </a-button>
    </div>
  </a-form>
</a-modal>

</AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { watch, ref, reactive } from 'vue';
import { FormOutlined, DeleteOutlined, SearchOutlined, PlusOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const buscar = ref('');
const facultades = ref([]);
const programas = ref([]);
const visible = ref(false);
const guardando = ref(false);
const formRef = ref(null);

const programa = reactive({
  id: null,
  codigo: '',
  nombre: '',
  nivel_academico: 'CARRERA PROFESIONAL',
  tipo_autorizacion: 'RECONOCIDO POR LIC.',
  id_facultad: null,
  estado: true,
  area: 'BIOMÉDICAS'
});

const areaColors = {
  'BIOMÉDICAS': 'cyan',
  'SOCIALES': 'purple',
  'INGENIERÍAS': 'blue'
};

const rules = {
  codigo: [{ required: true, message: 'Ingrese el código' }],
  nombre: [{ required: true, message: 'Ingrese el nombre' }],
  id_facultad: [{ required: true, message: 'Seleccione la facultad' }],
  area: [{ required: true, message: 'Seleccione el área' }],
};

const columnsProgramas = [
  { title: 'Código', dataIndex: 'codigo', key: 'codigo', width: 80, align: 'center' },
  { title: 'Nombre', dataIndex: 'nombre', key: 'nombre', ellipsis: true },
  { title: 'Facultad', dataIndex: 'facultad', key: 'facultad', ellipsis: true, responsive: ['md'] },
  { title: 'Área', dataIndex: 'area', key: 'area', align: 'center', width: 120, responsive: ['md'] },
  { title: 'Estado', dataIndex: 'estado', key: 'estado', align: 'center', width: 110 },
  { title: 'Acciones', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 100, fixed: 'right' },
];

const showModalPrograma = () => {
  resetPrograma();
  visible.value = true;
};

const abrirEditar = (item) => {
  Object.assign(programa, {
    id: item.id,
    codigo: item.codigo,
    nombre: item.nombre,
    nivel_academico: item.nivel_academico || 'CARRERA PROFESIONAL',
    tipo_autorizacion: item.tipo_autorizacion || 'RECONOCIDO POR LIC.',
    id_facultad: item.id_fac,
    estado: item.estado == 1,
    area: item.area
  });
  visible.value = true;
};

const getFacultades = async () => {
  try {
    const res = await axios.get('get-facultades');
    facultades.value = res.data.datos;
  } catch (error) {
    console.error('Error al obtener facultades:', error);
  }
};

const getProgramas = async () => {
  try {
    const res = await axios.post('programas/get-programas', {
      term: buscar.value
    });
    programas.value = res.data.datos.data;
  } catch (error) {
    console.error('Error al obtener programas:', error);
  }
};

const guardar = async () => {
  try {
    await formRef.value.validate();
    guardando.value = true;

    const payload = {
      id: programa.id,
      codigo: programa.codigo,
      nombre: programa.nombre,
      nivel_academico: programa.nivel_academico,
      tipo_autorizacion: programa.tipo_autorizacion,
      estado: programa.estado,
      id_facultad: programa.id_facultad,
      area: programa.area,
    };

    const res = await axios.post('save-programa', payload);
    notificacion('success', res.data.titulo, res.data.mensaje);
    getProgramas();
    visible.value = false;
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.values(error.response.data.errors).forEach(err => {
        notificacion('error', 'Error', err[0]);
      });
    }
  } finally {
    guardando.value = false;
  }
};

const eliminar = async (item) => {
  try {
    const res = await axios.get(`eliminar-programa/${item.id}`);
    notificacion('warning', 'PROGRAMA ELIMINADO', res.data.mensaje);
    getProgramas();
  } catch (error) {
    notificacion('error', 'Error', 'No se pudo eliminar el programa');
  }
};

const cancelar = () => {
  visible.value = false;
};

const resetPrograma = () => {
  Object.assign(programa, {
    id: null,
    codigo: '',
    nombre: '',
    nivel_academico: 'CARRERA PROFESIONAL',
    tipo_autorizacion: 'RECONOCIDO POR LIC.',
    id_facultad: null,
    estado: true,
    area: 'BIOMÉDICAS'
  });
};

const notificacion = (type, titulo, mensaje) => {
  notification[type]({
    message: titulo,
    description: mensaje,
    placement: 'topRight'
  });
};

const filterOption = (input, option) => {
  return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

watch(buscar, (newValue) => {
  clearTimeout(timeoutId);
  timeoutId = setTimeout(() => {
    getProgramas();
  }, 500);
});

let timeoutId;

getFacultades();
getProgramas();
</script>

<style scoped>
:deep(.ant-btn-primary) {
  background: #2563eb !important;
  border-color: #2563eb !important;
}
:deep(.ant-btn-primary:hover) {
  background: #1d4ed8 !important;
  border-color: #1d4ed8 !important;
}
:deep(.ant-input:focus),
:deep(.ant-input-focused),
:deep(.ant-input-affix-wrapper:focus),
:deep(.ant-input-affix-wrapper-focused) {
  border-color: #3b82f6 !important;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1) !important;
}
:deep(.ant-pagination .ant-pagination-item-active) {
  border-color: #2563eb !important;
}
:deep(.ant-pagination .ant-pagination-item-active a) {
  color: #2563eb !important;
}
</style>
