<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { usePublicItems } from '@/features/lookups/composables/usePublicItems'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import { formatDate } from '@/utils/date'

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

const stats = [
  { label: 'Campus Locations', value: '2 Campuses', desc: 'Dessie & Kombolcha' },
  { label: 'Verified Staff Custody', value: '100% Tracked', desc: 'Vault & Storage Registry' },
  { label: 'Fast Verification', value: '< 24 Hours', desc: 'Claim Decision SLA' },
  { label: 'Safe Handover', value: 'Verified', desc: 'ID & Signature Check' },
]

const howItWorks = [
  {
    step: '01',
    title: 'Report an Item',
    desc: 'Found or lost an item? Fill out our 3-step reporting form with photos, category, and campus location.',
    icon: 'report',
  },
  {
    step: '02',
    title: 'Browse & Match',
    desc: 'Search our real-time database with intelligent filtering by campus, date, and item characteristics.',
    icon: 'search',
  },
  {
    step: '03',
    title: 'Verify & Collect',
    desc: 'Submit proof of ownership. Campus security and staff verify details before coordinating a safe handover.',
    icon: 'handover',
  },
]
</script>

<template>
  <DefaultLayout>
    <!-- Hero Section -->
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0F5132] via-[#0B3822] to-[#04140B] py-16 sm:py-24 px-6 sm:px-12 text-white shadow-xl mb-12">
      <!-- Background pattern & radial glow -->
      <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-[#D4AF37]/15 blur-3xl" />
        <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-[#0F5132]/30 blur-3xl" />
      </div>

      <div class="relative z-10 max-w-4xl mx-auto text-center space-y-6">
        <div class="inline-flex items-center gap-2 rounded-full border border-[#D4AF37]/40 bg-[#D4AF37]/10 px-4 py-1.5 text-xs font-bold text-[#D4AF37]">
          <span class="h-1.5 w-1.5 rounded-full bg-[#D4AF37] animate-pulse" />
          Official Campus Property Recovery System
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black leading-tight tracking-tight text-white">
          Lost Something on Campus?
          <span class="block text-[#D4AF37] mt-1">We'll Help You Reclaim It.</span>
        </h1>

        <p class="text-sm sm:text-base text-slate-200/90 max-w-2xl mx-auto leading-relaxed">
          The verified property registry for Wollo University students, instructors, and staff across Dessie Main Campus and Kombolcha Institute of Technology.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-4">
          <AppButton variant="gold" size="lg" @click="navigateToBrowse">
            <template #icon-left>
              <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </template>
            Browse Found Items
          </AppButton>

          <AppButton
            v-if="authStore.isAuthenticated"
            variant="outline-white"
            size="lg"
            @click="navigateToDashboard"
          >
            Go to My Dashboard &rarr;
          </AppButton>
          <AppButton
            v-else
            variant="outline-white"
            size="lg"
            @click="router.push('/auth/register')"
          >
            Create Student Account
          </AppButton>
        </div>

        <div class="pt-4">
          <button
            type="button"
            class="text-xs text-white/60 hover:text-white underline transition cursor-pointer"
            @click="navigateToTrack"
          >
            Have a reference code? Track status directly &rarr;
          </button>
        </div>
      </div>
    </section>

    <!-- Stats Banner -->
    <section class="mb-14">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs text-center sm:text-left"
        >
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">
            {{ stat.label }}
          </span>
          <p class="text-xl sm:text-2xl font-extrabold text-[#0F5132]">
            {{ stat.value }}
          </p>
          <p class="text-xs text-slate-500 mt-1 font-medium">{{ stat.desc }}</p>
        </div>
      </div>
    </section>

    <!-- Recent Found Items Grid -->
    <section class="mb-16">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-xl sm:text-2xl font-black text-slate-900">Recently Found Items</h2>
          <p class="text-xs text-slate-500 mt-0.5">Discovered on campus and awaiting verified owner retrieval</p>
        </div>
        <RouterLink
          to="/browse"
          class="text-xs font-bold text-[#0F5132] hover:underline inline-flex items-center gap-1"
        >
          View All Items &rarr;
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
          class="group rounded-2xl bg-white border border-slate-200/80 p-4 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between cursor-pointer"
          @click="router.push(`/items/${item.id}`)"
        >
          <!-- Thumbnail image or placeholder -->
          <div class="h-44 rounded-xl bg-slate-100 mb-3 overflow-hidden relative border border-slate-200/60">
            <img
              v-if="item.primary_photo?.photo_url || (item.photos && item.photos[0]?.photo_url)"
              :src="item.primary_photo?.photo_url || item.photos?.[0]?.photo_url"
              :alt="item.title"
              class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
              loading="lazy"
            />
            <div v-else class="h-full w-full flex flex-col items-center justify-center text-slate-300">
              <svg class="h-10 w-10 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span class="text-[10px] font-semibold text-slate-400">No Photo Attached</span>
            </div>

            <!-- Type badge overlay -->
            <div class="absolute top-2.5 left-2.5">
              <AppBadge :variant="item.type === 'found' ? 'success' : 'warning'" size="sm">
                {{ item.type === 'found' ? 'Found Property' : 'Lost Report' }}
              </AppBadge>
            </div>
          </div>

          <!-- Info -->
          <div class="space-y-1.5 flex-1">
            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium">
              <span>{{ item.category?.name || 'General' }}</span>
              <span>{{ formatDate(item.incident_date) }}</span>
            </div>
            <h3 class="text-sm font-bold text-slate-900 group-hover:text-[#0F5132] transition-colors line-clamp-1">
              {{ item.title }}
            </h3>
            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
              {{ item.description }}
            </p>
          </div>

          <!-- Location Footer -->
          <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-xs">
            <div class="flex items-center gap-1.5 text-slate-500 truncate">
              <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              </svg>
              <span class="truncate">{{ item.location?.name || item.campus?.name || 'Campus Grounds' }}</span>
            </div>

            <span class="text-[#0F5132] font-bold text-xs shrink-0 group-hover:translate-x-0.5 transition-transform">
              View &rarr;
            </span>
          </div>
        </div>
      </div>

      <div v-else class="p-8 text-center bg-white rounded-2xl border border-slate-200">
        <p class="text-xs text-slate-500">No recent public items found at the moment.</p>
      </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-12 px-6 sm:px-10 rounded-3xl bg-white border border-slate-200/80 mb-14">
      <div class="max-w-4xl mx-auto text-center mb-10">
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2">How Campus Recovery Works</h2>
        <p class="text-xs sm:text-sm text-slate-500 max-w-lg mx-auto">
          A seamless 3-step digital protocol designed for speed, security, and verified student identity.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="step in howItWorks"
          :key="step.step"
          class="relative bg-slate-50/80 rounded-2xl p-6 border border-slate-200/60 flex flex-col justify-between"
        >
          <div class="flex items-center justify-between mb-4">
            <span class="text-2xl font-black text-[#0F5132]">{{ step.step }}</span>
            <div class="h-9 w-9 rounded-xl bg-emerald-100/60 text-[#0F5132] flex items-center justify-center font-bold">
              <svg v-if="step.icon === 'report'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <svg v-else-if="step.icon === 'search'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <h3 class="text-base font-bold text-slate-900 mb-1.5">{{ step.title }}</h3>
          <p class="text-xs text-slate-600 leading-relaxed">{{ step.desc }}</p>
        </div>
      </div>
    </section>

    <!-- Bottom CTA -->
    <section v-if="!authStore.isAuthenticated" class="rounded-3xl bg-gradient-to-r from-[#0F5132] to-[#0B3822] p-8 sm:p-12 text-center text-white space-y-5">
      <h2 class="text-2xl sm:text-3xl font-black">Get Started with Wollo Lost & Found</h2>
      <p class="text-xs sm:text-sm text-slate-200 max-w-xl mx-auto leading-relaxed">
        Register using your Wollo University email to report lost property, claim found items, and receive real-time notifications.
      </p>
      <div class="flex items-center justify-center gap-3 pt-2">
        <AppButton variant="gold" size="lg" @click="router.push('/auth/register')">
          Register Student Account
        </AppButton>
      </div>
    </section>
  </DefaultLayout>
</template>
