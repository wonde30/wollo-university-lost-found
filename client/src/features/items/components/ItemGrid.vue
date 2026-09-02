<script setup lang="ts">
import type { Item } from '../types/item.types'
import { t } from '@/i18n'
import ItemCard from './ItemCard.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import AppEmptyState from '@/components/ui/AppEmptyState.vue'

interface Props {
  items: Item[]
  loading?: boolean
}

withDefaults(defineProps<Props>(), {
  loading: false,
})
</script>

<template>
  <div>
    <!-- Loading Grid Skeletons -->
    <div v-if="loading && items.length === 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
      <div v-for="n in 8" :key="n" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 space-y-3">
        <AppSkeleton height="12rem" class="rounded-xl" />
        <AppSkeleton height="1rem" width="40%" />
        <AppSkeleton height="1.25rem" width="80%" />
        <AppSkeleton height="0.875rem" width="100%" />
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="items.length === 0">
      <AppEmptyState
        :title="t('browse.noItems')"
        :description="t('browse.noItemsDesc')"
      />
    </div>

    <!-- Item Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
      <ItemCard
        v-for="item in items"
        :key="item.id"
        :item="item"
      />
    </div>
  </div>
</template>
