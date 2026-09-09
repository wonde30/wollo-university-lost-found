<script setup lang="ts">
import { computed } from 'vue'
import type { ClaimStatus } from '../types/claim.types'
import AppBadge from '@/components/ui/AppBadge.vue'

import { claimStatusLabels } from '@/utils/formatters'

interface Props {
  status: ClaimStatus | string
}

const props = defineProps<Props>()

const badgeVariant = computed(() => {
  switch (props.status) {
    case 'approved': return 'success'
    case 'pending': return 'warning'
    case 'under_review': return 'info'
    case 'rejected': return 'danger'
    case 'reversed': return 'info'
    case 'cancelled':
    case 'withdrawn': return 'default'
    default: return 'default'
  }
})

const label = computed(() => {
  return claimStatusLabels[props.status] ?? props.status
})
</script>

<template>
  <AppBadge :variant="badgeVariant" size="sm" dot>
    {{ label }}
  </AppBadge>
</template>
