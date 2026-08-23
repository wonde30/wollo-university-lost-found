<script setup lang="ts">
import type { User } from '@/features/auth/types/auth.types'
import { formatDate } from '@/utils/date'
import AppAvatar from '@/components/ui/AppAvatar.vue'
import AppButton from '@/components/ui/AppButton.vue'
import UserRoleBadge from './UserRoleBadge.vue'

interface Props {
  users: User[]
  loading?: boolean
}

withDefaults(defineProps<Props>(), { loading: false })

const emit = defineEmits<{
  (e: 'change-role', user: User, role: string): void
  (e: 'toggle-active', user: User): void
}>()

const roleOptions = ['admin', 'staff', 'student']
</script>

<template>
  <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-xs">
    <table class="w-full min-w-[640px] text-xs">
      <thead>
        <tr class="border-b border-slate-200 bg-slate-50">
          <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">User</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Role</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Status</th>
          <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Joined</th>
          <th class="px-4 py-3 text-right font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody v-if="loading && users.length === 0">
        <tr>
          <td colspan="5" class="px-4 py-8 text-center text-slate-400">Loading users...</td>
        </tr>
      </tbody>
      <tbody v-else-if="users.length === 0">
        <tr>
          <td colspan="5" class="px-4 py-8 text-center text-slate-400">No users found.</td>
        </tr>
      </tbody>
      <tbody v-else class="divide-y divide-slate-100">
        <tr
          v-for="user in users"
          :key="user.id"
          class="hover:bg-slate-50/50 transition-colors"
        >
          <!-- User -->
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <AppAvatar :name="user.full_name" size="sm" />
              <div>
                <p class="font-semibold text-slate-900">{{ user.full_name }}</p>
                <p class="text-slate-400">{{ user.email }}</p>
              </div>
            </div>
          </td>

          <!-- Role -->
          <td class="px-4 py-3">
            <UserRoleBadge :role="user.role" />
          </td>

          <!-- Status -->
          <td class="px-4 py-3">
            <span
              :class="[
                'inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-semibold',
                (user as any).is_active !== false ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500',
              ]"
            >
              <span class="h-1.5 w-1.5 rounded-full" :class="(user as any).is_active !== false ? 'bg-emerald-500' : 'bg-slate-400'" />
              {{ (user as any).is_active !== false ? 'Active' : 'Suspended' }}
            </span>
          </td>

          <!-- Joined -->
          <td class="px-4 py-3 text-slate-500">
            {{ formatDate(user.created_at || '', 'short') }}
          </td>

          <!-- Actions -->
          <td class="px-4 py-3 text-right">
            <div class="flex items-center justify-end gap-2">
              <RouterLink
                :to="{ name: 'admin-user-detail', params: { id: user.id } }"
                class="inline-flex items-center gap-1 rounded-lg bg-slate-100 text-slate-700 px-2 py-1 text-xs font-medium hover:bg-slate-200 transition-colors"
              >
                View
              </RouterLink>
              
              <select
                :value="user.role"
                class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-[#0F5132]/30"
                @change="emit('change-role', user, ($event.target as HTMLSelectElement).value)"
              >
                <option v-for="r in roleOptions" :key="r" :value="r">{{ r }}</option>
              </select>

              <AppButton
                :variant="(user as any).is_active !== false ? 'danger' : 'primary'"
                size="xs"
                @click="emit('toggle-active', user)"
              >
                {{ (user as any).is_active !== false ? 'Suspend' : 'Activate' }}
              </AppButton>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
