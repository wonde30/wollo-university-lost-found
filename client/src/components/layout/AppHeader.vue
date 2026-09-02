<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUiStore } from '@/stores/ui.store'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { t } from '@/i18n'
import NotificationBell from './NotificationBell.vue'
import UserMenu from './UserMenu.vue'
import ThemeToggle from './ThemeToggle.vue'
import LanguageSwitcher from './LanguageSwitcher.vue'
import {
  Menu,
  PanelLeftClose,
  PanelLeftOpen,
  Search,
  Maximize2,
  Minimize2,
} from 'lucide-vue-next'

const router = useRouter()
const uiStore = useUiStore()
const authStore = useAuthStore()

const globalSearchQuery = ref('')
const searchInputRef = ref<HTMLInputElement | null>(null)
const isFullscreen = ref(false)

function handleGlobalSearch() {
  const query = globalSearchQuery.value.trim()
  if (!query) return

  if (authStore.user?.role === 'admin') {
    router.push({ name: 'admin-users', query: { search: query } })
  } else {
    router.push({ name: 'browse', query: { search: query } })
  }
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(() => {})
    isFullscreen.value = true
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen().catch(() => {})
    }
    isFullscreen.value = false
  }
}

function handleKeyDown(e: KeyboardEvent) {
  if (e.key === '/' && document.activeElement !== searchInputRef.value && !['INPUT', 'TEXTAREA', 'SELECT'].includes((document.activeElement as HTMLElement)?.tagName)) {
    e.preventDefault()
    searchInputRef.value?.focus()
  }
}

function onFullscreenChange() {
  isFullscreen.value = !!document.fullscreenElement
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown)
  document.addEventListener('fullscreenchange', onFullscreenChange)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
  document.removeEventListener('fullscreenchange', onFullscreenChange)
})
</script>

<template>
  <header class="sticky top-0 z-20 h-16 bg-white/95 dark:bg-[#111827]/95 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4 transition-colors duration-150">
    <!-- Left: Sidebar Toggle & Global Search Bar -->
    <div class="flex items-center gap-3 flex-1 max-w-lg">
      <!-- Mobile hamburger: toggles mobile slide-in drawer -->
      <button
        type="button"
        class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-colors cursor-pointer shrink-0"
        :aria-label="t('common.openMenu')"
        @click="uiStore.toggleMobileMenu"
      >
        <Menu class="h-5 w-5" />
      </button>

      <!-- Desktop toggle: collapses/expands desktop sidebar -->
      <button
        type="button"
        class="hidden lg:flex p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-colors cursor-pointer shrink-0"
        :aria-label="uiStore.sidebarCollapsed ? t('common.show') : t('common.close')"
        :title="uiStore.sidebarCollapsed ? t('common.show') : t('common.close')"
        @click="uiStore.toggleSidebar"
      >
        <PanelLeftOpen v-if="uiStore.sidebarCollapsed" class="h-5 w-5" />
        <PanelLeftClose v-else class="h-5 w-5" />
      </button>

      <!-- Global Navbar Search Bar (Matching Reference Design) -->
      <div class="relative w-full max-w-sm hidden sm:block">
        <input
          ref="searchInputRef"
          type="text"
          v-model="globalSearchQuery"
          :placeholder="t('common.searchPlaceholder')"
          class="w-full h-9 pl-9 pr-8 text-xs rounded-xl bg-slate-100 dark:bg-slate-800/80 border-0 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B] transition-all"
          @keyup.enter="handleGlobalSearch"
        />
        <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400 pointer-events-none" />
        <span class="absolute right-2.5 top-2 text-[10px] font-mono text-slate-400 bg-white dark:bg-slate-700 px-1.5 py-0.5 rounded border border-slate-200/80 dark:border-slate-600 pointer-events-none">/</span>
      </div>
    </div>

    <!-- Right: Fullscreen, Theme, Language, Bell & Profile -->
    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
      <!-- Fullscreen Toggle -->
      <button
        type="button"
        :title="isFullscreen ? 'Exit Fullscreen' : 'Fullscreen'"
        class="p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-colors cursor-pointer hidden md:inline-flex"
        @click="toggleFullscreen"
      >
        <Minimize2 v-if="isFullscreen" class="h-4 w-4" />
        <Maximize2 v-else class="h-4 w-4" />
      </button>

      <ThemeToggle />
      <LanguageSwitcher />
      <NotificationBell />
      <UserMenu />
    </div>
  </header>
</template>
