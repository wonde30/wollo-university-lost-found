<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useClaims } from '../composables/useClaims'
import { validateStoreClaimForm } from '../validation/claim.validation'
import { getErrorMessage, getValidationErrors } from '@/utils/error-handler'
import { useUiStore } from '@/stores/ui.store'
import type { StoreClaimData } from '../types/claim.types'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import AppButton from '@/components/ui/AppButton.vue'
import FormMultiImageUpload from '@/components/forms/FormMultiImageUpload.vue'

interface Props {
  itemId: number
  itemTitle?: string
}

const props = defineProps<Props>()

const router = useRouter()
const { submitClaim, loading } = useClaims()
const uiStore = useUiStore()

const form = reactive<StoreClaimData>({
  item_id: props.itemId,
  explanation: '',
  evidence: [],
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
  generalError.value = null
  errors.value = validateStoreClaimForm(form)
  if (Object.keys(errors.value).length > 0) return

  try {
    await submitClaim(form)
    uiStore.success('Your ownership claim has been submitted for review.')
    router.push('/student/my-claims')
  } catch (err) {
    const fieldErrors = getValidationErrors(err)
    if (fieldErrors) {
      errors.value = Object.entries(fieldErrors).reduce((acc, [k, v]) => {
        acc[k] = v[0] || ''
        return acc
      }, {} as Record<string, string>)
    } else {
      generalError.value = getErrorMessage(err, 'Failed to submit claim.')
    }
  }
}
</script>

<template>
  <form class="space-y-5 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs" @submit.prevent="handleSubmit">
    <div v-if="generalError" class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
      {{ generalError }}
    </div>

    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
      <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Claiming Item</span>
      <h4 class="text-base font-bold text-slate-900">{{ itemTitle || `Item #${itemId}` }}</h4>
    </div>

    <AppTextarea
      id="claim-explanation"
      label="Proof of Ownership & Distinctive Features"
      placeholder="Describe specific markings, contents, serial numbers, purchase details, or unlock codes that prove this item belongs to you..."
      :model-value="form.explanation"
      :error="errors.explanation"
      :hint="!errors.explanation ? `${form.explanation.length}/1000 — minimum 50 characters` : undefined"
      :rows="5"
      required
      @update:model-value="form.explanation = $event; clearError('explanation')"
    />

    <FormMultiImageUpload
      label="Attach Proof Evidence (Photos, Receipts, ID, etc.)"
      :max-files="4"
      @files-updated="form.evidence = $event"
    />

    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
      <AppButton variant="outline" type="button" @click="router.back()">
        Cancel
      </AppButton>
      <AppButton variant="primary" type="submit" :loading="loading">
        Submit Ownership Claim
      </AppButton>
    </div>
  </form>
</template>
