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
      return 'bg-emerald-50 text-[#0F5132] border-emerald-200/80 font-bold'
    case 'gold':
      return 'bg-amber-50 text-amber-900 border-amber-300 font-bold'
    case 'success':
      return 'bg-emerald-50 text-emerald-800 border-emerald-200'
    case 'warning':
      return 'bg-amber-50 text-amber-800 border-amber-200'
    case 'danger':
      return 'bg-rose-50 text-rose-700 border-rose-200'
    case 'info':
      return 'bg-sky-50 text-sky-700 border-sky-200'
    case 'purple':
      return 'bg-purple-50 text-purple-700 border-purple-200'
    case 'orange':
      return 'bg-orange-50 text-orange-700 border-orange-200'
    case 'default':
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200'
  }
})

const dotColor = computed(() => {
  switch (props.variant) {
    case 'primary':
      return 'bg-[#0F5132]'
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
      'inline-flex items-center font-semibold border select-none transition-colors leading-none',
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
