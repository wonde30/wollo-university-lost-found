<script setup lang="ts">
import { computed } from 'vue'
import type { UserRole } from '@/features/auth/types/auth.types'
import AppBadge from '@/components/ui/AppBadge.vue'

interface Props {
  role: UserRole
}

const props = defineProps<Props>()

const badgeVariant = computed(() => {
  switch (props.role) {
    case 'admin':
      return 'danger'
    case 'staff':
      return 'warning'
    case 'student':
      return 'primary'
    default:
      return 'default'
  }
})

const roleLabel = computed(() => {
  return props.role.charAt(0).toUpperCase() + props.role.slice(1)
})
</script>

<template>
  <AppBadge :variant="badgeVariant" :text="roleLabel" />
</template>