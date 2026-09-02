<script setup lang="ts">
import { ref, computed } from 'vue'
import { ArrowUp, ArrowDown, ArrowUpDown, PackageSearch } from 'lucide-vue-next'
import { t } from '@/i18n'
import type { TableColumn } from './types'
export type { TableColumn }

interface Props {
  columns: TableColumn[]
  items: Record<string, any>[]
  loading?: boolean
  emptyTitle?: string
  emptyDescription?: string
  selectable?: boolean
  selectedKeys?: (string | number)[]
  rowKey?: string
  stickyHeader?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  emptyTitle: undefined,
  emptyDescription: undefined,
  selectable: false,
  selectedKeys: () => [],
  rowKey: 'id',
  stickyHeader: false,
})

const effectiveEmptyTitle = computed(() => props.emptyTitle || t('common.noData'))
const effectiveEmptyDescription = computed(() => props.emptyDescription || t('common.noDataMatching'))

const emit = defineEmits<{
  (e: 'row-click', item: Record<string, any>): void
  (e: 'update:selectedKeys', keys: (string | number)[]): void
  (e: 'sort', key: string, direction: 'asc' | 'desc'): void
}>()

const sortKey = ref<string | null>(null)
const sortDirection = ref<'asc' | 'desc'>('asc')

function handleSort(col: TableColumn) {
  if (!col.sortable) return
  if (sortKey.value === col.key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = col.key
    sortDirection.value = 'asc'
  }
  emit('sort', col.key, sortDirection.value)
}

const allSelected = computed(() => {
  if (props.items.length === 0) return false
  return props.items.every(item => props.selectedKeys.includes(item[props.rowKey]))
})

function toggleSelectAll() {
  if (allSelected.value) {
    emit('update:selectedKeys', [])
  } else {
    const allKeys = props.items.map(item => item[props.rowKey])
    emit('update:selectedKeys', allKeys)
  }
}

function toggleRowSelect(item: Record<string, any>, event: Event) {
  event.stopPropagation()
  const key = item[props.rowKey]
  const current = [...props.selectedKeys]
  const idx = current.indexOf(key)
  if (idx !== -1) {
    current.splice(idx, 1)
  } else {
    current.push(key)
  }
  emit('update:selectedKeys', current)
}
</script>

<template>
  <div class="w-full overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xs transition-colors duration-200">
    <!-- Optional Bulk Actions Header Bar -->
    <div v-if="selectable && selectedKeys.length > 0" class="px-4 py-2.5 bg-[#E8F4EE] dark:bg-[#153C2D]/50 border-b border-[#0B5D3B]/20 dark:border-[#0B5D3B]/40 flex items-center justify-between text-xs">
      <span class="font-semibold text-[#0B5D3B] dark:text-[#75bd97]">
        {{ selectedKeys.length }} {{ t('common.items') }} selected
      </span>
      <slot name="bulk-actions" :selected-keys="selectedKeys" />
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm text-slate-700 dark:text-slate-200">
        <thead
          :class="[
            'bg-slate-50 dark:bg-slate-800/90 border-b border-slate-200 dark:border-slate-800 text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 select-none',
            stickyHeader ? 'sticky top-0 z-10 backdrop-blur-xs' : '',
          ]"
        >
          <tr>
            <!-- Select all checkbox -->
            <th v-if="selectable" class="w-10 px-4 py-3 text-center">
              <input
                type="checkbox"
                :checked="allSelected"
                class="rounded text-[#0B5D3B] focus:ring-[#0B5D3B] h-4 w-4 cursor-pointer bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700"
                @change="toggleSelectAll"
              />
            </th>

            <th
              v-for="col in columns"
              :key="col.key"
              :style="{ width: col.width }"
              :class="[
                'px-4 py-3 font-bold',
                col.sortable ? 'cursor-pointer hover:text-slate-900 dark:hover:text-white transition-colors' : '',
                col.align === 'center' ? 'text-center' : '',
                col.align === 'right' ? 'text-right' : '',
              ]"
              @click="handleSort(col)"
            >
              <div
                class="inline-flex items-center gap-1.5"
                :class="[
                  col.align === 'center' ? 'justify-center' : '',
                  col.align === 'right' ? 'justify-end' : '',
                ]"
              >
                <span>{{ col.label }}</span>
                <span v-if="col.sortable" class="text-slate-400 dark:text-slate-500">
                  <ArrowUp
                    v-if="sortKey === col.key && sortDirection === 'asc'"
                    class="h-3.5 w-3.5 text-[#0B5D3B] dark:text-[#3e9e70]"
                  />
                  <ArrowDown
                    v-else-if="sortKey === col.key && sortDirection === 'desc'"
                    class="h-3.5 w-3.5 text-[#0B5D3B] dark:text-[#3e9e70]"
                  />
                  <ArrowUpDown
                    v-else
                    class="h-3 w-3 opacity-40 hover:opacity-100"
                  />
                </span>
              </div>
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
          <!-- Loading skeleton rows -->
          <template v-if="loading">
            <tr v-for="n in 5" :key="n" class="animate-pulse">
              <td v-if="selectable" class="px-4 py-3.5 text-center">
                <div class="h-4 w-4 bg-slate-200 dark:bg-slate-800 rounded mx-auto" />
              </td>
              <td v-for="col in columns" :key="col.key" class="px-4 py-3.5">
                <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-3/4" />
              </td>
            </tr>
          </template>

          <!-- Empty State -->
          <tr v-else-if="items.length === 0">
            <td :colspan="columns.length + (selectable ? 1 : 0)" class="p-10 text-center text-slate-400 dark:text-slate-500">
              <div class="max-w-xs mx-auto space-y-1">
                <div class="h-10 w-10 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500 mb-2">
                  <PackageSearch class="h-5 w-5" />
                </div>
                <p class="font-semibold text-slate-800 dark:text-slate-200 text-sm">{{ effectiveEmptyTitle }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ effectiveEmptyDescription }}</p>
              </div>
            </td>
          </tr>

          <!-- Data rows -->
          <tr
            v-for="(item, index) in items"
            v-else
            :key="item[rowKey] || index"
            :class="[
              'hover:bg-slate-50/90 dark:hover:bg-slate-800/50 transition-colors cursor-pointer',
              selectedKeys.includes(item[rowKey]) ? 'bg-[#E8F4EE]/50 dark:bg-[#153C2D]/30' : '',
            ]"
            @click="emit('row-click', item)"
          >
            <!-- Checkbox row -->
            <td v-if="selectable" class="px-4 py-3 text-center" @click.stop>
              <input
                type="checkbox"
                :checked="selectedKeys.includes(item[rowKey])"
                class="rounded text-[#0B5D3B] focus:ring-[#0B5D3B] h-4 w-4 cursor-pointer bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700"
                @change="toggleRowSelect(item, $event)"
              />
            </td>

            <td
              v-for="col in columns"
              :key="col.key"
              :class="[
                'px-4 py-2.5 sm:py-3 text-slate-800 dark:text-slate-200 text-xs sm:text-sm',
                col.align === 'center' ? 'text-center' : '',
                col.align === 'right' ? 'text-right' : '',
              ]"
            >
              <slot :name="`cell-${col.key}`" :item="item" :value="item[col.key]">
                {{ item[col.key] ?? '—' }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Optional pagination footer -->
    <div v-if="$slots.pagination" class="border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
      <slot name="pagination" />
    </div>
  </div>
</template>
