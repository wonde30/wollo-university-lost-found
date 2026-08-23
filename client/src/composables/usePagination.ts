/**
 * Generic pagination composable.
 * Supports any store or local dataset with page navigation and per-page size management.
 */

import { ref, computed } from 'vue'
import type { PaginationMeta } from '@/types/common.types'

export function usePagination(initialPerPage = 15) {
  const currentPage = ref(1)
  const perPage = ref(initialPerPage)
  const total = ref(0)
  const lastPage = ref(1)

  const totalPages = computed(() => lastPage.value || Math.max(1, Math.ceil(total.value / perPage.value)))
  const hasNextPage = computed(() => currentPage.value < totalPages.value)
  const hasPrevPage = computed(() => currentPage.value > 1)

  function setPaginationMeta(meta: Partial<PaginationMeta>): void {
    if (meta.current_page !== undefined) currentPage.value = meta.current_page
    if (meta.last_page !== undefined) lastPage.value = meta.last_page
    if (meta.per_page !== undefined) perPage.value = meta.per_page
    if (meta.total !== undefined) total.value = meta.total
  }

  function goToPage(page: number): number {
    const clamped = Math.max(1, Math.min(page, totalPages.value))
    currentPage.value = clamped
    return clamped
  }

  function nextPage(): number | null {
    if (hasNextPage.value) {
      currentPage.value++
      return currentPage.value
    }
    return null
  }

  function prevPage(): number | null {
    if (hasPrevPage.value) {
      currentPage.value--
      return currentPage.value
    }
    return null
  }

  function setPerPage(count: number): void {
    perPage.value = count
    currentPage.value = 1
  }

  function reset(): void {
    currentPage.value = 1
    total.value = 0
    lastPage.value = 1
  }

  return {
    currentPage,
    perPage,
    total,
    totalPages,
    hasNextPage,
    hasPrevPage,
    setPaginationMeta,
    goToPage,
    nextPage,
    prevPage,
    setPerPage,
    reset,
  }
}
