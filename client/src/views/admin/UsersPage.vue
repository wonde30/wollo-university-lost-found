<script setup lang="ts">
import { onMounted, reactive } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAdminUsers } from '@/features/admin/composables/useAdminUsers'
import UserTable from '@/features/admin/components/UserTable.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import { useUiStore } from '@/stores/ui.store'
import type { User, UserRole } from '@/features/auth/types/auth.types'

const { users, loading, pagination, fetchUsers, updateUserRole, toggleUserActive } = useAdminUsers()
const uiStore = useUiStore()

const filters = reactive<{ search: string; role: UserRole | ''; page: number }>({
  search: '',
  role: '',
  page: 1,
})

async function load(page = 1) {
  filters.page = page
  await fetchUsers({ page, search: filters.search || undefined, role: filters.role ? (filters.role as UserRole) : undefined })
}

let searchTimer: ReturnType<typeof setTimeout>
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => load(1), 300)
}

function handleChangeRole(user: User, role: string) {
  uiStore.confirm({
    title: 'Change User Role',
    message: `Are you sure you want to change the role of "${user.full_name}" to ${role.toUpperCase()}?`,
    confirmText: 'Change Role',
    variant: 'warning',
    onConfirm: async () => {
      await updateUserRole(user.id, role)
      uiStore.success(`${user.full_name}'s role updated to ${role}.`)
    },
  })
}

function handleToggleActive(user: User) {
  const isCurrentlyActive = (user as any).is_active !== false
  uiStore.confirm({
    title: isCurrentlyActive ? 'Suspend User Account' : 'Activate User Account',
    message: `Are you sure you want to ${isCurrentlyActive ? 'suspend' : 'activate'} "${user.full_name}"?`,
    confirmText: isCurrentlyActive ? 'Suspend Account' : 'Activate Account',
    variant: isCurrentlyActive ? 'danger' : 'primary',
    onConfirm: async () => {
      await toggleUserActive(user.id)
      uiStore.success(`${user.full_name} has been ${isCurrentlyActive ? 'suspended' : 'activated'}.`)
    },
  })
}

onMounted(() => load())
</script>

<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900">User Management</h1>
          <p class="text-xs text-slate-500 mt-1">
            {{ pagination.total }} registered user account(s) across Wollo University
          </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
          <AppInput
            id="users-search"
            placeholder="Search by name, email or student ID..."
            :model-value="filters.search"
            class="w-64"
            @update:model-value="filters.search = $event; onSearch()"
          />

          <AppSelect
            :options="[
              { label: 'All Roles', value: '' },
              { label: 'Administrator', value: 'admin' },
              { label: 'Staff Member', value: 'staff' },
              { label: 'Student', value: 'student' },
            ]"
            :model-value="filters.role"
            class="w-40"
            @update:model-value="filters.role = $event as any; load(1)"
          />
        </div>
      </div>

      <UserTable
        :users="users"
        :loading="loading"
        @change-role="handleChangeRole"
        @toggle-active="handleToggleActive"
      />

      <AppPagination
        v-if="pagination.last_page > 1"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        :per-page="pagination.per_page"
        @change="load"
      />
    </div>
  </DashboardLayout>
</template>
