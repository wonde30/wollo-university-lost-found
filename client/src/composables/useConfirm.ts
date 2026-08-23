/**
 * Confirmation dialog composable.
 * Returns a Promise<boolean> that resolves true on user confirmation and false on cancellation.
 */

import { useUiStore } from '@/stores/ui.store'

export interface ConfirmOptions {
  confirmText?: string
  cancelText?: string
  variant?: 'danger' | 'primary' | 'warning'
}

export function useConfirm() {
  const uiStore = useUiStore()

  function confirm(
    title: string,
    message: string,
    options: ConfirmOptions = {}
  ): Promise<boolean> {
    return new Promise((resolve) => {
      uiStore.showConfirm({
        title,
        message,
        confirmText: options.confirmText ?? 'Confirm',
        cancelText: options.cancelText ?? 'Cancel',
        variant: options.variant ?? 'primary',
        onConfirm: () => {
          resolve(true)
        },
        onCancel: () => {
          resolve(false)
        },
      })
    })
  }

  return {
    confirm,
  }
}
