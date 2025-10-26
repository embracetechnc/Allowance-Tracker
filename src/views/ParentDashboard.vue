<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-3xl font-bold text-gray-900">Parent Dashboard</h1>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Children Overview -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="child in children" :key="child.id" class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                  <span class="text-xl font-bold text-indigo-600">{{ child.name.charAt(0) }}</span>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">{{ child.name }}</dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">${{ child.allowance_balance }}</div>
                    <div class="ml-2 flex items-baseline text-sm font-semibold">
                      Weekly Rate: ${{ child.weekly_allowance_rate }}
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
            <div class="mt-5">
              <div class="grid grid-cols-2 gap-4">
                <button
                  @click="viewChild(child)"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
                >
                  View Details
                </button>
                <button
                  @click="manageAllowance(child)"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200"
                >
                  Manage Allowance
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Add Child Card -->
        <div
          @click="showAddChildModal = true"
          class="bg-gray-50 overflow-hidden shadow rounded-lg border-2 border-dashed border-gray-300 p-6 cursor-pointer hover:bg-gray-100"
        >
          <div class="flex items-center justify-center h-full">
            <button type="button" class="relative block w-full">
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
                  d="M12 4v16m8-8H4"
                />
              </svg>
              <span class="mt-2 block text-sm font-medium text-gray-900">Add Child</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Task Management Section -->
      <div class="mt-8">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto">
            <h2 class="text-xl font-semibold text-gray-900">Recent Tasks</h2>
            <p class="mt-2 text-sm text-gray-700">
              Overview of recently assigned and completed tasks.
            </p>
          </div>
          <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <button
              @click="showCreateTaskModal = true"
              class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
            >
              Create Task
            </button>
          </div>
        </div>

        <TaskList
          :tasks="recentTasks"
          :children="children"
          @verify-task="verifyTask"
          @reject-task="rejectTask"
        />
      </div>

      <!-- Weekly Summary -->
      <div class="mt-8 bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg font-medium leading-6 text-gray-900">Weekly Summary</h3>
          <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500">Tasks Completed</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ weeklyStats.tasksCompleted }}</dd>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500">Points Earned</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ weeklyStats.pointsEarned }}</dd>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
              <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500">Allowance Paid</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">${{ weeklyStats.allowancePaid }}</dd>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Add Child Modal -->
    <AddChildModal
      v-if="showAddChildModal"
      @close="showAddChildModal = false"
      @add-child="addChild"
    />

    <!-- Create Task Modal -->
    <CreateTaskModal
      v-if="showCreateTaskModal"
      :children="children"
      @close="showCreateTaskModal = false"
      @create-task="createTask"
    />

    <!-- Child Details Modal -->
    <ChildDetailsModal
      v-if="selectedChild"
      :child="selectedChild"
      @close="selectedChild = null"
      @update-rate="updateAllowanceRate"
    />

    <!-- Allowance Management Modal -->
    <AllowanceModal
      v-if="allowanceChild"
      :child="allowanceChild"
      @close="allowanceChild = null"
      @calculate="calculateAllowance"
      @pay="payAllowance"
    />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import TaskList from '@/components/tasks/TaskList.vue';
import AddChildModal from '@/components/modals/AddChildModal.vue';
import CreateTaskModal from '@/components/modals/CreateTaskModal.vue';
import ChildDetailsModal from '@/components/modals/ChildDetailsModal.vue';
import AllowanceModal from '@/components/modals/AllowanceModal.vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

export default {
  name: 'ParentDashboard',
  components: {
    TaskList,
    AddChildModal,
    CreateTaskModal,
    ChildDetailsModal,
    AllowanceModal
  },

  setup() {
    const authStore = useAuthStore();
    const children = ref([]);
    const recentTasks = ref([]);
    const showAddChildModal = ref(false);
    const showCreateTaskModal = ref(false);
    const selectedChild = ref(null);
    const allowanceChild = ref(null);
    const weeklyStats = ref({
      tasksCompleted: 0,
      pointsEarned: 0,
      allowancePaid: 0
    });

    const fetchChildren = async () => {
      try {
        const response = await axios.get('/api/children');
        children.value = response.data;
      } catch (error) {
        console.error('Failed to fetch children:', error);
      }
    };

    const fetchRecentTasks = async () => {
      try {
        const response = await axios.get('/api/tasks', {
          params: { limit: 10, sort: 'recent' }
        });
        recentTasks.value = response.data;
      } catch (error) {
        console.error('Failed to fetch tasks:', error);
      }
    };

    const fetchWeeklyStats = async () => {
      try {
        const response = await axios.get('/api/parent/weekly-stats');
        weeklyStats.value = response.data;
      } catch (error) {
        console.error('Failed to fetch weekly stats:', error);
      }
    };

    const addChild = async (childData) => {
      try {
        console.log('Sending child data to server:', childData);
        const response = await axios.post('/api/children', childData);
        children.value.push(response.data);
        showAddChildModal.value = false;
        // Refresh children list
        await fetchChildren();
        // Refresh weekly stats
        await fetchWeeklyStats();
      } catch (error) {
        console.error('Failed to add child:', error);
        throw error; // Propagate error to modal
      }
    };

    const createTask = async (taskData) => {
      try {
        console.log('Creating task with data:', taskData);
        const response = await axios.post('/api/tasks', taskData);
        recentTasks.value.unshift(response.data);
        showCreateTaskModal.value = false;
        // Refresh the task list
        await fetchRecentTasks();
        // Update weekly stats
        await fetchWeeklyStats();
      } catch (error) {
        console.error('Failed to create task:', error);
        throw error; // Propagate error to modal
      }
    };

    const verifyTask = async (task) => {
      try {
        await axios.put(`/api/tasks/${task.id}/verify`, {
          action: 'verify'
        });
        await fetchRecentTasks();
        await fetchWeeklyStats();
      } catch (error) {
        console.error('Failed to verify task:', error);
      }
    };

    const rejectTask = async (task, reason) => {
      try {
        await axios.put(`/api/tasks/${task.id}/verify`, {
          action: 'reject',
          rejection_reason: reason
        });
        await fetchRecentTasks();
      } catch (error) {
        console.error('Failed to reject task:', error);
      }
    };

    const viewChild = (child) => {
      selectedChild.value = child;
    };

    const manageAllowance = (child) => {
      allowanceChild.value = child;
    };

    const updateAllowanceRate = async (childId, rate) => {
      try {
        await axios.put(`/api/allowance/rate`, {
          child_id: childId,
          weekly_rate: rate
        });
        await fetchChildren();
      } catch (error) {
        console.error('Failed to update allowance rate:', error);
      }
    };

    const calculateAllowance = async (data) => {
      try {
        const response = await axios.post('/api/allowance/calculate', data);
        return response.data;
      } catch (error) {
        console.error('Failed to calculate allowance:', error);
        throw error;
      }
    };

    const payAllowance = async (data) => {
      try {
        await axios.post('/api/allowance/payout', data);
        await fetchChildren();
        await fetchWeeklyStats();
      } catch (error) {
        console.error('Failed to pay allowance:', error);
        throw error;
      }
    };

    onMounted(() => {
      fetchChildren();
      fetchRecentTasks();
      fetchWeeklyStats();
    });

    return {
      children,
      recentTasks,
      weeklyStats,
      showAddChildModal,
      showCreateTaskModal,
      selectedChild,
      allowanceChild,
      addChild,
      createTask,
      verifyTask,
      rejectTask,
      viewChild,
      manageAllowance,
      updateAllowanceRate,
      calculateAllowance,
      payAllowance
    };
  }
};
</script>
