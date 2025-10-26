import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token'),
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    getUser: (state) => state.user
  },

  actions: {
    async register(credentials) {
      this.loading = true;
      try {
        // Add a timestamp as a simple token
        const timestamp = Date.now();
        const token = btoa(`${credentials.email}:${timestamp}`);
        
        const response = await axios.post('/api/auth/register', {
          ...credentials,
          token
        });
        
        this.token = token;
        this.user = response.data.user;
        localStorage.setItem('token', this.token);
        localStorage.setItem('userEmail', credentials.email);
        
        // Set the default Authorization header for all future requests
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async login(credentials) {
      this.loading = true;
      try {
        console.log('Attempting login with:', { email: credentials.email });
        // Generate token same way as registration
        const timestamp = Date.now();
        const token = btoa(`${credentials.email}:${timestamp}`);
        
        const response = await axios.post('/api/auth/login', {
          email: credentials.email,
          token
        });
        this.token = response.data.token;
        this.user = response.data.user;
        localStorage.setItem('token', this.token);
        localStorage.setItem('userEmail', credentials.email);
        
        // Set the default Authorization header for all future requests
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        
        return response.data;
      } catch (error) {
        console.error('Login error response:', error.response?.data);
        this.error = error.response?.data?.message || 'Login failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async requestPasswordReset(email) {
      this.loading = true;
      try {
        const response = await axios.post('/api/auth/forgot-password', { email });
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to send reset email';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async resetPassword(data) {
      this.loading = true;
      try {
        const response = await axios.post('/api/auth/reset-password', data);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Password reset failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        await axios.post('/api/auth/logout');
      } catch (error) {
        console.error('Logout failed:', error);
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('token');
        localStorage.removeItem('userEmail');
        delete axios.defaults.headers.common['Authorization'];
      }
    },

    async fetchUser() {
      if (!this.token) return;
      
      this.loading = true;
      try {
        const response = await axios.get('/api/auth/user');
        this.user = response.data;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch user';
        // If we get a 401, the token is invalid
        if (error.response?.status === 401) {
          this.logout();
        }
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Initialize the auth state
    async init() {
      // Set default admin user for testing
      this.user = {
        id: 1,
        name: 'William Stokes',
        email: 'william_stokes@hotmail.com',
        role: 'admin',
        allowance_balance: 0,
        weekly_allowance_rate: 0
      };
      this.token = 'bypass-token';
      axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
    }
  }
});
