<script setup lang="ts">
import { computed } from 'vue'
import { t } from '@/i18n'

interface Props {
  title?: string
  description?: string
  variant?: 'no-data' | 'no-results' | 'no-access'
}

const props = withDefaults(defineProps<Props>(), {
  title: undefined,
  description: undefined,
  variant: 'no-data',
})

const resolvedTitle = computed(() => {
  if (props.title) return props.title
  if (props.variant === 'no-results') return t('common.noDataMatching')
  return t('common.noData')
})

const resolvedDescription = computed(() => {
  if (props.description) return props.description
  if (props.variant === 'no-results') return t('common.tryAdjusting')
  return t('notifications.emptyDesc')
})
</script>

<template>
  <div class="flex flex-col items-center justify-center p-6 sm:p-8 text-center rounded-xl border border-dashed border-slate-200/90 dark:border-slate-800 bg-white dark:bg-[#111827]">
    <div class="h-11 w-11 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 shadow-2xs flex items-center justify-center text-slate-400 dark:text-slate-500 mb-3 font-bold">
      <slot name="icon">
        <svg v-if="variant === 'no-results'" class="h-5 w-5 text-slate-400 dark:text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="11" cy="11" r="8" stroke-width="2" />
          <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2" />
        </svg>
        <svg v-else-if="variant === 'no-access'" class="h-5 w-5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11V7a5 5 0 0110 0v4" />
        </svg>
        <svg v-else class="h-5 w-5 text-slate-400 dark:text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
      </slot>
    </div>

    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 mb-1">{{ resolvedTitle }}</h3>
    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mb-3.5 leading-relaxed font-medium">{{ resolvedDescription }}</p>

    <div v-if="$slots.action || $slots.default">
      <slot name="action">
        <slot />
      </slot>
    </div>
  </div>
</template>
