const swipe = {
  mounted(el, binding) {
    const { value } = binding;
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;

    const minSwipeDistance = 50; // Minimum distance for a swipe
    const maxSwipeTime = 300; // Maximum time for a swipe in milliseconds
    let touchStartTime = 0;

    el.addEventListener('touchstart', (e) => {
      touchStartX = e.touches[0].clientX;
      touchStartY = e.touches[0].clientY;
      touchStartTime = Date.now();
    }, { passive: true });

    el.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].clientX;
      touchEndY = e.changedTouches[0].clientY;
      const touchEndTime = Date.now();

      const deltaX = touchEndX - touchStartX;
      const deltaY = touchEndY - touchStartY;
      const deltaTime = touchEndTime - touchStartTime;

      // Only trigger if the swipe was fast enough
      if (deltaTime > maxSwipeTime) return;

      // Calculate the absolute distances
      const absX = Math.abs(deltaX);
      const absY = Math.abs(deltaY);

      // Only trigger if the swipe was long enough
      if (absX < minSwipeDistance && absY < minSwipeDistance) return;

      // Determine if the swipe was horizontal or vertical
      const isHorizontal = absX > absY;

      if (isHorizontal) {
        if (deltaX > 0 && value.right) {
          value.right();
        } else if (deltaX < 0 && value.left) {
          value.left();
        }
      } else {
        if (deltaY > 0 && value.down) {
          value.down();
        } else if (deltaY < 0 && value.up) {
          value.up();
        }
      }
    }, { passive: true });
  }
};

export default swipe;

