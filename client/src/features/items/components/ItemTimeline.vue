<script setup lang="ts">
import type { ItemStatusHistory } from '../types/item.types'
import { formatDateTime } from '@/utils/date'
import { formatStatus } from '@/utils/formatters'

interface Props {
  histories?: ItemStatusHistory[]
}

withDefaults(defineProps<Props>(), {
  histories: () => [],
})
</script>

<template>
  <div class="space-y-4">
    <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Status History & Audit</h4>

    <div v-if="histories.length === 0" class="text-xs text-slate-400">
      No status transitions recorded yet.
    </div>

    <ol v-else class="relative border-l border-slate-200 ml-3 space-y-6">
      <li v-for="h in histories" :key="h.id" class="mb-6 ml-6">
        <span class="absolute -left-3 flex items-center justify-center w-6 h-6 bg-[#0F5132]/10 rounded-full ring-4 ring-white">
          <span class="w-2 h-2 bg-[#0F5132] rounded-full" />
        </span>
        <div class="flex items-center justify-between gap-2">
          <span class="text-xs font-semibold text-slate-900">{{ formatStatus(h.new_status) }}</span>
          <span class="text-[11px] text-slate-400">{{ formatDateTime(h.created_at) }}</span>
        </div>
        <p v-if="h.reason" class="text-xs text-slate-600 mt-1">Reason: {{ h.reason }}</p>
        <p v-if="h.notes" class="text-xs text-slate-500 mt-0.5">Notes: {{ h.notes }}</p>
        <span v-if="h.changed_by" class="text-[10px] text-slate-400 mt-1 block">
          By: {{ h.changed_by.full_name }} ({{ h.changed_by.role }})
        </span>
      </li>
    </ol>
  </div>
</template>
