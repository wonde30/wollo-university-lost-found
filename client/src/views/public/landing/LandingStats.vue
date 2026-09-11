<script setup lang="ts">
import { computed } from 'vue'
import type { PublicStatistics } from '@/features/lookups/types/landing.types'
import {
  FileEdit,
  CheckCircle2,
  Users,
  Building2,
} from 'lucide-vue-next'

const props = defineProps<{
  statistics: PublicStatistics | null
  loading?: boolean
}>()

const statsList = computed(() => [
  {
    id: 'reported',
    label: 'Items Reported',
    value: (props.statistics?.items_reported ?? 0).toLocaleString(),
    icon: FileEdit,
    iconBg: 'bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97]',
  },
  {
    id: 'returned',
    label: 'Items Returned',
    value: (props.statistics?.items_returned ?? 0).toLocaleString(),
    icon: CheckCircle2,
    iconBg: 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400',
  },
  {
    id: 'community',
    label: 'Community Members',
    value: (props.statistics?.community_members ?? 0).toLocaleString(),
    icon: Users,
    iconBg: 'bg-amber-50 dark:bg-amber-950/60 text-[#B7791F] dark:text-[#D4AF37]',
  },
  {
    id: 'campuses',
    label: 'Campuses',
    value: (props.statistics?.campuses ?? 0).toLocaleString(),
    icon: Building2,
    iconBg: 'bg-slate-100 dark:bg-slate-800 text-[#0B5D3B] dark:text-[#75bd97]',
  },
])
</script>

<template>
  <div class="relative z-20 max-w-6xl mx-auto px-4 -mt-10 sm:-mt-14 mb-16 sm:mb-20" aria-label="Platform Statistics">
    <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-xl border border-slate-200/90 dark:border-slate-800 p-5 sm:p-6 transition-colors duration-150">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 divide-y lg:divide-y-0 lg:divide-x divide-slate-100 dark:divide-slate-800/80">
        <div
          v-for="(item, idx) in statsList"
          :key="item.id"
          :class="[
            'flex items-center gap-3.5 sm:gap-4',
            idx > 0 && idx % 2 === 0 ? 'pt-4 lg:pt-0' : '',
            idx === 1 ? 'pl-0 sm:pl-2' : '',
            idx > 0 ? 'lg:pl-6' : '',
          ]"
        >
          <!-- Icon Circle -->
          <div :class="['h-12 w-12 rounded-xl flex items-center justify-center shrink-0 shadow-2xs', item.iconBg]">
            <component :is="item.icon" class="h-6 w-6" />
          </div>

          <!-- Numbers & Label -->
          <div class="min-w-0">
            <div v-if="loading" class="h-7 w-16 bg-slate-200 dark:bg-slate-700 animate-pulse rounded mb-1" />
            <div v-else class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none">
              {{ item.value }}
            </div>

            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 truncate mt-1">
              {{ item.label }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
