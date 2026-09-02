<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { usePermissionsStore } from '@/permissions/stores/permissions.store'
import { resolveStorageUrl } from '@/utils/url'
import { currentLocale, t } from '@/i18n'
import AppAvatar from '@/components/ui/AppAvatar.vue'
import {
  User as UserIcon,
  LayoutDashboard,
  ShieldCheck,
  LogOut,
  ChevronDown,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const permissionsStore = usePermissionsStore()
const isOpen = ref(false)

function closeDropdown() {
  isOpen.value = false
}

const userAvatarUrl = computed(() => {
  return resolveStorageUrl(authStore.user?.profile_photo_url || authStore.user?.profile_photo || null)
})

const userRoleLabel = computed(() => {
  const roleKey = authStore.user?.role
  if (!roleKey) return 'STUDENT'
  const matchedRole = permissionsStore.roles.find(r => r.name === roleKey)
  if (matchedRole) {
    return (currentLocale.value === 'am' && matchedRole.display_name_am)
      ? matchedRole.display_name_am
      : (matchedRole.display_name || matchedRole.name)
  }
  return roleKey.replace('_', ' ').toUpperCase()
})

async function handleLogout(): Promise<void> {
  isOpen.value = false
  await authStore.logout()
  router.push('/auth/login')
}
</script>

<template>
  <div v-if="authStore.user" v-click-outside="closeDropdown" class="relative">
    <button
      type="button"
      class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
      :aria-label="t('common.userMenu')"
      :aria-expanded="isOpen"
      @click="isOpen = !isOpen"
    >
      <AppAvatar
        :src="userAvatarUrl"
        :name="authStore.user.full_name"
        size="sm"
      />
      <div class="hidden sm:flex flex-col text-left">
        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 leading-tight">
          {{ authStore.user.full_name }}
        </span>
        <span class="text-[10px] uppercase font-bold text-emerald-700 dark:text-emerald-400 tracking-wider">
          {{ userRoleLabel }}
        </span>
      </div>
      <ChevronDown
        class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200"
        :class="isOpen ? 'rotate-180' : ''"
      />
    </button>

    <!-- Dropdown -->
    <Transition name="fade">
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-56 rounded-2xl bg-white dark:bg-[#111827] shadow-xl ring-1 ring-black/5 dark:ring-white/10 p-1.5 z-50 border border-slate-100 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800 animate-scale-in"
      >
        <div class="px-3 py-2.5">
          <p class="text-xs font-semibold text-slate-900 dark:text-slate-100 truncate">{{ authStore.user.full_name }}</p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ authStore.user.email }}</p>
        </div>

        <div class="py-1">
          <RouterLink
            to="/profile"
            class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors"
            @click="isOpen = false"
          >
            <UserIcon class="h-4 w-4 text-slate-400" />
            {{ t('nav.profile') }}
          </RouterLink>

          <RouterLink
            v-if="authStore.canAccessAdminPortal"
            to="/admin/dashboard"
            class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors"
            @click="isOpen = false"
          >
            <LayoutDashboard class="h-4 w-4 text-[#0B5D3B] dark:text-[#3e9e70]" />
            {{ t('nav.adminPortal') }}
          </RouterLink>

          <RouterLink
            v-else-if="authStore.canAccessStaffPortal"
            to="/staff/dashboard"
            class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors"
            @click="isOpen = false"
          >
            <ShieldCheck class="h-4 w-4 text-[#0B5D3B] dark:text-[#3e9e70]" />
            {{ t('nav.staffPortal') }}
          </RouterLink>

          <RouterLink
            v-else
            to="/student/dashboard"
            class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors"
            @click="isOpen = false"
          >
            <LayoutDashboard class="h-4 w-4 text-[#0B5D3B] dark:text-[#3e9e70]" />
            {{ t('nav.studentPortal') }}
          </RouterLink>
        </div>

        <div class="py-1">
          <button
            type="button"
            class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors text-left cursor-pointer"
            @click="handleLogout"
          >
            <LogOut class="h-4 w-4 text-rose-500" />
            {{ t('nav.signOut') }}
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>
