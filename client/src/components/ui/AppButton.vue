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
      return 'bg-[#0F5132] hover:bg-[#0B3822] text-white shadow-sm hover:shadow focus:ring-2 focus:ring-[#0F5132]/30 active:scale-[0.99] border border-transparent'
    case 'gold':
      return 'bg-gradient-to-r from-[#D4AF37] to-[#B7791F] hover:from-[#c69500] hover:to-[#997404] text-white shadow-sm hover:shadow-gold focus:ring-2 focus:ring-[#D4AF37]/40 active:scale-[0.99] border border-transparent'
    case 'secondary':
      return 'bg-slate-100 hover:bg-slate-200 text-slate-800 focus:ring-2 focus:ring-slate-300 border border-slate-200/70'
    case 'outline':
      return 'border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 shadow-xs focus:ring-2 focus:ring-slate-200'
    case 'outline-white':
      return 'border border-white/60 bg-white/10 hover:bg-white/20 text-white backdrop-blur-sm focus:ring-2 focus:ring-white/30'
    case 'danger':
      return 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm focus:ring-2 focus:ring-rose-500/30 border border-transparent active:scale-[0.99]'
    case 'ghost':
      return 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent'
    case 'link':
      return 'text-[#0F5132] hover:text-[#0B3822] underline-offset-4 hover:underline p-0 border-transparent bg-transparent shadow-none'
    default:
      return 'bg-[#0F5132] text-white'
  }
})

const sizeClasses = computed(() => {
  if (props.variant === 'link') return 'text-sm font-medium'
  switch (props.size) {
    case 'xs':
      return 'px-2.5 py-1 text-xs font-medium rounded-md gap-1.5'
    case 'sm':
      return 'px-3 py-1.5 text-xs font-medium rounded-lg gap-2'
    case 'lg':
      return 'px-5 py-3 text-base font-semibold rounded-xl gap-2.5'
    case 'md':
    default:
      return 'px-4 py-2.5 text-sm font-medium rounded-lg gap-2'
  }
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center font-medium transition-all duration-150 outline-none select-none cursor-pointer disabled:opacity-55 disabled:cursor-not-allowed disabled:pointer-events-none',
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

    <slot v-if="!loading" name="icon-left" />
    <slot />
    <slot name="icon-right" />
  </button>
</template>
