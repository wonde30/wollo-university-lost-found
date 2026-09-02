<script setup lang="ts">
import { useId, ref, watch, onMounted } from 'vue'

interface Props {
  modelValue?: boolean
  label?: string
  description?: string
  disabled?: boolean
  error?: string
  indeterminate?: boolean
  id?: string
  name?: string
  labelPosition?: 'left' | 'right'
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  label: '',
  description: '',
  disabled: false,
  error: '',
  indeterminate: false,
  id: undefined,
  name: '',
  labelPosition: 'right',
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'change', value: boolean): void
}>()

const inputId = props.id ?? useId()
const inputRef = ref<HTMLInputElement | null>(null)

function updateIndeterminate() {
  if (inputRef.value) {
    inputRef.value.indeterminate = props.indeterminate
  }
}

watch(() => props.indeterminate, updateIndeterminate)
onMounted(updateIndeterminate)

function handleChange(event: Event) {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.checked)
  emit('change', target.checked)
}
</script>

<template>
  <div class="flex flex-col">
    <label
      :for="inputId"
      :class="[
        'inline-flex items-start gap-2.5 cursor-pointer select-none',
        labelPosition === 'left' ? 'flex-row-reverse justify-between' : '',
        disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : '',
      ]"
    >
      <div class="relative flex items-center pt-0.5">
        <input
          :id="inputId"
          ref="inputRef"
          type="checkbox"
          :name="name || inputId"
          :checked="modelValue"
          :disabled="disabled"
          :class="[
            'h-4 w-4 rounded-md border text-[#0B5D3B] dark:text-[#3e9e70] transition-colors focus:ring-2 focus:ring-[#0B5D3B]/30 dark:focus:ring-[#3e9e70]/30 focus:ring-offset-0 cursor-pointer',
            error ? 'border-rose-400 dark:border-rose-500 bg-rose-50/20 dark:bg-rose-950/20' : 'border-slate-300 dark:border-slate-700 bg-white dark:bg-[#111827] hover:border-[#0B5D3B] dark:hover:border-[#3e9e70]',
          ]"
          @change="handleChange"
        />
      </div>

      <div v-if="label || description" class="text-xs">
        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ label }}</span>
        <p v-if="description" class="text-slate-500 dark:text-slate-400 mt-0.5">{{ description }}</p>
      </div>
    </label>

    <p v-if="error" class="mt-1 text-xs text-rose-600 dark:text-rose-400 font-medium">
      {{ error }}
    </p>
  </div>
</template>
