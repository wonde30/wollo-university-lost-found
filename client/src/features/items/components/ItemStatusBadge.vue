<script setup lang="ts">
import { computed } from 'vue'
import type { ItemStatus } from '../types/item.types'
import AppBadge from '@/components/ui/AppBadge.vue'

interface Props {
  status: ItemStatus | string
}

const props = defineProps<Props>()

const badgeVariant = computed(() => {
  switch (props.status) {
    case 'found_unclaimed': return 'success'
    case 'found_claimed': return 'info'
    case 'returned': return 'primary'
    case 'lost': return 'warning'
    case 'expired': return 'default'
    case 'disposed': return 'danger'
    default: return 'default'
  }
})

const label = computed(() => {
  switch (props.status) {
    case 'found_unclaimed': return 'Found (Unclaimed)'
    case 'found_claimed': return 'Found (Claimed)'
    case 'returned': return 'Returned'
    case 'lost': return 'Reported Lost'
    case 'expired': return 'Expired'
    case 'disposed': return 'Disposed'
    default: return props.status
  }
})
</script>

<template>
  <AppBadge :variant="badgeVariant" size="sm" dot>
    {{ label }}
  </AppBadge>
</template>
