<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useMatchSuggestionsStore } from '@/features/match-suggestions/stores/match-suggestions.store'
import { useUiStore } from '@/stores/ui.store'
import { formatDate } from '@/utils/date'
import { currentLocale, t } from '@/i18n'
import AppButton from '@/components/ui/AppButton.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'
import AppEmptyState from '@/components/ui/AppEmptyState.vue'
import {
  Sparkles,
  Target,
  CheckCircle2,
  XCircle,
  Search,
  Package,
  ExternalLink,
} from 'lucide-vue-next'

const matchStore = useMatchSuggestionsStore()
const uiStore = useUiStore()

const selectedStatus = ref<'pending' | 'confirmed' | 'dismissed' | ''>('pending')
const processingId = ref<number | null>(null)

const statusOptions = computed(() => [
  { label: t('claims.tabs.pending'), value: 'pending' },
  { label: t('claims.tabs.approved'), value: 'confirmed' },
  { label: t('matchSuggestions.dismissMatch'), value: 'dismissed' },
  { label: t('claims.tabs.all'), value: '' },
])

async function loadData(page = 1) {
  try {
    await matchStore.fetchSuggestions({
      status: selectedStatus.value || undefined,
      page,
    })
  } catch (err) {
    uiStore.error(t('common.errorOccurred'))
  }
}

async function handleAction(id: number, status: 'confirmed' | 'dismissed') {
  processingId.value = id
  try {
    await matchStore.reviewSuggestion(id, status)
    uiStore.success(t('matchSuggestions.markedSuccess'))
    await loadData(matchStore.pagination?.current_page || 1)
  } catch (err) {
    uiStore.error(t('common.errorOccurred'))
  } finally {
    processingId.value = null
  }
}

onMounted(() => {
  loadData(1)
})
</script>

<template>
  <div class="space-y-4 sm:space-y-5">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300">
              <Sparkles class="h-3.5 w-3.5" />
              {{ t('matchSuggestions.title') }}
            </span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ t('matchSuggestions.title') }}</h1>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
            {{ t('matchSuggestions.subtitle') }}
          </p>
        </div>

        <div class="w-full sm:w-56">
          <AppSelect
            :options="statusOptions"
            :model-value="selectedStatus"
            :placeholder="t('common.filter')"
            @update:model-value="selectedStatus = $event as any; loadData(1)"
          />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="matchStore.loading && matchStore.suggestions.length === 0" class="space-y-4">
        <AppSkeleton v-for="n in 3" :key="n" height="12rem" class="rounded-3xl" />
      </div>

      <!-- Empty State -->
      <AppEmptyState
        v-else-if="matchStore.suggestions.length === 0"
        :title="t('matchSuggestions.noMatches')"
        :description="t('matchSuggestions.noMatchesDesc')"
      />

      <!-- Suggestions List -->
      <div v-else class="space-y-4">
        <div
          v-for="item in matchStore.suggestions"
          :key="item.id"
          class="bg-white dark:bg-[#111827] rounded-3xl border border-slate-200/80 dark:border-slate-800 p-5 sm:p-6 shadow-2xs hover:shadow-md dark:hover:border-slate-700 transition-all duration-150 space-y-4"
        >
          <!-- Top Bar: Match Score & Status -->
          <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3 flex-wrap">
              <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] border border-[#0B5D3B]/20 dark:border-[#0B5D3B]/40">
                <Target class="h-3.5 w-3.5 text-[#0B5D3B] dark:text-[#75bd97]" />
                <span>{{ t('matchSuggestions.matchScore') }}:</span>
                <span class="text-sm font-black">{{ Math.round((item.score || 0)) }}%</span>
              </div>
              <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-semibold">
                {{ item.algorithm_version || 'v1.0' }}
              </span>
              <AppBadge
                :variant="item.status === 'confirmed' ? 'success' : (item.status === 'dismissed' ? 'danger' : 'warning')"
                size="sm"
              >
                {{ item.status.toUpperCase() }}
              </AppBadge>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500 flex-wrap">
              <span v-if="item.category_score">{{ t('items.myItems.category') }}: {{ item.category_score }}pts</span>
              <span v-if="item.text_score">Text: {{ Math.round(item.text_score) }}pts</span>
              <span v-if="item.location_score">{{ t('nav.locations') }}: +{{ item.location_score }}pts</span>
              <span>&bull; {{ formatDate(item.created_at) }}</span>
            </div>
          </div>

          <!-- Comparison Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Lost Item Card -->
            <div class="rounded-2xl bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40 p-4 space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300 inline-flex items-center gap-1">
                  <Search class="h-3.5 w-3.5" />
                  {{ t('matchSuggestions.lostItem') }}
                </span>
                <RouterLink
                  v-if="item.lost_item"
                  :to="`/items/${item.lost_item.id}`"
                  class="text-xs font-semibold text-amber-700 dark:text-amber-400 hover:underline inline-flex items-center gap-1"
                  target="_blank"
                >
                  {{ t('common.details') }}
                  <ExternalLink class="h-3 w-3" />
                </RouterLink>
              </div>

              <div v-if="item.lost_item">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">{{ item.lost_item.title }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">Ref: {{ item.lost_item.reference_code }}</p>
                <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 line-clamp-2">{{ item.lost_item.description }}</p>
                <div class="grid grid-cols-2 gap-2 mt-3 pt-2 border-t border-amber-200/40 dark:border-amber-800/40 text-[11px] text-slate-600 dark:text-slate-300">
                  <div><strong>{{ t('items.myItems.category') }}:</strong> {{ (currentLocale === 'am' && item.lost_item.category?.display_name_am) ? item.lost_item.category.display_name_am : (item.lost_item.category?.display_name || item.lost_item.category?.name || t('items.category')) }}</div>
                  <div><strong>{{ t('matchSuggestions.dateLost') }}:</strong> {{ formatDate(item.lost_item.incident_date) }}</div>
                  <div v-if="item.lost_item.location"><strong>{{ t('nav.locations') }}:</strong> {{ (currentLocale === 'am' && item.lost_item.location.display_name_am) ? item.lost_item.location.display_name_am : (item.lost_item.location.display_name || item.lost_item.location.name) }}</div>
                  <div v-if="item.lost_item.brand"><strong>{{ t('matchSuggestions.brand') }}:</strong> {{ item.lost_item.brand }}</div>
                </div>
              </div>
              <div v-else class="text-xs text-slate-400 italic">{{ t('matchSuggestions.lostUnavailable') }}</div>
            </div>

            <!-- Found Item Card -->
            <div class="rounded-2xl bg-[#E8F4EE]/40 dark:bg-[#153C2D]/20 border border-[#0B5D3B]/20 dark:border-[#0B5D3B]/40 p-4 space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#0B5D3B] dark:text-[#75bd97] inline-flex items-center gap-1">
                  <Package class="h-3.5 w-3.5" />
                  {{ t('matchSuggestions.foundItem') }}
                </span>
                <RouterLink
                  v-if="item.found_item"
                  :to="`/items/${item.found_item.id}`"
                  class="text-xs font-semibold text-[#0B5D3B] dark:text-[#75bd97] hover:underline inline-flex items-center gap-1"
                  target="_blank"
                >
                  {{ t('common.details') }}
                  <ExternalLink class="h-3 w-3" />
                </RouterLink>
              </div>

              <div v-if="item.found_item">
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">{{ item.found_item.title }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">Ref: {{ item.found_item.reference_code }}</p>
                <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 line-clamp-2">{{ item.found_item.description }}</p>
                <div class="grid grid-cols-2 gap-2 mt-3 pt-2 border-t border-emerald-200/40 dark:border-emerald-800/40 text-[11px] text-slate-600 dark:text-slate-300">
                  <div><strong>{{ t('items.myItems.category') }}:</strong> {{ (currentLocale === 'am' && item.found_item.category?.display_name_am) ? item.found_item.category.display_name_am : (item.found_item.category?.display_name || item.found_item.category?.name || t('items.category')) }}</div>
                  <div><strong>{{ t('matchSuggestions.dateFound') }}:</strong> {{ formatDate(item.found_item.incident_date) }}</div>
                  <div v-if="item.found_item.location"><strong>{{ t('nav.locations') }}:</strong> {{ (currentLocale === 'am' && item.found_item.location.display_name_am) ? item.found_item.location.display_name_am : (item.found_item.location.display_name || item.found_item.location.name) }}</div>
                  <div v-if="item.found_item.brand"><strong>{{ t('matchSuggestions.brand') }}:</strong> {{ item.found_item.brand }}</div>
                </div>
              </div>
              <div v-else class="text-xs text-slate-400 italic">{{ t('matchSuggestions.foundUnavailable') }}</div>
            </div>
          </div>

          <!-- Actions -->
          <div v-if="item.status === 'pending'" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
            <p class="text-[11px] text-slate-400 dark:text-slate-500">
              * {{ t('matchSuggestions.subtitle') }}
            </p>
            <div class="flex items-center gap-2">
              <AppButton
                variant="outline"
                size="sm"
                :loading="processingId === item.id"
                @click="handleAction(item.id, 'dismissed')"
              >
                <template #icon-left>
                  <XCircle class="h-3.5 w-3.5 mr-1 text-rose-500" />
                </template>
                {{ t('matchSuggestions.dismissMatch') }}
              </AppButton>
              <AppButton
                variant="primary"
                size="sm"
                :loading="processingId === item.id"
                @click="handleAction(item.id, 'confirmed')"
              >
                <template #icon-left>
                  <CheckCircle2 class="h-3.5 w-3.5 mr-1" />
                </template>
                {{ t('matchSuggestions.confirmMatch') }}
              </AppButton>
            </div>
          </div>
          <div v-else class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
            <div v-if="item.status === 'confirmed' && item.found_item" class="flex items-center gap-2">
              <RouterLink :to="`/staff/review-claims?item_id=${item.found_item.id}`">
                <AppButton variant="secondary" size="xs">
                  {{ t('claims.actions.review') }} &rarr;
                </AppButton>
              </RouterLink>
            </div>
            <span class="text-[11px] text-slate-400 dark:text-slate-500 italic ml-auto">
              {{ t('claims.review.reviewedAt') }}: {{ formatDate(item.reviewed_at || item.created_at) }}
            </span>
          </div>
        </div>

        <AppPagination
          v-if="matchStore.pagination && matchStore.pagination.last_page > 1"
          :current-page="matchStore.pagination.current_page"
          :last-page="matchStore.pagination.last_page"
          :total="matchStore.pagination.total"
          :per-page="matchStore.pagination.per_page"
          @change="loadData"
        />
      </div>
    </div>
</template>
