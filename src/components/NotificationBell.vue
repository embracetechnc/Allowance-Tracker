<template>
  <div class="relative">
    <!-- Bell Icon with Badge -->
    <button 
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-gray-800 focus:outline-none"
      :class="{ 'text-gray-800': isOpen }"
    >
      <svg 
        xmlns="http://www.w3.org/2000/svg" 
        class="h-6 w-6" 
        fill="none" 
        viewBox="0 0 24 24" 
        stroke="currentColor"
      >
        <path 
          stroke-linecap="round" 
          stroke-linejoin="round" 
          stroke-width="2" 
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
        />
      </svg>
      
      <!-- Unread Badge -->
      <span 
        v-if="unreadCount > 0"
        class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
      >
        {{ unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <div 
      v-if="isOpen"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg py-2 z-50"
      @click.stop
    >
      <!-- Header -->
      <div class="px-4 py-2 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="px-4 py-3 text-center text-gray-600">
        Loading notifications...
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="px-4 py-3 text-center text-red-600">
        {{ error }}
      </div>

      <!-- Empty State -->
      <div 
        v-else-if="notifications.length === 0" 
        class="px-4 py-3 text-center text-gray-600"
      >
        No notifications
      </div>

      <!-- Notifications List -->
      <div v-else class="max-h-96 overflow-y-auto">
        <div 
          v-for="notification in notifications" 
          :key="notification.id"
          class="px-4 py-3 hover:bg-gray-50 cursor-pointer"
          :class="{ 'bg-blue-50': !notification.read_at }"
          @click="handleNotificationClick(notification)"
        >
          <!-- Task Completion Notification -->
          <div v-if="notification.type === 'task_completed'" class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-900">
                {{ notification.message }}
              </p>
              <p class="mt-1 text-xs text-gray-500">
                {{ formatDate(notification.created_at) }}
              </p>
            </div>
          </div>

          <!-- Task Verification Notification -->
          <div v-else-if="notification.type === 'task_verified'" class="flex items-start">
            <div class="flex-shrink-0">
              <svg 
                class="h-6 w-6" 
                :class="notification.message.includes('approved') ? 'text-green-500' : 'text-red-500'"
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
              >
                <path 
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-900">
                {{ notification.message }}
              </p>
              <p class="mt-1 text-xs text-gray-500">
                {{ formatDate(notification.created_at) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div 
        v-if="notifications.length > 0"
        class="px-4 py-2 border-t border-gray-200 text-center"
      >
        <button 
          class="text-sm text-blue-600 hover:text-blue-800"
          @click="markAllAsRead"
        >
          Mark all as read
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import { useStore } from 'vuex';
import { formatDistanceToNow } from 'date-fns';

export default {
  name: 'NotificationBell',
  
  setup() {
    const store = useStore();
    const isOpen = ref(false);
    
    // Close dropdown when clicking outside
    const handleClickOutside = (event) => {
      if (isOpen.value && !event.target.closest('.notification-bell')) {
        isOpen.value = false;
      }
    };
    
    onMounted(() => {
      document.addEventListener('click', handleClickOutside);
      // Fetch notifications when component mounts
      store.dispatch('notifications/fetchNotifications');
      
      // Set up polling for new notifications
      const pollInterval = setInterval(() => {
        if (!isOpen.value) { // Only poll when dropdown is closed
          store.dispatch('notifications/fetchNotifications');
        }
      }, 30000); // Poll every 30 seconds
      
      onUnmounted(() => {
        document.removeEventListener('click', handleClickOutside);
        clearInterval(pollInterval);
      });
    });
    
    const toggleDropdown = () => {
      isOpen.value = !isOpen.value;
      if (isOpen.value) {
        store.dispatch('notifications/fetchNotifications');
      }
    };
    
    const handleNotificationClick = async (notification) => {
      if (!notification.read_at) {
        try {
          await store.dispatch('notifications/markAsRead', notification.id);
        } catch (error) {
          console.error('Failed to mark notification as read:', error);
        }
      }
    };
    
    const markAllAsRead = async () => {
      const unreadNotifications = store.getters['notifications/unreadNotifications'];
      for (const notification of unreadNotifications) {
        try {
          await store.dispatch('notifications/markAsRead', notification.id);
        } catch (error) {
          console.error('Failed to mark notification as read:', error);
        }
      }
    };
    
    const formatDate = (date) => {
      return formatDistanceToNow(new Date(date), { addSuffix: true });
    };
    
    return {
      isOpen,
      toggleDropdown,
      handleNotificationClick,
      markAllAsRead,
      formatDate,
      notifications: computed(() => store.state.notifications.notifications),
      unreadCount: computed(() => store.state.notifications.unreadCount),
      loading: computed(() => store.state.notifications.loading),
      error: computed(() => store.state.notifications.error)
    };
  }
};
</script>

<style scoped>
.notification-bell {
  /* Add any custom styles here */
}
</style>
