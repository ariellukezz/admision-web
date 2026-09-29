<!--
  ============================================================================
  Certificados — validación documental en serie.
  ----------------------------------------------------------------------------
  Es una tarea repetitiva: abrir, mirar, decidir, siguiente. El diseño optimiza
  ese bucle: la fila seleccionada se mantiene marcada, el visor se abre sobre
  el mismo sitio y la decisión (validar / revertir) vive tanto en la fila como
  dentro del visor, para no obligar a cerrar y volver a buscar.
  El código y el DNI se copian con un clic porque es lo que se cruza a mano
  contra otros sistemas.
  ============================================================================
-->
<template>
  <Head title="Certificados" />
  <AuthenticatedLayout pagina="Certificados">
    <div class="val">

      <RevPageHeader
        title="Certificados"
        description="Verificación de los certificados presentados por los postulantes del proceso activo."
      />

      <RevPanel flush>
        <template #header>
          <div class="val-head">
            <span class="rev-meta"><strong class="rev-num">{{ totalpaginas }}</strong> certificados</span>
          </div>
        </template>

        <template #actions>
          <RevSearch v-model="buscar" placeholder="Código, DNI o postulante…" class="val-search" @search="onBuscar" />
        </template>

        <RevTable
          :columns="columns"
          :rows="certificados"
          row-key="id"
          :loading="loading"
          density="compact"
          clickable
          :selected-key="seleccionado"
          @row-click="abrirmodal"
        >
          <template #cell:cod="{ row }">
            <button class="val-copy" type="button" :title="`Copiar ${row.cod}`" @click.stop="copiar(row.cod, 'Código')">
              <span class="val-copy-text rev-mono">{{ row.cod }}</span>
              <RevIcon name="copy" size="xs" class="val-copy-icon" />
            </button>
          </template>

          <template #cell:tipo="{ row }">
            <span class="val-tipo">{{ row.tipo || '—' }}</span>
          </template>

          <template #cell:dni="{ row }">
            <button class="val-copy" type="button" :title="`Copiar ${row.dni}`" @click.stop="copiar(row.dni, 'DNI')">
              <span class="val-copy-text rev-mono">{{ row.dni }}</span>
              <RevIcon name="copy" size="xs" class="val-copy-icon" />
            </button>
          </template>

          <template #cell:postulante="{ row }">
            <span class="val-name">{{ row.nombres }} {{ row.paterno }} {{ row.materno }}</span>
          </template>

          <template #cell:verificado="{ row }">
            <RevBadge :tone="row.verificado === 1 ? 'success' : 'pending'" size="sm" dot>
              {{ row.verificado === 1 ? 'Verificado' : 'Sin verificar' }}
            </RevBadge>
          </template>

          <template #cell:acciones="{ row }">
            <div class="val-actions">
              <RevButton variant="ghost" size="sm" icon="eye" @click.stop="abrirmodal(row)">Ver</RevButton>
              <RevButton
                :variant="row.verificado === 1 ? 'danger' : 'primary'"
                size="sm"
                :icon="row.verificado === 1 ? 'close' : 'check'"
                :loading="validandoId === row.id"
                @click.stop="actualizarEstado(row)"
              >{{ row.verificado === 1 ? 'Revertir' : 'Validar' }}</RevButton>
            </div>
          </template>

          <template #empty>
            <RevEmptyState
              :variant="buscar ? 'filtered' : 'empty'"
              icon="shield-check"
              :title="buscar ? 'Sin resultados' : 'Sin certificados por revisar'"
              :description="buscar
                ? 'Ningún certificado coincide con la búsqueda. Prueba con el código completo o sólo el DNI.'
                : 'Los certificados aparecerán aquí cuando los postulantes los presenten para verificación.'"
            >
              <template v-if="buscar" #actions>
                <RevButton variant="secondary" size="sm" icon="close" @click="buscar = ''; onBuscar()">Limpiar búsqueda</RevButton>
              </template>
            </RevEmptyState>
          </template>
        </RevTable>

        <template #footer>
          <span class="rev-meta">Mostrando {{ certificados.length }} de {{ totalpaginas }}</span>
          <a-pagination
            v-model:current="pagina"
            v-model:pageSize="paginasize"
            :total="totalpaginas"
            show-size-changer
            show-less-items
          />
        </template>
      </RevPanel>

      <!-- Visor de certificado ------------------------------------------- -->
      <a-modal
        v-model:open="visible"
        :title="activo ? `Certificado ${activo.cod}` : 'Certificado'"
        width="min(1120px, 94vw)"
        :footer="null"
        @cancel="visible = false"
      >
        <div class="val-viewer">
          <div class="val-viewer-doc">
            <iframe v-if="verurl" :src="verurl" title="Certificado" />
          </div>

          <aside class="val-viewer-side">
            <RevKeyValue
              v-if="activo"
              :items="[
                { key: 'cod', label: 'Código', value: activo.cod, mono: true },
                { key: 'tipo', label: 'Tipo', value: activo.tipo },
                { key: 'dni', label: 'DNI', value: activo.dni, mono: true },
                { key: 'post', label: 'Postulante', value: `${activo.nombres || ''} ${activo.paterno || ''} ${activo.materno || ''}`.trim() },
              ]"
            />

            <div class="val-viewer-state">
              <span class="rev-label">Estado actual</span>
              <RevBadge :tone="activo?.verificado === 1 ? 'success' : 'pending'" dot>
                {{ activo?.verificado === 1 ? 'Verificado' : 'Sin verificar' }}
              </RevBadge>
            </div>

            <div class="val-viewer-foot">
              <RevButton
                v-if="activo"
                :variant="activo.verificado === 1 ? 'danger' : 'primary'"
                :icon="activo.verificado === 1 ? 'close' : 'check'"
                :loading="validandoId === activo.id"
                block
                @click="actualizarEstado(activo)"
              >{{ activo.verificado === 1 ? 'Revertir validación' : 'Validar certificado' }}</RevButton>
            </div>
          </aside>
        </div>
      </a-modal>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue'
import { watch, ref } from 'vue'
import { notification, message } from 'ant-design-vue'
import { toClipboard } from '@soerenmartius/vue3-clipboard'
import axios from 'axios'
import RevPageHeader from '@/Components/Revisor/RevPageHeader.vue'
import RevPanel from '@/Components/Revisor/RevPanel.vue'
import RevTable from '@/Components/Revisor/RevTable.vue'
import RevBadge from '@/Components/Revisor/RevBadge.vue'
import RevButton from '@/Components/Revisor/RevButton.vue'
import RevSearch from '@/Components/Revisor/RevSearch.vue'
import RevIcon from '@/Components/Revisor/RevIcon.vue'
import RevKeyValue from '@/Components/Revisor/RevKeyValue.vue'
import RevEmptyState from '@/Components/Revisor/RevEmptyState.vue'

const certificados = ref([])
const loading = ref(true)
const visible = ref(false)
const verurl = ref('')
const activo = ref(null)
const seleccionado = ref(null)
const validandoId = ref(null)

const buscar = ref('')
const totalpaginas = ref(0)
const pagina = ref(1)
const paginasize = ref(20)

const columns = [
  { key: 'cod',        title: 'Código', width: '132px' },
  { key: 'tipo',       title: 'Tipo', width: '150px' },
  { key: 'dni',        title: 'DNI', width: '118px' },
  { key: 'postulante', title: 'Postulante' },
  { key: 'verificado', title: 'Estado', width: '134px' },
  { key: 'acciones',   title: '', width: '188px', align: 'right', sticky: true },
]

const abrirmodal = (record) => {
  activo.value = record
  seleccionado.value = record.id
  verurl.value = `${window.location.origin}/${record.url}`
  visible.value = true
}

const copiar = async (valor, etiqueta) => {
  try {
    await toClipboard(String(valor))
    message.success(`${etiqueta} copiado`)
  } catch {
    message.error(`No se pudo copiar el ${etiqueta.toLowerCase()}`)
  }
}

const getCertificados = async () => {
  loading.value = true
  try {
    const res = await axios.post(`get-certificados-revision?page=${pagina.value}`, {
      term: buscar.value,
      paginasize: paginasize.value,
    })
    certificados.value = res.data.datos.data
    totalpaginas.value = res.data.datos.total
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudieron cargar los certificados.' })
  } finally {
    loading.value = false
  }
}

const actualizarEstado = async (item) => {
  validandoId.value = item.id
  try {
    const estado = item.verificado === 0 ? 1 : 0
    const res = await axios.post('cambiar-estado', { id: item.id, estado })
    notification.success({ message: res.data.titulo, description: res.data.mensaje })
    if (activo.value && activo.value.id === item.id) activo.value.verificado = estado
    await getCertificados()
  } catch (e) {
    notification.error({ message: 'Error', description: 'No se pudo actualizar el certificado.' })
  } finally {
    validandoId.value = null
  }
}

const onBuscar = () => { pagina.value = 1; getCertificados() }

watch(pagina, getCertificados)
watch(paginasize, () => { pagina.value = 1; getCertificados() })

getCertificados()
</script>

<style scoped>
.val { display: flex; flex-direction: column; gap: var(--rev-s-6); }
.val-head { display: flex; align-items: center; gap: var(--rev-s-5); }
.val-search { width: 268px; }

/* Copiar al portapapeles: acción discreta, aparece al pasar por la fila --- */
.val-copy {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 2px 5px; margin-left: -5px;
  border: 1px solid transparent; border-radius: var(--rev-r-sm);
  background: transparent; cursor: pointer; font-family: var(--rev-font);
  transition: background var(--rev-t-fast) var(--rev-ease), border-color var(--rev-t-fast) var(--rev-ease);
}
.val-copy:hover { background: var(--rev-surface); border-color: var(--rev-line-strong); }
.val-copy:focus-visible { outline: none; box-shadow: var(--rev-ring); }
.val-copy-text { font-size: var(--rev-fs-md); font-weight: 600; color: var(--rev-ink); letter-spacing: -.01em; }
.val-copy-icon { color: var(--rev-ink-4); opacity: 0; transition: opacity var(--rev-t-fast) var(--rev-ease); }
.val-copy:hover .val-copy-icon { opacity: 1; }

.val-tipo { font-size: var(--rev-fs-sm); color: var(--rev-ink-3); }
.val-name {
  font-size: var(--rev-fs-md); font-weight: 560; color: var(--rev-ink-2);
  text-transform: capitalize;
  display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.val-actions { display: flex; align-items: center; justify-content: flex-end; gap: 4px; }

/* Visor -------------------------------------------------------------------- */
.val-viewer { display: flex; align-items: stretch; height: min(72vh, 660px); margin: -18px; }
.val-viewer-doc { flex: 1 1 auto; min-width: 0; background: var(--rev-n-100); }
.val-viewer-doc iframe { width: 100%; height: 100%; border: 0; display: block; }
.val-viewer-side {
  flex: 0 0 276px; width: 276px;
  display: flex; flex-direction: column; gap: var(--rev-s-6);
  padding: var(--rev-s-6);
  border-left: 1px solid var(--rev-line);
  background: var(--rev-surface);
  overflow-y: auto;
}
.val-viewer-state { display: flex; flex-direction: column; gap: 6px; align-items: flex-start; }
.val-viewer-foot { margin-top: auto; padding-top: var(--rev-s-6); border-top: 1px solid var(--rev-line); }

@media (max-width: 1080px) {
  .val-viewer { flex-direction: column; height: auto; }
  .val-viewer-doc { height: 52vh; }
  .val-viewer-side { flex: 1 1 auto; width: 100%; border-left: 0; border-top: 1px solid var(--rev-line); }
}
@media (max-width: 720px) { .val-search { width: 100%; } }
</style>
