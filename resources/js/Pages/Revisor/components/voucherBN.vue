<template>
<div class="vch-wrap">
  <div class="vch-header">
    <a-button class="vch-btn-add" @click="modalPagos = true">+ Agregar Pagos</a-button>
  </div>

  <a-table :dataSource="pagos" :columns="colcomprobantes" :pagination="false" size="small" class="vch-table">
    <template #bodyCell="{ column, record }">
      <template v-if="column.dataIndex === 'proceso'">
        <a-tag v-if="record.id_modalidad_proceso === 3" color="red">{{ record.proceso }}</a-tag>
        <a-tag v-if="record.id_modalidad_proceso === 2" color="orange">{{ record.proceso }}</a-tag>
        <a-tag v-if="record.id_modalidad_proceso === 1" color="blue">{{ record.proceso }}</a-tag>
      </template>
    </template>
  </a-table>

  <a-modal v-model:visible="modalPagos" width="900px" title="Agregar Pagos" class="vch-modal">
    <div class="vch-modal-section">
      <h4 class="vch-modal-title">Banco de la Nación</h4>
      <a-table :dataSource="comprobantesBN" :columns="colVouchers" :pagination="false" size="small" class="vch-table">
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'opcion'">
            <div v-if="record.status">
              <a-button v-if="record.status === 0" class="vch-btn-select" @click="verificarBN(record)">Seleccionar</a-button>
              <a-button v-if="record.status === 1" class="vch-btn-selected" @click="verificarBN(record)">Seleccionado</a-button>
            </div>
            <div v-else>
              <a-button class="vch-btn-select" @click="verificarBN(record)">Seleccionar</a-button>
            </div>
          </template>
        </template>
      </a-table>
    </div>

    <div class="vch-modal-section">
      <h4 class="vch-modal-title">Caja</h4>
      <a-table :dataSource="comprobantesCaja" :columns="colVoucherCaja" :pagination="false" size="small" class="vch-table">
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'paymentAmount'">
            <strong>S/ {{ record.paymentAmount }}</strong>
          </template>
          <template v-if="column.dataIndex === 'opcion'">
            <a-button v-if="record.estado === 1" class="vch-btn-selected" @click="verificarCaja(record)">Seleccionado</a-button>
            <a-button v-else class="vch-btn-select" @click="verificarCaja(record)">Seleccionar</a-button>
          </template>
        </template>
      </a-table>
    </div>

    <template #footer>
      <a-button class="vch-btn-primary" @click="modalPagos = false">Aceptar</a-button>
    </template>
  </a-modal>
</div>
</template>

<script setup>
import { ref, defineProps, watch } from 'vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const modalPagos = ref(false);
const comprobantes = ref([]);
const props = defineProps(['dni', 'proceso']);

const pagos = ref([]);
const comprobantesCaja = ref([]);
const getCaja = async () => {
  let res = await axios.get('/get-pago-caja/'+props.dni);
  comprobantesCaja.value = res.data;
};

const comprobantesBN = ref([]);
const getBN = async () => {
  let res = await axios.get('/get-pago-BN/'+props.dni);
  comprobantesBN.value = res.data;
};


const verificar = async (comp) => {
  let res = await axios.post('verificar-comprobante-proceso', { comp });
  notificacion(res.data.type, res.data.titulo, res.data.mensaje);
  getPagosGeneral();
};

const verificarCaja = async (comp) => {
  let pag = {
    dni: props.dni,
    operacion: comp.paymentTitle,
    fecha: comp.paymentDatetime,
    monto: comp.paymentAmount,
    medio: 'Caja'
  }

  if (comp.estado == 1) {
    let res = await axios.get('/eliminar-pago/' + pag.dni + '/' + pag.operacion);
    comp.estado = 0;
    getPagosGeneral();
    getBN();
    getCaja();
    notificacion('info', 'PAGO REMOVIDO', 'Pago eliminado correctamente');
  } else {
    comp.estado = 1;
    let res = await axios.post('/insertar-pago', { pag });
    getPagosGeneral();
    getBN();
    getCaja();
    notificacion(res.data.type, res.data.titulo, res.data.mensaje);
  }
};



const verificarBN = async (comp) => {
  let pag = {
    dni: props.dni,
    operacion: comp.paymentId,
    fecha: comp.date,
    monto: comp.amount,
    medio: 'BN'
  }

  if (comp.status == 1) {
    let res = await axios.get('/eliminar-pago/' + pag.dni + '/' + pag.operacion);
    comp.status = 0;
    getPagosGeneral();
    getBN();
    getCaja();
    notificacion('info', 'PAGO REMOVIDO', 'Pago eliminado correctamente');
  } else {
    comp.status = 1;
    let res = await axios.post('/insertar-pago', { pag });
    getPagosGeneral();
    getBN();
    getCaja();
    notificacion(res.data.type, res.data.titulo, res.data.mensaje);
  }
};

const getPagosGeneral = async (comp) => {
  let res = await axios.get('/get-pagos-dni/'+props.dni);
  pagos.value = res.data.data;
};

watch(() => props.dni, async (newDni) => {
  if (newDni && newDni.length === 8 && /^[0-9]+$/.test(newDni)) {
    await getCaja();
    await getBN();
    await getPagosGeneral();    
  }
}, { immediate: true });

const colcomprobantes =  [
  { title: 'N° Operación', dataIndex: 'operacion'},
  { title: 'Nombres', dataIndex: 'nombres'},
  { title: 'Fecha', dataIndex: 'fecha'},
  { title: 'Concepto', dataIndex: 'concepto' },
  { title: 'Monto', dataIndex: 'monto' }
];

const colVouchers =  [
  { title: 'N° Operación', dataIndex: 'paymentId'},
  { title: 'Nombres', dataIndex: 'client'},
  { title: 'Fecha', dataIndex: 'date'},
  { title: 'Concepto', dataIndex: 'description' },
  { title: 'Monto', dataIndex: 'amount' },
  { title: '', dataIndex: 'opcion', align:'center' },
];

const colVoucherCaja =  [
  { title: 'N° Operación', dataIndex: 'paymentTitle'},
  { title: 'Fecha', dataIndex: 'paymentDatetime'},
  { title: 'Concepto', dataIndex: 'concepto' },
  { title: 'Monto', dataIndex: 'paymentAmount', align:'center' },
  { title: '', dataIndex: 'opcion', align:'center' },
];


const temp = ref([
  {
    "paymentId": "2177229",
    "document": "000000070080972",
    "code": "000000000000000",
    "client": "TICONA PONGO TANIA ROSARIO DEL",
    "universityId": "000000000000000",
    "description": "00000028",
    "amount": "21.00",
    "date": "2024-02-27 14:20",
    "status": "0"
  }]);

const notificacion = (type, titulo, mensaje) => { notification[type]({ message: titulo, description: mensaje, }); };
</script>

<style scoped>
.vch-wrap { width: 100%; }
.vch-header { display: flex; justify-content: flex-end; margin-bottom: 12px; }
.vch-btn-add { background: #3b82f6 !important; border: none !important; color: #fff !important; font-weight: 600; border-radius: 8px; }
.vch-btn-add:hover { background: #2563eb !important; }

.vch-modal-section { margin-bottom: 1.25rem; }
.vch-modal-title { font-size: .875rem; font-weight: 700; color: #1e293b; margin: 0 0 .625rem; padding-bottom: .5rem; border-bottom: 1px solid #f1f5f9; }

.vch-btn-select { background: #eff6ff !important; border: 1px solid #3b82f6 !important; color: #3b82f6 !important; font-weight: 600; border-radius: 6px; font-size: .75rem; }
.vch-btn-select:hover { background: #dbeafe !important; }
.vch-btn-selected { background: #ef4444 !important; border: none !important; color: #fff !important; font-weight: 600; border-radius: 6px; font-size: .75rem; }
.vch-btn-selected:hover { background: #dc2626 !important; }

.vch-btn-primary { background: #3b82f6 !important; border: none !important; color: #fff !important; font-weight: 600; border-radius: 8px; }
.vch-btn-primary:hover { background: #2563eb !important; }
</style>
