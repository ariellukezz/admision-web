<!--
  RevTable — tabla densa nativa. Cabecera fija, filas de 38–44px, zebra por hover.
  Gestiona sus tres estados: cargando, vacío y con datos. La fila puede ser
  seleccionable para alimentar un panel de detalle (split view).
-->
<template>
  <div class="rev-table-wrap" :class="{ 'is-scroll': scroll }">
    <table class="rev-table" :class="[`d-${density}`]">
      <thead>
        <tr>
          <th
            v-for="col in columns"
            :key="col.key"
            :style="colStyle(col)"
            :class="[`a-${col.align || 'left'}`, { 'is-sortable': col.sortable, 'is-sticky': col.sticky }]"
            :aria-sort="ariaSort(col)"
            @click="col.sortable && emit('sort', col.key)"
          >
            <span class="rev-th-inner">
              {{ col.title }}
              <RevIcon
                v-if="col.sortable"
                :name="sortKey === col.key && sortDir === 'asc' ? 'chevron-up' : 'chevron-down'"
                size="xs"
                class="rev-th-sort"
                :class="{ 'is-active': sortKey === col.key }"
              />
            </span>
          </th>
        </tr>
      </thead>

      <tbody v-if="loading">
        <tr><td :colspan="columns.length" class="rev-td-state">
          <RevSkeleton variant="table" :rows="skeletonRows" :cols="columns.length" />
        </td></tr>
      </tbody>

      <tbody v-else-if="!rows.length">
        <tr><td :colspan="columns.length" class="rev-td-state"><slot name="empty" /></td></tr>
      </tbody>

      <tbody v-else>
        <tr
          v-for="(row, i) in rows"
          :key="rowKey ? row[rowKey] : i"
          class="rev-tr"
          :class="{ 'is-selected': selectedKey !== null && rowKey && row[rowKey] === selectedKey, 'is-clickable': clickable }"
          :tabindex="clickable ? 0 : undefined"
          @click="clickable && emit('rowClick', row)"
          @keydown.enter="clickable && emit('rowClick', row)"
        >
          <td
            v-for="col in columns"
            :key="col.key"
            :class="[`a-${col.align || 'left'}`, { 'is-sticky': col.sticky }]"
          >
            <slot :name="`cell:${col.key}`" :row="row" :value="row[col.key]" :index="i">
              {{ row[col.key] }}
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import RevIcon from './RevIcon.vue'
import RevSkeleton from './RevSkeleton.vue'

const props = defineProps({
  columns: { type: Array, required: true },   // [{key,title,width,align,sortable,sticky}]
  rows: { type: Array, default: () => [] },
  rowKey: { type: String, default: 'id' },
  loading: { type: Boolean, default: false },
  density: { type: String, default: 'comfortable' },  // compact | comfortable
  clickable: { type: Boolean, default: false },
  selectedKey: { type: [String, Number], default: null },
  sortKey: { type: String, default: '' },
  sortDir: { type: String, default: 'asc' },
  scroll: { type: Boolean, default: true },
  skeletonRows: { type: Number, default: 7 },
})
const emit = defineEmits(['rowClick', 'sort'])

const colStyle = (col) => (col.width ? { width: col.width, minWidth: col.width } : {})
const ariaSort = (col) => {
  if (!col.sortable) return undefined
  if (props.sortKey !== col.key) return 'none'
  return props.sortDir === 'asc' ? 'ascending' : 'descending'
}
</script>

<style scoped>
.rev-table-wrap { width: 100%; min-width: 0; }
.rev-table-wrap.is-scroll { overflow-x: auto; }

.rev-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: var(--rev-fs-md); }

.rev-table thead th {
  position: sticky; top: 0; z-index: 2;
  background: var(--rev-surface-2);
  border-bottom: 1px solid var(--rev-line);
  padding: 13px var(--rev-s-6);
  font-size: var(--rev-fs-2xs);
  font-weight: 650;
  letter-spacing: var(--rev-track-caps);
  text-transform: uppercase;
  color: var(--rev-ink-4);
  white-space: nowrap;
  text-align: left;
  user-select: none;
}
.rev-th-inner { display: inline-flex; align-items: center; gap: 3px; }
.rev-table thead th.is-sortable { cursor: pointer; transition: color var(--rev-t-fast) var(--rev-ease); }
.rev-table thead th.is-sortable:hover { color: var(--rev-ink-2); }
.rev-th-sort { opacity: 0; transition: opacity var(--rev-t-fast) var(--rev-ease); }
.rev-table thead th.is-sortable:hover .rev-th-sort { opacity: .5; }
.rev-th-sort.is-active { opacity: 1; color: var(--rev-primary-600); }

.rev-table tbody td {
  padding: 15px var(--rev-s-6);
  border-bottom: 1px solid var(--rev-line-soft);
  color: var(--rev-ink-2);
  vertical-align: middle;
}
.d-compact tbody td { padding: 9px var(--rev-s-6); }
.d-compact thead th { padding: 9px var(--rev-s-6); }

.rev-tr { transition: background var(--rev-t-fast) var(--rev-ease); }
.rev-tr.is-clickable { cursor: pointer; }
.rev-tr:hover > td { background: var(--rev-n-25); }
.rev-tr.is-selected > td { background: var(--rev-primary-50); }
.rev-tr.is-selected > td:first-child { box-shadow: inset 2px 0 0 var(--rev-primary-600); }
.rev-tr:focus-visible { outline: none; }
.rev-tr:focus-visible > td { background: var(--rev-primary-50); box-shadow: inset 0 0 0 1px var(--rev-primary-300); }
.rev-table tbody tr:last-child > td { border-bottom: 0; }

.a-left { text-align: left } .a-center { text-align: center } .a-right { text-align: right }
th.is-sticky, td.is-sticky { position: sticky; right: 0; background: var(--rev-surface); z-index: 1; }
.rev-tr:hover > td.is-sticky { background: var(--rev-n-25); }
th.is-sticky { background: var(--rev-surface-2); z-index: 3; }

.rev-td-state { padding: 0 !important; border-bottom: 0 !important; background: var(--rev-surface) !important; }
</style>
