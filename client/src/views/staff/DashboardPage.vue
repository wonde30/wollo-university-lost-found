<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useClaims } from '@/features/claims/composables/useClaims'
import { getItems } from '@/features/items/api/items.api'
import { getClaims } from '@/features/claims/api/claims.api'
import { getReturns } from '@/features/returns/api/returns.api'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import AdminStatsCard from '@/features/admin/components/AdminStatsCard.vue'
import ClaimCard from '@/features/claims/components/ClaimCard.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import AppButton from '@/components/ui/AppButton.vue'

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
    const [itemsRes, claimsRes, returnsRes] = await Promise.allSettled([
      getItems({ per_page: 1 }),
      getClaims({ status: 'pending' }, { per_page: 1 }),
      getReturns({ per_page: 1 }),
    ])

    stats.value = {
      total_items:    itemsRes.status   === 'fulfilled' ? (itemsRes.value.meta?.total   ?? 0) : 0,
      pending_claims: claimsRes.status  === 'fulfilled' ? (claimsRes.value.meta?.total  ?? 0) : 0,
      returned_items: returnsRes.status === 'fulfilled' ? (returnsRes.value.meta?.total ?? 0) : 0,
      in_storage:     itemsRes.status   === 'fulfilled' ? (itemsRes.value.meta?.total   ?? 0) : 0,
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
  if (hour < 12) return 'Good morning'
  if (hour < 18) return 'Good afternoon'
  return 'Good evening'
})

const quickActions = [
  { label: 'Review Pending Claims', to: '/staff/review-claims', icon: 'claims', color: 'bg-amber-600' },
  { label: 'Process Handover Return', to: '/staff/process-return', icon: 'returns', color: 'bg-[#0F5132]' },
  { label: 'Manage Physical Custody', to: '/staff/manage-custody', icon: 'custody', color: 'bg-indigo-600' },
]
</script>

<template>
  <DashboardLayout>
    <div class="space-y-8">
      <!-- Welcome Banner -->
      <div class="rounded-3xl bg-gradient-to-br from-[#0F5132] via-[#0B3822] to-[#04140B] p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-[#D4AF37]/15 rounded-full blur-2xl pointer-events-none" />

        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-bold bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/30 mb-2">
              Staff & Security Operations
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white">
              {{ greeting }}, {{ authStore.user?.full_name || 'Staff Member' }} 👋
            </h1>
            <p class="text-xs sm:text-sm text-slate-200/90 mt-1 max-w-xl leading-relaxed">
              Verify student claims, manage storage vault locations, and record physical item handovers.
            </p>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <AppButton variant="gold" size="sm" @click="router.push('/staff/process-return')">
              + Process Return Handover
            </AppButton>
          </div>
        </div>
      </div>

      <!-- Quick Action Cards -->
      <div>
        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Operations Workflow</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <RouterLink
            v-for="action in quickActions"
            :key="action.to"
            :to="action.to"
            class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all group"
          >
            <div :class="`h-12 w-12 rounded-2xl ${action.color} flex items-center justify-center text-white shadow-xs group-hover:scale-105 transition-transform shrink-0`">
              <svg v-if="action.icon === 'claims'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
              <svg v-else-if="action.icon === 'returns'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3-3m-3 3l3 3m5 4v1a3 3 0 003 3h4a3 3 0 003-3v-5a3 3 0 00-3-3h-2" />
              </svg>
              <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <div>
              <span class="text-sm font-bold text-slate-800 group-hover:text-[#0F5132] transition-colors block">{{ action.label }}</span>
              <span class="text-xs text-slate-400">Launch workflow &rarr;</span>
            </div>
          </RouterLink>
        </div>
      </div>

      <!-- Stats Grid -->
      <div>
        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Platform Overview</h2>
        <div v-if="statsLoading && !stats" class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <AppSkeleton v-for="n in 4" :key="n" height="7rem" class="rounded-2xl" />
        </div>
        <div v-else-if="stats" class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <AdminStatsCard label="Total Items"     :value="stats.total_items"    icon="📦" variant="primary" />
          <AdminStatsCard label="Pending Claims"  :value="stats.pending_claims" icon="🔖" variant="warning" />
          <AdminStatsCard label="Items Returned"  :value="stats.returned_items" icon="✅" variant="success" />
          <AdminStatsCard label="Items in Custody" :value="stats.in_storage"    icon="🗄️" variant="info" />
        </div>
      </div>

      <!-- Pending Claims To Review -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-sm font-bold text-slate-900">Pending Claims to Review</h2>
            <p class="text-xs text-slate-500 mt-0.5">Claims awaiting identity and ownership verification</p>
          </div>
          <RouterLink to="/staff/review-claims" class="text-xs text-[#0F5132] font-bold hover:underline">
            View All Claims &rarr;
          </RouterLink>
        </div>

        <div v-if="claimsLoading && claims.length === 0" class="space-y-3">
          <AppSkeleton v-for="n in 3" :key="n" height="5rem" class="rounded-2xl" />
        </div>

        <div v-else-if="claims.length === 0" class="p-8 text-center bg-white rounded-2xl border border-slate-200 text-xs text-slate-400">
          No pending claims to review right now. All submissions are processed!
        </div>

        <div v-else class="space-y-4">
          <ClaimCard v-for="claim in claims" :key="claim.id" :claim="claim" />
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
