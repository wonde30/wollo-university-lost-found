<script setup lang="ts">
import { computed } from 'vue'
import { useReferenceData } from '@/features/lookups/composables/useReferenceData'
import type { ItemFilterState } from '../composables/useItemFilters'
import { currentLocale, t } from '@/i18n'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { Search } from 'lucide-vue-next'

interface Props {
  filters: ItemFilterState
}

defineProps<Props>()

const emit = defineEmits<{
  (e: 'filter-change'): void
  (e: 'reset'): void
}>()

const { categories } = useReferenceData()

const typeOptions = computed(() => [
  { label: t('items.myItems.all'), value: '' },
  { label: t('items.types.lost'), value: 'lost' },
  { label: t('items.types.found'), value: 'found' },
])

const categoryOptions = computed(() => [
  { label: t('items.myItems.all'), value: '' },
  ...categories.value.map(c => ({
    label: (currentLocale.value === 'am' && c.display_name_am) ? c.display_name_am : (c.display_name || c.name),
    value: c.id,
  })),
])

const statusOptions = computed(() => [
  { label: t('common.all'), value: '' },
  { label: t('items.status.lost'), value: 'lost' },
  { label: t('items.status.found_unclaimed'), value: 'found_unclaimed' },
  { label: t('items.status.found_claimed'), value: 'found_claimed' },
  { label: t('items.status.returned'), value: 'returned' },
])
</script>

<template>
  <div class="bg-white dark:bg-[#111827] p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-4 transition-colors duration-150">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <!-- Search Input -->
      <div>
        <div class="relative">
          <AppInput
            :placeholder="t('browse.searchPlaceholder')"
            :model-value="filters.search"
            class="pl-8"
            @update:model-value="filters.search = $event; emit('filter-change')"
          />
          <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 dark:text-slate-500 pointer-events-none" />
        </div>
        <p v-if="filters.search && filters.search.length > 0 && filters.search.length < 3" class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5 ml-1">
          {{ t('common.searchMinLength') }}
        </p>
      </div>

      <!-- Type Select -->
      <AppSelect
        :placeholder="t('items.myItems.type')"
        :options="typeOptions"
        :model-value="filters.type"
        @update:model-value="filters.type = $event as any; emit('filter-change')"
      />

      <!-- Category Select -->
      <AppSelect
        :placeholder="t('items.myItems.category')"
        :options="categoryOptions"
        :model-value="filters.category_id"
        @update:model-value="filters.category_id = $event ? Number($event) : ''; emit('filter-change')"
      />

      <!-- Status Select -->
      <AppSelect
        :placeholder="t('items.myItems.status')"
        :options="statusOptions"
        :model-value="filters.status"
        @update:model-value="filters.status = $event as any; emit('filter-change')"
      />
    </div>

    <!-- Clear filters action -->
    <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
      <span class="text-slate-400 dark:text-slate-500">{{ t('browse.subtitle') }}</span>
      <AppButton variant="ghost" size="sm" @click="emit('reset')">
        {{ t('common.reset') }}
      </AppButton>
    </div>
  </div>
</template>
