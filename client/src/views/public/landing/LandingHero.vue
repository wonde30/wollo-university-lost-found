<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useSettingsStore } from '@/stores/settings.store'
import AppButton from '@/components/ui/AppButton.vue'
import {
  Search,
  FilePlus2,
  ShieldCheck,
  Users,
  Radio,
  Sparkles,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const settingsStore = useSettingsStore()

function handleReportClick() {
  if (authStore.isAuthenticated) {
    router.push('/report-lost')
  } else {
    router.push({ name: 'login', query: { redirect: '/report-lost' } })
  }
}
</script>

<template>
  <section class="relative overflow-hidden text-white shadow-lg mb-12 sm:mb-16 w-full">
    <!-- Real Campus Background Image -->
    <div class="absolute inset-0 z-0">
      <img
        src="/images/campus-gate.jpg"
        :alt="settingsStore.institutionName + ' Main Gate'"
        class="w-full h-full object-cover object-center filter brightness-[0.9] scale-105 transform hover:scale-100 transition-transform duration-1000"
      />
      <!-- Cinematic Multi-stop Dark Gradient Overlay -->
      <div class="absolute inset-0 bg-gradient-to-r from-slate-950/95 via-slate-900/80 to-slate-900/40" />
      <div class="absolute inset-0 bg-radial-at-tl from-[#0B5D3B]/40 via-transparent to-black/70" />
    </div>

    <!-- Content Grid -->
    <div class="relative z-10 max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 py-16 sm:py-20 lg:py-24 pb-20 sm:pb-24">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
        <!-- Left: Hero Headline & Actions (7 cols) -->
        <div class="lg:col-span-8 space-y-6 text-left">
          <!-- Top Badge -->
          <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-[#D4AF37]/50 bg-[#D4AF37]/15 backdrop-blur-md px-3.5 py-1 text-xs font-bold text-[#D4AF37] shadow-sm">
              <Sparkles class="h-3.5 w-3.5 text-[#D4AF37] animate-pulse" />
              Together for a Safer Campus
            </span>
          </div>

          <!-- Headline -->
          <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black leading-[1.08] tracking-tight text-white drop-shadow-md">
            Lost Something?<br />
            You're <span class="text-[#D4AF37] underline decoration-[#D4AF37]/40 underline-offset-8">Not Alone.</span>
          </h1>

          <!-- Subtitle -->
          <p class="text-sm sm:text-base text-slate-200/95 max-w-2xl leading-relaxed drop-shadow-sm">
            {{ settingsStore.institutionName }} Lost &amp; Found helps students, staff, and visitors reunite with their lost items. Report, search, and track items easily and securely.
          </p>

          <!-- CTAs -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
            <AppButton
              variant="gold"
              size="lg"
              class="font-bold border-0 shadow-lg shadow-black/40 transition-all transform hover:-translate-y-0.5"
              @click="handleReportClick"
            >
              <template #icon-left>
                <FilePlus2 class="h-5 w-5 mr-1" />
              </template>
              Report Lost Item
            </AppButton>

            <AppButton
              variant="outline-white"
              size="lg"
              class="font-bold border-white/40 bg-white/10 hover:bg-white/20 text-white backdrop-blur-sm shadow-md transition-all transform hover:-translate-y-0.5"
              @click="router.push('/browse')"
            >
              <template #icon-left>
                <Search class="h-5 w-5 mr-1 text-slate-200" />
              </template>
              Browse Found Items
            </AppButton>
          </div>

          <!-- Trust Badges -->
          <div class="flex flex-wrap items-center gap-x-6 gap-y-2 pt-3 text-xs text-slate-300 font-semibold drop-shadow">
            <span class="inline-flex items-center gap-1.5">
              <ShieldCheck class="h-4 w-4 text-[#75bd97]" />
              Secure &amp; Reliable
            </span>
            <span class="inline-flex items-center gap-1.5">
              <Users class="h-4 w-4 text-[#D4AF37]" />
              University Community
            </span>
            <span class="inline-flex items-center gap-1.5">
              <Radio class="h-4 w-4 text-emerald-400" />
              Real-time Updates
            </span>
          </div>
        </div>

        <!-- Right: Quote Card Overlay (4 cols) -->
        <div class="lg:col-span-4 hidden lg:flex justify-end">
          <div class="bg-slate-900/70 backdrop-blur-md p-6 rounded-2xl border border-white/15 max-w-xs shadow-2xl space-y-2.5">
            <p class="text-sm font-medium italic text-slate-100 leading-relaxed">
              "Building a more caring and connected university community."
            </p>
            <p class="text-xs font-bold text-[#D4AF37] tracking-wide uppercase">
              — {{ settingsStore.institutionName }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
