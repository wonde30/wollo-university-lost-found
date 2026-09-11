<script setup lang="ts">
import AppNavbar from '@/components/layout/AppNavbar.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import MobileNav from '@/components/layout/MobileNav.vue'
import AppToast from '@/components/ui/AppToast.vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'
import LoadingOverlay from '@/components/feedback/LoadingOverlay.vue'
import NetworkStatus from '@/components/feedback/NetworkStatus.vue'
import SystemAnnouncementBanner from '@/components/common/SystemAnnouncementBanner.vue'

withDefaults(
  defineProps<{
    fullWidth?: boolean
  }>(),
  {
    fullWidth: false,
  }
)
</script>

<template>
  <div class="min-h-screen flex flex-col bg-white dark:bg-[#0F172A] text-slate-900 dark:text-slate-100 transition-colors duration-150">
    <NetworkStatus />
    <AppNavbar />
    <SystemAnnouncementBanner />
    <MobileNav />

    <main
      :class="[
        'flex-1 w-full animate-fade-in',
        fullWidth ? '' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-6',
      ]"
    >
      <slot />
    </main>

    <AppFooter />
    <AppToast />
    <AppConfirmDialog />
    <LoadingOverlay />
  </div>
</template>
