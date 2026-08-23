/**
 * Admin dashboard composable with 5-minute caching mechanism.
 * Shared between AdminDashboard and ReportsPage views.
 */

import { storeToRefs } from 'pinia'
import { useAdminStore } from '../stores/admin.store'
export type { AdminStats } from '../stores/admin.store'

export function useAdminDashboard() {
  const adminStore = useAdminStore()
  const { stats, statistics, recentActivity, loading, error, statisticsLoaded } = storeToRefs(adminStore)

  async function fetchStats(force = false): Promise<void> {
    return adminStore.fetchStats(force)
  }

  return {
    stats,
    statistics,
    recentActivity,
    loading,
    error,
    statisticsLoaded,
    fetchStats,
    refresh: () => adminStore.fetchStats(true),
  }
}

// Keep alias for useAdminStats
export const useAdminStats = useAdminDashboard
