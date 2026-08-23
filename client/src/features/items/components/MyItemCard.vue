<script setup lang="ts">
import { computed } from 'vue'
import type { Item } from '../types/item.types'
import { formatDate } from '@/utils/date'
import { resolveStorageUrl } from '@/utils/url'
import ItemStatusBadge from './ItemStatusBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'

interface Props {
  item: Item
  showActions?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  showActions: false
})

const emit = defineEmits<{
  (e: 'edit', item: Item): void
  (e: 'delete', item: Item): void
}>()

const photoUrl = computed(() => {
  if (props.item.primary_photo?.photo_url) {
    return resolveStorageUrl(props.item.primary_photo.photo_url)
  }
  if (props.item.photos && props.item.photos.length > 0 && props.item.photos[0]?.photo_url) {
    return resolveStorageUrl(props.item.photos[0].photo_url)
  }
  return null
})

const canEdit = computed(() => {
  // Can edit if status allows it (not returned, etc.)
  return !['returned', 'disposed'].includes(props.item.status)
})
</script>

<template>
  <div class="group flex flex-col rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-300 transition-all duration-200 overflow-hidden">
    <!-- Image thumbnail container -->
    <RouterLink :to="`/items/${item.id}`" class="relative h-48 w-full bg-slate-100 overflow-hidden">
      <img
        v-if="photoUrl"
        :src="photoUrl"
        :alt="item.title"
        class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
        loading="lazy"
      />
      <div v-else class="h-full w-full flex flex-col items-center justify-center text-slate-400 bg-slate-100">
        <svg class="h-10 w-10 mb-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-[11px] font-medium">No photo available</span>
      </div>

      <!-- Type Pill -->
      <span
        :class="[
          'absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-xs',
          item.type === 'found' ? 'bg-[#0F5132] text-white' : 'bg-amber-600 text-white',
        ]"
      >
        {{ item.type }}
      </span>

      <!-- High Value Tag -->
      <span
        v-if="item.is_high_value"
        class="absolute top-3 right-3 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 shadow-xs flex items-center gap-1"
      >
        ★ High Value
      </span>
    </RouterLink>

    <!-- Content -->
    <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between gap-2 mb-2">
          <span class="text-xs font-semibold text-[#0F5132]">
            {{ item.category?.name || 'General Item' }}
          </span>
          <span class="text-[11px] text-slate-400 font-mono">
            #{{ item.reference_code }}
          </span>
        </div>

        <RouterLink :to="`/items/${item.id}`">
          <h3 class="text-base font-bold text-slate-900 line-clamp-1 group-hover:text-[#0F5132] transition-colors mb-1.5">
            {{ item.title }}
          </h3>
        </RouterLink>

        <p class="text-xs text-slate-500 line-clamp-2 mb-3">
          {{ item.description }}
        </p>
      </div>

      <div class="space-y-2">
        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
          <ItemStatusBadge :status="item.status" />

          <div class="flex items-center gap-1 text-slate-400 text-[11px]">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ formatDate(item.incident_date, 'short') }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="showActions" class="flex gap-2">
          <AppButton
            v-if="canEdit"
            variant="ghost"
            size="xs"
            block
            @click="emit('edit', item)"
          >
            Edit
          </AppButton>
          <AppButton
            variant="danger"
            size="xs"
            block
            @click="emit('delete', item)"
          >
            Delete
          </AppButton>
        </div>
      </div>
    </div>
  </div>
</template>
