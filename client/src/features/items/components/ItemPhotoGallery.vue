<script setup lang="ts">
import { ref, computed } from 'vue'
import type { ItemPhoto } from '../types/item.types'
import { resolveStorageUrl } from '@/utils/url'
import { t } from '@/i18n'

interface Props {
  photos?: ItemPhoto[]
}

const props = withDefaults(defineProps<Props>(), {
  photos: () => [],
})

const selectedIndex = ref(0)

const activePhotoUrl = computed(() => {
  if (props.photos.length > 0 && props.photos[selectedIndex.value]) {
    return resolveStorageUrl(props.photos[selectedIndex.value].photo_url)
  }
  return null
})
</script>

<template>
  <div class="space-y-3">
    <!-- Main Photo Display -->
    <div class="relative h-72 sm:h-96 w-full rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs flex items-center justify-center">
      <img
        v-if="activePhotoUrl"
        :src="activePhotoUrl"
        :alt="t('items.photos')"
        class="h-full w-full object-contain"
      />
      <div v-else class="text-center text-slate-400 dark:text-slate-500 p-8">
        <svg class="mx-auto h-12 w-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-xs font-medium">{{ t('common.noPhotosUploaded') }}</span>
      </div>
    </div>

    <!-- Thumbnail Strip -->
    <div v-if="photos.length > 1" class="flex gap-2 overflow-x-auto py-1">
      <button
        v-for="(p, index) in photos"
        :key="p.id || index"
        type="button"
        :class="[
          'h-16 w-16 shrink-0 rounded-xl overflow-hidden border-2 transition-all cursor-pointer',
          selectedIndex === index ? 'border-[#0B5D3B] dark:border-[#75bd97] ring-2 ring-[#0B5D3B]/30 scale-95' : 'border-slate-200 dark:border-slate-700 opacity-70 hover:opacity-100',
        ]"
        @click="selectedIndex = index"
      >
        <img :src="resolveStorageUrl(p.photo_url)" :alt="t('common.thumbnail')" class="h-full w-full object-cover" />
      </button>
    </div>
  </div>
</template>
