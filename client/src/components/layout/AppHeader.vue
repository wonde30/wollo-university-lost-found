<script setup lang="ts">
import { useUiStore } from '@/stores/ui.store'
import NotificationBell from './NotificationBell.vue'
import UserMenu from './UserMenu.vue'
import Breadcrumbs from './Breadcrumbs.vue'

const uiStore = useUiStore()
</script>

<template>
  <header class="sticky top-0 z-20 h-16 bg-white/90 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
    <!-- Left: Sidebar Toggle & Breadcrumbs -->
    <div class="flex items-center gap-3">
      <!-- Mobile hamburger: toggles mobile slide-in drawer -->
      <button
        type="button"
        class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer"
        title="Open navigation menu"
        @click="uiStore.toggleMobileMenu"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <!-- Desktop toggle: collapses/expands desktop sidebar -->
      <button
        type="button"
        class="hidden lg:flex p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer"
        :title="uiStore.sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        @click="uiStore.toggleSidebar"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <Breadcrumbs />
    </div>

    <!-- Right: Bell & Profile -->
    <div class="flex items-center gap-3">
      <NotificationBell />
      <UserMenu />
    </div>
  </header>
</template>
