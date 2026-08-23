/**
 * Admin store using Pinia.
 * Owns dashboard statistics, audit logs, reports, settings, and high-level administrative state.
 */

import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as adminApi from '../api/admin.api'
import type { DashboardStatistics, AuditLog, Report, SystemSetting } from '../types/admin.types'
import type { PaginationMeta } from '@/types/common.types'

export interface AdminStats {
  total_items: number
  total_claims: number
  total_returns: number
  total_users: number
  pending_claims: number
  found_items: number
  lost_items: number
  returned_items: number
  in_storage: number
  recovery_rate_percentage: number
}

const STATS_CACHE_TTL_MS = 5 * 60 * 1000 // 5 minutes

export const useAdminStore = defineStore('admin', () => {
  // Statistics State
  const stats = ref<AdminStats | null>(null)
  const statistics = ref<DashboardStatistics | null>(null)
  const recentActivity = ref<DashboardStatistics['recent_activity'] | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const initialized = ref(false)
  const statisticsLoaded = ref(false)
  const statisticsLoadedAt = ref<number | null>(null)

  // Audit Logs State
  const auditLogs = ref<AuditLog[]>([])
  const auditLogsPagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, per_page: 20, total: 0 })
  const auditLogsLoading = ref(false)

  // Reports State
  const reports = ref<Report[]>([])
  const reportsPagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
  const reportsLoading = ref(false)

  // Settings State
  const settings = ref<SystemSetting[]>([])
  const settingsLoaded = ref(false)
  const settingsLoading = ref(false)

  let _fetchPromise: Promise<void> | null = null

  // ==========================================
  // Statistics Actions
  // ==========================================

  async function fetchStats(force = false): Promise<void> {
    const isStale = !statisticsLoadedAt.value || (Date.now() - statisticsLoadedAt.value > STATS_CACHE_TTL_MS)
    if (!force && initialized.value && !isStale) return

    if (_fetchPromise) return _fetchPromise

    _fetchPromise = _doFetchStats(force).finally(() => {
      _fetchPromise = null
    })

    return _fetchPromise
  }

  async function _doFetchStats(force: boolean): Promise<void> {
    const isStale = !statisticsLoadedAt.value || (Date.now() - statisticsLoadedAt.value > STATS_CACHE_TTL_MS)
    if (!force && initialized.value && !isStale) return

    if (!stats.value) {
      loading.value = true
    }
    error.value = null
    try {
      const data = await adminApi.getDashboardStatistics()
      statistics.value = data
      stats.value = {
        total_items:               data.summary?.total_items               ?? 0,
        lost_items:                data.summary?.lost_items                ?? 0,
        found_items:               data.summary?.found_items               ?? 0,
        returned_items:            data.summary?.returned_items            ?? 0,
        in_storage:                data.summary?.in_storage                ?? 0,
        pending_claims:            data.summary?.pending_claims            ?? 0,
        total_claims:              (data.summary?.pending_claims ?? 0) +
                                   (data.recent_activity?.recent_claims?.length ?? 0),
        total_returns:             data.summary?.returned_items            ?? 0,
        total_users:               data.summary?.total_users               ?? 0,
        recovery_rate_percentage:  data.summary?.recovery_rate_percentage  ?? 0,
      }
      recentActivity.value = data.recent_activity
      initialized.value = true
      statisticsLoaded.value = true
      statisticsLoadedAt.value = Date.now()
    } catch (err: any) {
      error.value = err.message ?? 'Failed to load statistics'
    } finally {
      loading.value = false
    }
  }

  // ==========================================
  // Audit Logs Actions
  // ==========================================

  async function fetchAuditLogs(params: { page?: number; per_page?: number; search?: string } = {}): Promise<void> {
    if (auditLogs.value.length === 0) {
      auditLogsLoading.value = true
    }
    try {
      const res = await adminApi.getAuditLogs({
        page: params.page || 1,
        per_page: params.per_page || 20,
      })
      auditLogs.value = res.data
      auditLogsPagination.value = res.meta
    } finally {
      auditLogsLoading.value = false
    }
  }

  // ==========================================
  // Reports Actions
  // ==========================================

  async function fetchReports(params: { page?: number; per_page?: number } = {}): Promise<void> {
    if (reports.value.length === 0) {
      reportsLoading.value = true
    }
    try {
      const res = await adminApi.getReports(params)
      reports.value = res.data
      reportsPagination.value = res.meta
    } finally {
      reportsLoading.value = false
    }
  }

  async function generateReport(data: any): Promise<Report> {
    const newReport = await adminApi.generateReport(data)
    reports.value.unshift(newReport)
    return newReport
  }

  // ==========================================
  // Settings Actions
  // ==========================================

  async function fetchSettings(force = false): Promise<SystemSetting[]> {
    if (!force && settingsLoaded.value && settings.value.length > 0) {
      return settings.value
    }
    settingsLoading.value = true
    try {
      const res = await adminApi.getSystemSettings()
      settings.value = res
      settingsLoaded.value = true
      return res
    } finally {
      settingsLoading.value = false
    }
  }

  async function updateSetting(key: string, data: any): Promise<SystemSetting> {
    const updated = await adminApi.updateSystemSetting(key, data)
    const index = settings.value.findIndex(s => s.key === key)
    if (index !== -1) settings.value[index] = updated
    return updated
  }

  return {
    // Stats
    stats,
    statistics,
    recentActivity,
    loading,
    error,
    initialized,
    statisticsLoaded,
    statisticsLoadedAt,
    fetchStats,
    fetchStatistics: fetchStats,

    // Audit logs
    auditLogs,
    auditLogsPagination,
    auditLogsLoading,
    fetchAuditLogs,

    // Reports
    reports,
    reportsPagination,
    reportsLoading,
    fetchReports,
    generateReport,

    // Settings
    settings,
    settingsLoaded,
    settingsLoading,
    fetchSettings,
    updateSetting,
  }
})
