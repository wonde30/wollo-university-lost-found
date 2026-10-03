<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { ItemPhoto } from '../types/item.types'
import { resolveStorageUrl } from '@/utils/url'
import { t } from '@/i18n'
import { ImageOff } from 'lucide-vue-next'

interface Props {
  photos?: ItemPhoto[]
}

const props = withDefaults(defineProps<Props>(), {
  photos: () => [],
})

const selectedIndex = ref(0)
const failedImageIndices = ref<Set<number>>(new Set())

const activePhotoUrl = computed(() => {
  if (
    props.photos.length > 0 &&
    props.photos[selectedIndex.value] &&
    !failedImageIndices.value.has(selectedIndex.value)
  ) {
    return resolveStorageUrl(props.photos[selectedIndex.value].photo_url)
  }
  return null
})

watch(() => props.photos, () => {
  failedImageIndices.value.clear()
  selectedIndex.value = 0
}, { deep: true })

function handleImageError(index: number) {
  failedImageIndices.value.add(index)
}
</script>

<template>
  <div class="space-y-3">
    <!-- Main Photo Display -->
    <div class="relative h-72 sm:h-96 w-full rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-850 border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-center group">
      <img
        v-if="activePhotoUrl"
        :src="activePhotoUrl"
        :alt="t('items.photos')"
        class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-[1.02]"
        @error="handleImageError(selectedIndex)"
      />
      <div v-else class="text-center text-slate-400 dark:text-slate-500 p-8 flex flex-col items-center justify-center">
        <div class="h-16 w-16 rounded-2xl bg-slate-200/60 dark:bg-slate-800 flex items-center justify-center mb-3">
          <ImageOff class="h-8 w-8 text-slate-400 dark:text-slate-500 opacity-80" />
        </div>
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ t('common.noPhotosUploaded') }}</span>
        <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ t('items.physicalVerifiedCustody') }}</span>
      </div>
    </div>

    <!-- Thumbnail Strip -->
    <div v-if="photos.length > 1" class="flex gap-2 overflow-x-auto py-1">
      <button
        v-for="(p, index) in photos"
        :key="p.id || index"
        type="button"
        :class="[
          'h-16 w-16 shrink-0 rounded-xl overflow-hidden border-2 transition-all cursor-pointer bg-slate-100 dark:bg-slate-800',
          selectedIndex === index ? 'border-[#0B5D3B] dark:border-[#75bd97] ring-2 ring-[#0B5D3B]/30 scale-95' : 'border-slate-200 dark:border-slate-700 opacity-70 hover:opacity-100',
        ]"
        @click="selectedIndex = index"
      >
        <img
          v-if="!failedImageIndices.has(index)"
          :src="resolveStorageUrl(p.photo_url)"
          :alt="t('common.thumbnail')"
          class="h-full w-full object-cover"
          @error="handleImageError(index)"
        />
        <div v-else class="h-full w-full flex items-center justify-center text-slate-400">
          <ImageOff class="h-5 w-5 opacity-60" />
        </div>
      </button>
    </div>
  </div>
</template>

