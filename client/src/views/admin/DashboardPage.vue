<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useAdminDashboard } from '@/features/admin/composables/useAdminDashboard'
import AdminStatsCard from '@/features/admin/components/AdminStatsCard.vue'
import BaseTrendChart from '@/components/charts/BaseTrendChart.vue'
import BaseBarChart from '@/components/charts/BaseBarChart.vue'
import BaseDonutChart from '@/components/charts/BaseDonutChart.vue'
import DateRangePresetFilter from '@/components/forms/DateRangePresetFilter.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import ClaimStatusBadge from '@/features/claims/components/ClaimStatusBadge.vue'
import { formatDate } from '@/utils/date'
import { t } from '@/i18n'
import {
  Users,
  Shield,
  Building2,
  Tag,
  History,
  Package,
  AlertCircle,
  CheckCircle2,
  Clock,
  FileText,
  RefreshCw,
  ShieldCheck,
  CheckCheck,
  Sparkles,
  AlertTriangle,
  Handshake,
  ArrowRight,
  ShieldAlert,
  Activity,
  TrendingUp,
  Calendar,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const { stats, sparklines, analytics, currentPeriod, recentActivity, loading, error, fetchStats } = useAdminDashboard()

const selectedPeriod = ref<string>('90d')

const activePeriodLabel = computed(() => {
  switch (selectedPeriod.value) {
    case 'today': return 'Today'
    case '7d': return '7 Days'
    case '30d': return '30 Days'
    case '90d': return '90 Days'
    case '12m': return '12 Months'
    case 'all': return 'All Time'
    default: return '90 Days'
  }
})

async function handlePeriodChange(period: string): Promise<void> {
  selectedPeriod.value = period
  await fetchStats({ period, force: true })
}

async function handleRefresh(): Promise<void> {
  await fetchStats({ period: selectedPeriod.value, force: true })
}

onMounted(() => {
  fetchStats({ period: selectedPeriod.value, force: false })
})

watch(currentPeriod, (newVal) => {
  if (newVal && newVal !== selectedPeriod.value) {
    selectedPeriod.value = newVal
  }
})

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return t('greetings.morning')
  if (hour < 18) return t('greetings.afternoon')
  return t('greetings.evening')
})

// Trend Series for Main Intake vs Returns Area Chart
const trendChartSeries = computed(() => {
  if (!analytics.value?.timeline) return []
  return [
    {
      name: 'Reported Lost',
      key: 'lost',
      data: analytics.value.timeline.lost || [],
      color: 'var(--wu-danger-500, #f43f5e)',
      fillColor: 'var(--wu-danger-500, #f43f5e)',
    },
    {
      name: 'Reported Found',
      key: 'found',
      data: analytics.value.timeline.found || [],
      color: 'var(--wu-info-500, #3b82f6)',
      fillColor: 'var(--wu-info-500, #3b82f6)',
    },
    {
      name: 'Returned to Owner',
      key: 'returned',
      data: analytics.value.timeline.returned || [],
      color: 'var(--wu-primary-500, #107c4f)',
      fillColor: 'var(--wu-primary-500, #107c4f)',
    },
  ]
})

// Category Breakdown for Horizontal Distribution Chart
const categoryChartItems = computed(() => {
  if (!analytics.value?.by_category) return []
  const colors = [
    'var(--wu-danger-500, #f43f5e)',
    'var(--wu-info-500, #3b82f6)',
    'var(--wu-warning-500, #f59e0b)',
    'var(--wu-success-500, #10b981)',
    'var(--wu-primary-500, #107c4f)',
    'var(--wu-gold-400, #d4af37)',
  ]
  return analytics.value.by_category.slice(0, 6).map((cat, idx) => ({
    id: cat.id,
    label: cat.name,
    value: cat.total_items,
    percentage: cat.percentage,
    color: colors[idx % colors.length],
  }))
})

// Campus Breakdown for Comparison Chart
const campusChartItems = computed(() => {
  if (!analytics.value?.by_campus) return []
  return analytics.value.by_campus.map((camp) => ({
    id: camp.id,
    label: camp.name,
    sublabel: camp.code,
    value: camp.total_items,
    secondaryValue: camp.returned_items,
    percentage: camp.recovery_rate,
    color: 'var(--wu-primary-200, #a3d3ba)',
  }))
})

// Lifecycle Status Segments for Donut Chart (Zero frontend arithmetic - pure DB tracing)
const statusDonutSegments = computed(() => {
  const lifecycle = analytics.value?.lifecycle_distribution
  if (lifecycle && lifecycle.length > 0) {
    return lifecycle.map((segment) => ({
      id: segment.id,
      label: segment.label,
      value: segment.count,
      color: segment.color,
    }))
  }
  if (!stats.value) return []
  return [
    {
      id: 'lost',
      label: 'Lost (Active)',
      value: stats.value.active_lost ?? stats.value.lost_items ?? 0,
      color: 'var(--wu-danger-500, #f43f5e)',
    },
    {
      id: 'found_unclaimed',
      label: 'Found (Unclaimed)',
      value: stats.value.found_unclaimed ?? 0,
      color: 'var(--wu-info-500, #3b82f6)',
    },
    {
      id: 'claimed',
      label: 'Claimed & Verifying',
      value: stats.value.claimed_items ?? 0,
      color: 'var(--wu-warning-500, #f59e0b)',
    },
    {
      id: 'returned',
      label: 'Returned to Owner',
      value: stats.value.returned_items ?? 0,
      color: 'var(--wu-success-500, #10b981)',
    },
  ]
})

// Real Attention items derived from live database metrics
interface AttentionItem {
  id: string
  title: string
  count: number
  variant: 'danger' | 'warning' | 'info'
  icon: any
  route: string
  actionLabel: string
}

const attentionItems = computed<AttentionItem[]>(() => {
  if (!stats.value) return []
  const items: AttentionItem[] = []

  if (stats.value.pending_claims > 0 && authStore.can('REVIEW_CLAIMS')) {
    items.push({
      id: 'claims',
      title: t('admin.dashboard.pendingClaims'),
      count: stats.value.pending_claims,
      variant: 'warning',
      icon: Clock,
      route: '/staff/review-claims',
      actionLabel: t('admin.dashboard.reviewAction'),
    })
  }

  if ((stats.value.pending_matches ?? 0) > 0 && authStore.can('MANAGE_ALL_ITEMS')) {
    items.push({
      id: 'matches',
      title: t('admin.dashboard.pendingMatches'),
      count: stats.value.pending_matches ?? 0,
      variant: 'info',
      icon: Sparkles,
      route: '/staff/match-suggestions',
      actionLabel: t('admin.dashboard.reviewAction'),
    })
  }

  if ((stats.value.expiring_items ?? 0) > 0) {
    items.push({
      id: 'expiring',
      title: t('admin.dashboard.expiringSoon'),
      count: stats.value.expiring_items ?? 0,
      variant: 'danger',
      icon: AlertTriangle,
      route: '/browse',
      actionLabel: t('admin.dashboard.inspectAction'),
    })
  }

  if (stats.value.in_storage > 0 && authStore.can('MANAGE_CUSTODY')) {
    items.push({
      id: 'storage',
      title: t('admin.dashboard.foundItems'),
      count: stats.value.in_storage,
      variant: 'info',
      icon: Package,
      route: '/staff/manage-custody',
      actionLabel: t('admin.dashboard.manageAction'),
    })
  }

  if ((stats.value.unconfirmed_returns ?? 0) > 0 && authStore.can('PROCESS_RETURNS')) {
    items.push({
      id: 'returns',
      title: t('admin.dashboard.unconfirmedReturns'),
      count: stats.value.unconfirmed_returns ?? 0,
      variant: 'warning',
      icon: Handshake,
      route: '/staff/process-return',
      actionLabel: t('admin.dashboard.trackAction'),
    })
  }

  return items
})

const governanceLinks = computed(() => {
  const links = []
  if (authStore.can('MANAGE_USERS')) {
    links.push({ label: t('nav.users'), to: '/admin/users', icon: Users, color: 'bg-slate-800 dark:bg-slate-700' })
  }
  if (authStore.can('MANAGE_PERMISSIONS')) {
    links.push({ label: t('nav.roles'), to: '/admin/roles', icon: Shield, color: 'bg-[#0B5D3B] dark:bg-[#153C2D]' })
    links.push({ label: t('nav.permissions'), to: '/admin/permissions', icon: ShieldCheck, color: 'bg-[#0B5D3B] dark:bg-[#153C2D]' })
  }
  if (authStore.can('MANAGE_CAMPUSES')) {
    links.push({ label: t('nav.campuses'), to: '/admin/campuses', icon: Building2, color: 'bg-[#0B5D3B] dark:bg-[#153C2D]' })
  }
  if (authStore.can('MANAGE_CATEGORIES')) {
    links.push({ label: t('nav.categories'), to: '/admin/categories', icon: Tag, color: 'bg-amber-600 dark:bg-amber-700' })
  }
  if (authStore.can('VIEW_AUDIT_LOGS')) {
    links.push({ label: t('nav.auditLogs'), to: '/admin/audit-logs', icon: History, color: 'bg-slate-700 dark:bg-slate-800' })
  }
  return links
})
</script>

<template>
  <div class="space-y-4 sm:space-y-5">
    <!-- 1. Header & Date Range Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3.5 pb-1">
      <div>
        <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#D4AF37]/20 text-[#B7791F] dark:text-[#D4AF37] border border-[#D4AF37]/30 mb-1">
          <ShieldCheck class="h-3.5 w-3.5" />
          {{ t('admin.dashboard.systemAdmin') }}
        </div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
          {{ greeting }}, {{ authStore.user?.full_name || t('admin.roles.admin') }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-xl font-mono">
          {{ t('admin.dashboard.description') }}
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-2 shrink-0">
        <!-- Date Range Presets -->
        <DateRangePresetFilter
          :model-value="selectedPeriod"
          :disabled="loading"
          size="sm"
          @change="handlePeriodChange"
        />

        <!-- Refresh Live Button -->
        <button
          type="button"
          title="Refresh Statistics"
          :disabled="loading"
          class="h-9 w-9 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer disabled:opacity-50"
          @click="handleRefresh"
        >
          <RefreshCw class="h-3.5 w-3.5" :class="loading ? 'animate-spin' : ''" />
        </button>

        <!-- Generate Report Action -->
        <AppButton variant="gold" size="sm" @click="router.push('/admin/reports')">
          <template #icon-left>
            <FileText class="h-3.5 w-3.5 mr-1" />
          </template>
          {{ t('admin.dashboard.generateReport') }}
        </AppButton>
      </div>
    </div>

    <!-- 2. Top 5 Executive KPI Cards with Sparklines (Benchmark Layout) -->
    <section aria-labelledby="kpi-summary-heading" class="space-y-2">
      <div v-if="loading && !stats" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <AppSkeleton v-for="n in 5" :key="n" height="6.2rem" class="rounded-2xl" />
      </div>

      <div v-else-if="stats" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <!-- 1. Total Items -->
        <AdminStatsCard
          :label="t('admin.dashboard.totalItems')"
          :value="stats.total_items"
          :sparkline-data="sparklines?.total_items"
          :icon="Package"
          variant="primary"
          :sublabel="t('admin.dashboard.allRecords')"
          to="/browse"
        />

        <!-- 2. Active Lost Items -->
        <AdminStatsCard
          :label="t('admin.dashboard.lostItems')"
          :value="stats.active_lost ?? stats.lost_items"
          :sparkline-data="sparklines?.lost_items"
          :icon="AlertCircle"
          variant="danger"
          :sublabel="t('admin.dashboard.lostSublabel')"
          to="/browse?type=lost"
        />

        <!-- 3. Found Items -->
        <AdminStatsCard
          :label="t('admin.dashboard.foundItems')"
          :value="stats.found_unclaimed ?? stats.found_items"
          :sparkline-data="sparklines?.found_items"
          :icon="CheckCircle2"
          variant="info"
          :sublabel="t('admin.dashboard.foundSublabel')"
          to="/browse?type=found"
        />

        <!-- 4. Returned to Owner (with Recovery Rate Trend) -->
        <AdminStatsCard
          :label="t('admin.dashboard.returnedItems')"
          :value="stats.returned_items"
          :sparkline-data="sparklines?.returned_items"
          :icon="CheckCheck"
          variant="success"
          :trend="{ value: `${stats.recovery_rate_percentage}%`, direction: 'up', label: 'Recovery Rate' }"
          to="/staff/process-return"
        />

        <!-- 5. Items in Custody / Storage -->
        <AdminStatsCard
          :label="t('staffDashboard.inStorage')"
          :value="stats.in_storage"
          :sparkline-data="sparklines?.in_storage"
          :icon="ShieldCheck"
          variant="warning"
          :sublabel="t('staffDashboard.inStorageSub')"
          to="/staff/manage-custody"
        />
      </div>

      <div v-else-if="error" class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 flex items-center justify-between text-xs text-rose-700 dark:text-rose-300">
        <span>{{ error }}</span>
        <AppButton variant="outline" size="xs" @click="handleRefresh">{{ t('common.retry') || 'Retry' }}</AppButton>
      </div>
    </section>

    <!-- 3. Primary Analytics Row: 2/3 Trend Chart + 1/3 Executive Summary Card (Fixed h-[310px]) -->
    <section aria-labelledby="analytics-main-heading" class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5 items-stretch">
      <!-- Left 2/3: Intake vs Returns Multi-Area Trend Chart (Fixed h-[310px]) -->
      <div class="lg:col-span-2 p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex flex-col justify-between h-[310px] overflow-hidden">
        <!-- Unified Header -->
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800 shrink-0">
          <div class="flex items-center gap-2.5">
            <div class="h-7 w-7 rounded-lg flex items-center justify-center shrink-0 bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97]">
              <Activity class="h-3.5 w-3.5" />
            </div>
            <div>
              <h2 id="analytics-main-heading" class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 leading-tight">
                Inventory Intake & Recovery Trend
              </h2>
              <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Item Activity Over Time</p>
            </div>
          </div>
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/60 shrink-0">
            <Calendar class="h-2.5 w-2.5 text-slate-400" />
            {{ activePeriodLabel }}
          </span>
        </div>

        <div class="flex-1 min-h-0 flex items-center">
          <BaseTrendChart
            :labels="analytics?.timeline?.labels || []"
            :series="trendChartSeries"
            :height="215"
            :loading="loading && !analytics"
            :error="error"
            @retry="handleRefresh"
          />
        </div>
      </div>

      <!-- Right 1/3: Executive Summary Card (Fixed h-[310px]) -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex flex-col justify-between h-[310px] overflow-hidden">
        <!-- Unified Header -->
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800 shrink-0">
          <div class="flex items-center gap-2.5">
            <div class="h-7 w-7 rounded-lg flex items-center justify-center shrink-0 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400">
              <TrendingUp class="h-3.5 w-3.5" />
            </div>
            <div>
              <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 leading-tight">
                Executive Summary
              </h3>
              <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Performance & Resolution</p>
            </div>
          </div>
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/60 shrink-0">
            <Calendar class="h-2.5 w-2.5 text-slate-400" />
            {{ activePeriodLabel }}
          </span>
        </div>

        <div class="space-y-2.5 py-1">
          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500 dark:text-slate-400 font-medium">Campus Recovery Rate</span>
            <span class="font-black text-slate-900 dark:text-white font-mono text-sm text-[#0B5D3B] dark:text-[#75bd97]">
              {{ stats?.recovery_rate_percentage || 0 }}%
            </span>
          </div>

          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500 dark:text-slate-400 font-medium">Avg Resolution Turnaround</span>
            <span class="font-black text-slate-900 dark:text-white font-mono text-xs">
              {{ stats?.avg_resolution_days !== null && stats?.avg_resolution_days !== undefined ? `${stats.avg_resolution_days} days` : 'N/A' }}
            </span>
          </div>

          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500 dark:text-slate-400 font-medium">Search Zero-Result Rate</span>
            <span class="font-black text-slate-900 dark:text-white font-mono text-xs" :class="(stats?.search_fail_rate_percentage || 0) > 25 ? 'text-amber-600' : 'text-slate-900 dark:text-white'">
              {{ stats?.search_fail_rate_percentage || 0 }}%
            </span>
          </div>

          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500 dark:text-slate-400 font-medium">Active Registered Users</span>
            <span class="font-black text-slate-900 dark:text-white font-mono text-xs">
              {{ stats?.total_users || 0 }}
            </span>
          </div>

          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-500 dark:text-slate-400 font-medium">Pending Claim Decisions</span>
            <span class="font-black font-mono text-xs" :class="(stats?.pending_claims || 0) > 0 ? 'text-amber-600' : 'text-slate-900 dark:text-white'">
              {{ stats?.pending_claims || 0 }}
            </span>
          </div>
        </div>

        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 shrink-0">
          <AppButton variant="outline" size="xs" class="w-full justify-center" @click="router.push('/admin/reports')">
            <template #icon-left>
              <FileText class="h-3 w-3 mr-1" />
            </template>
            Export Comprehensive Report
          </AppButton>
        </div>
      </div>
    </section>

    <!-- 4. Secondary Analytics Row: Category Distribution + Campus Recovery Comparison -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
      <!-- Category Volume Breakdown -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-3">
        <!-- Unified Header -->
        <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-2.5">
            <div class="h-7 w-7 rounded-lg flex items-center justify-center shrink-0 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400">
              <Tag class="h-3.5 w-3.5" />
            </div>
            <div>
              <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 leading-tight">
                Inventory by Category
              </h3>
              <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Volume & Percentage Share</p>
            </div>
          </div>
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/60 shrink-0">
            <Calendar class="h-2.5 w-2.5 text-slate-400" />
            {{ activePeriodLabel }}
          </span>
        </div>

        <BaseBarChart
          :items="categoryChartItems"
          layout="horizontal"
          :height="190"
          :loading="loading && !analytics"
          :error="error"
          @retry="handleRefresh"
        />
      </div>

      <!-- Campus Recovery Comparison -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-3">
        <!-- Unified Header -->
        <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-2.5">
            <div class="h-7 w-7 rounded-lg flex items-center justify-center shrink-0 bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97]">
              <Building2 class="h-3.5 w-3.5" />
            </div>
            <div>
              <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 leading-tight">
                Campus Recovery Performance
              </h3>
              <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Returned / Total by Location</p>
            </div>
          </div>
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/60 shrink-0">
            <Calendar class="h-2.5 w-2.5 text-slate-400" />
            {{ activePeriodLabel }}
          </span>
        </div>

        <BaseBarChart
          :items="campusChartItems"
          layout="horizontal"
          :show-secondary="true"
          secondary-label="Returned"
          :height="190"
          :loading="loading && !analytics"
          :error="error"
          @retry="handleRefresh"
        />
      </div>
    </section>

    <!-- 5. Operations Row: Status Funnel Ring + Operational Action Queue -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
      <!-- Item Lifecycle Status Donut -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-3">
        <!-- Unified Header -->
        <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-2.5">
            <div class="h-7 w-7 rounded-lg flex items-center justify-center shrink-0 bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97]">
              <Package class="h-3.5 w-3.5" />
            </div>
            <div>
              <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 leading-tight">
                Item Lifecycle Distribution
              </h3>
              <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Active Storage & Handover State</p>
            </div>
          </div>
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/60 shrink-0">
            <Calendar class="h-2.5 w-2.5 text-slate-400" />
            {{ activePeriodLabel }}
          </span>
        </div>

        <BaseDonutChart
          :segments="statusDonutSegments"
          :center-value="stats?.total_items || 0"
          center-label="Total Items"
          :size="155"
          :thickness="18"
          :loading="loading && !stats"
          :error="error"
          @retry="handleRefresh"
        />
      </div>

      <!-- Operational Action Queue (Needs Attention) -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-3 flex flex-col justify-between">
        <div>
          <!-- Unified Header -->
          <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
              <div class="h-7 w-7 rounded-lg flex items-center justify-center shrink-0 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400">
                <ShieldAlert class="h-3.5 w-3.5" />
              </div>
              <div>
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 leading-tight">
                  {{ t('admin.dashboard.needsAttentionTitle') }}
                </h3>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">
                  {{ t('admin.dashboard.needsAttentionSubtitle') }}
                </p>
              </div>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/60 shrink-0">
              <Clock class="h-2.5 w-2.5 text-slate-400" />
              Realtime
            </span>
          </div>

          <div v-if="loading && !stats" class="space-y-2 pt-2">
            <AppSkeleton v-for="n in 3" :key="n" height="3.8rem" class="rounded-xl" />
          </div>

          <div v-else-if="attentionItems.length > 0" class="space-y-2 pt-2">
            <RouterLink
              v-for="item in attentionItems.slice(0, 3)"
              :key="item.id"
              :to="item.route"
              class="flex items-center justify-between p-3 rounded-xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 hover:border-amber-400 dark:hover:border-amber-600 transition-all duration-150 group"
            >
              <div class="flex items-center gap-3 min-w-0">
                <div
                  class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0"
                  :class="{
                    'bg-amber-100/70 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400': item.variant === 'warning',
                    'bg-rose-100/70 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400': item.variant === 'danger',
                    'bg-sky-100/70 dark:bg-sky-950/60 text-sky-700 dark:text-sky-400': item.variant === 'info',
                  }"
                >
                  <component :is="item.icon" class="h-4 w-4" />
                </div>
                <div class="min-w-0">
                  <p class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors">
                    {{ item.title }}
                  </p>
                  <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    <span class="font-extrabold text-slate-900 dark:text-white font-mono">{{ item.count }}</span> {{ t('common.items') }}
                  </p>
                </div>
              </div>

              <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#0B5D3B] dark:text-[#75bd97] group-hover:underline shrink-0 ml-2">
                {{ item.actionLabel }}
                <ArrowRight class="h-3 w-3 transition-transform group-hover:translate-x-0.5" />
              </span>
            </RouterLink>
          </div>

          <div v-else class="p-6 rounded-xl bg-[#E8F4EE]/60 dark:bg-[#153C2D]/30 border border-[#0B5D3B]/20 dark:border-[#0B5D3B]/40 flex items-center justify-center gap-2.5 text-xs text-[#0B5D3B] dark:text-[#75bd97] mt-3">
            <CheckCircle2 class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97] shrink-0" />
            <span>{{ t('admin.dashboard.allCaughtUp') }}</span>
          </div>
        </div>

        <div class="pt-2">
          <RouterLink to="/staff/review-claims" class="text-xs text-[#0B5D3B] dark:text-[#75bd97] font-bold hover:underline inline-flex items-center gap-1">
            View All Pending Claims & Handovers &rarr;
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- 6. Recent Operational Activity Feeds (3-Column Table Grid) -->
    <section aria-labelledby="activity-feed-heading" class="space-y-3">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
        <!-- Recent Reported Items -->
        <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 sm:p-5 shadow-2xs space-y-3">
          <!-- Unified Header -->
          <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
              <div class="h-7 w-7 rounded-lg flex items-center justify-center shrink-0 bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97]">
                <Package class="h-3.5 w-3.5" />
              </div>
              <div>
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 leading-tight">
                  {{ t('items.reportedItems') }}
                </h3>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Recent Lost & Found</p>
              </div>
            </div>
            <RouterLink to="/browse" class="text-[11px] text-[#0B5D3B] dark:text-[#75bd97] font-semibold hover:underline inline-flex items-center gap-0.5">
              {{ t('nav.browse') }} &rarr;
            </RouterLink>
          </div>

          <div v-if="recentActivity?.recent_items && recentActivity.recent_items.length > 0" class="divide-y divide-slate-100 dark:divide-slate-800/80">
            <RouterLink
              v-for="item in recentActivity.recent_items"
              :key="item.id"
              :to="`/items/${item.id}`"
              class="py-2.5 flex items-center justify-between gap-2 text-xs hover:bg-slate-50 dark:hover:bg-slate-800/40 px-1 rounded transition-colors group block"
            >
              <div class="min-w-0 flex-1">
                <p class="font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97]">
                  {{ item.title }}
                </p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">
                  {{ item.reference_code }} &bull; {{ formatDate(item.created_at) }}
                </p>
              </div>
              <AppBadge :variant="item.type === 'found' ? 'success' : 'warning'" size="sm">
                {{ item.type === 'found' ? t('items.types.found') : t('items.types.lost') }}
              </AppBadge>
            </RouterLink>
          </div>
          <div v-else class="text-xs text-slate-400 dark:text-slate-500 py-6 text-center font-mono">
            {{ t('home.noRecentItems') }}
          </div>
        </div>

        <!-- Recent Claims -->
        <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 sm:p-5 shadow-2xs space-y-3">
          <!-- Unified Header -->
          <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
              <div class="h-7 w-7 rounded-lg flex items-center justify-center shrink-0 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400">
                <Clock class="h-3.5 w-3.5" />
              </div>
              <div>
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 leading-tight">
                  {{ t('claims.title') }}
                </h3>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Ownership Verifications</p>
              </div>
            </div>
            <RouterLink to="/staff/review-claims" class="text-[11px] text-[#0B5D3B] dark:text-[#75bd97] font-semibold hover:underline inline-flex items-center gap-0.5">
              {{ t('claims.actions.review') }} &rarr;
            </RouterLink>
          </div>

          <div v-if="recentActivity?.recent_claims && recentActivity.recent_claims.length > 0" class="divide-y divide-slate-100 dark:divide-slate-800/80">
            <RouterLink
              v-for="claim in recentActivity.recent_claims"
              :key="claim.id"
              to="/staff/review-claims"
              class="py-2.5 flex items-center justify-between gap-2 text-xs hover:bg-slate-50 dark:hover:bg-slate-800/40 px-1 rounded transition-colors group block"
            >
              <div class="min-w-0 flex-1">
                <p class="font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97]">
                  {{ claim.item?.title ? claim.item.title : `#CLM-${claim.id}` }}
                </p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">
                  {{ claim.item?.reference_code ? claim.item.reference_code + ' • ' : '' }}{{ formatDate(claim.created_at) }}
                </p>
              </div>
              <ClaimStatusBadge :status="claim.status" />
            </RouterLink>
          </div>
          <div v-else class="text-xs text-slate-400 dark:text-slate-500 py-6 text-center font-mono">
            {{ t('claims.review.noClaims') }}
          </div>
        </div>

        <!-- Recent Returns -->
        <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 sm:p-5 shadow-2xs space-y-3">
          <!-- Unified Header -->
          <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
              <div class="h-7 w-7 rounded-lg flex items-center justify-center shrink-0 bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97]">
                <CheckCheck class="h-3.5 w-3.5" />
              </div>
              <div>
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 leading-tight">
                  {{ t('returns.title') }}
                </h3>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Completed Handovers</p>
              </div>
            </div>
            <RouterLink to="/staff/process-return" class="text-[11px] text-[#0B5D3B] dark:text-[#75bd97] font-semibold hover:underline inline-flex items-center gap-0.5">
              {{ t('returns.returnHistory') }} &rarr;
            </RouterLink>
          </div>

          <div v-if="recentActivity?.recent_returns && recentActivity.recent_returns.length > 0" class="divide-y divide-slate-100 dark:divide-slate-800/80">
            <RouterLink
              v-for="ret in recentActivity.recent_returns"
              :key="ret.id"
              to="/staff/process-return"
              class="py-2.5 flex items-center justify-between gap-2 text-xs hover:bg-slate-50 dark:hover:bg-slate-800/40 px-1 rounded transition-colors group block"
            >
              <div class="min-w-0 flex-1">
                <p class="font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97]">
                  {{ ret.item?.title ? ret.item.title : `#RET-${ret.id}` }}
                </p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">
                  {{ ret.item?.reference_code ? ret.item.reference_code + ' • ' : '' }}{{ formatDate(ret.return_date || ret.created_at) }}
                </p>
              </div>
              <AppBadge variant="success" size="sm">
                {{ t('items.statuses.returned') }}
              </AppBadge>
            </RouterLink>
          </div>
          <div v-else class="text-xs text-slate-400 dark:text-slate-500 py-6 text-center font-mono">
            {{ t('returns.process.noHandovers') }}
          </div>
        </div>
      </div>
    </section>

    <!-- 7. Administrative Governance Hub -->
    <section aria-labelledby="governance-heading" class="space-y-3">
      <h2 id="governance-heading" class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200">
        {{ t('admin.dashboard.hub') }}
      </h2>
      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
        <RouterLink
          v-for="action in governanceLinks"
          :key="action.to"
          :to="action.to"
          class="flex flex-col items-center gap-2.5 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-[#111827] p-4 sm:p-5 text-center shadow-2xs hover:shadow-xs hover:border-[#0B5D3B] dark:hover:border-[#75bd97] transition-all group cursor-pointer"
        >
          <div :class="`h-10 w-10 sm:h-11 sm:w-11 rounded-xl ${action.color} flex items-center justify-center text-white shadow-2xs group-hover:scale-105 transition-transform`">
            <component :is="action.icon" class="h-5 w-5" />
          </div>
          <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300 group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors">
            {{ action.label }}
          </span>
        </RouterLink>
      </div>
    </section>
  </div>
</template>
