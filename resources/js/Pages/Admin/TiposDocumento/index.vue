<template>
  <Head title="Tipos de Documento" />

  <AuthenticatedLayout>
    <div class="overflow-hidden shadow-sm sm:rounded-lg p-4" style="background: var(--card-bg, #ffffff); color: var(--card-text, #1e293b); border: 1px solid var(--card-border, #e2e8f0);">

      <div class="flex justify-between mb-4">
        <a-button type="primary" @click="showModal">
          <template #icon>
            <PlusOutlined />
          </template>
          Nuevo
        </a-button>

        <a-input
          v-model:value="buscar"
          placeholder="Buscar"
          style="width: 220px"
        >
          <template #prefix>
            <SearchOutlined />
          </template>
        </a-input>
      </div>

      <a-table
        :columns="columns"
        :data-source="tiposFiltrados"
        :pagination="false"
        size="small"
        :loading="loading"
        row-key="id"
        :scroll="{ y: 'calc(100vh - 320px)' }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'acciones'">
            <div class="flex gap-1 justify-center">
              <a-button
                size="small"
                style="background: var(--card-bg, #ffffff);border:1px solid #d9d9d9; height: 32px; width: 32px;"
                @click="abrirEditar(record)"
              >
                <EditOutlined />
              </a-button>

              <a-popconfirm
                title="¿Estas seguro de eliminar?"
                @confirm="eliminar(record)"
              >
                <a-button
                  size="small"
                  style="background: var(--card-bg, #ffffff);border:1px solid #d9d9d9;color:#ff4d4f; height: 32px; width: 32px;"
                >
                  <DeleteOutlined />
                </a-button>
              </a-popconfirm>
            </div>
          </template>
        </template>
      </a-table>

      <div class="mt-2 text-right">
        <a-pagination
          v-model:current="pagina"
          :total="totalRegistros"
          show-less-items
          size="middle"
        />
      </div>

    </div>
  </AuthenticatedLayout>


  <a-modal
    v-model:open="visible"
    :title="tipoDocumento.id ? 'Editar Tipo de Documento' : 'Nuevo Tipo de Documento'"
    destroy-on-close
  >
    <a-form
      ref="formRef"
      :model="tipoDocumento"
      :rules="rules"
      :label-col="{ span: 7 }"
      :wrapper-col="{ span: 14 }"
    >
      <a-form-item label="Código" name="codigo" has-feedback>
        <a-input v-model:value="tipoDocumento.codigo" />
      </a-form-item>

      <a-form-item label="Nombre" name="nombre" has-feedback>
        <a-input v-model:value="tipoDocumento.nombre" />
      </a-form-item>
    </a-form>

    <template #footer>
      <a-button @click="cancelar">Cancelar</a-button>
      <a-button type="primary" :loading="guardando" @click="guardar">
        {{ tipoDocumento.id ? 'Actualizar' : 'Guardar' }}
      </a-button>
    </template>
  </a-modal>

</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, watch } from 'vue';
import { EditOutlined, DeleteOutlined, SearchOutlined, PlusOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const buscar = ref('');
const tiposDocumento = ref([]);
const visible = ref(false);
const loading = ref(false);
const guardando = ref(false);
const pagina = ref(1);
const totalRegistros = ref(0);
const formRef = ref();

const tipoDocumento = ref({
  id: null,
  codigo: '',
  nombre: '',
});

const columns = [
  { title: 'Código', dataIndex: 'codigo', align: 'center', width: 120 },
  { title: 'Nombre', dataIndex: 'nombre' },
  { title: 'Acciones', dataIndex: 'acciones', align: 'center', width: 120 },
];

const rules = {
  codigo: [{ required: true, message: 'Ingrese el código' }],
  nombre: [{ required: true, message: 'Ingrese el nombre' }],
};

const tiposFiltrados = computed(() => {
  if (!buscar.value) return tiposDocumento.value;
  const term = buscar.value.toLowerCase();
  return tiposDocumento.value.filter(td =>
    td.nombre?.toLowerCase().includes(term) ||
    td.codigo?.toLowerCase().includes(term)
  );
});

const getTiposDocumento = async () => {
  loading.value = true;
  try {
    const res = await axios.post('tipos-documento/get-tipos-documento', {
      page: pagina.value,
    });
    tiposDocumento.value = res.data.datos.data;
    totalRegistros.value = res.data.datos.total;
  } catch {
    notificacion('error', 'Error', 'No se pudieron cargar los tipos de documento');
  } finally {
    loading.value = false;
  }
};

const showModal = () => {
  visible.value = true;
};

const abrirEditar = (record) => {
  tipoDocumento.value = { ...record };
  visible.value = true;
};

const guardar = async () => {
  try {
    await formRef.value.validate();
  } catch {
    return;
  }

  guardando.value = true;
  try {
    const res = await axios.post('tipos-documento/save', tipoDocumento.value);
    notificacion('success', res.data.titulo, res.data.mensaje);
    getTiposDocumento();
    visible.value = false;
  } catch (error) {
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      Object.keys(errors).forEach(key => {
        notificacion('error', 'Error', errors[key][0]);
      });
    }
  } finally {
    guardando.value = false;
  }
};

const eliminar = async (record) => {
  try {
    const res = await axios.get(`tipos-documento/delete/${record.id}`);
    notificacion('warning', res.data.titulo, res.data.mensaje);
    getTiposDocumento();
  } catch {
    notificacion('error', 'Error', 'No se pudo eliminar');
  }
};

const cancelar = () => {
  visible.value = false;
};

const limpiar = () => {
  tipoDocumento.value = { id: null, codigo: '', nombre: '' };
  formRef.value?.resetFields();
};

const notificacion = (type, titulo, mensaje) => {
  notification[type]({ message: titulo, description: mensaje });
};

watch(buscar, () => {
  pagina.value = 1;
  getTiposDocumento();
});

watch(pagina, () => {
  getTiposDocumento();
});

watch(visible, v => {
  if (!v) limpiar();
});

getTiposDocumento();
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
