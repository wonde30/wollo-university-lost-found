<script setup lang="ts">
import AppToast from '@/components/ui/AppToast.vue'
import LoadingOverlay from '@/components/feedback/LoadingOverlay.vue'
import NetworkStatus from '@/components/feedback/NetworkStatus.vue'
import ThemeToggle from '@/components/layout/ThemeToggle.vue'
import LanguageSwitcher from '@/components/layout/LanguageSwitcher.vue'
import { t } from '@/i18n'
import { ArrowLeft } from 'lucide-vue-next'

interface Props {
  title?: string
  subtitle?: string
}

defineProps<Props>()
</script>

<template>
  <div class="min-h-screen flex flex-col justify-between bg-white dark:bg-[#0F172A] text-slate-900 dark:text-slate-100 transition-colors duration-150">
    <NetworkStatus />

    <!-- Top Navigation Bar -->
    <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <RouterLink to="/" class="inline-flex items-center gap-2.5 group">
        <img
          src="/images/wu-logo.png"
          :alt="t('common.universityEmblem')"
          class="h-9 w-9 shrink-0 object-contain rounded-full bg-white dark:bg-slate-800 shadow-2xs ring-1 ring-[#0B5D3B]/40 p-0.5 group-hover:scale-105 transition-transform"
        />
        <div class="flex flex-col text-left">
          <span class="text-sm font-black tracking-tight text-slate-900 dark:text-white leading-none">WOLLO UNIVERSITY</span>
          <span class="text-[9px] font-bold tracking-wider text-[#0B5D3B] dark:text-[#75bd97] uppercase mt-0.5">{{ t('common.portalTitle') }}</span>
        </div>
      </RouterLink>

      <div class="flex items-center gap-2">
        <LanguageSwitcher />
        <ThemeToggle />
      </div>
    </header>

    <!-- Main Centered Form Card -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 my-auto animate-fade-in">
      <div class="w-full max-w-md bg-white dark:bg-[#111827] p-5 sm:p-7 rounded-2xl shadow-md border border-slate-200/90 dark:border-slate-800 transition-colors duration-150">
        <!-- Title & Subtitle -->
        <div v-if="title || subtitle" class="text-center mb-5">
          <h1 v-if="title" class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-snug">
            {{ title }}
          </h1>
          <p v-if="subtitle" class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed font-medium">
            {{ subtitle }}
          </p>
        </div>

        <!-- Form Slot -->
        <div class="space-y-4">
          <slot />
        </div>

        <!-- Footer Slot -->
        <div v-if="$slots.footer" class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800/80 text-xs">
          <slot name="footer" />
        </div>
      </div>
    </main>

    <!-- Institutional Footer -->
    <footer class="py-5 px-4 text-center text-xs text-slate-400 dark:text-slate-500 space-y-1.5">
      <div>
        <RouterLink to="/" class="hover:text-slate-700 dark:hover:text-slate-300 transition-colors inline-flex items-center gap-1 font-medium text-xs dark:text-slate-300">
          <ArrowLeft class="h-3.5 w-3.5" />
          <span>{{ t('common.back') }} to {{ t('nav.home') }}</span>
        </RouterLink>
      </div>
      <p class="text-[11px]">&copy; {{ new Date().getFullYear() }} {{ t('common.wolloUniversity') }} &bull; {{ t('common.portalTitle') }}</p>
    </footer>

    <AppToast />
    <LoadingOverlay />
  </div>
</template>
