<template>
  <Head title="Mis Documentos" />
  <PostulanteAuthenticatedLayout>
    <div class="doc-page">

      <!-- ═══ HERO HEADER ═══ -->
      <div class="hero">
        <div class="hero-content">
          <div class="hero-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
          </div>
          <div>
            <h1 class="hero-title">Mis Documentos</h1>
            <p class="hero-subtitle">Sube los documentos requeridos según tu modalidad de inscripción</p>
          </div>
        </div>
        <div class="hero-actions">
          <!-- View toggle -->
          <div class="view-toggle">
            <button class="vt-btn" :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'" title="Vista lista">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
            </button>
            <button class="vt-btn" :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'" title="Vista cuadrícula">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
            </button>
          </div>
          <button class="hero-refresh" @click="cargarRequisitos" :class="{ spinning: loadingRequisitos }">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
          </button>
        </div>
      </div>

      <!-- ═══ MODALIDAD CHIPS ═══ -->
      <div class="modalidad-section" v-if="modalidades.length > 0">
        <div class="section-label">Modalidad</div>
        <div class="modalidad-chips">
          <button v-for="m in modalidades" :key="m.id" class="chip" :class="{ active: modalidadActiva === m.id }" @click="modalidadActiva = m.id">
            <span class="chip-dot"></span>{{ m.nombre }}
          </button>
        </div>
      </div>

      <!-- ═══ STATS ROW ═══ -->
      <div class="stats-row" v-if="cargado && modalidadActiva">
        <div class="stat-card"><div class="stat-num text-slate-700">{{ requisitosModalidad.length }}</div><div class="stat-label">Total</div></div>
        <div class="stat-card"><div class="stat-num text-emerald-600">{{ completadosModalidad }}</div><div class="stat-label">Completados</div></div>
        <div class="stat-card"><div class="stat-num text-amber-500">{{ requisitosModalidad.length - completadosModalidad }}</div><div class="stat-label">Pendientes</div></div>
        <div class="stat-card stat-progress">
          <div class="stat-ring-wrap">
            <svg class="stat-ring" viewBox="0 0 36 36"><path class="ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" /><path class="ring-fill" :stroke-dasharray="`${porcentajeModalidad}, 100`" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" /></svg>
            <span class="ring-pct">{{ porcentajeModalidad }}%</span>
          </div>
          <div class="stat-label">Avance</div>
        </div>
      </div>

      <!-- ═══ PROGRESS ═══ -->
      <div class="progress-section" v-if="requisitosModalidad.length > 0">
        <div class="progress-header">
          <span class="progress-title">{{ nombreModalidadActiva }}</span>
          <span class="progress-meta">{{ completadosModalidad }} de {{ requisitosModalidad.length }}</span>
        </div>
        <div class="progress-track">
          <div class="progress-fill" :style="{ width: porcentajeModalidad + '%' }" :class="progressClass(porcentajeModalidad)"></div>
        </div>
      </div>

      <!-- ═══ INFO ═══ -->
      <div class="info-banner">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
        <span>Solo archivos PDF · Máximo 20 MB · Sube <strong>uno</strong> de los tipos aceptados por requisito</span>
      </div>

      <!-- ═══ REQUISITOS — LIST VIEW ═══ -->
      <div v-if="requisitosModalidad.length > 0 && viewMode === 'list'" class="requisitos-list">
        <div v-for="req in requisitosModalidad" :key="req.id" class="req-card" :class="{ 'req-done': req.porcentaje === 100 }">
          <div class="req-head">
            <div class="req-check" :class="req.porcentaje === 100 ? 'done' : 'pending'">
              <svg v-if="req.porcentaje === 100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
              <span v-else>{{ req.orden }}</span>
            </div>
            <div class="req-info">
              <h3 class="req-name">{{ req.nombre }}</h3>
              <div class="req-tags">
                <span v-if="req.obligatorio" class="tag tag-req">Obligatorio</span>
                <span v-else-if="req.obligatorio_para_postulante" class="tag tag-req-programa">Obligatorio para tu programa</span>
                <span v-else class="tag tag-opt">Opcional</span>
              </div>
              <div v-if="!req.obligatorio && req.obligatorio_para_postulante && req.programas_obligatorios?.length" class="req-programa-alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                <span>Este documento es obligatorio para los programas: <strong>{{ req.programas_obligatorios.join(', ') }}</strong></span>
              </div>
              <div v-if="req.no_aplica && req.programas_obligatorios?.length" class="req-programa-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                <span>Solo es obligatorio para: <strong>{{ req.programas_obligatorios.join(', ') }}</strong></span>
              </div>
            </div>
            <div v-if="req.documento_subido && req.documento_id" class="req-thumb-check" @click="abrirPreview(req.documento_id, req.tipo_subido_nombre)">
              <div class="pdf-thumbnail">
                <canvas :ref="el => setCanvasRef(el, 'list_' + req.documento_id)" class="thumb-canvas"></canvas>
              </div>
              <div v-if="req.porcentaje === 100" class="thumb-check-mark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M4.5 12.75l6 6 9-13.5"/></svg></div>
            </div>
            <div class="req-status" :class="progressClass(req.porcentaje)">{{ req.porcentaje === 100 ? '✓ Completo' : 'Pendiente' }}</div>
          </div>
          <div class="req-body">
            <!-- Uploaded: show all docs across all tipos with selection -->
            <div v-if="req.documento_subido" class="docs-uploaded-list">
              <div v-for="td in req.tipos_documento.filter(t => t.subido)" :key="td.id" class="tipo-doc-group">
                <div class="tipo-doc-label">{{ td.nombre }}</div>
                <div v-for="doc in (td.documentos || [])" :key="doc.id" class="doc-item-row">
                  <div class="doc-thumb-small" @click="abrirPreview(doc.id, td.nombre)">
                    <canvas :ref="el => setCanvasRef(el, 'list_' + doc.id)" class="thumb-canvas-small"></canvas>
                  </div>
                  <div class="doc-item-info">
                    <span class="doc-item-name">{{ doc.nombre }}</span>
                    <span class="status-badge" :class="docEstadoBadge(doc).class">{{ docEstadoBadge(doc).text }}</span>
                    <span v-if="doc.fecha_caducidad" class="doc-caducidad">Vence: {{ doc.fecha_caducidad }}</span>
                  </div>
                  <div class="doc-item-actions" v-if="doc.puede_editar !== false">
                    <label class="act-btn act-replace"><input type="file" accept=".pdf" class="sr-only" @change="reemplazarArchivo($event, { ...req, documento_id: doc.id, tipo_subido_nombre: td.nombre })" :disabled="uploadingTo !== null" /><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg></label>
                    <button class="act-btn act-delete" @click="eliminarDocumento({ ...req, documento_id: doc.id, tipo_subido_nombre: td.nombre })" :disabled="deletingId !== null"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg></button>
                  </div>
                  <div class="doc-locked" v-else><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg></div>
                </div>
              </div>
            </div>
            <!-- Not uploaded -->
            <div v-else class="doc-upload-area">
              <div class="upload-type-row" :class="{ 'type-chosen': !!selectedTipo[req.id], 'needs-type': !selectedTipo[req.id] && highlightTipo[req.id] }">
                <div class="upload-type-header">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path d="M6 6h.008v.008H6V6z"/></svg>
                  <span>{{ selectedTipo[req.id] ? 'Tipo seleccionado' : 'Selecciona el tipo de documento' }}</span>
                </div>
                <div class="type-chips">
                  <button v-for="td in req.tipos_documento" :key="td.id" class="type-chip" :class="{ selected: selectedTipo[req.id] === td.id, glow: !selectedTipo[req.id] }" @click="selectedTipo[req.id] = td.id">
                    <svg v-if="selectedTipo[req.id] === td.id" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="chip-check"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
                    {{ td.nombre }}
                  </button>
                </div>
              </div>
              <input type="file" accept=".pdf" :ref="el => setFileInputRef(el, req.id)" class="sr-only" @change="iniciarSubida($event, req)" />
              <button class="upload-btn-main" :class="{ ready: !!selectedTipo[req.id], uploading: uploadingTo === req.id, 'needs-type': !selectedTipo[req.id] && highlightTipo[req.id] }" @click="triggerUpload(req)" :disabled="uploadingTo !== null">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                <span>{{ uploadingTo === req.id ? 'Subiendo...' : 'Subir PDF' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ REQUISITOS — GRID VIEW ═══ -->
      <div v-if="requisitosModalidad.length > 0 && viewMode === 'grid'" class="requisitos-grid">
        <div v-for="req in requisitosModalidad" :key="req.id" class="grid-card">
          <!-- Thumbnail grande -->
          <div class="grid-preview" @click="req.documento_id && abrirPreview(req.documento_id, req.tipo_subido_nombre)">
            <div v-if="req.documento_subido && req.documento_id" class="grid-canvas-wrap">
              <canvas :ref="el => setCanvasRef(el, 'grid_' + req.documento_id)" class="grid-canvas"></canvas>
            </div>
            <div v-else class="grid-placeholder">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            </div>
            <div class="grid-overlay" v-if="req.documento_subido && !req.verificado">
              <label class="grid-ov-btn" @click.stop><input type="file" accept=".pdf" class="sr-only" @change="reemplazarArchivo($event, req)" :disabled="uploadingTo !== null" /><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg></label>
              <button class="grid-ov-btn grid-ov-del" @click.stop="eliminarDocumento(req)" :disabled="deletingId !== null"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg></button>
            </div>
            <div class="grid-corner-check" v-if="req.porcentaje === 100"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M4.5 12.75l6 6 9-13.5"/></svg></div>
            <div class="grid-verified-stamp" v-if="req.verificado">✓</div>
            <!-- Name overlay on thumbnail -->
            <div class="grid-name-overlay" v-if="req.documento_subido && req.tipo_subido_nombre">
              <span>{{ req.tipo_subido_nombre }}</span>
            </div>
          </div>
          <!-- Info -->
          <div class="grid-info">
            <div class="grid-title-row">
              <span class="grid-name">{{ req.nombre }}</span>
              <span v-if="req.obligatorio" class="tag tag-req">Obligatorio</span>
              <span v-else-if="req.obligatorio_para_postulante" class="tag tag-req-programa">Obligatorio</span>
              <span v-else class="tag tag-opt">Opcional</span>
            </div>
            <div v-if="req.documento_subido" class="grid-meta">
              <span v-if="req.verificado" class="status-badge badge-verified">✓ Verificado</span>
              <span v-else class="status-badge badge-pending-verify">Pendiente</span>
            </div>
            <div v-else class="grid-upload">
              <div class="grid-type-row" :class="{ 'needs-type': !selectedTipo[req.id] && highlightTipo[req.id] }">
                <div class="type-chips-sm">
                  <button v-for="td in req.tipos_documento" :key="td.id" class="type-chip-sm" :class="{ selected: selectedTipo[req.id] === td.id, glow: !selectedTipo[req.id] }" @click="selectedTipo[req.id] = td.id">
                    <svg v-if="selectedTipo[req.id] === td.id" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="chip-check-sm"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
                    {{ td.codigo || td.nombre }}
                  </button>
                </div>
              </div>
              <input type="file" accept=".pdf" :ref="el => setFileInputRef(el, req.id)" class="sr-only" @change="iniciarSubida($event, req)" />
              <button class="grid-upload-btn" :class="{ ready: !!selectedTipo[req.id], disabled: uploadingTo !== null, 'needs-type': !selectedTipo[req.id] && highlightTipo[req.id] }" @click="triggerUpload(req)" :disabled="uploadingTo !== null">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>{{ uploadingTo === req.id ? 'Subiendo...' : 'Subir PDF' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══ EMPTY STATES ═══ -->
      <div class="empty-card" v-else-if="cargado && modalidades.length > 0 && !modalidadActiva">
        <div class="empty-illust"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg></div>
        <h3 class="empty-title">Selecciona una modalidad</h3>
        <p class="empty-desc">Elige tu modalidad para ver los documentos requeridos.</p>
      </div>
      <div class="empty-card" v-else-if="cargado && modalidadActiva && requisitosModalidad.length === 0">
        <div class="empty-illust"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-16.5 0h16.5"/></svg></div>
        <h3 class="empty-title">Sin requisitos configurados</h3>
        <p class="empty-desc">Los documentos aparecerán cuando el administrador configure los requisitos.</p>
      </div>
      <div class="empty-card" v-else-if="cargado && modalidades.length === 0">
        <div class="empty-illust"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg></div>
        <h3 class="empty-title">Sin modalidades disponibles</h3>
        <p class="empty-desc">Las modalidades aparecerán según las vacantes del proceso activo.</p>
      </div>
      <div class="loading-card" v-if="!cargado"><div class="spinner"></div><span>Cargando documentos...</span></div>

      <!-- ═══ SOLICITAR REVISIÓN ═══ -->
      <div v-if="cargado && puedeSolicitarRevision" class="revision-section">
        <div class="revision-card" :class="revisionCardClass">
          <div class="revision-icon" :class="revisionIconClass">
            <svg v-if="estadoRevision.revision_finalizada_at" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <svg v-else-if="estadoRevision.revision_solicitada" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
          </div>
          <div class="revision-content">
            <!-- Revisión completada -->
            <template v-if="estadoRevision.revision_finalizada_at">
              <h3 class="revision-title">Revisión completada</h3>
              <p class="revision-desc">Tu revisión fue completada el <strong>{{ estadoRevision.revision_finalizada_at }}</strong>.</p>
              <div v-if="estadoRevision.datos_citacion" class="revision-cita">
                <div class="cita-row"><span>📅 Fecha:</span> <strong>{{ estadoRevision.datos_citacion.fecha }}</strong></div>
                <div class="cita-row"><span>🕐 Hora:</span> <strong>{{ estadoRevision.datos_citacion.hora_inicio }} - {{ estadoRevision.datos_citacion.hora_fin }}</strong></div>
                <div class="cita-row"><span>📍 Lugar:</span> <strong>{{ estadoRevision.datos_citacion.lugar }}</strong></div>
                <div v-if="estadoRevision.datos_citacion.instrucciones" class="cita-row"><span>📋 Instrucciones:</span> <strong>{{ estadoRevision.datos_citacion.instrucciones }}</strong></div>
              </div>
              <p v-if="estadoRevision.veces_revision_solicitada > 1" class="revision-meta">Has solicitado revisión <strong>{{ estadoRevision.veces_revision_solicitada }} veces</strong></p>
              <button class="revision-btn finish" @click="solicitarRevision" :disabled="solicitandoRevision">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
                {{ solicitandoRevision ? 'Enviando...' : 'Solicitar nueva revisión' }}
              </button>
            </template>
            <!-- Revisión solicitada, en espera -->
            <template v-else-if="estadoRevision.revision_solicitada">
              <h3 class="revision-title">Revisión de documentos solicitada</h3>
              <p class="revision-desc">Tu solicitud fue registrada el <strong>{{ estadoRevision.revision_solicitada_at }}</strong>. Un revisor verificará tus documentos.</p>
              <p v-if="estadoRevision.revision_iniciada_at" class="revision-meta">✓ El revisor inició la revisión el <strong>{{ estadoRevision.revision_iniciada_at }}</strong></p>
              <p v-if="estadoRevision.veces_revision_solicitada > 1" class="revision-meta">Has solicitado revisión <strong>{{ estadoRevision.veces_revision_solicitada }} veces</strong></p>
              <div v-if="!estadoRevision.puede_insistir && estadoRevision.proxima_insistencia" class="revision-cooldown">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Podrás insistir a partir del <strong>{{ estadoRevision.proxima_insistencia }}</strong>
              </div>
              <button class="revision-btn insist" @click="solicitarRevision" :disabled="solicitandoRevision || !estadoRevision.puede_insistir">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                {{ !estadoRevision.puede_insistir ? 'En cooldown...' : (solicitandoRevision ? 'Enviando...' : 'Insistir en la revisión') }}
              </button>
            </template>
            <!-- Sin solicitar -->
            <template v-else>
              <h3 class="revision-title">¿Has terminado de subir tus documentos?</h3>
              <p class="revision-desc">Al finalizar, se notificará a un revisor para que verifique tus documentos. Asegúrate de haber subido todo lo requerido.</p>
              <button class="revision-btn finish" @click="solicitarRevision" :disabled="solicitandoRevision">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ solicitandoRevision ? 'Enviando...' : 'Finalizar y Solicitar Revisión' }}
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ PDF PREVIEW MODAL ═══ -->
    <a-modal v-model:open="previewVisible" :title="previewTitle" :footer="null" width="900px" :destroyOnClose="true">
      <div class="preview-container"><iframe v-if="previewUrl" :src="previewUrl" class="preview-iframe" /></div>
    </a-modal>

    <!-- ═══ UPLOAD PREVIEW MODAL ═══ -->
    <a-modal v-model:open="uploadPreviewVisible" title="Vista previa del documento" :okText="'Subir documento'" :cancelText="'Cancelar'" :okButtonProps="{ loading: uploadingTo !== null }" @ok="confirmarSubida" @cancel="cancelarSubida" width="800px" :destroyOnClose="true">
      <div class="up-body">
        <div class="up-file-row">
          <div class="up-file-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg></div>
          <div class="up-file-meta"><span class="up-file-name">{{ uploadPreviewName }}</span><span class="up-file-size">{{ uploadPreviewSize }}</span></div>
        </div>
        <div class="up-iframe-wrap" v-if="uploadPreviewUrl"><iframe :src="uploadPreviewUrl" class="up-iframe" /></div>
      </div>
    </a-modal>

  </PostulanteAuthenticatedLayout>
</template>

<script setup>
import PostulanteAuthenticatedLayout from '@/Layouts/PostulanteAuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';
import { message, Modal } from 'ant-design-vue';
import * as pdfjsLib from 'pdfjs-dist';

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL('pdfjs-dist/build/pdf.worker.min.mjs', import.meta.url).toString();

const requisitos = ref([]);
const modalidades = ref([]);
const conProgramas = ref(false);
const modalidadActiva = ref(null);
const cargado = ref(false);
const loadingRequisitos = ref(false);
const uploadingTo = ref(null);
const deletingId = ref(null);
const selectedTipo = reactive({});
const viewMode = ref('list');

const previewVisible = ref(false);
const previewUrl = ref('');
const previewTitle = ref('');
const uploadPreviewVisible = ref(false);
const uploadPreviewUrl = ref(null);
const uploadPreviewName = ref('');
const uploadPreviewSize = ref('');
const pendingUpload = ref(null);
const fileInputRefs = reactive({});
const highlightTipo = reactive({});
const canvasRefs = reactive({});

const estadoRevision = ref({
  revision_solicitada: false,
  tiene_revision_pendiente: false,
  puede_solicitar: false,
  revision_solicitada_at: null,
  veces_revision_solicitada: 0,
  revision_iniciada_at: null,
  revision_finalizada_at: null,
  datos_citacion: null,
  puede_insistir: true,
  proxima_insistencia: null,
});
const solicitandoRevision = ref(false);

// Estado de revisión pendiente derivado de la modalidad seleccionada

const revisionCardClass = computed(() => ({
  'revision-sent': estadoRevision.value.revision_solicitada,
  'revision-done': !!estadoRevision.value.revision_finalizada_at,
}));

const revisionIconClass = computed(() => ({
  'icon-done': !!estadoRevision.value.revision_finalizada_at,
  'icon-pending': !!estadoRevision.value.revision_solicitada && !estadoRevision.value.revision_finalizada_at,
}));

const puedeSolicitarRevision = computed(() => {
  if (!cargado.value || requisitosModalidad.value.length === 0) return false;
  const obligatorios = requisitosModalidad.value.filter(r => r.obligatorio || r.obligatorio_para_postulante);
  if (obligatorios.length === 0) return requisitosModalidad.value.some(r => r.documento_subido);
  // Cada obligatorio debe tener al menos un documento subido
  return obligatorios.every(r => r.documento_subido);
});

const setCanvasRef = (el, key) => { if (el) canvasRefs[key] = el; };
const setFileInputRef = (el, key) => { if (el) fileInputRefs[key] = el; };

const requisitosModalidad = computed(() => {
  if (!modalidadActiva.value) return [];
  return requisitos.value.filter(r => r.modalidades?.some(m => m.id === modalidadActiva.value));
});
const nombreModalidadActiva = computed(() => modalidades.value.find(m => m.id === modalidadActiva.value)?.nombre || '');
const completadosModalidad = computed(() => requisitosModalidad.value.filter(r => r.porcentaje === 100).length);
const porcentajeModalidad = computed(() => requisitosModalidad.value.length === 0 ? 0 : Math.round((completadosModalidad.value / requisitosModalidad.value.length) * 100));

const progressClass = (pct) => pct === 100 ? 'complete' : pct >= 50 ? 'half' : 'low';
const formatFileSize = (b) => { if (!b) return ''; if (b < 1024) return b + ' B'; if (b < 1048576) return (b/1024).toFixed(1) + ' KB'; return (b/1048576).toFixed(1) + ' MB'; };

const cargarRequisitos = async () => {
  loadingRequisitos.value = true;
  try {
    const res = await axios.get('/postulante/mis-requisitos');
    if (res.data.success) {
      requisitos.value = res.data.datos.requisitos;
      modalidades.value = res.data.datos.modalidades;
      conProgramas.value = res.data.datos.con_programas || false;
      if (!modalidadActiva.value && modalidades.value.length === 1) modalidadActiva.value = modalidades.value[0].id;
      requisitos.value.forEach(req => { if (!selectedTipo[req.id] && req.tipos_documento?.length > 0) selectedTipo[req.id] = req.tipos_documento[0].id; });
      await nextTick();
      renderThumbnails();
    }
  } catch (e) { console.error('Error cargando requisitos:', e); }
  finally { cargado.value = true; loadingRequisitos.value = false; }
};

const cargarEstadoRevision = async () => {
  try {
    const params = modalidadActiva.value ? { id_modalidad: modalidadActiva.value } : {};
    const res = await axios.get('/postulante/estado-revision', { params });
    if (res.data.success) {
      estadoRevision.value = res.data.datos;
    }
  } catch (e) { console.error('Error cargando estado revisión:', e); }
};

const solicitarRevision = async () => {
  if (solicitandoRevision.value) return;
  solicitandoRevision.value = true;
  try {
    const res = await axios.post('/postulante/solicitar-revision', {
      id_modalidad: modalidadActiva.value,
    });
    if (res.data.success) {
      message.success(res.data.mensaje);
      await cargarEstadoRevision();
      await cargarRequisitos();
    }
  } catch (e) {
    message.error(e.response?.data?.mensaje || 'Error al solicitar revisión');
  } finally {
    solicitandoRevision.value = false;
  }
};

const docEstadoBadge = (doc) => {
  if (doc.valido) return { text: 'Documento válido', class: 'badge-valido' };
  if (doc.apto_revision) return { text: 'Apto para revisión', class: 'badge-apto' };
  return { text: 'Pendiente', class: 'badge-pending-doc' };
};

const renderThumbnails = async () => {
  await nextTick();
  for (const req of requisitosModalidad.value) {
    // Renderizar thumbnails de todos los documentos subidos
    if (req.tipos_documento) {
      for (const td of req.tipos_documento) {
        if (!td.documentos) continue;
        for (const doc of td.documentos) {
          const listKey = 'list_' + doc.id;
          if (canvasRefs[listKey]) await renderPdfThumbnail(listKey, doc.id);
        }
      }
    }
    // Fallback: doc con documento_id directo
    if (req.documento_id && !req.tipos_documento) {
      const listKey = 'list_' + req.documento_id;
      if (canvasRefs[listKey]) await renderPdfThumbnail(listKey, req.documento_id);
    }
  }
};

const renderPdfThumbnail = async (canvasKey, docId) => {
  const canvas = canvasRefs[canvasKey];
  if (!canvas) return;
  try {
    const isGrid = canvasKey.startsWith('grid_');
    const scale = isGrid ? 1.2 : 0.4;
    const url = `/postulante/preview-documento/${docId}`;
    const pdf = await pdfjsLib.getDocument(url).promise;
    const page = await pdf.getPage(1);
    const viewport = page.getViewport({ scale });
    canvas.width = viewport.width;
    canvas.height = viewport.height;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, viewport.width, viewport.height);
    await page.render({ canvasContext: ctx, viewport }).promise;
  } catch (e) { console.warn('Thumbnail fail', docId, e); }
};

watch([requisitosModalidad, viewMode], () => { nextTick(() => renderThumbnails()); }, { deep: true });

const triggerUpload = (req) => {
  if (!selectedTipo[req.id]) {
    highlightTipo[req.id] = true;
    message.warning('Selecciona el tipo de documento primero');
    setTimeout(() => { highlightTipo[req.id] = false; }, 2000);
    return;
  }
  const input = fileInputRefs[req.id];
  if (input) input.click();
};

const iniciarSubida = (event, req) => {
  const idTipoDocumento = selectedTipo[req.id];
  if (!idTipoDocumento) { message.warning('Selecciona un tipo primero'); return; }
  const file = event.target.files?.[0];
  if (!file) return;
  if (file.type !== 'application/pdf') { message.error('Solo PDF'); event.target.value = ''; return; }
  if (file.size > 20*1024*1024) { message.error('Máximo 20 MB'); event.target.value = ''; return; }
  const reader = new FileReader();
  reader.onload = (e) => { uploadPreviewUrl.value = e.target.result; uploadPreviewName.value = file.name; uploadPreviewSize.value = formatFileSize(file.size); pendingUpload.value = { file, req, idTipoDocumento }; uploadPreviewVisible.value = true; };
  reader.readAsDataURL(file);
  event.target.value = '';
};

const reemplazarArchivo = (event, req) => {
  const file = event.target.files?.[0];
  if (!file) return;
  if (file.type !== 'application/pdf') { message.error('Solo PDF'); event.target.value = ''; return; }
  const reader = new FileReader();
  reader.onload = (e) => { uploadPreviewUrl.value = e.target.result; uploadPreviewName.value = file.name; uploadPreviewSize.value = formatFileSize(file.size); pendingUpload.value = { file, req, documentoId: req.documento_id, isReplace: true }; uploadPreviewVisible.value = true; };
  reader.readAsDataURL(file);
  event.target.value = '';
};

const confirmarSubida = async () => {
  if (!pendingUpload.value) return;
  const { file, req, idTipoDocumento, documentoId, isReplace } = pendingUpload.value;
  uploadingTo.value = req.id;
  try {
    if (isReplace && documentoId) {
      const fd = new FormData(); fd.append('archivo', file);
      const res = await axios.post(`/postulante/actualizar-documento/${documentoId}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
      if (res.data.success) { message.success('Documento reemplazado'); await cargarRequisitos(); }
    } else {
      const fd = new FormData(); fd.append('archivo', file); fd.append('id_tipo_documento', idTipoDocumento);
      const res = await axios.post('/postulante/subir-documento', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
      if (res.data.success) { message.success('Documento subido'); await cargarRequisitos(); }
    }
  } catch (e) { message.error(e.response?.data?.mensaje || 'Error al procesar'); }
  finally { uploadingTo.value = null; uploadPreviewVisible.value = false; pendingUpload.value = null; uploadPreviewUrl.value = null; }
};

const cancelarSubida = () => { uploadPreviewVisible.value = false; pendingUpload.value = null; uploadPreviewUrl.value = null; };

const abrirPreview = (docId, tipoNombre) => { previewUrl.value = `/postulante/preview-documento/${docId}`; previewTitle.value = tipoNombre || 'Vista previa'; previewVisible.value = true; };

const eliminarDocumento = (req) => {
  if (!req.documento_id) return;
  Modal.confirm({ title: '¿Eliminar documento?', content: `Se eliminará "${req.tipo_subido_nombre}".`, okText: 'Eliminar', okType: 'danger', cancelText: 'Cancelar',
    onOk: async () => { deletingId.value = req.documento_id; try { const res = await axios.delete(`/postulante/eliminar-documento/${req.documento_id}`); if (res.data.success) { message.success(res.data.mensaje); await cargarRequisitos(); } } catch (e) { message.error(e.response?.data?.mensaje || 'Error'); } finally { deletingId.value = null; } }
  });
};

onMounted(() => { cargarRequisitos(); cargarEstadoRevision(); });

watch(modalidadActiva, (nuevo) => { if (nuevo) cargarEstadoRevision(); });
</script>

<style scoped>
.doc-page { display: flex; flex-direction: column; gap: 1.25rem; }
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }

/* HERO */
.hero { display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 50%, #3b7ab5 100%); border-radius: 16px; padding: 1.5rem 1.75rem; color: #fff; position: relative; overflow: hidden; }
.hero::before { content: ''; position: absolute; top: -30%; right: -10%; width: 200px; height: 200px; background: rgba(255,255,255,.05); border-radius: 50%; }
.hero-content { display: flex; align-items: center; gap: 1rem; z-index: 1; }
.hero-icon { width: 48px; height: 48px; background: rgba(255,255,255,.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.hero-icon svg { width: 24px; height: 24px; }
.hero-title { font-size: 1.375rem; font-weight: 700; line-height: 1.2; }
.hero-subtitle { font-size: .8125rem; color: rgba(255,255,255,.7); margin-top: .25rem; }
.hero-actions { display: flex; align-items: center; gap: .5rem; z-index: 1; }

/* View toggle */
.view-toggle { display: flex; background: rgba(255,255,255,.1); border-radius: 8px; overflow: hidden; border: 1px solid rgba(255,255,255,.15); }
.vt-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.5); cursor: pointer; border: none; background: none; transition: all .2s; }
.vt-btn svg { width: 18px; height: 18px; }
.vt-btn:hover { color: rgba(255,255,255,.8); }
.vt-btn.active { background: rgba(255,255,255,.2); color: #fff; }

.hero-refresh { width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.8); cursor: pointer; transition: all .2s; }
.hero-refresh svg { width: 20px; height: 20px; }
.hero-refresh:hover { background: rgba(255,255,255,.2); color: #fff; }
.hero-refresh.spinning svg { animation: spin .8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

/* MODALIDAD */
.modalidad-section { background: #fff; border: 1px solid #eef2f7; border-radius: 14px; padding: 1rem 1.25rem; }
.section-label { font-size: .75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: .75rem; }
.modalidad-chips { display: flex; gap: .5rem; flex-wrap: wrap; }
.chip { display: inline-flex; align-items: center; gap: .375rem; padding: .5rem 1rem; border-radius: 100px; border: 1.5px solid #e2e8f0; background: #fff; font-size: .8125rem; font-weight: 600; color: #64748b; cursor: pointer; transition: all .25s; }
.chip:hover { border-color: #3b82f6; color: #3b82f6; }
.chip.active { background: #3b82f6; border-color: #3b82f6; color: #fff; box-shadow: 0 4px 12px rgba(59,130,246,.25); }
.chip-dot { width: 6px; height: 6px; border-radius: 50%; background: #cbd5e1; }
.chip.active .chip-dot { background: #fff; }

/* STATS */
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: .75rem; }
.stat-card { background: #fff; border: 1px solid #eef2f7; border-radius: 14px; padding: 1rem; text-align: center; transition: box-shadow .2s; }
.stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.05); }
.stat-num { font-size: 1.5rem; font-weight: 800; line-height: 1; }
.stat-label { font-size: .6875rem; color: #94a3b8; margin-top: .375rem; text-transform: uppercase; letter-spacing: .5px; font-weight: 600; }
.stat-progress { display: flex; flex-direction: column; align-items: center; }
.stat-ring-wrap { position: relative; width: 56px; height: 56px; }
.stat-ring { width: 100%; height: 100%; transform: rotate(-90deg); }
.ring-bg { fill: none; stroke: #eef2f7; stroke-width: 3; }
.ring-fill { fill: none; stroke: #3b82f6; stroke-width: 3; stroke-linecap: round; transition: stroke-dasharray .6s ease; }
.ring-pct { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: .6875rem; font-weight: 800; color: #3b82f6; }

/* PROGRESS */
.progress-section { background: #fff; border: 1px solid #eef2f7; border-radius: 14px; padding: 1rem 1.25rem; }
.progress-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: .5rem; }
.progress-title { font-size: .875rem; font-weight: 700; color: #1e293b; }
.progress-meta { font-size: .75rem; color: #94a3b8; font-weight: 600; }
.progress-track { height: 8px; background: #f1f5f9; border-radius: 100px; overflow: hidden; }
.progress-fill { height: 100%; border-radius: 100px; transition: width .5s ease; }
.progress-fill.complete { background: linear-gradient(90deg, #22c55e, #16a34a); }
.progress-fill.half { background: linear-gradient(90deg, #f59e0b, #d97706); }
.progress-fill.low { background: linear-gradient(90deg, #ef4444, #dc2626); }

/* INFO */
.info-banner { display: flex; align-items: center; gap: .5rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: .625rem 1rem; font-size: .8125rem; color: #1e40af; font-weight: 500; }
.info-banner svg { width: 18px; height: 18px; flex-shrink: 0; }

/* ═══ LIST VIEW ═══ */
.requisitos-list { display: flex; flex-direction: column; gap: 1rem; }
.req-card { background: #fff; border: 1px solid #eef2f7; border-radius: 16px; overflow: hidden; transition: box-shadow .2s; }
.req-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,.06); }
.req-card.req-done { border-color: #eef2f7; }
.req-head { display: flex; align-items: center; gap: .75rem; padding: 1rem 1.25rem; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom: 1px solid #f1f5f9; }
.req-card.req-done .req-head { background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-bottom-color: #f1f5f9; }
.req-check { width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 800; flex-shrink: 0; }
.req-check.done { background: #22c55e; color: #fff; }
.req-check.done svg { width: 18px; height: 18px; }
.req-check.pending { background: #e2e8f0; color: #64748b; }
.req-info { flex: 1; min-width: 0; }
.req-name { font-size: .9375rem; font-weight: 700; color: #0f172a; }
.req-tags { display: flex; gap: .375rem; flex-wrap: wrap; margin-top: .25rem; }
.tag { font-size: .625rem; padding: .125rem .5rem; border-radius: 100px; font-weight: 700; letter-spacing: .3px; text-transform: uppercase; }
.tag-req { background: #fee2e2; color: #dc2626; }
.tag-req-programa { background: #fef3c7; color: #d97706; }
.tag-opt { background: #f1f5f9; color: #94a3b8; }
.req-programa-alert { display: flex; align-items: flex-start; gap: .5rem; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: .5rem .75rem; margin-top: .5rem; font-size: .75rem; color: #92400e; font-weight: 500; line-height: 1.4; }
.req-programa-alert svg { width: 16px; height: 16px; flex-shrink: 0; color: #d97706; margin-top: 1px; }
.req-programa-info { display: flex; align-items: flex-start; gap: .5rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: .5rem .75rem; margin-top: .375rem; font-size: .6875rem; color: #1e40af; font-weight: 500; line-height: 1.4; }
.req-programa-info svg { width: 14px; height: 14px; flex-shrink: 0; color: #3b82f6; margin-top: 1px; }
.req-status { font-size: .75rem; font-weight: 700; flex-shrink: 0; }
.req-status.complete { color: #16a34a; }
.req-status.low { color: #94a3b8; }
.req-thumb-check { position: relative; flex-shrink: 0; cursor: pointer; }
.thumb-check-mark { position: absolute; top: -4px; right: -4px; width: 18px; height: 18px; border-radius: 50%; background: #22c55e; color: #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(34,197,94,.3); }
.thumb-check-mark svg { width: 12px; height: 12px; }
.req-body { padding: 1rem 1.25rem; }
.doc-uploaded-row { display: flex; align-items: center; gap: 1rem; padding: .75rem; background: #f8fafc; border-radius: 12px; border: 1px solid #eef2f7; }
.doc-uploaded-left { display: flex; flex-direction: column; gap: .25rem; min-width: 0; flex: 1; }
.doc-uploaded-type { font-size: .8125rem; font-weight: 600; color: #1e293b; }
.status-badge { display: inline-flex; align-items: center; gap: .25rem; padding: .125rem .5rem; border-radius: 100px; font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; width: fit-content; }
.badge-verified { background: #dcfce7; color: #16a34a; }
.badge-pending-verify { background: #dbeafe; color: #2563eb; }
.pdf-thumbnail { width: 54px; height: 76px; border-radius: 8px; background: #fff; border: 1px solid #e2e8f0; overflow: hidden; transition: all .2s; position: relative; }
.pdf-thumbnail:hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.thumb-canvas { width: 100%; height: 100%; object-fit: contain; }
.doc-uploaded-actions { display: flex; gap: .5rem; flex-shrink: 0; }
.doc-locked { display: flex; align-items: center; gap: .375rem; font-size: .6875rem; font-weight: 600; color: #16a34a; background: #dcfce7; padding: .375rem .75rem; border-radius: 8px; flex-shrink: 0; }
.doc-locked svg { width: 14px; height: 14px; }
.act-btn { display: inline-flex; align-items: center; gap: .375rem; padding: .375rem .75rem; border-radius: 8px; font-size: .6875rem; font-weight: 600; cursor: pointer; transition: all .2s; border: none; background: none; }
.act-btn svg { width: 14px; height: 14px; }
.act-replace { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.act-replace:hover { background: #e2e8f0; color: #1e293b; }
.act-delete { background: #fff; color: #ef4444; border: 1px solid #fecaca; }
.act-delete:hover { background: #fee2e2; }
.doc-upload-area { display: flex; flex-direction: column; gap: .75rem; }
.upload-type-row { padding: .75rem; border-radius: 10px; background: #f8fafc; border: 1.5px solid #e2e8f0; transition: all .3s; }
.upload-type-row.type-chosen { background: #f0fdf4; border-color: #86efac; }
.upload-type-row.needs-type { border-color: #f59e0b; background: #fffbeb; animation: shakeType .4s ease; }
@keyframes shakeType { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-4px); } 75% { transform: translateX(4px); } }
.upload-type-header { display: flex; align-items: center; gap: .5rem; margin-bottom: .5rem; }
.upload-type-header svg { width: 16px; height: 16px; color: #94a3b8; flex-shrink: 0; }
.type-chosen .upload-type-header svg { color: #22c55e; }
.upload-type-header span { font-size: .75rem; font-weight: 700; color: #64748b; }
.type-chosen .upload-type-header span { color: #16a34a; }
.type-chips { display: flex; gap: .5rem; flex-wrap: wrap; }
.type-chip { display: inline-flex; align-items: center; gap: .25rem; padding: .375rem .875rem; border-radius: 100px; border: 1.5px solid #e2e8f0; background: #fff; font-size: .8125rem; font-weight: 600; color: #64748b; cursor: pointer; transition: all .25s; }
.type-chip:hover { border-color: #3b82f6; color: #3b82f6; }
.type-chip.selected { background: #3b82f6; border-color: #3b82f6; color: #fff; box-shadow: 0 2px 8px rgba(59,130,246,.3); }
.type-chip.glow { animation: chipGlow 2s ease-in-out infinite; border-color: #93c5fd; color: #3b82f6; }
@keyframes chipGlow { 0%, 100% { box-shadow: 0 0 0 0 rgba(59,130,246,.15); } 50% { box-shadow: 0 0 0 6px rgba(59,130,246,.08); } }
.chip-check { width: 14px; height: 14px; }
.upload-btn-main { display: flex; align-items: center; justify-content: center; gap: .625rem; padding: .875rem 1.5rem; border-radius: 12px; border: 2px dashed #cbd5e1; background: #fff; cursor: pointer; font-size: .875rem; font-weight: 700; color: #94a3b8; transition: all .25s; }
.upload-btn-main svg { width: 20px; height: 20px; }
.upload-btn-main:hover { border-color: #94a3b8; color: #64748b; }
.upload-btn-main.ready { border-style: solid; border-color: #3b82f6; background: #eff6ff; color: #2563eb; box-shadow: 0 2px 8px rgba(59,130,246,.15); }
.upload-btn-main.ready:hover { background: #dbeafe; border-color: #2563eb; }
.upload-btn-main.uploading { opacity: .6; cursor: wait; }
.upload-btn-main.needs-type { border-color: #f59e0b; color: #d97706; background: #fffbeb; animation: shakeBtn .4s ease; }
@keyframes shakeBtn { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-3px); } 75% { transform: translateX(3px); } }
.upload-btn-main:disabled { opacity: .6; cursor: not-allowed; }

/* ═══ GRID VIEW ═══ */
.requisitos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; }
.grid-card { background: #fff; border: 1px solid #eef2f7; border-radius: 14px; overflow: hidden; transition: box-shadow .2s; }
.grid-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); }
.grid-preview { position: relative; width: 100%; aspect-ratio: 1/1.414; background: #fff; cursor: pointer; overflow: hidden; display: flex; align-items: center; justify-content: center; }
.grid-canvas-wrap { width: 100%; height: 100%; overflow: hidden; position: relative; background: #fff; display: flex; align-items: center; justify-content: center; }
.grid-canvas { max-width: 100%; max-height: 100%; object-fit: contain; }
.grid-placeholder { display: flex; align-items: center; justify-content: center; color: #e2e8f0; }
.grid-placeholder svg { width: 48px; height: 48px; }
.grid-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; gap: .5rem; opacity: 0; transition: opacity .2s; }
.grid-card:hover .grid-overlay { opacity: 1; }
.grid-ov-btn { width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,.9); border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; transition: all .2s; }
.grid-ov-btn svg { width: 18px; height: 18px; }
.grid-ov-btn:hover { background: #fff; transform: scale(1.1); }
.grid-ov-del { color: #ef4444; }
.grid-verified-stamp { position: absolute; top: .5rem; right: .5rem; width: 28px; height: 28px; border-radius: 50%; background: #22c55e; color: #fff; font-size: .875rem; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(34,197,94,.3); }
.grid-corner-check { position: absolute; top: .5rem; right: .5rem; width: 22px; height: 22px; border-radius: 50%; background: #22c55e; color: #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(34,197,94,.3); z-index: 2; }
.grid-corner-check svg { width: 14px; height: 14px; }
.grid-name-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: .375rem .5rem; background: linear-gradient(transparent, rgba(0,0,0,.65)); color: #fff; font-size: .6875rem; font-weight: 600; z-index: 1; }
.grid-name-overlay span { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.grid-info { padding: .875rem 1rem; }
.grid-title-row { display: flex; align-items: center; gap: .375rem; margin-bottom: .375rem; }
.grid-name { font-size: .8125rem; font-weight: 700; color: #0f172a; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.grid-meta { display: flex; align-items: center; gap: .375rem; flex-wrap: wrap; }
.grid-upload { display: flex; flex-direction: column; gap: .5rem; }
.grid-type-row { padding: .375rem; border-radius: 8px; border: 1.5px solid #e2e8f0; transition: all .3s; }
.grid-type-row.needs-type { border-color: #f59e0b; background: #fffbeb; animation: shakeType .4s ease; }
.type-chips-sm { display: flex; gap: .25rem; flex-wrap: wrap; }
.type-chip-sm { display: inline-flex; align-items: center; gap: .125rem; padding: .125rem .5rem; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; font-size: .625rem; font-weight: 600; color: #64748b; cursor: pointer; transition: all .2s; }
.type-chip-sm:hover { border-color: #3b82f6; color: #3b82f6; }
.type-chip-sm.selected { background: #3b82f6; border-color: #3b82f6; color: #fff; }
.type-chip-sm.glow { animation: chipGlowSm 2s ease-in-out infinite; border-color: #93c5fd; color: #3b82f6; }
@keyframes chipGlowSm { 0%, 100% { box-shadow: 0 0 0 0 rgba(59,130,246,.15); } 50% { box-shadow: 0 0 0 4px rgba(59,130,246,.08); } }
.chip-check-sm { width: 10px; height: 10px; }
.grid-upload-btn { display: flex; align-items: center; justify-content: center; gap: .375rem; padding: .5rem; border-radius: 8px; border: 2px dashed #cbd5e1; background: none; cursor: pointer; font-size: .75rem; font-weight: 600; color: #94a3b8; transition: all .2s; }
.grid-upload-btn:hover { border-color: #94a3b8; color: #64748b; }
.grid-upload-btn.ready { border-style: solid; border-color: #3b82f6; color: #2563eb; background: #eff6ff; }
.grid-upload-btn.ready:hover { background: #dbeafe; }
.grid-upload-btn.disabled { opacity: .4; cursor: not-allowed; }
.grid-upload-btn.needs-type { border-color: #f59e0b; color: #d97706; background: #fffbeb; }
.grid-upload-btn svg { width: 16px; height: 16px; }

/* UPLOAD PREVIEW MODAL */
.up-body { display: flex; flex-direction: column; gap: 1rem; }
.up-file-row { display: flex; align-items: center; gap: .75rem; padding: .75rem 1rem; background: #f8fafc; border-radius: 10px; border: 1px solid #eef2f7; }
.up-file-icon { width: 40px; height: 40px; border-radius: 10px; background: #dbeafe; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.up-file-icon svg { width: 20px; height: 20px; color: #2563eb; }
.up-file-meta { display: flex; flex-direction: column; min-width: 0; }
.up-file-name { font-size: .8125rem; font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.up-file-size { font-size: .6875rem; color: #94a3b8; }
.up-iframe-wrap { border-radius: 8px; overflow: hidden; border: 1px solid #eef2f7; }
.up-iframe { width: 100%; height: 400px; border: none; }

/* EMPTY & LOADING */
.empty-card { background: #fff; border: 1px solid #eef2f7; border-radius: 16px; padding: 3rem 2rem; text-align: center; }
.empty-illust { margin-bottom: 1rem; }
.empty-illust svg { width: 64px; height: 64px; color: #e2e8f0; }
.empty-title { font-size: 1rem; font-weight: 700; color: #475569; }
.empty-desc { font-size: .8125rem; color: #94a3b8; margin-top: .25rem; }
.loading-card { display: flex; align-items: center; justify-content: center; gap: .75rem; padding: 3rem; color: #94a3b8; font-size: .8125rem; }
.spinner { width: 20px; height: 20px; border: 2px solid #e2e8f0; border-top-color: #3b82f6; border-radius: 50%; animation: spin .6s linear infinite; }

/* PREVIEW MODAL */
.preview-container { min-height: 500px; }
.preview-iframe { width: 100%; height: 70vh; border: none; border-radius: 8px; }

/* REVISION SECTION */
.revision-section { margin-top: .5rem; }
.revision-card { display: flex; align-items: flex-start; gap: 1rem; padding: 1.25rem; border-radius: 14px; border: 2px solid #e2e8f0; background: #fff; transition: all .3s; }
.revision-card.revision-sent { border-color: #86efac; background: #f0fdf4; }
.revision-card.revision-done { border-color: #93c5fd; background: #eff6ff; }
.revision-icon.icon-done { background: #dcfce7; color: #22c55e; }
.revision-icon.icon-pending { background: #fef3c7; color: #f59e0b; }
.revision-card.revision-done .revision-icon { background: #dcfce7; color: #22c55e; }

.revision-cita {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: .75rem 1rem;
  margin: 0 0 .75rem;
  display: flex;
  flex-direction: column;
  gap: .375rem;
}
.cita-row { font-size: .8125rem; color: #475569; display: flex; gap: .25rem; }
.cita-row strong { color: #1e293b; }
.revision-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #eff6ff; color: #3b82f6; }
.revision-card.revision-sent .revision-icon { background: #dcfce7; color: #22c55e; }
.revision-icon svg { width: 24px; height: 24px; }
.revision-content { flex: 1; }
.revision-title { font-size: .9375rem; font-weight: 700; color: #0f172a; margin: 0 0 .375rem; }
.revision-desc { font-size: .8125rem; color: #64748b; line-height: 1.5; margin: 0 0 .75rem; }
.revision-meta { font-size: .75rem; color: #94a3b8; margin: 0 0 .5rem; }
.revision-btn { display: inline-flex; align-items: center; gap: .5rem; padding: .625rem 1.25rem; border-radius: 10px; font-size: .8125rem; font-weight: 700; border: none; cursor: pointer; transition: all .2s; }
.revision-btn svg { width: 18px; height: 18px; }
.revision-btn.finish { background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; box-shadow: 0 4px 12px rgba(34,197,94,.25); }
.revision-btn.finish:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(34,197,94,.35); }
.revision-btn.insist { background: #fff; color: #f59e0b; border: 2px solid #fde68a; }
.revision-btn.insist:hover { background: #fffbeb; border-color: #f59e0b; }
.revision-btn:disabled { opacity: .6; cursor: not-allowed; transform: none !important; }

/* REVISION COOLDOWN */
.revision-cooldown { display: flex; align-items: center; gap: .375rem; padding: .5rem .75rem; background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; font-size: .75rem; color: #92400e; margin: 0 0 .75rem; }
.revision-cooldown svg { width: 16px; height: 16px; flex-shrink: 0; }

/* DOCS UPLOADED LIST */
.docs-uploaded-list { display: flex; flex-direction: column; gap: .75rem; }
.tipo-doc-group { display: flex; flex-direction: column; gap: .375rem; }
.tipo-doc-label { font-size: .6875rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .3px; }
.doc-item-row { display: flex; align-items: center; gap: .625rem; padding: .625rem; background: #f8fafc; border: 1px solid #eef2f7; border-radius: 10px; transition: all .2s; }
.doc-item-row:hover { box-shadow: 0 2px 8px rgba(0,0,0,.04); }
.doc-thumb-small { width: 36px; height: 50px; border-radius: 6px; background: #fff; border: 1px solid #e2e8f0; overflow: hidden; cursor: pointer; flex-shrink: 0; }
.thumb-canvas-small { width: 100%; height: 100%; object-fit: contain; }
.doc-item-info { display: flex; flex-direction: column; gap: .125rem; flex: 1; min-width: 0; }
.doc-item-name { font-size: .75rem; font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.doc-caducidad { font-size: .625rem; color: #d97706; font-weight: 600; }
.badge-valido { background: #dcfce7; color: #16a34a; }
.badge-apto { background: #dbeafe; color: #2563eb; }
.badge-pending-doc { background: #f1f5f9; color: #94a3b8; }
.doc-item-actions { display: flex; gap: .25rem; flex-shrink: 0; }

/* RESPONSIVE */
@media (max-width: 767px) {
  .stats-row { grid-template-columns: repeat(2, 1fr); }
  .hero { padding: 1.25rem 1rem; }
  .hero-title { font-size: 1.125rem; }
  .req-head { padding: .75rem 1rem; }
  .req-body { padding: .75rem 1rem; }
  .doc-uploaded-row { flex-wrap: wrap; }
  .doc-thumb-wrap { order: -1; }
  .doc-uploaded-left { flex: 1 1 calc(100% - 76px); }
  .doc-uploaded-actions { flex: 1 1 100%; justify-content: flex-end; }
  .doc-locked { flex: 1 1 100%; justify-content: center; }
  .requisitos-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 768px) { .doc-page { padding-bottom: 2rem; } }
</style>
