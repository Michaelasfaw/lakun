<?php
$pageTitle = 'Task Feed';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-2xl mx-auto">
  <!-- Header -->
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-extrabold text-charcoal dark:text-cream flex items-center gap-2">
        Task Feed <span id="live-dot" class="inline-block w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse" title="Live"></span>
      </h1>
      <p class="text-charcoal-muted dark:text-gray-400 text-sm mt-0.5">First to accept wins the job</p>
    </div>
    <div class="flex items-center gap-2">
      <!-- Building selector -->
      <select id="building-select"
              class="bg-white dark:bg-charcoal-light border border-gray-200 dark:border-charcoal-lighter text-charcoal dark:text-cream text-sm rounded-xl px-3 py-2 font-medium focus:outline-none focus:ring-2 focus:ring-amber-400">
        <option value="">Loading…</option>
      </select>
    </div>
  </div>

  <!-- Active job alert -->
  <div id="active-job-banner" class="hidden mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-4 flex items-center gap-3">
    <span class="text-2xl">🏃</span>
    <div class="flex-1">
      <p class="text-sm font-bold text-blue-800 dark:text-blue-300">You have an active job!</p>
      <p class="text-xs text-blue-600 dark:text-blue-400">Complete it before accepting another.</p>
    </div>
    <a href="<?= $appUrl ?>/runner/job" class="bg-blue-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-blue-700 transition-colors">View Job →</a>
  </div>

  <!-- Feed -->
  <div id="feed-container" class="space-y-3">
    <div class="skeleton h-32 rounded-2xl"></div>
    <div class="skeleton h-32 rounded-2xl"></div>
    <div class="skeleton h-32 rounded-2xl"></div>
  </div>

  <!-- Empty state -->
  <div id="empty-state" class="hidden text-center py-16">
    <div class="text-6xl mb-4">⚡</div>
    <h3 class="text-lg font-bold text-charcoal dark:text-cream">No open tasks right now</h3>
    <p class="text-charcoal-muted dark:text-gray-400 text-sm mt-1">Auto-refreshing every 4 seconds…</p>
    <div class="flex items-center justify-center gap-1.5 mt-3">
      <div class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-bounce" style="animation-delay:0s"></div>
      <div class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-bounce" style="animation-delay:.15s"></div>
      <div class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-bounce" style="animation-delay:.3s"></div>
    </div>
  </div>

  <!-- Last updated -->
  <p id="last-updated" class="text-xs text-center text-charcoal-muted dark:text-gray-500 mt-4"></p>
</div>

<!-- Accept confirmation modal -->
<div id="accept-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-end md:items-center justify-center p-4">
  <div class="bg-white dark:bg-charcoal-light rounded-2xl p-6 w-full max-w-sm shadow-2xl animate-bounce-in">
    <div class="text-center mb-4">
      <div class="text-4xl mb-2" id="modal-icon">📦</div>
      <h3 class="text-lg font-bold text-charcoal dark:text-cream" id="modal-title">Accept this job?</h3>
      <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1" id="modal-desc"></p>
    </div>
    <div class="bg-amber-50 dark:bg-amber-900/10 rounded-xl p-3 mb-4">
      <p class="text-xs text-charcoal-muted dark:text-gray-400 mb-0.5">Tip offered</p>
      <p class="text-sm font-bold text-amber-600 dark:text-amber-400" id="modal-tip">None</p>
    </div>
    <div class="flex gap-2">
      <button onclick="closeModal()" class="flex-1 py-3 border border-gray-200 dark:border-charcoal-lighter text-charcoal dark:text-cream rounded-xl text-sm font-semibold hover:bg-gray-50 dark:hover:bg-charcoal-lighter transition-colors">Cancel</button>
      <button onclick="confirmAccept()" id="confirm-accept-btn" class="flex-1 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-bold transition-colors btn-primary flex items-center justify-center gap-2">
        <span>Accept ⚡</span>
        <svg id="accept-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
      </button>
    </div>
  </div>
</div>

<style>
.skeleton{animation:skeletonPulse 1.5s ease-in-out infinite;background:linear-gradient(90deg,#e5e7eb 25%,#f3f4f6 50%,#e5e7eb 75%);background-size:200% 100%;}
.dark .skeleton{background:linear-gradient(90deg,#3A3A3C 25%,#4A4A4C 50%,#3A3A3C 75%);background-size:200% 100%;}
@keyframes skeletonPulse{0%{background-position:200% 0}100%{background-position:-200% 0}}
.task-card{transition:transform .2s ease,box-shadow .2s ease;}
.task-card:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(0,0,0,.1);}
.status-open{border-left-color:#10B981;}
@keyframes cardAppear{from{transform:translateY(12px);opacity:0}to{transform:translateY(0);opacity:1}}
.animate-card-appear{animation:cardAppear .3s ease-out;}
@keyframes fadeOut{to{opacity:0;transform:translateY(-8px);}}
.task-taken{animation:fadeOut .4s ease-out forwards;}
@keyframes bounceIn{0%{transform:scale(.8);opacity:0}80%{transform:scale(1.05)}100%{transform:scale(1);opacity:1}}
.animate-bounce-in{animation:bounceIn .4s cubic-bezier(.36,.07,.19,.97);}
</style>

<script src="<?= $appUrl ?>/assets/js/task-feed.js"></script>
<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN = localStorage.getItem('td_token');
let selectedBuilding = null;
let pendingTaskId = null;
let hasActiveJob = false;
const categoryIcons = {grocery:'🛒',package:'📦',document:'📄',food:'🍔',other:'📌'};

// Load runner's buildings
async function loadBuildings() {
  const res = await fetch(APP_URL + '/api/v1/users/profile.php', { headers:{'Authorization':'Bearer '+TOKEN} });
  const data = await res.json();
  const buildings = data.data?.buildings || [];
  const sel = document.getElementById('building-select');
  if (buildings.length === 0) {
    sel.innerHTML = '<option value="">No buildings assigned</option>';
    return;
  }
  sel.innerHTML = buildings.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
  selectedBuilding = buildings[0].id;
  sel.addEventListener('change', () => { selectedBuilding = parseInt(sel.value); loadFeed(); });
  loadFeed();
}

// Check for active job
async function checkActiveJob() {
  try {
    const res = await fetch(APP_URL + '/api/v1/tasks/index.php?building_id=' + selectedBuilding, {
      headers: { 'Authorization': 'Bearer ' + TOKEN }
    });
  } catch {}
  // We check via a separate profile call
  const res = await fetch(APP_URL + '/api/v1/users/profile.php', { headers:{'Authorization':'Bearer '+TOKEN} });
  const data = await res.json();
  return data.data; // handled in loadFeed
}

let knownTaskIds = new Set();
async function loadFeed() {
  if (!selectedBuilding) return;
  try {
    const res = await fetch(APP_URL + '/api/v1/tasks/index.php?building_id=' + selectedBuilding, {
      headers: {'Authorization':'Bearer '+TOKEN}
    });
    const data = await res.json();
    const tasks = data.data || [];
    renderFeed(tasks);
    document.getElementById('last-updated').textContent = 'Updated: ' + new Date().toLocaleTimeString();
  } catch (e) {
    console.error(e);
  }
}

function renderFeed(tasks) {
  const container = document.getElementById('feed-container');
  const empty = document.getElementById('empty-state');

  if (tasks.length === 0) {
    container.innerHTML = '';
    empty.classList.remove('hidden');
    return;
  }
  empty.classList.add('hidden');

  // Check for new tasks
  const newIds = new Set(tasks.map(t => t.id));
  const hasNew = tasks.some(t => !knownTaskIds.has(t.id)) && knownTaskIds.size > 0;
  if (hasNew) {
    showToast('New task available! ⚡', 'Race to accept!', 'info');
  }
  knownTaskIds = newIds;

  container.innerHTML = tasks.map(t => {
    const isUrgent = t.urgency === 'urgent';
    return `
      <div class="task-card bg-white dark:bg-charcoal-light rounded-2xl p-5 border-l-4 status-open shadow-sm border border-gray-100 dark:border-charcoal-lighter animate-card-appear" data-id="${t.id}">
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
                <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 font-medium">Open</span>
                <span class="text-xs text-charcoal-muted dark:text-gray-500 capitalize">${t.category}</span>
                ${t.tip ? `<span class="text-xs font-bold text-amber-600 dark:text-amber-400">💰 ${t.tip}</span>` : ''}
              </div>
            </div>
          </div>
          <span class="text-xs text-charcoal-muted dark:text-gray-500 flex-shrink-0">${timeAgo(t.created_at)}</span>
        </div>
        <div class="mt-4 flex items-center justify-end">
          <button onclick="openAcceptModal(${t.id}, '${escapeHtml(t.title)}', '${escapeHtml(t.description||'')}', '${escapeHtml(t.tip||'')}', '${t.category}')"
                  class="btn-primary bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl accept-btn">
            Accept This Job ⚡
          </button>
        </div>
      </div>`;
  }).join('');
}

function escapeHtml(s) { return s.replace(/['"<>&]/g, c => ({'\'':'&#39;','"':'&quot;','<':'&lt;','>':'&gt;','&':'&amp;'}[c])); }
function timeAgo(ts) {
  const d = Math.floor((Date.now() - new Date(ts)) / 1000);
  if (d < 60) return 'just now';
  if (d < 3600) return Math.floor(d/60) + 'm ago';
  if (d < 86400) return Math.floor(d/3600) + 'h ago';
  return Math.floor(d/86400) + 'd ago';
}

function openAcceptModal(id, title, desc, tip, category) {
  pendingTaskId = id;
  document.getElementById('modal-icon').textContent = {grocery:'🛒',package:'📦',document:'📄',food:'🍔',other:'📌'}[category] || '📌';
  document.getElementById('modal-title').textContent = title;
  document.getElementById('modal-desc').textContent = desc || 'No additional details.';
  document.getElementById('modal-tip').textContent = tip || 'None offered';
  document.getElementById('accept-modal').classList.remove('hidden');
}
function closeModal() { document.getElementById('accept-modal').classList.add('hidden'); pendingTaskId = null; }

async function confirmAccept() {
  if (!pendingTaskId) return;
  const btn = document.getElementById('confirm-accept-btn');
  const spinner = document.getElementById('accept-spinner');
  btn.disabled = true; spinner.classList.remove('hidden'); btn.querySelector('span').textContent = 'Accepting…';

  const res = await fetch(APP_URL + '/api/v1/tasks/accept.php', {
    method: 'POST',
    headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json' },
    body: JSON.stringify({ task_id: pendingTaskId }),
  });
  const data = await res.json();

  if (data.status === 'success') {
    closeModal();
    showToast('Job is yours! 🏃', 'Head to the resident now.', 'success');
    setTimeout(() => window.location.href = APP_URL + '/runner/job', 800);
  } else {
    showToast(data.message || 'Could not accept', '', 'error');
    btn.disabled = false; spinner.classList.add('hidden'); btn.querySelector('span').textContent = 'Accept ⚡';
    closeModal();
    loadFeed(); // Refresh to remove taken task
  }
}

loadBuildings();
setInterval(loadFeed, 4000);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
