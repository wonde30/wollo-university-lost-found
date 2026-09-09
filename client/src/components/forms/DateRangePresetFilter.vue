<script setup lang="ts">
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
  options: () => [
    { label: 'Today', value: 'today' },
    { label: '7 Days', value: '7d' },
    { label: '30 Days', value: '30d' },
    { label: '90 Days', value: '90d' },
    { label: '12 Months', value: '12m' },
    { label: 'All Time', value: 'all' },
  ],
  disabled: false,
  size: 'md',
})

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
    aria-label="Time period selector"
  >
    <button
      v-for="opt in options"
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
