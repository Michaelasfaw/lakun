<?php
$pageTitle = 'Overview';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-7xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Operations</p>
      <h1 class="page-title">Platform overview</h1>
      <p class="page-sub flex items-center gap-2">
        <span class="relative flex w-2 h-2"><span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping-slow"></span><span class="relative inline-flex rounded-full w-2 h-2 bg-emerald-500"></span></span>
        Live · <span id="last-updated">Loading…</span>
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="<?= $appUrl ?>/admin/financial-report" class="btn-outline"><?= icon('chart', 'w-4 h-4') ?>Reports</a>
      <button onclick="loadDashboard()" class="btn-dark"><?= icon('refresh', 'w-4 h-4') ?>Refresh</button>
    </div>
  </div>

  <!-- KPI cards -->
  <div id="stats-grid" class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6">
    <?php for ($i = 0; $i < 8; $i++): ?>
      <div class="skeleton h-[118px] rounded-2xl"></div>
    <?php endfor; ?>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-5 mb-5">
    <!-- Payment verification -->
    <section class="card card-pad xl:col-span-2">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <span class="icon-tile bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-300"><?= icon('shield', 'w-5 h-5') ?></span>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="section-title">Payment verification</h2>
              <span id="payment-badge" class="hidden badge bg-red-500 text-white"></span>
            </div>
            <p class="hint">Customers who uploaded payment proof</p>
          </div>
        </div>
        <a href="<?= $appUrl ?>/admin/orders?filter=payment_under_review" class="link">View all <?= icon('arrow-right', 'w-3.5 h-3.5') ?></a>
      </div>
      <div id="payment-verify-list" class="space-y-2">
        <div class="skeleton h-16 rounded-xl"></div>
        <div class="skeleton h-16 rounded-xl"></div>
      </div>
    </section>

    <!-- Needs agent -->
    <section class="card card-pad">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <span class="icon-tile bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-300"><?= icon('bike', 'w-5 h-5') ?></span>
          <div>
            <h2 class="section-title">Needs a rider</h2>
            <p class="hint">Confirmed, not yet assigned</p>
          </div>
        </div>
        <a href="<?= $appUrl ?>/admin/orders?filter=confirmed" class="link">All</a>
      </div>
      <div id="pending-assignment" class="space-y-2">
        <div class="skeleton h-14 rounded-xl"></div>
        <div class="skeleton h-14 rounded-xl"></div>
      </div>
    </section>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-5 mb-5">
    <!-- Recent orders -->
    <section class="card xl:col-span-2 overflow-hidden">
      <div class="flex items-center justify-between px-5 md:px-6 pt-5 md:pt-6 pb-3">
        <div>
          <h2 class="section-title">Recent orders</h2>
          <p class="hint">Latest activity across all buildings</p>
        </div>
        <a href="<?= $appUrl ?>/admin/orders" class="link">Open orders board <?= icon('arrow-right', 'w-3.5 h-3.5') ?></a>
      </div>
      <div id="recent-orders" class="px-3 md:px-4 pb-3">
        <div class="skeleton h-12 rounded-xl m-2"></div>
        <div class="skeleton h-12 rounded-xl m-2"></div>
        <div class="skeleton h-12 rounded-xl m-2"></div>
      </div>
    </section>

    <div class="space-y-4 md:space-y-5">
      <!-- Low credit agents -->
      <section class="card card-pad">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <span class="icon-tile bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300"><?= icon('alert', 'w-5 h-5') ?></span>
            <div>
              <h2 class="section-title">Low credits</h2>
              <p class="hint">Riders who may stop receiving jobs</p>
            </div>
          </div>
          <a href="<?= $appUrl ?>/admin/credit-packages" class="link">Manage</a>
        </div>
        <div id="low-credit-agents" class="space-y-2">
          <div class="skeleton h-12 rounded-xl"></div>
        </div>
      </section>

      <!-- Quick links -->
      <section class="card card-pad">
        <h2 class="section-title mb-3">Shortcuts</h2>
        <div class="grid grid-cols-2 gap-2">
          <?php
          $links = [
            ['/admin/orders',           'package',  'Orders',    'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300'],
            ['/admin/users',            'users',    'People',    'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300'],
            ['/admin/buildings',        'building', 'Buildings', 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300'],
            ['/admin/credit-packages',  'coins',    'Credits',   'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300'],
            ['/admin/financial-report', 'trending', 'Finance',   'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-300'],
            ['/admin/settings',         'settings', 'Settings',  'bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300'],
          ];
          foreach ($links as [$href, $ic, $title, $tile]): ?>
          <a href="<?= $appUrl . $href ?>" class="flex items-center gap-2.5 rounded-xl p-2.5 border border-gray-200/70 dark:border-white/[0.06] hover:bg-gray-50 dark:hover:bg-white/[0.03] hover:border-gray-300 dark:hover:border-white/10 transition-colors">
            <span class="icon-tile w-8 h-8 rounded-lg <?= $tile ?>"><?= icon($ic, 'w-4 h-4') ?></span>
            <span class="text-[13px] font-semibold"><?= $title ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </section>
    </div>
  </div>

  <!-- Financial summary -->
  <section class="relative overflow-hidden rounded-2xl ink-gradient text-white p-5 md:p-6 shadow-lg">
    <div class="absolute inset-0 bg-grid opacity-30 [mask-image:linear-gradient(to_left,black,transparent_60%)]" aria-hidden="true"></div>
    <div class="relative flex flex-wrap items-center justify-between gap-3 mb-5">
      <div class="flex items-center gap-3">
        <span class="icon-tile bg-white/10 text-amber-300"><?= icon('trending', 'w-5 h-5') ?></span>
        <div>
          <h2 class="text-base font-bold">This month's finances</h2>
          <p class="text-xs text-white/50" id="fin-period"></p>
        </div>
      </div>
      <a href="<?= $appUrl ?>/admin/financial-report" class="inline-flex items-center gap-1 text-sm font-semibold text-amber-300 hover:text-amber-200">Full report <?= icon('arrow-right', 'w-3.5 h-3.5') ?></a>
    </div>
    <div id="fin-summary" class="relative grid grid-cols-2 md:grid-cols-4 gap-3">
      <div class="h-20 rounded-xl bg-white/5"></div>
      <div class="h-20 rounded-xl bg-white/5"></div>
      <div class="h-20 rounded-xl bg-white/5"></div>
      <div class="h-20 rounded-xl bg-white/5"></div>
    </div>
  </section>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');

function timeAgo(ts) { return LK.timeAgo(ts); }
function escHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtEtb(v){ return 'ETB ' + parseFloat(v||0).toLocaleString('en-US', {minimumFractionDigits:0, maximumFractionDigits:0}); }

async function loadDashboard() {
  let data;
  try {
    const res = await fetch(APP_URL+'/api/v1/admin/stats.php', { headers:{'Authorization':'Bearer '+TOKEN} });
    data = await res.json();
  } catch { return; }

  const d  = data.data || {};
  const u  = d.users  || {};
  const o  = d.orders || {};

  document.getElementById('last-updated').textContent = 'updated ' + new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});

  // ── KPI cards ───────────────────────────────────────────────────────────
  const kpis = [
    { val: o.total||0,             label:'Total orders',      sub:'All time',               ic:'package',      tile:'bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300', href:'/admin/orders' },
    { val: o.pending||0,           label:'Collecting prices', sub:'Waiting on vendors',      ic:'hourglass',    tile:'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300', href:'/admin/orders?filter=pending' },
    { val: o.payment_pending||0,   label:'Payment review',    sub:'Proof uploaded',          ic:'shield',       tile:'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-300', href:'/admin/orders?filter=payment_under_review', alert: (o.payment_pending||0) > 0 },
    { val: o.active_deliveries||0, label:'On the road',       sub:'Active deliveries',       ic:'truck',        tile:'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300', href:'/admin/orders?filter=delivering' },
    { val: o.completed||0,         label:'Completed',         sub:'Delivered & confirmed',   ic:'check-circle', tile:'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300', href:'/admin/orders?filter=completed' },
    { val: u.vendors_active||0,    label:'Active vendors',    sub:'Accepting orders',        ic:'store',        tile:'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300', href:'/admin/users?role=vendor' },
    { val: u.agents_active||0,     label:'Active riders',     sub:'Ready to deliver',        ic:'bike',         tile:'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-300', href:'/admin/users?role=delivery_agent' },
    { val: fmtEtb(o.revenue_total),label:'Gross order value', sub:'Confirmed orders',        ic:'wallet',       tile:'bg-white/15 text-white', href:'/admin/financial-report', hero: true },
  ];

  document.getElementById('stats-grid').innerHTML = kpis.map((s, i) => s.hero ? `
    <a href="${APP_URL}${s.href}" class="group relative overflow-hidden rounded-2xl p-5 hero-gradient text-white shadow-md hover:shadow-glow hover:-translate-y-0.5 transition-all animate-card-appear" style="animation-delay:${i*30}ms">
      <div class="flex items-start justify-between">
        <p class="text-[13px] font-semibold text-white/85">${s.label}</p>
        <span class="icon-tile w-9 h-9 ${s.tile}">${icon(s.ic, 'w-[18px] h-[18px]')}</span>
      </div>
      <p class="text-[1.6rem] font-extrabold tracking-tight leading-none mt-2 num">${s.val}</p>
      <p class="text-xs text-white/75 mt-1.5">${s.sub}</p>
    </a>` : `
    <a href="${APP_URL}${s.href}" class="stat card-hover group animate-card-appear" style="animation-delay:${i*30}ms">
      <div class="flex items-start justify-between">
        <p class="stat-label">${s.label}</p>
        <span class="icon-tile w-9 h-9 ${s.tile}">${icon(s.ic, 'w-[18px] h-[18px]')}</span>
      </div>
      <p class="stat-value mt-2">${s.val}</p>
      <p class="text-xs text-charcoal-muted dark:text-gray-500 mt-1.5 flex items-center gap-1.5">
        ${s.alert ? '<span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>' : ''}${s.sub}
      </p>
    </a>`).join('');

  // ── Payment Verification Queue ──────────────────────────────────────────
  const pvList = d.pending_verification || [];
  const badge  = document.getElementById('payment-badge');
  if (pvList.length) { badge.textContent = pvList.length + ' pending'; badge.classList.remove('hidden'); }
  else badge.classList.add('hidden');

  document.getElementById('payment-verify-list').innerHTML = !pvList.length
    ? LK.empty('check-circle', 'All payments verified', 'New payment proofs will show up here.')
    : pvList.slice(0,4).map(p => `
      <div class="flex items-center gap-3 p-3 rounded-xl surface-muted">
        ${LK.avatar(p.customer_name, 'w-10 h-10 text-xs')}
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <p class="text-sm font-semibold truncate">${escHtml(p.customer_name)}</p>
            <span class="text-xs font-bold text-charcoal-muted dark:text-gray-500 num">#${p.id}</span>
          </div>
          <p class="text-xs text-charcoal-muted dark:text-gray-400 flex items-center gap-1.5 mt-0.5">
            ${icon(p.payment_method === 'telebirr' ? 'phone' : 'card', 'w-3.5 h-3.5')}
            <span class="capitalize">${p.payment_method ? p.payment_method.replace('_',' ') : '—'}</span>
            <span class="text-gray-300 dark:text-gray-600">·</span>
            <span class="font-mono">${escHtml(p.payment_txn_id||'—')}</span>
          </p>
        </div>
        <p class="text-sm font-extrabold num hidden sm:block">${fmtEtb(p.total_price)}</p>
        <a href="${APP_URL}/admin/orders?id=${p.id}" class="btn-brand btn-sm">Review</a>
      </div>`).join('');

  // ── Needs Agent Assignment ───────────────────────────────────────────────
  const confirmed = (d.recent_orders||[]).filter(o => o.status === 'confirmed' || o.status === 'vendor_paid');
  document.getElementById('pending-assignment').innerHTML = !confirmed.length
    ? '<p class="text-sm text-charcoal-muted dark:text-gray-400 py-3">Every confirmed order has a rider.</p>'
    : confirmed.slice(0,4).map(o => `
      <div class="flex items-center gap-3 p-3 rounded-xl surface-muted">
        <div class="flex-1 min-w-0">
          <p class="text-xs font-bold text-charcoal-muted dark:text-gray-500 num">#${o.id}${o.is_fast_order ? ' · <span class="text-orange-600 dark:text-orange-400">Express</span>' : ''}</p>
          <p class="text-sm font-semibold truncate">${escHtml(o.item_name)}</p>
        </div>
        <a href="${APP_URL}/admin/orders" class="btn-dark btn-sm">Assign</a>
      </div>`).join('');

  // ── Low Credit Agents ───────────────────────────────────────────────────
  const lcAgents = d.low_credit_agents || [];
  document.getElementById('low-credit-agents').innerHTML = !lcAgents.length
    ? '<p class="text-sm text-charcoal-muted dark:text-gray-400 py-2">All riders have enough credits.</p>'
    : lcAgents.map(a => `
      <div class="flex items-center gap-3">
        ${LK.avatar(a.name)}
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold truncate">${escHtml(a.name)}</p>
          <div class="mt-1 h-1.5 rounded-full bg-gray-100 dark:bg-white/10 overflow-hidden"><div class="h-full rounded-full bg-red-500" style="width:${Math.max(8, Math.min(100, a.credits * 20))}%"></div></div>
        </div>
        <span class="text-xs font-bold text-red-600 dark:text-red-400 num whitespace-nowrap">${a.credits} left</span>
      </div>`).join('');

  // ── Recent Orders ───────────────────────────────────────────────────────
  const recent = (d.recent_orders||[]).slice(0, 8);
  document.getElementById('recent-orders').innerHTML = !recent.length
    ? LK.empty('inbox', 'No orders yet', 'Orders will stream in here as customers place them.')
    : recent.map(o => {
      const st = LK.status(o.status);
      return `
      <div class="list-row">
        <span class="icon-tile w-9 h-9 ${st.tile}">${icon(st.icon, 'w-4 h-4')}</span>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold truncate">${escHtml(o.item_name)}</p>
          <p class="text-xs text-charcoal-muted dark:text-gray-500 num">#${o.id} · ${timeAgo(o.created_at)}${o.is_fast_order ? ' · <span class="font-semibold text-orange-600 dark:text-orange-400">Express</span>' : ''}</p>
        </div>
        <span class="hidden sm:inline-flex">${LK.statusBadge(o.status)}</span>
        <span class="w-24 text-right text-sm font-bold num">${o.total_price ? fmtEtb(o.total_price) : '<span class="text-gray-300 dark:text-gray-600">—</span>'}</span>
      </div>`; }).join('');
}

async function loadFinancialSummary() {
  try {
    const res  = await fetch(`${APP_URL}/api/v1/admin/financial-report.php`, { headers:{'Authorization':'Bearer '+TOKEN} });
    const resp = await res.json();
    if (resp.status !== 'success') return;
    const d = resp.data;
    const t = d.this_month;
    const n = v => parseFloat(v||0).toLocaleString('en-US',{minimumFractionDigits:0,maximumFractionDigits:0});
    document.getElementById('fin-period').textContent = new Date().toLocaleString('en',{month:'long',year:'numeric'});
    const tile = (label, value, accent, sub) => `
      <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
        <p class="text-xs font-semibold text-white/55">${label}</p>
        <p class="text-2xl font-extrabold mt-1 num ${accent}">${value}</p>
        <p class="text-[11px] text-white/40 mt-0.5">${sub}</p>
      </div>`;
    document.getElementById('fin-summary').innerHTML =
      tile('Orders', parseInt(t.order_count||0), 'text-white', 'Completed this month') +
      tile('Platform income', n(t.platform_income), 'text-amber-300', 'ETB · fees & commission') +
      tile(`Tax (${d.tax_rate_percent}%)`, n(t.tax_amount), 'text-red-300', 'ETB · set aside') +
      tile('Net earnings', n(t.net_earnings), 'text-emerald-300', 'ETB · after tax');
  } catch {}
}

loadDashboard();
loadFinancialSummary();
setInterval(loadDashboard, 8000);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
