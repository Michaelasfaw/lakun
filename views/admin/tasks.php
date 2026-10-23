<?php
$pageTitle = 'All Tasks';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-4xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-extrabold text-charcoal dark:text-cream">All Tasks</h1>
      <p class="text-charcoal-muted dark:text-gray-400 text-sm mt-0.5">Platform-wide task monitor</p>
    </div>
    <select id="status-filter" onchange="loadTasks()"
            class="bg-white dark:bg-charcoal-light border border-gray-200 dark:border-charcoal-lighter text-charcoal dark:text-cream text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400">
      <option value="">All statuses</option>
      <option value="open">Open</option>
      <option value="accepted">In Progress</option>
      <option value="runner_done">Awaiting Confirm</option>
      <option value="completed">Completed</option>
      <option value="cancelled">Cancelled</option>
    </select>
  </div>

  <div id="tasks-container" class="space-y-2">
    <?php for($i=0;$i<5;$i++): ?>
      <div class="skeleton h-16 rounded-2xl"></div>
    <?php endfor; ?>
  </div>
</div>

<style>
.skeleton{animation:skeletonPulse 1.5s ease-in-out infinite;background:linear-gradient(90deg,#e5e7eb 25%,#f3f4f6 50%,#e5e7eb 75%);background-size:200% 100%}
.dark .skeleton{background:linear-gradient(90deg,#3A3A3C 25%,#4A4A4C 50%,#3A3A3C 75%);background-size:200% 100%}
@keyframes skeletonPulse{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN = localStorage.getItem('td_token');
const statusCls = {open:'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20',accepted:'text-blue-600 bg-blue-50 dark:bg-blue-900/20',runner_done:'text-amber-600 bg-amber-50 dark:bg-amber-900/20',completed:'text-gray-600 bg-gray-100 dark:bg-gray-800',cancelled:'text-red-600 bg-red-50 dark:bg-red-900/20'};
const catIcons = {grocery:'🛒',package:'📦',document:'📄',food:'🍔',other:'📌'};

function timeAgo(ts) {
  const d = Math.floor((Date.now()-new Date(ts))/1000);
  if(d<60)return'just now';if(d<3600)return Math.floor(d/60)+'m ago';
  if(d<86400)return Math.floor(d/3600)+'h ago';return Math.floor(d/86400)+'d ago';
}

async function loadTasks() {
  const status = document.getElementById('status-filter').value;
  const url = APP_URL + '/api/v1/admin/tasks.php' + (status ? '?status=' + status : '');
  const res = await fetch(url, { headers:{'Authorization':'Bearer '+TOKEN} });
  const data = await res.json();
  const tasks = data.data?.tasks || [];
  const container = document.getElementById('tasks-container');

  if (tasks.length === 0) {
    container.innerHTML = '<div class="text-center py-8 text-charcoal-muted dark:text-gray-400 text-sm">No tasks found.</div>';
    return;
  }

  container.innerHTML = `
    <div class="bg-white dark:bg-charcoal-light rounded-2xl border border-gray-100 dark:border-charcoal-lighter shadow-sm overflow-hidden">
      ${tasks.map(t => `
        <div class="p-4 flex items-start gap-3 border-b border-gray-50 dark:border-charcoal-lighter last:border-0 hover:bg-gray-50 dark:hover:bg-charcoal-lighter transition-colors">
          <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-lg flex-shrink-0">${catIcons[t.category]||'📌'}</div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-sm font-semibold text-charcoal dark:text-cream">${t.title}</span>
              ${t.urgency==='urgent'?'<span class="text-xs text-red-600 font-bold">🔴</span>':''}
            </div>
            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
              <span class="text-xs text-charcoal-muted dark:text-gray-400">👤 ${t.resident_name}</span>
              <span class="text-xs text-charcoal-muted dark:text-gray-400">🏢 ${t.building_name}</span>
              ${t.runner_name?`<span class="text-xs text-charcoal-muted dark:text-gray-400">🏃 ${t.runner_name}</span>`:''}
              <span class="text-xs text-charcoal-muted dark:text-gray-400">${timeAgo(t.created_at)}</span>
            </div>
          </div>
          <span class="text-xs px-2 py-1 rounded-full font-semibold flex-shrink-0 ${statusCls[t.status]||''}">${t.status.replace('_',' ')}</span>
        </div>`).join('')}
    </div>`;
}

loadTasks();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
