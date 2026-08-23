<script setup lang="ts">
interface Props {
  label: string
  value: number | string
  icon?: string
  variant?: 'primary' | 'success' | 'warning' | 'danger' | 'info'
  sublabel?: string
}

withDefaults(defineProps<Props>(), {
  variant: 'primary',
  icon: undefined,
  sublabel: undefined,
})

const variantColors: Record<string, string> = {
  primary: 'from-[#0F5132] to-[#0a3d22]',
  success: 'from-emerald-500 to-emerald-700',
  warning: 'from-amber-500 to-amber-700',
  danger: 'from-rose-500 to-rose-700',
  info: 'from-sky-500 to-sky-700',
}
</script>

<template>
  <div
    class="relative overflow-hidden rounded-2xl p-5 text-white shadow-md"
    :class="`bg-gradient-to-br ${variantColors[variant]}`"
  >
    <!-- Background decoration -->
    <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10" />
    <div class="absolute -right-2 -bottom-4 h-14 w-14 rounded-full bg-white/5" />

    <div class="relative z-10 flex items-start justify-between gap-2">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-white/70 mb-1">{{ label }}</p>
        <p class="text-3xl font-bold text-white tracking-tight">{{ value }}</p>
        <p v-if="sublabel" class="text-xs text-white/60 mt-1">{{ sublabel }}</p>
      </div>

      <div v-if="icon" class="h-12 w-12 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
        <span class="text-2xl" aria-hidden="true">{{ icon }}</span>
      </div>
    </div>
  </div>
</template>
