<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'primary' | 'secondary' | 'outline' | 'outline-white' | 'danger' | 'ghost' | 'gold' | 'link'
  size?: 'xs' | 'sm' | 'md' | 'lg'
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
  loading?: boolean
  block?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'primary',
  size: 'md',
  type: 'button',
  disabled: false,
  loading: false,
  block: false,
})

const emit = defineEmits<{
  (e: 'click', event: MouseEvent): void
}>()

function handleClick(event: MouseEvent) {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'primary':
      return 'bg-[#0B5D3B] hover:bg-[#084C30] active:bg-[#063D27] text-white shadow-xs hover:shadow focus:ring-2 focus:ring-[#0B5D3B]/30 border border-transparent active:scale-[0.99]'
    case 'gold':
      return 'bg-[#B7791F] hover:bg-[#996515] active:bg-[#7b4f10] text-white shadow-xs focus:ring-2 focus:ring-[#B7791F]/30 border border-transparent active:scale-[0.99]'
    case 'secondary':
      return 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-slate-300 dark:focus:ring-slate-700 border border-slate-200 dark:border-slate-700 active:scale-[0.99]'
    case 'outline':
      return 'border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 shadow-2xs focus:ring-2 focus:ring-[#0B5D3B]/20 active:scale-[0.99]'
    case 'outline-white':
      return 'border border-white/40 bg-white/10 hover:bg-white/20 text-white backdrop-blur-xs focus:ring-2 focus:ring-white/30 active:scale-[0.99]'
    case 'danger':
      return 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-xs focus:ring-2 focus:ring-rose-500/30 border border-transparent active:scale-[0.99]'
    case 'ghost':
      return 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white border border-transparent active:scale-[0.99]'
    case 'link':
      return 'text-[#0B5D3B] dark:text-[#3e9e70] hover:text-[#084C30] dark:hover:text-[#75bd97] underline-offset-4 hover:underline p-0 border-transparent bg-transparent shadow-none'
    default:
      return 'bg-[#0B5D3B] text-white'
  }
})

const sizeClasses = computed(() => {
  if (props.variant === 'link') return 'text-sm font-bold'
  switch (props.size) {
    case 'xs':
      return 'px-2.5 py-1 text-xs font-bold rounded-lg gap-1.5'
    case 'sm':
      return 'px-3 py-1.5 text-xs font-bold rounded-lg gap-1.5'
    case 'lg':
      return 'px-5 py-2.5 text-sm sm:text-base font-extrabold rounded-xl gap-2.5'
    case 'md':
    default:
      return 'px-4 py-2 text-xs sm:text-sm font-bold rounded-xl gap-2'
  }
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center font-bold tracking-tight transition-all duration-150 outline-none select-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none',
      variantClasses,
      sizeClasses,
      block ? 'w-full' : '',
    ]"
    @click="handleClick"
  >
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4 text-current"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
    </svg>

    <slot v-if="!loading" name="icon-left">
      <slot v-if="!loading" name="prefix" />
    </slot>
    <slot />
    <slot name="icon-right">
      <slot name="suffix" />
    </slot>
  </button>
</template>
