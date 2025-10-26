<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-3xl font-bold text-gray-900">My Dashboard</h1>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats Overview -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Current Balance -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Current Balance</dt>
            <dd class="mt-1 text-3xl font-semibold text-indigo-600">${{ userStats.balance }}</dd>
          </div>
        </div>

        <!-- Weekly Allowance -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Weekly Allowance</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">${{ userStats.weeklyRate }}</dd>
          </div>
        </div>

        <!-- Total Points -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Total Points</dt>
            <dd class="mt-1 text-3xl font-semibold text-green-600">{{ userStats.totalPoints }}</dd>
          </div>
        </div>

        <!-- Tasks Completed -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Tasks Completed</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ userStats.tasksCompleted }}</dd>
          </div>
        </div>
      </div>

      <!-- Current Tasks -->
      <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-medium text-gray-900">My Tasks</h2>
            
            <!-- Task Progress -->
            <div class="mt-4">
              <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                  <div>
                    <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-200">
                      Progress
                    </span>
                  </div>
                  <div class="text-right">
                    <span class="text-xs font-semibold inline-block text-indigo-600">
                      {{ taskProgress }}%
                    </span>
                  </div>
                </div>
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200">
                  <div
                    :style="{ width: `${taskProgress}%` }"
                    class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"
                  ></div>
                </div>
              </div>
            </div>

            <!-- Task List -->
            <div class="mt-6 space-y-4">
              <div v-for="task in currentTasks" :key="task.id" class="bg-gray-50 shadow-sm rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900">{{ task.name }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ task.description }}</p>
                    <div class="mt-2 flex items-center space-x-4">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="{
                          'bg-yellow-100 text-yellow-800': task.status === 'assigned',
                          'bg-blue-100 text-blue-800': task.status === 'in_progress',
                          'bg-green-100 text-green-800': task.status === 'completed'
                        }"
                      >
                        {{ task.status }}
                      </span>
                      <span class="text-sm text-gray-500">
                        Due: {{ formatDate(task.due_date) }}
                      </span>
                      <span class="text-sm font-medium text-indigo-600">
                        {{ task.points }} points
                      </span>
                    </div>
                  </div>
                  <div class="ml-4 flex-shrink-0">
                    <button
                      v-if="task.status === 'assigned'"
                      @click="startTask(task)"
                      class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                    >
                      Start Task
                    </button>
                    <button
                      v-if="task.status === 'in_progress'"
                      @click="completeTask(task)"
                      class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                    >
                      Complete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-medium text-gray-900">Recent Activity</h2>
            <div class="mt-6 flow-root">
              <ul class="-my-5 divide-y divide-gray-200">
                <li v-for="activity in recentActivity" :key="activity.id" class="py-5">
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
        </div>
      </div>

      <!-- Achievement Progress -->
      <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-medium text-gray-900">My Achievements</h2>
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
              <div v-for="achievement in achievements" :key="achievement.id"
                class="relative bg-white pt-5 px-4 pb-12 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden"
              >
                <dt>
                  <div class="absolute bg-indigo-500 rounded-md p-3">
                    <component
                      :is="achievement.icon"
                      class="h-6 w-6 text-white"
                      aria-hidden="true"
                    />
                  </div>
                  <p class="ml-16 text-sm font-medium text-gray-500 truncate">{{ achievement.name }}</p>
                </dt>
                <dd class="ml-16 pb-6 flex items-baseline sm:pb-7">
                  <p class="text-2xl font-semibold text-gray-900">
                    {{ achievement.progress }}%
                  </p>
                  <div class="absolute bottom-0 inset-x-0 bg-gray-50 px-4 py-4 sm:px-6">
                    <div class="text-sm">
                      <div class="relative pt-1">
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200">
                          <div
                            :style="{ width: `${achievement.progress}%` }"
                            class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"
                          ></div>
                        </div>
                      </div>
                      <p class="font-medium text-gray-500">
                        {{ achievement.description }}
                      </p>
                    </div>
                  </div>
                </dd>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { format } from 'date-fns';
import axios from 'axios';
import {
  AcademicCapIcon,
  SparklesIcon,
  StarIcon,
  TrophyIcon
} from '@heroicons/vue/outline';

export default {
  name: 'ChildDashboard',
  components: {
    AcademicCapIcon,
    SparklesIcon,
    StarIcon,
    TrophyIcon
  },

  setup() {
    const userStats = ref({
      balance: 0,
      weeklyRate: 0,
      totalPoints: 0,
      tasksCompleted: 0
    });

    const currentTasks = ref([]);
    const recentActivity = ref([]);
    const achievements = ref([]);

    const taskProgress = computed(() => {
      const total = currentTasks.value.length;
      if (total === 0) return 0;
      
      const completed = currentTasks.value.filter(
        task => task.status === 'completed'
      ).length;
      
      return Math.round((completed / total) * 100);
    });

    const fetchUserStats = async () => {
      try {
        const response = await axios.get('/api/child/stats');
        userStats.value = response.data;
      } catch (error) {
        console.error('Failed to fetch user stats:', error);
      }
    };

    const fetchCurrentTasks = async () => {
      try {
        const response = await axios.get('/api/tasks/assigned');
        currentTasks.value = response.data;
      } catch (error) {
        console.error('Failed to fetch tasks:', error);
      }
    };

    const fetchRecentActivity = async () => {
      try {
        const response = await axios.get('/api/allowance/history');
        recentActivity.value = response.data.transactions.slice(0, 5);
      } catch (error) {
        console.error('Failed to fetch activity:', error);
      }
    };

    const fetchAchievements = async () => {
      try {
        const response = await axios.get('/api/child/achievements');
        achievements.value = response.data.map(achievement => ({
          ...achievement,
          icon: getAchievementIcon(achievement.type)
        }));
      } catch (error) {
        console.error('Failed to fetch achievements:', error);
      }
    };

    const startTask = async (task) => {
      try {
        await axios.put(`/api/tasks/${task.id}/start`);
        await fetchCurrentTasks();
      } catch (error) {
        console.error('Failed to start task:', error);
      }
    };

    const completeTask = async (task) => {
      try {
        await axios.put(`/api/tasks/${task.id}/complete`);
        await fetchCurrentTasks();
        await fetchUserStats();
      } catch (error) {
        console.error('Failed to complete task:', error);
      }
    };

    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy');
    };

    const getAchievementIcon = (type) => {
      const icons = {
        academic: AcademicCapIcon,
        special: SparklesIcon,
        star: StarIcon,
        trophy: TrophyIcon
      };
      return icons[type] || StarIcon;
    };

    onMounted(() => {
      fetchUserStats();
      fetchCurrentTasks();
      fetchRecentActivity();
      fetchAchievements();
    });

    return {
      userStats,
      currentTasks,
      recentActivity,
      achievements,
      taskProgress,
      startTask,
      completeTask,
      formatDate
    };
  }
};
</script>
