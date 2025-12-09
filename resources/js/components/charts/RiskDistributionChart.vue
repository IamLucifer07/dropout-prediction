<template>
  <div class="relative">
    <canvas ref="chartRef" aria-label="Risk distribution chart" role="img"></canvas>
  </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { Chart } from 'chart.js/auto'

const props = defineProps<{
  data?: Record<string, number>
}>()

const chartRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null

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

const buildDataset = () => {
  const distribution = riskOrder.map((risk) => props.data?.[risk] ?? 0)

  return {
    labels: riskOrder.map((risk) => labels[risk]),
    datasets: [
      {
        label: 'Students',
        data: distribution,
        backgroundColor: riskOrder.map((risk) => colors[risk]),
        borderWidth: 0,
        hoverOffset: 12
      }
    ]
  }
}

const renderChart = () => {
  if (!chartRef.value) return

  const context = chartRef.value.getContext('2d')
  if (!context) return

  if (chartInstance) {
    chartInstance.destroy()
  }

  chartInstance = new Chart(context, {
    type: 'doughnut',
    data: buildDataset(),
    options: {
      responsive: true,
      maintainAspectRatio: false,
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
              const label = context.label ?? ''
              const value = context.parsed ?? 0
              return `${label}: ${value}`
            }
          }
        }
      },
      animation: {
        animateScale: true,
        animateRotate: true
      }
    }
  })
}

onMounted(() => {
  renderChart()
})

watch(
  () => props.data,
  () => {
    if (chartInstance) {
      chartInstance.data = buildDataset()
      chartInstance.update()
    } else {
      renderChart()
    }
  },
  { deep: true }
)

onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }
})
</script>