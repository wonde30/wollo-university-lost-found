<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useUiStore } from '@/stores/ui.store'
import { resolveStorageUrl } from '@/utils/url'
import AppAvatar from '@/components/ui/AppAvatar.vue'
import AppTooltip from '@/components/ui/AppTooltip.vue'

const route = useRoute()
const authStore = useAuthStore()
const uiStore = useUiStore()

const userAvatarUrl = computed(() => {
  return resolveStorageUrl(authStore.user?.profile_photo_url || authStore.user?.profile_photo || null)
})

const navigationItems = computed(() => {
  if (authStore.isAdmin) {
    return [
      { name: 'Dashboard', path: '/admin/dashboard', icon: 'dashboard' },
      { name: 'Users', path: '/admin/users', icon: 'users' },
      { name: 'Campuses', path: '/admin/campuses', icon: 'campuses' },
      { name: 'Categories', path: '/admin/categories', icon: 'categories' },
      { name: 'Locations', path: '/admin/locations', icon: 'locations' },
      { name: 'Reports', path: '/admin/reports', icon: 'reports' },
      { name: 'Permissions', path: '/admin/permissions', icon: 'permissions' },
      { name: 'Audit Logs', path: '/admin/audit-logs', icon: 'audit' },
      { name: 'Settings', path: '/admin/settings', icon: 'settings' },
    ]
  }

  if (authStore.isStaff) {
    return [
      { name: 'Dashboard', path: '/staff/dashboard', icon: 'dashboard' },
      { name: 'Review Claims', path: '/staff/review-claims', icon: 'claims' },
      { name: 'Manage Custody', path: '/staff/manage-custody', icon: 'custody' },
      { name: 'Process Return', path: '/staff/process-return', icon: 'returns' },
    ]
  }

  // Student default
  return [
    { name: 'Dashboard', path: '/student/dashboard', icon: 'dashboard' },
    { name: 'Report Lost', path: '/student/report-lost', icon: 'lost' },
    { name: 'Report Found', path: '/student/report-found', icon: 'found' },
    { name: 'My Items', path: '/student/my-items', icon: 'items' },
    { name: 'My Claims', path: '/student/my-claims', icon: 'claims' },
    { name: 'Browse All', path: '/browse', icon: 'browse' },
  ]
})
</script>

<template>
  <aside
    :class="[
      'fixed inset-y-0 left-0 z-40 bg-slate-950 text-white flex flex-col transition-all duration-200 ease-in-out border-r border-slate-800/80',
      uiStore.sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
      uiStore.sidebarMobileOpen ? 'translate-x-0 w-64 shadow-2xl' : '-translate-x-full lg:translate-x-0',
    ]"
  >
    <!-- Brand Header -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800/80 bg-slate-950/60 shrink-0">
      <RouterLink to="/" class="flex items-center gap-3 overflow-hidden">
        <img
          src="/images/wu-logo.png"
          alt="Wollo University Emblem"
          class="h-9 w-9 shrink-0 object-contain rounded-full bg-white shadow-sm ring-1 ring-[#D4AF37]/60 p-0.5"
        />

        <div v-if="!uiStore.sidebarCollapsed" class="flex flex-col transition-opacity duration-200 min-w-0">
          <span class="text-xs font-black tracking-tight text-white leading-tight truncate">WOLLO UNIVERSITY</span>
          <span class="text-[10px] font-bold text-[#D4AF37] tracking-wider uppercase truncate">Lost & Found</span>
        </div>
      </RouterLink>

      <!-- Mobile Close Button -->
      <button
        type="button"
        class="lg:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition-colors"
        @click="uiStore.setSidebarOpen(false)"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- User Profile Summary Pill -->
    <div class="p-3 border-b border-slate-800/80 bg-slate-900/40 shrink-0">
      <div :class="['flex items-center gap-3', uiStore.sidebarCollapsed ? 'justify-center' : '']">
        <AppAvatar
          :src="userAvatarUrl"
          :name="authStore.user?.full_name || 'User'"
          size="sm"
          status="online"
        />
        <div v-if="!uiStore.sidebarCollapsed" class="flex-1 min-w-0">
          <p class="text-xs font-bold text-white truncate">{{ authStore.user?.full_name || 'User' }}</p>
          <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold uppercase bg-emerald-950 text-emerald-400 border border-emerald-800/50">
            {{ authStore.user?.role || 'Guest' }}
          </span>
        </div>
      </div>
    </div>

    <!-- Navigation Items -->
    <nav class="flex-1 overflow-y-auto p-3 space-y-1.5 no-scrollbar">
      <template v-for="item in navigationItems" :key="item.path">
        <!-- Collapsed Item with Tooltip -->
        <AppTooltip
          v-if="uiStore.sidebarCollapsed"
          :text="item.name"
          position="right"
          class="w-full"
        >
          <RouterLink
            :to="item.path"
            :class="[
              'flex items-center justify-center h-11 w-full rounded-xl transition-all select-none',
              route.path === item.path
                ? 'bg-[#0F5132] text-white shadow-sm font-bold'
                : 'text-slate-400 hover:text-white hover:bg-slate-850',
            ]"
            @click="uiStore.setSidebarOpen(false)"
          >
            <!-- Navigation Icons -->
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="item.icon === 'dashboard'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              <path v-else-if="item.icon === 'users'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              <path v-else-if="item.icon === 'campuses'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              <path v-else-if="item.icon === 'categories'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
              <path v-else-if="item.icon === 'locations'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path v-else-if="item.icon === 'reports'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              <path v-else-if="item.icon === 'lost'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              <path v-else-if="item.icon === 'found'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path v-else-if="item.icon === 'items'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              <path v-else-if="item.icon === 'claims'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              <path v-else-if="item.icon === 'custody'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              <path v-else-if="item.icon === 'returns'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3-3m-3 3l3 3m5 4v1a3 3 0 003 3h4a3 3 0 003-3v-5a3 3 0 00-3-3h-2" />
              <path v-else-if="item.icon === 'audit'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              <path v-else-if="item.icon === 'settings'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path v-else-if="item.icon === 'permissions'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </RouterLink>
        </AppTooltip>

        <!-- Expanded Item -->
        <RouterLink
          v-else
          :to="item.path"
          :class="[
            'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all select-none group',
            route.path === item.path
              ? 'bg-[#0F5132] text-white shadow-sm'
              : 'text-slate-400 hover:text-white hover:bg-slate-850',
          ]"
          @click="uiStore.setSidebarOpen(false)"
        >
          <svg class="h-4 w-4 shrink-0 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="item.icon === 'dashboard'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            <path v-else-if="item.icon === 'users'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            <path v-else-if="item.icon === 'campuses'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            <path v-else-if="item.icon === 'categories'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            <path v-else-if="item.icon === 'locations'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path v-else-if="item.icon === 'reports'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            <path v-else-if="item.icon === 'lost'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            <path v-else-if="item.icon === 'found'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            <path v-else-if="item.icon === 'items'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            <path v-else-if="item.icon === 'claims'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            <path v-else-if="item.icon === 'custody'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            <path v-else-if="item.icon === 'returns'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3-3m-3 3l3 3m5 4v1a3 3 0 003 3h4a3 3 0 003-3v-5a3 3 0 00-3-3h-2" />
            <path v-else-if="item.icon === 'audit'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            <path v-else-if="item.icon === 'settings'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path v-else-if="item.icon === 'permissions'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <span class="truncate">{{ item.name }}</span>
        </RouterLink>
      </template>
    </nav>

    <!-- Bottom Actions: Profile & Desktop Sidebar Collapse Button -->
    <div class="p-3 border-t border-slate-800/80 shrink-0 space-y-1">
      <RouterLink
        to="/profile"
        :class="[
          'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors text-xs font-semibold',
          uiStore.sidebarCollapsed ? 'justify-center' : '',
        ]"
      >
        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <span v-if="!uiStore.sidebarCollapsed">Profile Settings</span>
      </RouterLink>

      <!-- Desktop Collapse Toggle Button -->
      <button
        type="button"
        :class="[
          'hidden lg:flex items-center gap-3 px-3.5 py-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors text-xs font-semibold w-full cursor-pointer',
          uiStore.sidebarCollapsed ? 'justify-center' : '',
        ]"
        @click="uiStore.toggleSidebar"
      >
        <svg
          class="h-4 w-4 shrink-0 transition-transform duration-200"
          :class="uiStore.sidebarCollapsed ? 'rotate-180' : ''"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
        <span v-if="!uiStore.sidebarCollapsed">Collapse Sidebar</span>
      </button>
    </div>
  </aside>
</template>
