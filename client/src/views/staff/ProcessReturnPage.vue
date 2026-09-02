<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import ReturnForm from '@/features/returns/components/ReturnForm.vue'
import ReturnHistory from '@/features/returns/components/ReturnHistory.vue'
import { t } from '@/i18n'
import { Handshake, History, Plus } from 'lucide-vue-next'

const route = useRoute()
const activeTab = ref<'form' | 'history'>('form')

const itemId = computed(() => route.query.item_id ? Number(route.query.item_id) : undefined)
const claimId = computed(() => route.query.claim_id ? Number(route.query.claim_id) : undefined)
</script>

<template>
  <div class="space-y-4 sm:space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#0B5D3B]/10 dark:bg-[#0B5D3B]/20 text-[#0B5D3B] dark:text-[#75bd97]">
            <Handshake class="h-3.5 w-3.5" />
            {{ t('returns.process.custodyResolution') }}
          </span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ t('returns.process.title') }}</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
          {{ t('returns.process.subtitle') }}
        </p>
      </div>

      <!-- Tab Toggle -->
      <div class="inline-flex rounded-2xl bg-slate-100 dark:bg-slate-800 p-1 border border-slate-200/80 dark:border-slate-700">
        <button
          type="button"
          class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer"
          :class="activeTab === 'form' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
          @click="activeTab = 'form'"
        >
          <Plus class="h-3.5 w-3.5" />
          {{ t('returns.process.recordHandover') }}
        </button>
        <button
          type="button"
          class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer"
          :class="activeTab === 'history' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
          @click="activeTab = 'history'"
        >
          <History class="h-3.5 w-3.5" />
          {{ t('returns.process.returnHistory') }}
        </button>
      </div>
    </div>

    <!-- Tab 1: Form -->
    <div v-if="activeTab === 'form'" class="max-w-2xl mx-auto">
      <ReturnForm :item-id="itemId" :claim-id="claimId" />
    </div>

    <!-- Tab 2: History & Export -->
    <div v-else>
      <ReturnHistory />
    </div>
  </div>
</template>
