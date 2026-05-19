<template>
  <a-layout class="dashboard">
    <a-layout-sider
      v-model:collapsed="collapsed"
      collapsible
      breakpoint="md"
      theme="light"
      :width="240"
      :collapsed-width="60"
      :trigger="null"
      class="sider"
    >
      <div class="sider-logo">
        <img
          src="../../assets/imagenes/logotiny.png"
          alt="Logo"
        />
        <div>
          <div> 
            <span v-if="!collapsed" class="sider-title">Dirección de Admisión</span>
          </div>
          <div> 
            <span v-if="!collapsed" class="sider-subtitle">PERFIL REVISOR</span>
          </div>
        </div>
      </div>

      <div v-if="!collapsed" class="sider-user-pro">
        <div class="profile">
          <a-avatar :src="userAvatar" :size="80"/>
          <div class="username">{{ userName }} {{ page.props.auth?.user?.paterno }}</div>
          <div class="user-role">{{ page.props.auth?.user?.dni }}</div>
        </div>

        <a-select
          v-model:value="proceso"
          show-search
          placeholder="Selecciona un proceso"
          style="width: 100%"
          size="middle"
          option-filter-prop="label"
          :options="procesos"
          @change="cambiarProceso"
        />
      </div>

      <a-menu
        v-model:selectedKeys="selectedKeys"
        v-model:openKeys="openKeys"
        mode="inline"
        class="sider-menu"
        style="padding: 0px 5px;"
      >
        <template v-for="item in menuItems" :key="item.key">
          <a-menu-item v-if="!item.children" :key="item.key">
            <Link
              :href="item.route"
              class="menu-link"
            >
              <component :is="item.icon" class="menu-icon" />
              <span>{{ item.label }}</span>
            </Link>
          </a-menu-item>

          <a-sub-menu v-else :key="item.key">
            <template #icon>
              <component :is="item.icon" class="menu-icon" />
            </template>
            <template #title>
              <div style="margin-left: 10px;">{{ item.label }}</div>
            </template>
            <a-menu-item v-for="child in item.children" :key="child.key">
              <Link
                :href="child.route"
                class="menu-link"
              >
                <span class="dot-submenu">•</span>
                <span>{{ child.label }}</span>
              </Link>
            </a-menu-item>
          </a-sub-menu>
        </template>
      </a-menu>
    </a-layout-sider>

    <a-layout>
      <a-layout-header class="header">
        <menu-fold-outlined
          class="trigger"
          @click="collapsed = !collapsed"
        />
        <span class="header-title">{{ props.pagina }} </span>
        <div class="header-right">
          <a-dropdown :trigger="['click']" placement="bottomRight">
            <div class="user-menu-trigger">
              <div class="user-avatar-wrapper">
                <a-avatar size="32" :src="userAvatar" class="user-avatar" />
              </div>
              <div class="user-info">
                <span class="user-name">{{ userName }}</span>
              </div>
              <down-outlined class="dropdown-arrow" />
            </div>
            <template #overlay>
              <a-menu>
                <a-menu-item key="profile">
                  <template #icon>
                    <user-outlined />
                  </template>
                  <span>Mi perfil</span>
                </a-menu-item>
                <a-menu-divider />
                <a-menu-item key="logout" @click="handleLogout">
                  <template #icon>
                    <logout-outlined />
                  </template>
                  <span>Cerrar sesión</span>
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>
      </a-layout-header>

      <a-layout-content class="content">
        <div style="padding: 0px;">
          <!-- <div>{{ procesos }}</div>
          <div><pre>
            {{ page.props.auth?.user }}
          </pre></div> -->
          <slot />
        </div>
      </a-layout-content>
    </a-layout>
  </a-layout>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { message } from 'ant-design-vue'

import {
  MenuFoldOutlined,
  DownOutlined,
  CameraOutlined,
  LogoutOutlined,
  AppstoreFilled,
  SettingFilled,
  UserOutlined
} from '@ant-design/icons-vue'

const page = usePage()

/* =========================================
   PROPS
========================================= */

const props = defineProps({
  pagina: {
    type: String,
    required: true
  }
})

/* =========================================
   ESTADOS
========================================= */

const collapsed = ref(false)

const selectedKeys = ref([])

const openKeys = ref([])

const proceso = ref(null)

const procesos = ref([])

/* =========================================
   USUARIO
========================================= */

const userName = computed(
  () => page.props.auth?.user?.name || 'Ariel Luque'
)

const userAvatar = computed(
  () =>
    page.props.auth?.user?.avatar ||
    'https://i.pravatar.cc/150?img=32'
)

/* =========================================
   CAMBIAR PROCESO
========================================= */

const cambiarProceso = async (value) => {

  try {

    if (!value) return

    const res = await axios.post(
      '/revisor/cambiar_proceso',
      {
        id_proceso: value
      }
    )

    if (res.data.estado === true) {

      message.success('Proceso actualizado')

      window.location.reload()

    } else {

      message.error('No se pudo actualizar')

    }

  } catch (error) {

    console.error(error)

    message.error('Error al cambiar proceso')

  }
}

/* =========================================
   OBTENER PROCESOS
========================================= */

const getProcesos = async () => {

  try {

    const res = await axios.get(
      '/api/get-select-procesos'
    )

    if (res.data.estado) {

      procesos.value = res.data.datos

      // IMPORTANTE:
      // asignar después de cargar options
      proceso.value =
        Number(page.props.auth?.user?.id_proceso)

    }

  } catch (error) {

    console.error(error)

    message.error(
      'Error al cargar procesos'
    )

  }
}

/* =========================================
   MENÚ
========================================= */

const menuItems = [
  {
    key: 'dashboard',
    icon: AppstoreFilled,
    label: 'Dashboard',
    route: '/revisor'
  },

  {
    key: 'gestion_acceso',
    icon: CameraOutlined,
    label: 'Gestión de acceso',
    children: [
      {
        key: 'fotos',
        label: 'Fotos',
        route: '/revisor/foto-inscripcion'
      },

      {
        key: 'revision',
        label: 'Revisión',
        route:
          '/revisor/revisor-impresion-inscripcion'
      },

      {
        key: 'fotos_huellas',
        label: 'Fotos y huellas',
        route: '/revisor/fotos-admision'
      }
    ]
  },

  {
    key: 'control_biometrico',
    icon: CameraOutlined,
    label: 'Control Biométrico',
    children: [
      {
        key: 'fotos_bio',
        label: 'Fotos biométrico',
        route: '/revisor/foto-biometrico'
      },

      {
        key: 'revision_bio',
        label: 'Revisión biométrico',
        route: '/revisor/revisor-imprimir'
      }
    ]
  },

  {
    key: 'certificados',
    icon: SettingFilled,
    label: 'Certificados',
    route: '/revisor/revisor-validacion'
  }
]

/* =========================================
   MAPEO RUTAS
========================================= */

const routeToKeyMap = {
  '/revisor': 'dashboard',

  '/revisor/foto-inscripcion': 'fotos',

  '/revisor/revisor-impresion-inscripcion':
    'revision',

  '/revisor/fotos-admision':
    'fotos_huellas',

  '/revisor/foto-biometrico':
    'fotos_bio',

  '/revisor/revisor-imprimir':
    'revision_bio',

  '/revisor/revisor-validacion':
    'certificados'
}

const childToParentMap = {
  fotos: 'gestion_acceso',

  revision: 'gestion_acceso',

  fotos_huellas: 'gestion_acceso',

  fotos_bio: 'control_biometrico',

  revision_bio: 'control_biometrico'
}

/* =========================================
   MENÚ ACTIVO
========================================= */

const setMenuState = (url) => {

  const cleanUrl = url.split('?')[0]

  const currentKey =
    routeToKeyMap[cleanUrl]

  if (currentKey) {

    selectedKeys.value = [currentKey]

    const parentKey =
      childToParentMap[currentKey]

    openKeys.value = parentKey
      ? [parentKey]
      : []

  } else {

    selectedKeys.value = []

    openKeys.value = []

  }
}

watch(
  () => page.url,
  (newUrl) => {
    setMenuState(newUrl)
  },
  {
    immediate: true
  }
)

watch(collapsed, (value) => {

  if (!value) {

    setMenuState(page.url)

  }

})

/* =========================================
   LOGOUT
========================================= */

const handleLogout = async () => {

  try {

    await router.post('/logout')

    message.success(
      'Sesión cerrada correctamente'
    )

    window.location.href = '/login'

  } catch (error) {

    console.error(error)

    message.error(
      'Error al cerrar sesión'
    )

  }
}

/* =========================================
   MOUNT
========================================= */

onMounted(async () => {

  await getProcesos()

})
</script>
<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap");

.dashboard {
  min-height: 100vh;
  background: #ececec;
  font-family: "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
  font-size: 14px;
  color: #2c2c2c;
}

.sider {
  background: #f9f9f9 !important;
  border-right: 1px solid rgba(0, 0, 0, 0.05);
  box-shadow: 2px 0 6px rgba(0, 0, 0, 0.03);
  backdrop-filter: blur(10px);
}

.sider-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 18px 14px;
  height: 64px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.07);
}

.sider-logo img {
  margin-top: -6px;
  height: 32px;
  filter: grayscale(10%);
}

.sider-title {
  font-weight: 600;
  font-size: 14px;
  color: #1d1d1d;
  letter-spacing: -0.3px;
}

.sider-subtitle {
  font-weight: 400;
  font-size: 0.7rem;
  color: #7a7a7a;
}

.sider-user-pro {
  padding: 20px 18px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.profile {
  text-align: center;
}

.username {
  margin-top: 10px;
  font-weight: 600;
  font-size: 14px;
  color: #222;
}

.user-role {
  font-size: 12px;
  color: #6c6c6c;
}

/* === MENÚ === */
.sider-menu {
  background: transparent !important;
  border: none;
  padding: 10px 0;
}

.menu-link {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 10pt;
  color: #444 !important;
  transition: all 0.2s ease;
  padding: 3px 0;
}

.menu-icon {
  font-size: 14px;
  color: #6a6a6a;
  transition: color 0.2s ease;
}

.dot-submenu {
  font-size: 16px;
  color: #9ca3af;
  margin-left: 4px;
}

.sider-menu :deep(.ant-menu-item-selected) {
  background: #e9e9e9 !important;
  border-radius: 8px;
}

.sider-menu :deep(.ant-menu-item-selected .menu-link),
.sider-menu :deep(.ant-menu-item-selected .menu-icon) {
  color: #152245 !important;
  font-weight: 600;
}

.sider-menu :deep(.ant-menu-item:hover),
.sider-menu :deep(.ant-menu-submenu-title:hover) {
  background: #efefef !important;
  border-radius: 8px;
}

.sider-menu :deep(.ant-menu-submenu-title:hover .menu-icon),
.menu-link:hover .menu-icon {
  color: #000 !important;
}

.menu-link:hover {
  color: #000 !important;
}

.sider-menu :deep(.ant-menu-submenu-title) {
  font-size: .85rem;
}

.sider-menu :deep(.ant-menu-submenu-open .ant-menu-submenu-title) {
  background: transparent !important;
  color: #1a1a1a !important;
  font-weight: 500;
  font-size: .85rem;
}

.sider-menu :deep(.ant-menu-sub) {
  background: transparent !important;
  box-shadow: none !important;
  border: none !important;
}

.sider-menu :deep(.ant-menu-item:focus),
.sider-menu :deep(.ant-menu-submenu-title:focus) {
  background: transparent !important;
  outline: none !important;
  color: inherit !important;
}

/* === HEADER === */
.header {
  background: #fcfcfc;
  padding: 0 20px;
  display: flex;
  align-items: center;
  border-bottom: 1px solid rgba(0, 0, 0, 0.07);
  height: 58px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
  backdrop-filter: blur(10px);
}

.trigger {
  font-size: 16px;
  cursor: pointer;
  color: #555;
  padding: 6px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.trigger:hover {
  background-color: #e0e0e0;
  color: #111;
}

.header-title {
  margin-left: 14px;
  font-weight: 600;
  font-size: 15px;
  color: #1f1f1f;
}

.header-right {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 14px;
}

.content {
  margin: 10px;
}

.content-card {
  border-radius: 10px;
  background: #ffffff;
  box-shadow: 0 3px 12px #0000000d;
  border: 1px solid #0000000f;
  min-height: calc(100vh - 80px);
  padding: 0px;
}

.user-menu-trigger {
  height: 42px;
  border-radius: 15px;
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 0px 14px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.user-menu-trigger:hover {

  background: #e6e6e6;
  color: #fff;
  margin-right: 10px;
}

.user-avatar {
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 500;
  font-size: 13px;
  color: #222;
}

.dropdown-arrow {
  font-size: 12px;
  color: #00009a;
}
</style>  