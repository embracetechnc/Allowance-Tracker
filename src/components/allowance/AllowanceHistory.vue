<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h3 class="text-lg leading-6 font-medium text-gray-900">
        Allowance History
      </h3>

      <!-- Summary Cards -->
      <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Total Earned</dt>
            <dd class="mt-1 text-3xl font-semibold text-green-600">${{ summary.total_earned }}</dd>
          </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Total Spent</dt>
            <dd class="mt-1 text-3xl font-semibold text-red-600">${{ summary.total_spent }}</dd>
          </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <dt class="text-sm font-medium text-gray-500 truncate">Current Balance</dt>
            <dd class="mt-1 text-3xl font-semibold text-indigo-600">${{ summary.current_balance }}</dd>
          </div>
        </div>
      </div>

      <!-- Transaction History -->
      <div class="mt-8">
        <div class="flex items-center justify-between">
          <h4 class="text-lg font-medium text-gray-900">Transactions</h4>
          <div class="flex space-x-3">
            <select
              v-model="filters.type"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">All Types</option>
              <option value="credit">Credits</option>
              <option value="debit">Debits</option>
            </select>
          </div>
        </div>

        <div class="mt-4 flex flex-col">
          <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
              <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Description</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                      <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Amount</th>
                      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Payment Method</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="transaction in filteredTransactions" :key="transaction.id">
                      <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 sm:pl-6">
                        {{ formatDate(transaction.created_at) }}
                      </td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        {{ transaction.description }}
                      </td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm">
                        <span
                          :class="{
                            'bg-green-100 text-green-800': transaction.type === 'credit',
                            'bg-red-100 text-red-800': transaction.type === 'debit'
                          }"
                          class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        >
                          {{ transaction.type }}
                        </span>
                      </td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-right"
                        :class="{
                          'text-green-600': transaction.type === 'credit',
                          'text-red-600': transaction.type === 'debit'
                        }"
                      >
                        ${{ transaction.amount }}
                      </td>
                      <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        {{ transaction.payment_method || '-' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredTransactions.length === 0" class="text-center py-12">
          <p class="text-sm text-gray-500">No transactions found.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { format } from 'date-fns';

export default {
  name: 'AllowanceHistory',
  props: {
    transactions: {
      type: Array,
      required: true
    },
    summary: {
      type: Object,
      required: true
    }
  },

  setup(props) {
    const filters = ref({
      type: ''
    });

    const filteredTransactions = computed(() => {
      let result = [...props.transactions];
      
      if (filters.value.type) {
        result = result.filter(t => t.type === filters.value.type);
      }

      return result;
    });

    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy');
    };

    return {
      filters,
      filteredTransactions,
      formatDate
    };
  }
};
</script>
