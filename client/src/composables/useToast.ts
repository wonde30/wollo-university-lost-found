/**
 * Toast notifications composable.
 * Bridges application views with the centralized UI store toast manager.
 */

import { computed } from 'vue'
import { useUiStore, type Toast } from '@/stores/ui.store'

export function useToast() {
  const uiStore = useUiStore()

  const toasts = computed<Toast[]>(() => uiStore.activeToasts)

  function success(message: string, title?: string): string {
    return uiStore.showToast({
      type: 'success',
      message,
      title,
      duration: 4000,
    })
  }

  function error(message: string, title?: string): string {
    return uiStore.showToast({
      type: 'error',
      message,
      title,
      duration: 5000,
    })
  }

  function warning(message: string, title?: string): string {
    return uiStore.showToast({
      type: 'warning',
      message,
      title,
      duration: 4500,
    })
  }

  function info(message: string, title?: string): string {
    return uiStore.showToast({
      type: 'info',
      message,
      title,
      duration: 4000,
    })
  }

  function dismiss(id: string): void {
    uiStore.dismissToast(id)
  }

  return {
    toasts,
    success,
    error,
    warning,
    info,
    dismiss,
    show: uiStore.showToast,
  }
}
