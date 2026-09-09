<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Filler,
  Tooltip,
  Legend,
  type ChartConfiguration,
} from 'chart.js'
import { AlertCircle, FileX2 } from 'lucide-vue-next'

Chart.register(
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Filler,
  Tooltip,
  Legend
)

export interface TrendSeries {
  name: string
  key: string
  data: number[]
  color?: string
  fillColor?: string
  strokeWidth?: number
}

interface Props {
  labels?: string[]
  series?: TrendSeries[]
  height?: number
  loading?: boolean
  error?: string | null
  emptyText?: string
}

const props = withDefaults(defineProps<Props>(), {
  labels: () => [],
  series: () => [],
  height: 260,
  loading: false,
  error: null,
  emptyText: 'No trend data recorded for this period',
})

const emit = defineEmits<{
  (e: 'retry'): void
}>()

const canvasRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null

const defaultColors = ['#f43f5e', '#3b82f6', '#0B5D3B', '#f59e0b', '#D4AF37']

function parseColor(rawColor?: string, fallback: string = '#0B5D3B'): { stroke: string; rgba0: string; rgba1: string } {
  const target = rawColor || fallback
  let hex = target

  if (target.includes('#')) {
    const match = target.match(/#[a-fA-F0-9]{6}|#[a-fA-F0-9]{3}/)
    if (match) {
      hex = match[0]
    }
  }

  if (hex.startsWith('#')) {
    let cleanHex = hex.replace('#', '')
    if (cleanHex.length === 3) {
      cleanHex = cleanHex.split('').map(c => c + c).join('')
    }
    const num = parseInt(cleanHex, 16)
    const r = (num >> 16) & 255
    const g = (num >> 8) & 255
    const b = num & 255
    return {
      stroke: `#${cleanHex}`,
      rgba0: `rgba(${r}, ${g}, ${b}, 0.28)`,
      rgba1: `rgba(${r}, ${g}, ${b}, 0.0)`,
    }
  }

  return { stroke: target, rgba0: 'rgba(11, 93, 59, 0.28)', rgba1: 'rgba(11, 93, 59, 0.0)' }
}

function renderChart(): void {
  if (!canvasRef.value) return

  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }

  const labels = props.labels || []
  const series = props.series || []

  if (labels.length === 0 || series.length === 0 || series.every(s => (s.data || []).every(v => v === 0))) {
    return
  }

  const ctx = canvasRef.value.getContext('2d')
  if (!ctx) return

  const datasets = series.map((s, idx) => {
    const colorInfo = parseColor(s.color, defaultColors[idx % defaultColors.length])
    const grad = ctx.createLinearGradient(0, 0, 0, props.height)
    grad.addColorStop(0, colorInfo.rgba0)
    grad.addColorStop(0.85, colorInfo.rgba1)
    grad.addColorStop(1, 'rgba(255, 255, 255, 0)')

    return {
      label: s.name,
      data: s.data || [],
      borderColor: colorInfo.stroke,
      backgroundColor: grad,
      borderWidth: s.strokeWidth || 2.2,
      fill: true,
      tension: 0.35,
      pointRadius: 0,
      pointHoverRadius: 5,
      pointHoverBackgroundColor: colorInfo.stroke,
      pointHoverBorderColor: '#ffffff',
      pointHoverBorderWidth: 2,
    }
  })

  const config: ChartConfiguration<'line'> = {
    type: 'line',
    data: {
      labels,
      datasets,
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      plugins: {
        legend: {
          display: true,
          position: 'top',
          align: 'end',
          labels: {
            boxWidth: 8,
            boxHeight: 8,
            usePointStyle: true,
            pointStyle: 'circle',
            font: {
              size: 11,
              weight: 'bold',
              family: 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace',
            },
            color: '#64748b',
            padding: 10,
          },
        },
        tooltip: {
          enabled: true,
          backgroundColor: 'rgba(15, 23, 42, 0.95)',
          titleFont: {
            size: 11,
            weight: 'bold',
          },
          bodyFont: {
            size: 11,
            family: 'ui-monospace, monospace',
          },
          padding: 10,
          cornerRadius: 8,
          borderColor: 'rgba(255, 255, 255, 0.1)',
          borderWidth: 1,
          displayColors: true,
          boxWidth: 6,
          boxHeight: 6,
          boxPadding: 4,
          usePointStyle: true,
        },
      },
      scales: {
        x: {
          grid: {
            display: false,
          },
          ticks: {
            font: {
              size: 10,
              weight: 'bold',
            },
            color: '#94a3b8',
            maxRotation: 0,
          },
          border: {
            display: false,
          },
        },
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(148, 163, 184, 0.15)',
            lineWidth: 1,
          },
          ticks: {
            font: {
              size: 10,
              family: 'ui-monospace, monospace',
            },
            color: '#94a3b8',
            precision: 0,
            padding: 8,
          },
          border: {
            display: false,
            dash: [3, 3],
          },
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
  () => [props.labels, props.series, props.loading, props.error],
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
      class="w-full rounded-2xl bg-slate-100 dark:bg-slate-800/80 animate-pulse flex flex-col justify-between p-4"
      :style="{ height: `${height}px` }"
      aria-label="Loading trend chart"
    >
      <div class="h-4 w-36 bg-slate-200 dark:bg-slate-700 rounded-md" />
      <div class="space-y-3 w-full my-auto">
        <div class="h-2 w-full bg-slate-200/70 dark:bg-slate-700/70 rounded" />
        <div class="h-2 w-full bg-slate-200/70 dark:bg-slate-700/70 rounded" />
        <div class="h-2 w-full bg-slate-200/70 dark:bg-slate-700/70 rounded" />
      </div>
      <div class="flex justify-between items-center w-full pt-2 border-t border-slate-200/60 dark:border-slate-700/60">
        <div v-for="n in 6" :key="n" class="h-3 w-10 bg-slate-200 dark:bg-slate-700 rounded" />
      </div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="w-full rounded-2xl border border-rose-200 dark:border-rose-900/50 bg-rose-50/50 dark:bg-rose-950/20 flex flex-col items-center justify-center p-6 text-center"
      :style="{ height: `${height}px` }"
    >
      <AlertCircle class="h-7 w-7 text-rose-500 mb-2" />
      <p class="text-xs font-bold text-rose-700 dark:text-rose-400 mb-1">Failed to load trend data</p>
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
      v-else-if="!labels || labels.length === 0 || series.length === 0 || series.every(s => (s.data || []).every(v => v === 0))"
      class="w-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 flex flex-col items-center justify-center p-6 text-center"
      :style="{ height: `${height}px` }"
    >
      <FileX2 class="h-7 w-7 text-slate-400 mb-2" />
      <p class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-0.5">No Historical Records</p>
      <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ emptyText }}</p>
    </div>

    <!-- Success / Rendered Chart.js Canvas -->
    <div v-else class="relative w-full" :style="{ height: `${height}px` }">
      <canvas ref="canvasRef" class="w-full h-full block" />
    </div>
  </div>
</template>
