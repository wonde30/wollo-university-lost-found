<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  shape?: 'line' | 'circle' | 'rectangle'
  width?: string
  height?: string
  preset?: 'table' | 'card' | 'form'
  rows?: number
}

const props = withDefaults(defineProps<Props>(), {
  shape: 'rectangle',
  width: '100%',
  height: '1.25rem',
  preset: undefined,
  rows: 4,
})

const shapeClasses = computed(() => {
  switch (props.shape) {
    case 'circle':
      return 'rounded-full'
    case 'line':
      return 'rounded-md'
    case 'rectangle':
    default:
      return 'rounded-xl'
  }
})
</script>

<template>
  <!-- Table Skeleton Preset -->
  <div v-if="preset === 'table'" class="w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] p-6 space-y-4 animate-pulse">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
      <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/4" />
      <div class="h-8 bg-slate-200 dark:bg-slate-800 rounded-lg w-28" />
    </div>
    <div v-for="n in rows" :key="n" class="flex items-center gap-4 py-2">
      <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/6" />
      <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-2/6" />
      <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/6" />
      <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/6 ml-auto" />
    </div>
  </div>

  <!-- Card Skeleton Preset -->
  <div v-else-if="preset === 'card'" class="w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] p-6 space-y-4 animate-pulse">
    <div class="h-40 bg-slate-200 dark:bg-slate-800 rounded-xl w-full" />
    <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-3/4" />
    <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-1/2" />
    <div class="flex items-center justify-between pt-2">
      <div class="h-6 bg-slate-200 dark:bg-slate-800 rounded-full w-20" />
      <div class="h-8 bg-slate-200 dark:bg-slate-800 rounded-lg w-24" />
    </div>
  </div>

  <!-- Form Skeleton Preset -->
  <div v-else-if="preset === 'form'" class="w-full rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] p-6 space-y-5 animate-pulse">
    <div class="h-5 bg-slate-200 dark:bg-slate-800 rounded w-1/3 mb-2" />
    <div class="space-y-2">
      <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-24" />
      <div class="h-10 bg-slate-200 dark:bg-slate-800 rounded-xl w-full" />
    </div>
    <div class="space-y-2">
      <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-24" />
      <div class="h-10 bg-slate-200 dark:bg-slate-800 rounded-xl w-full" />
    </div>
    <div class="space-y-2">
      <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-24" />
      <div class="h-24 bg-slate-200 dark:bg-slate-800 rounded-xl w-full" />
    </div>
    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
      <div class="h-9 bg-slate-200 dark:bg-slate-800 rounded-lg w-20" />
      <div class="h-9 bg-slate-200 dark:bg-slate-800 rounded-lg w-28" />
    </div>
  </div>

  <!-- Base Custom Skeleton -->
  <div
    v-else
    :class="['animate-pulse bg-slate-200/80 dark:bg-slate-800', shapeClasses]"
    :style="{ width, height }"
  />
</template>
