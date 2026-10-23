<?php
$pageTitle = 'Dashboard';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-2xl mx-auto">
  <!-- Greeting -->
  <div class="mb-6">
    <h1 class="text-2xl font-extrabold text-charcoal dark:text-cream">
      Hey, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?> 👋
    </h1>
    <p class="text-charcoal-muted dark:text-gray-400 mt-0.5 text-sm">What do you need done today?</p>
  </div>

  <!-- Quick action card -->
  <a href="<?= $appUrl ?>/resident/create"
     class="block bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-lg mb-6 hover:shadow-xl transition-all hover:-translate-y-0.5">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold">Post a New Task</h2>
        <p class="text-amber-100 text-sm mt-0.5">Groceries, deliveries, errands — all sorted.</p>
      </div>
      <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-3xl">➕</div>
    </div>
  </a>

  <!-- Stats row -->
  <div id="stats-row" class="grid grid-cols-3 gap-3 mb-6">
    <div class="skeleton h-20 rounded-2xl"></div>
    <div class="skeleton h-20 rounded-2xl"></div>
    <div class="skeleton h-20 rounded-2xl"></div>
  </div>

  <!-- Active Tasks -->
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-bold text-charcoal dark:text-cream">Active Tasks</h2>
    <a href="<?= $appUrl ?>/resident/tasks" class="text-sm text-amber-600 dark:text-amber-400 font-semibold hover:underline">View all →</a>
  </div>

  <div id="active-tasks" class="space-y-3">
    <!-- Skeleton loaders -->
    <div class="skeleton h-28 rounded-2xl"></div>
    <div class="skeleton h-28 rounded-2xl"></div>
  </div>

  <!-- Empty state (hidden by default) -->
  <div id="empty-state" class="hidden text-center py-16">
    <div class="text-6xl mb-4">📭</div>
    <h3 class="text-lg font-bold text-charcoal dark:text-cream mb-2">No active tasks</h3>
    <p class="text-charcoal-muted dark:text-gray-400 text-sm mb-6">Post your first task and let runners handle it!</p>
    <a href="<?= $appUrl ?>/resident/create"
       class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-bold px-6 py-3 rounded-xl text-sm transition-colors">
      Post a Task
    </a>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN = localStorage.getItem('td_token');

const categoryIcons = {grocery:'🛒',package:'📦',document:'📄',food:'🍔',other:'📌'};
const statusLabels = {
  open:        {label:'Open',         cls:'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'},
  accepted:    {label:'In Progress',  cls:'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'},
  runner_done: {label:'Confirm Now!', cls:'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 animate-pulse'},
  completed:   {label:'Completed',    cls:'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'},
  cancelled:   {label:'Cancelled',    cls:'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'},
};

function timeAgo(ts) {
  const d = Math.floor((Date.now() - new Date(ts)) / 1000);
  if (d < 60) return 'just now';
  if (d < 3600) return Math.floor(d/60) + 'm ago';
  if (d < 86400) return Math.floor(d/3600) + 'h ago';
  return Math.floor(d/86400) + 'd ago';
}

function taskCardHtml(t) {
  const badge = statusLabels[t.status] || statusLabels.open;
  const isUrgent = t.urgency === 'urgent';
  const needsConfirm = t.status === 'runner_done';
  return `
    <div class="task-card bg-white dark:bg-charcoal-light rounded-2xl p-5 border-l-4 status-${t.status} shadow-sm border border-gray-100 dark:border-charcoal-lighter animate-card-appear" data-id="${t.id}">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-start gap-3 flex-1 min-w-0">
          <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-xl flex-shrink-0">${categoryIcons[t.category]||'📌'}</div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="font-semibold text-charcoal dark:text-cream">${t.title}</h3>
              ${isUrgent ? '<span class="text-xs font-bold text-red-600 dark:text-red-400 animate-pulse">🔴 URGENT</span>' : ''}
            </div>
            ${t.description ? `<p class="text-sm text-charcoal-muted dark:text-gray-400 mt-0.5 line-clamp-2">${t.description}</p>` : ''}
            <div class="flex items-center gap-3 mt-2 flex-wrap">
              <span class="text-xs px-2 py-0.5 rounded-full ${badge.cls} font-medium">${badge.label}</span>
              ${t.runner_name ? `<span class="text-xs text-charcoal-muted dark:text-gray-400">Runner: <strong>${t.runner_name}</strong></span>` : ''}
              ${t.tip ? `<span class="text-xs font-semibold text-amber-600 dark:text-amber-400">💰 ${t.tip}</span>` : ''}
            </div>
          </div>
        </div>
        <span class="text-xs text-charcoal-muted dark:text-gray-500 flex-shrink-0">${timeAgo(t.created_at)}</span>
      </div>
      ${needsConfirm ? `
        <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/10 rounded-xl border border-amber-200 dark:border-amber-800">
          <p class="text-sm text-amber-800 dark:text-amber-300 font-medium mb-2">🎉 Runner marked this done!</p>
          <div class="flex gap-2">
            <button onclick="confirmTask(${t.id})" class="btn-primary flex-1 bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 rounded-xl text-sm">Confirm & Close ✅</button>
            ${t.runner_phone ? `<a href="tel:${t.runner_phone}" class="flex-1 text-center bg-gray-100 dark:bg-charcoal-lighter hover:bg-gray-200 dark:hover:bg-charcoal text-charcoal dark:text-cream font-semibold py-2.5 rounded-xl text-sm transition-colors">📞 Call Runner</a>` : ''}
          </div>
        </div>
      ` : ''}
      ${t.status === 'open' ? `
        <div class="mt-3 flex justify-end">
          <button onclick="cancelTask(${t.id})" class="text-xs text-red-500 hover:text-red-600 transition-colors">Cancel task</button>
        </div>
      ` : ''}
    </div>`;
}

async function loadTasks() {
  try {
    const res = await fetch(APP_URL + '/api/v1/tasks/index.php', {
      headers: { 'Authorization': 'Bearer ' + TOKEN }
    });
    const data = await res.json();
    const tasks = (data.data || []).filter(t => !['completed','cancelled'].includes(t.status));
    const all   = (data.data || []);

    // Update stats
    const statsRow = document.getElementById('stats-row');
    const total = all.length;
    const active = all.filter(t => ['open','accepted','runner_done'].includes(t.status)).length;
    const done   = all.filter(t => t.status === 'completed').length;
    statsRow.innerHTML = `
      <div class="bg-white dark:bg-charcoal-light rounded-2xl p-4 border border-gray-100 dark:border-charcoal-lighter text-center">
        <p class="text-2xl font-extrabold text-amber-600">${total}</p>
        <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5 font-medium">Total</p>
      </div>
      <div class="bg-white dark:bg-charcoal-light rounded-2xl p-4 border border-gray-100 dark:border-charcoal-lighter text-center">
        <p class="text-2xl font-extrabold text-blue-600">${active}</p>
        <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5 font-medium">Active</p>
      </div>
      <div class="bg-white dark:bg-charcoal-light rounded-2xl p-4 border border-gray-100 dark:border-charcoal-lighter text-center">
        <p class="text-2xl font-extrabold text-emerald-600">${done}</p>
        <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5 font-medium">Done</p>
      </div>`;

    const container = document.getElementById('active-tasks');
    const emptyState = document.getElementById('empty-state');
    if (tasks.length === 0) {
      container.innerHTML = '';
      emptyState.classList.remove('hidden');
    } else {
      emptyState.classList.add('hidden');
      container.innerHTML = tasks.map(taskCardHtml).join('');
    }
  } catch (e) {
    console.error(e);
  }
}

async function confirmTask(taskId) {
  const res = await fetch(APP_URL + '/api/v1/tasks/confirm.php', {
    method: 'POST',
    headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json' },
    body: JSON.stringify({ task_id: taskId }),
  });
  const data = await res.json();
  if (data.status === 'success') {
    showToast('Task confirmed! 🎉', 'Great work everyone!', 'success');
    loadTasks();
  } else {
    showToast(data.message || 'Error', '', 'error');
  }
}

async function cancelTask(taskId) {
  if (!confirm('Cancel this task?')) return;
  const res = await fetch(APP_URL + '/api/v1/tasks/cancel.php', {
    method: 'POST',
    headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json' },
    body: JSON.stringify({ task_id: taskId }),
  });
  const data = await res.json();
  if (data.status === 'success') {
    showToast('Task cancelled', '', 'success');
    loadTasks();
  } else {
    showToast(data.message || 'Error', '', 'error');
  }
}

loadTasks();
setInterval(loadTasks, 8000);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
