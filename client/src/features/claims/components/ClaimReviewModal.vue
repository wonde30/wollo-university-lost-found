<script setup lang="ts">
import { reactive, ref } from 'vue'
import type { Claim } from '../types/claim.types'
import { useClaims } from '../composables/useClaims'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
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
  (e: 'close'): void
  (e: 'reviewed'): void
}>()

const { reviewClaim, loading } = useClaims()
const uiStore = useUiStore()

const form = reactive({
  status: 'approved' as 'approved' | 'rejected',
  review_note: '',
})

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
    errors.value.status = 'Please select a review decision.'
  }
  if (form.status === 'rejected' && !form.review_note.trim()) {
    errors.value.review_note = 'A review note is required when rejecting a claim.'
  }

  if (Object.keys(errors.value).length > 0) return

  try {
    await reviewClaim(props.claim.id, {
      status: form.status,
      review_note: form.review_note,
    })
    uiStore.success(`Claim #${props.claim.id} decision updated to ${form.status}.`)
    emit('reviewed')
    emit('close')
  } catch (err) {
    generalError.value = getErrorMessage(err, 'Failed to review claim.')
  }
}
</script>

<template>
  <AppModal
    :open="open"
    title="Review Ownership Claim"
    size="md"
    @close="$emit('close')"
  >
    <div v-if="claim" class="space-y-4">
      <div v-if="generalError" class="p-3 rounded-xl bg-rose-50 text-xs text-rose-700">
        {{ generalError }}
      </div>

      <div class="rounded-xl bg-slate-50 p-3 text-xs space-y-1.5 border border-slate-200/80">
        <p><span class="font-semibold text-slate-700">Item:</span> {{ claim.item?.title || `#${claim.item_id}` }}</p>
        <p><span class="font-semibold text-slate-700">Claimant:</span> {{ claim.claimant?.full_name || claim.claimant?.email }}</p>
        <p><span class="font-semibold text-slate-700">Explanation:</span> {{ claim.explanation }}</p>
      </div>

      <form class="space-y-4" @submit.prevent="handleSubmit">
        <AppSelect
          label="Review Decision"
          :options="[
            { label: 'Approve Claim (Verified Owner)', value: 'approved' },
            { label: 'Reject Claim (Insufficient Proof)', value: 'rejected' },
          ]"
          :model-value="form.status"
          :error="errors.status"
          required
          @update:model-value="form.status = $event as any; clearError('status')"
        />

        <AppTextarea
          label="Review Notes / Instructions for Claimant"
          :placeholder="form.status === 'rejected' ? 'Required: explain why the claim was rejected...' : 'Provide verification details or instructions for item pickup...'"
          :model-value="form.review_note"
          :error="errors.review_note"
          :hint="form.status === 'rejected' ? 'Required when rejecting a claim' : undefined"
          :rows="3"
          @update:model-value="form.review_note = $event; clearError('review_note')"
        />

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
          <AppButton variant="outline" size="sm" type="button" @click="$emit('close')">
            Cancel
          </AppButton>
          <AppButton
            :variant="form.status === 'approved' ? 'primary' : 'danger'"
            size="sm"
            type="submit"
            :loading="loading"
          >
            Submit Decision
          </AppButton>
        </div>
      </form>
    </div>
  </AppModal>
</template>
