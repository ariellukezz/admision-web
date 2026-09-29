<!--
  ============================================================================
  Seguimiento del postulante — consulta por DNI.
  ----------------------------------------------------------------------------
  Pantalla de mostrador: se usa de pie, a menudo proyectada, y su único trabajo
  es responder «¿en qué paso estoy y tengo algo pendiente?». Por eso el estado
  manda sobre la decoración: la observación, si existe, aparece antes que la
  línea de pasos, porque es lo único que exige acción.
  ============================================================================
-->
<template>
  <Head title="Seguimiento del postulante" />
  <Layout>
    <div class="seg rev-scope">

      <!-- Consulta -------------------------------------------------------- -->
      <div class="seg-query">
        <label class="seg-query-label" for="seg-dni">Documento de identidad</label>
        <div class="seg-query-field" :class="{ 'is-focused': focused, 'is-busy': loading }">
          <RevIcon name="search" size="md" class="seg-query-icon" />
          <input
            id="seg-dni"
            ref="myInput"
            v-model="dni"
            type="text"
            inputmode="numeric"
            :maxlength="dniMaxLength"
            placeholder="Ingrese los 8 dígitos del DNI"
            autocomplete="off"
            @keyup.enter="handleEnter"
            @focus="focused = true"
            @blur="focused = false"
          />
          <RevIcon v-if="loading" name="loader" size="sm" spin class="seg-query-busy" />
          <button v-else-if="dni" type="button" class="seg-query-clear" aria-label="Limpiar" @click="handleEnter">
            <RevIcon name="close" size="xs" />
          </button>
        </div>
        <p class="seg-query-hint">La consulta se lanza automáticamente al completar los 8 dígitos.</p>
      </div>

      <!-- Estado vacío ----------------------------------------------------- -->
      <RevEmptyState
        v-if="currentStep < 0 && !loading"
        icon="user"
        title="Esperando una consulta"
        description="Introduce el DNI del postulante para ver en qué punto del proceso de admisión se encuentra."
      />

      <template v-else-if="currentStep >= 0">
        <!-- Identidad ----------------------------------------------------- -->
        <div class="seg-person">
          <RevAvatar :name="datos.nombres || 'Postulante'" size="lg" tone="accent" />
          <div class="seg-person-copy">
            <span class="rev-eyebrow">Postulante</span>
            <h2 class="seg-person-name">{{ datos.nombres || '—' }}</h2>
          </div>
          <RevBadge :tone="observacion ? 'warning' : 'success'" dot :pulse="!!observacion">
            {{ observacion ? 'Con observación' : 'Sin observaciones' }}
          </RevBadge>
        </div>

        <!-- Observación: lo único que exige acción va primero -------------- -->
        <RevBanner
          v-if="observacion"
          tone="warning"
          title="Hay una observación pendiente"
        >{{ observacion }}</RevBanner>

        <!-- Línea de pasos ------------------------------------------------- -->
        <div class="seg-steps" role="list">
          <div
            v-for="(step, index) in steps"
            :key="index"
            class="seg-step"
            :class="{
              'is-done': index < currentStep,
              'is-current': index === currentStep,
              'is-todo': index > currentStep,
              'is-flagged': observacion && index === currentStep,
            }"
            role="listitem"
          >
            <span class="seg-step-line" aria-hidden="true" />
            <span class="seg-step-mark">
              <RevIcon v-if="observacion && index === currentStep" name="alert" size="sm" />
              <RevIcon v-else-if="index <= currentStep" name="check" size="sm" />
              <span v-else class="rev-num">{{ index + 1 }}</span>
            </span>
            <span class="seg-step-name">{{ step.description }}</span>
            <span class="seg-step-state">
              {{ index < currentStep ? 'Completado'
                 : index === currentStep ? (observacion ? 'Observado' : 'En curso')
                 : 'Pendiente' }}
            </span>
          </div>
        </div>
      </template>
    </div>
  </Layout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import Layout from '@/Layouts/LayoutPasos.vue'
import { ref, reactive, onMounted, watch } from 'vue'
import RevIcon from '@/Components/Revisor/RevIcon.vue'
import RevBadge from '@/Components/Revisor/RevBadge.vue'
import RevBanner from '@/Components/Revisor/RevBanner.vue'
import RevAvatar from '@/Components/Revisor/RevAvatar.vue'
import RevEmptyState from '@/Components/Revisor/RevEmptyState.vue'

const steps = reactive([
  { description: 'Preinscripción' },
  { description: 'Examen vocacional' },
  { description: 'Inscripción' },
  { description: 'Examen' },
  { description: 'Resultados' },
])

const myInput = ref(null)
const focused = ref(false)
const loading = ref(false)
const currentStep = ref(-1)
const observacion = ref('')
const dni = ref('')
const dniMaxLength = 12

const datos = ref({
  dni_postulante: '',
  id_proceso: '',
  avance: '',
  nombres: '',
  id_usuario: 1,
})

function handleEnter() {
  myInput.value?.focus()
  dni.value = ''
  currentStep.value = -1
  observacion.value = ''
}

const getDatos = async () => {
  loading.value = true
  try {
    const res = await axios.post('/get-avance-postulante2', { dni: dni.value })
    datos.value = res.data.datos
    currentStep.value = res.data.datos.avance - 1
    observacion.value = res.data.datos.observacion || ''
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

watch(dni, () => {
  if (dni.value.length === 8) getDatos()
})

onMounted(() => { myInput.value?.focus() })
</script>

<style scoped>
.seg {
  display: flex; flex-direction: column; gap: var(--rev-s-7);
  width: 100%; max-width: 760px; margin: 0 auto;
  padding: var(--rev-s-9) var(--rev-s-6);
}

/* Consulta ---------------------------------------------------------------- */
.seg-query { display: flex; flex-direction: column; gap: 6px; }
.seg-query-label { font-size: var(--rev-fs-sm); font-weight: 600; color: var(--rev-ink-2); }
.seg-query-field {
  display: flex; align-items: center; gap: 10px;
  height: 46px; padding: 0 12px;
  background: var(--rev-surface);
  border: 1px solid var(--rev-line-strong);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
  transition: border-color var(--rev-t-fast) var(--rev-ease), box-shadow var(--rev-t-fast) var(--rev-ease);
}
.seg-query-field.is-focused { border-color: var(--rev-primary-500); box-shadow: var(--rev-ring); }
.seg-query-icon { color: var(--rev-ink-4); flex: none; }
.seg-query-busy { color: var(--rev-primary-600); flex: none; }
.seg-query-field input {
  flex: 1 1 auto; min-width: 0;
  border: 0; outline: 0; background: transparent;
  font-family: var(--rev-font); font-size: var(--rev-fs-xl); font-weight: 600;
  letter-spacing: .06em; color: var(--rev-ink);
  font-variant-numeric: tabular-nums;
}
.seg-query-field input::placeholder { font-size: var(--rev-fs-md); font-weight: 500; letter-spacing: 0; color: var(--rev-ink-4); }
.seg-query-clear {
  flex: none; display: grid; place-items: center; width: 22px; height: 22px;
  border: 0; border-radius: var(--rev-r-sm);
  background: var(--rev-n-100); color: var(--rev-ink-3); cursor: pointer;
  transition: background var(--rev-t-fast) var(--rev-ease);
}
.seg-query-clear:hover { background: var(--rev-n-200); color: var(--rev-ink); }
.seg-query-hint { margin: 0; font-size: var(--rev-fs-sm); color: var(--rev-ink-4); }

/* Identidad --------------------------------------------------------------- */
.seg-person {
  display: flex; align-items: center; gap: var(--rev-s-5);
  padding: var(--rev-s-5) var(--rev-s-6);
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
  animation: rev-rise var(--rev-t-slow) var(--rev-ease-out) both;
}
.seg-person-copy { flex: 1 1 auto; min-width: 0; }
.seg-person-name {
  margin: 1px 0 0; font-size: var(--rev-fs-2xl); font-weight: 660;
  letter-spacing: -.02em; color: var(--rev-ink); line-height: 1.2;
  text-transform: capitalize;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

/* Pasos ------------------------------------------------------------------- */
.seg-steps {
  display: flex; flex-direction: column;
  padding: var(--rev-s-6) var(--rev-s-7);
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
}
.seg-step {
  position: relative;
  display: grid; grid-template-columns: 28px 1fr auto;
  align-items: center; gap: var(--rev-s-5);
  padding: 11px 0;
}
.seg-step-line {
  position: absolute; left: 13px; top: 30px; bottom: -11px;
  width: 2px; background: var(--rev-line);
}
.seg-step:last-child .seg-step-line { display: none; }
.seg-step.is-done .seg-step-line { background: var(--rev-success); }

.seg-step-mark {
  display: grid; place-items: center;
  width: 28px; height: 28px; border-radius: 50%;
  font-size: var(--rev-fs-sm); font-weight: 680;
  border: 1px solid var(--rev-line); background: var(--rev-n-50); color: var(--rev-ink-4);
  z-index: 1;
  transition: background var(--rev-t-base) var(--rev-ease), color var(--rev-t-base) var(--rev-ease), border-color var(--rev-t-base) var(--rev-ease);
}
.seg-step.is-done .seg-step-mark { background: var(--rev-success); border-color: var(--rev-success); color: #fff; }
.seg-step.is-current .seg-step-mark {
  background: var(--rev-primary-600); border-color: var(--rev-primary-600); color: #fff;
  box-shadow: 0 0 0 4px var(--rev-primary-100);
}
.seg-step.is-flagged .seg-step-mark {
  background: var(--rev-warning); border-color: var(--rev-warning); color: #fff;
  box-shadow: 0 0 0 4px var(--rev-warning-bg);
}

.seg-step-name { font-size: var(--rev-fs-lg); font-weight: 560; color: var(--rev-ink-3); }
.seg-step.is-done .seg-step-name,
.seg-step.is-current .seg-step-name { color: var(--rev-ink); font-weight: 620; }
.seg-step.is-todo .seg-step-name { color: var(--rev-ink-4); }

.seg-step-state {
  font-size: var(--rev-fs-sm); font-weight: 620;
  letter-spacing: .01em; color: var(--rev-ink-4);
}
.seg-step.is-done .seg-step-state { color: var(--rev-success); }
.seg-step.is-current .seg-step-state { color: var(--rev-primary-700); }
.seg-step.is-flagged .seg-step-state { color: var(--rev-warning-ink); }

@media (max-width: 600px) {
  .seg { padding: var(--rev-s-7) var(--rev-s-5); }
  .seg-person { flex-wrap: wrap; }
  .seg-step { grid-template-columns: 28px 1fr; }
  .seg-step-state { grid-column: 2; font-size: var(--rev-fs-xs); }
}
</style>
