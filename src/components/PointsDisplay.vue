<template>
  <div class="bg-white rounded-lg shadow">
    <!-- Header -->
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg leading-6 font-medium text-gray-900">Points Summary</h3>
          <p class="mt-1 text-sm text-gray-500">
            {{ isParent ? "View children's points and history" : "Your school points and history" }}
          </p>
        </div>
        <!-- Weekly Total -->
        <div class="text-right">
          <p class="text-sm text-gray-500">Weekly Total</p>
          <p 
            class="text-2xl font-semibold"
            :class="{
              'text-green-600': totalPoints <= 3,
              'text-red-600': totalPoints > 3
            }"
          >
            {{ totalPoints }} {{ totalPoints === 1 ? 'Point' : 'Points' }}
          </p>
          <p 
            v-if="allowanceDeduction > 0"
            class="text-sm text-red-600 font-medium"
          >
            -${{ allowanceDeduction.toFixed(2) }} from allowance
          </p>
        </div>
      </div>
    </div>

    <!-- Points Chart -->
    <div class="px-4 py-5 sm:p-6">
      <div class="h-48">
        <canvas ref="chartCanvas"></canvas>
      </div>
    </div>

    <!-- Points List -->
    <div class="px-4 sm:px-6">
      <!-- Filters -->
      <div class="pb-4 border-b border-gray-200 sm:flex sm:items-center sm:justify-between">
        <h3 class="text-sm font-medium text-gray-900">Point History</h3>
        <div class="mt-3 sm:mt-0 sm:ml-4">
          <label for="category" class="sr-only">Filter by Category</label>
          <select
            id="category"
            v-model="selectedCategory"
            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
          >
            <option value="">All Categories</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Points List -->
      <div class="mt-2 flow-root">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                      Date
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                      Points
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                      Category
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                      Reason
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                      Added By
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr 
                    v-for="point in filteredPoints" 
                    :key="point.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 sm:pl-6">
                      {{ formatDate(point.created_at) }}
                    </td>
                    <td 
                      class="whitespace-nowrap px-3 py-4 text-sm font-medium"
                      :class="{
                        'text-green-600': point.points > 0,
                        'text-red-600': point.points < 0
                      }"
                    >
                      {{ point.points > 0 ? '+' : '' }}{{ point.points }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                      {{ getCategoryName(point.category_id) }}
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-500">
                      {{ point.reason }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                      {{ point.assigned_by_name }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div 
        v-if="filteredPoints.length === 0"
        class="text-center py-12"
      >
        <svg 
          class="mx-auto h-12 w-12 text-gray-400" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
          />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No Points Found</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ noPointsMessage }}
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { usePointsStore } from '../stores/points';
import { format } from 'date-fns';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export default {
  name: 'PointsDisplay',

  props: {
    userId: {
      type: Number,
      required: true
    },
    isParent: {
      type: Boolean,
      default: false
    }
  },

  setup(props) {
    const pointsStore = usePointsStore();
    const chartCanvas = ref(null);
    const selectedCategory = ref('');
    let chart = null;

    // Computed properties
    const weeklyPoints = computed(() => pointsStore.weeklyPoints);
    const categories = computed(() => pointsStore.categories);
    const totalPoints = computed(() => pointsStore.totalPoints);
    const allowanceDeduction = computed(() => pointsStore.allowanceDeduction);

    const filteredPoints = computed(() => {
      let points = [...weeklyPoints.value];
      if (selectedCategory.value) {
        points = points.filter(p => p.category_id === selectedCategory.value);
      }
      return points;
    });

    const pointsByDate = computed(() => store.getters['points/pointsByDate']);

    const noPointsMessage = computed(() => {
      if (selectedCategory.value) {
        return `No points found for the selected category`;
      }
      return `No points recorded for this week`;
    });

    // Methods
    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy h:mm a');
    };

    const getCategoryName = (categoryId) => {
      const category = categories.value.find(c => c.id === categoryId);
      return category ? category.name : 'Uncategorized';
    };

    const createChart = () => {
      if (!chartCanvas.value) return;

      const ctx = chartCanvas.value.getContext('2d');
      const dates = Object.keys(pointsByDate.value).sort();
      const data = dates.map(date => {
        return pointsByDate.value[date].reduce((sum, p) => sum + p.points, 0);
      });

      if (chart) {
        chart.destroy();
      }

      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: dates.map(date => format(new Date(date), 'MMM d')),
          datasets: [{
            label: 'Daily Points',
            data: data,
            borderColor: totalPoints.value > 3 ? '#dc2626' : '#059669',
            backgroundColor: totalPoints.value > 3 ? '#fee2e2' : '#d1fae5',
            tension: 0.4,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              mode: 'index',
              intersect: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          },
          interaction: {
            intersect: false,
            mode: 'index'
          }
        }
      });
    };

    // Watch for changes
    watch([weeklyPoints, selectedCategory], () => {
      createChart();
    });

    // Lifecycle hooks
    onMounted(async () => {
      await pointsStore.fetchWeeklyPoints({ userId: props.userId });
      await pointsStore.fetchCategories();
      createChart();
    });

    return {
      selectedCategory,
      categories,
      weeklyPoints,
      filteredPoints,
      totalPoints,
      allowanceDeduction,
      chartCanvas,
      formatDate,
      getCategoryName,
      noPointsMessage
    };
  }
};
</script>
