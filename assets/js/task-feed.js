/**
 * TaskDoor — Live Task Feed
 * Polls for open tasks, diffs, and animates changes.
 */

// Exposed for runner/feed.php
window.taskFeed = (function() {
  let pollInterval = null;
  let knownTaskIds = new Set();
  let onUpdate = null;

  function start(fetchFn, callback, intervalMs = 4000) {
    onUpdate = callback;
    poll(fetchFn);
    pollInterval = setInterval(() => poll(fetchFn), intervalMs);
  }

  function stop() {
    if (pollInterval) clearInterval(pollInterval);
  }

  async function poll(fetchFn) {
    try {
      const tasks = await fetchFn();
      const newIds = new Set(tasks.map(t => t.id));

      // Detect new tasks (after initial load)
      if (knownTaskIds.size > 0) {
        const added   = tasks.filter(t => !knownTaskIds.has(t.id));
        const removed = [...knownTaskIds].filter(id => !newIds.has(id));

        if (added.length > 0 && onUpdate) {
          showToast(`${added.length} new task${added.length > 1 ? 's' : ''} available! ⚡`, 'Race to accept!', 'info');
        }

        // Animate removed task cards as "taken"
        removed.forEach(id => {
          const card = document.querySelector(`[data-id="${id}"]`);
          if (card) {
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95)';
            setTimeout(() => card.remove(), 400);
          }
        });
      }

      knownTaskIds = newIds;
      if (onUpdate) onUpdate(tasks, knownTaskIds.size === 0);
    } catch (e) {
      console.error('[TaskFeed] Poll error:', e);
    }
  }

  return { start, stop };
})();
