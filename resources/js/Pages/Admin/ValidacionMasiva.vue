<template>
  <Head title="Validación Masiva" />
  <AuthenticatedLayout>
    <div class="vm-page">

      <div class="vm-header">
        <div class="vm-header-content">
          <h1>Validación Masiva de Documentos</h1>
          <p>Marca como válidos los documentos de múltiples postulantes con solicitudes de revisión pendientes.</p>
        </div>
        <button class="vm-refresh" @click="cargarSolicitudes" :class="{ spinning: loading }">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
        </button>
      </div>

      <!-- Stats -->
      <div class="vm-stats" v-if="!loading && solicitudes.length > 0">
        <div class="vm-stat">
          <span class="vm-stat-num">{{ solicitudes.length }}</span>
          <span class="vm-stat-label">Solicitudes pendientes</span>
        </div>
        <div class="vm-stat">
          <span class="vm-stat-num">{{ seleccionados.length }}</span>
          <span class="vm-stat-label">Seleccionados</span>
        </div>
        <div class="vm-stat">
          <span class="vm-stat-num text-emerald-600">{{ totalDocs }}</span>
          <span class="vm-stat-label">Documentos a validar</span>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="vm-loading">
        <a-spin size="large" />
        <p>Cargando solicitudes pendientes...</p>
      </div>

      <!-- Table -->
      <div v-else-if="solicitudes.length > 0" class="vm-table-wrap">
        <div class="vm-table-header">
          <label class="vm-check-all">
            <input type="checkbox" :checked="todosSeleccionados" @change="toggleTodos" />
            <span>Seleccionar todos</span>
          </label>
          <button
            v-if="seleccionados.length > 0"
            class="vm-btn-validate"
            @click="abrirModal"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Validar {{ seleccionados.length }} seleccionado(s)
          </button>
        </div>

        <div class="vm-table">
          <div class="vm-thead">
            <div class="vm-th vm-th-check"></div>
            <div class="vm-th">Postulante</div>
            <div class="vm-th">DNI</div>
            <div class="vm-th">Modalidad</div>
            <div class="vm-th">Programa</div>
            <div class="vm-th vm-th-center">Docs</div>
            <div class="vm-th">Solicitada</div>
            <div class="vm-th">Estado</div>
          </div>
          <div v-for="s in solicitudes" :key="s.id_solicitud" class="vm-row" :class="{ selected: isSelected(s) }">
            <div class="vm-td vm-td-check">
              <input type="checkbox" :checked="isSelected(s)" @change="toggleSeleccion(s)" />
            </div>
            <div class="vm-td vm-td-name">{{ s.nombre_completo }}</div>
            <div class="vm-td vm-td-dni">{{ s.dni }}</div>
            <div class="vm-td">{{ s.modalidad }}</div>
            <div class="vm-td">{{ s.programa }}</div>
            <div class="vm-td vm-td-center">
              <span class="vm-doc-badge">{{ s.docs_validos }}/{{ s.total_docs }}</span>
            </div>
            <div class="vm-td vm-td-date">{{ s.solicitada_at }}</div>
            <div class="vm-td">
              <span class="vm-estado-badge" :class="s.estado === 'en_revision' ? 'en-revision' : 'pendiente'">
                {{ s.estado === 'en_revision' ? 'En curso' : 'Pendiente' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="vm-empty">
        <div class="vm-empty-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3>No hay solicitudes pendientes</h3>
        <p>Todos los postulantes están al día con sus revisiones.</p>
      </div>

      <!-- Modal de confirmación -->
      <a-modal
        v-model:open="modalVisible"
        title="Confirmar Validación Masiva"
        width="600px"
        :footer="null"
        @cancel="modalVisible = false"
      >
        <div class="vm-modal">
          <div class="vm-modal-warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <div>
              <p class="vm-modal-text">
                Se validarán <strong>{{ totalDocs }}</strong> documento(s) de
                <strong>{{ seleccionados.length }}</strong> postulante(s).
                Todas las solicitudes de revisión activas serán marcadas como completadas.
              </p>
            </div>
          </div>

          <div class="vm-modal-form">
            <label class="vm-modal-label">Motivo de la validación *</label>
            <textarea
              v-model="motivo"
              class="vm-modal-textarea"
              rows="3"
              placeholder="Ej: Validación administrativa por cierre de proceso. Documentos verificados en oficina."
            ></textarea>
          </div>

          <div class="vm-modal-actions">
            <button class="vm-btn-cancel" @click="modalVisible = false">Cancelar</button>
            <button class="vm-btn-confirm" :disabled="!motivo.trim() || validando" @click="ejecutarValidacion">
              <span v-if="validando" class="spinner"></span>
              <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Confirmar Validación
            </button>
          </div>
        </div>
      </a-modal>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { notification } from 'ant-design-vue';
import axios from 'axios';

const solicitudes = ref([]);
const loading = ref(true);
const seleccionados = ref([]);
const modalVisible = ref(false);
const motivo = ref('');
const validando = ref(false);

const totalDocs = computed(() => {
  return seleccionados.value.reduce((sum, s) => sum + s.total_docs, 0);
});

const todosSeleccionados = computed(() => {
  return solicitudes.value.length > 0 && seleccionados.value.length === solicitudes.value.length;
});

const isSelected = (s) => seleccionados.value.some(sel => sel.id_solicitud === s.id_solicitud);

const toggleSeleccion = (s) => {
  if (isSelected(s)) {
    seleccionados.value = seleccionados.value.filter(sel => sel.id_solicitud !== s.id_solicitud);
  } else {
    seleccionados.value.push(s);
  }
};

const toggleTodos = () => {
  if (todosSeleccionados.value) {
    seleccionados.value = [];
  } else {
    seleccionados.value = [...solicitudes.value];
  }
};

const cargarSolicitudes = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/admin/api/solicitudes-pendientes');
    if (res.data.success) {
      solicitudes.value = res.data.datos;
      seleccionados.value = [];
    }
  } catch (e) {
    notification.error({
      message: 'Error',
      description: 'No se pudieron cargar las solicitudes pendientes',
    });
  } finally {
    loading.value = false;
  }
};

const abrirModal = () => {
  motivo.value = '';
  modalVisible.value = true;
};

const ejecutarValidacion = async () => {
  if (!motivo.value.trim()) return;
  validando.value = true;
  try {
    const res = await axios.post('/admin/api/validacion-masiva', {
      ids_postulantes: seleccionados.value.map(s => s.id_postulante),
      motivo: motivo.value.trim(),
    });
    if (res.data.success) {
      notification.success({
        message: 'Validación completada',
        description: res.data.mensaje,
      });
      modalVisible.value = false;
      await cargarSolicitudes();
    }
  } catch (e) {
    notification.error({
      message: 'Error',
      description: e.response?.data?.mensaje || 'No se pudo completar la validación',
    });
  } finally {
    validando.value = false;
  }
};

cargarSolicitudes();
</script>

<style scoped>
.vm-page { padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem; }

.vm-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 1.75rem;
  background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 50%, #3b7ab5 100%);
  border-radius: 16px;
  color: #fff;
}
.vm-header-content h1 { margin: 0; font-size: 1.375rem; font-weight: 800; }
.vm-header-content p { margin: .25rem 0 0; font-size: .8125rem; color: rgba(255,255,255,.7); }
.vm-refresh {
  width: 40px; height: 40px;
  border-radius: 10px;
  background: rgba(255,255,255,.12);
  border: 1px solid rgba(255,255,255,.15);
  display: flex; align-items: center; justify-content: center;
  color: rgba(255,255,255,.8);
  cursor: pointer;
  transition: all .2s;
}
.vm-refresh svg { width: 20px; height: 20px; }
.vm-refresh:hover { background: rgba(255,255,255,.2); color: #fff; }
.vm-refresh.spinning svg { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.vm-stats { display: flex; gap: 1rem; }
.vm-stat {
  flex: 1;
  background: #fff;
  border: 1px solid #eef2f7;
  border-radius: 14px;
  padding: 1rem 1.25rem;
  text-align: center;
}
.vm-stat-num { display: block; font-size: 1.75rem; font-weight: 800; color: #1e293b; line-height: 1; }
.vm-stat-label { font-size: .6875rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-top: .375rem; display: block; }

.vm-loading { display: flex; flex-direction: column; align-items: center; gap: 1rem; padding: 3rem; }
.vm-loading p { color: #64748b; font-weight: 600; }

.vm-table-wrap { background: #fff; border: 1px solid #eef2f7; border-radius: 14px; overflow: hidden; }
.vm-table-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .75rem 1rem;
  border-bottom: 1px solid #f1f5f9;
}
.vm-check-all { display: flex; align-items: center; gap: .5rem; font-size: .8125rem; font-weight: 600; color: #475569; cursor: pointer; }
.vm-check-all input { width: 16px; height: 16px; cursor: pointer; }
.vm-btn-validate {
  display: inline-flex;
  align-items: center;
  gap: .375rem;
  padding: .5rem 1rem;
  border: none;
  border-radius: 10px;
  background: #1B3A5C;
  color: #fff;
  font-size: .8125rem;
  font-weight: 700;
  cursor: pointer;
  transition: all .2s;
}
.vm-btn-validate:hover { background: #2D5A8E; }
.vm-btn-validate svg { width: 16px; height: 16px; }

.vm-table { overflow-x: auto; }
.vm-thead { display: flex; align-items: center; padding: .625rem 1rem; background: #f8fafc; border-bottom: 1px solid #eef2f7; min-width: 800px; }
.vm-th { font-size: .6875rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .03em; flex: 1; }
.vm-th-check { width: 40px; flex: 0 0 40px; }
.vm-th-center { text-align: center; }
.vm-row { display: flex; align-items: center; padding: .625rem 1rem; border-bottom: 1px solid #f8fafc; min-width: 800px; transition: background .15s; }
.vm-row:hover { background: #f8fafc; }
.vm-row.selected { background: #eff6ff; }
.vm-row:last-child { border-bottom: none; }
.vm-td { font-size: .8125rem; color: #1e293b; flex: 1; }
.vm-td-check { width: 40px; flex: 0 0 40px; }
.vm-td-check input { width: 16px; height: 16px; cursor: pointer; }
.vm-td-name { font-weight: 600; text-transform: capitalize; }
.vm-td-dni { font-variant-numeric: tabular-nums; color: #64748b; }
.vm-td-center { text-align: center; }
.vm-td-date { font-size: .75rem; color: #94a3b8; }
.vm-doc-badge {
  display: inline-flex;
  align-items: center;
  padding: .125rem .5rem;
  border-radius: 100px;
  font-size: .6875rem;
  font-weight: 700;
  background: #f1f5f9;
  color: #475569;
}
.vm-estado-badge {
  display: inline-flex;
  padding: .125rem .5rem;
  border-radius: 100px;
  font-size: .625rem;
  font-weight: 700;
  text-transform: uppercase;
}
.vm-estado-badge.pendiente { background: #fef3c7; color: #d97706; }
.vm-estado-badge.en-revision { background: #dbeafe; color: #2563eb; }

.vm-empty {
  background: #fff;
  border: 1px solid #eef2f7;
  border-radius: 14px;
  padding: 3rem 2rem;
  text-align: center;
}
.vm-empty-icon svg { width: 56px; height: 56px; color: #e2e8f0; }
.vm-empty h3 { font-size: 1rem; font-weight: 700; color: #475569; margin: 0 0 .25rem; }
.vm-empty p { font-size: .8125rem; color: #94a3b8; margin: 0; }

/* Modal */
.vm-modal { display: flex; flex-direction: column; gap: 1.25rem; }
.vm-modal-warning {
  display: flex;
  align-items: flex-start;
  gap: .625rem;
  padding: .875rem 1rem;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 10px;
}
.vm-modal-warning svg { width: 24px; height: 24px; color: #d97706; flex-shrink: 0; }
.vm-modal-text { font-size: .8125rem; color: #92400e; line-height: 1.5; margin: 0; }
.vm-modal-text strong { color: #78350f; }
.vm-modal-form { display: flex; flex-direction: column; gap: .375rem; }
.vm-modal-label { font-size: .8125rem; font-weight: 700; color: #1e293b; }
.vm-modal-textarea {
  padding: .625rem .75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: .8125rem;
  color: #1e293b;
  font-family: inherit;
  outline: none;
  resize: vertical;
  transition: border-color .2s;
}
.vm-modal-textarea:focus { border-color: #3b82f6; }
.vm-modal-actions { display: flex; justify-content: flex-end; gap: .625rem; }
.vm-btn-cancel {
  padding: .5rem 1.25rem;
  border: 1px solid #d1d5db;
  border-radius: 10px;
  background: #fff;
  color: #475569;
  font-size: .8125rem;
  font-weight: 700;
  cursor: pointer;
}
.vm-btn-cancel:hover { background: #f8fafc; }
.vm-btn-confirm {
  display: inline-flex;
  align-items: center;
  gap: .375rem;
  padding: .5rem 1.25rem;
  border: none;
  border-radius: 10px;
  background: #1B3A5C;
  color: #fff;
  font-size: .8125rem;
  font-weight: 800;
  cursor: pointer;
  transition: all .2s;
}
.vm-btn-confirm:hover { background: #2D5A8E; }
.vm-btn-confirm:disabled { opacity: .5; cursor: not-allowed; }
.vm-btn-confirm svg { width: 16px; height: 16px; }

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid currentColor;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin .6s linear infinite;
}
</style>
