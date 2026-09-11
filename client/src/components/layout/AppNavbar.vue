<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useUiStore } from '@/stores/ui.store'
import { useSettingsStore } from '@/stores/settings.store'
import UserMenu from './UserMenu.vue'
import NotificationBell from './NotificationBell.vue'
import ThemeToggle from './ThemeToggle.vue'
import LanguageSwitcher from './LanguageSwitcher.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { Menu, LogIn } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()
const settingsStore = useSettingsStore()

function handleReportLost() {
  if (authStore.isAuthenticated) {
    router.push('/report-lost')
  } else {
    router.push({ name: 'login', query: { redirect: '/report-lost' } })
  }
}

function handleReportFound() {
  if (authStore.isAuthenticated) {
    router.push('/report-found')
  } else {
    router.push({ name: 'login', query: { redirect: '/report-found' } })
  }
}
</script>

<template>
  <header class="sticky top-0 z-40 w-full border-b border-slate-200/80 dark:border-slate-800 bg-white/95 dark:bg-[#111827]/95 backdrop-blur-md transition-colors duration-150">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
      <!-- Left: Logo & Brand -->
      <div class="flex items-center gap-3">
        <RouterLink to="/" class="flex items-center gap-2.5 group">
          <img
            :src="settingsStore.logoUrl"
            :alt="settingsStore.institutionName + ' Emblem'"
            class="h-9 w-9 shrink-0 object-contain rounded-full bg-white dark:bg-slate-800 shadow-2xs ring-1 ring-slate-300/40 dark:ring-slate-700/40 p-0.5 group-hover:scale-105 transition-transform"
            @error="($event.target as HTMLImageElement).src = '/images/wu-logo.png'"
          />
          <div class="flex flex-col">
            <span class="text-sm font-black tracking-tight text-slate-900 dark:text-white leading-none">
              {{ settingsStore.institutionName }}
            </span>
            <span class="text-[10px] font-bold tracking-wider text-[#0B5D3B] dark:text-[#75bd97] uppercase mt-0.5">
              Lost &amp; Found
            </span>
          </div>
        </RouterLink>
      </div>

      <!-- Center: Navigation Links -->
      <nav class="hidden lg:flex items-center gap-1">
        <RouterLink
          to="/"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          active-class="font-bold text-[#0B5D3B] dark:text-[#75bd97] bg-[#E8F4EE] dark:bg-[#153C2D]/60"
        >
          Home
        </RouterLink>

        <RouterLink
          to="/browse"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          active-class="font-bold text-[#0B5D3B] dark:text-[#75bd97] bg-[#E8F4EE] dark:bg-[#153C2D]/60"
        >
          Browse Items
        </RouterLink>

        <button
          type="button"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
          @click="handleReportLost"
        >
          Report Lost
        </button>

        <button
          type="button"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
          @click="handleReportFound"
        >
          Report Found
        </button>

        <RouterLink
          to="/track"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          active-class="font-bold text-[#0B5D3B] dark:text-[#75bd97] bg-[#E8F4EE] dark:bg-[#153C2D]/60"
        >
          Track Item
        </RouterLink>

        <RouterLink
          to="/browse"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        >
          About
        </RouterLink>
      </nav>

      <!-- Right: Action / Profile / Theme / Language / Mobile toggle -->
      <div class="flex items-center gap-2 sm:gap-2.5">
        <LanguageSwitcher class="hidden sm:inline-flex" />
        <ThemeToggle />

        <template v-if="authStore.isAuthenticated">
          <NotificationBell />
          <UserMenu />
        </template>

        <template v-else>
          <RouterLink to="/auth/login">
            <AppButton variant="ghost" size="sm" class="font-bold text-xs">
              <template #icon-left>
                <LogIn class="h-3.5 w-3.5 mr-1" />
              </template>
              Login
            </AppButton>
          </RouterLink>

          <RouterLink to="/auth/register" class="hidden sm:inline-block">
            <AppButton variant="primary" size="sm" class="font-bold text-xs shadow-xs">
              Get Started
            </AppButton>
          </RouterLink>
        </template>

        <!-- Mobile burger -->
        <button
          type="button"
          class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-colors cursor-pointer"
          aria-label="Open Navigation Menu"
          @click="uiStore.toggleMobileMenu"
        >
          <Menu class="h-5 w-5" />
        </button>
      </div>
    </div>
  </header>
</template>
