import { mount } from '@vue/test-utils';
import { describe, it, expect, beforeEach } from 'vitest';
import RewardSystem from '@/components/rewards/RewardSystem.vue';

describe('RewardSystem.vue', () => {
  let wrapper;

  beforeEach(() => {
    wrapper = mount(RewardSystem, {
      props: {
        childId: '1'
      }
    });
  });

  it('renders achievements correctly', () => {
    const achievements = wrapper.findAll('.bg-gray-50.rounded-lg');
    expect(achievements.length).toBe(3); // We have 3 default achievements
    
    // Check first achievement
    const firstAchievement = achievements[0];
    expect(firstAchievement.text()).toContain('Task Master');
    expect(firstAchievement.text()).toContain('Complete 50 tasks');
  });

  it('displays correct bonus points', () => {
    // Check streak bonus
    expect(wrapper.text()).toContain('Task Streak');
    expect(wrapper.text()).toContain('+5 points');

    // Check early completion bonus
    expect(wrapper.text()).toContain('Early Bird');
    expect(wrapper.text()).toContain('+2 points/task');

    // Check perfect week bonus
    expect(wrapper.text()).toContain('Perfect Week');
    expect(wrapper.text()).toContain('+10 points');
  });

  it('handles reward claiming', async () => {
    // Set available points
    wrapper.vm.availablePoints = 30;

    // Try to claim a reward
    const reward = wrapper.findAll('.border.rounded-lg')[0]; // First reward
    await reward.trigger('click');

    // Check if points were deducted
    expect(wrapper.vm.availablePoints).toBeLessThan(30);
  });

  it('disables rewards when not enough points', async () => {
    // Set low available points
    wrapper.vm.availablePoints = 5;

    // Check if expensive rewards are disabled
    const expensiveReward = wrapper.findAll('.border.rounded-lg')
      .find(r => r.text().includes('30 points'));
    expect(expensiveReward.classes()).toContain('opacity-50');
    expect(expensiveReward.classes()).toContain('cursor-not-allowed');
  });

  it('tracks achievement progress', () => {
    const progressBars = wrapper.findAll('.bg-indigo-500');
    expect(progressBars.length).toBeGreaterThan(0);
    
    // Check if at least one achievement shows progress
    const hasProgress = progressBars.some(bar => {
      const width = bar.attributes('style');
      return width && parseInt(width.match(/\d+/)[0]) > 0;
    });
    expect(hasProgress).toBe(true);
  });

  it('displays streak calendar correctly', () => {
    const streakDays = wrapper.findAll('.w-8.h-8');
    expect(streakDays.length).toBe(7); // 7 days in streak

    // Check if some days are marked as completed
    const completedDays = streakDays.filter(day => 
      day.classes().includes('bg-green-500')
    );
    expect(completedDays.length).toBeGreaterThan(0);
  });

  it('emits claim-reward event', async () => {
    // Set enough points
    wrapper.vm.availablePoints = 50;

    // Find and click a reward
    const reward = wrapper.findAll('.border.rounded-lg')[0];
    await reward.trigger('click');

    // Check if event was emitted
    expect(wrapper.emitted('claim-reward')).toBeTruthy();
    expect(wrapper.emitted('claim-reward')[0][0]).toHaveProperty('name');
    expect(wrapper.emitted('claim-reward')[0][0]).toHaveProperty('cost');
  });
});
