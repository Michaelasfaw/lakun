<?php
$pageTitle = 'Track order';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-5xl mx-auto">
  <div class="page-head">
    <div class="flex items-center gap-3">
      <a href="<?= $appUrl ?>/customer/orders" class="icon-btn border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03]" aria-label="Back to orders"><?= icon('chevron-left', 'w-5 h-5') ?></a>
      <div>
        <p class="page-eyebrow">Order tracking</p>
        <h1 class="page-title">Order #<?= (int)$orderId ?></h1>
      </div>
    </div>
    <span class="inline-flex items-center gap-2 text-xs font-semibold text-charcoal-muted dark:text-gray-400">
      <span class="relative flex w-2 h-2"><span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping-slow"></span><span class="relative inline-flex rounded-full w-2 h-2 bg-emerald-500"></span></span>
      Live &middot; updates every 4 seconds
    </span>
  </div>
  <div id="order-detail" class="space-y-4">
    <div class="skeleton h-40 rounded-2xl"></div>
    <div class="grid lg:grid-cols-[1fr_340px] gap-4">
      <div class="skeleton h-64 rounded-2xl"></div>
      <div class="skeleton h-64 rounded-2xl"></div>
    </div>
  </div>
</div>

<!-- Payment upload modal -->
<div id="payment-modal" class="hidden fixed inset-0 z-50 items-end md:items-center justify-center">
  <div class="absolute inset-0 bg-ink/60 backdrop-blur-sm" onclick="closePaymentModal()"></div>
  <div class="relative card !rounded-b-none md:!rounded-3xl shadow-2xl w-full max-w-md mx-auto z-10 max-h-[92vh] overflow-y-auto animate-fade-slide-up">
    <div class="p-5 md:p-6">
      <div class="flex items-start gap-3 mb-4">
        <span class="icon-tile-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"><?= icon('upload', 'w-6 h-6') ?></span>
        <div class="flex-1 min-w-0">
          <h3 class="text-lg font-extrabold tracking-tight">Upload payment proof</h3>
          <p class="text-sm font-semibold text-amber-700 dark:text-amber-300 num" id="pay-amount-label"></p>
        </div>
        <button type="button" onclick="closePaymentModal()" class="icon-btn -mr-2 -mt-1" aria-label="Close"><?= icon('x', 'w-5 h-5') ?></button>
      </div>

      <!-- Payment instructions -->
      <div id="payment-instructions" class="rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200/80 dark:border-amber-500/20 p-4 mb-5 space-y-2 text-sm"></div>

      <div class="space-y-4">
        <div>
          <label class="label">Payment method <span class="text-red-500">*</span></label>
          <div class="grid grid-cols-2 gap-2.5">
            <button type="button" onclick="selectPayMethod('bank_transfer')" id="btn-bank" data-method="bank_transfer"
                    class="pay-method-btn flex items-center gap-3 border border-gray-200 dark:border-white/10 rounded-2xl p-3 text-left hover:border-amber-400 transition-all">
              <span class="icon-tile w-9 h-9 bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300"><?= icon('building', 'w-[18px] h-[18px]') ?></span>
              <span class="text-[13px] font-bold">Bank transfer</span>
            </button>
            <button type="button" onclick="selectPayMethod('telebirr')" id="btn-telebirr" data-method="telebirr"
                    class="pay-method-btn flex items-center gap-3 border border-gray-200 dark:border-white/10 rounded-2xl p-3 text-left hover:border-amber-400 transition-all">
              <span class="icon-tile w-9 h-9 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300"><?= icon('phone', 'w-[18px] h-[18px]') ?></span>
              <span class="text-[13px] font-bold">Telebirr</span>
            </button>
          </div>
          <input type="hidden" id="pay-method" value="">
        </div>
        <div>
          <label class="label" for="pay-txn-id">Transaction ID <span class="font-normal text-charcoal-muted dark:text-gray-500">(optional)</span></label>
          <input type="text" id="pay-txn-id" placeholder="e.g. TXN123456789" class="input">
        </div>
        <div>
          <label class="label" for="pay-sender">Sender name <span class="font-normal text-charcoal-muted dark:text-gray-500">(optional)</span></label>
          <input type="text" id="pay-sender" placeholder="Name as it appears on the transfer" class="input">
        </div>
        <div>
          <label class="label">Payment screenshot</label>
          <label class="flex items-center gap-3 cursor-pointer border-2 border-dashed border-gray-300 dark:border-white/15 rounded-2xl p-4 hover:border-amber-400 hover:bg-amber-50/50 dark:hover:bg-amber-500/5 transition-all" for="pay-proof-file">
            <span class="icon-tile bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-300"><?= icon('image', 'w-5 h-5') ?></span>
            <div class="min-w-0">
              <p class="text-sm font-semibold truncate" id="file-label">Choose screenshot</p>
              <p class="text-xs text-charcoal-muted dark:text-gray-400">JPEG or PNG, up to 5 MB</p>
            </div>
          </label>
          <input type="file" id="pay-proof-file" accept="image/*" class="hidden" onchange="onFileSelect(this)">
          <img id="proof-preview" class="hidden mt-3 w-full rounded-xl object-contain max-h-40 border border-gray-200 dark:border-white/10" alt="Preview">
          <!-- Scan status banner -->
          <div id="scan-status" class="hidden mt-3 items-center gap-2 text-xs rounded-xl px-3 py-2.5"></div>
        </div>
      </div>
      <div class="flex gap-3 mt-6">
        <button onclick="closePaymentModal()" class="btn-outline flex-1">Cancel</button>
        <button onclick="submitPayment()" id="pay-submit-btn" class="btn-brand flex-1">Submit proof</button>
      </div>
    </div>
  </div>
</div>

<style>
#payment-modal:not(.hidden) { display:flex; }
#scan-status:not(.hidden) { display:flex; }
.pay-method-btn.is-active { border-color:#D97706; background:rgba(217,119,6,.07); box-shadow:0 0 0 3px rgba(217,119,6,.14); }
.bid-card { transition: all .18s ease; }
.bid-card.is-selected { border-color:#D97706; background:rgba(245,154,11,.07); box-shadow:0 0 0 3px rgba(217,119,6,.16); }
.bid-card.is-selected .bid-check { background:#D97706; border-color:#D97706; color:#fff; }
</style>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
const APP_URL  = '<?= $appUrl ?>';
const TOKEN    = localStorage.getItem('td_token');
const ORDER_ID = <?= (int)$orderId ?>;
let paymentSettings = {};

// 5 customer-facing stages (groups internal statuses)
const CUSTOMER_STEPS = [
  { key:'order_placed',  label:'Order placed',    icon:'list',         statuses:['requested','sent_to_vendors','bidding','bids_received'] },
  { key:'price_payment', label:'Price & payment', icon:'card',         statuses:['sent_to_customer','awaiting_confirmation','awaiting_payment','payment_under_review','failed_payment'] },
  { key:'confirmed',     label:'Confirmed',       icon:'check-circle', statuses:['confirmed','assigned'] },
  { key:'on_the_way',    label:'On the way',      icon:'truck',        statuses:['out_for_delivery','picked_up','on_the_way'] },
  { key:'delivered',     label:'Delivered',       icon:'package',      statuses:['delivered','completed'] },
];

// Friendly one-liner per status for the hero card
const STATUS_HINT = {
  requested: 'Your request is being sent to nearby shops.',
  sent_to_vendors: 'Nearby shops are preparing their best price for you.',
  bidding: 'Nearby shops are preparing their best price for you.',
  bids_received: 'Prices are coming in. Hang tight while the bidding window closes.',
  sent_to_customer: 'Prices are ready. Compare and choose the one you like.',
  awaiting_confirmation: 'Prices are ready. Compare and choose the one you like.',
  awaiting_payment: 'Pay the total and upload your proof to get things moving.',
  payment_under_review: 'We are verifying your payment. This usually takes a few minutes.',
  failed_payment: 'Your payment proof was rejected. Please try again.',
  confirmed: 'Payment confirmed. We are finding a rider for you.',
  vendor_paid: 'Payment confirmed. We are finding a rider for you.',
  assigned: 'A rider has been assigned and is heading to the shop.',
  picked_up: 'Your order is picked up and on its way.',
  out_for_delivery: 'Your rider is on the way to your door.',
  on_the_way: 'Your rider is on the way to your door.',
  delivered: 'Your order has arrived. Please confirm you received it.',
  completed: 'All done. Thanks for ordering with Lakun.',
  cancelled: 'This order was cancelled.',
  expired: 'No shop answered in time, so this order expired.',
};

function escHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtEtb(v) { return 'ETB ' + parseFloat(v||0).toLocaleString('en-ET',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function fmtClock(sec) { sec = Math.max(0, parseInt(sec)||0); return Math.floor(sec/60) + ':' + String(sec%60).padStart(2,'0'); }

async function loadOrder() {
  try {
    const res  = await fetch(`${APP_URL}/api/v1/orders/show.php?id=${ORDER_ID}`, { headers:{'Authorization':'Bearer '+TOKEN} });
    const data = await res.json();
    if (data.status === 'success') renderOrder(data.data);
  } catch(e) { console.error(e); }
}

function renderOrder(o) {
  const rawStatus   = o.status;
  const isCancelled = rawStatus === 'cancelled';
  const isExpired   = rawStatus === 'expired';
  const st          = LK.status(rawStatus);

  // Find which customer stage the current status maps to
  const stepIdx = CUSTOMER_STEPS.findIndex(s => s.statuses.includes(rawStatus));
  const hasError = rawStatus === 'failed_payment';
  const stopped = isCancelled || isExpired;

  // ── Stepper (horizontal, 5 stages) ──────────────────────────────────────
  const stepsHtml = CUSTOMER_STEPS.map((s, i) => {
    const isLast        = i === CUSTOMER_STEPS.length - 1;
    const isDoneBefore  = hasError && i < stepIdx;
    const done          = !stopped && !hasError && i <= stepIdx;
    const curr          = !stopped && !hasError && i === stepIdx;
    const isErrorStep   = hasError && i === stepIdx;
    const complete      = (done && !curr) || isDoneBefore || (rawStatus === 'completed' && curr) || (rawStatus === 'delivered' && false);
    const active        = isDoneBefore || done || curr || isErrorStep;

    let circleCls, inner;
    if (isErrorStep) {
      circleCls = 'bg-red-500 text-white ring-4 ring-red-200 dark:ring-red-500/25';
      inner = icon('alert', 'w-[18px] h-[18px]');
    } else if (complete) {
      circleCls = 'bg-amber-500 text-white shadow-sm';
      inner = icon('check', 'w-[18px] h-[18px] !stroke-[3]');
    } else if (curr) {
      circleCls = 'bg-amber-500 text-white ring-4 ring-amber-200 dark:ring-amber-500/25 shadow-md';
      inner = icon(s.icon, 'w-[18px] h-[18px]');
    } else {
      circleCls = 'bg-gray-100 dark:bg-white/[0.06] text-gray-400 dark:text-gray-500';
      inner = icon(s.icon, 'w-[18px] h-[18px]');
    }

    const lineDone = (isDoneBefore || (done && i < stepIdx));
    const labelCls = isErrorStep ? 'text-red-600 dark:text-red-400'
                   : active ? 'text-charcoal dark:text-cream' : 'text-charcoal-muted dark:text-gray-500';

    return `<li class="relative flex-1 flex flex-col items-center text-center min-w-0">
      ${!isLast ? `<span class="absolute top-[18px] left-1/2 w-full h-0.5 rounded-full ${lineDone ? 'bg-amber-500' : 'bg-gray-200 dark:bg-white/10'}"></span>` : ''}
      <span class="relative z-10 w-9 h-9 rounded-full flex items-center justify-center ${circleCls} ${curr && !complete ? 'animate-pulse-dot' : ''}">${inner}</span>
      <span class="mt-2 px-0.5 text-[11px] sm:text-xs leading-tight ${curr || isErrorStep ? 'font-bold' : 'font-semibold'} ${labelCls}">${s.label}</span>
    </li>`;
  }).join('');

  // ── Items ───────────────────────────────────────────────────────────────
  let itemsHtml = '';
  if (o.items_json) {
    try {
      const items = JSON.parse(o.items_json);
      itemsHtml = `<ul class="divide-y divide-gray-100 dark:divide-white/[0.05]">
        ${items.map(it => `<li class="flex items-center justify-between gap-3 py-2.5 text-sm">
          <span class="min-w-0 truncate font-medium">${escHtml(it.name)}${it.brand ? ` <span class="text-xs font-normal text-charcoal-muted dark:text-gray-500">(${escHtml(it.brand)})</span>` : ''}</span>
          <span class="chip num flex-shrink-0">&times;${escHtml(it.qty)}</span>
        </li>`).join('')}
      </ul>`;
    } catch {}
  }
  if (!itemsHtml) itemsHtml = `<div class="flex items-center justify-between gap-3 py-2.5 text-sm"><span class="font-medium">${escHtml(o.item_name)}</span><span class="chip num">&times;${escHtml(o.quantity)}</span></div>`;

  // ── Price breakdown ─────────────────────────────────────────────────────
  const priceHtml = o.total_price ? `
    <div class="surface-muted p-4 space-y-2 text-sm mt-4">
      <div class="flex justify-between"><span class="text-charcoal-muted dark:text-gray-400">Item price</span><span class="num font-semibold">${fmtEtb(o.item_price)}</span></div>
      <div class="flex justify-between"><span class="text-charcoal-muted dark:text-gray-400">Delivery fee</span><span class="num font-semibold">${fmtEtb(o.delivery_fee)}</span></div>
      <div class="flex justify-between items-baseline border-t border-gray-200 dark:border-white/10 pt-2.5">
        <span class="font-bold">Total</span><span class="num text-lg font-extrabold">${fmtEtb(o.total_price)}</span>
      </div>
    </div>` : '';

  // ── Bids ────────────────────────────────────────────────────────────────
  let bidHtml = '';
  const canChooseBid = ['sent_to_customer','awaiting_confirmation'].includes(rawStatus);
  if (o.bids && o.bids.length) {
    if (canChooseBid && o.bids.length > 1) {
      const prices = o.bids.map(b => parseFloat(b.price) || 0);
      const lowest = Math.min(...prices);
      const sorted = [...o.bids].sort((a, b) => (parseFloat(a.price)||0) - (parseFloat(b.price)||0));
      bidHtml = `<section class="card card-pad">
        <div class="flex items-center justify-between gap-3 mb-4">
          <div class="flex items-center gap-3">
            <span class="icon-tile bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">${icon('tag', 'w-5 h-5')}</span>
            <div>
              <h3 class="section-title">Compare prices</h3>
              <p class="hint">${o.bids.length} shops answered. Tap one to select it.</p>
            </div>
          </div>
        </div>
        <div class="space-y-2.5">
          ${sorted.map(bid => {
            const p = parseFloat(bid.price) || 0;
            const best = p === lowest;
            const diff = p - lowest;
            const name = bid.shop_name || bid.vendor_name || 'Vendor';
            return `
            <div class="bid-card relative rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.02] p-4 cursor-pointer hover:border-amber-400 hover:shadow-md" data-bid-id="${bid.id}"
                 onclick="selectVendorBid(${bid.id}, ${bid.price}, this)">
              <div class="flex items-center gap-3.5">
                <span class="hidden sm:block flex-shrink-0">${LK.avatar(name, 'w-11 h-11 text-sm')}</span>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <p class="font-bold text-sm break-words">${escHtml(name)}</p>
                    ${best ? `<span class="badge bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300">${icon('sparkles', 'w-3 h-3')}Best price</span>` : ''}
                  </div>
                  ${bid.shop_name && bid.vendor_name ? `<p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5">${escHtml(bid.vendor_name)}</p>` : ''}
                  ${bid.note ? `<p class="text-xs text-charcoal-muted dark:text-gray-400 mt-1 italic">&ldquo;${escHtml(bid.note)}&rdquo;</p>` : ''}
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="text-base sm:text-lg font-extrabold num leading-none ${best ? 'text-emerald-600 dark:text-emerald-400' : ''}">${fmtEtb(bid.price)}</p>
                  <p class="text-[11px] mt-1 ${best ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-charcoal-muted dark:text-gray-500'} num">${best ? 'Lowest offer' : '+' + fmtEtb(diff) + ' vs lowest'}</p>
                </div>
                <div class="bid-check w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center flex-shrink-0 text-transparent">${icon('check', 'w-3.5 h-3.5 !stroke-[3]')}</div>
              </div>
            </div>`;
          }).join('')}
        </div>
        <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-3 text-center">Delivery fee is added after you choose. Select a shop, then confirm below.</p>
      </section>`;
    } else {
      // Single bid or already-selected vendor
      const bid = o.bids[0];
      bidHtml = `<section class="card card-pad">
        <div class="flex items-center gap-3.5">
          ${LK.avatar(bid.shop_name || bid.vendor_name || 'Vendor', 'w-11 h-11 text-sm')}
          <div class="flex-1 min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Vendor quote</p>
            <p class="font-bold text-sm truncate">${escHtml(bid.shop_name || bid.vendor_name || 'Vendor')}</p>
            ${bid.shop_name && bid.vendor_name ? `<p class="text-xs text-charcoal-muted dark:text-gray-400">from ${escHtml(bid.vendor_name)}</p>` : ''}
          </div>
          <div class="text-right">
            <p class="text-lg font-extrabold num leading-none">${fmtEtb(bid.price)}</p>
            <p class="text-[11px] text-charcoal-muted dark:text-gray-500 mt-1">+ delivery</p>
          </div>
        </div>
      </section>`;
    }
  }

  // ── Action panel (CTA) ──────────────────────────────────────────────────
  let ctaHtml = '';
  const panel = (tone, ic, title, text, extra) => `
    <section class="rounded-2xl border p-5 ${tone.box}">
      <div class="flex items-start gap-3.5">
        <span class="icon-tile ${tone.tile}">${icon(ic, 'w-5 h-5')}</span>
        <div class="flex-1 min-w-0">
          <p class="text-[15px] font-bold ${tone.title}">${title}</p>
          <p class="text-sm mt-0.5 ${tone.text}">${text}</p>
        </div>
      </div>
      ${extra || ''}
    </section>`;
  const TONE = {
    amber:   { box:'bg-amber-50 border-amber-200/80 dark:bg-amber-500/10 dark:border-amber-500/20', tile:'bg-white text-amber-600 dark:bg-amber-500/15 dark:text-amber-300', title:'text-amber-900 dark:text-amber-200', text:'text-amber-800/80 dark:text-amber-300/80' },
    blue:    { box:'bg-blue-50 border-blue-200/80 dark:bg-blue-500/10 dark:border-blue-500/20', tile:'bg-white text-blue-600 dark:bg-blue-500/15 dark:text-blue-300', title:'text-blue-900 dark:text-blue-200', text:'text-blue-800/80 dark:text-blue-300/80' },
    red:     { box:'bg-red-50 border-red-200/80 dark:bg-red-500/10 dark:border-red-500/20', tile:'bg-white text-red-600 dark:bg-red-500/15 dark:text-red-300', title:'text-red-800 dark:text-red-200', text:'text-red-700/80 dark:text-red-300/80' },
    emerald: { box:'bg-emerald-50 border-emerald-200/80 dark:bg-emerald-500/10 dark:border-emerald-500/20', tile:'bg-white text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300', title:'text-emerald-900 dark:text-emerald-200', text:'text-emerald-800/80 dark:text-emerald-300/80' },
  };
  if (rawStatus === 'sent_to_customer' || rawStatus === 'awaiting_confirmation') {
    const hasMany = o.bids && o.bids.length > 1;
    ctaHtml = panel(TONE.amber, hasMany ? 'hand' : 'tag',
      hasMany ? 'Choose a shop and confirm' : 'Your price is ready',
      hasMany ? 'Select your preferred shop below, then confirm to continue to payment.' : 'Review the price and confirm to proceed to payment.',
      `<button onclick="acceptBid()" id="accept-bid-btn" class="btn-brand btn-lg w-full mt-4">
          ${hasMany ? 'Confirm selected shop' : ('Accept price & pay' + (o.total_price ? ' — ' + fmtEtb(o.total_price) : ''))}
          ${icon('arrow-right', 'w-4 h-4')}
        </button>`);
  } else if (rawStatus === 'awaiting_payment') {
    ctaHtml = panel(TONE.amber, 'card', 'Please complete your payment',
      `Pay <strong class="num">${fmtEtb(o.total_price)}</strong> via bank transfer or Telebirr, then upload your proof.`,
      `<button onclick="openPaymentModal(${o.total_price})" class="btn-brand btn-lg w-full mt-4">${icon('upload', 'w-4 h-4')}Upload payment proof</button>`);
  } else if (rawStatus === 'payment_under_review') {
    ctaHtml = panel(TONE.blue, 'shield', 'Payment under review',
      "We're verifying your payment. You'll be notified once it's confirmed.",
      o.payment_txn_id ? `<p class="mt-3 chip font-mono">TXN: ${escHtml(o.payment_txn_id)}</p>` : '');
  } else if (rawStatus === 'failed_payment') {
    ctaHtml = panel(TONE.red, 'alert', 'Payment rejected',
      'Your payment proof was rejected. Please try again with the correct details.',
      `<button onclick="openPaymentModal(${o.total_price})" class="btn-danger btn-lg w-full mt-4">${icon('refresh', 'w-4 h-4')}Resubmit payment proof</button>`);
  } else if (rawStatus === 'delivered') {
    ctaHtml = panel(TONE.emerald, 'package', 'Your order has arrived',
      'Confirm that you received everything to complete the order.',
      `<button onclick="confirmDelivery()" class="btn-success btn-lg w-full mt-4">${icon('check-circle', 'w-4 h-4')}Confirm delivery received</button>`);
  }

  // Cancel
  const canCancel = ['requested','sent_to_vendors','bids_received'].includes(rawStatus);
  const cancelHtml = canCancel
    ? `<button onclick="cancelOrder()" class="btn-ghost btn-sm w-full !text-red-600 dark:!text-red-400 hover:!bg-red-50 dark:hover:!bg-red-500/10">${icon('x-circle', 'w-4 h-4')}Cancel order</button>`
    : '';

  // ── Hero ────────────────────────────────────────────────────────────────
  const bidding = ['sent_to_vendors','bidding'].includes(rawStatus) && o.seconds_remaining > 0;
  const heroHtml = `
    <section class="card overflow-hidden">
      <div class="h-1.5 ${st.bar}"></div>
      <div class="p-5 md:p-6">
        <div class="flex items-start gap-4">
          <span class="icon-tile-lg ${st.tile}">${icon(st.icon, 'w-6 h-6')}</span>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-sm font-extrabold text-amber-600 dark:text-amber-400 num">${escHtml(o.order_code || ('LK'+o.id))}</span>
              ${LK.statusBadge(rawStatus)}
              ${+o.is_fast_order ? `<span class="badge bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/15 dark:bg-orange-500/10 dark:text-orange-300">${icon('flame', 'w-3 h-3')}Express</span>` : ''}
              ${o.category ? `<span class="chip capitalize">${escHtml(o.category)}</span>` : ''}
            </div>
            <p class="mt-2 text-[15px] md:text-base font-semibold leading-snug">${STATUS_HINT[rawStatus] || ''}</p>
            <p class="mt-1.5 text-xs text-charcoal-muted dark:text-gray-400 flex items-center gap-1">${icon('map-pin', 'w-3.5 h-3.5')} Block ${escHtml(o.block||'')}, Floor ${escHtml(o.floor||'')}, House ${escHtml(o.house_no||'')}</p>
          </div>
          ${bidding ? `<div class="hidden sm:flex flex-col items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 px-4 py-2.5 flex-shrink-0">
              <span class="text-2xl font-extrabold num leading-none">${fmtClock(o.seconds_remaining)}</span>
              <span class="text-[10px] font-bold uppercase tracking-wider mt-1">${o.seconds_remaining}s remaining</span>
            </div>` : ''}
        </div>
        ${bidding ? `<div class="sm:hidden mt-4 inline-flex items-center gap-2 badge bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/15 dark:bg-blue-500/10 dark:text-blue-300">${icon('clock', 'w-3 h-3')}<span class="num">${o.seconds_remaining}s remaining</span></div>` : ''}

        <!-- Stepper -->
        <div class="mt-6 pt-5 border-t border-gray-100 dark:border-white/[0.06]">
          ${stopped
            ? `<div class="flex items-center gap-3 text-red-600 dark:text-red-400"><span class="icon-tile bg-red-50 dark:bg-red-500/10">${icon(isCancelled ? 'x-circle' : 'clock', 'w-5 h-5')}</span><span class="font-bold text-sm">${isCancelled ? 'Order cancelled' : 'Order expired'}</span></div>`
            : `${hasError ? `<div class="mb-5 rounded-2xl bg-red-50 dark:bg-red-500/10 border border-red-200/80 dark:border-red-500/20 p-4 flex gap-3 items-start">
                  <span class="text-red-600 dark:text-red-300 mt-0.5">${icon('alert', 'w-5 h-5')}</span>
                  <div>
                    <p class="text-sm font-bold text-red-700 dark:text-red-300">There's a problem with your order</p>
                    <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">Your payment proof was rejected. The highlighted step needs your attention.</p>
                  </div>
                </div>` : ''}
                <ol class="flex items-start">${stepsHtml}</ol>`
          }
        </div>
      </div>
    </section>`;

  // ── Sidebar: order items, people ───────────────────────────────────────
  const peopleHtml = (o.vendor_name || o.agent_name) ? `
    <section class="card card-pad">
      <h3 class="section-title mb-4">Your team</h3>
      <div class="space-y-3.5">
        ${o.vendor_name ? `<div class="flex items-center gap-3">
          ${LK.avatar(o.shop_name || o.vendor_name, 'w-10 h-10 text-xs')}
          <div class="min-w-0 flex-1">
            <p class="text-[11px] font-bold uppercase tracking-wider text-charcoal-muted dark:text-gray-500">Vendor</p>
            <p class="text-sm font-semibold truncate">${escHtml(o.shop_name || o.vendor_name)}</p>
            ${o.shop_name ? `<p class="text-xs text-charcoal-muted dark:text-gray-400 truncate">${escHtml(o.vendor_name)}</p>` : ''}
          </div>
        </div>` : ''}
        ${o.agent_name ? `<div class="flex items-center gap-3">
          ${LK.avatar(o.agent_name, 'w-10 h-10 text-xs')}
          <div class="min-w-0 flex-1">
            <p class="text-[11px] font-bold uppercase tracking-wider text-charcoal-muted dark:text-gray-500">Rider</p>
            <p class="text-sm font-semibold truncate">${escHtml(o.agent_name)}</p>
            ${o.agent_phone ? `<p class="text-xs text-charcoal-muted dark:text-gray-400 num">${escHtml(o.agent_phone)}</p>` : ''}
          </div>
          ${o.agent_phone ? `<a href="tel:${escHtml(o.agent_phone)}" class="btn-soft btn-sm" aria-label="Call rider">${icon('phone', 'w-3.5 h-3.5')}Call</a>` : ''}
        </div>` : ''}
      </div>
    </section>` : '';

  const orderCardHtml = `
    <section class="card card-pad">
      <div class="flex items-center justify-between mb-1">
        <h3 class="section-title">Order summary</h3>
      </div>
      ${itemsHtml}
      ${priceHtml}
    </section>`;

  const hasMain = !!(ctaHtml || bidHtml);
  const mainCol = ctaHtml + bidHtml + (cancelHtml ? `<div class="lg:hidden">${cancelHtml}</div>` : '');
  const sideCancel = cancelHtml ? `<div class="${hasMain ? 'hidden lg:block' : ''}">${cancelHtml}</div>` : '';

  document.getElementById('order-detail').innerHTML = `
    <div class="space-y-4">
      ${heroHtml}
      ${hasMain ? `<div class="grid lg:grid-cols-[1fr_340px] gap-4 items-start">
        <div class="space-y-4 min-w-0">${mainCol}</div>
        <aside class="space-y-4 min-w-0">${orderCardHtml}${peopleHtml}${sideCancel}</aside>
      </div>` : `<div class="grid md:grid-cols-2 gap-4 items-start">
        ${orderCardHtml}
        <div class="space-y-4">${peopleHtml}${sideCancel}</div>
      </div>`}
    </div>`;

  // Polling re-renders the page: restore the shop the customer had selected
  if (canChooseBid && _selectedBidId) {
    const el = document.querySelector(`.bid-card[data-bid-id="${_selectedBidId}"]`);
    if (el) selectVendorBid(_selectedBidId, _selectedBidPrice, el);
    else _selectedBidId = null;
  }
}

// ── Vendor bid selection ──────────────────────────────────────────────────
let _selectedBidId = null;
let _selectedBidPrice = null;

function selectVendorBid(bidId, price, cardEl) {
  _selectedBidId = bidId;
  _selectedBidPrice = price;
  document.querySelectorAll('.bid-card').forEach(c => c.classList.remove('is-selected'));
  cardEl.classList.add('is-selected');
  const btn = document.getElementById('accept-bid-btn');
  if (btn) {
    const label = price != null ? `Confirm — ${fmtEtb(price)}` : 'Confirm selected shop';
    btn.innerHTML = `${label} ${icon('arrow-right', 'w-4 h-4')}`;
  }
}

// ── Accept bid (customer) ─────────────────────────────────────────────────
async function acceptBid() {
  const body = { order_id: ORDER_ID };
  if (_selectedBidId) body.bid_id = _selectedBidId;
  const res  = await fetch(APP_URL + '/api/v1/orders/select-bid.php', {
    method:'POST', headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
    body: JSON.stringify(body),
  });
  const data = await res.json();
  if (data.status === 'success') {
    paymentSettings = data.data.payment || {};
    showToast('Vendor confirmed!', 'Please complete payment.', 'success');
    loadOrder();
  } else { showToast(data.message || 'Error', '', 'error'); }
}

// ── Payment proof upload ──────────────────────────────────────────────────
async function loadPaymentSettings() {
  const res  = await fetch(APP_URL + '/api/v1/settings/index.php');
  const data = await res.json();
  if (data.status === 'success') paymentSettings = data.data || {};
}

function openPaymentModal(total) {
  document.getElementById('pay-amount-label').textContent = `Amount to pay: ETB ${parseFloat(total||0).toLocaleString('en-ET',{minimumFractionDigits:2})}`;
  document.getElementById('pay-txn-id').value = '';
  document.getElementById('pay-sender').value = '';
  document.getElementById('pay-method').value = '';
  document.getElementById('proof-preview').classList.add('hidden');
  document.getElementById('scan-status').classList.add('hidden');
  document.getElementById('pay-proof-file').value = '';
  document.getElementById('file-label').textContent = 'Choose screenshot';
  document.querySelectorAll('.pay-method-btn').forEach(b => b.classList.remove('is-active'));

  // Show payment instructions
  const p = paymentSettings;
  const row = (k, v, mono) => `<div class="flex justify-between gap-3 text-[13px]"><span class="text-amber-800/80 dark:text-amber-300/80">${k}</span><strong class="text-right ${mono ? 'font-mono num' : ''}">${escHtml(v)}</strong></div>`;
  document.getElementById('payment-instructions').innerHTML = `
    <p class="font-bold text-amber-900 dark:text-amber-200 flex items-center gap-2">${icon('info', 'w-4 h-4')}Payment details</p>
    ${p.payment_bank_name ? row('Bank', p.payment_bank_name) : ''}
    ${p.payment_bank_account ? row('Account no.', p.payment_bank_account, true) : ''}
    ${p.payment_account_name ? row('Account name', p.payment_account_name) : ''}
    ${p.payment_telebirr ? `<div class="pt-2 border-t border-amber-200 dark:border-amber-500/20">${row('Telebirr', p.payment_telebirr, true)}</div>` : ''}`;

  document.getElementById('payment-modal').classList.remove('hidden');
}

function closePaymentModal() { document.getElementById('payment-modal').classList.add('hidden'); }

function selectPayMethod(method) {
  document.getElementById('pay-method').value = method;
  document.querySelectorAll('.pay-method-btn').forEach(b => {
    b.classList.toggle('is-active', b.dataset.method === method);
  });
}

function onFileSelect(input) {
  const file = input.files[0];
  if (!file) return;
  document.getElementById('file-label').textContent = file.name;

  // Show image preview
  const reader = new FileReader();
  reader.onload = e => {
    const img = document.getElementById('proof-preview');
    img.src = e.target.result;
    img.classList.remove('hidden');
  };
  reader.readAsDataURL(file);

  // Auto-scan receipt
  scanReceipt(file);
}

// ── QR decode helpers ─────────────────────────────────────────────────────

/** Draw image file onto a canvas and return its ImageData */
function fileToImageData(file) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    const url = URL.createObjectURL(file);
    img.onload = () => {
      // Scale down very large screenshots — QR finder doesn't need 4K
      const MAX = 1600;
      let w = img.width, h = img.height;
      if (w > MAX) { h = Math.round(h * MAX / w); w = MAX; }
      const canvas = document.createElement('canvas');
      canvas.width = w; canvas.height = h;
      canvas.getContext('2d').drawImage(img, 0, 0, w, h);
      URL.revokeObjectURL(url);
      resolve(canvas.getContext('2d').getImageData(0, 0, w, h));
    };
    img.onerror = () => { URL.revokeObjectURL(url); reject(); };
    img.src = url;
  });
}

/** Parse the QR text payload and extract transaction fields */
function parseQRText(text) {
  const result = { transaction_id: null, sender_name: null, payment_method: null };

  if (/telebirr|ethio\s*telecom/i.test(text))       result.payment_method = 'telebirr';
  else if (/commercial\s*bank|CBE\b/i.test(text))   result.payment_method = 'bank_transfer';

  const txnPatterns = [
    /transaction\s*(?:id|no|number|ref)[:\s#.]*([A-Z0-9]{6,30})/i,
    /\b(FT[A-Z0-9]{8,18})\b/,
    /ref(?:erence)?\s*(?:no|#|id)?[:\s.]*([A-Z0-9]{6,30})/i,
    /(?:txn|trx|trans(?:action)?)\s*(?:no|id|#)?[:\s.]*([A-Z0-9]{6,30})/i,
    /\b([A-Z]{2,4}[0-9]{6,14})\b/,
    /\b(\d{12,20})\b/,
  ];
  for (const p of txnPatterns) {
    const m = text.match(p);
    if (m && !/^0+$/.test(m[1])) { result.transaction_id = m[1].toUpperCase(); break; }
  }

  const namePatterns = [
    /debited\s+from\s+([A-Z][A-Z\s]{2,50})/i,
    /payer\s*name[:\s]+([A-Za-z][a-zA-Z\s]{2,50})/i,
    /(?:sender|account\s*holder|account\s*name)[:\s]+([A-Za-z][a-zA-Z\s]{2,50})/i,
  ];
  for (const p of namePatterns) {
    const m = text.match(p);
    if (m) {
      const name = m[1].trim().replace(/\s+/g, ' ');
      if (name.length <= 60 && !/\d/.test(name) && name.split(' ').length <= 6) {
        result.sender_name = name.replace(/\w\S*/g, w => w[0].toUpperCase() + w.slice(1).toLowerCase());
        break;
      }
    }
  }
  return result;
}

async function scanReceipt(file) {
  const statusEl = document.getElementById('scan-status');

  const show = (cls, ic, msg) => {
    statusEl.className = `mt-3 items-center gap-2 text-xs rounded-xl px-3 py-2.5 ${cls}`;
    statusEl.innerHTML = `${icon(ic, 'w-4 h-4 flex-shrink-0')}<span>${msg}</span>`;
    statusEl.classList.remove('hidden');
  };

  show('bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300', 'scan', 'Scanning QR code…');

  // ── 1. Try in-browser QR decode (instant, no server) ──────────────────
  let extracted = null;
  try {
    if (typeof jsQR !== 'undefined') {
      const imageData = await fileToImageData(file);
      const code = jsQR(imageData.data, imageData.width, imageData.height, {
        inversionAttempts: 'attemptBoth', // handles dark/light QR codes
      });
      if (code && code.data) {
        extracted = parseQRText(code.data);
        extracted._source = 'QR code';
      }
    }
  } catch { /* fall through */ }

  // ── 2. PHP fallback (needs vendor/ on server) ──────────────────────────
  if (!extracted || (!extracted.transaction_id && !extracted.sender_name)) {
    try {
      const fd = new FormData();
      fd.append('proof', file);
      const res  = await fetch(APP_URL + '/api/v1/orders/parse-receipt.php', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + TOKEN },
        body: fd,
      });
      const data = await res.json();
      if (data.status === 'success' && data.data.detected) {
        extracted = { ...data.data, _source: 'QR code (server)' };
      }
    } catch { /* fall through */ }
  }

  // ── 3. Apply results ───────────────────────────────────────────────────
  if (!extracted || (!extracted.transaction_id && !extracted.sender_name)) {
    show('bg-yellow-50 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-400',
         'alert', 'No QR code detected — fill in the fields manually.');
    return;
  }

  const filled = [];
  if (extracted.transaction_id) {
    document.getElementById('pay-txn-id').value = extracted.transaction_id;
    filled.push('Transaction ID');
  }
  if (extracted.sender_name) {
    document.getElementById('pay-sender').value = extracted.sender_name;
    filled.push('Sender Name');
  }
  if (extracted.payment_method) {
    selectPayMethod(extracted.payment_method);
  }

  show('bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
       'check-circle', `Auto-filled from ${extracted._source}: ${filled.join(', ')}. Please verify.`);
}

async function submitPayment() {
  const method = document.getElementById('pay-method').value;
  const txnId  = document.getElementById('pay-txn-id').value.trim();
  const sender = document.getElementById('pay-sender').value.trim();
  const file   = document.getElementById('pay-proof-file').files[0];
  if (!file) { showToast('Please upload a payment screenshot', '', 'error'); return; }

  const btn = document.getElementById('pay-submit-btn');
  btn.disabled = true; btn.textContent = 'Uploading…';

  const fd = new FormData();
  fd.append('order_id', ORDER_ID);
  fd.append('payment_method', method);
  fd.append('txn_id', txnId);
  fd.append('sender_name', sender);
  fd.append('proof', file);

  try {
    const res  = await fetch(APP_URL + '/api/v1/orders/upload-payment.php', {
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + TOKEN },
      body: fd,
    });
    const data = await res.json();
    if (data.status === 'success') {
      showToast('Payment proof submitted!', 'Admin will verify shortly.', 'success');
      closePaymentModal();
      loadOrder();
    } else {
      showToast(data.message || 'Error', '', 'error');
    }
  } catch { showToast('Upload failed. Try again.', '', 'error'); }
  btn.disabled = false; btn.textContent = 'Submit proof';
}

// ── Confirm delivery ──────────────────────────────────────────────────────
async function confirmDelivery() {
  const res  = await fetch(APP_URL + '/api/v1/delivery/confirm.php', {
    method:'POST', headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
    body: JSON.stringify({ order_id: ORDER_ID }),
  });
  const data = await res.json();
  if (data.status === 'success') { showToast('Delivery confirmed!', 'Thank you for using Lakun.', 'success'); loadOrder(); }
  else showToast(data.message || 'Error', '', 'error');
}

// ── Cancel order ─────────────────────────────────────────────────────────
async function cancelOrder() {
  if (!confirm('Cancel this order?')) return;
  const res  = await fetch(APP_URL + '/api/v1/orders/cancel.php', {
    method:'POST', headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
    body: JSON.stringify({ order_id: ORDER_ID }),
  });
  const data = await res.json();
  if (data.status === 'success') { showToast('Order cancelled.', '', 'info'); loadOrder(); }
  else showToast(data.message || 'Error', '', 'error');
}

loadOrder();
loadPaymentSettings();
setInterval(loadOrder, 4000);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
