<?php
$pageTitle = 'Notifications';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-2xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Inbox</p>
      <h1 class="page-title">Notifications</h1>
      <p class="page-sub" id="notif-summary">Loading…</p>
    </div>
    <button onclick="markAllRead()" class="btn-outline btn-sm"><?= icon('check', 'w-3.5 h-3.5') ?>Mark all read</button>
  </div>

  <div id="notif-list" class="space-y-2.5">
    <div class="skeleton h-[72px] rounded-2xl"></div>
    <div class="skeleton h-[72px] rounded-2xl"></div>
    <div class="skeleton h-[72px] rounded-2xl"></div>
    <div class="skeleton h-[72px] rounded-2xl"></div>
  </div>

  <div id="empty-state" class="hidden card">
    <div class="empty">
      <div class="empty-icon"><?= icon('bell', 'w-7 h-7') ?></div>
      <h3 class="text-base font-bold">You're all caught up</h3>
      <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5 max-w-xs">New updates about your orders will show up here.</p>
    </div>
  </div>
</div>

<!-- Notification Detail Modal -->
<div id="notif-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
  <div class="absolute inset-0 bg-ink/60 backdrop-blur-sm" onclick="closeModal()"></div>
  <div class="relative card !rounded-3xl shadow-2xl w-full max-w-md animate-card-appear">
    <div class="flex items-center gap-3 p-5 border-b border-gray-100 dark:border-white/[0.06]">
      <div id="modal-icon" class="icon-tile-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"></div>
      <div class="flex-1 min-w-0">
        <p id="modal-type-label" class="text-[11px] font-bold uppercase tracking-[0.12em] text-amber-600 dark:text-amber-400 mb-0.5"></p>
        <h2 id="modal-title" class="text-base font-bold leading-snug"></h2>
      </div>
      <button onclick="closeModal()" aria-label="Close" class="icon-btn flex-shrink-0 self-start"><?= icon('x', 'w-[18px] h-[18px]') ?></button>
    </div>
    <div class="p-5 space-y-4">
      <div id="modal-body-wrap" class="hidden">
        <p class="text-[11px] font-bold text-charcoal-muted dark:text-gray-500 uppercase tracking-wider mb-1.5">Details</p>
        <p id="modal-body" class="text-sm leading-relaxed"></p>
      </div>
      <div class="flex items-center gap-2 text-xs text-charcoal-muted dark:text-gray-500">
        <?= icon('clock', 'w-3.5 h-3.5') ?>
        <span id="modal-time"></span>
      </div>
      <div id="modal-order-wrap" class="hidden items-center gap-2 text-xs">
        <span class="text-charcoal-muted dark:text-gray-500">Order ID:</span>
        <span id="modal-order-id" class="chip num font-semibold"></span>
      </div>
    </div>
    <div class="px-5 pb-5">
      <button onclick="closeModal()" class="btn-dark w-full">Close</button>
    </div>
  </div>
</div>

<style>
#notif-modal:not(.hidden), #modal-order-wrap:not(.hidden) { display:flex; }
</style>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN = localStorage.getItem('td_token');

// type -> [icon, tile classes]
const T_AMBER  = 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300';
const T_EMER   = 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300';
const T_SKY    = 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300';
const T_RED    = 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300';
const T_ORANGE = 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-300';
const T_TEAL   = 'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-300';
const T_BLUE   = 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300';
const T_VIOLET = 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300';
const T_GRAY   = 'bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300';

const notifMeta = {
  new_order:['zap',T_AMBER], bid_received:['tag',T_BLUE], bid_selected:['check-circle',T_EMER], order_confirmed:['check-circle',T_EMER],
  order_assigned:['bike',T_TEAL], out_for_delivery:['truck',T_SKY], delivered:['check',T_EMER], no_bids:['hourglass',T_GRAY],
  payment_uploaded:['card',T_ORANGE], payment_verified:['shield',T_EMER], payment_rejected:['x-circle',T_RED],
  new_assignment:['bike',T_TEAL], picked_up:['package',T_SKY], on_the_way:['navigation',T_SKY],
  new_task:['zap',T_AMBER], accepted:['check-circle',T_EMER], runner_done:['check',T_EMER], confirmed:['check-circle',T_EMER],
  completed:['sparkles',T_VIOLET], cancelled:['x-circle',T_RED], new_message:['message',T_BLUE]
};
const metaFor = t => notifMeta[t] || ['bell', T_GRAY];

const notifTypeLabels = {
  new_order:'New Order', bid_received:'Bid Received', bid_selected:'Bid Selected',
  order_confirmed:'Order Confirmed', order_assigned:'Order Assigned',
  out_for_delivery:'Out for Delivery', delivered:'Delivered', no_bids:'No Bids',
  payment_uploaded:'Payment Uploaded', payment_verified:'Payment Verified',
  payment_rejected:'Payment Rejected', new_assignment:'New Assignment',
  picked_up:'Picked Up', on_the_way:'On the Way',
  new_task:'New Task', accepted:'Accepted', runner_done:'Runner Done',
  confirmed:'Confirmed', completed:'Completed', cancelled:'Cancelled', new_message:'New Message'
};

function timeAgo(ts) {
  const d = Math.floor((Date.now() - new Date(ts)) / 1000);
  if (d < 60) return 'just now';
  if (d < 3600) return Math.floor(d/60) + 'm ago';
  if (d < 86400) return Math.floor(d/3600) + 'h ago';
  return Math.floor(d/86400) + 'd ago';
}

function fullDateTime(ts) {
  return new Date(ts).toLocaleString(undefined, {
    year:'numeric', month:'short', day:'numeric',
    hour:'2-digit', minute:'2-digit'
  });
}

async function loadNotifications() {
  const res = await fetch(APP_URL + '/api/v1/notifications/index.php', { headers:{'Authorization':'Bearer '+TOKEN} });
  const data = await res.json();
  const notifs = data.data?.notifications || [];
  const unread = data.data?.unread_count || 0;

  document.getElementById('notif-summary').textContent =
    unread > 0 ? `${unread} unread notification${unread > 1 ? 's' : ''}` : 'All caught up';

  const list = document.getElementById('notif-list');
  const empty = document.getElementById('empty-state');

  if (notifs.length === 0) {
    list.innerHTML = '';
    empty.classList.remove('hidden');
    return;
  }
  empty.classList.add('hidden');

  list.innerHTML = notifs.map(n => {
    const [ic, tile] = metaFor(n.type);
    return `
    <div class="relative card overflow-hidden p-4 animate-card-appear cursor-pointer hover:shadow-md transition-shadow ${n.is_read ? '' : '!border-amber-300/70 dark:!border-amber-500/30 !bg-amber-50/50 dark:!bg-amber-500/[0.06]'}"
         onclick="openNotif(${n.id})">
      ${n.is_read ? '' : '<span class="absolute left-0 top-0 bottom-0 w-1 bg-amber-500"></span>'}
      <div class="flex items-start gap-3">
        <span class="icon-tile ${n.is_read ? T_GRAY : tile}">${icon(ic, 'w-5 h-5')}</span>
        <div class="flex-1 min-w-0">
          <p class="text-sm truncate ${n.is_read ? 'text-charcoal-muted dark:text-gray-400' : 'text-charcoal dark:text-cream font-semibold'}">${n.title}</p>
          ${n.body ? `<p class="text-xs text-charcoal-muted dark:text-gray-500 mt-0.5 truncate">${n.body}</p>` : ''}
        </div>
        <div class="flex flex-col items-end gap-1.5 flex-shrink-0 pl-1">
          <span class="text-[11px] text-charcoal-muted dark:text-gray-500 whitespace-nowrap">${timeAgo(n.created_at)}</span>
          ${!n.is_read ? '<span class="w-2 h-2 bg-amber-500 rounded-full"></span>' : ''}
        </div>
      </div>
    </div>`;
  }).join('');

  // store for modal lookup
  window._notifs = notifs;
}

function openNotif(id) {
  const n = (window._notifs || []).find(x => x.id == id);
  if (!n) return;

  const [ic, tile] = metaFor(n.type);
  const mi = document.getElementById('modal-icon');
  mi.className = 'icon-tile-lg ' + tile;
  mi.innerHTML = icon(ic, 'w-6 h-6');
  document.getElementById('modal-type-label').textContent = notifTypeLabels[n.type] || n.type;
  document.getElementById('modal-title').textContent = n.title;
  document.getElementById('modal-time').textContent = fullDateTime(n.created_at) + ' · ' + timeAgo(n.created_at);

  const bodyWrap = document.getElementById('modal-body-wrap');
  if (n.body) {
    document.getElementById('modal-body').textContent = n.body;
    bodyWrap.classList.remove('hidden');
  } else {
    bodyWrap.classList.add('hidden');
  }

  const orderWrap = document.getElementById('modal-order-wrap');
  if (n.order_id) {
    document.getElementById('modal-order-id').textContent = '#' + n.order_id;
    orderWrap.classList.remove('hidden');
  } else {
    orderWrap.classList.add('hidden');
  }

  document.getElementById('notif-modal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';

  if (!n.is_read) markRead(id);
}

function closeModal() {
  document.getElementById('notif-modal').classList.add('hidden');
  document.body.style.overflow = '';
}

async function markRead(id) {
  await fetch(APP_URL + '/api/v1/notifications/mark_read.php', {
    method: 'POST', headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json'},
    body: JSON.stringify({id}),
  });
  loadNotifications();
}

async function markAllRead() {
  await fetch(APP_URL + '/api/v1/notifications/mark_read.php', {
    method: 'POST', headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json'},
    body: JSON.stringify({}),
  });
  showToast('All marked as read', '', 'success');
  loadNotifications();
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

loadNotifications();
setInterval(loadNotifications, 10000);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
