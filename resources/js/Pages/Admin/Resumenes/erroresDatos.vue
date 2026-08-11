<template>
  <Head title="Errores de Datos" />
  <AuthenticatedLayout>
    <div class="overflow-hidden shadow-sm sm:rounded-lg p-4" style="background: var(--card-bg, #ffffff); border: 1px solid var(--card-border, #e2e8f0); color: var(--card-text, #1e293b); height: calc(100vh - 80px); display: flex; flex-direction: column;">
      <div class="flex justify-between items-center mb-4" style="flex-shrink: 0;">
        <span style="font-size: 1.3rem;">Errores de Datos</span>
        <a-button type="primary" :loading="loading" style="border-radius: 5px; background: #096dd9; border: none;" @click="cargarResumen">
          <template #icon><ReloadOutlined /></template>
          Actualizar
        </a-button>
      </div>

      <!-- Total errores -->
      <div v-if="resumen.length > 0" class="totales-banner" style="flex-shrink: 0;">
        <div class="totales-item">
          <span class="totales-label">Total de errores:</span>
          <span class="totales-value" :style="{ color: totalErrores > 0 ? '#ff4d4f' : '#52c41a' }">{{ totalErrores }}</span>
        </div>
      </div>

      <!-- Tarjetas de resumen con scroll interno -->
      <div class="cards-scroll-container" v-if="resumen.length > 0">
        <div class="cards-grid">
          <div
            v-for="item in resumen"
            :key="item.key"
            class="error-card"
            :class="{ 'card-error': item.cantidad > 0, 'card-ok': item.cantidad === 0 }"
            @click="verDetalle(item)"
            style="cursor: pointer;"
          >
            <div class="card-icon">
              <CheckCircleOutlined v-if="item.cantidad === 0" style="color: #52c41a; font-size: 1.5rem;" />
              <WarningOutlined v-else style="color: #ff4d4f; font-size: 1.5rem;" />
            </div>
            <div class="card-body">
              <div class="card-title">{{ item.titulo }}</div>
              <div class="card-count" :style="{ color: item.cantidad > 0 ? '#ff4d4f' : '#52c41a' }">
                {{ item.cantidad }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="!loading" class="empty-state">
        <p>No hay datos que mostrar.</p>
      </div>
    </div>

    <!-- Modal de detalle -->
    <a-modal
      v-model:open="modalVisible"
      :title="detalleTitulo"
      width="950px"
      :footer="null"
    >
      <div style="max-height: 550px; overflow-y: auto;">
        <div v-if="detalleKey === 'verificacion_reniec' && detalleDatos.length > 0" style="margin-bottom: 12px;">
          <a-button
            type="primary"
            :loading="actualizandoLote"
            :disabled="reniecSeleccionados.length === 0"
            style="border-radius: 5px; background: #096dd9; border: none;"
            @click="actualizarLoteReniec"
          >
            Actualizar seleccionados ({{ reniecSeleccionados.length }})
          </a-button>
        </div>
        <a-table
          :columns="detalleColumns"
          :data-source="detalleDatos"
          :loading="loadingDetalle"
          :pagination="{ pageSize: 20 }"
          size="small"
          row-key="nro_doc"
          :scroll="{ x: (detalleKey === 'sin_pago' || detalleKey === 'verificacion_reniec') ? 900 : undefined }"
          :row-selection="detalleKey === 'verificacion_reniec' ? { selectedRowKeys: reniecSeleccionados, onChange: (keys) => reniecSeleccionados = keys } : undefined"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'accion' && detalleKey === 'sin_pago'">
              <a-button
                type="primary"
                size="small"
                style="border-radius: 5px; background: #096dd9; border: none;"
                @click.stop="abrirRectificar(record)"
              >
                <template #icon><DollarOutlined /></template>
                Corregir
              </a-button>
            </template>
            <template v-if="column.key === 'accion' && detalleKey === 'verificacion_reniec'">
              <a-button
                type="primary"
                size="small"
                :loading="record.actualizando_reniec"
                style="border-radius: 5px; background: #096dd9; border: none;"
                @click.stop="actualizarReniec(record)"
              >
                <template #icon><CheckCircleOutlined /></template>
                Actualizar
              </a-button>
            </template>
          </template>
        </a-table>
      </div>
    </a-modal>

    <!-- Modal Rectificar Pago -->
    <a-modal
      v-model:open="rectificarVisible"
      title="Corregir Pago"
      width="520px"
      :footer="null"
    >
      <div style="margin-top: 12px;">
        <a-form-item label="Postulante">
          <a-input :value="rectificarForm.nombres" disabled />
        </a-form-item>
        <a-form-item label="DNI">
          <a-input :value="rectificarForm.dni" disabled />
        </a-form-item>

        <!-- Pagos encontrados -->
        <div v-if="buscandoPagos" style="text-align: center; padding: 16px;">
          <a-spin size="small" />
          <span style="margin-left: 8px; font-size: 0.85rem; color: var(--card-muted, #64748b);">Buscando pagos disponibles...</span>
        </div>
        <div v-else-if="pagosEncontrados.length > 0" style="margin-bottom: 16px;">
          <div style="font-size: 0.85rem; color: var(--card-muted, #64748b); margin-bottom: 8px;">Pagos encontrados (no registrados):</div>
          <a-table
            :columns="pagosDisponiblesColumns"
            :data-source="pagosEncontrados"
            :pagination="false"
            size="small"
            :row-key="(r, idx) => idx"
          >
            <template #bodyCell="{ column, record: pago }">
              <template v-if="column.key === 'accion'">
                <a-button
                  type="primary"
                  size="small"
                  :loading="pago.operacion === rectificarForm.operacion && rectificarLoading"
                  :disabled="pagoSeleccionado && pago.operacion !== rectificarForm.operacion"
                  style="border-radius: 5px; background: #52c41a; border: none;"
                  @click.stop="seleccionarPago(pago)"
                >
                  {{ pago.operacion === rectificarForm.operacion && pagoSeleccionado ? 'Seleccionado' : 'Seleccionar' }}
                </a-button>
              </template>
            </template>
          </a-table>
        </div>
        <div v-else-if="!buscandoPagos && pagosBuscados" style="margin-bottom: 16px; text-align: center; padding: 8px; color: var(--card-muted, #64748b); font-size: 0.85rem; border: 1px dashed var(--card-border, #e2e8f0); border-radius: 6px;">
          No se encontraron pagos disponibles.
        </div>

        <div v-if="pagoSeleccionado" style="text-align: right; margin-top: 16px;">
          <a-button @click="rectificarVisible = false" style="margin-right: 8px;">Cancelar</a-button>
          <a-button
            type="primary"
            :loading="rectificarLoading"
            style="border-radius: 5px; background: #096dd9; border: none;"
            @click="guardarRectificacion"
          >
            Registrar Pago
          </a-button>
        </div>
      </div>
    </a-modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { ReloadOutlined, CheckCircleOutlined, WarningOutlined, DollarOutlined } from '@ant-design/icons-vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const loading = ref(false);
const loadingDetalle = ref(false);
const resumen = ref([]);
const totalErrores = ref(0);
const modalVisible = ref(false);
const detalleTitulo = ref('');
const detalleDatos = ref([]);
const detalleKey = ref('');

// Rectificar pago
const rectificarVisible = ref(false);
const rectificarLoading = ref(false);
const rectificarForm = ref({
  dni: '',
  nombres: '',
  operacion: '',
  fecha: '',
  monto: '',
  medio: 'Caja',
});
const buscandoPagos = ref(false);
const pagosBuscados = ref(false);
const pagosEncontrados = ref([]);
const pagoSeleccionado = ref(false);
const reniecSeleccionados = ref([]);
const actualizandoLote = ref(false);

const pagosDisponiblesColumns = [
  { title: 'Origen', dataIndex: 'origen', key: 'origen', width: 80 },
  { title: 'N° Operación', dataIndex: 'operacion', key: 'operacion', width: 140 },
  { title: 'Fecha', dataIndex: 'fecha', key: 'fecha', width: 120 },
  { title: 'Monto', dataIndex: 'monto', key: 'monto', width: 100 },
  { title: '', key: 'accion', width: 100 },
];

const detalleColumns = computed(() => {
  const base = [
    { title: 'DNI', dataIndex: 'nro_doc', key: 'nro_doc', width: 100 },
    { title: 'Nombres', dataIndex: 'nombres', key: 'nombres', ellipsis: true },
    { title: 'P. Apellido', dataIndex: 'primer_apellido', key: 'primer_apellido', ellipsis: true },
    { title: 'S. Apellido', dataIndex: 'segundo_apellido', key: 'segundo_apellido', ellipsis: true },
  ];

  const extra = {
    celular_vacio: [{ title: 'Celular', dataIndex: 'celular', key: 'celular', width: 120 }],
    celular_invalido: [{ title: 'Celular', dataIndex: 'celular', key: 'celular', width: 120 }],
    email_vacio: [{ title: 'Email', dataIndex: 'email', key: 'email', ellipsis: true }],
    email_invalido: [{ title: 'Email', dataIndex: 'email', key: 'email', ellipsis: true }],
    sin_fecha_nacimiento: [{ title: 'F. Nacimiento', dataIndex: 'fec_nacimiento', key: 'fec_nacimiento', width: 120 }],
    sin_sexo: [{ title: 'Sexo', dataIndex: 'sexo', key: 'sexo', width: 80 }],
    sin_ubigeo_nacimiento: [{ title: 'Ubigeo Nac.', dataIndex: 'ubigeo_nacimiento', key: 'ubigeo_nacimiento', width: 120 }],
    sin_ubigeo_residencia: [{ title: 'Ubigeo Res.', dataIndex: 'ubigeo_residencia', key: 'ubigeo_residencia', width: 120 }],
    sin_pais: [{ title: 'ID País', dataIndex: 'id_pais', key: 'id_pais', width: 80 }],
    verificacion_reniec: [
      { title: 'Revisado', dataIndex: 'revisado', key: 'revisado', width: 90 },
      { title: 'F. Verificación', dataIndex: 'fecha_verificacion', key: 'fecha_verificacion', width: 130 },
      { title: 'Edad', dataIndex: 'edad', key: 'edad', width: 70 },
    ],
    discapacidad_inconsistente: [
      { title: 'Discapacidad', dataIndex: 'discapacidad', key: 'discapacidad', width: 100 },
      { title: 'Tipo Disc.', dataIndex: 'tipo_discapacidad', key: 'tipo_discapacidad', width: 100 },
    ],
    discapacidad_sin_tipo: [
      { title: 'Discapacidad', dataIndex: 'discapacidad', key: 'discapacidad', width: 100 },
      { title: 'Tipo Disc.', dataIndex: 'tipo_discapacidad', key: 'tipo_discapacidad', width: 100 },
    ],
    extranjero_con_ubigeo: [
      { title: 'Tipo Doc', dataIndex: 'tipo_doc', key: 'tipo_doc', width: 80 },
      { title: 'Ubigeo Nac.', dataIndex: 'ubigeo_nacimiento', key: 'ubigeo_nacimiento', width: 110 },
      { title: 'Cód. País', dataIndex: 'codigo', key: 'codigo', width: 90 },
    ],
    peruano_sin_ubigeo_nac: [
      { title: 'Tipo Doc', dataIndex: 'tipo_doc', key: 'tipo_doc', width: 80 },
      { title: 'Ubigeo Nac.', dataIndex: 'ubigeo_nacimiento', key: 'ubigeo_nacimiento', width: 110 },
    ],
    sin_tipo_doc: [{ title: 'Tipo Doc', dataIndex: 'tipo_doc', key: 'tipo_doc', width: 80 }],
    menor_edad: [
      { title: 'F. Nacimiento', dataIndex: 'fec_nacimiento', key: 'fec_nacimiento', width: 120 },
      { title: 'Edad', dataIndex: 'edad', key: 'edad', width: 70 },
    ],
    sin_pago: [{ title: 'Programa', dataIndex: 'programa', key: 'programa', ellipsis: true }],
  };

  const cols = [...base, ...(extra[detalleKey.value] || [])];
  cols.push({ title: 'Error', dataIndex: 'error', key: 'error', ellipsis: true });

  if (detalleKey.value === 'sin_pago' || detalleKey.value === 'verificacion_reniec') {
    cols.push({
      title: 'Acción',
      key: 'accion',
      width: 110,
      fixed: 'right',
    });
  }

  return cols;
});

const cargarResumen = async () => {
  loading.value = true;
  try {
    const res = await axios.post('/admin/errores-datos', {});
    if (res.data.estado) {
      resumen.value = res.data.resumen;
      totalErrores.value = res.data.total_errores;
    }
  } catch (error) {
    notification.error({ message: 'Error', description: 'No se pudieron cargar los errores.' });
  } finally {
    loading.value = false;
  }
};

const verDetalle = async (item) => {
  if (item.cantidad === 0) return;
  modalVisible.value = true;
  detalleTitulo.value = item.titulo + ` (${item.cantidad})`;
  detalleKey.value = item.key;
  loadingDetalle.value = true;
  detalleDatos.value = [];

  try {
    const res = await axios.post('/admin/errores-datos', { tipo: item.key });
    if (res.data.estado) {
      detalleDatos.value = res.data.datos;
    }
  } catch (error) {
    notification.error({ message: 'Error', description: 'No se pudo cargar el detalle.' });
  } finally {
    loadingDetalle.value = false;
  }
};

const abrirRectificar = async (record) => {
  rectificarForm.value = {
    dni: record.nro_doc,
    nombres: `${record.nombres} ${record.primer_apellido} ${record.segundo_apellido}`,
    operacion: '',
    fecha: '',
    monto: '',
    medio: 'Caja',
  };
  pagosEncontrados.value = [];
  pagosBuscados.value = false;
  pagoSeleccionado.value = false;
  rectificarVisible.value = true;

  buscandoPagos.value = true;
  try {
    const res = await axios.get(`/admin/errores-datos/pagos/${record.nro_doc}`);
    if (res.data.estado) {
      pagosEncontrados.value = res.data.pagos;
    }
  } catch (e) {
    // silenciar
  } finally {
    buscandoPagos.value = false;
    pagosBuscados.value = true;
  }
};

const seleccionarPago = (pago) => {
  rectificarForm.value.operacion = pago.operacion || '';
  rectificarForm.value.fecha = pago.fecha ? String(pago.fecha).substring(0, 10) : '';
  rectificarForm.value.monto = pago.monto || '';
  rectificarForm.value.medio = pago.origen === 'Banco' ? 'Banco' : 'Caja';
  pagoSeleccionado.value = true;
};

const actualizarReniec = async (record) => {
  record.actualizando_reniec = true;
  try {
    const res = await axios.post('/admin/actualizar-lista-reniec', { dnis: [record.nro_doc] });
    const procesados = res.data.procesados || 0;
    if (procesados > 0) {
      notification.success({ message: 'RENIEC actualizado', description: 'Los datos del postulante fueron actualizados.' });
      const item = resumen.value.find(r => r.key === detalleKey.value);
      if (item) await verDetalle(item);
      await cargarResumen();
    } else {
      notification.warning({ message: 'Sin respuesta', description: 'El servicio RENIEC no respondió o se alcanzó el límite diario.' });
    }
  } catch (error) {
    notification.error({ message: 'Error', description: 'No se pudo actualizar con RENIEC.' });
  } finally {
    record.actualizando_reniec = false;
  }
};

const actualizarLoteReniec = async () => {
  if (reniecSeleccionados.value.length === 0) return;
  actualizandoLote.value = true;
  try {
    const res = await axios.post('/admin/actualizar-lista-reniec', { dnis: reniecSeleccionados.value });
    const procesados = res.data.procesados || 0;
    if (procesados > 0) {
      notification.success({ message: 'RENIEC actualizado', description: `${procesados} postulante(s) actualizado(s).` });
    } else {
      notification.warning({ message: 'Sin respuesta', description: 'El servicio RENIEC no respondió o se alcanzó el límite diario.' });
    }
    reniecSeleccionados.value = [];
    const item = resumen.value.find(r => r.key === detalleKey.value);
    if (item) await verDetalle(item);
    await cargarResumen();
  } catch (error) {
    notification.error({ message: 'Error', description: 'No se pudo actualizar con RENIEC.' });
  } finally {
    actualizandoLote.value = false;
  }
};

const guardarRectificacion = async () => {
  const f = rectificarForm.value;
  if (!f.operacion || !f.fecha || !f.monto) {
    notification.warning({ message: 'Campos requeridos', description: 'Complete operación, fecha y monto.' });
    return;
  }
  rectificarLoading.value = true;
  try {
    const res = await axios.post('/insertar-pago', {
      pag: {
        dni: f.dni,
        operacion: f.operacion,
        fecha: f.fecha,
        monto: f.monto,
        medio: f.medio,
      },
    });
    notification.success({ message: 'Pago registrado', description: 'El pago se registró correctamente.' });
    rectificarVisible.value = false;
    // Refrescar detalle y resumen
    const item = resumen.value.find(r => r.key === detalleKey.value);
    if (item) await verDetalle(item);
    await cargarResumen();
  } catch (error) {
    notification.error({ message: 'Error', description: 'No se pudo registrar el pago.' });
  } finally {
    rectificarLoading.value = false;
  }
};

cargarResumen();
</script>

<style scoped>
.cards-scroll-container {
  flex: 1;
  overflow-y: auto;
  padding-right: 6px;
}
.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
  gap: 14px;
}
.totales-banner {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}
.totales-item {
  background: var(--content-bg, #f8fafc);
  padding: 10px 20px;
  border-radius: 10px;
  border: 1px solid var(--card-border, #e2e8f0);
}
.totales-label {
  font-size: 0.85rem;
  color: var(--card-muted, #64748b);
  margin-right: 8px;
}
.totales-value {
  font-size: 1.4rem;
  font-weight: 700;
}
.error-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  border-radius: 12px;
  border: 1px solid var(--card-border, #e2e8f0);
  background: var(--content-bg, #f8fafc);
  transition: all 0.2s ease;
}
.error-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.card-error {
  border-left: 4px solid #ff4d4f;
}
.card-ok {
  border-left: 4px solid #52c41a;
}
.card-icon { flex-shrink: 0; }
.card-body { flex: 1; min-width: 0; }
.card-title {
  font-size: 0.82rem;
  color: var(--card-muted, #64748b);
  margin-bottom: 4px;
}
.card-count {
  font-size: 1.6rem;
  font-weight: 700;
}
.empty-state {
  text-align: center;
  padding: 40px;
  color: var(--card-muted, #64748b);
}
</style>

<style>
/* ====== MODO OSCURO / HÍBRIDO: TABLA (modal renderiza en body) ====== */
body.theme-dark .ant-table,
body.theme-hybrid .ant-table {
    background: var(--card-bg) !important;
    color: var(--card-text) !important;
}
body.theme-dark .ant-table-thead > tr > th,
body.theme-hybrid .ant-table-thead > tr > th {
    background: var(--table-header-bg) !important;
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
}
body.theme-dark .ant-table-tbody > tr > td,
body.theme-hybrid .ant-table-tbody > tr > td {
    color: var(--card-text) !important;
    border-bottom: 1px solid var(--card-border) !important;
    background: var(--card-bg) !important;
}
body.theme-dark .ant-table-tbody > tr:hover > td,
body.theme-hybrid .ant-table-tbody > tr:hover > td {
    background: var(--hover-bg) !important;
}
body.theme-dark .ant-table-tbody > tr:nth-child(even) > td,
body.theme-hybrid .ant-table-tbody > tr:nth-child(even) > td {
    background: var(--row-even) !important;
}

/* Modal oscuro */
body.theme-dark .ant-modal-content,
body.theme-hybrid .ant-modal-content {
    background: var(--card-bg) !important;
}
body.theme-dark .ant-modal-header,
body.theme-hybrid .ant-modal-header {
    background: var(--card-bg) !important;
}
body.theme-dark .ant-modal-title,
body.theme-hybrid .ant-modal-title {
    color: var(--card-text) !important;
}
body.theme-dark .ant-modal-close-icon,
body.theme-hybrid .ant-modal-close-icon {
    color: var(--card-muted) !important;
}

/* Scrollbar */
.cards-scroll-container::-webkit-scrollbar { width: 9px; }
.cards-scroll-container::-webkit-scrollbar-track { background: var(--content-bg, #f1f5f9); border-radius: 10px; }
.cards-scroll-container::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
.cards-scroll-container::-webkit-scrollbar-thumb:hover { background: #555; }
</style>
