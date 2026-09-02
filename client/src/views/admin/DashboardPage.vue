<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useAdminDashboard as useAdminStats } from '@/features/admin/composables/useAdminDashboard'
import AdminStatsCard from '@/features/admin/components/AdminStatsCard.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
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
  Search,
  FileText,
  RefreshCw,
  ShieldCheck,
  CheckCheck,
  Sparkles,
  AlertTriangle,
  Handshake,
  ArrowRight,
  ShieldAlert,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const { stats, recentActivity, loading, error, fetchStats } = useAdminStats()

const lastUpdated = ref<Date>(new Date())

async function handleRefresh(): Promise<void> {
  await fetchStats(true)
  lastUpdated.value = new Date()
}

onMounted(() => {
  fetchStats(false)
})

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return t('greetings.morning')
  if (hour < 18) return t('greetings.afternoon')
  return t('greetings.evening')
})

const recoveryRate = computed(() => {
  if (!stats.value || stats.value.total_items === 0) return 0
  return Math.round((stats.value.returned_items / stats.value.total_items) * 100)
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
      <!-- 1. Header & Context Surface -->
      <div class="rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#0B5D3B] via-[#084C30] to-[#063D27] p-4 sm:p-6 text-white shadow-md relative overflow-hidden border border-[#0B5D3B]/40">
        <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-[#D4AF37]/15 rounded-full blur-2xl pointer-events-none" />

        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/30 mb-1.5">
              <ShieldCheck class="h-3.5 w-3.5" />
              {{ t('admin.dashboard.systemAdmin') }}
            </div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-tight">
              {{ greeting }}, {{ authStore.user?.full_name || t('admin.roles.admin') }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-200/90 mt-1 max-w-xl leading-relaxed">
              {{ t('admin.dashboard.description') }}
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-2 shrink-0">
            <AppButton variant="ghost" size="sm" class="text-white hover:bg-white/10" :loading="loading" @click="handleRefresh">
              <template #icon-left>
                <RefreshCw class="h-3.5 w-3.5 mr-1" />
              </template>
              {{ t('admin.dashboard.refreshLive') }}
            </AppButton>
            <AppButton variant="gold" size="sm" @click="router.push('/admin/reports')">
              <template #icon-left>
                <FileText class="h-4 w-4 mr-1" />
              </template>
              {{ t('admin.dashboard.generateReport') }}
            </AppButton>
          </div>
        </div>
      </div>

      <!-- 2. Needs Attention / Operational Action Queue (High Priority) -->
      <section aria-labelledby="needs-attention-heading" class="space-y-2.5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <ShieldAlert class="h-4 w-4 text-amber-600 dark:text-amber-400" />
            <h2 id="needs-attention-heading" class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200">
              {{ t('admin.dashboard.needsAttentionTitle') }}
            </h2>
          </div>
          <span class="text-[11px] text-slate-400 dark:text-slate-500">
            {{ t('admin.dashboard.needsAttentionSubtitle') }}
          </span>
        </div>

        <div v-if="loading && !stats" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <AppSkeleton v-for="n in 4" :key="n" height="4.5rem" class="rounded-xl" />
        </div>

        <div v-else-if="attentionItems.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <RouterLink
            v-for="item in attentionItems"
            :key="item.id"
            :to="item.route"
            class="flex items-center justify-between p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs hover:shadow-xs hover:border-amber-400 dark:hover:border-amber-600 transition-all duration-150 group"
          >
            <div class="flex items-center gap-3 min-w-0">
              <div
                class="h-9 w-9 rounded-lg flex items-center justify-center shrink-0"
                :class="{
                  'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400': item.variant === 'warning',
                  'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400': item.variant === 'danger',
                  'bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400': item.variant === 'info',
                }"
              >
                <component :is="item.icon" class="h-4 w-4" />
              </div>
              <div class="min-w-0">
                <p class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors">
                  {{ item.title }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                  <span class="font-extrabold text-slate-900 dark:text-white">{{ item.count }}</span> {{ t('common.items') }}
                </p>
              </div>
            </div>

            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#0B5D3B] dark:text-[#75bd97] group-hover:underline shrink-0 ml-2">
              {{ item.actionLabel }}
              <ArrowRight class="h-3 w-3 transition-transform group-hover:translate-x-0.5" />
            </span>
          </RouterLink>
        </div>

        <div v-else class="p-4 rounded-xl bg-[#E8F4EE]/60 dark:bg-[#153C2D]/30 border border-[#0B5D3B]/20 dark:border-[#0B5D3B]/40 flex items-center gap-2.5 text-xs text-[#0B5D3B] dark:text-[#75bd97]">
          <CheckCircle2 class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97] shrink-0" />
          <span>{{ t('admin.dashboard.allCaughtUp') }}</span>
        </div>
      </section>

      <!-- 3. Core KPI Summary (Concise & Actionable) -->
      <section aria-labelledby="kpi-summary-heading" class="space-y-3">
        <div class="flex items-center justify-between">
          <div>
            <h2 id="kpi-summary-heading" class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
              {{ t('admin.dashboard.metricsTitle') }}
            </h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
              {{ t('admin.dashboard.metricsSubtitle') }}
            </p>
          </div>
        </div>

        <div v-if="loading && !stats" class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
          <AppSkeleton v-for="n in 8" :key="n" height="6.2rem" class="rounded-2xl" />
        </div>
        <div v-else-if="stats" class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
          <AdminStatsCard
            :label="t('admin.dashboard.totalItems')"
            :value="stats.total_items"
            :icon="Package"
            variant="primary"
            :sublabel="t('admin.dashboard.allRecords')"
            to="/browse"
          />
          <AdminStatsCard
            :label="t('admin.dashboard.lostItems')"
            :value="stats.active_lost ?? stats.lost_items"
            :icon="AlertCircle"
            variant="warning"
            :sublabel="t('admin.dashboard.lostSublabel')"
            to="/browse?type=lost"
          />
          <AdminStatsCard
            :label="t('admin.dashboard.foundItems')"
            :value="stats.found_items"
            :icon="CheckCircle2"
            variant="success"
            :sublabel="t('admin.dashboard.foundSublabel')"
            to="/browse?type=found"
          />
          <AdminStatsCard
            :label="t('admin.dashboard.returnedItems')"
            :value="stats.returned_items"
            :icon="CheckCheck"
            variant="success"
            :trend="{ value: `${recoveryRate}%`, direction: 'up', label: t('admin.dashboard.recoveryRate') }"
            to="/staff/process-return"
          />
          <AdminStatsCard
            :label="t('admin.dashboard.avgTurnaround')"
            :value="stats.avg_resolution_days !== null ? `${stats.avg_resolution_days}d` : 'N/A'"
            :icon="Clock"
            variant="info"
            :sublabel="t('admin.dashboard.avgSublabel')"
            to="/admin/reports"
          />
          <AdminStatsCard
            :label="t('admin.dashboard.searchFailRate')"
            :value="`${stats.search_fail_rate_percentage || 0}%`"
            :icon="Search"
            variant="warning"
            :sublabel="t('admin.dashboard.searchFailSublabel')"
            to="/admin/audit-logs"
          />
          <AdminStatsCard
            :label="t('admin.dashboard.pendingClaims')"
            :value="stats.pending_claims"
            :icon="Clock"
            variant="warning"
            :sublabel="t('admin.dashboard.pendingClaimsSublabel')"
            to="/staff/review-claims"
          />
          <AdminStatsCard
            :label="t('admin.dashboard.registeredUsers')"
            :value="stats.total_users"
            :icon="Users"
            variant="info"
            :sublabel="t('admin.dashboard.registeredUsersSublabel')"
            to="/admin/users"
          />
        </div>
        <div v-else-if="error" class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 flex items-center justify-between text-xs text-rose-700 dark:text-rose-300">
          <span>{{ error }}</span>
          <AppButton variant="outline" size="xs" @click="handleRefresh">{{ t('common.retry') || 'Retry' }}</AppButton>
        </div>
      </section>

      <!-- 4. Recent Operational Activity Feed (Real Backend Data) -->
      <section aria-labelledby="activity-feed-heading" class="space-y-3">
        <div class="flex items-center justify-between">
          <div>
            <h2 id="activity-feed-heading" class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
              {{ t('admin.dashboard.activityFeedTitle') }}
            </h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
              {{ t('admin.dashboard.activityFeedSubtitle') }}
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
          <!-- Recent Reported Items -->
          <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 sm:p-5 shadow-2xs space-y-3">
            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
              <div class="flex items-center gap-2">
                <Package class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                  {{ t('items.reportedItems') }}
                </h3>
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
            <div v-else class="text-xs text-slate-400 dark:text-slate-500 py-6 text-center">
              {{ t('home.noRecentItems') }}
            </div>
          </div>

          <!-- Recent Claims -->
          <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 sm:p-5 shadow-2xs space-y-3">
            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
              <div class="flex items-center gap-2">
                <Clock class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                  {{ t('claims.title') }}
                </h3>
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
                <AppBadge
                  :variant="claim.status === 'approved' ? 'success' : claim.status === 'rejected' ? 'danger' : 'warning'"
                  size="sm"
                >
                  {{ t(`claims.status.${claim.status}`) }}
                </AppBadge>
              </RouterLink>
            </div>
            <div v-else class="text-xs text-slate-400 dark:text-slate-500 py-6 text-center">
              {{ t('claims.review.noClaims') }}
            </div>
          </div>

          <!-- Recent Returns -->
          <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 sm:p-5 shadow-2xs space-y-3">
            <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-800">
              <div class="flex items-center gap-2">
                <CheckCheck class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                  {{ t('returns.title') }}
                </h3>
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
            <div v-else class="text-xs text-slate-400 dark:text-slate-500 py-6 text-center">
              {{ t('returns.process.noHandovers') }}
            </div>
          </div>
        </div>
      </section>

      <!-- 5. Operational Breakdown (Recovery Progress & Categories) -->
      <section v-if="stats" aria-labelledby="breakdown-heading" class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Recovery Efficiency -->
        <div class="lg:col-span-2 p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 id="breakdown-heading" class="text-sm font-bold text-slate-900 dark:text-white">
                {{ t('admin.dashboard.efficiencyTitle') }}
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                {{ t('admin.dashboard.efficiencySubtitle') }}
              </p>
            </div>
            <span class="text-lg font-black text-[#0B5D3B] dark:text-[#75bd97]">
              {{ recoveryRate }}% {{ t('admin.dashboard.reclaimed') }}
            </span>
          </div>

          <!-- Progress Bar -->
          <div class="space-y-2 pt-1">
            <div class="h-3 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden flex">
              <div
                class="h-full bg-[#0B5D3B] dark:bg-[#75bd97] transition-all duration-500"
                :style="{ width: `${recoveryRate}%` }"
              />
              <div
                class="h-full bg-amber-500 transition-all duration-500"
                :style="{ width: `${Math.max(0, 100 - recoveryRate)}%` }"
              />
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
              <span class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-[#0B5D3B] dark:bg-[#75bd97]" />
                {{ t('admin.dashboard.returnedToOwner') }} ({{ stats.returned_items }})
              </span>
              <span class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-amber-500" />
                {{ t('admin.dashboard.pendingCustody') }} ({{ Math.max(0, stats.total_items - stats.returned_items) }})
              </span>
            </div>
          </div>
        </div>

        <!-- Top 3 Categories -->
        <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-4">
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
              {{ t('admin.dashboard.topCategoriesTitle') }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              {{ t('admin.dashboard.topCategoriesSubtitle') }}
            </p>
          </div>

          <div v-if="stats.top_3_categories && stats.top_3_categories.length" class="space-y-3 pt-1">
            <div
              v-for="(cat, idx) in stats.top_3_categories"
              :key="cat.id"
              class="space-y-1.5"
            >
              <div class="flex items-center justify-between text-xs font-semibold">
                <span class="text-slate-800 dark:text-slate-200 truncate mr-2">{{ idx + 1 }}. {{ cat.name }}</span>
                <span class="text-[#0B5D3B] dark:text-[#75bd97] font-bold shrink-0">{{ cat.total }} {{ t('common.items') }}</span>
              </div>
              <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                <div
                  class="h-full bg-[#0B5D3B] dark:bg-[#75bd97] rounded-full"
                  :style="{ width: `${Math.min(100, Math.round((cat.total / (stats.total_items || 1)) * 100))}%` }"
                />
              </div>
            </div>
          </div>
          <div v-else class="text-xs text-slate-400 dark:text-slate-500 py-4 text-center">
            {{ t('admin.dashboard.noCategoryData') }}
          </div>
        </div>
      </section>

      <!-- 6. Administrative Governance Hub -->
      <section aria-labelledby="governance-heading" class="space-y-3">
        <h2 id="governance-heading" class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
          {{ t('admin.dashboard.hub') }}
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
          <RouterLink
            v-for="action in governanceLinks"
            :key="action.to"
            :to="action.to"
            class="flex flex-col items-center gap-2.5 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-[#111827] p-4 sm:p-5 text-center shadow-2xs hover:shadow-xs hover:border-[#0B5D3B] dark:hover:border-[#75bd97] transition-all group cursor-pointer"
          >
            <div :class="`h-10 w-10 sm:h-12 sm:w-12 rounded-xl ${action.color} flex items-center justify-center text-white shadow-2xs group-hover:scale-105 transition-transform`">
              <component :is="action.icon" class="h-5 w-5 sm:h-6 sm:w-6" />
            </div>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors">
              {{ action.label }}
            </span>
          </RouterLink>
        </div>
      </section>
    </div>
</template>
