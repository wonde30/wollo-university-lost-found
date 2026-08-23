/**
 * Thin composable wrapper around useReturnsStore (Pinia).
 *
 * State is shared via the store — all components (ReturnForm, ProcessReturnPage,
 * etc.) share the same returns list and never fire duplicate concurrent fetches.
 * The loading spinner only appears on the very first visit to a page.
 */

import { storeToRefs } from 'pinia'
import { useReturnsStore } from '../stores/returns.store'

export function useReturns() {
  const store = useReturnsStore()

  const { returns, currentReturn, loading, error, pagination } = storeToRefs(store)

  return {
    // Reactive state
    returns,
    currentReturn,
    loading,
    error,
    pagination,

    // Actions (stable references — no need for storeToRefs on functions)
    fetchReturns:      store.fetchReturns,
    fetchReturn:       store.fetchReturn,
    createReturn:      store.createReturn,
    /** Alias kept for backward compatibility with components that call processReturn(). */
    processReturn:     store.processReturn,
    clearCurrentReturn: store.clearCurrentReturn,
  }
}
