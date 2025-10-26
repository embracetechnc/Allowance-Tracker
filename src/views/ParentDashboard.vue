<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Parent Dashboard</h1>
          <button
            @click="showCreateTaskModal = true"
            class="mt-4 sm:mt-0 w-full sm:w-auto inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
            data-test="create-task"
          >
            Create Task
          </button>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <!-- Children Overview -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="child in validChildren" :key="child.id" class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-xl font-bold text-indigo-600">{{ child?.name?.charAt(0) || '?' }}</span>
                  </div>
                </div>
                <div class="ml-5">
                  <h3 class="text-lg font-medium text-gray-900">{{ child?.name || 'Unnamed Child' }}</h3>
                  <p class="text-sm text-gray-500">{{ child?.email }}</p>
                </div>
              </div>
              <div class="mt-4 sm:mt-0 sm:ml-auto">
                <div class="text-center sm:text-right">
                  <p class="text-2xl font-semibold text-gray-900">${{ child?.allowance_balance?.toFixed(2) || '0.00' }}</p>
                  <p class="text-sm text-gray-500">Weekly Rate: ${{ child?.weekly_allowance_rate?.toFixed(2) || '0.00' }}</p>
                </div>
              </div>
            </div>
            <div class="mt-5">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button
                  @click="viewChild(child)"
                  class="w-full inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
                  data-test="view-details"
                >
                  <span class="sm:hidden">View</span>
                  <span class="hidden sm:inline">View Details</span>
                </button>
                <button
                  @click="manageAllowance(child)"
                  class="w-full inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200"
                  data-test="manage-allowance"
                >
                  <span class="sm:hidden">Allowance</span>
                  <span class="hidden sm:inline">Manage Allowance</span>
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
          <div class="flex flex-col items-center justify-center h-full min-h-[200px] sm:min-h-0">
            <svg
              class="h-12 w-12 text-gray-400"
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
            <span class="mt-2 text-sm font-medium text-gray-900">Add Child</span>
            <p class="mt-1 text-sm text-gray-500 text-center hidden sm:block">
              Add a new child to manage their tasks and allowance
            </p>
          </div>
        </div>
      </div>

      <!-- Task Management Section -->
      <div class="mt-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-xl font-semibold text-gray-900">Recent Tasks</h2>
            <p class="mt-2 text-sm text-gray-700">
              Overview of recently assigned and completed tasks.
            </p>
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
          <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            <div class="bg-white overflow-hidden shadow rounded-lg p-4 sm:p-6">
              <div class="flex flex-row sm:flex-col items-baseline sm:items-start justify-between">
                <dt class="text-sm font-medium text-gray-500">Tasks Completed</dt>
                <dd class="text-2xl sm:text-3xl font-semibold text-gray-900 sm:mt-1">{{ weeklyStats.tasksCompleted }}</dd>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg p-4 sm:p-6">
              <div class="flex flex-row sm:flex-col items-baseline sm:items-start justify-between">
                <dt class="text-sm font-medium text-gray-500">Points Earned</dt>
                <dd class="text-2xl sm:text-3xl font-semibold text-gray-900 sm:mt-1">{{ weeklyStats.pointsEarned }}</dd>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg p-4 sm:p-6">
              <div class="flex flex-row sm:flex-col items-baseline sm:items-start justify-between">
                <dt class="text-sm font-medium text-gray-500">Allowance Paid</dt>
                <dd class="text-2xl sm:text-3xl font-semibold text-gray-900 sm:mt-1">${{ weeklyStats.allowancePaid }}</dd>
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

    <!-- Edit Task Modal -->
    <EditTaskModal
      v-if="selectedTask"
      :task="selectedTask"
      @close="selectedTask = null"
      @update-task="updateTask"
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
import { ref, computed, onMounted } from 'vue';
import TaskList from '@/components/tasks/TaskList.vue';
import AddChildModal from '@/components/modals/AddChildModal.vue';
import CreateTaskModal from '@/components/modals/CreateTaskModal.vue';
import ChildDetailsModal from '@/components/modals/ChildDetailsModal.vue';
import AllowanceModal from '@/components/modals/AllowanceModal.vue';
import EditTaskModal from '@/components/modals/EditTaskModal.vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

export default {
  name: 'ParentDashboard',
  components: {
    TaskList,
    AddChildModal,
    CreateTaskModal,
    ChildDetailsModal,
    AllowanceModal,
    EditTaskModal
  },

  setup() {
    const authStore = useAuthStore();
    const children = ref([]);
    const recentTasks = ref([]);

    const validChildren = computed(() => {
      return children.value.filter(child => 
        child && 
        child.id && 
        child.name && 
        child.name.trim() !== '' &&
        typeof child.allowance_balance !== 'undefined' &&
        typeof child.weekly_allowance_rate !== 'undefined'
      );
    });
    const showAddChildModal = ref(false);
    const showCreateTaskModal = ref(false);
    const selectedChild = ref(null);
    const allowanceChild = ref(null);
    const selectedTask = ref(null);
    const weeklyStats = ref({
      tasksCompleted: 0,
      pointsEarned: 0,
      allowancePaid: 0
    });

    const fetchChildren = () => {
      // Initialize with default children since we don't have a backend yet
      children.value = [
        {
          id: 1,
          name: 'Hannah',
          email: 'hannahastokes@icloud.com',
          allowance_balance: 0,
          weekly_allowance_rate: 20.00,
          role: 'child'
        },
        {
          id: 2,
          name: 'Haven',
          email: 'havenastokes@icloud.com',
          allowance_balance: 0,
          weekly_allowance_rate: 20.00,
          role: 'child'
        }
      ];
      console.log('Children initialized:', children.value);
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
        console.log('Adding child with data:', childData);
        const newChild = {
          id: children.value.length + 1,
          name: childData.name,
          email: childData.email,
          allowance_balance: 0,
          weekly_allowance_rate: childData.weekly_allowance_rate,
          role: 'child'
        };
        
        children.value = [...children.value, newChild];
        showAddChildModal.value = false;
        console.log('Updated children list:', children.value);
      } catch (error) {
        console.error('Failed to add child:', error);
        throw error;
      }
    };

    const createTask = async (taskData) => {
      try {
        console.log('Creating task with data:', taskData);
        // Add task to recent tasks
        recentTasks.value.unshift(taskData);
        showCreateTaskModal.value = false;
        
        // Update weekly stats
        weeklyStats.value = {
          tasksCompleted: weeklyStats.value.tasksCompleted,
          pointsEarned: weeklyStats.value.pointsEarned,
          allowancePaid: weeklyStats.value.allowancePaid
        };
        
        console.log('Task created successfully:', taskData);
      } catch (error) {
        console.error('Failed to create task:', error);
        throw error;
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

    const updateTask = (updatedTask) => {
      const index = recentTasks.value.findIndex(task => task.id === updatedTask.id);
      if (index !== -1) {
        recentTasks.value[index] = updatedTask;
        console.log('Task updated:', updatedTask);
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
      validChildren,
      recentTasks,
      weeklyStats,
      showAddChildModal,
      showCreateTaskModal,
      selectedChild,
      selectedTask,
      allowanceChild,
      addChild,
      createTask,
      verifyTask,
      rejectTask,
      viewChild,
      manageAllowance,
      updateAllowanceRate,
      calculateAllowance,
      payAllowance,
      updateTask
    };
  }
};
</script>
