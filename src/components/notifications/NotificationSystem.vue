<template>
  <div>
    <!-- Notification Bell Icon with Badge -->
    <div class="relative">
      <button 
        @click="toggleNotifications" 
        class="p-2 text-gray-600 hover:text-gray-800 focus:outline-none"
      >
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <!-- Notification Badge -->
        <span 
          v-if="unreadCount > 0"
          class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
        >
          {{ unreadCount }}
        </span>
      </button>
    </div>

    <!-- Notification Panel -->
    <div 
      v-if="showNotifications"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl overflow-hidden z-50"
    >
      <div class="p-4 border-b">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">Notifications</h2>
          <button 
            @click="markAllAsRead"
            class="text-sm text-indigo-600 hover:text-indigo-800"
          >
            Mark all as read
          </button>
        </div>
      </div>

      <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
        <div 
          v-for="notification in notifications" 
          :key="notification.id"
          :class="['p-4 hover:bg-gray-50', { 'bg-blue-50': !notification.read }]"
        >
          <div class="flex items-start">
            <!-- Task Completion Icon -->
            <div v-if="notification.type === 'task_completed'" class="flex-shrink-0">
              <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <!-- Task Verification Icon -->
            <div v-else-if="notification.type === 'task_verified'" class="flex-shrink-0">
              <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <!-- Reminder Icon -->
            <div v-else class="flex-shrink-0">
              <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>

            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-900">
                {{ notification.title }}
              </p>
              <p class="mt-1 text-sm text-gray-500">
                {{ notification.message }}
              </p>
              <p class="mt-2 text-xs text-gray-400">
                {{ formatTime(notification.timestamp) }}
              </p>
            </div>

            <button 
              @click="markAsRead(notification.id)"
              v-if="!notification.read"
              class="ml-4 text-indigo-600 hover:text-indigo-800"
            >
              Mark as read
            </button>
          </div>
        </div>

        <!-- Empty State -->
        <div 
          v-if="notifications.length === 0" 
          class="p-4 text-center text-gray-500"
        >
          No notifications
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { format } from 'date-fns';

export default {
  name: 'NotificationSystem',
  
  setup() {
    const notifications = ref([]);
    const showNotifications = ref(false);

    const unreadCount = computed(() => {
      return notifications.value.filter(n => !n.read).length;
    });

    const toggleNotifications = () => {
      showNotifications.value = !showNotifications.value;
    };

    const markAsRead = (id) => {
      const notification = notifications.value.find(n => n.id === id);
      if (notification) {
        notification.read = true;
      }
    };

    const markAllAsRead = () => {
      notifications.value.forEach(n => n.read = true);
    };

    const addNotification = (notification) => {
      notifications.value.unshift({
        id: Date.now(),
        read: false,
        timestamp: new Date(),
        ...notification
      });
    };

    const formatTime = (timestamp) => {
      return format(new Date(timestamp), 'MMM d, h:mm a');
    };

    // Example notification types
    const notificationTypes = {
      TASK_COMPLETED: 'task_completed',
      TASK_VERIFIED: 'task_verified',
      TASK_REJECTED: 'task_rejected',
      ALLOWANCE_PAID: 'allowance_paid',
      DUE_SOON: 'due_soon'
    };

    return {
      notifications,
      showNotifications,
      unreadCount,
      toggleNotifications,
      markAsRead,
      markAllAsRead,
      addNotification,
      formatTime,
      notificationTypes
    };
  }
};
</script>


