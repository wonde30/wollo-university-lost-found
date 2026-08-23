/**
 * Reference data composable for lazy-loading lookup data across forms and filter sidebars.
 */

import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useReferencesStore } from '../stores/references.store'

export function useReferenceData(autoFetch = true) {
  const store = useReferencesStore()
  const {
    categories,
    locations,
    campuses,
    departments,
    categoriesLoaded,
    locationsLoaded,
    loading,
    initialized,
  } = storeToRefs(store)

  onMounted(() => {
    if (autoFetch && !initialized.value) {
      store.fetchReferences()
    }
  })

  return {
    categories,
    locations,
    campuses,
    departments,
    categoriesLoaded,
    locationsLoaded,
    loading,
    initialized,
    fetchCategories: store.fetchCategories,
    fetchLocations: store.fetchLocations,
    fetchAll: store.fetchAll,
    refresh: () => store.fetchReferences(true),
    invalidate: store.invalidate,
  }
}
