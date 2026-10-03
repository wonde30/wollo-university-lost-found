<script setup lang="ts">
import { useNotificationStream } from '@/composables/useNotificationStream'
import ForcedPasswordChangeModal from '@/features/auth/components/ForcedPasswordChangeModal.vue'

useNotificationStream()
</script>

<template>
  <RouterView />
  <ForcedPasswordChangeModal />
</template>