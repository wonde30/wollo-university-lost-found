<script setup lang="ts">
import type { CustodyEvent } from '../types/custody.types'
import { formatDateTime } from '@/utils/date'
import { formatStatus } from '@/utils/formatters'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'

interface Props {
  events: CustodyEvent[]
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'transfer', payload: { id: number; title?: string }): void
}>()
</script>

<template>
  <div class="space-y-3">
    <div v-if="events.length === 0" class="p-6 text-center text-xs text-slate-400 border border-dashed rounded-xl">
      No physical custody events recorded yet.
    </div>

    <div
      v-for="ev in events"
      :key="ev.id"
      class="rounded-xl border border-slate-200 bg-white p-4 space-y-2 text-xs"
    >
      <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2">
          <AppBadge variant="primary" size="sm">
            {{ formatStatus(ev.event_type) }}
          </AppBadge>
          <span class="font-bold text-slate-900">
            {{ ev.item?.title || `Item #${ev.item_id}` }}
          </span>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-[11px] text-slate-400">{{ formatDateTime(ev.created_at) }}</span>
          <AppButton
            v-if="ev.item_id"
            size="xs"
            variant="outline"
            @click="emit('transfer', { id: ev.item_id, title: ev.item?.title })"
          >
            Move / Transfer
          </AppButton>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-slate-600 bg-slate-50 p-2.5 rounded-lg">
        <p><span class="font-medium text-slate-700">Storage:</span> {{ ev.storage_location?.name || 'Unspecified' }}</p>
        <p><span class="font-medium text-slate-700">Handler:</span> {{ ev.actor?.full_name || 'Staff' }}</p>
        <p v-if="ev.condition"><span class="font-medium text-slate-700">Physical Condition:</span> {{ ev.condition }}</p>
        <p v-if="ev.notes"><span class="font-medium text-slate-700">Notes:</span> {{ ev.notes }}</p>
      </div>
    </div>
  </div>
</template>
