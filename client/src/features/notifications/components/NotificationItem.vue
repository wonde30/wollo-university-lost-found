<script setup lang="ts">
import type { Notification } from '../types/notification.types'
import { formatRelativeTime } from '@/utils/date'

interface Props {
  notification: Notification
}

defineProps<Props>()

defineEmits<{
  (e: 'click'): void
  (e: 'mark-read'): void
}>()
</script>

<template>
  <div
    :class="[
      'flex items-start justify-between gap-4 p-4 rounded-xl transition-colors border',
      notification.read_at
        ? 'bg-white border-slate-200/80 hover:bg-slate-50'
        : 'bg-emerald-50/50 border-emerald-200/70 hover:bg-emerald-50',
    ]"
    @click="$emit('click')"
  >
    <div class="flex items-start gap-3 min-w-0">
      <span
        class="mt-1 h-2 w-2 rounded-full shrink-0"
        :class="notification.read_at ? 'bg-transparent' : 'bg-[#0F5132]'"
      />

      <div class="space-y-1 min-w-0">
        <p class="text-xs font-semibold text-slate-900">
          {{ notification.data?.message || notification.type }}
        </p>
        <span class="text-[10px] text-slate-400 block">
          {{ formatRelativeTime(notification.created_at) }}
        </span>
      </div>
    </div>

    <button
      v-if="!notification.read_at"
      type="button"
      class="text-[11px] text-[#0F5132] hover:underline font-medium shrink-0"
      @click.stop="$emit('mark-read')"
    >
      Mark read
    </button>
  </div>
</template>
