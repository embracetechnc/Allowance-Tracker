<template>
  <div
    class="bg-white shadow rounded-lg overflow-hidden"
    v-swipe="{
      left: () => showActions = true,
      right: () => showActions = false
    }"
  >
    <!-- Task Content -->
    <div
      class="p-4 transform transition-transform"
      :class="{ '-translate-x-20': showActions }"
    >
      <div class="flex items-start">
        <!-- Status Icon -->
        <div class="flex-shrink-0">
          <div
            :class="[
              'h-8 w-8 rounded-full flex items-center justify-center',
              task.status === 'completed' ? 'bg-green-100' : 'bg-gray-100'
            ]"
          >
            <svg
              v-if="task.status === 'completed'"
              class="h-5 w-5 text-green-600"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg
              v-else
              class="h-5 w-5 text-gray-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>

        <!-- Task Details -->
        <div class="ml-4 flex-1">
          <h3 class="text-lg font-medium text-gray-900">{{ task.name }}</h3>
          <p class="mt-1 text-sm text-gray-500">{{ task.description }}</p>
          <div class="mt-2 flex items-center text-sm text-gray-500">
            <!-- Due Date -->
            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Due {{ formatDate(task.due_date) }}</span>

            <!-- Points -->
            <span class="ml-4 flex items-center">
              <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ task.points }} points
            </span>
          </div>
        </div>
      </div>

      <!-- Task Progress (if in progress) -->
      <div v-if="task.status === 'in_progress'" class="mt-4">
        <div class="relative pt-1">
          <div class="flex mb-2 items-center justify-between">
            <div>
              <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-indigo-600 bg-indigo-200">
                In Progress
              </span>
            </div>
            <div class="text-right">
              <span class="text-xs font-semibold inline-block text-indigo-600">
                {{ task.progress }}%
              </span>
            </div>
          </div>
          <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200">
            <div
              :style="{ width: task.progress + '%' }"
              class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Swipe Actions -->
    <div
      class="absolute top-0 right-0 h-full flex items-center transform transition-transform"
      :class="{ 'translate-x-full': !showActions }"
    >
      <!-- Complete/Start Button -->
      <button
        v-if="task.status !== 'completed'"
        @click="$emit(task.status === 'pending' ? 'start-task' : 'complete-task', task)"
        class="h-full px-4 bg-green-500 text-white flex items-center justify-center"
      >
        <span class="sr-only">{{ task.status === 'pending' ? 'Start' : 'Complete' }}</span>
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path
            v-if="task.status === 'pending'"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
          />
          <path
            v-else
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M5 13l4 4L19 7"
          />
        </svg>
      </button>

      <!-- Edit Button -->
      <button
        @click="$emit('edit-task', task)"
        class="h-full px-4 bg-indigo-500 text-white flex items-center justify-center"
      >
        <span class="sr-only">Edit</span>
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
          />
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { format } from 'date-fns';
import swipe from '@/directives/swipe';

export default {
  name: 'TouchTaskCard',
  directives: {
    swipe
  },
  props: {
    task: {
      type: Object,
      required: true
    }
  },
  emits: ['start-task', 'complete-task', 'edit-task'],

  setup() {
    const showActions = ref(false);

    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy');
    };

    return {
      showActions,
      formatDate
    };
  }
};
</script>

<style scoped>
.task-card {
  touch-action: pan-y pinch-zoom;
}
</style>

