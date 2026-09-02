<script setup lang="ts">
import { computed } from 'vue'
import { t } from '@/i18n'
import AppButton from './AppButton.vue'

interface Props {
  title?: string
  description?: string
  retryText?: string
  fullPage?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  title: undefined,
  description: undefined,
  retryText: undefined,
  fullPage: false,
})

const resolvedTitle = computed(() => props.title || t('common.errorOccurred'))
const resolvedDescription = computed(() => props.description || t('errors.500.message'))
const resolvedRetryText = computed(() => props.retryText || t('errors.500.tryAgain'))

defineEmits<{
  (e: 'retry'): void
}>()
</script>

<template>
  <div
    :class="[
      'flex flex-col items-center justify-center text-center p-8 rounded-2xl select-none',
      fullPage ? 'min-h-[60vh]' : 'border border-rose-100 dark:border-rose-950/40 bg-rose-50/40 dark:bg-rose-950/20 my-4',
    ]"
  >
    <div class="h-12 w-12 rounded-2xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center mb-3 shadow-xs">
      <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <circle cx="12" cy="12" r="10" stroke-width="2" />
        <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
        <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
      </svg>
    </div>

    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-1">{{ resolvedTitle }}</h3>
    <p class="text-xs text-slate-600 dark:text-slate-400 max-w-sm mb-4 leading-relaxed">{{ resolvedDescription }}</p>

    <div class="flex items-center gap-3">
      <AppButton
        variant="primary"
        size="sm"
        @click="$emit('retry')"
      >
        <template #icon-left>
          <svg class="h-3.5 w-3.5 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </template>
        {{ resolvedRetryText }}
      </AppButton>
      <slot name="extra-actions" />
    </div>
  </div>
</template>
