<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAnnouncementsStore } from '@/stores/announcements.store'
import { t } from '@/i18n'
import type { Announcement } from '@/features/admin/types/admin.types'
import {
  AlertCircle,
  AlertTriangle,
  Wrench,
  CheckCircle2,
  X,
  ChevronLeft,
  ChevronRight,
  Megaphone,
} from 'lucide-vue-next'

const announcementsStore = useAnnouncementsStore()
const currentIndex = ref(0)
const isExpanded = ref(false)

onMounted(() => {
  announcementsStore.fetchActive()
})

const activeBanners = computed(() => announcementsStore.activeBanners)

const currentAnnouncement = computed<Announcement | null>(() => {
  if (activeBanners.value.length === 0) return null
  const idx = Math.min(currentIndex.value, activeBanners.value.length - 1)
  return activeBanners.value[idx] || null
})

function prev() {
  if (currentIndex.value > 0) {
    currentIndex.value--
  } else {
    currentIndex.value = activeBanners.value.length - 1
  }
}

function next() {
  if (currentIndex.value < activeBanners.value.length - 1) {
    currentIndex.value++
  } else {
    currentIndex.value = 0
  }
}

function dismissCurrent() {
  if (currentAnnouncement.value) {
    announcementsStore.dismiss(currentAnnouncement.value.id)
    if (currentIndex.value >= activeBanners.value.length && currentIndex.value > 0) {
      currentIndex.value--
    }
  }
}

const bannerConfig = computed(() => {
  const type = currentAnnouncement.value?.type || 'info'
  switch (type) {
    case 'urgent':
      return {
        wrapperClass: 'bg-rose-500/10 dark:bg-rose-950/40 border-rose-500/30 text-rose-900 dark:text-rose-100',
        badgeClass: 'bg-rose-500 text-white',
        iconClass: 'text-rose-600 dark:text-rose-400',
        icon: AlertCircle,
        label: t('admin.announcements.types.urgent'),
      }
    case 'warning':
      return {
        wrapperClass: 'bg-amber-500/10 dark:bg-amber-950/40 border-amber-500/30 text-amber-900 dark:text-amber-100',
        badgeClass: 'bg-amber-500 text-slate-900 font-bold',
        iconClass: 'text-amber-600 dark:text-amber-400',
        icon: AlertTriangle,
        label: t('admin.announcements.types.warning'),
      }
    case 'maintenance':
      return {
        wrapperClass: 'bg-indigo-500/10 dark:bg-indigo-950/40 border-indigo-500/30 text-indigo-900 dark:text-indigo-100',
        badgeClass: 'bg-indigo-600 text-white',
        iconClass: 'text-indigo-600 dark:text-indigo-400',
        icon: Wrench,
        label: t('admin.announcements.types.maintenance'),
      }
    case 'success':
      return {
        wrapperClass: 'bg-emerald-500/10 dark:bg-emerald-950/40 border-emerald-500/30 text-emerald-900 dark:text-emerald-100',
        badgeClass: 'bg-emerald-600 text-white',
        iconClass: 'text-emerald-600 dark:text-emerald-400',
        icon: CheckCircle2,
        label: t('admin.announcements.types.success'),
      }
    case 'info':
    default:
      return {
        wrapperClass: 'bg-[#0B5D3B]/10 dark:bg-[#0B5D3B]/20 border-[#0B5D3B]/30 text-[#073824] dark:text-emerald-100',
        badgeClass: 'bg-[#0B5D3B] text-white',
        iconClass: 'text-[#0B5D3B] dark:text-emerald-400',
        icon: Megaphone,
        label: t('admin.announcements.types.info'),
      }
  }
})
</script>

<template>
  <Transition name="fade">
    <div
      v-if="currentAnnouncement"
      class="w-full border-b transition-colors duration-200"
      :class="bannerConfig.wrapperClass"
      role="alert"
      aria-live="polite"
    >
      <div class="max-w-[1600px] mx-auto px-4 sm:px-6 py-2.5 flex items-center justify-between gap-3 text-xs sm:text-sm">
        <!-- Left: Icon & Content -->
        <div class="flex items-start sm:items-center gap-2.5 min-w-0 flex-1">
          <component
            :is="bannerConfig.icon"
            class="h-4 w-4 shrink-0 mt-0.5 sm:mt-0"
            :class="bannerConfig.iconClass"
          />

          <div class="flex flex-wrap items-center gap-x-2 gap-y-1 min-w-0">
            <span
              class="px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase shrink-0"
              :class="bannerConfig.badgeClass"
            >
              {{ bannerConfig.label }}
            </span>

            <span class="font-bold shrink-0 truncate max-w-[200px] sm:max-w-[320px]">
              {{ currentAnnouncement.title }}:
            </span>

            <span
              class="text-xs opacity-90 transition-all duration-200"
              :class="isExpanded ? 'block' : 'truncate max-w-[300px] md:max-w-[600px] inline'"
            >
              {{ currentAnnouncement.body }}
            </span>

            <button
              v-if="currentAnnouncement.body.length > 80"
              type="button"
              class="text-[11px] font-bold underline opacity-80 hover:opacity-100 cursor-pointer ml-1"
              @click="isExpanded = !isExpanded"
            >
              {{ isExpanded ? t('common.showLess') : t('common.readMore') }}
            </button>
          </div>
        </div>

        <!-- Right: Pagination (if multiple) & Dismiss Button -->
        <div class="flex items-center gap-1.5 shrink-0">
          <template v-if="activeBanners.length > 1">
            <span class="text-[10px] font-bold opacity-75 mr-1 hidden sm:inline">
              {{ currentIndex + 1 }} / {{ activeBanners.length }}
            </span>

            <button
              type="button"
              class="p-1 rounded-md hover:bg-black/5 dark:hover:bg-white/10 transition-colors cursor-pointer"
              :title="t('common.previous')"
              @click="prev"
            >
              <ChevronLeft class="h-3.5 w-3.5" />
            </button>

            <button
              type="button"
              class="p-1 rounded-md hover:bg-black/5 dark:hover:bg-white/10 transition-colors cursor-pointer"
              :title="t('common.next')"
              @click="next"
            >
              <ChevronRight class="h-3.5 w-3.5" />
            </button>
          </template>

          <button
            type="button"
            class="p-1 rounded-md hover:bg-black/10 dark:hover:bg-white/10 transition-colors cursor-pointer ml-1"
            :title="t('common.dismiss')"
            :aria-label="t('common.dismiss')"
            @click="dismissCurrent"
          >
            <X class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>
