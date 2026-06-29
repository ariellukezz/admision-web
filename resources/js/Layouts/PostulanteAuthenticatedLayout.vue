<template>
  <div class="app-layout">
    <!-- ═══ SIDEBAR (desktop) ═══ -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <img :src="logoUrl" alt="UNA" class="sidebar-logo"/>
        <div class="sidebar-brand">
          <span class="sidebar-title">Admisión UNA</span>
          <span class="sidebar-subtitle">Plataforma de Postulante</span>
        </div>
      </div>

      <!-- Process selector -->
      <div class="sidebar-proceso" v-if="procesoActual">
        <button class="proceso-selector" @click="mostrarModalProceso = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="proceso-icon"><path d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
          <div class="proceso-text">
            <span class="proceso-nombre">{{ procesoActual.nombre }}</span>
            <span class="proceso-cambiar">Cambiar proceso</span>
          </div>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="proceso-chevron"><path d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        </button>
      </div>
      <div class="sidebar-proceso" v-else>
        <button class="proceso-selector proceso-selector--empty" @click="mostrarModalProceso = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="proceso-icon"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
          <span class="proceso-empty-text">Seleccionar proceso</span>
        </button>
      </div>

      <nav class="sidebar-nav">
        <Link v-for="item in navItems" :key="item.route" :href="route(item.route)" class="sidebar-link" :class="{ active: isCurrent(item.route) }">
          <div class="sidebar-icon" v-html="item.icon"></div>
          <span class="sidebar-label">{{ item.label }}</span>
        </Link>
      </nav>

      <div class="sidebar-footer">
        <!-- Notification Bell (desktop) -->
        <button class="btn-bell-desktop" @click="toggleNotifPanel" :class="{ active: noLeidas > 0 }" title="Notificaciones">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75c0 2.69-1.18 5.1-3.31 6.322a23.85 23.85 0 005.454 1.31m6.703 0a24.255 24.255 0 01-6.703 0m6.703 0a3 3 0 11-6.703 0"/></svg>
          <span v-if="noLeidas > 0" class="bell-badge">{{ noLeidas > 9 ? '9+' : noLeidas }}</span>
        </button>
        <div class="sidebar-user">
          <div class="user-avatar">{{ initials }}</div>
          <div class="user-text">
            <span class="user-name">{{ $page.props.auth.user.name }}</span>
            <span class="user-role">Postulante</span>
          </div>
        </div>
        <button class="btn-logout" @click="logout" title="Cerrar sesión">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
        </button>
      </div>
    </aside>

    <!-- ═══ MAIN AREA ═══ -->
    <div class="main-area">
      <!-- Top bar (mobile header) -->
      <header class="top-bar">
        <div class="top-left">
          <img :src="logoUrl" alt="UNA" class="top-logo"/>
          <div class="top-brand">
            <span class="top-title">Admisión UNA</span>
            <span class="top-subtitle" v-if="procesoActual">{{ procesoActual.nombre }}</span>
            <span class="top-subtitle" v-else>Plataforma de Postulante</span>
          </div>
        </div>
        <div class="top-right">
          <!-- Notification Bell -->
          <button class="btn-bell" @click="toggleNotifPanel" :class="{ active: noLeidas > 0 }" title="Notificaciones">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75c0 2.69-1.18 5.1-3.31 6.322a23.85 23.85 0 005.454 1.31m6.703 0a24.255 24.255 0 01-6.703 0m6.703 0a3 3 0 11-6.703 0"/></svg>
            <span v-if="noLeidas > 0" class="bell-badge">{{ noLeidas > 9 ? '9+' : noLeidas }}</span>
          </button>
          <!-- FCM activation banner -->
          <div v-if="showFcmBanner" class="fcm-banner-mini">
            <span>Activa las notificaciones push</span>
            <button @click="activarFcm">Activar</button>
            <button @click="showFcmBanner = false">×</button>
          </div>
          <button v-if="procesoActual" class="btn-change-proceso-mobile" @click="mostrarModalProceso = true" title="Cambiar proceso">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
          </button>
          <div class="user-info-mobile">
            <div class="user-avatar">{{ initials }}</div>
          </div>
          <button class="btn-logout-mobile" @click="logout" title="Cerrar sesión">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
          </button>
        </div>
      </header>

      <!-- Content -->
      <main class="main-content" :class="{ 'no-bottom-nav': hideMobileNav }">
        <slot />
      </main>
    </div>

    <!-- ═══ MOBILE BOTTOM NAV ═══ -->
    <nav v-if="!hideMobileNav" class="bottom-nav">
      <Link v-for="item in navItems" :key="'m'+item.route" :href="route(item.route)" class="bnav-item" :class="{ active: isCurrent(item.route) }">
        <div class="bnav-icon" v-html="item.icon"></div>
        <span class="bnav-label">{{ item.shortLabel }}</span>
      </Link>
    </nav>

    <!-- ═══ NOTIFICATION DROPDOWN PANEL ═══ -->
    <div v-if="notifPanelOpen" class="notif-overlay" @click="closeNotifPanel"></div>
    <div v-if="notifPanelOpen" class="notif-panel">
      <div class="notif-panel-header">
        <span class="notif-panel-title">Notificaciones</span>
        <button v-if="noLeidas > 0" class="notif-mark-all" @click="marcarTodasLeidas">Marcar todas como leídas</button>
      </div>
      <div class="notif-panel-body">
        <div v-if="notificaciones.length === 0" class="notif-empty">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75c0 2.69-1.18 5.1-3.31 6.322a23.85 23.85 0 005.454 1.31m6.703 0a24.255 24.255 0 01-6.703 0m6.703 0a3 3 0 11-6.703 0"/></svg>
          <p>No tienes notificaciones</p>
        </div>
        <div v-else class="notif-list">
          <div
            v-for="n in notificaciones"
            :key="n.id"
            class="notif-item"
            :class="{ unread: !n.leida }"
            @click="marcarLeida(n)"
          >
            <div class="notif-item-icon" :class="notifIconClass(n.tipo)">
              <svg v-if="n.tipo === 'revision_completada'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <svg v-else-if="n.tipo === 'revision_iniciada'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75c0 2.69-1.18 5.1-3.31 6.322a23.85 23.85 0 005.454 1.31"/></svg>
            </div>
            <div class="notif-item-content">
              <span class="notif-item-msg">{{ n.mensaje }}</span>
              <div v-if="n.tipo === 'revision_completada'" class="notif-item-detail">
                <div v-if="n.documentos_verificados?.length" class="notif-docs-ok">
                  ✓ {{ n.documentos_verificados.length }} documento(s) aprobado(s)
                </div>
                <div v-if="n.documentos_pendientes?.length" class="notif-docs-pending">
                  ⚠ {{ n.documentos_pendientes.length }} documento(s) por corregir
                </div>
                <div v-if="n.fecha_cita" class="notif-cita">
                  <strong>Cita presencial:</strong> {{ n.fecha_cita }} · {{ n.hora_inicio }} - {{ n.hora_fin }}
                  <br>{{ n.lugar }}
                  <span v-if="n.instrucciones"><br>{{ n.instrucciones }}</span>
                </div>
              </div>
              <div v-else-if="n.tipo === 'revision_iniciada'" class="notif-item-detail">
                <div class="notif-docs-ok" v-if="n.revisor_nombre">Revisor: {{ n.revisor_nombre }}</div>
              </div>
              <span class="notif-item-time">{{ n.created_at_diff }}</span>
            </div>
            <span v-if="!n.leida" class="notif-unread-dot"></span>
          </div>
        </div>
      </div>
      <div class="notif-panel-footer">
        <Link href="/postulante/seguimiento" class="notif-view-all" @click="closeNotifPanel">Ver seguimiento de postulación</Link>
      </div>
    </div>

    <!-- ═══ PANTALLA SELECCIÓN DE PROCESO (GATEWAY) ═══ -->
    <div v-if="mostrarModalProceso" class="ps-gateway">
      <div class="ps-gateway-inner">
        <!-- Header -->
        <div class="ps-gw-header">
          <img :src="logoUrl" alt="UNA" class="ps-gw-logo"/>
          <div>
            <h1 class="ps-gw-title">¡Bienvenidos!</h1>
            <p class="ps-gw-subtitle">Selecciona el proceso de admisión para ingresar</p>
          </div>
        </div>

        <div v-if="loadingProcesos" class="ps-gw-loading"><a-spin size="large" /></div>

        <div v-else class="ps-gw-body">
          <!-- Procesos Disponibles -->
          <div v-if="procesosActivos.length > 0" class="ps-gw-section">
            <h3 class="ps-gw-section-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
              Procesos Disponibles
            </h3>
            <div class="ps-gw-grid">
              <button
                v-for="p in procesosActivos" :key="p.id"
                class="ps-gw-card ps-gw-card--new"
                @click="elegirProceso(p)"
                :disabled="guardandoProceso"
              >
                <div class="ps-gw-card-icon ps-gw-card-icon--new">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </div>
                <span class="ps-gw-card-nombre">{{ p.nombre }}</span>
                <span class="ps-gw-card-anio">{{ p.anio }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="ps-gw-card-go"><path d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
              </button>
            </div>
          </div>

          <!-- Mis Procesos -->
          <div v-if="procesosParticipados.length > 0" class="ps-gw-section">
            <h3 class="ps-gw-section-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Mis Procesos
            </h3>
            <div class="ps-gw-grid">
              <button
                v-for="p in procesosParticipados" :key="p.id"
                class="ps-gw-card"
                :class="{ 'ps-gw-card--current': procesoActual && procesoActual.id === p.id }"
                @click="elegirProceso(p)"
                :disabled="guardandoProceso"
              >
                <div class="ps-gw-card-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                </div>
                <span class="ps-gw-card-nombre">{{ p.nombre }}</span>
                <span class="ps-gw-card-anio">{{ p.anio }}</span>
                <span v-if="procesoActual && procesoActual.id === p.id" class="ps-gw-card-badge">Actual</span>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="ps-gw-card-go"><path d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
              </button>
            </div>
          </div>

          <!-- Vacío -->
          <div v-if="procesosParticipados.length === 0 && procesosActivos.length === 0" class="ps-gw-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="ps-gw-empty-icon"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <p>No hay procesos disponibles en este momento</p>
            <p class="ps-gw-empty-hint">Contacta con la oficina de admisión para más información</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import { useNotificaciones } from '@/composables/useFcm';

import logoUrl from '../../assets/imagenes/logotiny.png';

const props = defineProps({
  hideMobileNav: { type: Boolean, default: false },
});

const page = usePage();

const initials = computed(() => {
  const name = page.props.auth.user.name || '';
  const parts = name.split(' ');
  return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : name.substring(0, 2).toUpperCase();
});

const procesoActual = computed(() => page.props.proceso_actual);

const mostrarModalProceso = ref(false);
const loadingProcesos = ref(false);
const guardandoProceso = ref(false);
const procesosParticipados = ref([]);
const procesosActivos = ref([]);

const navItems = [
  {
    route: 'postulante.dashboard',
    label: 'Inicio',
    shortLabel: 'Inicio',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>'
  },
  {
    route: 'postulante.mis-datos',
    label: 'Mis Datos',
    shortLabel: 'Datos',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>'
  },
  {
    route: 'postulante.documentos',
    label: 'Documentos',
    shortLabel: 'Docs',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>'
  },
  {
    route: 'postulante.seguimiento',
    label: 'Seguimiento',
    shortLabel: 'Estado',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>'
  },
  {
    route: 'postulante.mis-acciones',
    label: 'Mis Acciones',
    shortLabel: 'Acciones',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
  },
  {
    route: 'postulante.mis-resultados',
    label: 'Resultados',
    shortLabel: 'Notas',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>'
  }
];

const isCurrent = (routeName) => {
  return route().current(routeName);
};

// ═══ NOTIFICATIONS ═══
const { activar: activarFcm } = useNotificaciones();
const notifPanelOpen = ref(false);
const notificaciones = ref([]);
const noLeidas = ref(0);
const showFcmBanner = ref(false);
const idsMostrados = new Set();
let notifPolling = null;
let fcmListener = null;

const toggleNotifPanel = () => {
  notifPanelOpen.value = !notifPanelOpen.value;
  if (notifPanelOpen.value && notificaciones.value.length === 0) {
    cargarNotificaciones(false);
  }
};

const closeNotifPanel = () => {
  notifPanelOpen.value = false;
};

const cargarNotificaciones = async (isPoll = false) => {
  try {
    const res = await axios.get('/postulante/notificaciones?limit=10');
    const nuevas = res.data.notificaciones || [];

    if (isPoll) {
      // Detectar nuevas notificaciones no vistas
      for (const n of nuevas) {
        if (!idsMostrados.has(n.id) && !n.leida) {
          idsMostrados.add(n.id);
          // Mostrar notificación nativa si hay permiso
          if (Notification.permission === 'granted') {
            try {
              const reg = await navigator.serviceWorker?.ready;
              if (reg) {
                reg.showNotification(n.mensaje, {
                  body: n.fecha_cita ? `Cita: ${n.fecha_cita} · ${n.lugar}` : '',
                  icon: '/favicon.ico',
                  requireInteraction: true,
                  tag: n.tipo,
                });
              } else {
                new Notification(n.mensaje, { icon: '/favicon.ico' });
              }
            } catch { /* fallback silencioso */ }
          }
        }
      }
    } else {
      nuevas.forEach(n => idsMostrados.add(n.id));
    }

    notificaciones.value = nuevas;
    noLeidas.value = res.data.no_leidas || 0;
  } catch {
    // silencioso
  }
};

const marcarLeida = async (n) => {
  if (n.leida) return;
  try {
    await axios.post(`/postulante/notificaciones/${n.id}/leer`);
    n.leida = true;
    noLeidas.value = Math.max(0, noLeidas.value - 1);
  } catch { /* */ }
};

const marcarTodasLeidas = async () => {
  try {
    await axios.post('/postulante/notificaciones/leer-todas');
    notificaciones.value.forEach(n => n.leida = true);
    noLeidas.value = 0;
  } catch { /* */ }
};

const notifIconClass = (tipo) => ({
  'notif-icon-ok': tipo === 'revision_completada',
  'notif-icon-info': tipo === 'solicitud_revision',
  'notif-icon-started': tipo === 'revision_iniciada',
});

const logout = async () => {
  try {
    const fcm = useNotificaciones();
    await fcm.logout();
  } catch (e) {
    console.warn('FCM logout failed:', e);
  }
  sessionStorage.removeItem('fcm_user_id');
  router.post(route('logout'));
};

const cargarProcesos = async () => {
  loadingProcesos.value = true;
  try {
    const res = await axios.get('/postulante/mis-procesos');
    procesosParticipados.value = res.data.procesos_participados || [];
    procesosActivos.value = res.data.procesos_activos || [];
  } finally {
    loadingProcesos.value = false;
  }
};

const elegirProceso = async (proceso) => {
  guardandoProceso.value = true;
  try {
    const res = await axios.post('/postulante/seleccionar-proceso', {
      id_proceso: proceso.id,
    });
    if (res.data.estado) {
      router.visit('/postulante/dashboard');
    }
  } finally {
    guardandoProceso.value = false;
  }
};

onMounted(() => {
  const params = new URLSearchParams(window.location.search);
  if (params.get('seleccionar_proceso') === '1') {
    cargarProcesos();
    mostrarModalProceso.value = true;
  }

  // Inicializar notificaciones (solo al montar, sin polling)
  cargarNotificaciones(false);

  // Escuchar notificaciones push de Firebase para recargar solo cuando llega una
  const fcm = useNotificaciones();
  fcmListener = () => cargarNotificaciones(true);
  fcm.fcmEventTarget.addEventListener('fcm-message', fcmListener);

  // FCM banner si no tiene permiso
  if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
    showFcmBanner.value = true;
  }
});

onUnmounted(() => {
  if (notifPolling) clearInterval(notifPolling);
  if (fcmListener) {
    const fcm = useNotificaciones();
    fcm.fcmEventTarget.removeEventListener('fcm-message', fcmListener);
  }
});

// Watch for modal open to load data
const openModalWith = () => {
  cargarProcesos();
};

// Expose for parent to trigger
const handleOpenModal = () => {
  cargarProcesos();
  mostrarModalProceso.value = true;
};

// Watch modal open
watch(mostrarModalProceso, (val) => {
  if (val && procesosParticipados.value.length === 0 && procesosActivos.value.length === 0) {
    cargarProcesos();
  }
});
</script>

<style scoped>
.app-layout {
  min-height: 100vh;
  background: #F7F9FC;
  display: flex;
  flex-direction: column;
}

/* ═══════════════════════════
   TOP BAR (mobile)
   ═══════════════════════════ */
.top-bar {
  background: linear-gradient(180deg, #2D4E75 0%, #4472B3 100%);
  padding: .75rem 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: #fff;
  position: sticky;
  top: 0;
  z-index: 20;
}

.top-left {
  display: flex;
  align-items: center;
  gap: .625rem;
}

.top-logo {
  width: 32px;
  height: auto;
}

.top-brand { display: flex; flex-direction: column; }
.top-title { font-size: .9375rem; font-weight: 700; line-height: 1.2; }
.top-subtitle { font-size: .625rem; color: rgba(255,255,255,.65); letter-spacing: .5px; text-transform: uppercase; }

.top-right {
  display: flex;
  align-items: center;
  gap: .5rem;
}

.user-info-mobile {
  display: flex;
  align-items: center;
}

.btn-change-proceso-mobile {
  background: rgba(255,255,255,.15);
  border: none;
  border-radius: 8px;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255,255,255,.8);
  cursor: pointer;
  transition: all .2s;
}
.btn-change-proceso-mobile svg { width: 18px; height: 18px; }
.btn-change-proceso-mobile:hover { background: rgba(255,255,255,.25); color: #fff; }

.btn-logout-mobile {
  background: rgba(255,255,255,.1);
  border: none;
  border-radius: 8px;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255,255,255,.7);
  cursor: pointer;
  transition: all .2s;
}
.btn-logout-mobile svg { width: 18px; height: 18px; }
.btn-logout-mobile:hover { background: rgba(255,255,255,.2); color: #fff; }

/* ═══════════════════════════
   MAIN CONTENT (mobile default)
   ═══════════════════════════ */
.main-content {
  flex: 1;
  padding: 1rem;
  padding-bottom: 5rem;
}

.main-content.no-bottom-nav {
  padding: 0;
  overflow: hidden;
}

/* ═══════════════════════════
   MOBILE BOTTOM NAV
   ═══════════════════════════ */
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: #fff;
  border-top: 1px solid #E2E8F0;
  display: flex;
  justify-content: space-around;
  padding: .375rem 0 .5rem;
  z-index: 20;
}

.bnav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .125rem;
  padding: .375rem .5rem;
  color: #718096;
  text-decoration: none;
  font-size: .625rem;
  font-weight: 500;
  transition: color .2s;
  min-width: 56px;
}

.bnav-item.active { color: #3B6AA0; }
.bnav-item.active .bnav-icon { background: rgba(59,106,160,.1); border-radius: 50%; }

.bnav-icon {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2px;
  transition: all .2s;
}
.bnav-icon :deep(svg) { width: 20px; height: 20px; }
.bnav-label { line-height: 1; }

/* ═══════════════════════════
   SIDEBAR (hidden on mobile)
   ═══════════════════════════ */
.sidebar {
  display: none;
}

/* ═══════════════════════════
   USER AVATAR (shared)
   ═══════════════════════════ */
.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #F6AD55;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .8125rem;
  font-weight: 700;
  flex-shrink: 0;
}

/* ═══════════════════════════
   PROCESS SELECTOR
   ═══════════════════════════ */
.sidebar-proceso {
  padding: .5rem .75rem;
  border-bottom: 1px solid rgba(255,255,255,.1);
}

.proceso-selector {
  display: flex;
  align-items: center;
  gap: .625rem;
  width: 100%;
  padding: .625rem .75rem;
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 10px;
  color: #fff;
  cursor: pointer;
  transition: all .2s;
  text-align: left;
}

.proceso-selector:hover {
  background: rgba(255,255,255,.14);
  border-color: rgba(255,255,255,.2);
}

.proceso-selector--empty {
  background: rgba(245,158,11,.15);
  border-color: rgba(245,158,11,.3);
}

.proceso-icon { width: 18px; height: 18px; flex-shrink: 0; }

.proceso-text { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.proceso-nombre { font-size: .75rem; font-weight: 600; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.proceso-cambiar { font-size: .5625rem; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .3px; }

.proceso-chevron { width: 14px; height: 14px; color: rgba(255,255,255,.4); flex-shrink: 0; }

.proceso-empty-text { font-size: .75rem; font-weight: 500; color: rgba(255,255,255,.8); }

/* ═══════════════════════════
   GATEWAY - SELECCIÓN DE PROCESO (TEMA CLARO)
   ═══════════════════════════ */
.ps-gateway {
  position: fixed;
  inset: 0;
  z-index: 200;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  overflow-y: auto;
}

.ps-gateway-inner {
  width: 100%;
  max-width: 580px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0,0,0,.1);
  padding: 2rem 1.75rem;
}

.ps-gw-header {
  text-align: center;
  margin-bottom: 1.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.ps-gw-logo {
  width: 44px;
  height: auto;
  margin-bottom: .875rem;
}

.ps-gw-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1e293b;
  letter-spacing: -0.5px;
  line-height: 1;
  margin: 0;
}

.ps-gw-subtitle {
  font-size: .8125rem;
  color: #94a3b8;
  margin-top: .5rem;
  font-weight: 400;
}

.ps-gw-loading {
  padding: 2rem;
  display: flex;
  justify-content: center;
}

.ps-gw-body {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.ps-gw-section {}

.ps-gw-section-title {
  font-size: .625rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: .8px;
  margin: 0 0 .5rem;
  display: flex;
  align-items: center;
  gap: .375rem;
}

.ps-gw-section-title svg {
  width: 12px;
  height: 12px;
}

.ps-gw-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .5rem;
}

.ps-gw-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .375rem;
  padding: .875rem .75rem;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all .15s;
  text-align: center;
  position: relative;
}

.ps-gw-card:hover {
  background: #eff6ff;
  border-color: #3b6aa0;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(59,106,160,.15);
}

.ps-gw-card:disabled {
  opacity: .6;
  cursor: wait;
  transform: none !important;
  box-shadow: none !important;
}

.ps-gw-card--current {
  border-color: #16a34a;
  background: #f0fdf4;
}

.ps-gw-card--new {
  border-color: #fde68a;
  background: #fffbeb;
}

.ps-gw-card--new:hover {
  border-color: #d97706;
  background: #fef3c7;
}

.ps-gw-card-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: #eff6ff;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #3b6aa0;
}

.ps-gw-card-icon svg { width: 18px; height: 18px; }

.ps-gw-card-icon--new {
  background: #fef3c7;
  color: #d97706;
}

.ps-gw-card-nombre {
  font-size: .8125rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.2;
}

.ps-gw-card-anio {
  font-size: .625rem;
  color: #94a3b8;
}

.ps-gw-card-badge {
  font-size: .5rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .3px;
  background: #16a34a;
  color: #fff;
  padding: 1px 6px;
  border-radius: 4px;
}

.ps-gw-card-go {
  width: 14px;
  height: 14px;
  color: #cbd5e1;
  position: absolute;
  top: .5rem;
  right: .5rem;
}

.ps-gw-card:hover .ps-gw-card-go { color: #94a3b8; }

.ps-gw-empty {
  text-align: center;
  padding: 2rem 1rem;
  color: #94a3b8;
}

.ps-gw-empty-icon { width: 36px; height: 36px; margin: 0 auto .5rem; }
.ps-gw-empty p { font-size: .8125rem; margin: 0; color: #64748b; }
.ps-gw-empty-hint { font-size: .75rem; margin-top: .25rem; color: #94a3b8; }

/* ═══════════════════════════
   DESKTOP (≥768px)
   ═══════════════════════════ */
@media (min-width: 768px) {
  .app-layout {
    flex-direction: row;
  }

  /* --- Sidebar --- */
  .sidebar {
    display: flex;
    flex-direction: column;
    width: 250px;
    min-height: 100vh;
    background: linear-gradient(180deg, #1B3A5C 0%, #2D4E75 50%, #3B6AA0 100%);
    color: #fff;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 30;
    overflow-y: auto;
  }

  .sidebar-header {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: 1.25rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,.1);
  }

  .sidebar-logo {
    width: 40px;
    height: auto;
    flex-shrink: 0;
  }

  .sidebar-brand { display: flex; flex-direction: column; }
  .sidebar-title { font-size: 1rem; font-weight: 700; line-height: 1.2; }
  .sidebar-subtitle { font-size: .625rem; color: rgba(255,255,255,.55); letter-spacing: .5px; text-transform: uppercase; margin-top: 2px; }

  /* --- Sidebar Nav --- */
  .sidebar-nav {
    flex: 1;
    padding: .75rem .5rem;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .sidebar-link {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem 1rem;
    border-radius: 10px;
    font-size: .875rem;
    font-weight: 500;
    color: rgba(255,255,255,.7);
    text-decoration: none;
    transition: all .2s;
  }

  .sidebar-link:hover {
    background: rgba(255,255,255,.08);
    color: #fff;
  }

  .sidebar-link.active {
    background: rgba(255,255,255,.15);
    color: #fff;
    font-weight: 600;
    box-shadow: inset 3px 0 0 #F6AD55;
  }

  .sidebar-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .sidebar-icon :deep(svg) { width: 20px; height: 20px; }

  .sidebar-label { white-space: nowrap; }

  /* --- Sidebar Footer --- */
  .sidebar-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255,255,255,.1);
    display: flex;
    align-items: center;
    gap: .5rem;
  }

  .sidebar-user {
    display: flex;
    align-items: center;
    gap: .625rem;
    flex: 1;
    min-width: 0;
  }

  .sidebar-user .user-avatar {
    width: 38px;
    height: 38px;
    font-size: .8125rem;
  }

  .user-text { display: flex; flex-direction: column; min-width: 0; }
  .user-name { font-size: .8125rem; font-weight: 600; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .user-role { font-size: .625rem; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .5px; }

  .btn-logout {
    background: rgba(255,255,255,.08);
    border: none;
    border-radius: 8px;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,.6);
    cursor: pointer;
    transition: all .2s;
    flex-shrink: 0;
  }
  .btn-logout svg { width: 18px; height: 18px; }
  .btn-logout:hover { background: rgba(255,255,255,.15); color: #fff; }

  /* --- Main Area --- */
  .main-area {
    margin-left: 250px;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  .top-bar {
    display: none;
  }

  .main-content {
    padding: 1.5rem 2rem;
    padding-bottom: 2rem;
  }

  .main-content.no-bottom-nav {
    padding: 1.5rem 2rem;
    padding-bottom: 2rem;
    overflow: visible;
  }

  .bottom-nav {
    display: none;
  }

  /* Grid del gateway */
  .ps-gw-grid {
    grid-template-columns: repeat(3, 1fr);
  }

  .ps-gw-card-nombre {
    font-size: .875rem;
  }
}

/* ═══ NOTIFICATION BELL ═══ */
.btn-bell, .btn-bell-desktop {
  position: relative;
  background: rgba(255,255,255,.1);
  border: none;
  border-radius: 8px;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255,255,255,.8);
  cursor: pointer;
  transition: all .2s;
  flex-shrink: 0;
}
.btn-bell svg, .btn-bell-desktop svg { width: 18px; height: 18px; }
.btn-bell:hover, .btn-bell-desktop:hover { background: rgba(255,255,255,.2); color: #fff; }
.btn-bell.active, .btn-bell-desktop.active { background: rgba(245,158,11,.25); color: #fbbf24; }

.btn-bell-desktop {
  display: none;
}

.bell-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  min-width: 16px;
  height: 16px;
  border-radius: 100px;
  background: #ef4444;
  color: #fff;
  font-size: .5625rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
  line-height: 1;
  border: 1.5px solid #1B3A5C;
}

/* FCM mini banner */
.fcm-banner-mini {
  position: absolute;
  top: 52px;
  right: 1rem;
  background: #1e293b;
  color: #fff;
  border-radius: 10px;
  padding: .5rem .625rem;
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .6875rem;
  font-weight: 600;
  z-index: 50;
  box-shadow: 0 4px 20px rgba(0,0,0,.25);
  animation: slideDown .25s ease;
}
.fcm-banner-mini button:first-of-type {
  background: #3b82f6;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: .25rem .5rem;
  font-size: .625rem;
  font-weight: 700;
  cursor: pointer;
}
.fcm-banner-mini button:first-of-type:hover { background: #2563eb; }
.fcm-banner-mini button:last-of-type {
  background: none;
  border: none;
  color: rgba(255,255,255,.5);
  font-size: .875rem;
  cursor: pointer;
  padding: 0 .25rem;
}
@keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

/* Notif panel */
.notif-overlay {
  position: fixed;
  inset: 0;
  z-index: 99;
  background: transparent;
}

.notif-panel {
  position: fixed;
  top: 0;
  right: 0;
  width: 360px;
  max-width: 90vw;
  max-height: 100vh;
  background: #fff;
  border-radius: 0 0 0 16px;
  box-shadow: -8px 0 40px rgba(0,0,0,.12);
  z-index: 100;
  display: flex;
  flex-direction: column;
  animation: slideInRight .2s ease;
}
@keyframes slideInRight { from { transform: translateX(100%); } to { transform: translateX(0); } }

.notif-panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
  background: #f8fafc;
  border-radius: 0 0 0 16px;
}
.notif-panel-title { font-size: 1rem; font-weight: 800; color: #1e293b; }
.notif-mark-all {
  background: none;
  border: none;
  color: #3b82f6;
  font-size: .6875rem;
  font-weight: 600;
  cursor: pointer;
}
.notif-mark-all:hover { color: #2563eb; text-decoration: underline; }

.notif-panel-body { flex: 1; overflow-y: auto; padding: .5rem 0; }

.notif-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .5rem;
  padding: 3rem 1rem;
  color: #cbd5e1;
}
.notif-empty svg { width: 40px; height: 40px; }
.notif-empty p { font-size: .8125rem; color: #94a3b8; margin: 0; }

.notif-list { display: flex; flex-direction: column; }
.notif-item {
  display: flex;
  gap: .75rem;
  padding: .875rem 1.25rem;
  border-bottom: 1px solid #f8fafc;
  cursor: pointer;
  transition: background .15s;
  position: relative;
}
.notif-item:hover { background: #f8fafc; }
.notif-item.unread { background: #eff6ff; }
.notif-item.unread:hover { background: #dbeafe; }

.notif-item-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.notif-item-icon svg { width: 18px; height: 18px; }
.notif-icon-ok { background: #dcfce7; color: #16a34a; }
.notif-icon-info { background: #dbeafe; color: #2563eb; }
.notif-icon-started { background: #fef3c7; color: #d97706; }

.notif-item-content { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: .25rem; }
.notif-item-msg { font-size: .8125rem; font-weight: 600; color: #1e293b; line-height: 1.3; }
.notif-item-detail { font-size: .75rem; color: #475569; display: flex; flex-direction: column; gap: .25rem; }
.notif-docs-ok { color: #16a34a; font-weight: 600; }
.notif-docs-pending { color: #d97706; font-weight: 600; }
.notif-cita { color: #1e293b; line-height: 1.4; padding: .375rem .5rem; background: #f1f5f9; border-radius: 6px; font-size: .6875rem; }
.notif-item-time { font-size: .625rem; color: #94a3b8; }

.notif-unread-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #3b82f6;
  flex-shrink: 0;
  margin-top: 4px;
}

.notif-panel-footer {
  padding: .75rem 1.25rem;
  border-top: 1px solid #f1f5f9;
  text-align: center;
}
.notif-view-all {
  font-size: .75rem;
  font-weight: 700;
  color: #3b82f6;
  text-decoration: none;
}
.notif-view-all:hover { color: #2563eb; text-decoration: underline; }

/* Desktop bell placement */
@media (min-width: 768px) {
  .btn-bell { display: none; }
  .btn-bell-desktop { display: flex; }

  .notif-panel {
    top: auto;
    bottom: 0;
    right: 0;
    max-height: 70vh;
    border-radius: 16px 0 0 0;
    animation: slideInRightDesktop .2s ease;
  }
  @keyframes slideInRightDesktop { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

  .notif-panel-header { border-radius: 16px 0 0 0; }
}
</style>
