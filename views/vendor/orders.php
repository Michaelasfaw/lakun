<?php
$pageTitle = 'Orders';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-3xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Vendor</p>
      <h1 class="page-title">Orders</h1>
      <p class="page-sub">Bid on nearby requests and manage your active orders.</p>
    </div>
    <span class="badge bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/15 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/20">
      <span class="relative flex w-2 h-2"><span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping-slow"></span><span class="relative inline-flex rounded-full w-2 h-2 bg-emerald-500"></span></span>
      Live
    </span>
  </div>

  <!-- Tabs -->
  <div class="segmented w-full mb-5">
    <button onclick="switchTab('open')" id="tab-open" class="tab-btn flex-1 is-active inline-flex items-center justify-center gap-2">
      <?= icon('tag', 'w-4 h-4') ?>Open bids
    </button>
    <button onclick="switchTab('won')" id="tab-won" class="tab-btn flex-1 inline-flex items-center justify-center gap-2">
      <?= icon('package', 'w-4 h-4') ?>My orders
    </button>
  </div>

  <!-- Open orders feed -->
  <div id="pane-open">
    <div id="orders-feed" class="space-y-4">
      <div class="skeleton h-44 rounded-2xl"></div>
      <div class="skeleton h-44 rounded-2xl"></div>
    </div>
    <div id="empty-open" class="hidden card">
      <div class="empty">
        <div class="empty-icon"><?= icon('inbox', 'w-7 h-7') ?></div>
        <h3 class="text-base font-bold text-charcoal dark:text-cream">No nearby orders right now</h3>
        <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5 max-w-xs">Requests from customers in your area will show up here the moment they arrive.</p>
        <button onclick="setShopLocation()" class="btn-soft btn-sm mt-5"><?= icon('map-pin', 'w-4 h-4') ?>Update my shop location</button>
      </div>
    </div>
  </div>

  <!-- Won / active orders pane -->
  <div id="pane-won" class="hidden">
    <div id="won-feed" class="space-y-4">
      <div class="skeleton h-40 rounded-2xl"></div>
    </div>
    <div id="empty-won" class="hidden card">
      <div class="empty">
        <div class="empty-icon"><?= icon('package', 'w-7 h-7') ?></div>
        <h3 class="text-base font-bold text-charcoal dark:text-cream">No active orders</h3>
        <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5 max-w-xs">Orders you've won will appear here.</p>
      </div>
    </div>
  </div>
</div>

<!-- Bid modal -->
<div id="bid-modal" class="hidden fixed inset-0 z-50 flex items-end md:items-center justify-center">
  <div class="absolute inset-0 bg-ink-900/60 backdrop-blur-sm" onclick="closeBidModal()"></div>
  <div class="relative bg-white dark:bg-charcoal-light rounded-t-3xl md:rounded-3xl shadow-2xl w-full max-w-md mx-auto z-10 max-h-[92vh] overflow-y-auto border border-gray-200/60 dark:border-white/[0.06]">
    <div class="p-5 md:p-6">
      <div class="flex items-start gap-3 mb-5">
        <span class="icon-tile-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"><?= icon('tag', 'w-6 h-6') ?></span>
        <div class="flex-1 min-w-0">
          <p class="page-eyebrow !mb-0.5">Your bid</p>
          <h3 class="text-lg font-extrabold text-charcoal dark:text-cream leading-snug break-words" id="modal-item-name">Submit bid</h3>
          <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-0.5" id="modal-order-info"></p>
        </div>
        <button type="button" onclick="closeBidModal()" class="icon-btn -mr-2 -mt-1" aria-label="Close"><?= icon('x', 'w-5 h-5') ?></button>
      </div>

      <!-- Per-item price inputs (multi-item orders) -->
      <div id="item-prices-section" class="hidden mb-4">
        <p class="label">Price per item (ETB)</p>
        <div id="item-prices-list" class="space-y-2"></div>
        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-white/[0.08] flex justify-between items-center">
          <span class="text-sm font-semibold text-charcoal-muted dark:text-gray-400">Total</span>
          <span id="items-total-display" class="text-lg font-extrabold text-charcoal dark:text-cream num">ETB 0.00</span>
        </div>
      </div>

      <!-- Single price input (single-item orders) -->
      <div id="single-price-section" class="mb-4">
        <label class="label" for="bid-price">Your price for all items</label>
        <div class="relative">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-charcoal-muted dark:text-gray-400">ETB</span>
          <input type="number" id="bid-price" min="1" step="0.01" placeholder="e.g. 1500" class="input pl-14 text-lg font-bold num">
        </div>
        <p class="hint mt-1.5">The delivery fee is added separately by the platform.</p>
      </div>

      <div class="mb-4">
        <label class="label" for="bid-note">Note to customer <span class="font-normal text-charcoal-muted dark:text-gray-500">(optional)</span></label>
        <input type="text" id="bid-note" placeholder="e.g. Fresh stock, ready in 15 min" class="input">
      </div>

      <label class="flex items-center gap-3 mb-5 cursor-pointer surface-muted px-4 py-3">
        <input type="checkbox" id="bid-available" checked class="w-4 h-4 accent-amber-600">
        <span class="text-sm font-medium text-charcoal dark:text-cream">I have all items in stock</span>
      </label>
      <button onclick="submitBid()" class="btn-brand btn-lg w-full" id="bid-submit-btn">Submit bid</button>
      <button onclick="closeBidModal()" class="btn-ghost w-full mt-2">Cancel</button>
    </div>
  </div>
</div>

<script>
const APP_URL  = '<?= $appUrl ?>';
const TOKEN    = localStorage.getItem('td_token');
let currentOrderId = null;
let currentItems   = [];
let currentTab     = 'open';
let _timers        = [];

function switchTab(tab) {
  currentTab = tab;
  document.getElementById('pane-open').classList.toggle('hidden', tab !== 'open');
  document.getElementById('pane-won').classList.toggle('hidden', tab !== 'won');
  document.getElementById('tab-open').classList.toggle('is-active', tab === 'open');
  document.getElementById('tab-won').classList.toggle('is-active', tab === 'won');
  if (tab === 'won') loadWonOrders();
}

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtEtb(v)  { return 'ETB ' + parseFloat(v||0).toLocaleString('en-ET',{minimumFractionDigits:2,maximumFractionDigits:2}); }

function timerClasses(secs) {
  return secs < 15
    ? 'bg-red-50 text-red-600 ring-1 ring-inset ring-red-600/15 dark:bg-red-500/10 dark:text-red-300'
    : 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/15 dark:bg-blue-500/10 dark:text-blue-300';
}

// ── Open orders (bidding) ────────────────────────────────────────────────────
async function loadOrders() {
  try {
    const res = await fetch(APP_URL + '/api/v1/orders/index.php', {
      headers: { 'Authorization': 'Bearer ' + TOKEN }
    });
    const data = await res.json();
    const orders = data.data || [];
    const feed  = document.getElementById('orders-feed');
    const empty = document.getElementById('empty-open');

    _timers.forEach(clearInterval); _timers = [];

    if (!orders.length) { feed.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');

    // Cache order data by ID so onclick handlers don't need inline JSON
    const _orderCache = {};
    orders.forEach(o => {
      let parsedItems = [];
      try { parsedItems = o.items_json ? JSON.parse(o.items_json) : []; } catch {}
      _orderCache[o.id] = { itemName: o.item_name, items: parsedItems };
    });
    window._orderCache = _orderCache;

    feed.innerHTML = orders.map((o, idx) => {
      const secsLeft   = Math.max(0, parseInt(o.seconds_remaining) || 0);
      const alreadyBid = o.my_bid_id;
      const orderCode  = o.order_code || ('LK' + o.id);
      const cached     = _orderCache[o.id];
      const parsedItems = cached.items;

      let itemsPreview = escHtml(o.item_name);
      if (parsedItems.length > 1) {
        itemsPreview = parsedItems.map(it => `${escHtml(it.name)} ×${it.qty}`).join(', ');
      }

      return `<div class="card task-card overflow-hidden animate-card-appear" style="animation-delay:${idx*40}ms" id="order-${o.id}">
        <div class="p-4 md:p-5">
          <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1.5 flex-wrap mb-1.5">
                <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400 tracking-wide">${escHtml(orderCode)}</span>
                ${o.distance_label ? `<span class="badge ${LK.TONES.emerald}">${icon('map-pin','w-3 h-3')}${escHtml(o.distance_label)}</span>` : ''}
                ${parsedItems.length > 1 ? `<span class="badge ${LK.TONES.violet}">${icon('list','w-3 h-3')}${parsedItems.length} items</span>` : ''}
              </div>
              <h3 class="font-bold text-charcoal dark:text-cream text-[15px] leading-snug break-words">${itemsPreview}</h3>
              <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-1.5 flex items-start gap-1.5">${icon('building','w-3.5 h-3.5 mt-px')}<span>${escHtml(o.building_name)} &middot; Block ${escHtml(o.block)}, Floor ${escHtml(o.floor)}, House ${escHtml(o.house_no)}</span></p>
              ${o.notes ? `<p class="text-xs text-amber-700 dark:text-amber-300 mt-2 surface-muted !bg-amber-50/60 dark:!bg-amber-500/5 px-2.5 py-1.5 flex items-start gap-1.5">${icon('message','w-3.5 h-3.5 mt-px')}<span>${escHtml(o.notes)}</span></p>` : ''}
            </div>
            <div class="flex-shrink-0 text-center">
              <div class="inline-flex items-center gap-1.5 rounded-xl px-2.5 py-1.5 ${timerClasses(secsLeft)}" id="timer-wrap-${o.id}">
                ${icon('clock','w-3.5 h-3.5')}<span class="text-base font-extrabold num leading-none" id="timer-${o.id}">${Math.floor(secsLeft/60)}:${String(secsLeft%60).padStart(2,"0")}</span>
              </div>
              <div class="text-[10px] font-semibold uppercase tracking-wider text-charcoal-muted dark:text-gray-500 mt-1">remaining</div>
            </div>
          </div>
          ${alreadyBid && secsLeft > 0
            ? `<div class="flex gap-2">
                 <div class="flex-1 flex items-center gap-2 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 text-sm font-semibold px-3 py-2.5 rounded-xl">
                   ${icon('check-circle','w-4 h-4')}Bid submitted
                 </div>
                 <button onclick="openBidModalById(${o.id})" class="btn-soft">${icon('edit','w-4 h-4')}Edit</button>
               </div>`
            : alreadyBid
            ? `<div class="flex items-center gap-2 surface-muted text-charcoal-muted dark:text-gray-400 text-sm px-4 py-2.5">
                 ${icon('lock','w-4 h-4')}Bid submitted &mdash; window closed
               </div>`
            : `<button onclick="openBidModalById(${o.id})" class="btn-brand w-full">${icon('tag','w-4 h-4')}Submit my price</button>`
          }
        </div>
      </div>`;
    }).join('');

    // Countdown timers
    orders.forEach(o => {
      let secs = Math.max(0, parseInt(o.seconds_remaining) || 0);
      const el = document.getElementById(`timer-${o.id}`);
      const wrap = document.getElementById(`timer-wrap-${o.id}`);
      if (!el) return;
      const iv = setInterval(() => {
        secs--;
        if (secs <= 0) { clearInterval(iv); el.textContent = 'Closed'; loadOrders(); return; }
        el.textContent = Math.floor(secs / 60) + ':' + String(secs % 60).padStart(2, '0');
        wrap.className = `inline-flex items-center gap-1.5 rounded-xl px-2.5 py-1.5 ${timerClasses(secs)}`;
      }, 1000);
      _timers.push(iv);
    });
  } catch(e) { console.error(e); }
}

// ── Won / active orders ──────────────────────────────────────────────────────
async function loadWonOrders() {
  const feed  = document.getElementById('won-feed');
  const empty = document.getElementById('empty-won');
  try {
    const res = await fetch(APP_URL + '/api/v1/orders/index.php?type=won', {
      headers: { 'Authorization': 'Bearer ' + TOKEN }
    });
    const data   = await res.json();
    const orders = data.data || [];

    if (!orders.length) { feed.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');

    feed.innerHTML = orders.map((o, idx) => {
      const orderCode = o.order_code || ('LK' + o.id);
      const canApprove = ['confirmed','assigned'].includes(o.status) && !o.vendor_approved_at;
      const approved   = !!o.vendor_approved_at;
      const st = LK.status(o.status);

      let itemsHtml = escHtml(o.item_name);
      try {
        const its = o.items_json ? JSON.parse(o.items_json) : [];
        if (its.length > 1) itemsHtml = its.map(it => `${escHtml(it.name)} ×${it.qty}`).join(' · ');
      } catch {}

      return `<div class="card overflow-hidden animate-card-appear" style="animation-delay:${idx*40}ms">
        <div class="flex">
          <div class="w-1 ${st.bar} flex-shrink-0"></div>
          <div class="flex-1 min-w-0 p-4 md:p-5">
            <div class="flex items-start justify-between gap-3 mb-2">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-extrabold text-amber-600 dark:text-amber-400">${escHtml(orderCode)}</span>
                ${LK.statusBadge(o.status)}
              </div>
              <span class="text-sm font-bold text-charcoal dark:text-cream num">${fmtEtb(o.item_price||0)}</span>
            </div>
            <p class="text-sm font-semibold text-charcoal dark:text-cream mb-1 break-words">${itemsHtml}</p>
            <p class="text-xs text-charcoal-muted dark:text-gray-400 flex items-center gap-1.5">${icon('map-pin','w-3.5 h-3.5')}Block ${escHtml(o.block||'')}, Floor ${escHtml(o.floor||'')}, House ${escHtml(o.house_no||'')}</p>
            ${o.agent_name ? `<p class="text-xs text-charcoal-muted dark:text-gray-400 mt-1 flex items-center gap-1.5">${icon('bike','w-3.5 h-3.5')}Rider: ${escHtml(o.agent_name)}</p>` : ''}

            ${canApprove
              ? `<button onclick="approvePickup(${o.id}, this)" class="btn-success w-full mt-4">${icon('check-circle','w-4 h-4')}Approve pickup &mdash; items ready</button>
                 <p class="hint mt-1.5 text-center">Tap once items are packed and ready for rider pickup.</p>`
              : approved
              ? `<div class="mt-4 flex items-center gap-2 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 text-xs font-semibold px-3 py-2.5 rounded-xl">
                   ${icon('check-circle','w-4 h-4')}Pickup approved &mdash; rider notified
                 </div>`
              : ''}
          </div>
        </div>
      </div>`;
    }).join('');
  } catch(e) { console.error(e); }
}

// ── Bid modal ────────────────────────────────────────────────────────────────
function openBidModalById(orderId) {
  const cached = (window._orderCache || {})[orderId] || {};
  openBidModal(orderId, cached.itemName || '', cached.items || []);
}

function openBidModal(orderId, itemName, items) {
  currentOrderId = orderId;
  currentItems   = Array.isArray(items) ? items : [];

  document.getElementById('modal-item-name').textContent = itemName || 'Submit bid';
  document.getElementById('modal-order-info').textContent = currentItems.length > 1
    ? `${currentItems.length} items — price each below`
    : 'Enter your price for all items';

  // Multi-item vs single-item mode
  const multiMode = currentItems.length > 1;
  document.getElementById('item-prices-section').classList.toggle('hidden', !multiMode);
  document.getElementById('single-price-section').classList.toggle('hidden', multiMode);

  if (multiMode) {
    document.getElementById('item-prices-list').innerHTML = currentItems.map((it, i) =>
      `<div class="flex items-center gap-3">
        <label for="item-price-${i}" class="flex-1 text-sm font-medium text-charcoal dark:text-cream min-w-0 break-words">${escHtml(it.name)} <span class="text-charcoal-muted dark:text-gray-400">×${it.qty}</span></label>
        <input type="number" min="0" step="0.01" placeholder="0.00" id="item-price-${i}"
               oninput="recalcTotal()"
               class="input w-32 !py-2.5 num text-right">
      </div>`
    ).join('');
    recalcTotal();
  } else {
    document.getElementById('bid-price').value = '';
  }

  document.getElementById('bid-note').value = '';
  document.getElementById('bid-available').checked = true;
  document.getElementById('bid-submit-btn').textContent = 'Submit bid';
  document.getElementById('bid-modal').classList.remove('hidden');
  setTimeout(() => {
    const first = multiMode ? document.querySelector('#item-prices-list input') : document.getElementById('bid-price');
    if (first) first.focus();
  }, 100);
}

function recalcTotal() {
  let total = 0;
  currentItems.forEach((_, i) => {
    total += parseFloat(document.getElementById(`item-price-${i}`)?.value || 0);
  });
  document.getElementById('items-total-display').textContent = fmtEtb(total);
  document.getElementById('bid-price').value = total; // keep hidden total in sync
}

function closeBidModal() {
  document.getElementById('bid-modal').classList.add('hidden');
  currentOrderId = null;
  currentItems   = [];
}

async function submitBid() {
  const multiMode = currentItems.length > 1;
  let price = 0;
  let itemsBreakdown = null;

  if (multiMode) {
    itemsBreakdown = currentItems.map((it, i) => ({
      name: it.name, qty: it.qty,
      price: parseFloat(document.getElementById(`item-price-${i}`)?.value || 0),
    }));
    price = itemsBreakdown.reduce((s, it) => s + it.price, 0);
    if (itemsBreakdown.some(it => it.price <= 0)) {
      showToast('Enter a price for every item', '', 'error'); return;
    }
  } else {
    price = parseFloat(document.getElementById('bid-price').value);
  }

  if (!price || price <= 0) { showToast('Enter a valid price', '', 'error'); return; }

  const btn = document.getElementById('bid-submit-btn');
  btn.disabled = true; btn.textContent = 'Submitting…';

  try {
    const res = await fetch(APP_URL + '/api/v1/bids/submit.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
      body: JSON.stringify({
        order_id:        currentOrderId,
        price,
        available:       document.getElementById('bid-available').checked,
        note:            document.getElementById('bid-note').value.trim() || null,
        items_breakdown: itemsBreakdown,
      }),
    });
    const data = await res.json();
    if (data.status === 'success') {
      showToast('Bid submitted', fmtEtb(price), 'success');
      closeBidModal();
      loadOrders();
    } else {
      showToast(data.message || 'Error', '', 'error');
    }
  } catch { showToast('Network error', '', 'error'); }
  btn.disabled = false; btn.textContent = 'Submit bid';
}

// ── Approve pickup ───────────────────────────────────────────────────────────
const APPROVE_LABEL = icon('check-circle','w-4 h-4') + 'Approve pickup — items ready';
async function approvePickup(orderId, btn) {
  btn.disabled = true; btn.textContent = 'Approving…';
  try {
    const res = await fetch(APP_URL + '/api/v1/vendor/approve-pickup.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
      body: JSON.stringify({ order_id: orderId }),
    });
    const data = await res.json();
    if (data.status === 'success') {
      showToast('Pickup approved', 'The rider has been notified.', 'success');
      loadWonOrders();
    } else {
      showToast(data.message || 'Error', '', 'error');
      btn.disabled = false; btn.innerHTML = APPROVE_LABEL;
    }
  } catch {
    showToast('Network error', '', 'error');
    btn.disabled = false; btn.innerHTML = APPROVE_LABEL;
  }
}

// ── Shop location update ─────────────────────────────────────────────────────
function setShopLocation() {
  if (!navigator.geolocation) { showToast('Geolocation not supported', '', 'error'); return; }
  showToast('Getting your location…', 'Please allow access', 'info');
  navigator.geolocation.getCurrentPosition(async (pos) => {
    const { latitude, longitude } = pos.coords;
    try {
      const res = await fetch(APP_URL + '/api/v1/location/update-position.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
        body: JSON.stringify({ latitude, longitude }),
      });
      const data = await res.json();
      if (data.status === 'success') {
        const nb = data.data.nearest_building;
        showToast('Location saved', nb ? `Nearest: ${nb.name} (${nb.distance_label})` : 'Position stored.', 'success');
        loadOrders();
      } else {
        showToast(data.message || 'Failed to save location', '', 'error');
      }
    } catch { showToast('Network error', '', 'error'); }
  }, () => showToast('Location access denied', 'Enable it in browser settings', 'error'));
}

loadOrders();
setInterval(loadOrders, 5000);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
