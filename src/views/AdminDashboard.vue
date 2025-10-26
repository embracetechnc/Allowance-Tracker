<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
      </div>
    </header>
    <main>
      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- User Management Section -->
        <div class="px-4 py-6 sm:px-0">
          <div class="border-4 border-dashed border-gray-200 rounded-lg p-4">
            <h2 class="text-xl font-semibold mb-4">User Management</h2>
            
            <!-- User List -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
              <ul class="divide-y divide-gray-200">
                <li v-for="user in allowedEmails" :key="user.email" class="px-4 py-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-sm font-medium text-indigo-600">{{ user.email }}</p>
                      <p class="text-sm text-gray-500">Role: {{ user.role }}</p>
                      <p class="text-sm text-gray-500">Default Role: {{ user.defaultRole }}</p>
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="editUser(user)"
                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
                      >
                        Edit
                      </button>
                      <button
                        v-if="user.email !== currentUserEmail"
                        @click="removeUser(user.email)"
                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200"
                      >
                        Remove
                      </button>
                    </div>
                  </div>
                </li>
              </ul>
            </div>

            <!-- Add New User -->
            <div class="mt-6">
              <h3 class="text-lg font-medium mb-4">Add New User</h3>
              <form @submit.prevent="addNewUser" class="space-y-4">
                <div>
                  <label for="newEmail" class="block text-sm font-medium text-gray-700">Email</label>
                  <input
                    type="email"
                    id="newEmail"
                    v-model="newUser.email"
                    required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                </div>
                <div>
                  <label for="newRole" class="block text-sm font-medium text-gray-700">Role</label>
                  <select
                    id="newRole"
                    v-model="newUser.role"
                    required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  >
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>
                <div>
                  <label for="newDefaultRole" class="block text-sm font-medium text-gray-700">Default Role</label>
                  <select
                    id="newDefaultRole"
                    v-model="newUser.defaultRole"
                    required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  >
                    <option value="parent">Parent</option>
                    <option value="child">Child</option>
                  </select>
                </div>
                <div>
                  <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    Add User
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Edit User Modal -->
    <div v-if="showEditModal" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Edit User
            </h3>
            <div class="mt-2">
              <form @submit.prevent="updateUser" class="space-y-4">
                <div>
                  <label for="editRole" class="block text-sm font-medium text-gray-700">Role</label>
                  <select
                    id="editRole"
                    v-model="editingUser.role"
                    required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  >
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>
                <div>
                  <label for="editDefaultRole" class="block text-sm font-medium text-gray-700">Default Role</label>
                  <select
                    id="editDefaultRole"
                    v-model="editingUser.defaultRole"
                    required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  >
                    <option value="parent">Parent</option>
                    <option value="child">Child</option>
                  </select>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                  <button
                    type="submit"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm"
                  >
                    Save
                  </button>
                  <button
                    type="button"
                    @click="showEditModal = false"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                  >
                    Cancel
                  </button>
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
import { ref, computed } from 'vue';
import { useAllowedEmailsStore } from '@/stores/allowedEmails';
import { useAuthStore } from '@/stores/auth';

export default {
  name: 'AdminDashboard',
  setup() {
    const allowedEmailsStore = useAllowedEmailsStore();
    const authStore = useAuthStore();

    const allowedEmails = computed(() => allowedEmailsStore.allowedEmails);
    const currentUserEmail = computed(() => authStore.user?.email);

    const newUser = ref({
      email: '',
      role: 'user',
      defaultRole: 'child'
    });

    const showEditModal = ref(false);
    const editingUser = ref(null);

    const addNewUser = () => {
      allowedEmailsStore.addAllowedEmail(
        newUser.value.email,
        newUser.value.role,
        newUser.value.defaultRole
      );
      newUser.value = {
        email: '',
        role: 'user',
        defaultRole: 'child'
      };
    };

    const editUser = (user) => {
      editingUser.value = { ...user };
      showEditModal.value = true;
    };

    const updateUser = () => {
      const index = allowedEmailsStore.allowedEmails.findIndex(
        u => u.email === editingUser.value.email
      );
      if (index !== -1) {
        allowedEmailsStore.allowedEmails[index] = { ...editingUser.value };
      }
      showEditModal.value = false;
    };

    const removeUser = (email) => {
      if (confirm('Are you sure you want to remove this user?')) {
        allowedEmailsStore.removeAllowedEmail(email);
      }
    };

    return {
      allowedEmails,
      currentUserEmail,
      newUser,
      showEditModal,
      editingUser,
      addNewUser,
      editUser,
      updateUser,
      removeUser
    };
  }
};
</script>
