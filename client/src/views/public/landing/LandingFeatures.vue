<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import {
  Search,
  Send,
  ShieldCheck,
  Bell,
  ArrowRight,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

function handleReportClick() {
  if (authStore.isAuthenticated) {
    router.push('/report-lost')
  } else {
    router.push({ name: 'login', query: { redirect: '/report-lost' } })
  }
}

const features = [
  {
    id: 'search',
    title: 'Smart Search',
    description: 'Find lost or found items using advanced search filters, images, and AI-powered matching.',
    linkText: 'Search Items',
    icon: Search,
    iconBg: 'bg-[#0B5D3B] text-white shadow-sm',
    cardBg: 'bg-[#E8F4EE]/60 dark:bg-[#153C2D]/30 border-[#0B5D3B]/20 dark:border-[#0B5D3B]/40',
    linkHover: 'text-[#0B5D3B] dark:text-[#75bd97]',
    action: () => router.push('/browse'),
  },
  {
    id: 'report',
    title: 'Report Items',
    description: 'Report lost or found items in minutes with photos and detailed descriptions.',
    linkText: 'Report Now',
    icon: Send,
    iconBg: 'bg-[#B7791F] text-white shadow-sm',
    cardBg: 'bg-amber-50/60 dark:bg-amber-950/25 border-amber-200/60 dark:border-amber-800/40',
    linkHover: 'text-[#B7791F] dark:text-amber-400',
    action: handleReportClick,
  },
  {
    id: 'track',
    title: 'Track Progress',
    description: 'Get real-time updates on your reports and claims through your dashboard.',
    linkText: 'Track Item',
    icon: ShieldCheck,
    iconBg: 'bg-emerald-700 text-white shadow-sm',
    cardBg: 'bg-emerald-50/60 dark:bg-emerald-950/25 border-emerald-200/60 dark:border-emerald-800/40',
    linkHover: 'text-emerald-700 dark:text-emerald-400',
    action: () => router.push('/track'),
  },
  {
    id: 'notify',
    title: 'Get Notified',
    description: 'Receive instant notifications when matches are found or status changes.',
    linkText: 'Learn More',
    icon: Bell,
    iconBg: 'bg-slate-800 dark:bg-slate-700 text-[#D4AF37] shadow-sm',
    cardBg: 'bg-slate-50/80 dark:bg-slate-900/50 border-slate-200 dark:border-slate-800',
    linkHover: 'text-[#0B5D3B] dark:text-[#75bd97]',
    action: () => {
      const el = document.getElementById('how-it-works')
      if (el) el.scrollIntoView({ behavior: 'smooth' })
    },
  },
]
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20 sm:mb-24">
    <!-- Header -->
    <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-14 space-y-2.5">
      <span class="text-xs font-black uppercase tracking-wider text-[#0B5D3B] dark:text-[#75bd97]">
        OUR FEATURES
      </span>
      <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-slate-900 dark:text-white">
        A Smarter Way to Find What Matters
      </h2>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
        Simple, secure, and efficient tools to help our university community.
      </p>
    </div>

    <!-- 4 Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div
        v-for="f in features"
        :key="f.id"
        :class="[
          'p-6 sm:p-7 rounded-2xl border transition-all duration-200 flex flex-col justify-between hover:shadow-lg hover:-translate-y-1',
          f.cardBg,
        ]"
      >
        <div class="space-y-4">
          <!-- Icon -->
          <div :class="['h-11 w-11 rounded-xl flex items-center justify-center shadow-md', f.iconBg]">
            <component :is="f.icon" class="h-5 w-5" />
          </div>

          <!-- Title & Desc -->
          <div class="space-y-2">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">
              {{ f.title }}
            </h3>
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
              {{ f.description }}
            </p>
          </div>
        </div>

        <!-- Action Link -->
        <button
          type="button"
          :class="[
            'mt-6 inline-flex items-center gap-1.5 text-xs font-bold transition-colors group cursor-pointer self-start',
            f.linkHover,
          ]"
          @click="f.action"
        >
          <span>{{ f.linkText }}</span>
          <ArrowRight class="h-3.5 w-3.5 group-hover:translate-x-1 transition-transform" />
        </button>
      </div>
    </div>
  </section>
</template>
