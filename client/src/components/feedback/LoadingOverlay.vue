<script setup lang="ts">
import { useUiStore } from '@/stores/ui.store'
import { t } from '@/i18n'
import AppSpinner from '@/components/ui/AppSpinner.vue'

const uiStore = useUiStore()
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="uiStore.globalLoading"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs"
      >
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-2xl flex flex-col items-center gap-3 min-w-[200px]">
          <AppSpinner size="lg" color="primary" />
          <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">
            {{ uiStore.loadingMessage || t('common.loading') }}
          </p>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
