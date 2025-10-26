<template>
  <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
        <div>
          <div class="flex items-center justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              Manage {{ child.name }}'s Allowance
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

          <!-- Period Selection -->
          <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-900">Select Period</h4>
            <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input
                  type="date"
                  id="start_date"
                  name="start_date"
                  v-model="period.start_date"
                  :class="getFieldStateClasses('start_date', errors, touched)"
                  @blur="handleFieldBlur('start_date')"
                  @input="handleFieldInput('start_date')"
                />
                <div class="mt-1" v-if="touched.start_date">
                  <p :class="['text-sm', getValidationMessage('start_date', errors, validationSchema).color]">
                    {{ getValidationMessage('start_date', errors, validationSchema).message }}
                  </p>
                </div>
              </div>
              <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input
                  type="date"
                  id="end_date"
                  name="end_date"
                  v-model="period.end_date"
                  :class="getFieldStateClasses('end_date', errors, touched)"
                  @blur="handleFieldBlur('end_date')"
                  @input="handleFieldInput('end_date')"
                />
                <div class="mt-1" v-if="touched.end_date">
                  <p :class="['text-sm', getValidationMessage('end_date', errors, validationSchema).color]">
                    {{ getValidationMessage('end_date', errors, validationSchema).message }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Quick Period Selectors -->
            <div class="mt-4 flex space-x-3">
              <button
                v-for="(label, days) in quickPeriods"
                :key="days"
                @click="setQuickPeriod(days)"
                class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                {{ label }}
              </button>
            </div>
          </div>

          <!-- Calculate Button -->
          <div class="mt-5">
            <button
              @click="calculateAllowance"
              :disabled="loading"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              {{ loading ? 'Calculating...' : 'Calculate Allowance' }}
            </button>
          </div>

          <!-- Calculation Results -->
          <div v-if="calculation" class="mt-6">
            <div class="bg-gray-50 px-4 py-5 sm:rounded-lg sm:p-6">
              <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                  <h3 class="text-lg font-medium leading-6 text-gray-900">Summary</h3>
                  <div class="mt-4 space-y-2">
                    <p class="text-sm text-gray-500">Tasks Completed: {{ calculation.tasks_completed }}</p>
                    <p class="text-sm text-gray-500">Total Points: {{ calculation.total_points }}</p>
                  </div>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                  <div class="grid grid-cols-1 gap-4">
                    <div class="bg-white px-4 py-5 shadow sm:rounded-lg sm:p-6">
                      <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Base Allowance</dt>
                          <dd class="mt-1 text-2xl font-semibold text-gray-900">${{ calculation.base_allowance }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Bonus Amount</dt>
                          <dd class="mt-1 text-2xl font-semibold text-gray-900">${{ calculation.bonus_amount }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                          <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                          <dd class="mt-1 text-3xl font-semibold text-indigo-600">${{ calculation.total_amount }}</dd>
                        </div>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Task List -->
              <div class="mt-6">
                <h4 class="text-lg font-medium text-gray-900">Completed Tasks</h4>
                <ul class="mt-3 divide-y divide-gray-200">
                  <li v-for="task in calculation.tasks" :key="task.name" class="py-3">
                    <div class="flex items-center justify-between">
                      <div>
                        <p class="text-sm font-medium text-gray-900">{{ task.name }}</p>
                        <p class="text-sm text-gray-500">
                          Completed: {{ formatDate(task.completed_at) }}
                        </p>
                      </div>
                      <div class="text-sm font-medium text-indigo-600">
                        {{ task.points_earned }} points
                      </div>
                    </div>
                  </li>
                </ul>
              </div>

              <!-- Payment Method -->
              <div class="mt-6">
                <label for="payment_method" class="block text-sm font-medium text-gray-700">
                  Payment Method
                </label>
                <select
                  id="payment_method"
                  name="payment_method"
                  v-model="paymentMethod"
                  :class="getFieldStateClasses('payment_method', errors, touched)"
                  @blur="handleFieldBlur('payment_method')"
                  @change="handleFieldInput('payment_method')"
                >
                  <option value="">Select Payment Method</option>
                  <option value="cash">Cash</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="cash_app">Cash App</option>
                </select>
                <div class="mt-1" v-if="touched.payment_method">
                  <p :class="['text-sm', getValidationMessage('payment_method', errors, validationSchema).color]">
                    {{ getValidationMessage('payment_method', errors, validationSchema).message }}
                  </p>
                </div>
              </div>

              <!-- Pay Button -->
              <div class="mt-6">
                <button
                  @click="payAllowance"
                  :disabled="loading || !paymentMethod"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  {{ loading ? 'Processing...' : 'Pay Allowance' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="mt-4 bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-red-700">{{ error }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, reactive } from 'vue';
import { format, subDays, isAfter, isBefore, isValid } from 'date-fns';
import { fieldRequirements, validateField, validateForm, getFieldStateClasses, getValidationMessage } from '@/utils/validation';

export default {
  name: 'AllowanceModal',
  props: {
    child: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'calculate', 'pay'],

  setup(props, { emit }) {
    const loading = ref(false);
    const error = ref('');
    const calculation = ref(null);
    const paymentMethod = ref('');
    const touched = reactive({});
    const errors = reactive({});

    const period = ref({
      start_date: '',
      end_date: ''
    });

    const validationSchema = {
      start_date: {
        required: true,
        message: 'Please select a start date',
        hint: 'When should the allowance period start?',
        validate: (value) => {
          if (!value) return false;
          const date = new Date(value);
          return isValid(date) && isBefore(date, new Date());
        }
      },
      end_date: {
        required: true,
        message: 'Please select an end date',
        hint: 'When should the allowance period end?',
        validate: (value, form) => {
          if (!value) return false;
          const endDate = new Date(value);
          const startDate = new Date(form.start_date);
          return isValid(endDate) && 
                 isBefore(endDate, new Date()) && 
                 (!form.start_date || isAfter(endDate, startDate));
        }
      },
      payment_method: {
        required: (form) => !!calculation.value,
        message: 'Please select a payment method',
        hint: 'How would you like to pay the allowance?'
      }
    };

    const quickPeriods = {
      7: 'Last Week',
      14: 'Last 2 Weeks',
      30: 'Last Month'
    };

    const setQuickPeriod = (days) => {
      const end = new Date();
      const start = subDays(end, days);
      
      period.value = {
        start_date: format(start, 'yyyy-MM-dd'),
        end_date: format(end, 'yyyy-MM-dd')
      };
    };

    const validateField = (fieldName, value) => {
      touched[fieldName] = true;
      const result = validateField(value, validationSchema[fieldName]);
      if (!result.isValid) {
        errors[fieldName] = result.errors;
      } else {
        delete errors[fieldName];
      }
      return result.isValid;
    };

    const handleFieldBlur = (fieldName) => {
      validateField(fieldName, fieldName === 'payment_method' ? paymentMethod.value : period.value[fieldName]);
    };

    const handleFieldInput = (fieldName) => {
      if (touched[fieldName]) {
        validateField(fieldName, fieldName === 'payment_method' ? paymentMethod.value : period.value[fieldName]);
      }
    };

    const calculateAllowance = async () => {
      // Mark date fields as touched
      touched.start_date = true;
      touched.end_date = true;

      // Validate dates
      const startValid = validateField('start_date', period.value.start_date);
      const endValid = validateField('end_date', period.value.end_date);

      if (!startValid || !endValid) {
        error.value = 'Please fix the date errors before calculating';
        return;
      }

      loading.value = true;
      error.value = '';

      try {
        const result = await emit('calculate', {
          child_id: props.child.id,
          ...period.value
        });
        calculation.value = result;
      } catch (err) {
        error.value = err.message || 'Failed to calculate allowance';
      } finally {
        loading.value = false;
      }
    };

    const payAllowance = async () => {
      // Mark payment method as touched
      touched.payment_method = true;

      // Validate payment method
      const paymentValid = validateField('payment_method', paymentMethod.value);

      if (!paymentValid) {
        error.value = 'Please select a valid payment method';
        return;
      }

      loading.value = true;
      error.value = '';

      try {
        await emit('pay', {
          child_id: props.child.id,
          amount: calculation.value.total_amount,
          period_start: period.value.start_date,
          period_end: period.value.end_date,
          payment_method: paymentMethod.value
        });
      } catch (err) {
        error.value = err.message || 'Failed to process payment';
      } finally {
        loading.value = false;
      }
    };

    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy');
    };

    return {
      loading,
      error,
      period,
      calculation,
      paymentMethod,
      quickPeriods,
      errors,
      touched,
      validationSchema,
      setQuickPeriod,
      calculateAllowance,
      payAllowance,
      formatDate,
      handleFieldBlur,
      handleFieldInput,
      getFieldStateClasses,
      getValidationMessage
    };
  }
};
</script>

