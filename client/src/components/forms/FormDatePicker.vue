<script setup lang="ts">
import AppInput from '@/components/ui/AppInput.vue'

interface Props {
  modelValue?: string | null
  label?: string
  error?: string | null
  required?: boolean
  disabled?: boolean
  max?: string
  min?: string
}

withDefaults(defineProps<Props>(), {
  modelValue: '',
  label: 'Date',
  error: null,
  required: false,
  disabled: false,
  max: '',
  min: '',
})

defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()
</script>

<template>
  <AppInput
    type="date"
    :model-value="modelValue || ''"
    :label="label"
    :error="error"
    :required="required"
    :disabled="disabled"
    :max="max"
    :min="min"
    @update:model-value="$emit('update:modelValue', $event)"
  />
</template>
