<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h3 class="text-lg leading-6 font-medium text-gray-900">
        {{ isEditing ? 'Edit Task' : 'Create New Task' }}
      </h3>
      
      <form @submit.prevent="handleSubmit" class="mt-5 space-y-4">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Task Name</label>
          <input
            type="text"
            id="name"
            v-model="form.name"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :placeholder="isEditing ? 'Edit task name' : 'Enter task name'"
          />
        </div>

        <div>
          <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :placeholder="isEditing ? 'Edit task description' : 'Enter task description'"
          ></textarea>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
            <select
              id="category"
              v-model="form.category"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">Select Category</option>
              <option value="chores">Chores</option>
              <option value="homework">Homework</option>
              <option value="extra_credit">Extra Credit</option>
              <option value="behavior">Behavior</option>
            </select>
          </div>

          <div>
            <label for="points" class="block text-sm font-medium text-gray-700">Points</label>
            <input
              type="number"
              id="points"
              v-model.number="form.points"
              required
              min="0"
              step="0.5"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
        </div>

        <div class="flex items-start">
          <div class="flex items-center h-5">
            <input
              id="is_recurring"
              type="checkbox"
              v-model="form.is_recurring"
              class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
            />
          </div>
          <div class="ml-3 text-sm">
            <label for="is_recurring" class="font-medium text-gray-700">Recurring Task</label>
            <p class="text-gray-500">Check if this task repeats on a schedule</p>
          </div>
        </div>

        <div v-if="form.is_recurring">
          <label for="recurrence_pattern" class="block text-sm font-medium text-gray-700">Recurrence Pattern</label>
          <select
            id="recurrence_pattern"
            v-model="form.recurrence_pattern"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="">Select Pattern</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>

        <div v-if="!isEditing" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label for="child" class="block text-sm font-medium text-gray-700">Assign To</label>
            <select
              id="child"
              v-model="form.child_id"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">Select Child</option>
              <option v-for="child in children" :key="child.id" :value="child.id">
                {{ child.name }}
              </option>
            </select>
          </div>

          <div v-if="form.child_id">
            <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
            <input
              type="datetime-local"
              id="due_date"
              v-model="form.due_date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="$emit('cancel')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            {{ loading ? 'Saving...' : (isEditing ? 'Update Task' : 'Create Task') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'TaskForm',
  props: {
    task: {
      type: Object,
      default: null
    },
    children: {
      type: Array,
      default: () => []
    }
  },

  setup(props, { emit }) {
    const loading = ref(false);
    const isEditing = computed(() => !!props.task);

    const form = ref({
      name: '',
      description: '',
      category: '',
      points: 0,
      is_recurring: false,
      recurrence_pattern: '',
      child_id: '',
      due_date: ''
    });

    // If editing, populate form with task data
    if (props.task) {
      form.value = {
        name: props.task.name,
        description: props.task.description || '',
        category: props.task.category,
        points: props.task.points,
        is_recurring: props.task.is_recurring,
        recurrence_pattern: props.task.recurrence_pattern || ''
      };
    }

    const handleSubmit = async () => {
      loading.value = true;
      try {
        const taskData = { ...form.value };
        
        // Only include assignment data for new tasks
        if (!isEditing.value && taskData.child_id) {
          taskData.assignment = {
            child_id: taskData.child_id,
            due_date: taskData.due_date
          };
        }
        
        // Remove assignment fields from task data
        delete taskData.child_id;
        delete taskData.due_date;

        emit('submit', taskData);
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      isEditing,
      handleSubmit
    };
  }
};
</script>
