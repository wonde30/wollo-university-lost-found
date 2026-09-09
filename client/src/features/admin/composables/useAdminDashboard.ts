/**
 * Admin dashboard composable with 5-minute caching mechanism.
 * Shared between AdminDashboard and ReportsPage views.
 */

import { storeToRefs } from 'pinia'
import { useAdminStore } from '../stores/admin.store'
export type { AdminStats } from '../stores/admin.store'

export function useAdminDashboard() {
  const adminStore = useAdminStore()
  const { stats, statistics, sparklines, analytics, currentPeriod, recentActivity, loading, error, statisticsLoaded } = storeToRefs(adminStore)

  async function fetchStats(options: boolean | { force?: boolean; period?: string; date_from?: string; date_to?: string } = false): Promise<void> {
    return adminStore.fetchStats(options)
  }

  return {
    stats,
    statistics,
    sparklines,
    analytics,
    currentPeriod,
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
