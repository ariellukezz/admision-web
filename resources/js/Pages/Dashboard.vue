<template>
<Head title="Dashboard" />
<AuthenticatedLayout>
<div class="dash-wrapper">
  <div class="dash-main">
    <!-- CARDS-->
    <div class="flex justify-between">
        <div class="p-4 card-dash">
          <div class="flex justify-between">
            <div><span class="dash-label">Preinscritos</span></div>
            <div class="dash-icon-circle">
              <div style="margin-top: -5px;">
                <span style="color: var(--primary-color); font-size: 1.15em;"><team-outlined /></span>
              </div>
            </div>
          </div>
          <div style="margin-top: 50px;">
            <div v-if="preinscritos">
              <span class="dash-number">
                {{ preinscritos }}
              </span>
            </div>
          </div>
          <div class="flex justify-start">
            <div>
              <span class="dash-success"> {{ ultimopreinscrito.count }} preinscritos <span class="dash-muted">el {{ ultimopreinscrito.date }}</span> </span>
            </div>
          </div>

        </div>

        <div class="p-4 card-dash">
          <div class="flex justify-between">
            <div><span class="dash-label">Documento</span></div>
            <div class="dash-icon-circle">
              <div style="margin-top: -5px;">
                <span style="color: var(--primary-color); font-size: 1.15em;"><team-outlined /></span>
              </div>
            </div>
          </div>
          <div style="margin-top: 50px;">
            <div>
              <span class="dash-number">
                1369
              </span>
            </div>
          </div>
          <div class="flex justify-start">
            <div>
              <span class="dash-success"> icon  16% <span class="dash-muted">Since las week</span> </span>
            </div>
          </div>

        </div>
        <div class="p-4 card-dash">
          <div class="flex justify-between">
            <div><span class="dash-label">Inscritos</span></div>
            <div class="dash-icon-circle">
              <div style="margin-top: -5px;">
                <span style="color: var(--primary-color); font-size: 1.15em;"><team-outlined /></span>
              </div>
            </div>
          </div>
          <div style="margin-top: 50px;">
            <div>
              <span class="dash-number">
                {{ inscritos }}
              </span>
            </div>
          </div>
          <div class="flex justify-start">
            <div>
              <span class="dash-success"> {{ ultimoinscrito.count }} inscritos <span class="dash-muted">el {{ ultimoinscrito.date }}</span> </span>
            </div>
          </div>

        </div>
    </div>
    <!-- END CARD -->
    <div style="height: 16px;" ></div>

    <div class="flex justify-between">
        <div class="p-4 card-dash card-chart">
          <div class="flex justify-between">
            <div><span class="dash-label">Documento</span></div>
            <div class="dash-icon-circle">
              <div style="margin-top: -5px;">
                <span style="color: var(--primary-color); font-size: 1.15em;"><team-outlined /></span>
              </div>
            </div>
          </div>
          <div style="margin-top: 0px;">
            <Bar
              id="my-chart-id"
              :options="barrasOpciones"
              :data="barras"
            />
          </div>
        </div>

        <div class="p-4 card-dash card-chart">
          <div class="flex justify-between">
            <div><span class="dash-label">Documento</span></div>
            </div>
          <div style="margin-top: 0px;">
            <ChartComponent :isDark="isDark"/>
          </div>
        </div>
    </div>
    <div class="flex" style="height: 16px;"></div>
    <div class="card-dash card-blank">

    </div>
  </div>
  <div class="dash-sidebar">
    <div class="p-4 card-sidebar">
      <div class="mb-5">
        <div class="mb-3 flex justify-between">
          <h1 class="dash-title">Mejores inscriptores</h1>
          <div style="margin-top: -5px;"> <span style="color: var(--primary-color);"><eye-outlined/></span></div>
        </div>

        <div v-if="minscriptores != null">
          <div v-for="(inscriptor,index) in minscriptores" :key="inscriptor.id" class="flex mb-2" style="height: 38px; width: 100%;">
            <div style="border-radius: 50%; height: 38px; overflow: hidden;">
                <div v-if="inscriptor.url">
                  <img src="https://images.pexels.com/photos/614810/pexels-photo-614810.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500"
                    width="38" height="38">
                </div>

                <div v-else style="width: 38px; height: 38px;" :style="'background:'+colores[index]">
                  <div class="flex justify-center pt-0">
                    <span style="color: white; font-size: 1.7rem;">
                      {{ inscriptor.paterno[0].toUpperCase() }}
                    </span>
                  </div>
                </div>


            </div>
            <div class="ml-2">
              <div style="margin-top: 2px;"><span class="dash-inscriptor-name">{{ inscriptor.name }} {{ inscriptor.paterno }}</span></div>
              <div style="margin-top: -7px;"><span class="dash-muted" style="font-size: .8rem;">{{ inscriptor.cant }} Inscritos</span></div>
            </div>
          </div>
        </div>
      </div>
      <div>
        <div class="mb-3">
          <h1 class="dash-title">Mejores Revisores del día</h1>
        </div>

        <div v-if="minscriptoresD != null"  >
          <div v-for="(inscriptor,index) in minscriptoresD" :key="inscriptor.id" class="flex mb-2" style="height: 38px; width: 100%;">
            <div style="border-radius: 50%; height: 38px; overflow: hidden;">
                <div v-if="inscriptor.url">
                  <img src="https://images.pexels.com/photos/614810/pexels-photo-614810.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500"
                    width="38" height="38">
                </div>

                <div v-else style="width: 38px; height: 38px;" :style="'background:'+colores[index]">
                  <div class="flex justify-center pt-0">
                    <span style="color: white; font-size: 1.7rem;">
                      {{ inscriptor.paterno[0].toUpperCase() }}
                    </span>
                  </div>
                </div>


            </div>
            <div class="ml-2">
              <div style="margin-top: 2px;"><span class="dash-inscriptor-name">{{ inscriptor.name }} {{ inscriptor.paterno }}</span></div>
              <div style="margin-top: -7px;"><span class="dash-muted" style="font-size: .8rem;">{{ inscriptor.cant }} Inscritos</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3';
import { TeamOutlined, EyeOutlined } from '@ant-design/icons-vue';
import { Bar } from 'vue-chartjs'
import ChartComponent from './Dashboard/components/ChartComponent.vue';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { toFixed } from 'ant-design-vue/lib/input-number/src/utils/MiniDecimal';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

/* ====== THEME DETECTION ====== */
const currentTheme = ref(localStorage.getItem('sider-theme') || 'light');
let themeObserver = null;

onMounted(() => {
  themeObserver = new MutationObserver(() => {
    const cls = document.body.className;
    const match = cls.match(/theme-(\w+)/);
    if (match && match[1] !== currentTheme.value) {
      currentTheme.value = match[1];
    }
  });
  themeObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });
});
onUnmounted(() => {
  if (themeObserver) themeObserver.disconnect();
});

const isDark = computed(() => currentTheme.value === 'dark');
const chartTextColor = computed(() => isDark.value ? '#cbd5e1' : '#475569');
const chartGridColor = computed(() => isDark.value ? 'rgba(148,163,184,0.12)' : 'rgba(0,0,0,0.06)');

/* ====== CHART DATA (reactive to theme) ====== */
const barras = computed(() => ({
  labels: ['Lun', 'Mar', 'Mie', 'Jue', 'Vie'],
  datasets: [
    {
      label: 'Preinscritos',
      backgroundColor: isDark.value ? 'rgba(59,130,246,0.6)' : '#00aae4',
      borderColor: '#3b82f6',
      borderWidth: 2,
      fill: false,
      data: [20, 39, 50, 60, 70, 89],
      tension: 0.5,
      pointRadius: false,
    },
    {
      label: 'Inscritos',
      backgroundColor: isDark.value ? 'rgba(139,92,246,0.6)' : 'rgba(139,92,246,0.7)',
      borderColor: '#8b5cf6',
      borderWidth: 2,
      fill: false,
      data: [70, 89, 10, 90, 35, 89],
      tension: 0.5,
      pointRadius: false,
    },
  ]
}));

const barrasOpciones = computed(() => ({
  responsive: true,
  plugins: {
    legend: {
      labels: { color: chartTextColor.value }
    },
    tooltip: {
      titleColor: isDark.value ? '#e2e8f0' : '#1e293b',
      bodyColor: isDark.value ? '#cbd5e1' : '#475569',
      backgroundColor: isDark.value ? '#0f172a' : '#ffffff',
      borderColor: isDark.value ? '#334155' : '#e2e8f0',
      borderWidth: 1,
    }
  },
  scales: {
    x: {
      ticks: { color: chartTextColor.value },
      grid: { color: chartGridColor.value }
    },
    y: {
      ticks: { color: chartTextColor.value },
      grid: { color: chartGridColor.value }
    }
  }
}));

const preinscritos = ref(0);
const ultimopreinscrito = ref({ count:0, date:'0000-00-00' });
const inscritos = ref(0);
const ultimoinscrito = ref({ count:0, date:'0000-00-00' });
const minscriptores = ref(null);
const minscriptoresD = ref(null);


const getPreinscritos = async () => {
  try {
    const response = await axios.get("get-preinscritos", { dni: "dni" });
    preinscritos.value = response.data.preinscritos;
    ultimopreinscrito.value = response.data.fecha;
  } catch (error) {
    console.error("Error al obtener datos de preinscritos:", error);
    return null;
  }
};

const getInscritos = async () => {
  try {
    const response = await axios.get("get-inscritos", { dni: "dni" });
    inscritos.value = response.data.inscritos;
    ultimoinscrito.value = response.data.fecha;
  } catch (error) {
    console.error("Error al obtener datos de los inscritos:", error);
    return null;
  }
};

const getMinscriptores = async () => {
  try {
    const response = await axios.get("get-mejores-inscriptores", { dni: "dni" });
    minscriptores.value = response.data.inscriptores;
  } catch (error) {
    console.error("Error al obtener datos de los inscritos:", error);
    return null;
  }
};

const getMinscriptoresDia = async () => {
  try {
    const response = await axios.post("get-mejores-inscriptores-dia", { fecha: "2023-08-04" });
    minscriptoresD.value = response.data.inscriptores;
  } catch (error) {
    console.error("Error al obtener datos de los inscritos:", error);
    return null;
  }
};



getPreinscritos();
getInscritos();
getMinscriptores();
getMinscriptoresDia();


const colores = [
  "#f3b4c8", "#f9cb9c", "#f4d5ae", "#d6e4a3", "#92c7a3",
  "#a3cedc", "#d7aefb", "#f7aef8", "#f8a1d1", "#f0bad6",
  "#f9d6ac", "#fed6a3", "#f6b89d", "#fcc5ae", "#fee1a3",
  "#c9e8a3", "#a3e2c7", "#a3e1e0", "#a3cce1", "#c3a3e1",
  "#e8a3d0", "#e2a3a3", "#f29b82", "#efc085", "#ebd47f",
  "#d1d17a", "#87c289", "#87c2bd", "#8bb4cc", "#a48bcc",
  "#b58bcc", "#cb8bcc", "#cc8bb1", "#cf8b8b", "#d0a18b",
  "#d5a18b", "#d9a18b", "#dba88c", "#ddb593", "#deb6a6",
  "#95b0ac", "#9aaca7", "#a19e98", "#bba895", "#d2c0a7",
  "#d5c6a3", "#d6cfad", "#d9cfad", "#dbd4b2", "#ddd8b6"
];

</script>

<style scoped>
/* ====== LAYOUT ====== */
.dash-wrapper {
  display: flex;
  background: var(--content-bg, #f1f5f9);
  min-height: 100%;
}
.dash-main {
  width: calc(100% - 300px);
}
.dash-sidebar {
  width: 300px;
  height: 560px;
  padding-left: 15px;
}

/* ====== CARDS ====== */
.card-dash {
  background: var(--card-bg, #ffffff);
  border: 1px solid var(--card-border, #e2e8f0);
  color: var(--card-text, #1e293b);
  border-radius: 9px;
  height: 160px;
  width: 260px;
  transition: background 0.3s, border-color 0.3s, color 0.3s;
}
.card-chart {
  width: 49%;
  height: 240px;
}
.card-blank {
  width: 100%;
  height: 300px;
}
.card-sidebar {
  background: var(--card-bg, #ffffff);
  border: 1px solid var(--card-border, #e2e8f0);
  color: var(--card-text, #1e293b);
  border-radius: 9px;
  height: 560px;
  transition: background 0.3s, border-color 0.3s, color 0.3s;
}

/* ====== TEXT ELEMENTS ====== */
.dash-label {
  font-weight: bold;
  color: var(--card-text, #1e293b);
}
.dash-number {
  font-size: 1.5rem;
  font-weight: bold;
  color: var(--card-text, #1e293b);
}
.dash-title {
  font-weight: bold;
  color: var(--card-text, #1e293b);
}
.dash-inscriptor-name {
  font-weight: bold;
  color: var(--card-text, #1e293b);
}
.dash-muted {
  color: var(--card-muted, #64748b);
}
.dash-success {
  color: var(--success-color, #16a34a);
  font-weight: bold;
}
.dash-icon-circle {
  padding: 4px 8px;
  background: var(--icon-bg, #f1f5f9);
  border-radius: 50%;
  transition: background 0.3s;
}
</style>

<!-- Non-scoped overrides for dark/hybrid themes -->
<style>
.theme-dark .card-dash,
.theme-dark .card-sidebar {
  background: #1e293b !important;
  border-color: #334155 !important;
  color: #e2e8f0 !important;
}
.theme-dark .dash-label,
.theme-dark .dash-number,
.theme-dark .dash-title,
.theme-dark .dash-inscriptor-name {
  color: #e2e8f0 !important;
}
.theme-dark .dash-muted {
  color: #94a3b8 !important;
}
.theme-dark .dash-success {
  color: #4ade80 !important;
}
.theme-dark .dash-icon-circle {
  background: rgba(255,255,255,0.04) !important;
}
.theme-dark .dash-wrapper {
  background: #0f172a !important;
}

.theme-hybrid .card-dash,
.theme-hybrid .card-sidebar {
  background: #ffffff !important;
  border-color: #e2e8f0 !important;
  color: #1e293b !important;
}
.theme-hybrid .dash-wrapper {
  background: #f1f5f9 !important;
}
</style>