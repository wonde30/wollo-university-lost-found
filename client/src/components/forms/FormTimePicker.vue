<script setup lang="ts">
import AppInput from '@/components/ui/AppInput.vue'

interface Props {
  modelValue?: string | null
  label?: string
  error?: string | null
  required?: boolean
  disabled?: boolean
}

withDefaults(defineProps<Props>(), {
  modelValue: '',
  label: 'Time',
  error: null,
  required: false,
  disabled: false,
})

defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()
</script>

<template>
  <AppInput
    type="time"
    :model-value="modelValue || ''"
    :label="label"
    :error="error"
    :required="required"
    :disabled="disabled"
    @update:model-value="$emit('update:modelValue', $event)"
  />
</template>
