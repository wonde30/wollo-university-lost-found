<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const breadcrumbs = computed(() => {
  const metaTitle = (route.meta?.title as string) || ''
  return [
    { name: 'Home', path: '/' },
    { name: metaTitle, path: route.path },
  ].filter(b => b.name)
})
</script>

<template>
  <nav class="hidden sm:flex items-center text-xs font-medium text-slate-500 gap-2">
    <RouterLink to="/" class="hover:text-slate-800 transition-colors">Home</RouterLink>
    <template v-for="(crumb, index) in breadcrumbs.slice(1)" :key="crumb.path">
      <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
      <span v-if="index === breadcrumbs.length - 2" class="text-slate-800 font-semibold truncate max-w-xs">
        {{ crumb.name }}
      </span>
      <RouterLink v-else :to="crumb.path" class="hover:text-slate-800 transition-colors">
        {{ crumb.name }}
      </RouterLink>
    </template>
  </nav>
</template>
