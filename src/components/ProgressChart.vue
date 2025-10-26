<template>
  <div class="relative">
    <!-- Chart Container -->
    <div class="w-full h-64">
      <canvas ref="chartCanvas"></canvas>
    </div>

    <!-- Loading Overlay -->
    <div 
      v-if="loading"
      class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center"
    >
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Error Message -->
    <div 
      v-if="error"
      class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center"
    >
      <div class="text-red-600 text-center">
        <p>{{ error }}</p>
        <button 
          @click="retryLoading"
          class="mt-2 text-sm text-indigo-600 hover:text-indigo-500"
        >
          Try Again
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'
import { format, subDays, eachDayOfInterval } from 'date-fns'

// Register Chart.js components
Chart.register(...registerables)

export default {
  name: 'ProgressChart',

  props: {
    data: {
      type: Object,
      required: true,
      validator: (value) => {
        return [
          'room_cleaning',
          'bathroom_cleaning',
          'kitchen_cleaning',
          'laundry'
        ].every(key => Array.isArray(value[key]))
      }
    },
    loading: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: ''
    }
  },

  emits: ['retry'],

  setup(props, { emit }) {
    const chartCanvas = ref(null)
    let chart = null

    const createChart = () => {
      if (!chartCanvas.value) return

      // Get last 7 days
      const today = new Date()
      const dates = eachDayOfInterval({
        start: subDays(today, 6),
        end: today
      })

      // Prepare datasets
      const datasets = Object.entries(props.data).map(([key, values], index) => {
        const colors = {
          room_cleaning: { bg: 'rgba(79, 70, 229, 0.2)', border: 'rgb(79, 70, 229)' },
          bathroom_cleaning: { bg: 'rgba(16, 185, 129, 0.2)', border: 'rgb(16, 185, 129)' },
          kitchen_cleaning: { bg: 'rgba(245, 158, 11, 0.2)', border: 'rgb(245, 158, 11)' },
          laundry: { bg: 'rgba(239, 68, 68, 0.2)', border: 'rgb(239, 68, 68)' }
        }

        return {
          label: key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '),
          data: dates.map(date => {
            const dateStr = format(date, 'yyyy-MM-dd')
            return values.find(v => v.date === dateStr)?.completed ? 1 : 0
          }),
          backgroundColor: colors[key].bg,
          borderColor: colors[key].border,
          borderWidth: 2,
          tension: 0.4
        }
      })

      // Create chart
      const ctx = chartCanvas.value.getContext('2d')
      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: dates.map(date => format(date, 'MMM d')),
          datasets
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 1,
              ticks: {
                stepSize: 1,
                callback: value => value === 1 ? 'Done' : 'Not Done'
              }
            }
          },
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                usePointStyle: true,
                padding: 20
              }
            },
            tooltip: {
              mode: 'index',
              intersect: false,
              callbacks: {
                label: (context) => {
                  const label = context.dataset.label
                  const value = context.raw === 1 ? 'Completed' : 'Not Completed'
                  return `${label}: ${value}`
                }
              }
            }
          },
          interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
          }
        }
      })
    }

    const destroyChart = () => {
      if (chart) {
        chart.destroy()
        chart = null
      }
    }

    const retryLoading = () => {
      emit('retry')
    }

    // Watch for data changes
    watch(() => props.data, () => {
      destroyChart()
      createChart()
    }, { deep: true })

    // Watch for loading state
    watch(() => props.loading, (newValue) => {
      if (!newValue) {
        destroyChart()
        createChart()
      }
    })

    onMounted(() => {
      if (!props.loading && !props.error) {
        createChart()
      }
    })

    onUnmounted(() => {
      destroyChart()
    })

    return {
      chartCanvas,
      retryLoading
    }
  }
}
</script>
