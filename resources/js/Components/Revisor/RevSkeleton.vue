<!-- RevSkeleton — carga que respeta la forma de lo que va a llegar. -->
<template>
  <div v-if="variant === 'table'" class="rev-sk-table" aria-hidden="true">
    <div v-for="r in rows" :key="r" class="rev-sk-row">
      <div v-for="c in cols" :key="c" class="rev-skel rev-sk-cell" :style="cellStyle(c)" />
    </div>
  </div>
  <div v-else-if="variant === 'list'" class="rev-sk-list" aria-hidden="true">
    <div v-for="r in rows" :key="r" class="rev-sk-item">
      <div class="rev-skel rev-sk-avatar" />
      <div class="rev-sk-lines">
        <div class="rev-skel" style="height:10px;width:44%" />
        <div class="rev-skel" style="height:9px;width:26%" />
      </div>
    </div>
  </div>
  <div v-else class="rev-skel" :style="{ height, width }" aria-hidden="true" />
</template>

<script setup>
const props = defineProps({
  variant: { type: String, default: 'block' },  // block | table | list
  rows: { type: Number, default: 6 },
  cols: { type: Number, default: 5 },
  height: { type: String, default: '12px' },
  width: { type: String, default: '100%' },
})
const widths = ['34%', '18%', '22%', '14%', '20%', '16%', '24%']
const cellStyle = (c) => ({ height: '10px', width: widths[(c - 1) % widths.length] })
</script>

<style scoped>
.rev-sk-table { display: flex; flex-direction: column; }
.rev-sk-row { display: flex; align-items: center; gap: var(--rev-s-8); padding: 13px var(--rev-s-6); border-bottom: 1px solid var(--rev-line-soft); }
.rev-sk-cell { flex: 1 1 0; }
.rev-sk-list { display: flex; flex-direction: column; }
.rev-sk-item { display: flex; align-items: center; gap: var(--rev-s-5); padding: 12px var(--rev-s-6); border-bottom: 1px solid var(--rev-line-soft); }
.rev-sk-avatar { width: 30px; height: 30px; border-radius: var(--rev-r-md); flex: none; }
.rev-sk-lines { display: flex; flex-direction: column; gap: 6px; flex: 1 1 auto; }
</style>
