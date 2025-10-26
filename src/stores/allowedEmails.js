import { defineStore } from 'pinia';

export const useAllowedEmailsStore = defineStore('allowedEmails', {
  state: () => ({
    allowedEmails: [
      {
        email: 'william_stokes@hotmail.com',
        role: 'admin',
        defaultRole: 'parent'
      },
      {
        email: 'tonyastokes@yahoo.com',
        role: 'user',
        defaultRole: 'parent'
      },
      {
        email: 'hannahastokes@icloud.com',
        role: 'user',
        defaultRole: 'child'
      },
      {
        email: 'havenastokes@icloud.com',
        role: 'user',
        defaultRole: 'child'
      }
    ]
  }),

  getters: {
    isEmailAllowed: (state) => (email) => {
      return state.allowedEmails.some(entry => entry.email.toLowerCase() === email.toLowerCase());
    },

    getEmailInfo: (state) => (email) => {
      return state.allowedEmails.find(entry => entry.email.toLowerCase() === email.toLowerCase());
    },

    isAdmin: (state) => (email) => {
      const entry = state.allowedEmails.find(entry => entry.email.toLowerCase() === email.toLowerCase());
      return entry?.role === 'admin';
    }
  },

  actions: {
    addAllowedEmail(email, role = 'user', defaultRole = 'child') {
      if (!this.isEmailAllowed(email)) {
        this.allowedEmails.push({
          email: email.toLowerCase(),
          role,
          defaultRole
        });
      }
    },

    removeAllowedEmail(email) {
      const index = this.allowedEmails.findIndex(entry => entry.email.toLowerCase() === email.toLowerCase());
      if (index > -1) {
        this.allowedEmails.splice(index, 1);
      }
    },

    updateEmailRole(email, role) {
      const entry = this.allowedEmails.find(entry => entry.email.toLowerCase() === email.toLowerCase());
      if (entry) {
        entry.role = role;
      }
    }
  }
});