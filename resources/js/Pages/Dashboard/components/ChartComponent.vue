<template>
    <canvas id="myChart"></canvas>
</template>

<script setup>
import { onMounted, onUnmounted, watch, ref } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  isDark: { type: Boolean, default: false },
});

let chartInstance = null;
const textColor = () => props.isDark ? '#cbd5e1' : '#475569';
const gridColor = () => props.isDark ? 'rgba(148,163,184,0.12)' : 'rgba(0,0,0,0.06)';

const buildConfig = () => ({
  type: 'line',
  data: {
    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May'],
    datasets: [
      {
        label: 'Prueba 1',
        backgroundColor: props.isDark ? 'rgba(139,92,246,0.15)' : 'rgba(244,205,255,0.4)',
        data: [80, 30, 40, 50, 54],
        borderWidth: 2,
        pointRadius: 0,
        tension: 0.5,
        borderColor: '#8b5cf6',
      },
      {
        label: 'Prueba 2',
        backgroundColor: props.isDark ? 'rgba(59,130,246,0.15)' : 'rgba(0,170,228,0.2)',
        data: [10, 40, 40, 80, 94],
        borderWidth: 2,
        pointRadius: 0,
        tension: 0.5,
        borderColor: '#3b82f6',
      },
    ],
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        labels: { color: textColor() },
      },
      tooltip: {
        titleColor: props.isDark ? '#e2e8f0' : '#1e293b',
        bodyColor: props.isDark ? '#cbd5e1' : '#475569',
        backgroundColor: props.isDark ? '#0f172a' : '#ffffff',
        borderColor: props.isDark ? '#334155' : '#e2e8f0',
        borderWidth: 1,
      },
    },
    scales: {
      x: {
        ticks: { color: textColor() },
        grid: { color: gridColor() },
      },
      y: {
        ticks: { color: textColor() },
        grid: { color: gridColor() },
      },
    },
  },
});

const renderChart = () => {
  if (chartInstance) {
    chartInstance.destroy();
  }
  chartInstance = new Chart(
    document.getElementById('myChart'),
    buildConfig()
  );
};

onMounted(() => {
  renderChart();
});

watch(() => props.isDark, () => {
  renderChart();
});

onUnmounted(() => {
  if (chartInstance) chartInstance.destroy();
});
</script>
