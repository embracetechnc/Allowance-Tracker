import { mount } from '@vue/test-utils';
import { describe, it, expect, beforeEach, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { nextTick } from 'vue';
import TaskManager from '@/components/tasks/EnhancedTaskManager.vue';
import ParentDashboard from '@/views/ParentDashboard.vue';
import ChildDashboard from '@/views/ChildDashboard.vue';

describe('Task Flow Integration', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  describe('Parent Creates and Assigns Task', () => {
    it('creates a task and assigns it to a child', async () => {
      // Mount parent dashboard with required dependencies
      const parentDashboard = mount(ParentDashboard, {
        global: {
          plugins: [createPinia()],
          stubs: {
            RouterLink: true,
            RouterView: true
          }
        },
        data() {
          return {
            children: [
              {
                id: 1,
                name: 'Hannah',
                email: 'hannahastokes@icloud.com',
                allowance_balance: 0,
                weekly_allowance_rate: 20,
                role: 'child'
              },
              {
                id: 2,
                name: 'Haven',
                email: 'havenastokes@icloud.com',
                allowance_balance: 0,
                weekly_allowance_rate: 20,
                role: 'child'
              }
            ]
          };
        }
      });

      // Open create task modal
      await parentDashboard.find('button[data-test="create-task"]').trigger('click');

      // Fill in task details
      const taskForm = parentDashboard.findComponent({ name: 'CreateTaskModal' });
      await taskForm.find('input[name="name"]').setValue('Clean Room');
      await taskForm.find('textarea[name="description"]').setValue('Make bed and vacuum floor');
      await taskForm.find('select[name="category"]').setValue('Chores');
      await taskForm.find('input[name="points"]').setValue(5);
      await taskForm.find('select[name="child"]').setValue('Hannah');
      await taskForm.find('input[name="due_date"]').setValue('2025-10-30');

      // Submit task
      await taskForm.find('button[type="submit"]').trigger('click');
      await nextTick();

      // Verify task appears in task list
      const taskList = parentDashboard.findComponent({ name: 'TaskList' });
      expect(taskList.text()).toContain('Clean Room');
      expect(taskList.text()).toContain('Hannah');
      expect(taskList.text()).toContain('5 points');
    });
  });

  describe('Child Completes Task', () => {
    it('marks task as complete and uploads photo', async () => {
      // Mount child dashboard with required dependencies
      const childDashboard = mount(ChildDashboard, {
        global: {
          plugins: [createPinia()],
          stubs: {
            RouterLink: true,
            RouterView: true
          }
        },
        data() {
          return {
            user: {
              id: 1,
              name: 'Hannah',
              email: 'hannahastokes@icloud.com',
              role: 'child'
            },
            tasks: [{
              id: 1,
              name: 'Clean Room',
              description: 'Make bed and vacuum floor',
              points: 5,
              status: 'assigned',
              childId: 1
            }]
          };
        }
      });

      // Start task
      const taskCard = childDashboard.find('[data-test="task-1"]');
      await taskCard.find('button[data-test="start-task"]').trigger('click');
      await nextTick();

      // Upload photo
      const file = new File(['test'], 'room.jpg', { type: 'image/jpeg' });
      const input = taskCard.find('input[type="file"]');
      Object.defineProperty(input.element, 'files', {
        value: [file],
        writable: true
      });
      await input.trigger('change');
      await nextTick();

      // Complete task
      await taskCard.find('button[data-test="complete-task"]').trigger('click');
      await nextTick();

      // Verify task status changed
      expect(taskCard.text()).toContain('Completed');
      expect(taskCard.find('img[alt="Task completion"]').exists()).toBe(true);
    });
  });

  describe('Parent Verifies Task', () => {
    it('verifies completed task and awards points', async () => {
      // Mount parent dashboard with completed task
      const parentDashboard = mount(ParentDashboard, {
        global: {
          plugins: [createPinia()],
          stubs: {
            RouterLink: true,
            RouterView: true
          }
        },
        data() {
          return {
            tasks: [{
              id: 1,
              name: 'Clean Room',
              description: 'Make bed and vacuum floor',
              points: 5,
              status: 'completed',
              childName: 'Hannah',
              photo: 'data:image/jpeg;base64,test'
            }]
          };
        }
      });

      // View task details
      const taskCard = parentDashboard.find('[data-test="task-1"]');
      await taskCard.find('button[data-test="view-details"]').trigger('click');
      await nextTick();

      // Verify task completion photo is visible
      const taskDetails = parentDashboard.findComponent({ name: 'TaskDetails' });
      expect(taskDetails.find('img[alt="Task completion"]').exists()).toBe(true);

      // Verify task
      await taskDetails.find('button[data-test="verify-task"]').trigger('click');
      await nextTick();

      // Check points were awarded
      expect(taskCard.text()).toContain('Verified');
      const childCard = parentDashboard.find('[data-test="child-hannah"]');
      expect(childCard.text()).toContain('Points: 5');
    });
  });

  describe('Weekly Allowance Calculation', () => {
    it('calculates and processes weekly allowance', async () => {
      // Mount parent dashboard with completed tasks
      const parentDashboard = mount(ParentDashboard, {
        data() {
          return {
            children: [{
              id: 1,
              name: 'Hannah',
              completedTasks: 4,
              earnedPoints: 20,
              weeklyAllowanceRate: 5.00
            }]
          };
        }
      });

      // Open allowance modal
      const childCard = parentDashboard.find('[data-test="child-hannah"]');
      await childCard.find('button[data-test="manage-allowance"]').trigger('click');
      await nextTick();

      // Calculate allowance
      const allowanceModal = parentDashboard.findComponent({ name: 'AllowanceModal' });
      await allowanceModal.find('button[data-test="calculate-allowance"]').trigger('click');
      await nextTick();

      // Verify calculation
      expect(allowanceModal.text()).toContain('$5.00');
      expect(allowanceModal.text()).toContain('4 tasks completed');
      expect(allowanceModal.text()).toContain('20 points earned');

      // Process payment
      await allowanceModal.find('button[data-test="process-payment"]').trigger('click');
      await nextTick();

      // Verify payment processed
      expect(childCard.text()).toContain('Allowance Paid: $5.00');
    });
  });
});
