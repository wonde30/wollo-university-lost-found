<script setup lang="ts">
import type { ItemStatusHistory } from '../types/item.types'
import type { User } from '@/features/auth/types/auth.types'
import { formatDateTime } from '@/utils/date'
import { formatStatus } from '@/utils/formatters'

interface Props {
  histories?: ItemStatusHistory[]
}

withDefaults(defineProps<Props>(), {
  histories: () => [],
})

function resolveChanger(h: ItemStatusHistory): string | null {
  // Backend sends changed_by as full UserResource object
  const userObj = h.changed_by as User | null | undefined
  if (userObj && typeof userObj === 'object' && userObj.full_name) {
    return userObj.full_name
  }
  // Fallback aliases
  if (h.changed_by_user?.full_name) return h.changed_by_user.full_name
  if (h.user?.full_name) return h.user.full_name
  return null
}

function resolveChangerRole(h: ItemStatusHistory): string | null {
  const userObj = h.changed_by as User | null | undefined
  if (userObj && typeof userObj === 'object' && userObj.role) {
    return String(userObj.role)
  }
  return h.changed_by_role || h.changed_by_user?.role || h.user?.role || null
}
</script>

<template>
  <div class="space-y-4">
    <h4 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Status History & Audit</h4>

    <div v-if="histories.length === 0" class="text-xs text-slate-400 dark:text-slate-500">
      No status transitions recorded yet.
    </div>

    <ol v-else class="relative border-l border-slate-200 dark:border-slate-800 ml-3 space-y-6">
      <li v-for="h in histories" :key="h.id" class="mb-6 ml-6">
        <span class="absolute -left-3 flex items-center justify-center w-6 h-6 bg-[#0B5D3B]/10 dark:bg-[#0B5D3B]/30 rounded-full ring-4 ring-white dark:ring-[#111827]">
          <span class="w-2 h-2 bg-[#0B5D3B] dark:bg-[#75bd97] rounded-full" />
        </span>
        <div class="flex items-center justify-between gap-2">
          <span class="text-xs font-semibold text-slate-900 dark:text-slate-200">
            {{ formatStatus(h.new_status || h.to_status) }}
          </span>
          <span class="text-[11px] text-slate-400 dark:text-slate-500">{{ formatDateTime(h.created_at) }}</span>
        </div>
        <p v-if="h.previous_status || h.from_status" class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
          From: {{ formatStatus(h.previous_status || h.from_status) }}
        </p>
        <p v-if="h.reason" class="text-xs text-slate-600 dark:text-slate-300 mt-1">Reason: {{ h.reason }}</p>
        <p v-if="h.notes" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Notes: {{ h.notes }}</p>
        <span v-if="resolveChanger(h)" class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 block">
          By: {{ resolveChanger(h) }}
          <span v-if="resolveChangerRole(h)">({{ resolveChangerRole(h) }})</span>
        </span>
      </li>
    </ol>
  </div>
</template>
