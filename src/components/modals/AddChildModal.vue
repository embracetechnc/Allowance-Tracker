<template>
  <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <div>
          <div class="mt-3 text-center sm:mt-5">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Add New Child
            </h3>
            <div class="mt-2">
              <form>
                <div class="space-y-4">
                  <!-- Name Field -->
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 text-left">
                      Name
                    </label>
                    <div class="mt-1">
                      <input
                        type="text"
                        id="name"
                        v-model="form.name"
                        required
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Enter child's name"
                      />
                    </div>
                  </div>

                  <!-- Email Field -->
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 text-left">
                      Email
                    </label>
                    <div class="mt-1">
                      <input
                        type="email"
                        id="email"
                        v-model="form.email"
                        required
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Enter email address"
                      />
                    </div>
                  </div>

                  <!-- Weekly Allowance Field -->
                  <div>
                    <label for="weekly_allowance" class="block text-sm font-medium text-gray-700 text-left">
                      Weekly Allowance Rate
                    </label>
                    <div class="mt-1">
                      <input
                        type="number"
                        id="weekly_allowance"
                        v-model.number="form.weekly_allowance_rate"
                        required
                        min="0"
                        step="0.01"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="0.00"
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
                      type="button"
                      @click="handleSubmit"
                      class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm"
                      :disabled="loading"
                    >
                      {{ loading ? 'Adding...' : 'Add Child' }}
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
import { ref, reactive } from 'vue';

export default {
  name: 'AddChildModal',
  emits: ['close', 'add-child'],

  setup(props, { emit }) {
    const loading = ref(false);
    const error = ref('');
    const form = reactive({
      name: '',
      email: '',
      weekly_allowance_rate: 0
    });

    const handleSubmit = () => {
      console.log('Add Child button clicked', { formData: form });

      // Validate form
      if (!form.name.trim()) {
        error.value = 'Name is required';
        return;
      }
      if (!form.email.trim()) {
        error.value = 'Email is required';
        return;
      }
      if (form.weekly_allowance_rate < 0) {
        error.value = 'Weekly allowance rate cannot be negative';
        return;
      }

      loading.value = true;
      error.value = '';

      const childData = {
        name: form.name.trim(),
        email: form.email.trim(),
        weekly_allowance_rate: Number(form.weekly_allowance_rate),
        role: 'child'
      };

      console.log('Emitting add-child event with data:', childData);
      emit('add-child', childData);
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