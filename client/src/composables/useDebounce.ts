/**
 * Debounce composable and utility functions.
 * Useful for debouncing search input events (300ms) and reactive search queries.
 */

import { ref, watch, type Ref } from 'vue'

export function debounce<T extends (...args: any[]) => any>(
  fn: T,
  delay = 300
): (...args: Parameters<T>) => void {
  let timer: ReturnType<typeof setTimeout> | null = null

  return function (this: any, ...args: Parameters<T>) {
    if (timer !== null) {
      clearTimeout(timer)
    }
    timer = setTimeout(() => {
      fn.apply(this, args)
      timer = null
    }, delay)
  }
}

export function debouncedRef<T>(source: Ref<T>, delay = 300): Ref<T> {
  const debounced = ref(source.value) as Ref<T>
  let timer: ReturnType<typeof setTimeout> | null = null

  watch(source, (newVal) => {
    if (timer !== null) {
      clearTimeout(timer)
    }
    timer = setTimeout(() => {
      debounced.value = newVal
      timer = null
    }, delay)
  })

  return debounced
}

export function useDebounce() {
  return {
    debounce,
    debouncedRef,
  }
}
