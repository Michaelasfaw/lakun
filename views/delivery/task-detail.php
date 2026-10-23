<?php
$pageTitle = 'Task detail';
$appUrl    = APP_URL;
$taskId    = (int)($orderId ?? 0);
if (!$taskId) { header('Location: ' . APP_URL . '/delivery/tasks'); exit; }
ob_start();
?>
<div class="max-w-xl mx-auto">

  <!-- Back link -->
  <a href="<?= $appUrl ?>/delivery/tasks" class="link mb-5 !text-charcoal-muted dark:!text-gray-400 hover:!text-amber-600">
    <?= icon('chevron-left', 'w-4 h-4') ?>Back to deliveries
  </a>

  <!-- Skeleton -->
  <div id="skeleton" class="space-y-4">
    <div class="skeleton h-24 rounded-2xl"></div>
    <div class="skeleton h-28 rounded-2xl"></div>
    <div class="skeleton h-28 rounded-2xl"></div>
    <div class="skeleton h-16 rounded-2xl"></div>
    <div class="skeleton h-14 rounded-2xl"></div>
  </div>

  <!-- Content -->
  <div id="content" class="hidden space-y-4"></div>

  <!-- Error state -->
  <div id="error" class="hidden card">
    <div class="empty">
      <div class="empty-icon"><?= icon('alert', 'w-7 h-7') ?></div>
      <h3 class="text-base font-bold text-charcoal dark:text-cream">Task not found</h3>
      <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5 max-w-xs">This task may have been reassigned or cancelled.</p>
      <a href="<?= $appUrl ?>/delivery/tasks" class="btn-brand mt-5">Back to tasks</a>
    </div>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');
const TASK_ID = <?= $taskId ?>;

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
function fmtDate(d) { return d ? new Date(d).toLocaleString() : '—'; }

async function loadTask() {
  try {
    const res  = await fetch(APP_URL + '/api/v1/orders/show.php?id=' + TASK_ID, {
      headers: { 'Authorization': 'Bearer ' + TOKEN }
    });
    const data = await res.json();
    if (data.status !== 'success' || !data.data) {
      document.getElementById('skeleton').classList.add('hidden');
      document.getElementById('error').classList.remove('hidden');
      return;
    }
    render(data.data);
  } catch(e) {
    document.getElementById('skeleton').classList.add('hidden');
    document.getElementById('error').classList.remove('hidden');
  }
}

function sectionHead(ic, tile, title) {
  return `<div class="flex items-center gap-2.5 mb-3">
    <span class="icon-tile w-8 h-8 rounded-lg ${tile}">${icon(ic,'w-4 h-4')}</span>
    <h2 class="text-[11px] font-bold uppercase tracking-wider text-charcoal-muted dark:text-gray-400">${title}</h2>
  </div>`;
}

function render(o) {
  const m  = STATUS_META[o.status] || { next: null };
  const st = LK.status(o.status);

  // Parse items_json for multi-item display
  let itemsHtml = '';
  try {
    const items = JSON.parse(o.items_json || '[]');
    if (items.length) {
      itemsHtml = items.map(it => `
        <div class="flex justify-between items-center py-2.5 border-b border-gray-100 dark:border-white/[0.05] last:border-0">
          <div class="min-w-0">
            <span class="text-sm font-medium text-charcoal dark:text-cream">${escHtml(it.name)}</span>
            ${it.brand ? `<span class="text-xs text-charcoal-muted dark:text-gray-400 ml-1">(${escHtml(it.brand)})</span>` : ''}
          </div>
          <span class="chip num font-bold">×${it.qty}</span>
        </div>`).join('');
    }
  } catch(e) {}
  if (!itemsHtml) {
    itemsHtml = `<div class="flex justify-between items-center py-1"><span class="text-sm font-medium text-charcoal dark:text-cream">${escHtml(o.item_name)}</span><span class="chip num font-bold">×${o.quantity}</span></div>`;
  }

  // Timeline steps
  const steps = [
    { key: 'assigned',         label: 'Assigned to you',     ts: o.assigned_at },
    { key: 'picked_up',        label: 'Picked up from shop', ts: null },
    { key: 'out_for_delivery', label: 'Out for delivery',    ts: null },
    { key: 'delivered',        label: 'Delivered',           ts: o.delivered_at },
    { key: 'completed',        label: 'Customer confirmed',  ts: o.completed_at },
  ];
  const statusOrder = ['assigned','picked_up','out_for_delivery','on_the_way','delivered','completed'];
  const currentIdx  = statusOrder.indexOf(o.status);

  const timelineHtml = steps.map((step, i) => {
    const stepIdx = statusOrder.indexOf(step.key);
    const done    = currentIdx >= stepIdx && currentIdx !== -1;
    const current = o.status === step.key || (step.key === 'out_for_delivery' && o.status === 'on_the_way');
    return `
    <div class="flex items-start gap-3">
      <div class="flex flex-col items-center">
        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                    ${done ? 'bg-amber-600 text-white' : 'bg-gray-100 dark:bg-white/[0.06] text-gray-400 dark:text-gray-500'}
                    ${current ? 'ring-2 ring-amber-400 ring-offset-2 ring-offset-white dark:ring-offset-charcoal-light' : ''}">
          ${done ? icon('check','w-3.5 h-3.5') : i + 1}
        </div>
        ${i < steps.length - 1 ? `<div class="w-0.5 h-6 mt-1 rounded-full ${done && currentIdx > stepIdx ? 'bg-amber-400' : 'bg-gray-100 dark:bg-white/[0.06]'}"></div>` : ''}
      </div>
      <div class="pb-4">
        <p class="text-sm font-semibold ${done ? 'text-charcoal dark:text-cream' : 'text-gray-400 dark:text-gray-500'}">${step.label}</p>
        ${step.ts && done ? `<p class="text-xs text-charcoal-muted dark:text-gray-400">${fmtDate(step.ts)}</p>` : ''}
      </div>
    </div>`;
  }).join('');

  const catIcons = { pharmacy:'pill', shop:'cart', liquor:'wine', food:'utensils', general:'package' };
  const catIcon  = catIcons[o.category] || 'package';

  document.getElementById('skeleton').classList.add('hidden');
  document.getElementById('content').classList.remove('hidden');
  document.getElementById('content').innerHTML = `

    <!-- Header -->
    <div class="card overflow-hidden">
      <div class="h-1 ${st.bar}"></div>
      <div class="p-4 md:p-5 flex items-start gap-3">
        <span class="icon-tile-lg ${st.tile}">${icon(catIcon,'w-6 h-6')}</span>
        <div class="flex-1 min-w-0">
          <p class="text-xs font-bold text-amber-600 dark:text-amber-400 mb-0.5 flex items-center gap-2">Order #${o.id} ${o.is_fast_order ? `<span class="badge ${LK.TONES.orange}">${icon('zap','w-3 h-3')}Fast</span>` : ''}</p>
          <p class="font-extrabold text-charcoal dark:text-cream text-lg leading-snug break-words">${escHtml(o.item_name)}</p>
          <div class="mt-2">${LK.statusBadge(o.status)}</div>
        </div>
      </div>
    </div>

    <!-- Items list -->
    <div class="card card-pad">
      ${sectionHead('list','bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300','Items to deliver')}
      ${itemsHtml}
      ${o.notes ? `<div class="mt-3 surface-muted px-3 py-2.5">
        <p class="text-[11px] font-bold uppercase tracking-wider text-charcoal-muted dark:text-gray-400 mb-1">Customer note</p>
        <p class="text-sm text-charcoal dark:text-cream italic">${escHtml(o.notes)}</p>
      </div>` : ''}
    </div>

    <!-- Pick up from -->
    <div class="card card-pad !border-amber-200/70 dark:!border-amber-500/20 !bg-amber-50/60 dark:!bg-amber-500/[0.04]">
      ${sectionHead('store','bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300','Pick up from')}
      <p class="font-bold text-charcoal dark:text-cream">${escHtml(o.vendor_name || o.shop_name || '—')}</p>
      ${o.shop_name && o.vendor_name && o.shop_name !== o.vendor_name
        ? `<p class="text-sm text-charcoal-muted dark:text-gray-400">${escHtml(o.shop_name)}</p>` : ''}
      ${o.shop_location ? `<p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5 flex items-start gap-1.5">${icon('map-pin','w-4 h-4 mt-px')}<span>${escHtml(o.shop_location)}</span></p>` : ''}
    </div>

    <!-- Deliver to -->
    <div class="card card-pad !border-sky-200/70 dark:!border-sky-500/20 !bg-sky-50/60 dark:!bg-sky-500/[0.04]">
      ${sectionHead('home','bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300','Deliver to')}
      <p class="font-bold text-charcoal dark:text-cream">${escHtml(o.customer_name)}</p>
      <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-0.5 flex items-center gap-1.5">${icon('phone','w-4 h-4')}${escHtml(o.customer_phone)}</p>
      <p class="text-sm text-charcoal dark:text-cream mt-2 font-medium">${escHtml(o.building_name)}</p>
      <p class="text-sm text-charcoal-muted dark:text-gray-400">Block ${escHtml(o.block)}, Floor ${escHtml(o.floor)}, House ${escHtml(o.house_no)}</p>
    </div>

    <!-- COD -->
    <div class="card ink-gradient !border-transparent text-white p-4 md:p-5 flex items-center justify-between">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-wider text-white/60">Collect on delivery</p>
        <p class="text-xs text-white/50 mt-0.5">Item + delivery fee</p>
      </div>
      <p class="text-2xl font-extrabold num">${fmtEtb(o.total_price||0)}</p>
    </div>

    <!-- Timeline -->
    <div class="card card-pad">
      ${sectionHead('navigation','bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300','Progress')}
      ${timelineHtml}
    </div>

    <!-- Action button -->
    ${m.next ? `
    <button id="action-btn" data-label="${escHtml(m.nextLabel)}" onclick="updateStatus('${m.next}')" class="${m.btn} btn-lg w-full">
      ${icon(m.next === 'delivered' ? 'check-circle' : 'truck','w-5 h-5')}${m.nextLabel}
    </button>` : (o.status === 'delivered' ? `
    <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl p-4 text-center">
      <p class="text-emerald-700 dark:text-emerald-300 font-bold flex items-center justify-center gap-2">${icon('check-circle','w-5 h-5')}Delivered &mdash; awaiting customer confirmation</p>
      <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">COD collected: ${fmtEtb(o.total_price||0)}</p>
    </div>` : (o.status === 'completed' ? `
    <div class="surface-muted p-4 text-center">
      <p class="text-charcoal dark:text-cream font-bold flex items-center justify-center gap-2">${icon('check-circle','w-5 h-5')}Order completed</p>
      <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-1">Earnings: ${fmtEtb(o.delivery_fee||0)}</p>
    </div>` : ''))}
  `;
}

async function updateStatus(newStatus) {
  const btn = document.getElementById('action-btn');
  if (btn) { btn.disabled = true; btn.textContent = 'Updating…'; }

  const res  = await fetch(APP_URL + '/api/v1/delivery/update.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
    body: JSON.stringify({ order_id: TASK_ID, status: newStatus }),
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
    loadTask(); // Reload to reflect new status
  } else {
    showToast(data.message || 'Error updating status', '', 'error');
    if (btn) { btn.disabled = false; btn.textContent = btn.dataset.label || 'Retry'; }
  }
}

loadTask();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
