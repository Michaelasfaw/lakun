/**
 * TaskDoor — Swipe-to-Complete
 * Touch events: drag right >= 70% of container width to trigger completion.
 */

function initSwipeToComplete(containerId, thumbId, fillId, onComplete) {
  const container = document.getElementById(containerId);
  const thumb     = document.getElementById(thumbId);
  const fill      = document.getElementById(fillId);

  if (!container || !thumb) return;

  let startX = 0;
  let currentX = 0;
  let isDragging = false;
  const THRESHOLD = 0.70; // 70% of container width

  function getMaxTravel() {
    return container.offsetWidth - thumb.offsetWidth - 16; // padding
  }

  function setProgress(x) {
    const max = getMaxTravel();
    const clamped = Math.max(0, Math.min(x, max));
    const pct = clamped / max;
    thumb.style.transform = `translateX(${clamped}px)`;
    if (fill) {
      fill.style.transform = `scaleX(${pct})`;
      fill.style.opacity = (0.15 + pct * 0.5).toString();
    }
    return pct;
  }

  function reset() {
    thumb.style.transition = 'transform 0.3s cubic-bezier(0.36,0.07,0.19,0.97)';
    if (fill) fill.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
    setProgress(0);
    setTimeout(() => {
      thumb.style.transition = '';
      if (fill) fill.style.transition = '';
    }, 300);
  }

  function complete() {
    thumb.style.transition = 'transform 0.2s ease';
    setProgress(getMaxTravel());
    container.style.background = '#10B981';
    thumb.textContent = '🎉';
    setTimeout(() => {
      if (typeof onComplete === 'function') onComplete();
    }, 400);
  }

  // Touch events
  thumb.addEventListener('touchstart', (e) => {
    startX = e.touches[0].clientX;
    isDragging = true;
    thumb.style.cursor = 'grabbing';
  }, { passive: true });

  document.addEventListener('touchmove', (e) => {
    if (!isDragging) return;
    currentX = e.touches[0].clientX - startX;
    setProgress(currentX);
  }, { passive: true });

  document.addEventListener('touchend', () => {
    if (!isDragging) return;
    isDragging = false;
    thumb.style.cursor = 'grab';
    const pct = currentX / getMaxTravel();
    if (pct >= THRESHOLD) {
      complete();
    } else {
      reset();
    }
  });

  // Mouse events (desktop testing)
  thumb.addEventListener('mousedown', (e) => {
    startX = e.clientX;
    isDragging = true;
    thumb.style.cursor = 'grabbing';
    e.preventDefault();
  });

  document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    currentX = e.clientX - startX;
    setProgress(currentX);
  });

  document.addEventListener('mouseup', () => {
    if (!isDragging) return;
    isDragging = false;
    thumb.style.cursor = 'grab';
    const max = getMaxTravel();
    const pct = Math.max(0, Math.min(currentX, max)) / max;
    if (pct >= THRESHOLD) {
      complete();
    } else {
      reset();
    }
  });
}
