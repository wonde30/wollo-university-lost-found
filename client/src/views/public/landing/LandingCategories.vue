<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import type { Category } from '@/types/common.types'
import { getCategoryIcon } from '@/utils/categoryIcons'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import { ArrowRight } from 'lucide-vue-next'

const props = defineProps<{
  categories: Category[]
  loading?: boolean
}>()

const router = useRouter()

const displayList = computed(() => {
  if (!props.categories || props.categories.length === 0) return []
  return props.categories.map(c => ({
    id: c.id,
    name: c.name,
    count: c.items_count ?? 0,
    icon: getCategoryIcon(c.icon_slug),
  }))
})

function handleCategoryClick(catId: number) {
  router.push({ path: '/browse', query: { category: catId } })
}
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20 sm:mb-24" aria-labelledby="browse-categories-heading">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8 sm:mb-10">
      <div class="space-y-1.5">
        <span class="text-xs font-black uppercase tracking-wider text-[#0B5D3B] dark:text-[#75bd97]">
          BROWSE BY CATEGORY
        </span>
        <h2 id="browse-categories-heading" class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          Common Items on Campus
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
          Explore lost and found items organized by official campus category.
        </p>
      </div>

      <button
        type="button"
        class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0B5D3B] dark:text-[#75bd97] hover:text-[#084C30] dark:hover:text-emerald-300 transition-colors group cursor-pointer self-start sm:self-auto"
        @click="router.push('/browse')"
      >
        <span>View All Categories</span>
        <ArrowRight class="h-3.5 w-3.5 group-hover:translate-x-1 transition-transform" />
      </button>
    </div>

    <!-- Skeletons when Loading -->
    <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3 sm:gap-4">
      <div
        v-for="n in 8"
        :key="n"
        class="p-4 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 space-y-2.5 flex flex-col items-center"
      >
        <AppSkeleton class="h-11 w-11 rounded-xl" />
        <AppSkeleton class="h-4 w-16 rounded" />
        <AppSkeleton class="h-3 w-8 rounded" />
      </div>
    </div>

    <!-- Real Database Category Cards -->
    <div
      v-else-if="displayList.length > 0"
      class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3 sm:gap-4"
      role="list"
    >
      <div
        v-for="cat in displayList"
        :key="cat.id"
        role="listitem"
        class="group p-4 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 shadow-2xs hover:shadow-md hover:border-[#0B5D3B] dark:hover:border-[#75bd97] hover:-translate-y-0.5 transition-all duration-200 cursor-pointer flex flex-col items-center text-center space-y-2 focus:outline-hidden focus:ring-2 focus:ring-[#0B5D3B]/40"
        tabindex="0"
        :aria-label="cat.name + ' (' + cat.count + ' items)'"
        @click="handleCategoryClick(cat.id)"
        @keydown.enter="handleCategoryClick(cat.id)"
      >
        <!-- Icon -->
        <div class="h-11 w-11 rounded-xl bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] flex items-center justify-center transition-transform group-hover:scale-110 shadow-2xs">
          <component :is="cat.icon" class="h-5 w-5" />
        </div>

        <!-- Name & Count -->
        <div class="space-y-0.5 w-full">
          <h3 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors truncate">
            {{ cat.name }}
          </h3>
          <span class="inline-block text-[11px] font-semibold text-slate-400 dark:text-slate-500">
            ({{ cat.count }})
          </span>
        </div>
      </div>
    </div>

    <!-- Fallback if Empty -->
    <div
      v-else
      class="p-8 text-center bg-white dark:bg-[#111827] rounded-2xl border border-slate-200 dark:border-slate-800"
    >
      <p class="text-xs text-slate-500 dark:text-slate-400">No categories currently configured.</p>
    </div>
  </section>
</template>
