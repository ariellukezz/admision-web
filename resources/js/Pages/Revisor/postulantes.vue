<!--
  ============================================================================
  Revisión de documentos del postulante — el puesto de trabajo del revisor.
  ----------------------------------------------------------------------------
  La pantalla responde, de arriba abajo, a la secuencia real de la tarea:
    A quién reviso  → barra de identidad, siempre visible al desplazar
    Cuánto llevo    → banda de progreso (requisitos y documentos)
    Qué ocurre      → aviso de estado con la consecuencia de finalizar
    Qué reviso      → requisitos en secciones, documentos en filas densas
    Qué decido      → V°B° u observación, en la fila y también en el visor
  El visor es una vista dividida: el documento a la izquierda, la decisión a la
  derecha. Revisar y decidir sin cambiar de contexto es el cuello de botella
  de este puesto, y es donde se ha puesto el esfuerzo de diseño.
  ============================================================================
-->
<template>
  <Head title="Revisión de documentos" />
  <AuthenticatedLayout :pagina="isProfileMode ? 'Revisión de documentos' : 'Postulantes'">

    <!-- ══════════════════════════ MODO FICHA ══════════════════════════ -->
    <div v-if="isProfileMode" class="pr">

      <!-- Barra de identidad ------------------------------------------- -->
      <div class="pr-bar">
        <RevButton variant="ghost" icon="arrow-left" icon-only aria-label="Volver al listado" @click="goBack" />
        <span class="rev-divider-v pr-bar-sep" />

        <RevAvatar :name="nombreCompleto" size="md" tone="accent" />

        <div class="pr-identity">
          <div class="pr-identity-row">
            <h2 class="pr-name">{{ nombreCompleto || 'Revisión de documentos' }}</h2>
            <span class="pr-dni rev-mono">{{ dni }}</span>
          </div>
          <div class="pr-identity-meta">
            <span v-if="requisitosData?.modalidad_postulante" class="pr-meta-item">
              <RevIcon name="flag" size="xs" />{{ requisitosData.modalidad_postulante.nombre }}
            </span>
            <span v-if="requisitosData?.programa_postulante" class="pr-meta-item">
              <RevIcon name="award" size="xs" />
              {{ requisitosData.programa_postulante.nombre_corto || requisitosData.programa_postulante.nombre }}
            </span>
          </div>
        </div>

        <div class="pr-bar-right">
          <RevBadge :tone="estadoRevision.tone" dot :pulse="estadoRevision.key === 'en_curso'">
            {{ estadoRevision.label }}
          </RevBadge>

          <RevButton
            v-if="revisionSolicitada && !revisionData.iniciada_at"
            variant="primary" icon="play" :loading="startingRevision" @click="iniciarRevision"
          >Iniciar revisión</RevButton>

          <RevButton
            v-else-if="revisionEnCurso"
            variant="primary" icon="check" @click="abrirModalFinalizar"
          >Finalizar revisión</RevButton>

          <RevButton
            v-else-if="revisionData.finalizada_at && revisionData.estado === 'pendiente'"
            variant="secondary" icon="send" :loading="renotifying" @click="renotificarPostulante"
          >Re-notificar</RevButton>
        </div>
      </div>

      <!-- Carga --------------------------------------------------------- -->
      <div v-if="loadingDocs" class="pr-loading">
        <RevPanel flush><RevSkeleton variant="list" :rows="5" /></RevPanel>
      </div>

      <template v-else-if="requisitosData">

        <!-- Banda de progreso ------------------------------------------ -->
        <div class="pr-progress">
          <div class="pr-progress-block">
            <RevMeter
              label="Requisitos cumplidos"
              :value="requisitosData.requisitos_cumplidos"
              :max="requisitosData.total_requisitos || 1"
              :tone="requisitosData.requisitos_cumplidos >= requisitosData.total_requisitos ? 'success' : 'accent'"
            >
              <template #foot>
                <span class="rev-num">{{ requisitosData.requisitos_cumplidos }}</span> de
                <span class="rev-num">{{ requisitosData.total_requisitos }}</span>
              </template>
            </RevMeter>
          </div>

          <span class="rev-divider-v" />

          <div class="pr-progress-block">
            <RevMeter
              label="Documentos validados"
              :value="docsVerificados"
              :max="totalDocs || 1"
              :tone="totalDocs > 0 && docsVerificados >= totalDocs ? 'success' : 'accent'"
            >
              <template #foot>
                <span class="rev-num">{{ docsVerificados }}</span> de
                <span class="rev-num">{{ totalDocs }}</span>
                <template v-if="docsPendientes > 0"> · <span class="pr-pending rev-num">{{ docsPendientes }}</span> sin validar</template>
              </template>
            </RevMeter>
          </div>

          <div class="pr-progress-action">
            <RevButton
              v-if="revisionEnCurso"
              variant="secondary" icon="check-circle" :loading="rapidValidating" @click="revisionRapida"
            >Validar todos</RevButton>
          </div>
        </div>

        <!-- Aviso de estado -------------------------------------------- -->
        <RevBanner v-if="revisionEnCurso" tone="info" title="Revisión en curso">
          Marca cada documento con V°B° o deja una observación. Al finalizar: si todo está válido se
          agenda la citación presencial; si hay observaciones, el postulante deberá corregirlas.
        </RevBanner>

        <RevBanner
          v-else-if="revisionData.finalizada_at && revisionData.estado === 'pendiente'"
          tone="warning"
          title="Pendiente — documentos por corregir"
        >
          El postulante tiene {{ docsPendientes }} documento(s) observado(s). Debe corregirlos y volver a
          solicitar revisión. Puedes enviarle un recordatorio desde el botón «Re-notificar».
        </RevBanner>

        <RevBanner
          v-else-if="revisionData.finalizada_at && revisionData.estado === 'completada'"
          tone="success"
          title="Solicitud atendida"
        >
          Todos los documentos fueron validados y el postulante ya fue notificado para su citación presencial.
        </RevBanner>

        <RevBanner
          v-else-if="!revisionSolicitada && !revisionData.iniciada_at"
          tone="neutral"
          title="Sin revisión activa"
        >
          Esta solicitud ya fue atendida o el postulante aún no ha pedido la revisión de sus documentos.
          Los documentos se muestran en modo de solo lectura.
        </RevBanner>

        <!-- Requisitos --------------------------------------------------- -->
        <div class="pr-reqs">
          <RevPanel
            v-for="req in requisitosConDocs"
            :key="req.id"
            flush
            class="pr-req"
            :class="{ 'is-done': req.cumplido && req.verificado }"
          >
            <template #header>
              <div class="pr-req-head">
                <span class="pr-req-num rev-num" :class="{ 'is-done': req.cumplido && req.verificado }">
                  <RevIcon v-if="req.cumplido && req.verificado" name="check" size="xs" />
                  <template v-else>{{ req.orden }}</template>
                </span>
                <div class="pr-req-titles">
                  <h3 class="pr-req-name">{{ req.nombre }}</h3>
                  <span class="pr-req-sub">{{ contarDocs(req) }} documento(s) subido(s)</span>
                </div>
              </div>
            </template>

            <template #actions>
              <RevBadge
                :tone="req.no_aplica ? 'neutral' : req.obligatorio_para_postulante ? 'accent' : 'neutral'"
                size="sm"
              >{{ req.no_aplica ? 'No aplica' : req.obligatorio_para_postulante ? 'Obligatorio' : 'Opcional' }}</RevBadge>

              <RevBadge
                :tone="req.cumplido && req.verificado ? 'success' : req.cumplido ? 'warning' : 'danger'"
                size="sm" dot
              >{{ req.cumplido && req.verificado ? 'Verificado' : req.cumplido ? 'Por verificar' : 'Faltante' }}</RevBadge>
            </template>

            <!-- Tipos de documento subidos -->
            <div v-for="td in req.tipos_subidos" :key="td.id" class="pr-type">
              <div class="pr-type-label">
                <RevIcon name="files" size="xs" />{{ td.nombre }}
              </div>

              <div class="pr-docs">
                <div
                  v-for="doc in td.documentos"
                  :key="doc.id"
                  class="pr-doc"
                  :class="{ 'is-valid': doc.valido, 'is-observed': doc.observacion_revisor && !doc.valido }"
                >
                  <!-- archivo -->
                  <button v-if="doc.url" class="pr-doc-file" type="button" @click="previewReqDoc(td, doc)">
                    <span class="pr-doc-icon"><RevIcon :name="doc.valido ? 'file-check' : 'file'" size="md" /></span>
                    <span class="pr-doc-name">{{ doc.nombre }}</span>
                    <RevIcon name="eye" size="sm" class="pr-doc-open" />
                  </button>
                  <span v-else class="pr-doc-file is-missing">
                    <span class="pr-doc-icon"><RevIcon name="file-alert" size="md" /></span>
                    <span class="pr-doc-name">{{ doc.nombre }}</span>
                    <span class="pr-doc-missing">archivo no disponible</span>
                  </span>

                  <!-- vigencia -->
                  <RevBadge
                    v-if="doc.fecha_caducidad"
                    size="sm"
                    :tone="vigenciaTone(doc)"
                  >{{ vigenciaBadge(doc).text }}</RevBadge>

                  <!-- decisión -->
                  <div class="pr-doc-actions">
                    <RevButton
                      v-if="revisionEnCurso"
                      variant="ghost" size="sm" icon="message"
                      :class="{ 'is-on': observandoDocId === doc.id }"
                      @click="toggleObservacion(doc)"
                    >{{ doc.observacion_revisor ? 'Editar nota' : 'Observar' }}</RevButton>

                    <RevButton
                      :variant="doc.valido ? 'success' : 'secondary'"
                      size="sm"
                      :icon="doc.valido ? 'check' : 'check-circle'"
                      :loading="verifyingId === doc.id"
                      :disabled="!revisionEnCurso"
                      @click="cambiarEstadoDoc(td, doc)"
                    >{{ doc.valido ? 'Validado' : 'Dar V°B°' }}</RevButton>
                  </div>

                  <!-- observación registrada -->
                  <div v-if="doc.observacion_revisor && observandoDocId !== doc.id" class="pr-doc-obs">
                    <RevIcon name="message" size="xs" />
                    <span>{{ doc.observacion_revisor }}</span>
                  </div>

                  <!-- edición de la observación -->
                  <transition name="rev-rise">
                    <div v-if="observandoDocId === doc.id" class="pr-obs-edit">
                      <input
                        v-model="observacionText"
                        type="text"
                        class="pr-obs-input"
                        placeholder="Ej.: el documento está borroso y no se lee la fecha de emisión…"
                        @keyup.enter="guardarObservacion(doc)"
                        @keyup.esc="observandoDocId = null; observacionText = null"
                      />
                      <RevButton variant="primary" size="sm" @click="guardarObservacion(doc)">Guardar</RevButton>
                      <RevButton variant="ghost" size="sm" @click="observandoDocId = null; observacionText = null">Cancelar</RevButton>
                    </div>
                  </transition>
                </div>
              </div>
            </div>
          </RevPanel>
        </div>
      </template>

      <!-- Vacío --------------------------------------------------------- -->
      <RevPanel v-else flush>
        <RevEmptyState
          icon="file"
          title="No hay documentos subidos"
          description="Este postulante aún no ha cargado documentos para revisión. Vuelve a esta ficha cuando solicite la revisión."
        >
          <template #actions>
            <RevButton variant="secondary" icon="arrow-left" @click="goBack">Volver a solicitudes</RevButton>
          </template>
        </RevEmptyState>
      </RevPanel>

      <!-- ═════════════════ VISOR DE DOCUMENTO (vista dividida) ═════════ -->
      <a-modal
        v-model:open="previewVisible"
        :title="previewTitle"
        width="min(1180px, 94vw)"
        :footer="null"
        wrap-class-name="pr-viewer-wrap"
        @cancel="previewVisible = false"
      >
        <div class="pr-viewer">
          <div class="pr-viewer-doc">
            <iframe v-if="previewUrl" :src="previewUrl" title="Documento del postulante" />
          </div>

          <aside class="pr-viewer-side">
            <div class="pr-viewer-section">
              <span class="rev-eyebrow">Documento</span>
              <p class="pr-viewer-docname">{{ activeDoc?.nombre }}</p>
              <RevBadge
                v-if="activeDoc"
                :tone="activeDoc.valido ? 'success' : activeDoc.apto_revision ? 'progress' : 'pending'"
                size="sm" dot
              >{{ activeDoc.valido ? 'Validado' : activeDoc.apto_revision ? 'Apto para revisión' : 'Sin revisar' }}</RevBadge>
            </div>

            <div class="rev-divider" />

            <div class="pr-viewer-section">
              <RevField label="Fecha de caducidad" hint="Se registra al dar el V°B°.">
                <input v-model="fechaCaducidadModal" type="date" :disabled="!revisionEnCurso" />
              </RevField>
              <RevBadge
                v-if="activeDoc?.fecha_caducidad"
                size="sm"
                :tone="vigenciaTone(activeDoc)"
                class="pr-viewer-vig"
              >{{ vigenciaBadge(activeDoc).text }}</RevBadge>
            </div>

            <div class="rev-divider" />

            <div class="pr-viewer-section">
              <RevField label="Observación al postulante">
                <textarea
                  v-model="observacionModal"
                  rows="4"
                  :disabled="!revisionEnCurso"
                  placeholder="Describe con precisión qué debe corregir. El postulante leerá este texto tal cual."
                />
              </RevField>
              <RevButton
                v-if="revisionEnCurso"
                variant="secondary" size="sm" icon="message" block
                @click="guardarObservacionModal"
              >Guardar observación</RevButton>
            </div>

            <div class="pr-viewer-foot">
              <RevButton
                v-if="activeDoc"
                variant="ghost" size="sm" icon="download"
                :href="`/revisor/descargar-documento-revisor/${activeDoc.id}`"
              >Descargar</RevButton>

              <RevButton
                v-if="activeDoc"
                :variant="activeDoc.valido ? 'success' : 'primary'"
                :icon="activeDoc.valido ? 'check' : 'check-circle'"
                :loading="verifyingId === activeDoc.id"
                :disabled="!revisionEnCurso"
                @click="cambiarEstadoDoc({ nombre: activeDoc.tipo }, activeDoc)"
              >{{ activeDoc.valido ? 'Validado' : 'Dar V°B°' }}</RevButton>
            </div>
          </aside>
        </div>
      </a-modal>

      <!-- ═════════════════ FINALIZAR REVISIÓN ══════════════════════════ -->
      <a-modal
        v-model:open="finalizarModalVisible"
        title="Finalizar revisión"
        width="min(680px, 94vw)"
        :footer="null"
        @cancel="finalizarModalVisible = false"
      >
        <div class="fin">
          <!-- Resumen -->
          <div class="fin-summary">
            <div class="fin-cell is-ok">
              <span class="fin-cell-num rev-num">{{ docsVerificados }}</span>
              <span class="fin-cell-label">Documentos válidos</span>
            </div>
            <div class="fin-cell" :class="docsPendientes > 0 ? 'is-warn' : 'is-muted'">
              <span class="fin-cell-num rev-num">{{ docsPendientes }}</span>
              <span class="fin-cell-label">Observados</span>
            </div>
          </div>

          <!-- Qué va a pasar -->
          <RevBanner
            v-if="docsPendientes > 0"
            tone="warning"
            title="La revisión quedará en estado pendiente"
          >
            Se notificará al postulante para que corrija los documentos observados y vuelva a solicitar revisión.
          </RevBanner>
          <RevBanner
            v-else
            tone="success"
            title="Todos los documentos están válidos"
          >
            Al confirmar se agendará la citación presencial y se notificará al postulante sin novedades.
          </RevBanner>

          <!-- Documentos observados -->
          <RevPanel v-if="docsPendientes > 0" title="Documentos que deberá corregir" flush>
            <ul class="fin-list">
              <template v-for="req in requisitosConDocs" :key="req.id">
                <template v-for="td in req.tipos_subidos" :key="td.id">
                  <li v-for="doc in (td.documentos || []).filter(d => !d.valido)" :key="doc.id">
                    <RevIcon name="file-alert" size="sm" />
                    <div>
                      <strong>{{ td.nombre }}</strong>
                      <span class="fin-list-file">{{ doc.nombre }}</span>
                      <p v-if="doc.observacion_revisor" class="fin-list-obs">{{ doc.observacion_revisor }}</p>
                      <p v-else class="fin-list-obs is-empty">Sin observación registrada — el postulante no sabrá qué corregir.</p>
                    </div>
                  </li>
                </template>
              </template>
              <template v-if="!requisitosConDocs.length">
                <li v-for="doc in documentos.filter(d => !d.valido)" :key="doc.id">
                  <RevIcon name="file-alert" size="sm" />
                  <div><strong>{{ doc.tipo || doc.nombre }}</strong></div>
                </li>
              </template>
            </ul>
          </RevPanel>

          <!-- Citación -->
          <RevPanel v-if="docsPendientes === 0" title="Citación presencial">
            <div v-if="loadingCitacion" class="fin-loading">
              <RevIcon name="loader" size="sm" spin /> Calculando la cita sugerida…
            </div>

            <template v-else>
              <div class="fin-grid">
                <RevField label="Fecha" required><input v-model="citacionForm.fecha" type="date" /></RevField>
                <RevField label="Hora de inicio" required><input v-model="citacionForm.hora_inicio" type="time" /></RevField>
                <RevField label="Hora de fin" required><input v-model="citacionForm.hora_fin" type="time" /></RevField>
              </div>

              <RevField label="Lugar" required class="fin-field">
                <input v-model="citacionForm.lugar" type="text" placeholder="Ej.: Dirección de Admisión — UNA Puno" />
              </RevField>

              <RevField label="Instrucciones" hint="Aparecerán íntegras en la notificación al postulante." class="fin-field">
                <textarea v-model="citacionForm.instrucciones" rows="3" placeholder="Ej.: Acercarse con documentos originales y copias simples." />
              </RevField>

              <p v-if="citacionSugerida" class="fin-hint">
                <RevIcon name="info" size="xs" />
                Cita calculada automáticamente por <strong>{{ citacionSugerida.tipo_criterio }}</strong>
                <template v-if="citacionSugerida.valor"> ({{ citacionSugerida.valor }})</template>.
              </p>
            </template>
          </RevPanel>

          <div class="fin-actions">
            <RevButton variant="ghost" @click="finalizarModalVisible = false">Cancelar</RevButton>
            <RevButton
              :variant="docsPendientes > 0 ? 'secondary' : 'primary'"
              :icon="docsPendientes > 0 ? 'send' : 'check'"
              :loading="finishingRevision"
              :disabled="loadingCitacion"
              @click="finalizarRevision"
            >{{ docsPendientes > 0 ? 'Finalizar con observaciones' : 'Confirmar y notificar' }}</RevButton>
          </div>
        </div>
      </a-modal>
    </div>

    <!-- ══════════════════════════ MODO LISTADO ════════════════════════ -->
    <div v-else class="pl">
      <RevPageHeader
        title="Postulantes"
        description="Estado de los requisitos registrados por postulante para el proceso activo."
      />

      <RevPanel flush>
        <template #header>
          <span class="rev-meta">
            <strong class="rev-num">{{ totalpaginas }}</strong> postulantes
          </span>
        </template>
        <template #actions>
          <RevSearch v-model="buscar" placeholder="Nombre o DNI…" class="pl-search" />
        </template>

        <RevTable :columns="listColumns" :rows="postulantes" row-key="dni" density="compact">
          <template #cell:postulante="{ row }">
            <div class="pl-person">
              <span class="pl-dni rev-mono">{{ row.dni }}</span>
              <span class="pl-name">{{ row.nombres }} {{ row.paterno }} {{ row.materno }}</span>
            </div>
          </template>

          <template #cell:requisitos="{ row }">
            <div class="pl-reqs">
              <RevBadge
                v-for="opt in requisitos"
                :key="opt.value"
                size="sm"
                :tone="parseReqs(row.requisitos).includes(opt.value) ? 'success' : 'neutral'"
                :icon="parseReqs(row.requisitos).includes(opt.value) ? 'check' : ''"
              >{{ opt.label }}</RevBadge>
            </div>
          </template>

          <template #empty>
            <RevEmptyState
              :variant="buscar ? 'filtered' : 'empty'"
              :title="buscar ? 'Sin resultados' : 'Sin postulantes registrados'"
              :description="buscar ? 'Ningún postulante coincide con la búsqueda.' : 'Aparecerán aquí en cuanto existan inscripciones en el proceso activo.'"
            />
          </template>
        </RevTable>

        <template #footer>
          <span class="rev-meta">Mostrando {{ postulantes.length }} de {{ totalpaginas }}</span>
          <a-pagination
            v-model:current="pagina"
            v-model:pageSize="paginasize"
            :total="totalpaginas"
            show-size-changer
            show-less-items
          />
        </template>
      </RevPanel>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue';
import { watch, computed, ref, onMounted } from 'vue';
import RevPageHeader from '@/Components/Revisor/RevPageHeader.vue';
import RevPanel from '@/Components/Revisor/RevPanel.vue';
import RevTable from '@/Components/Revisor/RevTable.vue';
import RevBadge from '@/Components/Revisor/RevBadge.vue';
import RevButton from '@/Components/Revisor/RevButton.vue';
import RevBanner from '@/Components/Revisor/RevBanner.vue';
import RevIcon from '@/Components/Revisor/RevIcon.vue';
import RevAvatar from '@/Components/Revisor/RevAvatar.vue';
import RevMeter from '@/Components/Revisor/RevMeter.vue';
import RevField from '@/Components/Revisor/RevField.vue';
import RevSearch from '@/Components/Revisor/RevSearch.vue';
import RevSkeleton from '@/Components/Revisor/RevSkeleton.vue';
import RevEmptyState from '@/Components/Revisor/RevEmptyState.vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const props = defineProps({
  dni: { type: String, default: null },
  solicitudId: { type: [String, Number], default: null },
});

const isProfileMode = computed(() => !!props.dni);

// --- Profile mode state ---
const postulanteData = ref(null);
const documentos = ref([]);
const requisitosData = ref(null);
const loadingDocs = ref(true);
const verifyingId = ref(null);
const previewVisible = ref(false);
const previewUrl = ref('');
const previewTitle = ref('');
const activeDoc = ref(null);

// --- Revision workflow state ---
const revisionData = ref({
  iniciada_at: null,
  finalizada_at: null,
  revisor: null,
  estado: null,
});
const startingRevision = ref(false);
const finishingRevision = ref(false);
const finalizarModalVisible = ref(false);
const loadingCitacion = ref(false);
const citacionSugerida = ref(null);
const rapidValidating = ref(false);
const fechaCaducidadModal = ref(null);
const renotifying = ref(false);
const citacionForm = ref({
  fecha: '',
  hora_inicio: '',
  hora_fin: '',
  lugar: '',
  instrucciones: '',
});

const docsVerificados = computed(() => {
  if (requisitosData.value?.requisitos) {
    let count = 0;
    requisitosData.value.requisitos.forEach(r => {
      r.tipos_documento.forEach(td => {
        if (td.documentos) {
          count += td.documentos.filter(d => d.valido).length;
        }
      });
    });
    return count;
  }
  return documentos.value.filter(d => d.valido).length;
});

const totalDocs = computed(() => {
  if (requisitosData.value?.requisitos) {
    let count = 0;
    requisitosData.value.requisitos.forEach(r => {
      r.tipos_documento.forEach(td => {
        if (td.documentos) count += td.documentos.length;
      });
    });
    return count;
  }
  return documentos.value.length;
});
const revisionSolicitada = computed(() => {
  if (postulanteData.value?.revision_solicitada == true || postulanteData.value?.revision_solicitada == 1) return true;
  // getPostulanteByDni (pre_inscripcion) no trae campos de revisión; usar la fuente real
  if (requisitosData.value?.postulante?.revision_solicitada == true || requisitosData.value?.postulante?.revision_solicitada == 1) return true;
  if (revisionData.value?.estado && revisionData.value.estado !== 'completada') return true;
  return false;
});
const revisionEnCurso = computed(() => revisionData.value.iniciada_at && !revisionData.value.finalizada_at);

// Solo requisitos que tienen documentos subidos, con tipos filtrados a los subidos únicamente
const requisitosConDocs = computed(() => {
  if (!requisitosData.value?.requisitos) return [];
  return requisitosData.value.requisitos
    .map(req => ({
      ...req,
      tipos_subidos: (req.tipos_documento || []).filter(td => td.subido),
    }))
    .filter(req => req.tipos_subidos.length > 0);
});

// --- Observation state ---
const observandoDocId = ref(null);
const observacionText = ref(null);

const loadPostulanteData = async () => {
  if (!props.dni) return;
  loadingDocs.value = true;
  try {
    const [postRes, docsRes, reqRes] = await Promise.allSettled([
      axios.get(`/revisor/get-postulante-dni/${props.dni}`),
      axios.get(`/revisor/get-documentos-postulante/${props.dni}`),
      axios.get(`/revisor/documentos-requisitos/${props.dni}`, {
        params: { solicitud: props.solicitudId },
      }),
    ]);

    // Postulante data: usar getPostulanteByDni o fallback a datos de documentosPorRequisitos
    let pData = null;
    if (postRes.status === 'fulfilled') {
      pData = postRes.value.data?.datos || null;
    }
    if (!pData && reqRes.status === 'fulfilled') {
      pData = reqRes.value.data?.data?.postulante || reqRes.value.data?.datos?.postulante || null;
    }
    postulanteData.value = pData;

    const d = pData;
    if (d) {
      const solicitada = d.revision_solicitada == true || d.revision_solicitada == 1;
      revisionData.value = {
        iniciada_at: d.revision_iniciada_at || null,
        finalizada_at: d.revision_finalizada_at || null,
        revisor: d.revision_revisor_id || null,
        estado: d.revision_estado || (solicitada ? (d.revision_iniciada_at ? 'en_revision' : 'solicitada') : 'completada'),
      };
    }

    // Documentos planos (para el modal de finalizar)
    if (docsRes.status === 'fulfilled') {
      documentos.value = docsRes.value.data?.datos || [];
    }

    // Requisitos (ApiResponse devuelve `data`, no `datos`)
    if (reqRes.status === 'fulfilled') {
      requisitosData.value = reqRes.value.data?.data || reqRes.value.data?.datos || null;
      // Siempre actualizar revisionData desde documentosPorRequisitos (que tiene los datos de revisión reales)
      // getPostulanteByDni NO incluye campos de revisión, por lo que no podemos depender de él
      const pd = reqRes.value.data?.data?.postulante || reqRes.value.data?.datos?.postulante;
      if (pd) {
        // Si no teníamos postulanteData del otro endpoint, usar el de requisitos
        if (!postulanteData.value) {
          postulanteData.value = pd;
        }
        const solicitada = pd.revision_solicitada == true || pd.revision_solicitada == 1;
        revisionData.value = {
          iniciada_at: pd.revision_iniciada_at || null,
          finalizada_at: pd.revision_finalizada_at || null,
          revisor: pd.revision_revisor_id || null,
          estado: pd.revision_estado || (solicitada ? (pd.revision_iniciada_at ? 'en_revision' : 'solicitada') : 'completada'),
        };
      }
    } else {
      notification.error({
        message: 'Error',
        description: 'No se pudieron cargar los documentos del postulante',
      });
    }
  } catch (e) {
    notification.error({
      message: 'Error',
      description: 'No se pudieron cargar los datos del postulante',
    });
  } finally {
    loadingDocs.value = false;
  }
};

const previewDoc = (doc) => {
  activeDoc.value = { id: doc.id, nombre: doc.nombre, tipo: doc.tipo || doc.nombre, verificado: doc.valido ? 1 : 0, apto_revision: doc.apto_revision, valido: doc.valido, fecha_caducidad: doc.fecha_caducidad };
  previewUrl.value = `/revisor/preview-documento-revisor/${doc.id}`;
  previewTitle.value = doc.tipo || doc.nombre || 'Documento';
  previewVisible.value = true;
};

const previewReqDoc = (td, doc) => {
  activeDoc.value = { id: doc.id, nombre: doc.nombre, tipo: td.nombre, verificado: doc.valido ? 1 : 0, apto_revision: doc.apto_revision, valido: doc.valido, fecha_caducidad: doc.fecha_caducidad, observacion_revisor: doc.observacion_revisor };
  fechaCaducidadModal.value = doc.fecha_caducidad || null;
  observandoDocId.value = null;
  observacionText.value = null;
  previewUrl.value = `/revisor/preview-documento-revisor/${doc.id}`;
  previewTitle.value = td.nombre + ' — ' + doc.nombre;
  previewVisible.value = true;
};

const cambiarEstadoDoc = async (td, doc) => {
  verifyingId.value = doc.id;
  try {
    let accion;
    if (doc.valido) {
      accion = 'desmarcar';
    } else if (doc.apto_revision) {
      accion = 'valido';
    } else {
      accion = 'apto_revision';
    }

    // Si es "valido", usar la fecha del modal si está disponible
    let fechaCaducidad = null;
    if (accion === 'valido') {
      fechaCaducidad = fechaCaducidadModal.value;
    }

    const res = await axios.post('/revisor/cambiar-estado-documento', {
      id_documento: doc.id,
      accion: accion,
      fecha_caducidad: fechaCaducidad,
    });

    if (res.data.success) {
      // Actualizar el documento en requisitosData
      if (requisitosData.value?.requisitos) {
        requisitosData.value.requisitos.forEach(r => {
          r.tipos_documento.forEach(t => {
            if (t.documentos) {
              t.documentos.forEach(d => {
                if (d.id === doc.id) {
                  d.apto_revision = res.data.datos.apto_revision;
                  d.valido = res.data.datos.valido;
                  if (res.data.datos.fecha_caducidad) d.fecha_caducidad = res.data.datos.fecha_caducidad;
                  // Recalcular vigencia
                  const v = vigenciaBadge(d);
                  d.vigente = v.vigente;
                  d.por_vencer = v.por_vencer;
                  d.caducado = v.caducado;
                }
              });
              t.verificado = t.documentos.some(d => d.valido);
            }
          });
          r.verificado = r.tipos_documento.some(t => t.subido && t.verificado);
        });
      }

      // Actualizar activeDoc si está en el modal
      if (activeDoc.value && activeDoc.value.id === doc.id) {
        activeDoc.value.apto_revision = res.data.datos.apto_revision;
        activeDoc.value.valido = res.data.datos.valido;
        activeDoc.value.verificado = res.data.datos.valido ? 1 : 0;
        if (res.data.datos.fecha_caducidad) activeDoc.value.fecha_caducidad = res.data.datos.fecha_caducidad;
      }

      notification.success({
        message: 'Actualizado',
        description: res.data.mensaje,
      });
    }
  } catch (e) {
    notification.error({
      message: 'Error',
      description: e.response?.data?.mensaje || 'No se pudo actualizar el documento',
    });
  } finally {
    verifyingId.value = null;
  }
};

const toggleObservacion = (doc) => {
  if (observandoDocId.value === doc.id) {
    observandoDocId.value = null;
    observacionText.value = null;
  } else {
    observandoDocId.value = doc.id;
    observacionText.value = doc.observacion_revisor || '';
  }
};

const guardarObservacion = async (doc) => {
  try {
    const res = await axios.post('/revisor/observar-documento', {
      id_documento: doc.id,
      observacion: observacionText.value,
      solicitud_id: props.solicitudId,
    });

    if (res.data.success) {
      // Actualizar en requisitosData
      if (requisitosData.value?.requisitos) {
        requisitosData.value.requisitos.forEach(r => {
          r.tipos_documento.forEach(t => {
            if (t.documentos) {
              t.documentos.forEach(d => {
                if (d.id === doc.id) {
                  d.observacion_revisor = res.data.datos.observacion_revisor;
                }
              });
            }
          });
        });
      }
      // Actualizar activeDoc si está en el modal
      if (activeDoc.value && activeDoc.value.id === doc.id) {
        activeDoc.value.observacion_revisor = res.data.datos.observacion_revisor;
      }
      observandoDocId.value = null;
      observacionText.value = null;
      notification.success({ message: 'Observación guardada', description: res.data.mensaje });
    }
  } catch (e) {
    notification.error({ message: 'Error', description: e.response?.data?.mensaje || 'No se pudo guardar la observación' });
  }
};

const revisionRapida = async () => {
  rapidValidating.value = true;
  try {
    const res = await axios.post(`/revisor/revision-rapida/${props.dni}`, {
      solicitud_id: props.solicitudId,
    });
    if (res.data.success) {
      notification.success({
        message: 'Revisión rápida completada',
        description: res.data.mensaje,
      });
      // Recargar datos para reflejar los cambios
      await loadPostulanteData();
    }
  } catch (e) {
    notification.error({
      message: 'Error',
      description: e.response?.data?.mensaje || 'No se pudo completar la revisión rápida',
    });
  } finally {
    rapidValidating.value = false;
  }
};

const vigenciaBadge = (doc) => {
  if (!doc.fecha_caducidad) return { text: 'Sin vencimiento', class: 'vig-none', vigente: true, por_vencer: false, caducado: false };
  const hoy = new Date();
  hoy.setHours(0, 0, 0, 0);
  const fecha = new Date(doc.fecha_caducidad + 'T00:00:00');
  const en30dias = new Date(hoy);
  en30dias.setDate(en30dias.getDate() + 30);

  if (fecha < hoy) return { text: 'Caducado', class: 'vig-caducado', vigente: false, por_vencer: false, caducado: true };
  if (fecha <= en30dias) return { text: 'Por vencer', class: 'vig-por-vencer', vigente: true, por_vencer: true, caducado: false };
  return { text: 'Vigente', class: 'vig-vigente', vigente: true, por_vencer: false, caducado: false };
};

const goBack = () => {
  router.visit('/revisor/solicitudes-revision');
};

// --- Revision workflow methods ---
const iniciarRevision = async () => {
  startingRevision.value = true;
  try {
    const res = await axios.post(`/revisor/iniciar-revision/${props.dni}`, {
      solicitud_id: props.solicitudId,
    });
    revisionData.value.iniciada_at = res.data.iniciada_at;
    notification.success({
      message: 'Revisión iniciada',
      description: 'Puede comenzar a revisar los documentos del postulante.',
    });
  } catch (e) {
    notification.error({
      message: 'Error',
      description: e.response?.data?.mensaje || 'No se pudo iniciar la revisión',
    });
  } finally {
    startingRevision.value = false;
  }
};

const renotificarPostulante = async () => {
  renotifying.value = true;
  try {
    const res = await axios.post(`/revisor/renotificar-postulante/${props.dni}`, {
      solicitud_id: props.solicitudId,
    });
    notification.success({
      message: 'Recordatorio enviado',
      description: res.data.mensaje,
    });
  } catch (e) {
    notification.error({
      message: 'Error',
      description: e.response?.data?.mensaje || 'No se pudo enviar el recordatorio',
    });
  } finally {
    renotifying.value = false;
  }
};

const abrirModalFinalizar = async () => {
  finalizarModalVisible.value = true;
  loadingCitacion.value = true;

  // Pre-llenar con datos de documentos actuales
  citacionForm.value = {
    fecha: '',
    hora_inicio: '',
    hora_fin: '',
    lugar: '',
    instrucciones: 'Acercarse a la Dirección de Admisión con todos los documentos originales y copias simples.',
  };

  // Buscar citación sugerida
  try {
    const res = await axios.get(`/revisor/citacion-sugerida/${props.dni}`);
    if (res.data.success && res.data.citacion) {
      const c = res.data.citacion;
      citacionSugerida.value = c;
      citacionForm.value = {
        fecha: c.fecha || '',
        hora_inicio: c.hora_inicio || '',
        hora_fin: c.hora_fin || '',
        lugar: c.lugar || '',
        instrucciones: c.instrucciones || citacionForm.value.instrucciones,
      };
    }
  } catch {
    citacionSugerida.value = null;
  } finally {
    loadingCitacion.value = false;
  }
};

const finalizarRevision = async () => {
  // Si todos los docs están válidos, requerir datos de citación
  const requiereCitacion = totalDocs.value === docsVerificados.value;

  if (requiereCitacion) {
    if (!citacionForm.value.fecha || !citacionForm.value.hora_inicio || !citacionForm.value.hora_fin || !citacionForm.value.lugar) {
      notification.warning({
        message: 'Datos incompletos',
        description: 'Complete todos los campos obligatorios de la citación.',
      });
      return;
    }
  }

  finishingRevision.value = true;
  try {
    const payload = requiereCitacion
      ? { ...citacionForm.value, solicitud_id: props.solicitudId }
      : { solicitud_id: props.solicitudId };

    const res = await axios.post(`/revisor/finalizar-revision/${props.dni}`, payload);
    revisionData.value.finalizada_at = res.data.finalizada_at;
    revisionData.value.estado = res.data.resultado;
    finalizarModalVisible.value = false;

    if (res.data.resultado === 'completada') {
      notification.success({
        message: 'Revisión completada — Sin novedades',
        description: 'Todos los documentos válidos. Se notificó al postulante para citación presencial.',
      });
    } else {
      notification.warning({
        message: 'Revisión finalizada con observaciones',
        description: res.data.mensaje,
      });
    }

    setTimeout(() => router.visit(route('revisor.solicitudes-revision')), 1500);
  } catch (e) {
    notification.error({
      message: 'Error',
      description: e.response?.data?.mensaje || 'No se pudo finalizar la revisión',
    });
  } finally {
    finishingRevision.value = false;
  }
};

// --- List mode state ---
const buscar = ref('');
const certificados = ref([]);
const totalpaginas = ref(0);
const pagina = ref(1);
const paginasize = ref(20);
const requisitos = ref([]);
const valores = ref([]);
const postulantes = ref([]);

const getPostulantes = async () => {
  let res = await axios.post(`get-postulantes-requisitos?page=${pagina.value}`, { term: buscar.value, paginasize: paginasize.value });
  postulantes.value = res.data.datos.data;
  totalpaginas.value = res.data.datos.total;
};

const getRequisitos = async () => {
  let res = await axios.get('get-requisitos');
  requisitos.value = res.data.datos;
};

watch(buscar, () => getPostulantes());
watch(pagina, () => getPostulantes());
watch(paginasize, () => getPostulantes());

const columns = [
  { title: 'Nombres', dataIndex: 'nombres', key: 'nombres', align: 'left', width: '400px' },
  { title: 'Requisitos', dataIndex: 'requisitos', key: 'requisitos' },
];

onMounted(() => {
  if (isProfileMode.value) {
    loadPostulanteData();
  } else {
    getPostulantes();
    getRequisitos();
  }
});

/* ══════════════════════════ Derivados de presentación ══════════════════════
   Nada de lógica de negocio aquí: sólo lo que la interfaz necesita para
   nombrar estados y contar cosas sin repetir expresiones en la plantilla. */

const nombreCompleto = computed(() => {
  const p = postulanteData.value;
  if (!p) return '';
  return [p.nombres, p.primer_apellido || p.paterno, p.segundo_apellido || p.materno]
    .filter(Boolean).join(' ').trim();
});

const docsPendientes = computed(() => Math.max(0, totalDocs.value - docsVerificados.value));

/** Un único lugar donde se traduce el estado del flujo a lenguaje humano. */
const estadoRevision = computed(() => {
  if (revisionData.value.finalizada_at && revisionData.value.estado === 'completada')
    return { key: 'completada', tone: 'success', label: 'Atendida' };
  if (revisionData.value.finalizada_at && revisionData.value.estado === 'pendiente')
    return { key: 'pendiente', tone: 'warning', label: 'Con observaciones' };
  if (revisionEnCurso.value)
    return { key: 'en_curso', tone: 'progress', label: 'En curso' };
  if (revisionSolicitada.value)
    return { key: 'solicitada', tone: 'warning', label: 'Pendiente' };
  return { key: 'inactiva', tone: 'neutral', label: 'Sin revisión activa' };
});

const vigenciaTone = (doc) => {
  const v = vigenciaBadge(doc);
  if (v.caducado) return 'danger';
  if (v.por_vencer) return 'warning';
  return v.text === 'Sin vencimiento' ? 'neutral' : 'success';
};

const contarDocs = (req) =>
  (req.tipos_subidos || []).reduce((n, td) => n + ((td.documentos || []).length), 0);

/* ── Observación desde el visor ─────────────────────────────────────────── */
const observacionModal = ref('');

watch(activeDoc, (doc) => { observacionModal.value = doc?.observacion_revisor || ''; });

const guardarObservacionModal = async () => {
  if (!activeDoc.value) return;
  observacionText.value = observacionModal.value;
  await guardarObservacion(activeDoc.value);
  observacionText.value = null;
};

/* ── Modo listado ───────────────────────────────────────────────────────── */
const parseReqs = (raw) => {
  if (Array.isArray(raw)) return raw;
  try { return JSON.parse(raw || '[]') || []; } catch { return []; }
};

const listColumns = [
  { key: 'postulante', title: 'Postulante', width: '340px' },
  { key: 'requisitos', title: 'Requisitos cumplidos' },
];
</script>

<style scoped>
/* ══════════════════════════════ MODO FICHA ═════════════════════════════ */
.pr { display: flex; flex-direction: column; gap: var(--rev-s-5); }

/* Barra de identidad: se mantiene visible mientras se recorre la lista ---- */
.pr-bar {
  position: sticky; top: 0; z-index: var(--rev-z-sticky);
  display: flex; align-items: center; gap: var(--rev-s-5);
  padding: 10px var(--rev-s-6);
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-sm);
}
.pr-bar-sep { height: 26px; }
.pr-identity { min-width: 0; flex: 1 1 auto; }
.pr-identity-row { display: flex; align-items: baseline; gap: var(--rev-s-4); flex-wrap: wrap; }
.pr-name {
  margin: 0; font-size: var(--rev-fs-xl); font-weight: 660;
  letter-spacing: -.018em; color: var(--rev-ink); line-height: 1.25;
  text-transform: capitalize;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 44ch;
}
.pr-dni {
  font-size: var(--rev-fs-sm); font-weight: 600; color: var(--rev-ink-3);
  background: var(--rev-n-100); border: 1px solid var(--rev-line);
  padding: 1px 6px; border-radius: var(--rev-r-sm);
}
.pr-identity-meta { display: flex; align-items: center; gap: var(--rev-s-5); flex-wrap: wrap; margin-top: 3px; }
.pr-meta-item {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: var(--rev-fs-sm); color: var(--rev-ink-3);
}
.pr-bar-right { display: flex; align-items: center; gap: var(--rev-s-4); flex: none; }

/* Banda de progreso ------------------------------------------------------ */
.pr-progress {
  display: flex; align-items: center; gap: var(--rev-s-7);
  padding: 12px var(--rev-s-6);
  background: var(--rev-surface);
  border: 1px solid var(--rev-line);
  border-radius: var(--rev-r-lg);
  box-shadow: var(--rev-sh-xs);
}
.pr-progress-block { flex: 1 1 0; min-width: 160px; }
.pr-progress-action { flex: none; margin-left: auto; }
.pr-pending { color: var(--rev-warning-ink); font-weight: 680; }

/* Requisitos -------------------------------------------------------------- */
.pr-reqs { display: flex; flex-direction: column; gap: var(--rev-s-5); }
.pr-req { transition: border-color var(--rev-t-base) var(--rev-ease); }
.pr-req.is-done { border-color: var(--rev-success-border); }
.pr-req :deep(.rev-panel-head) { padding-top: 9px; padding-bottom: 9px; gap: var(--rev-s-5); }
.pr-req.is-done :deep(.rev-panel-head) { background: var(--rev-success-bg); border-bottom-color: var(--rev-success-border); }

.pr-req-head { display: flex; align-items: center; gap: var(--rev-s-5); min-width: 0; }
.pr-req-num {
  flex: none; display: grid; place-items: center;
  width: 24px; height: 24px; border-radius: var(--rev-r-md);
  background: var(--rev-n-100); color: var(--rev-ink-3);
  font-size: var(--rev-fs-sm); font-weight: 680;
  border: 1px solid var(--rev-line);
  transition: background var(--rev-t-base) var(--rev-ease), color var(--rev-t-base) var(--rev-ease);
}
.pr-req-num.is-done { background: var(--rev-success); color: #fff; border-color: var(--rev-success); }
.pr-req-titles { min-width: 0; }
.pr-req-name {
  margin: 0; font-size: var(--rev-fs-lg); font-weight: 620;
  letter-spacing: -.012em; color: var(--rev-ink); line-height: 1.25;
}
.pr-req-sub { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); }

/* Tipo de documento ------------------------------------------------------- */
.pr-type + .pr-type { border-top: 1px solid var(--rev-line-soft); }
.pr-type-label {
  display: flex; align-items: center; gap: 5px;
  padding: 8px var(--rev-s-6) 4px;
  font-size: var(--rev-fs-2xs); font-weight: 680;
  letter-spacing: var(--rev-track-caps); text-transform: uppercase;
  color: var(--rev-ink-4);
}

/* Fila de documento ------------------------------------------------------- */
.pr-docs { display: flex; flex-direction: column; }
.pr-doc {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto auto;
  align-items: center;
  gap: var(--rev-s-5);
  padding: 8px var(--rev-s-6) 10px;
  border-left: 2px solid transparent;
  transition: background var(--rev-t-fast) var(--rev-ease), border-color var(--rev-t-fast) var(--rev-ease);
}
.pr-doc:hover { background: var(--rev-n-25); }
.pr-doc.is-valid { border-left-color: var(--rev-success); }
.pr-doc.is-observed { border-left-color: var(--rev-warning); }

.pr-doc-file {
  display: flex; align-items: center; gap: 9px; min-width: 0;
  padding: 5px 8px; margin-left: -8px;
  border: 1px solid transparent; border-radius: var(--rev-r-md);
  background: transparent; cursor: pointer; text-align: left;
  font-family: var(--rev-font);
  transition: background var(--rev-t-fast) var(--rev-ease), border-color var(--rev-t-fast) var(--rev-ease);
}
.pr-doc-file:hover { background: var(--rev-surface); border-color: var(--rev-line-strong); }
.pr-doc-file:focus-visible { outline: none; box-shadow: var(--rev-ring); }
.pr-doc-file.is-missing { cursor: default; color: var(--rev-ink-4); }
.pr-doc-icon {
  flex: none; display: grid; place-items: center;
  width: 28px; height: 28px; border-radius: var(--rev-r-md);
  background: var(--rev-n-100); color: var(--rev-ink-3);
}
.pr-doc.is-valid .pr-doc-icon { background: var(--rev-success-bg); color: var(--rev-success); }
.pr-doc-file.is-missing .pr-doc-icon { background: var(--rev-danger-bg); color: var(--rev-danger); }
.pr-doc-name {
  font-size: var(--rev-fs-md); font-weight: 550; color: var(--rev-ink-2);
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0;
}
.pr-doc-open { color: var(--rev-ink-4); opacity: 0; transition: opacity var(--rev-t-fast) var(--rev-ease); }
.pr-doc-file:hover .pr-doc-open { opacity: 1; }
.pr-doc-missing { font-size: var(--rev-fs-sm); color: var(--rev-danger-ink); white-space: nowrap; }

.pr-doc-actions { display: flex; align-items: center; gap: 4px; flex: none; }
.pr-doc-actions :deep(.is-on) { background: var(--rev-warning-bg); color: var(--rev-warning-ink); }

.pr-doc-obs {
  grid-column: 1 / -1;
  display: flex; align-items: flex-start; gap: 6px;
  margin-top: 2px; padding: 6px 9px;
  background: var(--rev-warning-bg);
  border: 1px solid var(--rev-warning-border);
  border-radius: var(--rev-r-md);
  font-size: var(--rev-fs-sm); color: var(--rev-warning-ink); line-height: 1.45;
}

.pr-obs-edit { grid-column: 1 / -1; display: flex; align-items: center; gap: 6px; margin-top: 4px; }
.pr-obs-input {
  flex: 1 1 auto; min-width: 0;
  height: 30px; padding: 0 10px;
  font-family: var(--rev-font); font-size: var(--rev-fs-md); color: var(--rev-ink);
  background: var(--rev-surface);
  border: 1px solid var(--rev-line-strong); border-radius: var(--rev-r-md);
  transition: border-color var(--rev-t-fast) var(--rev-ease), box-shadow var(--rev-t-fast) var(--rev-ease);
}
.pr-obs-input:focus { outline: none; border-color: var(--rev-primary-500); box-shadow: var(--rev-ring); }

/* ══════════════════════════ VISOR DE DOCUMENTO ═════════════════════════ */
.pr-viewer { display: flex; align-items: stretch; gap: 0; height: min(74vh, 680px); margin: -18px; }
.pr-viewer-doc { flex: 1 1 auto; min-width: 0; background: var(--rev-n-100); }
.pr-viewer-doc iframe { width: 100%; height: 100%; border: 0; display: block; }
.pr-viewer-side {
  flex: 0 0 292px; width: 292px;
  display: flex; flex-direction: column; gap: 0;
  padding: var(--rev-s-6);
  border-left: 1px solid var(--rev-line);
  background: var(--rev-surface);
  overflow-y: auto;
}
.pr-viewer-section { display: flex; flex-direction: column; gap: var(--rev-s-4); }
.pr-viewer-docname {
  margin: 0; font-size: var(--rev-fs-md); font-weight: 600; color: var(--rev-ink);
  overflow-wrap: anywhere; line-height: 1.4;
}
.pr-viewer-vig { align-self: flex-start; }
.pr-viewer-foot {
  margin-top: auto; padding-top: var(--rev-s-6);
  display: flex; align-items: center; justify-content: space-between; gap: var(--rev-s-4);
  border-top: 1px solid var(--rev-line);
}

/* ══════════════════════════ FINALIZAR REVISIÓN ═════════════════════════ */
.fin { display: flex; flex-direction: column; gap: var(--rev-s-6); }
.fin-summary { display: grid; grid-template-columns: 1fr 1fr; gap: var(--rev-s-5); }
.fin-cell {
  display: flex; flex-direction: column; gap: 1px;
  padding: 12px var(--rev-s-6);
  border: 1px solid var(--rev-line); border-radius: var(--rev-r-lg);
  background: var(--rev-surface-2);
}
.fin-cell-num { font-size: var(--rev-fs-3xl); font-weight: 660; letter-spacing: -.03em; line-height: 1.1; }
.fin-cell-label { font-size: var(--rev-fs-sm); color: var(--rev-ink-3); }
.fin-cell.is-ok { background: var(--rev-success-bg); border-color: var(--rev-success-border); }
.fin-cell.is-ok .fin-cell-num { color: var(--rev-success-ink); }
.fin-cell.is-warn { background: var(--rev-warning-bg); border-color: var(--rev-warning-border); }
.fin-cell.is-warn .fin-cell-num { color: var(--rev-warning-ink); }
.fin-cell.is-muted .fin-cell-num { color: var(--rev-ink-4); }

.fin-list { list-style: none; margin: 0; padding: 0; }
.fin-list li {
  display: flex; align-items: flex-start; gap: 9px;
  padding: 10px var(--rev-s-6);
  border-bottom: 1px solid var(--rev-line-soft);
  color: var(--rev-warning);
}
.fin-list li:last-child { border-bottom: 0; }
.fin-list strong { display: block; font-size: var(--rev-fs-md); font-weight: 620; color: var(--rev-ink); }
.fin-list-file { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); }
.fin-list-obs { margin: 3px 0 0; font-size: var(--rev-fs-sm); color: var(--rev-ink-3); line-height: 1.45; }
.fin-list-obs.is-empty { color: var(--rev-danger-ink); }

.fin-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--rev-s-5); }
.fin-field { margin-top: var(--rev-s-5); }
.fin-loading { display: flex; align-items: center; gap: 7px; font-size: var(--rev-fs-md); color: var(--rev-ink-3); padding: var(--rev-s-5) 0; }
.fin-hint {
  display: flex; align-items: center; gap: 5px;
  margin: var(--rev-s-5) 0 0; font-size: var(--rev-fs-sm); color: var(--rev-ink-3);
}
.fin-actions { display: flex; justify-content: flex-end; gap: var(--rev-s-4); }

/* ══════════════════════════ MODO LISTADO ═══════════════════════════════ */
.pl { display: flex; flex-direction: column; gap: var(--rev-s-6); }
.pl-search { width: 250px; }
.pl-person { display: flex; flex-direction: column; line-height: 1.3; min-width: 0; }
.pl-dni { font-size: var(--rev-fs-sm); color: var(--rev-ink-4); }
.pl-name {
  font-size: var(--rev-fs-md); font-weight: 600; color: var(--rev-ink);
  text-transform: capitalize; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.pl-reqs { display: flex; flex-wrap: wrap; gap: 4px; }

/* ══════════════════════════ RESPONSIVO ═════════════════════════════════ */
@media (max-width: 1080px) {
  .pr-viewer { flex-direction: column; height: auto; }
  .pr-viewer-doc { height: 52vh; }
  .pr-viewer-side { flex: 1 1 auto; width: 100%; border-left: 0; border-top: 1px solid var(--rev-line); }
}
@media (max-width: 860px) {
  .pr-bar { flex-wrap: wrap; position: static; }
  .pr-bar-right { width: 100%; justify-content: space-between; }
  .pr-progress { flex-direction: column; align-items: stretch; gap: var(--rev-s-5); }
  .pr-progress .rev-divider-v { display: none; }
  .pr-progress-action { margin-left: 0; }
  .pr-doc { grid-template-columns: 1fr; align-items: flex-start; }
  .pr-doc-actions { justify-content: flex-end; width: 100%; }
  .fin-summary, .fin-grid { grid-template-columns: 1fr; }
  .pl-search { width: 100%; }
}
</style>
