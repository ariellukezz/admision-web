<template>
  <Head title="Gestión de Módulos" />

  <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 md:p-6" style="height: calc(100vh - 100px);">

      <div class="flex flex-col sm:flex-row justify-between mb-6 gap-4">
        <h2 class="text-xl font-semibold text-gray-800">Módulos, Vistas y Acciones</h2>
        <a-button
          type="primary"
          style="background: #2563eb; border: none; border-radius: 6px;"
          @click="showModalModulo()"
        >
          <template #icon><PlusOutlined /></template>
          Nuevo Módulo
        </a-button>
      </div>

      <div class="overflow-x-auto">
        <a-table
          :columns="columns"
          :data-source="modulos"
          :loading="loading"
          :pagination="false"
          size="small"
          :scroll="{ y: 'calc(100vh - 280px)' }"
          row-key="id"
        >
          <!-- Nivel 2: Vistas dentro de un módulo -->
          <template #expandedRowRender="{ record }">
            <div class="pl-8">
              <div class="flex justify-between items-center mb-2">
                <span class="font-medium text-gray-600">Vistas ({{ record.views.length }})</span>
                <a-button size="small" type="link" @click="showModalView(record.id)">
                  <PlusOutlined /> Nueva Vista
                </a-button>
              </div>
              <a-table
                :columns="viewColumns"
                :data-source="record.views"
                :pagination="false"
                size="small"
                row-key="id"
              >
                <!-- Nivel 3: Acciones dentro de una vista -->
                <template #expandedRowRender="{ record: view }">
                  <div class="pl-8">
                    <div class="flex justify-between items-center mb-2">
                      <span class="font-medium text-gray-500">
                        Acciones de "{{ view.name }}" ({{ view.permissions?.length || 0 }})
                      </span>
                      <a-button size="small" type="link" @click="showModalAccion(view)">
                        <PlusOutlined /> Asignar Acción
                      </a-button>
                    </div>
                    <a-table
                      :columns="accionColumns"
                      :data-source="view.permissions || []"
                      :pagination="false"
                      size="small"
                      row-key="id"
                    >
                      <template #bodyCell="{ column, record: perm }">
                        <template v-if="column.dataIndex === 'action_code'">
                          <a-tag color="blue">{{ perm.action?.code }}</a-tag>
                        </template>
                        <template v-if="column.dataIndex === 'code'">
                          <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ view.code }}.{{ perm.action?.code }}</code>
                        </template>
                        <template v-if="column.dataIndex === 'status'">
                          <a-switch
                            :checked="perm.status"
                            checked-children="Activo"
                            un-checked-children="Inactivo"
                            size="small"
                            @change="() => toggleAccion(perm)"
                          />
                        </template>
                        <template v-if="column.dataIndex === 'acciones'">
                          <a-popconfirm title="¿Quitar esta acción de la vista?" @confirm="deleteAccion(perm.id)">
                            <a-button size="small" type="text" danger><DeleteOutlined /></a-button>
                          </a-popconfirm>
                        </template>
                      </template>
                    </a-table>
                  </div>
                </template>

                <template #bodyCell="{ column, record: view }">
                  <template v-if="column.dataIndex === 'status'">
                    <a-tag :color="view.status ? 'green' : 'red'">
                      {{ view.status ? 'ACTIVO' : 'INACTIVO' }}
                    </a-tag>
                  </template>
                  <template v-if="column.dataIndex === 'acciones'">
                    <a-space size="small">
                      <a-button size="small" type="text" class="text-blue-600" @click="editView(view)">
                        <EditOutlined />
                      </a-button>
                      <a-button size="small" type="text" @click="showModalAccion(view)" title="Asignar acción">
                        <PlusOutlined />
                      </a-button>
                      <a-popconfirm title="¿Eliminar vista?" @confirm="deleteView(view.id)">
                        <a-button size="small" type="text" danger><DeleteOutlined /></a-button>
                      </a-popconfirm>
                    </a-space>
                  </template>
                </template>
              </a-table>
            </div>
          </template>

          <template #bodyCell="{ column, record }">
            <template v-if="column.dataIndex === 'status'">
              <a-tag :color="record.status ? 'green' : 'red'">
                {{ record.status ? 'ACTIVO' : 'INACTIVO' }}
              </a-tag>
            </template>
            <template v-if="column.dataIndex === 'acciones'">
              <a-space size="small">
                <a-button size="small" type="text" class="text-blue-600" @click="editModulo(record)">
                  <EditOutlined />
                </a-button>
                <a-button size="small" type="text" @click="showModalView(record.id)">
                  <PlusOutlined />
                </a-button>
                <a-popconfirm title="¿Eliminar módulo y todas sus vistas?" @confirm="deleteModulo(record.id)">
                  <a-button size="small" type="text" danger><DeleteOutlined /></a-button>
                </a-popconfirm>
              </a-space>
            </template>
          </template>
        </a-table>
      </div>
    </div>
  </AuthenticatedLayout>

  <!-- Modal Módulo -->
  <a-modal
    v-model:open="modalModulo"
    centered
    :title="moduloForm.id ? 'Editar Módulo' : 'Nuevo Módulo'"
    width="90%"
    :style="{ maxWidth: '500px' }"
    :footer="null"
    destroy-on-close
  >
    <a-form ref="moduloFormRef" :model="moduloForm" :rules="moduloRules" layout="vertical">
      <a-form-item label="Código" name="code">
        <a-input v-model:value="moduloForm.code" placeholder="ej: postulantes" />
      </a-form-item>
      <a-form-item label="Nombre" name="name">
        <a-input v-model:value="moduloForm.name" placeholder="ej: Postulantes" />
      </a-form-item>
      <a-form-item label="Descripción" name="description">
        <a-input v-model:value="moduloForm.description" placeholder="Opcional" />
      </a-form-item>
      <a-form-item v-if="moduloForm.id" label="Estado" name="status">
        <a-switch v-model:checked="moduloForm.status" checked-children="Activo" un-checked-children="Inactivo" />
      </a-form-item>
      <div class="flex justify-end gap-3 pt-4 border-t">
        <a-button @click="modalModulo = false">Cancelar</a-button>
        <a-button type="primary" style="background: #2563eb; border: none;" :loading="guardando" @click="saveModulo">
          Guardar
        </a-button>
      </div>
    </a-form>
  </a-modal>

  <!-- Modal Vista -->
  <a-modal
    v-model:open="modalView"
    centered
    :title="viewForm.id ? 'Editar Vista' : 'Nueva Vista'"
    width="90%"
    :style="{ maxWidth: '500px' }"
    :footer="null"
    destroy-on-close
  >
    <a-form ref="viewFormRef" :model="viewForm" :rules="viewRules" layout="vertical">
      <a-form-item label="Módulo" name="module_id">
        <a-select v-model:value="viewForm.module_id" :options="moduloOptions" disabled />
      </a-form-item>
      <a-form-item label="Código" name="code">
        <a-input v-model:value="viewForm.code" placeholder="ej: lista-postulantes" />
      </a-form-item>
      <a-form-item label="Nombre" name="name">
        <a-input v-model:value="viewForm.name" placeholder="ej: Lista de Postulantes" />
      </a-form-item>
      <a-form-item label="Descripción" name="description">
        <a-input v-model:value="viewForm.description" placeholder="Opcional" />
      </a-form-item>
      <a-form-item v-if="viewForm.id" label="Estado" name="status">
        <a-switch v-model:checked="viewForm.status" checked-children="Activo" un-checked-children="Inactivo" />
      </a-form-item>
      <div class="flex justify-end gap-3 pt-4 border-t">
        <a-button @click="modalView = false">Cancelar</a-button>
        <a-button type="primary" style="background: #2563eb; border: none;" :loading="guardando" @click="saveView">
          Guardar
        </a-button>
      </div>
    </a-form>
  </a-modal>

  <!-- Modal Asignar Acción a Vista -->
  <a-modal
    v-model:open="modalAccion"
    centered
    title="Asignar Acción a Vista"
    width="90%"
    :style="{ maxWidth: '500px' }"
    :footer="null"
    destroy-on-close
  >
    <div class="mb-4">
      <span class="text-gray-500">Vista:</span>
      <span class="font-medium ml-2">{{ accionForm.viewName }}</span>
      <code class="ml-2 text-xs bg-gray-100 px-2 py-1 rounded">{{ accionForm.viewCode }}</code>
    </div>
    <a-form ref="accionFormRef" :model="accionForm" :rules="accionRules" layout="vertical">
      <a-form-item label="Acción" name="action_id">
        <a-select
          v-model:value="accionForm.action_id"
          :options="accionesDisponibles"
          placeholder="Seleccionar acción"
          show-search
          option-filter-prop="label"
        />
      </a-form-item>
      <div class="flex justify-end gap-3 pt-4 border-t">
        <a-button @click="modalAccion = false">Cancelar</a-button>
        <a-button type="primary" style="background: #2563eb; border: none;" :loading="guardando" @click="saveAccion">
          Asignar
        </a-button>
      </div>
    </a-form>
  </a-modal>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'
import { PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons-vue'
import { notification } from 'ant-design-vue'
import axios from 'axios'

const loading = ref(false)
const guardando = ref(false)
const modulos = ref([])
const modalModulo = ref(false)
const modalView = ref(false)
const modalAccion = ref(false)
const moduloFormRef = ref(null)
const viewFormRef = ref(null)
const accionFormRef = ref(null)

const moduloForm = ref({ id: null, code: '', name: '', description: '', status: true })
const viewForm = ref({ id: null, module_id: null, code: '', name: '', description: '', status: true })
const accionForm = ref({ view_id: null, viewCode: '', viewName: '', action_id: null })

const moduloRules = {
  code: [{ required: true, message: 'Ingrese el código' }],
  name: [{ required: true, message: 'Ingrese el nombre' }],
}
const viewRules = {
  module_id: [{ required: true, message: 'Seleccione un módulo' }],
  code: [{ required: true, message: 'Ingrese el código' }],
  name: [{ required: true, message: 'Ingrese el nombre' }],
}
const accionRules = {
  action_id: [{ required: true, message: 'Seleccione una acción' }],
}

const moduloOptions = ref([])
const accionesDisponibles = ref([])
const todasLasAcciones = ref([])

const columns = [
  { title: 'Código', dataIndex: 'code', key: 'code', width: 180 },
  { title: 'Nombre', dataIndex: 'name', key: 'name' },
  { title: 'Descripción', dataIndex: 'description', key: 'description', ellipsis: true },
  { title: 'Estado', dataIndex: 'status', key: 'status', align: 'center', width: 100 },
  { title: 'Acciones', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 130 },
]

const viewColumns = [
  { title: 'Código', dataIndex: 'code', key: 'code', width: 180 },
  { title: 'Nombre', dataIndex: 'name', key: 'name' },
  { title: 'Estado', dataIndex: 'status', key: 'status', align: 'center', width: 100 },
  { title: '', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 100 },
]

const accionColumns = [
  { title: 'Acción', dataIndex: 'action_code', key: 'action_code', width: 120 },
  { title: 'Descripción', dataIndex: ['action', 'description'], key: 'action_desc' },
  { title: 'Código de permiso', dataIndex: 'code', key: 'code', width: 220 },
  { title: 'Estado', dataIndex: 'status', key: 'status', align: 'center', width: 120 },
  { title: '', dataIndex: 'acciones', key: 'acciones', align: 'center', width: 60 },
]

const getModulos = async () => {
  loading.value = true
  try {
    const res = await axios.get('modulos/get')
    modulos.value = res.data.datos
    moduloOptions.value = modulos.value.map(m => ({ value: m.id, label: m.name }))
  } catch {
    notif('error', 'Error', 'No se pudieron cargar los módulos')
  } finally {
    loading.value = false
  }
}

const getAcciones = async () => {
  try {
    const res = await axios.get('modulos/get-acciones')
    todasLasAcciones.value = res.data.datos
    accionesDisponibles.value = res.data.datos.map(a => ({ value: a.id, label: `${a.code} — ${a.description}` }))
  } catch {
    notif('error', 'Error', 'No se pudieron cargar las acciones')
  }
}

// ── Módulo ────────────────────────────────────────────

const showModalModulo = () => {
  moduloForm.value = { id: null, code: '', name: '', description: '', status: true }
  modalModulo.value = true
}

const editModulo = (item) => {
  moduloForm.value = { ...item }
  modalModulo.value = true
}

const saveModulo = async () => {
  try { await moduloFormRef.value.validate() } catch { return }
  guardando.value = true
  try {
    const res = await axios.post('modulos/save', moduloForm.value)
    notif('success', 'Éxito', res.data.mensaje)
    getModulos()
    modalModulo.value = false
  } catch {
    notif('error', 'Error', 'No se pudo guardar')
  } finally {
    guardando.value = false
  }
}

const deleteModulo = async (id) => {
  try {
    await axios.get(`modulos/delete/${id}`)
    notif('warning', 'Eliminado', 'Módulo eliminado')
    getModulos()
  } catch {
    notif('error', 'Error', 'No se pudo eliminar')
  }
}

// ── Vista ──────────────────────────────────────────────

const showModalView = (moduleId) => {
  viewForm.value = { id: null, module_id: moduleId, code: '', name: '', description: '', status: true }
  modalView.value = true
}

const editView = (view) => {
  viewForm.value = { ...view }
  modalView.value = true
}

const saveView = async () => {
  try { await viewFormRef.value.validate() } catch { return }
  guardando.value = true
  try {
    const res = await axios.post('modulos/save-view', viewForm.value)
    notif('success', 'Éxito', res.data.mensaje)
    getModulos()
    modalView.value = false
  } catch {
    notif('error', 'Error', 'No se pudo guardar')
  } finally {
    guardando.value = false
  }
}

const deleteView = async (id) => {
  try {
    await axios.get(`modulos/delete-view/${id}`)
    notif('warning', 'Eliminado', 'Vista eliminada')
    getModulos()
  } catch {
    notif('error', 'Error', 'No se pudo eliminar')
  }
}

// ── Acción (permiso view+action) ──────────────────────

const showModalAccion = (view) => {
  accionForm.value = {
    view_id: view.id,
    viewCode: view.code,
    viewName: view.name,
    action_id: null,
  }
  // Filtrar acciones que ya están asignadas
  const asignadas = (view.permissions || []).map(p => p.action_id)
  accionesDisponibles.value = todasLasAcciones.value
    .filter(a => !asignadas.includes(a.id))
    .map(a => ({ value: a.id, label: `${a.code} — ${a.description}` }))
  modalAccion.value = true
}

const saveAccion = async () => {
  try { await accionFormRef.value.validate() } catch { return }
  guardando.value = true
  try {
    const res = await axios.post('modulos/save-accion', {
      view_id: accionForm.value.view_id,
      action_id: accionForm.value.action_id,
    })
    notif('success', 'Éxito', res.data.mensaje)
    getModulos()
    modalAccion.value = false
  } catch {
    notif('error', 'Error', 'No se pudo asignar la acción')
  } finally {
    guardando.value = false
  }
}

const deleteAccion = async (id) => {
  try {
    await axios.get(`modulos/delete-accion/${id}`)
    notif('warning', 'Quitada', 'Acción quitada de la vista')
    getModulos()
  } catch {
    notif('error', 'Error', 'No se pudo quitar')
  }
}

const toggleAccion = async (perm) => {
  try {
    await axios.post(`modulos/toggle-accion/${perm.id}`)
    perm.status = !perm.status
  } catch {
    notif('error', 'Error', 'No se pudo cambiar el estado')
    perm.status = !perm.status
  }
}

const notif = (type, titulo, mensaje) => {
  notification[type]({ message: titulo, description: mensaje, placement: 'topRight' })
}

getModulos()
getAcciones()
</script>

<style scoped>
:deep(.ant-btn-primary) {
  background: #2563eb !important;
  border-color: #2563eb !important;
}
</style>
