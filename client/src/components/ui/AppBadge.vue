<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'default' | 'primary' | 'success' | 'warning' | 'danger' | 'info' | 'purple' | 'orange' | 'gold'
  size?: 'sm' | 'md'
  dot?: boolean
  removable?: boolean
  pill?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'sm',
  dot: false,
  removable: false,
  pill: true,
})

const emit = defineEmits<{
  (e: 'remove'): void
}>()

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'primary':
      return 'bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] border-[#0B5D3B]/30 dark:border-[#0B5D3B]/50 font-bold'
    case 'gold':
      return 'bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border-amber-300 dark:border-amber-700 font-bold'
    case 'success':
      return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800 font-bold'
    case 'warning':
      return 'bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border-amber-200 dark:border-amber-800 font-bold'
    case 'danger':
      return 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800 font-bold'
    case 'info':
      return 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800 font-bold'
    case 'purple':
      return 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800 font-bold'
    case 'orange':
      return 'bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-800 font-bold'
    case 'default':
    default:
      return 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border-slate-200 dark:border-slate-700 font-bold'
  }
})

const dotColor = computed(() => {
  switch (props.variant) {
    case 'primary':
      return 'bg-[#0B5D3B]'
    case 'gold':
      return 'bg-amber-600'
    case 'success':
      return 'bg-emerald-500'
    case 'warning':
      return 'bg-amber-500'
    case 'danger':
      return 'bg-rose-500'
    case 'info':
      return 'bg-sky-500'
    case 'purple':
      return 'bg-purple-500'
    case 'orange':
      return 'bg-orange-500'
    default:
      return 'bg-slate-400'
  }
})

const sizeClasses = computed(() => {
  return props.size === 'md'
    ? 'px-2.5 py-1 text-xs gap-1.5'
    : 'px-2 py-0.5 text-[11px] gap-1'
})
</script>

<template>
  <span
    :class="[
      'inline-flex items-center font-bold tracking-wide border select-none transition-colors leading-none',
      pill ? 'rounded-full' : 'rounded-md',
      variantClasses,
      sizeClasses,
    ]"
  >
    <span
      v-if="dot"
      :class="['h-1.5 w-1.5 rounded-full shrink-0', dotColor]"
    />
    <slot />
    <button
      v-if="removable"
      type="button"
      class="ml-0.5 hover:opacity-70 focus:outline-none cursor-pointer"
      @click.stop="emit('remove')"
    >
      <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </span>
</template>
