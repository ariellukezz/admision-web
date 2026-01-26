<template>
  <div class="profile-container">
    <!-- Header del perfil -->
    <div class="profile-header">
      <a-avatar :size="96" :src="userData.foto" class="profile-avatar">
        <UserOutlined />
      </a-avatar>
      <div class="header-info">
        <h1>{{ fullName }}</h1>
        <p class="email">{{ userData.rol }}</p>
        <div class="tags">
          <a-tag :color="getStatusColor(userData.estado)">
            {{ userData.estado == 1 ? 'Activo' : 'Inactivo' }}
          </a-tag>
          <a-tag :color="signatureStatus.color">
            {{ signatureStatus.text }}
          </a-tag>
        </div>
      </div>
      <a-button type="primary" @click="showSignatureModal" class="signature-btn">
        <SafetyCertificateOutlined />
        Certificados de Firma
      </a-button>
    </div>

    <!-- Contenido principal -->
    <a-row :gutter="24" class="content-grid">
      <!-- Columna izquierda -->
      <a-col :xs="24" :md="8" :lg="8">
        <!-- Información personal -->
        <a-card title="Información Personal" class="info-card">
          <a-descriptions :column="1" size="small">
            <a-descriptions-item label="DNI">
              {{ userData.dni }}
            </a-descriptions-item>
            <a-descriptions-item label="Teléfono">
              {{ userData.celular }}
            </a-descriptions-item>
            <a-descriptions-item label="Proceso">
              <code>{{ userData.proceso }}</code>
            </a-descriptions-item>
            <a-descriptions-item label="Estado">
              <a-tag :color="getStatusColor(userData.estado)" size="small">
                {{ userData.estado == 1 ?'Activo':'Inactivo' }}
              </a-tag>
            </a-descriptions-item>
          </a-descriptions>

          <a-divider />

          <div class="quick-actions">
            <a-button block @click="showEditModal" class="action-btn">
              <EditOutlined /> Editar Perfil
            </a-button>
            <a-button block @click="showPasswordModal" class="action-btn">
              <LockOutlined /> Cambiar Contraseña
            </a-button>
          </div>
        </a-card>

        <!-- Estadísticas -->
        <a-card title="Estadísticas" class="stats-card">
          <div class="stats-grid">
            <div class="stat-item">
              <div class="stat-value">.-.</div>
              <div class="stat-label">Documentos revisados</div>
            </div>
            <div class="stat-item">
              <div class="stat-value">.-.</div>
              <div class="stat-label">Firmados</div>
            </div>
            <div class="stat-item">
              <div class="stat-value">.-.</div>
              <div class="stat-label">Certificado</div>
            </div>
          </div>
        </a-card>
      </a-col>

      <!-- Columna derecha -->
      <a-col :xs="24" :md="16" :lg="16">
        <!-- Actividad reciente -->
        <a-card title="Actividad Reciente" class="activity-card">
          <a-timeline>
            <a-timeline-item v-for="activity in activities" :key="activity.id" :color="activity.color">
              <div class="activity-item">
                <div class="activity-title">{{ activity.title }}</div>
                <div class="activity-desc">{{ activity.description }}</div>
                <div class="activity-time">{{ activity.time }}</div>
              </div>
            </a-timeline-item>
          </a-timeline>
        </a-card>
      </a-col>
    </a-row>

    <!-- Modal Certificado Digital -->
    <a-modal
      v-model:open="signatureModalVisible"
      title="Certificado Digital"
      :footer="null"
      width="520px"
    >
      <div class="signature-modal">
        <div class="signature-header">
          <SafetyCertificateOutlined class="header-icon" />
          <div>
            <h3>Gestión de certificado digital</h3>
            <p>Creación y configuración de tu certificado personal</p>
          </div>
        </div>

         <div class="certificate-display">
            <div class="certificate-header">
              <SafetyCertificateOutlined class="icon" />
              <h3>Certificado Digital</h3>
            </div>

            <div v-if="certificado" class="certificate-content">
              <div class="field">
                <span class="label">Departamento:</span>
                <span class="value">{{ certificado.departamento || 'No especificado' }}</span>
              </div>

              <div class="field">
                <span class="label">Cargo:</span>
                <span class="value">{{ certificado.cargo || 'No especificado' }}</span>
              </div>

              <div class="field">
                <span class="label">Vigencia:</span>
                <span class="value">{{ certificado.valid_days || '0' }} días</span>
              </div>

              <div class="field">
                <span class="label">Creado:</span>
                <span class="value">{{ formatDate(certificado.fecha_creacion) }}</span>
              </div>

              <div class="field">
                <span class="label">Expira:</span>
                <span class="value expiry">{{ formatDate(certificado.fecha_expiracion) }}</span>
              </div>
            </div>

            <div v-else class="certificate-content empty-certificate">
              <div class="field">
                <span class="label">Estado:</span>
                <span class="value text-gray-500">No hay certificado registrado</span>
              </div>
              <div class="field">
                <span class="label">Acción:</span>
                <span class="value text-blue-500">Crea tu primer certificado</span>
              </div>
            </div>
          </div>

        <div class="signature-status">
          <span class="label">Firma electrónica</span>
          <div>
            <a-switch v-model:checked="signatureForm.estado" @change="actualizarEstado" />
            <span
              class="status-text"
              :class="signatureForm.estado ? 'activado' : 'desactivado'"
            >
            </span>
          </div>
        </div>

        <a-divider />

        <div class="mb-2">
          <span style="font-weight: bold; font-size: 1.2rem;">Actualizar contraseña del certificado digital</span>
        </div>

        <a-form layout="vertical" class="signature-form mt-0">
          <a-form-item label="Contraseña del Certificado">
            <a-input-password
              v-model:value="signatureForm.password_p12"
              placeholder="Contraseña segura"
            />
          </a-form-item>

          <a-form-item label="Vigencia del Certificado">
            <a-radio-group v-model:value="signatureForm.valid_days">
              <a-radio :value="365">1 año</a-radio>
              <a-radio :value="730">2 años</a-radio>
              <a-radio :value="1095">3 años</a-radio>
            </a-radio-group>
          </a-form-item>

          <a-divider />

          <a-button
            type="primary"
            block
            size="large"
            :loading="creatingSignature"
            @click="crearCertificadoDigital()"
          >
            {{certificado ? 'Actualizar Contraseña' : 'Crear Certificado'}}
          </a-button>
        </a-form>
      </div>
    </a-modal>

    <!-- Modal Editar Perfil -->
    <a-modal
      v-model:open="editModalVisible"
      title="Editar Perfil"
      @ok="actualizarDatos"
      ok-text="Guardar cambios"
      cancel-text="Cancelar"
      :ok-button-props="{ loading: updatingProfile }"
    >
      <a-form :model="editForm" layout="vertical">
        <a-row :gutter="16">
          <a-col :xs="24" :sm="12">
            <a-form-item label="Nombres">
              <a-input v-model:value="editForm.name">
                <template #prefix><UserOutlined /></template>
              </a-input>
            </a-form-item>
          </a-col>
          <a-col :xs="24" :sm="12">
            <a-form-item label="Apellido Paterno">
              <a-input v-model:value="editForm.paterno">
                <template #prefix><UserOutlined /></template>
              </a-input>
            </a-form-item>
          </a-col>
        </a-row>
        <a-form-item label="Apellido Materno">
          <a-input v-model:value="editForm.materno">
            <template #prefix><UserOutlined /></template>
          </a-input>
        </a-form-item>
        <a-form-item label="Email">
          <a-input v-model:value="editForm.email">
            <template #prefix><UserOutlined /></template>
          </a-input>
        </a-form-item>
        <a-form-item label="Teléfono">
          <a-input v-model:value="editForm.celular">
            <template #prefix><UserOutlined /></template>
          </a-input>
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- Modal Cambiar Contraseña -->
    <a-modal
      v-model:open="passwordModalVisible"
      title="Cambiar Contraseña"
      ok-text="Cambiar Contraseña"
      cancel-text="Cancelar"
      :ok-button-props="{ loading: changingPassword }"
      @ok="changePassword"
    >
      <a-form
        ref="passwordFormRef"
        :model="passwordForm"
        :rules="passwordRules"
        layout="vertical"
      >
        <a-form-item label="Contraseña actual" name="currentPassword">
          <a-input-password v-model:value="passwordForm.currentPassword" placeholder="Ingresa tu contraseña actual" />
        </a-form-item>
        <a-form-item label="Nueva contraseña" name="newPassword">
          <a-input-password v-model:value="passwordForm.newPassword" placeholder="Ingresa nueva contraseña" />
          <div v-if="passwordForm.newPassword" class="password-strength">
            <div class="strength-meter">
              <div
                class="strength-fill"
                :style="{ width: passwordStrength.percent + '%' }"
                :class="passwordStrength.class"
              ></div>
            </div>
            <span class="strength-text">{{ passwordStrength.text }}</span>
          </div>
        </a-form-item>
        <a-form-item label="Confirmar contraseña" name="confirmPassword">
          <a-input-password v-model:value="passwordForm.confirmPassword" placeholder="Confirma la nueva contraseña" />
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { message, Modal } from 'ant-design-vue'
import {
  UserOutlined,
  EditOutlined,
  LockOutlined,
  SafetyCertificateOutlined
} from '@ant-design/icons-vue'

// Estado reactivo
const creatingSignature = ref(false)
const signatureModalVisible = ref(false)
const editModalVisible = ref(false)
const passwordModalVisible = ref(false)
const signatureActive = ref(true)
const loading = ref(false)
const updatingProfile = ref(false)
const changingPassword = ref(false)
const certificado = ref(null)

// Datos del usuario
const userData = reactive({
  dni: '',
  name: '',
  paterno: '',
  materno: '',
  email: '',
  celular: '',
  id_proceso: null,
  id_rol: null,
  rol: '',
  proceso: '',
  estado: null,
  foto: 'https://i.pinimg.com/originals/2b/93/d8/2b93d8b64d3350b1151ac2ef05e89702.jpg'
})

// Formularios
const editForm = computed(() => ({
  name: userData.name,
  paterno: userData.paterno,
  materno: userData.materno,
  email: userData.email,
  celular: userData.celular
}))

const signatureForm = reactive({
  dni: userData.dni,
  email: userData.email,
  usuario: `${userData.name} ${userData.paterno} ${userData.materno}`,
  rol: userData.rol,
  estado: true,
  departamento: 'Dirección de admisión',
  password_p12: '',
  valid_days: 365
})

const passwordForm = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const passwordStrength = reactive({ percent: 0, text: '', class: '' })

// Datos de ejemplo
const activities = ref([])
const signatures = ref([])

// Reglas de validación
const passwordRules = {
  currentPassword: [
    { required: true, message: 'Ingresa tu contraseña actual', trigger: 'blur' }
  ],
  newPassword: [
    { required: true, message: 'Ingresa una nueva contraseña', trigger: 'blur' },
    { min: 8, message: 'Mínimo 8 caracteres', trigger: 'blur' },
    {
      validator: (_, value) => {
        const hasUpperCase = /[A-Z]/.test(value)
        const hasLowerCase = /[a-z]/.test(value)
        const hasNumbers = /\d/.test(value)
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(value)
        return (hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChar)
          ? Promise.resolve()
          : Promise.reject('Debe contener mayúsculas, minúsculas, números y símbolos')
      },
      trigger: 'blur'
    }
  ],
  confirmPassword: [
    { required: true, message: 'Confirma tu nueva contraseña', trigger: 'blur' },
    {
      validator: (_, value) => {
        if (!value || value === passwordForm.newPassword) {
          return Promise.resolve()
        }
        return Promise.reject('Las contraseñas no coinciden')
      },
      trigger: 'blur'
    }
  ]
}

// Computed properties
const fullName = computed(() => `${userData.name} ${userData.paterno} ${userData.materno}`)

const signatureStatus = computed(() => ({
  text: signatureForm.estado ? 'Firma Activa' : 'Firma Inactiva',
  color: signatureForm.estado ? 'green' : 'red'
}))

// Métodos
const getStatusColor = (status) => {
  const statusMap = {
    'Activo': 'green',
    'Pendiente': 'orange',
    'Inactivo': 'red'
  }
  return statusMap[status] || 'default'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

// Funciones de UI
const showSignatureModal = () => signatureModalVisible.value = true
const showEditModal = () => editModalVisible.value = true
const showPasswordModal = () => {
  passwordForm.currentPassword = ''
  passwordForm.newPassword = ''
  passwordForm.confirmPassword = ''
  passwordStrength.percent = 0
  passwordStrength.text = ''
  passwordStrength.class = ''
  passwordModalVisible.value = true
}

// Funciones de firmas
const toggleSignature = (signature) => {
  signature.active = !signature.active
  message.info(`Firma ${signature.active ? 'activada' : 'desactivada'}`)
}

const revokeSignature = (signature) => {
  Modal.confirm({
    title: '¿Revocar firma?',
    content: 'Los documentos firmados seguirán siendo válidos.',
    okText: 'Revocar',
    okType: 'danger',
    onOk: () => {
      signature.active = false
      message.warning('Firma revocada')
    }
  })
}

// Funciones API
const getDatosUsuario = async () => {
  try {
    const response = await axios.get('/get-datos-perfil')
    Object.assign(userData, response.data.data)
  } catch (error) {
    message.error('Error al cargar datos de usuario')
  }
}

const getActivityLogs = async () => {
  try {
    const response = await axios.get('/get-activity-log')
    activities.value = response.data.data || []
  } catch (error) {
    message.error('Error al cargar actividad')
  }
}

const getCertificado = async () => {
  try {
    const response = await axios.get('/get-certificado-digital')
    certificado.value = response.data.data
  } catch (error) {
    message.error('Error al cargar certificado')
  }
}

const actualizarDatos = async () => {
  updatingProfile.value = true
  try {
    const { data } = await axios.post('/actualizar-datos-perfil', editForm.value)
    if (data.success) {
      message.success('Perfil actualizado correctamente')
      editModalVisible.value = false
      await getDatosUsuario()
    } else {
      message.error(data.message || 'Error al actualizar perfil')
    }
  } catch (error) {
    message.error('Error al actualizar perfil')
  } finally {
    updatingProfile.value = false
  }
}

const actualizarEstado = async () => {
  try {
    const { data } = await axios.post('/actualizar-estado-firma-perfil', {
      estado: signatureForm.estado
    })
    if (data.success) {
      message.success('Perfil actualizado correctamente')
    } else {
      message.error(data.message || 'Error al actualizar perfil')
    }
  } catch (error) {
    message.error('Error al actualizar estado')
  }
}

const crearCertificadoDigital = async () => {
  creatingSignature.value = true
  try {
    const response = await axios.post('/crear-certificado-digital', {
      dni: userData.dni,
      email: userData.email,
      usuario: `${userData.name} ${userData.paterno} ${userData.materno}`,
      rol: userData.rol,
      departamento: 'Dirección de admisión',
      password_p12: signatureForm.password_p12,
      valid_days: signatureForm.valid_days
    })

    if (response.data.success) {
      message.success(response.data.message || 'Certificado creado exitosamente')
      signatureModalVisible.value = false
      await getCertificado()
    } else {
      message.error(response.data.message || 'Error al crear certificado')
    }
  } catch (error) {
    message.error('Error al crear certificado')
  } finally {
    creatingSignature.value = false
  }
}

const changePassword = async () => {
  if (!passwordForm.currentPassword) {
    message.error('Ingresa tu contraseña actual')
    return
  }

  if (passwordForm.newPassword !== passwordForm.confirmPassword) {
    message.error('Las contraseñas no coinciden')
    return
  }

  changingPassword.value = true
  try {
    const { data } = await axios.post('(/cambiar-contrasena-perfil', {
      password_actual: passwordForm.currentPassword,
      password_nueva: passwordForm.newPassword,
    })

    if (data.success) {
      message.success(data.message || 'Contraseña cambiada exitosamente')
      passwordModalVisible.value = false
      passwordForm.currentPassword = ''
      passwordForm.newPassword = ''
      passwordForm.confirmPassword = ''
      passwordStrength.percent = 0
      passwordStrength.text = ''
      passwordStrength.class = ''
    } else {
      message.error(data.message || 'Error al cambiar contraseña')
    }
  } catch (error) {
    message.error('Error al cambiar contraseña')
  } finally {
    changingPassword.value = false
  }
}

// Watchers
watch(() => passwordForm.newPassword, (newVal) => {
  if (!newVal) {
    passwordStrength.percent = 0
    passwordStrength.text = ''
    passwordStrength.class = ''
    return
  }

  let strength = 0
  if (newVal.length >= 8) strength += 25
  if (/[A-Z]/.test(newVal)) strength += 25
  if (/[a-z]/.test(newVal)) strength += 25
  if (/[0-9]/.test(newVal)) strength += 15
  if (/[^A-Za-z0-9]/.test(newVal)) strength += 10

  passwordStrength.percent = Math.min(strength, 100)

  if (strength < 50) {
    passwordStrength.text = 'Débil'
    passwordStrength.class = 'weak'
  } else if (strength < 75) {
    passwordStrength.text = 'Media'
    passwordStrength.class = 'medium'
  } else {
    passwordStrength.text = 'Fuerte'
    passwordStrength.class = 'strong'
  }
})

watch(
  () => userData.email,
  (val) => {
    signatureForm.email = val
  },
  { immediate: true }
)

// Inicialización
getDatosUsuario()
getActivityLogs()
getCertificado()
</script>

<style scoped>
.profile-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px;
}

.profile-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px;
  margin-bottom: 16px;
  padding: 24px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

@media (min-width: 768px) {
  .profile-header {
    flex-direction: row;
    align-items: center;
  }
}

.profile-avatar {
  flex-shrink: 0;
  border: 4px solid #f0f0f0;
}

.header-info {
  text-align: center;
  flex: 1;
}

@media (min-width: 768px) {
  .header-info {
    text-align: left;
  }
}

.header-info h1 {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 600;
  color: #1f2937;
  word-break: break-word;
}

.header-info .email {
  margin: 0px 0px 12px 0;
  color: #ec3333;
  word-break: break-word;
  font-weight: bold;
}

.tags {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: center;
}

@media (min-width: 768px) {
  .tags {
    justify-content: flex-start;
  }
}

.signature-btn {
  margin-top: 16px;
  width: 100%;
}

@media (min-width: 768px) {
  .signature-btn {
    margin-top: 0;
    width: auto;
    margin-left: auto;
  }
}

.content-grid {
  margin-top: 24px;
}

.info-card, .stats-card, .activity-card, .signatures-card {
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  margin-bottom: 24px;
}

.info-card :deep(.ant-card-head) {
  border-bottom: 2px solid #f0f0f0;
}

.quick-actions {
  margin-top: 16px;
}

.action-btn {
  margin-bottom: 8px;
  height: 40px;
}

.stats-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
  text-align: center;
}

@media (min-width: 576px) {
  .stats-grid {
    flex-direction: row;
    justify-content: space-around;
  }
}

.stat-item {
  padding: 8px;
  flex: 1;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #3b82f6;
  line-height: 1;
}

.stat-label {
  margin-top: 8px;
  color: #6b7280;
  font-size: 14px;
}

.activity-item {
  padding: 8px 0;
}

.activity-title {
  font-weight: 600;
  color: #1f2937;
}

.activity-desc {
  color: #6b7280;
  font-size: 14px;
  margin-top: 4px;
}

.activity-time {
  color: #9ca3af;
  font-size: 12px;
  margin-top: 4px;
}

.signatures-card {
  margin-top: 24px;
}

.signature-title {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 8px;
}

@media (min-width: 576px) {
  .signature-title {
    flex-direction: row;
    align-items: center;
    margin-bottom: 0;
  }
}

.signature-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 13px;
  color: #6b7280;
}

@media (min-width: 576px) {
  .signature-info {
    flex-direction: row;
    gap: 16px;
    flex-wrap: wrap;
  }
}

.signature-info span {
  display: flex;
  align-items: center;
  gap: 4px;
  white-space: nowrap;
}

.signature-modal {
  padding: 8px 0;
}

.signature-status {
  background: #f8fafc;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.signature-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.header-icon {
  font-size: 34px;
  color: #1677ff;
}

.signature-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.signature-header p {
  margin: 0;
  font-size: 13px;
  color: #888;
}

.signature-status .label {
  font-weight: 500;
}

.status-text {
  font-weight: 600;
  font-size: 13px;
}

.status-text.active {
  color: #52c41a;
}

.status-text.inactive {
  color: #ff4d4f;
}

.signature-form {
  background: #fafafa;
  padding: 16px;
  border-radius: 12px;
}

.certificate-display {
  background: white;
  border-radius: 8px;
  padding: 16px;
  border: 1px solid #e8e8e8;
  margin-bottom: 20px;
}

.certificate-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f0f0f0;
}

.certificate-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 500;
  color: #1890ff;
}

.icon {
  font-size: 20px;
  color: #1890ff;
}

.certificate-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.field {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 0;
}

.label {
  color: #666;
  font-size: 14px;
  font-weight: 400;
}

.value {
  color: #333;
  font-size: 14px;
  font-weight: 500;
}

.value.expiry {
  color: #52c41a;
  font-weight: 600;
}

.password-strength {
  margin-top: 8px;
}

.strength-meter {
  height: 6px;
  background: #f0f0f0;
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 4px;
}

.strength-fill {
  height: 100%;
  transition: width 0.3s ease, background-color 0.3s ease;
}

.strength-fill.weak {
  background-color: #ff4d4f;
}

.strength-fill.medium {
  background-color: #faad14;
}

.strength-fill.strong {
  background-color: #52c41a;
}

.strength-text {
  font-size: 12px;
  color: #6b7280;
  font-weight: 500;
}

:deep(.ant-list-item) {
  flex-direction: column;
  align-items: flex-start !important;
}

@media (min-width: 768px) {
  :deep(.ant-list-item) {
    flex-direction: row;
    align-items: center !important;
  }
}

:deep(.ant-list-item-actions) {
  margin-top: 12px;
  width: 100%;
  display: flex;
  gap: 8px;
}

@media (min-width: 768px) {
  :deep(.ant-list-item-actions) {
    margin-top: 0;
    width: auto;
  }
}

:deep(.ant-list-item-action) {
  margin: 0 !important;
}

:deep(.ant-list-item-action-split) {
  display: none;
}
</style>
