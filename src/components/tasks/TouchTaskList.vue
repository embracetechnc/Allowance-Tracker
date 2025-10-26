<template>
  <div class="space-y-4">
    <!-- Task Filters -->
    <div class="flex overflow-x-auto py-2 -mx-4 px-4 sm:px-0 touch-pan-x">
      <div class="flex space-x-2">
        <button
          v-for="filter in filters"
          :key="filter.value"
          @click="currentFilter = filter.value"
          :class="[
            currentFilter === filter.value
              ? 'bg-indigo-100 text-indigo-700'
              : 'bg-white text-gray-500 hover:text-gray-700',
            'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-indigo-500'
          ]"
        >
          {{ filter.label }}
        </button>
      </div>
    </div>

    <!-- Task List -->
    <div class="space-y-4">
      <TouchTaskCard
        v-for="task in filteredTasks"
        :key="task.id"
        :task="task"
        @start-task="$emit('start-task', $event)"
        @complete-task="$emit('complete-task', $event)"
        @edit-task="$emit('edit-task', $event)"
      />
    </div>

    <!-- Empty State -->
    <div
      v-if="filteredTasks.length === 0"
      class="text-center py-12"
    >
      <svg
        class="mx-auto h-12 w-12 text-gray-400"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
        />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks</h3>
      <p class="mt-1 text-sm text-gray-500">
        {{ getEmptyStateMessage }}
      </p>
    </div>

    <!-- Pull to Refresh Indicator -->
    <div
      v-if="isRefreshing"
      class="fixed top-0 left-0 right-0 flex items-center justify-center bg-indigo-500 text-white py-1 transform transition-transform"
      :class="{ '-translate-y-full': !isRefreshing }"
    >
      <svg
        class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
      </svg>
      Refreshing...
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import TouchTaskCard from './TouchTaskCard.vue';
import swipe from '@/directives/swipe';

export default {
  name: 'TouchTaskList',
  components: {
    TouchTaskCard
  },
  directives: {
    swipe
  },
  props: {
    tasks: {
      type: Array,
      required: true
    }
  },
  emits: ['start-task', 'complete-task', 'edit-task', 'refresh'],

  setup(props, { emit }) {
    const currentFilter = ref('all');
    const isRefreshing = ref(false);
    let pullStartY = 0;
    const PULL_THRESHOLD = 60;

    const filters = [
      { label: 'All', value: 'all' },
      { label: 'Pending', value: 'pending' },
      { label: 'In Progress', value: 'in_progress' },
      { label: 'Completed', value: 'completed' }
    ];

    const filteredTasks = computed(() => {
      if (currentFilter.value === 'all') return props.tasks;
      return props.tasks.filter(task => task.status === currentFilter.value);
    });

    const getEmptyStateMessage = computed(() => {
      switch (currentFilter.value) {
        case 'pending':
          return 'No pending tasks to show';
        case 'in_progress':
          return 'No tasks in progress';
        case 'completed':
          return 'No completed tasks yet';
        default:
          return 'No tasks to show';
      }
    });

    // Pull to refresh handlers
    const handleTouchStart = (e) => {
      if (window.scrollY === 0) {
        pullStartY = e.touches[0].clientY;
      }
    };

    const handleTouchMove = (e) => {
      if (pullStartY === 0) return;

      const pullDistance = e.touches[0].clientY - pullStartY;
      if (pullDistance > 0 && pullDistance < PULL_THRESHOLD) {
        document.body.style.transform = `translateY(${pullDistance}px)`;
      }
    };

    const handleTouchEnd = async (e) => {
      if (pullStartY === 0) return;

      const pullDistance = e.changedTouches[0].clientY - pullStartY;
      document.body.style.transform = '';
      pullStartY = 0;

      if (pullDistance > PULL_THRESHOLD) {
        isRefreshing.value = true;
        await emit('refresh');
        isRefreshing.value = false;
      }
    };

    // Add touch event listeners
    if (typeof window !== 'undefined') {
      window.addEventListener('touchstart', handleTouchStart);
      window.addEventListener('touchmove', handleTouchMove);
      window.addEventListener('touchend', handleTouchEnd);
    }

    return {
      currentFilter,
      filters,
      filteredTasks,
      getEmptyStateMessage,
      isRefreshing
    };
  }
};
</script>

<style scoped>
.touch-pan-x {
  touch-action: pan-x pinch-zoom;
}
</style>

