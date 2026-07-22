<template>
  <Head title="Gestión de Permisos" />

  <AuthenticatedLayout>
    <div class="rbac-page">

      <!-- Header -->
      <div class="rbac-header">
        <div class="rbac-title-group">
          <h2 class="rbac-title">
            <span class="rbac-title-icon"><SafetyOutlined /></span>
            Gestión de Permisos
          </h2>
          <span class="rbac-subtitle">Administra permisos, asigna a roles y configura overrides por usuario</span>
        </div>
      </div>

      <!-- Stats -->
      <!-- <div class="rbac-stats">
        <div class="rbac-stat">
          <span class="rbac-stat-value">{{ permisos.length }}</span>
          <span class="rbac-stat-label">Permisos</span>
        </div>
        <div class="rbac-stat">
          <span class="rbac-stat-value">{{ moduloOptions.length }}</span>
          <span class="rbac-stat-label">Módulos</span>
        </div>
        <div class="rbac-stat">
          <span class="rbac-stat-value">{{ rolesOptions.length }}</span>
          <span class="rbac-stat-label">Roles</span>
        </div>
        <div class="rbac-stat">
          <span class="rbac-stat-value">{{ permisos.filter(p => p.status).length }}</span>
          <span class="rbac-stat-label">Activos</span>
        </div>
        <div class="rbac-stat">
          <span class="rbac-stat-value">{{ acciones.length }}</span>
          <span class="rbac-stat-label">Acciones</span>
        </div>
      </div> -->

      <!-- Tabs -->
      <div class="rbac-tabs">
        <a-tabs v-model:activeKey="activeTab">

          <!-- Tab 1: Permisos -->
          <a-tab-pane key="permisos">
            <template #tab><SafetyOutlined /> Permisos</template>

            <div class="rbac-toolbar">
              <div class="rbac-toolbar-left">
                <a-input-search
                  v-model:value="buscarPermiso"
                  placeholder="Buscar por código, módulo o vista..."
                  style="width: 340px"
                  allow-clear
                  @search="filtrarPermisos"
                  @change="filtrarPermisos"
                />
              </div>
              <a-button type="primary" class="rbac-btn-primary" @click="showModalPermiso()">
                <PlusOutlined /> Nuevo Permiso
              </a-button>
            </div>

            <div class="rbac-table">
              <a-table
                :columns="permisoColumns"
                :data-source="permisosFiltrados"
                :loading="loadingPermisos"
                :pagination="{ pageSize: 50, showSizeChanger: false }"
                size="small"
                :scroll="{ y: 'calc(100vh - 420px)' }"
                row-key="id"
              >
                <template #bodyCell="{ column, record }">
                  <template v-if="column.dataIndex === 'code'">
                    <span class="rbac-code">{{ record.code }}</span>
                  </template>
                  <template v-if="column.dataIndex === 'status'">
                    <span class="rbac-badge-active" :class="record.status ? 'on' : 'off'">
                      {{ record.status ? '● Activo' : '○ Inactivo' }}
                    </span>
                  </template>
                  <template v-if="column.dataIndex === 'acciones'">
                    <a-popconfirm title="¿Eliminar permiso?" @confirm="deletePermiso(record.id)">
                      <a-button size="small" type="text" danger><DeleteOutlined /></a-button>
                    </a-popconfirm>
                  </template>
                </template>
              </a-table>
            </div>
          </a-tab-pane>

          <!-- Tab 2: Asignar a Rol -->
          <a-tab-pane key="rol">
            <template #tab><TeamOutlined /> Asignar a Rol</template>

            <div class="rbac-toolbar">
              <div class="rbac-toolbar-left">
                <a-select
                  v-model:value="rolSeleccionado"
                  :options="rolesOptions"
                  placeholder="Seleccionar rol"
                  style="width: 260px"
                  show-search
                  option-filter-prop="label"
                  @change="loadPermisosRol"
                />
                <a-tag v-if="rolSeleccionado" color="purple">
                  {{ permisosRol.filter(p => p.asignado).length }} / {{ permisosRol.length }} asignados
                </a-tag>
              </div>
            </div>

            <div v-if="rolSeleccionado" class="rbac-table">
              <a-table
                :columns="asignacionColumns"
                :data-source="permisosRol"
                :loading="loadingAsignacion"
                :pagination="{ pageSize: 50, showSizeChanger: false }"
                size="small"
                :scroll="{ y: 'calc(100vh - 420px)' }"
                row-key="id"
              >
                <template #bodyCell="{ column, record }">
                  <template v-if="column.dataIndex === 'code'">
                    <span class="rbac-code">{{ record.code }}</span>
                  </template>
                  <template v-if="column.dataIndex === 'asignado'">
                    <a-switch
                      :checked="record.asignado"
                      @change="(val) => togglePermisoRol(record, val)"
                    />
                  </template>
                </template>
              </a-table>
            </div>
            <div v-else style="text-align: center; padding: 60px 0; color: #94a3b8;">
              <TeamOutlined style="font-size: 40px; margin-bottom: 12px; display: block;" />
              Selecciona un rol para ver y asignar permisos
            </div>
          </a-tab-pane>

          <!-- Tab 3: Acciones -->
          <a-tab-pane key="acciones">
            <template #tab><ThunderboltOutlined /> Acciones</template>

            <div class="rbac-toolbar">
              <div class="rbac-toolbar-left">
                <span style="font-size: 13px; color: var(--rbac-text-muted);">
                  Define las acciones disponibles (access, read, create, approve, validate, etc.)
                </span>
              </div>
              <a-button type="primary" class="rbac-btn-primary" @click="showModalAccion()">
                <PlusOutlined /> Nueva Acción
              </a-button>
            </div>

            <div class="rbac-table">
              <a-table
                :columns="accionColumns"
                :data-source="acciones"
                :loading="loadingAcciones"
                :pagination="{ pageSize: 50, showSizeChanger: false }"
                size="small"
                :scroll="{ y: 'calc(100vh - 420px)' }"
                row-key="id"
              >
                <template #bodyCell="{ column, record }">
                  <template v-if="column.dataIndex === 'code'">
                    <span class="rbac-code">{{ record.code }}</span>
                  </template>
                  <template v-if="column.dataIndex === 'permisos_count'">
                    <a-tag :color="record.permisos_count > 0 ? 'blue' : 'default'">
                      {{ record.permisos_count }}
                    </a-tag>
                  </template>
                  <template v-if="column.dataIndex === 'acciones'">
                    <a-button size="small" type="text" @click="showModalAccion(record)">
                      <EditOutlined />
                    </a-button>
                    <a-popconfirm
                      :title="record.permisos_count > 0 ? 'No se puede eliminar: está en uso' : '¿Eliminar acción?'"
                      :ok-button-props="{ disabled: record.permisos_count > 0 }"
                      @confirm="deleteAccion(record.id)"
                    >
                      <a-button size="small" type="text" danger :disabled="record.permisos_count > 0">
                        <DeleteOutlined />
                      </a-button>
                    </a-popconfirm>
                  </template>
                </template>
              </a-table>
            </div>
          </a-tab-pane>

          <!-- Tab 4: Overrides por Usuario -->
          <a-tab-pane key="usuario">
            <template #tab><UserOutlined /> Overrides por Usuario</template>

            <div class="rbac-toolbar">
              <div class="rbac-toolbar-left">
                <a-input-search
                  v-model:value="buscarUsuario"
                  placeholder="Buscar usuario por nombre, DNI o email..."
                  style="width: 380px"
                  @search="buscarUsuarios"
                />
              </div>
            </div>

            <div v-if="usuariosEncontrados.length" class="rbac-toolbar" style="margin-bottom: 0;">
              <a-select
                v-model:value="usuarioSeleccionado"
                :options="usuariosOptions"
                placeholder="Seleccionar usuario"
                style="width: 420px"
                show-search
                option-filter-prop="label"
                @change="loadPermisosUsuario"
              />
            </div>

            <div v-if="usuarioSeleccionado" class="rbac-table" style="margin-top: 14px;">
              <a-table
                :columns="overrideColumns"
                :data-source="permisosUsuario"
                :loading="loadingOverride"
                :pagination="{ pageSize: 50, showSizeChanger: false }"
                size="small"
                :scroll="{ y: 'calc(100vh - 480px)' }"
                row-key="id"
              >
                <template #bodyCell="{ column, record }">
                  <template v-if="column.dataIndex === 'code'">
                    <span class="rbac-code">{{ record.code }}</span>
                  </template>
                  <template v-if="column.dataIndex === 'override'">
                    <a-select
                      v-model:value="record.override_type"
                      style="width: 130px"
                      :options="overrideOptions"
                      size="small"
                      @change="(val) => togglePermisoUsuario(record, val)"
                    />
                  </template>
                </template>
              </a-table>
            </div>
            <div v-else-if="!usuariosEncontrados.length" style="text-align: center; padding: 60px 0; color: #94a3b8;">
              <UserOutlined style="font-size: 40px; margin-bottom: 12px; display: block;" />
              Busca un usuario para configurar overrides de permisos
            </div>
          </a-tab-pane>

        </a-tabs>
      </div>
    </div>

    <!-- Modal Nuevo Permiso -->
    <a-modal
      v-model:open="modalPermiso"
      centered
      title="Nuevo Permiso"
      width="90%"
      :style="{ maxWidth: '520px' }"
      :footer="null"
      destroy-on-close
      wrap-class="rbac-modal"
    >
      <a-form ref="permisoFormRef" :model="permisoForm" :rules="permisoRules" layout="vertical">
        <a-form-item label="Módulo" name="module_id">
          <a-select
            v-model:value="permisoForm.module_id"
            :options="moduloOptions"
            placeholder="Seleccionar módulo"
            show-search
            option-filter-prop="label"
            @change="onModuloChange"
          />
        </a-form-item>
        <a-form-item label="Vista" name="view_id">
          <a-select
            v-model:value="permisoForm.view_id"
            :options="viewOptions"
            placeholder="Seleccionar vista"
            show-search
            option-filter-prop="label"
          />
        </a-form-item>
        <a-form-item label="Acción" name="action_id">
          <a-select
            v-model:value="permisoForm.action_id"
            :options="actionOptions"
            placeholder="Seleccionar acción"
          />
        </a-form-item>
        <div class="rbac-modal-footer">
          <a-button @click="modalPermiso = false">Cancelar</a-button>
          <a-button type="primary" class="rbac-btn-primary" :loading="guardando" @click="savePermiso">
            Guardar
          </a-button>
        </div>
      </a-form>
    </a-modal>

    <!-- Modal Nueva/Editar Acción -->
    <a-modal
      v-model:open="modalAccion"
      centered
      :title="accionForm.id ? 'Editar Acción' : 'Nueva Acción'"
      width="90%"
      :style="{ maxWidth: '420px' }"
      :footer="null"
      destroy-on-close
      wrap-class="rbac-modal"
    >
      <a-form ref="accionFormRef" :model="accionForm" :rules="accionRules" layout="vertical">
        <a-form-item label="Código" name="code">
          <a-input
            v-model:value="accionForm.code"
            placeholder="ej: approve, validate, sign"
            :disabled="!!accionForm.id"
          />
        </a-form-item>
        <a-form-item label="Descripción" name="description">
          <a-input
            v-model:value="accionForm.description"
            placeholder="Descripción de la acción"
          />
        </a-form-item>
        <div class="rbac-modal-footer">
          <a-button @click="modalAccion = false">Cancelar</a-button>
          <a-button type="primary" class="rbac-btn-primary" :loading="guardandoAccion" @click="saveAccion">
            Guardar
          </a-button>
        </div>
      </a-form>
    </a-modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, computed } from 'vue'
import {
  PlusOutlined,
  DeleteOutlined,
  EditOutlined,
  SafetyOutlined,
  TeamOutlined,
  UserOutlined,
  ThunderboltOutlined
} from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const activeTab = ref('permisos')
const loadingPermisos = ref(false)
const loadingAsignacion = ref(false)
const loadingOverride = ref(false)
const guardando = ref(false)
const permisos = ref([])
const permisosFiltrados = ref([])
const buscarPermiso = ref('')
const modalPermiso = ref(false)
const permisoFormRef = ref(null)

const permisoForm = ref({ module_id: null, view_id: null, action_id: null })

const permisoRules = {
  module_id: [{ required: true, message: 'Seleccione un módulo' }],
  view_id: [{ required: true, message: 'Seleccione una vista' }],
  action_id: [{ required: true, message: 'Seleccione una acción' }],
}

const moduloOptions = ref([])
const viewOptions = ref([])
const actionOptions = ref([])
const allModulos = ref([])

const rolesOptions = ref([])
const rolSeleccionado = ref(null)
const permisosRol = ref([])

const usuariosEncontrados = ref([])
const usuariosOptions = ref([])
const usuarioSeleccionado = ref(null)
const buscarUsuario = ref('')
const permisosUsuario = ref([])

const overrideOptions = [
  { value: 'none', label: '—' },
  { value: 'add', label: 'Agregar' },
  { value: 'remove', label: 'Quitar' },
]

const permisoColumns = [
  { title: 'Módulo', dataIndex: 'module_name', key: 'module_name', width: 150 },
  { title: 'Vista', dataIndex: 'view_name', key: 'view_name', width: 150 },
  { title: 'Acción', dataIndex: 'action_desc', key: 'action_desc', width: 140 },
  { title: 'Código', dataIndex: 'code', key: 'code', width: 220 },
  { title: 'Estado', dataIndex: 'status', key: 'status', align: 'center', width: 100 },
  { title: '', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 60 },
]

const asignacionColumns = [
  { title: 'Módulo', dataIndex: 'module_name', key: 'module_name', width: 150 },
  { title: 'Vista', dataIndex: 'view_name', key: 'view_name', width: 150 },
  { title: 'Código', dataIndex: 'code', key: 'code', width: 220 },
  { title: 'Asignado', dataIndex: 'asignado', key: 'asignado', align: 'center', width: 100 },
]

const overrideColumns = [
  { title: 'Módulo', dataIndex: 'module_name', key: 'module_name', width: 150 },
  { title: 'Vista', dataIndex: 'view_name', key: 'view_name', width: 150 },
  { title: 'Código', dataIndex: 'code', key: 'code', width: 220 },
  { title: 'Override', dataIndex: 'override', key: 'override', align: 'center', width: 150 },
]

const getModulos = async () => {
  try {
    const res = await axios.get('permisos/get-modulos')
    allModulos.value = res.data.datos
    moduloOptions.value = allModulos.value.map(m => ({ value: m.id, label: m.name }))
    viewOptions.value = []
  } catch {
    notif('error', 'Error', 'No se pudieron cargar los módulos')
  }
}

const getPermisos = async () => {
  loadingPermisos.value = true
  try {
    const res = await axios.get('permisos/get-permisos')
    permisos.value = res.data.datos
    permisosFiltrados.value = [...permisos.value]
  } catch {
    notif('error', 'Error', 'No se pudieron cargar los permisos')
  } finally {
    loadingPermisos.value = false
  }
}

const filtrarPermisos = () => {
  if (!buscarPermiso.value) {
    permisosFiltrados.value = [...permisos.value]
    return
  }
  const term = buscarPermiso.value.toLowerCase()
  permisosFiltrados.value = permisos.value.filter(p =>
    p.code?.toLowerCase().includes(term) ||
    p.module_name?.toLowerCase().includes(term) ||
    p.view_name?.toLowerCase().includes(term)
  )
}

const showModalPermiso = () => {
  permisoForm.value = { module_id: null, view_id: null, action_id: null }
  viewOptions.value = []
  modalPermiso.value = true
}

const onModuloChange = (moduleId) => {
  permisoForm.value.view_id = null
  const modulo = allModulos.value.find(m => m.id === moduleId)
  viewOptions.value = modulo
    ? modulo.views.map(v => ({ value: v.id, label: v.name }))
    : []
}

const savePermiso = async () => {
  try { await permisoFormRef.value.validate() } catch { return }
  guardando.value = true
  try {
    const res = await axios.post('permisos/save', permisoForm.value)
    notif('success', 'Éxito', res.data.mensaje)
    getPermisos()
    modalPermiso.value = false
  } catch {
    notif('error', 'Error', 'No se pudo guardar el permiso')
  } finally {
    guardando.value = false
  }
}

const deletePermiso = async (id) => {
  try {
    await axios.get(`permisos/delete/${id}`)
    notif('warning', 'Eliminado', 'Permiso eliminado')
    getPermisos()
  } catch {
    notif('error', 'Error', 'No se pudo eliminar')
  }
}

// ── Acciones ──────────────────────────────────────────

const acciones = ref([])
const loadingAcciones = ref(false)
const modalAccion = ref(false)
const guardandoAccion = ref(false)
const accionFormRef = ref(null)
const accionForm = ref({ id: null, code: '', description: '' })

const accionRules = {
  code: [{ required: true, message: 'Ingrese el código' }],
}

const accionColumns = [
  { title: 'Código', dataIndex: 'code', key: 'code', width: 180 },
  { title: 'Descripción', dataIndex: 'description', key: 'description' },
  { title: 'En uso', dataIndex: 'permisos_count', key: 'permisos_count', align: 'center', width: 90 },
  { title: '', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 100 },
]

const getAcciones = async () => {
  loadingAcciones.value = true
  try {
    const res = await axios.get('permisos/get-acciones')
    acciones.value = res.data.datos
    actionOptions.value = acciones.value.map(a => ({ value: a.id, label: a.description || a.code }))
  } catch {
    notif('error', 'Error', 'No se pudieron cargar las acciones')
  } finally {
    loadingAcciones.value = false
  }
}

const showModalAccion = (record = null) => {
  accionForm.value = record
    ? { id: record.id, code: record.code, description: record.description || '' }
    : { id: null, code: '', description: '' }
  modalAccion.value = true
}

const saveAccion = async () => {
  try { await accionFormRef.value.validate() } catch { return }
  guardandoAccion.value = true
  try {
    const res = await axios.post('permisos/accion/save', accionForm.value)
    notif('success', 'Éxito', res.data.mensaje)
    getAcciones()
    modalAccion.value = false
  } catch (e) {
    const msg = e.response?.data?.mensaje || 'No se pudo guardar la acción'
    notif('error', 'Error', msg)
  } finally {
    guardandoAccion.value = false
  }
}

const deleteAccion = async (id) => {
  try {
    const res = await axios.get(`permisos/accion/delete/${id}`)
    notif('warning', 'Eliminado', res.data.mensaje)
    getAcciones()
  } catch (e) {
    const msg = e.response?.data?.mensaje || 'No se pudo eliminar'
    notif('error', 'Error', msg)
  }
}

// ── Asignación Rol ────────────────────────────────────

const getRoles = async () => {
  try {
    const res = await axios.get('permisos/get-roles')
    rolesOptions.value = res.data.datos.map(r => ({ value: r.id, label: r.name }))
  } catch {
    notif('error', 'Error', 'No se pudieron cargar los roles')
  }
}

const loadPermisosRol = async () => {
  if (!rolSeleccionado.value) return
  loadingAsignacion.value = true
  try {
    const [permRes, rolRes] = await Promise.all([
      axios.get('permisos/get-permisos'),
      axios.get('permisos/rol/get', { params: { role_id: rolSeleccionado.value } }),
    ])
    const asignados = rolRes.data.datos
    permisosRol.value = permRes.data.datos.map(p => ({
      ...p,
      asignado: asignados.includes(p.id),
    }))
  } catch {
    notif('error', 'Error', 'No se pudieron cargar los permisos del rol')
  } finally {
    loadingAsignacion.value = false
  }
}

const togglePermisoRol = async (record, val) => {
  try {
    await axios.post('permisos/rol/save', {
      role_id: rolSeleccionado.value,
      permission_id: record.id,
      status: val,
    })
    record.asignado = val
  } catch {
    notif('error', 'Error', 'No se pudo actualizar')
    record.asignado = !val
  }
}

// ── Overrides Usuario ──────────────────────────────────

const buscarUsuarios = async () => {
  if (!buscarUsuario.value || buscarUsuario.value.length < 3) return
  try {
    const res = await axios.get('permisos/get-usuarios', { params: { term: buscarUsuario.value } })
    usuariosEncontrados.value = res.data.datos
    usuariosOptions.value = res.data.datos.map(u => ({
      value: u.id,
      label: `${u.paterno} ${u.materno} ${u.name} (${u.email})`,
    }))
  } catch {
    notif('error', 'Error', 'No se pudieron buscar usuarios')
  }
}

const loadPermisosUsuario = async () => {
  if (!usuarioSeleccionado.value) return
  loadingOverride.value = true
  try {
    const [permRes, userRes] = await Promise.all([
      axios.get('permisos/get-permisos'),
      axios.get('permisos/usuario/get', { params: { user_id: usuarioSeleccionado.value } }),
    ])
    const overrides = {}
    userRes.data.datos.forEach(o => {
      overrides[o.permission_id] = o.type
    })
    permisosUsuario.value = permRes.data.datos.map(p => ({
      ...p,
      override_type: overrides[p.id] || 'none',
    }))
  } catch {
    notif('error', 'Error', 'No se pudieron cargar los permisos del usuario')
  } finally {
    loadingOverride.value = false
  }
}

const togglePermisoUsuario = async (record, val) => {
  if (val === 'none') {
    record.override_type = 'none'
    return
  }
  try {
    await axios.post('permisos/usuario/save', {
      user_id: usuarioSeleccionado.value,
      permission_id: record.id,
      type: val,
      status: true,
    })
    record.override_type = val
  } catch {
    notif('error', 'Error', 'No se pudo guardar el override')
  }
}

const notif = (type, titulo, mensaje) => {
  notification[type]({ message: titulo, description: mensaje, placement: 'topRight' })
}

getModulos()
getPermisos()
getRoles()
getAcciones()
</script>

<style scoped src="./permisos.css"></style>

<style>
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
