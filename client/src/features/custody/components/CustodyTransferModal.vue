<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import { useCustody } from '../composables/useCustody'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
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
    generalError.value = 'Please enter a valid Item ID.'
    return
  }

  if (!form.storage_location_id) {
    generalError.value = 'Please select a destination storage location.'
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
    uiStore.success('Item custody transfer recorded successfully.')
    emit('transferred')
    emit('close')
  } catch (err) {
    generalError.value = getErrorMessage(err, 'Failed to transfer item custody.')
  }
}
</script>

<template>
  <AppModal
    :open="open"
    title="Transfer Item Storage Location"
    size="md"
    @close="$emit('close')"
  >
    <form class="space-y-4" @submit.prevent="handleSubmit">
      <div v-if="generalError" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
        {{ generalError }}
      </div>

      <div v-if="props.itemId > 0" class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
        <span class="font-medium text-slate-500">Item:</span>
        <p class="text-sm font-bold text-slate-900">{{ itemTitle || `Item #${props.itemId}` }}</p>
      </div>

      <div v-else>
        <AppInput
          id="custody-item-id"
          label="Item ID"
          type="number"
          placeholder="Enter Found Item ID (e.g. 1)"
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
        label="Item Condition on Transfer"
        placeholder="e.g. Good condition, minor scratches"
        :model-value="form.condition"
        @update:model-value="form.condition = $event"
      />

      <AppTextarea
        label="Transfer Notes / Shelf Details"
        placeholder="e.g. Moved to shelf B3"
        :model-value="form.notes"
        :rows="2"
        @update:model-value="form.notes = $event"
      />

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
        <AppButton variant="outline" size="sm" type="button" @click="$emit('close')">
          Cancel
        </AppButton>
        <AppButton variant="primary" size="sm" type="submit" :loading="loading">
          Record Transfer
        </AppButton>
      </div>
    </form>
  </AppModal>
</template>
