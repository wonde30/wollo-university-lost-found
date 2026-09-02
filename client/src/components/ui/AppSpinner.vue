<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  size?: 'sm' | 'md' | 'lg' | 'xl'
  color?: 'primary' | 'gold' | 'white' | 'current'
  label?: string
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  color: 'primary',
  label: '',
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm': return 'h-4 w-4 border-2'
    case 'lg': return 'h-8 w-8 border-3'
    case 'xl': return 'h-12 w-12 border-4'
    case 'md':
    default: return 'h-6 w-6 border-2'
  }
})

const colorClasses = computed(() => {
  switch (props.color) {
    case 'gold': return 'border-amber-200 border-t-amber-500'
    case 'white': return 'border-white/30 border-t-white'
    case 'current': return 'border-current/30 border-t-current'
    case 'primary':
    default: return 'border-[#0B5D3B]/20 border-t-[#0B5D3B]'
  }
})
</script>

<template>
  <div class="inline-flex flex-col items-center justify-center gap-2">
    <div
      :class="[
        'rounded-full animate-spin',
        sizeClasses,
        colorClasses,
      ]"
    />
    <span v-if="label" class="text-xs text-slate-500 font-medium">{{ label }}</span>
  </div>
</template>
