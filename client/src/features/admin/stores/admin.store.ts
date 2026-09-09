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
  pending_matches?: number
  expiring_items?: number
  unconfirmed_returns?: number
  found_items: number
  found_unclaimed?: number
  lost_items: number
  active_lost?: number
  returned_items: number
  claimed_items?: number
  in_storage: number
  recovery_rate_percentage: number
  avg_resolution_days?: number | null
  top_3_categories?: { id: number; name: string; total: number }[]
  search_fail_rate_percentage?: number
}

const STATS_CACHE_TTL_MS = 5 * 60 * 1000 // 5 minutes

export const useAdminStore = defineStore('admin', () => {
  // Statistics State
  const stats = ref<AdminStats | null>(null)
  const statistics = ref<DashboardStatistics | null>(null)
  const sparklines = ref<DashboardStatistics['sparklines'] | null>(null)
  const analytics = ref<DashboardStatistics['analytics'] | null>(null)
  const recentActivity = ref<DashboardStatistics['recent_activity'] | null>(null)
  const currentPeriod = ref<string>('90d')
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

  let _activeRequestId = 0
  const _statsCache = new Map<string, { data: DashboardStatistics; timestamp: number }>()

  function _applyStatsData(data: DashboardStatistics): void {
    statistics.value = data
    sparklines.value = data.sparklines ?? null
    analytics.value = data.analytics ?? null
    stats.value = {
      total_items:               data.summary?.total_items               ?? 0,
      lost_items:                data.summary?.lost_items                ?? 0,
      active_lost:               data.summary?.active_lost               ?? data.summary?.lost_items ?? 0,
      found_items:               data.summary?.found_items               ?? 0,
      found_unclaimed:           data.summary?.found_unclaimed           ?? 0,
      returned_items:            data.summary?.returned_items            ?? 0,
      claimed_items:             data.summary?.claimed_items             ?? 0,
      in_storage:                data.summary?.in_storage                ?? 0,
      pending_claims:            data.summary?.pending_claims            ?? 0,
      pending_matches:           data.summary?.pending_matches           ?? 0,
      expiring_items:            data.summary?.expiring_items            ?? 0,
      unconfirmed_returns:       data.summary?.unconfirmed_returns       ?? 0,
      total_claims:              (data.summary?.pending_claims ?? 0) +
                                 (data.recent_activity?.recent_claims?.length ?? 0),
      total_returns:             data.summary?.returned_items            ?? 0,
      total_users:               data.summary?.total_users               ?? 0,
      recovery_rate_percentage:  data.summary?.recovery_rate_percentage  ?? 0,
      avg_resolution_days:       data.summary?.avg_resolution_days       !== undefined ? data.summary.avg_resolution_days : null,
      top_3_categories:          data.summary?.top_3_categories          ?? [],
      search_fail_rate_percentage: data.summary?.search_fail_rate_percentage ?? 0,
    }
    recentActivity.value = data.recent_activity
    initialized.value = true
    statisticsLoaded.value = true
    statisticsLoadedAt.value = Date.now()
  }

  async function fetchStats(options: boolean | { force?: boolean; period?: string; date_from?: string; date_to?: string } = false): Promise<void> {
    const opts = typeof options === 'boolean' ? { force: options } : options
    const force = opts.force ?? false
    const period = opts.period ?? currentPeriod.value
    const dateFrom = opts.date_from
    const dateTo = opts.date_to
    const cacheKey = `${period}:${dateFrom ?? ''}:${dateTo ?? ''}`

    if (!force && _statsCache.has(cacheKey)) {
      const cached = _statsCache.get(cacheKey)!
      if (Date.now() - cached.timestamp < STATS_CACHE_TTL_MS) {
        currentPeriod.value = period
        _applyStatsData(cached.data)
        return
      }
    }

    const currentRequestId = ++_activeRequestId
    loading.value = true
    error.value = null
    currentPeriod.value = period

    try {
      const data = await adminApi.getDashboardStatistics({
        period,
        date_from: dateFrom,
        date_to: dateTo,
        force,
      })

      if (currentRequestId === _activeRequestId) {
        _statsCache.set(cacheKey, { data, timestamp: Date.now() })
        _applyStatsData(data)
      }
    } catch (err: any) {
      if (currentRequestId === _activeRequestId) {
        error.value = err.message ?? 'Failed to load statistics'
      }
    } finally {
      if (currentRequestId === _activeRequestId) {
        loading.value = false
      }
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
    // Stats & Analytics
    stats,
    statistics,
    sparklines,
    analytics,
    currentPeriod,
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
