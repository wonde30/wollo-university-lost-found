<script setup lang="ts">
import { ref, computed } from 'vue'

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
  emptyTitle: 'No records found',
  emptyDescription: 'There is no data matching your criteria.',
  selectable: false,
  selectedKeys: () => [],
  rowKey: 'id',
  stickyHeader: false,
})

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
  <div class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
    <!-- Optional Bulk Actions Header Bar -->
    <div v-if="selectable && selectedKeys.length > 0" class="px-4 py-2.5 bg-emerald-50 border-b border-emerald-200 flex items-center justify-between text-xs">
      <span class="font-semibold text-emerald-800">
        {{ selectedKeys.length }} item{{ selectedKeys.length > 1 ? 's' : '' }} selected
      </span>
      <slot name="bulk-actions" :selected-keys="selectedKeys" />
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm text-slate-600">
        <thead
          :class="[
            'bg-slate-50/90 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500 select-none',
            stickyHeader ? 'sticky top-0 z-10 backdrop-blur-sm' : '',
          ]"
        >
          <tr>
            <!-- Select all checkbox -->
            <th v-if="selectable" class="w-10 px-4 py-3.5 text-center">
              <input
                type="checkbox"
                :checked="allSelected"
                class="rounded text-[#0F5132] focus:ring-[#0F5132] h-4 w-4 cursor-pointer"
                @change="toggleSelectAll"
              />
            </th>

            <th
              v-for="col in columns"
              :key="col.key"
              :style="{ width: col.width }"
              :class="[
                'px-4 py-3.5',
                col.sortable ? 'cursor-pointer hover:text-slate-800' : '',
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
                <span v-if="col.sortable" class="text-slate-400">
                  <svg
                    v-if="sortKey === col.key && sortDirection === 'asc'"
                    class="h-3.5 w-3.5 text-[#0F5132]"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg
                    v-else-if="sortKey === col.key && sortDirection === 'desc'"
                    class="h-3.5 w-3.5 text-[#0F5132]"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                  <svg
                    v-else
                    class="h-3 w-3 opacity-50"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                  </svg>
                </span>
              </div>
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">
          <!-- Loading skeleton rows -->
          <template v-if="loading">
            <tr v-for="n in 5" :key="n" class="animate-pulse">
              <td v-if="selectable" class="px-4 py-4 text-center">
                <div class="h-4 w-4 bg-slate-200 rounded mx-auto" />
              </td>
              <td v-for="col in columns" :key="col.key" class="px-4 py-4">
                <div class="h-4 bg-slate-200 rounded w-3/4" />
              </td>
            </tr>
          </template>

          <!-- Empty State -->
          <tr v-else-if="items.length === 0">
            <td :colspan="columns.length + (selectable ? 1 : 0)" class="p-10 text-center text-slate-400">
              <div class="max-w-xs mx-auto space-y-1">
                <div class="h-10 w-10 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                </div>
                <p class="font-semibold text-slate-800 text-sm">{{ emptyTitle }}</p>
                <p class="text-xs text-slate-500">{{ emptyDescription }}</p>
              </div>
            </td>
          </tr>

          <!-- Data rows -->
          <tr
            v-for="(item, index) in items"
            v-else
            :key="item[rowKey] || index"
            :class="[
              'hover:bg-slate-50/90 transition-colors cursor-pointer',
              selectedKeys.includes(item[rowKey]) ? 'bg-emerald-50/50' : '',
            ]"
            @click="emit('row-click', item)"
          >
            <!-- Checkbox row -->
            <td v-if="selectable" class="px-4 py-3.5 text-center" @click.stop>
              <input
                type="checkbox"
                :checked="selectedKeys.includes(item[rowKey])"
                class="rounded text-[#0F5132] focus:ring-[#0F5132] h-4 w-4 cursor-pointer"
                @change="toggleRowSelect(item, $event)"
              />
            </td>

            <td
              v-for="col in columns"
              :key="col.key"
              :class="[
                'px-4 py-3.5 text-slate-700',
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
    <div v-if="$slots.pagination" class="border-t border-slate-100 bg-slate-50/50">
      <slot name="pagination" />
    </div>
  </div>
</template>
