<script setup lang="ts">
import { computed } from 'vue'
import { t } from '@/i18n'
import {
  ChevronsLeft,
  ChevronLeft,
  ChevronRight,
  ChevronsRight,
} from 'lucide-vue-next'

interface Props {
  currentPage?: number
  lastPage?: number
  totalPages?: number
  total?: number
  totalItems?: number
  perPage?: number
  showPerPageSelector?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  currentPage: 1,
  lastPage: undefined,
  totalPages: undefined,
  total: undefined,
  totalItems: undefined,
  perPage: 15,
  showPerPageSelector: true,
})

const effectiveLastPage = computed(() => {
  return props.lastPage ?? props.totalPages ?? 1
})

const effectiveTotal = computed(() => {
  return props.total ?? props.totalItems ?? 0
})

const emit = defineEmits<{
  (e: 'change', page: number): void
  (e: 'page-change', page: number): void
  (e: 'update:currentPage', page: number): void
  (e: 'update:perPage', count: number): void
}>()

function setPage(page: number) {
  if (page >= 1 && page <= effectiveLastPage.value && page !== props.currentPage) {
    emit('change', page)
    emit('page-change', page)
    emit('update:currentPage', page)
  }
}

function handlePerPageChange(event: Event) {
  const value = Number((event.target as HTMLSelectElement).value)
  emit('update:perPage', value)
  emit('change', 1)
  emit('page-change', 1)
}

const displayedPages = computed(() => {
  const totalP = effectiveLastPage.value
  const current = props.currentPage
  const delta = 2
  const pages: (number | '...')[] = []

  for (let i = 1; i <= totalP; i++) {
    if (i === 1 || i === totalP || (i >= current - delta && i <= current + delta)) {
      pages.push(i)
    } else if (pages[pages.length - 1] !== '...') {
      pages.push('...')
    }
  }

  return pages
})

const resultsSummary = computed(() => {
  if (!effectiveTotal.value) return ''
  const start = (props.currentPage - 1) * props.perPage + 1
  const end = Math.min(props.currentPage * props.perPage, effectiveTotal.value)
  return `${t('common.showing')} ${start}-${end} ${t('common.of')} ${effectiveTotal.value} ${t('common.items')}`
})
</script>

<template>
  <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-2.5 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400 select-none transition-colors duration-200">
    <!-- Results Summary & Per-page selector -->
    <div class="flex items-center gap-3 flex-wrap">
      <span v-if="effectiveTotal > 0" class="font-bold text-slate-700 dark:text-slate-200">
        {{ resultsSummary }}
      </span>

      <div v-if="showPerPageSelector && effectiveTotal > 10" class="flex items-center gap-1.5 font-medium">
        <span>{{ t('common.show') }}</span>
        <select
          :value="perPage"
          class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-2 py-1 text-xs font-semibold text-slate-800 dark:text-slate-200 outline-none focus:border-[#0B5D3B] dark:focus:border-[#3e9e70]"
          @change="handlePerPageChange"
        >
          <option :value="10">10</option>
          <option :value="15">15</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
        <span>{{ t('common.perPage') }}</span>
      </div>
    </div>

    <!-- Navigation Page Buttons -->
    <nav v-if="effectiveLastPage > 1" class="flex items-center gap-1">
      <!-- First Page Button -->
      <button
        type="button"
        :title="t('common.firstPage')"
        :aria-label="t('common.firstPage')"
        :disabled="currentPage === 1"
        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white disabled:opacity-40 disabled:pointer-events-none transition-colors cursor-pointer"
        @click="setPage(1)"
      >
        <ChevronsLeft class="h-3.5 w-3.5" />
      </button>

      <!-- Previous Page Button -->
      <button
        type="button"
        :title="t('common.prevPage')"
        :aria-label="t('common.prevPage')"
        :disabled="currentPage === 1"
        class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white disabled:opacity-40 disabled:pointer-events-none transition-colors font-bold flex items-center gap-1 cursor-pointer"
        @click="setPage(currentPage - 1)"
      >
        <ChevronLeft class="h-3.5 w-3.5" />
        <span class="hidden sm:inline">{{ t('common.prevPage') }}</span>
      </button>

      <!-- Page Numbers with Ellipsis -->
      <div class="flex items-center gap-1 mx-1">
        <template v-for="(p, index) in displayedPages" :key="index">
          <span v-if="p === '...'" class="px-2 py-1 text-slate-400 dark:text-slate-500 font-bold">...</span>
          <button
            v-else
            type="button"
            :class="[
              'min-w-8 h-8 px-2 rounded-lg text-xs font-bold transition-all cursor-pointer',
              p === currentPage
                ? 'bg-[#0B5D3B] text-white font-extrabold shadow-2xs'
                : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white',
            ]"
            @click="setPage(Number(p))"
          >
            {{ p }}
          </button>
        </template>
      </div>

      <!-- Next Page Button -->
      <button
        type="button"
        :title="t('common.nextPage')"
        :aria-label="t('common.nextPage')"
        :disabled="currentPage === effectiveLastPage"
        class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white disabled:opacity-40 disabled:pointer-events-none transition-colors font-bold flex items-center gap-1 cursor-pointer"
        @click="setPage(currentPage + 1)"
      >
        <span class="hidden sm:inline">{{ t('common.nextPage') }}</span>
        <ChevronRight class="h-3.5 w-3.5" />
      </button>

      <!-- Last Page Button -->
      <button
        type="button"
        :title="t('common.lastPage')"
        :aria-label="t('common.lastPage')"
        :disabled="currentPage === effectiveLastPage"
        class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white disabled:opacity-40 disabled:pointer-events-none transition-colors cursor-pointer"
        @click="setPage(effectiveLastPage)"
      >
        <ChevronsRight class="h-3.5 w-3.5" />
      </button>
    </nav>
  </div>
</template>
