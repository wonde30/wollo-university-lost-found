<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useUiStore } from '@/stores/ui.store'
import { useSettingsStore } from '@/stores/settings.store'
import AppTooltip from '@/components/ui/AppTooltip.vue'
import {
  LayoutDashboard,
  Users,
  Shield,
  ShieldCheck,
  Package,
  AlertCircle,
  CheckCircle2,
  PackageSearch,
  ClipboardList,
  ClipboardCheck,
  Sparkles,
  Handshake,
  Building2,
  Building,
  MapPin,
  Tag,
  Archive,
  FileText,
  History,
  Settings,
  HelpCircle,
  ChevronDown,
  Megaphone,
  X,
} from 'lucide-vue-next'
import { t } from '@/i18n'

const route = useRoute()
const authStore = useAuthStore()
const uiStore = useUiStore()
const settingsStore = useSettingsStore()

// ==========================================
// Types
// ==========================================
interface NavBase {
  id: string
  titleKey: string
  icon: any
}

interface NavLinkItem extends NavBase {
  type: 'link'
  path: string | (() => string)
  isAuthorized?: () => boolean
}

interface NavAccordionItem extends NavBase {
  type: 'accordion'
  children: NavLinkItem[]
  isAuthorized?: () => boolean
}

type NavEntry = NavLinkItem | NavAccordionItem

interface NavSection {
  id: string
  titleKey: string
  items: NavEntry[]
}

// ==========================================
// Navigation Structure Definition
// ==========================================
const rawNavigation: NavSection[] = [
  // 1. MENU
  {
    id: 'menu',
    titleKey: 'nav.menu',
    items: [
      {
        id: 'dashboard',
        type: 'link',
        titleKey: 'nav.dashboard',
        path: () => authStore.dashboardRoute,
        icon: LayoutDashboard,
      },
    ],
  },

  // 2. ACCESS
  {
    id: 'access',
    titleKey: 'nav.access',
    items: [
      {
        id: 'userAndAccess',
        type: 'accordion',
        titleKey: 'nav.userAndAccess',
        icon: Users,
        children: [
          {
            id: 'users',
            type: 'link',
            titleKey: 'nav.users',
            path: '/admin/users',
            icon: Users,
            isAuthorized: () => authStore.can('MANAGE_USERS'),
          },
          {
            id: 'roles',
            type: 'link',
            titleKey: 'nav.roles',
            path: '/admin/roles',
            icon: Shield,
            isAuthorized: () => authStore.can('MANAGE_PERMISSIONS'),
          },
          {
            id: 'permissions',
            type: 'link',
            titleKey: 'nav.permissions',
            path: '/admin/permissions',
            icon: ShieldCheck,
            isAuthorized: () => authStore.can('MANAGE_PERMISSIONS'),
          },
        ],
      },
    ],
  },

  // 3. LOST & FOUND
  {
    id: 'lostAndFound',
    titleKey: 'nav.lostAndFound',
    items: [
      {
        id: 'items',
        type: 'accordion',
        titleKey: 'nav.items',
        icon: Package,
        children: [
          {
            id: 'allItems',
            type: 'link',
            titleKey: 'nav.items',
            path: () => (authStore.isAdmin ? '/admin/items' : '/staff/items'),
            icon: Package,
            isAuthorized: () => authStore.isAdmin || authStore.isStaff || authStore.can('MANAGE_ALL_ITEMS'),
          },
          {
            id: 'lostItems',
            type: 'link',
            titleKey: 'nav.lostItems',
            path: () => ((authStore.isAdmin || authStore.isStaff) ? '/admin/items?type=lost' : '/student/report-lost'),
            icon: AlertCircle,
            isAuthorized: () => authStore.can('REPORT_LOST') || authStore.isAdmin || authStore.isStaff,
          },
          {
            id: 'foundItems',
            type: 'link',
            titleKey: 'nav.foundItems',
            path: () => ((authStore.isAdmin || authStore.isStaff) ? '/admin/items?type=found' : '/student/report-found'),
            icon: CheckCircle2,
            isAuthorized: () => authStore.can('REPORT_FOUND') || authStore.isAdmin || authStore.isStaff,
          },
          {
            id: 'myItems',
            type: 'link',
            titleKey: 'nav.myItems',
            path: '/student/my-items',
            icon: PackageSearch,
            isAuthorized: () =>
              !authStore.isAdmin &&
              (authStore.can('REPORT_LOST') ||
                authStore.can('REPORT_FOUND') ||
                authStore.can('EDIT_OWN_ITEM') ||
                authStore.isStudent),
          },
        ],
      },
      {
        id: 'claims',
        type: 'accordion',
        titleKey: 'nav.claims',
        icon: ClipboardList,
        children: [
          {
            id: 'pendingClaims',
            type: 'link',
            titleKey: 'nav.pendingClaims',
            path: '/staff/review-claims',
            icon: ClipboardCheck,
            isAuthorized: () => authStore.can('REVIEW_CLAIMS') || authStore.isAdmin || authStore.isStaff,
          },
          {
            id: 'myClaims',
            type: 'link',
            titleKey: 'nav.myClaims',
            path: '/student/my-claims',
            icon: ClipboardList,
            isAuthorized: () =>
              !authStore.isAdmin &&
              (authStore.can('SUBMIT_CLAIM') || authStore.isStudent),
          },
        ],
      },
    ],
  },

  // 4. OPERATIONS
  {
    id: 'operations',
    titleKey: 'nav.operations',
    items: [
      {
        id: 'matching',
        type: 'link',
        titleKey: 'nav.matching',
        path: '/staff/match-suggestions',
        icon: Sparkles,
        isAuthorized: () => authStore.can('MANAGE_ALL_ITEMS') || authStore.isAdmin || authStore.isStaff,
      },
      {
        id: 'custody',
        type: 'link',
        titleKey: 'nav.custody',
        path: '/staff/manage-custody',
        icon: Package,
        isAuthorized: () => authStore.can('MANAGE_CUSTODY') || authStore.isAdmin || authStore.isStaff,
      },
      {
        id: 'returns',
        type: 'link',
        titleKey: 'nav.returns',
        path: '/staff/process-return',
        icon: Handshake,
        isAuthorized: () => authStore.can('PROCESS_RETURNS') || authStore.isAdmin || authStore.isStaff,
      },
    ],
  },

  // 5. ADMIN SETUP
  {
    id: 'adminSetup',
    titleKey: 'nav.adminSetup',
    items: [
      {
        id: 'announcements',
        type: 'link',
        titleKey: 'nav.announcements',
        path: '/admin/announcements',
        icon: Megaphone,
        isAuthorized: () => authStore.can('MANAGE_SETTINGS') || authStore.isAdmin,
      },
      {
        id: 'administrativeStructure',
        type: 'accordion',
        titleKey: 'nav.administrativeStructure',
        icon: Building2,
        children: [
          {
            id: 'campuses',
            type: 'link',
            titleKey: 'nav.campuses',
            path: '/admin/campuses',
            icon: Building2,
            isAuthorized: () => authStore.can('MANAGE_CAMPUSES'),
          },
          {
            id: 'organizationalUnits',
            type: 'link',
            titleKey: 'nav.organizationalUnits',
            path: '/admin/organizational-units',
            icon: Building,
            isAuthorized: () => authStore.can('MANAGE_CAMPUSES'),
          },
          {
            id: 'locations',
            type: 'link',
            titleKey: 'nav.locations',
            path: '/admin/locations',
            icon: MapPin,
            isAuthorized: () => authStore.can('MANAGE_LOCATIONS'),
          },
          {
            id: 'categories',
            type: 'link',
            titleKey: 'nav.categories',
            path: '/admin/categories',
            icon: Tag,
            isAuthorized: () => authStore.can('MANAGE_CATEGORIES'),
          },
          {
            id: 'storageLocations',
            type: 'link',
            titleKey: 'nav.storageLocations',
            path: '/admin/storage-locations',
            icon: Archive,
            isAuthorized: () => authStore.can('MANAGE_LOCATIONS'),
          },
        ],
      },
    ],
  },

  // 6. REPORTING
  {
    id: 'reporting',
    titleKey: 'nav.reporting',
    items: [
      {
        id: 'reports',
        type: 'link',
        titleKey: 'nav.reports',
        path: '/admin/reports',
        icon: FileText,
        isAuthorized: () => authStore.can('GENERATE_REPORTS'),
      },
      {
        id: 'auditLogs',
        type: 'link',
        titleKey: 'nav.auditLogs',
        path: '/admin/audit-logs',
        icon: History,
        isAuthorized: () => authStore.can('VIEW_AUDIT_LOGS'),
      },
    ],
  },
]

// ==========================================
// Bottom Fixed Links
// ==========================================
const bottomItems = computed<NavLinkItem[]>(() => [
  {
    id: 'settings',
    type: 'link',
    titleKey: 'nav.settings',
    path: () => (authStore.can('MANAGE_SETTINGS') ? '/admin/settings' : '/profile'),
    icon: Settings,
  },
  {
    id: 'helpAndSupport',
    type: 'link',
    titleKey: 'nav.helpAndSupport',
    path: '/track',
    icon: HelpCircle,
  },
])

// ==========================================
// Path and Active State Helpers
// ==========================================
function resolvePath(path: string | (() => string)): string {
  return typeof path === 'function' ? path() : path
}

function isRouteActive(pathOrFn: string | (() => string)): boolean {
  const targetPath = resolvePath(pathOrFn)
  if (targetPath === '/admin/dashboard' || targetPath === '/staff/dashboard' || targetPath === '/student/dashboard') {
    return route.path === targetPath
  }
  return route.path === targetPath || (targetPath !== '/' && route.path.startsWith(targetPath + '/'))
}

function isLinkAuthorized(link: NavLinkItem): boolean {
  if (link.isAuthorized) {
    return link.isAuthorized()
  }
  return true
}

interface VisibleAccordionItem extends NavAccordionItem {
  visibleChildren: NavLinkItem[]
}

type VisibleNavEntry = NavLinkItem | VisibleAccordionItem

interface VisibleNavSection {
  id: string
  titleKey: string
  items: VisibleNavEntry[]
}

const visibleSections = computed<VisibleNavSection[]>(() => {
  const sections: VisibleNavSection[] = []

  for (const section of rawNavigation) {
    const visibleEntries: VisibleNavEntry[] = []

    for (const item of section.items) {
      if (item.type === 'link') {
        if (isLinkAuthorized(item)) {
          visibleEntries.push(item)
        }
      } else if (item.type === 'accordion') {
        const allowedChildren = item.children.filter(isLinkAuthorized)
        if (allowedChildren.length > 0) {
          visibleEntries.push({
            ...item,
            visibleChildren: allowedChildren,
          })
        }
      }
    }

    if (visibleEntries.length > 0) {
      sections.push({
        id: section.id,
        titleKey: section.titleKey,
        items: visibleEntries,
      })
    }
  }

  return sections
})

// ==========================================
// Accordion State & Active-Route Auto-Expansion
// ==========================================
const openAccordions = ref<Record<string, boolean>>({})

function isOpen(groupId: string): boolean {
  return !!openAccordions.value[groupId]
}

function toggleAccordion(groupId: string): void {
  const isCurrentlyOpen = !!openAccordions.value[groupId]
  openAccordions.value = isCurrentlyOpen ? {} : { [groupId]: true }
}

function isGroupActive(group: NavAccordionItem | VisibleAccordionItem): boolean {
  const children = 'visibleChildren' in group ? group.visibleChildren : group.children
  return children.some(child => isRouteActive(child.path))
}

function autoExpandActiveGroup(): void {
  for (const section of rawNavigation) {
    for (const item of section.items) {
      if (item.type === 'accordion') {
        const hasActiveChild = item.children.some(child => isRouteActive(child.path))
        if (hasActiveChild) {
          openAccordions.value = { [item.id]: true }
          return
        }
      }
    }
  }
}

watch(
  () => route.path,
  () => {
    autoExpandActiveGroup()
  },
  { immediate: true }
)

onMounted(() => {
  autoExpandActiveGroup()
})

function handleNavClick(): void {
  uiStore.setSidebarOpen(false)
}
</script>

<template>
  <aside
    :class="[
      'fixed inset-y-0 left-0 z-40 bg-white dark:bg-[#0F172A] text-slate-700 dark:text-slate-200 flex flex-col transition-all duration-150 ease-in-out border-r border-slate-200 dark:border-[#1E293B]',
      uiStore.sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
      uiStore.sidebarMobileOpen ? 'translate-x-0 w-64 shadow-2xl' : '-translate-x-full lg:translate-x-0',
    ]"
  >
    <!-- Brand Header -->
    <div
      class="h-16 flex items-center justify-between px-4 border-b border-slate-200 dark:border-[#1E293B] bg-white/95 dark:bg-[#0F172A]/95 shrink-0"
    >
      <RouterLink to="/" class="flex items-center gap-3 overflow-hidden" @click="handleNavClick">
        <img
          :src="settingsStore.logoUrl"
          :alt="settingsStore.institutionName"
          class="h-9 w-9 shrink-0 object-contain rounded-full bg-white dark:bg-slate-800 shadow-2xs ring-1 ring-[#0B5D3B]/40 p-0.5"
          @error="($event.target as HTMLImageElement).src = '/images/wu-logo.png'"
        />

        <div v-if="!uiStore.sidebarCollapsed" class="flex flex-col transition-opacity duration-150 min-w-0">
          <span class="text-xs font-black tracking-tight text-slate-900 dark:text-white leading-tight truncate uppercase">
            {{ settingsStore.institutionName }}
          </span>
          <span class="text-[10px] font-bold text-[#0B5D3B] dark:text-[#75bd97] tracking-wider uppercase truncate">
            {{ settingsStore.tagline || settingsStore.systemShortName }}
          </span>
        </div>
      </RouterLink>

      <!-- Mobile Close Button -->
      <button
        type="button"
        class="lg:hidden text-slate-400 hover:text-slate-700 dark:hover:text-white p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer dark:text-slate-300"
        aria-label="Close menu"
        @click="uiStore.setSidebarOpen(false)"
      >
        <X class="h-5 w-5" />
      </button>
    </div>

    <!-- Scrollable Navigation Area -->
    <nav class="flex-1 overflow-y-auto px-3 py-2 space-y-2.5 no-scrollbar">
      <!-- Section Loop -->
      <div v-for="section in visibleSections" :key="section.id" class="space-y-1">
        <!-- Section Header (Hidden in collapsed mode) -->
        <div
          v-if="!uiStore.sidebarCollapsed"
          class="px-3 pt-2 pb-1 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 select-none"
        >
          {{ t(section.titleKey) }}
        </div>

        <!-- Section Items -->
        <div class="space-y-0.5">
          <template v-for="item in section.items" :key="item.id">
            <!-- ========================================== -->
            <!-- 1. DIRECT LINK                             -->
            <!-- ========================================== -->
            <template v-if="item.type === 'link'">
              <!-- Collapsed Mode: Direct Link with Tooltip -->
              <AppTooltip
                v-if="uiStore.sidebarCollapsed"
                :text="t(item.titleKey)"
                position="right"
                class="w-full"
              >
                <RouterLink
                  :to="resolvePath(item.path)"
                  :class="[
                    'flex items-center justify-center h-10 w-full rounded-xl transition-colors duration-150 select-none',
                    isRouteActive(item.path)
                      ? 'bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] font-extrabold shadow-2xs'
                      : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60',
                  ]"
                  @click="handleNavClick"
                >
                  <component :is="item.icon" class="h-4.5 w-4.5 shrink-0" />
                </RouterLink>
              </AppTooltip>

              <!-- Expanded Mode: Direct Link -->
              <RouterLink
                v-else
                :to="resolvePath(item.path)"
                :class="[
                  'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold transition-colors duration-150 select-none group',
                  isRouteActive(item.path)
                    ? 'bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] shadow-2xs font-extrabold'
                    : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60',
                ]"
                @click="handleNavClick"
              >
                <component
                  :is="item.icon"
                  :class="[
                    'h-4 w-4 shrink-0 transition-transform group-hover:scale-105',
                    isRouteActive(item.path) ? 'text-[#0B5D3B] dark:text-[#75bd97]' : 'text-slate-500 dark:text-slate-400',
                  ]"
                />
                <span class="truncate">{{ t(item.titleKey) }}</span>
              </RouterLink>
            </template>

            <!-- ========================================== -->
            <!-- 2. ACCORDION GROUP                         -->
            <!-- ========================================== -->
            <template v-else-if="item.type === 'accordion'">
              <!-- Collapsed Mode: Accordion Root Icon with Tooltip linking to first child -->
              <AppTooltip
                v-if="uiStore.sidebarCollapsed"
                :text="t(item.titleKey)"
                position="right"
                class="w-full"
              >
                <RouterLink
                  :to="resolvePath(item.visibleChildren[0]?.path || '/')"
                  :class="[
                    'flex items-center justify-center h-10 w-full rounded-xl transition-colors duration-150 select-none',
                    isGroupActive(item)
                      ? 'bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] font-extrabold shadow-2xs'
                      : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60',
                  ]"
                  @click="handleNavClick"
                >
                  <component :is="item.icon" class="h-4.5 w-4.5 shrink-0" />
                </RouterLink>
              </AppTooltip>

              <!-- Expanded Mode: Accordion Toggle Button & Collapsible Submenu -->
              <div v-else class="space-y-0.5">
                <button
                  type="button"
                  :class="[
                    'w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition-colors duration-150 select-none cursor-pointer group',
                    isGroupActive(item)
                      ? 'text-[#0B5D3B] dark:text-[#75bd97] font-extrabold'
                      : 'text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60',
                  ]"
                  @click="toggleAccordion(item.id)"
                >
                  <div class="flex items-center gap-3 min-w-0">
                    <component
                      :is="item.icon"
                      :class="[
                        'h-4 w-4 shrink-0 transition-transform group-hover:scale-105',
                        isGroupActive(item) ? 'text-[#0B5D3B] dark:text-[#75bd97]' : 'text-slate-500 dark:text-slate-400',
                      ]"
                    />
                    <span class="truncate">{{ t(item.titleKey) }}</span>
                  </div>

                  <ChevronDown
                    :class="[
                      'h-3.5 w-3.5 text-slate-400 dark:text-slate-500 transition-transform duration-200 ease-in-out shrink-0',
                      isOpen(item.id) ? 'rotate-180 text-slate-600 dark:text-slate-300' : 'rotate-0',
                    ]"
                  />
                </button>

                <!-- Collapsible Children Container (Smooth CSS Grid Transition) -->
                <div
                  :class="[
                    'grid transition-all duration-200 ease-in-out',
                    isOpen(item.id) ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0 pointer-events-none',
                  ]"
                >
                  <div class="overflow-hidden">
                    <div class="ml-4 pl-3.5 border-l-2 border-slate-200 dark:border-slate-800 space-y-0.5 py-1">
                      <RouterLink
                        v-for="child in item.visibleChildren"
                        :key="child.id"
                        :to="resolvePath(child.path)"
                        :class="[
                          'flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-colors duration-150 select-none group',
                          isRouteActive(child.path)
                            ? 'bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] font-bold shadow-2xs'
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60',
                        ]"
                        @click="handleNavClick"
                      >
                        <component
                          :is="child.icon"
                          :class="[
                            'h-3.5 w-3.5 shrink-0 transition-transform group-hover:scale-105',
                            isRouteActive(child.path)
                              ? 'text-[#0B5D3B] dark:text-[#75bd97]'
                              : 'text-slate-400 dark:text-slate-500',
                          ]"
                        />
                        <span class="truncate">{{ t(child.titleKey) }}</span>
                      </RouterLink>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </template>
        </div>
      </div>
    </nav>

    <!-- Bottom Actions Container (Anchored with Divider) -->
    <div class="border-t border-slate-200 dark:border-[#1E293B] shrink-0 p-3 space-y-1 bg-white/95 dark:bg-[#0F172A]/95">
      <template v-for="bottomItem in bottomItems" :key="bottomItem.id">
        <!-- Collapsed Bottom Item -->
        <AppTooltip
          v-if="uiStore.sidebarCollapsed"
          :text="t(bottomItem.titleKey)"
          position="right"
          class="w-full"
        >
          <RouterLink
            :to="resolvePath(bottomItem.path)"
            :class="[
              'flex items-center justify-center h-10 w-full rounded-xl transition-colors duration-150 select-none',
              isRouteActive(bottomItem.path)
                ? 'bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] font-bold shadow-2xs'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60',
            ]"
            @click="handleNavClick"
          >
            <component :is="bottomItem.icon" class="h-4.5 w-4.5 shrink-0" />
          </RouterLink>
        </AppTooltip>

        <!-- Expanded Bottom Item -->
        <RouterLink
          v-else
          :to="resolvePath(bottomItem.path)"
          :class="[
            'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-150 select-none group',
            isRouteActive(bottomItem.path)
              ? 'bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] shadow-2xs font-bold'
              : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60',
          ]"
          @click="handleNavClick"
        >
          <component
            :is="bottomItem.icon"
            :class="[
              'h-4 w-4 shrink-0 transition-transform group-hover:scale-105',
              isRouteActive(bottomItem.path)
                ? 'text-[#0B5D3B] dark:text-[#75bd97]'
                : 'text-slate-500 dark:text-slate-400',
            ]"
          />
          <span class="truncate">{{ t(bottomItem.titleKey) }}</span>
        </RouterLink>
      </template>
    </div>
  </aside>
</template>
