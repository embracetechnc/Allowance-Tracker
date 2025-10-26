import axios from 'axios';
import { format, subDays } from 'date-fns';

export default {
    namespaced: true,
    
    state: {
        tasks: {
            room_cleaning: [],
            bathroom_cleaning: [],
            kitchen_cleaning: [],
            laundry: []
        },
        currentBalance: 0,
        nextPayout: null,
        recentActivity: [],
        loading: {
            tasks: false,
            balance: false,
            activity: false
        },
        error: {
            tasks: null,
            balance: null,
            activity: null
        }
    },
    
    mutations: {
        SET_TASKS(state, tasks) {
            state.tasks = tasks;
        },
        
        SET_CURRENT_BALANCE(state, balance) {
            state.currentBalance = balance;
        },
        
        SET_NEXT_PAYOUT(state, date) {
            state.nextPayout = date;
        },
        
        SET_RECENT_ACTIVITY(state, activity) {
            state.recentActivity = activity;
        },
        
        SET_LOADING(state, { type, value }) {
            state.loading[type] = value;
        },
        
        SET_ERROR(state, { type, error }) {
            state.error[type] = error;
        },
        
        UPDATE_TASK_STATUS(state, { type, taskId, status }) {
            const task = state.tasks[type].find(t => t.id === taskId);
            if (task) {
                task.status = status;
            }
        },
        
        ADD_ACTIVITY(state, activity) {
            state.recentActivity.unshift(activity);
            // Keep only last 10 activities
            state.recentActivity = state.recentActivity.slice(0, 10);
        }
    },
    
    actions: {
        async fetchDashboardData({ dispatch }) {
            await Promise.all([
                dispatch('fetchTasks'),
                dispatch('fetchBalance'),
                dispatch('fetchRecentActivity')
            ]);
        },
        
        async fetchTasks({ commit }) {
            commit('SET_LOADING', { type: 'tasks', value: true });
            commit('SET_ERROR', { type: 'tasks', error: null });
            
            try {
                const response = await axios.get('/api/tasks');
                commit('SET_TASKS', response.data.tasks);
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'tasks', 
                    error: error.response?.data?.message || 'Failed to fetch tasks' 
                });
                console.error('Error fetching tasks:', error);
            } finally {
                commit('SET_LOADING', { type: 'tasks', value: false });
            }
        },
        
        async fetchBalance({ commit }) {
            commit('SET_LOADING', { type: 'balance', value: true });
            commit('SET_ERROR', { type: 'balance', error: null });
            
            try {
                const response = await axios.get('/api/balance');
                commit('SET_CURRENT_BALANCE', response.data.balance);
                commit('SET_NEXT_PAYOUT', response.data.nextPayout);
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'balance', 
                    error: error.response?.data?.message || 'Failed to fetch balance' 
                });
                console.error('Error fetching balance:', error);
            } finally {
                commit('SET_LOADING', { type: 'balance', value: false });
            }
        },
        
        async fetchRecentActivity({ commit }) {
            commit('SET_LOADING', { type: 'activity', value: true });
            commit('SET_ERROR', { type: 'activity', error: null });
            
            try {
                const response = await axios.get('/api/activity');
                commit('SET_RECENT_ACTIVITY', response.data.activity);
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'activity', 
                    error: error.response?.data?.message || 'Failed to fetch activity' 
                });
                console.error('Error fetching activity:', error);
            } finally {
                commit('SET_LOADING', { type: 'activity', value: false });
            }
        },
        
        async completeTask({ commit, dispatch }, { type, taskId }) {
            try {
                const response = await axios.post('/api/tasks/complete', {
                    task_type: type,
                    task_id: taskId
                });
                
                commit('UPDATE_TASK_STATUS', {
                    type,
                    taskId,
                    status: 'completed'
                });
                
                commit('ADD_ACTIVITY', {
                    type: 'task_completed',
                    task_type: type,
                    timestamp: new Date().toISOString()
                });
                
                // Refresh balance after task completion
                dispatch('fetchBalance');
                
                return response.data;
            } catch (error) {
                console.error('Error completing task:', error);
                throw error;
            }
        },
        
        async verifyTask({ commit, dispatch }, { type, taskId, approved }) {
            try {
                const response = await axios.post('/api/tasks/verify', {
                    task_type: type,
                    task_id: taskId,
                    approved
                });
                
                commit('UPDATE_TASK_STATUS', {
                    type,
                    taskId,
                    status: approved ? 'verified' : 'rejected'
                });
                
                commit('ADD_ACTIVITY', {
                    type: 'task_verified',
                    task_type: type,
                    approved,
                    timestamp: new Date().toISOString()
                });
                
                // Refresh balance after task verification
                dispatch('fetchBalance');
                
                return response.data;
            } catch (error) {
                console.error('Error verifying task:', error);
                throw error;
            }
        }
    },
    
    getters: {
        tasksByType: state => type => state.tasks[type] || [],
        
        completedTasks: state => type => {
            return state.tasks[type]?.filter(task => task.status === 'verified') || [];
        },
        
        pendingTasks: state => type => {
            return state.tasks[type]?.filter(task => task.status === 'completed') || [];
        },
        
        taskProgress: state => {
            const progress = {};
            Object.keys(state.tasks).forEach(type => {
                const tasks = state.tasks[type];
                const completed = tasks.filter(t => t.status === 'verified').length;
                progress[type] = {
                    total: tasks.length,
                    completed,
                    percentage: tasks.length ? (completed / tasks.length) * 100 : 0
                };
            });
            return progress;
        },
        
        isLoading: state => type => state.loading[type],
        
        hasError: state => type => !!state.error[type],
        
        getError: state => type => state.error[type],
        
        formattedBalance: state => {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(state.currentBalance);
        },
        
        nextPayoutDate: state => {
            return state.nextPayout ? format(new Date(state.nextPayout), 'MMMM d, yyyy') : null;
        }
    }
};
