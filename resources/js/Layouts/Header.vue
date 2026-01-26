<template>
  <div>
    <!-- Menú desplegable del usuario -->
    <a-dropdown :trigger="['click']">
      <a @click.prevent style="text-decoration: none; color: black;">
        {{ $page.props.auth.user.name }} &nbsp;
        <DownOutlined />
      </a>
      <template #overlay>
        <a-menu>
          <a-menu-item key="0" @click="modalPerfil = true">
            Perfil
          </a-menu-item>
          <a-menu-item key="1" @click="showModal = true">
            Cambiar contraseña
          </a-menu-item>
          <a-menu-divider />
          <a-menu-item key="2" @click="logout">
            Salir del sistema
          </a-menu-item>
        </a-menu>
      </template>
    </a-dropdown>

    <!-- Modal para cambiar la contraseña -->
    <a-modal
      v-model:open="showModal"
      title="Cambiar Contraseña"
      ok-text="Guardar"
      cancel-text="Cancelar"
      @ok="save"
      :confirm-loading="confirmLoading"
      @cancel="handleCancel"
    >
      <a-form
        :model="form"
        :rules="rules"
        ref="formRef"
        layout="vertical"
      >
        <a-form-item
          label="Contraseña Antigua"
          name="oldPassword"
        >
          <a-input
            type="password"
            v-model:value="form.oldPassword"
            autocomplete="current-password"
          >
            <template #prefix> <UserOutlined /> </template>
          </a-input>
        </a-form-item>

        <a-form-item
          label="Nueva Contraseña"
          name="newPassword"
        >
          <a-input
            type="password"
            v-model:value="form.newPassword"
            autocomplete="new-password"
          >
            <template #prefix> <UserOutlined /> </template>
          </a-input>
        </a-form-item>

        <a-form-item
          label="Confirmar Nueva Contraseña"
          name="confirmPassword"
        >
          <a-input
            type="password"
            v-model:value="form.confirmPassword"
            autocomplete="new-password"
          >
            <template #prefix> <UserOutlined /> </template>
          </a-input>
        </a-form-item>
      </a-form>
    </a-modal>

    <!-- Modal del perfil -->
    <a-modal
      v-model:open="modalPerfil"
      title="Perfil de usuario"
      :footer="false"
      width="80%"
      style="top: 20px"
    >
      <PerfilUsuario />
    </a-modal>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { DownOutlined, UserOutlined } from '@ant-design/icons-vue';
import axios from 'axios';
import { notification } from 'ant-design-vue';
import PerfilUsuario from '@/Pages/Perfil/perfil.vue';

const showModal = ref(false);
const modalPerfil = ref(false);
const confirmLoading = ref(false);
const formRef = ref(null);

const form = reactive({
  oldPassword: '',
  newPassword: '',
  confirmPassword: '',
});

const rules = {
  oldPassword: [
    { required: true, message: 'Por favor, ingresa tu contraseña antigua.' },
  ],
  newPassword: [
    { required: true, message: 'Por favor, ingresa una nueva contraseña.' },
    { min: 6, message: 'La nueva contraseña debe tener al menos 6 caracteres.' },
  ],
  confirmPassword: [
    { required: true, message: 'Por favor, confirma tu nueva contraseña.' },
    {
      validator: (_, value) => {
        if (value !== form.newPassword) {
          return Promise.reject('Las contraseñas no coinciden.');
        }
        return Promise.resolve();
      },
    },
  ],
};

const save = () => {
  formRef.value
    .validate()
    .then(() => {
      confirmLoading.value = true;
      axios.post('cambiar-contra', form)
        .then(response => {
          notificacion('info', 'CONTRASEÑA ACTUALIZADA', "");
          showModal.value = false;
          // Limpiar formulario
          form.oldPassword = '';
          form.newPassword = '';
          form.confirmPassword = '';
        })
        .catch(error => {
          notificacion('error', '', error.response.data.mensaje);
          console.error('Error en la solicitud:', error.response.data.mensaje);
        })
        .finally(() => {
          confirmLoading.value = false;
        });
    })
    .catch(error => {
      console.log('Error de validación:', error);
    });
};

const handleCancel = () => {
  showModal.value = false;
  // Limpiar formulario al cancelar
  form.oldPassword = '';
  form.newPassword = '';
  form.confirmPassword = '';
};

const logout = async () => {
  try {
    await axios.post('/logout');
    location.href = '/login';
  } catch (error) {
    console.error('Error al cerrar sesión:', error);
  }
};

const notificacion = (type, titulo, mensaje) => {
  notification[type]({
    message: titulo,
    description: mensaje,
  });
};
</script>
