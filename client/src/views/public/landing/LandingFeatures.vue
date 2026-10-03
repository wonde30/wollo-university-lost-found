<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { t } from '@/i18n'
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

const features = computed(() => [
  {
    id: 'search',
    title: t('home.features.smartSearchTitle'),
    description: t('home.features.smartSearchDesc'),
    linkText: t('home.features.smartSearchAction'),
    icon: Search,
    iconBg: 'bg-[#0B5D3B] text-white shadow-sm',
    linkHover: 'text-[#0B5D3B] dark:text-[#75bd97]',
    action: () => router.push('/browse'),
  },
  {
    id: 'report',
    title: t('home.features.reportTitle'),
    description: t('home.features.reportDesc'),
    linkText: t('home.features.reportAction'),
    icon: Send,
    iconBg: 'bg-[#B7791F] text-white shadow-sm',
    linkHover: 'text-[#B7791F] dark:text-[#D4AF37]',
    action: handleReportClick,
  },
  {
    id: 'track',
    title: t('home.features.trackTitle'),
    description: t('home.features.trackDesc'),
    linkText: t('home.features.trackAction'),
    icon: ShieldCheck,
    iconBg: 'bg-[#084C30] text-white shadow-sm',
    linkHover: 'text-[#0B5D3B] dark:text-[#75bd97]',
    action: () => router.push('/track'),
  },
  {
    id: 'notify',
    title: t('home.features.notifyTitle'),
    description: t('home.features.notifyDesc'),
    linkText: t('home.features.notifyAction'),
    icon: Bell,
    iconBg: 'bg-slate-800 dark:bg-slate-700 text-[#D4AF37] shadow-sm',
    linkHover: 'text-[#0B5D3B] dark:text-[#75bd97]',
    action: () => {
      const el = document.getElementById('how-it-works')
      if (el) el.scrollIntoView({ behavior: 'smooth' })
    },
  },
])
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20 sm:mb-24" aria-labelledby="features-heading">
    <!-- Header -->
    <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-14 space-y-2.5">
      <span class="text-xs font-black uppercase tracking-wider text-[#0B5D3B] dark:text-[#75bd97]">
        {{ t('home.features.tag') }}
      </span>
      <h2 id="features-heading" class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-slate-900 dark:text-white">
        {{ t('home.features.title') }}
      </h2>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
        {{ t('home.features.subtitle') }}
      </p>
    </div>

    <!-- 4 Cohesive Enterprise Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div
        v-for="f in features"
        :key="f.id"
        class="bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 hover:border-[#0B5D3B]/40 dark:hover:border-[#75bd97]/40 shadow-2xs hover:shadow-lg p-6 sm:p-7 rounded-2xl transition-all duration-200 flex flex-col justify-between hover:-translate-y-1 group"
      >
        <div class="space-y-4">
          <!-- Icon -->
          <div :class="['h-11 w-11 rounded-xl flex items-center justify-center shadow-md transition-transform group-hover:scale-105', f.iconBg]">
            <component :is="f.icon" class="h-5 w-5" />
          </div>

          <!-- Title & Desc -->
          <div class="space-y-2">
            <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors">
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
            'mt-6 inline-flex items-center gap-1.5 text-xs font-bold transition-colors group/btn cursor-pointer self-start',
            f.linkHover,
          ]"
          @click="f.action"
        >
          <span>{{ f.linkText }}</span>
          <ArrowRight class="h-3.5 w-3.5 group-hover/btn:translate-x-1 transition-transform" />
        </button>
      </div>
    </div>
  </section>
</template>
