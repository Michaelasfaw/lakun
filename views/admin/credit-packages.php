<?php
$pageTitle = 'Credit packages';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-5xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Administration</p>
      <h1 class="page-title">Credit packages</h1>
      <p class="page-sub">Configure pricing for rider task credits.</p>
    </div>
    <button onclick="openModal()" class="btn-brand"><?= icon('plus', 'w-4 h-4') ?>New package</button>
  </div>

  <!-- Info banner -->
  <div class="bg-amber-50 dark:bg-amber-500/[0.07] border border-amber-200/80 dark:border-amber-500/20 rounded-2xl p-4 mb-6 flex gap-3">
    <span class="icon-tile bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><?= icon('info', 'w-5 h-5') ?></span>
    <div>
      <p class="text-sm font-bold text-amber-900 dark:text-amber-200">How credits work</p>
      <p class="text-[13px] text-amber-800/90 dark:text-amber-300/80 mt-0.5">Riders must buy credits before they can be assigned deliveries. Each assignment deducts 1 credit. Set the prices below in Ethiopian Birr (ETB).</p>
    </div>
  </div>

  <!-- Packages grid -->
  <div id="packages-grid" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="skeleton h-56 rounded-2xl"></div>
    <div class="skeleton h-56 rounded-2xl"></div>
    <div class="skeleton h-56 rounded-2xl"></div>
  </div>

  <!-- Transaction log -->
  <div class="card overflow-hidden">
    <div class="px-5 md:px-6 py-4 border-b border-gray-100 dark:border-white/[0.06] flex items-center gap-3">
      <span class="icon-tile w-9 h-9 bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300"><?= icon('receipt', 'w-[18px] h-[18px]') ?></span>
      <h2 class="section-title">Recent credit transactions</h2>
    </div>
    <div id="tx-list" class="divide-y divide-gray-100 dark:divide-white/[0.05]">
      <div class="p-4"><div class="skeleton h-8 rounded-xl"></div></div>
      <div class="p-4"><div class="skeleton h-8 rounded-xl"></div></div>
    </div>
  </div>
</div>

<!-- Package Modal -->
<div id="pkg-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden">
  <div class="absolute inset-0 bg-ink-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
  <div class="relative bg-white dark:bg-charcoal-light rounded-3xl shadow-2xl w-full max-w-md border border-gray-200/60 dark:border-white/[0.06]">
    <div class="p-5 md:p-6 pb-0 flex items-center gap-3">
      <span class="icon-tile bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"><?= icon('coins', 'w-5 h-5') ?></span>
      <h3 id="modal-title" class="font-extrabold text-lg text-charcoal dark:text-cream flex-1">New package</h3>
      <button onclick="closeModal()" class="icon-btn -mr-2" aria-label="Close"><?= icon('x', 'w-5 h-5') ?></button>
    </div>
    <div class="p-5 md:p-6 space-y-4">
      <input type="hidden" id="pkg-id">
      <div>
        <label class="label" for="pkg-name">Package name</label>
        <input id="pkg-name" type="text" placeholder="e.g. Standard pack" class="input">
      </div>
      <div>
        <label class="label" for="pkg-desc">Description</label>
        <input id="pkg-desc" type="text" placeholder="Short description for riders" class="input">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label" for="pkg-price">Price (ETB)</label>
          <input id="pkg-price" type="number" min="1" step="0.01" placeholder="2000" class="input num">
        </div>
        <div>
          <label class="label" for="pkg-tasks">Task credits</label>
          <input id="pkg-tasks" type="number" min="1" step="1" placeholder="10" class="input num">
        </div>
      </div>
      <label class="flex items-center gap-3 cursor-pointer surface-muted px-4 py-3">
        <input type="checkbox" id="pkg-active" checked class="w-4 h-4 rounded accent-amber-600">
        <span class="text-sm text-charcoal dark:text-cream font-medium">Active (visible to riders)</span>
      </label>
    </div>
    <div class="p-5 md:p-6 pt-0 flex gap-3">
      <button onclick="closeModal()" class="btn-outline flex-1">Cancel</button>
      <button onclick="savePackage()" class="btn-brand flex-1">Save</button>
    </div>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');
let _pkgs = [];

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function timeAgo(ts) {
  const d = Math.floor((Date.now() - new Date(ts)) / 1000);
  if (d < 60) return 'just now';
  if (d < 3600) return Math.floor(d/60)+'m ago';
  if (d < 86400) return Math.floor(d/3600)+'h ago';
  return Math.floor(d/86400)+'d ago';
}

async function loadPackages() {
  const res  = await fetch(APP_URL + '/api/v1/admin/credit-packages.php', { headers:{'Authorization':'Bearer '+TOKEN} });
  const data = await res.json();
  const pkgs = data.data?.packages || [];
  _pkgs = pkgs;
  const grid = document.getElementById('packages-grid');

  if (!pkgs.length) {
    grid.className = 'mb-6';
    grid.innerHTML = '<div class="card">' + LK.empty('coins', 'No packages yet', 'Create your first credit package for riders.', '<button onclick="openModal()" class="btn-brand">' + icon('plus','w-4 h-4') + 'New package</button>') + '</div>';
    return;
  }
  grid.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 mb-6';

  grid.innerHTML = pkgs.map((p, i) => {
    const tierIcon = p.task_count >= 25 ? 'flame' : p.task_count >= 8 ? 'zap' : 'sparkles';
    const per = p.task_count > 0 ? (p.price_birr / p.task_count) : 0;
    return `
    <div class="card p-5 relative flex flex-col animate-card-appear ${p.is_active ? '' : 'opacity-70'}" style="animation-delay:${i*40}ms">
      <div class="flex items-start justify-between mb-4">
        <span class="icon-tile ${p.is_active ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400'}">${icon(tierIcon,'w-5 h-5')}</span>
        <span class="badge ${p.is_active ? LK.TONES.emerald : LK.TONES.gray}">${p.is_active ? 'Active' : 'Inactive'}</span>
      </div>
      <h3 class="font-extrabold text-charcoal dark:text-cream text-lg leading-snug">${escHtml(p.name)}</h3>
      <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-1 min-h-[1rem]">${escHtml(p.description||'')}</p>
      <div class="mt-4 flex-1">
        <p class="text-2xl font-extrabold tracking-tight text-charcoal dark:text-cream num">${LK.money(p.price_birr)}</p>
        <p class="text-sm text-charcoal dark:text-cream font-semibold mt-0.5"><span class="num">${p.task_count}</span> task credits</p>
        <p class="hint num">${LK.money(per, 1)} per credit</p>
      </div>
      <div class="flex gap-2 mt-4">
        <button onclick="editPackageIdx(${i})" class="btn-outline btn-sm flex-1">${icon('edit','w-3.5 h-3.5')}Edit</button>
        <button onclick="deletePackage(${p.id})" class="btn-sm btn text-red-600 dark:text-red-300 border border-red-200/70 dark:border-red-500/20 hover:bg-red-50 dark:hover:bg-red-500/10">${icon('trash','w-3.5 h-3.5')}Delete</button>
      </div>
    </div>`;
  }).join('');
}

function editPackageIdx(i) { if (_pkgs[i]) openModal(_pkgs[i]); }

async function loadTransactions() {
  try {
    const res  = await fetch(APP_URL + '/api/v1/admin/credit-transactions.php', { headers:{'Authorization':'Bearer '+TOKEN} });
    const data = await res.json();
    const txs  = data.data?.transactions || [];
    const el   = document.getElementById('tx-list');
    if (!txs.length) { el.innerHTML = LK.empty('receipt', 'No transactions yet', 'Credit purchases and adjustments will show up here.'); return; }
    const typeColors = { purchase:'text-emerald-600 dark:text-emerald-400', deduction:'text-red-500 dark:text-red-400', adjustment:'text-amber-600 dark:text-amber-400', refund:'text-blue-600 dark:text-blue-400' };
    const typeTiles  = { purchase:'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300', deduction:'bg-red-50 text-red-500 dark:bg-red-500/10 dark:text-red-300', adjustment:'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300', refund:'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300' };
    const typeIcons  = { purchase:'arrow-up-right', deduction:'minus', adjustment:'edit', refund:'refresh' };
    el.innerHTML = txs.map(t => `
      <div class="px-5 md:px-6 py-3.5 flex items-center gap-3">
        <span class="icon-tile w-9 h-9 rounded-full ${typeTiles[t.type]||'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-300'}">${icon(typeIcons[t.type]||'circle-dot','w-4 h-4')}</span>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-charcoal dark:text-cream">${escHtml(t.user_name||'Unknown')}</p>
          <p class="text-xs text-charcoal-muted dark:text-gray-400 truncate">${escHtml(t.description)}</p>
        </div>
        <div class="text-right flex-shrink-0">
          <p class="font-extrabold num ${typeColors[t.type]||'text-gray-600'}">${t.amount > 0 ? '+' : ''}${t.amount} cr</p>
          <p class="text-xs text-charcoal-muted dark:text-gray-400">${timeAgo(t.created_at)}</p>
        </div>
      </div>`).join('');
  } catch(e) {
    document.getElementById('tx-list').innerHTML = LK.empty('alert', 'Could not load transactions', 'Please refresh and try again.');
  }
}

function openModal(pkg = null) {
  document.getElementById('pkg-id').value   = pkg?.id || '';
  document.getElementById('pkg-name').value = pkg?.name || '';
  document.getElementById('pkg-desc').value = pkg?.description || '';
  document.getElementById('pkg-price').value= pkg?.price_birr || '';
  document.getElementById('pkg-tasks').value= pkg?.task_count || '';
  document.getElementById('pkg-active').checked = pkg ? !!pkg.is_active : true;
  document.getElementById('modal-title').textContent = pkg ? 'Edit package' : 'New package';
  document.getElementById('pkg-modal').classList.remove('hidden');
}
function editPackage(pkg) { openModal(pkg); }
function closeModal() { document.getElementById('pkg-modal').classList.add('hidden'); }

async function savePackage() {
  const id = document.getElementById('pkg-id').value;
  const body = {
    action:     id ? 'update' : 'create',
    id:         id ? parseInt(id) : undefined,
    name:       document.getElementById('pkg-name').value.trim(),
    description:document.getElementById('pkg-desc').value.trim(),
    price_birr: parseFloat(document.getElementById('pkg-price').value),
    task_count: parseInt(document.getElementById('pkg-tasks').value),
    is_active:  document.getElementById('pkg-active').checked ? 1 : 0,
  };
  if (!body.name || !body.price_birr || !body.task_count) {
    showToast('Fill in all required fields', '', 'error'); return;
  }
  const res  = await fetch(APP_URL + '/api/v1/admin/credit-packages.php', {
    method:'POST', headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json'},
    body: JSON.stringify(body)
  });
  const data = await res.json();
  if (data.status === 'success') { closeModal(); loadPackages(); showToast('Package saved', '', 'success'); }
  else showToast(data.message||'Error', '', 'error');
}

async function deletePackage(id) {
  if (!confirm('Delete this package?')) return;
  const res  = await fetch(APP_URL + '/api/v1/admin/credit-packages.php', {
    method:'POST', headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json'},
    body: JSON.stringify({ action:'delete', id })
  });
  const data = await res.json();
  if (data.status === 'success') { loadPackages(); showToast('Deleted', '', 'success'); }
  else showToast(data.message||'Error', '', 'error');
}

loadPackages();
loadTransactions();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
