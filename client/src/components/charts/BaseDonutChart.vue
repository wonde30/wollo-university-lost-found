<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import {
  Chart,
  DoughnutController,
  ArcElement,
  Tooltip,
  type ChartConfiguration,
} from 'chart.js'
import { AlertCircle, PieChart } from 'lucide-vue-next'

Chart.register(DoughnutController, ArcElement, Tooltip)

export interface DonutSegment {
  id?: string | number
  label: string
  value: number
  color?: string
  percentage?: number
}

interface Props {
  segments?: DonutSegment[]
  centerValue?: string | number
  centerLabel?: string
  size?: number
  thickness?: number
  loading?: boolean
  error?: string | null
  emptyText?: string
}

const props = withDefaults(defineProps<Props>(), {
  segments: () => [],
  centerValue: undefined,
  centerLabel: undefined,
  size: 155,
  thickness: 18,
  loading: false,
  error: null,
  emptyText: 'No categorical distribution data',
})

const emit = defineEmits<{
  (e: 'retry'): void
}>()

const canvasRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null
const hoveredSegmentIndex = ref<number | null>(null)

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

function getSemanticColor(id?: string | number, fallbackIdx = 0): string {
  const key = String(id || '').toLowerCase()
  if (key === 'lost' || key.includes('lost')) return resolveToken('--wu-danger-500', '#f43f5e')
  if (key === 'found_unclaimed' || key.includes('unclaimed') || key === 'found') return resolveToken('--wu-info-500', '#3b82f6')
  if (key === 'claimed' || key.includes('claim')) return resolveToken('--wu-warning-500', '#f59e0b')
  if (key === 'returned' || key.includes('return')) return resolveToken('--wu-success-500', '#10b981')
  if (key === 'closed' || key.includes('close') || key.includes('other') || key.includes('withdraw') || key.includes('dispose')) {
    return resolveToken('--wu-slate-400', '#94a3b8')
  }

  const defaultPalette = [
    resolveToken('--wu-danger-500', '#f43f5e'),
    resolveToken('--wu-info-500', '#3b82f6'),
    resolveToken('--wu-warning-500', '#f59e0b'),
    resolveToken('--wu-success-500', '#10b981'),
    resolveToken('--wu-slate-400', '#94a3b8'),
    resolveToken('--wu-primary-500', '#107c4f'),
  ]
  return defaultPalette[fallbackIdx % defaultPalette.length]
}

const totalValue = computed(() => {
  return props.segments.reduce((acc, curr) => acc + (curr.value || 0), 0)
})

const computedSegments = computed(() => {
  const total = totalValue.value || 1
  return props.segments.map((seg, idx) => {
    const val = seg.value || 0
    const pct = Math.round((val / total) * 100)
    const colorHex = seg.color ? resolveColor(seg.color, getSemanticColor(seg.id, idx)) : getSemanticColor(seg.id, idx)
    return {
      ...seg,
      color: colorHex,
      computedPct: pct,
    }
  })
})

const displayCenterValue = computed(() => {
  if (hoveredSegmentIndex.value !== null && computedSegments.value[hoveredSegmentIndex.value]) {
    return computedSegments.value[hoveredSegmentIndex.value].value
  }
  return props.centerValue !== undefined ? props.centerValue : totalValue.value
})

const displayCenterLabel = computed(() => {
  if (hoveredSegmentIndex.value !== null && computedSegments.value[hoveredSegmentIndex.value]) {
    return computedSegments.value[hoveredSegmentIndex.value].label
  }
  return props.centerLabel || 'Total Items'
})

function renderChart(): void {
  if (!canvasRef.value) return

  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }

  const segments = computedSegments.value
  if (segments.length === 0 || totalValue.value === 0) {
    return
  }

  const ctx = canvasRef.value.getContext('2d')
  if (!ctx) return

  const labels = segments.map(s => s.label)
  const data = segments.map(s => s.value)
  const backgroundColors = segments.map(s => s.color)

  const config: ChartConfiguration<'doughnut'> = {
    type: 'doughnut',
    data: {
      labels,
      datasets: [
        {
          data,
          backgroundColor: backgroundColors,
          borderWidth: 2,
          borderColor: '#ffffff',
          hoverBorderWidth: 3,
          hoverBorderColor: '#ffffff',
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '72%',
      plugins: {
        legend: { display: false },
        tooltip: {
          enabled: true,
          backgroundColor: 'rgba(15, 23, 42, 0.95)',
          titleFont: { size: 11, weight: 'bold' },
          bodyFont: { size: 11, family: 'ui-monospace, monospace' },
          padding: 8,
          cornerRadius: 8,
          callbacks: {
            label: (item) => {
              const val = item.raw as number
              const pct = totalValue.value > 0 ? Math.round((val / totalValue.value) * 100) : 0
              return ` ${item.label}: ${val} (${pct}%)`
            },
          },
        },
      },
      onHover: (_event, activeElements) => {
        if (activeElements.length > 0) {
          hoveredSegmentIndex.value = activeElements[0].index
        } else {
          hoveredSegmentIndex.value = null
        }
      },
    },
  }

  chartInstance = new Chart(ctx, config)
}

function handleLegendHover(idx: number | null): void {
  hoveredSegmentIndex.value = idx
  if (!chartInstance) return

  if (idx !== null) {
    chartInstance.setActiveElements([{ datasetIndex: 0, index: idx }])
    chartInstance.tooltip?.setActiveElements([{ datasetIndex: 0, index: idx }], { x: 0, y: 0 })
  } else {
    chartInstance.setActiveElements([])
  }
  chartInstance.update()
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
  () => [props.segments, props.loading, props.error],
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
      class="w-full rounded-2xl bg-slate-100 dark:bg-slate-800/80 animate-pulse flex flex-col sm:flex-row items-center justify-around p-4 gap-4"
      :style="{ minHeight: `${size + 30}px` }"
      aria-label="Loading donut chart"
    >
      <div
        class="rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center shrink-0"
        :style="{ width: `${size}px`, height: `${size}px` }"
      >
        <div
          class="rounded-full bg-white dark:bg-slate-900"
          :style="{ width: `${size - thickness * 2}px`, height: `${size - thickness * 2}px` }"
        />
      </div>
      <div class="space-y-2.5 flex-1 max-w-xs">
        <div v-for="n in 3" :key="n" class="flex justify-between items-center">
          <div class="h-3 w-20 bg-slate-200 dark:bg-slate-700 rounded" />
          <div class="h-3 w-10 bg-slate-200 dark:bg-slate-700 rounded" />
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="w-full rounded-2xl border border-rose-200 dark:border-rose-900/50 bg-rose-50/50 dark:bg-rose-950/20 flex flex-col items-center justify-center p-6 text-center"
      :style="{ minHeight: `${size + 30}px` }"
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
      v-else-if="!segments || segments.length === 0 || totalValue === 0"
      class="w-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 flex flex-col items-center justify-center p-6 text-center"
      :style="{ minHeight: `${size + 30}px` }"
    >
      <PieChart class="h-7 w-7 text-slate-400 mb-2" />
      <p class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-0.5">No Distribution Data</p>
      <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ emptyText }}</p>
    </div>

    <!-- Success / Interactive Chart.js State -->
    <div v-else class="flex flex-col sm:flex-row items-center justify-between gap-5 py-2">
      <!-- Chart.js Doughnut Canvas + Center Overlay -->
      <div class="relative shrink-0 flex items-center justify-center" :style="{ width: `${size}px`, height: `${size}px` }">
        <canvas ref="canvasRef" :width="size" :height="size" class="block w-full h-full" />

        <!-- Center Overlay -->
        <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none p-2">
          <span class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-none font-mono">
            {{ displayCenterValue }}
          </span>
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mt-1 max-w-[85px] truncate">
            {{ displayCenterLabel }}
          </span>
        </div>
      </div>

      <!-- Segment Legend Breakdown List -->
      <div class="flex-1 w-full space-y-2 min-w-0">
        <div
          v-for="(seg, idx) in computedSegments"
          :key="seg.id || seg.label"
          class="flex items-center justify-between text-xs p-1.5 rounded-lg transition-colors cursor-pointer"
          :class="hoveredSegmentIndex === idx ? 'bg-slate-50 dark:bg-slate-800/60' : ''"
          @mouseenter="handleLegendHover(idx)"
          @mouseleave="handleLegendHover(null)"
        >
          <div class="flex items-center gap-2 min-w-0 pr-2">
            <span
              class="h-2.5 w-2.5 rounded-full shrink-0"
              :style="{ backgroundColor: seg.color }"
            />
            <span class="font-bold text-slate-700 dark:text-slate-300 truncate">
              {{ seg.label }}
            </span>
          </div>

          <div class="flex items-center gap-2 font-mono shrink-0">
            <span class="font-extrabold text-slate-900 dark:text-white">
              {{ seg.value }}
            </span>
            <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
              ({{ seg.computedPct }}%)
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
