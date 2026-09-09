import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useSettingsStore } from './settings.store'

export const useAppContextStore = defineStore('appContext', () => {
  const settingsStore = useSettingsStore()

  const isOnline = ref(typeof navigator !== 'undefined' ? navigator.onLine : true)
  const networkStatus = ref<'online' | 'offline'>(isOnline.value ? 'online' : 'offline')
  const appVersion = ref('1.0.0')
  const systemVersion = appVersion
  const lastActivity = ref<number>(Date.now())

  // Dynamic from settings store
  const systemName = computed(() => settingsStore.siteName)

  function recordActivity(): void {
    lastActivity.value = Date.now()
  }

  function setNetworkStatus(online: boolean): void {
    isOnline.value = online
    networkStatus.value = online ? 'online' : 'offline'
  }

  if (typeof window !== 'undefined') {
    window.addEventListener('online', () => {
      setNetworkStatus(true)
    })
    window.addEventListener('offline', () => {
      setNetworkStatus(false)
    })
    // Periodic activity tracker
    window.addEventListener('mousemove', () => recordActivity(), { passive: true })
    window.addEventListener('keydown', () => recordActivity(), { passive: true })
  }

  return {
    isOnline,
    networkStatus,
    systemName,
    appVersion,
    systemVersion,
    lastActivity,
    recordActivity,
    setNetworkStatus,
  }
})
