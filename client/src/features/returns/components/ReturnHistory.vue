<script setup lang="ts">
import type { ItemReturn } from '../types/return.types'
import { formatDate } from '@/utils/date'
import AppBadge from '@/components/ui/AppBadge.vue'

interface Props {
  returns: ItemReturn[]
}

defineProps<Props>()
</script>

<template>
  <div class="space-y-3">
    <div v-if="returns.length === 0" class="p-6 text-center text-xs text-slate-400 border border-dashed rounded-xl">
      No return handovers recorded yet.
    </div>

    <div
      v-for="ret in returns"
      :key="ret.id"
      class="rounded-xl border border-slate-200 bg-white p-4 space-y-2 text-xs shadow-2xs"
    >
      <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2">
          <AppBadge variant="success" size="sm">
            Handed Over
          </AppBadge>
          <span class="font-bold text-slate-900">
            {{ ret.item?.title || `Item #${ret.item_id}` }}
          </span>
        </div>
        <span class="text-[11px] text-slate-400">{{ formatDate(ret.return_date, 'short') }}</span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-slate-600 bg-slate-50 p-2.5 rounded-lg">
        <p><span class="font-medium text-slate-700">Returned To:</span> {{ ret.recipient?.full_name || `User #${ret.returned_to}` }}</p>
        <p><span class="font-medium text-slate-700">Handled By:</span> {{ ret.staff?.full_name || 'Staff' }}</p>
        <p v-if="ret.condition_on_return"><span class="font-medium text-slate-700">Condition:</span> {{ ret.condition_on_return }}</p>
        <p v-if="ret.notes"><span class="font-medium text-slate-700">Notes:</span> {{ ret.notes }}</p>
      </div>
    </div>
  </div>
</template>
