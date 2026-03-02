<template>
  <div class="admission-schedule">
    <div class="schedule-header">
      <div class="header-content">
        <div>
          <h2>CRONOGRAMA DE ADMISIÓN 2026</h2>
          <p class="subtitle">Segunda Especialidad - UNA-P</p>
        </div>
        <a-button 
          type="primary" 
          class="preinscription-btn"
          @click="showPreinscriptionModal = true"
        >
          <template #icon>
            <FormOutlined />
          </template>
          INICIAR PREINSCRIPCIÓN
        </a-button>
      </div>
    </div>

    <div class="stages-grid">
      <div v-for="(stage, i) in stages" :key="i" class="stage-card" :class="{ 'active': i === activeStage }">
        <div class="stage-number">ETAPA {{ i + 1 }}</div>
        <div class="stage-name">{{ stage }}</div>
      </div>
    </div>

    <div class="schedule-table-container">
      <table class="schedule-table">
        <thead>
          <tr>
            <th>#</th>
            <th>ACTIVIDAD</th>
            <th>FECHAS</th>
            <th>ETAPA</th>
            <th>OBSERVACIONES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in timelineData" :key="index" :class="getRowClass(index)">
            <td class="cell-number">{{ index + 1 }}</td>
            <td class="cell-activity">{{ item.actividad }}</td>
            <td class="cell-date">{{ item.fecha }}</td>
            <td>
              <span class="stage-badge" :style="{ backgroundColor: getStageColor(index) }">
                {{ getStageLabel(index) }}
              </span>
            </td>
            <td class="cell-note">{{ item.nota || '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="summary-grid">
      <div class="summary-card">
        <div class="summary-number">{{ timelineData.length }}</div>
        <div class="summary-label">ACTIVIDADES TOTALES</div>
      </div>
      <div class="summary-card">
        <div class="summary-number">{{ stages.length }}</div>
        <div class="summary-label">ETAPAS</div>
      </div>
      <div class="summary-card">
        <div class="summary-number">Feb - Dic</div>
        <div class="summary-label">PERIODO 2026</div>
      </div>
      <div class="summary-card">
        <div class="summary-number">11 meses</div>
        <div class="summary-label">DURACIÓN</div>
      </div>
    </div>

    <div class="note-box">
      <strong>Nota:</strong> Cronograma sujeto a modificaciones según disposiciones oficiales de la UNA-P.
    </div>

    <!-- Modal de Preinscripción Automático -->
    <a-modal
      v-model:open="showPreinscriptionModal"
      title="INICIAR PREINSCRIPCIÓN"
      :footer="null"
      centered
      width="500px"
      class="preinscription-modal"
    >
      <div class="modal-content">
        <div class="modal-icon">
          <FormOutlined />
        </div>
        
        <h3>¿Deseas iniciar tu preinscripción?</h3>
        <p class="modal-description">Serás redirigido al formulario de preinscripción para completar tus datos</p>

        <div class="modal-actions">
          <a-button @click="showPreinscriptionModal = false">
            Cancelar
          </a-button>
          <a-button 
            type="primary" 
            @click="startPreinscription"
            class="action-btn"
          >
            Iniciar ahora
          </a-button>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { FormOutlined } from '@ant-design/icons-vue';

const showPreinscriptionModal = ref(false);

// Abrir modal automáticamente al cargar la página
onMounted(() => {
  showPreinscriptionModal.value = true;
});

const stages = [
  'CONVOCATORIA Y PREINSCRIPCIÓN',
  'EVALUACIÓN DE EXPEDIENTES',
  'ENTREVISTA Y RESULTADOS',
  'MATRÍCULA Y CLASES'
];

const activeStage = 0;

const timelineData = ref([
  { actividad: 'CONVOCATORIA OFICIAL', fecha: '02 FEB - 06 MAR 2026', nota: 'Publicación oficial' },
  { actividad: 'PREINSCRIPCIÓN VIRTUAL', fecha: '02 - 15 MAR 2026', nota: 'Registro en línea' },
  { actividad: 'RECEPCIÓN DE EXPEDIENTES', fecha: '09 - 21 MAR 2026', nota: 'Documentación completa' },
  { actividad: 'EVALUACIÓN DE EXPEDIENTES', fecha: '23 - 26 MAR 2026', nota: 'Revisión por comisión' },
  { actividad: 'ENTREVISTA VIRTUAL', fecha: '27 - 29 MAR 2026', nota: 'Evaluación de competencias' },
  { actividad: 'PUBLICACIÓN DE RESULTADOS', fecha: '30 MAR 2026', nota: 'Lista de ingresantes' },
  { actividad: 'MATRÍCULA I SEMESTRE', fecha: '31 MAR - 04 ABR 2026', nota: 'Proceso ordinario' },
  { actividad: 'INICIO I SEMESTRE', fecha: '04 ABR 2026', nota: 'Inicio de clases' },
  { actividad: 'FINALIZACIÓN I SEMESTRE', fecha: '31 JUL 2026', nota: 'Fin primer ciclo' },
  { actividad: 'MATRÍCULAS II SEMESTRE', fecha: '01 - 07 AGO 2026', nota: 'Matrícula segundo ciclo' },
  { actividad: 'INICIO II SEMESTRE', fecha: '08 AGO 2026', nota: 'Inicio segundo ciclo' },
  { actividad: 'FINALIZACIÓN II SEMESTRE', fecha: '12 DIC 2026', nota: 'Fin año académico' }
]);

const getRowClass = (index) => {
  if (index < 2) return 'row-stage1';
  if (index < 4) return 'row-stage2';
  if (index < 6) return 'row-stage3';
  return 'row-stage4';
};

const getStageColor = (index) => {
  if (index < 2) return '#1890ff';
  if (index < 4) return '#52c41a';
  if (index < 6) return '#fa8c16';
  return '#722ed1';
};

const getStageLabel = (index) => {
  if (index < 2) return 'ETAPA 1';
  if (index < 4) return 'ETAPA 2';
  if (index < 6) return 'ETAPA 3';
  return 'ETAPA 4';
};

const startPreinscription = () => {
  window.location.href = '/preinscripcion-segundas-2026-formulario/preinscripcion';
  showPreinscriptionModal.value = false;
};
</script>

<style scoped>
.admission-schedule {
  background: white;
  border-radius: 12px;
  padding: 32px;
  margin: 20px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.schedule-header {
  margin-bottom: 32px;
  border-bottom: 2px solid #1890ff;
  padding-bottom: 16px;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.schedule-header h2 {
  margin: 0;
  color: #1c1c1e;
  font-size: 26px;
  font-weight: 600;
}

.subtitle {
  margin-top: 8px;
  color: #595959;
  font-size: 14px;
}

.preinscription-btn {
  height: 48px;
  padding: 0 24px;
  font-size: 14px;
  font-weight: 600;
  background: #1890ff;
  border: none;
  box-shadow: 0 4px 12px rgba(24,144,255,0.3);
  transition: all 0.3s;
}

.preinscription-btn:hover {
  background: #40a9ff;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(24,144,255,0.4);
}

.stages-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 32px;
}

.stage-card {
  background: #fafafa;
  border: 2px solid #e8e8e8;
  border-radius: 12px;
  padding: 16px;
  text-align: center;
  transition: all 0.3s;
}

.stage-card.active {
  border-color: #1890ff;
  background: #e6f7ff;
  box-shadow: 0 0 0 2px rgba(24,144,255,0.2);
}

.stage-number {
  font-weight: bold;
  color: #1890ff;
  font-size: 12px;
  margin-bottom: 8px;
}

.stage-name {
  font-size: 13px;
  color: #1c1c1e;
  line-height: 1.4;
}

.schedule-table-container {
  overflow-x: auto;
  margin-bottom: 32px;
  border: 1px solid #f0f0f0;
  border-radius: 12px;
}

.schedule-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.schedule-table th {
  background: #fafafa;
  padding: 16px 12px;
  text-align: left;
  font-weight: 600;
  color: #1c1c1e;
  border-bottom: 2px solid #f0f0f0;
}

.schedule-table td {
  padding: 14px 12px;
  border-bottom: 1px solid #f0f0f0;
  color: #1c1c1e;
}

.schedule-table tbody tr:hover {
  background: #fafafa;
}

.row-stage1:nth-child(odd) { background: #f0f8ff; }
.row-stage2:nth-child(odd) { background: #f6ffed; }
.row-stage3:nth-child(odd) { background: #fff7e6; }
.row-stage4:nth-child(odd) { background: #f9f0ff; }

.cell-number {
  font-weight: bold;
  color: #1890ff;
  text-align: center;
}
.cell-activity { font-weight: 500; }
.cell-date { font-family: monospace; color: #1890ff; font-weight: 500; }
.cell-note { color: #595959; font-size: 12px; }

.stage-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  color: white;
  font-size: 11px;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.summary-card {
  background: #1890ff;
  color: white;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}

.summary-number {
  font-size: 28px;
  font-weight: 600;
  margin-bottom: 8px;
}

.summary-label {
  font-size: 12px;
  opacity: 0.9;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.note-box {
  background: #fff7e6;
  border: 1px solid #ffd591;
  border-radius: 12px;
  padding: 16px;
  font-size: 13px;
  color: #1c1c1e;
}

.modal-content {
  text-align: center;
  padding: 20px 0;
}

.modal-icon {
  width: 64px;
  height: 64px;
  background: #e6f7ff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
  font-size: 32px;
  color: #1890ff;
}

.modal-content h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1c1c1e;
  margin-bottom: 8px;
}

.modal-description {
  color: #595959;
  margin-bottom: 32px;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 16px;
}

.modal-actions :deep(.ant-btn) {
  min-width: 120px;
  height: 44px;
  font-size: 14px;
  font-weight: 500;
}

.action-btn {
  background: #1890ff;
  border: none;
}

.action-btn:hover {
  background: #40a9ff !important;
}

@media (max-width: 1200px) {
  .stages-grid,
  .summary-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
  .admission-schedule { padding: 16px; margin: 10px; }
  .stages-grid,
  .summary-grid { grid-template-columns: 1fr; }
  .schedule-table th,
  .schedule-table td { padding: 12px 8px; font-size: 12px; }
  .header-content { flex-direction: column; text-align: center; }
  .preinscription-btn { width: 100%; }
  .modal-actions { flex-direction: column; }
  .modal-actions :deep(.ant-btn) { width: 100%; }
}
</style>