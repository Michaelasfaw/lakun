<?php
$pageTitle = 'History';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-2xl mx-auto">
  <div class="mb-6">
    <h1 class="text-2xl font-extrabold text-charcoal dark:text-cream">Job History</h1>
    <p class="text-charcoal-muted dark:text-gray-400 text-sm mt-0.5">All your completed and past jobs</p>
  </div>

  <!-- Stats bar -->
  <div id="stats-bar" class="grid grid-cols-3 gap-3 mb-6">
    <div class="skeleton h-20 rounded-2xl"></div>
    <div class="skeleton h-20 rounded-2xl"></div>
    <div class="skeleton h-20 rounded-2xl"></div>
  </div>

  <!-- Jobs list -->
  <div id="history-list" class="space-y-3">
    <div class="skeleton h-24 rounded-2xl"></div>
    <div class="skeleton h-24 rounded-2xl"></div>
    <div class="skeleton h-24 rounded-2xl"></div>
  </div>

  <div id="empty-state" class="hidden text-center py-16">
    <div class="text-6xl mb-4">📜</div>
    <h3 class="text-lg font-bold text-charcoal dark:text-cream">No completed jobs yet</h3>
    <p class="text-charcoal-muted dark:text-gray-400 text-sm mt-1">Accept your first task from the feed!</p>
    <a href="<?= $appUrl ?>/runner/feed" class="inline-block mt-4 bg-amber-600 hover:bg-amber-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm btn-primary">Go to Feed ⚡</a>
  </div>
</div>

<style>
.skeleton{animation:skeletonPulse 1.5s ease-in-out infinite;background:linear-gradient(90deg,#e5e7eb 25%,#f3f4f6 50%,#e5e7eb 75%);background-size:200% 100%}
.dark .skeleton{background:linear-gradient(90deg,#3A3A3C 25%,#4A4A4C 50%,#3A3A3C 75%);background-size:200% 100%}
@keyframes skeletonPulse{0%{background-position:200% 0}100%{background-position:-200% 0}}
.status-completed{border-left-color:#6B7280}.status-cancelled{border-left-color:#EF4444}.status-accepted{border-left-color:#3B82F6}
@keyframes cardAppear{from{transform:translateY(12px);opacity:0}to{transform:translateY(0);opacity:1}}
.animate-card-appear{animation:cardAppear .3s ease-out}
.task-card{transition:transform .2s,box-shadow .2s}.task-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.08)}
</style>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN = localStorage.getItem('td_token');
const catIcons = {grocery:'🛒',package:'📦',document:'📄',food:'🍔',other:'📌'};
const statusLabels = {
  completed:'Completed', cancelled:'Cancelled', accepted:'In Progress', runner_done:'Awaiting Confirm', open:'Open'
};
const statusCls = {
  completed:'text-gray-600 dark:text-gray-400',cancelled:'text-red-600 dark:text-red-400',
  accepted:'text-blue-600 dark:text-blue-400',runner_done:'text-amber-600 dark:text-amber-400'
};

function timeAgo(ts) {
  const d = Math.floor((Date.now() - new Date(ts)) / 1000);
  if (d < 60) return 'just now';
  if (d < 3600) return Math.floor(d/60) + 'm ago';
  if (d < 86400) return Math.floor(d/3600) + 'h ago';
  return Math.floor(d/86400) + 'd ago';
}

async function load() {
  const [taskRes, statsRes] = await Promise.all([
    fetch(APP_URL + '/api/v1/tasks/index.php', { headers:{'Authorization':'Bearer '+TOKEN} }),
    fetch(APP_URL + '/api/v1/ratings/runner_stats.php', { headers:{'Authorization':'Bearer '+TOKEN} }),
  ]);
  const taskData = await taskRes.json();
  const statsData = await statsRes.json();
  const tasks = taskData.data || [];
  const stats = statsData.data?.stats || {};

  // Stats bar
  const completed = tasks.filter(t => t.status === 'completed').length;
  const earned = tasks.filter(t => t.status === 'completed' && t.tip).length;
  const rating = parseFloat(stats.avg_score) || 0;
  document.getElementById('stats-bar').innerHTML = `
    <div class="bg-white dark:bg-charcoal-light rounded-2xl p-4 border border-gray-100 dark:border-charcoal-lighter text-center">
      <p class="text-2xl font-extrabold text-emerald-600">${completed}</p>
      <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5 font-medium">Completed</p>
    </div>
    <div class="bg-white dark:bg-charcoal-light rounded-2xl p-4 border border-gray-100 dark:border-charcoal-lighter text-center">
      <p class="text-2xl font-extrabold text-amber-600">${earned}</p>
      <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5 font-medium">With Tip</p>
    </div>
    <div class="bg-white dark:bg-charcoal-light rounded-2xl p-4 border border-gray-100 dark:border-charcoal-lighter text-center">
      <p class="text-2xl font-extrabold text-amber-500">${rating > 0 ? rating.toFixed(1) + '⭐' : '—'}</p>
      <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5 font-medium">Rating</p>
    </div>`;

  const list = document.getElementById('history-list');
  const empty = document.getElementById('empty-state');
  if (tasks.length === 0) {
    list.innerHTML = '';
    empty.classList.remove('hidden');
    return;
  }
  empty.classList.add('hidden');
  list.innerHTML = tasks.map(t => `
    <div class="task-card bg-white dark:bg-charcoal-light rounded-2xl p-4 border-l-4 status-${t.status} border border-gray-100 dark:border-charcoal-lighter shadow-sm animate-card-appear">
      <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-xl flex-shrink-0">${catIcons[t.category]||'📌'}</div>
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-2">
            <h3 class="font-semibold text-charcoal dark:text-cream text-sm">${t.title}</h3>
            <span class="text-xs ${statusCls[t.status]||''} font-semibold flex-shrink-0">${statusLabels[t.status]||t.status}</span>
          </div>
          <div class="flex items-center gap-3 mt-1.5 flex-wrap">
            <span class="text-xs text-charcoal-muted dark:text-gray-400">${timeAgo(t.created_at)}</span>
            ${t.resident_name ? `<span class="text-xs text-charcoal-muted dark:text-gray-400">For ${t.resident_name}</span>` : ''}
            ${t.tip ? `<span class="text-xs font-bold text-amber-600 dark:text-amber-400">💰 ${t.tip}</span>` : ''}
          </div>
        </div>
      </div>
      ${t.status === 'completed' ? `
        <div class="mt-3 flex items-center justify-between">
          <a href="${APP_URL}/chat/${t.id}" class="text-xs text-blue-600 dark:text-blue-400 font-medium hover:underline">💬 View chat</a>
          <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">✅ Done</span>
        </div>` : ''}
    </div>`).join('');
}

load();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
