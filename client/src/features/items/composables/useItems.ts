/**
 * Items composable wrapping the Pinia items store.
 * Provides unified, reactive item operations, debounced filtering, and computed state.
 */

import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useItemsStore } from '../stores/items.store'
import type { ItemListParams } from '../types/item.types'
import { debounce } from '@/composables/useDebounce'

export function useItems() {
  const store = useItemsStore()

  const {
    items,
    currentItem,
    filters,
    loaded,
    pagination,
    loading,
    fetching,
    creating,
    updating,
    deleting,
  } = storeToRefs(store)

  const searchQuery = ref('')

  // Computed state indicators
  const hasItems = computed(() => items.value.length > 0)
  const isEmpty = computed(() => !loading.value && items.value.length === 0)
  const isLoading = computed(() => loading.value || fetching.value)

  // Debounced search handler (300ms)
  const debouncedSearch = debounce((query: string) => {
    store.fetchItems({ ...filters.value, search: query || undefined, page: 1 })
  }, 300)

  watch(searchQuery, (newQuery) => {
    debouncedSearch(newQuery)
  })

  function resetFilters(): void {
    searchQuery.value = ''
    store.resetFilters()
    store.fetchItems({ page: 1 })
  }

  async function fetchItemsWithFilters(params: ItemListParams = {}): Promise<void> {
    return store.fetchItems(params)
  }

  return {
    // Reactive Store State
    items,
    currentItem,
    filters,
    loaded,
    pagination,
    loading,
    fetching,
    creating,
    updating,
    deleting,

    // Computed
    hasItems,
    isEmpty,
    isLoading,

    // Search & Filter
    searchQuery,
    resetFilters,

    // Actions
    fetchItems: fetchItemsWithFilters,
    loadItems: fetchItemsWithFilters, // Alias
    fetchItem: store.fetchItemDetail,
    loadItem: store.fetchItemDetail, // Alias
    fetchItemDetail: store.fetchItemDetail,
    createLostItem: store.createLostItem,
    createFoundItem: store.createFoundItem,
    updateItem: store.updateItem,
    updateItemStatus: store.updateItemStatus,
    updateStatus: store.updateItemStatus, // Alias
    deleteItem: store.deleteItem,
    addItemPhotos: store.uploadPhoto,
    addPhotos: store.uploadPhoto, // Alias
    deletePhoto: store.deletePhoto,
    deleteItemPhoto: store.deletePhoto, // Alias
    clearCurrentItem: store.clearCurrentItem,
  }
}
