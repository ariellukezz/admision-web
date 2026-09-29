/**
 * ============================================================================
 * Gráficos del módulo Revisor — paleta y opciones base
 * ----------------------------------------------------------------------------
 * La paleta categórica está VALIDADA, no elegida a ojo: banda de luminosidad,
 * suelo de croma, separación para daltonismo (ΔE OKLab ≥ 8 en pares
 * adyacentes), suelo de visión normal (ΔE ≥ 15) y contraste contra el lienzo
 * blanco. El orden de los slots es el mecanismo de seguridad: se asignan en
 * orden fijo y NUNCA se ciclan.
 *
 * Límite conocido: más allá de 3 series ningún orden supera la prueba de
 * «todos los pares». Por eso las distribuciones de muchas categorías (áreas,
 * modalidades, programas) se dibujan como BARRAS HORIZONTALES de una sola
 * serie —una sola tinta, etiqueta directa— en lugar de anillos multicolor.
 * Es mejor lectura y además elimina el problema de color de raíz.
 * ============================================================================
 */

/* Slots categóricos — orden fijo, validado en claro sobre #FFFFFF */
export const REV_SERIES = [
  '#2C63C9', // 1 · azul
  '#E06B2E', // 2 · naranja
  '#00958C', // 3 · verde azulado
  '#D6453F', // 4 · rojo
  '#D79A00', // 5 · ámbar  (contraste < 3:1 → exige etiqueta directa o leyenda)
  '#6A4FC0', // 6 · violeta
  '#DB6C9C', // 7 · magenta
]

/* Rampa secuencial de una sola tinta (magnitud continua) */
export const REV_RAMP = ['#86A9E8', '#5A85D8', '#3A66C4', '#28509F', '#1B3C7A']

/* Colores de estado — reservados, jamás reutilizados como «serie 4» */
export const REV_SEMANTIC = {
  accent:  '#2C63C9',
  success: '#157F4E',
  warning: '#B06A00',
  danger:  '#BE3B31',
  neutral: '#8D99A9',
}

const INK        = '#6B7787'
const INK_STRONG = '#3D4653'
const GRID       = '#EDF0F4'
const SURFACE    = '#FFFFFF'
const FONT       = '"Inter", -apple-system, "Segoe UI", sans-serif'

/* ── Capa de interacción: un gráfico HTML es interactivo por defecto ─────── */
const tooltip = {
  backgroundColor: '#1A1F27',
  titleColor: '#FFFFFF',
  bodyColor: '#E3E8EE',
  titleFont: { family: FONT, size: 11, weight: '600' },
  bodyFont: { family: FONT, size: 12, weight: '500' },
  padding: 9,
  cornerRadius: 6,
  displayColors: true,
  usePointStyle: true,
  boxWidth: 8, boxHeight: 8, boxPadding: 4,
  borderColor: 'rgba(255,255,255,.08)',
  borderWidth: 1,
}

/* La leyenda sólo aparece con 2 o más series: con una, el título ya la nombra. */
const legend = (show) => (show
  ? {
      position: 'bottom',
      align: 'start',
      labels: {
        boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle',
        padding: 14, color: INK, font: { family: FONT, size: 11, weight: '500' },
      },
    }
  : { display: false })

/* Rejilla discreta: una sola línea sólida de 1px, un paso por encima del lienzo. */
const axis = (showGrid) => ({
  grid: { display: showGrid, color: GRID, drawTicks: false, lineWidth: 1 },
  border: { display: false },
  ticks: { color: INK, font: { family: FONT, size: 10.5 }, padding: 7 },
})

/** Columnas verticales — serie única por defecto. */
export const revBarOptions = ({ legend: showLegend = false, stacked = false } = {}) => ({
  responsive: true,
  maintainAspectRatio: false,
  layout: { padding: { top: 10 } },
  plugins: { legend: legend(showLegend), tooltip },
  scales: {
    x: { ...axis(false), stacked, ticks: { ...axis(false).ticks, maxRotation: 0, autoSkipPadding: 14 } },
    y: { ...axis(true), stacked, beginAtZero: true, ticks: { ...axis(true).ticks, precision: 0 } },
  },
})

/** Barras horizontales — la forma correcta para rankings y distribuciones largas. */
export const revBarHOptions = ({ legend: showLegend = false, stacked = false } = {}) => ({
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y',
  layout: { padding: { right: 34 } },
  plugins: { legend: legend(showLegend), tooltip },
  scales: {
    y: { ...axis(false), stacked, ticks: { ...axis(false).ticks, color: INK_STRONG, font: { family: FONT, size: 11 } } },
    x: { ...axis(true), stacked, beginAtZero: true, ticks: { ...axis(true).ticks, precision: 0 } },
  },
})

/** Serie temporal — línea de 2px, punto ≥8px con anillo del lienzo. */
export const revLineOptions = () => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: { mode: 'index', intersect: false },
  layout: { padding: { top: 10 } },
  plugins: { legend: legend(false), tooltip },
  scales: {
    x: { ...axis(false), ticks: { ...axis(false).ticks, maxRotation: 0, autoSkipPadding: 16 } },
    y: { ...axis(true), beginAtZero: true, ticks: { ...axis(true).ticks, precision: 0 } },
  },
})

/* ── Especificaciones de marca, en un solo sitio ─────────────────────────── */

/** Columna vertical: extremo de dato redondeado 4px, base cuadrada, ≤24px. */
export const revBar = (data, color = REV_SEMANTIC.accent, label = '') => ({
  label,
  data,
  backgroundColor: color,
  borderRadius: { topLeft: 4, topRight: 4, bottomLeft: 0, bottomRight: 0 },
  borderSkipped: 'start',
  maxBarThickness: 24,
  borderColor: SURFACE,
  borderWidth: { top: 0, right: 1, bottom: 0, left: 1 },  /* 2px de aire entre vecinas */
})

/** Barra horizontal: mismo criterio, girado. */
export const revBarH = (data, color = REV_SEMANTIC.accent, label = '') => ({
  label,
  data,
  backgroundColor: color,
  borderRadius: { topRight: 4, bottomRight: 4, topLeft: 0, bottomLeft: 0 },
  borderSkipped: 'start',
  maxBarThickness: 20,
  borderColor: SURFACE,
  borderWidth: { top: 1, right: 0, bottom: 1, left: 0 },
})

/** Línea con lavado de área al 10% — función, no adorno: separa serie de fondo. */
export const revLine = (data, color = REV_SEMANTIC.accent, label = '') => ({
  label,
  data,
  borderColor: color,
  borderWidth: 2,
  tension: 0.32,
  fill: true,
  backgroundColor: (ctx) => revAreaFill(ctx, color),
  pointRadius: 0,
  pointHoverRadius: 5,
  pointHoverBorderWidth: 2,
  pointHoverBorderColor: SURFACE,
  pointHoverBackgroundColor: color,
  pointHitRadius: 14,
})

export const revAreaFill = (ctx, hex = REV_SEMANTIC.accent) => {
  const area = ctx?.chart?.chartArea
  if (!area) return hex + '1A'
  const g = ctx.chart.ctx.createLinearGradient(0, area.top, 0, area.bottom)
  g.addColorStop(0, hex + '1F')
  g.addColorStop(1, hex + '00')
  return g
}

/** Asignación en orden fijo; nunca se cicla más allá de los 7 slots. */
export const revSeries = (n) => REV_SERIES.slice(0, Math.min(n, REV_SERIES.length))

/** Recorta una distribución larga a las n primeras + «Otros». */
export const foldOther = (rows, n = 8, labelKey = 'label', valueKey = 'value') => {
  if (rows.length <= n) return rows
  const head = rows.slice(0, n)
  const tail = rows.slice(n)
  const otros = tail.reduce((s, r) => s + Number(r[valueKey] || 0), 0)
  return [...head, { [labelKey]: `Otros (${tail.length})`, [valueKey]: otros }]
}

export const pctOf = (value, total) => (total > 0 ? Math.round((Number(value) / total) * 100) : 0)
