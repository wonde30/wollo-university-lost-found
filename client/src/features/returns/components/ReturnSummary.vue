<script setup lang="ts">
import type { ItemReturn } from '../types/return.types'
import { formatDate } from '@/utils/date'
import AppCard from '@/components/ui/AppCard.vue'
import AppBadge from '@/components/ui/AppBadge.vue'

interface Props {
  itemReturn: ItemReturn
}

defineProps<Props>()
</script>

<template>
  <AppCard>
    <template #header>
      <div class="flex items-center justify-between w-full">
        <h4 class="text-sm font-bold text-slate-900">Return Handover Certificate</h4>
        <AppBadge variant="success" size="sm">Completed</AppBadge>
      </div>
    </template>

    <div class="space-y-3 text-xs">
      <div class="grid grid-cols-2 gap-3">
        <div>
          <span class="text-slate-400 block">Item</span>
          <span class="font-semibold text-slate-800">{{ itemReturn.item?.title || `#${itemReturn.item_id}` }}</span>
        </div>
        <div>
          <span class="text-slate-400 block">Return Date</span>
          <span class="font-semibold text-slate-800">{{ formatDate(itemReturn.return_date, 'medium') }}</span>
        </div>
        <div>
          <span class="text-slate-400 block">Recipient</span>
          <span class="font-semibold text-slate-800">{{ itemReturn.recipient?.full_name || `#${itemReturn.returned_to}` }}</span>
        </div>
        <div>
          <span class="text-slate-400 block">Processed By</span>
          <span class="font-semibold text-slate-800">{{ itemReturn.staff?.full_name || 'Staff' }}</span>
        </div>
      </div>

      <div v-if="itemReturn.condition_on_return" class="pt-2 border-t border-slate-100">
        <span class="text-slate-400 block">Condition Verified</span>
        <p class="text-slate-700">{{ itemReturn.condition_on_return }}</p>
      </div>

      <div v-if="itemReturn.notes" class="pt-2 border-t border-slate-100">
        <span class="text-slate-400 block">Handover Notes</span>
        <p class="text-slate-700">{{ itemReturn.notes }}</p>
      </div>
    </div>
  </AppCard>
</template>
