<template>
  <a-layout class="reviewer-layout">
    <a-layout-sider
      v-model:collapsed="collapsed"
      :width="265"
      :collapsed-width="74"
      :trigger="null"
      breakpoint="lg"
      class="reviewer-sider"
      @breakpoint="isMobile = $event"
    >
      <div class="sider-shell">
        <div class="brand-block">
          <div class="brand-mark">
            <img src="../../assets/imagenes/logotiny.png" class="brand-logo" alt="Logo" />
          </div>
          <transition name="fade-slide">
            <div v-if="!collapsed" class="brand-copy">
              <span class="brand-kicker">UNA PUNO</span>
              <strong>Dirección de Admisión</strong>
            </div>
          </transition>
        </div>

        <div class="sider-scroll">
          <div class="profile-card" :class="{ compact: collapsed }">
            <a-avatar :src="userAvatar" :size="collapsed ? 32 : 58" class="profile-avatar">
              <span style="font-size: 1.4rem;">{{ userInitials }}</span>
            </a-avatar>
            <transition name="fade-slide">
              <div v-if="!collapsed" class="profile-copy">
                <span class="profile-label">Panel de revisor</span>
                <strong>{{ userFullName }}</strong>
                <span class="profile-meta">DNI {{ userDni }}</span>
              </div>
            </transition>
          </div>

          <transition name="fade-slide">
            <div v-if="!collapsed" class="process-card">
              <div class="process-heading">
                <span>Proceso activo</span>
                <check-circle-filled />
              </div>
              <a-select
                v-model:value="proceso"
                show-search
                placeholder="Seleccionar proceso"
                option-filter-prop="label"
                :options="procesos"
                class="process-select"
                @change="cambiarProceso"
              />
            </div>
          </transition>

          <div class="menu-section" :class="{ compact: collapsed }">
            <transition name="fade-slide">
              <div v-if="!collapsed" class="menu-label">Operacion</div>
            </transition>

            <a-menu
              v-model:selectedKeys="selectedKeys"
              v-model:openKeys="openKeys"
              theme="dark"
              mode="inline"
              class="reviewer-menu"
            >
              <template v-for="item in filteredMenuItems" :key="item.key">
                <a-menu-item v-if="!item.children" :key="item.key">
                  <Link :href="item.route" class="menu-link">
                    <component :is="item.icon" class="menu-icon" />
                    <span class="menu-text">{{ item.label }}</span>
                  </Link>
                </a-menu-item>

                <a-sub-menu v-else :key="item.key">
                  <template #icon>
                    <component :is="item.icon" class="menu-icon" />
                  </template>
                  <template #title>
                    <span class="menu-text">{{ item.label }}</span>
                  </template>
                  <a-menu-item v-for="child in item.children" :key="child.key">
                    <Link :href="child.route" class="menu-link child-link">
                      <span class="child-dot" />
                      <span class="menu-text">{{ child.label }}</span>
                    </Link>
                  </a-menu-item>
                </a-sub-menu>
              </template>
            </a-menu>
          </div>
        </div>

        <button class="collapse-action" type="button" @click="collapsed = !collapsed">
          <menu-unfold-outlined v-if="collapsed" />
          <menu-fold-outlined v-else />
          <transition name="fade-slide">
            <span v-if="!collapsed">Contraer panel</span>
          </transition>
        </button>
      </div>
    </a-layout-sider>

    <a-layout class="workspace">
      <a-layout-header class="topbar">
        <div class="topbar-left">
          <button class="icon-button" type="button" aria-label="Alternar menu" @click="collapsed = !collapsed">
            <menu-unfold-outlined v-if="collapsed" />
            <menu-fold-outlined v-else />
          </button>
          <div class="title-stack">
            <span class="eyebrow">Mesa de revision</span>
            <h1>{{ pageTitle }}</h1>
          </div>
        </div>

        <div class="topbar-right">
          <a-popover v-model:open="notifOpen" trigger="click" placement="bottomRight" overlay-class-name="notif-popover">
            <template #content>
              <div class="notif-dropdown">
                <div class="notif-head">
                  <div>
                    <span class="notif-kicker">Centro de alertas</span>
                    <strong>Notificaciones</strong>
                  </div>
                  <span class="notif-count">{{ noLeidas }}</span>
                </div>

                <button v-if="notificaciones.length > 0" class="mark-read" type="button" @click="marcarTodasLeidas">
                  Marcar todas como leidas
                </button>

                <div v-if="notificaciones.length > 0" class="notif-list">
                  <button
                    v-for="n in notificaciones"
                    :key="n.id"
                    type="button"
                    class="notif-item"
                    :class="{ unread: !n.leida }"
                    @click="clickNotificacion(n)"
                  >
                    <span class="notif-icon"><bell-outlined /></span>
                    <span class="notif-body">
                      <strong>{{ n.mensaje }}</strong>
                      <small>{{ n.created_at_diff }}</small>
                    </span>
                  </button>
                </div>

                <div v-else class="notif-empty">
                  <bell-outlined />
                  <span>No hay notificaciones pendientes</span>
                </div>

                <Link href="/revisor/solicitudes-revision" class="notif-link" @click="notifOpen = false">
                  Ver solicitudes
                </Link>
              </div>
            </template>

            <button class="icon-button bell-button" :class="{ active: noLeidas > 0 }" type="button" aria-label="Notificaciones">
              <bell-outlined />
              <span v-if="noLeidas > 0" class="bell-badge">{{ noLeidas > 9 ? '9+' : noLeidas }}</span>
            </button>
          </a-popover>

          <a-dropdown :trigger="['click']" placement="bottomRight">
            <button class="user-menu" type="button">
              <a-avatar :src="userAvatar" :size="34" class="user-avatar">{{ userInitials }}</a-avatar>
              <span>{{ userName }}</span>
              <down-outlined />
            </button>
            <template #overlay>
              <a-menu class="account-menu">
                <a-menu-item key="logout" @click="handleLogout">
                  <template #icon><logout-outlined /></template>
                  Cerrar sesion
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>
      </a-layout-header>

      <a-layout-content class="main-content">
        <div class="content-inner">
          <div v-if="showNotifBanner" class="notif-banner">
            <div class="notif-banner-text">
              <bell-outlined />
              <span>Activa las notificaciones push para recibir alertas cuando un postulante solicite revisión de documentos.</span>
            </div>
            <div class="notif-banner-actions">
              <button class="notif-banner-btn primary" type="button" @click="activarNotificaciones">Activar</button>
              <button class="notif-banner-btn ghost" type="button" @click="dismissNotifBanner">Cerrar</button>
            </div>
          </div>
          <slot />
        </div>
      </a-layout-content>
    </a-layout>
  </a-layout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { message, notification } from 'ant-design-vue'
import {
  AppstoreFilled,
  AuditOutlined,
  BellOutlined,
  CameraOutlined,
  CheckCircleFilled,
  DashboardOutlined,
  DownOutlined,
  FileDoneOutlined,
  LogoutOutlined,
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  SafetyCertificateOutlined,
} from '@ant-design/icons-vue'
import { useNotificaciones } from '@/composables/useFcm.js'

const page = usePage()

const props = defineProps({
  pagina: { type: String, default: '' },
  title: { type: [String, Number], default: '' },
})

const collapsed = ref(false)
const isMobile = ref(false)
const selectedKeys = ref([])
const openKeys = ref([])
const proceso = ref(null)
const procesos = ref([])

const notifOpen = ref(false)
const notificaciones = ref([])
const noLeidas = ref(page.props.notificacionesNoLeidas || 0)
const idsMostrados = new Set()
let fcmListener = null
const showNotifBanner = ref(false)

const checkNotifPermission = () => {
  if (!('Notification' in window)) return
  const dismissed = sessionStorage.getItem('notif_banner_dismissed')
  if (Notification.permission === 'default' && !dismissed) {
    showNotifBanner.value = true
  }
}

const activarNotificaciones = async () => {
  try {
    const fcm = useNotificaciones()
    await fcm.activar()
    showNotifBanner.value = false
    message.success('Notificaciones activadas correctamente')
  } catch {
    message.error('No se pudieron activar las notificaciones')
  }
}

const dismissNotifBanner = () => {
  showNotifBanner.value = false
  sessionStorage.setItem('notif_banner_dismissed', '1')
}

const user = computed(() => page.props.auth?.user || {})
const userName = computed(() => user.value.name || 'Usuario')
const userFullName = computed(() => [user.value.name, user.value.paterno].filter(Boolean).join(' ') || 'Usuario revisor')
const userDni = computed(() => user.value.dni || '---')
const userAvatar = computed(() => user.value.avatar || '')
const userInitials = computed(() => {
  const names = userFullName.value.trim().split(/\s+/).slice(0, 2)
  return names.map((name) => name.charAt(0)).join('').toUpperCase() || 'R'
})
const pageTitle = computed(() => props.pagina || props.title || activeMenuLabel.value || 'Revision')

const menuItems = [
  {
    key: 'dashboard',
    icon: DashboardOutlined,
    label: 'Dashboard',
    route: '/revisor',
    permission: 'revisor.access',
  },
  {
    key: 'mi_actividad',
    icon: AppstoreFilled,
    label: 'Mi Actividad',
    route: '/revisor/mi-actividad',
    permission: 'revisor-actividad.read',
  },
  {
    key: 'gestion_acceso',
    icon: CameraOutlined,
    label: 'Gestion de acceso',
    children: [
      { key: 'fotos', label: 'Fotos', route: '/revisor/foto-inscripcion', permission: 'revisor-inscripcion.read' },
      { key: 'revision', label: 'Revision', route: '/revisor/impresion', permission: 'revisor-inscripcion.read' },
      { key: 'fotos_huellas', label: 'Fotos y huellas', route: '/revisor/fotos-admision', permission: 'revisor-biometrico.read' },
    ],
  },
  {
    key: 'control_biometrico',
    icon: AuditOutlined,
    label: 'Control Biometrico',
    children: [
      { key: 'fotos_bio', label: 'Fotos biometrico', route: '/revisor/foto-biometrico', permission: 'revisor-biometrico.read' },
      { key: 'revision_bio', label: 'Revision biometrico', route: '/revisor/imprimir', permission: 'revisor-biometrico.read' },
    ],
  },
  {
    key: 'certificados',
    icon: SafetyCertificateOutlined,
    label: 'Certificados',
    route: '/revisor/validacion',
    permission: 'revisor-validacion.read',
  },
  {
    key: 'solicitudes_revision',
    icon: FileDoneOutlined,
    label: 'Solicitudes',
    route: '/revisor/solicitudes-revision',
    permission: 'revisor-solicitudes.read',
  },
]

const permissions = computed(() => page.props.auth?.permissions || [])

const hasPermission = (perm) => permissions.value.includes(perm)

const filteredMenuItems = computed(() => {
  return menuItems
    .map((item) => {
      if (!item.children) {
        return hasPermission(item.permission) ? item : null
      }
      const visibleChildren = item.children.filter((child) => hasPermission(child.permission))
      return visibleChildren.length > 0 ? { ...item, children: visibleChildren } : null
    })
    .filter(Boolean)
})

const flatMenu = computed(() => filteredMenuItems.value.flatMap((item) => item.children || item))
const activeMenuLabel = computed(() => {
  const cleanUrl = page.url.split('?')[0]
  return flatMenu.value.find((item) => item.route === cleanUrl)?.label
})

const routeToKeyMap = {
  '/revisor': 'dashboard',
  '/revisor/mi-actividad': 'mi_actividad',
  '/revisor/foto-inscripcion': 'fotos',
  '/revisor/impresion': 'revision',
  '/revisor/revisor-impresion-inscripcion': 'revision',
  '/revisor/fotos-admision': 'fotos_huellas',
  '/revisor/foto-biometrico': 'fotos_bio',
  '/revisor/imprimir': 'revision_bio',
  '/revisor/revisor-imprimir': 'revision_bio',
  '/revisor/validacion': 'certificados',
  '/revisor/revisor-validacion': 'certificados',
  '/revisor/solicitudes-revision': 'solicitudes_revision',
}

const childToParentMap = {
  fotos: 'gestion_acceso',
  revision: 'gestion_acceso',
  fotos_huellas: 'gestion_acceso',
  fotos_bio: 'control_biometrico',
  revision_bio: 'control_biometrico',
}

const setMenuState = (url) => {
  const cleanUrl = url.split('?')[0]
  const currentKey = routeToKeyMap[cleanUrl]
  selectedKeys.value = currentKey ? [currentKey] : []
  openKeys.value = currentKey && childToParentMap[currentKey] ? [childToParentMap[currentKey]] : []
}

const mostrarNotificacionNativa = (n) => {
  if (!('Notification' in window) || Notification.permission !== 'granted') return false

  try {
    const notif = new Notification('Nueva solicitud de revision', {
      body: n.mensaje || 'Tienes una nueva solicitud de revision de documentos',
      icon: '/favicon.ico',
      tag: n.id,
      data: { url: n.url },
      requireInteraction: true,
    })

    notif.onclick = () => {
      window.focus()
      if (n.url) window.location.href = n.url
      notif.close()
    }

    return true
  } catch {
    return false
  }
}

const cargarNotificaciones = async (mostrarPopup = false) => {
  try {
    const res = await axios.get('/revisor/notificaciones?limit=10')
    if (!res.data.success) return

    noLeidas.value = res.data.no_leidas || 0
    notificaciones.value = res.data.notificaciones || []

    if (!mostrarPopup) {
      notificaciones.value.forEach((n) => idsMostrados.add(n.id))
      return
    }

    notificaciones.value
      .filter((n) => !idsMostrados.has(n.id))
      .forEach((n) => {
        idsMostrados.add(n.id)
        if (!mostrarNotificacionNativa(n)) {
          notification.info({
            message: 'Nueva solicitud de revision',
            description: n.mensaje || 'Tienes una nueva solicitud de revision de documentos',
            placement: 'bottomRight',
            duration: 6,
            onClick: () => {
              if (n.url) window.location.href = n.url
            },
          })
        }
      })
  } catch {
    /* El polling no debe interrumpir el trabajo del revisor. */
  }
}

const marcarTodasLeidas = async () => {
  try {
    await axios.post('/revisor/notificaciones/leer-todas')
    noLeidas.value = 0
    notificaciones.value.forEach((n) => {
      n.leida = true
    })
  } catch {
    message.error('No se pudieron actualizar las notificaciones')
  }
}

const clickNotificacion = async (notif) => {
  if (!notif.leida) {
    try {
      await axios.post(`/revisor/notificaciones/${notif.id}/leer`)
      notif.leida = true
      noLeidas.value = Math.max(0, noLeidas.value - 1)
    } catch {
      /* Mantiene la navegacion aunque falle el marcado. */
    }
  }

  if (notif.url) {
    notifOpen.value = false
    window.location.href = notif.url
  }
}

const cambiarProceso = async (value) => {
  if (!value) return

  try {
    const res = await axios.post('/revisor/cambiar_proceso', { id_proceso: value })
    if (res.data.estado === true) {
      message.success('Proceso actualizado')
      window.location.reload()
      return
    }

    message.error('No se pudo actualizar el proceso')
  } catch (error) {
    console.error(error)
    message.error('Error al cambiar proceso')
  }
}

const getProcesos = async () => {
  try {
    const res = await axios.get('/api/get-select-procesos')
    if (res.data.estado) {
      procesos.value = res.data.datos
      proceso.value = Number(user.value.id_proceso)
    }
  } catch (error) {
    console.error(error)
    message.error('Error al cargar procesos')
  }
}

const handleLogout = async () => {
  try {
    const fcm = useNotificaciones()
    await fcm.logout()
  } catch (e) {
    console.warn('FCM logout failed:', e)
  }
  sessionStorage.removeItem('fcm_user_id')
  router.post('/logout', {}, {
    onSuccess: () => {
      window.location.href = '/login'
    },
    onError: () => {
      message.error('Error al cerrar sesion')
    },
  })
}

watch(() => page.url, setMenuState, { immediate: true })
watch(collapsed, (value) => {
  if (!value) setMenuState(page.url)
})
watch(isMobile, (value) => {
  if (value) collapsed.value = true
})

onMounted(async () => {
  await getProcesos()
  cargarNotificaciones(false)
  checkNotifPermission()

  // Escuchar notificaciones push de Firebase para recargar solo cuando llega una
  const fcm = useNotificaciones()
  fcmListener = () => cargarNotificaciones(true)
  fcm.fcmEventTarget.addEventListener('fcm-message', fcmListener)
})

onUnmounted(() => {
  if (fcmListener) {
    const fcm = useNotificaciones()
    fcm.fcmEventTarget.removeEventListener('fcm-message', fcmListener)
  }
})
</script>

<style>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap");

:root {
  --reviewer-ink: #111827;
  --reviewer-muted: #64748b;
  --reviewer-soft: #f6f8fb;
  --reviewer-line: #e5e7eb;
  --reviewer-accent: #3b82f6;
  --reviewer-accent-strong: #2563eb;
  --reviewer-blue: #2563eb;
  --sider-navy: #0f172a;
  --sider-panel: #111827;
  --sider-line: rgba(255, 255, 255, 0.07);
  --sider-text: #cbd5e1;
  --sider-muted: #94a3b8;
  --sider-accent: #3b82f6;
}

.reviewer-layout {
  min-height: 100vh;
  font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  background: #f4f6f9 !important;
}

.reviewer-sider {
  background:
    linear-gradient(180deg, rgba(59, 130, 246, 0.08), transparent 40%),
    linear-gradient(180deg, #0f172a 0%, #111827 50%, #0b1220 100%) !important;
  background-color: #0f172a !important;
  position: relative;
}

/* Grid pattern overlay */
.reviewer-sider::before {
  content: "";
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255, 255, 255, 0.010) 1px, transparent 1px),
    linear-gradient(45deg, rgba(255, 255, 255, 0.015) 1px, transparent 1px);
  background-size: 6px 6px;
  pointer-events: none;
  z-index: 0;
}

/* Subtle top glow */
.reviewer-sider::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 200px;
  background: radial-gradient(ellipse at top, rgba(59, 130, 246, 0.06), transparent 70%);
  pointer-events: none;
  z-index: 0;
}

.reviewer-sider .ant-layout-sider-children,
.reviewer-sider .sider-shell {
  position: relative;
  z-index: 1;
}

.sider-shell {
  position: relative;
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
}

.brand-block {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 78px;
  padding: 18px 18px 14px;
  border-bottom: 1px solid var(--sider-line);
}

.brand-mark {
  display: grid;
  place-items: center;
  width: 42px;
  height: 42px;
  flex: 0 0 42px;
  border-radius: 8px;
  background: linear-gradient(145deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.04));
  border: 1px solid rgba(255, 255, 255, 0.14);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.16);
}

.brand-logo {
  width: 28px;
  height: 28px;
  object-fit: contain;
}

.brand-copy {
  display: flex;
  flex-direction: column;
  min-width: 0;
  color: #fff;
  line-height: 1.1;
}

.brand-kicker {
  margin-bottom: 3px;
  color: #93c5fd;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.16em;
}

.brand-copy strong {
  font-size: 17px;
  font-weight: 800;
  letter-spacing: 0;
}

.sider-scroll {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  padding: 16px 12px 88px;
}

.sider-scroll::-webkit-scrollbar {
  width: 4px;
}

.sider-scroll::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.13);
  border-radius: 999px;
}

.profile-card {
  display: flex;
  align-items: center;
  gap: 13px;
  padding: 14px;
  border: 1px solid var(--sider-line);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.055);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.profile-card.compact {
  justify-content: center;
  padding: 12px 8px;
}

.profile-avatar,
.user-avatar {
  color: #0f172a;
  font-weight: 800;
  background: linear-gradient(145deg, #bfdbfe, #93c5fd);
  border: 1px solid rgba(255, 255, 255, 0.35);
}

.profile-copy {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.profile-label,
.profile-meta {
  color: var(--sider-muted);
  font-size: 11px;
  font-weight: 600;
}

.profile-copy strong {
  margin: 2px 0;
  color: var(--sider-text);
  font-size: 14px;
  font-weight: 800;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.process-card {
  margin-top: 12px;
  padding: 12px;
  border-radius: 8px;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid var(--sider-line);
}

.process-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
  color: #93c5fd;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.process-select {
  width: 100%;
}

.process-select .ant-select-selector {
  min-height: 38px !important;
  border: 1px solid rgba(255, 255, 255, 0.12) !important;
  border-radius: 8px !important;
  background: rgba(255, 255, 255, 0.08) !important;
  color: var(--sider-text) !important;
}

.process-select .ant-select-selection-item,
.process-select .ant-select-selection-placeholder,
.process-select .ant-select-arrow {
  color: var(--sider-text) !important;
}

.menu-section {
  padding-top: 18px;
}

.menu-section.compact {
  padding-top: 12px;
}

.menu-label {
  padding: 0 10px 8px;
  color: var(--sider-muted);
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.14em;
}

.reviewer-menu {
  background: transparent !important;
  border: 0 !important;
}

.reviewer-menu .ant-menu-item,
.reviewer-menu .ant-menu-submenu-title {
  width: auto !important;
  height: 42px !important;
  margin: 3px 0 !important;
  border-radius: 8px !important;
  color: var(--sider-muted) !important;
  line-height: 42px !important;
}

.reviewer-menu .ant-menu-item::after {
  display: none !important;
}

.reviewer-menu .menu-link {
  display: flex;
  align-items: center;
  gap: 11px;
  color: inherit;
  text-decoration: none;
}

.reviewer-menu .menu-icon {
  color: currentColor;
  font-size: 17px;
}

.reviewer-menu .menu-text {
  color: inherit;
  font-size: 12px;
  font-weight: 500;
}

.reviewer-menu .ant-menu-item:hover,
.reviewer-menu .ant-menu-submenu-title:hover {
  color: #fff !important;
  background: rgba(255, 255, 255, 0.08) !important;
}

.reviewer-menu .ant-menu-item-selected {
  color: #fff !important;
  background: linear-gradient(90deg, rgba(59, 130, 246, 0.22), rgba(59, 130, 246, 0.10)) !important;
  box-shadow: inset 3px 0 0 #3b82f6;
}

.reviewer-menu .ant-menu-sub {
  background: rgba(5, 8, 20, 0.35) !important;
  border-radius: 8px;
  margin: 2px 0 6px !important;
}

.reviewer-menu .ant-menu-sub .ant-menu-item {
  padding-left: 42px !important;
}

.child-link {
  gap: 10px;
}

.child-dot {
  width: 6px;
  height: 6px;
  flex: 0 0 6px;
  border-radius: 999px;
  background: rgba(148, 163, 184, 0.6);
}

.reviewer-menu .ant-menu-item-selected .child-dot {
  background: #3b82f6;
}

.reviewer-menu .ant-menu-submenu-arrow {
  color: currentColor !important;
}

.collapse-action {
  position: absolute;
  right: 12px;
  bottom: 14px;
  left: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  min-height: 42px;
  border: 1px solid var(--sider-line);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.07);
  color: var(--sider-text);
  font-size: 13px;
  font-weight: 800;
  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease;
}

.collapse-action:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
}

.workspace {
  min-width: 0;
  background: #f4f6f9 !important;
}

.ant-layout-header.topbar,
.topbar {
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  flex-shrink: 0 !important;
  width: 100% !important;
  height: 65px !important;
  line-height: 1 !important;
  margin: 0 !important;
  padding: 0 12px !important;
  box-sizing: border-box !important;
  background: #ffffff !important;
  border-bottom: 1px solid rgba(226, 232, 240, 0.95) !important;
  box-shadow: none !important;
  position: sticky !important;
  top: 0 !important;
  z-index: 50 !important;
}

.topbar-left,
.topbar-right {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.topbar-left {
  flex: 1 1 auto;
}

.topbar-right {
  flex: 0 0 auto;
  margin-left: auto;
}

.title-stack {
  min-width: 0;
}

.title-stack .eyebrow {
  display: block;
  margin-bottom: 2px;
  color: var(--reviewer-muted);
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

.title-stack h1 {
  margin: 0;
  color: var(--reviewer-ink);
  font-size: 20px;
  font-weight: 800;
  letter-spacing: 0;
  line-height: 1.1;
}

.icon-button,
.user-menu {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--reviewer-line);
  border-radius: 8px;
  background: #fff;
  color: #334155;
  cursor: pointer;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
}

.icon-button {
  position: relative;
  width: 42px;
  height: 42px;
  font-size: 18px;
}

.icon-button:hover,
.user-menu:hover {
  border-color: rgba(59, 130, 246, 0.35);
  color: #3b82f6;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
}

.bell-button.active {
  color: #3b82f6;
  border-color: rgba(59, 130, 246, 0.32);
  background: #eff6ff;
}

.bell-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  display: grid;
  place-items: center;
  min-width: 20px;
  height: 20px;
  padding: 0 5px;
  border: 2px solid #fff;
  border-radius: 999px;
  background: #dc2626;
  color: #fff;
  font-size: 10px;
  font-weight: 800;
}

.user-menu {
  gap: 9px;
  height: 42px;
  padding: 0 10px 0 5px;
  font-weight: 800;
}

.user-menu span:not(.ant-avatar-string) {
  max-width: 120px;
  overflow: hidden;
  color: var(--reviewer-ink);
  font-size: 13px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.account-menu .ant-menu-item {
  color: #dc2626 !important;
  font-weight: 700;
}

.ant-layout-content.main-content,
.main-content {
  height: calc(100vh - 72px) !important;
  overflow-y: auto !important;
  background: #f4f6f9 !important;
}

.content-inner {
  width: 100%;
  max-width: none;
  margin: 0;
  padding: 18px 14px 28px;
}

.notif-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
  padding: .75rem 1rem;
  border-radius: 10px;
  background: linear-gradient(135deg, #eff6ff, #dbeafe);
  border: 1px solid #93c5fd;
}

.notif-banner-text {
  display: flex;
  align-items: center;
  gap: .5rem;
  color: #1e40af;
  font-size: .8125rem;
  font-weight: 600;
}

.notif-banner-actions {
  display: flex;
  gap: .5rem;
  flex-shrink: 0;
}

.notif-banner-btn {
  border: none;
  border-radius: 6px;
  padding: .375rem .875rem;
  font-size: .75rem;
  font-weight: 800;
  cursor: pointer;
  transition: all .2s;
}

.notif-banner-btn.primary {
  background: #3b82f6;
  color: #fff;
}

.notif-banner-btn.primary:hover {
  background: #2563eb;
}

.notif-banner-btn.ghost {
  background: transparent;
  color: #64748b;
}

.notif-banner-btn.ghost:hover {
  background: rgba(0, 0, 0, .05);
}

.notif-popover .ant-popover-inner {
  padding: 0 !important;
  overflow: hidden;
  border-radius: 8px !important;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.18) !important;
}

.notif-dropdown {
  width: 370px;
  padding: 16px;
}

.notif-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--reviewer-line);
}

.notif-head > div {
  display: flex;
  flex-direction: column;
}

.notif-kicker {
  color: var(--reviewer-muted);
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.notif-head strong {
  color: var(--reviewer-ink);
  font-size: 16px;
  font-weight: 800;
}

.notif-count {
  display: grid;
  place-items: center;
  min-width: 28px;
  height: 28px;
  padding: 0 8px;
  border-radius: 8px;
  background: #eff6ff;
  color: #3b82f6;
  font-weight: 800;
}

.mark-read {
  width: 100%;
  margin: 12px 0 8px;
  padding: 9px 10px;
  border: 1px solid #93c5fd;
  border-radius: 8px;
  background: #eff6ff;
  color: #3b82f6;
  font-size: 12px;
  font-weight: 800;
  cursor: pointer;
}

.notif-list {
  display: grid;
  gap: 8px;
  max-height: 330px;
  overflow-y: auto;
  padding-right: 2px;
}

.notif-item {
  display: flex;
  gap: 10px;
  width: 100%;
  padding: 11px;
  border: 1px solid transparent;
  border-radius: 8px;
  background: #fff;
  text-align: left;
  cursor: pointer;
}

.notif-item:hover,
.notif-item.unread {
  border-color: #bfdbfe;
  background: #f8fbff;
}

.notif-icon {
  display: grid;
  place-items: center;
  width: 32px;
  height: 32px;
  flex: 0 0 32px;
  border-radius: 8px;
  background: #eff6ff;
  color: var(--reviewer-blue);
}

.notif-body {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.notif-body strong {
  color: #1f2937;
  font-size: 13px;
  font-weight: 700;
  line-height: 1.35;
}

.notif-body small {
  margin-top: 4px;
  color: var(--reviewer-muted);
  font-size: 11px;
  font-weight: 600;
}

.notif-empty {
  display: grid;
  place-items: center;
  gap: 8px;
  padding: 30px 10px;
  color: var(--reviewer-muted);
  font-size: 13px;
  font-weight: 700;
}

.notif-empty .anticon {
  font-size: 25px;
  color: #cbd5e1;
}

.notif-link {
  display: flex;
  justify-content: center;
  margin-top: 12px;
  padding: 10px;
  border-radius: 8px;
  background: #111827;
  color: #fff;
  font-size: 13px;
  font-weight: 800;
  text-decoration: none;
}

.notif-link:hover {
  color: #fff;
  background: #3b82f6;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(3px);
}

@media (max-width: 900px) {
  .ant-layout-header.topbar {
    padding: 0 10px !important;
  }

  .title-stack h1 {
    font-size: 18px;
  }

  .user-menu span:not(.ant-avatar-string) {
    max-width: 90px;
  }

  .content-inner {
    padding: 14px 10px 24px;
  }
}

@media (max-width: 640px) {
  .ant-layout-header.topbar {
    height: auto !important;
    min-height: 64px;
    flex-wrap: wrap;
    gap: 8px;
    padding: 10px 8px !important;
  }

  .topbar-left,
  .topbar-right {
    width: 100%;
  }

  .topbar-right {
    justify-content: flex-start;
    overflow-x: auto;
    padding-bottom: 2px;
  }

  .main-content {
    height: calc(100vh - 124px);
  }

  .content-inner {
    padding: 12px 8px 20px;
  }

  .title-stack h1 {
    font-size: 16px;
  }

  .notif-dropdown {
    width: min(340px, calc(100vw - 32px));
  }
}

@media (max-width: 480px) {
  .ant-layout-header.topbar {
    gap: 6px;
    padding: 8px 6px !important;
  }

  .icon-button {
    width: 38px;
    height: 38px;
    font-size: 16px;
  }

  .user-menu {
    height: 38px;
  }

  .title-stack .eyebrow {
    font-size: 9px;
  }

  .title-stack h1 {
    font-size: 15px;
  }

  .main-content {
    height: calc(100vh - 116px);
  }
}
</style>
