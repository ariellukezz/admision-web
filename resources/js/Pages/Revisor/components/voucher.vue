<!--
  Comprobantes de pago — un comprobante es un objeto físico, así que aquí la
  tarjeta sí está justificada: reproduce el recibo. El estado (verificado o no)
  se lee por la franja lateral y la marca, nunca sólo por el fondo.
-->
<template>
  <div class="vc">
    <button
      v-for="item in comprobantes"
      :key="item.id"
      type="button"
      class="vc-item"
      :class="{ 'is-ok': item.verificado === 1 }"
      :aria-pressed="item.verificado === 1"
      @click="verificar(item.id, item.verificado)"
    >
      <header class="vc-top">
        <span class="vc-code rev-mono">{{ (item.codigo || '··').slice(-2) }}</span>
        <div class="vc-top-right">
          <span class="vc-date rev-num">{{ (item.fecha || '').split('-').reverse().join('/') }}</span>
          <span class="vc-op rev-num">Op. {{ item.nro_operacion }}</span>
        </div>
      </header>

      <footer class="vc-bottom">
        <div class="vc-person">
          <span class="vc-dni rev-mono">{{ item.ndoc_postulante }}</span>
          <span class="vc-name">{{ item.nombres }} {{ item.primer_apellido }} {{ item.segundo_apellido }}</span>
        </div>
        <span class="vc-amount rev-num">S/ {{ Number(item.monto || 0).toFixed(2) }}</span>
      </footer>

      <span class="vc-state">
        <RevIcon :name="item.verificado === 1 ? 'check-circle' : 'clock'" size="xs" />
        {{ item.verificado === 1 ? 'Verificado' : 'Sin verificar' }}
      </span>
    </button>

    <p v-if="!comprobantes.length" class="vc-empty">Sin comprobantes registrados para este postulante.</p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import RevIcon from '@/Components/Revisor/RevIcon.vue'

const comprobantes = ref([])

const props = defineProps({
  dni: { type: String, default: '' },
  proceso: { type: String, default: '' },
})

const getComprobantes = async () => {
  const res = await axios.post('get-comprobantes', { dni: props.dni })
  comprobantes.value = res.data.datos
}

const verificar = async (id, estado) => {
  await axios.post('verificar-comprobante', { id, estado: !estado })
  getComprobantes()
}

/* El componente antiguo definía la carga pero nunca la invocaba: la rejilla
   quedaba siempre vacía. Se mantiene el mismo endpoint y el mismo contrato,
   sólo se dispara cuando el DNI está completo. */
watch(
  () => props.dni,
  (dni) => { if (dni && String(dni).length === 8) getComprobantes() },
  { immediate: true }
)
</script>

<style scoped>
.vc { display: grid; grid-template-columns: repeat(auto-fill, minmax(268px, 1fr)); gap: var(--rev-s-5); }

.vc-item {
  position: relative;
  display: flex; flex-direction: column; gap: var(--rev-s-6);
  padding: var(--rev-s-6) var(--rev-s-6) 34px;
  text-align: left; cursor: pointer;
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-left: 3px solid var(--rev-n-300);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
  font-family: var(--rev-font);
  transition: border-color var(--rev-t-base) var(--rev-ease), box-shadow var(--rev-t-base) var(--rev-ease);
}
.vc-item:hover { box-shadow: var(--rev-sh-sm); border-color: var(--rev-line-strong); border-left-color: var(--rev-primary-500); }
.vc-item:focus-visible { outline: none; box-shadow: var(--rev-ring); }
.vc-item.is-ok { border-left-color: var(--rev-success); background: var(--rev-success-bg); }

.vc-top { display: flex; align-items: flex-start; justify-content: space-between; gap: var(--rev-s-5); }
.vc-code {
  display: grid; place-items: center;
  width: 38px; height: 38px; flex: none;
  border: 1px solid var(--rev-line-strong); border-radius: var(--rev-r-md);
  background: var(--rev-surface);
  font-size: var(--rev-fs-xl); font-weight: 700; color: var(--rev-ink);
}
.vc-top-right { display: flex; flex-direction: column; align-items: flex-end; gap: 1px; }
.vc-date { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); }
.vc-op { font-size: var(--rev-fs-lg); font-weight: 680; color: var(--rev-ink); letter-spacing: -.01em; }

.vc-bottom { display: flex; align-items: flex-end; justify-content: space-between; gap: var(--rev-s-5); }
.vc-person { display: flex; flex-direction: column; min-width: 0; line-height: 1.3; }
.vc-dni { font-size: var(--rev-fs-sm); font-weight: 650; color: var(--rev-ink-2); }
.vc-name {
  font-size: var(--rev-fs-sm); color: var(--rev-ink-3);
  text-transform: capitalize;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.vc-amount { font-size: var(--rev-fs-2xl); font-weight: 660; letter-spacing: -.02em; color: var(--rev-ink); flex: none; }

.vc-state {
  position: absolute; left: var(--rev-s-6); bottom: 10px;
  display: inline-flex; align-items: center; gap: 4px;
  font-size: var(--rev-fs-2xs); font-weight: 680;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-ink-4);
}
.vc-item.is-ok .vc-state { color: var(--rev-success-ink); }

.vc-empty { grid-column: 1 / -1; margin: 0; padding: var(--rev-s-7); text-align: center; font-size: var(--rev-fs-md); color: var(--rev-ink-4); }
</style>
