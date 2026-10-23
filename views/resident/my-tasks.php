<?php
$pageTitle = 'My Tasks';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-2xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-extrabold text-charcoal dark:text-cream">My Tasks</h1>
      <p class="text-charcoal-muted dark:text-gray-400 text-sm mt-0.5">All your posted tasks</p>
    </div>
    <a href="<?= $appUrl ?>/resident/create"
       class="btn-primary bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold px-4 py-2.5 rounded-xl flex items-center gap-1.5">
      <span>➕</span> New Task
    </a>
  </div>

  <!-- Filter tabs -->
  <div class="flex gap-2 mb-5 overflow-x-auto pb-1" id="filter-tabs">
    <?php foreach (['all' => 'All', 'open' => 'Open', 'accepted' => 'Active', 'completed' => 'Done', 'cancelled' => 'Cancelled'] as $val => $label): ?>
      <button onclick="setFilter('<?= $val ?>')" data-filter="<?= $val ?>"
              class="filter-tab flex-shrink-0 px-4 py-2 rounded-full text-sm font-semibold border border-gray-200 dark:border-charcoal-lighter text-charcoal-muted dark:text-gray-400 hover:border-amber-400 hover:text-amber-600 transition-all <?= $val === 'all' ? 'bg-amber-600 text-white border-amber-600' : '' ?>">
        <?= $label ?>
      </button>
    <?php endforeach; ?>
  </div>

  <!-- Tasks list -->
  <div id="tasks-list" class="space-y-3">
    <div class="skeleton h-28 rounded-2xl"></div>
    <div class="skeleton h-28 rounded-2xl"></div>
    <div class="skeleton h-28 rounded-2xl"></div>
  </div>

  <div id="empty-state" class="hidden text-center py-16">
    <div class="text-5xl mb-4">📭</div>
    <h3 class="text-lg font-bold text-charcoal dark:text-cream">No tasks found</h3>
    <p class="text-charcoal-muted dark:text-gray-400 text-sm mt-1">Try a different filter or post a new task.</p>
  </div>
</div>

<!-- Rating Modal -->
<div id="rating-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-end md:items-center justify-center p-4">
  <div class="bg-white dark:bg-charcoal-light rounded-2xl p-6 w-full max-w-sm shadow-2xl animate-bounce-in">
    <h3 class="text-lg font-bold text-charcoal dark:text-cream mb-1">Rate your runner</h3>
    <p class="text-sm text-charcoal-muted dark:text-gray-400 mb-4">How was the service?</p>
    <input type="hidden" id="rating-task-id">
    <div class="flex justify-center gap-2 mb-4" id="star-row">
      <?php for ($i = 1; $i <= 5; $i++): ?>
        <button onclick="setRating(<?= $i ?>)" data-star="<?= $i ?>" class="star-btn text-4xl text-gray-300 hover:text-amber-400 transition-colors">★</button>
      <?php endfor; ?>
    </div>
    <textarea id="rating-comment" rows="2" placeholder="Any comments? (optional)"
              class="w-full bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl px-4 py-2.5 text-sm text-charcoal dark:text-cream placeholder-gray-400 mb-4 resize-none"></textarea>
    <div class="flex gap-2">
      <button onclick="closeRating()" class="flex-1 py-3 border border-gray-200 dark:border-charcoal-lighter text-charcoal dark:text-cream rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">Skip</button>
      <button onclick="submitRating()" class="flex-1 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-bold transition-colors btn-primary">Submit ⭐</button>
    </div>
  </div>
</div>

<style>
.skeleton { animation: skeletonPulse 1.5s ease-in-out infinite; background: linear-gradient(90deg,#e5e7eb 25%,#f3f4f6 50%,#e5e7eb 75%); background-size: 200% 100%; }
.dark .skeleton { background: linear-gradient(90deg,#3A3A3C 25%,#4A4A4C 50%,#3A3A3C 75%); background-size: 200% 100%; }
@keyframes skeletonPulse { 0%{background-position:200% 0}100%{background-position:-200% 0} }
.task-card { transition: transform .2s ease, box-shadow .2s ease; }
.task-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,.1); }
.status-open     { border-left-color: #10B981; }
.status-accepted { border-left-color: #3B82F6; }
.status-runner_done { border-left-color: #F59E0B; }
.status-completed   { border-left-color: #6B7280; }
.status-cancelled   { border-left-color: #EF4444; }
@keyframes cardAppear { from{transform:translateY(12px);opacity:0}to{transform:translateY(0);opacity:1} }
.animate-card-appear { animation: cardAppear .3s ease-out; }
</style>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN = localStorage.getItem('td_token');
let currentFilter = 'all';
let currentRating = 0;
let allTasks = [];

const categoryIcons = {grocery:'🛒',package:'📦',document:'📄',food:'🍔',other:'📌'};
const statusLabels = {
  open:        {label:'Open',         cls:'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'},
  accepted:    {label:'In Progress',  cls:'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'},
  runner_done: {label:'Awaiting Confirm',cls:'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'},
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

function setFilter(f) {
  currentFilter = f;
  document.querySelectorAll('.filter-tab').forEach(btn => {
    btn.classList.toggle('bg-amber-600', btn.dataset.filter === f);
    btn.classList.toggle('text-white', btn.dataset.filter === f);
    btn.classList.toggle('border-amber-600', btn.dataset.filter === f);
  });
  renderTasks();
}

function renderTasks() {
  const filtered = currentFilter === 'all' ? allTasks : allTasks.filter(t => t.status === currentFilter);
  const list = document.getElementById('tasks-list');
  const empty = document.getElementById('empty-state');
  if (filtered.length === 0) {
    list.innerHTML = '';
    empty.classList.remove('hidden');
  } else {
    empty.classList.add('hidden');
    list.innerHTML = filtered.map(t => {
      const badge = statusLabels[t.status] || statusLabels.open;
      const needsConfirm = t.status === 'runner_done';
      const canRate = t.status === 'completed' && !parseInt(t.rating_given);
      return `
        <div class="task-card bg-white dark:bg-charcoal-light rounded-2xl p-5 border-l-4 status-${t.status} shadow-sm border border-gray-100 dark:border-charcoal-lighter animate-card-appear">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3 flex-1 min-w-0">
              <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-xl flex-shrink-0">${categoryIcons[t.category]||'📌'}</div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2"><h3 class="font-semibold text-charcoal dark:text-cream">${t.title}</h3>${t.urgency==='urgent'?'<span class="text-xs font-bold text-red-600">🔴 URGENT</span>':''}</div>
                ${t.description?`<p class="text-sm text-charcoal-muted dark:text-gray-400 mt-0.5 line-clamp-2">${t.description}</p>`:''}
                <div class="flex items-center gap-3 mt-2 flex-wrap">
                  <span class="text-xs px-2 py-0.5 rounded-full ${badge.cls} font-medium">${badge.label}</span>
                  ${t.runner_name?`<span class="text-xs text-charcoal-muted dark:text-gray-400">🏃 ${t.runner_name}</span>`:''}
                  ${t.tip?`<span class="text-xs font-semibold text-amber-600 dark:text-amber-400">💰 ${t.tip}</span>`:''}
                </div>
              </div>
            </div>
            <span class="text-xs text-charcoal-muted dark:text-gray-500 flex-shrink-0">${timeAgo(t.created_at)}</span>
          </div>
          ${needsConfirm?`
            <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/10 rounded-xl border border-amber-200 dark:border-amber-800">
              <p class="text-sm text-amber-800 dark:text-amber-300 font-medium mb-2">🎉 Runner says it's done!</p>
              <button onclick="confirmTask(${t.id})" class="btn-primary w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 rounded-xl text-sm">Confirm & Close ✅</button>
            </div>`:''}
          ${canRate?`
            <div class="mt-3 flex justify-end">
              <button onclick="openRating(${t.id})" class="text-sm text-amber-600 dark:text-amber-400 font-semibold hover:underline">⭐ Rate your runner</button>
            </div>`:''}
          ${t.status==='open'?`<div class="mt-3 flex justify-end"><button onclick="cancelTask(${t.id})" class="text-xs text-red-500 hover:text-red-600 transition-colors">Cancel task</button></div>`:''}
        </div>`;
    }).join('');
  }
}

async function loadTasks() {
  const res = await fetch(APP_URL + '/api/v1/tasks/index.php', { headers: { 'Authorization': 'Bearer ' + TOKEN } });
  const data = await res.json();
  allTasks = data.data || [];
  renderTasks();
}

async function confirmTask(id) {
  const res = await fetch(APP_URL + '/api/v1/tasks/confirm.php', {
    method: 'POST', headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json' },
    body: JSON.stringify({ task_id: id }),
  });
  const data = await res.json();
  if (data.status === 'success') { showToast('Task confirmed! 🎉','','success'); loadTasks(); }
  else showToast(data.message || 'Error','','error');
}

async function cancelTask(id) {
  if (!confirm('Cancel this task?')) return;
  const res = await fetch(APP_URL + '/api/v1/tasks/cancel.php', {
    method: 'POST', headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json' },
    body: JSON.stringify({ task_id: id }),
  });
  const data = await res.json();
  if (data.status === 'success') { showToast('Cancelled','','success'); loadTasks(); }
  else showToast(data.message || 'Error','','error');
}

function openRating(taskId) {
  document.getElementById('rating-task-id').value = taskId;
  document.getElementById('rating-modal').classList.remove('hidden');
  setRating(0);
}
function closeRating() { document.getElementById('rating-modal').classList.add('hidden'); currentRating = 0; }
function setRating(n) {
  currentRating = n;
  document.querySelectorAll('.star-btn').forEach((btn, i) => {
    btn.classList.toggle('text-amber-400', i < n);
    btn.classList.toggle('text-gray-300', i >= n);
  });
}
async function submitRating() {
  if (!currentRating) { showToast('Please select a rating','','error'); return; }
  const taskId = document.getElementById('rating-task-id').value;
  const comment = document.getElementById('rating-comment').value;
  const res = await fetch(APP_URL + '/api/v1/ratings/create.php', {
    method: 'POST', headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json' },
    body: JSON.stringify({ task_id: parseInt(taskId), score: currentRating, comment }),
  });
  const data = await res.json();
  if (data.status === 'success') { showToast('Rating submitted! ⭐','Thanks!','success'); closeRating(); loadTasks(); }
  else showToast(data.message || 'Error','','error');
}

loadTasks();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
