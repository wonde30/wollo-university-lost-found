<script setup lang="ts">
import type { Claim } from '../types/claim.types'
import { formatDate } from '@/utils/date'
import ClaimStatusBadge from './ClaimStatusBadge.vue'

interface Props {
  claim: Claim
}

defineProps<Props>()
</script>

<template>
  <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:shadow-md transition-all space-y-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="text-xs font-mono text-slate-400">#CLM-{{ claim.id }}</span>
          <ClaimStatusBadge :status="claim.status" />
        </div>
        <h4 class="text-base font-bold text-slate-900">
          {{ claim.item?.title || `Item #${claim.item_id}` }}
        </h4>
      </div>

      <span class="text-xs text-slate-400">
        {{ formatDate(claim.created_at, 'short') }}
      </span>
    </div>

    <div class="rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
      <p class="font-semibold text-slate-700 mb-1">Proof & Explanation:</p>
      <p class="line-clamp-3 leading-relaxed">{{ claim.explanation }}</p>
    </div>

    <!-- Reviewer Note if any -->
    <div v-if="claim.review_note" class="rounded-xl bg-amber-50/70 border border-amber-200/60 p-3 text-xs text-amber-900">
      <p class="font-semibold mb-0.5">Staff Review Note:</p>
      <p>{{ claim.review_note }}</p>
    </div>

    <!-- Evidence summary -->
    <div v-if="claim.evidence && claim.evidence.length > 0" class="flex items-center gap-2 text-xs text-slate-500">
      <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
      </svg>
      <span>{{ claim.evidence.length }} evidence file(s) attached</span>
    </div>
  </div>
</template>
