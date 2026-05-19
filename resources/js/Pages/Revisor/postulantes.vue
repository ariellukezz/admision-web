<template>
  <Head title="Postulantes" />
  <AuthenticatedLayout pagina="Postulantes">
    <div class="p-page">

      <!-- PROFILE MODE: revisor/postulante/{dni} -->
      <div v-if="isProfileMode" class="profile-mode">

        <div class="profile-header">
          <div class="profile-header-left">
            <button class="back-btn" type="button" @click="goBack">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
              Volver
            </button>
          </div>
          <div class="profile-header-center">
            <h1>Revisión de Documentos</h1>
            <p v-if="postulanteData" class="profile-subtitle">
              {{ postulanteData.nombres }} {{ postulanteData.primer_apellido || postulanteData.paterno }} {{ postulanteData.segundo_apellido || postulanteData.materno }} — DNI: {{ dni }}
            </p>
          </div>
          <div class="profile-header-right">
            <button
              v-if="revisionSolicitada && !revisionData.iniciada_at"
              class="rev-btn start"
              type="button"
              :disabled="startingRevision"
              @click="iniciarRevision"
            >
              <span v-if="startingRevision" class="spinner"></span>
              <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/></svg>
              Iniciar Revisión
            </button>
            <button
              v-else-if="revisionEnCurso"
              class="rev-btn finish"
              type="button"
              @click="abrirModalFinalizar"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
              Finalizar Revisión
            </button>
            <button
              v-else-if="revisionData.finalizada_at && revisionData.estado === 'pendiente'"
              class="rev-btn renotify"
              type="button"
              :disabled="renotifying"
              @click="renotificarPostulante"
            >
              <span v-if="renotifying" class="spinner"></span>
              <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.852 7.5h4.983v4.983m-4.5-4.483L9.5 14M9.5 14a8.25 8.25 0 11-5.5-2.5M9.5 14H4.5"/></svg>
              Re-notificar Postulante
            </button>
            <div v-else-if="revisionData.finalizada_at" class="rev-completed-badge">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Solicitud ya atendida
            </div>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loadingDocs" class="loading-state">
          <a-spin size="large" />
          <p>Cargando documentos...</p>
        </div>

        <!-- Requirements-based document view -->
        <div v-else-if="requisitosData" class="docs-section">

          <!-- Info bar -->
          <div class="info-bar">
            <div class="info-chip" v-if="requisitosData.modalidad_postulante">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A24.18 24.18 0 0 1 12 20.25a24.18 24.18 0 0 1 8.231-3.756 60.4 60.4 0 0 0-.49-6.347m-15.482 0a50.636 50.636 0 0 0-2.522 12.193M12 12.75c-4.04 0-7.5-.78-7.5-2.25s3.46-2.25 7.5-2.25 7.5.78 7.5 2.25-3.46 2.25-7.5 2.25Z"/></svg>
              {{ requisitosData.modalidad_postulante.nombre }}
            </div>
            <div class="info-chip" v-if="requisitosData.programa_postulante">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A24.18 24.18 0 0 1 12 20.25a24.18 24.18 0 0 1 8.231-3.756 60.4 60.4 0 0 0-.49-6.347m-15.482 0a50.636 50.636 0 0 0-2.522 12.193M12 12.75c-4.04 0-7.5-.78-7.5-2.25s3.46-2.25 7.5-2.25 7.5.78 7.5 2.25-3.46 2.25-7.5 2.25Z"/></svg>
              {{ requisitosData.programa_postulante.nombre_corto || requisitosData.programa_postulante.nombre }}
            </div>
            <div class="info-chip accent">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
              {{ requisitosData.requisitos_cumplidos }}/{{ requisitosData.total_requisitos }} cumplidos
            </div>
            <button
              v-if="revisionEnCurso"
              class="rapid-btn"
              type="button"
              :disabled="rapidValidating"
              @click="revisionRapida"
            >
              <span v-if="rapidValidating" class="spinner"></span>
              <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
              Validar Todos
            </button>
          </div>

          <!-- Status banner -->
          <div v-if="revisionEnCurso" class="status-banner info">
            <div class="status-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
            </div>
            <div class="status-text">
              <span class="status-title">Revisión en curso</span>
              <span class="status-desc">Revisa cada documento. Marca como válido o agrega observaciones. Al finalizar, si todos están válidos se agendará la citación presencial; si hay observaciones, el postulante deberá corregirlos.</span>
            </div>
          </div>
          <div v-else-if="revisionData.finalizada_at && revisionData.estado === 'pendiente'" class="status-banner warning">
            <div class="status-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            </div>
            <div class="status-text">
              <span class="status-title">Pendiente — Documentos por corregir</span>
              <span class="status-desc">El postulante tiene {{ totalDocs - docsVerificados }} documento(s) observado(s). Debe corregirlos y solicitar nuevamente revisión. Puedes re-notificarle para que actualice sus documentos.</span>
            </div>
          </div>
          <div v-else-if="revisionData.finalizada_at && revisionData.estado === 'completada'" class="status-banner success">
            <div class="status-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="status-text">
              <span class="status-title">Solicitud ya atendida</span>
              <span class="status-desc">Esta solicitud de revisión fue completada. Todos los documentos fueron validados y el postulante fue notificado para su citación presencial.</span>
            </div>
          </div>
          <div v-else-if="!revisionSolicitada && !revisionData.iniciada_at" class="status-banner info">
            <div class="status-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
            </div>
            <div class="status-text">
              <span class="status-title">No hay revisión activa</span>
              <span class="status-desc">Esta solicitud ya fue atendida o el postulante no ha solicitado revisión.</span>
            </div>
          </div>

          <!-- Requirements list — only uploaded documents -->
          <div class="req-list">
            <div
              v-for="req in requisitosConDocs"
              :key="req.id"
              class="req-card"
              :class="{ verified: req.cumplido && req.verificado, pending: !req.cumplido || !req.verificado, na: req.no_aplica }"
            >
              <!-- Card header -->
              <div class="req-head">
                <div class="req-num">{{ req.orden }}</div>
                <div class="req-head-info">
                  <h3 class="req-title">{{ req.nombre }}</h3>
                  <div class="req-badges">
                    <span class="badge-pill" :class="{ obligatory: req.obligatorio_para_postulante, optional: !req.obligatorio_para_postulante && !req.no_aplica, na: req.no_aplica }">
                      {{ req.no_aplica ? 'No aplica' : req.obligatorio_para_postulante ? 'Obligatorio' : 'Opcional' }}
                    </span>
                    <span class="badge-pill status" :class="{ ok: req.cumplido && req.verificado, partial: req.cumplido && !req.verificado, missing: !req.cumplido }">
                      {{ req.cumplido && req.verificado ? 'Verificado' : req.cumplido ? 'Subido' : 'Faltante' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Card body — only uploaded document types -->
              <div class="req-body">
                <div v-for="td in req.tipos_subidos" :key="td.id" class="doc-type-block">
                  <div class="doc-type-label">{{ td.nombre }}</div>
                  <div class="doc-files">
                    <div v-for="doc in td.documentos" :key="doc.id" class="doc-file">
                      <!-- File preview button -->
                      <button v-if="doc.url" class="file-chip" type="button" @click="previewReqDoc(td, doc)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        <span>{{ doc.nombre }}</span>
                      </button>
                      <span v-else class="file-chip no-url">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <span>{{ doc.nombre }}</span>
                      </span>

                      <!-- Vigency badge -->
                      <span v-if="doc.fecha_caducidad" class="vig-badge" :class="vigenciaBadge(doc).class">{{ vigenciaBadge(doc).text }}</span>

                      <!-- Observation toggle -->
                      <button v-if="revisionEnCurso" type="button" class="obs-toggle-btn" :class="{ active: observandoDocId === doc.id }" @click="toggleObservacion(doc)" title="Observar documento">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7.5 8.25v4.5m0-4.5h4.5m-4.5 0L15 15.75M7.5 8.25 3 12.75m4.5 4.5L15 8.25"/></svg>
                        Observar
                      </button>

                      <!-- Validation button -->
                      <button
                        type="button"
                        class="status-btn"
                        :class="{ verified: doc.valido, apto: doc.apto_revision && !doc.valido, pending: !doc.apto_revision }"
                        :disabled="verifyingId === doc.id || !revisionEnCurso"
                        @click="cambiarEstadoDoc(td, doc)"
                      >
                        <span v-if="verifyingId === doc.id" class="spinner"></span>
                        <svg v-else-if="doc.valido" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        V°B°
                      </button>

                      <!-- Observation display (when doc has an observation and input is not open) -->
                      <div v-if="doc.observacion_revisor && observandoDocId !== doc.id" class="obs-display">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7.5 8.25v4.5m0-4.5h4.5m-4.5 0L15 15.75M7.5 8.25 3 12.75m4.5 4.5L15 8.25"/></svg>
                        <span>{{ doc.observacion_revisor }}</span>
                      </div>
                    </div>

                    <!-- Inline observation input -->
                    <div v-if="observandoDocId && td.documentos.some(d => d.id === observandoDocId)" class="obs-inline">
                      <input
                        type="text"
                        v-model="observacionText"
                        class="obs-input"
                        placeholder="Ej: Documento borroso, no se lee claramente..."
                        @keyup.enter="guardarObservacion(td.documentos.find(d => d.id === observandoDocId))"
                      />
                      <button type="button" class="obs-save" @click="guardarObservacion(td.documentos.find(d => d.id === observandoDocId))">Guardar</button>
                      <button type="button" class="obs-cancel" @click="observandoDocId = null; observacionText = null">Cancelar</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty -->
        <div v-else class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
          </div>
          <h3>No hay documentos subidos</h3>
          <p>Este postulante aún no ha subido documentos para revisión.</p>
        </div>

        <!-- Preview Modal -->
        <a-modal
          v-model:open="previewVisible"
          :title="previewTitle"
          width="85%"
          style="top: 20px;"
          :footer="null"
          @cancel="previewVisible = false"
        >
          <div class="modal-body-wrap">
            <!-- Existing observation display (above footer for visibility) -->
            <div v-if="activeDoc?.observacion_revisor && !(observandoDocId && observandoDocId === activeDoc.id)" class="modal-obs-display">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7.5 8.25v4.5m0-4.5h4.5m-4.5 0L15 15.75M7.5 8.25 3 12.75m4.5 4.5L15 8.25"/></svg>
              <span>{{ activeDoc.observacion_revisor }}</span>
            </div>

            <iframe
              v-if="previewUrl"
              :src="previewUrl"
              width="100%"
              height="70vh"
              style="border: none; border-radius: 8px; min-height: 500px;"
            ></iframe>
            <div class="modal-footer-bar">
              <div class="modal-caducidad-field" v-if="activeDoc">
                <label>Fecha de caducidad:</label>
                <input type="date" v-model="fechaCaducidadModal" class="fin-input" :disabled="!revisionEnCurso" />
              </div>
              <a :href="`/revisor/descargar-documento-revisor/${activeDoc?.id}`" class="doc-btn download">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Descargar
              </a>
              <button
                v-if="activeDoc && revisionEnCurso"
                type="button"
                class="obs-btn-modal"
                @click="toggleObservacion(activeDoc)"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7.5 8.25v4.5m0-4.5h4.5m-4.5 0L15 15.75M7.5 8.25L3 12.75m4.5 4.5L15 8.25"/></svg>
                {{ activeDoc.observacion_revisor ? 'Editar observación' : 'Observar' }}
              </button>
              <button
                v-if="activeDoc"
                type="button"
                class="verify-btn"
                :class="{ verified: activeDoc.valido, apto: activeDoc.apto_revision && !activeDoc.valido, pending: !activeDoc.apto_revision }"
                :disabled="verifyingId === activeDoc.id || !revisionEnCurso"
                @click="cambiarEstadoDoc({ nombre: activeDoc.tipo }, activeDoc)"
              >
                <span v-if="verifyingId === activeDoc.id" class="spinner"></span>
                <svg v-else-if="activeDoc.valido" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                V°B°
              </button>
            </div>
            <!-- Observation input field in modal -->
            <div v-if="observandoDocId && activeDoc && observandoDocId === activeDoc.id" class="modal-obs-field">
              <input
                type="text"
                v-model="observacionText"
                class="obs-input"
                placeholder="Ej: Documento borroso, no se lee claramente..."
                @keyup.enter="guardarObservacion(activeDoc)"
              />
              <button type="button" class="obs-save-btn" @click="guardarObservacion(activeDoc)">Guardar</button>
              <button type="button" class="obs-cancel-btn" @click="observandoDocId = null; observacionText = null">Cancelar</button>
            </div>
          </div>
        </a-modal>

        <!-- Finalizar Revisión Modal -->
        <a-modal
          v-model:open="finalizarModalVisible"
          title="Finalizar Revisión y Notificar Postulante"
          width="700px"
          style="top: 30px;"
          :footer="null"
          @cancel="finalizarModalVisible = false"
        >
          <div class="finalizar-modal">
            <!-- Resumen de documentos -->
            <div class="fin-section">
              <h3 class="fin-section-title">Resumen de documentos</h3>
              <div class="fin-docs-summary">
                <div class="fin-docs-col approved">
                  <span class="fin-docs-count">{{ docsVerificados }}</span>
                  <span class="fin-docs-label">Documentos válidos</span>
                </div>
                <div class="fin-docs-col pending">
                  <span class="fin-docs-count">{{ totalDocs - docsVerificados }}</span>
                  <span class="fin-docs-label">Observados</span>
                </div>
              </div>

              <!-- Pending docs list -->
              <div v-if="totalDocs - docsVerificados > 0" class="fin-docs-list">
                <p class="fin-docs-list-title">Documentos observados (el postulante deberá corregir):</p>
                <ul>
                  <template v-for="req in requisitosConDocs" :key="req.id">
                    <template v-for="td in req.tipos_subidos" :key="td.id">
                      <li v-for="doc in (td.documentos || []).filter(d => !d.valido)" :key="doc.id">
                        {{ td.nombre }} — {{ doc.nombre }}
                        <span v-if="doc.observacion_revisor" class="fin-obs-note">({{ doc.observacion_revisor }})</span>
                      </li>
                    </template>
                  </template>
                  <template v-if="!requisitosConDocs.length">
                    <li v-for="doc in documentos.filter(d => !d.valido)" :key="doc.id">
                      {{ doc.tipo || doc.nombre }}
                    </li>
                  </template>
                </ul>
                <p class="fin-result-hint warning">Al finalizar, la revisión quedará en estado <strong>pendiente</strong> y se notificará al postulante para que corrija sus documentos.</p>
              </div>

              <!-- All valid message -->
              <div v-else class="fin-result-hint success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Todos los documentos están válidos. Al finalizar se agendará la citación presencial y se notificará al postulante sin novedades.
              </div>
            </div>

            <!-- Datos de citación — solo si todos los docs están válidos -->
            <div class="fin-section" v-if="totalDocs === docsVerificados">
              <h3 class="fin-section-title">Datos de citación presencial</h3>
              <div v-if="loadingCitacion" class="fin-citacion-loading">
                <a-spin size="small" />
                <span>Calculando cita automática...</span>
              </div>
              <div v-else class="fin-citacion-form">
                <div class="fin-form-row">
                  <div class="fin-form-group">
                    <label>Fecha de cita *</label>
                    <input type="date" v-model="citacionForm.fecha" class="fin-input" />
                  </div>
                  <div class="fin-form-group">
                    <label>Hora inicio *</label>
                    <input type="time" v-model="citacionForm.hora_inicio" class="fin-input" />
                  </div>
                  <div class="fin-form-group">
                    <label>Hora fin *</label>
                    <input type="time" v-model="citacionForm.hora_fin" class="fin-input" />
                  </div>
                </div>
                <div class="fin-form-group">
                  <label>Lugar *</label>
                  <input type="text" v-model="citacionForm.lugar" class="fin-input" placeholder="Ej: Dirección de Admisión - UNAP" />
                </div>
                <div class="fin-form-group">
                  <label>Instrucciones</label>
                  <textarea v-model="citacionForm.instrucciones" class="fin-textarea" rows="3"
                    placeholder="Ej: Acercarse con documentos originales y copias simples. Traer recibo de pago."></textarea>
                </div>
                <div v-if="citacionSugerida" class="fin-citacion-hint">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                  Cita auto-calculada por: <strong>{{ citacionSugerida.tipo_criterio }}</strong>
                  <span v-if="citacionSugerida.valor"> ({{ citacionSugerida.valor }})</span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="fin-actions">
              <button class="fin-btn-cancel" type="button" @click="finalizarModalVisible = false">Cancelar</button>
              <button class="fin-btn-confirm" type="button" :disabled="finishingRevision || loadingCitacion" @click="finalizarRevision">
                <span v-if="finishingRevision" class="spinner"></span>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                {{ totalDocs === docsVerificados ? 'Confirmar y Notificar' : 'Finalizar con Observaciones' }}
              </button>
            </div>
          </div>
        </a-modal>
      </div>

      <!-- LIST MODE: /revisor/postulantes -->
      <div v-else>
        <a-card style="background: white;" class="m-0 p-0">
          <div class="mb-2 flex justify-end">
            <a-input placeholder="Buscar" v-model:value="buscar" style="max-width: 300px;">
              <template #suffix>
                <search-outlined />
              </template>
            </a-input>
          </div>

          <a-table size="small" :dataSource="postulantes" :columns="columns" :pagination="false">
            <template #bodyCell="{ column, text, record }">
              <template v-if="column.dataIndex === 'nombres'">
                <div class="flex">
                  <span style="text-transform: uppercase; font-size: .9rem;"> {{ record.dni}} {{ record.nombres }} {{ record.paterno }} {{ record.materno }}</span>
                </div>
              </template>

              <template v-if="column.dataIndex === 'requisitos'">
                <div style="height: 20px;">
                  <div style="display: none;">
                    {{ valores = JSON.parse(record.requisitos) }}
                  </div>
                  <a-checkbox-group v-model:value="valores" class="checkbox-group-vertical">
                    <a-checkbox v-for="(option, index) in requisitos" :key="option.value" :value="option.value" :class="{ 'first-item': index === 0 }" class="checkbox-item">
                      <div style="height: 20px; border-right: 1px #d9d9d9 solid; margin-right: 10px; padding-right: 20px;">
                        {{ option.label }}
                      </div>
                    </a-checkbox>
                  </a-checkbox-group>
                </div>
              </template>
            </template>
          </a-table>

          <div class="flex justify-end">
            <a-pagination
              v-model:current="pagina"
              v-model:pageSize="paginasize"
              :total="totalpaginas"
              show-size-changer
              show-less-items />
          </div>
        </a-card>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutDocente.vue';
import { watch, computed, ref, onMounted } from 'vue';
import { SearchOutlined } from '@ant-design/icons-vue';
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
const revisionSolicitada = computed(() => postulanteData.value?.revision_solicitada == true || postulanteData.value?.revision_solicitada == 1);
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
      pData = reqRes.value.data?.datos?.postulante || null;
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

    // Requisitos
    if (reqRes.status === 'fulfilled') {
      requisitosData.value = reqRes.value.data?.datos || null;
      // Siempre actualizar revisionData desde documentosPorRequisitos (que tiene los datos de revisión reales)
      // getPostulanteByDni NO incluye campos de revisión, por lo que no podemos depender de él
      if (reqRes.value.data?.datos?.postulante) {
        const pd = reqRes.value.data.datos.postulante;
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
</script>

<style scoped>
.p-page { padding: 1rem; }

/* ═══ Header ═══ */
.profile-header {
  display: flex; align-items: center; gap: 1rem;
  margin-bottom: 1.5rem; padding: 1.25rem 1.5rem;
  background: linear-gradient(135deg, #0f172a, #1e3a5f);
  border-radius: 18px; color: #fff;
  box-shadow: 0 8px 32px rgba(15, 23, 42, .15);
}
.profile-header-center { flex: 1; }
.profile-header-center h1 { margin: 0; font-size: 1.375rem; font-weight: 800; letter-spacing: -.02em; }
.profile-subtitle { margin: .25rem 0 0; font-size: .8125rem; color: rgba(255,255,255,.65); }

.back-btn {
  display: inline-flex; align-items: center; gap: .375rem;
  padding: .5rem .875rem;
  border: 1px solid rgba(255,255,255,.15);
  border-radius: 10px; background: rgba(255,255,255,.08);
  color: #fff; font-size: .8125rem; font-weight: 600;
  cursor: pointer; transition: all .2s;
}
.back-btn:hover { background: rgba(255,255,255,.15); border-color: rgba(255,255,255,.3); }
.back-btn svg { width: 16px; height: 16px; }
.profile-header-right { margin-left: auto; }

/* ═══ Action buttons ═══ */
.rev-btn {
  display: inline-flex; align-items: center; gap: .375rem;
  padding: .5rem 1.125rem; border: none; border-radius: 10px;
  font-size: .8125rem; font-weight: 700; cursor: pointer;
  transition: all .2s; white-space: nowrap;
}
.rev-btn svg { width: 16px; height: 16px; }
.rev-btn.start { background: #fbbf24; color: #78350f; }
.rev-btn.start:hover { background: #f59e0b; transform: translateY(-1px); }
.rev-btn.start:disabled { opacity: .6; cursor: wait; }
.rev-btn.finish { background: #34d399; color: #064e3b; }
.rev-btn.finish:hover { background: #10b981; transform: translateY(-1px); }
.rev-btn.apto { background: #93c5fd; color: #1e3a5f; }
.rev-btn.apto:hover { background: #60a5fa; color: #fff; }
.rev-btn.renotify { background: #fbbf24; color: #78350f; }
.rev-btn.renotify:hover { background: #f59e0b; transform: translateY(-1px); }
.rev-btn:disabled { opacity: .6; cursor: wait; }

.rev-completed-badge {
  display: inline-flex; align-items: center; gap: .375rem;
  padding: .5rem 1rem; border-radius: 10px;
  background: rgba(255,255,255,.12); color: #fff;
  font-size: .8125rem; font-weight: 600;
}
.rev-completed-badge svg { width: 16px; height: 16px; }

/* ═══ Loading ═══ */
.loading-state { display: flex; flex-direction: column; align-items: center; gap: 1rem; padding: 4rem; }
.loading-state p { color: #64748b; font-weight: 600; font-size: .875rem; }

/* ═══ Info bar ═══ */
.info-bar {
  display: flex; flex-wrap: wrap; align-items: center; gap: .625rem;
  margin-bottom: 1rem; padding: .75rem 1rem;
  background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px;
}
.info-chip {
  display: inline-flex; align-items: center; gap: .375rem;
  padding: .375rem .75rem;
  background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: .75rem; font-weight: 600; color: #475569;
}
.info-chip svg { width: 14px; height: 14px; color: #64748b; }
.info-chip.accent { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.info-chip.accent svg { color: #2563eb; }

.rapid-btn {
  display: inline-flex; align-items: center; gap: .375rem;
  padding: .375rem .875rem; border: none; border-radius: 8px;
  background: #1e3a5f; color: #fff;
  font-size: .75rem; font-weight: 700; cursor: pointer;
  transition: all .2s; margin-left: auto;
}
.rapid-btn:hover { background: #2D5A8E; transform: translateY(-1px); }
.rapid-btn:disabled { opacity: .6; cursor: wait; }
.rapid-btn svg { width: 14px; height: 14px; }

/* ═══ Status banners ═══ */
.status-banner {
  display: flex; align-items: center; gap: .75rem;
  padding: 1rem 1.25rem; border-radius: 14px;
  margin-bottom: 1rem;
}
.status-banner.info { background: #eff6ff; border: 1px solid #bfdbfe; }
.status-banner.warning { background: #fffbeb; border: 1px solid #fcd34d; }
.status-banner.success { background: #ecfdf5; border: 1px solid #a7f3d0; }
.status-icon {
  display: grid; place-items: center;
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(255,255,255,.6); flex-shrink: 0;
}
.status-banner.info .status-icon { color: #2563eb; }
.status-banner.warning .status-icon { color: #d97706; }
.status-banner.success .status-icon { color: #059669; }
.status-icon svg { width: 22px; height: 22px; }
.status-text { display: flex; flex-direction: column; flex: 1; }
.status-title { font-size: .875rem; font-weight: 700; color: #1e293b; }
.status-desc { font-size: .75rem; color: #64748b; margin-top: .125rem; }

/* ═══ Requirement cards ═══ */
.req-list { display: flex; flex-direction: column; gap: .75rem; }

.req-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
  overflow: hidden; transition: all .2s;
}
.req-card.verified { border-color: #a7f3d0; box-shadow: 0 2px 8px rgba(16, 185, 129, .06); }
.req-card.pending { border-color: #fde68a; }
.req-card.na { opacity: .5; }

.req-head {
  display: flex; align-items: center; gap: .75rem;
  padding: .875rem 1rem;
  border-bottom: 1px solid #f1f5f9;
}
.req-num {
  display: grid; place-items: center;
  width: 28px; height: 28px; border-radius: 8px;
  background: #1e3a5f; color: #fff;
  font-size: .6875rem; font-weight: 800; flex-shrink: 0;
}
.req-card.verified .req-num { background: #059669; }
.req-head-info { flex: 1; min-width: 0; }
.req-title { font-size: .875rem; font-weight: 700; color: #0f172a; margin: 0; }
.req-badges { display: flex; gap: .375rem; margin-top: .25rem; }

.badge-pill {
  font-size: .625rem; font-weight: 700;
  padding: .125rem .5rem; border-radius: 6px;
  text-transform: uppercase; letter-spacing: .04em;
}
.badge-pill.obligatory { background: #fef3c7; color: #92400e; }
.badge-pill.optional { background: #e0e7ff; color: #3730a3; }
.badge-pill.na { background: #f1f5f9; color: #94a3b8; }
.badge-pill.status.ok { background: #d1fae5; color: #065f46; }
.badge-pill.status.partial { background: #dbeafe; color: #1e40af; }
.badge-pill.status.missing { background: #fee2e2; color: #991b1b; }

.req-body { padding: .625rem 1rem .875rem; }

.doc-type-block { padding: .375rem 0; }
.doc-type-block:not(:last-child) { border-bottom: 1px solid #f8fafc; padding-bottom: .625rem; margin-bottom: .25rem; }
.doc-type-label {
  font-size: .6875rem; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: .05em;
  margin-bottom: .5rem;
}

.doc-files { display: flex; flex-direction: column; gap: .375rem; }

.doc-file {
  display: flex; align-items: center; gap: .5rem;
  flex-wrap: wrap;
}

/* File chip */
.file-chip {
  display: inline-flex; align-items: center; gap: .375rem;
  padding: .375rem .625rem;
  background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: .75rem; font-weight: 600; color: #334155;
  cursor: pointer; transition: all .2s; max-width: 260px;
}
.file-chip svg { width: 14px; height: 14px; color: #64748b; flex-shrink: 0; }
.file-chip span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.file-chip:hover { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.file-chip:hover svg { color: #2563eb; }
.file-chip.no-url { color: #94a3b8; border-style: dashed; cursor: default; }
.file-chip.no-url:hover { background: #f8fafc; border-color: #e2e8f0; color: #94a3b8; }

/* Vigency badge */
.vig-badge {
  font-size: .625rem; font-weight: 700;
  padding: .125rem .4rem; border-radius: 5px;
  text-transform: uppercase; letter-spacing: .03em; white-space: nowrap;
}
.vig-vigente { background: #d1fae5; color: #065f46; }
.vig-por-vencer { background: #fef3c7; color: #92400e; }
.vig-caducado { background: #fee2e2; color: #991b1b; }
.vig-none { background: #f1f5f9; color: #94a3b8; }

/* (obs-tag removed — now using obs-display for full text) */

/* Observation display in card list */
.obs-display {
  display: flex; align-items: flex-start; gap: .375rem;
  width: 100%; margin-top: .375rem; padding: .375rem .625rem;
  background: #fffbeb; border: 1px solid #fcd34d; border-radius: 7px;
  font-size: .6875rem; color: #92400e; line-height: 1.3;
}
.obs-display svg { width: 12px; height: 12px; flex-shrink: 0; margin-top: 1px; }
.obs-display span { overflow: hidden; text-overflow: ellipsis; }

/* Observation toggle button with text */
.obs-toggle-btn {
  display: inline-flex; align-items: center; gap: .25rem;
  padding: .25rem .5rem; border: 1px solid #e2e8f0; border-radius: 7px;
  background: #f8fafc; color: #64748b;
  font-size: .6875rem; font-weight: 700; cursor: pointer;
  transition: all .2s; white-space: nowrap; flex-shrink: 0;
}
.obs-toggle-btn svg { width: 13px; height: 13px; }
.obs-toggle-btn:hover { background: #fef3c7; color: #d97706; border-color: #fcd34d; }
.obs-toggle-btn.active { background: #fef3c7; color: #d97706; border-color: #fcd34d; }

/* Status button */
.status-btn {
  display: inline-flex; align-items: center; gap: .25rem;
  padding: .25rem .625rem; border: none; border-radius: 7px;
  font-size: .6875rem; font-weight: 700; cursor: pointer;
  transition: all .2s; white-space: nowrap;
}
.status-btn svg { width: 14px; height: 14px; }
.status-btn.verified { background: #d1fae5; color: #065f46; }
.status-btn.verified:hover { background: #a7f3d0; }
.status-btn.apto { background: #dbeafe; color: #1e40af; }
.status-btn.apto:hover { background: #bfdbfe; }
.status-btn.pending { background: #fef3c7; color: #92400e; }
.status-btn.pending:hover { background: #fde68a; }
.status-btn:disabled { opacity: .35; cursor: not-allowed; }

/* Inline observation */
.obs-inline {
  display: flex; align-items: center; gap: .375rem;
  margin-top: .25rem; padding-left: .125rem;
}
.obs-input {
  flex: 1; padding: .375rem .625rem;
  border: 1px solid #fcd34d; border-radius: 7px;
  font-size: .75rem; color: #1e293b;
  outline: none; background: #fffbeb;
}
.obs-input:focus { border-color: #d97706; }
.obs-save, .obs-save-btn {
  padding: .375rem .625rem; border: none; border-radius: 7px;
  background: #d97706; color: #fff;
  font-size: .6875rem; font-weight: 700; cursor: pointer; white-space: nowrap;
}
.obs-save:hover, .obs-save-btn:hover { background: #b45309; }
.obs-cancel, .obs-cancel-btn {
  padding: .375rem .625rem; border: 1px solid #e2e8f0; border-radius: 7px;
  background: #fff; color: #64748b;
  font-size: .6875rem; font-weight: 600; cursor: pointer; white-space: nowrap;
}
.obs-cancel:hover, .obs-cancel-btn:hover { background: #f8fafc; }

/* ═══ Empty state ═══ */
.empty-state {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
  padding: 3rem 2rem; text-align: center;
}
.empty-icon { margin-bottom: 1rem; }
.empty-icon svg { width: 56px; height: 56px; color: #e2e8f0; }
.empty-state h3 { font-size: 1rem; font-weight: 700; color: #475569; margin: 0 0 .25rem; }
.empty-state p { font-size: .8125rem; color: #94a3b8; margin: 0; }

/* ═══ Preview modal ═══ */
.modal-body-wrap { display: flex; flex-direction: column; gap: 1rem; }
.modal-footer-bar {
  display: flex; align-items: center; justify-content: flex-end;
  gap: .75rem; padding-top: .75rem; border-top: 1px solid #eef2f7;
}
.modal-caducidad-field {
  display: flex; align-items: center; gap: .375rem; margin-right: auto;
}
.modal-caducidad-field label {
  font-size: .75rem; font-weight: 600; color: #475569; white-space: nowrap;
}
.modal-caducidad-field input {
  padding: .25rem .5rem; border: 1px solid #d1d5db; border-radius: 6px;
  font-size: .75rem; color: #1e293b; outline: none;
}
.modal-caducidad-field input:focus { border-color: #3b82f6; }
.modal-caducidad-field input:disabled { opacity: .5; cursor: not-allowed; }

.doc-btn {
  display: inline-flex; align-items: center; gap: .375rem;
  padding: .375rem .75rem; border-radius: 8px;
  font-size: .75rem; font-weight: 600; text-decoration: none; cursor: pointer;
  border: 1px solid transparent; transition: all .2s;
}
.doc-btn svg { width: 14px; height: 14px; }
.doc-btn.download { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
.doc-btn.download:hover { background: #f1f5f9; }

.verify-btn {
  display: flex; align-items: center; justify-content: center; gap: .375rem;
  padding: .625rem 1.25rem; border: none; border-radius: 10px;
  font-size: .8125rem; font-weight: 700; cursor: pointer; transition: all .2s;
}
.verify-btn svg { width: 18px; height: 18px; }
.verify-btn.verified { background: #d1fae5; color: #065f46; }
.verify-btn.verified:hover { background: #a7f3d0; }
.verify-btn.apto { background: #dbeafe; color: #1e40af; }
.verify-btn.apto:hover { background: #bfdbfe; }
.verify-btn.pending { background: #fef3c7; color: #92400e; }
.verify-btn.pending:hover { background: #fde68a; }
.verify-btn:disabled { opacity: .35; cursor: not-allowed; }

.obs-btn-modal {
  display: inline-flex; align-items: center; gap: .375rem;
  padding: .5rem .875rem; border: 1px solid #e2e8f0; border-radius: 10px;
  background: #f8fafc; color: #64748b;
  font-size: .8125rem; font-weight: 600; cursor: pointer;
  transition: all .2s; white-space: nowrap;
}
.obs-btn-modal svg { width: 16px; height: 16px; }
.obs-btn-modal:hover { background: #fef3c7; color: #d97706; border-color: #fcd34d; }

.modal-obs-field { display: flex; align-items: center; gap: .375rem; padding-top: .5rem; }
.modal-obs-display {
  display: flex; align-items: center; gap: .375rem;
  padding: .5rem .625rem; background: #fffbeb;
  border: 1px solid #fcd34d; border-radius: 8px;
  font-size: .75rem; color: #92400e; margin-top: .5rem;
}
.modal-obs-display svg { width: 16px; height: 16px; flex-shrink: 0; }

/* ═══ Finalizar modal ═══ */
.finalizar-modal { display: flex; flex-direction: column; gap: 1.5rem; }
.fin-section-title {
  font-size: .9375rem; font-weight: 700; color: #0f172a;
  margin: 0 0 .75rem; padding-bottom: .5rem;
  border-bottom: 2px solid #f1f5f9;
}
.fin-docs-summary { display: flex; gap: 1rem; }
.fin-docs-col {
  flex: 1; display: flex; flex-direction: column; align-items: center;
  padding: 1rem; border-radius: 12px; text-align: center;
}
.fin-docs-col.approved { background: #ecfdf5; }
.fin-docs-col.pending { background: #fffbeb; }
.fin-docs-count { font-size: 2rem; font-weight: 900; line-height: 1; }
.fin-docs-col.approved .fin-docs-count { color: #059669; }
.fin-docs-col.pending .fin-docs-count { color: #d97706; }
.fin-docs-label { font-size: .75rem; font-weight: 600; color: #64748b; margin-top: .25rem; }
.fin-docs-list { margin-top: .75rem; }
.fin-docs-list-title { font-size: .75rem; font-weight: 700; color: #d97706; margin: 0 0 .375rem; }
.fin-docs-list ul { margin: 0; padding-left: 1.25rem; }
.fin-docs-list li { font-size: .8125rem; color: #475569; padding: .125rem 0; }
.fin-obs-note { color: #d97706; font-style: italic; font-size: .75rem; }
.fin-result-hint {
  display: flex; align-items: center; gap: .375rem;
  padding: .625rem .875rem; border-radius: 8px;
  font-size: .75rem; font-weight: 600; margin-top: .75rem;
}
.fin-result-hint.success { background: #ecfdf5; color: #065f46; }
.fin-result-hint.warning { background: #fffbeb; color: #92400e; }
.fin-result-hint svg { width: 16px; height: 16px; flex-shrink: 0; }
.fin-citacion-loading { display: flex; align-items: center; gap: .5rem; padding: .5rem 0; color: #64748b; font-size: .8125rem; }
.fin-citacion-form { display: flex; flex-direction: column; gap: .75rem; }
.fin-form-row { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: .75rem; }
.fin-form-group { display: flex; flex-direction: column; gap: .25rem; }
.fin-form-group label { font-size: .75rem; font-weight: 600; color: #475569; }
.fin-input, .fin-textarea {
  padding: .5rem .625rem; border: 1px solid #d1d5db; border-radius: 8px;
  font-size: .8125rem; color: #1e293b; font-family: inherit;
  outline: none; transition: border-color .2s;
}
.fin-input:focus, .fin-textarea:focus { border-color: #3b82f6; }
.fin-textarea { resize: vertical; }
.fin-citacion-hint {
  display: flex; align-items: center; gap: .375rem;
  padding: .5rem .625rem; background: #eff6ff; border-radius: 8px;
  font-size: .75rem; color: #2563eb;
}
.fin-citacion-hint svg { width: 16px; height: 16px; flex-shrink: 0; }
.fin-actions { display: flex; justify-content: flex-end; gap: .75rem; padding-top: .5rem; border-top: 1px solid #f1f5f9; }
.fin-btn-cancel {
  padding: .5rem 1.25rem; border: 1px solid #d1d5db; border-radius: 10px;
  background: #fff; color: #475569; font-size: .8125rem; font-weight: 600;
  cursor: pointer; transition: all .2s;
}
.fin-btn-cancel:hover { background: #f8fafc; }
.fin-btn-confirm {
  display: flex; align-items: center; gap: .375rem;
  padding: .5rem 1.25rem; border: none; border-radius: 10px;
  background: #1e3a5f; color: #fff;
  font-size: .8125rem; font-weight: 700; cursor: pointer; transition: all .2s;
}
.fin-btn-confirm:hover { background: #2D5A8E; transform: translateY(-1px); }
.fin-btn-confirm:disabled { opacity: .6; cursor: wait; }
.fin-btn-confirm svg { width: 16px; height: 16px; }

/* ═══ List mode ═══ */
.checkbox-group-vertical { display: flex; flex-wrap: wrap; }
.checkbox-item { margin-left: 0 !important; }
.first-item { border-left: 2px solid #1890ff; padding-left: 8px; }

/* ═══ Spinner ═══ */
.spinner {
  width: 16px; height: 16px;
  border: 2px solid currentColor; border-top-color: transparent;
  border-radius: 50%; animation: spin .6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ═══ Responsive ═══ */
@media (max-width: 768px) {
  .profile-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
  .profile-header-right { margin-left: 0; }
  .fin-form-row { grid-template-columns: 1fr; }
  .doc-file { flex-wrap: wrap; }
}
</style>
