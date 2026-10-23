<?php
$pageTitle = 'Bid history';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-2xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Vendor</p>
      <h1 class="page-title">Bid history</h1>
      <p class="page-sub">Every bid you've submitted, and whether you won it.</p>
    </div>
  </div>

  <!-- Tabs -->
  <div class="overflow-x-auto scrollbar-none -mx-4 px-4 md:mx-0 md:px-0 mb-5">
    <div class="segmented">
      <button onclick="setTab('all')"       id="tab-all"       class="tab-btn is-active">All</button>
      <button onclick="setTab('won')"       id="tab-won"       class="tab-btn">Won</button>
      <button onclick="setTab('pending')"   id="tab-pending"   class="tab-btn">Pending</button>
      <button onclick="setTab('lost')"      id="tab-lost"      class="tab-btn">Lost</button>
      <button onclick="setTab('cancelled')" id="tab-cancelled" class="tab-btn">Cancelled</button>
    </div>
  </div>

  <div id="list" class="space-y-3">
    <div class="skeleton h-24 rounded-2xl"></div>
    <div class="skeleton h-24 rounded-2xl"></div>
    <div class="skeleton h-24 rounded-2xl"></div>
  </div>
  <div id="empty" class="hidden card">
    <div class="empty">
      <div class="empty-icon"><?= icon('history', 'w-7 h-7') ?></div>
      <h3 class="text-base font-bold text-charcoal dark:text-cream">No bids here</h3>
      <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5 max-w-xs">Bids you submit on orders will appear here.</p>
    </div>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');
let allBids    = [];
let currentTab = 'all';

const OUTCOME = {
  won:       { label: 'Won',       tone: 'emerald', ic: 'check-circle' },
  lost:      { label: 'Lost',      tone: 'red',     ic: 'x-circle' },
  pending:   { label: 'Pending',   tone: 'amber',   ic: 'hourglass' },
  cancelled: { label: 'Cancelled', tone: 'gray',    ic: 'x-circle' },
};

const CAT_ICONS = { pharmacy:'pill', shop:'cart', liquor:'wine', food:'utensils', general:'package' };

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtEtb(v)  { return 'ETB ' + parseFloat(v||0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); }

function setTab(tab) {
  currentTab = tab;
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('is-active'));
  document.getElementById('tab-' + tab).classList.add('is-active');
  renderList();
}

function renderList() {
  const filtered = currentTab === 'all' ? allBids : allBids.filter(b => b.outcome === currentTab);
  const list  = document.getElementById('list');
  const empty = document.getElementById('empty');

  if (!filtered.length) {
    list.innerHTML = '';
    empty.classList.remove('hidden');
    return;
  }
  empty.classList.add('hidden');

  list.innerHTML = filtered.map((b, i) => {
    const o = OUTCOME[b.outcome] || OUTCOME.pending;
    const catIcon = CAT_ICONS[b.category] || 'package';

    // Won bid: show order journey status
    let extraHtml = '';
    if (b.outcome === 'won') {
      extraHtml = `<span class="chip">Order: <span class="font-semibold text-charcoal dark:text-cream">${LK.esc(LK.status(b.order_status).label)}</span></span>`;
    }

    return `
    <div class="card p-4 md:p-5 animate-card-appear ${b.outcome === 'won' ? '!border-emerald-300/70 dark:!border-emerald-500/30' : ''}" style="animation-delay:${i*30}ms">
      <div class="flex items-start gap-3">
        <span class="icon-tile ${LK.ACCENTS[o.tone].tile}">${icon(catIcon, 'w-5 h-5')}</span>
        <div class="flex-1 min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <span class="text-[15px] font-bold text-charcoal dark:text-cream break-words">${escHtml(b.item_name)}</span>
            <span class="text-xs text-charcoal-muted dark:text-gray-400">×${b.quantity}</span>
            ${b.is_fast_order ? `<span class="badge ${LK.TONES.orange}">${icon('zap','w-3 h-3')}Fast</span>` : ''}
          </div>
          <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5">${escHtml(b.building_name)} &middot; ${new Date(b.created_at).toLocaleDateString()}</p>
          <div class="flex flex-wrap items-center gap-2 mt-2.5">
            <span class="text-xs text-charcoal-muted dark:text-gray-400">Your bid <strong class="text-sm font-extrabold text-charcoal dark:text-cream num ml-1">${fmtEtb(b.price)}</strong></span>
            ${extraHtml}
          </div>
        </div>
        <span class="badge ${LK.TONES[o.tone]} flex-shrink-0">${icon(o.ic,'w-3.5 h-3.5')}${o.label}</span>
      </div>
    </div>`;
  }).join('');
}

async function load() {
  try {
    const res  = await fetch(APP_URL + '/api/v1/bids/my-bids.php', {
      headers: { 'Authorization': 'Bearer ' + TOKEN }
    });
    const data = await res.json();
    allBids = data.data || [];
    renderList();
  } catch(e) { console.error(e); }
}

load();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
