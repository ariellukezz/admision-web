<!--
  ============================================================================
  Revisión de documentos por requisitos (vista clásica).
  ----------------------------------------------------------------------------
  Vista dividida: a la izquierda el checklist de requisitos —lo que se decide—,
  a la derecha el documento —lo que se mira—. Antes ambos competían por el
  mismo ancho; ahora el visor manda, porque leer el documento es el trabajo.
  La barra de acción es fija: guardar nunca obliga a desplazarse.
  ============================================================================
-->
<template>
  <Head title="Revisión de documentos" />
  <AuthenticatedLayout pagina="Revisión de documentos">
    <div class="doc">

      <!-- Búsqueda -------------------------------------------------------- -->
      <RevToolbar>
        <template #lead>
          <span class="rev-label doc-lead"><RevIcon name="search" size="sm" /> Postulante</span>
        </template>

        <a-auto-complete
          v-model:value="dniseleccionado"
          :options="postulantes"
          class="doc-auto"
          @select="onSelect"
          @search="onSearch"
        >
          <a-input ref="dniInput" v-model:value="dni" placeholder="Buscar por DNI o nombre…" allow-clear />
          <template #option="{ value: val, label: lab }">
            <div class="doc-option">
              <span class="doc-option-dni rev-mono">{{ val }}</span>
              <span class="doc-option-name">{{ lab }}</span>
            </div>
          </template>
        </a-auto-complete>

        <template #trail>
          <RevBadge v-if="dniSeleccionadoValido" tone="accent" icon="user">
            DNI {{ dniseleccionado }}
          </RevBadge>
        </template>
      </RevToolbar>

      <!-- Sin selección --------------------------------------------------- -->
      <RevPanel v-if="!dniSeleccionadoValido" flush>
        <RevEmptyState
          icon="user"
          title="Selecciona un postulante"
          description="Busca por DNI o por nombre para cargar sus requisitos y revisar los documentos presentados."
        />
      </RevPanel>

      <!-- Vista dividida --------------------------------------------------- -->
      <div v-else class="doc-split">

        <!-- Checklist -->
        <RevPanel
          title="Requisitos"
          :description="`${checkedList.length} de ${requisitos.length} marcados`"
          class="doc-check"
        >
          <template #actions>
            <RevButton variant="ghost" size="sm" @click="onCheckAllChange({ target: { checked: !checkAll } })">
              {{ checkAll ? 'Desmarcar todo' : 'Marcar todo' }}
            </RevButton>
          </template>

          <RevMeter
            :value="checkedList.length"
            :max="requisitos.length || 1"
            :tone="checkedList.length === requisitos.length && requisitos.length > 0 ? 'success' : 'accent'"
            class="doc-check-meter"
          />

          <ul class="doc-check-list">
            <li v-for="option in requisitos" :key="option.value">
              <label class="doc-check-item" :class="{ 'is-on': checkedList.includes(option.value) }">
                <input
                  type="checkbox"
                  :value="option.value"
                  :checked="checkedList.includes(option.value)"
                  @change="toggleRequisito(option.value)"
                />
                <span class="doc-check-box"><RevIcon name="check" size="xs" /></span>
                <span class="doc-check-label">{{ option.label }}</span>
              </label>
            </li>
          </ul>

          <RevEmptyState v-if="!requisitos.length" compact title="Sin requisitos configurados" />

          <template #footer>
            <span class="rev-meta">Los cambios se aplican al guardar</span>
            <RevButton variant="primary" icon="check" :loading="guardando" @click="save">Guardar requisitos</RevButton>
          </template>
        </RevPanel>

        <!-- Visor -->
        <RevPanel flush class="doc-viewer">
          <template #header>
            <div class="doc-tabs" role="tablist">
              <button
                v-for="t in tabs"
                :key="t.key"
                type="button"
                role="tab"
                class="doc-tab"
                :class="{ 'is-active': activeKey === t.key }"
                :aria-selected="activeKey === t.key"
                @click="activeKey = t.key"
              >
                <RevIcon :name="t.icon" size="sm" />{{ t.label }}
              </button>
            </div>
          </template>

          <div class="doc-frame">
            <Vouchers v-if="activeKey === '2'" :dni="dniseleccionado" class="doc-vouchers" />
            <iframe
              v-else
              :key="activeKey"
              :src="urlActual"
              title="Documento del postulante"
              @load="frameLoading = false"
            />
          </div>
        </RevPanel>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue'
import { watch, computed, ref } from 'vue'
import { notification, message } from 'ant-design-vue'
import axios from 'axios'
import Vouchers from './components/voucher.vue'
import RevToolbar from '@/Components/Revisor/RevToolbar.vue'
import RevPanel from '@/Components/Revisor/RevPanel.vue'
import RevButton from '@/Components/Revisor/RevButton.vue'
import RevBadge from '@/Components/Revisor/RevBadge.vue'
import RevMeter from '@/Components/Revisor/RevMeter.vue'
import RevIcon from '@/Components/Revisor/RevIcon.vue'
import RevEmptyState from '@/Components/Revisor/RevEmptyState.vue'

const baseUrl = window.location.origin

const dni = ref(null)
const dniseleccionado = ref(null)
const dniInput = ref(null)
const postulantes = ref([])
const requisitos = ref([])
const checkedList = ref([])
const checkAll = ref(false)
const guardando = ref(false)
const activeKey = ref('1')
const frameLoading = ref(false)

const dniSeleccionadoValido = computed(() => !!dniseleccionado.value && String(dniseleccionado.value).length === 8)

/* Las pestañas describen el documento, no el orden del formulario antiguo. */
const tabs = [
  { key: '1', label: 'Solicitud',       icon: 'file',        path: 'solicitud-1.pdf' },
  { key: '2', label: 'Comprobantes',    icon: 'credit-card', path: null },
  { key: '3', label: 'Certificado',     icon: 'shield-check', path: 'certificado-1.pdf' },
  { key: '4', label: 'Ex. vocacional',  icon: 'file-check',  path: 'constancia%20vocacional-1.pdf' },
  { key: '5', label: 'Cert. Cepreuna',  icon: 'award',       path: 'constancia%20vocacional-1.pdf' },
]

const urlActual = computed(() => {
  const tab = tabs.find((t) => t.key === activeKey.value)
  if (!tab?.path || !dniSeleccionadoValido.value) return ''
  return `${baseUrl}/documentos/cepre2023-II/${dniseleccionado.value}/${tab.path}`
})

const onCheckAllChange = (e) => {
  checkAll.value = e.target.checked
  checkedList.value = e.target.checked ? requisitos.value.map((o) => o.value) : []
}

const toggleRequisito = (value) => {
  const i = checkedList.value.indexOf(value)
  if (i === -1) checkedList.value.push(value)
  else checkedList.value.splice(i, 1)
  checkAll.value = checkedList.value.length === requisitos.value.length && requisitos.value.length > 0
}

const getRequisitos = async () => {
  try {
    const res = await axios.get('get-requisitos')
    requisitos.value = res.data.datos
  } catch {
    notification.error({ message: 'Error', description: 'No se pudieron cargar los requisitos.' })
  }
}

const save = async () => {
  guardando.value = true
  try {
    await axios.post('save-requisito', { dni: dniseleccionado.value, requisitos: checkedList.value })
    message.success('Requisitos guardados')
    dniseleccionado.value = null
    dni.value = null
    checkedList.value = []
    checkAll.value = false
    dniInput.value?.focus?.()
  } catch {
    notification.error({ message: 'Error', description: 'No se pudieron guardar los requisitos.' })
  } finally {
    guardando.value = false
  }
}

const getPostulantes = async () => {
  try {
    const res = await axios.post('get-postulantes?page=1', { term: dni.value })
    postulantes.value = res.data.datos.data
  } catch { /* la búsqueda no debe romper la pantalla */ }
}

const getPostulanteRequisitos = async () => {
  checkedList.value = []
  checkAll.value = false
  if (!dniSeleccionadoValido.value) return
  try {
    const res = await axios.post('get-postulante-requisitos', { dni: dniseleccionado.value })
    if (res.data.estado === true) {
      checkedList.value = JSON.parse(res.data.datos.requisitos) || []
      checkAll.value = checkedList.value.length === requisitos.value.length && requisitos.value.length > 0
    }
  } catch { /* un postulante sin requisitos previos es un caso normal */ }
}

const onSelect = () => { activeKey.value = '1' }
const onSearch = () => getPostulantes()

watch(dni, getPostulantes)
watch(dniseleccionado, getPostulanteRequisitos)

getRequisitos()
</script>

<style scoped>
.doc { display: flex; flex-direction: column; gap: var(--rev-s-6); min-height: 0; }
.doc-lead { display: inline-flex; align-items: center; gap: 5px; color: var(--rev-ink-4); }
.doc-auto { width: 340px; max-width: 100%; }

.doc-option { display: flex; flex-direction: column; line-height: 1.3; padding: 2px 0; }
.doc-option-dni { font-size: var(--rev-fs-sm); font-weight: 680; color: var(--rev-ink); }
.doc-option-name { font-size: var(--rev-fs-sm); color: var(--rev-ink-3); text-transform: capitalize; }

/* Vista dividida: el visor pesa más que el checklist, a propósito --------- */
.doc-split { display: grid; grid-template-columns: 320px 1fr; gap: var(--rev-s-5); align-items: start; }

.doc-check-meter { margin-bottom: var(--rev-s-5); }
.doc-check-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 1px; }
.doc-check-item {
  display: flex; align-items: center; gap: 9px;
  padding: 7px 8px; margin: 0 -8px;
  border-radius: var(--rev-r-md);
  cursor: pointer;
  transition: background var(--rev-t-fast) var(--rev-ease);
}
.doc-check-item:hover { background: var(--rev-n-50); }
.doc-check-item input { position: absolute; opacity: 0; width: 0; height: 0; }
.doc-check-box {
  flex: none; display: grid; place-items: center;
  width: 17px; height: 17px; border-radius: var(--rev-r-xs);
  border: 1px solid var(--rev-n-300); background: var(--rev-surface);
  color: transparent;
  transition: background var(--rev-t-fast) var(--rev-ease), border-color var(--rev-t-fast) var(--rev-ease), color var(--rev-t-fast) var(--rev-ease);
}
.doc-check-item.is-on .doc-check-box { background: var(--rev-primary-600); border-color: var(--rev-primary-600); color: #fff; }
.doc-check-item input:focus-visible + .doc-check-box { box-shadow: var(--rev-ring); }
.doc-check-label { font-size: var(--rev-fs-md); color: var(--rev-ink-2); line-height: 1.4; }
.doc-check-item.is-on .doc-check-label { color: var(--rev-ink); font-weight: 560; }

/* Visor -------------------------------------------------------------------- */
.doc-viewer :deep(.rev-panel-head) { padding: 0 var(--rev-s-5); min-height: 42px; }
.doc-tabs { display: flex; align-items: center; gap: 2px; overflow-x: auto; }
.doc-tab {
  position: relative;
  display: inline-flex; align-items: center; gap: 6px;
  height: 41px; padding: 0 12px;
  border: 0; background: transparent; cursor: pointer;
  font-family: var(--rev-font); font-size: var(--rev-fs-md); font-weight: 560;
  color: var(--rev-ink-3); white-space: nowrap;
  transition: color var(--rev-t-fast) var(--rev-ease);
}
.doc-tab:hover { color: var(--rev-ink); }
.doc-tab:focus-visible { outline: none; box-shadow: var(--rev-ring); border-radius: var(--rev-r-sm); }
.doc-tab.is-active { color: var(--rev-primary-700); font-weight: 620; }
.doc-tab.is-active::after {
  content: ""; position: absolute; left: 8px; right: 8px; bottom: -1px;
  height: 2px; background: var(--rev-primary-600); border-radius: 2px 2px 0 0;
}

.doc-frame { height: min(68vh, 660px); background: var(--rev-n-100); }
.doc-frame iframe { width: 100%; height: 100%; border: 0; display: block; }
.doc-vouchers { height: 100%; overflow: auto; background: var(--rev-surface); padding: var(--rev-s-5); }

@media (max-width: 1100px) {
  .doc-split { grid-template-columns: 1fr; }
  .doc-frame { height: 56vh; }
}
@media (max-width: 640px) {
  .doc-auto { width: 100%; }
}
</style>
