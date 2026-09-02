<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useReturnsStore } from '../stores/returns.store'
import { useUiStore } from '@/stores/ui.store'
import { formatDate } from '@/utils/date'
import { t } from '@/i18n'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import { Download, Filter, RotateCcw, PackageSearch } from 'lucide-vue-next'

const returnsStore = useReturnsStore()
const uiStore = useUiStore()

const dateFrom = ref('')
const dateTo = ref('')
const exporting = ref(false)

async function loadData(page = 1) {
  try {
    await returnsStore.fetchReturns({
      date_from: dateFrom.value || undefined,
      date_to: dateTo.value || undefined,
      page,
    })
  } catch (err) {
    uiStore.error(t('returns.loadError'))
  }
}

function handleFilter() {
  loadData(1)
}

function handleReset() {
  dateFrom.value = ''
  dateTo.value = ''
  loadData(1)
}

async function handleExportCsv() {
  exporting.value = true
  try {
    await returnsStore.exportCsv({
      date_from: dateFrom.value || undefined,
      date_to: dateTo.value || undefined,
    })
    uiStore.success(t('returns.exportSuccess'))
  } catch (err) {
    uiStore.error(t('returns.exportError'))
  } finally {
    exporting.value = false
  }
}

onMounted(() => {
  loadData(1)
})
</script>

<template>
  <div class="space-y-4">
    <!-- Filter & Export Toolbar (FR-46) -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4 transition-colors duration-200">
      <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
        <div class="w-full sm:w-40">
          <AppInput
            :label="t('common.filter') + ' (From)'"
            type="date"
            :model-value="dateFrom"
            @update:model-value="dateFrom = $event"
          />
        </div>
        <div class="w-full sm:w-40">
          <AppInput
            :label="t('common.filter') + ' (To)'"
            type="date"
            :model-value="dateTo"
            @update:model-value="dateTo = $event"
          />
        </div>
        <div class="flex items-center gap-2 pt-5">
          <AppButton variant="secondary" size="md" @click="handleFilter">
            <template #icon-left>
              <Filter class="h-3.5 w-3.5 mr-1" />
            </template>
            {{ t('common.filter') }}
          </AppButton>
          <AppButton variant="ghost" size="md" @click="handleReset">
            <template #icon-left>
              <RotateCcw class="h-3.5 w-3.5 mr-1" />
            </template>
            {{ t('common.reset') }}
          </AppButton>
        </div>
      </div>

      <div class="pt-2 sm:pt-0 shrink-0 w-full sm:w-auto flex justify-end">
        <AppButton
          variant="primary"
          size="md"
          :loading="exporting"
          @click="handleExportCsv"
        >
          <template #icon-left>
            <Download class="h-4 w-4 mr-1" />
          </template>
          {{ t('admin.auditLogs.export') }}
        </AppButton>
      </div>
    </div>

    <!-- Returns Table Container -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-xs transition-colors duration-200">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
          <thead class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">
            <tr>
              <th class="px-5 py-3.5">{{ t('returns.process.returnId') }}</th>
              <th class="px-5 py-3.5">{{ t('returns.process.itemTitle') }}</th>
              <th class="px-5 py-3.5">{{ t('returns.process.returnedTo') }}</th>
              <th class="px-5 py-3.5">{{ t('returns.process.handedBy') }}</th>
              <th class="px-5 py-3.5">{{ t('returns.process.returnDate') }}</th>
              <th class="px-5 py-3.5">{{ t('returns.process.condition') }}</th>
              <th class="px-5 py-3.5">{{ t('returns.process.recipientStatus') }}</th>
            </tr>
          </thead>
          <tbody v-if="returnsStore.loading && returnsStore.returns.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="n in 4" :key="n" class="animate-pulse">
              <td class="px-5 py-4">
                <div class="h-4 w-20 bg-slate-200 dark:bg-slate-800 rounded font-mono" />
              </td>
              <td class="px-5 py-4 space-y-1.5">
                <div class="h-4 w-36 bg-slate-200 dark:bg-slate-800 rounded" />
                <div class="h-3 w-24 bg-slate-100 dark:bg-slate-800/60 rounded font-mono" />
              </td>
              <td class="px-5 py-4 space-y-1">
                <div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded" />
                <div class="h-3 w-32 bg-slate-100 dark:bg-slate-800/60 rounded font-mono" />
              </td>
              <td class="px-5 py-4">
                <div class="h-4 w-24 bg-slate-200 dark:bg-slate-800 rounded" />
              </td>
              <td class="px-5 py-4">
                <div class="h-4 w-20 bg-slate-200 dark:bg-slate-800 rounded" />
              </td>
              <td class="px-5 py-4">
                <div class="h-5 w-16 bg-slate-200 dark:bg-slate-800 rounded-full" />
              </td>
              <td class="px-5 py-4">
                <div class="h-5 w-20 bg-slate-200 dark:bg-slate-800 rounded-full" />
              </td>
            </tr>
          </tbody>
          <tbody v-else-if="returnsStore.returns.length === 0">
            <tr>
              <td colspan="7" class="p-12 text-center">
                <div class="max-w-xs mx-auto space-y-2 text-center">
                  <div class="h-12 w-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                    <PackageSearch class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                  </div>
                  <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                    {{ dateFrom || dateTo ? 'No returns match selected date range' : t('returns.process.noHandovers') }}
                  </p>
                  <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    {{ dateFrom || dateTo ? 'Try clearing or widening your date filter.' : 'All finalized property handovers and claimant receipts will appear here.' }}
                  </p>
                </div>
              </td>
            </tr>
          </tbody>
          <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr v-for="ret in returnsStore.returns" :key="ret.id" class="hover:bg-slate-50/75 dark:hover:bg-slate-800/50 transition-colors">
              <td class="px-5 py-4 font-mono font-bold text-slate-900 dark:text-white">
                #RET-{{ ret.id }}
              </td>
              <td class="px-5 py-4">
                <div class="font-bold text-slate-900 dark:text-white">
                  {{ ret.item?.title || `Item #${ret.item_id}` }}
                </div>
                <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500">Ref: {{ ret.item?.reference_code || '—' }}</span>
              </td>
              <td class="px-5 py-4">
                <div class="font-medium text-slate-900 dark:text-white">{{ ret.recipient?.full_name || `User #${ret.returned_to}` }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ ret.recipient?.email || '' }}</div>
              </td>
              <td class="px-5 py-4 text-slate-600 dark:text-slate-300">
                {{ ret.staff?.full_name || 'Campus Staff' }}
              </td>
              <td class="px-5 py-4 text-slate-600 dark:text-slate-300">
                {{ formatDate(ret.return_date) }}
              </td>
              <td class="px-5 py-4">
                <span class="capitalize px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                  {{ ret.condition_on_return || 'Good' }}
                </span>
              </td>
              <td class="px-5 py-4">
                <AppBadge :variant="ret.recipient_confirmed ? 'success' : 'warning'" size="sm">
                  {{ ret.recipient_confirmed ? t('returns.process.confirmed') : t('returns.process.pendingReceipt') }}
                </AppBadge>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <AppPagination
        v-if="returnsStore.pagination && returnsStore.pagination.last_page > 1"
        :current-page="returnsStore.pagination.current_page"
        :last-page="returnsStore.pagination.last_page"
        :total="returnsStore.pagination.total"
        :per-page="returnsStore.pagination.per_page"
        @change="loadData"
      />
    </div>
  </div>
</template>
