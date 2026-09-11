<script setup lang="ts">
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useUiStore } from '@/stores/ui.store'
import { t } from '@/i18n'
import AppDrawer from '@/components/ui/AppDrawer.vue'
import AppButton from '@/components/ui/AppButton.vue'
import ThemeToggle from './ThemeToggle.vue'
import LanguageSwitcher from './LanguageSwitcher.vue'
import {
  Home,
  Search,
  Compass,
  LayoutDashboard,
  User,
  LogIn,
  UserPlus,
} from 'lucide-vue-next'

const authStore = useAuthStore()
const uiStore = useUiStore()
</script>

<template>
  <AppDrawer
    :open="uiStore.isMobileMenuOpen"
    :title="t('nav.home')"
    placement="left"
    @close="uiStore.setMobileMenuOpen(false)"
  >
    <div class="space-y-4 py-2 flex flex-col justify-between h-full">
      <div class="space-y-1">
        <RouterLink
          to="/"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          active-class="text-[#0B5D3B] dark:text-[#75bd97] bg-[#E8F4EE] dark:bg-[#153C2D]/60 font-bold"
          @click="uiStore.setMobileMenuOpen(false)"
        >
          <Home class="h-4 w-4" />
          Home
        </RouterLink>

        <RouterLink
          to="/browse"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          active-class="text-[#0B5D3B] dark:text-[#75bd97] bg-[#E8F4EE] dark:bg-[#153C2D]/60 font-bold"
          @click="uiStore.setMobileMenuOpen(false)"
        >
          <Search class="h-4 w-4" />
          Browse Items
        </RouterLink>

        <RouterLink
          :to="authStore.isAuthenticated ? '/report-lost' : { name: 'login', query: { redirect: '/report-lost' } }"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          active-class="text-[#0B5D3B] dark:text-[#75bd97] bg-[#E8F4EE] dark:bg-[#153C2D]/60 font-bold"
          @click="uiStore.setMobileMenuOpen(false)"
        >
          <FileText class="h-4 w-4" />
          Report Lost
        </RouterLink>

        <RouterLink
          :to="authStore.isAuthenticated ? '/report-found' : { name: 'login', query: { redirect: '/report-found' } }"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          active-class="text-[#0B5D3B] dark:text-[#75bd97] bg-[#E8F4EE] dark:bg-[#153C2D]/60 font-bold"
          @click="uiStore.setMobileMenuOpen(false)"
        >
          <Package class="h-4 w-4" />
          Report Found
        </RouterLink>

        <RouterLink
          to="/track"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          active-class="text-[#0B5D3B] dark:text-[#75bd97] bg-[#E8F4EE] dark:bg-[#153C2D]/60 font-bold"
          @click="uiStore.setMobileMenuOpen(false)"
        >
          <Compass class="h-4 w-4" />
          Track Item
        </RouterLink>

        <RouterLink
          to="/browse"
          class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          @click="uiStore.setMobileMenuOpen(false)"
        >
          <Info class="h-4 w-4" />
          About
        </RouterLink>
      </div>

      <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
        <!-- Controls: Language & Theme -->
        <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60">
          <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ t('common.language') }} & {{ t('common.toggleTheme') }}</span>
          <div class="flex items-center gap-2">
            <LanguageSwitcher />
            <ThemeToggle />
          </div>
        </div>

        <template v-if="authStore.isAuthenticated">
          <RouterLink
            :to="authStore.dashboardRoute"
            class="block mb-2"
            @click="uiStore.setMobileMenuOpen(false)"
          >
            <AppButton variant="primary" block>
              <template #icon-left>
                <LayoutDashboard class="h-4 w-4 mr-1" />
              </template>
              {{ t('nav.dashboard') }}
            </AppButton>
          </RouterLink>

          <RouterLink
            to="/profile"
            class="block"
            @click="uiStore.setMobileMenuOpen(false)"
          >
            <AppButton variant="outline" block>
              <template #icon-left>
                <User class="h-4 w-4 mr-1" />
              </template>
              {{ t('nav.profile') }}
            </AppButton>
          </RouterLink>
        </template>

        <template v-else>
          <div class="space-y-2">
            <RouterLink to="/auth/login" class="block" @click="uiStore.setMobileMenuOpen(false)">
              <AppButton variant="outline" block>
                <template #icon-left>
                  <LogIn class="h-4 w-4 mr-1" />
                </template>
                {{ t('nav.signIn') }}
              </AppButton>
            </RouterLink>
            <RouterLink to="/auth/register" class="block" @click="uiStore.setMobileMenuOpen(false)">
              <AppButton variant="primary" block>
                <template #icon-left>
                  <UserPlus class="h-4 w-4 mr-1" />
                </template>
                {{ t('nav.register') }}
              </AppButton>
            </RouterLink>
          </div>
        </template>
      </div>
    </div>
  </AppDrawer>
</template>
