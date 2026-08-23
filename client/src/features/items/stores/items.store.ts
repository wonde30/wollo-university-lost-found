import { defineStore } from 'pinia'
import { ref } from 'vue'
import type {
  Item,
  ItemDetail,
  ItemListParams,
  StoreLostItemData,
  StoreFoundItemData,
  UpdateItemData,
  UpdateItemStatusData,
} from '../types/item.types'
import type { PaginationMeta } from '@/types/common.types'
import * as itemsApi from '../api/items.api'

export const useItemsStore = defineStore('items', () => {
  // State
  const items = ref<Item[]>([])
  const currentItem = ref<ItemDetail | null>(null)
  const filters = ref<ItemListParams>({})
  const loaded = ref(false)
  const pagination = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  // Loading States
  const loading = ref(false)
  const fetching = ref(false)
  const creating = ref(false)
  const updating = ref(false)
  const deleting = ref(false)

  // Inflight request deduplication
  let _fetchPromise: Promise<void> | null = null

  function setFilters(newFilters: Partial<ItemListParams>): void {
    filters.value = { ...filters.value, ...newFilters }
  }

  function resetFilters(): void {
    filters.value = {}
  }

  async function fetchItems(params: ItemListParams = {}, force = false): Promise<void> {
    if (!force && loaded.value && items.value.length > 0 && Object.keys(params).length === 0) {
      return
    }

    if (_fetchPromise) return _fetchPromise

    _fetchPromise = (async () => {
      fetching.value = true
      if (items.value.length === 0) {
        loading.value = true
      }
      try {
        const queryParams = { ...filters.value, ...params }
        const res = await itemsApi.getItems(queryParams)
        items.value = res.data
        pagination.value = res.meta
        loaded.value = true
      } finally {
        fetching.value = false
        loading.value = false
        _fetchPromise = null
      }
    })()

    return _fetchPromise
  }

  async function fetchItemDetail(id: number): Promise<ItemDetail> {
    fetching.value = true
    loading.value = true
    try {
      const res = await itemsApi.getItem(id)
      currentItem.value = res as ItemDetail
      return res as ItemDetail
    } finally {
      fetching.value = false
      loading.value = false
    }
  }

  const fetchItem = fetchItemDetail // Alias

  async function createLostItem(data: StoreLostItemData): Promise<Item> {
    creating.value = true
    loading.value = true
    try {
      const item = await itemsApi.createLostItem(data)
      items.value.unshift(item)
      return item
    } finally {
      creating.value = false
      loading.value = false
    }
  }

  async function createFoundItem(data: StoreFoundItemData): Promise<Item> {
    creating.value = true
    loading.value = true
    try {
      const item = await itemsApi.createFoundItem(data)
      items.value.unshift(item)
      return item
    } finally {
      creating.value = false
      loading.value = false
    }
  }

  async function updateItem(id: number, data: UpdateItemData): Promise<Item> {
    updating.value = true
    loading.value = true
    try {
      const updatedItem = await itemsApi.updateItem(id, data)

      const index = items.value.findIndex(i => i.id === id)
      if (index !== -1) {
        items.value[index] = updatedItem
      }

      if (currentItem.value?.id === id) {
        currentItem.value = { ...currentItem.value, ...updatedItem } as ItemDetail
      }

      return updatedItem
    } finally {
      updating.value = false
      loading.value = false
    }
  }

  async function updateItemStatus(id: number, data: UpdateItemStatusData): Promise<void> {
    updating.value = true
    loading.value = true
    try {
      const updatedItem = await itemsApi.updateItemStatus(id, data)

      const index = items.value.findIndex(i => i.id === id)
      if (index !== -1) {
        items.value[index] = updatedItem
      }

      if (currentItem.value?.id === id) {
        currentItem.value = { ...currentItem.value, ...updatedItem } as ItemDetail
      }
    } finally {
      updating.value = false
      loading.value = false
    }
  }

  const updateStatus = updateItemStatus // Alias

  async function deleteItem(id: number): Promise<void> {
    deleting.value = true
    loading.value = true
    try {
      await itemsApi.deleteItem(id)
      items.value = items.value.filter(i => i.id !== id)
      if (currentItem.value?.id === id) {
        currentItem.value = null
      }
    } finally {
      deleting.value = false
      loading.value = false
    }
  }

  async function uploadPhoto(id: number, photos: File[]): Promise<void> {
    updating.value = true
    loading.value = true
    try {
      await itemsApi.addItemPhotos(id, photos)
      if (currentItem.value?.id === id) {
        await fetchItemDetail(id)
      }
    } finally {
      updating.value = false
      loading.value = false
    }
  }

  const addItemPhotos = uploadPhoto // Alias

  async function deletePhoto(itemId: number, photoId: number): Promise<void> {
    updating.value = true
    loading.value = true
    try {
      await itemsApi.deleteItemPhoto(itemId, photoId)
      if (currentItem.value?.id === itemId && currentItem.value.photos) {
        currentItem.value = {
          ...currentItem.value,
          photos: currentItem.value.photos.filter(p => p.id !== photoId),
        }
      }
    } finally {
      updating.value = false
      loading.value = false
    }
  }

  const deleteItemPhoto = deletePhoto // Alias

  function clearCurrentItem(): void {
    currentItem.value = null
  }

  return {
    // State
    items,
    currentItem,
    filters,
    loaded,
    pagination,

    // Loading states
    loading,
    fetching,
    creating,
    updating,
    deleting,

    // Actions
    setFilters,
    resetFilters,
    fetchItems,
    fetchItem,
    fetchItemDetail,
    createLostItem,
    createFoundItem,
    updateItem,
    updateItemStatus,
    updateStatus,
    deleteItem,
    uploadPhoto,
    addItemPhotos,
    deletePhoto,
    deleteItemPhoto,
    clearCurrentItem,
  }
})
