<script setup lang="ts">
import { useId, computed } from 'vue'

interface Props {
  modelValue?: string | null
  placeholder?: string
  label?: string
  error?: string | null
  hint?: string
  rows?: number
  disabled?: boolean
  readonly?: boolean
  required?: boolean
  id?: string
  name?: string
  maxlength?: number
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: '',
  label: '',
  error: null,
  hint: '',
  rows: 4,
  disabled: false,
  readonly: false,
  required: false,
  id: undefined,
  name: '',
  maxlength: undefined,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'blur', event: FocusEvent): void
  (e: 'focus', event: FocusEvent): void
}>()

const textareaId = props.id ?? useId()

const charCount = computed(() => {
  return String(props.modelValue ?? '').length
})
</script>

<template>
  <div class="w-full">
    <div v-if="label || maxlength" class="flex items-center justify-between mb-1.5">
      <label
        v-if="label"
        :for="textareaId"
        class="block text-xs font-semibold text-slate-700 dark:text-slate-300 select-none"
      >
        {{ label }}
        <span v-if="required" class="text-rose-500 font-bold">*</span>
      </label>

      <span v-if="maxlength" class="text-[11px] text-slate-400 dark:text-slate-500 font-mono">
        {{ charCount }}/{{ maxlength }}
      </span>
    </div>

    <textarea
      :id="textareaId"
      :name="name || textareaId"
      :rows="rows"
      :value="modelValue ?? ''"
      :placeholder="placeholder"
      :disabled="disabled"
      :readonly="readonly"
      :required="required"
      :maxlength="maxlength"
      :class="[
        'w-full rounded-xl border bg-white dark:bg-[#111827] px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none resize-y',
        'focus:ring-2 focus:border-transparent',
        error
          ? 'border-rose-400 dark:border-rose-500 focus:ring-rose-500/20 focus:border-rose-500 bg-rose-50/10 dark:bg-rose-950/20'
          : 'border-slate-300 dark:border-slate-700 focus:border-[#0B5D3B] dark:focus:border-[#75bd97] focus:ring-[#0B5D3B]/20 dark:focus:ring-[#75bd97]/20',
        disabled ? 'bg-slate-50 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed border-slate-200 dark:border-slate-800' : '',
        readonly ? 'bg-slate-50/75 dark:bg-slate-800/50' : '',
      ]"
      @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
      @blur="emit('blur', $event)"
      @focus="emit('focus', $event)"
    />

    <p v-if="error" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-medium">
      {{ error }}
    </p>
    <p v-else-if="hint" class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
      {{ hint }}
    </p>
  </div>
</template>
