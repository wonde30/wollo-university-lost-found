<script setup lang="ts">
import { useReferenceData } from '@/features/lookups/composables/useReferenceData'
import type { ItemFilterState } from '../composables/useItemFilters'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'

interface Props {
  filters: ItemFilterState
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'filter-change'): void
  (e: 'reset'): void
}>()

const { categories } = useReferenceData()

const typeOptions = [
  { label: 'All Item Types', value: '' },
  { label: 'Lost Items', value: 'lost' },
  { label: 'Found Items', value: 'found' },
]

const statusOptions = [
  { label: 'All Statuses', value: '' },
  { label: 'Reported Lost', value: 'lost' },
  { label: 'Found (Unclaimed)', value: 'found_unclaimed' },
  { label: 'Found (Claimed)', value: 'found_claimed' },
  { label: 'Returned to Owner', value: 'returned' },
]
</script>

<template>
  <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <!-- Search Input -->
      <AppInput
        placeholder="Search keywords..."
        :model-value="filters.search"
        @update:model-value="filters.search = $event; emit('filter-change')"
      >
        <template #prefix>
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </template>
      </AppInput>

      <!-- Type Select -->
      <AppSelect
        placeholder="Filter by Type"
        :options="typeOptions"
        :model-value="filters.type"
        @update:model-value="filters.type = $event as any; emit('filter-change')"
      />

      <!-- Category Select -->
      <AppSelect
        placeholder="All Categories"
        :options="[{ label: 'All Categories', value: '' }, ...categories.map(c => ({ label: c.name, value: c.id }))]"
        :model-value="filters.category_id"
        @update:model-value="filters.category_id = $event ? Number($event) : ''; emit('filter-change')"
      />

      <!-- Status Select -->
      <AppSelect
        placeholder="Filter by Status"
        :options="statusOptions"
        :model-value="filters.status"
        @update:model-value="filters.status = $event as any; emit('filter-change')"
      />
    </div>

    <!-- Clear filters action -->
    <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
      <span class="text-slate-400">Filter and refine items</span>
      <AppButton variant="ghost" size="sm" @click="emit('reset')">
        Reset All Filters
      </AppButton>
    </div>
  </div>
</template>
