<?php
$pageTitle = 'Financial report';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-6xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Finance</p>
      <h1 class="page-title">Financial report</h1>
      <p class="page-sub">Platform income, tax and net earnings.</p>
    </div>
    <!-- Year / Month filters -->
    <div class="flex items-center gap-2">
      <select id="filter-year" onchange="loadReport()" class="input !w-auto !py-2.5 font-semibold">
        <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
        <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
      <select id="filter-month" onchange="loadReport()" class="input !w-auto !py-2.5 font-semibold">
        <?php for ($m = 1; $m <= 12; $m++): ?>
        <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>><?= date('M', mktime(0,0,0,$m,1)) ?></option>
        <?php endfor; ?>
      </select>
    </div>
  </div>

  <!-- Summary cards row -->
  <div id="summary-cards" class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6">
    <div class="skeleton h-28 rounded-2xl"></div>
    <div class="skeleton h-28 rounded-2xl"></div>
    <div class="skeleton h-28 rounded-2xl"></div>
    <div class="skeleton h-28 rounded-2xl"></div>
  </div>

  <!-- Tabs: Daily / Monthly / All-time -->
  <div class="segmented w-full sm:w-auto mb-5">
    <button onclick="switchTab('daily')"   id="tab-daily"   class="tab-btn is-active flex-1 sm:flex-none">Daily</button>
    <button onclick="switchTab('monthly')" id="tab-monthly" class="tab-btn flex-1 sm:flex-none">Monthly</button>
    <button onclick="switchTab('alltime')" id="tab-alltime" class="tab-btn flex-1 sm:flex-none">All-time</button>
  </div>

  <!-- Report table -->
  <div id="report-table" class="card overflow-hidden">
    <div class="skeleton rounded-2xl" style="min-height:240px"></div>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');
let _data     = null;
let _tab      = 'daily';

function fmtEtb(v) { return 'ETB ' + parseFloat(v||0).toLocaleString('en-ET',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function pct(a, b) { return b > 0 ? (a / b * 100).toFixed(1) + '%' : '—'; }

function switchTab(tab) {
  _tab = tab;
  ['daily','monthly','alltime'].forEach(t => {
    document.getElementById('tab-' + t).classList.toggle('is-active', t === tab);
  });
  if (_data) renderTable();
}

async function loadReport() {
  const year  = document.getElementById('filter-year').value;
  const month = document.getElementById('filter-month').value;
  const url   = `${APP_URL}/api/v1/admin/financial-report.php?year=${year}&month=${month}`;
  const res   = await fetch(url, { headers: { 'Authorization': 'Bearer ' + TOKEN } });
  const resp  = await res.json();
  if (resp.status !== 'success') return;
  _data = resp.data;
  renderSummary();
  renderTable();
}

function renderSummary() {
  const d = _data;
  const t = d.this_month;

  document.getElementById('summary-cards').innerHTML = `
    <div class="stat animate-card-appear">
      <div class="flex items-start justify-between">
        <p class="stat-label">Orders this month</p>
        <span class="icon-tile w-9 h-9 bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300">${icon('package','w-[18px] h-[18px]')}</span>
      </div>
      <p class="stat-value mt-2">${parseInt(t.order_count||0).toLocaleString()}</p>
      <p class="text-xs text-charcoal-muted dark:text-gray-500 mt-1.5 num">Gross ${fmtEtb(t.gross_income)}</p>
    </div>
    <div class="stat animate-card-appear" style="animation-delay:40ms">
      <div class="flex items-start justify-between">
        <p class="stat-label">Platform income</p>
        <span class="icon-tile w-9 h-9 bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">${icon('wallet','w-[18px] h-[18px]')}</span>
      </div>
      <p class="text-[1.4rem] font-extrabold tracking-tight leading-none mt-2 text-amber-600 dark:text-amber-400 num">${fmtEtb(t.platform_income)}</p>
      <p class="text-xs text-charcoal-muted dark:text-gray-500 mt-1.5">Delivery fees + tips</p>
    </div>
    <div class="stat animate-card-appear" style="animation-delay:80ms">
      <div class="flex items-start justify-between">
        <p class="stat-label">Tax (${d.tax_rate_percent}%)</p>
        <span class="icon-tile w-9 h-9 bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300">${icon('percent','w-[18px] h-[18px]')}</span>
      </div>
      <p class="text-[1.4rem] font-extrabold tracking-tight leading-none mt-2 text-red-500 dark:text-red-400 num">${fmtEtb(t.tax_amount)}</p>
      <p class="text-xs text-charcoal-muted dark:text-gray-500 mt-1.5">Estimated VAT liability</p>
    </div>
    <div class="relative overflow-hidden rounded-2xl p-5 hero-gradient text-white shadow-md animate-card-appear" style="animation-delay:120ms">
      <div class="flex items-start justify-between">
        <p class="text-[13px] font-semibold text-white/85">Net earnings</p>
        <span class="icon-tile w-9 h-9 bg-white/15 text-white">${icon('trending','w-[18px] h-[18px]')}</span>
      </div>
      <p class="text-[1.4rem] font-extrabold tracking-tight leading-none mt-2 num">${fmtEtb(t.net_earnings)}</p>
      <p class="text-xs text-white/75 mt-1.5">After ${d.tax_rate_percent}% tax</p>
    </div>`;
}

function renderTable() {
  const d = _data;
  const table = document.getElementById('report-table');

  if (_tab === 'alltime') {
    const at = d.all_time;
    table.innerHTML = `
      <div class="p-5 md:p-6">
        <div class="flex items-center gap-3 mb-4">
          <span class="icon-tile bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">${icon('chart','w-5 h-5')}</span>
          <h3 class="section-title">All-time totals</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
          ${statCard('Total orders', parseInt(at.total_orders||0).toLocaleString(), '')}
          ${statCard('Gross income', fmtEtb(at.gross_income), 'text-charcoal dark:text-cream')}
          ${statCard('Vendor payouts', fmtEtb(at.vendor_payouts), 'text-violet-600 dark:text-violet-400')}
          ${statCard('Delivery fees', fmtEtb(at.delivery_fees), 'text-amber-600 dark:text-amber-400')}
          ${statCard('Tips', fmtEtb(at.tips), 'text-amber-600 dark:text-amber-400')}
          ${statCard('Platform income', fmtEtb(at.platform_income), 'text-amber-600 dark:text-amber-400')}
          ${statCard(`Tax (${d.tax_rate_percent}%)`, fmtEtb(at.tax_amount), 'text-red-500 dark:text-red-400')}
          ${statCard('Net earnings', fmtEtb(at.net_earnings), 'text-emerald-600 dark:text-emerald-400')}
        </div>
      </div>`;
    return;
  }

  const rows    = _tab === 'daily' ? d.daily : d.monthly;
  const headers = _tab === 'daily'
    ? ['Date', 'Orders', 'Gross', 'Vendor out', 'Delivery fees', 'Tips', 'Platform', 'Tax', 'Net']
    : ['Month', 'Orders', 'Gross', 'Vendor out', 'Delivery fees', 'Tips', 'Platform', 'Tax', 'Net'];

  if (!rows || !rows.length) {
    table.innerHTML = LK.empty('chart', 'No data for this period', 'Try another month or year.');
    return;
  }

  table.innerHTML = `
    <div class="overflow-x-auto">
      <table class="table-clean">
        <thead>
          <tr>
            ${headers.map((h, i) => `<th class="whitespace-nowrap ${i > 0 ? 'text-right' : ''}">${h}</th>`).join('')}
          </tr>
        </thead>
        <tbody>
          ${rows.map(row => {
            const label = _tab === 'daily' ? row.day : row.month_label;
            return `<tr>
              <td class="font-semibold whitespace-nowrap">${label}</td>
              <td class="text-right num text-charcoal-muted dark:text-gray-400">${row.order_count}</td>
              <td class="text-right num whitespace-nowrap">${fmtEtb(row.gross_income)}</td>
              <td class="text-right num whitespace-nowrap text-violet-600 dark:text-violet-400">${fmtEtb(row.vendor_payouts)}</td>
              <td class="text-right num whitespace-nowrap text-amber-600 dark:text-amber-400">${fmtEtb(row.delivery_fees)}</td>
              <td class="text-right num whitespace-nowrap text-amber-600 dark:text-amber-400">${fmtEtb(row.tips)}</td>
              <td class="text-right num whitespace-nowrap font-semibold text-amber-700 dark:text-amber-300">${fmtEtb(row.platform_income)}</td>
              <td class="text-right num whitespace-nowrap text-red-500 dark:text-red-400">${fmtEtb(row.tax_amount)}</td>
              <td class="text-right num whitespace-nowrap font-bold text-emerald-600 dark:text-emerald-400">${fmtEtb(row.net_earnings)}</td>
            </tr>`;
          }).join('')}
        </tbody>
        <tfoot>
          <tr class="bg-gray-50 dark:bg-white/[0.03] border-t-2 border-gray-200 dark:border-white/10 font-bold text-xs">
            <td class="px-4 py-3.5 uppercase tracking-wider">Total</td>
            <td class="px-4 py-3.5 text-right num">${rows.reduce((s,r)=>s+parseInt(r.order_count||0),0)}</td>
            <td class="px-4 py-3.5 text-right num whitespace-nowrap">${fmtEtb(rows.reduce((s,r)=>s+parseFloat(r.gross_income||0),0))}</td>
            <td class="px-4 py-3.5 text-right num whitespace-nowrap text-violet-600 dark:text-violet-400">${fmtEtb(rows.reduce((s,r)=>s+parseFloat(r.vendor_payouts||0),0))}</td>
            <td class="px-4 py-3.5 text-right num whitespace-nowrap text-amber-600 dark:text-amber-400">${fmtEtb(rows.reduce((s,r)=>s+parseFloat(r.delivery_fees||0),0))}</td>
            <td class="px-4 py-3.5 text-right num whitespace-nowrap text-amber-600 dark:text-amber-400">${fmtEtb(rows.reduce((s,r)=>s+parseFloat(r.tips||0),0))}</td>
            <td class="px-4 py-3.5 text-right num whitespace-nowrap text-amber-700 dark:text-amber-300">${fmtEtb(rows.reduce((s,r)=>s+parseFloat(r.platform_income||0),0))}</td>
            <td class="px-4 py-3.5 text-right num whitespace-nowrap text-red-500 dark:text-red-400">${fmtEtb(rows.reduce((s,r)=>s+parseFloat(r.tax_amount||0),0))}</td>
            <td class="px-4 py-3.5 text-right num whitespace-nowrap text-emerald-600 dark:text-emerald-400">${fmtEtb(rows.reduce((s,r)=>s+parseFloat(r.net_earnings||0),0))}</td>
          </tr>
        </tfoot>
      </table>
    </div>`;
}

function statCard(label, value, cls) {
  return `<div class="surface-muted p-4">
    <p class="text-xs text-charcoal-muted dark:text-gray-400 mb-1">${label}</p>
    <p class="text-lg font-extrabold num ${cls || 'text-charcoal dark:text-cream'}">${value}</p>
  </div>`;
}

loadReport();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
