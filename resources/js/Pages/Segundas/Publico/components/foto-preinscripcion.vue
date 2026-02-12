<template>
  <a-modal
    :open="visible"
    :title="localFormData.id ? 'Editar usuario' : 'Nuevo usuario'"
    :confirm-loading="loading"
    ok-text="Guardar"
    cancel-text="Cancelar"
    @ok="handleSave"
    @cancel="handleCancel"
    width="720px"
    centered
  >
    <a-form ref="formRef" :model="localFormData" layout="vertical" size="middle">
      <a-tabs default-active-key="1">
        <a-tab-pane key="1" tab="Datos Personales">
          <div class="space-y-4">
            <a-form-item label="DNI" name="dni">
              <a-input v-model:value="localFormData.dni" placeholder="Ej: 12345678" size="large" allow-clear :maxlength="8"/>
            </a-form-item>

            <a-form-item label="Nombres" name="nombres">
              <a-input v-model:value="localFormData.nombres" placeholder="Ej: Juan Carlos" size="large" allow-clear/>
            </a-form-item>

            <div class="grid grid-cols-2 gap-4">
              <a-form-item label="Apellido Paterno" name="paterno">
                <a-input v-model:value="localFormData.paterno" placeholder="Ej: Pérez" size="large" allow-clear/>
              </a-form-item>

              <a-form-item label="Apellido Materno" name="materno">
                <a-input v-model:value="localFormData.materno" placeholder="Ej: Gómez" size="large" allow-clear/>
              </a-form-item>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <a-form-item label="Celular" name="celular">
                <a-input v-model:value="localFormData.celular" placeholder="Ej: 999888777" size="large" allow-clear/>
              </a-form-item>

              <a-form-item label="Correo" name="email">
                <a-input v-model:value="localFormData.email" placeholder="ejemplo@correo.com" size="large" allow-clear/>
              </a-form-item>
            </div>
          </div>
        </a-tab-pane>

        <a-tab-pane key="2" tab="Datos de Usuario">
          <div class="grid grid-cols-12 gap-4">
            <div class="col-span-4 flex flex-col items-center">
              <div
                class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-blue-400 transition-colors w-full"
                @click="$refs.fileInput.click()"
              >
                <div v-if="!previewImage" class="py-8">
                  <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <span class="text-gray-500 text-sm mt-2 block">Haz clic para subir foto</span>
                </div>
                <img v-else :src="previewImage" class="rounded-lg w-full max-h-48 object-cover"/>
              </div>

              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                @change="handleFileChange"
                class="hidden"
              />

              <a-button v-if="previewImage" type="link" size="small" @click="removeFile" danger class="mt-2">
                Eliminar foto
              </a-button>

              <div v-if="selectedFile" class="text-xs text-gray-500 mt-1">
                Archivo: {{ selectedFile.name }} ({{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB)
              </div>
            </div>

            <div class="col-span-8 space-y-4">
              <a-form-item label="Contraseña" name="password">
                <a-input-password v-model:value="localFormData.password" placeholder="Ingresa contraseña" size="large"/>
              </a-form-item>

              <a-form-item label="Confirmar" name="password_confirmation">
                <a-input-password v-model:value="localFormData.password_confirmation" placeholder="Confirma contraseña" size="large"/>
              </a-form-item>

              <a-form-item label="Rol" name="rol">
                <a-select
                  v-model:value="localFormData.rol"
                  placeholder="Selecciona rol"
                  size="large"
                  :options="[
                    { value: 1, label: '👑 Administrador' },
                    { value: 2, label: '📊 Calificador' },
                    { value: 3, label: '📢 Publicador' }
                  ]"
                />
              </a-form-item>

              <a-form-item label="Estado" name="estado">
                <a-select
                  v-model:value="localFormData.estado"
                  placeholder="Selecciona estado"
                  size="large"
                  :options="[
                    { value: 1, label: '🟢 Activo' },
                    { value: 0, label: '🔴 Inactivo' }
                  ]"
                />
              </a-form-item>
            </div>
          </div>
        </a-tab-pane>
      </a-tabs>
    </a-form>
  </a-modal>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { message } from 'ant-design-vue'

const props = defineProps({ visible: Boolean, formData: Object, loading: Boolean })
const emit = defineEmits(['save', 'cancel'])

const formRef = ref()
const fileInput = ref()
const previewImage = ref('')
const selectedFile = ref(null)

const localFormData = reactive({
  id: null, dni: '', nombres: '', paterno: '', materno: '',
  celular: '', email: '', password: '', password_confirmation: '',
  foto: null, rol: null, estado: 1, fec_inicio: null, fec_fin: null
})

// Cargar datos existentes
watch(() => props.formData, (newData) => {
  if (newData) {
    Object.assign(localFormData, newData)

    if (newData.foto && typeof newData.foto === 'string') {
      previewImage.value = newData.foto.startsWith('data:')
        ? newData.foto
        : `${window.location.origin}/${newData.foto}`
      selectedFile.value = null
    }
  }
}, { immediate: true })

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    message.error('Solo se permiten imágenes')
    return
  }

  if (file.size / 1024 / 1024 > 2) {
    message.error('La imagen debe ser menor a 2MB')
    return
  }

  selectedFile.value = file

  const reader = new FileReader()
  reader.onload = e => previewImage.value = e.target.result
  reader.readAsDataURL(file)
}

const removeFile = () => {
  previewImage.value = ''
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
}

const handleSave = async () => {
  try {
    const values = await formRef.value.validateFields()
    const dataToSend = { ...localFormData, ...values }

    if (selectedFile.value) {
      dataToSend.foto = selectedFile.value // ✅ Aquí va el Blob/File real
    } else {
      dataToSend.foto = null
    }

    console.log('🔍 Enviando foto:', dataToSend.foto)
    emit('save', dataToSend)

  } catch (err) {
    message.error('Corrige los errores del formulario')
  }
}

const handleCancel = () => {
  formRef.value?.resetFields()
  previewImage.value = ''
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
  Object.assign(localFormData, {
    id: null, dni: '', nombres: '', paterno: '', materno: '',
    celular: '', email: '', password: '', password_confirmation: '',
    foto: null, rol: null, estado: 1, fec_inicio: null, fec_fin: null
  })
  emit('cancel')
}
</script>

<style scoped>
.border-dashed { border-style: dashed; }
</style>
