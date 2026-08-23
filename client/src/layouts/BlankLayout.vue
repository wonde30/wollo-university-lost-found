<script setup lang="ts">
import AppToast from '@/components/ui/AppToast.vue'
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 flex flex-col justify-center items-center p-4">
    <slot />
    <AppToast />
  </div>
</template>
