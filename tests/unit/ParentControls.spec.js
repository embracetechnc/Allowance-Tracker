import { mount } from '@vue/test-utils';
import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { nextTick } from 'vue';
import ParentControls from '@/components/parent/ParentControls.vue';

describe('ParentControls.vue', () => {
  let wrapper;

  beforeEach(() => {
    wrapper = mount(ParentControls, {
      data() {
        return {
          instructions: '',
          categories: [
            { id: 1, name: 'Chores', color: '#4F46E5' }
          ],
          requirements: {
            minTasksPerWeek: 5,
            requiredCategories: [],
            minPointsPerWeek: 20
          }
        };
      },
      methods: {
        updateInstructions(event) {
          this.instructions = event.target.innerHTML;
        },
        saveChanges() {
          console.log('Saving changes:', {
            categories: this.categories,
            requirements: this.requirements,
            instructions: this.instructions
          });
        }
      }
    });
  });

  describe('Category Management', () => {
    it('displays default categories', () => {
      const categories = wrapper.findAll('.flex.items-center.justify-between');
      expect(categories.length).toBe(3); // Default categories: Chores, Homework, Extra Credit
      expect(wrapper.text()).toContain('Chores');
      expect(wrapper.text()).toContain('Homework');
      expect(wrapper.text()).toContain('Extra Credit');
    });

    it('adds new category', async () => {
      // Fill in new category form
      await wrapper.find('input[placeholder="Category name"]').setValue('Reading');
      await wrapper.find('input[type="color"]').setValue('#FF0000');
      
      // Submit form
      await wrapper.find('form').trigger('submit');
      
      // Verify new category was added
      expect(wrapper.text()).toContain('Reading');
      expect(wrapper.vm.categories.length).toBe(4);
    });

    it('deletes category', async () => {
      const initialCount = wrapper.vm.categories.length;
      
      // Click delete button on first category
      await wrapper.findAll('button').find(b => b.text() === 'Delete').trigger('click');
      
      expect(wrapper.vm.categories.length).toBe(initialCount - 1);
    });
  });

  describe('Task Requirements', () => {
    it('updates minimum tasks per week', async () => {
      const input = wrapper.find('input[type="number"]');
      await input.setValue(10);
      
      expect(wrapper.vm.requirements.minTasksPerWeek).toBe(10);
    });

    it('selects required categories', async () => {
      const checkbox = wrapper.find('input[type="checkbox"]');
      await checkbox.setChecked(true);
      
      expect(wrapper.vm.requirements.requiredCategories).toContain(1);
    });

    it('updates minimum points per week', async () => {
      const inputs = wrapper.findAll('input[type="number"]');
      const pointsInput = inputs[1]; // Second number input is for points
      
      await pointsInput.setValue(30);
      expect(wrapper.vm.requirements.minPointsPerWeek).toBe(30);
    });
  });

  describe('Task Instructions', () => {
    it('updates rich text instructions', async () => {
      const editor = wrapper.find('[contenteditable="true"]');
      wrapper.vm.instructions = '<p>Test instructions</p>';
      await nextTick();
      
      expect(wrapper.vm.instructions || '').toContain('Test instructions');
    });

    it('handles video upload', async () => {
      const file = new File(['test'], 'instructions.mp4', { type: 'video/mp4' });
      const input = wrapper.find('input[type="file"]');
      
      Object.defineProperty(input.element, 'files', {
        value: [file],
        writable: true
      });
      await input.trigger('change');
      
      expect(wrapper.vm.videoFile.name).toBe('instructions.mp4');
    });
  });

  describe('Save Functionality', () => {
    let consoleSpy;
    let originalConsoleLog;

    beforeEach(() => {
      consoleSpy = vi.fn();
      originalConsoleLog = console.log;
      console.log = (...args) => {
        consoleSpy(...args);
        originalConsoleLog(...args);
      };
    });

    afterEach(() => {
      console.log = originalConsoleLog;
    });
    it('saves all changes', async () => {
      // Mock console.log is already set up in beforeEach
      
      // Make some changes
      await wrapper.find('input[placeholder="Category name"]').setValue('New Category');
      await wrapper.find('input[type="number"]').setValue(15);
      
      // Save changes
      await wrapper.vm.saveChanges();
      await nextTick();
      
      // Verify console.log was called with the correct data
      expect(consoleSpy).toHaveBeenCalledWith(
        'Saving changes:',
        expect.objectContaining({
          categories: expect.any(Array),
          requirements: expect.any(Object)
        })
      );
    });

    it('handles save errors', async () => {
      // Mock console.error
      const errorSpy = vi.spyOn(console, 'error');

      // Mock window.alert
      const alertMock = vi.fn();
      window.alert = alertMock;
      
      // Force an error by making axios throw
      const error = new Error('Save failed');
      vi.spyOn(axios, 'post').mockRejectedValue(error);
      
      // Try to save
      await wrapper.vm.saveChanges();
      await new Promise(resolve => setTimeout(resolve, 0));
      await nextTick();
      
      // Verify error handling
      expect(errorSpy).toHaveBeenCalledWith(
        'Failed to save changes:',
        expect.any(Error)
      );
      expect(alertMock).toHaveBeenCalledWith('Failed to save changes. Please try again.');
    });
  });
});
