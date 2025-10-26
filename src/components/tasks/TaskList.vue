<template>
  <div class="space-y-6">
    <!-- Task Filters -->
    <div class="bg-white shadow rounded-lg p-4">
      <div class="flex flex-wrap gap-4">
        <div>
          <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
          <select
            id="status"
            v-model="filters.status"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="">All</option>
            <option value="assigned">Assigned</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="verified">Verified</option>
          </select>
        </div>
        <div>
          <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
          <select
            id="category"
            v-model="filters.category"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="">All</option>
            <option value="chores">Chores</option>
            <option value="homework">Homework</option>
            <option value="extra_credit">Extra Credit</option>
            <option value="behavior">Behavior</option>
          </select>
        </div>
        <div v-if="isParent">
          <label for="child" class="block text-sm font-medium text-gray-700">Child</label>
          <select
            id="child"
            v-model="filters.childId"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="">All Children</option>
            <option v-for="child in children" :key="child.id" :value="child.id">
              {{ child.name }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Task List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <ul class="divide-y divide-gray-200">
        <li v-for="task in filteredTasks" :key="task.id" class="px-4 py-4 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-indigo-600 truncate">{{ task.name }}</p>
                <div class="ml-2 flex-shrink-0 flex">
                  <p :class="statusClasses[task.status]" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ task.status }}
                  </p>
                </div>
              </div>
              <div class="mt-2 flex justify-between">
                <div class="sm:flex">
                  <p class="flex items-center text-sm text-gray-500">
                    <span class="truncate">{{ task.description }}</span>
                  </p>
                </div>
                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                  <p class="text-sm text-gray-500">Points: {{ task.points }}</p>
                </div>
              </div>
            </div>
            
            <!-- Task Actions -->
            <div class="ml-6 flex items-center space-x-3">
              <template v-if="isChild">
                <button
                  v-if="task.status === 'assigned'"
                  @click="startTask(task)"
                  class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                >
                  Start
                </button>
                <button
                  v-if="task.status === 'in_progress'"
                  @click="completeTask(task)"
                  class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                >
                  Complete
                </button>
              </template>
              <template v-if="isParent">
                <button
                  v-if="task.status === 'completed'"
                  @click="verifyTask(task)"
                  class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                >
                  Verify
                </button>
                <button
                  v-if="task.status === 'completed'"
                  @click="rejectTask(task)"
                  class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                >
                  Reject
                </button>
              </template>
            </div>
          </div>
        </li>
      </ul>
    </div>

    <!-- No Tasks Message -->
    <div v-if="filteredTasks.length === 0" class="text-center py-12">
      <p class="text-sm text-gray-500">No tasks found matching your filters.</p>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

export default {
  name: 'TaskList',
  props: {
    tasks: {
      type: Array,
      required: true
    },
    children: {
      type: Array,
      default: () => []
    }
  },
  
  setup(props, { emit }) {
    const authStore = useAuthStore();
    const user = computed(() => authStore.user);
    
    const filters = ref({
      status: '',
      category: '',
      childId: ''
    });

    const statusClasses = {
      assigned: 'bg-gray-100 text-gray-800',
      in_progress: 'bg-yellow-100 text-yellow-800',
      completed: 'bg-blue-100 text-blue-800',
      verified: 'bg-green-100 text-green-800',
      rejected: 'bg-red-100 text-red-800'
    };

    const isParent = computed(() => user.value?.role === 'parent');
    const isChild = computed(() => user.value?.role === 'child');

    const filteredTasks = computed(() => {
      return props.tasks.filter(task => {
        if (filters.value.status && task.status !== filters.value.status) return false;
        if (filters.value.category && task.category !== filters.value.category) return false;
        if (filters.value.childId && task.child_id !== filters.value.childId) return false;
        return true;
      });
    });

    const startTask = (task) => {
      emit('start-task', task);
    };

    const completeTask = (task) => {
      emit('complete-task', task);
    };

    const verifyTask = (task) => {
      emit('verify-task', task);
    };

    const rejectTask = (task) => {
      emit('reject-task', task);
    };

    return {
      filters,
      filteredTasks,
      statusClasses,
      isParent,
      isChild,
      startTask,
      completeTask,
      verifyTask,
      rejectTask
    };
  }
};
</script>
