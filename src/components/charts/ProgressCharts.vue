<template>
  <div class="space-y-6">
    <!-- Points Progress Chart -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-medium text-gray-900">Weekly Points Progress</h3>
      <div class="mt-4 h-64">
        <canvas ref="pointsChart"></canvas>
      </div>
    </div>

    <!-- Task Completion Rate Chart -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-medium text-gray-900">Monthly Task Completion Rate</h3>
      <div class="mt-4 h-64">
        <canvas ref="completionChart"></canvas>
      </div>
    </div>

    <!-- Allowance History Chart -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-medium text-gray-900">Allowance History</h3>
      <div class="mt-4 h-64">
        <canvas ref="allowanceChart"></canvas>
      </div>
    </div>

    <!-- Sibling Comparison Chart -->
    <div v-if="showComparison" class="bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-medium text-gray-900">Sibling Comparison</h3>
      <div class="mt-4 h-64">
        <canvas ref="comparisonChart"></canvas>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';
import { format, subDays, eachDayOfInterval } from 'date-fns';

Chart.register(...registerables);

export default {
  name: 'ProgressCharts',
  
  props: {
    childId: {
      type: [Number, String],
      required: true
    },
    showComparison: {
      type: Boolean,
      default: false
    }
  },

  setup(props) {
    const pointsChart = ref(null);
    const completionChart = ref(null);
    const allowanceChart = ref(null);
    const comparisonChart = ref(null);

    const createPointsChart = (data) => {
      const ctx = pointsChart.value.getContext('2d');
      
      const dates = eachDayOfInterval({
        start: subDays(new Date(), 7),
        end: new Date()
      }).map(date => format(date, 'MMM d'));

      new Chart(ctx, {
        type: 'line',
        data: {
          labels: dates,
          datasets: [{
            label: 'Points Earned',
            data: data,
            borderColor: 'rgb(79, 70, 229)',
            tension: 0.1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Points'
              }
            }
          }
        }
      });
    };

    const createCompletionChart = (data) => {
      const ctx = completionChart.value.getContext('2d');
      
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
          datasets: [{
            label: 'Completed Tasks',
            data: data.completed,
            backgroundColor: 'rgba(59, 130, 246, 0.5)'
          }, {
            label: 'Total Tasks',
            data: data.total,
            backgroundColor: 'rgba(209, 213, 219, 0.5)'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Tasks'
              }
            }
          }
        }
      });
    };

    const createAllowanceChart = (data) => {
      const ctx = allowanceChart.value.getContext('2d');
      
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.dates,
          datasets: [{
            label: 'Allowance Amount',
            data: data.amounts,
            borderColor: 'rgb(34, 197, 94)',
            tension: 0.1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Amount ($)'
              }
            }
          }
        }
      });
    };

    const createComparisonChart = (data) => {
      if (!props.showComparison) return;

      const ctx = comparisonChart.value.getContext('2d');
      
      new Chart(ctx, {
        type: 'radar',
        data: {
          labels: ['Tasks Completed', 'Points Earned', 'Streak Bonus', 'Extra Credit', 'On-time Completion'],
          datasets: data.map(child => ({
            label: child.name,
            data: child.stats,
            fill: true,
            backgroundColor: child.color + '0.2)',
            borderColor: child.color + '1)',
            pointBackgroundColor: child.color + '1)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: child.color + '1)'
          }))
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          elements: {
            line: {
              borderWidth: 3
            }
          }
        }
      });
    };

    // Fetch and update chart data
    const fetchChartData = async () => {
      try {
        // Fetch points data
        const pointsResponse = await fetch(`/api/stats/points/${props.childId}`);
        const pointsData = await pointsResponse.json();
        createPointsChart(pointsData);

        // Fetch completion data
        const completionResponse = await fetch(`/api/stats/completion/${props.childId}`);
        const completionData = await completionResponse.json();
        createCompletionChart(completionData);

        // Fetch allowance data
        const allowanceResponse = await fetch(`/api/stats/allowance/${props.childId}`);
        const allowanceData = await allowanceResponse.json();
        createAllowanceChart(allowanceData);

        // Fetch comparison data if needed
        if (props.showComparison) {
          const comparisonResponse = await fetch('/api/stats/comparison');
          const comparisonData = await comparisonResponse.json();
          createComparisonChart(comparisonData);
        }
      } catch (error) {
        console.error('Failed to fetch chart data:', error);
      }
    };

    onMounted(() => {
      fetchChartData();
    });

    watch(() => props.childId, () => {
      fetchChartData();
    });

    return {
      pointsChart,
      completionChart,
      allowanceChart,
      comparisonChart
    };
  }
};
</script>


