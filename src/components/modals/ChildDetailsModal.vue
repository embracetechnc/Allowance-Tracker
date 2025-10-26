<template>
  <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
        <div>
          <div class="flex items-center justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              {{ child.name }}'s Details
            </h3>
            <button
              @click="$emit('close')"
              class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none"
            >
              <span class="sr-only">Close</span>
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Child Stats -->
          <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500">Current Balance</dt>
                <dd class="mt-1 text-3xl font-semibold text-indigo-600">${{ child.allowance_balance }}</dd>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500">Weekly Rate</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">${{ child.weekly_allowance_rate }}</dd>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500">Total Points</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ totalPoints }}</dd>
              </div>
            </div>
          </div>

          <!-- Update Weekly Rate -->
          <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-900">Update Weekly Allowance Rate</h4>
            <div class="mt-2 flex items-center space-x-4">
              <input
                type="number"
                v-model.number="newRate"
                min="0"
                step="0.01"
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
              />
              <button
                @click="updateRate"
                :disabled="loading"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                {{ loading ? 'Updating...' : 'Update Rate' }}
              </button>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-900">Recent Activity</h4>
            <div class="mt-2 flow-root">
              <ul class="-my-5 divide-y divide-gray-200">
                <li v-for="activity in recentActivity" :key="activity.id" class="py-4">
                  <div class="flex items-center space-x-4">
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate">
                        {{ activity.description }}
                      </p>
                      <p class="text-sm text-gray-500">
                        {{ formatDate(activity.created_at) }}
                      </p>
                    </div>
                    <div>
                      <span
                        :class="{
                          'bg-green-100 text-green-800': activity.type === 'credit',
                          'bg-red-100 text-red-800': activity.type === 'debit'
                        }"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      >
                        {{ activity.type === 'credit' ? '+' : '-' }}${{ activity.amount }}
                      </span>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <!-- Task Progress -->
          <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-900">Task Progress</h4>
            <div class="mt-2">
              <div class="bg-gray-200 rounded-full overflow-hidden">
                <div
                  class="h-2 bg-indigo-600 rounded-full"
                  :style="{ width: `${taskProgress}%` }"
                ></div>
              </div>
              <div class="mt-2 flex justify-between text-sm text-gray-500">
                <div>{{ completedTasks }} completed</div>
                <div>{{ pendingTasks }} pending</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { format } from 'date-fns';
import axios from 'axios';

export default {
  name: 'ChildDetailsModal',
  props: {
    child: {
      type: Object,
      required: true
    }
  },

  setup(props, { emit }) {
    const loading = ref(false);
    const newRate = ref(props.child.weekly_allowance_rate);
    const recentActivity = ref([]);
    const completedTasks = ref(0);
    const pendingTasks = ref(0);
    const totalPoints = ref(0);

    const taskProgress = computed(() => {
      const total = completedTasks.value + pendingTasks.value;
      return total === 0 ? 0 : (completedTasks.value / total) * 100;
    });

    const fetchActivity = async () => {
      try {
        const response = await axios.get(`/api/allowance/history`, {
          params: { child_id: props.child.id }
        });
        recentActivity.value = response.data.transactions.slice(0, 5);
      } catch (error) {
        console.error('Failed to fetch activity:', error);
      }
    };

    const fetchTaskStats = async () => {
      try {
        const response = await axios.get(`/api/tasks/stats`, {
          params: { child_id: props.child.id }
        });
        completedTasks.value = response.data.completed;
        pendingTasks.value = response.data.pending;
        totalPoints.value = response.data.total_points;
      } catch (error) {
        console.error('Failed to fetch task stats:', error);
      }
    };

    const updateRate = async () => {
      loading.value = true;
      try {
        await emit('update-rate', props.child.id, newRate.value);
      } finally {
        loading.value = false;
      }
    };

    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy');
    };

    onMounted(() => {
      fetchActivity();
      fetchTaskStats();
    });

    return {
      loading,
      newRate,
      recentActivity,
      completedTasks,
      pendingTasks,
      totalPoints,
      taskProgress,
      updateRate,
      formatDate
    };
  }
};
</script>
