<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Filler,
  type ChartConfiguration,
} from 'chart.js'

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler)

interface Props {
  data?: number[]
  variant?: 'primary' | 'success' | 'warning' | 'danger' | 'info' | 'gold'
  width?: number
  height?: number
  strokeWidth?: number
  showArea?: boolean
  loading?: boolean
  error?: string | null
}

const props = withDefaults(defineProps<Props>(), {
  data: () => [],
  variant: 'primary',
  width: 96,
  height: 32,
  strokeWidth: 2,
  showArea: true,
  loading: false,
  error: null,
})

const canvasRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null

const variantColors: Record<string, { stroke: string; fill: string }> = {
  primary: { stroke: '#0B5D3B', fill: 'rgba(11, 93, 59, 0.18)' },
  gold: { stroke: '#D4AF37', fill: 'rgba(212, 175, 55, 0.18)' },
  success: { stroke: '#10b981', fill: 'rgba(16, 185, 129, 0.18)' },
  warning: { stroke: '#f59e0b', fill: 'rgba(245, 158, 11, 0.18)' },
  danger: { stroke: '#f43f5e', fill: 'rgba(244, 63, 94, 0.18)' },
  info: { stroke: '#3b82f6', fill: 'rgba(59, 130, 246, 0.18)' },
}

const activeColors = computed(() => variantColors[props.variant] || variantColors.primary)

function renderChart(): void {
  if (!canvasRef.value) return

  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }

  const values = props.data || []
  if (values.length === 0) return

  const ctx = canvasRef.value.getContext('2d')
  if (!ctx) return

  // Create subtle vertical area gradient
  let gradient: CanvasGradient | string = activeColors.value.fill
  if (props.showArea) {
    gradient = ctx.createLinearGradient(0, 0, 0, props.height)
    gradient.addColorStop(0, activeColors.value.fill)
    gradient.addColorStop(1, 'rgba(255, 255, 255, 0)')
  }

  const pointRadii = values.map((_, idx) => (idx === values.length - 1 ? 2.5 : 0))

  const config: ChartConfiguration<'line'> = {
    type: 'line',
    data: {
      labels: values.map((_, idx) => String(idx)),
      datasets: [
        {
          data: values,
          borderColor: activeColors.value.stroke,
          backgroundColor: props.showArea ? gradient : 'transparent',
          borderWidth: props.strokeWidth,
          fill: props.showArea,
          tension: 0.35,
          pointRadius: pointRadii,
          pointBackgroundColor: activeColors.value.stroke,
          pointBorderColor: '#ffffff',
          pointBorderWidth: 1,
        },
      ],
    },
    options: {
      responsive: false,
      maintainAspectRatio: false,
      animation: false,
      plugins: {
        legend: { display: false },
        tooltip: { enabled: false },
      },
      scales: {
        x: { display: false },
        y: { display: false, min: Math.min(...values) > 0 ? 0 : undefined },
      },
      elements: {
        line: { capBezierPoints: true },
      },
    },
  }

  chartInstance = new Chart(ctx, config)
}

onMounted(() => {
  nextTick(() => {
    if (!props.loading && !props.error && props.data.length > 0) {
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
  () => [props.data, props.variant, props.loading, props.error],
  () => {
    nextTick(() => {
      if (!props.loading && !props.error && props.data.length > 0) {
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
  <div class="relative inline-flex items-center" :style="{ width: `${width}px`, height: `${height}px` }">
    <!-- Skeleton Loading State -->
    <div
      v-if="loading"
      class="w-full h-full rounded-md bg-slate-100 dark:bg-slate-800 animate-pulse"
      aria-label="Loading sparkline"
    />

    <!-- Error State -->
    <div
      v-else-if="error"
      class="w-full h-full flex items-center justify-center text-[10px] text-rose-500 font-mono"
      title="Failed to load sparkline"
    >
      &mdash;
    </div>

    <!-- Empty State -->
    <div
      v-else-if="!data || data.length === 0"
      class="w-full h-full flex items-center justify-center"
      title="No sparkline data"
    >
      <div class="w-full border-t border-dashed border-slate-300 dark:border-slate-700" />
    </div>

    <!-- Success / Rendered Chart.js Canvas -->
    <canvas
      v-else
      ref="canvasRef"
      :width="width"
      :height="height"
      class="block select-none"
    />
  </div>
</template>
