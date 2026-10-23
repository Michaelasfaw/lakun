<?php
$pageTitle = 'My credits';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-4xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Rider</p>
      <h1 class="page-title">My credits</h1>
      <p class="page-sub">Buy credits to accept delivery assignments.</p>
    </div>
  </div>

  <!-- Balance card -->
  <div class="relative overflow-hidden rounded-3xl hero-gradient text-white p-6 md:p-8 mb-8 shadow-lg">
    <div class="absolute inset-0 bg-grid opacity-20 [mask-image:linear-gradient(to_left,black,transparent_70%)]" aria-hidden="true"></div>
    <div class="relative flex items-center justify-between gap-4">
      <div>
        <p class="text-white/80 text-sm font-semibold mb-1">Current balance</p>
        <div class="flex items-end gap-2">
          <p id="credit-balance" class="text-5xl md:text-6xl font-extrabold tracking-tight leading-none num">—</p>
          <p class="text-white/75 text-lg mb-1">credits</p>
        </div>
        <p class="text-white/80 text-sm mt-4 max-w-sm">Each delivery assignment costs 1 credit. Pick a package below to top up.</p>
      </div>
      <span class="icon-tile-lg w-14 h-14 bg-white/15 text-white hidden sm:inline-flex"><?= icon('coins', 'w-7 h-7') ?></span>
    </div>
  </div>

  <!-- Packages -->
  <div class="flex items-center gap-3 mb-4">
    <span class="icon-tile bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"><?= icon('tag', 'w-5 h-5') ?></span>
    <div>
      <h2 class="section-title">Credit packages</h2>
      <p class="hint">The more you buy, the less each credit costs.</p>
    </div>
  </div>
  <div id="packages-list" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="skeleton h-64 rounded-2xl"></div>
    <div class="skeleton h-64 rounded-2xl"></div>
    <div class="skeleton h-64 rounded-2xl"></div>
  </div>

  <!-- Transaction history -->
  <div class="flex items-center gap-3 mb-4">
    <span class="icon-tile bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300"><?= icon('receipt', 'w-5 h-5') ?></span>
    <h2 class="section-title">Transaction history</h2>
  </div>
  <div class="card overflow-hidden">
    <div id="tx-list" class="divide-y divide-gray-100 dark:divide-white/[0.05]">
      <div class="p-4"><div class="skeleton h-8 rounded-xl"></div></div>
    </div>
  </div>
</div>

<!-- Confirm Purchase Modal -->
<div id="buy-modal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 hidden">
  <div class="absolute inset-0 bg-ink-900/60 backdrop-blur-sm" onclick="closeBuyModal()"></div>
  <div class="relative bg-white dark:bg-charcoal-light rounded-3xl shadow-2xl w-full max-w-sm border border-gray-200/60 dark:border-white/[0.06]">
    <div class="p-5 md:p-6">
      <div class="flex items-center gap-3 mb-4">
        <span class="icon-tile-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"><?= icon('coins', 'w-6 h-6') ?></span>
        <div class="min-w-0">
          <h3 class="font-extrabold text-charcoal dark:text-cream text-lg leading-snug" id="buy-pkg-name">—</h3>
          <p class="text-charcoal-muted dark:text-gray-400 text-sm" id="buy-pkg-detail">—</p>
        </div>
      </div>
      <div class="bg-amber-50 dark:bg-amber-500/10 rounded-xl p-3.5 flex items-start gap-2.5">
        <?= icon('info', 'w-4 h-4 mt-0.5 text-amber-600 dark:text-amber-300') ?>
        <p class="text-xs text-amber-800 dark:text-amber-200 font-medium">Credits are added to your account immediately. Payment is collected separately by the admin.</p>
      </div>
    </div>
    <div class="p-5 md:p-6 pt-0 flex gap-3">
      <button onclick="closeBuyModal()" class="btn-outline flex-1">Cancel</button>
      <button id="confirm-buy-btn" onclick="confirmPurchase()" class="btn-brand flex-1">Confirm purchase</button>
    </div>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');
let selectedPkgId = null;
let _pkgs = [];

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function timeAgo(ts) {
  const d = Math.floor((Date.now() - new Date(ts)) / 1000);
  if (d < 60) return 'just now';
  if (d < 3600) return Math.floor(d/60)+'m ago';
  if (d < 86400) return Math.floor(d/3600)+'h ago';
  return new Date(ts).toLocaleDateString();
}

async function loadBalance() {
  try {
    const res  = await fetch(APP_URL + '/api/v1/credits/balance.php', { headers:{'Authorization':'Bearer '+TOKEN} });
    const data = await res.json();
    const bal  = data.data?.credits ?? 0;
    document.getElementById('credit-balance').textContent = bal;

    const txs = data.data?.transactions || [];
    const el  = document.getElementById('tx-list');
    if (!txs.length) {
      el.innerHTML = LK.empty('receipt', 'No transactions yet', 'Credit purchases and usage will show up here.');
      return;
    }
    const typeColors = { purchase:'text-emerald-600 dark:text-emerald-400', deduction:'text-red-500 dark:text-red-400', adjustment:'text-amber-600 dark:text-amber-400', refund:'text-blue-600 dark:text-blue-400' };
    const typeLabels = { purchase:'Purchase', deduction:'Used', adjustment:'Adjusted', refund:'Refund' };
    el.innerHTML = txs.map(t => `
      <div class="px-5 py-3.5 flex items-center gap-3">
        <span class="icon-tile w-9 h-9 rounded-full
          ${t.type==='purchase'?'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300':
            t.type==='deduction'?'bg-red-50 text-red-500 dark:bg-red-500/10 dark:text-red-300':
            'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300'}">
          ${icon(t.type==='purchase' ? 'arrow-up-right' : (t.type==='deduction' ? 'minus' : 'refresh'), 'w-4 h-4')}
        </span>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-charcoal dark:text-cream">${typeLabels[t.type]||escHtml(t.type)}</p>
          <p class="text-xs text-charcoal-muted dark:text-gray-400 truncate">${escHtml(t.description)}</p>
        </div>
        <div class="text-right">
          <p class="font-extrabold num ${typeColors[t.type]||''}">${t.amount>0?'+':''}${t.amount}</p>
          <p class="text-xs text-charcoal-muted dark:text-gray-400">${timeAgo(t.created_at)}</p>
        </div>
      </div>`).join('');
  } catch(e) { console.error(e); }
}

async function loadPackages() {
  const res  = await fetch(APP_URL + '/api/v1/credits/packages.php', { headers:{'Authorization':'Bearer '+TOKEN} });
  const data = await res.json();
  const pkgs = data.data?.packages || [];
  _pkgs = pkgs;
  const el   = document.getElementById('packages-list');

  if (!pkgs.length) {
    el.className = 'mb-8';
    el.innerHTML = '<div class="card">' + LK.empty('coins', 'No packages available', 'Contact the admin to get credits.') + '</div>';
    return;
  }

  const tierIcons = ['sparkles','zap','flame'];
  el.className = 'grid grid-cols-1 md:grid-cols-' + Math.min(3, Math.max(1, pkgs.length)) + ' gap-4 mb-8';
  el.innerHTML = pkgs.map((p, i) => {
    const popular = pkgs.length > 1 && i === 1;
    const per = p.task_count > 0 ? (parseFloat(p.price_birr) / p.task_count) : 0;
    return `<div class="relative card flex flex-col p-6 animate-card-appear ${popular ? '!border-amber-400 dark:!border-amber-500/60 shadow-xl md:-translate-y-2 ring-4 ring-amber-500/10' : ''}" style="animation-delay:${i*60}ms">
      ${popular ? `<span class="absolute -top-3 left-1/2 -translate-x-1/2 badge bg-amber-600 text-white shadow-md">${icon('star','w-3 h-3')}Most popular</span>` : ''}
      <div class="flex items-center gap-3 mb-5">
        <span class="icon-tile ${popular ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300'}">${icon(tierIcons[i % 3],'w-5 h-5')}</span>
        <p class="font-extrabold text-charcoal dark:text-cream">${escHtml(p.name)}</p>
      </div>
      <div class="mb-1">
        <span class="text-3xl font-extrabold tracking-tight text-charcoal dark:text-cream num">${LK.money(p.price_birr)}</span>
      </div>
      <p class="hint mb-5">${per ? LK.money(per, 2) + ' per credit' : '&nbsp;'}</p>
      <ul class="space-y-2.5 text-sm text-charcoal dark:text-cream mb-6 flex-1">
        <li class="flex items-center gap-2.5"><span class="text-emerald-500">${icon('check-circle','w-4 h-4')}</span><strong class="num">${p.task_count}</strong> delivery credits</li>
        ${p.description ? `<li class="flex items-start gap-2.5 text-charcoal-muted dark:text-gray-400"><span class="text-emerald-500">${icon('check-circle','w-4 h-4 mt-px')}</span><span>${escHtml(p.description)}</span></li>` : ''}
        <li class="flex items-center gap-2.5 text-charcoal-muted dark:text-gray-400"><span class="text-emerald-500">${icon('check-circle','w-4 h-4')}</span>Added instantly</li>
      </ul>
      <button onclick="openBuyModalIdx(${i})" class="${popular ? 'btn-brand' : 'btn-outline'} w-full">Buy package</button>
    </div>`;
  }).join('');
}

function openBuyModalIdx(i) { if (_pkgs[i]) openBuyModal(_pkgs[i]); }

function openBuyModal(pkg) {
  selectedPkgId = pkg.id;
  document.getElementById('buy-pkg-name').textContent = pkg.name;
  document.getElementById('buy-pkg-detail').textContent = `${pkg.task_count} credits for ${LK.money(pkg.price_birr)}`;
  document.getElementById('buy-modal').classList.remove('hidden');
}
function closeBuyModal() { document.getElementById('buy-modal').classList.add('hidden'); selectedPkgId = null; }

async function confirmPurchase() {
  if (!selectedPkgId) return;
  const btn = document.getElementById('confirm-buy-btn');
  btn.disabled = true; btn.textContent = 'Processing…';
  const res  = await fetch(APP_URL + '/api/v1/credits/purchase.php', {
    method:'POST', headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json'},
    body: JSON.stringify({ package_id: selectedPkgId })
  });
  const data = await res.json();
  btn.disabled = false; btn.textContent = 'Confirm purchase';
  if (data.status === 'success') {
    closeBuyModal();
    showToast('Credits added', data.message, 'success');
    loadBalance();
  } else {
    showToast(data.message || 'Purchase failed', '', 'error');
  }
}

loadBalance();
loadPackages();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
