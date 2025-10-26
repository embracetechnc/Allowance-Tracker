import axios from 'axios';

export default {
    namespaced: true,
    
    state: {
        isLinked: false,
        cashtag: null,
        paymentHistory: [],
        pendingPayments: [],
        loading: {
            linking: false,
            payments: false,
            history: false
        },
        error: {
            linking: null,
            payments: null,
            history: null
        }
    },
    
    mutations: {
        SET_LINKED_STATUS(state, status) {
            state.isLinked = status;
        },
        
        SET_CASHTAG(state, cashtag) {
            state.cashtag = cashtag;
        },
        
        SET_PAYMENT_HISTORY(state, history) {
            state.paymentHistory = history;
        },
        
        SET_PENDING_PAYMENTS(state, payments) {
            state.pendingPayments = payments;
        },
        
        SET_LOADING(state, { type, value }) {
            state.loading[type] = value;
        },
        
        SET_ERROR(state, { type, error }) {
            state.error[type] = error;
        },
        
        ADD_PAYMENT(state, payment) {
            state.paymentHistory.unshift(payment);
        },
        
        UPDATE_PAYMENT_STATUS(state, { paymentId, status }) {
            const payment = state.paymentHistory.find(p => p.id === paymentId);
            if (payment) {
                payment.status = status;
            }
        }
    },
    
    actions: {
        async initializeCashApp({ commit, dispatch }) {
            try {
                const response = await axios.get('/api/cashapp/status');
                commit('SET_LINKED_STATUS', response.data.isLinked);
                if (response.data.isLinked) {
                    commit('SET_CASHTAG', response.data.cashtag);
                    dispatch('fetchPaymentHistory');
                }
            } catch (error) {
                console.error('Failed to initialize Cash App:', error);
            }
        },
        
        async startLinking({ commit }) {
            commit('SET_LOADING', { type: 'linking', value: true });
            commit('SET_ERROR', { type: 'linking', error: null });
            
            try {
                const response = await axios.get('/api/cashapp/auth');
                window.location.href = response.data.auth_url;
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'linking', 
                    error: error.response?.data?.message || 'Failed to start Cash App linking' 
                });
                console.error('Error starting Cash App linking:', error);
            } finally {
                commit('SET_LOADING', { type: 'linking', value: false });
            }
        },
        
        async handleCallback({ commit }, { code, state }) {
            commit('SET_LOADING', { type: 'linking', value: true });
            commit('SET_ERROR', { type: 'linking', error: null });
            
            try {
                const response = await axios.post('/api/cashapp/callback', { code, state });
                commit('SET_LINKED_STATUS', true);
                commit('SET_CASHTAG', response.data.cashtag);
                return true;
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'linking', 
                    error: error.response?.data?.message || 'Failed to complete Cash App linking' 
                });
                console.error('Error handling callback:', error);
                return false;
            } finally {
                commit('SET_LOADING', { type: 'linking', value: false });
            }
        },
        
        async unlinkAccount({ commit }) {
            commit('SET_LOADING', { type: 'linking', value: true });
            commit('SET_ERROR', { type: 'linking', error: null });
            
            try {
                await axios.post('/api/cashapp/unlink');
                commit('SET_LINKED_STATUS', false);
                commit('SET_CASHTAG', null);
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'linking', 
                    error: error.response?.data?.message || 'Failed to unlink Cash App account' 
                });
                console.error('Error unlinking account:', error);
            } finally {
                commit('SET_LOADING', { type: 'linking', value: false });
            }
        },
        
        async fetchPaymentHistory({ commit }) {
            commit('SET_LOADING', { type: 'history', value: true });
            commit('SET_ERROR', { type: 'history', error: null });
            
            try {
                const response = await axios.get('/api/cashapp/history');
                commit('SET_PAYMENT_HISTORY', response.data.payments);
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'history', 
                    error: error.response?.data?.message || 'Failed to fetch payment history' 
                });
                console.error('Error fetching payment history:', error);
            } finally {
                commit('SET_LOADING', { type: 'history', value: false });
            }
        },
        
        async fetchPendingPayments({ commit }) {
            commit('SET_LOADING', { type: 'payments', value: true });
            commit('SET_ERROR', { type: 'payments', error: null });
            
            try {
                const response = await axios.get('/api/cashapp/pending');
                commit('SET_PENDING_PAYMENTS', response.data.payments);
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'payments', 
                    error: error.response?.data?.message || 'Failed to fetch pending payments' 
                });
                console.error('Error fetching pending payments:', error);
            } finally {
                commit('SET_LOADING', { type: 'payments', value: false });
            }
        },
        
        async schedulePayment({ commit }, { userId, amount, paymentDate }) {
            commit('SET_LOADING', { type: 'payments', value: true });
            commit('SET_ERROR', { type: 'payments', error: null });
            
            try {
                const response = await axios.post('/api/cashapp/schedule', {
                    user_id: userId,
                    amount,
                    payment_date: paymentDate
                });
                
                commit('ADD_PAYMENT', response.data.payment);
                return response.data.payment;
            } catch (error) {
                commit('SET_ERROR', { 
                    type: 'payments', 
                    error: error.response?.data?.message || 'Failed to schedule payment' 
                });
                console.error('Error scheduling payment:', error);
                throw error;
            } finally {
                commit('SET_LOADING', { type: 'payments', value: false });
            }
        }
    },
    
    getters: {
        isLinked: state => state.isLinked,
        cashtag: state => state.cashtag,
        
        recentPayments: state => (limit = 5) => {
            return state.paymentHistory.slice(0, limit);
        },
        
        paymentsByStatus: state => status => {
            return state.paymentHistory.filter(p => p.status === status);
        },
        
        isLoading: state => type => state.loading[type],
        hasError: state => type => !!state.error[type],
        getError: state => type => state.error[type],
        
        totalPendingAmount: state => {
            return state.pendingPayments.reduce((sum, payment) => sum + payment.amount, 0);
        },
        
        nextScheduledPayment: state => {
            const pending = state.pendingPayments
                .filter(p => new Date(p.payment_date) > new Date())
                .sort((a, b) => new Date(a.payment_date) - new Date(b.payment_date));
            return pending[0] || null;
        }
    }
};
