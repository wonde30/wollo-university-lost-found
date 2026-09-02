<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, useId } from 'vue'
import { t } from '@/i18n'

import type { SelectOption } from './types'
export type { SelectOption }

interface Props {
  modelValue?: string | number | null
  options?: (SelectOption | { name: string; id: number | string })[]
  label?: string
  placeholder?: string
  error?: string | null
  hint?: string
  disabled?: boolean
  required?: boolean
  id?: string
  name?: string
  searchable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  options: () => [],
  label: '',
  placeholder: undefined,
  error: null,
  hint: '',
  disabled: false,
  required: false,
  id: '',
  name: '',
  searchable: false,
})

const resolvedPlaceholder = computed(() => props.placeholder || t('common.selectOption'))

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void
  (e: 'change', value: string | number): void
}>()

const selectId = props.id || useId()
const isOpen = ref(false)
const selectRef = ref<HTMLDivElement | null>(null)
const searchFilter = ref('')

const normalizedOptions = computed<SelectOption[]>(() => {
  return (props.options || []).map(opt => {
    if ('name' in opt && 'id' in opt && !('label' in opt)) {
      return { label: opt.name, value: opt.id }
    }
    return opt as SelectOption
  })
})

const filteredOptions = computed(() => {
  if (!props.searchable || !searchFilter.value) return normalizedOptions.value
  const q = searchFilter.value.toLowerCase()
  return normalizedOptions.value.filter(opt =>
    opt.label.toLowerCase().includes(q)
  )
})

const selectedOption = computed(() => {
  return normalizedOptions.value.find(opt => String(opt.value) === String(props.modelValue))
})

function toggleDropdown() {
  if (props.disabled) return
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    searchFilter.value = ''
  }
}

function selectOption(option: SelectOption) {
  emit('update:modelValue', option.value)
  emit('change', option.value)
  isOpen.value = false
}

function handleClickOutside(event: MouseEvent) {
  if (selectRef.value && !selectRef.value.contains(event.target as Node)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div ref="selectRef" class="w-full space-y-1.5 relative">
    <label
      v-if="label"
      :for="selectId"
      class="block text-xs font-bold text-slate-800 dark:text-slate-200 select-none"
    >
      {{ label }}
      <span v-if="required" class="text-rose-500 font-extrabold ml-0.5">*</span>
    </label>

    <div class="relative">
      <button
        :id="selectId"
        type="button"
        :name="name"
        :disabled="disabled"
        :class="[
          'w-full flex items-center justify-between px-3.5 py-2 text-sm rounded-xl border bg-white dark:bg-[#111827] transition-all text-left select-none cursor-pointer',
          isOpen
            ? 'border-[#0B5D3B] ring-2 ring-[#0B5D3B]/20 dark:ring-[#3e9e70]/20'
            : error
              ? 'border-rose-400 dark:border-rose-500'
              : 'border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-600',
          disabled ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : '',
        ]"
        @click="toggleDropdown"
      >
        <span
          :class="[
            'truncate',
            selectedOption ? 'text-slate-900 dark:text-white font-semibold' : 'text-slate-400 dark:text-slate-500',
          ]"
        >
          {{ selectedOption ? selectedOption.label : resolvedPlaceholder }}
        </span>

        <svg
          class="h-4 w-4 text-slate-400 shrink-0 transition-transform duration-200"
          :class="isOpen ? 'rotate-180 text-[#0B5D3B] dark:text-[#3e9e70]' : ''"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
            clip-rule="evenodd"
          />
        </svg>
      </button>

      <!-- Dropdown Menu -->
      <div
        v-if="isOpen"
        class="absolute z-50 left-0 right-0 mt-1 max-h-60 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] p-1 shadow-lg animate-in fade-in zoom-in-95 duration-100"
      >
        <!-- Search filter if enabled -->
        <div v-if="searchable" class="p-1 mb-1 border-b border-slate-100 dark:border-slate-800">
          <input
            v-model="searchFilter"
            type="text"
            :placeholder="t('common.searchPlaceholder')"
            class="w-full rounded-lg bg-slate-50 dark:bg-slate-800 px-2.5 py-1.5 text-sm text-slate-800 dark:text-slate-200 outline-none focus:bg-white dark:focus:bg-slate-700 focus:ring-1 focus:ring-[#0B5D3B] dark:focus:ring-[#3e9e70]"
            @click.stop
          />
        </div>

        <div v-if="filteredOptions.length === 0" class="p-3 text-center text-sm text-slate-400 dark:text-slate-500">
          {{ t('common.noData') }}
        </div>

        <div
          v-for="opt in filteredOptions"
          :key="opt.value"
          :class="[
            'flex items-center justify-between px-3 py-2 text-sm rounded-lg cursor-pointer transition-colors select-none',
            String(opt.value) === String(modelValue)
              ? 'bg-[#0B5D3B] text-white font-semibold'
              : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
          ]"
          @click="selectOption(opt)"
        >
          <span class="truncate">{{ opt.label }}</span>
          <svg
            v-if="String(opt.value) === String(modelValue)"
            class="h-4 w-4 shrink-0"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
      </div>
    </div>

    <p v-if="error" class="text-xs text-rose-600 dark:text-rose-400 font-medium">
      {{ error }}
    </p>
    <p v-else-if="hint" class="text-[11px] text-slate-400 dark:text-slate-500">
      {{ hint }}
    </p>
  </div>
</template>
