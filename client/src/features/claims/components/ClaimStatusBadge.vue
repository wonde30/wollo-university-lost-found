<script setup lang="ts">
import { computed } from 'vue'
import type { ClaimStatus } from '../types/claim.types'
import AppBadge from '@/components/ui/AppBadge.vue'

interface Props {
  status: ClaimStatus | string
}

const props = defineProps<Props>()

const badgeVariant = computed(() => {
  switch (props.status) {
    case 'approved': return 'success'
    case 'pending': return 'warning'
    case 'rejected': return 'danger'
    case 'reversed': return 'info'
    default: return 'default'
  }
})

const label = computed(() => {
  switch (props.status) {
    case 'approved': return 'Approved'
    case 'pending': return 'Pending Review'
    case 'rejected': return 'Rejected'
    case 'reversed': return 'Reversed'
    default: return props.status
  }
})
</script>

<template>
  <AppBadge :variant="badgeVariant" size="sm" dot>
    {{ label }}
  </AppBadge>
</template>
