<script setup lang="ts">
import AppNavbar from '@/components/layout/AppNavbar.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import MobileNav from '@/components/layout/MobileNav.vue'
import AppToast from '@/components/ui/AppToast.vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'
import LoadingOverlay from '@/components/feedback/LoadingOverlay.vue'
import NetworkStatus from '@/components/feedback/NetworkStatus.vue'
</script>

<template>
  <div class="min-h-screen flex flex-col bg-slate-50 text-slate-900">
    <NetworkStatus />
    <AppNavbar />
    <MobileNav />

    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in">
      <slot />
    </main>

    <AppFooter />
    <AppToast />
    <AppConfirmDialog />
    <LoadingOverlay />
  </div>
</template>
