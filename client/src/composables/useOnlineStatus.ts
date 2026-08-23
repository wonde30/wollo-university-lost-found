/**
 * Online/offline status composable.
 */

import { storeToRefs } from 'pinia'
import { useAppContextStore } from '@/stores/app-context.store'

export function useOnlineStatus() {
  const store = useAppContextStore()
  const { isOnline, networkStatus } = storeToRefs(store)

  return {
    isOnline,
    networkStatus,
  }
}
