import axios from 'axios';

export default {
    namespaced: true,
    
    state: {
        notifications: [],
        unreadCount: 0,
        loading: false,
        error: null
    },
    
    mutations: {
        SET_NOTIFICATIONS(state, notifications) {
            state.notifications = notifications;
            state.unreadCount = notifications.filter(n => !n.read_at).length;
        },
        
        SET_LOADING(state, loading) {
            state.loading = loading;
        },
        
        SET_ERROR(state, error) {
            state.error = error;
        },
        
        MARK_AS_READ(state, notificationId) {
            const notification = state.notifications.find(n => n.id === notificationId);
            if (notification && !notification.read_at) {
                notification.read_at = new Date().toISOString();
                state.unreadCount--;
            }
        }
    },
    
    actions: {
        async fetchNotifications({ commit }) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            
            try {
                const response = await axios.get('/api/notifications');
                commit('SET_NOTIFICATIONS', response.data.notifications);
            } catch (error) {
                commit('SET_ERROR', error.response?.data?.message || 'Failed to fetch notifications');
                console.error('Error fetching notifications:', error);
            } finally {
                commit('SET_LOADING', false);
            }
        },
        
        async markAsRead({ commit }, notificationId) {
            try {
                await axios.post('/api/notifications/read', { notification_id: notificationId });
                commit('MARK_AS_READ', notificationId);
            } catch (error) {
                console.error('Error marking notification as read:', error);
                throw error;
            }
        }
    },
    
    getters: {
        unreadNotifications: state => state.notifications.filter(n => !n.read_at),
        readNotifications: state => state.notifications.filter(n => n.read_at),
        hasUnread: state => state.unreadCount > 0
    }
};
