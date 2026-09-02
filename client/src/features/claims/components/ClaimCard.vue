<script setup lang="ts">
import type { Claim } from '../types/claim.types'
import { formatDate } from '@/utils/date'
import ClaimStatusBadge from './ClaimStatusBadge.vue'
import { Paperclip, Calendar, User, FileText } from 'lucide-vue-next'

interface Props {
  claim: Claim
}

defineProps<Props>()
</script>

<template>
  <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-[#111827] p-5 shadow-2xs hover:shadow-md dark:hover:border-slate-700 transition-all duration-150 space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-100 dark:border-slate-800">
      <div>
        <div class="flex items-center gap-2 mb-1 flex-wrap">
          <span class="text-xs font-mono font-bold text-slate-400 dark:text-slate-500">#CLM-{{ claim.id }}</span>
          <ClaimStatusBadge :status="claim.status" />
          <span v-if="claim.claimant?.full_name" class="inline-flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400 font-medium">
            <User class="h-3 w-3 text-slate-400" />
            {{ claim.claimant.full_name }}
          </span>
        </div>
        <h4 class="text-base font-bold text-slate-900 dark:text-white">
          {{ claim.item?.title || `Item #${claim.item_id}` }}
        </h4>
      </div>

      <div class="flex items-center gap-2 shrink-0 self-start sm:self-auto">
        <span class="inline-flex items-center gap-1 text-xs text-slate-400 dark:text-slate-500 font-medium">
          <Calendar class="h-3.5 w-3.5 text-slate-400" />
          {{ formatDate(claim.created_at, 'short') }}
        </span>
        <slot name="header-actions" :claim="claim" />
      </div>
    </div>

    <!-- Proof / Explanation -->
    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3.5 text-xs text-slate-600 dark:text-slate-300 space-y-1">
      <div class="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-200">
        <FileText class="h-3.5 w-3.5 text-slate-400" />
        <span>Proof & Explanation:</span>
      </div>
      <p class="leading-relaxed whitespace-pre-line">{{ claim.explanation }}</p>
    </div>

    <!-- Reviewer Note if any -->
    <div v-if="claim.review_note" class="rounded-xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/60 p-3 text-xs text-amber-900 dark:text-amber-200">
      <p class="font-bold mb-0.5">Staff Review Note:</p>
      <p>{{ claim.review_note }}</p>
    </div>

    <!-- Bottom: Evidence files & Slot for Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Evidence summary -->
      <div v-if="claim.evidence && claim.evidence.length > 0" class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium">
        <Paperclip class="h-3.5 w-3.5 text-slate-400" />
        <span>{{ claim.evidence.length }} evidence file(s) attached</span>
      </div>
      <div v-else class="text-[11px] text-slate-400 dark:text-slate-500 italic">
        No evidence attachments
      </div>

      <!-- Action buttons slot -->
      <div v-if="$slots.actions" class="flex items-center gap-2 self-end sm:self-auto">
        <slot name="actions" :claim="claim" />
      </div>
    </div>
  </div>
</template>
