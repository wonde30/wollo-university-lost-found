<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useCustodyStore } from '../stores/custody.store'
import AppSelect from '@/components/ui/AppSelect.vue'

interface Props {
  modelValue?: number | string | null
  label?: string
  error?: string | null
  required?: boolean
}

withDefaults(defineProps<Props>(), {
  modelValue: '',
  label: 'Storage Location',
  error: null,
  required: false,
})

defineEmits<{
  (e: 'update:modelValue', value: number | string): void
}>()

const custodyStore = useCustodyStore()
const { storageLocations, storageLocationsLoading: loading } = storeToRefs(custodyStore)

// Only fetch if not already loaded — the store guards duplicate requests.
// Any number of StorageLocationSelect instances on the page share one fetch.
if (!custodyStore.storageLocationsInitialized) {
  custodyStore.fetchStorageLocations()
}
</script>

<template>
  <AppSelect
    :label="label"
    :placeholder="loading ? 'Loading locations...' : 'Select storage location'"
    :options="storageLocations.map(loc => ({
      label: `${loc.name} (${loc.building || 'Main'} - ${loc.room_number || ''})`,
      value: loc.id,
    }))"
    :model-value="modelValue || ''"
    :error="error"
    :required="required"
    @update:model-value="$emit('update:modelValue', $event ? Number($event) : '')"
  />
</template>
