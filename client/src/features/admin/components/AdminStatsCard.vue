<script setup lang="ts">
import type { Component } from 'vue'
import { RouterLink } from 'vue-router'
import { TrendingUp, TrendingDown, Minus, ArrowUpRight, AlertCircle } from 'lucide-vue-next'
import BaseSparkline from '@/components/charts/BaseSparkline.vue'

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
  sparklineData?: number[]
  to?: string
  loading?: boolean
  error?: string | null
}

withDefaults(defineProps<Props>(), {
  variant: 'primary',
  icon: undefined,
  sublabel: undefined,
  trend: undefined,
  sparklineData: undefined,
  to: undefined,
  loading: false,
  error: null,
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
  <!-- Skeleton State -->
  <div
    v-if="loading"
    class="rounded-2xl p-3.5 bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs animate-pulse flex flex-col justify-between"
    style="min-height: 100px;"
  >
    <div class="flex items-center gap-2.5">
      <div class="h-7 w-7 bg-slate-200 dark:bg-slate-700 rounded-lg shrink-0" />
      <div class="h-3 w-20 bg-slate-200 dark:bg-slate-700 rounded" />
    </div>
    <div class="h-6 w-24 bg-slate-200 dark:bg-slate-700 rounded my-1" />
    <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100 dark:border-slate-800/80">
      <div class="h-2.5 w-16 bg-slate-200/70 dark:bg-slate-700/70 rounded" />
      <div class="h-4 w-20 bg-slate-200/70 dark:bg-slate-700/70 rounded" />
    </div>
  </div>

  <!-- Error State -->
  <div
    v-else-if="error"
    class="rounded-2xl p-3.5 bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 shadow-2xs flex items-center gap-2.5 text-xs text-rose-700 dark:text-rose-400"
    style="min-height: 100px;"
  >
    <AlertCircle class="h-4 w-4 text-rose-500 shrink-0" />
    <span class="truncate text-[11px]">{{ error }}</span>
  </div>

  <!-- Rendered KPI Card -->
  <component
    :is="to ? RouterLink : 'div'"
    v-else
    :to="to"
    class="relative rounded-2xl p-3.5 bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs transition-all duration-150 flex flex-col justify-between group select-none overflow-hidden"
    :class="[
      to ? 'cursor-pointer hover:shadow-md hover:-translate-y-0.5 ' + variantStyles[variant].ring : '',
    ]"
    style="min-height: 100px;"
  >
    <!-- Top Row: Icon on Top-Left + Label + Quick Link Arrow -->
    <div class="flex items-center justify-between gap-2">
      <div class="flex items-center gap-2 min-w-0">
        <div
          v-if="icon"
          class="h-7 w-7 rounded-lg flex items-center justify-center shrink-0 transition-transform group-hover:scale-105"
          :class="variantStyles[variant].iconBg"
        >
          <component :is="icon" v-if="typeof icon !== 'string'" class="h-3.5 w-3.5" />
          <span v-else class="text-sm" aria-hidden="true">{{ icon }}</span>
        </div>
        <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate leading-tight">
          {{ label }}
        </p>
      </div>

      <div v-if="to" class="text-slate-300 dark:text-slate-600 group-hover:text-slate-700 dark:group-hover:text-slate-200 transition-colors shrink-0">
        <ArrowUpRight class="h-3 w-3" />
      </div>
    </div>

    <!-- Middle: Large Monospace KPI Metric Value -->
    <div class="my-1">
      <p class="text-2xl sm:text-[26px] font-black font-mono text-slate-900 dark:text-white tracking-tight leading-none">
        {{ value }}
      </p>
    </div>

    <!-- Bottom Row: Sublabel/Trend Pill (Left) + Sparkline (Right) in Single Line -->
    <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100/80 dark:border-slate-800/60">
      <!-- Left: Trend Pill or Sublabel -->
      <div class="min-w-0 flex items-center gap-1">
        <div v-if="trend" class="flex items-center gap-1 text-[10px] font-bold font-mono">
          <span
            class="inline-flex items-center gap-0.5 px-1 py-0.2 rounded text-[10px] font-extrabold"
            :class="trend.direction === 'down' ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400' : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400'"
          >
            <TrendingUp v-if="trend.direction === 'up'" class="h-2.5 w-2.5" />
            <TrendingDown v-else-if="trend.direction === 'down'" class="h-2.5 w-2.5" />
            <Minus v-else class="h-2.5 w-2.5" />
            {{ trend.value }}
          </span>
          <span v-if="trend.label" class="text-[10px] text-slate-400 dark:text-slate-500 truncate hidden sm:inline">{{ trend.label }}</span>
        </div>
        <p v-else-if="sublabel" class="text-[10px] sm:text-[11px] font-mono text-slate-400 dark:text-slate-500 truncate">
          {{ sublabel }}
        </p>
      </div>

      <!-- Right: Sparkline in Bottom Row -->
      <div v-if="sparklineData && sparklineData.length > 0" class="shrink-0 flex items-center">
        <BaseSparkline
          :data="sparklineData"
          :variant="variant"
          :width="78"
          :height="20"
          :stroke-width="1.6"
        />
      </div>
    </div>
  </component>
</template>
