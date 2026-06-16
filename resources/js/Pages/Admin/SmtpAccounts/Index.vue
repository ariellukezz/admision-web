<template>
  <Head title="Correos SMTP" />

  <AuthenticatedLayout>
    <div class="bg-white overflow-hidden shadow-xl rounded-2xl" style="height: calc(100vh - 103px); display: flex; flex-direction: column;">

      <!-- Header -->
      <div class="border-b border-gray-100 px-8 py-6 bg-white rounded-t-2xl">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Correos SMTP</h1>
            <p class="text-sm text-gray-500 mt-1">Gestione las cuentas de correo para el envío de códigos de verificación</p>
          </div>

          <div class="flex items-center gap-4">
            <!-- Toggle email verification -->
            <div class="flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200">
              <span class="text-sm font-medium text-gray-700">Verificación por correo</span>
              <a-switch
                :checked="emailVerificationEnabled"
                @change="toggleEmailVerification"
                size="small"
              />
            </div>

            <button
              @click="showModal"
              class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md flex items-center gap-2"
            >
              <span>+</span> Nueva cuenta
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="flex-1 overflow-auto px-8 py-6">
        <table class="w-full">
          <thead class="bg-gray-50 rounded-lg sticky top-0">
            <tr class="border-b border-gray-200">
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Host:Port</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Remitente</th>
              <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Activo</th>
              <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Predeterminado</th>
              <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in cuentas" :key="item.id" class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors">
              <td class="py-3 px-4">
                <span class="text-sm font-medium text-gray-900">{{ item.name }}</span>
              </td>
              <td class="py-3 px-4">
                <span class="text-sm text-gray-600">{{ item.host }}:{{ item.port }}</span>
                <span v-if="item.encryption" class="ml-1 text-xs text-gray-400">({{ item.encryption }})</span>
              </td>
              <td class="py-3 px-4">
                <span class="text-sm text-gray-600">{{ item.username }}</span>
              </td>
              <td class="py-3 px-4">
                <div class="text-sm text-gray-900 font-medium">{{ item.from_name }}</div>
                <div class="text-xs text-gray-500">{{ item.from_address }}</div>
              </td>
              <td class="py-3 px-4 text-center">
                <a-switch
                  :checked="item.is_active"
                  @change="toggleActive(item)"
                  size="small"
                />
              </td>
              <td class="py-3 px-4 text-center">
                <button
                  v-if="!item.is_default"
                  @click="setDefault(item)"
                  class="text-xs text-gray-400 hover:text-blue-600 font-medium"
                >
                  Establecer
                </button>
                <span v-else class="inline-flex items-center gap-1 text-xs text-amber-600 font-semibold">
                  ★ Predeterminado
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <button @click="editar(item)" class="text-blue-600 hover:text-blue-800 text-sm font-medium mr-3">Editar</button>
                <button @click="confirmarEliminar(item)" class="text-red-500 hover:text-red-700 text-sm font-medium">Eliminar</button>
              </td>
            </tr>
            <tr v-if="cuentas.length === 0">
              <td colspan="7" class="py-8 text-center text-gray-400 text-sm">No hay cuentas SMTP registradas.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Crear/Editar -->
    <a-modal
      v-model:open="modalVisible"
      :title="editando ? 'Editar Cuenta SMTP' : 'Nueva Cuenta SMTP'"
      width="560px"
      :footer="null"
      @cancel="cerrarModal"
    >
      <div class="flex flex-col gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
          <input v-model="form.name" type="text" placeholder="Ej: Gmail admision.informatica1"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mailer *</label>
            <select v-model="form.mailer"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
              <option value="smtp">smtp</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cifrado</label>
            <select v-model="form.encryption"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
              <option value="ssl">SSL</option>
              <option value="tls">TLS</option>
              <option value="">Ninguno</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Puerto *</label>
            <input v-model="form.port" type="number" placeholder="465"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Host *</label>
          <input v-model="form.host" type="text" placeholder="smtp.gmail.com"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Usuario *</label>
          <input v-model="form.username" type="text" placeholder="admision.informatica1@unap.edu.pe"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña *</label>
          <input v-model="form.password" type="text" placeholder="App password"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
          <p v-if="editando" class="text-xs text-gray-400 mt-1">Dejar en blanco para mantener la contraseña actual.</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo remitente *</label>
            <input v-model="form.from_address" type="email" placeholder="dgadmision@unap.edu.pe"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre remitente *</label>
            <input v-model="form.from_name" type="text" placeholder="DIRECCIÓN DE ADMISIÓN UNA PUNO"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400" />
          </div>
        </div>

        <div class="flex items-center gap-2">
          <input type="checkbox" v-model="form.is_active" id="estado-smtp" class="rounded" />
          <label for="estado-smtp" class="text-sm text-gray-700">Cuenta activa</label>
        </div>

        <div class="flex items-center gap-2">
          <input type="checkbox" v-model="form.is_default" id="default-smtp" class="rounded" />
          <label for="default-smtp" class="text-sm text-gray-700">Establecer como predeterminada</label>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
          <button @click="cerrarModal" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Cancelar</button>
          <button @click="guardar" :disabled="guardando" class="px-5 py-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
            {{ guardando ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </a-modal>

    <!-- Confirmar eliminar -->
    <a-modal v-model:open="eliminarVisible" title="Eliminar cuenta SMTP" :footer="null" width="400px">
      <p class="text-sm text-gray-600">¿Está seguro de eliminar la cuenta <strong>{{ eliminarItem?.name }}</strong>?</p>
      <div class="flex justify-end gap-3 mt-4">
        <button @click="eliminarVisible = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancelar</button>
        <button @click="eliminar" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg">Eliminar</button>
      </div>
    </a-modal>

  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted } from 'vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const cuentas = ref([]);
const modalVisible = ref(false);
const eliminarVisible = ref(false);
const editando = ref(false);
const guardando = ref(false);
const eliminarItem = ref(null);
const emailVerificationEnabled = ref(true);

const form = ref({
  id: null,
  name: '',
  mailer: 'smtp',
  host: '',
  port: 465,
  username: '',
  password: '',
  encryption: 'ssl',
  from_address: '',
  from_name: '',
  is_active: true,
  is_default: false,
});

const limpiarForm = () => {
  form.value = {
    id: null,
    name: '',
    mailer: 'smtp',
    host: '',
    port: 465,
    username: '',
    password: '',
    encryption: 'ssl',
    from_address: '',
    from_name: '',
    is_active: true,
    is_default: false,
  };
};

const showModal = () => {
  limpiarForm();
  editando.value = false;
  modalVisible.value = true;
};

const editar = (item) => {
  form.value = {
    id: item.id,
    name: item.name,
    mailer: item.mailer,
    host: item.host,
    port: item.port,
    username: item.username,
    password: '',
    encryption: item.encryption || '',
    from_address: item.from_address,
    from_name: item.from_name,
    is_active: item.is_active,
    is_default: item.is_default,
  };
  editando.value = true;
  modalVisible.value = true;
};

const cerrarModal = () => {
  modalVisible.value = false;
  limpiarForm();
};

const guardar = async () => {
  if (!form.value.name || !form.value.host || !form.value.username || !form.value.from_address || !form.value.from_name) {
    notification.warning({ message: 'Datos incompletos', description: 'Complete todos los campos obligatorios.' });
    return;
  }
  if (!editando.value && !form.value.password) {
    notification.warning({ message: 'Contraseña requerida', description: 'Ingrese la contraseña de la cuenta SMTP.' });
    return;
  }

  guardando.value = true;
  try {
    if (editando.value) {
      await axios.put(`/admin/smtp-accounts/${form.value.id}`, form.value);
      notification.success({ message: 'Actualizado', description: 'Cuenta SMTP actualizada correctamente.' });
    } else {
      await axios.post('/admin/smtp-accounts', form.value);
      notification.success({ message: 'Creado', description: 'Cuenta SMTP creada correctamente.' });
    }
    modalVisible.value = false;
    limpiarForm();
    await getCuentas();
  } catch (e) {
    notification.error({ message: 'Error', description: e.response?.data?.mensaje || 'No se pudo guardar.' });
  } finally {
    guardando.value = false;
  }
};

const confirmarEliminar = (item) => {
  eliminarItem.value = item;
  eliminarVisible.value = true;
};

const eliminar = async () => {
  try {
    await axios.delete(`/admin/smtp-accounts/${eliminarItem.value.id}`);
    notification.success({ message: 'Eliminado', description: 'Cuenta SMTP eliminada.' });
    eliminarVisible.value = false;
    eliminarItem.value = null;
    await getCuentas();
  } catch (e) {
    notification.error({ message: 'Error', description: e.response?.data?.mensaje || 'No se pudo eliminar.' });
  }
};

const toggleActive = async (item) => {
  try {
    await axios.post(`/admin/smtp-accounts/${item.id}/toggle`);
    await getCuentas();
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudo cambiar el estado.' });
  }
};

const setDefault = async (item) => {
  try {
    await axios.post(`/admin/smtp-accounts/${item.id}/default`);
    notification.success({ message: 'Predeterminada', description: 'Cuenta establecida como predeterminada.' });
    await getCuentas();
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudo establecer como predeterminada.' });
  }
};

const toggleEmailVerification = async () => {
  try {
    const res = await axios.post('/admin/settings/preinscripcion-email/toggle');
    emailVerificationEnabled.value = res.data.valor;
    notification.success({ message: res.data.mensaje });
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudo cambiar la configuración.' });
  }
};

const getCuentas = async () => {
  try {
    const res = await axios.get('/admin/smtp-accounts/lista');
    cuentas.value = res.data.datos || [];
  } catch {
    notification.error({ message: 'Error', description: 'No se pudieron cargar las cuentas.' });
  }
};

const getEmailVerification = async () => {
  try {
    const res = await axios.get('/admin/settings/preinscripcion-email');
    emailVerificationEnabled.value = res.data.estado;
  } catch {
    // default to true on error
  }
};

onMounted(() => {
  getCuentas();
  getEmailVerification();
});
</script>
