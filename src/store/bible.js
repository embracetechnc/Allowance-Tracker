import axios from 'axios';

export default {
    namespaced: true,
    
    state: {
        dailyVerse: null,
        moneyVerse: null,
        loading: false,
        error: null,
        lastFetch: null
    },
    
    mutations: {
        SET_DAILY_VERSE(state, verse) {
            state.dailyVerse = verse;
            state.lastFetch = new Date().toISOString();
        },
        
        SET_MONEY_VERSE(state, verse) {
            state.moneyVerse = verse;
        },
        
        SET_LOADING(state, loading) {
            state.loading = loading;
        },
        
        SET_ERROR(state, error) {
            state.error = error;
        }
    },
    
    actions: {
        async fetchDailyVerse({ commit, state }) {
            // Check if we already have a verse that's less than an hour old
            if (state.dailyVerse && state.lastFetch) {
                const lastFetch = new Date(state.lastFetch);
                const now = new Date();
                const hoursSinceLastFetch = (now - lastFetch) / (1000 * 60 * 60);
                
                if (hoursSinceLastFetch < 1) {
                    return state.dailyVerse;
                }
            }
            
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            
            try {
                const response = await axios.get('/api/bible/daily');
                if (response.data.success) {
                    commit('SET_DAILY_VERSE', response.data.data);
                    return response.data.data;
                } else {
                    throw new Error(response.data.message || 'Failed to fetch daily verse');
                }
            } catch (error) {
                commit('SET_ERROR', error.message);
                console.error('Error fetching daily verse:', error);
                throw error;
            } finally {
                commit('SET_LOADING', false);
            }
        },
        
        async fetchMoneyVerse({ commit }) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            
            try {
                const response = await axios.get('/api/bible/money');
                if (response.data.success) {
                    commit('SET_MONEY_VERSE', response.data.data);
                    return response.data.data;
                } else {
                    throw new Error(response.data.message || 'Failed to fetch money verse');
                }
            } catch (error) {
                commit('SET_ERROR', error.message);
                console.error('Error fetching money verse:', error);
                throw error;
            } finally {
                commit('SET_LOADING', false);
            }
        }
    },
    
    getters: {
        dailyVerse: state => state.dailyVerse,
        moneyVerse: state => state.moneyVerse,
        isLoading: state => state.loading,
        error: state => state.error,
        lastFetchTime: state => state.lastFetch ? new Date(state.lastFetch) : null
    }
};
