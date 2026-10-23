<?php
$pageTitle = 'My deliveries';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-2xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Rider</p>
      <h1 class="page-title">My deliveries</h1>
      <p class="page-sub">Tasks assigned to you by the admin.</p>
    </div>
    <a href="<?= $appUrl ?>/delivery/earnings" class="btn-soft"><?= icon('wallet', 'w-4 h-4') ?>Earnings</a>
  </div>

  <div id="tasks-list" class="space-y-4">
    <div class="skeleton h-64 rounded-2xl"></div>
    <div class="skeleton h-64 rounded-2xl"></div>
  </div>
  <div id="empty" class="hidden card">
    <div class="empty">
      <div class="empty-icon"><?= icon('bike', 'w-7 h-7') ?></div>
      <h3 class="text-base font-bold text-charcoal dark:text-cream">No tasks yet</h3>
      <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5 max-w-xs">When the admin assigns a delivery to you, it will show up here.</p>
    </div>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');

// Supports both legacy (picked_up/on_the_way) and new (out_for_delivery) status names
const STATUS_META = {
  assigned:         { next: 'picked_up',        nextLabel: 'Mark picked up',        btn: 'btn-brand' },
  picked_up:        { next: 'out_for_delivery', nextLabel: 'Mark out for delivery', btn: 'btn-brand' },
  out_for_delivery: { next: 'delivered',        nextLabel: 'Mark delivered',        btn: 'btn-success' },
  on_the_way:       { next: 'delivered',        nextLabel: 'Mark delivered',        btn: 'btn-success' },
  delivered:        { next: null, nextLabel: null },
  completed:        { next: null, nextLabel: null },
  cancelled:        { next: null, nextLabel: null },
};

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtEtb(v)  { return LK.money(v, 2); }

async function loadTasks() {
  try {
    const res  = await fetch(APP_URL + '/api/v1/orders/index.php', {
      headers: { 'Authorization': 'Bearer ' + TOKEN }
    });
    const data = await res.json();
    const tasks = data.data || [];
    const list  = document.getElementById('tasks-list');
    const empty = document.getElementById('empty');

    if (!tasks.length) { list.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');

    list.innerHTML = tasks.map((t, idx) => {
      const m         = STATUS_META[t.status] || { next: null };
      const st        = LK.status(t.status);
      const orderCode = t.order_code || ('LK' + t.id);

      // In new workflow: admin pays vendor BEFORE assigning agent.
      // When status = 'assigned', vendor has already been paid — agent can pick up immediately.
      let gateHtml = '';
      if (t.status === 'assigned') {
        gateHtml = `<div class="flex items-start gap-2 bg-teal-50 dark:bg-teal-500/10 border border-teal-200/70 dark:border-teal-500/20 rounded-xl px-3 py-2.5 mb-4 text-xs font-semibold text-teal-700 dark:text-teal-300">
          ${icon('check-circle','w-4 h-4 mt-px')}<span>Vendor has been paid by admin. Pick up immediately and show this screen as proof.</span>
        </div>`;
      }

      // Primary action button
      let actionBtn = '';
      if (t.status === 'assigned') {
        actionBtn = `<button onclick="updateStatus(${t.id}, 'out_for_delivery')" class="btn-brand flex-1">${icon('package','w-4 h-4')}Picked up — start delivery</button>`;
      } else if (m.next) {
        actionBtn = `<button onclick="updateStatus(${t.id}, '${m.next}')" class="${m.btn} flex-1">${icon(m.next === 'delivered' ? 'check-circle' : 'truck','w-4 h-4')}${m.nextLabel}</button>`;
      }
      const hasAction = !!(m.next || t.status === 'assigned');

      return `
      <div class="card overflow-hidden animate-card-appear" style="animation-delay:${idx*40}ms">
        <div class="h-1 ${st.bar}"></div>
        <div class="p-4 md:p-5">
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="min-w-0">
              <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400 tracking-wide">${escHtml(orderCode)}</span>
              <p class="font-bold text-charcoal dark:text-cream text-[15px] leading-snug break-words">${escHtml(t.item_name)} <span class="font-normal text-charcoal-muted dark:text-gray-400 text-sm">×${t.quantity}</span></p>
            </div>
            ${LK.statusBadge(t.status)}
          </div>

          ${gateHtml}

          <!-- Route -->
          <div class="relative pl-9 space-y-4 mb-4">
            <span class="absolute left-[13px] top-6 bottom-6 w-px border-l-2 border-dashed border-gray-300 dark:border-white/15" aria-hidden="true"></span>
            <div class="relative">
              <span class="icon-tile w-7 h-7 rounded-full absolute -left-9 top-0.5 bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">${icon('store','w-3.5 h-3.5')}</span>
              <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Pick up from</p>
              <p class="text-sm font-semibold text-charcoal dark:text-cream">${escHtml(t.vendor_name || t.shop_name || '—')}</p>
              ${t.shop_location ? `<p class="text-xs text-charcoal-muted dark:text-gray-400">${escHtml(t.shop_location)}</p>` : ''}
            </div>
            <div class="relative">
              <span class="icon-tile w-7 h-7 rounded-full absolute -left-9 top-0.5 bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">${icon('home','w-3.5 h-3.5')}</span>
              <p class="text-[11px] font-bold uppercase tracking-wider text-sky-700 dark:text-sky-400">Deliver to</p>
              <p class="text-sm font-semibold text-charcoal dark:text-cream">${escHtml(t.customer_name)} <span class="font-normal text-charcoal-muted dark:text-gray-400">&middot; ${escHtml(t.customer_phone)}</span></p>
              <p class="text-xs text-charcoal-muted dark:text-gray-400">Block ${escHtml(t.block)}, Floor ${escHtml(t.floor)}, House ${escHtml(t.house_no)}</p>
            </div>
          </div>

          <!-- COD amount -->
          <div class="surface-muted flex items-center justify-between px-4 py-3 mb-4">
            <span class="text-xs font-semibold text-charcoal-muted dark:text-gray-400 flex items-center gap-1.5">${icon('wallet','w-4 h-4')}Collect on delivery</span>
            <span class="text-lg font-extrabold text-charcoal dark:text-cream num">${fmtEtb(t.total_price||0)}</span>
          </div>

          <div class="flex gap-2">
            ${actionBtn}
            <a href="${APP_URL}/delivery/task/${t.id}" class="btn-outline ${hasAction ? '' : 'flex-1'}">Details${icon('arrow-right','w-4 h-4')}</a>
          </div>
        </div>
      </div>`;
    }).join('');
  } catch(e) { console.error(e); }
}

async function updateStatus(orderId, newStatus) {
  const res  = await fetch(APP_URL + '/api/v1/delivery/update.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
    body: JSON.stringify({ order_id: orderId, status: newStatus }),
  });
  const data = await res.json();
  if (data.status === 'success') {
    const labels = {
      picked_up:        'Picked up',
      out_for_delivery: 'Out for delivery',
      on_the_way:       'On the way',
      delivered:        'Delivered. COD collected.',
    };
    showToast(labels[newStatus] || 'Updated', '', 'success');
    loadTasks();
  } else {
    showToast(data.message || 'Error', '', 'error');
  }
}

loadTasks();
setInterval(loadTasks, 6000);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
