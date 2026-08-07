<template>
<Head title="Roles"/>
<AuthenticatedLayout>
<div class="roles-container">

<a-button class="mb-3" type="primary" @click="showModalRol">Nuevo</a-button>
<a-table 
  :columns="columnsRoles" 
  :data-source="roles"
  :pagination="{ pageSize: 5 }"
  size="small"
  > 
  <template #bodyCell="{ column, index }">
    <template v-if="column.dataIndex === 'acciones'">
      <a-button type="primary" @click="abrirEditar(roles[index])" size="small">
        <template #icon><form-outlined/></template>
      </a-button>
      <a-divider type="vertical" />
      <a-button type="danger" shape="" size="small">
        <template #icon><delete-outlined /></template>
      </a-button>
    </template>
  </template>
</a-table> 

</div>

</AuthenticatedLayout>

<div>
  <a-modal v-model:visible="visible" title="Crear Roles">
    <a-form-item name="email">
      <a-label>Rol</a-label>
      <a-input v-model:value="rolname" type="text" autocomplete="off" />
    </a-form-item>
    <a-table 
      :row-selection="rowSelection" 
      :columns="columns" 
      :data-source="permisos"
      :pagination="{ pageSize: 10 }"
      size="small"
      /> 
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
import { computed, ref, unref } from 'vue';
import { FormOutlined, DeleteOutlined } from '@ant-design/icons-vue';
import axios from 'axios';

const rolname = ref("");


const roles = ref([])
const permisos = ref([]);

const visible = ref(false);
const showModalRol = () => {
  getPermisos()
  visible.value = true;
};
const handleOk = e => {
  console.log(e);
  visible.value = false;
};
const getPermisos = async () => {  
  let res = await axios.get(`get-permission`);
  permisos.value = res.data.permisos;
}

const getRoles = async () => {  
  let res = await axios.get(`get-roles`);
  roles.value = res.data.datos.data;
}

const abrirEditar = (item) => {
  visible.value = true;
  rolname.value = item.name;
}

const guardar = () => {
  let post = {
    name: rolname.value,
    permisos: rowSelection.value.selectedRowKeys,
  };
  axios.post("save-rol", post).then((result) => {
    console.log(result);
    getRoles();
    visible.value = false;
  });
}

const columnsRoles = [
  { title: 'Rol', dataIndex: 'name', sorter :true },
  { title: 'Tipo permisos', dataIndex: 'guard_name', },
  { title: 'Acciones', dataIndex: 'acciones',  }
];

const columns = [
  { title: 'Nombre', dataIndex: 'name', sorter :true },
  { title: 'Permisos', dataIndex: 'guard_name'},
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



getRoles()


</script>
<style scoped>
.roles-container {
  background: var(--card-bg, #ffffff);
  border: 1px solid var(--card-border, #e2e8f0);
  color: var(--card-text, #1e293b);
  border-radius: 8px;
  padding: 1.25rem;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
</style>

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
.theme-dark .ant-modal-content,
.theme-hybrid .ant-modal-content {
    background: var(--card-bg) !important;
    color: var(--card-text) !important;
}
.theme-dark .ant-modal-header,
.theme-hybrid .ant-modal-header {
    background: var(--card-bg) !important;
    border-bottom: 1px solid var(--card-border) !important;
}
.theme-dark .ant-modal-title,
.theme-hybrid .ant-modal-title {
    color: var(--card-text) !important;
}
.theme-dark .ant-modal-close,
.theme-hybrid .ant-modal-close {
    color: var(--card-muted) !important;
}
</style>