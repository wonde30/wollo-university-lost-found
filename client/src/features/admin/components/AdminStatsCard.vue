<script setup lang="ts">
import type { Component } from 'vue'
import { RouterLink } from 'vue-router'
import { TrendingUp, TrendingDown, Minus, ArrowUpRight } from 'lucide-vue-next'

interface Trend {
  value: number | string
  direction?: 'up' | 'down' | 'neutral'
  label?: string
}

interface Props {
  label: string
  value: number | string
  icon?: Component | string
  variant?: 'primary' | 'success' | 'warning' | 'danger' | 'info' | 'gold'
  sublabel?: string
  trend?: Trend
  to?: string
}

withDefaults(defineProps<Props>(), {
  variant: 'primary',
  icon: undefined,
  sublabel: undefined,
  trend: undefined,
  to: undefined,
})

const variantStyles: Record<string, { iconBg: string; iconText: string; ring: string }> = {
  primary: {
    iconBg: 'bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97]',
    iconText: 'text-[#0B5D3B] dark:text-[#75bd97]',
    ring: 'hover:border-[#0B5D3B] dark:hover:border-[#75bd97]',
  },
  gold: {
    iconBg: 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400',
    iconText: 'text-amber-700 dark:text-amber-400',
    ring: 'hover:border-amber-400 dark:hover:border-amber-600',
  },
  success: {
    iconBg: 'bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97]',
    iconText: 'text-[#0B5D3B] dark:text-[#75bd97]',
    ring: 'hover:border-[#0B5D3B] dark:hover:border-[#75bd97]',
  },
  warning: {
    iconBg: 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400',
    iconText: 'text-amber-700 dark:text-amber-400',
    ring: 'hover:border-amber-400 dark:hover:border-amber-600',
  },
  danger: {
    iconBg: 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400',
    iconText: 'text-rose-700 dark:text-rose-400',
    ring: 'hover:border-rose-400 dark:hover:border-rose-600',
  },
  info: {
    iconBg: 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-400',
    iconText: 'text-sky-700 dark:text-sky-400',
    ring: 'hover:border-sky-400 dark:hover:border-sky-600',
  },
}
</script>

<template>
  <component
    :is="to ? RouterLink : 'div'"
    :to="to"
    class="relative rounded-xl p-3.5 sm:p-4 bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs transition-all duration-150 block group"
    :class="[
      to ? 'cursor-pointer hover:shadow-md hover:-translate-y-0.5 ' + variantStyles[variant].ring : '',
    ]"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0 flex-1">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1 leading-tight">
          {{ label }}
        </p>
        <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none mt-1">
          {{ value }}
        </p>

        <!-- Trend or Sublabel -->
        <div v-if="trend" class="flex items-center gap-1.5 mt-2.5 text-xs font-semibold">
          <span
            class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md text-[10px] font-bold"
            :class="trend.direction === 'down' ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400' : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400'"
          >
            <TrendingUp v-if="trend.direction === 'up'" class="h-3 w-3" />
            <TrendingDown v-else-if="trend.direction === 'down'" class="h-3 w-3" />
            <Minus v-else class="h-3 w-3" />
            {{ trend.value }}
          </span>
          <span v-if="trend.label" class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ trend.label }}</span>
        </div>
        <p v-else-if="sublabel" class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 truncate">{{ sublabel }}</p>
      </div>

      <div class="flex flex-col items-end justify-between h-full shrink-0">
        <div
          v-if="icon"
          class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl flex items-center justify-center transition-transform group-hover:scale-105"
          :class="variantStyles[variant].iconBg"
        >
          <component :is="icon" v-if="typeof icon !== 'string'" class="h-5 w-5" />
          <span v-else class="text-lg" aria-hidden="true">{{ icon }}</span>
        </div>

        <div v-if="to" class="mt-3 text-slate-300 dark:text-slate-600 group-hover:text-slate-700 dark:group-hover:text-slate-200 transition-colors dark:hover:text-slate-200">
          <ArrowUpRight class="h-3.5 w-3.5" />
        </div>
      </div>
    </div>
  </component>
</template>
