<?php
$pageTitle = 'My orders';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-3xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Orders</p>
      <h1 class="page-title">My orders</h1>
      <p class="page-sub">Everything you've requested, newest first.</p>
    </div>
    <a href="<?= $appUrl ?>/customer/order" class="btn-brand"><?= icon('plus', 'w-4 h-4') ?>New order</a>
  </div>

  <div id="orders-list" class="space-y-3">
    <div class="skeleton h-28 rounded-2xl"></div>
    <div class="skeleton h-28 rounded-2xl"></div>
    <div class="skeleton h-28 rounded-2xl"></div>
  </div>
  <div id="empty" class="hidden card">
    <div class="empty">
      <div class="empty-icon"><?= icon('inbox', 'w-7 h-7') ?></div>
      <h3 class="text-base font-bold">No orders yet</h3>
      <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5 max-w-xs">Place your first order and nearby shops will compete to give you the best price.</p>
      <a href="<?= $appUrl ?>/customer/order" class="btn-brand mt-5"><?= icon('plus', 'w-4 h-4') ?>Place first order</a>
    </div>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');

async function load() {
  try {
    const res = await fetch(APP_URL + '/api/v1/orders/index.php', {
      headers: { 'Authorization': 'Bearer ' + TOKEN }
    });
    const data = await res.json();
    const orders = data.data || [];
    const list = document.getElementById('orders-list');
    if (!orders.length) { list.innerHTML = ''; document.getElementById('empty').classList.remove('hidden'); return; }
    list.innerHTML = orders.map((o, i) => {
      const st = LK.status(o.status);
      return `<a href="${APP_URL}/customer/track/${o.id}" class="task-card group block card p-4 md:p-5 animate-card-appear" style="animation-delay:${Math.min(i, 8) * 40}ms">
        <div class="flex items-start gap-4">
          <span class="icon-tile-lg ${st.tile}">${icon(st.icon, 'w-6 h-6')}</span>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              ${o.order_code ? `<span class="text-xs font-bold text-charcoal-muted dark:text-gray-500 num">${LK.esc(o.order_code)}</span>` : ''}
              ${LK.statusBadge(o.status)}
            </div>
            <p class="mt-1.5 text-[15px] font-bold truncate">${LK.esc(o.item_name)} <span class="font-medium text-charcoal-muted dark:text-gray-500 text-xs">&times;${LK.esc(o.quantity)}</span></p>
            <p class="mt-0.5 text-xs text-charcoal-muted dark:text-gray-400 flex items-center gap-1">${icon('map-pin', 'w-3.5 h-3.5')} Block ${LK.esc(o.block)}, Floor ${LK.esc(o.floor)}, House ${LK.esc(o.house_no)}</p>
          </div>
          <div class="text-right flex-shrink-0">
            ${o.total_price ? `<p class="text-base font-extrabold num">${LK.money(o.total_price)}</p>` : '<p class="text-xs text-charcoal-muted dark:text-gray-500">Awaiting price</p>'}
            <p class="text-[11px] text-charcoal-muted dark:text-gray-500 mt-0.5">${new Date(o.created_at).toLocaleDateString()}</p>
            ${icon('chevron-right', 'w-4 h-4 text-gray-300 dark:text-gray-600 mt-2 ml-auto group-hover:translate-x-0.5 transition-transform')}
          </div>
        </div>
      </a>`;
    }).join('');
  } catch (e) { console.error(e); }
}

load();
setInterval(load, 8000);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
