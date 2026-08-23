/**
 * Composable for public reference data (categories, locations).
 * Delegates entirely to useReferencesStore which caches data for the session.
 * Never fetches on every mount — only when the store has no data yet.
 */

import { storeToRefs } from 'pinia'
import { useReferencesStore } from '../stores/references.store'

export function usePublicLookups() {
  const referencesStore = useReferencesStore()
  const { categories, locations, loading } = storeToRefs(referencesStore)

  /**
   * Ensure categories and locations are loaded.
   * The store guards against duplicate fetches with its initialized flag.
   */
  async function fetchAll(): Promise<void> {
    await referencesStore.fetchReferences()
  }

  async function fetchCategories(): Promise<void> {
    await referencesStore.fetchReferences()
  }

  async function fetchLocations(): Promise<void> {
    await referencesStore.fetchReferences()
  }

  return {
    categories,
    locations,
    loading,
    fetchAll,
    fetchCategories,
    fetchLocations,
  }
}
