<!-- RevAvatar — iniciales o imagen, sin degradados. -->
<template>
  <span class="rev-avatar" :class="[`s-${size}`, `t-${tone}`]" :title="name || undefined">
    <img v-if="src" :src="src" :alt="name" @error="broken = true" v-show="!broken" />
    <span v-if="!src || broken" class="rev-avatar-initials">{{ initials }}</span>
  </span>
</template>

<script setup>
import { computed, ref } from 'vue'
const props = defineProps({
  name: { type: String, default: '' },
  src: { type: String, default: '' },
  size: { type: String, default: 'md' },   // xs sm md lg
  tone: { type: String, default: 'neutral' },
})
const broken = ref(false)
const initials = computed(() =>
  (props.name || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0)).join('').toUpperCase() || '—'
)
</script>

<style scoped>
.rev-avatar {
  display: inline-grid; place-items: center; flex: none;
  border-radius: var(--rev-r-md);
  overflow: hidden;
  font-weight: 660; letter-spacing: .01em;
  border: 1px solid var(--rev-line);
}
.rev-avatar img { width: 100%; height: 100%; object-fit: cover; }
.s-xs { width: 22px; height: 22px; font-size: 9px; border-radius: var(--rev-r-sm); }
.s-sm { width: 28px; height: 28px; font-size: 10px; }
.s-md { width: 34px; height: 34px; font-size: var(--rev-fs-sm); }
.s-lg { width: 46px; height: 46px; font-size: var(--rev-fs-lg); border-radius: var(--rev-r-lg); }
.t-neutral { background: var(--rev-n-100); color: var(--rev-ink-2); }
.t-accent  { background: var(--rev-primary-50); color: var(--rev-primary-700); border-color: var(--rev-primary-200); }
</style>
