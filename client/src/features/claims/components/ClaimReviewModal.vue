<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import type { Claim } from '../types/claim.types'
import { useClaims } from '../composables/useClaims'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { t } from '@/i18n'
import AppModal from '@/components/ui/AppModal.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import AppButton from '@/components/ui/AppButton.vue'

interface Props {
  open: boolean
  claim: Claim | null
}

const props = defineProps<Props>()
const emit = defineEmits<{
  close: []
  reviewed: []
}>()

const { reviewClaim, loading } = useClaims()
const uiStore = useUiStore()

const form = reactive({
  status: 'approved' as 'approved' | 'rejected',
  review_note: '',
})

const decisionOptions = computed(() => [
  { label: t('claims.approveClaim'), value: 'approved' },
  { label: t('claims.rejectClaim'), value: 'rejected' },
])

const errors = ref<Record<string, string>>({})
const generalError = ref<string | null>(null)

function clearError(field: string) {
  if (errors.value[field]) {
    const { [field]: _, ...rest } = errors.value
    errors.value = rest
  }
}

async function handleSubmit(): Promise<void> {
  if (!props.claim) return
  generalError.value = null
  errors.value = {}

  if (!form.status) {
    errors.value.status = t('validation.required')
  }
  if (form.status === 'rejected' && !form.review_note.trim()) {
    errors.value.review_note = t('validation.required')
  }

  if (Object.keys(errors.value).length > 0) return

  try {
    await reviewClaim(props.claim.id, {
      status: form.status,
      review_note: form.review_note,
    })
    uiStore.success(t('claims.decisionUpdated', { id: props.claim.id, status: form.status }))
    emit('reviewed')
    emit('close')
  } catch (err) {
    generalError.value = getErrorMessage(err, t('common.errorOccurred'))
  }
}
</script>

<template>
  <AppModal
    :open="open"
    :title="t('claims.review.title')"
    size="md"
    @close="$emit('close')"
  >
    <div v-if="claim" class="space-y-4">
      <div v-if="generalError" class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-xs text-rose-700 dark:text-rose-400">
        {{ generalError }}
      </div>

      <div class="rounded-xl bg-slate-50 dark:bg-slate-800/60 p-3 text-xs space-y-1.5 border border-slate-200/80 dark:border-slate-700">
        <p><span class="font-semibold text-slate-700 dark:text-slate-300">{{ t('items.myItems.item') }}:</span> {{ claim.item?.title || `#${claim.item_id}` }}</p>
        <p><span class="font-semibold text-slate-700 dark:text-slate-300">{{ t('claims.review.ownership') }}:</span> {{ claim.claimant?.full_name || claim.claimant?.email }}</p>
        <p><span class="font-semibold text-slate-700 dark:text-slate-300">{{ t('items.form.description') }}:</span> {{ claim.explanation }}</p>
      </div>

      <form class="space-y-4" @submit.prevent="handleSubmit">
        <AppSelect
          :label="t('claims.review.verificationStatus')"
          :options="decisionOptions"
          :model-value="form.status"
          :error="errors.status"
          required
          @update:model-value="form.status = $event as any; clearError('status')"
        />

        <AppTextarea
          :label="t('admin.auditLogs.details')"
          :placeholder="t('claims.reviewerNotePlaceholder')"
          :model-value="form.review_note"
          :error="errors.review_note"
          :hint="form.status === 'rejected' ? t('validation.required') : undefined"
          :rows="3"
          @update:model-value="form.review_note = $event; clearError('review_note')"
        />

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
          <AppButton variant="outline" size="sm" type="button" @click="$emit('close')">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton
            :variant="form.status === 'approved' ? 'primary' : 'danger'"
            size="sm"
            type="submit"
            :loading="loading"
          >
            {{ t('common.confirm') }}
          </AppButton>
        </div>
      </form>
    </div>
  </AppModal>
</template>
