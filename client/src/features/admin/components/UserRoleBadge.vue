<script setup lang="ts">
import { computed } from 'vue'
import type { UserRole } from '@/features/auth/types/auth.types'
import AppBadge from '@/components/ui/AppBadge.vue'
import { usePermissionsStore } from '@/permissions/stores/permissions.store'
import { currentLocale, t } from '@/i18n'

interface Props {
  role: UserRole
}

const props = defineProps<Props>()
const permissionsStore = usePermissionsStore()

const roleRecord = computed(() => {
  return permissionsStore.roles.find(r => r.name === props.role)
})

const badgeVariant = computed(() => {
  switch (props.role) {
    case 'admin':
      return 'danger'
    case 'staff':
      return 'warning'
    case 'student':
      return 'primary'
    default:
      return 'purple'
  }
})

const roleLabel = computed(() => {
  if (roleRecord.value) {
    if (currentLocale.value === 'am' && roleRecord.value.display_name_am) {
      return roleRecord.value.display_name_am
    }
    return roleRecord.value.display_name || roleRecord.value.name
  }

  const translated = t(`admin.roles.${props.role}`)
  if (translated && !translated.startsWith('admin.roles.')) {
    return translated
  }

  return String(props.role).replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
})
</script>

<template>
  <AppBadge :variant="badgeVariant">{{ roleLabel }}</AppBadge>
</template>