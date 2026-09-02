<script setup lang="ts">
import { ref, computed, useId } from 'vue'

interface Props {
  modelValue?: string | number | null
  type?: string
  placeholder?: string
  label?: string
  error?: string | null
  hint?: string
  disabled?: boolean
  readonly?: boolean
  required?: boolean
  id?: string
  name?: string
  autocomplete?: string
  maxlength?: number
  clearable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  type: 'text',
  placeholder: '',
  label: '',
  error: null,
  hint: '',
  disabled: false,
  readonly: false,
  required: false,
  id: undefined,
  name: '',
  autocomplete: 'off',
  maxlength: undefined,
  clearable: false,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'blur', event: FocusEvent): void
  (e: 'focus', event: FocusEvent): void
  (e: 'clear'): void
}>()

const inputId = props.id ?? useId()
const showPassword = ref(false)

const computedType = computed(() => {
  if (props.type === 'password') {
    return showPassword.value ? 'text' : 'password'
  }
  return props.type
})

const charCount = computed(() => {
  return String(props.modelValue ?? '').length
})

function handleClear() {
  emit('update:modelValue', '')
  emit('clear')
}
</script>

<template>
  <div class="w-full">
    <div v-if="label || maxlength" class="flex items-center justify-between mb-1.5">
      <label
        v-if="label"
        :for="inputId"
        class="block text-xs font-bold text-slate-800 dark:text-slate-200 select-none"
      >
        {{ label }}
        <span v-if="required" class="text-rose-500 font-extrabold">*</span>
      </label>

      <span v-if="maxlength" class="text-[11px] text-slate-400 dark:text-slate-500 font-mono">
        {{ charCount }}/{{ maxlength }}
      </span>
    </div>

    <div class="relative rounded-xl">
      <!-- Left icon / slot -->
      <div v-if="$slots.prefix || $slots['icon-left']" class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
        <slot name="icon-left">
          <slot name="prefix" />
        </slot>
      </div>

      <input
        :id="inputId"
        :name="props.name || inputId"
        :type="computedType"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :maxlength="maxlength"
        :autocomplete="autocomplete"
        :class="[
          'w-full rounded-xl border bg-white dark:bg-[#111827] px-3.5 py-2 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none',
          'focus:ring-2 focus:border-transparent',
          ($slots.prefix || $slots['icon-left']) ? 'pl-10' : '',
          $slots.suffix || type === 'password' || (clearable && modelValue) ? 'pr-10' : '',
          error
            ? 'border-rose-400 dark:border-rose-500 focus:ring-rose-500/20 focus:border-rose-500 bg-rose-50/10 dark:bg-rose-950/20'
            : 'border-slate-300 dark:border-slate-700 focus:border-[#0B5D3B] dark:focus:border-[#3e9e70] focus:ring-[#0B5D3B]/20 dark:focus:ring-[#3e9e70]/20',
          disabled ? 'bg-slate-50 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed border-slate-200 dark:border-slate-800' : '',
          readonly ? 'bg-slate-50/75 dark:bg-slate-800/50' : '',
        ]"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />

      <!-- Right Actions: Clear button / Password toggle / Suffix slot -->
      <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-1.5 text-slate-400 dark:text-slate-500">
        <!-- Clearable button -->
        <button
          v-if="clearable && modelValue && !disabled && !readonly"
          type="button"
          tabindex="-1"
          class="p-1 rounded-md hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors dark:text-slate-400"
          @click="handleClear"
        >
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Password toggle -->
        <button
          v-if="type === 'password' && !disabled"
          type="button"
          tabindex="-1"
          class="p-1 rounded-md hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors dark:text-slate-400"
          @click="showPassword = !showPassword"
        >
          <svg v-if="!showPassword" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
          </svg>
        </button>

        <slot name="suffix" />
      </div>
    </div>

    <!-- Error / Hint -->
    <p v-if="error" class="mt-1.5 text-xs text-rose-600 dark:text-rose-400 font-medium flex items-center gap-1">
      <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <circle cx="12" cy="12" r="10" stroke-width="2" />
        <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
        <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
      </svg>
      {{ error }}
    </p>
    <p v-else-if="hint" class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
      {{ hint }}
    </p>
  </div>
</template>
