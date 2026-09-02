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
    organizationalUnits,
    organizationalUnitTypes,
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
    organizationalUnits,
    organizationalUnitTypes,
    categoriesLoaded,
    locationsLoaded,
    loading,
    initialized,
    fetchCampuses: store.fetchCampuses,
    fetchOrganizationalUnits: store.fetchOrganizationalUnits,
    fetchOrganizationalUnitTypes: store.fetchOrganizationalUnitTypes,
    fetchCategories: store.fetchCategories,
    fetchLocations: store.fetchLocations,
    fetchAll: store.fetchAll,
    refresh: () => store.fetchReferences(true),
    invalidate: store.invalidate,
  }
}
