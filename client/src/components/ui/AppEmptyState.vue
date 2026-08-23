<script setup lang="ts">
interface Props {
  title?: string
  description?: string
  variant?: 'no-data' | 'no-results' | 'no-access'
}

withDefaults(defineProps<Props>(), {
  title: 'No items found',
  description: 'There is nothing to display at this moment.',
  variant: 'no-data',
})
</script>

<template>
  <div class="flex flex-col items-center justify-center p-8 sm:p-12 text-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50">
    <div class="h-12 w-12 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-center text-slate-400 mb-3.5">
      <slot name="icon">
        <svg v-if="variant === 'no-results'" class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="11" cy="11" r="8" stroke-width="2" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2" />
        </svg>
        <svg v-else-if="variant === 'no-access'" class="h-6 w-6 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11V7a5 5 0 0110 0v4" />
        </svg>
        <svg v-else class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
      </slot>
    </div>

    <h3 class="text-sm font-bold text-slate-900 mb-1">{{ title }}</h3>
    <p class="text-xs text-slate-500 max-w-sm mb-4 leading-relaxed">{{ description }}</p>

    <div v-if="$slots.action || $slots.default">
      <slot name="action">
        <slot />
      </slot>
    </div>
  </div>
</template>
