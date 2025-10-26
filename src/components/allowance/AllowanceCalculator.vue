<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h3 class="text-lg leading-6 font-medium text-gray-900">
        Calculate Allowance
      </h3>
      
      <!-- Period Selection -->
      <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
          <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
          <input
            type="date"
            id="start_date"
            v-model="period.start_date"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
        <div>
          <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
          <input
            type="date"
            id="end_date"
            v-model="period.end_date"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <!-- Quick Period Selectors -->
      <div class="mt-4 flex space-x-3">
        <button
          v-for="(label, days) in quickPeriods"
          :key="days"
          @click="setQuickPeriod(days)"
          class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
        >
          {{ label }}
        </button>
      </div>

      <!-- Calculate Button -->
      <div class="mt-5">
        <button
          @click="calculateAllowance"
          :disabled="loading"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          {{ loading ? 'Calculating...' : 'Calculate Allowance' }}
        </button>
      </div>

      <!-- Calculation Results -->
      <div v-if="calculation" class="mt-6">
        <div class="bg-gray-50 px-4 py-5 sm:rounded-lg sm:p-6">
          <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
              <h3 class="text-lg font-medium leading-6 text-gray-900">Summary</h3>
              <div class="mt-4 space-y-2">
                <p class="text-sm text-gray-500">Tasks Completed: {{ calculation.tasks_completed }}</p>
                <p class="text-sm text-gray-500">Total Points: {{ calculation.total_points }}</p>
              </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
              <div class="grid grid-cols-1 gap-4">
                <div class="bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
                  <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Base Allowance</dt>
                      <dd class="mt-1 text-2xl font-semibold text-gray-900">${{ calculation.base_allowance }}</dd>
                    </div>
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Bonus Amount</dt>
                      <dd class="mt-1 text-2xl font-semibold text-gray-900">${{ calculation.bonus_amount }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                      <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                      <dd class="mt-1 text-3xl font-semibold text-indigo-600">${{ calculation.total_amount }}</dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Task List -->
          <div class="mt-6">
            <h4 class="text-lg font-medium text-gray-900">Completed Tasks</h4>
            <ul class="mt-3 divide-y divide-gray-200">
              <li v-for="task in calculation.tasks" :key="task.name" class="py-3">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ task.name }}</p>
                    <p class="text-sm text-gray-500">
                      Completed: {{ formatDate(task.completed_at) }}
                    </p>
                  </div>
                  <div class="text-sm font-medium text-indigo-600">
                    {{ task.points_earned }} points
                  </div>
                </div>
              </li>
            </ul>
          </div>

          <!-- Pay Allowance Button -->
          <div class="mt-6">
            <button
              @click="payAllowance"
              :disabled="loading"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              {{ loading ? 'Processing...' : 'Pay Allowance' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { format } from 'date-fns';

export default {
  name: 'AllowanceCalculator',
  props: {
    childId: {
      type: [Number, String],
      required: true
    }
  },

  setup(props, { emit }) {
    const loading = ref(false);
    const period = ref({
      start_date: '',
      end_date: ''
    });
    const calculation = ref(null);

    const quickPeriods = {
      7: 'Last Week',
      14: 'Last 2 Weeks',
      30: 'Last Month'
    };

    const setQuickPeriod = (days) => {
      const end = new Date();
      const start = new Date();
      start.setDate(start.getDate() - days);
      
      period.value = {
        start_date: format(start, 'yyyy-MM-dd'),
        end_date: format(end, 'yyyy-MM-dd')
      };
    };

    const calculateAllowance = async () => {
      loading.value = true;
      try {
        emit('calculate', {
          child_id: props.childId,
          ...period.value
        });
      } finally {
        loading.value = false;
      }
    };

    const payAllowance = async () => {
      if (!calculation.value) return;
      
      loading.value = true;
      try {
        emit('pay', {
          child_id: props.childId,
          amount: calculation.value.total_amount,
          period_start: period.value.start_date,
          period_end: period.value.end_date
        });
      } finally {
        loading.value = false;
      }
    };

    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy');
    };

    return {
      loading,
      period,
      calculation,
      quickPeriods,
      setQuickPeriod,
      calculateAllowance,
      payAllowance,
      formatDate
    };
  }
};
</script>
