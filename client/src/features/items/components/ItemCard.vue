<script setup lang="ts">
import { computed } from 'vue'
import type { Item } from '../types/item.types'
import { formatDate } from '@/utils/date'
import { resolveStorageUrl } from '@/utils/url'
import { currentLocale, t } from '@/i18n'
import ItemStatusBadge from './ItemStatusBadge.vue'
import { ImageOff, Calendar } from 'lucide-vue-next'

interface Props {
  item: Item
}

const props = defineProps<Props>()

const photoUrl = computed(() => {
  if (props.item.primary_photo?.photo_url) {
    return resolveStorageUrl(props.item.primary_photo.photo_url)
  }
  if (props.item.photos && props.item.photos.length > 0 && props.item.photos[0]?.photo_url) {
    return resolveStorageUrl(props.item.photos[0].photo_url)
  }
  return null
})
</script>

<template>
  <RouterLink
    :to="`/items/${item.id}`"
    class="group flex flex-col rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/80 dark:border-slate-800 shadow-2xs hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-150 overflow-hidden"
  >
    <!-- Image thumbnail container -->
    <div class="relative h-48 w-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
      <img
        v-if="photoUrl"
        :src="photoUrl"
        :alt="item.title"
        class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
        loading="lazy"
      />
      <div v-else class="h-full w-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800">
        <ImageOff class="h-10 w-10 mb-1 opacity-50" />
        <span class="text-[11px] font-medium">{{ t('common.noPhoto') }}</span>
      </div>

      <!-- Type Pill -->
      <span
        :class="[
          'absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider shadow-2xs',
          item.type === 'found' ? 'bg-[#0B5D3B] text-white' : 'bg-amber-600 text-white',
        ]"
      >
        {{ item.type === 'found' ? t('items.types.found') : t('items.types.lost') }}
      </span>

      <!-- High Value Tag -->
      <span
        v-if="item.is_high_value"
        class="absolute top-3 right-3 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 dark:bg-amber-950/80 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-700 shadow-2xs flex items-center gap-1"
      >
        ★ {{ t('items.highValue') }}
      </span>
    </div>

    <!-- Content -->
    <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between gap-2 mb-1.5">
          <span class="text-xs font-bold text-[#0B5D3B] dark:text-[#75bd97]">
            {{ (currentLocale === 'am' && item.category?.display_name_am) ? item.category.display_name_am : (item.category?.display_name || item.category?.name || t('items.category')) }}
          </span>
          <span class="text-[11px] text-slate-400 dark:text-slate-500 font-mono font-bold">
            #{{ item.reference_code }}
          </span>
        </div>

        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white line-clamp-1 group-hover:text-[#0B5D3B] dark:group-hover:text-[#75bd97] transition-colors mb-1">
          {{ item.title }}
        </h3>

        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-2.5">
          {{ item.description }}
        </p>
      </div>

      <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
        <ItemStatusBadge :status="item.status" />

        <div class="flex items-center gap-1 text-slate-400 dark:text-slate-500 text-[11px] font-medium">
          <Calendar class="h-3.5 w-3.5" />
          <span>{{ formatDate(item.incident_date, 'short') }}</span>
        </div>
      </div>
    </div>
  </RouterLink>
</template>
