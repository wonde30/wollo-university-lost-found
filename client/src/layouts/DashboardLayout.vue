<script setup lang="ts">
import { useUiStore } from '@/stores/ui.store'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppToast from '@/components/ui/AppToast.vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'
import LoadingOverlay from '@/components/feedback/LoadingOverlay.vue'
import NetworkStatus from '@/components/feedback/NetworkStatus.vue'

const uiStore = useUiStore()
</script>

<template>
  <div class="min-h-screen bg-slate-100/70 text-slate-900 flex">
    <!-- Sidebar -->
    <AppSidebar />

    <!-- Backdrop for Mobile Sidebar Drawer -->
    <Transition name="fade">
      <div
        v-if="uiStore.sidebarMobileOpen"
        class="fixed inset-0 z-30 bg-slate-950/60 backdrop-blur-xs lg:hidden"
        @click="uiStore.setSidebarOpen(false)"
      />
    </Transition>

    <!-- Main Content Area -->
    <div
      :class="[
        'flex-1 flex flex-col min-w-0 transition-all duration-200 ease-in-out',
        uiStore.sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64',
      ]"
    >
      <NetworkStatus />
      <AppHeader />

      <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto animate-fade-in">
        <slot />
      </main>

      <footer class="py-4 px-6 border-t border-slate-200/80 bg-white text-xs text-slate-400 text-center sm:text-left flex flex-col sm:flex-row justify-between items-center gap-2">
        <div class="flex items-center gap-2">
          <span class="font-bold text-slate-700">Wollo University</span>
          <span>&bull;</span>
          <span>Lost & Found Operations</span>
        </div>
        <span class="font-medium text-slate-400">Powered by WONDATIR (IT)</span>
      </footer>
    </div>

    <AppToast />
    <AppConfirmDialog />
    <LoadingOverlay />
  </div>
</template>
