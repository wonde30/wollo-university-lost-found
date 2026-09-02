<script setup lang="ts">
import type { Notification } from '../types/notification.types'
import NotificationItem from './NotificationItem.vue'
import AppEmptyState from '@/components/ui/AppEmptyState.vue'
import { t } from '@/i18n'

interface Props {
  notifications: Notification[]
  loading?: boolean
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'select', notification: Notification): void
  (e: 'mark-read', id: string | number): void
}>()
</script>

<template>
  <div class="space-y-2.5">
    <div v-if="loading" class="space-y-2.5">
      <div
        v-for="n in 3"
        :key="n"
        class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-[#111827] flex items-start gap-3 animate-pulse"
      >
        <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-800 shrink-0" />
        <div class="space-y-1.5 flex-1">
          <div class="h-4 w-40 bg-slate-200 dark:bg-slate-800 rounded" />
          <div class="h-3 w-3/4 bg-slate-100 dark:bg-slate-800/60 rounded" />
        </div>
      </div>
    </div>

    <AppEmptyState
      v-else-if="notifications.length === 0"
      :title="t('notifications.empty')"
      :description="t('notifications.emptyDesc')"
    />

    <NotificationItem
      v-for="n in notifications"
      v-else
      :key="n.id"
      :notification="n"
      @click="emit('select', n)"
      @mark-read="emit('mark-read', n.id)"
    />
  </div>
</template>
