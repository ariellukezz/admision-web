<!--
  ============================================================================
  MESA DE REVISIÓN · Shell del Rol 2 (Revisor)
  ----------------------------------------------------------------------------
  Tres zonas con una responsabilidad cada una:
    · Panel lateral  → dónde puedo ir (navegación estable, agrupada por tarea)
    · Barra superior → dónde estoy y bajo qué contexto (proceso activo, alertas)
    · Lienzo         → en qué estoy trabajando
  El proceso activo vive arriba porque condiciona TODO lo que se ve debajo:
  es contexto global, no un ajuste de menú.
  ============================================================================
-->
<template>
  <div class="rev-shell rev-scope" :class="{ 'is-collapsed': collapsed, 'is-mobile-open': mobileOpen }">

    <!-- ══════════════════════════════ PANEL LATERAL ══════════════════════ -->
    <aside class="rev-nav" :aria-hidden="isMobile && !mobileOpen ? 'true' : 'false'">

      <div class="rev-nav-brand">
        <span class="rev-nav-mark"><img :src="logoSrc" alt="" /></span>
        <span v-if="!collapsed" class="rev-nav-wordmark">
          <small>UNA PUNO</small>
          <strong>Dirección de Admisión</strong>
        </span>
      </div>

      <nav class="rev-nav-scroll" aria-label="Navegación principal">
        <template v-for="group in navGroups" :key="group.label">
          <div v-if="group.items.length" class="rev-nav-group">
            <div v-if="!collapsed" class="rev-nav-group-label">{{ group.label }}</div>

            <template v-for="item in group.items" :key="item.key">
              <!-- Enlace simple -->
              <a-tooltip v-if="!item.children" :title="collapsed ? item.label : ''" placement="right">
                <Link
                  :href="item.route"
                  class="rev-nav-item"
                  :class="{ 'is-active': selectedKey === item.key }"
                  @click="mobileOpen = false"
                >
                  <RevIcon :name="item.icon" size="lg" class="rev-nav-icon" />
                  <span v-if="!collapsed" class="rev-nav-text">{{ item.label }}</span>
                  <span v-if="!collapsed && item.badge" class="rev-nav-badge rev-num">{{ item.badge }}</span>
                </Link>
              </a-tooltip>

              <!-- Grupo desplegable -->
              <div v-else class="rev-nav-sub">
                <a-tooltip :title="collapsed ? item.label : ''" placement="right">
                  <button
                    type="button"
                    class="rev-nav-item is-parent"
                    :class="{ 'is-open': openKey === item.key, 'is-active-branch': isBranchActive(item) }"
                    :aria-expanded="openKey === item.key ? 'true' : 'false'"
                    @click="toggleBranch(item.key)"
                  >
                    <RevIcon :name="item.icon" size="lg" class="rev-nav-icon" />
                    <span v-if="!collapsed" class="rev-nav-text">{{ item.label }}</span>
                    <RevIcon v-if="!collapsed" name="chevron-down" size="xs" class="rev-nav-caret" />
                  </button>
                </a-tooltip>

                <transition name="rev-expand">
                  <div v-if="openKey === item.key && !collapsed" class="rev-nav-children">
                    <Link
                      v-for="child in item.children"
                      :key="child.key"
                      :href="child.route"
                      class="rev-nav-child"
                      :class="{ 'is-active': selectedKey === child.key }"
                      @click="mobileOpen = false"
                    >
                      <span class="rev-nav-child-tick" />
                      <span class="rev-nav-text">{{ child.label }}</span>
                    </Link>
                  </div>
                </transition>
              </div>
            </template>
          </div>
        </template>
      </nav>

      <div class="rev-nav-foot">
        <a-dropdown :trigger="['click']" placement="topRight" overlay-class-name="rev-overlay">
          <button type="button" class="rev-nav-user" :class="{ 'is-compact': collapsed }">
            <RevAvatar :name="userFullName" :src="userAvatar" size="sm" tone="accent" />
            <span v-if="!collapsed" class="rev-nav-user-copy">
              <strong>{{ userFullName }}</strong>
              <small>DNI {{ userDni }}</small>
            </span>
            <RevIcon v-if="!collapsed" name="chevron-up" size="xs" class="rev-nav-user-caret" />
          </button>
          <template #overlay>
            <a-menu class="rev-account-menu">
              <a-menu-item key="role" disabled>
                <div class="rev-account-head">
                  <span class="rev-eyebrow">Sesión activa</span>
                  <strong>{{ userFullName }}</strong>
                  <small>Rol · Revisor</small>
                </div>
              </a-menu-item>
              <a-menu-divider />
              <a-menu-item key="logout" danger @click="handleLogout">
                <span class="rev-account-action"><RevIcon name="logout" size="sm" /> Cerrar sesión</span>
              </a-menu-item>
            </a-menu>
          </template>
        </a-dropdown>

        <button class="rev-nav-collapse" type="button" :aria-label="collapsed ? 'Expandir panel' : 'Contraer panel'" @click="collapsed = !collapsed">
          <RevIcon :name="collapsed ? 'chevron-right' : 'chevron-left'" size="sm" />
          <span v-if="!collapsed">Contraer</span>
        </button>
      </div>
    </aside>

    <!-- Velo para móvil -->
    <div v-if="mobileOpen" class="rev-nav-scrim" @click="mobileOpen = false" />

    <!-- ══════════════════════════════ ÁREA DE TRABAJO ════════════════════ -->
    <div class="rev-work">

      <header class="rev-topbar">
        <div class="rev-topbar-left">
          <button class="rev-icon-btn is-menu" type="button" aria-label="Abrir navegación" @click="mobileOpen = !mobileOpen">
            <RevIcon name="menu" size="lg" />
          </button>
          <h1 class="rev-topbar-title">{{ pageTitle }}</h1>
        </div>

        <div class="rev-topbar-right">
          <!-- Contexto global: proceso activo -->
          <div class="rev-context" :class="{ 'is-empty': !proceso }">
            <span class="rev-context-label">Proceso</span>
            <a-select
              v-model:value="proceso"
              show-search
              :loading="procesosLoading"
              placeholder="Seleccionar"
              option-filter-prop="label"
              :options="procesos"
              class="rev-context-select"
              :bordered="false"
              @change="cambiarProceso"
            />
          </div>

          <span class="rev-divider-v" aria-hidden="true" />

          <!-- Tema: junto al contexto global, no escondido en un menu -->
          <div class="rev-theme-switch" role="group" aria-label="Tema de la interfaz">
            <button
              type="button"
              class="rev-theme-btn"
              :class="{ 'is-on': !isDark }"
              :aria-pressed="!isDark"
              aria-label="Modo claro"
              @click="setTheme('light')"
            >
              <RevIcon name="sun" size="sm" />
            </button>
            <button
              type="button"
              class="rev-theme-btn"
              :class="{ 'is-on': isDark }"
              :aria-pressed="isDark"
              aria-label="Modo oscuro"
              @click="setTheme('dark')"
            >
              <RevIcon name="moon" size="sm" />
            </button>
          </div>

          <!-- Alertas -->
          <a-popover v-model:open="notifOpen" trigger="click" placement="bottomRight" overlay-class-name="rev-overlay rev-notif-overlay">
            <template #content>
              <div class="rev-notif">
                <div class="rev-notif-head">
                  <div>
                    <span class="rev-eyebrow">Centro de alertas</span>
                    <strong class="rev-title-sm">Notificaciones</strong>
                  </div>
                  <RevBadge v-if="noLeidas" tone="danger" solid size="sm">{{ noLeidas }} sin leer</RevBadge>
                </div>

                <div v-if="notificaciones.length" class="rev-notif-list">
                  <button
                    v-for="n in notificaciones"
                    :key="n.id"
                    type="button"
                    class="rev-notif-item"
                    :class="{ 'is-unread': !n.leida }"
                    @click="clickNotificacion(n)"
                  >
                    <span class="rev-notif-mark"><RevIcon name="file-alert" size="sm" /></span>
                    <span class="rev-notif-copy">
                      <span class="rev-notif-msg">{{ n.mensaje }}</span>
                      <small>{{ n.created_at_diff }}</small>
                    </span>
                    <span v-if="!n.leida" class="rev-notif-dot" aria-label="Sin leer" />
                  </button>
                </div>

                <RevEmptyState
                  v-else
                  compact
                  icon="bell"
                  title="Todo al día"
                  description="No hay solicitudes de revisión sin atender."
                />

                <div class="rev-notif-foot">
                  <button v-if="notificaciones.length && noLeidas" class="rev-link-btn" type="button" @click="marcarTodasLeidas">
                    Marcar todas como leídas
                  </button>
                  <Link href="/revisor/solicitudes-revision" class="rev-link-btn is-strong" @click="notifOpen = false">
                    Ver solicitudes <RevIcon name="arrow-right" size="xs" />
                  </Link>
                </div>
              </div>
            </template>

            <button class="rev-icon-btn" :class="{ 'has-alert': noLeidas > 0 }" type="button" :aria-label="`Notificaciones${noLeidas ? ', ' + noLeidas + ' sin leer' : ''}`">
              <RevIcon name="bell" size="lg" />
              <span v-if="noLeidas > 0" class="rev-icon-btn-dot rev-num">{{ noLeidas > 9 ? '9+' : noLeidas }}</span>
            </button>
          </a-popover>
        </div>
      </header>

      <main class="rev-canvas">
        <div class="rev-canvas-inner">
          <RevBanner
            v-if="showNotifBanner"
            tone="info"
            icon="bell"
            title="Activa las notificaciones de escritorio"
            description="Recibirás un aviso en el momento en que un postulante solicite la revisión de sus documentos."
            class="rev-canvas-banner"
          >
            <template #actions>
              <RevButton variant="primary" size="sm" @click="activarNotificaciones">Activar</RevButton>
              <RevButton variant="ghost" size="sm" @click="dismissNotifBanner">Ahora no</RevButton>
            </template>
          </RevBanner>

          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { message, notification } from 'ant-design-vue'
import { useNotificaciones } from '@/composables/useFcm.js'
import RevIcon from '@/Components/Revisor/RevIcon.vue'
import RevAvatar from '@/Components/Revisor/RevAvatar.vue'
import RevBadge from '@/Components/Revisor/RevBadge.vue'
import RevBanner from '@/Components/Revisor/RevBanner.vue'
import RevButton from '@/Components/Revisor/RevButton.vue'
import RevEmptyState from '@/Components/Revisor/RevEmptyState.vue'
import logoSrc from '../../assets/imagenes/logotiny.png'

const page = usePage()

const props = defineProps({
  pagina: { type: String, default: '' },
  title: { type: [String, Number], default: '' },
})

/* ───────────────────────────────── estado del shell ───────────────────── */
const collapsed = ref(false)
const isMobile = ref(false)
const mobileOpen = ref(false)
const selectedKey = ref('')
const openKey = ref('')
const proceso = ref(null)
const procesos = ref([])
const procesosLoading = ref(false)

const notifOpen = ref(false)
const notificaciones = ref([])
const noLeidas = ref(page.props.notificacionesNoLeidas || 0)
const idsMostrados = new Set()
let fcmListener = null
const showNotifBanner = ref(false)

/* ── Tema ───────────────────────────────────────────────────────────────
   app.blade.php fija data-theme antes de pintar para evitar el parpadeo;
   aqui solo leemos lo que ya hay y lo cambiamos cuando el revisor decide. */
const isDark = ref(false)

const readTheme = () => {
  isDark.value = document.documentElement.getAttribute('data-theme') === 'dark'
}

const setTheme = (mode) => {
  isDark.value = mode === 'dark'
  document.documentElement.setAttribute('data-theme', mode)
  try {
    localStorage.setItem('rev-theme', mode)
  } catch {
    /* Un navegador sin almacenamiento no debe romper el cambio de tema. */
  }
}

const applyViewport = () => {
  const w = window.innerWidth
  isMobile.value = w < 1024
  if (w < 1280 && w >= 1024) collapsed.value = true
  if (!isMobile.value) mobileOpen.value = false
}

/* ───────────────────────────────── identidad ──────────────────────────── */
const user = computed(() => page.props.auth?.user || {})
const userFullName = computed(() => [user.value.name, user.value.paterno].filter(Boolean).join(' ') || 'Usuario revisor')
const userDni = computed(() => user.value.dni || '—')
const userAvatar = computed(() => user.value.avatar || '')

/* ───────────────────────────────── navegación ─────────────────────────
   Agrupada por tarea, no por tabla de base de datos.                     */
const navGroups = computed(() => {
  const groups = [
    {
      label: 'Revisión',
      items: [
        { key: 'solicitudes_revision', icon: 'inbox', label: 'Solicitudes', route: '/revisor/solicitudes-revision', permission: 'revisor-solicitudes.read', badge: noLeidas.value || null },
        { key: 'certificados', icon: 'shield-check', label: 'Certificados', route: '/revisor/validacion', permission: 'revisor-validacion.read' },
      ],
    },
    {
      label: 'Operación',
      items: [
        {
          key: 'gestion_acceso', icon: 'files', label: 'Gestión de acceso',
          children: [
            { key: 'fotos', label: 'Fotos', route: '/revisor/foto-inscripcion', permission: 'revisor-inscripcion.read' },
            { key: 'revision', label: 'Ficha de inscripción', route: '/revisor/impresion', permission: 'revisor-inscripcion.read' },
            { key: 'fotos_huellas', label: 'Fotos y huellas', route: '/revisor/fotos-admision', permission: 'revisor-biometrico.read' },
          ],
        },
        {
          key: 'control_biometrico', icon: 'fingerprint', label: 'Control biométrico',
          children: [
            { key: 'fotos_bio', label: 'Captura biométrica', route: '/revisor/foto-biometrico', permission: 'revisor-biometrico.read' },
            { key: 'revision_bio', label: 'Revisión biométrica', route: '/revisor/imprimir', permission: 'revisor-biometrico.read' },
          ],
        },
      ],
    },
    {
      label: 'Análisis',
      items: [
        { key: 'dashboard', icon: 'dashboard', label: 'Panel general', route: '/revisor', permission: 'revisor.access' },
        { key: 'mi_actividad', icon: 'activity', label: 'Mi actividad', route: '/revisor/mi-actividad', permission: 'revisor-actividad.read' },
      ],
    },
  ]

  const perms = page.props.auth?.permissions || []
  const can = (p) => perms.includes(p)

  return groups.map((g) => ({
    ...g,
    items: g.items
      .map((item) => {
        if (!item.children) return can(item.permission) ? item : null
        const visible = item.children.filter((c) => can(c.permission))
        return visible.length ? { ...item, children: visible } : null
      })
      .filter(Boolean),
  }))
})

const flatItems = computed(() => navGroups.value.flatMap((g) => g.items.flatMap((i) => i.children || [i])))

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
  '/revisor/postulantes': 'solicitudes_revision',
}
const childToParentMap = {
  fotos: 'gestion_acceso', revision: 'gestion_acceso', fotos_huellas: 'gestion_acceso',
  fotos_bio: 'control_biometrico', revision_bio: 'control_biometrico',
}

const setMenuState = (url) => {
  const clean = (url || '').split('?')[0]
  const key = routeToKeyMap[clean] || (clean.startsWith('/revisor/postulante') ? 'solicitudes_revision' : '')
  selectedKey.value = key || ''
  if (key && childToParentMap[key]) openKey.value = childToParentMap[key]
}

const isBranchActive = (item) => (item.children || []).some((c) => c.key === selectedKey.value)
const toggleBranch = (key) => {
  if (collapsed.value) { collapsed.value = false; openKey.value = key; return }
  openKey.value = openKey.value === key ? '' : key
}

const activeLabel = computed(() => flatItems.value.find((i) => i.key === selectedKey.value)?.label)
const pageTitle = computed(() => props.pagina || props.title || activeLabel.value || 'Mesa de revisión')

/* ───────────────────────────────── notificaciones ─────────────────────── */
const checkNotifPermission = () => {
  if (!('Notification' in window)) return
  const dismissed = sessionStorage.getItem('notif_banner_dismissed')
  if (Notification.permission === 'default' && !dismissed) showNotifBanner.value = true
}

const activarNotificaciones = async () => {
  try {
    const fcm = useNotificaciones()
    await fcm.activar()
    showNotifBanner.value = false
    message.success('Notificaciones activadas')
  } catch {
    message.error('No se pudieron activar las notificaciones')
  }
}

const dismissNotifBanner = () => {
  showNotifBanner.value = false
  sessionStorage.setItem('notif_banner_dismissed', '1')
}

const mostrarNotificacionNativa = (n) => {
  if (!('Notification' in window) || Notification.permission !== 'granted') return false
  try {
    const notif = new Notification('Nueva solicitud de revisión', {
      body: n.mensaje || 'Tienes una nueva solicitud de revisión de documentos',
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
            message: 'Nueva solicitud de revisión',
            description: n.mensaje || 'Tienes una nueva solicitud de revisión de documentos',
            placement: 'bottomRight',
            duration: 6,
            onClick: () => { if (n.url) window.location.href = n.url },
          })
        }
      })
  } catch {
    /* El sondeo nunca debe interrumpir el trabajo del revisor. */
  }
}

const marcarTodasLeidas = async () => {
  try {
    await axios.post('/revisor/notificaciones/leer-todas')
    noLeidas.value = 0
    notificaciones.value.forEach((n) => { n.leida = true })
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
    } catch { /* la navegación no depende del marcado */ }
  }
  if (notif.url) {
    notifOpen.value = false
    window.location.href = notif.url
  }
}

/* ───────────────────────────────── proceso activo ─────────────────────── */
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
    message.error('Error al cambiar de proceso')
  }
}

const getProcesos = async () => {
  procesosLoading.value = true
  try {
    const res = await axios.get('/api/get-select-procesos')
    if (res.data.estado) {
      procesos.value = res.data.datos
      proceso.value = Number(user.value.id_proceso)
    }
  } catch (error) {
    console.error(error)
    message.error('Error al cargar los procesos')
  } finally {
    procesosLoading.value = false
  }
}

/* ───────────────────────────────── sesión ─────────────────────────────── */
const handleLogout = async () => {
  try {
    const fcm = useNotificaciones()
    await fcm.logout()
  } catch (e) {
    console.warn('FCM logout failed:', e)
  }
  sessionStorage.removeItem('fcm_user_id')
  router.post('/logout', {}, {
    onSuccess: () => { window.location.href = '/login' },
    onError: () => { message.error('Error al cerrar sesión') },
  })
}

/* ───────────────────────────────── ciclo de vida ──────────────────────── */
watch(() => page.url, setMenuState, { immediate: true })

onMounted(async () => {
  document.body.classList.add('rev-theme')
  readTheme()
  applyViewport()
  window.addEventListener('resize', applyViewport)

  await getProcesos()
  cargarNotificaciones(false)
  checkNotifPermission()

  const fcm = useNotificaciones()
  fcmListener = () => cargarNotificaciones(true)
  fcm.fcmEventTarget.addEventListener('fcm-message', fcmListener)
})

onUnmounted(() => {
  document.body.classList.remove('rev-theme')
  window.removeEventListener('resize', applyViewport)
  if (fcmListener) {
    const fcm = useNotificaciones()
    fcm.fcmEventTarget.removeEventListener('fcm-message', fcmListener)
  }
})
</script>

<style>
/* ══════════════════════════════════ ESTRUCTURA ═════════════════════════ */
.rev-shell {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: var(--rev-bg);
  font-family: var(--rev-font);
  color: var(--rev-ink);
}

/* ══════════════════════════════════ PANEL LATERAL ══════════════════════ */
.rev-nav {
  position: fixed; inset: 0 auto 0 0;
  z-index: 60;
  display: flex; flex-direction: column;
  width: var(--rev-nav-w);
  background: var(--rev-nav-bg);
  border-right: 1px solid rgba(255,255,255,.06);
  transition: width var(--rev-t-base) var(--rev-ease), transform var(--rev-t-base) var(--rev-ease);
}
.rev-shell.is-collapsed .rev-nav { width: var(--rev-nav-w-collapsed); }

/* Marca ------------------------------------------------------------------ */
.rev-nav-brand {
  display: flex; align-items: center; gap: 10px;
  height: var(--rev-topbar-h);
  flex: none;
  padding: 0 14px;
  border-bottom: 1px solid var(--rev-nav-line);
}
.rev-nav-mark {
  display: grid; place-items: center;
  width: 30px; height: 30px; flex: none;
  border-radius: var(--rev-r-md);
  background: rgba(255,255,255,.07);
  border: 1px solid var(--rev-nav-line-2);
}
.rev-nav-mark img { width: 19px; height: 19px; object-fit: contain; }
.rev-nav-wordmark { display: flex; flex-direction: column; min-width: 0; line-height: 1.15; }
.rev-nav-wordmark small {
  font-size: var(--rev-fs-2xs); font-weight: 650;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-nav-accent);
}
.rev-nav-wordmark strong {
  font-size: var(--rev-fs-md); font-weight: 620; color: #fff;
  letter-spacing: -.012em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* Lista ------------------------------------------------------------------ */
.rev-nav-scroll { flex: 1 1 auto; min-height: 0; overflow-y: auto; padding: var(--rev-s-6) var(--rev-s-5) var(--rev-s-7); }
.rev-nav-scroll::-webkit-scrollbar { width: 6px; }
.rev-nav-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 999px; }

.rev-nav-group + .rev-nav-group { margin-top: var(--rev-s-6); }
.rev-nav-group-label {
  padding: 0 8px var(--rev-s-3);
  font-size: var(--rev-fs-2xs); font-weight: 680;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-nav-muted);
}
.rev-shell.is-collapsed .rev-nav-group + .rev-nav-group { margin-top: var(--rev-s-5); position: relative; padding-top: var(--rev-s-5); }
.rev-shell.is-collapsed .rev-nav-group + .rev-nav-group::before {
  content: ""; position: absolute; top: 0; left: 8px; right: 8px; height: 1px; background: var(--rev-nav-line);
}

.rev-nav-item {
  position: relative;
  display: flex; align-items: center; gap: 10px;
  width: 100%;
  min-height: 36px; padding: 0 10px;
  margin-bottom: 2px;
  border: 0; background: transparent;
  border-radius: var(--rev-r-md);
  color: var(--rev-nav-text);
  font-family: var(--rev-font); font-size: var(--rev-fs-md); font-weight: 520;
  text-align: left; text-decoration: none; cursor: pointer;
  transition: background var(--rev-t-fast) var(--rev-ease), color var(--rev-t-fast) var(--rev-ease);
}
.rev-shell.is-collapsed .rev-nav-item { justify-content: center; padding: 0; }
.rev-nav-item:hover { background: rgba(255,255,255,.055); color: var(--rev-nav-text-hi); }
.rev-nav-item:focus-visible { outline: none; box-shadow: 0 0 0 2px var(--rev-nav-accent); }
.rev-nav-item.is-active {
  background: rgba(108,155,255,.13);
  color: #fff; font-weight: 600;
}
.rev-nav-item.is-active::before {
  content: ""; position: absolute; left: -12px; top: 9px; bottom: 9px;
  width: 2px; border-radius: 0 2px 2px 0; background: var(--rev-nav-accent);
}
.rev-shell.is-collapsed .rev-nav-item.is-active::before { left: -12px; }
.rev-nav-item.is-active-branch { color: var(--rev-nav-text-hi); }
.rev-nav-icon { flex: none; opacity: .82; }
.rev-nav-item.is-active .rev-nav-icon { opacity: 1; color: var(--rev-nav-accent); }
.rev-nav-text { flex: 1 1 auto; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.rev-nav-badge {
  flex: none; font-size: var(--rev-fs-2xs); font-weight: 700;
  background: var(--rev-danger); color: #fff;
  border-radius: var(--rev-r-pill); padding: 0 5px; line-height: 15px; min-width: 17px; text-align: center;
}
.rev-nav-caret { flex: none; opacity: .5; transition: transform var(--rev-t-base) var(--rev-ease); }
.rev-nav-item.is-open .rev-nav-caret { transform: rotate(180deg); opacity: .8; }

.rev-nav-children { padding: 2px 0 4px 18px; position: relative; }
.rev-nav-children::before {
  content: ""; position: absolute; left: 18px; top: 2px; bottom: 4px; width: 1px; background: var(--rev-nav-line-2);
}
.rev-nav-child {
  display: flex; align-items: center; gap: 9px;
  min-height: 30px; padding: 0 9px 0 12px;
  border-radius: var(--rev-r-md);
  color: var(--rev-nav-muted); font-size: var(--rev-fs-md); font-weight: 500;
  text-decoration: none;
  transition: background var(--rev-t-fast) var(--rev-ease), color var(--rev-t-fast) var(--rev-ease);
}
.rev-nav-child:hover { background: rgba(255,255,255,.05); color: var(--rev-nav-text-hi); }
.rev-nav-child.is-active { color: #fff; font-weight: 600; background: rgba(108,155,255,.1); }
.rev-nav-child-tick { width: 4px; height: 4px; border-radius: 50%; background: currentColor; opacity: .5; flex: none; }
.rev-nav-child.is-active .rev-nav-child-tick { opacity: 1; background: var(--rev-nav-accent); }

/* Pie --------------------------------------------------------------------- */
.rev-nav-foot { flex: none; padding: 10px; border-top: 1px solid var(--rev-nav-line); display: flex; flex-direction: column; gap: 6px; }
.rev-nav-user {
  display: flex; align-items: center; gap: 9px; width: 100%;
  padding: 7px 8px; border: 0; border-radius: var(--rev-r-md);
  background: rgba(255,255,255,.045); color: var(--rev-nav-text);
  cursor: pointer; text-align: left;
  transition: background var(--rev-t-fast) var(--rev-ease);
}
.rev-nav-user:hover { background: rgba(255,255,255,.09); }
.rev-nav-user.is-compact { justify-content: center; padding: 7px 0; }
.rev-nav-user-copy { display: flex; flex-direction: column; min-width: 0; line-height: 1.25; }
.rev-nav-user-copy strong { font-size: var(--rev-fs-sm); font-weight: 620; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rev-nav-user-copy small { font-size: var(--rev-fs-2xs); color: var(--rev-nav-muted); font-variant-numeric: tabular-nums; }
.rev-nav-user-caret { opacity: .5; flex: none; }

.rev-nav-collapse {
  display: flex; align-items: center; justify-content: center; gap: 7px;
  height: 29px; border: 0; border-radius: var(--rev-r-md);
  background: transparent; color: var(--rev-nav-muted);
  font-family: var(--rev-font); font-size: var(--rev-fs-sm); font-weight: 560;
  cursor: pointer; transition: background var(--rev-t-fast) var(--rev-ease), color var(--rev-t-fast) var(--rev-ease);
}
.rev-nav-collapse:hover { background: rgba(255,255,255,.06); color: var(--rev-nav-text-hi); }

.rev-nav-scrim { position: fixed; inset: 0; z-index: 55; background: rgba(12,19,34,.5); animation: rev-fade-in var(--rev-t-base) var(--rev-ease); }

/* ══════════════════════════════════ ÁREA DE TRABAJO ════════════════════ */
.rev-work {
  flex: 1 1 auto; min-width: 0;
  height: 100vh; overflow: hidden;
  display: flex; flex-direction: column;
  margin-left: var(--rev-nav-w);
  transition: margin-left var(--rev-t-base) var(--rev-ease);
}
.rev-shell.is-collapsed .rev-work { margin-left: var(--rev-nav-w-collapsed); }

/* Barra superior ---------------------------------------------------------- */
.rev-topbar {
  position: sticky; top: 0; z-index: var(--rev-z-topbar);
  display: flex; align-items: center; justify-content: space-between; gap: var(--rev-s-5);
  height: var(--rev-topbar-h); flex: none;
  padding: 0 var(--rev-gutter);
  background: var(--rev-surface);
  border-bottom: 1px solid var(--rev-line);
}
.rev-topbar-left { display: flex; align-items: center; gap: var(--rev-s-5); min-width: 0; }
.rev-topbar-title {
  margin: 0;
  font-family: var(--rev-font);
  font-size: var(--rev-fs-lg); font-weight: 500;
  letter-spacing: -.01em; color: var(--rev-ink);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.rev-topbar-right { display: flex; align-items: center; gap: var(--rev-s-5); flex: none; }

.rev-icon-btn {
  position: relative;
  display: grid; place-items: center;
  width: 32px; height: 32px;
  border: 1px solid transparent; border-radius: var(--rev-r-md);
  background: transparent; color: var(--rev-ink-3);
  cursor: pointer;
  transition: background var(--rev-t-fast) var(--rev-ease), color var(--rev-t-fast) var(--rev-ease), border-color var(--rev-t-fast) var(--rev-ease);
}
.rev-icon-btn:hover { background: var(--rev-n-100); color: var(--rev-ink); }
.rev-icon-btn:focus-visible { outline: none; box-shadow: var(--rev-ring); }
.rev-icon-btn.has-alert { color: var(--rev-primary-600); }
.rev-icon-btn.is-menu { display: none; }
.rev-icon-btn-dot {
  position: absolute; top: -2px; right: -2px;
  min-width: 15px; height: 15px; padding: 0 4px;
  display: grid; place-items: center;
  border-radius: var(--rev-r-pill);
  background: var(--rev-danger); color: #fff;
  font-size: 9px; font-weight: 700; line-height: 1;
  border: 1.5px solid #fff;
}

/* Interruptor de tema ----------------------------------------------------- */
.rev-theme-switch {
  display: flex; align-items: center; gap: 2px;
  height: 32px; padding: 3px;
  border: 1px solid var(--rev-line); border-radius: var(--rev-r-md);
  box-sizing: border-box; flex: none;
}
.rev-theme-btn {
  display: grid; place-items: center;
  width: 28px; height: 24px;
  border: 0; border-radius: var(--rev-r-xs);
  background: transparent; color: var(--rev-ink-3);
  cursor: pointer;
  transition: background var(--rev-t-fast) var(--rev-ease), color var(--rev-t-fast) var(--rev-ease);
}
.rev-theme-btn:hover { color: var(--rev-ink-2); }
.rev-theme-btn:focus-visible { outline: none; box-shadow: var(--rev-ring); }
.rev-theme-btn.is-on { background: var(--rev-surface-2); color: var(--rev-ink); }

/* Selector de contexto (proceso activo) ---------------------------------- */
.rev-context {
  display: flex; align-items: center; gap: 2px;
  height: 30px; padding: 0 3px 0 10px;
  border: 1px solid var(--rev-line-strong); border-radius: var(--rev-r-md);
  background: var(--rev-surface);
  transition: border-color var(--rev-t-fast) var(--rev-ease);
  max-width: 320px;
}
.rev-context:hover { border-color: var(--rev-n-300); }
.rev-context-label {
  font-size: var(--rev-fs-2xs); font-weight: 680;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-ink-4); flex: none;
  padding-right: 8px; border-right: 1px solid var(--rev-line);
}
.rev-context-select { min-width: 150px; max-width: 220px; }
.rev-context-select .ant-select-selector { height: 28px !important; padding: 0 6px !important; background: transparent !important; }
.rev-context-select .ant-select-selection-item {
  line-height: 28px !important;
  font-size: var(--rev-fs-md) !important; font-weight: 600 !important; color: var(--rev-ink) !important;
}
.rev-context.is-empty .rev-context-select .ant-select-selection-placeholder { line-height: 28px !important; }

/* Lienzo ------------------------------------------------------------------ */
.rev-canvas { flex: 1 1 auto; min-height: 0; overflow-y: auto; }
.rev-canvas-inner {
  padding: var(--rev-s-8) var(--rev-gutter) var(--rev-s-11);
  min-height: 100%;
  display: flex; flex-direction: column;
}
.rev-canvas-banner { margin-bottom: var(--rev-s-6); }

/* ══════════════════════════════════ NOTIFICACIONES ═════════════════════ */
.rev-notif-overlay .ant-popover-inner-content { padding: 0 !important; }
.rev-notif-overlay .ant-popover-inner { padding: 0 !important; overflow: hidden; }
.rev-notif { width: 366px; max-width: calc(100vw - 32px); }
.rev-notif-head {
  display: flex; align-items: center; justify-content: space-between; gap: var(--rev-s-5);
  padding: 12px var(--rev-s-6); border-bottom: 1px solid var(--rev-line);
}
.rev-notif-head > div { display: flex; flex-direction: column; gap: 1px; }
.rev-notif-list { max-height: 344px; overflow-y: auto; padding: 5px; }
.rev-notif-item {
  position: relative;
  display: flex; align-items: flex-start; gap: 10px; width: 100%;
  padding: 9px 10px; border: 0; border-radius: var(--rev-r-md);
  background: transparent; text-align: left; cursor: pointer;
  transition: background var(--rev-t-fast) var(--rev-ease);
}
.rev-notif-item:hover { background: var(--rev-n-50); }
.rev-notif-mark {
  flex: none; display: grid; place-items: center; width: 26px; height: 26px;
  border-radius: var(--rev-r-md); background: var(--rev-n-100); color: var(--rev-ink-3);
}
.rev-notif-item.is-unread .rev-notif-mark { background: var(--rev-primary-50); color: var(--rev-primary-600); }
.rev-notif-copy { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1 1 auto; }
.rev-notif-msg { font-size: var(--rev-fs-md); font-weight: 550; color: var(--rev-ink-2); line-height: 1.4; }
.rev-notif-item.is-unread .rev-notif-msg { font-weight: 620; color: var(--rev-ink); }
.rev-notif-copy small { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); }
.rev-notif-dot { flex: none; width: 6px; height: 6px; border-radius: 50%; background: var(--rev-primary-600); margin-top: 8px; }
.rev-notif-foot {
  display: flex; align-items: center; justify-content: space-between; gap: var(--rev-s-4);
  padding: 9px var(--rev-s-5); border-top: 1px solid var(--rev-line); background: var(--rev-surface-2);
}
.rev-link-btn {
  display: inline-flex; align-items: center; gap: 4px;
  border: 0; background: transparent; cursor: pointer;
  font-family: var(--rev-font); font-size: var(--rev-fs-sm); font-weight: 600;
  color: var(--rev-ink-3); text-decoration: none; padding: 3px 5px; border-radius: var(--rev-r-sm);
  transition: color var(--rev-t-fast) var(--rev-ease), background var(--rev-t-fast) var(--rev-ease);
}
.rev-link-btn:hover { color: var(--rev-ink); background: var(--rev-n-100); }
.rev-link-btn.is-strong { color: var(--rev-primary-700); }
.rev-link-btn.is-strong:hover { background: var(--rev-primary-50); }

/* Menú de cuenta ---------------------------------------------------------- */
.rev-account-menu { min-width: 216px; }
.rev-account-menu .ant-menu-item-disabled { cursor: default !important; padding: 8px 12px !important; height: auto !important; }
.rev-account-head { display: flex; flex-direction: column; gap: 1px; line-height: 1.35; }
.rev-account-head strong { font-size: var(--rev-fs-md); font-weight: 640; color: var(--rev-ink); }
.rev-account-head small { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); }
.rev-account-action { display: inline-flex; align-items: center; gap: 7px; font-weight: 580; }

/* ══════════════════════════════════ RESPONSIVO ═════════════════════════ */
@media (max-width: 1023px) {
  .rev-nav { transform: translateX(-100%); box-shadow: var(--rev-sh-xl); }
  .rev-shell.is-mobile-open .rev-nav { transform: none; width: var(--rev-nav-w); }
  .rev-work, .rev-shell.is-collapsed .rev-work { margin-left: 0; }
  .rev-icon-btn.is-menu { display: grid; }
  .rev-topbar { padding: 0 var(--rev-gutter); }
  .rev-shell { --rev-gutter: 20px; }
  .rev-canvas-inner { padding: var(--rev-s-6) var(--rev-gutter) var(--rev-s-9); }
  .rev-context-label { display: none; }
  .rev-context { padding-left: 4px; }
}
@media (max-width: 640px) {
  .rev-topbar-title { font-size: var(--rev-fs-lg); }
  .rev-context-select { min-width: 110px; }
  .rev-shell { --rev-gutter: 16px; }
  .rev-canvas-inner { padding: var(--rev-s-5) var(--rev-gutter) var(--rev-s-8); }
}
</style>
