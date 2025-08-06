<template>
  <a-layout class="min-h-screen">
    <!-- Sidebar -->
    <a-layout-sider
      v-model:collapsed="collapsed"
      :width="265"
      :collapsed-width="50"
      class="custom-sider"
    >
      <div class="sider-header mt-3">
        <div>
          <img
            src="../../assets/imagenes/logotiny.png"
            class="sider-logo"
            alt="Logo"
          />
        </div>

        <div class="sider-title" v-if="!collapsed">
          Sistema de Admisión
        </div>
      </div>

      <div class="h-[calc(100vh-80px)] overflow-y-scroll custom-scrollbar">
        <div class="mb-4">
          <div class="flex justify-center">
            <img
              src="../../assets/imagenes/usuario.png"
              style="width: 150px;"
            />
          </div>

          <div class="flex justify-center text-gray-400" v-if="!collapsed">
            <div>
              <div class="text-center text-md mb-2">Administrador</div>
              <div>
                <a-select
                    ref="select"
                    v-model:value="proceso"
                    style="width: 200px;"
                    theme="dark"
                    @focus="focus"
                    @change="handleChange"
                  >
                    <a-select-option :value="item.value" v-for="item in procesos" :key="item" > {{ item.label }}</a-select-option>
                    <!-- <a-select-option value="cepreuna">Cepreuna 2025-I</a-select-option>
                    <a-select-option value="extraordinario">Extraordinario 2025-I</a-select-option> -->
                  </a-select>
              </div>
            </div>

          </div>
        </div>

        <a-menu
          v-model:selectedKeys="selectedKeys"
          v-model:openKeys="openKeys"
          theme="dark"
          mode="inline"
          class="custom-menu"
        >
          <template v-for="item in menuItems" :key="item.key">
            <a-menu-item
              v-if="!item.children"
              :key="item"
              :class="{ 'menu-item-active': route().current(item.route) }"
            >
              <Link :href="route(item.route)">
                <component :is="item.icon" class="menu-icon" />
                <span class="menu-text">{{ item.label }}</span>
              </Link>
            </a-menu-item>

            <a-sub-menu
              v-else
              :key="item.key"
              :title="item.label"
              class="submenu"
            >
              <template #icon>
                <component :is="item.icon" />
              </template>

              <a-menu-item
                v-for="child in item.children"
                :key="child.key"
                :class="{ 'menu-item-active': route().current(child.route) }"
              >
                <Link :href="route(child.route)">
                  <component :is="child.icon" class="child-icon" />
                  <span class="child-text">{{ child.label }}</span>
                </Link>
              </a-menu-item>
            </a-sub-menu>
          </template>
        </a-menu>
      </div>
    </a-layout-sider>

    <!-- Contenido principal -->
    <a-layout>
      <a-layout-header class="main-header" style="background: white; padding: 0px 20px; margin:0px 0px;">
        <menu-fold-outlined
          class="collapse-trigger"
          @click="collapsed = !collapsed"
        />
        <Header class="header-content" />
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
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3'
import Header from '@/Layouts/Header.vue';
import {
  AppstoreFilled,
  SettingFilled,
  MenuFoldOutlined
} from '@ant-design/icons-vue';
const page = usePage()

const collapsed = ref(false);
const selectedKeys = ref([]);
const openKeys = ref([]);
const proceso = ref(page.props.auth.user.id_proceso);

const menuItems = [
  {
    key: 'dashboard',
    icon: AppstoreFilled,
    label: 'Dashboard',
    route: 'admin-dashboard'
  },
  {
    key: 'configuracion',
    icon: SettingFilled,
    label: 'Configuración',
    children: [
      {
        key: 'vacantes',
        icon: SettingFilled,
        label: 'Vacantes',
        route: 'admin-vacantes'
      },
      {
        key: 'tarifas',
        icon: SettingFilled,
        label: 'Tarifas',
        route: 'programa-index'
      },
      {
        key: 'observados',
        icon: SettingFilled,
        label: 'Observados',
        route: 'programa-index'
      },
      // {
      //   key: 'vacantes',
      //   icon: SettingFilled,
      //   label: 'Vacantes',
      //   route: 'admin-vacantes'
      // }
    ]
  },
  {
    key: 'gestionadmision',
    icon: SettingFilled,
    label: 'Gestión de admision',
    children: [
      {
        key: 'preinscripcion',
        icon: SettingFilled,
        label: 'Preinscripciones',
        route: 'admin-preinscripciones'
      },
      {
        key: 'inscripcion',
        icon: SettingFilled,
        label: 'Inscripciones',
        route: 'admin-inscripciones'
      },
      {
        key: 'controlbiometrico',
        icon: SettingFilled,
        label: 'Ctrl Biométrico',
        route: 'admin-control-biometrico'
      },
      {
        key: 'fotoshuellas',
        icon: SettingFilled,
        label: 'Fotos y huellas',
        route: 'about'
      },
      {
        key: 'resultados',
        icon: SettingFilled,
        label: 'Puntajes',
        route: 'about'
      },
      {
        key: 'observados',
        icon: SettingFilled,
        label: 'Observados',
        route: 'admin-observados'
      },
      {
        key: 'estudiantes',
        icon: SettingFilled,
        label: 'Estudiantes',
        route: 'admin-observados'
      },
      {
        key: 'estudiantecepre',
        icon: SettingFilled,
        label: 'Est. Cepreuna',
        route: 'admin-observados'
      },
    ]
  },
  {
    key: 'mantenimiento',
    icon: SettingFilled,
    label: 'Mantenimiento',
    children: [
      {
        key: 'filial',
        icon: SettingFilled,
        label: 'Sede',
        route: 'filial-index'
      },
      {
        key: 'procesos',
        icon: SettingFilled,
        label: 'Procesos',
        route: 'proceso-index'
      },
      {
        key: 'programas',
        icon: SettingFilled,
        label: 'Programas',
        route: 'programa-index'
      },
      {
        key: 'modalidades',
        icon: SettingFilled,
        label: 'Modalidades',
        route: 'modalidad-index'
      },
      {
        key: 'colegios',
        icon: SettingFilled,
        label: 'Colegios',
        route: 'admin-colegios'
      },

      {
        key: 'ubigeo',
        icon: SettingFilled,
        label: 'Ubigeos',
        route: 'programa-index'
      },
      {
        key: 'pagos',
        icon: SettingFilled,
        label: 'Pagos',
        route: 'programa-index'
      },
      {
        key: 'reglamentos',
        icon: SettingFilled,
        label: 'Reglamentos',
        route: 'admin-reglamento'
      },

    ]
  },
  {
    key: 'participantes',
    icon: SettingFilled,
    label: 'Gestion de participantes',
    children: [
      {
        key: 'docentes',
        icon: SettingFilled,
        label: 'Docentes',
        route: 'admin-participante-docente'
      },
      {
        key: 'administrativos',
        icon: SettingFilled,
        label: 'Administrativos',
        route: 'admin-participante-administrativo'
      },
      {
        key: 'sorteo',
        icon: SettingFilled,
        label: 'Sorteo',
        route: 'admin-participante-sorteo'
      },
      {
        key: 'participantes',
        icon: SettingFilled,
        label: 'Participantes',
        route: 'modalidad-index'
      },
    ]
  },
  {
    key: 'usuarios',
    icon: SettingFilled,
    label: 'Roles y usuarios',
    children: [
      {
        key: 'roles',
        icon: SettingFilled,
        label: 'Roles',
        route: 'roles-index'
      },
      {
        key: 'usuarios',
        icon: SettingFilled,
        label: 'Usuarios',
        route: 'usuarios-index'
      },
    ]
  },

  {
    key: 'gestion',
    icon: SettingFilled,
    label: 'Gestión técnica',
    children: [
      {
        key: 'apoderados',
        icon: SettingFilled,
        label: 'Apoderados',
        route: 'admin-apoderado-index'
      },
      {
        key: 'postulantes',
        icon: SettingFilled,
        label: 'Postulantes',
        route: 'admin-postulante-index'
      },
      {
        key: 'documentos',
        icon: SettingFilled,
        label: 'Documentos',
        route: 'admin-documento-index'
      },
      {
        key: 'colegios',
        icon: SettingFilled,
        label: 'Colegios',
        route: 'admin-colegios'
      },
      {
        key: 'carrerasprevias',
        icon: SettingFilled,
        label: 'Estudios anteriores',
        route: 'admin-carreras-previas'
      },
      {
        key: 'pagosbn',
        icon: SettingFilled,
        label: 'Pagos BN',
        route: 'admin-pagos-banco'
      },
    ]
  },
  {
    key: 'reportes',
    icon: SettingFilled,
    label: 'Reportes',
    children: [       
      {
        key: 'resumen_general',
        icon: SettingFilled,
        label: 'Resumen general',
        route: 'admin-resumenes-general'
      },
      {
        key: 'resumen',
        icon: SettingFilled,
        label: 'Resumen inscripciones',
        route: 'admin-resumenes-inscripcion'
      },

      {
        key: 'res_programa_diario',
        icon: SettingFilled,
        label: 'Rep programa diario',
        route: 'admin-resumenes-programa-diario'
      },
      {
        key: 'res_usuarios_diario',
        icon: SettingFilled,
        label: 'Rep usuarios diario',
        route: 'admin-resumenes-usuario-diario'
      },
      {
        key: 'ratio',
        icon: SettingFilled,
        label: 'Ratio',
        route: 'admin-ratio'
      },

      {
        key: 'resumenbiometrico',
        icon: SettingFilled,
        label: 'Res. biométrico',
        route: 'admin-resumenes-biometrico'
      },

      {
        key: 'errores',
        icon: SettingFilled,
        label: 'Rep errorres',
        route: 'programa-index'
      },
    ]
  },
  {
    key: 'ayuda',
    icon: SettingFilled,
    label: 'Centro de ayuda',
    children: [
      {
        key: 'soportetecnico',
        icon: SettingFilled,
        label: 'Soporte técnico',
        route: 'roles-index'
      },
      {
        key: 'manualesguias',
        icon: SettingFilled,
        label: 'Manuales y guias',
        route: 'usuarios-index'
      },
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
:root {
  --sider-bg: #2d3748;
  --primary-color: #1890ff;
  --hover-bg: #4a5568;
  --text-color: #cdcdcd;
}

.custom-scrollbar::-webkit-scrollbar {
  display: none;
}sx

.custom-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.custom-sider {
  background: var(--sider-bg);
  border-right: 1px solid #0000000f;
}

.sider-header {
  display: flex;
  align-items: center;
  padding: 10px;
}

.sider-logo {
  height: 30px;
  min-width: 30px;
}

.sider-title {
  margin-left: 10px;
  color: var(--text-color);
  font-size: 1.4rem;
  font-weight: 500;
}

.custom-menu .ant-menu-item,
.custom-menu .ant-menu-submenu-title {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.custom-menu .menu-item-active {
  background: linear-gradient(
    90deg,
    rgba(24, 144, 255, 0.3) 0%,
    rgba(24, 144, 255, 0.1) 100%
  ) !important;
  border-left: 3px solid var(--primary-color) !important;
  /* margin-left: 46px !important; */

  /* padding-left: 38px !important; */
}

.custom-menu .menu-item-active .menu-text,
.custom-menu .menu-item-active .anticon {
  color: white !important;
  font-weight: 600;
}

.custom-menu .ant-menu-item:hover {
  background: var(--hover-bg) !important;
}

.main-header {
  background:yellow;
  display: flex;
  align-items: center;
  border-bottom: 1px solid #d9d9d9;
}

.collapse-trigger {
  font-size: 1.3rem;
  cursor: pointer;
  transition: color 0.2s;
}

.collapse-trigger:hover {
  color: var(--primary-color);
}

.header-content {
  margin-left: auto;
}

.main-content {
  height: calc(100vh - 140px);
  margin-top: 0px;
  padding: 12px 0px;
  overflow-y: scroll;
}

.content-container {
  padding: 0px 14px 14px 14px;
}
</style>
