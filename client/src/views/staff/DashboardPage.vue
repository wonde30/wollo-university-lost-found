<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useClaims } from '@/features/claims/composables/useClaims'
import { getItems } from '@/features/items/api/items.api'
import { getClaims } from '@/features/claims/api/claims.api'
import { getReturns } from '@/features/returns/api/returns.api'
import AdminStatsCard from '@/features/admin/components/AdminStatsCard.vue'
import ClaimCard from '@/features/claims/components/ClaimCard.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { t } from '@/i18n'
import {
  ClipboardCheck,
  Sparkles,
  Handshake,
  Package,
  CheckCircle2,
  Clock,
  ShieldCheck,
  Plus,
  ArrowRight,
  ShieldAlert,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const { claims, loading: claimsLoading, loadClaims } = useClaims()

interface StaffStats {
  total_items: number
  pending_claims: number
  returned_items: number
  in_storage: number
}

const stats = ref<StaffStats | null>(null)
const statsLoading = ref(false)

async function fetchStaffStats(): Promise<void> {
  statsLoading.value = true
  try {
    const [itemsRes, claimsRes, returnsRes, custodyRes] = await Promise.allSettled([
      getItems({ per_page: 1 }),
      getClaims({ status: 'pending' }, { per_page: 1 }),
      getReturns({ per_page: 1 }),
      getItems({ status: 'in_custody', per_page: 1 }),
    ])

    stats.value = {
      total_items:    itemsRes.status   === 'fulfilled' ? (itemsRes.value.meta?.total   ?? 0) : 0,
      pending_claims: claimsRes.status  === 'fulfilled' ? (claimsRes.value.meta?.total  ?? 0) : 0,
      returned_items: returnsRes.status === 'fulfilled' ? (returnsRes.value.meta?.total ?? 0) : 0,
      in_storage:     custodyRes.status === 'fulfilled' ? (custodyRes.value.meta?.total ?? 0) : (itemsRes.status === 'fulfilled' ? (itemsRes.value.meta?.total ?? 0) : 0),
    }
  } finally {
    statsLoading.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    fetchStaffStats(),
    loadClaims({ status: 'pending', per_page: 5 }),
  ])
})

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return t('greetings.morning')
  if (hour < 18) return t('greetings.afternoon')
  return t('greetings.evening')
})

const quickActions = computed(() => {
  const actions = []
  if (authStore.can('REVIEW_CLAIMS')) {
    actions.push({ label: t('nav.reviewClaims'), to: '/staff/review-claims', icon: ClipboardCheck, color: 'bg-amber-600 dark:bg-amber-700' })
  }
  if (authStore.can('MANAGE_ALL_ITEMS')) {
    actions.push({ label: t('nav.matchSuggestions'), to: '/staff/match-suggestions', icon: Sparkles, color: 'bg-purple-600 dark:bg-purple-700' })
  }
  if (authStore.can('PROCESS_RETURNS')) {
    actions.push({ label: t('nav.processReturn'), to: '/staff/process-return', icon: Handshake, color: 'bg-[#0B5D3B] dark:bg-[#153C2D]' })
  }
  if (authStore.can('MANAGE_CUSTODY')) {
    actions.push({ label: t('nav.manageCustody'), to: '/staff/manage-custody', icon: Package, color: 'bg-slate-800 dark:bg-slate-700' })
  }
  return actions
})
</script>

<template>
  <div class="space-y-4 sm:space-y-5">
      <!-- 1. Staff Header Banner -->
      <div class="rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#0B5D3B] via-[#084C30] to-[#063D27] p-4 sm:p-6 text-white shadow-md relative overflow-hidden border border-[#0B5D3B]/40">
        <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-[#D4AF37]/15 rounded-full blur-2xl pointer-events-none" />

        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/30 mb-1.5">
              <ShieldCheck class="h-3.5 w-3.5" />
              {{ t('staffDashboard.operations') }}
            </div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-tight">
              {{ greeting }}, {{ authStore.user?.full_name || t('admin.roles.staff') }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-200/90 mt-1 max-w-xl leading-relaxed">
              {{ t('staffDashboard.operationsSubtitle') }}
            </p>
          </div>

          <div v-if="authStore.can('PROCESS_RETURNS')" class="flex items-center gap-2 shrink-0">
            <AppButton variant="gold" size="sm" @click="router.push('/staff/process-return')">
              <template #icon-left>
                <Plus class="h-4 w-4 mr-1" />
              </template>
              {{ t('nav.processReturn') }}
            </AppButton>
          </div>
        </div>
      </div>

      <!-- 2. Operational Needs Attention (Staff) -->
      <section v-if="stats && (stats.pending_claims > 0 || stats.in_storage > 0) && (authStore.can('REVIEW_CLAIMS') || authStore.can('MANAGE_ALL_ITEMS') || authStore.can('MANAGE_CUSTODY'))" aria-labelledby="staff-attention-heading" class="space-y-2.5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <ShieldAlert class="h-4 w-4 text-amber-600 dark:text-amber-400" />
            <h2 id="staff-attention-heading" class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200">
              {{ t('admin.dashboard.needsAttentionTitle') }}
            </h2>
          </div>
          <span class="text-[11px] text-slate-400 dark:text-slate-500">
            {{ t('admin.dashboard.needsAttentionSubtitle') }}
          </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <RouterLink
            v-if="authStore.can('REVIEW_CLAIMS')"
            to="/staff/review-claims"
            class="flex items-center justify-between p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs hover:shadow-xs hover:border-amber-400 dark:hover:border-amber-600 transition-all duration-150 group"
          >
            <div class="flex items-center gap-3 min-w-0">
              <div class="h-9 w-9 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <Clock class="h-4 w-4" />
              </div>
              <div class="min-w-0">
                <p class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors">
                  {{ t('admin.dashboard.pendingClaims') }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                  <span class="font-extrabold text-slate-900 dark:text-white">{{ stats.pending_claims }}</span> {{ t('common.items') }}
                </p>
              </div>
            </div>

            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#0B5D3B] dark:text-[#75bd97] group-hover:underline shrink-0 ml-2">
              {{ t('admin.dashboard.reviewAction') }}
              <ArrowRight class="h-3 w-3 transition-transform group-hover:translate-x-0.5" />
            </span>
          </RouterLink>

          <RouterLink
            v-if="authStore.can('MANAGE_ALL_ITEMS')"
            to="/staff/match-suggestions"
            class="flex items-center justify-between p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs hover:shadow-xs hover:border-purple-400 dark:hover:border-purple-600 transition-all duration-150 group"
          >
            <div class="flex items-center gap-3 min-w-0">
              <div class="h-9 w-9 rounded-lg bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                <Sparkles class="h-4 w-4" />
              </div>
              <div class="min-w-0">
                <p class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors">
                  {{ t('nav.matchSuggestions') }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                  {{ t('matchSuggestions.subtitle') }}
                </p>
              </div>
            </div>

            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#0B5D3B] dark:text-[#75bd97] group-hover:underline shrink-0 ml-2">
              {{ t('admin.dashboard.reviewAction') }}
              <ArrowRight class="h-3 w-3 transition-transform group-hover:translate-x-0.5" />
            </span>
          </RouterLink>

          <RouterLink
            v-if="authStore.can('MANAGE_CUSTODY')"
            to="/staff/manage-custody"
            class="flex items-center justify-between p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs hover:shadow-xs hover:border-indigo-400 dark:hover:border-indigo-600 transition-all duration-150 group"
          >
            <div class="flex items-center gap-3 min-w-0">
              <div class="h-9 w-9 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <Package class="h-4 w-4" />
              </div>
              <div class="min-w-0">
                <p class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors">
                  {{ t('staffDashboard.inStorage') }}
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                  <span class="font-extrabold text-slate-900 dark:text-white">{{ stats.in_storage }}</span> {{ t('common.items') }}
                </p>
              </div>
            </div>

            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#0B5D3B] dark:text-[#75bd97] group-hover:underline shrink-0 ml-2">
              {{ t('admin.dashboard.manageAction') }}
              <ArrowRight class="h-3 w-3 transition-transform group-hover:translate-x-0.5" />
            </span>
          </RouterLink>
        </div>
      </section>

      <!-- 3. Operational Workflows Navigation -->
      <section v-if="quickActions.length > 0" aria-labelledby="staff-workflow-heading" class="space-y-3">
        <h2 id="staff-workflow-heading" class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
          {{ t('staffDashboard.workflowTitle') }}
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          <RouterLink
            v-for="action in quickActions"
            :key="action.to"
            :to="action.to"
            class="flex items-center gap-4 rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-[#111827] p-4 sm:p-5 shadow-2xs hover:shadow-xs hover:border-[#0B5D3B] dark:hover:border-[#75bd97] transition-all group cursor-pointer"
          >
            <div :class="`h-11 w-11 sm:h-12 sm:w-12 rounded-xl ${action.color} flex items-center justify-center text-white shadow-2xs group-hover:scale-105 transition-transform shrink-0`">
              <component :is="action.icon" class="h-5 w-5 sm:h-6 sm:w-6" />
            </div>
            <div>
              <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors block">
                {{ action.label }}
              </span>
              <span class="text-[11px] text-slate-400 dark:text-slate-500 inline-flex items-center gap-1 group-hover:text-slate-600 dark:group-hover:text-slate-400">
                {{ t('staffDashboard.launchWorkflow') }}
                <ArrowRight class="h-3 w-3 transition-transform group-hover:translate-x-0.5" />
              </span>
            </div>
          </RouterLink>
        </div>
      </section>

      <!-- 4. Operational Stats Grid -->
      <section aria-labelledby="staff-metrics-heading" class="space-y-3">
        <h2 id="staff-metrics-heading" class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
          {{ t('staffDashboard.overviewTitle') }}
        </h2>
        <div v-if="statsLoading && !stats" class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
          <AppSkeleton v-for="n in 4" :key="n" height="6.2rem" class="rounded-2xl" />
        </div>
        <div v-else-if="stats" class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
          <AdminStatsCard
            :label="t('staffDashboard.totalItems')"
            :value="stats.total_items"
            :icon="Package"
            variant="primary"
            :sublabel="t('staffDashboard.totalItemsSub')"
            to="/browse"
          />
          <AdminStatsCard
            :label="t('staffDashboard.pendingClaims')"
            :value="stats.pending_claims"
            :icon="Clock"
            variant="warning"
            :sublabel="t('staffDashboard.pendingClaimsSub')"
            :to="authStore.can('REVIEW_CLAIMS') ? '/staff/review-claims' : undefined"
          />
          <AdminStatsCard
            :label="t('staffDashboard.itemsReturned')"
            :value="stats.returned_items"
            :icon="CheckCircle2"
            variant="success"
            :sublabel="t('staffDashboard.itemsReturnedSub')"
            :to="authStore.can('PROCESS_RETURNS') ? '/staff/process-return' : undefined"
          />
          <AdminStatsCard
            :label="t('staffDashboard.inStorage')"
            :value="stats.in_storage"
            :icon="ShieldCheck"
            variant="info"
            :sublabel="t('staffDashboard.inStorageSub')"
            :to="authStore.can('MANAGE_CUSTODY') ? '/staff/manage-custody' : undefined"
          />
        </div>
      </section>

      <!-- 5. Pending Claims To Review -->
      <section v-if="authStore.can('REVIEW_CLAIMS')" aria-labelledby="staff-claims-heading" class="space-y-3">
        <div class="flex items-center justify-between">
          <div>
            <h2 id="staff-claims-heading" class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
              {{ t('staffDashboard.pendingReviewTitle') }}
            </h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
              {{ t('staffDashboard.pendingReviewSub') }}
            </p>
          </div>
          <RouterLink to="/staff/review-claims" class="text-xs text-[#0B5D3B] dark:text-[#75bd97] font-bold hover:underline inline-flex items-center gap-1">
            {{ t('staffDashboard.viewAllClaims') }}
            <ArrowRight class="h-3.5 w-3.5" />
          </RouterLink>
        </div>

        <div v-if="claimsLoading && claims.length === 0" class="space-y-3">
          <AppSkeleton v-for="n in 3" :key="n" height="5rem" class="rounded-2xl" />
        </div>

        <div v-else-if="claims.length === 0" class="p-8 text-center bg-white dark:bg-[#111827] rounded-2xl border border-slate-200/90 dark:border-slate-800 text-xs text-slate-400 dark:text-slate-500">
          {{ t('staffDashboard.noPendingClaims') }}
        </div>

        <div v-else class="space-y-3">
          <ClaimCard v-for="claim in claims" :key="claim.id" :claim="claim" />
        </div>
      </section>
    </div>
</template>
