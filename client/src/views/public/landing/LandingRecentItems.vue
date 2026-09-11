<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import type { Item } from '@/features/items/types/item.types'
import { formatDate } from '@/utils/date'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import AppButton from '@/components/ui/AppButton.vue'
import {
  MapPin,
  Calendar,
  ArrowRight,
  Package,
  Sparkles,
} from 'lucide-vue-next'

const props = defineProps<{
  items: Item[]
  loading?: boolean
}>()

const router = useRouter()

const displayItems = computed(() => {
  if (!props.items) return []
  return props.items.slice(0, 5).map(item => ({
    id: item.id,
    title: item.title,
    categoryName: item.category?.name || 'General',
    locationName: item.location?.name || item.campus?.name || 'Campus Grounds',
    date: item.incident_date || item.created_at,
    status: item.status || 'found',
    image: item.primary_photo?.photo_url || item.primary_photo?.url || (item.photos && (item.photos[0]?.photo_url || item.photos[0]?.url)) || null,
  }))
})

function handleItemClick(id: number) {
  router.push(`/items/${id}`)
}

function handleImageError(event: Event) {
  const target = event.target as HTMLElement
  if (target) {
    target.style.display = 'none'
    const parent = target.parentElement
    if (parent) {
      const fallback = parent.querySelector('.photo-fallback') as HTMLElement
      if (fallback) fallback.style.display = 'flex'
    }
  }
}
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20 sm:mb-24" aria-labelledby="recent-found-heading">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8 sm:mb-10">
      <div class="space-y-1.5">
        <span class="text-xs font-black uppercase tracking-wider text-[#0B5D3B] dark:text-[#75bd97]">
          RECENT FOUND ITEMS
        </span>
        <h2 id="recent-found-heading" class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          Recently Found Items
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
          These items were recently found on campus and are in university custody awaiting their owners.
        </p>
      </div>

      <button
        type="button"
        class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0B5D3B] dark:text-[#75bd97] hover:text-[#084C30] dark:hover:text-emerald-300 transition-colors group cursor-pointer self-start sm:self-auto"
        @click="router.push('/browse?type=found')"
      >
        <span>View All Found Items</span>
        <ArrowRight class="h-3.5 w-3.5 group-hover:translate-x-1 transition-transform" />
      </button>
    </div>

    <!-- Loading Skeletons -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5">
      <div
        v-for="n in 5"
        :key="n"
        class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-200/80 dark:border-slate-800 p-3 space-y-3"
      >
        <AppSkeleton class="h-44 w-full rounded-xl" />
        <AppSkeleton class="h-4 w-3/4 rounded" />
        <AppSkeleton class="h-3 w-1/2 rounded" />
      </div>
    </div>

    <!-- Real Database Items Grid -->
    <div
      v-else-if="displayItems.length > 0"
      class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5"
    >
      <article
        v-for="item in displayItems"
        :key="item.id"
        class="group bg-white dark:bg-[#111827] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs hover:shadow-lg hover:border-[#0B5D3B]/40 dark:hover:border-[#75bd97]/40 transition-all duration-200 overflow-hidden flex flex-col cursor-pointer focus-within:ring-2 focus-within:ring-[#0B5D3B]/40"
        tabindex="0"
        :aria-label="item.title"
        @click="handleItemClick(item.id)"
        @keydown.enter="handleItemClick(item.id)"
      >
        <!-- Photo Container -->
        <div class="relative h-44 w-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
          <img
            v-if="item.image"
            :src="item.image"
            :alt="item.title"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            loading="lazy"
            @error="handleImageError($event)"
          />

          <!-- Fallback placeholder when no photo or photo fails -->
          <div
            :class="[
              'photo-fallback w-full h-full flex-col items-center justify-center text-slate-400 dark:text-slate-500 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800 dark:to-slate-900',
              item.image ? 'hidden' : 'flex'
            ]"
          >
            <div class="h-12 w-12 rounded-full bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] flex items-center justify-center mb-1.5">
              <Package class="h-6 w-6" />
            </div>
            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ item.categoryName }}</span>
          </div>

          <!-- Status Badge -->
          <span class="absolute bottom-2.5 left-2.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#0B5D3B] text-white shadow-sm">
            Found
          </span>
        </div>

        <!-- Info Area -->
        <div class="p-4 flex-1 flex flex-col justify-between space-y-2.5">
          <div>
            <div class="text-[10px] font-bold uppercase tracking-wider text-[#0B5D3B] dark:text-[#75bd97] mb-1">
              {{ item.categoryName }}
            </div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors line-clamp-2 leading-snug">
              {{ item.title }}
            </h3>
          </div>

          <div class="space-y-1 text-[11px] text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800/80">
            <div class="flex items-center gap-1.5 truncate">
              <MapPin class="h-3 w-3 shrink-0 text-slate-400" />
              <span class="truncate">{{ item.locationName }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <Calendar class="h-3 w-3 shrink-0 text-slate-400" />
              <span>{{ formatDate(item.date) }}</span>
            </div>
          </div>
        </div>
      </article>
    </div>

    <!-- Honest Empty State when Database Has No Items -->
    <div
      v-else
      class="p-10 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 text-center space-y-3"
    >
      <div class="h-12 w-12 rounded-full bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] flex items-center justify-center mx-auto">
        <Sparkles class="h-6 w-6" />
      </div>
      <h3 class="text-base font-bold text-slate-900 dark:text-white">
        No Found Items Currently in Custody
      </h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
        All reported items have been reunited or verified. If you recently found or lost property on campus, please submit a report.
      </p>
      <div class="pt-2">
        <AppButton variant="primary" size="sm" @click="router.push('/report-found')">
          Report a Found Item
        </AppButton>
      </div>
    </div>
  </section>
</template>
