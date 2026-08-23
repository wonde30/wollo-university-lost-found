<script setup lang="ts">
import { onMounted } from 'vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { usePublicItems } from '@/features/lookups/composables/usePublicItems'
import { useItemFilters } from '@/features/items/composables/useItemFilters'
import ItemGrid from '@/features/items/components/ItemGrid.vue'
import ItemFilters from '@/features/items/components/ItemFilters.vue'
import AppPagination from '@/components/ui/AppPagination.vue'

const { items, loading, pagination, fetchItems } = usePublicItems()
const { filters, resetFilters, setPage } = useItemFilters()

async function loadItems() {
  await fetchItems({
    search: filters.search || undefined,
    type: filters.type || undefined,
    status: filters.status || undefined,
    category_id: filters.category_id || undefined,
    page: filters.page,
  })
}

function onFilterChange() {
  filters.page = 1
  loadItems()
}

function onReset() {
  resetFilters()
  loadItems()
}

function onPageChange(page: number) {
  setPage(page)
  loadItems()
}

onMounted(loadItems)
</script>

<template>
  <DefaultLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
      <!-- Page Header -->
      <div>
        <h1 class="text-2xl font-black text-slate-900">Browse Lost & Found Items</h1>
        <p class="text-sm text-slate-500 mt-1">
          Search through all reported items across Wollo University campuses.
        </p>
      </div>

      <!-- Filters -->
      <ItemFilters
        :filters="filters"
        @filter-change="onFilterChange"
        @reset="onReset"
      />

      <!-- Results Count -->
      <div v-if="pagination" class="flex items-center justify-between text-xs text-slate-500">
        <span>{{ pagination.total }} item(s) found</span>
        <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
      </div>

      <!-- Grid -->
      <ItemGrid :items="items" :loading="loading" />

      <!-- Pagination -->
      <AppPagination
        v-if="pagination && pagination.last_page > 1"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        @change="onPageChange"
      />
    </div>
  </DefaultLayout>
</template>
