<script setup lang="ts">
import { ref } from 'vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { useItemSearch } from '@/features/items/composables/useItemSearch'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'

const { trackingResult, loading, error, trackByReference } = useItemSearch()
const referenceCode = ref('')

async function handleTrack() {
  if (!referenceCode.value.trim()) return
  await trackByReference(referenceCode.value)
}
</script>

<template>
  <DefaultLayout>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-16 space-y-8">
      <!-- Header -->
      <div class="text-center space-y-3">
        <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-[#0F5132]/10 text-3xl mx-auto">
          🔍
        </div>
        <h1 class="text-2xl font-black text-slate-900">Track an Item</h1>
        <p class="text-sm text-slate-500 max-w-sm mx-auto">
          Enter the unique reference code from your report receipt to track the current status of your item.
        </p>
      </div>

      <!-- Search Form -->
      <form class="flex gap-3" @submit.prevent="handleTrack">
        <AppInput
          id="track-code"
          class="flex-1"
          placeholder="e.g. WU-2024-001234"
          :model-value="referenceCode"
          :error="error || undefined"
          @update:model-value="referenceCode = $event"
        />
        <AppButton
          type="submit"
          variant="primary"
          :loading="loading"
        >
          Track
        </AppButton>
      </form>

      <!-- Result -->
      <div v-if="trackingResult" class="space-y-4 animate-fade-in">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tracking Result</p>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs space-y-4">
          <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
              <span class="text-xs font-mono text-slate-400">#{{ trackingResult.reference_code }}</span>
              <h2 class="text-lg font-bold text-slate-900 mt-0.5">{{ trackingResult.title }}</h2>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-800">
              {{ trackingResult.status }}
            </span>
          </div>

          <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100 text-xs">
            <div>
              <span class="text-slate-400 block mb-0.5">Category</span>
              <span class="font-semibold text-slate-700">{{ trackingResult.category || 'General' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block mb-0.5">Incident Date</span>
              <span class="font-semibold text-slate-700">{{ trackingResult.incident_date || '—' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>
