/**
 * Thin composable wrapper around useCustodyStore (Pinia).
 *
 * State is shared via the Pinia store — all components (ManageCustodyPage,
 * CustodyTransferModal, etc.) share the same events & storage location list
 * and never fire duplicate concurrent fetches.
 *
 * This wrapper maps store property names to the legacy names expected by
 * consumers so they don't need to change.
 */

import { storeToRefs } from 'pinia'
import { useCustodyStore } from '../stores/custody.store'

export function useCustody() {
  const store = useCustodyStore()

  const {
    // Events
    events,
    eventsLoading,
    eventsPagination,
    // Storage locations
    storageLocations,
    storageLocationsLoading,
    storageLocationsInitialized,
  } = storeToRefs(store)

  // Map store names → legacy names expected by consumers
  const loading = eventsLoading          // CustodyTransferModal uses "loading"
  const pagination = eventsPagination    // ManageCustodyPage uses "pagination"

  return {
    // State — legacy names
    events,
    loading,
    pagination,
    storageLocations,
    storageLocationsLoading,
    storageLocationsInitialized,

    // Actions — Custody Events
    fetchEvents:   store.fetchEvents,
    loadEvents:    store.fetchEvents,   // alias used by ManageCustodyPage
    createEvent:   store.createEvent,
    moveItem:      store.moveItem,      // used by CustodyTransferModal

    // Actions — Storage Locations
    fetchStorageLocations:   store.fetchStorageLocations,
    loadStorageLocations:    store.fetchStorageLocations,
    createStorageLocation:   store.createStorageLocation,
    updateStorageLocation:   store.updateStorageLocation,
    deleteStorageLocation:   store.deleteStorageLocation,
  }
}
