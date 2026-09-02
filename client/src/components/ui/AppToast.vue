<script setup lang="ts">
import { useUiStore, type Toast } from '@/stores/ui.store'

const uiStore = useUiStore()

function getIconColor(type: Toast['type']) {
  switch (type) {
    case 'success':
      return 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/60 dark:text-emerald-400'
    case 'error':
      return 'text-rose-600 bg-rose-50 dark:bg-rose-950/60 dark:text-rose-400'
    case 'warning':
      return 'text-amber-600 bg-amber-50 dark:bg-amber-950/60 dark:text-amber-400'
    case 'info':
    default:
      return 'text-sky-600 bg-sky-50 dark:bg-sky-950/60 dark:text-sky-400'
  }
}
</script>

<template>
  <Teleport to="body">
    <div
      class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none p-2 sm:p-0"
      aria-live="polite"
    >
      <TransitionGroup name="toast-list">
        <div
          v-for="toast in uiStore.activeToasts"
          :key="toast.id"
          :class="[
            'pointer-events-auto w-full overflow-hidden rounded-xl bg-white dark:bg-[#111827] p-3.5 sm:p-4 shadow-xl border border-slate-200/90 dark:border-slate-800 transition-all flex items-start gap-3 select-none',
          ]"
        >
          <!-- Icon -->
          <div :class="['p-2 rounded-xl shrink-0 flex items-center justify-center', getIconColor(toast.type)]">
            <svg v-if="toast.type === 'success'" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            <svg v-else-if="toast.type === 'error'" class="h-4 w-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <svg v-else-if="toast.type === 'warning'" class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <svg v-else class="h-4 w-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>

          <!-- Message -->
          <div class="flex-1 min-w-0 pt-0.5">
            <h4 v-if="toast.title" class="text-xs font-bold text-slate-900 dark:text-slate-100 mb-0.5">{{ toast.title }}</h4>
            <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed break-words">{{ toast.message }}</p>
          </div>

          <!-- Close Button -->
          <button
            type="button"
            class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0 cursor-pointer dark:text-slate-300"
            @click="uiStore.dismissToast(toast.id)"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>
