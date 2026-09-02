<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { usePublicItems } from '@/features/lookups/composables/usePublicItems'
import { t } from '@/i18n'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import { formatDate } from '@/utils/date'
import {
  Search,
  FilePlus2,
  Handshake,
  ArrowRight,
  MapPin,
  ImageOff,
  Sparkles,
  Building2,
  ShieldCheck,
  Clock,
  CheckCircle2,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const { items: recentItems, loading: loadingItems, fetchItems } = usePublicItems()

onMounted(async () => {
  try {
    await fetchItems({ type: 'found', per_page: 6 })
  } catch {
    // Graceful fallback
  }
})

function navigateToBrowse() {
  router.push('/browse')
}

function navigateToTrack() {
  router.push('/track')
}

function navigateToDashboard() {
  if (authStore.isAdmin) router.push('/admin/dashboard')
  else if (authStore.isStaff) router.push('/staff/dashboard')
  else router.push('/student/dashboard')
}

const stats = computed(() => [
  { label: t('home.stats.campuses'), value: '2 Campuses', desc: t('home.stats.dessieKombolcha'), icon: Building2 },
  { label: t('home.stats.verifiedCustody'), value: '100% Tracked', desc: t('home.stats.vaultStorage'), icon: ShieldCheck },
  { label: t('home.stats.fastVerification'), value: '< 24 Hours', desc: t('home.stats.claimSLA'), icon: Clock },
  { label: t('home.stats.safeHandover'), value: 'Verified', desc: t('home.stats.idCheck'), icon: CheckCircle2 },
])

const howItWorks = computed(() => [
  {
    step: '01',
    title: t('home.howItWorks.step1Title'),
    desc: t('home.howItWorks.step1Desc'),
    icon: FilePlus2,
  },
  {
    step: '02',
    title: t('home.howItWorks.step2Title'),
    desc: t('home.howItWorks.step2Desc'),
    icon: Search,
  },
  {
    step: '03',
    title: t('home.howItWorks.step3Title'),
    desc: t('home.howItWorks.step3Desc'),
    icon: Handshake,
  },
])
</script>

<template>
  <DefaultLayout>
    <!-- Hero Section -->
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0B5D3B] via-[#084C30] to-[#063D27] py-12 sm:py-16 px-6 sm:px-10 text-white shadow-lg mb-8 border border-[#0B5D3B]/40">
      <!-- Subtle background radial lighting -->
      <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-[#D4AF37]/10 blur-3xl" />
        <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-[#0B5D3B]/40 blur-3xl" />
      </div>

      <div class="relative z-10 max-w-4xl mx-auto text-center space-y-5">
        <div class="inline-flex items-center gap-2 rounded-full border border-[#D4AF37]/40 bg-[#D4AF37]/15 px-3.5 py-1 text-xs font-bold text-[#D4AF37]">
          <Sparkles class="h-3.5 w-3.5 text-[#D4AF37] animate-pulse" />
          {{ t('home.hero.badge') }}
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black leading-tight tracking-tight text-white">
          {{ t('home.hero.title') }}
          <span class="block text-[#D4AF37] mt-1">{{ t('home.hero.subtitle') }}</span>
        </h1>

        <p class="text-sm sm:text-base text-slate-200/90 max-w-2xl mx-auto leading-relaxed">
          {{ t('home.hero.description') }}
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
          <AppButton variant="gold" size="lg" @click="navigateToBrowse">
            <template #icon-left>
              <Search class="h-5 w-5 mr-1" />
            </template>
            {{ t('home.hero.browseBtn') }}
          </AppButton>

          <AppButton
            v-if="authStore.isAuthenticated"
            variant="outline-white"
            size="lg"
            @click="navigateToDashboard"
          >
            <template #icon-right>
              <ArrowRight class="h-4 w-4 ml-1" />
            </template>
            {{ t('home.hero.dashboardBtn') }}
          </AppButton>
          <AppButton
            v-else
            variant="outline-white"
            size="lg"
            @click="router.push('/auth/register')"
          >
            <template #icon-right>
              <ArrowRight class="h-4 w-4 ml-1" />
            </template>
            {{ t('home.hero.reportBtn') }}
          </AppButton>
        </div>

        <div class="pt-2">
          <button
            type="button"
            class="text-xs text-white/80 hover:text-white underline transition cursor-pointer inline-flex items-center gap-1.5 font-bold"
            @click="navigateToTrack"
          >
            <span>{{ t('home.hero.trackBtn') }}</span>
            <ArrowRight class="h-3 w-3" />
          </button>
        </div>
      </div>
    </section>

    <!-- Stats Banner -->
    <section class="mb-10">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs text-center sm:text-left transition-colors duration-150"
        >
          <div class="flex items-center justify-center sm:justify-between mb-1.5">
            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">
              {{ stat.label }}
            </span>
            <component :is="stat.icon" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97] hidden sm:block" />
          </div>
          <p class="text-2xl sm:text-3xl font-black text-[#0B5D3B] dark:text-[#75bd97] tracking-tight">
            {{ stat.value }}
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">{{ stat.desc }}</p>
        </div>
      </div>
    </section>

    <!-- Recent Found Items Grid -->
    <section class="mb-16">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">{{ t('home.recentItems.title') }}</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ t('home.recentItems.subtitle') }}</p>
        </div>
        <RouterLink
          to="/browse"
          class="text-xs font-bold text-[#0B5D3B] dark:text-[#75bd97] hover:underline inline-flex items-center gap-1"
        >
          {{ t('home.recentItems.viewAll') }}
          <ArrowRight class="h-3.5 w-3.5" />
        </RouterLink>
      </div>

      <!-- Loading Skeletons -->
      <div v-if="loadingItems && recentItems.length === 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <AppSkeleton v-for="n in 6" :key="n" preset="card" />
      </div>

      <!-- Items Grid -->
      <div v-else-if="recentItems.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="item in recentItems.slice(0, 6)"
          :key="item.id"
          class="group rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 p-4 shadow-2xs hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700 hover:-translate-y-0.5 transition-all flex flex-col justify-between cursor-pointer"
          @click="router.push(`/items/${item.id}`)"
        >
          <!-- Thumbnail image or placeholder -->
          <div class="h-44 rounded-xl bg-slate-100 dark:bg-slate-800 mb-3 overflow-hidden relative border border-slate-200/60 dark:border-slate-700/60">
            <img
              v-if="item.primary_photo?.photo_url || (item.photos && item.photos[0]?.photo_url)"
              :src="item.primary_photo?.photo_url || item.photos?.[0]?.photo_url"
              :alt="item.title"
              class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
              loading="lazy"
            />
            <div v-else class="h-full w-full flex flex-col items-center justify-center text-slate-300 dark:text-slate-600">
              <ImageOff class="h-10 w-10 mb-1 text-slate-300 dark:text-slate-600" />
              <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500">No Photo Attached</span>
            </div>

            <!-- Type badge overlay -->
            <div class="absolute top-2.5 left-2.5">
              <AppBadge :variant="item.type === 'found' ? 'success' : 'warning'" size="sm">
                {{ item.type === 'found' ? t('items.types.found') : t('items.types.lost') }}
              </AppBadge>
            </div>
          </div>

          <!-- Info -->
          <div class="space-y-1.5 flex-1">
            <div class="flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500 font-medium">
              <span>{{ item.category?.name || 'General' }}</span>
              <span>{{ formatDate(item.incident_date) }}</span>
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors line-clamp-1">
              {{ item.title }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
              {{ item.description }}
            </p>
          </div>

          <!-- Location Footer -->
          <div class="pt-3 mt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400 truncate">
              <MapPin class="h-3.5 w-3.5 text-slate-400 dark:text-slate-500 shrink-0" />
              <span class="truncate">{{ item.location?.name || item.campus?.name || 'Campus Grounds' }}</span>
            </div>

            <span class="text-[#0B5D3B] dark:text-[#75bd97] font-bold text-xs shrink-0 group-hover:translate-x-0.5 transition-transform inline-flex items-center gap-0.5">
              {{ t('common.view') }}
              <ArrowRight class="h-3 w-3" />
            </span>
          </div>
        </div>
      </div>

      <div v-else class="p-8 text-center bg-white dark:bg-[#111827] rounded-2xl border border-slate-200 dark:border-slate-800">
        <p class="text-xs text-slate-500 dark:text-slate-400">{{ t('common.noData') }}</p>
      </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-12 px-6 sm:px-10 rounded-3xl bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 mb-14 transition-colors duration-150">
      <div class="max-w-4xl mx-auto text-center mb-10">
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mb-2">{{ t('home.howItWorks.title') }}</h2>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-lg mx-auto">
          {{ t('home.howItWorks.subtitle') }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="step in howItWorks"
          :key="step.step"
          class="relative bg-slate-50/80 dark:bg-slate-900/60 rounded-2xl p-6 border border-slate-200/60 dark:border-slate-800 flex flex-col justify-between space-y-4"
        >
          <div class="flex items-center justify-between">
            <span class="text-2xl font-black text-[#0B5D3B] dark:text-[#75bd97]">{{ step.step }}</span>
            <div class="h-10 w-10 rounded-xl bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] flex items-center justify-center font-bold">
              <component :is="step.icon" class="h-5 w-5" />
            </div>
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1.5">{{ step.title }}</h3>
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Bottom CTA -->
    <section v-if="!authStore.isAuthenticated" class="rounded-3xl bg-gradient-to-r from-[#0B5D3B] to-[#084C30] p-8 sm:p-12 text-center text-white space-y-5 border border-[#0B5D3B]/30">
      <h2 class="text-2xl sm:text-3xl font-black">{{ t('home.cta.title') }}</h2>
      <p class="text-xs sm:text-sm text-slate-200 max-w-xl mx-auto leading-relaxed">
        {{ t('home.cta.subtitle') }}
      </p>
      <div class="flex items-center justify-center gap-3 pt-2">
        <AppButton variant="gold" size="lg" @click="router.push('/auth/register')">
          {{ t('home.cta.registerBtn') }}
        </AppButton>
      </div>
    </section>
  </DefaultLayout>
</template>
