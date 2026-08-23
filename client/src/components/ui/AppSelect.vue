<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, useId } from 'vue'

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
  placeholder: 'Select an option',
  error: null,
  hint: '',
  disabled: false,
  required: false,
  id: undefined,
  name: '',
  searchable: false,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void
  (e: 'blur', event: FocusEvent): void
  (e: 'focus', event: FocusEvent): void
}>()

const selectId = props.id ?? useId()
const isOpen = ref(false)
const searchFilter = ref('')
const containerRef = ref<HTMLElement | null>(null)

const normalizedOptions = computed(() => {
  return props.options.map(opt => {
    if ('value' in opt) {
      return { label: opt.label, value: opt.value, disabled: opt.disabled ?? false }
    }
    return { label: opt.name, value: opt.id, disabled: false }
  })
})

const filteredOptions = computed(() => {
  if (!props.searchable || !searchFilter.value.trim()) {
    return normalizedOptions.value
  }
  const q = searchFilter.value.toLowerCase()
  return normalizedOptions.value.filter(opt => opt.label.toLowerCase().includes(q))
})

const selectedOption = computed(() => {
  return normalizedOptions.value.find(opt => String(opt.value) === String(props.modelValue))
})

function selectOption(value: string | number, disabled = false) {
  if (disabled) return
  emit('update:modelValue', value)
  isOpen.value = false
  searchFilter.value = ''
}

function handleClickOutside(event: MouseEvent) {
  if (containerRef.value && !containerRef.value.contains(event.target as Node)) {
    isOpen.value = false
  }
}

onMounted(() => {
  if (typeof document !== 'undefined') {
    document.addEventListener('click', handleClickOutside)
  }
})

onUnmounted(() => {
  if (typeof document !== 'undefined') {
    document.removeEventListener('click', handleClickOutside)
  }
})
</script>

<template>
  <div ref="containerRef" class="w-full relative">
    <label
      v-if="label"
      :for="selectId"
      class="block text-xs font-semibold text-slate-700 mb-1.5 select-none"
    >
      {{ label }}
      <span v-if="required" class="text-rose-500 font-bold">*</span>
    </label>

    <!-- Custom Select Trigger Button -->
    <div
      :id="selectId"
      tabindex="0"
      :class="[
        'w-full flex items-center justify-between rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-900 transition-all outline-none cursor-pointer select-none',
        'focus:ring-2 focus:border-transparent',
        error
          ? 'border-rose-400 focus:ring-rose-500/20 focus:border-rose-500 bg-rose-50/10'
          : 'border-slate-300 focus:border-[#0F5132] focus:ring-[#0F5132]/20',
        disabled ? 'bg-slate-50 text-slate-400 cursor-not-allowed border-slate-200 pointer-events-none' : '',
        isOpen ? 'ring-2 border-transparent border-[#0F5132] ring-[#0F5132]/20' : '',
      ]"
      @click="!disabled && (isOpen = !isOpen)"
      @keydown.esc="isOpen = false"
      @keydown.enter.prevent="!disabled && (isOpen = !isOpen)"
    >
      <span v-if="selectedOption" class="font-medium text-slate-800 truncate">
        {{ selectedOption.label }}
      </span>
      <span v-else class="text-slate-400 truncate">
        {{ placeholder }}
      </span>

      <div class="pointer-events-none flex items-center pl-2 text-slate-400 transition-transform duration-200" :class="isOpen ? 'rotate-180 text-[#0F5132]' : ''">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>

    <!-- Dropdown Menu -->
    <Transition name="dropdown">
      <div
        v-if="isOpen && !disabled"
        class="absolute z-50 mt-1.5 w-full rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl ring-1 ring-black/5 max-h-60 overflow-y-auto"
      >
        <!-- Search filter if enabled -->
        <div v-if="searchable" class="p-1 mb-1 border-b border-slate-100">
          <input
            v-model="searchFilter"
            type="text"
            placeholder="Search..."
            class="w-full rounded-lg bg-slate-50 px-2.5 py-1.5 text-xs text-slate-800 outline-none focus:bg-white focus:ring-1 focus:ring-[#0F5132]"
            @click.stop
          />
        </div>

        <div v-if="filteredOptions.length === 0" class="p-3 text-center text-xs text-slate-400">
          No options found
        </div>

        <div
          v-for="opt in filteredOptions"
          :key="opt.value"
          :class="[
            'flex items-center justify-between px-3 py-2 text-xs rounded-lg cursor-pointer transition-colors select-none',
            String(opt.value) === String(modelValue) ? 'bg-[#0F5132] text-white font-semibold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900',
            opt.disabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : '',
          ]"
          @click="selectOption(opt.value, opt.disabled)"
        >
          <span class="truncate">{{ opt.label }}</span>
          <svg
            v-if="String(opt.value) === String(modelValue)"
            class="h-4 w-4 shrink-0 text-white"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
      </div>
    </Transition>

    <p v-if="error" class="mt-1.5 text-xs text-rose-600 font-medium">
      {{ error }}
    </p>
    <p v-else-if="hint" class="mt-1.5 text-xs text-slate-500">
      {{ hint }}
    </p>
  </div>
</template>
