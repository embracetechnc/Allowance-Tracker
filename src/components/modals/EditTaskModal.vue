<template>
  <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <div>
          <div class="mt-3 text-center sm:mt-5">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Edit Task
            </h3>
            <div class="mt-2">
              <form @submit.prevent="handleSubmit">
                <div class="space-y-4">
                  <!-- Task Name -->
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 text-left">
                      Task Name
                    </label>
                    <div class="mt-1">
                      <input
                        type="text"
                        id="name"
                        v-model="form.name"
                        required
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      />
                    </div>
                  </div>

                  <!-- Description -->
                  <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 text-left">
                      Description
                    </label>
                    <div class="mt-1">
                      <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      ></textarea>
                    </div>
                  </div>

                  <!-- Category -->
                  <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 text-left">
                      Category
                    </label>
                    <div class="mt-1">
                      <select
                        id="category"
                        v-model="form.category"
                        required
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      >
                        <option value="chores">Chores</option>
                        <option value="homework">Homework</option>
                        <option value="extra_credit">Extra Credit</option>
                        <option value="behavior">Behavior</option>
                      </select>
                    </div>
                  </div>

                  <!-- Points -->
                  <div>
                    <label for="points" class="block text-sm font-medium text-gray-700 text-left">
                      Points
                    </label>
                    <div class="mt-1">
                      <input
                        type="number"
                        id="points"
                        v-model.number="form.points"
                        required
                        min="0"
                        step="1"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      />
                    </div>
                  </div>

                  <!-- Due Date -->
                  <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 text-left">
                      Due Date
                    </label>
                    <div class="mt-1">
                      <input
                        type="datetime-local"
                        id="due_date"
                        v-model="form.due_date"
                        required
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      />
                    </div>
                  </div>

                  <!-- Error Message -->
                  <div v-if="error" class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                      </div>
                      <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ error }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Buttons -->
                  <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button
                      type="submit"
                      class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm"
                      :disabled="loading"
                    >
                      {{ loading ? 'Saving...' : 'Save Changes' }}
                    </button>
                    <button
                      type="button"
                      class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                      @click="$emit('close')"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';

export default {
  name: 'EditTaskModal',
  props: {
    task: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'update-task'],

  setup(props, { emit }) {
    const loading = ref(false);
    const error = ref('');
    const form = reactive({
      name: '',
      description: '',
      category: '',
      points: 0,
      due_date: ''
    });

    onMounted(() => {
      // Initialize form with task data
      form.name = props.task.name;
      form.description = props.task.description || '';
      form.category = props.task.category;
      form.points = props.task.points;
      form.due_date = props.task.due_date;
    });

    const handleSubmit = async () => {
      try {
        // Validate form
        if (!form.name.trim()) {
          error.value = 'Task name is required';
          return;
        }
        if (!form.category) {
          error.value = 'Category is required';
          return;
        }
        if (form.points < 0) {
          error.value = 'Points cannot be negative';
          return;
        }
        if (!form.due_date) {
          error.value = 'Due date is required';
          return;
        }

        loading.value = true;
        error.value = '';

        const updatedTask = {
          ...props.task,
          name: form.name.trim(),
          description: form.description.trim(),
          category: form.category,
          points: Number(form.points),
          due_date: form.due_date
        };

        console.log('Updating task:', updatedTask);
        emit('update-task', updatedTask);
        emit('close');
      } catch (err) {
        console.error('Failed to update task:', err);
        error.value = err.message || 'Failed to update task. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      error,
      handleSubmit
    };
  }
};
</script>


