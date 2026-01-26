<template>
  <div class="admission-schedule">
    <!-- Encabezado -->
    <div class="schedule-header">
      <h2>📅 CRONOGRAMA DE ADMISIÓN 2026</h2>
      <p class="subtitle">Segunda Especialidad - UNA-P</p>
    </div>

    <!-- Cuadro de Etapas -->
    <div class="stages-grid">
      <div v-for="(stage, i) in stages" :key="i" class="stage-card" :class="{ 'active': i === activeStage }">
        <div class="stage-number">ETAPA {{ i + 1 }}</div>
        <div class="stage-name">{{ stage }}</div>
      </div>
    </div>

    <!-- Tabla de Cronograma -->
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

    <!-- Resumen en Cuadros -->
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

    <!-- Nota -->
    <div class="note-box">
      <strong>Nota:</strong> Cronograma sujeto a modificaciones según disposiciones oficiales de la UNA-P.
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

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
</script>

<style scoped>
.admission-schedule {
  background: white;
  border-radius: 12px;
  padding: 32px;
  margin: 20px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.schedule-header {
  text-align: center;
  margin-bottom: 32px;
  border-bottom: 2px solid #1890ff;
  padding-bottom: 16px;
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

/* Etapas */
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
  cursor: default;
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

/* Tabla */
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

/* Filas coloreadas */
.row-stage1:nth-child(odd) { background: #f0f8ff; }
.row-stage2:nth-child(odd) { background: #f6ffed; }
.row-stage3:nth-child(odd) { background: #fff7e6; }
.row-stage4:nth-child(odd) { background: #f9f0ff; }

/* Celdas */
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

/* Resumen */
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

/* Nota */
.note-box {
  background: #fff7e6;
  border: 1px solid #ffd591;
  border-radius: 12px;
  padding: 16px;
  font-size: 13px;
  color: #1c1c1e;
}

/* Responsive */
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
}
</style>
