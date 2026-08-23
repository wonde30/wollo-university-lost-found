<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useItemsStore } from '@/features/items/stores/items.store'
import { useClaimsStore } from '@/features/claims/stores/claims.store'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import ItemCard from '@/features/items/components/ItemCard.vue'
import AppButton from '@/components/ui/AppButton.vue'

const router = useRouter()
const authStore = useAuthStore()
const itemsStore = useItemsStore()
const claimsStore = useClaimsStore()

const loadingDashboard = ref(true)

onMounted(async () => {
  loadingDashboard.value = true
  try {
    // Parallelize loading stats & items
    await Promise.all([
      itemsStore.fetchItems({ per_page: 4 }, true),
      claimsStore.fetchClaims({ per_page: 5 }, true),
    ])
  } catch {
    // Non-blocking
  } finally {
    loadingDashboard.value = false
  }
})

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12) return 'Good morning'
  if (hour < 18) return 'Good afternoon'
  return 'Good evening'
})

const myLostCount = computed(() => {
  return itemsStore.items.filter(i => i.type === 'lost').length
})

const myFoundCount = computed(() => {
  return itemsStore.items.filter(i => i.type === 'found').length
})

const myClaimsCount = computed(() => {
  return claimsStore.claims.length
})

const quickLinks = [
  { label: 'Report Lost Item', to: '/student/report-lost', icon: 'lost', color: 'bg-amber-600' },
  { label: 'Report Found Item', to: '/student/report-found', icon: 'found', color: 'bg-[#0F5132]' },
  { label: 'Browse Campus Registry', to: '/browse', icon: 'browse', color: 'bg-sky-600' },
  { label: 'My Ownership Claims', to: '/student/my-claims', icon: 'claims', color: 'bg-indigo-600' },
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
              Student Property Dashboard
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white">
              {{ greeting }}, {{ authStore.user?.full_name?.split(' ')[0] || 'Student' }} 👋
            </h1>
            <p class="text-xs sm:text-sm text-slate-200/90 mt-1 max-w-xl leading-relaxed">
              Track your reported belongings, submit ownership claims, and check verification status across all campus offices.
            </p>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <AppButton variant="gold" size="sm" @click="router.push('/student/report-lost')">
              + Report Lost Item
            </AppButton>
          </div>
        </div>
      </div>

      <!-- Live Metrics Overview Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">My Lost Reports</span>
            <p class="text-2xl font-black text-slate-900">{{ loadingDashboard ? '—' : myLostCount }}</p>
            <RouterLink to="/student/my-items" class="text-xs text-[#0F5132] font-semibold hover:underline mt-1 inline-block">
              View my lost items &rarr;
            </RouterLink>
          </div>
          <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">My Found Reports</span>
            <p class="text-2xl font-black text-slate-900">{{ loadingDashboard ? '—' : myFoundCount }}</p>
            <RouterLink to="/student/my-items" class="text-xs text-[#0F5132] font-semibold hover:underline mt-1 inline-block">
              View my found reports &rarr;
            </RouterLink>
          </div>
          <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-[#0F5132] flex items-center justify-center font-bold">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Active Claims</span>
            <p class="text-2xl font-black text-slate-900">{{ loadingDashboard ? '—' : myClaimsCount }}</p>
            <RouterLink to="/student/my-claims" class="text-xs text-[#0F5132] font-semibold hover:underline mt-1 inline-block">
              Track claim status &rarr;
            </RouterLink>
          </div>
          <div class="h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Quick Action Cards -->
      <div>
        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <RouterLink
            v-for="link in quickLinks"
            :key="link.to"
            :to="link.to"
            class="flex flex-col items-center justify-center gap-2.5 rounded-2xl border border-slate-200/80 bg-white p-5 text-center shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all group"
          >
            <div :class="`h-12 w-12 rounded-2xl ${link.color} flex items-center justify-center text-white shadow-xs group-hover:scale-105 transition-transform`">
              <svg v-if="link.icon === 'lost'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <svg v-else-if="link.icon === 'found'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <svg v-else-if="link.icon === 'browse'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <span class="text-xs font-bold text-slate-700 group-hover:text-[#0F5132] transition-colors">{{ link.label }}</span>
          </RouterLink>
        </div>
      </div>

      <!-- Recent Reported Items List -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-sm font-bold text-slate-900">Recent Campus Items</h2>
            <p class="text-xs text-slate-500 mt-0.5">Your latest reports and matches</p>
          </div>
          <RouterLink to="/student/my-items" class="text-xs text-[#0F5132] font-bold hover:underline">
            Manage All My Items &rarr;
          </RouterLink>
        </div>

        <div v-if="itemsStore.loading && itemsStore.items.length === 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div v-for="n in 4" :key="n" class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
            <AppSkeleton height="9rem" class="rounded-xl" />
            <AppSkeleton height="1rem" width="60%" />
            <AppSkeleton height="0.875rem" />
          </div>
        </div>

        <div v-else-if="itemsStore.items.length === 0" class="text-center py-12 bg-white rounded-2xl border border-slate-200 text-xs text-slate-400 space-y-3">
          <p>No reported items yet. When you report a lost or found item, it will appear here.</p>
          <AppButton variant="primary" size="sm" @click="router.push('/student/report-lost')">
            Report an Item Now
          </AppButton>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <ItemCard v-for="item in itemsStore.items.slice(0, 4)" :key="item.id" :item="item" />
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>
