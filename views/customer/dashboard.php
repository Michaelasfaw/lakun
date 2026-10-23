<?php
$pageTitle = 'Home';
$appUrl = APP_URL;
$firstName = htmlspecialchars(explode(' ', $user['name'])[0]);
$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
ob_start();
?>
<div class="max-w-6xl mx-auto">

  <!-- Greeting -->
  <div class="page-head">
    <div>
      <p class="page-eyebrow"><?= date('l, j F') ?></p>
      <h1 class="page-title"><?= $greeting ?>, <?= $firstName ?></h1>
      <p class="page-sub">Here's what's happening with your orders today.</p>
    </div>
    <a href="<?= $appUrl ?>/customer/order" class="btn-brand hidden sm:inline-flex"><?= icon('plus', 'w-4 h-4') ?>New order</a>
  </div>

  <!-- Hero CTA -->
  <section class="relative overflow-hidden rounded-3xl hero-gradient text-white p-6 md:p-8 shadow-lg mb-6">
    <div class="absolute inset-0 bg-grid opacity-[0.15] [mask-image:linear-gradient(to_left,black,transparent_70%)]" aria-hidden="true"></div>
    <div class="absolute -right-10 -bottom-16 w-64 h-64 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
    <div class="relative grid md:grid-cols-[1fr_auto] gap-6 items-center">
      <div>
        <p class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-[0.14em] text-amber-100/90"><?= icon('sparkles', 'w-3.5 h-3.5') ?> Shops compete for your order</p>
        <h2 class="mt-3 text-2xl md:text-[2rem] font-extrabold leading-tight tracking-tight max-w-lg text-balance">What do you need delivered today?</h2>
        <p class="mt-2 text-sm md:text-[15px] text-white/80 max-w-md">List your items, nearby shops send their best price within minutes, and a rider brings it to your door.</p>
        <div class="mt-5 flex flex-wrap gap-2">
          <?php foreach ([['cart', 'Groceries'], ['pill', 'Pharmacy'], ['utensils', 'Food'], ['package', 'Household']] as [$ic, $label]): ?>
          <a href="<?= $appUrl ?>/customer/order" class="inline-flex items-center gap-2 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 backdrop-blur px-3.5 py-2 text-sm font-semibold transition-colors">
            <?= icon($ic, 'w-4 h-4') ?><?= $label ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <a href="<?= $appUrl ?>/customer/order" class="group hidden md:flex flex-col items-center justify-center w-40 h-40 rounded-3xl bg-white text-amber-700 shadow-xl hover:-translate-y-1 transition-transform">
        <span class="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center"><?= icon('cart', 'w-7 h-7') ?></span>
        <span class="mt-3 text-sm font-extrabold">Start an order</span>
        <span class="text-xs text-amber-700/70 inline-flex items-center gap-1">Takes 30 sec <?= icon('arrow-right', 'w-3 h-3 group-hover:translate-x-0.5 transition-transform') ?></span>
      </a>
    </div>
  </section>

  <!-- Stats -->
  <div id="stats" class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-8">
    <?php for ($i = 0; $i < 4; $i++): ?><div class="skeleton h-[104px] rounded-2xl"></div><?php endfor; ?>
  </div>

  <div class="grid lg:grid-cols-[1fr_340px] gap-6">
    <!-- Active orders -->
    <section>
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
          <h2 class="section-title text-lg">Active orders</h2>
          <span id="active-count" class="hidden badge bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300"></span>
        </div>
        <a href="<?= $appUrl ?>/customer/orders" class="link">View all <?= icon('arrow-right', 'w-3.5 h-3.5') ?></a>
      </div>

      <div id="active-orders" class="space-y-3">
        <div class="skeleton h-36 rounded-2xl"></div>
        <div class="skeleton h-36 rounded-2xl"></div>
      </div>
      <div id="empty-state" class="hidden card">
        <div class="empty">
          <div class="empty-icon"><?= icon('inbox', 'w-7 h-7') ?></div>
          <h3 class="text-base font-bold">No active orders</h3>
          <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5 max-w-xs">Place an order and nearby shops will compete to give you the lowest price.</p>
          <a href="<?= $appUrl ?>/customer/order" class="btn-brand mt-5"><?= icon('plus', 'w-4 h-4') ?>Place an order</a>
        </div>
      </div>
    </section>

    <!-- Side column -->
    <aside class="space-y-4">
      <div class="card card-pad">
        <h3 class="section-title">How <?= APP_NAME ?> works</h3>
        <ol class="mt-4 space-y-4">
          <?php foreach ([
            ['list',   'List your items',       'Add what you need — groceries, medicine, meals.'],
            ['tag',    'Shops send prices',     'Nearby vendors bid within a few minutes.'],
            ['card',   'Pick & pay',            'Choose the best offer, pay via Telebirr or bank.'],
            ['truck',  'Delivered to your door','A rider brings it right up to your floor.'],
          ] as $i => [$ic, $t, $d]): ?>
          <li class="flex gap-3">
            <span class="relative icon-tile bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">
              <?= icon($ic, 'w-[18px] h-[18px]') ?>
              <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-charcoal dark:bg-cream text-white dark:text-charcoal text-[10px] font-bold flex items-center justify-center ring-2 ring-white dark:ring-charcoal-light"><?= $i + 1 ?></span>
            </span>
            <div>
              <p class="text-sm font-semibold"><?= $t ?></p>
              <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5"><?= $d ?></p>
            </div>
          </li>
          <?php endforeach; ?>
        </ol>
      </div>

      <div class="card card-pad">
        <div class="flex items-center justify-between">
          <h3 class="section-title">Recently delivered</h3>
          <a href="<?= $appUrl ?>/customer/orders" class="link text-xs">History</a>
        </div>
        <div id="recent-done" class="mt-3 space-y-1">
          <div class="skeleton h-12 rounded-xl"></div>
        </div>
      </div>

      <a href="<?= $appUrl ?>/customer/nearby" class="group card card-hover card-pad flex items-center gap-4">
        <span class="icon-tile-lg bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300"><?= icon('map-pin', 'w-6 h-6') ?></span>
        <div class="flex-1">
          <p class="text-sm font-bold">Shops near you</p>
          <p class="text-xs text-charcoal-muted dark:text-gray-400">See who delivers to your building</p>
        </div>
        <?= icon('chevron-right', 'w-5 h-5 text-gray-400 group-hover:translate-x-0.5 transition-transform') ?>
      </a>
    </aside>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');

// Customer-facing lifecycle (index into STEPS)
const STEPS = ['Requested', 'Prices', 'Payment', 'Confirmed', 'On the way', 'Delivered'];
const STEP_OF = {
  requested: 0, sent_to_vendors: 0, bidding: 0,
  bids_received: 1, sent_to_customer: 1, awaiting_confirmation: 1,
  awaiting_payment: 2, payment_under_review: 2, failed_payment: 2,
  confirmed: 3, vendor_paid: 3, assigned: 3,
  picked_up: 4, out_for_delivery: 4, on_the_way: 4,
  delivered: 5, completed: 5,
};
const NEEDS_ACTION = ['sent_to_customer', 'awaiting_confirmation', 'awaiting_payment', 'failed_payment', 'delivered'];
const DONE = ['completed', 'cancelled', 'expired'];

function ctaFor(o) {
  if (['sent_to_customer', 'awaiting_confirmation'].includes(o.status)) return ['Compare prices', true];
  if (['awaiting_payment', 'failed_payment'].includes(o.status)) return ['Pay now', true];
  if (o.status === 'delivered') return ['Confirm receipt', true];
  return ['Track order', false];
}

function orderCard(o, i) {
  const st = LK.status(o.status);
  const step = STEP_OF[o.status] ?? 0;
  const [cta, urgent] = ctaFor(o);
  const countdown = (['sent_to_vendors', 'bidding'].includes(o.status) && o.seconds_remaining > 0)
    ? `<span class="chip">${icon('clock', 'w-3.5 h-3.5')} ${Math.ceil(o.seconds_remaining / 60)} min left for bids</span>` : '';
  return `
  <a href="${APP_URL}/customer/track/${o.id}" class="task-card group block card p-4 md:p-5 animate-card-appear" style="animation-delay:${i * 50}ms">
    <div class="flex items-start gap-4">
      <span class="icon-tile-lg ${st.tile}">${icon(st.icon, 'w-6 h-6')}</span>
      <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-xs font-bold text-charcoal-muted dark:text-gray-500 num">${LK.esc(o.order_code || ('#' + o.id))}</span>
              ${LK.statusBadge(o.status)}
              ${+o.is_fast_order ? `<span class="badge bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/15 dark:bg-orange-500/10 dark:text-orange-300">${icon('flame', 'w-3 h-3')}Express</span>` : ''}
            </div>
            <p class="mt-1.5 text-[15px] font-bold text-charcoal dark:text-cream truncate">${LK.esc(o.item_name)}</p>
            <p class="mt-0.5 text-xs text-charcoal-muted dark:text-gray-400 flex items-center gap-1">${icon('map-pin', 'w-3.5 h-3.5')} ${LK.esc(o.building_name || '')} · Block ${LK.esc(o.block)}, Floor ${LK.esc(o.floor)}, ${LK.esc(o.house_no)}</p>
          </div>
          <div class="text-right flex-shrink-0">
            ${o.total_price ? `<p class="text-base font-extrabold num">${LK.money(o.total_price)}</p>` : `<p class="text-xs text-charcoal-muted dark:text-gray-500">Awaiting price</p>`}
            <p class="text-[11px] text-charcoal-muted dark:text-gray-500 mt-0.5">${LK.timeAgo(o.created_at)}</p>
          </div>
        </div>

        <!-- Progress -->
        <div class="mt-4">
          <div class="flex gap-1.5">
            ${STEPS.map((_, k) => `<span class="h-1.5 flex-1 rounded-full ${k < step ? 'bg-amber-500' : k === step ? 'bg-gradient-to-r from-amber-500 to-amber-300 animate-pulse' : 'bg-gray-200 dark:bg-white/10'}"></span>`).join('')}
          </div>
          <div class="mt-2 flex items-center justify-between gap-2">
            <p class="text-xs text-charcoal-muted dark:text-gray-400">Step ${step + 1} of ${STEPS.length} · <span class="font-semibold text-charcoal dark:text-cream">${STEPS[step]}</span></p>
            <div class="flex items-center gap-2">
              ${countdown}
              <span class="${urgent ? 'btn-brand btn-sm' : 'btn-soft btn-sm'}">${cta} ${icon('arrow-right', 'w-3.5 h-3.5')}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </a>`;
}

function statCard(label, value, sub, ic, tile) {
  return `<div class="stat">
    <div class="flex items-start justify-between">
      <p class="stat-label">${label}</p>
      <span class="icon-tile w-9 h-9 ${tile}">${icon(ic, 'w-[18px] h-[18px]')}</span>
    </div>
    <p class="stat-value mt-2">${value}</p>
    <p class="text-xs text-charcoal-muted dark:text-gray-500 mt-1">${sub}</p>
  </div>`;
}

async function loadOrders() {
  try {
    const res = await fetch(APP_URL + '/api/v1/orders/index.php', {
      headers: { 'Authorization': 'Bearer ' + TOKEN }
    });
    const data = await res.json();
    const all = data.data || [];
    const orders = all.filter(o => !DONE.includes(o.status));
    const done = all.filter(o => o.status === 'completed');
    const needAction = orders.filter(o => NEEDS_ACTION.includes(o.status)).length;
    const spent = done.reduce((s, o) => s + parseFloat(o.total_price || 0), 0);

    document.getElementById('stats').innerHTML =
      statCard('Active orders', orders.length, 'In progress right now', 'package', 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300') +
      statCard('Needs your action', needAction, needAction ? 'Prices or payment waiting' : 'You\'re all caught up', 'hand', needAction ? 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-300' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400') +
      statCard('Completed', done.length, 'Delivered to your door', 'check-circle', 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300') +
      statCard('Total spent', LK.money(spent), 'Across completed orders', 'wallet', 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300');

    const container = document.getElementById('active-orders');
    const empty = document.getElementById('empty-state');
    const badge = document.getElementById('active-count');
    badge.textContent = orders.length;
    badge.classList.toggle('hidden', !orders.length);

    if (!orders.length) {
      container.innerHTML = '';
      empty.classList.remove('hidden');
    } else {
      empty.classList.add('hidden');
      // Orders needing action first
      orders.sort((a, b) => NEEDS_ACTION.includes(b.status) - NEEDS_ACTION.includes(a.status));
      container.innerHTML = orders.map(orderCard).join('');
    }

    document.getElementById('recent-done').innerHTML = done.length
      ? done.slice(0, 3).map(o => `
        <a href="${APP_URL}/customer/track/${o.id}" class="list-row -mx-2">
          <span class="icon-tile w-9 h-9 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">${icon('check', 'w-4 h-4')}</span>
          <span class="flex-1 min-w-0">
            <span class="block text-sm font-semibold truncate">${LK.esc(o.item_name)}</span>
            <span class="block text-xs text-charcoal-muted dark:text-gray-500">${LK.timeAgo(o.completed_at || o.created_at)}</span>
          </span>
          <span class="text-sm font-bold num">${LK.money(o.total_price)}</span>
        </a>`).join('')
      : '<p class="text-sm text-charcoal-muted dark:text-gray-400 py-2">Your delivered orders will appear here.</p>';
  } catch (e) { console.error(e); }
}

loadOrders();
setInterval(loadOrders, 5000);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
