<template>
  <div>
    <p v-if="!hasData" class="text-sm text-gray-500">No trend data available yet.</p>
    <div v-else class="relative h-64">
      <canvas ref="chartRef" aria-label="Monthly dropout trends" role="img"></canvas>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch, nextTick } from 'vue'
import { Chart } from 'chart.js/auto'

type TrendRecord = Record<string, number>

const props = defineProps<{
  data?: Record<string, TrendRecord>
}>()

const chartRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null
let updateTimeout: ReturnType<typeof setTimeout> | null = null

const riskOrder = ['dropout', 'at_risk', 'safe'] as const
const colors: Record<(typeof riskOrder)[number], string> = {
  dropout: '#ef4444',
  at_risk: '#f97316',
  safe: '#22c55e'
}

const labels = {
  dropout: 'High Risk',
  at_risk: 'At Risk',
  safe: 'Safe'
} as const

const hasData = computed(() => {
  if (!props.data) return false
  return Object.values(props.data).some((record) =>
    riskOrder.some((risk) => (record?.[risk] ?? 0) > 0)
  )
})

const monthFormatter = new Intl.DateTimeFormat('en-US', {
  month: 'short',
  year: 'numeric'
})

// Memoize labels computation
const chartLabels = computed(() => {
  if (!props.data) return []
  return Object.keys(props.data)
    .sort()
    .map((month) => {
      const [year, value] = month.split('-')
      const parsedDate = new Date(Number(year), Number(value) - 1, 1)
      return monthFormatter.format(parsedDate)
    })
})

// Memoize dataset computation
const chartDataset = computed(() => {
  if (!props.data) return { labels: [], datasets: [] }
  
  const months = Object.keys(props.data).sort()

  const datasets = riskOrder.map((risk) => ({
    label: labels[risk],
    data: months.map((month) => props.data?.[month]?.[risk] ?? 0),
    borderColor: colors[risk],
    backgroundColor: `${colors[risk]}66`,
    tension: 0.35,
    fill: false,
    pointRadius: 4
  }))

  return {
    labels: chartLabels.value,
    datasets
  }
})

const destroyChart = () => {
  if (updateTimeout) {
    clearTimeout(updateTimeout)
    updateTimeout = null
  }
  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }
}

const renderChart = () => {
  if (!chartRef.value || !hasData.value) {
    destroyChart()
    return
  }

  const context = chartRef.value.getContext('2d')
  if (!context) return

  if (chartInstance) {
    chartInstance.destroy()
  }

  const dataset = chartDataset.value

  chartInstance = new Chart(context, {
    type: 'line',
    data: dataset,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 0 // Disable animations for faster rendering
      },
      transitions: {
        active: {
          animation: {
            duration: 0
          }
        }
      },
      interaction: {
        mode: 'index',
        intersect: false
      },
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            usePointStyle: true,
            font: { size: 12 }
          }
        },
        tooltip: {
          callbacks: {
            label: (context) => {
              const label = context.dataset?.label ?? ''
              const value = context.parsed?.y ?? 0
              return `${label}: ${value}`
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { precision: 0 },
          title: { display: true, text: 'Predictions' }
        }
      }
    }
  })
}

const updateChart = () => {
  if (!chartRef.value || !hasData.value) {
    destroyChart()
    return
  }

  if (chartInstance) {
    const dataset = chartDataset.value
    chartInstance.data.labels = dataset.labels
    chartInstance.data.datasets = dataset.datasets as any
    chartInstance.update('none') // Update without animation
  } else {
    renderChart()
  }
}

// Debounced update function
const debouncedUpdate = () => {
  if (updateTimeout) {
    clearTimeout(updateTimeout)
  }
  updateTimeout = setTimeout(() => {
    nextTick(() => {
      updateChart()
    })
  }, 100) // 100ms debounce
}

onMounted(() => {
  nextTick(() => {
    renderChart()
  })
})

// Use shallow watch instead of deep watch for better performance
watch(
  () => props.data,
  () => {
    debouncedUpdate()
  },
  { immediate: false }
)

watch(hasData, (value) => {
  if (!value) {
    destroyChart()
  } else if (!chartInstance) {
    nextTick(() => {
      renderChart()
    })
  }
})

onBeforeUnmount(() => {
  destroyChart()
})
</script>