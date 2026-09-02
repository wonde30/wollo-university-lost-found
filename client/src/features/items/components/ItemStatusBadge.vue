<script setup lang="ts">
import { computed } from 'vue'
import type { ItemStatus } from '../types/item.types'
import AppBadge from '@/components/ui/AppBadge.vue'
import { t } from '@/i18n'

interface Props {
  status: ItemStatus | string
}

const props = defineProps<Props>()

const badgeVariant = computed(() => {
  switch (props.status) {
    case 'found_unclaimed': return 'success'
    case 'returned': return 'success'
    case 'in_custody': return 'primary'
    case 'matched': return 'info'
    case 'claimed':
    case 'found_claimed': return 'info'
    case 'lost':
    case 'pending_verification':
    case 'pending_surrender': return 'warning'
    case 'withdrawn':
    case 'cancelled':
    case 'expired': return 'default'
    case 'disposed': return 'danger'
    default: return 'default'
  }
})

const label = computed(() => {
  const key = `items.statuses.${props.status}`
  const translated = t(key)
  if (translated !== key) return translated
  return props.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
})
</script>

<template>
  <AppBadge :variant="badgeVariant" size="sm" dot>
    {{ label }}
  </AppBadge>
</template>
