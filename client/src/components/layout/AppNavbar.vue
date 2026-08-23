<script setup lang="ts">
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useUiStore } from '@/stores/ui.store'
import UserMenu from './UserMenu.vue'
import NotificationBell from './NotificationBell.vue'
import AppButton from '@/components/ui/AppButton.vue'

const authStore = useAuthStore()
const uiStore = useUiStore()
</script>

<template>
  <header class="sticky top-0 z-40 w-full border-b border-slate-200/80 bg-white/95 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
      <!-- Left: Logo & Brand -->
      <div class="flex items-center gap-3">
        <RouterLink to="/" class="flex items-center gap-2.5 group">
          <img
            src="/images/wu-logo.png"
            alt="Wollo University Emblem"
            class="h-10 w-10 shrink-0 object-contain rounded-full bg-white shadow-md ring-1 ring-[#D4AF37]/50 p-0.5 group-hover:scale-105 transition-transform"
          />
          <div class="flex flex-col">
            <span class="text-base font-black tracking-tight text-slate-900 leading-none">
              WOLLO <span class="text-[#0F5132]">LOST & FOUND</span>
            </span>
            <span class="text-[10px] font-semibold tracking-wider text-[#D4AF37] uppercase mt-0.5">
              ወሎ ዩኒቨርሲቲ &bull; Digital Recovery
            </span>
          </div>
        </RouterLink>
      </div>

      <!-- Center: Navigation Links -->
      <nav class="hidden md:flex items-center gap-1">
        <RouterLink
          to="/"
          class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors"
          active-class="text-[#0F5132] bg-[#0F5132]/5 font-semibold"
        >
          Home
        </RouterLink>

        <RouterLink
          to="/browse"
          class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors"
          active-class="text-[#0F5132] bg-[#0F5132]/5 font-semibold"
        >
          Browse Items
        </RouterLink>

        <RouterLink
          to="/track"
          class="px-3.5 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors"
          active-class="text-[#0F5132] bg-[#0F5132]/5 font-semibold"
        >
          Track Item
        </RouterLink>
      </nav>

      <!-- Right: Action / Profile / Mobile toggle -->
      <div class="flex items-center gap-2.5">
        <template v-if="authStore.isAuthenticated">
          <NotificationBell />
          <UserMenu />
        </template>

        <template v-else>
          <RouterLink to="/auth/login">
            <AppButton variant="ghost" size="sm">
              Sign In
            </AppButton>
          </RouterLink>

          <RouterLink to="/auth/register">
            <AppButton variant="primary" size="sm">
              Register
            </AppButton>
          </RouterLink>
        </template>

        <!-- Mobile burger -->
        <button
          type="button"
          class="md:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100"
          @click="uiStore.toggleMobileMenu"
        >
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>
  </header>
</template>
