<script setup lang="ts">
import { onMounted, ref, reactive, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { useAdminUsers } from '@/features/admin/composables/useAdminUsers'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { formatDate } from '@/utils/date'
import type { UpdateUserData } from '@/features/admin/types/admin.types'
import UserRoleBadge from '@/features/admin/components/UserRoleBadge.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppSpinner from '@/components/ui/AppSpinner.vue'

const route = useRoute()
const router = useRouter()
const uiStore = useUiStore()
const { currentUser, loading, error, fetchUser, updateUser, updateUserRole, toggleUserActive } = useAdminUsers()

const userId = computed(() => Number(route.params.id))
const editMode = ref(false)
const saving = ref(false)

const form = reactive({
  full_name: '',
  email: '',
  phone: '',
  university_id: '',
  is_active: true
})

const roleOptions = ['student', 'staff', 'admin']

async function load() {
  if (!userId.value || isNaN(userId.value)) {
    uiStore.error('Invalid user ID')
    router.push('/admin/users')
    return
  }
  
  const user = await fetchUser(userId.value)
  if (user) {
    // Populate form
    form.full_name = user.full_name || ''
    form.email = user.email || ''
    form.phone = user.phone || ''
    form.university_id = user.university_id || ''
    form.is_active = user.is_active
  } else {
    uiStore.error('User not found')
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
      is_active: form.is_active
    }
    
    await updateUser(currentUser.value.id, updateData)
    editMode.value = false
    uiStore.success('User updated successfully')
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to update user'))
  } finally {
    saving.value = false
  }
}

function cancelEdit() {
  if (currentUser.value) {
    // Reset form to original values
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
    uiStore.success(`User role updated to ${newRole}`)
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to update user role'))
  }
}

async function handleToggleActive() {
  if (!currentUser.value) return
  
  try {
    await toggleUserActive(currentUser.value.id)
    const newStatus = currentUser.value.is_active ? 'activated' : 'suspended'
    uiStore.success(`User ${newStatus} successfully`)
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to update user status'))
  }
}

onMounted(load)
</script>

<template>
  <DashboardLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <AppButton variant="ghost" size="sm" @click="router.push('/admin/users')">
            ← Back to Users
          </AppButton>
          <div>
            <h1 class="text-xl font-black text-slate-900">User Details</h1>
            <p class="text-sm text-slate-500 mt-0.5">View and manage user information</p>
          </div>
        </div>
        <div v-if="currentUser && !editMode" class="flex gap-2">
          <AppButton variant="outline" size="sm" @click="editMode = true">
            Edit Details
          </AppButton>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && !currentUser" class="flex items-center justify-center py-12">
        <AppSpinner size="lg" />
        <span class="ml-3 text-slate-600">Loading user details...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <p class="text-red-600 font-medium">{{ error }}</p>
        <AppButton variant="outline" size="sm" class="mt-4" @click="load">
          Retry
        </AppButton>
      </div>

      <!-- User Details -->
      <div v-else-if="currentUser" class="space-y-6">
        <!-- Basic Information Card -->
        <AppCard>
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-slate-900">Basic Information</h2>
            <div class="flex items-center gap-3">
              <UserRoleBadge :role="currentUser.role" />
              <AppBadge
                :variant="currentUser.is_active ? 'success' : 'default'"
                :text="currentUser.is_active ? 'Active' : 'Suspended'"
              />
            </div>
          </div>

          <div v-if="!editMode" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Full Name</label>
              <p class="text-slate-900">{{ currentUser.full_name }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">University ID</label>
              <p class="text-slate-900 font-mono">{{ currentUser.university_id }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
              <p class="text-slate-900">{{ currentUser.email }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Phone</label>
              <p class="text-slate-900">{{ currentUser.phone || '—' }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Role</label>
              <p class="text-slate-900 capitalize">{{ currentUser.role }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Status</label>
              <p class="text-slate-900">{{ currentUser.is_active ? 'Active' : 'Suspended' }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Joined</label>
              <p class="text-slate-900">{{ formatDate(currentUser.created_at || '', 'long') }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1">Last Updated</label>
              <p class="text-slate-900">{{ formatDate(currentUser.updated_at || '', 'long') }}</p>
            </div>
          </div>

          <!-- Edit Form -->
          <div v-else class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <AppInput
                id="full-name"
                label="Full Name"
                :model-value="form.full_name"
                required
                @update:model-value="form.full_name = $event"
              />
              <AppInput
                id="university-id"
                label="University ID"
                :model-value="form.university_id"
                required
                @update:model-value="form.university_id = $event"
              />
              <AppInput
                id="email"
                label="Email"
                type="email"
                :model-value="form.email"
                required
                @update:model-value="form.email = $event"
              />
              <AppInput
                id="phone"
                label="Phone"
                type="tel"
                :model-value="form.phone"
                @update:model-value="form.phone = $event"
              />
            </div>

            <div class="flex items-center gap-3">
              <input
                id="is-active"
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-[#0F5132] border-slate-300 rounded focus:ring-[#0F5132]"
              />
              <label for="is-active" class="text-sm font-medium text-slate-700">
                User is active
              </label>
            </div>

            <div class="flex justify-end gap-3">
              <AppButton variant="ghost" @click="cancelEdit">
                Cancel
              </AppButton>
              <AppButton
                variant="primary"
                :loading="saving"
                @click="handleSave"
              >
                Save Changes
              </AppButton>
            </div>
          </div>
        </AppCard>

        <!-- Quick Actions Card -->
        <AppCard v-if="!editMode">
          <h2 class="text-lg font-bold text-slate-900 mb-4">Quick Actions</h2>
          <div class="flex flex-wrap gap-3">
            <!-- Role Change -->
            <div class="flex items-center gap-2">
              <label class="text-sm font-medium text-slate-700">Change Role:</label>
              <select
                :value="currentUser.role"
                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0F5132]/30"
                @change="handleRoleChange(($event.target as HTMLSelectElement).value)"
              >
                <option v-for="role in roleOptions" :key="role" :value="role">
                  {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                </option>
              </select>
            </div>

            <!-- Status Toggle -->
            <AppButton
              :variant="currentUser.is_active ? 'danger' : 'primary'"
              size="sm"
              @click="handleToggleActive"
            >
              {{ currentUser.is_active ? 'Suspend User' : 'Activate User' }}
            </AppButton>
          </div>
        </AppCard>
      </div>
    </div>
  </DashboardLayout>
</template>