<script setup lang="ts">
import { useNotificationStream } from '@/composables/useNotificationStream'

useNotificationStream()
</script>

<template>
  <RouterView />
</template>