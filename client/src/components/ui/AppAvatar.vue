<script setup lang="ts">
import { computed, ref, watch } from 'vue'

interface Props {
  src?: string | null
  name?: string
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl'
  shape?: 'circle' | 'square'
  status?: 'online' | 'offline' | 'away'
}

const props = withDefaults(defineProps<Props>(), {
  src: null,
  name: 'User',
  size: 'md',
  shape: 'circle',
  status: undefined,
})

const hasImageError = ref(false)

watch(() => props.src, () => {
  hasImageError.value = false
})

const initials = computed(() => {
  if (!props.name) return 'U'
  const parts = props.name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return props.name.slice(0, 2).toUpperCase()
})

// Deterministic background colors based on name string hash
const bgColor = computed(() => {
  const colors = [
    'bg-[#0B5D3B] text-white',
    'bg-[#B7791F] text-white',
    'bg-slate-800 text-white',
    'bg-emerald-700 text-white',
    'bg-teal-700 text-white',
    'bg-indigo-700 text-white',
    'bg-rose-700 text-white',
  ]
  let hash = 0
  const name = props.name || 'User'
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return colors[Math.abs(hash) % colors.length]
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'xs':
      return 'h-6 w-6 text-[10px]'
    case 'sm':
      return 'h-8 w-8 text-xs'
    case 'lg':
      return 'h-12 w-12 text-base'
    case 'xl':
      return 'h-16 w-16 text-lg'
    case '2xl':
      return 'h-24 w-24 text-2xl'
    case 'md':
    default:
      return 'h-10 w-10 text-sm'
  }
})

const statusDotColor = computed(() => {
  switch (props.status) {
    case 'online':
      return 'bg-emerald-500 ring-white'
    case 'away':
      return 'bg-amber-500 ring-white'
    case 'offline':
    default:
      return 'bg-slate-400 ring-white'
  }
})
</script>

<template>
  <div class="relative inline-flex shrink-0 select-none">
    <img
      v-if="src && !hasImageError"
      :src="src"
      :alt="name"
      :class="[
        'object-cover border border-slate-200/80 shadow-xs',
        shape === 'circle' ? 'rounded-full' : 'rounded-xl',
        sizeClasses,
      ]"
      @error="hasImageError = true"
    />
    <div
      v-else
      :class="[
        'flex items-center justify-center font-bold tracking-tight shadow-xs border border-white/20',
        shape === 'circle' ? 'rounded-full' : 'rounded-xl',
        bgColor,
        sizeClasses,
      ]"
    >
      {{ initials }}
    </div>

    <!-- Status Indicator Dot -->
    <span
      v-if="status"
      :class="[
        'absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full ring-2',
        statusDotColor,
      ]"
    />
  </div>
</template>
