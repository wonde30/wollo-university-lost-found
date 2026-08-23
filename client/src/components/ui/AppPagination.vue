<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  currentPage?: number
  lastPage?: number
  total?: number
  perPage?: number
  showPerPageSelector?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  currentPage: 1,
  lastPage: 1,
  total: 0,
  perPage: 15,
  showPerPageSelector: true,
})

const emit = defineEmits<{
  (e: 'change', page: number): void
  (e: 'update:currentPage', page: number): void
  (e: 'update:perPage', count: number): void
}>()

function setPage(page: number) {
  if (page >= 1 && page <= props.lastPage && page !== props.currentPage) {
    emit('change', page)
    emit('update:currentPage', page)
  }
}

function handlePerPageChange(event: Event) {
  const value = Number((event.target as HTMLSelectElement).value)
  emit('update:perPage', value)
  emit('change', 1)
}

const displayedPages = computed(() => {
  const totalP = props.lastPage
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
  if (!props.total) return ''
  const start = (props.currentPage - 1) * props.perPage + 1
  const end = Math.min(props.currentPage * props.perPage, props.total)
  return `Showing ${start}-${end} of ${props.total} items`
})
</script>

<template>
  <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 bg-white border-t border-slate-100 text-xs text-slate-500 select-none">
    <!-- Results Summary & Per-page selector -->
    <div class="flex items-center gap-4">
      <span v-if="total > 0" class="font-medium text-slate-600">
        {{ resultsSummary }}
      </span>

      <div v-if="showPerPageSelector && total > 10" class="flex items-center gap-1.5">
        <span>Show</span>
        <select
          :value="perPage"
          class="rounded-md border border-slate-200 bg-white px-2 py-1 text-xs text-slate-700 outline-none focus:border-[#0F5132]"
          @change="handlePerPageChange"
        >
          <option :value="10">10</option>
          <option :value="15">15</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
        <span>per page</span>
      </div>
    </div>

    <!-- Navigation Page Buttons -->
    <nav v-if="lastPage > 1" class="flex items-center gap-1">
      <!-- First Page Button -->
      <button
        type="button"
        title="First Page"
        :disabled="currentPage === 1"
        class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-900 disabled:opacity-40 disabled:pointer-events-none transition-colors"
        @click="setPage(1)"
      >
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
      </button>

      <!-- Previous Page Button -->
      <button
        type="button"
        title="Previous Page"
        :disabled="currentPage === 1"
        class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 disabled:opacity-40 disabled:pointer-events-none transition-colors font-medium flex items-center gap-1"
        @click="setPage(currentPage - 1)"
      >
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        <span class="hidden sm:inline">Prev</span>
      </button>

      <!-- Page Numbers with Ellipsis -->
      <div class="flex items-center gap-1 mx-1">
        <template v-for="(p, index) in displayedPages" :key="index">
          <span v-if="p === '...'" class="px-2 py-1 text-slate-400">...</span>
          <button
            v-else
            type="button"
            :class="[
              'min-w-8 h-8 px-2 rounded-lg text-xs font-semibold transition-all',
              p === currentPage
                ? 'bg-[#0F5132] text-white shadow-xs'
                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
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
        title="Next Page"
        :disabled="currentPage === lastPage"
        class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 disabled:opacity-40 disabled:pointer-events-none transition-colors font-medium flex items-center gap-1"
        @click="setPage(currentPage + 1)"
      >
        <span class="hidden sm:inline">Next</span>
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <!-- Last Page Button -->
      <button
        type="button"
        title="Last Page"
        :disabled="currentPage === lastPage"
        class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-900 disabled:opacity-40 disabled:pointer-events-none transition-colors"
        @click="setPage(lastPage)"
      >
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
        </svg>
      </button>
    </nav>
  </div>
</template>
