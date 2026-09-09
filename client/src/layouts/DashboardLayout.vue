<script setup lang="ts">
import { useUiStore } from '@/stores/ui.store'
import { useSettingsStore } from '@/stores/settings.store'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppToast from '@/components/ui/AppToast.vue'
import AppConfirmDialog from '@/components/ui/AppConfirmDialog.vue'
import LoadingOverlay from '@/components/feedback/LoadingOverlay.vue'
import NetworkStatus from '@/components/feedback/NetworkStatus.vue'
import SystemAnnouncementBanner from '@/components/common/SystemAnnouncementBanner.vue'

const uiStore = useUiStore()
const settingsStore = useSettingsStore()
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-[#0F172A] text-slate-900 dark:text-slate-100 flex transition-colors duration-150">
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
        'flex-1 flex flex-col min-w-0 transition-all duration-150 ease-in-out',
        uiStore.sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64',
      ]"
    >
      <NetworkStatus />
      <AppHeader />
      <SystemAnnouncementBanner />

      <main class="flex-1 p-4 sm:p-5 lg:p-6 max-w-[1600px] w-full mx-auto animate-fade-in">
        <RouterView v-slot="{ Component }">
          <component :is="Component" v-if="Component" />
          <slot v-else />
        </RouterView>
      </main>

      <footer class="py-3 px-6 border-t border-slate-200/80 dark:border-slate-800 bg-white dark:bg-[#111827] text-xs text-slate-400 dark:text-slate-500 text-center sm:text-left flex flex-col sm:flex-row justify-between items-center gap-2">
        <div class="flex items-center gap-2">
          <span class="font-bold text-slate-700 dark:text-slate-300">{{ settingsStore.institutionName }}</span>
          <span>&bull;</span>
          <span>{{ settingsStore.tagline }}</span>
        </div>
        <span class="font-semibold text-slate-400 dark:text-slate-500">{{ settingsStore.developerCredit }}</span>
      </footer>
    </div>

    <AppToast />
    <AppConfirmDialog />
    <LoadingOverlay />
  </div>
</template>
