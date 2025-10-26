import axios from 'axios';
import { startOfWeek, endOfWeek, format } from 'date-fns';

export default {
    namespaced: true,
    
    state: {
        weeklyPoints: [],
        pointHistory: [],
        categories: [],
        weeklySummary: null,
        loading: {
            points: false,
            history: false,
            categories: false,
            adding: false,
            removing: false
        },
        error: {
            points: null,
            history: null,
            categories: null,
            adding: null,
            removing: null
        }
    },
    
    mutations: {
        SET_WEEKLY_POINTS(state, points) {
            state.weeklyPoints = points;
        },
        
        SET_POINT_HISTORY(state, history) {
            state.pointHistory = history;
        },
        
        SET_CATEGORIES(state, categories) {
            state.categories = categories;
        },
        
        SET_WEEKLY_SUMMARY(state, summary) {
            state.weeklySummary = summary;
        },
        
        SET_LOADING(state, { type, value }) {
            state.loading[type] = value;
        },
        
        SET_ERROR(state, { type, error }) {
            state.error[type] = error;
        },
        
        ADD_POINTS(state, points) {
            state.weeklyPoints.unshift(points);
            if (state.weeklySummary) {
                state.weeklySummary.total_points += points.points;
                state.weeklySummary.allowance_deduction = 
                    state.weeklySummary.total_points > 3 ? 5.00 : 0.00;
            }
        },
        
        REMOVE_POINTS(state, points) {
            state.weeklyPoints.unshift(points);
            if (state.weeklySummary) {
                state.weeklySummary.total_points -= points.points;
                state.weeklySummary.allowance_deduction = 
                    state.weeklySummary.total_points > 3 ? 5.00 : 0.00;
            }
        }
    },
    
    actions: {
        async fetchWeeklyPoints({ commit }, { userId, weekNumber, year } = {}) {
            commit('SET_LOADING', { type: 'points', value: true });
            commit('SET_ERROR', { type: 'points', error: null });
            
            try {
                const params = new URLSearchParams();
                if (userId) params.append('user_id', userId);
                if (weekNumber) params.append('week', weekNumber);
                if (year) params.append('year', year);

                const response = await axios.get(`/api/points/weekly?${params.toString()}`);
                
                if (response.data.success) {
                    commit('SET_WEEKLY_POINTS', response.data.points);
                    commit('SET_WEEKLY_SUMMARY', response.data.summary);
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'points', 
                    error: error.response?.data?.message || 'Failed to fetch weekly points' 
                });
                console.error('Error fetching weekly points:', error);
            } finally {
                commit('SET_LOADING', { type: 'points', value: false });
            }
        },
        
        async fetchPointHistory({ commit }, { userId, limit } = {}) {
            commit('SET_LOADING', { type: 'history', value: true });
            commit('SET_ERROR', { type: 'history', error: null });
            
            try {
                const params = new URLSearchParams();
                if (userId) params.append('user_id', userId);
                if (limit) params.append('limit', limit);

                const response = await axios.get(`/api/points/history?${params.toString()}`);
                
                if (response.data.success) {
                    commit('SET_POINT_HISTORY', response.data.history);
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'history', 
                    error: error.response?.data?.message || 'Failed to fetch point history' 
                });
                console.error('Error fetching point history:', error);
            } finally {
                commit('SET_LOADING', { type: 'history', value: false });
            }
        },
        
        async fetchCategories({ commit }) {
            commit('SET_LOADING', { type: 'categories', value: true });
            commit('SET_ERROR', { type: 'categories', error: null });
            
            try {
                const response = await axios.get('/api/points/categories');
                
                if (response.data.success) {
                    commit('SET_CATEGORIES', response.data.categories);
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'categories', 
                    error: error.response?.data?.message || 'Failed to fetch categories' 
                });
                console.error('Error fetching categories:', error);
            } finally {
                commit('SET_LOADING', { type: 'categories', value: false });
            }
        },
        
        async addPoints({ commit }, { userId, points, reason, categoryId }) {
            commit('SET_LOADING', { type: 'adding', value: true });
            commit('SET_ERROR', { type: 'adding', error: null });
            
            try {
                const response = await axios.post('/api/points/add', {
                    user_id: userId,
                    points,
                    reason,
                    category_id: categoryId
                });
                
                if (response.data.success) {
                    commit('ADD_POINTS', {
                        points,
                        reason,
                        category_id: categoryId,
                        created_at: new Date().toISOString()
                    });
                    commit('SET_WEEKLY_SUMMARY', response.data.summary);
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'adding', 
                    error: error.response?.data?.message || 'Failed to add points' 
                });
                console.error('Error adding points:', error);
                throw error;
            } finally {
                commit('SET_LOADING', { type: 'adding', value: false });
            }
        },
        
        async removePoints({ commit }, { userId, points, reason }) {
            commit('SET_LOADING', { type: 'removing', value: true });
            commit('SET_ERROR', { type: 'removing', error: null });
            
            try {
                const response = await axios.post('/api/points/remove', {
                    user_id: userId,
                    points,
                    reason
                });
                
                if (response.data.success) {
                    commit('REMOVE_POINTS', {
                        points,
                        reason,
                        created_at: new Date().toISOString()
                    });
                    commit('SET_WEEKLY_SUMMARY', response.data.summary);
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'removing', 
                    error: error.response?.data?.message || 'Failed to remove points' 
                });
                console.error('Error removing points:', error);
                throw error;
            } finally {
                commit('SET_LOADING', { type: 'removing', value: false });
            }
        }
    },
    
    getters: {
        totalPoints: state => {
            return state.weeklySummary?.total_points || 0;
        },
        
        allowanceDeduction: state => {
            return state.weeklySummary?.allowance_deduction || 0;
        },
        
        pointsByCategory: state => {
            const byCategory = {};
            state.weeklyPoints.forEach(point => {
                const categoryId = point.category_id || 'uncategorized';
                if (!byCategory[categoryId]) {
                    byCategory[categoryId] = [];
                }
                byCategory[categoryId].push(point);
            });
            return byCategory;
        },
        
        pointsByDate: state => {
            const byDate = {};
            state.weeklyPoints.forEach(point => {
                const date = format(new Date(point.created_at), 'yyyy-MM-dd');
                if (!byDate[date]) {
                    byDate[date] = [];
                }
                byDate[date].push(point);
            });
            return byDate;
        },
        
        isLoading: state => type => state.loading[type],
        hasError: state => type => !!state.error[type],
        getError: state => type => state.error[type],
        
        getCategoryById: state => id => {
            return state.categories.find(c => c.id === id);
        },
        
        hasExcessivePoints: state => {
            return state.weeklySummary?.total_points > 3;
        }
    }
};
