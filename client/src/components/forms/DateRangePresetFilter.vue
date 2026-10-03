<script setup lang="ts">
import { computed } from 'vue'
import { t } from '@/i18n'

export interface PresetOption {
  label: string
  value: string
  sublabel?: string
}

interface Props {
  modelValue?: string
  options?: PresetOption[]
  disabled?: boolean
  size?: 'sm' | 'md'
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '90d',
  options: undefined,
  disabled: false,
  size: 'md',
})

const defaultOptions = computed<PresetOption[]>(() => [
  { label: t('common.today'), value: 'today' },
  { label: t('common.days7'), value: '7d' },
  { label: t('common.days30'), value: '30d' },
  { label: t('common.days90'), value: '90d' },
  { label: t('common.months12'), value: '12m' },
  { label: t('common.allTime'), value: 'all' },
])

const effectiveOptions = computed(() => props.options || defaultOptions.value)

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'change', value: string): void
}>()

function selectPreset(val: string) {
  if (props.disabled || props.modelValue === val) return
  emit('update:modelValue', val)
  emit('change', val)
}
</script>

<template>
  <div
    class="inline-flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/60 shadow-2xs select-none"
    role="radiogroup"
    :aria-label="t('common.timePeriodSelector')"
  >
    <button
      v-for="opt in effectiveOptions"
      :key="opt.value"
      type="button"
      role="radio"
      :aria-checked="modelValue === opt.value"
      :disabled="disabled"
      class="rounded-lg font-bold transition-all duration-150 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
      :class="[
        size === 'sm' ? 'px-2.5 py-1 text-[11px]' : 'px-3 py-1.5 text-xs',
        modelValue === opt.value
          ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs font-extrabold'
          : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-white/50 dark:hover:bg-slate-800/50',
      ]"
      @click="selectPreset(opt.value)"
    >
      {{ opt.label }}
    </button>
  </div>
</template>
