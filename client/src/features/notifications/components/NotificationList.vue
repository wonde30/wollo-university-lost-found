<script setup lang="ts">
import type { Notification } from '../types/notification.types'
import NotificationItem from './NotificationItem.vue'
import AppEmptyState from '@/components/ui/AppEmptyState.vue'

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
    <div v-if="loading" class="p-8 text-center text-xs text-slate-400">
      Loading notifications...
    </div>

    <AppEmptyState
      v-else-if="notifications.length === 0"
      title="All caught up!"
      description="You have no notifications right now."
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
