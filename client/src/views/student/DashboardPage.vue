<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useItemsStore } from '@/features/items/stores/items.store'
import { useClaimsStore } from '@/features/claims/stores/claims.store'
import { getUserSummary, type UserSummaryData } from '@/features/auth/api/auth.api'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import ItemCard from '@/features/items/components/ItemCard.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { t } from '@/i18n'
import {
  AlertCircle,
  CheckCircle2,
  ClipboardList,
  Search,
  Plus,
  ArrowRight,
  ShieldCheck,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const itemsStore = useItemsStore()
const claimsStore = useClaimsStore()

const loadingDashboard = ref(true)
const userSummary = ref<UserSummaryData | null>(null)

async function loadDashboardData() {
  loadingDashboard.value = true
  try {
    const [summary] = await Promise.all([
      getUserSummary().catch(() => null),
      itemsStore.fetchItems({ mine: true, per_page: 4 }, true).catch(() => {}),
    ])
    if (summary) {
      userSummary.value = summary
    }
  } finally {
    loadingDashboard.value = false
  }
}

onMounted(() => {
  loadDashboardData()
})

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return t('greetings.morning')
  if (hour < 18) return t('greetings.afternoon')
  return t('greetings.evening')
})

const myLostCount = computed(() => {
  return userSummary.value?.my_lost_count ?? itemsStore.items.filter(i => i.type === 'lost').length
})

const myFoundCount = computed(() => {
  return userSummary.value?.my_found_count ?? itemsStore.items.filter(i => i.type === 'found').length
})

const myClaimsCount = computed(() => {
  return userSummary.value?.my_claims_count ?? claimsStore.claims.length
})

const quickLinks = computed(() => {
  const links = []
  if (authStore.can('REPORT_LOST')) {
    links.push({ label: t('items.reportLost'), to: '/student/report-lost', icon: AlertCircle, color: 'bg-amber-600 dark:bg-amber-700' })
  }
  if (authStore.can('REPORT_FOUND')) {
    links.push({ label: t('items.reportFound'), to: '/student/report-found', icon: CheckCircle2, color: 'bg-[#0B5D3B] dark:bg-[#153C2D]' })
  }
  links.push({ label: t('nav.browse'), to: '/browse', icon: Search, color: 'bg-sky-600 dark:bg-sky-700' })
  if (authStore.can('SUBMIT_CLAIM')) {
    links.push({ label: t('nav.myClaims'), to: '/student/my-claims', icon: ClipboardList, color: 'bg-indigo-600 dark:bg-indigo-700' })
  }
  return links
})
</script>

<template>
  <div class="space-y-4 sm:space-y-5">
      <!-- 1. Student Welcome Banner -->
      <div class="rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#0B5D3B] via-[#084C30] to-[#063D27] p-4 sm:p-6 text-white shadow-md relative overflow-hidden border border-[#0B5D3B]/40">
        <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-[#D4AF37]/15 rounded-full blur-2xl pointer-events-none" />

        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/30 mb-1.5">
              <ShieldCheck class="h-3.5 w-3.5" />
              {{ t('studentDashboard.welcomeTitle') }}
            </div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-tight">
              {{ greeting }}, {{ authStore.user?.full_name?.split(' ')[0] || t('admin.roles.student') }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-200/90 mt-1 max-w-xl leading-relaxed">
              {{ t('studentDashboard.welcomeSub') }}
            </p>
          </div>

          <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <AppButton v-if="authStore.can('REPORT_LOST')" variant="gold" size="sm" @click="router.push('/student/report-lost')">
              <template #icon-left>
                <Plus class="h-4 w-4 mr-1" />
              </template>
              {{ t('items.reportLost') }}
            </AppButton>
            <AppButton v-if="authStore.can('REPORT_FOUND')" variant="secondary" size="sm" @click="router.push('/student/report-found')">
              <template #icon-left>
                <Plus class="h-4 w-4 mr-1" />
              </template>
              {{ t('items.reportFound') }}
            </AppButton>
          </div>
        </div>
      </div>

      <!-- 2. Personal Metrics Overview -->
      <section aria-labelledby="student-metrics-heading" class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <RouterLink
          to="/student/my-items"
          class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between hover:shadow-xs hover:border-amber-400 dark:hover:border-amber-600 transition-all duration-150 group"
        >
          <div class="min-w-0">
            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-0.5">
              {{ t('studentDashboard.myLostReports') }}
            </span>
            <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
              {{ loadingDashboard ? '—' : myLostCount }}
            </p>
            <span class="text-xs text-[#0B5D3B] dark:text-[#75bd97] font-semibold group-hover:underline mt-1.5 inline-flex items-center gap-1">
              {{ t('studentDashboard.viewLostItems') }}
              <ArrowRight class="h-3 w-3 transition-transform group-hover:translate-x-0.5" />
            </span>
          </div>
          <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold shrink-0">
            <AlertCircle class="h-5 w-5" />
          </div>
        </RouterLink>

        <RouterLink
          to="/student/my-items"
          class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between hover:shadow-xs hover:border-[#0B5D3B] dark:hover:border-[#75bd97] transition-all duration-150 group"
        >
          <div class="min-w-0">
            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-0.5">
              {{ t('studentDashboard.myFoundReports') }}
            </span>
            <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
              {{ loadingDashboard ? '—' : myFoundCount }}
            </p>
            <span class="text-xs text-[#0B5D3B] dark:text-[#75bd97] font-semibold group-hover:underline mt-1.5 inline-flex items-center gap-1">
              {{ t('studentDashboard.viewFoundItems') }}
              <ArrowRight class="h-3 w-3 transition-transform group-hover:translate-x-0.5" />
            </span>
          </div>
          <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] flex items-center justify-center font-bold shrink-0">
            <CheckCircle2 class="h-5 w-5" />
          </div>
        </RouterLink>

        <RouterLink
          to="/student/my-claims"
          class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between hover:shadow-xs hover:border-indigo-400 dark:hover:border-indigo-600 transition-all duration-150 group"
        >
          <div class="min-w-0">
            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-0.5">
              {{ t('studentDashboard.activeClaims') }}
            </span>
            <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
              {{ loadingDashboard ? '—' : myClaimsCount }}
            </p>
            <span class="text-xs text-[#0B5D3B] dark:text-[#75bd97] font-semibold group-hover:underline mt-1.5 inline-flex items-center gap-1">
              {{ t('studentDashboard.trackClaimStatus') }}
              <ArrowRight class="h-3 w-3 transition-transform group-hover:translate-x-0.5" />
            </span>
          </div>
          <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold shrink-0">
            <ClipboardList class="h-5 w-5" />
          </div>
        </RouterLink>
      </section>

      <!-- 3. Quick Actions -->
      <section aria-labelledby="student-actions-heading" class="space-y-2.5">
        <h2 id="student-actions-heading" class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">
          {{ t('studentDashboard.quickActions') }}
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
          <RouterLink
            v-for="link in quickLinks"
            :key="link.to"
            :to="link.to"
            class="flex flex-col items-center justify-center gap-2 rounded-xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-[#111827] p-3.5 sm:p-4 text-center shadow-2xs hover:shadow-xs hover:border-[#0B5D3B] dark:hover:border-[#75bd97] transition-all group cursor-pointer"
          >
            <div :class="`h-10 w-10 sm:h-11 sm:w-11 rounded-xl ${link.color} flex items-center justify-center text-white shadow-2xs group-hover:scale-105 transition-transform font-bold`">
              <component :is="link.icon" class="h-5 w-5" />
            </div>
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors">
              {{ link.label }}
            </span>
          </RouterLink>
        </div>
      </section>

      <!-- 4. Recent Reported Items List -->
      <section aria-labelledby="student-recent-heading" class="space-y-2.5">
        <div class="flex items-center justify-between">
          <div>
            <h2 id="student-recent-heading" class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
              {{ t('studentDashboard.recentItemsTitle') }}
            </h2>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
              {{ t('studentDashboard.recentItemsSub') }}
            </p>
          </div>
          <RouterLink to="/student/my-items" class="text-xs text-[#0B5D3B] dark:text-[#75bd97] font-bold hover:underline inline-flex items-center gap-1">
            {{ t('studentDashboard.manageAllItems') }}
            <ArrowRight class="h-3.5 w-3.5" />
          </RouterLink>
        </div>

        <div v-if="itemsStore.loading && itemsStore.items.length === 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          <div v-for="n in 4" :key="n" class="rounded-xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-[#111827] p-3.5 space-y-3">
            <AppSkeleton height="9rem" class="rounded-xl" />
            <AppSkeleton height="1rem" width="60%" />
            <AppSkeleton height="0.875rem" />
          </div>
        </div>

        <div v-else-if="itemsStore.items.length === 0" class="text-center py-8 bg-white dark:bg-[#111827] rounded-xl border border-slate-200/90 dark:border-slate-800 text-xs text-slate-400 dark:text-slate-500 space-y-2.5">
          <p>{{ t('studentDashboard.noReportedItems') }}</p>
          <AppButton v-if="authStore.can('REPORT_LOST')" variant="primary" size="sm" @click="router.push('/student/report-lost')">
            {{ t('items.reportLost') }}
          </AppButton>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <ItemCard v-for="item in itemsStore.items.slice(0, 4)" :key="item.id" :item="item" />
        </div>
      </section>
    </div>
</template>
