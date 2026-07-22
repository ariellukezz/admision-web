<template>
  <a-layout class="min-h-screen" :class="'theme-' + themeMode">
    <!-- Sidebar -->
    <a-layout-sider
      v-model:collapsed="collapsed"
      :width="270"
      :collapsed-width="64"
      class="custom-sider"
    >
      <!-- Logo + Título -->
      <div class="sider-brand">
        <div class="brand-logo-wrapper">
          <img
            src="../../assets/imagenes/logotiny.png"
            class="sider-logo"
            alt="Logo"
          />
        </div>
        <transition name="fade">
          <div v-if="!collapsed" class="brand-text">
            <span class="brand-title">Sistema de Admisión</span>
            <span class="brand-subtitle">UNAP · Puno</span>
          </div>
        </transition>
      </div>

      <!-- User Card -->
      <div class="sider-scroll custom-scrollbar">
        <div class="user-card" v-if="!collapsed">
          <a-avatar
            :size="64"
            :src="page.props.auth.user.foto || undefined"
            class="sider-avatar"
          >
            <template #icon><UserOutlined /></template>
          </a-avatar>
          <div class="user-info">
            <div class="user-name">{{ page.props.auth.user.name }}</div>
            <a-select
              v-model:value="proceso"
              style="width: 100%;"
              class="proceso-select"
              @change="handleChange"
            >
              <a-select-option :value="item.value" v-for="item in procesos" :key="item.value">
                {{ item.label }}
              </a-select-option>
            </a-select>
          </div>
        </div>

        <!-- Avatar mini cuando está colapsado -->
        <div class="collapsed-avatar" v-else>
          <a-avatar
            :size="40"
            :src="page.props.auth.user.foto || undefined"
            class="sider-avatar"
          >
            <template #icon><UserOutlined /></template>
          </a-avatar>
        </div>

        <!-- Menu -->
        <a-menu
          v-model:selectedKeys="selectedKeys"
          v-model:openKeys="openKeys"
          :theme="(themeMode === 'light') ? 'light' : 'dark'"
          mode="inline"
          class="custom-menu"
        >
          <template v-for="item in menuItems" :key="item.key">
            <a-menu-item
              v-if="!item.children"
              :key="item.key"
              :class="{ 'menu-item-active': route().current(item.route) }"
            >
              <Link :href="route(item.route)">
                <span class="menu-icon-wrapper" :style="{ '--accent': item.color || '#3b82f6' }">
                  <component :is="item.icon" />
                </span>
                <span class="menu-text">{{ item.label }}</span>
              </Link>
            </a-menu-item>

            <a-sub-menu
              v-else
              :key="item.key"
              :class="['submenu', { 'submenu-open': openKeys.includes(item.key) }]"
            >
              <template #icon>
                <span class="menu-icon-wrapper" :style="{ '--accent': item.color || '#3b82f6' }">
                  <component :is="item.icon" />
                </span>
              </template>
              <template #title>
                <span class="submenu-title-text">{{ item.label }}</span>
              </template>

              <a-menu-item
                v-for="child in item.children"
                :key="child.key"
                :class="{ 'menu-item-active': route().current(child.route) }"
              >
                <Link :href="route(child.route)">
                  <span class="child-dot"></span>
                  <span class="child-text">{{ child.label }}</span>
                </Link>
              </a-menu-item>
            </a-sub-menu>
          </template>
        </a-menu>

        <!-- Footer version -->
        <div class="sider-footer" v-if="!collapsed">
          <span class="footer-version">v2.0 · 2026</span>
        </div>
      </div>
    </a-layout-sider>

    <!-- Contenido principal -->
    <a-layout>
      <a-layout-header class="main-header">
        <div class="header-left">
          <menu-fold-outlined
            v-if="!collapsed"
            class="collapse-trigger"
            @click="collapsed = !collapsed"
          />
          <menu-unfold-outlined
            v-else
            class="collapse-trigger"
            @click="collapsed = !collapsed"
          />
        </div>
        <div class="header-right">
          <a-tooltip :title="themeLabel">
            <a-button type="text" class="theme-toggle" @click="cycleTheme">
              <template #icon>
                <BulbOutlined v-if="themeMode === 'light'" />
                <BulbFilled v-else-if="themeMode === 'dark'" />
                <BorderOutlined v-else />
              </template>
            </a-button>
          </a-tooltip>
          <Header class="header-content" />
        </div>
      </a-layout-header>

      <a-layout-content class="main-content">
        <div class="content-container">
          <slot/>
        </div>
      </a-layout-content>
    </a-layout>
  </a-layout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3'
import Header from '@/Layouts/Header.vue';
import {
  AppstoreFilled,
  SettingFilled,
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  UserOutlined,
  MailOutlined,
  UserAddOutlined,
  ControlOutlined,
  SolutionOutlined,
  ToolOutlined,
  TeamOutlined,
  SafetyCertificateOutlined,
  RocketOutlined,
  BarChartOutlined,
  QuestionCircleOutlined,
  BulbOutlined,
  BulbFilled,
  BorderOutlined,
} from '@ant-design/icons-vue';
const page = usePage()

const collapsed = ref(false);
const selectedKeys = ref([]);
const openKeys = ref([]);
const proceso = ref(page.props.auth.user.id_proceso);

const themeMode = ref('light');
const themeLabels = { light: 'Modo claro', dark: 'Modo oscuro', hybrid: 'Modo híbrido' };
const themeOrder = ['light', 'dark', 'hybrid'];

const themeLabel = computed(() => {
  const next = themeOrder[(themeOrder.indexOf(themeMode.value) + 1) % 3];
  return `${themeLabels[themeMode.value]} → ${themeLabels[next]}`;
});

const cycleTheme = () => {
  const idx = themeOrder.indexOf(themeMode.value);
  themeMode.value = themeOrder[(idx + 1) % 3];
  localStorage.setItem('sider-theme', themeMode.value);
};

const savedTheme = localStorage.getItem('sider-theme');
if (savedTheme && themeOrder.includes(savedTheme)) {
  themeMode.value = savedTheme;
}

watch(themeMode, (val) => {
  document.body.className = document.body.className
    .replace(/theme-\w+/g, '')
    .trim();
  document.body.classList.add('theme-' + val);
}, { immediate: true });

const menuItems = [
  {
    key: 'dashboard',
    icon: AppstoreFilled,
    label: 'Dashboard',
    route: 'admin-dashboard',
    color: '#3b82f6',
  },
  {
    key: 'configuracion',
    icon: ControlOutlined,
    label: 'Configuración',
    color: '#8b5cf6',
    children: [
      { key: 'vacantes', icon: SettingFilled, label: 'Vacantes', route: 'admin-vacantes' },
      { key: 'tarifas', icon: SettingFilled, label: 'Tarifas', route: 'tarifa-index' },
      { key: 'observados', icon: SettingFilled, label: 'Observados', route: 'programa-index' },
      { key: 'requisitos', icon: SettingFilled, label: 'Requisitos', route: 'requisitos-index' },
      { key: 'tipos-documento', icon: SettingFilled, label: 'Tipos de Documento', route: 'tipos-documento-index' },
    ]
  },
  {
    key: 'gestionadmision',
    icon: SolutionOutlined,
    label: 'Gestión de admisión',
    color: '#06b6d4',
    children: [
      { key: 'preinscripcion', icon: SettingFilled, label: 'Preinscripciones', route: 'admin-preinscripciones' },
      { key: 'inscripcion', icon: SettingFilled, label: 'Inscripciones', route: 'admin-inscripciones' },
      { key: 'controlbiometrico', icon: SettingFilled, label: 'Ctrl Biométrico', route: 'admin-control-biometrico' },
      { key: 'fotoshuellas', icon: SettingFilled, label: 'Fotos y huellas', route: 'about' },
      { key: 'resultados', icon: SettingFilled, label: 'Puntajes', route: 'about' },
      { key: 'observados', icon: SettingFilled, label: 'Observados', route: 'admin-observados' },
      { key: 'observadosv2', icon: SettingFilled, label: 'Observados v2', route: 'adminv2.observados' },
      { key: 'estudiantes', icon: SettingFilled, label: 'Estudiantes', route: 'admin-observados' },
      { key: 'estudiantecepre', icon: SettingFilled, label: 'Est. Cepreuna', route: 'admin-observados' },
    ]
  },
  {
    key: 'mantenimiento',
    icon: ToolOutlined,
    label: 'Mantenimiento',
    color: '#f59e0b',
    children: [
      { key: 'filial', icon: SettingFilled, label: 'Sede', route: 'filial-index' },
      { key: 'procesos', icon: SettingFilled, label: 'Procesos', route: 'proceso-index' },
      { key: 'programas', icon: SettingFilled, label: 'Programas', route: 'programa-index' },
      { key: 'modalidades', icon: SettingFilled, label: 'Modalidades', route: 'modalidad-index' },
      { key: 'colegios', icon: SettingFilled, label: 'Colegios', route: 'admin-colegios' },
      { key: 'ubigeo', icon: SettingFilled, label: 'Ubigeos', route: 'programa-index' },
      { key: 'pagos', icon: SettingFilled, label: 'Pagos', route: 'programa-index' },
      { key: 'reglamentos', icon: SettingFilled, label: 'Reglamentos', route: 'admin-reglamento' },
      { key: 'anios', icon: SettingFilled, label: 'Años', route: 'anio-index' },
    ]
  },
  {
    key: 'participantes',
    icon: TeamOutlined,
    label: 'Gestión de participantes',
    color: '#ec4899',
    children: [
      { key: 'docentes', icon: SettingFilled, label: 'Docentes', route: 'admin-participante-docente' },
      { key: 'administrativos', icon: SettingFilled, label: 'Administrativos', route: 'admin-participante-administrativo' },
      { key: 'sorteo', icon: SettingFilled, label: 'Sorteo', route: 'admin-participante-sorteo' },
      { key: 'participantes', icon: SettingFilled, label: 'Participantes', route: 'modalidad-index' },
    ]
  },
  {
    key: 'usuarios',
    icon: SafetyCertificateOutlined,
    label: 'Roles y usuarios',
    color: '#10b981',
    children: [
      { key: 'roles', icon: SettingFilled, label: 'Roles', route: 'roles-index' },
      { key: 'usuarios', icon: SettingFilled, label: 'Usuarios', route: 'usuarios-index' },
      { key: 'permisos', icon: SettingFilled, label: 'Permisos', route: 'admin-permisos' },
      { key: 'modulos', icon: SettingFilled, label: 'Módulos RBAC', route: 'admin-modulos' },
    ]
  },
  {
    key: 'gestion',
    icon: RocketOutlined,
    label: 'Gestión técnica',
    color: '#f97316',
    children: [
      { key: 'apoderados', icon: SettingFilled, label: 'Apoderados', route: 'admin-apoderado-index' },
      { key: 'postulantes', icon: SettingFilled, label: 'Postulantes', route: 'admin-postulante-index' },
      { key: 'consultaReniec', icon: SettingFilled, label: 'Consulta Reniec', route: 'admin-consulta-reniec' },
      { key: 'documentos', icon: SettingFilled, label: 'Documentos', route: 'admin-documento-index' },
      { key: 'colegios', icon: SettingFilled, label: 'Colegios', route: 'admin-colegios' },
      { key: 'carrerasprevias', icon: SettingFilled, label: 'Estudios anteriores', route: 'admin-carreras-previas' },
      { key: 'pagosbn', icon: SettingFilled, label: 'Pagos BN', route: 'admin-pagos-banco' },
      { key: 'certificadosfirma', icon: SettingFilled, label: 'Certificados Firma', route: 'admin-certificados-firma' },
      { key: 'respaldobd', icon: SettingFilled, label: 'Respaldo BD', route: 'admin-respaldo-bd' },
      { key: 'trazabilidad', icon: SettingFilled, label: 'Trazabilidad', route: 'admin-trazabilidad' },
      { key: 'configuracion-citacion', icon: SettingFilled, label: 'Configuración de Citación', route: 'admin.configuracion-citacion' },
      { key: 'registro-postulante', icon: UserAddOutlined, label: 'Registro de Postulante', route: 'admin.registro-postulante' },
      { key: 'smtp-accounts', icon: MailOutlined, label: 'Correos SMTP', route: 'admin.smtp-accounts' },
      { key: 'estudiantes-oti', icon: SettingFilled, label: 'Estudiantes OTI', route: 'admin.estudiantes-oti' },
      { key: 'puntajes', icon: SettingFilled, label: 'Gestión de Puntajes', route: 'admin.puntajes' },
    ]
  },
  {
    key: 'reportes',
    icon: BarChartOutlined,
    label: 'Reportes',
    color: '#6366f1',
    children: [
      { key: 'resumen_general', icon: SettingFilled, label: 'Resumen general', route: 'admin-resumenes-general' },
      { key: 'resumen', icon: SettingFilled, label: 'Resumen inscripciones', route: 'admin-resumenes-inscripcion' },
      { key: 'res_programa_diario', icon: SettingFilled, label: 'Rep programa diario', route: 'admin-resumenes-programa-diario' },
      { key: 'res_usuarios_diario', icon: SettingFilled, label: 'Rep usuarios diario', route: 'admin-resumenes-usuario-diario' },
      { key: 'ratio', icon: SettingFilled, label: 'Ratio', route: 'admin-ratio' },
      { key: 'resumenbiometrico', icon: SettingFilled, label: 'Res. biométrico', route: 'admin-resumenes-biometrico' },
      { key: 'errores', icon: SettingFilled, label: 'Rep errores', route: 'programa-index' },
    ]
  },
  {
    key: 'ayuda',
    icon: QuestionCircleOutlined,
    label: 'Centro de ayuda',
    color: '#64748b',
    children: [
      { key: 'soportetecnico', icon: SettingFilled, label: 'Soporte técnico', route: 'roles-index' },
      { key: 'manualesguias', icon: SettingFilled, label: 'Manuales y guías', route: 'usuarios-index' },
    ]
  }
];

const findParentKey = (routeName) => {
  for (const item of menuItems) {
    if (item.children) {
      const found = item.children.find(child => route().current(child.route));
      if (found) return item.key;
    }
  }
  return null;
};

const procesos = ref([])
const getProcesos = async () => {
    let res = await axios.get(`/admin/get-select-procesos`);
    procesos.value = res.data.datos;
}
getProcesos();

watch(() => router.page.url, () => {
  const activeItem = menuItems
    .flatMap(item => item.children ? item.children : item)
    .find(item => route().current(item.route));

  selectedKeys.value = activeItem ? [activeItem.key] : [];

  const parentKey = findParentKey(router.page.url);
  openKeys.value = parentKey ? [parentKey] : [];
}, { immediate: true });

const cambiarProceso = async () => {
  let res = await axios.post("/admin/cambiar_proceso",{ "id_proceso": proceso.value});
  if(res.data.estado == true){
    location.reload();
  }
}

watch(proceso, (newVal, oldVal) => {
    cambiarProceso();
})
</script>

<style>
/* ====== THEME VARIABLES ====== */
.theme-light {
  --sider-bg: #ffffff;
  --sider-gradient: #ffffff;
  --primary-color: #3b82f6;
  --hover-bg: #eff6ff;
  --text-color: #475569;
  --text-active: #0f172a;
  --muted-text: #94a3b8;
  --border-color: #e2e8f0;
  --icon-bg: #f1f5f9;
  --icon-bg-hover: #e0f2fe;
  --icon-bg-active: #dbeafe;
  --submenu-bg: #f8fafc;
  --user-card-bg: linear-gradient(135deg, #eff6ff, #f0fdf4);
  --user-card-border: #dbeafe;
  --avatar-border: #bfdbfe;
  --content-bg: #f1f5f9;
  --header-bg: #ffffff;
  --header-border: #e2e8f0;
  --scrollbar-color: #cbd5e1;
  --card-bg: #ffffff;
  --card-text: #1e293b;
  --card-muted: #64748b;
  --card-border: #e2e8f0;
  --row-even: rgba(0,0,0,0.02);
  --table-header-bg: #f8fafc;
  --success-color: #16a34a;
}

.theme-dark {
  --sider-bg: #0a0f1c;
  --sider-gradient: linear-gradient(180deg, #0a0f1c 0%, #0c1322 50%, #0a0f1c 100%);
  --primary-color: #3b82f6;
  --hover-bg: #131c2f;
  --text-color: #94a3b8;
  --text-active: #f1f5f9;
  --muted-text: #64748b;
  --border-color: #1a2333;
  --icon-bg: rgba(255,255,255,0.04);
  --icon-bg-hover: rgba(255,255,255,0.08);
  --icon-bg-active: rgba(59,130,246,0.2);
  --submenu-bg: rgba(0,0,0,0.25);
  --user-card-bg: linear-gradient(135deg, rgba(59,130,246,0.08), rgba(139,92,246,0.05));
  --user-card-border: rgba(59,130,246,0.15);
  --avatar-border: rgba(59,130,246,0.35);
  --content-bg: #0f172a;
  --header-bg: #0d1526;
  --header-border: #1a2333;
  --scrollbar-color: #334155;
  --card-bg: #1e293b;
  --card-text: #e2e8f0;
  --card-muted: #94a3b8;
  --card-border: #334155;
  --row-even: rgba(255,255,255,0.03);
  --table-header-bg: #1e293b;
  --success-color: #4ade80;
}

/* Hybrid: dark sidebar + white header/content */
.theme-hybrid {
  --sider-bg: #0a0f1c;
  --sider-gradient: linear-gradient(180deg, #0a0f1c 0%, #0c1322 50%, #0a0f1c 100%);
  --primary-color: #3b82f6;
  --hover-bg: #131c2f;
  --text-color: #94a3b8;
  --text-active: #f1f5f9;
  --muted-text: #64748b;
  --border-color: #1a2333;
  --icon-bg: rgba(255,255,255,0.04);
  --icon-bg-hover: rgba(255,255,255,0.08);
  --icon-bg-active: rgba(59,130,246,0.2);
  --submenu-bg: rgba(0,0,0,0.25);
  --user-card-bg: linear-gradient(135deg, rgba(59,130,246,0.08), rgba(139,92,246,0.05));
  --user-card-border: rgba(59,130,246,0.15);
  --avatar-border: rgba(59,130,246,0.35);
  --content-bg: #f1f5f9;
  --header-bg: #ffffff;
  --header-border: #e2e8f0;
  --scrollbar-color: #cbd5e1;
  --card-bg: #ffffff;
  --card-text: #1e293b;
  --card-muted: #64748b;
  --card-border: #e2e8f0;
  --row-even: rgba(0,0,0,0.02);
  --table-header-bg: #f8fafc;
  --success-color: #16a34a;
}

.custom-scrollbar::-webkit-scrollbar { display: none; }
.custom-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* ====== SIDEBAR ====== */
.custom-sider {
  background: var(--sider-bg) !important;
  border-right: 1px solid var(--border-color);
  box-shadow: 2px 0 16px rgba(0, 0, 0, 0.06);
  transition: width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  z-index: 20;
}
.theme-dark .custom-sider,
.theme-hybrid .custom-sider {
  background: var(--sider-bg) !important;
  box-shadow: 4px 0 32px rgba(0, 0, 0, 0.35);
}
.custom-sider .ant-layout-sider-children {
  background: var(--sider-bg) !important;
}

/* BRAND */
.sider-brand {
  display: flex;
  align-items: center;
  padding: 18px 16px 14px 16px;
  border-bottom: 1px solid var(--border-color);
}
.brand-logo-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
}
.sider-logo {
  height: 34px;
}
.theme-dark .sider-logo,
.theme-hybrid .sider-logo {
  filter: drop-shadow(0 2px 6px rgba(59, 130, 246, 0.4));
}
.brand-text {
  margin-left: 12px;
  display: flex;
  flex-direction: column;
  white-space: nowrap;
}
.brand-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-active);
  letter-spacing: 0.2px;
}
.brand-subtitle {
  font-size: 0.7rem;
  color: var(--muted-text);
  letter-spacing: 0.5px;
}

/* USER CARD */
.sider-scroll {
  height: calc(100vh - 72px);
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}
.user-card {
  margin: 16px 14px;
  padding: 16px;
  background: var(--user-card-bg);
  border: 1px solid var(--user-card-border);
  border-radius: 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}
.sider-avatar {
  border: 3px solid var(--avatar-border);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}
.theme-dark .sider-avatar,
.theme-hybrid .sider-avatar {
  box-shadow: 0 0 0 4px rgba(10, 15, 28, 0.6), 0 4px 12px rgba(0,0,0,0.3);
}
.user-info {
  width: 100%;
  text-align: center;
}
.user-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-active);
  margin-bottom: 8px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.proceso-select .ant-select-selector {
  background: var(--icon-bg) !important;
  border: 1px solid var(--border-color) !important;
  border-radius: 8px !important;
}
.proceso-select .ant-select-selection-item {
  color: var(--text-color) !important;
  font-size: 0.75rem !important;
}

/* Collapsed avatar */
.collapsed-avatar {
  display: flex;
  justify-content: center;
  padding: 16px 0;
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 8px;
}

/* ====== MENU ====== */
.custom-menu {
  background: transparent !important;
  border-right: none !important;
  flex: 1;
  padding: 4px 0 16px 0;
}
.custom-menu.ant-menu {
  background: transparent !important;
}
.custom-menu .ant-menu-item,
.custom-menu .ant-menu-submenu-title {
  color: var(--text-color) !important;
  margin: 2px 10px;
  border-radius: 10px;
  height: 44px;
  line-height: 44px;
  transition: all 0.2s ease;
}
.custom-menu .ant-menu-item::after,
.custom-menu .ant-menu-submenu-title::after {
  display: none !important;
}

/* Icon wrapper */
.menu-icon-wrapper {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  font-size: 16px;
  color: var(--accent, #3b82f6);
  background: var(--icon-bg);
  transition: all 0.2s ease;
}

/* Hover */
.custom-menu .ant-menu-item:hover,
.custom-menu .ant-menu-submenu-title:hover {
  background: var(--hover-bg) !important;
  color: var(--text-active) !important;
}
.custom-menu .ant-menu-item:hover .menu-icon-wrapper,
.custom-menu .ant-menu-submenu-title:hover .menu-icon-wrapper {
  background: var(--icon-bg-hover);
  transform: scale(1.05);
}

/* Active */
.custom-menu .menu-item-active {
  background: linear-gradient(90deg, rgba(59,130,246,0.12), rgba(59,130,246,0.01)) !important;
  box-shadow: inset 3px 0 0 var(--primary-color);
}
.theme-dark .custom-menu .menu-item-active,
.theme-hybrid .custom-menu .menu-item-active {
  background: linear-gradient(90deg, rgba(59,130,246,0.2), rgba(59,130,246,0.02)) !important;
}
.custom-menu .menu-item-active .menu-text,
.custom-menu .menu-item-active .child-text {
  color: var(--text-active) !important;
  font-weight: 600;
}
.custom-menu .menu-item-active .menu-icon-wrapper {
  background: var(--icon-bg-active);
}

/* Submenu open */
.custom-menu .submenu-open > .ant-menu-submenu-title {
  color: var(--text-active) !important;
}
.custom-menu .submenu-open > .ant-menu-submenu-title .menu-icon-wrapper {
  background: var(--icon-bg-hover);
}

/* Submenu children */
.custom-menu .ant-menu-sub {
  background: var(--submenu-bg) !important;
  border-radius: 0 0 10px 10px;
  margin: 0 10px 4px 10px;
  padding: 4px 0;
}
.theme-dark .custom-menu .ant-menu-sub,
.theme-hybrid .custom-menu .ant-menu-sub {
  background: var(--submenu-bg) !important;
}
.theme-dark .custom-menu.ant-menu-dark .ant-menu-sub,
.theme-hybrid .custom-menu.ant-menu-dark .ant-menu-sub {
  background: var(--submenu-bg) !important;
}
.custom-menu .ant-menu-sub .ant-menu-item {
  margin: 1px 8px;
  height: 38px;
  line-height: 38px;
  font-size: 0.82rem;
  border-radius: 8px;
  color: var(--text-color) !important;
}
.custom-menu .ant-menu-sub .ant-menu-item:hover {
  color: var(--text-active) !important;
}

/* Child dot */
.child-dot {
  display: inline-block;
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: var(--muted-text);
  margin-right: 10px;
  margin-left: 4px;
  flex-shrink: 0;
  transition: all 0.2s ease;
}
.custom-menu .ant-menu-sub .ant-menu-item:hover .child-dot {
  background: var(--primary-color);
}
.custom-menu .ant-menu-sub .menu-item-active .child-dot {
  background: var(--primary-color);
  box-shadow: 0 0 6px var(--primary-color);
}

/* Footer */
.sider-footer {
  padding: 12px 24px;
  margin-top: auto;
}
.footer-version {
  font-size: 0.65rem;
  color: var(--muted-text);
  letter-spacing: 1px;
}

/* ====== HEADER ====== */
.main-header {
  background: var(--header-bg) !important;
  display: flex;
  align-items: center;
  padding: 0 20px;
  border-bottom: 1px solid var(--header-border);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
  height: 60px;
  line-height: 60px;
}
.header-left {
  display: flex;
  align-items: center;
}
.header-right {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 8px;
}
.collapse-trigger {
  font-size: 1.2rem;
  cursor: pointer;
  color: var(--text-color);
  transition: all 0.2s ease;
}
.collapse-trigger:hover {
  color: var(--primary-color);
  transform: scale(1.1);
}
.theme-toggle {
  color: var(--text-color);
  font-size: 1.1rem;
}
.theme-toggle:hover {
  color: var(--primary-color) !important;
}
.header-content {
  margin-left: 0;
}

/* ====== CONTENT ====== */
.main-content {
  background: var(--content-bg) !important;
}
.content-container {
  padding: 0 14px 14px 14px;
}
.theme-light .ant-layout {
  background: var(--content-bg) !important;
}
.theme-dark .ant-layout,
.theme-hybrid .ant-layout {
  background: var(--content-bg) !important;
}

/* Transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Collapsed */
.custom-sider.ant-layout-sider-collapsed .brand-text,
.custom-sider.ant-layout-sider-collapsed .user-card,
.custom-sider.ant-layout-sider-collapsed .sider-footer {
  display: none;
}

/* ====== DARK THEME OVERRIDES ====== */
.theme-dark .main-content {
  scrollbar-color: var(--scrollbar-color) transparent;
}
.theme-dark .main-content::-webkit-scrollbar {
  width: 8px;
}
.theme-dark .main-content::-webkit-scrollbar-track {
  background: transparent;
}
.theme-dark .main-content::-webkit-scrollbar-thumb {
  background: var(--scrollbar-color);
  border-radius: 4px;
}

/* ====== LIGHT THEME: ensure no dark bleeds ====== */
.theme-light .custom-sider,
.theme-light .custom-sider .ant-layout-sider-children,
.theme-light .main-header,
.theme-light .main-content,
.theme-light .custom-menu,
.theme-light .custom-menu .ant-menu-sub,
.theme-light .ant-layout {
  background-color: var(--sider-bg, #ffffff) !important;
}
.theme-light .main-header {
  background-color: var(--header-bg, #ffffff) !important;
}
.theme-light .main-content,
.theme-light .ant-layout > .ant-layout {
  background-color: var(--content-bg, #f1f5f9) !important;
}

/* Dropdown text color for both themes */
.theme-dark .header-content .user-name {
  color: #e2e8f0 !important;
}
.theme-dark .header-content .header-avatar {
  border-color: var(--border-color) !important;
}
.theme-dark .header-content .ant-dropdown-link {
  color: #cbd5e1 !important;
}

/* ====== GLOBAL DARK/HYBRID OVERRIDES FOR ANT-DESIGN ====== */

/* Inputs */
.theme-dark .ant-input,
.theme-dark .ant-input-affix-wrapper,
.theme-dark .ant-input-affix-wrapper > input,
.theme-hybrid .ant-input,
.theme-hybrid .ant-input-affix-wrapper,
.theme-hybrid .ant-input-affix-wrapper > input {
  background: var(--card-bg) !important;
  border-color: var(--card-border) !important;
  color: var(--card-text) !important;
}
.theme-dark .ant-input::placeholder,
.theme-hybrid .ant-input::placeholder {
  color: var(--card-muted) !important;
}
.theme-dark .ant-input-affix-wrapper:hover,
.theme-hybrid .ant-input-affix-wrapper:hover {
  border-color: var(--primary-color) !important;
}

/* Selects */
.theme-dark .ant-select .ant-select-selector,
.theme-hybrid .ant-select .ant-select-selector {
  background: var(--card-bg) !important;
  border-color: var(--card-border) !important;
  color: var(--card-text) !important;
}
.theme-dark .ant-select .ant-select-selection-item,
.theme-hybrid .ant-select .ant-select-selection-item {
  color: var(--card-text) !important;
}
.theme-dark .ant-select .ant-select-selection-placeholder,
.theme-hybrid .ant-select .ant-select-selection-placeholder {
  color: var(--card-muted) !important;
}
.theme-dark .ant-select-multiple .ant-select-selection-item-content,
.theme-hybrid .ant-select-multiple .ant-select-selection-item-content {
  color: var(--card-text) !important;
}

/* Date pickers */
.theme-dark .ant-picker,
.theme-hybrid .ant-picker {
  background: var(--card-bg) !important;
  border-color: var(--card-border) !important;
  color: var(--card-text) !important;
}
.theme-dark .ant-picker input,
.theme-hybrid .ant-picker input {
  color: var(--card-text) !important;
}
.theme-dark .ant-picker input::placeholder,
.theme-hybrid .ant-picker input::placeholder {
  color: var(--card-muted) !important;
}

/* Pagination */
.theme-dark .ant-pagination .ant-pagination-item,
.theme-hybrid .ant-pagination .ant-pagination-item {
  background: var(--card-bg) !important;
  border-color: var(--card-border) !important;
}
.theme-dark .ant-pagination .ant-pagination-item a,
.theme-hybrid .ant-pagination .ant-pagination-item a {
  color: var(--card-text) !important;
}
.theme-dark .ant-pagination .ant-pagination-item-active,
.theme-hybrid .ant-pagination .ant-pagination-item-active {
  border-color: var(--primary-color) !important;
  background: rgba(59,130,246,0.15) !important;
}
.theme-dark .ant-pagination .ant-pagination-item-active a,
.theme-hybrid .ant-pagination .ant-pagination-item-active a {
  color: var(--primary-color) !important;
}
.theme-dark .ant-pagination .ant-pagination-prev .ant-pagination-item-link,
.theme-dark .ant-pagination .ant-pagination-next .ant-pagination-item-link,
.theme-hybrid .ant-pagination .ant-pagination-prev .ant-pagination-item-link,
.theme-hybrid .ant-pagination .ant-pagination-next .ant-pagination-item-link {
  background: var(--card-bg) !important;
  border-color: var(--card-border) !important;
  color: var(--card-text) !important;
}

/* Modal */
.theme-dark .ant-modal-content,
.theme-hybrid .ant-modal-content {
  background: var(--card-bg) !important;
}
.theme-dark .ant-modal-header,
.theme-hybrid .ant-modal-header {
  background: var(--card-bg) !important;
}
.theme-dark .ant-modal-title,
.theme-hybrid .ant-modal-title {
  color: var(--card-text) !important;
}
.theme-dark .ant-modal-close-icon,
.theme-hybrid .ant-modal-close-icon {
  color: var(--card-muted) !important;
}

/* Form labels */
.theme-dark .ant-form-item-label > label,
.theme-hybrid .ant-form-item-label > label {
  color: var(--card-text) !important;
}

/* Textarea */
.theme-dark .ant-input.textarea,
.theme-dark textarea.ant-input,
.theme-hybrid textarea.ant-input {
  background: var(--card-bg) !important;
  color: var(--card-text) !important;
  border-color: var(--card-border) !important;
}

/* Table hover fix */
.theme-dark .ant-table-tbody > tr.ant-table-row:hover > td,
.theme-hybrid .ant-table-tbody > tr.ant-table-row:hover > td {
  background: var(--hover-bg) !important;
}

/* Buttons (text/ghost type) */
.theme-dark .ant-btn:not(.ant-btn-primary):not(.ant-btn-dangerous),
.theme-hybrid .ant-btn:not(.ant-btn-primary):not(.ant-btn-dangerous) {
  background: transparent !important;
}
.theme-dark .ant-btn-text,
.theme-hybrid .ant-btn-text {
  background: transparent !important;
}
.theme-dark .ant-btn-text:hover,
.theme-hybrid .ant-btn-text:hover {
  background: var(--hover-bg) !important;
}
.theme-dark .ant-btn-default,
.theme-hybrid .ant-btn-default {
  background: var(--card-bg) !important;
  border-color: var(--card-border) !important;
}
.theme-dark .ant-btn-default:hover,
.theme-hybrid .ant-btn-default:hover {
  background: var(--hover-bg) !important;
  border-color: var(--primary-color) !important;
}
.theme-dark .ant-btn[disabled],
.theme-hybrid .ant-btn[disabled] {
  background: var(--card-bg) !important;
  color: var(--card-muted) !important;
  border-color: var(--card-border) !important;
}

/* Empty state text */
.theme-dark .ant-empty-description,
.theme-hybrid .ant-empty-description {
  color: var(--card-muted) !important;
}

/* Popconfirm */
.theme-dark .ant-popover-inner,
.theme-hybrid .ant-popover-inner {
  background: var(--card-bg) !important;
}
.theme-dark .ant-popover-message-title,
.theme-hybrid .ant-popover-message-title {
  color: var(--card-text) !important;
}

/* ====== DROPDOWN POPUPS (rendered in body, outside layout) ====== */
body.theme-dark .ant-select-dropdown,
body.theme-hybrid .ant-select-dropdown {
  background: var(--card-bg) !important;
}
body.theme-dark .ant-select-dropdown .ant-select-item,
body.theme-hybrid .ant-select-dropdown .ant-select-item {
  color: var(--card-text) !important;
}
body.theme-dark .ant-select-dropdown .ant-select-item-option-active,
body.theme-hybrid .ant-select-dropdown .ant-select-item-option-active {
  background: var(--hover-bg) !important;
}
body.theme-dark .ant-select-dropdown .ant-select-item-option-selected,
body.theme-hybrid .ant-select-dropdown .ant-select-item-option-selected {
  background: rgba(59,130,246,0.15) !important;
  color: var(--primary-color) !important;
  font-weight: 600;
}
body.theme-dark .ant-select-dropdown .ant-select-item-option-selected .ant-select-item-option-content,
body.theme-hybrid .ant-select-dropdown .ant-select-item-option-selected .ant-select-item-option-content {
  color: var(--primary-color) !important;
}

/* DatePicker popup */
body.theme-dark .ant-picker-dropdown .ant-picker-panel-container,
body.theme-hybrid .ant-picker-dropdown .ant-picker-panel-container {
  background: var(--card-bg) !important;
}
body.theme-dark .ant-picker-dropdown .ant-picker-header,
body.theme-hybrid .ant-picker-dropdown .ant-picker-header {
  color: var(--card-text) !important;
}
body.theme-dark .ant-picker-dropdown .ant-picker-content th,
body.theme-hybrid .ant-picker-dropdown .ant-picker-content th {
  color: var(--card-muted) !important;
}
body.theme-dark .ant-picker-dropdown .ant-picker-cell,
body.theme-hybrid .ant-picker-dropdown .ant-picker-cell {
  color: var(--card-text) !important;
}
body.theme-dark .ant-picker-dropdown .ant-picker-cell-in-view,
body.theme-hybrid .ant-picker-dropdown .ant-picker-cell-in-view {
  color: var(--card-text) !important;
}
body.theme-dark .ant-picker-dropdown .ant-picker-cell:hover:not(.ant-picker-cell-selected) .ant-picker-cell-inner,
body.theme-hybrid .ant-picker-dropdown .ant-picker-cell:hover:not(.ant-picker-cell-selected) .ant-picker-cell-inner {
  background: var(--hover-bg) !important;
}
body.theme-dark .ant-picker-dropdown .ant-picker-cell-selected .ant-picker-cell-inner,
body.theme-hybrid .ant-picker-dropdown .ant-picker-cell-selected .ant-picker-cell-inner {
  background: var(--primary-color) !important;
  color: #fff !important;
}

/* Dropdown menu (generic) */
body.theme-dark .ant-dropdown-menu,
body.theme-hybrid .ant-dropdown-menu {
  background: var(--card-bg) !important;
}
body.theme-dark .ant-dropdown-menu-item,
body.theme-hybrid .ant-dropdown-menu-item {
  color: var(--card-text) !important;
}
body.theme-dark .ant-dropdown-menu-item:hover,
body.theme-hybrid .ant-dropdown-menu-item:hover {
  background: var(--hover-bg) !important;
}
</style>
