/**
 * Custody store using Pinia.
 * Single source of truth for storage locations — prevents duplicate fetches
 * from StorageLocationSelect and ManageCustodyPage.
 */

import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { StorageLocation } from '@/types/common.types'
import type {
  CustodyEvent,
  CustodyListParams,
  StoreCustodyEventData,
  MoveItemCustodyData,
} from '../types/custody.types'
import type { PaginatedResponse, PaginationParams } from '@/lib/api/pagination'
import * as custodyApi from '../api/custody.api'

export const useCustodyStore = defineStore('custody', () => {
  // ==========================================
  // Storage Locations — shared, fetched once
  // ==========================================

  const storageLocations = ref<StorageLocation[]>([])
  const storageLocationsInitialized = ref(false)
  const storageLocationsLoading = ref(false)

  async function fetchStorageLocations(force = false): Promise<void> {
    if (!force && storageLocationsInitialized.value) return
    if (storageLocationsLoading.value) return

    storageLocationsLoading.value = true
    try {
      storageLocations.value = await custodyApi.getStorageLocations()
      storageLocationsInitialized.value = true
    } finally {
      storageLocationsLoading.value = false
    }
  }

  async function createStorageLocation(data: any): Promise<StorageLocation> {
    const location = await custodyApi.createStorageLocation(data)
    storageLocations.value.push(location)
    return location
  }

  async function updateStorageLocation(id: number, data: any): Promise<StorageLocation> {
    const location = await custodyApi.updateStorageLocation(id, data)
    const index = storageLocations.value.findIndex(l => l.id === id)
    if (index !== -1) storageLocations.value[index] = location
    return location
  }

  async function deleteStorageLocation(id: number): Promise<void> {
    await custodyApi.deleteStorageLocation(id)
    storageLocations.value = storageLocations.value.filter(l => l.id !== id)
  }

  // ==========================================
  // Custody Events — paginated, page-scoped
  // ==========================================

  const events = ref<CustodyEvent[]>([])
  const eventsPagination = ref<PaginatedResponse<CustodyEvent>['meta'] | null>(null)
  const eventsLoading = ref(false)

  async function fetchEvents(
    filters?: CustodyListParams,
    paginationParams?: PaginationParams
  ): Promise<void> {
    if (events.value.length === 0) {
      eventsLoading.value = true
    }
    try {
      const response = await custodyApi.getCustodyEvents(filters, paginationParams)
      events.value = response.data
      eventsPagination.value = response.meta
    } finally {
      eventsLoading.value = false
    }
  }

  async function createEvent(data: StoreCustodyEventData): Promise<CustodyEvent> {
    const event = await custodyApi.createCustodyEvent(data)
    events.value.unshift(event)
    return event
  }

  async function moveItem(itemId: number, data: MoveItemCustodyData): Promise<CustodyEvent> {
    const event = await custodyApi.moveItemCustody(itemId, data)
    events.value.unshift(event)
    return event
  }

  return {
    // Storage locations
    storageLocations,
    storageLocationsInitialized,
    storageLocationsLoading,
    fetchStorageLocations,
    createStorageLocation,
    updateStorageLocation,
    deleteStorageLocation,

    // Custody events
    events,
    eventsPagination,
    eventsLoading,
    fetchEvents,
    createEvent,
    moveItem,
  }
})
