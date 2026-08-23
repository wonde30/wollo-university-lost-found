<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useAdminDashboard as useAdminStats } from '@/features/admin/composables/useAdminDashboard'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import AdminStatsCard from '@/features/admin/components/AdminStatsCard.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import AppButton from '@/components/ui/AppButton.vue'

const router = useRouter()
const authStore = useAuthStore()
const { stats, loading, fetchStats } = useAdminStats()

onMounted(fetchStats)

const greeting = computed(() => {
  const h = new Date().getHours()
  return h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening'
})

const recoveryRate = computed(() => {
  if (!stats.value || stats.value.total_items === 0) return 0
  return Math.round((stats.value.returned_items / stats.value.total_items) * 100)
})

const quickActions = [
  { label: 'User Directory', to: '/admin/users', icon: 'users', color: 'bg-blue-600' },
  { label: 'Campus Registry', to: '/admin/campuses', icon: 'campuses', color: 'bg-[#0F5132]' },
  { label: 'Category Settings', to: '/admin/categories', icon: 'categories', color: 'bg-amber-600' },
  { label: 'Security Audit Logs', to: '/admin/audit-logs', icon: 'audit', color: 'bg-indigo-600' },
]
</script>

<template>
  <DashboardLayout>
    <div class="space-y-8">
      <!-- Admin Hero Banner -->
      <div class="rounded-3xl bg-gradient-to-br from-[#0F5132] via-[#0B3822] to-[#04140B] p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-[#D4AF37]/15 rounded-full blur-2xl pointer-events-none" />

        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-bold bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/30 mb-2">
              System Administration
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white">
              {{ greeting }}, {{ authStore.user?.full_name || 'Administrator' }} 👋
            </h1>
            <p class="text-xs sm:text-sm text-slate-200/90 mt-1 max-w-xl leading-relaxed">
              Full platform oversight, ISO compliance monitoring, user access control, and institutional property analytics.
            </p>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <AppButton variant="gold" size="sm" @click="router.push('/admin/reports')">
              Generate System Report
            </AppButton>
          </div>
        </div>
      </div>

      <!-- Quick Action Cards -->
      <div>
        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Administration Hub</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <RouterLink
            v-for="action in quickActions"
            :key="action.to"
            :to="action.to"
            class="flex flex-col items-center gap-2.5 rounded-2xl border border-slate-200/80 bg-white p-5 text-center shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all group"
          >
            <div :class="`h-12 w-12 rounded-2xl ${action.color} flex items-center justify-center text-white shadow-xs group-hover:scale-105 transition-transform`">
              <svg v-if="action.icon === 'users'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <svg v-else-if="action.icon === 'campuses'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <svg v-else-if="action.icon === 'categories'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
              </svg>
              <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <span class="text-xs font-bold text-slate-700 group-hover:text-[#0F5132] transition-colors">{{ action.label }}</span>
          </RouterLink>
        </div>
      </div>

      <!-- Stats Grid -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cached Metrics Overview (5-min TTL)</h2>
          <AppButton variant="ghost" size="xs" :loading="loading" @click="fetchStats(true)">
            Refresh Live
          </AppButton>
        </div>

        <div v-if="loading && !stats" class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <AppSkeleton v-for="n in 8" :key="n" height="6rem" class="rounded-2xl" />
        </div>
        <div v-else-if="stats" class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <AdminStatsCard label="Total Items Reported" :value="stats.total_items" icon="📦" variant="primary" />
          <AdminStatsCard label="Active Lost Items" :value="stats.lost_items" icon="🔴" variant="warning" />
          <AdminStatsCard label="Found in Custody" :value="stats.found_items" icon="🟢" variant="success" />
          <AdminStatsCard label="Items Returned to Owners" :value="stats.returned_items" icon="✅" variant="success" />
          <AdminStatsCard label="Total Claims Filed" :value="stats.total_claims" icon="🔖" variant="info" />
          <AdminStatsCard label="Pending Claims" :value="stats.pending_claims" icon="⏳" variant="warning" />
          <AdminStatsCard label="Total Handovers" :value="stats.total_returns" icon="📤" variant="primary" />
          <AdminStatsCard label="Registered Users" :value="stats.total_users" icon="👥" variant="info" />
        </div>
      </div>

      <!-- Analytics Breakdown (Visual Bar Chart) -->
      <div v-if="stats" class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-bold text-slate-900">Campus Recovery Efficiency</h3>
            <p class="text-xs text-slate-500 mt-0.5">Ratio of recovered and returned property across university offices</p>
          </div>
          <span class="text-xl font-black text-[#0F5132]">{{ recoveryRate }}% Reclaimed</span>
        </div>

        <!-- Visual Progress Bar -->
        <div class="space-y-2">
          <div class="h-3 w-full rounded-full bg-slate-100 overflow-hidden flex">
            <div
              class="h-full bg-[#0F5132] transition-all duration-500"
              :style="{ width: `${recoveryRate}%` }"
            />
            <div
              class="h-full bg-amber-500 transition-all duration-500"
              :style="{ width: `${100 - recoveryRate}%` }"
            />
          </div>
          <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium">
            <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-[#0F5132]" /> Returned to Owner ({{ stats.returned_items }})</span>
            <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-500" /> Pending / In Custody ({{ stats.total_items - stats.returned_items }})</span>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
