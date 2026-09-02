<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import { useCustody } from '../composables/useCustody'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { t } from '@/i18n'
import AppModal from '@/components/ui/AppModal.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import StorageLocationSelect from './StorageLocationSelect.vue'

interface Props {
  open: boolean
  itemId?: number
  itemTitle?: string
}

const props = withDefaults(defineProps<Props>(), {
  itemId: 0,
  itemTitle: '',
})

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'transferred'): void
}>()

const { moveItem, loading } = useCustody()
const uiStore = useUiStore()

const inputItemId = ref<number | ''>(props.itemId || '')
const form = reactive({
  storage_location_id: '' as number | '',
  condition: '',
  notes: '',
})

const generalError = ref<string | null>(null)

watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) {
      inputItemId.value = props.itemId > 0 ? props.itemId : ''
      form.storage_location_id = ''
      form.condition = ''
      form.notes = ''
      generalError.value = null
    }
  }
)

async function handleSubmit(): Promise<void> {
  const targetId = props.itemId > 0 ? props.itemId : Number(inputItemId.value)
  if (!targetId || targetId <= 0) {
    generalError.value = t('validation.required')
    return
  }

  if (!form.storage_location_id) {
    generalError.value = t('validation.required')
    return
  }

  generalError.value = null
  try {
    const combinedNotes = [
      form.condition ? `Condition: ${form.condition}` : '',
      form.notes || '',
    ].filter(Boolean).join(' | ')

    await moveItem(targetId, {
      storage_location_id: Number(form.storage_location_id),
      notes: combinedNotes || undefined,
    })
    uiStore.success(t('custody.transferSuccess'))
    emit('transferred')
    emit('close')
  } catch (err) {
    generalError.value = getErrorMessage(err, t('common.errorOccurred'))
  }
}
</script>

<template>
  <AppModal
    :open="open"
    :title="t('custody.transferTitle')"
    size="md"
    @close="$emit('close')"
  >
    <form class="space-y-4" @submit.prevent="handleSubmit">
      <div v-if="generalError" class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-700 dark:text-rose-300">
        {{ generalError }}
      </div>

      <div v-if="props.itemId > 0" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 text-xs">
        <span class="font-medium text-slate-500 dark:text-slate-400">{{ t('items.myItems.item') }}:</span>
        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ itemTitle || `Item #${props.itemId}` }}</p>
      </div>

      <div v-else>
        <AppInput
          id="custody-item-id"
          :label="t('custody.itemId')"
          type="number"
          :placeholder="t('custody.itemId')"
          :model-value="inputItemId"
          required
          @update:model-value="inputItemId = $event ? Number($event) : ''"
        />
      </div>

      <StorageLocationSelect
        :model-value="form.storage_location_id"
        required
        @update:model-value="form.storage_location_id = $event ? Number($event) : ''"
      />

      <AppInput
        :label="t('custody.conditionOnTransfer')"
        :placeholder="t('custody.placeholders.condition')"
        :model-value="form.condition"
        @update:model-value="form.condition = $event"
      />

      <AppTextarea
        :label="t('custody.officerNotes')"
        :placeholder="t('custody.placeholders.condition')"
        :model-value="form.notes"
        :rows="2"
        @update:model-value="form.notes = $event"
      />

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
        <AppButton variant="outline" size="sm" type="button" @click="$emit('close')">
          {{ t('common.cancel') }}
        </AppButton>
        <AppButton variant="primary" size="sm" type="submit" :loading="loading">
          {{ t('custody.transferBtn') }}
        </AppButton>
      </div>
    </form>
  </AppModal>
</template>
