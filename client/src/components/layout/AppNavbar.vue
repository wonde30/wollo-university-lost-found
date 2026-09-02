<script setup lang="ts">
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useUiStore } from '@/stores/ui.store'
import { t } from '@/i18n'
import UserMenu from './UserMenu.vue'
import NotificationBell from './NotificationBell.vue'
import ThemeToggle from './ThemeToggle.vue'
import LanguageSwitcher from './LanguageSwitcher.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { Menu } from 'lucide-vue-next'

const authStore = useAuthStore()
const uiStore = useUiStore()
</script>

<template>
  <header class="sticky top-0 z-40 w-full border-b border-slate-200/80 dark:border-slate-800 bg-white/95 dark:bg-[#111827]/95 backdrop-blur-md transition-colors duration-150">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
      <!-- Left: Logo & Brand -->
      <div class="flex items-center gap-3">
        <RouterLink to="/" class="flex items-center gap-2.5 group">
          <img
            src="/images/wu-logo.png"
            alt="Wollo University Emblem"
            class="h-9 w-9 shrink-0 object-contain rounded-full bg-white dark:bg-slate-800 shadow-2xs ring-1 ring-[#0B5D3B]/40 p-0.5 group-hover:scale-105 transition-transform"
          />
          <div class="flex flex-col">
            <span class="text-base font-black tracking-tight text-slate-900 dark:text-white leading-none">
              WOLLO <span class="text-[#0B5D3B] dark:text-[#75bd97]">LOST & FOUND</span>
            </span>
            <span class="text-[10px] font-bold tracking-wider text-[#0B5D3B] dark:text-[#75bd97] uppercase mt-0.5">
              ወሎ ዩኒቨርሲቲ &bull; Digital Recovery
            </span>
          </div>
        </RouterLink>
      </div>

      <!-- Center: Navigation Links -->
      <nav class="hidden md:flex items-center gap-1.5">
        <RouterLink
          to="/"
          class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          active-class="text-[#0B5D3B] dark:text-white bg-[#E8F4EE] dark:bg-[#153C2D] font-extrabold shadow-2xs"
        >
          {{ t('nav.home') }}
        </RouterLink>

        <RouterLink
          to="/browse"
          class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          active-class="text-[#0B5D3B] dark:text-white bg-[#E8F4EE] dark:bg-[#153C2D] font-extrabold shadow-2xs"
        >
          {{ t('nav.browse') }}
        </RouterLink>

        <RouterLink
          to="/track"
          class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          active-class="text-[#0B5D3B] dark:text-white bg-[#E8F4EE] dark:bg-[#153C2D] font-extrabold shadow-2xs"
        >
          {{ t('nav.track') }}
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
            <AppButton variant="ghost" size="sm">
              {{ t('nav.signIn') }}
            </AppButton>
          </RouterLink>

          <RouterLink to="/auth/register" class="hidden sm:inline-block">
            <AppButton variant="primary" size="sm">
              {{ t('nav.register') }}
            </AppButton>
          </RouterLink>
        </template>

        <!-- Mobile burger -->
        <button
          type="button"
          class="md:hidden p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-colors cursor-pointer"
          aria-label="Open Navigation Menu"
          @click="uiStore.toggleMobileMenu"
        >
          <Menu class="h-5 w-5" />
        </button>
      </div>
    </div>
  </header>
</template>
