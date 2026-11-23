<template>
  <Head title="Resumen de ingresantes" />
  <AuthenticatedLayout>
    <div class="rounded-lg border p-4 shadow-sm" style="background: var(--card-bg, #fff); border-color: var(--card-border, #e2e8f0); color: var(--card-text, #1e293b);">
      <h1 class="mb-4 text-xl font-semibold">Resumen de ingresantes</h1>
      <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <a-checkbox-group v-model:value="selectedColumns" :options="columnOptions" />
        <span class="font-semibold">Total de ingresantes: {{ totalGeneral }}</span>
      </div>
    </div>

    <div class="mt-4 rounded-lg border p-4 shadow-sm" style="height: calc(100vh - 245px); background: var(--card-bg, #fff); border-color: var(--card-border, #e2e8f0);">
      <a-table
        :columns="columns"
        :data-source="resumenes"
        :loading="loading"
        :pagination="false"
        :scroll="{ x: 200, y: 'calc(100vh - 360px)' }"
        size="small"
      />
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const columnOptions = [
  { label: 'Área', value: 'area' },
  { label: 'Programa', value: 'programa' },
  { label: 'Modalidad', value: 'modalidad' },
  { label: 'Sexo', value: 'sexo' },
];

const selectedColumns = ref(['area']);
const resumenes = ref([]);
const totalGeneral = ref(0);
const loading = ref(false);

const columns = computed(() => [
  ...selectedColumns.value.map((key) => ({
    title: columnOptions.find((option) => option.value === key)?.label ?? key,
    dataIndex: key,
    key,
  })),
  {
    title: 'Total',
    dataIndex: 'total',
    key: 'total',
    align: 'center',
    width: 100,
  },
]);

const getResumen = async () => {
  loading.value = true;

  try {
    const response = await axios.post('/admin/resumen-ingresantes', {
      group_by: selectedColumns.value,
    });

    resumenes.value = response.data.data;
    totalGeneral.value = response.data.total_general;
  } catch {
    notification.error({
      message: 'No se pudo cargar el resumen de ingresantes',
      description: 'Intente nuevamente o contacte al administrador.',
    });
  } finally {
    loading.value = false;
  }
};

watch(selectedColumns, getResumen, { deep: true });
onMounted(getResumen);
</script>
