<?php
$pageTitle = 'Orders';
$appUrl = APP_URL;
// Read filter from URL if admin clicked a dashboard link
$urlFilter = $_GET['filter'] ?? '';
$modalShell = 'relative bg-white dark:bg-charcoal-light rounded-t-3xl md:rounded-3xl w-full mx-auto z-10 shadow-2xl border border-gray-200/70 dark:border-white/[0.06] animate-fade-slide-up';
$modalBackdrop = 'absolute inset-0 bg-charcoal/60 backdrop-blur-sm';
$closeBtn = fn(string $fn) => '<button onclick="' . $fn . '()" class="icon-btn w-9 h-9 -mr-2 -mt-1" aria-label="Close">' . icon('x', 'w-[18px] h-[18px]') . '</button>';
ob_start();
?>
<div class="max-w-6xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Operations</p>
      <h1 class="page-title">Orders board</h1>
      <p class="page-sub">Review requests, forward bids, verify payments and dispatch riders.</p>
    </div>
    <div class="relative w-full sm:w-72">
      <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"><?= icon('search', 'w-[18px] h-[18px]') ?></span>
      <input id="order-search" type="search" placeholder="Search item, customer, code…" class="input pl-11 py-2.5" oninput="renderOrders()">
    </div>
  </div>

  <!-- Status filters — full lifecycle -->
  <div class="-mx-4 px-4 md:mx-0 md:px-0 mb-5 overflow-x-auto scrollbar-none">
    <div class="flex gap-1.5 w-max md:w-auto md:flex-wrap">
      <?php
      $filters = [
        ''                     => ['All orders',      'list'],
        'requested'            => ['Pending review',  'clock'],
        'bids_received'        => ['Bid review',      'tag'],
        'awaiting_payment'     => ['Awaiting payment','card'],
        'payment_under_review' => ['Payment review',  'shield'],
        'confirmed'            => ['Confirmed',       'check-circle'],
        'vendor_paid'          => ['Vendor paid',     'wallet'],
        'out_for_delivery'     => ['Delivering',      'truck'],
        'delivered'            => ['Delivered',       'check'],
        'completed'            => ['Completed',       'package'],
        'cancelled'            => ['Cancelled',       'x-circle'],
      ];
      foreach ($filters as $val => [$label, $ic]):
      ?>
      <button onclick="filterStatus('<?= $val ?>')" id="btn-<?= $val ?: 'all' ?>" class="filter-btn">
        <?= icon($ic, 'w-3.5 h-3.5') ?><?= $label ?>
      </button>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="flex items-center justify-between mb-3">
    <p id="orders-count" class="text-xs font-semibold text-charcoal-muted dark:text-gray-500"></p>
    <p class="text-xs text-charcoal-muted dark:text-gray-500 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Auto-refreshing</p>
  </div>

  <div id="orders-table" class="space-y-3">
    <div class="skeleton h-28 rounded-2xl"></div>
    <div class="skeleton h-28 rounded-2xl"></div>
    <div class="skeleton h-28 rounded-2xl"></div>
  </div>
</div>

<!-- Assign Agent Modal -->
<div id="assign-modal" class="hidden fixed inset-0 z-50 flex items-end md:items-center justify-center p-0 md:p-4">
  <div class="<?= $modalBackdrop ?>" onclick="closeAssign()"></div>
  <div class="<?= $modalShell ?> max-w-md p-6">
    <div class="flex items-start justify-between gap-3 mb-5">
      <div class="flex items-center gap-3">
        <span class="icon-tile bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-300"><?= icon('bike', 'w-5 h-5') ?></span>
        <div>
          <h3 class="text-lg font-bold">Assign a rider</h3>
          <p class="text-xs text-charcoal-muted dark:text-gray-400" id="assign-order-info"></p>
        </div>
      </div>
      <?= $closeBtn('closeAssign') ?>
    </div>
    <div id="agents-list" class="space-y-2 max-h-72 overflow-y-auto -mx-1 px-1"></div>
    <p class="hint mt-4 flex items-center gap-1.5"><?= icon('info', 'w-3.5 h-3.5') ?>Assigning a rider uses 1 of their task credits.</p>
  </div>
</div>

<!-- Payment Verify Modal -->
<div id="payment-modal" class="hidden fixed inset-0 z-50 flex items-end md:items-center justify-center p-0 md:p-4">
  <div class="<?= $modalBackdrop ?>" onclick="closePaymentModal()"></div>
  <div class="<?= $modalShell ?> max-w-lg max-h-[92vh] flex flex-col">
    <div class="flex items-start justify-between gap-3 p-6 pb-4">
      <div class="flex items-center gap-3">
        <span class="icon-tile bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-300"><?= icon('shield', 'w-5 h-5') ?></span>
        <div>
          <h3 class="text-lg font-bold">Verify payment</h3>
          <p class="text-xs text-charcoal-muted dark:text-gray-400">Match the screenshot against the transaction details.</p>
        </div>
      </div>
      <?= $closeBtn('closePaymentModal') ?>
    </div>
    <div id="payment-proof-content" class="px-6 overflow-y-auto"></div>
    <div class="flex gap-3 p-6 pt-5 border-t border-gray-100 dark:border-white/[0.06] mt-5">
      <button onclick="verifyPayment('reject')" class="btn flex-1 py-3 border border-red-200 dark:border-red-500/30 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10">
        <?= icon('x-circle', 'w-4 h-4') ?>Reject
      </button>
      <button onclick="verifyPayment('approve')" class="btn-success flex-[1.4] py-3">
        <?= icon('check-circle', 'w-4 h-4') ?>Approve payment
      </button>
    </div>
  </div>
</div>

<!-- Bid Review Modal -->
<div id="bid-modal" class="hidden fixed inset-0 z-50 flex items-end md:items-center justify-center p-0 md:p-4">
  <div class="<?= $modalBackdrop ?>" onclick="closeBidModal()"></div>
  <div class="<?= $modalShell ?> max-w-md p-6 max-h-[92vh] overflow-y-auto">
    <div class="flex items-start justify-between gap-3 mb-5">
      <div class="flex items-center gap-3">
        <span class="icon-tile bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300"><?= icon('tag', 'w-5 h-5') ?></span>
        <div>
          <h3 class="text-lg font-bold">Review bids</h3>
          <p class="text-xs text-charcoal-muted dark:text-gray-400" id="bid-modal-info"></p>
        </div>
      </div>
      <?= $closeBtn('closeBidModal') ?>
    </div>
    <div id="bid-modal-list" class="space-y-2 mb-4 max-h-80 overflow-y-auto"></div>
    <p class="hint flex items-start gap-1.5"><?= icon('info', 'w-3.5 h-3.5 mt-px') ?>Forward a bid to the customer. They'll see the price and can accept or decline.</p>
  </div>
</div>

<!-- Review & Edit Order Modal -->
<div id="review-modal" class="hidden fixed inset-0 z-50 flex items-end md:items-center justify-center p-0 md:p-4">
  <div class="<?= $modalBackdrop ?>" onclick="closeReviewModal()"></div>
  <div class="<?= $modalShell ?> max-w-md p-6 max-h-[92vh] overflow-y-auto">
    <div class="flex items-start justify-between gap-3 mb-5">
      <div class="flex items-center gap-3">
        <span class="icon-tile bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300"><?= icon('edit', 'w-5 h-5') ?></span>
        <div>
          <h3 class="text-lg font-bold">Review &amp; edit order</h3>
          <p class="text-xs text-charcoal-muted dark:text-gray-400" id="review-order-info"></p>
        </div>
      </div>
      <?= $closeBtn('closeReviewModal') ?>
    </div>
    <div class="space-y-4 mb-6">
      <div>
        <label class="label" for="review-item-name">Items</label>
        <input id="review-item-name" type="text" class="input">
      </div>
      <div>
        <label class="label" for="review-quantity">Quantity</label>
        <input id="review-quantity" type="number" min="1" class="input">
      </div>
      <div>
        <label class="label" for="review-notes">Notes for vendors</label>
        <textarea id="review-notes" rows="3" class="input resize-none"></textarea>
      </div>
    </div>
    <div class="flex gap-3">
      <button onclick="saveOrderEdits()" class="btn-outline flex-1 py-3"><?= icon('check', 'w-4 h-4') ?>Save edits</button>
      <button onclick="openForBidFromModal()" class="btn-brand flex-1 py-3"><?= icon('send', 'w-4 h-4') ?>Open for bids</button>
    </div>
  </div>
</div>

<!-- Mark Vendor Paid Modal -->
<div id="vendor-paid-modal" class="hidden fixed inset-0 z-50 flex items-end md:items-center justify-center p-0 md:p-4">
  <div class="<?= $modalBackdrop ?>" onclick="closeVendorPaidModal()"></div>
  <div class="<?= $modalShell ?> max-w-md p-6">
    <div class="flex items-start justify-between gap-3 mb-5">
      <div class="flex items-center gap-3">
        <span class="icon-tile bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300"><?= icon('wallet', 'w-5 h-5') ?></span>
        <div>
          <h3 class="text-lg font-bold">Mark vendor paid</h3>
          <p class="text-xs text-charcoal-muted dark:text-gray-400" id="vendor-paid-order-info"></p>
        </div>
      </div>
      <?= $closeBtn('closeVendorPaidModal') ?>
    </div>
    <div class="space-y-4 mb-6">
      <div>
        <label class="label" for="vendor-paid-ref">Payment reference / TXN ID</label>
        <input id="vendor-paid-ref" type="text" placeholder="e.g. TXN-123456" class="input font-mono">
      </div>
      <div>
        <label class="label" for="vendor-paid-amount">Amount sent</label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-charcoal-muted dark:text-gray-500">ETB</span>
          <input id="vendor-paid-amount" type="number" step="0.01" min="0" placeholder="0.00" class="input pl-12 num">
        </div>
      </div>
    </div>
    <button onclick="confirmVendorPaid()" class="btn-success w-full py-3"><?= icon('check-circle', 'w-4 h-4') ?>Confirm vendor paid</button>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');
let currentAssignOrderId = null;
let currentPaymentOrderId = null;
let currentBidOrderId = null;
let currentReviewOrderId = null;
let currentVendorPaidOrderId = null;
let currentFilter = '<?= addslashes($urlFilter) ?>';
let lastOrders = [];

const FILTER_BASE   = 'filter-btn inline-flex items-center gap-1.5 whitespace-nowrap text-xs px-3 py-2 rounded-xl font-semibold transition-all bg-white dark:bg-white/[0.04] border border-gray-200/80 dark:border-white/[0.07] text-charcoal-muted dark:text-gray-400 hover:text-charcoal dark:hover:text-cream hover:border-gray-300 dark:hover:border-white/15';
const FILTER_ACTIVE = 'filter-btn inline-flex items-center gap-1.5 whitespace-nowrap text-xs px-3 py-2 rounded-xl font-semibold transition-all bg-charcoal text-white border border-charcoal dark:bg-cream dark:text-charcoal dark:border-cream shadow-sm';
const CAT_ICONS = { pharmacy:'pill', shop:'cart', liquor:'wine', food:'utensils', general:'package' };

function escHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtEtb(v) { return 'ETB ' + parseFloat(v||0).toLocaleString(); }
function q(s) { return escHtml(s).replace(/'/g,"\\'"); }

function filterStatus(s) {
  currentFilter = s;
  document.querySelectorAll('.filter-btn').forEach(b => { b.className = FILTER_BASE; });
  const activeBtn = document.getElementById('btn-' + (s || 'all'));
  if (activeBtn) activeBtn.className = FILTER_ACTIVE;
  loadOrders();
}

async function loadOrders() {
  let url = APP_URL + '/api/v1/orders/index.php';
  if (currentFilter) url += '?status=' + encodeURIComponent(currentFilter);
  const res  = await fetch(url, { headers: { 'Authorization': 'Bearer ' + TOKEN } });
  const data = await res.json();
  lastOrders = data.data || [];
  renderOrders();
}

function actionBtn(cls, onclick, ic, label) {
  return `<button onclick="${onclick}" class="${cls} btn-sm whitespace-nowrap">${icon(ic, 'w-3.5 h-3.5')}${label}</button>`;
}

function renderOrders() {
  const term = (document.getElementById('order-search').value || '').trim().toLowerCase();
  const orders = !term ? lastOrders : lastOrders.filter(o =>
    [o.item_name, o.customer_name, o.order_code, o.vendor_name, o.shop_name, o.agent_name, o.building_name]
      .some(v => String(v || '').toLowerCase().includes(term)));
  const container = document.getElementById('orders-table');
  document.getElementById('orders-count').textContent = orders.length + (orders.length === 1 ? ' order' : ' orders');

  if (!orders.length) {
    container.innerHTML = `<div class="card">${LK.empty('inbox', 'No orders found', term ? 'Try a different search term.' : 'Orders in this state will appear here.')}</div>`;
    return;
  }

  container.innerHTML = orders.map((o, i) => {
    const st = LK.status(o.status);
    const canReview     = o.status === 'requested';
    const canAssign     = o.status === 'vendor_paid';
    const canVerify     = o.status === 'payment_under_review';
    const canMarkVendorPaid = o.status === 'confirmed';
    const canReviewBids = ['bidding','sent_to_vendors','bids_received'].includes(o.status);
    const isPayFail     = o.status === 'failed_payment';
    const canReinit     = ['cancelled', 'expired'].includes(o.status);
    const catIcon       = CAT_ICONS[o.category] || 'package';
    const needsAttention = canVerify || canAssign || canReview || canMarkVendorPaid;

    return `
    <div class="card relative overflow-hidden p-4 md:p-5 animate-card-appear ${needsAttention ? 'ring-1 ring-amber-400/40 dark:ring-amber-500/25' : ''}" style="animation-delay:${Math.min(i, 10) * 25}ms">
      <span class="absolute left-0 inset-y-0 w-1 ${st.bar}"></span>
      <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="flex items-start gap-3.5 flex-1 min-w-0">
          <span class="icon-tile-lg ${st.tile}">${icon(catIcon, 'w-6 h-6')}</span>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span class="text-xs font-extrabold text-charcoal-muted dark:text-gray-500 num">${escHtml(o.order_code || ('LK'+o.id))}</span>
              ${LK.statusBadge(o.status)}
              ${+o.is_fast_order ? `<span class="badge bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/15 dark:bg-orange-500/10 dark:text-orange-300">${icon('flame', 'w-3 h-3')}Express</span>` : ''}
              <span class="text-xs text-charcoal-muted dark:text-gray-500">${LK.timeAgo(o.created_at)}</span>
            </div>
            <p class="mt-1.5 text-[15px] font-bold truncate">${escHtml(o.item_name)} <span class="text-charcoal-muted dark:text-gray-500 font-medium">· ${o.quantity} item${o.quantity == 1 ? '' : 's'}</span></p>
            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-charcoal-muted dark:text-gray-400">
              <span class="flex items-center gap-1.5">${LK.avatar(o.customer_name, 'w-5 h-5 text-[9px] rounded-md')}<span class="font-semibold text-charcoal dark:text-cream">${escHtml(o.customer_name)}</span></span>
              <span class="flex items-center gap-1">${icon('building', 'w-3.5 h-3.5')}${escHtml(o.building_name)} · ${escHtml(o.block||'')}/${escHtml(o.floor||'')}/${escHtml(o.house_no||'')}</span>
              ${o.vendor_name ? `<span class="flex items-center gap-1">${icon('store', 'w-3.5 h-3.5')}${escHtml(o.shop_name || o.vendor_name)}</span>` : ''}
              ${o.agent_name ? `<span class="flex items-center gap-1">${icon('bike', 'w-3.5 h-3.5')}${escHtml(o.agent_name)}</span>` : ''}
            </div>
            ${canVerify && o.payment_txn_id ? `
              <div class="mt-3 inline-flex flex-wrap items-center gap-x-3 gap-y-1 text-xs rounded-lg px-3 py-2 bg-orange-50 dark:bg-orange-500/10 text-orange-800 dark:text-orange-200 ring-1 ring-inset ring-orange-500/15">
                <span class="flex items-center gap-1 font-semibold capitalize">${icon(o.payment_method === 'telebirr' ? 'phone' : 'card', 'w-3.5 h-3.5')}${o.payment_method ? o.payment_method.replace('_',' ') : ''}</span>
                <span>TXN <strong class="font-mono">${escHtml(o.payment_txn_id)}</strong></span>
                <span>Sender <strong>${escHtml(o.payment_sender_name||'')}</strong></span>
              </div>` : ''}
          </div>
        </div>
        <div class="flex md:flex-col items-center md:items-end justify-between gap-3 md:min-w-[180px] pl-[62px] md:pl-0">
          ${o.total_price ? `
            <div class="md:text-right">
              <p class="text-[11px] text-charcoal-muted dark:text-gray-500">Items + delivery</p>
              <p class="text-lg font-extrabold num leading-tight">${fmtEtb(o.total_price)}</p>
            </div>` : '<p class="text-xs text-charcoal-muted dark:text-gray-500">No price yet</p>'}
          <div class="flex flex-wrap justify-end gap-1.5">
            ${canReviewBids ? actionBtn('btn-dark', `openBidModal(${o.id}, '${q(o.item_name)}', ${o.quantity})`, 'tag', 'Review bids') : ''}
            ${canAssign ? actionBtn('btn-brand', `openAssign(${o.id}, '${q(o.item_name)}', ${o.building_id})`, 'bike', 'Assign rider') : ''}
            ${canReview ? actionBtn('btn-outline', `openReviewModal(${o.id}, '${q(o.item_name)}', ${o.quantity}, '${q(o.notes||'')}')`, 'edit', 'Review') + actionBtn('btn-brand', `openForBid(${o.id})`, 'send', 'Open for bids') : ''}
            ${canMarkVendorPaid ? actionBtn('btn-success', `openVendorPaidModal(${o.id}, '${q(o.item_name)}', ${parseFloat(o.item_price||0)})`, 'wallet', 'Mark vendor paid') : ''}
            ${canVerify ? actionBtn('btn-brand', `openPaymentModal(${o.id})`, 'shield', 'Verify payment') : ''}
            ${isPayFail ? `<span class="badge ${LK.TONES.red}">${icon('alert', 'w-3.5 h-3.5')}Payment rejected</span>` : ''}
            ${canReinit ? actionBtn('btn-outline', `reinitiateOrder(${o.id})`, 'refresh', 'Re-open bidding') : ''}
          </div>
        </div>
      </div>
    </div>`;
  }).join('');
}

// ── Assign Agent ───────────────────────────────────────────────────────────
async function openAssign(orderId, itemName, buildingId) {
  currentAssignOrderId = orderId;
  document.getElementById('assign-order-info').textContent = `Order #${orderId}: ${itemName}`;
  document.getElementById('agents-list').innerHTML = '<div class="skeleton h-12 rounded-xl"></div><div class="skeleton h-12 rounded-xl mt-2"></div>';
  document.getElementById('assign-modal').classList.remove('hidden');

  const res   = await fetch(APP_URL + `/api/v1/admin/agents.php?building_id=${buildingId}`, { headers:{'Authorization':'Bearer '+TOKEN} });
  const data  = await res.json();
  const agents = data.data || [];
  const list  = document.getElementById('agents-list');
  if (!agents.length) { list.innerHTML = LK.empty('bike', 'No riders for this building', 'Link a rider to this building from the People page.'); return; }
  list.innerHTML = agents.map(a => {
    const hasCredits = a.credits > 0;
    const tone = a.credits === 0 ? LK.TONES.red : a.credits < 3 ? LK.TONES.amber : LK.TONES.emerald;
    return `<button onclick="assignAgent(${a.id})" ${!hasCredits ? 'disabled title="No credits"' : ''}
      class="group w-full text-left flex items-center gap-3 p-3 rounded-2xl border transition-all
             ${hasCredits ? 'border-gray-200/80 dark:border-white/[0.07] hover:border-amber-400 hover:bg-amber-50/60 dark:hover:bg-amber-500/10' : 'border-red-100 dark:border-red-500/20 opacity-60 cursor-not-allowed'}">
      ${LK.avatar(a.name, 'w-10 h-10 text-xs')}
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold truncate">${escHtml(a.name)}</p>
        <p class="text-xs text-charcoal-muted dark:text-gray-400 num">${escHtml(a.phone)}</p>
      </div>
      <span class="badge ${tone}">${icon('zap', 'w-3 h-3')}${a.credits||0} credits</span>
      ${hasCredits ? icon('chevron-right', 'w-4 h-4 text-gray-300 group-hover:text-amber-500 transition-colors') : ''}
    </button>`;
  }).join('');
}

function closeAssign() { document.getElementById('assign-modal').classList.add('hidden'); currentAssignOrderId = null; }

async function assignAgent(agentId) {
  const res  = await fetch(APP_URL + '/api/v1/admin/assign.php', {
    method: 'POST', headers: {'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
    body: JSON.stringify({ order_id: currentAssignOrderId, agent_id: agentId }),
  });
  const data = await res.json();
  if (data.status === 'success') { showToast('Agent assigned!', '', 'success'); closeAssign(); loadOrders(); }
  else showToast(data.message || 'Error', '', 'error');
}

// ── Payment Verification ───────────────────────────────────────────────────
async function openPaymentModal(orderId) {
  currentPaymentOrderId = orderId;
  document.getElementById('payment-proof-content').innerHTML = '<div class="skeleton h-32 rounded-xl"></div>';
  document.getElementById('payment-modal').classList.remove('hidden');

  // Fetch order details to show proof
  const res  = await fetch(APP_URL + '/api/v1/orders/show.php?id=' + orderId, { headers:{'Authorization':'Bearer '+TOKEN} });
  const data = await res.json();
  const o    = data.data || {};

  const itemPrice    = parseFloat(o.item_price   || 0);
  const deliveryFee  = parseFloat(o.delivery_fee  || 0);
  const tipAmount    = parseFloat(o.tip_amount    || 0);
  const totalPrice   = parseFloat(o.total_price   || (itemPrice + deliveryFee + tipAmount));
  const platformKeep = deliveryFee + tipAmount;

  const field = (label, value, extra = '') => `
    <div class="surface-muted p-3">
      <p class="text-[11px] font-semibold text-charcoal-muted dark:text-gray-500">${label}</p>
      <p class="mt-0.5 text-sm font-bold truncate ${extra}">${value}</p>
    </div>`;
  const line = (ic, label, value, strong = '') => `
    <div class="flex items-center justify-between text-sm">
      <span class="flex items-center gap-2 text-charcoal-muted dark:text-gray-400">${icon(ic, 'w-4 h-4')}${label}</span>
      <span class="font-semibold num ${strong}">${value}</span>
    </div>`;

  document.getElementById('payment-proof-content').innerHTML = `
    <div class="space-y-4">
      <div class="flex items-center gap-3">
        ${LK.avatar(o.customer_name, 'w-10 h-10 text-xs')}
        <div class="min-w-0">
          <p class="text-sm font-bold truncate">${escHtml(o.customer_name||'')}</p>
          <p class="text-xs text-charcoal-muted dark:text-gray-400 truncate"><span class="font-bold num">${escHtml(o.order_code || ('LK'+o.id))}</span> · ${escHtml(o.item_name||'')}</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-2">
        ${field('Method', o.payment_method ? o.payment_method.replace('_',' ') : '—', 'capitalize')}
        ${field('Total paid', fmtEtb(totalPrice), 'num')}
        ${field('Transaction ID', escHtml(o.payment_txn_id||'—'), 'font-mono text-[13px]')}
        ${field('Sender name', escHtml(o.payment_sender_name||'—'))}
      </div>

      <div class="rounded-2xl border border-gray-200/80 dark:border-white/[0.07] p-4 space-y-2.5">
        <p class="text-[11px] font-bold uppercase tracking-wider text-charcoal-muted dark:text-gray-500">Payment distribution</p>
        ${line('store', 'Vendor receives (items)', fmtEtb(itemPrice))}
        ${line('bike', 'Delivery fee', fmtEtb(deliveryFee))}
        ${tipAmount > 0 ? line('gift', 'Tip', fmtEtb(tipAmount)) : ''}
        <div class="pt-2.5 border-t border-dashed border-gray-200 dark:border-white/10">
          ${line('wallet', 'Platform retains (fee + tip)', fmtEtb(platformKeep), 'text-amber-600 dark:text-amber-400 font-extrabold')}
        </div>
      </div>

      ${o.payment_proof_path ? `
      <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-charcoal-muted dark:text-gray-500 mb-2">Payment screenshot</p>
        <a href="${APP_URL}/${escHtml(o.payment_proof_path)}" target="_blank" rel="noopener" class="block rounded-2xl overflow-hidden border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/[0.03]">
          <img src="${APP_URL}/${escHtml(o.payment_proof_path)}" class="w-full object-contain max-h-72" alt="Payment proof">
        </a>
      </div>` : `<div class="surface-muted p-4 text-sm text-charcoal-muted dark:text-gray-400 flex items-center gap-2">${icon('image', 'w-4 h-4')}No screenshot uploaded.</div>`}

      <p class="text-xs text-charcoal-muted dark:text-gray-400 flex items-center gap-1.5">${icon('map-pin', 'w-3.5 h-3.5')}Deliver to ${escHtml(o.building_name||'')} · ${escHtml(o.house_no||'')}</p>
    </div>`;
}

function closePaymentModal() { document.getElementById('payment-modal').classList.add('hidden'); currentPaymentOrderId = null; }

async function verifyPayment(action) {
  const reason = action === 'reject' ? (prompt('Rejection reason (optional):') || 'Payment could not be verified.') : null;
  const res    = await fetch(APP_URL + '/api/v1/admin/verify-payment.php', {
    method: 'POST', headers: {'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
    body: JSON.stringify({ order_id: currentPaymentOrderId, action, ...(reason ? {reason} : {}) }),
  });
  const data = await res.json();
  if (data.status === 'success') {
    showToast(action === 'approve' ? 'Payment approved!' : 'Payment rejected', '', action === 'approve' ? 'success' : 'warning');
    closePaymentModal();
    loadOrders();
  } else showToast(data.message || 'Error', '', 'error');
}

// ── Bid Review ─────────────────────────────────────────────────────────────
async function openBidModal(orderId, itemName, qty) {
  currentBidOrderId = orderId;
  document.getElementById('bid-modal-info').textContent = `Order #${orderId}: ${itemName} ×${qty}`;
  document.getElementById('bid-modal-list').innerHTML = '<div class="skeleton h-14 rounded-xl"></div><div class="skeleton h-14 rounded-xl mt-2"></div>';
  document.getElementById('bid-modal').classList.remove('hidden');

  const res  = await fetch(APP_URL + '/api/v1/orders/show.php?id=' + orderId, {
    headers: { 'Authorization': 'Bearer ' + TOKEN }
  });
  const data = await res.json();
  const bids = (data.data || {}).bids || [];
  const list = document.getElementById('bid-modal-list');

  if (!bids.length) {
    list.innerHTML = LK.empty('hourglass', 'No bids yet', 'Vendors near this building haven\'t sent a price yet.');
    return;
  }

  list.innerHTML = bids.map((b, i) => `
    <div class="flex items-center gap-3 p-3 rounded-2xl border ${i === 0 ? 'bg-emerald-50/70 dark:bg-emerald-500/[0.07] border-emerald-300/70 dark:border-emerald-500/30' : 'border-gray-200/80 dark:border-white/[0.07]'}">
      <span class="icon-tile ${i === 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400'}">${icon('store', 'w-5 h-5')}</span>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold truncate">${escHtml(b.shop_name || b.vendor_name || '—')}</p>
        <p class="text-xs text-charcoal-muted dark:text-gray-400 truncate">${b.shop_name ? escHtml(b.vendor_name || '') : ''}${i === 0 ? `${b.shop_name ? ' · ' : ''}<span class="font-semibold text-emerald-700 dark:text-emerald-400">Lowest price</span>` : ''}</p>
      </div>
      <p class="text-base font-extrabold num">${fmtEtb(b.price)}</p>
      ${!b.is_selected ? `
      <button onclick="forwardBid(${b.id})" class="btn-brand btn-sm flex-shrink-0">Forward ${icon('arrow-right', 'w-3.5 h-3.5')}</button>`
      : `<span class="badge ${LK.TONES.emerald}">${icon('check', 'w-3 h-3')}Sent</span>`}
    </div>`).join('');
}

function closeBidModal() { document.getElementById('bid-modal').classList.add('hidden'); currentBidOrderId = null; }

async function forwardBid(bidId) {
  if (!confirm('Forward this bid to the customer?')) return;
  const res  = await fetch(APP_URL + '/api/v1/admin/forward-bid.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
    body: JSON.stringify({ order_id: currentBidOrderId, bid_id: bidId }),
  });
  const data = await res.json();
  if (data.status === 'success') {
    showToast('Bid forwarded to customer!', '', 'success');
    closeBidModal();
    loadOrders();
  } else {
    showToast(data.message || 'Error', '', 'error');
  }
}

// ── Review & Edit Order ────────────────────────────────────────────────────
function openReviewModal(orderId, itemName, qty, notes) {
  currentReviewOrderId = orderId;
  document.getElementById('review-order-info').textContent = `Order #${orderId}`;
  document.getElementById('review-item-name').value = itemName;
  document.getElementById('review-quantity').value  = qty;
  document.getElementById('review-notes').value     = notes;
  document.getElementById('review-modal').classList.remove('hidden');
}

function closeReviewModal() { document.getElementById('review-modal').classList.add('hidden'); currentReviewOrderId = null; }

async function saveOrderEdits() {
  const itemName = document.getElementById('review-item-name').value.trim();
  const quantity = parseInt(document.getElementById('review-quantity').value) || 1;
  const notes    = document.getElementById('review-notes').value.trim();
  if (!itemName) { showToast('Item name is required', '', 'error'); return; }

  const res  = await fetch(APP_URL + '/api/v1/admin/order-actions.php', {
    method: 'POST', headers: {'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
    body: JSON.stringify({ action: 'edit_order', order_id: currentReviewOrderId, item_name: itemName, quantity, notes }),
  });
  const data = await res.json();
  if (data.status === 'success') {
    showToast('Order updated!', '', 'success');
    // Update inputs to saved values
    document.getElementById('review-item-name').value = itemName;
  } else showToast(data.message || 'Error', '', 'error');
}

async function openForBid(orderId) {
  if (!confirm(`Open Order #${orderId} for vendor bidding?`)) return;
  const window = prompt('Bid window (seconds):', '120');
  if (window === null) return;
  const res  = await fetch(APP_URL + '/api/v1/admin/order-actions.php', {
    method: 'POST', headers: {'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
    body: JSON.stringify({ action: 'open_for_bid', order_id: orderId, bid_window_seconds: parseInt(window)||120 }),
  });
  const data = await res.json();
  if (data.status === 'success') { showToast('Order opened for bidding!', 'Vendors will be notified.', 'success'); loadOrders(); }
  else showToast(data.message || 'Error', '', 'error');
}

async function openForBidFromModal() {
  const itemName = document.getElementById('review-item-name').value.trim();
  const quantity = parseInt(document.getElementById('review-quantity').value) || 1;
  const notes    = document.getElementById('review-notes').value.trim();
  const orderId  = currentReviewOrderId;

  // Save edits first, then open for bid
  if (itemName) {
    await fetch(APP_URL + '/api/v1/admin/order-actions.php', {
      method: 'POST', headers: {'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
      body: JSON.stringify({ action: 'edit_order', order_id: orderId, item_name: itemName, quantity, notes }),
    });
  }

  const window = prompt('Bid window (seconds):', '120');
  if (window === null) return;
  const res  = await fetch(APP_URL + '/api/v1/admin/order-actions.php', {
    method: 'POST', headers: {'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
    body: JSON.stringify({ action: 'open_for_bid', order_id: orderId, bid_window_seconds: parseInt(window)||120 }),
  });
  const data = await res.json();
  if (data.status === 'success') {
    showToast('Order opened for bidding!', '', 'success');
    closeReviewModal();
    loadOrders();
  } else showToast(data.message || 'Error', '', 'error');
}

// ── Mark Vendor Paid ───────────────────────────────────────────────────────
function openVendorPaidModal(orderId, itemName, itemPrice) {
  currentVendorPaidOrderId = orderId;
  document.getElementById('vendor-paid-order-info').textContent = `Order #${orderId}: ${itemName} — Item price: ETB ${parseFloat(itemPrice).toLocaleString()}`;
  document.getElementById('vendor-paid-ref').value    = '';
  document.getElementById('vendor-paid-amount').value = itemPrice || '';
  document.getElementById('vendor-paid-modal').classList.remove('hidden');
}

function closeVendorPaidModal() { document.getElementById('vendor-paid-modal').classList.add('hidden'); currentVendorPaidOrderId = null; }

async function confirmVendorPaid() {
  const ref    = document.getElementById('vendor-paid-ref').value.trim();
  const amount = parseFloat(document.getElementById('vendor-paid-amount').value) || null;
  const res    = await fetch(APP_URL + '/api/v1/admin/order-actions.php', {
    method: 'POST', headers: {'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
    body: JSON.stringify({ action: 'mark_vendor_paid', order_id: currentVendorPaidOrderId, payment_ref: ref, amount }),
  });
  const data = await res.json();
  if (data.status === 'success') {
    showToast('Vendor marked as paid!', 'Delivery agent can now be assigned.', 'success');
    closeVendorPaidModal();
    loadOrders();
  } else showToast(data.message || 'Error', '', 'error');
}

// ── Reinitiate cancelled/expired order ────────────────────────────────────
async function reinitiateOrder(orderId) {
  if (!confirm(`Re-initiate Order #${orderId} for bidding? Old bids will be cleared.`)) return;
  const res  = await fetch(APP_URL + '/api/v1/admin/reinitiate.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
    body: JSON.stringify({ order_id: orderId }),
  });
  const data = await res.json();
  if (data.status === 'success') {
    showToast('Order reinitiated!', 'Vendors have been notified.', 'success');
    loadOrders();
  } else {
    showToast(data.message || 'Error', '', 'error');
  }
}

// Activate URL filter on load
filterStatus(currentFilter);
setInterval(loadOrders, 8000);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
