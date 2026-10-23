<?php
$pageTitle = 'My earnings';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-2xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Rider</p>
      <h1 class="page-title">My earnings</h1>
      <p class="page-sub">Your weekly deliveries, bonuses and totals.</p>
    </div>
    <a href="<?= $appUrl ?>/delivery/tasks" class="btn-outline"><?= icon('bike', 'w-4 h-4') ?>My deliveries</a>
  </div>

  <div id="earnings-content" class="space-y-4">
    <div class="grid grid-cols-2 gap-3">
      <div class="skeleton h-32 rounded-2xl"></div>
      <div class="skeleton h-32 rounded-2xl"></div>
    </div>
    <div class="skeleton h-32 rounded-2xl"></div>
    <div class="skeleton h-40 rounded-2xl"></div>
    <div class="skeleton h-48 rounded-2xl"></div>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');

function fmtEtb(v) { return LK.money(v, 2); }
function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

async function loadEarnings() {
  try {
    const res  = await fetch(APP_URL + '/api/v1/delivery/earnings.php', {
      headers: { 'Authorization': 'Bearer ' + TOKEN }
    });
    const data = await res.json();
    if (data.status !== 'success') throw new Error(data.message);
    render(data.data);
  } catch(e) { console.error(e); }
}

function render(d) {
  const w    = d.week;
  const at   = d.all_time;
  const bonus = d.bonus;

  // ── Bonus earned this week ──────────────────────────────────────────────
  let earnedHtml = '';
  if (bonus.earned_this_week && bonus.earned_this_week.length) {
    earnedHtml = `
      <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl p-4 md:p-5">
        <div class="flex items-center gap-2.5 mb-2">
          <span class="icon-tile w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">${icon('gift','w-4 h-4')}</span>
          <p class="font-bold text-emerald-800 dark:text-emerald-300">Bonuses earned this week</p>
        </div>
        ${bonus.earned_this_week.map(t => `
          <div class="flex justify-between items-center text-sm py-2 border-t border-emerald-200/60 dark:border-emerald-500/15 first:border-0">
            <span class="text-emerald-700 dark:text-emerald-300 font-semibold">${escHtml(t.label)} &middot; ${t.deliveries}+ deliveries</span>
            <span class="font-extrabold text-emerald-800 dark:text-emerald-200 num">${fmtEtb(t.bonus)}</span>
          </div>`).join('')}
      </div>`;
  }

  // ── Next bonus progress bar ─────────────────────────────────────────────
  let nextHtml = '';
  if (bonus.next) {
    const n = bonus.next;
    nextHtml = `
      <div class="card card-pad">
        <div class="flex items-center justify-between gap-3 mb-3">
          <div class="flex items-center gap-3">
            <span class="icon-tile bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">${icon('flame','w-5 h-5')}</span>
            <div>
              <h2 class="section-title">Next bonus target</h2>
              <p class="hint">Hit the target to unlock <strong class="text-amber-600 dark:text-amber-400 num">${fmtEtb(n.bonus)}</strong></p>
            </div>
          </div>
          <span class="badge ${LK.TONES.amber}">${escHtml(n.label)} tier</span>
        </div>
        <div class="flex items-baseline justify-between mb-2">
          <p class="text-sm text-charcoal-muted dark:text-gray-400"><strong class="text-xl font-extrabold text-charcoal dark:text-cream num">${n.progress}</strong> / ${n.deliveries} deliveries this week</p>
        </div>
        <div class="w-full bg-gray-100 dark:bg-white/[0.06] rounded-full h-3 overflow-hidden">
          <div class="bg-gradient-to-r from-amber-500 to-amber-400 h-3 rounded-full transition-all duration-700" style="width:${n.pct}%"></div>
        </div>
        <div class="flex justify-between mt-2 text-xs text-charcoal-muted dark:text-gray-400">
          <span>${n.progress} done</span>
          <span>${n.remaining} more needed</span>
        </div>
      </div>`;
  }

  // ── All bonus tiers reference ───────────────────────────────────────────
  const weekDeliveries = parseInt(w.deliveries) || 0;
  const tiersHtml = bonus.tiers.map(t => {
    const reached = weekDeliveries >= t.deliveries;
    return `
      <div class="flex items-center gap-3 py-3 border-t border-gray-100 dark:border-white/[0.05] first:border-0">
        <span class="icon-tile w-9 h-9 rounded-full ${reached ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-gray-100 text-gray-400 dark:bg-white/[0.06] dark:text-gray-500'}">${icon(reached ? 'check' : 'star','w-4 h-4')}</span>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-charcoal dark:text-cream">${escHtml(t.label)}</p>
          <p class="text-xs text-charcoal-muted dark:text-gray-400">${t.deliveries} deliveries per week</p>
        </div>
        <span class="text-sm font-extrabold num ${reached ? 'text-emerald-600 dark:text-emerald-400' : 'text-charcoal dark:text-cream'}">${fmtEtb(t.bonus)}</span>
      </div>`;
  }).join('');

  // ── Weekly deliveries list ──────────────────────────────────────────────
  let ordersHtml = '';
  if (w.orders && w.orders.length) {
    ordersHtml = `
      <div class="card overflow-hidden">
        <div class="px-5 md:px-6 py-4 border-b border-gray-100 dark:border-white/[0.06]">
          <h2 class="section-title">This week's deliveries</h2>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-white/[0.05]">
          ${w.orders.map(o => `
          <div class="px-5 md:px-6 py-3.5 flex items-center gap-3">
            <span class="icon-tile w-9 h-9 rounded-lg ${LK.status(o.status).tile}">${icon(LK.status(o.status).icon,'w-4 h-4')}</span>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-charcoal dark:text-cream truncate">${escHtml(o.item_name)}</p>
              <p class="text-xs text-charcoal-muted dark:text-gray-400 truncate">${escHtml(o.building_name)} &middot; ${escHtml(LK.status(o.status).label)}</p>
            </div>
            <span class="text-sm font-extrabold text-amber-600 dark:text-amber-400 flex-shrink-0 num">${fmtEtb(o.delivery_fee)}</span>
          </div>`).join('')}
        </div>
      </div>`;
  }

  document.getElementById('earnings-content').innerHTML = `
    <!-- Weekly stat cards -->
    <div class="grid grid-cols-2 gap-3">
      <div class="relative overflow-hidden rounded-2xl p-5 hero-gradient text-white shadow-md">
        <div class="absolute inset-0 bg-grid opacity-20" aria-hidden="true"></div>
        <div class="relative">
          <div class="flex items-center justify-between">
            <p class="text-[13px] font-semibold text-white/85">This week</p>
            <span class="icon-tile w-9 h-9 bg-white/15 text-white">${icon('bike','w-[18px] h-[18px]')}</span>
          </div>
          <p class="text-4xl font-extrabold tracking-tight leading-none mt-3 num">${weekDeliveries}</p>
          <p class="text-xs text-white/75 mt-1.5">deliveries</p>
        </div>
      </div>
      <div class="stat">
        <div class="flex items-center justify-between">
          <p class="stat-label">Week earnings</p>
          <span class="icon-tile w-9 h-9 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">${icon('wallet','w-[18px] h-[18px]')}</span>
        </div>
        <p class="text-[1.4rem] font-extrabold tracking-tight leading-none mt-3 text-charcoal dark:text-cream num">${fmtEtb(w.delivery_earnings)}</p>
        <p class="text-xs text-charcoal-muted dark:text-gray-500 mt-1.5">${escHtml(w.start)} → ${escHtml(w.end)}</p>
      </div>
    </div>

    <!-- All-time stats -->
    <div class="card card-pad">
      <div class="flex items-center gap-3 mb-4">
        <span class="icon-tile bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">${icon('trending','w-5 h-5')}</span>
        <h2 class="section-title">All-time</h2>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div class="surface-muted p-4">
          <p class="text-2xl font-extrabold text-charcoal dark:text-cream num">${parseInt(at.total_deliveries)||0}</p>
          <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-1">Total deliveries</p>
        </div>
        <div class="surface-muted p-4">
          <p class="text-xl font-extrabold text-amber-600 dark:text-amber-400 num">${fmtEtb(at.total_delivery_earnings)}</p>
          <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-1">Total earned</p>
        </div>
      </div>
    </div>

    ${earnedHtml}
    ${nextHtml}

    <!-- Bonus tiers reference -->
    <div class="card card-pad">
      <div class="flex items-center gap-3 mb-2">
        <span class="icon-tile bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">${icon('gift','w-5 h-5')}</span>
        <div>
          <h2 class="section-title">Weekly bonus tiers</h2>
          <p class="hint">Bonuses reset every Monday</p>
        </div>
      </div>
      ${tiersHtml}
    </div>

    ${ordersHtml}
  `;
}

loadEarnings();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
