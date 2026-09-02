<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useReturns } from '../composables/useReturns'
import { getClaims, getClaim } from '@/features/claims/api/claims.api'
import type { Claim } from '@/features/claims/types/claim.types'
import { useUiStore } from '@/stores/ui.store'
import { toISODateInput } from '@/utils/date'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
import { t } from '@/i18n'
import type { StoreReturnData } from '../types/return.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import AppButton from '@/components/ui/AppButton.vue'
import StorageLocationSelect from '@/features/custody/components/StorageLocationSelect.vue'
import FormMultiImageUpload from '@/components/forms/FormMultiImageUpload.vue'

interface Props {
  itemId?: number
  claimId?: number
}

const props = defineProps<Props>()

const router = useRouter()
const { processReturn, loading } = useReturns()
const uiStore = useUiStore()

const conditionOptions = computed(() => [
  { label: t('returns.conditionGood'), value: 'good' },
  { label: t('returns.conditionDamaged'), value: 'damaged' },
  { label: t('returns.conditionAsReported'), value: 'incomplete' },
])

const approvedClaims = ref<Claim[]>([])
const loadingApprovedClaims = ref(false)
const selectedClaim = ref<Claim | null>(null)
const manualEntryMode = ref(false)

const form = reactive<StoreReturnData>({
  item_id: props.itemId || 0,
  claim_id: props.claimId,
  returned_to: 0,
  storage_location_id: undefined,
  return_date: toISODateInput(),
  condition_on_return: 'good',
  notes: '',
  documents: [],
})

const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)

const claimOptions = computed(() => {
  return approvedClaims.value.map(c => ({
    value: c.id,
    label: `Claim #${c.id}: ${c.item?.title || 'Item #' + c.item_id} — Claimant: ${c.claimant?.full_name || 'User #' + c.claimant_id}`,
  }))
})

function applyClaimData(claim: Claim) {
  selectedClaim.value = claim
  form.claim_id = claim.id
  form.item_id = claim.item_id || claim.item?.id || form.item_id
  form.returned_to = claim.claimant_id || claim.user_id || claim.claimant?.id || 0
  clearError('claim_id')
  clearError('item_id')
  clearError('returned_to')
}

async function handleClaimSelect(claimIdVal: number | string) {
  const cId = Number(claimIdVal)
  if (!cId) {
    selectedClaim.value = null
    form.claim_id = undefined
    return
  }

  const found = approvedClaims.value.find(c => c.id === cId)
  if (found) {
    applyClaimData(found)
  } else {
    try {
      const fetched = await getClaim(cId)
      applyClaimData(fetched)
    } catch {
      form.claim_id = cId
    }
  }
}

async function initClaims(): Promise<void> {
  loadingApprovedClaims.value = true
  try {
    const res = await getClaims({ status: 'approved' }, { per_page: 50 })
    approvedClaims.value = res.data || []

    if (props.claimId) {
      const match = approvedClaims.value.find(c => c.id === props.claimId)
      if (match) {
        applyClaimData(match)
      } else {
        const fetched = await getClaim(props.claimId)
        applyClaimData(fetched)
      }
    } else if (approvedClaims.value.length > 0 && !props.itemId) {
      // If there are approved claims and none selected yet, auto-select the first one
      applyClaimData(approvedClaims.value[0])
    }
  } catch (err) {
    console.error('Failed to load approved claims', err)
  } finally {
    loadingApprovedClaims.value = false
  }
}

watch(() => props.claimId, (newId) => {
  if (newId) handleClaimSelect(newId)
})

onMounted(() => {
  initClaims()
})

function clearError(field: string) {
  if (errors.value[field]) {
    const { [field]: _, ...rest } = errors.value
    errors.value = rest
  }
}

async function handleSubmit(): Promise<void> {
  generalError.value = null
  errors.value = {}

  if (!form.claim_id || form.claim_id <= 0) errors.value.claim_id = t('validation.required')
  if (!form.item_id || form.item_id <= 0) errors.value.item_id = t('validation.required')
  if (!form.returned_to || form.returned_to <= 0) errors.value.returned_to = t('validation.required')
  if (!form.return_date) errors.value.return_date = t('validation.required')
  if (!form.condition_on_return) errors.value.condition_on_return = t('validation.required')

  if (Object.keys(errors.value).length > 0) return

  try {
    await processReturn(form)
    uiStore.success(t('returns.processedSuccess'))
    router.push('/staff/dashboard')
  } catch (err) {
    const fieldErrors = getValidationErrors(err)
    if (fieldErrors) {
      errors.value = Object.entries(fieldErrors).reduce((acc, [k, v]) => {
        acc[k] = v[0] || ''
        return acc
      }, {} as Record<string, string>)
    } else {
      generalError.value = getErrorMessage(err, t('common.errorOccurred'))
    }
  }
}
</script>

<template>
  <form class="space-y-5 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs transition-colors duration-200" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-400">
      {{ generalError }}
    </div>

    <!-- Mode Selector: Choose from Approved Claims or Enter Manually -->
    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
      <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ t('returns.selectApprovedClaim') }}</span>
      <button
        type="button"
        class="text-xs text-[#0B5D3B] dark:text-[#75bd97] font-medium hover:underline cursor-pointer"
        @click="manualEntryMode = !manualEntryMode"
      >
        {{ manualEntryMode ? '← ' + t('returns.selectApprovedClaim') : t('common.edit') }}
      </button>
    </div>

    <!-- Approved Claim Selector Dropdown -->
    <div v-if="!manualEntryMode" class="space-y-4">
      <AppSelect
        :label="t('returns.selectApprovedClaim') + ' *'"
        :placeholder="loadingApprovedClaims ? t('common.loading') : (approvedClaims.length ? t('returns.selectApprovedClaim') : t('claims.review.noClaims'))"
        :options="claimOptions"
        :model-value="form.claim_id || ''"
        :error="errors.claim_id"
        required
        @update:model-value="handleClaimSelect($event)"
      />

      <!-- Claim & Claimant Summary Card -->
      <div v-if="selectedClaim" class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2 text-xs">
        <div class="flex items-center justify-between pb-2 border-b border-slate-200/80 dark:border-slate-700">
          <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 font-bold uppercase text-[10px]">
              {{ t('claims.reviewClaimsTitle') }} #{{ selectedClaim.id }}
            </span>
            <span class="font-bold text-slate-900 dark:text-white text-sm">
              {{ selectedClaim.item?.title || `Item #${selectedClaim.item_id}` }}
            </span>
          </div>
          <span class="text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ t('custody.itemId') }}: {{ selectedClaim.item_id }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
          <div>
            <span class="text-slate-400 dark:text-slate-500 block text-[11px]">{{ t('returns.recipientName') }}:</span>
            <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
              {{ selectedClaim.claimant?.full_name || 'N/A' }}
            </p>
            <p class="text-slate-500 dark:text-slate-400 text-[11px]">{{ selectedClaim.claimant?.email || '' }}</p>
            <p v-if="selectedClaim.claimant?.phone" class="text-slate-500 dark:text-slate-400 text-[11px]">
              Tel: {{ selectedClaim.claimant.phone }}
            </p>
          </div>
          <div>
            <span class="text-slate-400 dark:text-slate-500 block text-[11px]">{{ t('returns.recipientId') }}:</span>
            <p class="font-mono font-bold text-slate-700 dark:text-slate-300">
              ID #{{ selectedClaim.claimant_id || selectedClaim.user_id || selectedClaim.claimant?.id }}
            </p>
            <span class="text-slate-400 dark:text-slate-500 block text-[11px] mt-1">{{ t('claims.reviewerNote') }}:</span>
            <p class="text-slate-600 dark:text-slate-400 italic text-[11px]">{{ selectedClaim.review_note || 'Owner verified' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Manual Entry Mode (fallback) -->
    <div v-else class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <AppInput
          id="ret-claim-id"
          :label="t('claims.claimId') + ' *'"
          type="number"
          placeholder="e.g. 1"
          :model-value="form.claim_id || ''"
          :error="errors.claim_id"
          required
          @update:model-value="form.claim_id = $event ? Number($event) : undefined; handleClaimSelect($event); clearError('claim_id')"
        />

        <AppInput
          id="ret-item-id"
          :label="t('custody.itemId') + ' *'"
          type="number"
          placeholder="e.g. 1"
          :model-value="form.item_id || ''"
          :error="errors.item_id"
          required
          @update:model-value="form.item_id = Number($event); clearError('item_id')"
        />

        <AppInput
          id="ret-user-id"
          :label="t('returns.recipientId') + ' *'"
          type="number"
          placeholder="e.g. 4"
          :model-value="form.returned_to || ''"
          :error="errors.returned_to"
          required
          @update:model-value="form.returned_to = Number($event); clearError('returned_to')"
        />
      </div>
    </div>

    <!-- Handover Details -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <AppInput
        id="ret-date"
        :label="t('returns.returnDate') + ' *'"
        type="date"
        :model-value="form.return_date"
        :error="errors.return_date"
        required
        @update:model-value="form.return_date = $event; clearError('return_date')"
      />

      <StorageLocationSelect
        :label="t('returns.dispatchedStorage')"
        :model-value="form.storage_location_id"
        @update:model-value="form.storage_location_id = $event ? Number($event) : undefined"
      />
    </div>

    <AppSelect
      :label="t('claims.conditionOnReturn') + ' *'"
      :options="conditionOptions"
      :model-value="form.condition_on_return || 'good'"
      :error="errors.condition_on_return"
      required
      @update:model-value="form.condition_on_return = String($event); clearError('condition_on_return')"
    />

    <AppTextarea
      id="ret-notes"
      :label="t('returns.notes')"
      :placeholder="t('returns.placeholders.notes')"
      :model-value="form.notes || ''"
      :rows="3"
      @update:model-value="form.notes = $event"
    />

    <FormMultiImageUpload
      :label="t('claims.evidenceFiles')"
      :max-files="3"
      @files-updated="form.documents = $event"
    />

    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
      <AppButton variant="outline" type="button" @click="router.back()">
        {{ t('common.cancel') }}
      </AppButton>
      <AppButton variant="primary" type="submit" :loading="loading">
        {{ t('common.confirm') }}
      </AppButton>
    </div>
  </form>
</template>
