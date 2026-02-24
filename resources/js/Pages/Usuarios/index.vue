<template>
  <Head title="Usuarios" />
  <AuthenticatedLayout>

    <a-card class="mac-card" :bordered="false">

      <div class="header">
        <h2>Gestión de Usuarios</h2>

        <div class="header-actions">
          <a-input
            v-model:value="buscar"
            placeholder="Buscar usuario"
            allow-clear
            style="width:260px"
          >
            <template #prefix><SearchOutlined /></template>
          </a-input>

          <a-button type="primary" @click="nuevoUsuario">
            Nuevo Usuario
          </a-button>
        </div>
      </div>

      <a-table
        :data-source="users"
        :columns="columns"
        row-key="id"
        class="mac-table"
      >
        <template #bodyCell="{ column, record }">

          <template v-if="column.dataIndex === 'usuario'">
            <div class="user-cell">
              <a-avatar :src="record.foto_url" size="large" style="background: red; min-width: 40px; height: 40px; display: flex; align-items: center; justify-content: center  ;">
                <template #icon><UserOutlined /></template>
              </a-avatar>

              <div class="user-info">
                <div class="user-name">
                  {{ record.name }} {{ record.paterno }} {{ record.materno }}
                </div>
                <div class="user-dni">
                  DNI: {{ record.dni }}
                </div>
              </div>
            </div>
          </template>

          <template v-if="column.dataIndex === 'email'">
            {{ record.email }}
          </template>

          <template v-if="column.dataIndex === 'rol'">
            <a-tag color="blue">{{ record.rol }}</a-tag>
          </template>

          <template v-if="column.dataIndex === 'proceso'">
            <a-tag color="purple">{{ record.proceso }}</a-tag>
          </template>

          <template v-if="column.dataIndex === 'estado'">
            <a-tag :color="record.estado ? 'green' : 'red'">
              {{ record.estado ? 'Activo' : 'Inactivo' }}
            </a-tag>
          </template>

          <template v-if="column.dataIndex === 'acciones'">
            <div style="display: flex; gap: 2px;">
              <a-button @click="modalCertificado = true" size="small" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: green; display: flex; align-items: center;">
                <IdcardOutlined />
              </a-button>
              <a-button @click="editarUsuario(record)" size="small" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: #1890ff; display: flex; align-items: center;">
                <FormOutlined />
              </a-button>
              <a-button @click="eliminarUsuario(record.id)" size="small" style="background:white; height: 28px; border: 1px solid #d9d9d9; color: #ff4d4f; display: flex; align-items: center;">
                <DeleteOutlined />
              </a-button>
            </div>
          </template>

        </template>
      </a-table>
    </a-card>

    <a-modal
      v-model:open="modalVisible"
      :title="form.id ? 'Editar Usuario' : 'Nuevo Usuario'"
      width="780px"
      :confirm-loading="saving"
      @ok="guardarUsuario"
    >
      <a-form layout="vertical">

        <a-form-item label="Foto">
          <div class="foto-box">
            <a-avatar :size="96" :src="fotoPreview">
              <template #icon><UserOutlined /></template>
            </a-avatar>

            <a-upload
              :before-upload="() => false"
              :show-upload-list="false"
              @change="onChangeFoto"
            >
              <a-button size="small">Cambiar foto</a-button>
            </a-upload>
          </div>
        </a-form-item>

        <a-row :gutter="16">

          <a-col :span="8">
            <a-form-item label="DNI">
              <a-input v-model:value="form.dni">
                <template #prefix><IdcardOutlined /></template>
              </a-input>
            </a-form-item>
          </a-col>

          <a-col :span="8">
            <a-form-item label="Celular">
              <a-input v-model:value="form.celular">
                <template #prefix><PhoneOutlined /></template>
              </a-input>
            </a-form-item>
          </a-col>

          <a-col :span="8">
            <a-form-item label="Email">
              <a-input v-model:value="form.email">
                <template #prefix><MailOutlined /></template>
              </a-input>
            </a-form-item>
          </a-col>

          <a-col :span="8">
            <a-form-item label="Nombres">
              <a-input v-model:value="form.name">
                <template #prefix><UserOutlined /></template>
              </a-input>
            </a-form-item>
          </a-col>

          <a-col :span="8">
            <a-form-item label="Apellido Paterno">
              <a-input v-model:value="form.paterno">
                <template #prefix><UserOutlined /></template>
              </a-input>
            </a-form-item>
          </a-col>

          <a-col :span="8">
            <a-form-item label="Apellido Materno">
              <a-input v-model:value="form.materno">
                <template #prefix><UserOutlined /></template>
              </a-input>
            </a-form-item>
          </a-col>

          <a-col :span="8">
            <a-form-item label="Rol">
              <a-select v-model:value="form.id_rol" :options="roles">
                <template #prefix><TeamOutlined /></template>
              </a-select>
            </a-form-item>
          </a-col>

          <a-col :span="8">
            <a-form-item label="Proceso">
              <a-select v-model:value="form.id_proceso" :options="procesos" />
            </a-form-item>
          </a-col>

          <a-col :span="8">
            <a-form-item label="Estado">
              <a-switch v-model:checked="form.estado" />
            </a-form-item>
          </a-col>

          <a-col :span="12">
            <a-form-item label="Contraseña" v-if="!form.id">
              <a-input-password v-model:value="form.password" />
            </a-form-item>
          </a-col>

        </a-row>
      </a-form>
    </a-modal>

    <a-modal
      v-model:open="modalCertificado"
      title="Certificado Digital"
      width="480px"
      @ok="crearCertificadoDigital"
      ok-text="Crear certificado"
    >
      <a-form layout="vertical">
        <a-form-item label="Contraseña para el archivo .p12">
          <a-input-password
            v-model:value="password_p12"
            placeholder="Ingrese una contraseña segura"
          />
        </a-form-item>
      </a-form>
    </a-modal>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { message, Modal } from 'ant-design-vue'
import {
  SearchOutlined,
  UserOutlined,
  FormOutlined,
  DeleteOutlined,
  MailOutlined,
  PhoneOutlined,
  ApartmentOutlined,
  IdcardOutlined,
  TeamOutlined
} from '@ant-design/icons-vue'

const modalCertificado = ref(false)
const buscar = ref('')
const users = ref([])
const procesos = ref([])
const roles = ref([
  { value: 1, label: 'Administrador' },
  { value: 2, label: 'Revisor' },
  { value: 3, label: 'Segundas Director' },
  { value: 6, label: 'Simulacro' },
  { value: 7, label: 'Calificador' }
])

const modalVisible = ref(false)
const saving = ref(false)
const fotoFile = ref(null)
const fotoPreview = ref(null)

const form = reactive({
  id: null,
  dni: '',
  name: '',
  paterno: '',
  materno: '',
  email: '',
  celular: '',
  id_rol: null,
  password: '',
  id_proceso: null,
  estado: true
})

const columns = [
  { title: 'Usuario', dataIndex: 'usuario' },
  { title: 'Email', dataIndex: 'email' },
  { title: 'Rol', dataIndex: 'rol' },
  { title: 'Proceso', dataIndex: 'proceso' },
  { title: 'Estado', dataIndex: 'estado', width: 90, align: 'center' },
  { title: 'Acciones', dataIndex: 'acciones', width: 100, align: 'center' }
]

watch(buscar, getUsuarios)

function onChangeFoto(info) {
  fotoFile.value = info.file
  fotoPreview.value = URL.createObjectURL(info.file)
}

function nuevoUsuario() {
  Object.assign(form, {
    id: null,
    dni: '',
    name: '',
    paterno: '',
    materno: '',
    email: '',
    celular: '',
    id_rol: null,
    password: '',
    id_proceso: null,
    estado: true
  })
  fotoPreview.value = null
  modalVisible.value = true
}

function editarUsuario(u) {
  Object.assign(form, u)
  fotoPreview.value = u.foto_url || null
  modalVisible.value = true
}

async function guardarUsuario() {
  saving.value = true
  const data = new FormData()
  Object.keys(form).forEach(k => data.append(k, form[k] ?? ''))
  if (fotoFile.value) data.append('foto', fotoFile.value)

  await axios.post('save-user', data)
  saving.value = false
  modalVisible.value = false
  message.success('Usuario guardado')
  getUsuarios()
}

function eliminarUsuario(id) {
  Modal.confirm({
    title: 'Eliminar usuario',
    content: 'Esta acción no se puede deshacer',
    okType: 'danger',
    onOk: async () => {
      await axios.post('delete-user', { id })
      getUsuarios()
    }
  })
}

async function getUsuarios() {
  const { data } = await axios.post('get-usuarios', { term: buscar.value })
  users.value = data.usuarios
}

async function getProcesos() {
  const { data } = await axios.get('get-select-procesos')
  procesos.value = data.datos
}

const password_p12 = ref('')

const crearCertificadoDigital = async () => {
  const response = await axios.post('/crear-certificado-digital', {
    dni: form.dni,
    email: form.email,
    usuario: `${form.name} ${form.paterno} ${form.materno}`,
    rol: form.id_rol,
    departamento: 'Dirección de admisión',
    password_p12: password_p12.value,
    valid_days: 365
  })

  if (response.data.success) {
    message.success(response.data.message || 'Certificado creado exitosamente')
    modalCertificado.value = false
  } else {
    message.error(response.data.message || 'Error al crear certificado')
  }
}

getUsuarios()
getProcesos()
</script>

<style scoped>
.mac-card {
  background: rgba(255,255,255,.92);
  backdrop-filter: blur(16px);
  border-radius: 20px;
  box-shadow: 0 20px 45px rgba(0,0,0,.06);
  padding: 18px;
  transition: all .3s ease;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  gap: 12px;
  flex-wrap: wrap;
}

.header h2 {
  font-size: 20px;
  font-weight: 600;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.header-actions .ant-input {
  border-radius: 10px;
}

.header-actions .ant-btn {
  border-radius: 10px;
  height: 38px;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 500;
  font-size: 14px;
}

.user-dni {
  font-size: 12px;
  color: #6b7280;
}

.mac-table {
  overflow-x: auto;
}

.mac-table :deep(.ant-table) {
  min-width: 750px;
}

.mac-table :deep(.ant-table-thead th) {
  background: #f8fafc;
  font-size: 12px;
  text-transform: uppercase;
  color: #64748b;
  font-weight: 600;
}

.mac-table :deep(.ant-table-tbody tr:hover) {
  background: #f1f5f9;
  transition: .2s;
}

.foto-box {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

:deep(.ant-modal) {
  border-radius: 16px;
}

:deep(.ant-modal-content) {
  border-radius: 16px;
  padding: 10px;
}

:deep(.ant-form-item-label > label) {
  font-weight: 500;
  color: #334155;
}

:deep(.ant-input),
:deep(.ant-select-selector),
:deep(.ant-input-password) {
  border-radius: 10px !important;
}

:deep(.ant-switch) {
  background: #e2e8f0;
}

:deep(.ant-switch-checked) {
  background: #2563eb;
}

@media (max-width: 1024px) {
  .mac-card {
    padding: 14px;
  }
}

@media (max-width: 768px) {
  .header {
    flex-direction: column;
    align-items: stretch;
  }

  .header-actions {
    width: 100%;
  }

  .header-actions .ant-input {
    width: 100% !important;
  }

  .header-actions .ant-btn {
    width: 100%;
  }

  .user-cell {
    gap: 8px;
  }

  .user-name {
    font-size: 13px;
  }

  :deep(.ant-modal) {
    width: 95% !important;
    max-width: 95% !important;
  }
}

@media (max-width: 480px) {
  .mac-card {
    padding: 10px;
    border-radius: 14px;
  }

  .header h2 {
    font-size: 16px;
  }

  .user-name {
    font-size: 12px;
  }

  .user-dni {
    font-size: 11px;
  }
}
</style>