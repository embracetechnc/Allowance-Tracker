<template>
  <div>
    <!-- Desktop Navigation -->
    <div class="hidden sm:block">
      <DesktopNav />
    </div>

    <!-- Mobile Navigation -->
    <div class="sm:hidden">
      <MobileNav />
    </div>
  </div>
</template>

<script>
import DesktopNav from './DesktopNav.vue';
import MobileNav from './MobileNav.vue';

export default {
  name: 'Navigation',
  components: {
    DesktopNav,
    MobileNav
  }
};
</script>

