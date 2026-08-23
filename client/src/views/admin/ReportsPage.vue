<script setup lang="ts">
import { onMounted } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAdminDashboard as useAdminStats } from '@/features/admin/composables/useAdminDashboard'
import AdminStatsCard from '@/features/admin/components/AdminStatsCard.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'

const { stats, loading, fetchStats } = useAdminStats()

onMounted(fetchStats)
</script>

<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-xl font-black text-slate-900">Reports & Analytics</h1>
        <p class="text-sm text-slate-500 mt-0.5">Platform-wide statistics and operational overview.</p>
      </div>

      <!-- Stats Grid -->
      <div v-if="loading && !stats" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <AppSkeleton v-for="n in 8" :key="n" height="6rem" class="rounded-2xl" />
      </div>

      <div v-else-if="stats" class="space-y-8">
        <div>
          <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Item Reports</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <AdminStatsCard label="Total Items" :value="stats.total_items" icon="📦" variant="primary" />
            <AdminStatsCard label="Lost Reports" :value="stats.lost_items" icon="🔴" variant="warning" />
            <AdminStatsCard label="Found Reports" :value="stats.found_items" icon="🟢" variant="success" />
            <AdminStatsCard label="Returned Items" :value="stats.returned_items" icon="✅" variant="success" sublabel="Successfully reunited" />
          </div>
        </div>

        <div>
          <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Claims & Returns</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <AdminStatsCard label="Total Claims" :value="stats.total_claims" icon="🔖" variant="info" />
            <AdminStatsCard label="Pending Claims" :value="stats.pending_claims" icon="⏳" variant="warning" sublabel="Awaiting review" />
            <AdminStatsCard label="Total Returns" :value="stats.total_returns" icon="📤" variant="primary" />
            <AdminStatsCard label="Total Users" :value="stats.total_users" icon="👥" variant="info" />
          </div>
        </div>

        <!-- Recovery Rate -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
          <h2 class="text-sm font-bold text-slate-700 mb-4">Recovery Rate</h2>
          <div class="flex items-center gap-4">
            <div class="flex-1 h-4 bg-slate-100 rounded-full overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-[#0F5132] to-emerald-400 rounded-full transition-all"
                :style="{ width: stats.found_items > 0 ? `${Math.round((stats.returned_items / stats.found_items) * 100)}%` : '0%' }"
              />
            </div>
            <span class="text-lg font-black text-[#0F5132] shrink-0">
              {{ stats.found_items > 0 ? Math.round((stats.returned_items / stats.found_items) * 100) : 0 }}%
            </span>
          </div>
          <p class="text-xs text-slate-400 mt-2">
            {{ stats.returned_items }} of {{ stats.found_items }} found items successfully returned to their owners.
          </p>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
