<template>
  <div class="space-y-6">
    <!-- Task Completion Stats -->
    <div class="bg-white shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Task Completion</h3>
      <div class="h-64">
        <canvas ref="taskChart"></canvas>
      </div>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div v-for="stat in taskTypeStats" :key="stat.type" class="bg-gray-50 p-4 rounded-lg">
          <h4 class="text-sm font-medium text-gray-500">{{ formatTaskType(stat.task_type) }}</h4>
          <p class="mt-2 text-2xl font-semibold text-gray-900">
            {{ stat.verified }}/{{ stat.total }}
          </p>
          <p class="text-sm text-gray-500">
            {{ Math.round((stat.verified / stat.total) * 100) }}% completion rate
          </p>
        </div>
      </div>
    </div>

    <!-- Earnings Report -->
    <div class="bg-white shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Earnings History</h3>
      <div class="h-64">
        <canvas ref="earningsChart"></canvas>
      </div>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-green-50 p-4 rounded-lg">
          <h4 class="text-sm font-medium text-green-700">Total Earnings</h4>
          <p class="mt-2 text-2xl font-semibold text-green-900">
            ${{ totals.total_earnings }}
          </p>
        </div>
        <div class="bg-red-50 p-4 rounded-lg">
          <h4 class="text-sm font-medium text-red-700">Total Deductions</h4>
          <p class="mt-2 text-2xl font-semibold text-red-900">
            ${{ totals.total_deductions }}
          </p>
        </div>
        <div class="bg-blue-50 p-4 rounded-lg">
          <h4 class="text-sm font-medium text-blue-700">Net Earnings</h4>
          <p class="mt-2 text-2xl font-semibold text-blue-900">
            ${{ totals.total_earnings - totals.total_deductions }}
          </p>
        </div>
      </div>
    </div>

    <!-- School Points -->
    <div v-if="isChild" class="bg-white shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">School Performance</h3>
      <div class="h-64">
        <canvas ref="pointsChart"></canvas>
      </div>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-purple-50 p-4 rounded-lg">
          <h4 class="text-sm font-medium text-purple-700">Average Points</h4>
          <p class="mt-2 text-2xl font-semibold text-purple-900">
            {{ averagePoints.toFixed(1) }}
          </p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg">
          <h4 class="text-sm font-medium text-yellow-700">Weeks Without Deductions</h4>
          <p class="mt-2 text-2xl font-semibold text-yellow-900">
            {{ weeksWithoutDeductions }}
          </p>
        </div>
      </div>
    </div>

    <!-- Parent Overview -->
    <div v-if="isParent" class="bg-white shadow rounded-lg p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Children Overview</h3>
      <div class="space-y-4">
        <div v-for="child in childrenStats" :key="child.first_name" class="bg-gray-50 p-4 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-lg font-medium text-gray-900">{{ child.first_name }}</h4>
              <p class="text-sm text-gray-500">
                {{ child.completed_tasks }}/{{ child.total_tasks }} tasks completed
              </p>
            </div>
            <div class="text-right">
              <p class="text-lg font-medium text-green-600">
                ${{ child.total_earnings }}
              </p>
              <p class="text-sm text-red-600">
                -${{ child.total_deductions }}
              </p>
            </div>
          </div>
          <div class="mt-4">
            <div class="relative pt-1">
              <div class="flex mb-2 items-center justify-between">
                <div>
                  <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-600 bg-green-200">
                    Task Completion
                  </span>
                </div>
                <div class="text-right">
                  <span class="text-xs font-semibold inline-block text-green-600">
                    {{ Math.round((child.completed_tasks / child.total_tasks) * 100) }}%
                  </span>
                </div>
              </div>
              <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-200">
                <div
                  :style="{ width: `${(child.completed_tasks / child.total_tasks) * 100}%` }"
                  class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useStore } from 'vuex'
import Chart from 'chart.js/auto'
import moment from 'moment'
import axios from 'axios'

export default {
  name: 'Reports',
  setup() {
    const store = useStore()
    const taskChart = ref(null)
    const earningsChart = ref(null)
    const pointsChart = ref(null)
    
    const taskTypeStats = ref([])
    const totals = ref({
      total_earnings: 0,
      total_deductions: 0
    })
    const averagePoints = ref(0)
    const weeksWithoutDeductions = ref(0)
    const childrenStats = ref([])

    const user = computed(() => store.getters['auth/user'])
    const isChild = computed(() => user.value?.role === 'child')
    const isParent = computed(() => user.value?.role === 'parent')

    const initCharts = async () => {
      try {
        // Fetch task stats
        const taskResponse = await axios.get('/api/reports/tasks')
        const taskData = taskResponse.data
        
        taskTypeStats.value = taskData.task_types
        
        new Chart(taskChart.value.getContext('2d'), {
          type: 'line',
          data: {
            labels: taskData.daily_stats.map(stat => moment(stat.date).format('MMM D')),
            datasets: [{
              label: 'Completed Tasks',
              data: taskData.daily_stats.map(stat => stat.verified_tasks),
              borderColor: 'rgb(59, 130, 246)',
              tension: 0.1
            }]
          },
          options: {
            responsive: true,
            scales: {
              y: {
                beginAtZero: true,
                max: 4
              }
            }
          }
        })

        // Fetch earnings report
        const earningsResponse = await axios.get('/api/reports/earnings')
        const earningsData = earningsResponse.data
        
        totals.value = earningsData.totals
        
        new Chart(earningsChart.value.getContext('2d'), {
          type: 'bar',
          data: {
            labels: earningsData.monthly_earnings.map(earning => moment(earning.month).format('MMM YYYY')),
            datasets: [
              {
                label: 'Earnings',
                data: earningsData.monthly_earnings.map(earning => earning.earnings),
                backgroundColor: 'rgba(34, 197, 94, 0.5)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1
              },
              {
                label: 'Deductions',
                data: earningsData.monthly_earnings.map(earning => earning.deductions),
                backgroundColor: 'rgba(239, 68, 68, 0.5)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1
              }
            ]
          },
          options: {
            responsive: true,
            scales: {
              y: {
                beginAtZero: true
              }
            }
          }
        })

        if (isChild.value) {
          // Fetch school points
          const pointsResponse = await axios.get('/api/reports/school-points')
          const pointsData = pointsResponse.data
          
          averagePoints.value = pointsData.average_points
          weeksWithoutDeductions.value = pointsData.weekly_points.filter(week => week.points <= 3).length
          
          new Chart(pointsChart.value.getContext('2d'), {
            type: 'line',
            data: {
              labels: pointsData.weekly_points.map(point => moment(point.week_start).format('MMM D')),
              datasets: [{
                label: 'School Points',
                data: pointsData.weekly_points.map(point => point.points),
                borderColor: 'rgb(147, 51, 234)',
                tension: 0.1
              }]
            },
            options: {
              responsive: true,
              scales: {
                y: {
                  beginAtZero: true,
                  max: 10
                }
              }
            }
          })
        }

        if (isParent.value) {
          // Fetch parent report
          const parentResponse = await axios.get('/api/reports/parent')
          childrenStats.value = parentResponse.data.task_stats.map(child => ({
            ...child,
            ...parentResponse.data.earnings.find(e => e.first_name === child.first_name)
          }))
        }
      } catch (error) {
        console.error('Error fetching report data:', error)
      }
    }

    const formatTaskType = (type) => {
      return type.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
      ).join(' ')
    }

    onMounted(() => {
      initCharts()
    })

    return {
      taskTypeStats,
      totals,
      averagePoints,
      weeksWithoutDeductions,
      childrenStats,
      isChild,
      isParent,
      taskChart,
      earningsChart,
      pointsChart,
      formatTaskType
    }
  }
}
</script>
