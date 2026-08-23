<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { resolveStorageUrl } from '@/utils/url'
import AppAvatar from '@/components/ui/AppAvatar.vue'

const router = useRouter()
const authStore = useAuthStore()
const isOpen = ref(false)

const userAvatarUrl = computed(() => {
  return resolveStorageUrl(authStore.user?.profile_photo_url || authStore.user?.profile_photo || null)
})

async function handleLogout(): Promise<void> {
  isOpen.value = false
  await authStore.logout()
  router.push('/auth/login')
}
</script>

<template>
  <div v-if="authStore.user" class="relative">
    <button
      type="button"
      class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-slate-100 transition-colors cursor-pointer"
      @click="isOpen = !isOpen"
    >
      <AppAvatar
        :src="userAvatarUrl"
        :name="authStore.user.full_name"
        size="sm"
      />
      <div class="hidden sm:flex flex-col text-left">
        <span class="text-xs font-semibold text-slate-800 leading-tight">
          {{ authStore.user.full_name }}
        </span>
        <span class="text-[10px] uppercase font-bold text-emerald-700 tracking-wider">
          {{ authStore.user.role }}
        </span>
      </div>
      <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 p-1.5 z-50 border border-slate-100 divide-y divide-slate-100"
    >
      <div class="px-3 py-2.5">
        <p class="text-xs font-semibold text-slate-900 truncate">{{ authStore.user.full_name }}</p>
        <p class="text-[11px] text-slate-500 truncate">{{ authStore.user.email }}</p>
      </div>

      <div class="py-1">
        <RouterLink
          to="/profile"
          class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-lg transition-colors"
          @click="isOpen = false"
        >
          <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          Account Profile
        </RouterLink>

        <RouterLink
          v-if="authStore.isAdmin"
          to="/admin/dashboard"
          class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-lg transition-colors"
          @click="isOpen = false"
        >
          <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          Admin Portal
        </RouterLink>

        <RouterLink
          v-else-if="authStore.isStaff"
          to="/staff/dashboard"
          class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-lg transition-colors"
          @click="isOpen = false"
        >
          <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
          Staff Portal
        </RouterLink>

        <RouterLink
          v-else
          to="/student/dashboard"
          class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-lg transition-colors"
          @click="isOpen = false"
        >
          <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Student Portal
        </RouterLink>
      </div>

      <div class="py-1">
        <button
          type="button"
          class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 rounded-lg transition-colors text-left cursor-pointer"
          @click="handleLogout"
        >
          <svg class="h-4 w-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Sign out
        </button>
      </div>
    </div>
  </div>
</template>
