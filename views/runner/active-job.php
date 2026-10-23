<?php
$pageTitle = 'Active Job';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-lg mx-auto">
  <!-- Loading -->
  <div id="loading-state" class="space-y-4">
    <div class="skeleton h-48 rounded-2xl"></div>
    <div class="skeleton h-32 rounded-2xl"></div>
    <div class="skeleton h-16 rounded-2xl"></div>
  </div>

  <!-- No active job -->
  <div id="no-job" class="hidden text-center py-16">
    <div class="text-6xl mb-4">🎯</div>
    <h3 class="text-xl font-bold text-charcoal dark:text-cream mb-2">No active job</h3>
    <p class="text-charcoal-muted dark:text-gray-400 text-sm mb-6">Head to the feed and grab a task!</p>
    <a href="<?= $appUrl ?>/runner/feed"
       class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-bold px-6 py-3 rounded-xl text-sm btn-primary">
      Go to Feed ⚡
    </a>
  </div>

  <!-- Active job card -->
  <div id="job-card" class="hidden">
    <!-- Task info -->
    <div class="bg-white dark:bg-charcoal-light rounded-2xl p-6 border border-gray-100 dark:border-charcoal-lighter shadow-sm mb-4">
      <div class="flex items-start gap-4">
        <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-3xl" id="job-icon">📌</div>
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-1">
            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-semibold">In Progress</span>
            <span id="job-urgency" class="hidden text-xs font-bold text-red-600">🔴 URGENT</span>
          </div>
          <h2 class="text-xl font-extrabold text-charcoal dark:text-cream" id="job-title">Loading…</h2>
          <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1" id="job-desc"></p>
        </div>
      </div>

      <div class="mt-4 grid grid-cols-2 gap-3">
        <div class="bg-gray-50 dark:bg-charcoal rounded-xl p-3">
          <p class="text-xs text-charcoal-muted dark:text-gray-400 font-medium mb-0.5">Deliver to</p>
          <p class="text-sm font-bold text-charcoal dark:text-cream" id="job-unit">—</p>
        </div>
        <div class="bg-amber-50 dark:bg-amber-900/10 rounded-xl p-3">
          <p class="text-xs text-charcoal-muted dark:text-gray-400 font-medium mb-0.5">Tip offered</p>
          <p class="text-sm font-bold text-amber-600 dark:text-amber-400" id="job-tip">None</p>
        </div>
      </div>
    </div>

    <!-- Resident contact -->
    <div class="bg-white dark:bg-charcoal-light rounded-2xl p-5 border border-gray-100 dark:border-charcoal-lighter shadow-sm mb-4">
      <p class="text-xs font-semibold text-charcoal-muted dark:text-gray-400 uppercase tracking-wide mb-3">Resident Contact</p>
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-full bg-amber-100 dark:bg-amber-900/20 flex items-center justify-center text-amber-700 dark:text-amber-400 font-bold text-lg" id="resident-avatar">A</div>
        <div class="flex-1">
          <p class="font-semibold text-charcoal dark:text-cream" id="resident-name">—</p>
          <p class="text-sm text-charcoal-muted dark:text-gray-400" id="resident-unit">—</p>
        </div>
        <div class="flex gap-2">
          <a id="resident-phone-link" href="#"
             class="w-9 h-9 bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 rounded-full flex items-center justify-center hover:bg-emerald-200 transition-colors" title="Call">
            📞
          </a>
          <a id="chat-link" href="#"
             class="w-9 h-9 bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-full flex items-center justify-center hover:bg-blue-200 transition-colors" title="Chat">
            💬
          </a>
        </div>
      </div>
    </div>

    <!-- Status: waiting for resident confirm -->
    <div id="waiting-confirm" class="hidden mb-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-2xl p-5 text-center">
      <div class="text-4xl mb-2">⏳</div>
      <h3 class="font-bold text-amber-800 dark:text-amber-300 mb-1">Waiting for resident confirmation</h3>
      <p class="text-sm text-amber-700 dark:text-amber-400">The resident will confirm once they receive the item.</p>
    </div>

    <!-- Swipe to complete -->
    <div id="complete-section" class="mb-4">
      <!-- Desktop: button -->
      <button id="done-btn" onclick="markDone()"
              class="hidden md:flex btn-primary w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-2xl items-center justify-center gap-2 text-base">
        ✅ Mark Job as Done
      </button>

      <!-- Mobile: swipe -->
      <div id="swipe-container" class="md:hidden relative bg-gray-100 dark:bg-charcoal-lighter rounded-2xl h-16 overflow-hidden select-none">
        <div class="absolute inset-0 flex items-center justify-center">
          <p class="text-sm font-semibold text-charcoal-muted dark:text-gray-400 pointer-events-none">Swipe right to complete →</p>
        </div>
        <div id="swipe-thumb" class="absolute left-2 top-2 bottom-2 w-12 bg-emerald-500 rounded-xl flex items-center justify-center text-white font-bold text-lg cursor-grab active:cursor-grabbing shadow-md">
          ✅
        </div>
        <div id="swipe-fill" class="absolute inset-0 bg-emerald-500 rounded-2xl origin-left scale-x-0 transition-none opacity-20"></div>
      </div>
    </div>

    <!-- Cancel -->
    <div class="text-center">
      <button onclick="cancelCurrentJob()" class="text-sm text-red-500 hover:text-red-600 transition-colors">
        Cancel this job
      </button>
    </div>
  </div>
</div>

<style>
.skeleton{animation:skeletonPulse 1.5s ease-in-out infinite;background:linear-gradient(90deg,#e5e7eb 25%,#f3f4f6 50%,#e5e7eb 75%);background-size:200% 100%}
.dark .skeleton{background:linear-gradient(90deg,#3A3A3C 25%,#4A4A4C 50%,#3A3A3C 75%);background-size:200% 100%}
@keyframes skeletonPulse{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>

<script src="<?= $appUrl ?>/assets/js/swipe.js"></script>
<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN = localStorage.getItem('td_token');
const categoryIcons = {grocery:'🛒',package:'📦',document:'📄',food:'🍔',other:'📌'};
let currentTaskId = null;

async function loadActiveJob() {
  try {
    const res = await fetch(APP_URL + '/api/v1/users/profile.php', { headers:{'Authorization':'Bearer '+TOKEN} });
    const data = await res.json();
    // Get active task by checking running tasks
    const taskRes = await fetch(APP_URL + '/api/v1/tasks/index.php', { headers:{'Authorization':'Bearer '+TOKEN} });
    const taskData = await taskRes.json();
    const active = (taskData.data || []).find(t => ['accepted','runner_done'].includes(t.status));

    document.getElementById('loading-state').classList.add('hidden');

    if (!active) {
      document.getElementById('no-job').classList.remove('hidden');
      return;
    }

    currentTaskId = active.id;
    document.getElementById('job-card').classList.remove('hidden');

    // Populate
    document.getElementById('job-icon').textContent = categoryIcons[active.category] || '📌';
    document.getElementById('job-title').textContent = active.title;
    document.getElementById('job-desc').textContent = active.description || '';
    document.getElementById('job-unit').textContent = 'Unit ' + active.unit_number;
    document.getElementById('job-tip').textContent = active.tip || 'None';
    if (active.urgency === 'urgent') document.getElementById('job-urgency').classList.remove('hidden');

    const name = active.resident_name || 'Resident';
    document.getElementById('resident-name').textContent = name;
    document.getElementById('resident-unit').textContent = 'Unit ' + active.unit_number;
    document.getElementById('resident-avatar').textContent = name.charAt(0).toUpperCase();
    if (active.resident_phone) {
      document.getElementById('resident-phone-link').href = 'tel:' + active.resident_phone;
    }
    document.getElementById('chat-link').href = APP_URL + '/chat/' + active.id;

    if (active.status === 'runner_done') {
      document.getElementById('complete-section').classList.add('hidden');
      document.getElementById('waiting-confirm').classList.remove('hidden');
    } else {
      document.getElementById('done-btn').classList.remove('hidden');
      initSwipe();
    }
  } catch (e) {
    console.error(e);
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('no-job').classList.remove('hidden');
  }
}

async function markDone() {
  const res = await fetch(APP_URL + '/api/v1/tasks/complete.php', {
    method: 'POST',
    headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json' },
    body: JSON.stringify({ task_id: currentTaskId }),
  });
  const data = await res.json();
  if (data.status === 'success') {
    showToast('Marked as done! ✅', 'Waiting for resident to confirm.', 'success');
    document.getElementById('complete-section').classList.add('hidden');
    document.getElementById('waiting-confirm').classList.remove('hidden');
  } else {
    showToast(data.message || 'Error', '', 'error');
  }
}

async function cancelCurrentJob() {
  if (!confirm('Cancel this job? It will be re-posted.')) return;
  const res = await fetch(APP_URL + '/api/v1/tasks/cancel.php', {
    method: 'POST',
    headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json' },
    body: JSON.stringify({ task_id: currentTaskId }),
  });
  const data = await res.json();
  if (data.status === 'success') {
    showToast('Job cancelled', '', 'success');
    setTimeout(() => window.location.href = APP_URL + '/runner/feed', 700);
  } else {
    showToast(data.message || 'Error', '', 'error');
  }
}

function initSwipe() {
  initSwipeToComplete('swipe-container', 'swipe-thumb', 'swipe-fill', markDone);
}

loadActiveJob();
setInterval(loadActiveJob, 10000);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
