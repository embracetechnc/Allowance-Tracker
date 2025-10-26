import axios from 'axios';

class NotificationService {
  constructor() {
    this.emailEndpoint = '/api/notifications/email';
    this.subscribers = new Set();
  }

  // Subscribe to notifications
  subscribe(callback) {
    this.subscribers.add(callback);
    return () => this.subscribers.delete(callback);
  }

  // Notify all subscribers
  notify(notification) {
    this.subscribers.forEach(callback => callback(notification));
  }

  // Send email notification
  async sendEmail(to, subject, content) {
    try {
      await axios.post(this.emailEndpoint, {
        to,
        subject,
        content
      });
    } catch (error) {
      console.error('Failed to send email notification:', error);
      throw error;
    }
  }

  // Task completion notification
  async notifyTaskCompleted(task, child) {
    const notification = {
      type: 'task_completed',
      title: 'Task Completed',
      message: `${child.name} has completed the task: ${task.name}`,
      timestamp: new Date()
    };

    // Notify in-app
    this.notify(notification);

    // Send email to parent
    await this.sendEmail(
      child.parent.email,
      'Task Completed',
      `${child.name} has completed the task "${task.name}". Please verify the task.`
    );
  }

  // Task verification notification
  async notifyTaskVerified(task, child) {
    const notification = {
      type: 'task_verified',
      title: 'Task Verified',
      message: `Your task "${task.name}" has been verified. You earned ${task.points} points!`,
      timestamp: new Date()
    };

    // Notify in-app
    this.notify(notification);

    // Send email to child
    await this.sendEmail(
      child.email,
      'Task Verified',
      `Your task "${task.name}" has been verified. You earned ${task.points} points!`
    );
  }

  // Task rejection notification
  async notifyTaskRejected(task, child, reason) {
    const notification = {
      type: 'task_rejected',
      title: 'Task Needs Attention',
      message: `Your task "${task.name}" needs some improvements: ${reason}`,
      timestamp: new Date()
    };

    // Notify in-app
    this.notify(notification);

    // Send email to child
    await this.sendEmail(
      child.email,
      'Task Needs Attention',
      `Your task "${task.name}" needs some improvements:\n\n${reason}`
    );
  }

  // Due date reminder
  async sendDueReminder(task, child) {
    const notification = {
      type: 'due_soon',
      title: 'Task Due Soon',
      message: `Your task "${task.name}" is due soon!`,
      timestamp: new Date()
    };

    // Notify in-app
    this.notify(notification);

    // Send email reminder
    await this.sendEmail(
      child.email,
      'Task Due Soon',
      `Don't forget! Your task "${task.name}" is due soon.`
    );
  }

  // Weekly allowance reminder
  async sendAllowanceReminder(parent) {
    const notification = {
      type: 'allowance_reminder',
      title: 'Weekly Allowance Review',
      message: 'Time to review and process weekly allowances',
      timestamp: new Date()
    };

    // Notify in-app
    this.notify(notification);

    // Send email reminder
    await this.sendEmail(
      parent.email,
      'Weekly Allowance Review',
      'It\'s time to review and process weekly allowances for your children.'
    );
  }

  // Allowance payment notification
  async notifyAllowancePaid(child, amount) {
    const notification = {
      type: 'allowance_paid',
      title: 'Allowance Paid',
      message: `Your allowance of $${amount} has been paid!`,
      timestamp: new Date()
    };

    // Notify in-app
    this.notify(notification);

    // Send email confirmation
    await this.sendEmail(
      child.email,
      'Allowance Paid',
      `Your allowance of $${amount} has been paid to your account.`
    );
  }
}

export const notificationService = new NotificationService();


