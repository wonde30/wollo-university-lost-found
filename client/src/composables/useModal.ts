/**
 * Modal visibility and focus management composable.
 */

import { ref } from 'vue'

export function useModal(initialState = false) {
  const isOpen = ref(initialState)

  function open(): void {
    isOpen.value = true
  }

  function close(): void {
    isOpen.value = false
  }

  function toggle(): void {
    isOpen.value = !isOpen.value
  }

  return {
    isOpen,
    open,
    close,
    toggle,
  }
}
