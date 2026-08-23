<script setup lang="ts">
import { computed } from 'vue'

import type { TabItem } from './types'
export type { TabItem }

interface Props {
  tabs: TabItem[]
  modelValue: string | number
  variant?: 'underline' | 'pills' | 'bordered'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'underline',
})

const emit = defineEmits<{
  (e: 'update:modelValue', id: string | number): void
  (e: 'change', id: string | number): void
}>()

function selectTab(tab: TabItem) {
  if (tab.disabled) return
  emit('update:modelValue', tab.id)
  emit('change', tab.id)
}

const variantNavClasses = computed(() => {
  switch (props.variant) {
    case 'pills':
      return 'p-1 bg-slate-100/90 rounded-xl gap-1'
    case 'bordered':
      return 'border border-slate-200 rounded-xl p-1 gap-1 bg-white'
    case 'underline':
    default:
      return 'border-b border-slate-200 gap-6'
  }
})
</script>

<template>
  <div class="w-full">
    <!-- Tabs Header -->
    <div :class="['flex items-center select-none overflow-x-auto overflow-y-hidden no-scrollbar', variantNavClasses]">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        :disabled="tab.disabled"
        :class="[
          'inline-flex items-center gap-2 text-xs font-semibold transition-all cursor-pointer whitespace-nowrap',
          variant === 'underline'
            ? [
                'pb-3 pt-1 border-b-2 -mb-px',
                modelValue === tab.id
                  ? 'border-[#0F5132] text-[#0F5132]'
                  : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300',
              ]
            : [
                'px-3.5 py-1.5 rounded-lg',
                modelValue === tab.id
                  ? 'bg-white text-slate-900 shadow-xs'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-white/50',
              ],
          tab.disabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : '',
        ]"
        @click="selectTab(tab)"
      >
        <slot :name="`tab-icon-${tab.id}`">
          <!-- Fallback generic icon slot if passed as prop -->
        </slot>
        <span>{{ tab.label }}</span>
        <span
          v-if="tab.badge !== undefined"
          :class="[
            'px-1.5 py-0.2 rounded-full text-[10px] font-bold',
            modelValue === tab.id ? 'bg-emerald-100 text-[#0F5132]' : 'bg-slate-200 text-slate-700',
          ]"
        >
          {{ tab.badge }}
        </span>
      </button>
    </div>

    <!-- Active Tab Content -->
    <div class="mt-4">
      <slot :active-tab="modelValue" />
    </div>
  </div>
</template>
