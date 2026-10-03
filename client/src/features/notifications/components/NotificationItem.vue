<script setup lang="ts">
import type { Notification } from '../types/notification.types'
import { formatRelativeTime } from '@/utils/date'
import { t } from '@/i18n'
import { resolveNotificationTitle, resolveNotificationMessage } from '../utils/notificationMessage'

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
      'flex items-start justify-between gap-4 p-4 rounded-xl transition-colors border cursor-pointer',
      notification.read_at || notification.is_read
        ? 'bg-white dark:bg-[#111827] border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/60'
        : 'bg-[#E8F4EE]/50 dark:bg-[#153C2D]/30 border-[#0B5D3B]/20 dark:border-[#0B5D3B]/40 hover:bg-[#E8F4EE] dark:hover:bg-[#153C2D]/50',
    ]"
    role="button"
    tabindex="0"
    @click="$emit('click')"
    @keydown.enter="$emit('click')"
    @keydown.space.prevent="$emit('click')"
  >
    <div class="flex items-start gap-3 min-w-0">
      <span
        class="mt-1.5 h-2 w-2 rounded-full shrink-0"
        :class="(notification.read_at || notification.is_read) ? 'bg-transparent' : 'bg-[#0B5D3B] dark:bg-[#75bd97] ring-2 ring-[#0B5D3B]/20'"
      />

      <div class="space-y-1 min-w-0">
        <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">
          {{ resolveNotificationTitle(notification) }}
        </p>
        <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2 leading-relaxed">
          {{ resolveNotificationMessage(notification) }}
        </p>
        <span class="text-[10px] text-slate-400 dark:text-slate-500 block">
          {{ formatRelativeTime(notification.created_at) }}
        </span>
      </div>
    </div>

    <button
      v-if="!notification.read_at && !notification.is_read"
      type="button"
      class="text-[11px] text-[#0B5D3B] dark:text-[#75bd97] hover:underline font-medium shrink-0 cursor-pointer"
      :aria-label="t('notifications.markAsRead')"
      @click.stop="$emit('mark-read')"
    >
      {{ t('notifications.markAsRead') }}
    </button>
  </div>
</template>
