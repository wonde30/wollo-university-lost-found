<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { useItemSearch } from '@/features/items/composables/useItemSearch'
import { t } from '@/i18n'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import { formatDate } from '@/utils/date'
import { Compass, Search, ShieldCheck } from 'lucide-vue-next'

const route = useRoute()
const { trackingResult, loading, error, trackByReference } = useItemSearch()
const referenceCode = ref('')

async function handleTrack() {
  if (!referenceCode.value.trim()) return
  await trackByReference(referenceCode.value)
}

onMounted(() => {
  const refParam = (route.params.reference as string) || (route.query.ref as string) || (route.query.reference as string)
  if (refParam) {
    referenceCode.value = refParam.trim()
    trackByReference(refParam.trim())
  }
})

function getStatusVariant(status: string): any {
  switch (status) {
    case 'returned': return 'success'
    case 'found_claimed': return 'purple'
    case 'found_unclaimed': return 'primary'
    case 'lost': return 'warning'
    default: return 'default'
  }
}
</script>

<template>
  <DefaultLayout>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 sm:py-12 space-y-6">
      <!-- Header -->
      <div class="text-center space-y-2.5">
        <div class="inline-flex items-center justify-center h-14 w-14 rounded-2xl bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] mx-auto shadow-2xs font-bold">
          <Compass class="h-7 w-7" />
        </div>
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ t('nav.trackItem') }}</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto font-medium">
          Enter the unique reference code from your report receipt to track the live custody and resolution status of your item.
        </p>
      </div>

      <!-- Search Form -->
      <form class="flex gap-2.5 sm:gap-3" @submit.prevent="handleTrack">
        <div class="relative flex-1">
          <AppInput
            id="track-code"
            class="w-full pl-8"
            placeholder="e.g. WU-2024-001234"
            :model-value="referenceCode"
            :error="error || undefined"
            @update:model-value="referenceCode = $event"
          />
          <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 dark:text-slate-500 pointer-events-none" />
        </div>
        <AppButton
          type="submit"
          variant="primary"
          :loading="loading"
        >
          {{ t('nav.trackItem') }}
        </AppButton>
      </form>

      <!-- Result Card -->
      <div v-if="trackingResult" class="space-y-3 animate-scale-in">
        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ t('nav.trackItem') }}</p>
        <div class="rounded-2xl border border-slate-200/90 dark:border-slate-800 bg-white dark:bg-[#111827] p-4 sm:p-5 shadow-2xs space-y-4 transition-colors duration-150">
          <div class="flex items-center justify-between gap-4 flex-wrap pb-3.5 border-b border-slate-100 dark:border-slate-800">
            <div>
              <span class="text-xs font-mono font-bold text-slate-400 dark:text-slate-500">#{{ trackingResult.reference_code }}</span>
              <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-0.5">{{ trackingResult.title }}</h2>
            </div>
            <AppBadge :variant="getStatusVariant(trackingResult.status)" size="md">
              {{ t(`items.statuses.${trackingResult.status}`) }}
            </AppBadge>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
            <div>
              <span class="text-slate-400 dark:text-slate-500 block mb-1 font-semibold uppercase text-[10px]">{{ t('items.myItems.category') }}</span>
              <span class="font-bold text-slate-800 dark:text-slate-200">{{ trackingResult.category || 'General' }}</span>
            </div>
            <div>
              <span class="text-slate-400 dark:text-slate-500 block mb-1 font-semibold uppercase text-[10px]">{{ t('items.form.incidentDate') }}</span>
              <span class="font-bold text-slate-800 dark:text-slate-200">{{ formatDate(trackingResult.incident_date) }}</span>
            </div>
            <div class="col-span-2 sm:col-span-1">
              <span class="text-slate-400 dark:text-slate-500 block mb-1 font-semibold uppercase text-[10px]">{{ t('nav.locations') }}</span>
              <span class="font-bold text-slate-800 dark:text-slate-200">{{ (trackingResult as any).campus || 'Dessie Main Campus' }}</span>
            </div>
          </div>

          <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300 flex items-start gap-2">
            <ShieldCheck class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97] shrink-0 mt-0.5" />
            <p>{{ t('home.stats.safeHandover') }}</p>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
