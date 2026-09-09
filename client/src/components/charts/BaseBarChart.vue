<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import {
  Chart,
  BarController,
  BarElement,
  LinearScale,
  CategoryScale,
  Tooltip,
  Legend,
  type ChartConfiguration,
} from 'chart.js'
import { AlertCircle, BarChart2 } from 'lucide-vue-next'

Chart.register(BarController, BarElement, LinearScale, CategoryScale, Tooltip, Legend)

export interface BarChartItem {
  id?: string | number
  label: string
  value: number
  secondaryValue?: number
  percentage?: number
  color?: string
  sublabel?: string
}

interface Props {
  items?: BarChartItem[]
  layout?: 'horizontal' | 'vertical'
  height?: number
  showPercentage?: boolean
  showSecondary?: boolean
  secondaryLabel?: string
  loading?: boolean
  error?: string | null
  emptyText?: string
}

const props = withDefaults(defineProps<Props>(), {
  items: () => [],
  layout: 'horizontal',
  height: 200,
  showPercentage: true,
  showSecondary: false,
  secondaryLabel: 'Returned',
  loading: false,
  error: null,
  emptyText: 'No distribution metrics available',
})

const emit = defineEmits<{
  (e: 'retry'): void
}>()

const canvasRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null

function resolveToken(tokenName: string, fallback: string): string {
  if (typeof window === 'undefined') return fallback
  const val = getComputedStyle(document.documentElement).getPropertyValue(tokenName).trim()
  return val || fallback
}

function resolveColor(color?: string, fallback = '#107c4f'): string {
  if (!color) return fallback
  if (color.startsWith('var(')) {
    const match = color.match(/var\(\s*([^,\)]+)(?:,\s*([^)]+))?\)/)
    if (match) {
      const varName = match[1].trim()
      const fb = (match[2] || fallback).trim()
      return resolveToken(varName, fb)
    }
  }
  return color
}

function getCategoryPalette(): string[] {
  return [
    resolveToken('--wu-danger-500', '#f43f5e'),
    resolveToken('--wu-info-500', '#3b82f6'),
    resolveToken('--wu-warning-500', '#f59e0b'),
    resolveToken('--wu-success-500', '#10b981'),
    resolveToken('--wu-primary-500', '#107c4f'),
    resolveToken('--wu-gold-400', '#d4af37'),
  ]
}

function renderChart(): void {
  if (!canvasRef.value) return

  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }

  const items = props.items || []
  if (items.length === 0 || items.every(i => i.value === 0 && (!props.showSecondary || !i.secondaryValue))) {
    return
  }

  const ctx = canvasRef.value.getContext('2d')
  if (!ctx) return

  const isHorizontal = props.layout === 'horizontal'
  const labels = items.map(i => (i.sublabel ? `${i.label} (${i.sublabel})` : i.label))
  const primaryData = items.map(i => i.value)
  const palette = getCategoryPalette()

  // Campus comparison vs Category breakdown
  const isCampusChart = props.showSecondary
  const campusPrimaryHex = resolveToken('--wu-primary-200', '#a3d3ba')

  const primaryColors = items.map((i, idx) => {
    if (isCampusChart) return campusPrimaryHex
    if (i.color) return resolveColor(i.color, palette[idx % palette.length])
    return palette[idx % palette.length]
  })

  const datasets: any[] = [
    {
      label: 'Total Items',
      data: primaryData,
      backgroundColor: primaryColors,
      borderRadius: 4,
      barThickness: isHorizontal ? 12 : 20,
      maxBarThickness: 24,
    },
  ]

  if (props.showSecondary) {
    const secondaryData = items.map(i => i.secondaryValue ?? 0)
    const successHex = resolveToken('--wu-success-500', '#10b981')
    datasets.push({
      label: props.secondaryLabel || 'Returned',
      data: secondaryData,
      backgroundColor: successHex,
      borderRadius: 4,
      barThickness: isHorizontal ? 12 : 20,
      maxBarThickness: 24,
    })
  }

  const config: ChartConfiguration<'bar'> = {
    type: 'bar',
    data: {
      labels,
      datasets,
    },
    options: {
      indexAxis: isHorizontal ? 'y' : 'x',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: props.showSecondary,
          position: 'top',
          align: 'end',
          labels: {
            boxWidth: 8,
            boxHeight: 8,
            usePointStyle: true,
            font: { size: 10, weight: 'bold' },
            color: '#64748b',
          },
        },
        tooltip: {
          enabled: true,
          backgroundColor: 'rgba(15, 23, 42, 0.95)',
          titleFont: { size: 11, weight: 'bold' },
          bodyFont: { size: 11, family: 'ui-monospace, monospace' },
          padding: 8,
          cornerRadius: 8,
        },
      },
      scales: {
        x: {
          beginAtZero: true,
          grid: {
            display: isHorizontal,
            color: 'rgba(148, 163, 184, 0.12)',
          },
          ticks: {
            font: { size: 10, family: 'ui-monospace, monospace' },
            color: '#94a3b8',
            precision: 0,
          },
          border: { display: false },
        },
        y: {
          beginAtZero: true,
          grid: {
            display: !isHorizontal,
            color: 'rgba(148, 163, 184, 0.12)',
          },
          ticks: {
            font: { size: 10, weight: isHorizontal ? 'normal' : 'bold' },
            color: '#64748b',
          },
          border: { display: false },
        },
      },
    },
  }

  chartInstance = new Chart(ctx, config)
}

onMounted(() => {
  nextTick(() => {
    if (!props.loading && !props.error) {
      renderChart()
    }
  })
})

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }
})

watch(
  () => [props.items, props.layout, props.loading, props.error],
  () => {
    nextTick(() => {
      if (!props.loading && !props.error) {
        renderChart()
      } else if (chartInstance) {
        chartInstance.destroy()
        chartInstance = null
      }
    })
  },
  { deep: true }
)
</script>

<template>
  <div class="w-full select-none">
    <!-- Skeleton State -->
    <div
      v-if="loading"
      class="w-full rounded-2xl bg-slate-100 dark:bg-slate-800/80 animate-pulse p-4 space-y-4"
      :style="{ height: `${height}px` }"
      aria-label="Loading bar chart"
    >
      <div v-for="n in 4" :key="n" class="space-y-1.5">
        <div class="flex justify-between items-center">
          <div class="h-3 w-28 bg-slate-200 dark:bg-slate-700 rounded" />
          <div class="h-3 w-12 bg-slate-200 dark:bg-slate-700 rounded" />
        </div>
        <div class="h-3 w-full bg-slate-200/70 dark:bg-slate-700/70 rounded-full" />
      </div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="w-full rounded-2xl border border-rose-200 dark:border-rose-900/50 bg-rose-50/50 dark:bg-rose-950/20 flex flex-col items-center justify-center p-6 text-center"
      :style="{ height: `${height}px` }"
    >
      <AlertCircle class="h-7 w-7 text-rose-500 mb-2" />
      <p class="text-xs font-bold text-rose-700 dark:text-rose-400 mb-1">Failed to load chart metrics</p>
      <p class="text-[11px] text-rose-600/80 dark:text-rose-400/80 mb-3">{{ error }}</p>
      <button
        type="button"
        class="px-3 py-1 text-xs font-bold bg-white dark:bg-slate-900 border border-rose-300 dark:border-rose-700 text-rose-700 dark:text-rose-300 rounded-lg hover:bg-rose-50 cursor-pointer shadow-2xs transition-colors"
        @click="emit('retry')"
      >
        Retry Analytics
      </button>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="!items || items.length === 0 || items.every(i => i.value === 0)"
      class="w-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 flex flex-col items-center justify-center p-6 text-center"
      :style="{ height: `${height}px` }"
    >
      <BarChart2 class="h-7 w-7 text-slate-400 mb-2" />
      <p class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-0.5">No Distribution Data</p>
      <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ emptyText }}</p>
    </div>

    <!-- Success / Rendered Chart.js Canvas -->
    <div v-else class="relative w-full" :style="{ height: `${height}px` }">
      <canvas ref="canvasRef" class="w-full h-full block" />
    </div>
  </div>
</template>
