/**
 * Pinia store for active system announcements and broadcasts.
 * Handles fetching, active state calculation, and per-user local dismissal persistence.
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Announcement } from '@/features/admin/types/admin.types'
import { getActiveAnnouncements } from '@/features/admin/api/admin.api'

const DISMISSED_STORAGE_KEY = 'wu_dismissed_announcements'

function loadDismissedIds(): number[] {
  try {
    const raw = localStorage.getItem(DISMISSED_STORAGE_KEY)
    return raw ? JSON.parse(raw) : []
  } catch {
    return []
  }
}

function saveDismissedIds(ids: number[]): void {
  try {
    localStorage.setItem(DISMISSED_STORAGE_KEY, JSON.stringify(ids))
  } catch {
    // Ignore storage quota errors
  }
}

export const useAnnouncementsStore = defineStore('announcements', () => {
  const announcements = ref<Announcement[]>([])
  const loading = ref(false)
  const dismissedIds = ref<number[]>(loadDismissedIds())
  const lastFetched = ref<Date | null>(null)

  // Visible active announcements that haven't been dismissed by the user
  const activeBanners = computed(() => {
    return announcements.value.filter(a => !dismissedIds.value.includes(a.id))
  })

  const count = computed(() => activeBanners.value.length)

  async function fetchActive(force = false): Promise<void> {
    // Cache for 2 minutes unless forced
    if (!force && lastFetched.value && (Date.now() - lastFetched.value.getTime() < 120_000)) {
      return
    }

    loading.value = true
    try {
      const data = await getActiveAnnouncements()
      announcements.value = data || []
      lastFetched.value = new Date()
    } catch {
      // Soft-fail on public announcement fetch
      announcements.value = []
    } finally {
      loading.value = false
    }
  }

  function dismiss(id: number): void {
    if (!dismissedIds.value.includes(id)) {
      dismissedIds.value.push(id)
      saveDismissedIds(dismissedIds.value)
    }
  }

  function resetDismissed(): void {
    dismissedIds.value = []
    saveDismissedIds([])
  }

  return {
    announcements,
    loading,
    dismissedIds,
    activeBanners,
    count,
    fetchActive,
    dismiss,
    resetDismissed,
  }
})
