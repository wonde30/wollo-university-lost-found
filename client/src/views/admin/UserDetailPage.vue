<script setup lang="ts">
import { onMounted, ref, reactive, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAdminUsers } from '@/features/admin/composables/useAdminUsers'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { formatDate } from '@/utils/date'
import type { UpdateUserData } from '@/features/admin/types/admin.types'
import * as adminApi from '@/features/admin/api/admin.api'
import type { UserPermissionsPayload } from '@/features/admin/api/admin.api'
import UserRoleBadge from '@/features/admin/components/UserRoleBadge.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppSpinner from '@/components/ui/AppSpinner.vue'
import { t, currentLocale } from '@/i18n'
import { usePermissionsStore } from '@/permissions/stores/permissions.store'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import {
  User as UserIcon,
  Shield,
  Key,
  Search,
  Save,
  Sparkles,
  Info,
  ArrowLeft,
  Edit3,
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const uiStore = useUiStore()
const authStore = useAuthStore()
const permissionsStore = usePermissionsStore()
const { currentUser, loading, error, fetchUser, updateUser, updateUserRole, toggleUserActive } = useAdminUsers()

const userId = computed(() => Number(route.params.id))
const activeTab = ref<'profile' | 'permissions'>('profile')
const editMode = ref(false)
const saving = ref(false)

// Form state for basic profile
const form = reactive({
  full_name: '',
  email: '',
  phone: '',
  university_id: '',
  is_active: true,
})

// Permissions Management State
const permissionsLoading = ref(false)
const savingPermissions = ref(false)
const userPermsData = ref<UserPermissionsPayload | null>(null)
const selectedDirectPermIds = ref<number[]>([])
const permissionSearch = ref('')
const selectedCategory = ref<string>('all')

const dynamicRoleOptions = computed(() => {
  if (permissionsStore.roles.length > 0) {
    return permissionsStore.roles.map(r => ({
      name: r.name,
      label: (currentLocale.value === 'am' && r.display_name_am) ? r.display_name_am : (r.display_name || r.name),
    }))
  }
  return [
    { name: 'student', label: t('admin.roles.student') },
    { name: 'staff', label: t('admin.roles.staff') },
    { name: 'admin', label: t('admin.roles.admin') },
  ]
})

// Categories list for permission filter
const permissionCategories = computed(() => {
  if (!userPermsData.value) return ['all']
  const cats = new Set(userPermsData.value.available_permissions.map(p => p.category || 'general'))
  return ['all', ...Array.from(cats)]
})

// Grouped and filtered permissions
const filteredPermissions = computed(() => {
  if (!userPermsData.value) return []
  let list = userPermsData.value.available_permissions

  if (selectedCategory.value !== 'all') {
    list = list.filter(p => (p.category || 'general') === selectedCategory.value)
  }

  const query = permissionSearch.value.trim().toLowerCase()
  if (query) {
    list = list.filter(
      p =>
        p.name.toLowerCase().includes(query) ||
        p.display_name.toLowerCase().includes(query) ||
        (p.description && p.description.toLowerCase().includes(query))
    )
  }

  return list
})

// Check if a permission is inherited from role
function isRoleInherited(permName: string): boolean {
  if (!userPermsData.value) return false
  if (currentUser.value?.role === 'admin') return true
  return userPermsData.value.role_permissions.includes(permName)
}

// Check if a permission is directly granted to this user
function isDirectlyGranted(permId: number): boolean {
  return selectedDirectPermIds.value.includes(permId)
}

// Check if a permission is effectively granted (role OR direct)
function isEffectivelyGranted(perm: { id: number; name: string }): boolean {
  return isRoleInherited(perm.name) || isDirectlyGranted(perm.id)
}

function toggleDirectPermission(permId: number) {
  const index = selectedDirectPermIds.value.indexOf(permId)
  if (index !== -1) {
    selectedDirectPermIds.value.splice(index, 1)
  } else {
    selectedDirectPermIds.value.push(permId)
  }
}

function grantAllAvailable() {
  if (!userPermsData.value) return
  const allIds = userPermsData.value.available_permissions.map(p => p.id)
  selectedDirectPermIds.value = Array.from(new Set([...selectedDirectPermIds.value, ...allIds]))
}

function clearDirectPermissions() {
  selectedDirectPermIds.value = []
}

async function loadUserPermissions() {
  if (!userId.value) return
  permissionsLoading.value = true
  try {
    const data = await adminApi.getUserPermissions(userId.value)
    userPermsData.value = data
    selectedDirectPermIds.value = [...data.direct_permission_ids]
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to load user permissions'))
  } finally {
    permissionsLoading.value = false
  }
}

async function handleSavePermissions() {
  if (!userId.value) return
  savingPermissions.value = true
  try {
    await adminApi.syncUserPermissions(userId.value, selectedDirectPermIds.value)
    uiStore.success(t('admin.users.updateSuccess') || 'User permissions updated successfully')
    await loadUserPermissions()
    if (authStore.user?.id === userId.value) {
      await authStore.fetchUser(true)
    }
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to save user permissions'))
  } finally {
    savingPermissions.value = false
  }
}

async function load() {
  if (!userId.value || isNaN(userId.value)) {
    uiStore.error(t('common.errorOccurred'))
    router.push('/admin/users')
    return
  }
  
  const user = await fetchUser(userId.value)
  if (user) {
    form.full_name = user.full_name || ''
    form.email = user.email || ''
    form.phone = user.phone || ''
    form.university_id = user.university_id || ''
    form.is_active = user.is_active
    await loadUserPermissions()
  } else {
    uiStore.error(t('common.errorOccurred'))
    router.push('/admin/users')
  }
}

async function handleSave() {
  if (!currentUser.value) return
  
  saving.value = true
  try {
    const updateData: UpdateUserData = {
      full_name: form.full_name.trim(),
      email: form.email.trim(),
      phone: form.phone.trim() || undefined,
      university_id: form.university_id.trim(),
      is_active: form.is_active,
    }
    
    await updateUser(currentUser.value.id, updateData)
    editMode.value = false
    uiStore.success(t('admin.users.updateSuccess'))
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    saving.value = false
  }
}

function cancelEdit() {
  if (currentUser.value) {
    form.full_name = currentUser.value.full_name || ''
    form.email = currentUser.value.email || ''
    form.phone = currentUser.value.phone || ''
    form.university_id = currentUser.value.university_id || ''
    form.is_active = currentUser.value.is_active
  }
  editMode.value = false
}

async function handleRoleChange(newRole: string) {
  if (!currentUser.value) return
  
  try {
    await updateUserRole(currentUser.value.id, newRole)
    await loadUserPermissions()
    if (authStore.user?.id === currentUser.value.id) {
      await authStore.fetchUser(true)
    }
    uiStore.success(t('admin.users.roleUpdatedSuccess'))
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  }
}

async function handleToggleActive() {
  if (!currentUser.value) return
  
  try {
    await toggleUserActive(currentUser.value.id)
    uiStore.success(t('admin.users.statusUpdated'))
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  }
}

onMounted(async () => {
  permissionsStore.fetchRoles()
  await load()
})
</script>

<template>
  <div class="space-y-4 sm:space-y-5">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <AppButton variant="ghost" size="sm" @click="router.push('/admin/users')">
          <template #icon-left>
            <ArrowLeft class="h-4 w-4 mr-1" />
          </template>
          {{ t('common.back') }}
        </AppButton>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
              {{ currentUser?.full_name || t('admin.users.title') }}
            </h1>
            <UserRoleBadge v-if="currentUser" :role="currentUser.role" />
          </div>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-mono">
            {{ currentUser?.university_id ? `ID: ${currentUser.university_id} • ` : '' }}{{ currentUser?.email }}
          </p>
        </div>
      </div>

      <!-- Quick Action Controls -->
      <div v-if="currentUser" class="flex items-center gap-2 shrink-0">
        <AppButton
          v-if="activeTab === 'profile' && !editMode"
          variant="outline"
          size="sm"
          @click="editMode = true"
        >
          <template #icon-left>
            <Edit3 class="h-3.5 w-3.5 mr-1" />
          </template>
          {{ t('common.edit') }}
        </AppButton>

        <AppButton
          :variant="currentUser.is_active ? 'danger' : 'primary'"
          size="sm"
          @click="handleToggleActive"
        >
          {{ currentUser.is_active ? t('admin.users.suspendBtn') : t('admin.users.activateBtn') }}
        </AppButton>
      </div>
    </div>

    <!-- Loading Skeleton State -->
    <div v-if="loading && !currentUser" class="space-y-6 animate-pulse">
      <div class="p-6 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 flex flex-col sm:flex-row items-center gap-5">
        <div class="h-20 w-20 rounded-full bg-slate-200 dark:bg-slate-800 shrink-0" />
        <div class="space-y-2 flex-1 w-full text-center sm:text-left">
          <div class="h-6 w-48 bg-slate-200 dark:bg-slate-800 rounded mx-auto sm:mx-0" />
          <div class="h-4 w-64 bg-slate-100 dark:bg-slate-800/60 rounded mx-auto sm:mx-0" />
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="n in 2" :key="n" class="p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 space-y-3">
          <div class="h-5 w-32 bg-slate-200 dark:bg-slate-800 rounded" />
          <div class="h-4 w-full bg-slate-100 dark:bg-slate-800/60 rounded" />
          <div class="h-4 w-3/4 bg-slate-100 dark:bg-slate-800/60 rounded" />
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-16 space-y-3">
      <p class="text-rose-600 font-bold">{{ error }}</p>
      <AppButton variant="outline" size="sm" @click="load">
        {{ t('errors.500.tryAgain') }}
      </AppButton>
    </div>

    <!-- Main Content Area -->
    <div v-else-if="currentUser" class="space-y-6">
      <!-- Top Tabs Navigation -->
      <div class="border-b border-slate-200 dark:border-slate-800 flex items-center gap-2">
        <button
          type="button"
          :class="[
            'px-4 py-2.5 font-bold text-sm border-b-2 transition-all cursor-pointer flex items-center gap-2',
            activeTab === 'profile'
              ? 'border-[#0B5D3B] text-[#0B5D3B] dark:text-[#75bd97] dark:border-[#75bd97]'
              : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200',
          ]"
          @click="activeTab = 'profile'"
        >
          <UserIcon class="h-4 w-4" />
          <span>User Profile & Account</span>
        </button>

        <button
          type="button"
          :class="[
            'px-4 py-2.5 font-bold text-sm border-b-2 transition-all cursor-pointer flex items-center gap-2',
            activeTab === 'permissions'
              ? 'border-[#0B5D3B] text-[#0B5D3B] dark:text-[#75bd97] dark:border-[#75bd97]'
              : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200',
          ]"
          @click="activeTab = 'permissions'"
        >
          <Key class="h-4 w-4" />
          <span>Direct Permissions & Overrides</span>
          <span
            v-if="userPermsData"
            class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97]"
          >
            {{ userPermsData.effective_permissions.length }} active
          </span>
        </button>
      </div>

      <!-- TAB 1: Profile & Account Information -->
      <div v-if="activeTab === 'profile'" class="space-y-6">
        <AppCard>
          <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-white">Account Details</h2>
              <p class="text-xs text-slate-500 dark:text-slate-400">Institutional identity and profile parameters</p>
            </div>
            <div class="flex items-center gap-2">
              <AppBadge
                :variant="currentUser.is_active ? 'success' : 'default'"
                :text="currentUser.is_active ? t('admin.users.active') : t('admin.users.suspended')"
              />
            </div>
          </div>

          <div v-if="!editMode" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 text-sm">
            <div>
              <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                {{ t('admin.users.name') }}
              </span>
              <p class="font-bold text-slate-900 dark:text-white">{{ currentUser.full_name }}</p>
            </div>

            <div>
              <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                {{ t('auth.register.idNumber') }}
              </span>
              <p class="font-mono font-bold text-slate-900 dark:text-white">{{ currentUser.university_id || '—' }}</p>
            </div>

            <div>
              <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                {{ t('auth.login.email') }}
              </span>
              <p class="text-slate-900 dark:text-white">{{ currentUser.email }}</p>
            </div>

            <div>
              <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                {{ t('auth.register.phone') }}
              </span>
              <p class="text-slate-900 dark:text-white">{{ currentUser.phone || '—' }}</p>
            </div>

            <div>
              <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                {{ t('admin.users.role') }}
              </span>
              <div class="flex items-center gap-2">
                <select
                  :value="currentUser.role"
                  class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white px-2.5 py-1 text-xs font-bold focus:ring-[#0B5D3B]"
                  @change="handleRoleChange(($event.target as HTMLSelectElement).value)"
                >
                  <option v-for="role in dynamicRoleOptions" :key="role.name" :value="role.name">
                    {{ role.label }}
                  </option>
                </select>
              </div>
            </div>

            <div>
              <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">
                {{ t('admin.users.joined') }}
              </span>
              <p class="text-slate-700 dark:text-slate-300">{{ formatDate(currentUser.created_at || '', 'long') }}</p>
            </div>
          </div>

          <!-- Edit Form -->
          <div v-else class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <AppInput
                id="full-name"
                :label="t('admin.users.name')"
                :model-value="form.full_name"
                required
                @update:model-value="form.full_name = $event"
              />
              <AppInput
                id="university-id"
                :label="t('auth.register.idNumber')"
                :model-value="form.university_id"
                required
                @update:model-value="form.university_id = $event"
              />
              <AppInput
                id="email"
                :label="t('auth.login.email')"
                type="email"
                :model-value="form.email"
                required
                @update:model-value="form.email = $event"
              />
              <AppInput
                id="phone"
                :label="t('auth.register.phone')"
                type="tel"
                :model-value="form.phone"
                @update:model-value="form.phone = $event"
              />
            </div>

            <div class="flex items-center gap-2 pt-2">
              <input
                id="is-active"
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-[#0B5D3B] border-slate-300 rounded focus:ring-[#0B5D3B]"
              />
              <label for="is-active" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                {{ t('admin.users.active') }}
              </label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
              <AppButton variant="ghost" @click="cancelEdit">
                {{ t('common.cancel') }}
              </AppButton>
              <AppButton variant="primary" :loading="saving" @click="handleSave">
                {{ t('common.save') }}
              </AppButton>
            </div>
          </div>
        </AppCard>
      </div>

      <!-- TAB 2: Dynamic User Permissions Management -->
      <div v-if="activeTab === 'permissions'" class="space-y-5">
        <!-- Info Banner explaining Role vs User Permissions -->
        <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/40 dark:to-indigo-950/40 border border-blue-200/80 dark:border-blue-800/80 flex items-start gap-3">
          <Info class="h-5 w-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" />
          <div class="text-xs text-blue-950 dark:text-blue-200 space-y-1">
            <p class="font-bold text-sm text-blue-900 dark:text-blue-100">
              Per-User Permission Overrides
            </p>
            <p>
              Users automatically inherit base capabilities from their assigned role (<span class="font-bold uppercase">{{ currentUser.role }}</span>).
              You can grant specific additional permissions to this individual user without altering the role for everyone else.
            </p>
          </div>
        </div>

        <!-- Toolbar: Search, Filters & Bulk Helpers -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="flex items-center gap-2 flex-1 max-w-md">
            <div class="relative flex-1">
              <AppInput
                id="perm-search"
                placeholder="Search permissions by name or capability..."
                :model-value="permissionSearch"
                class="w-full text-xs"
                @update:model-value="permissionSearch = $event"
              >
                <template #icon-left>
                  <Search class="h-4 w-4 text-slate-400" />
                </template>
              </AppInput>
            </div>

            <select
              v-model="selectedCategory"
              class="h-10 px-3 text-xs font-bold rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-[#0B5D3B] cursor-pointer"
            >
              <option v-for="cat in permissionCategories" :key="cat" :value="cat">
                {{ cat === 'all' ? 'All Categories' : cat.toUpperCase() }}
              </option>
            </select>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <AppButton variant="secondary" size="xs" @click="grantAllAvailable">
              Grant All
            </AppButton>
            <AppButton variant="secondary" size="xs" @click="clearDirectPermissions">
              Reset to Role Defaults
            </AppButton>
            <AppButton
              variant="primary"
              size="sm"
              :loading="savingPermissions"
              @click="handleSavePermissions"
            >
              <template #icon-left>
                <Save class="h-3.5 w-3.5 mr-1" />
              </template>
              Save User Permissions
            </AppButton>
          </div>
        </div>

        <!-- Loading Permissions Spinner -->
        <div v-if="permissionsLoading" class="flex items-center justify-center py-12">
          <AppSpinner size="md" />
          <span class="ml-2 text-xs font-semibold text-slate-500">Loading permission matrix...</span>
        </div>

        <!-- Permission Matrix Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <div
            v-for="perm in filteredPermissions"
            :key="perm.id"
            :class="[
              'p-3.5 rounded-2xl border transition-all relative select-none flex flex-col justify-between gap-3',
              isEffectivelyGranted(perm)
                ? 'bg-white dark:bg-[#111827] border-emerald-300 dark:border-emerald-700/80 shadow-2xs'
                : 'bg-slate-50/60 dark:bg-slate-900/50 border-slate-200 dark:border-slate-800 opacity-75',
            ]"
          >
            <div>
              <!-- Header with Badges -->
              <div class="flex items-center justify-between gap-2 mb-2">
                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                  {{ perm.category || 'General' }}
                </span>

                <div class="flex items-center gap-1">
                  <!-- Inherited from Role Badge -->
                  <span
                    v-if="isRoleInherited(perm.name)"
                    class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800"
                    title="Granted automatically via assigned role"
                  >
                    <Shield class="h-2.5 w-2.5" />
                    Role ({{ currentUser.role }})
                  </span>

                  <!-- Direct Custom User Badge -->
                  <span
                    v-if="isDirectlyGranted(perm.id)"
                    class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
                    title="Specifically granted to this individual user"
                  >
                    <Sparkles class="h-2.5 w-2.5" />
                    Direct
                  </span>
                </div>
              </div>

              <!-- Permission Name & Description -->
              <h4 class="font-bold text-xs text-slate-900 dark:text-white font-mono">
                {{ perm.name }}
              </h4>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed line-clamp-2">
                {{ perm.description || perm.display_name }}
              </p>
            </div>

            <!-- Interactive Toggle for Direct Assignment -->
            <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
              <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                <input
                  type="checkbox"
                  :checked="isDirectlyGranted(perm.id)"
                  class="h-4 w-4 text-[#0B5D3B] border-slate-300 rounded focus:ring-[#0B5D3B] cursor-pointer"
                  @change="toggleDirectPermission(perm.id)"
                />
                <span>Direct User Grant</span>
              </label>

              <span
                v-if="isRoleInherited(perm.name)"
                class="text-[10px] font-bold text-blue-600 dark:text-blue-400"
              >
                Active by Role
              </span>
              <span
                v-else-if="isDirectlyGranted(perm.id)"
                class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400"
              >
                Active by Direct
              </span>
              <span
                v-else
                class="text-[10px] font-medium text-slate-400"
              >
                Disabled
              </span>
            </div>
          </div>
        </div>

        <!-- Sticky Save Floating Footer Bar when dirty -->
        <div class="sticky bottom-4 z-20 p-4 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-lg flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] flex items-center justify-center font-bold">
              {{ selectedDirectPermIds.length }}
            </div>
            <div>
              <p class="text-xs font-bold text-slate-900 dark:text-white">Custom Direct Permissions Assigned</p>
              <p class="text-[11px] text-slate-500">Effective permissions total: {{ userPermsData?.effective_permissions.length || 0 }}</p>
            </div>
          </div>

          <AppButton
            variant="primary"
            size="md"
            :loading="savingPermissions"
            @click="handleSavePermissions"
          >
            <template #icon-left>
              <Save class="h-4 w-4 mr-1" />
            </template>
            Save User Permissions
          </AppButton>
        </div>
      </div>
    </div>
  </div>
</template>