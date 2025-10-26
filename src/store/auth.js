import axios from 'axios'

export default {
  namespaced: true,
  
  state: {
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user')) || null
  },

  mutations: {
    SET_TOKEN(state, token) {
      state.token = token
      localStorage.setItem('token', token)
    },

    SET_USER(state, user) {
      state.user = user
      localStorage.setItem('user', JSON.stringify(user))
    },

    CLEAR_AUTH(state) {
      state.token = null
      state.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  },

  actions: {
    async login({ commit }, credentials) {
      try {
        const response = await axios.post('/api/auth/login', credentials)
        const { token, user } = response.data
        
        commit('SET_TOKEN', token)
        commit('SET_USER', user)
        
        // Set token in axios headers
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
        
        return response
      } catch (error) {
        throw error
      }
    },

    async register({ commit }, userData) {
      try {
        const response = await axios.post('/api/auth/register', userData)
        const { token, user } = response.data
        
        commit('SET_TOKEN', token)
        commit('SET_USER', user)
        
        // Set token in axios headers
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
        
        return response
      } catch (error) {
        throw error
      }
    },

    logout({ commit }) {
      commit('CLEAR_AUTH')
      delete axios.defaults.headers.common['Authorization']
    }
  },

  getters: {
    isAuthenticated: state => !!state.token,
    isParent: state => state.user?.role === 'parent',
    isChild: state => state.user?.role === 'child',
    user: state => state.user
  }
}
