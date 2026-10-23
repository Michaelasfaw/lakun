<?php
$pageTitle = 'Nearby shops';
$appUrl    = APP_URL;
ob_start();
?>
<div class="max-w-3xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Discover</p>
      <h1 class="page-title">Shops near you</h1>
      <p class="page-sub">Vendors available to fulfil orders in your area.</p>
    </div>
  </div>

  <!-- Location status bar -->
  <div id="location-bar" class="rounded-2xl p-4 mb-5 flex items-center gap-3 border bg-white dark:bg-charcoal-light border-gray-200/70 dark:border-white/[0.06]">
    <span id="loc-icon" class="icon-tile bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-300"><?= icon('navigation', 'w-5 h-5') ?></span>
    <div class="flex-1 min-w-0">
      <p class="text-sm font-semibold" id="loc-title">Detecting your location…</p>
      <p class="text-xs text-charcoal-muted dark:text-gray-400" id="loc-sub">Please allow location access</p>
    </div>
    <button onclick="detectAndLoad()" class="btn-outline btn-sm flex-shrink-0"><?= icon('refresh', 'w-3.5 h-3.5') ?>Refresh</button>
  </div>

  <!-- Building tabs (populated after location detected) -->
  <div id="building-tabs" class="hidden gap-2 overflow-x-auto pb-1 mb-5 scrollbar-none"></div>

  <!-- Vendor grid -->
  <div id="vendor-grid" class="grid sm:grid-cols-2 gap-3">
    <div class="skeleton h-32 rounded-2xl"></div>
    <div class="skeleton h-32 rounded-2xl"></div>
  </div>

  <div id="empty" class="hidden card">
    <div class="empty">
      <div class="empty-icon"><?= icon('store', 'w-7 h-7') ?></div>
      <h3 class="text-base font-bold">No shops found nearby</h3>
      <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5 max-w-xs">Try refreshing or check that your location is enabled.</p>
    </div>
  </div>
</div>

<style>
#building-tabs:not(.hidden) { display:flex; }
.tab-btn.active { background:#D97706; color:#fff; border-color:#D97706; box-shadow:0 4px 12px -4px rgba(217,119,6,.6); }
</style>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');

let nearbyBuildings  = [];
let selectedBuilding = null;

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// ── Location detection ────────────────────────────────────────────────────────
async function detectAndLoad() {
  setLocBar('navigation', 'Detecting your location…', 'Please allow location access', 'gray');

  if (!navigator.geolocation) {
    setLocBar('alert', 'Geolocation not available', 'Using your registered building instead.', 'amber');
    loadVendorsForBuilding(<?= (int)($user['building_id'] ?? 0) ?>);
    return;
  }

  navigator.geolocation.getCurrentPosition(
    async (pos) => {
      const { latitude, longitude, accuracy } = pos.coords;
      setLocBar('map-pin', 'Location detected', `Accuracy: ±${Math.round(accuracy)} m`, 'emerald');

      // Fetch nearby buildings
      try {
        const res  = await fetch(`${APP_URL}/api/v1/location/nearby-buildings.php?lat=${latitude}&lng=${longitude}&radius=5`);
        const data = await res.json();
        nearbyBuildings = data.data || [];

        if (!nearbyBuildings.length) {
          setLocBar('alert', 'No buildings found within 5 km', 'Using your registered building.', 'amber');
          loadVendorsForBuilding(<?= (int)($user['building_id'] ?? 0) ?>);
          return;
        }

        // Build building tabs
        renderBuildingTabs();

        // Auto-select nearest
        selectBuilding(nearbyBuildings[0]);
      } catch {
        setLocBar('x-circle', 'Could not load nearby buildings', 'Check your connection.', 'red');
        document.getElementById('vendor-grid').innerHTML = '';
        document.getElementById('empty').classList.remove('hidden');
      }
    },
    () => {
      // Permission denied — fall back to registered building
      setLocBar('lock', 'Location access denied', 'Showing vendors for your registered building.', 'amber');
      loadVendorsForBuilding(<?= (int)($user['building_id'] ?? 0) ?>);
    },
    { timeout: 8000, enableHighAccuracy: false }
  );
}

function setLocBar(ic, title, sub, color) {
  const tiles = {
    gray:    'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-300',
    emerald: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
    amber:   'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
    red:     'bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-300',
  };
  const iconEl = document.getElementById('loc-icon');
  iconEl.className = 'icon-tile ' + (tiles[color] || tiles.gray);
  iconEl.innerHTML = icon(ic, 'w-5 h-5');
  document.getElementById('loc-title').textContent = title;
  document.getElementById('loc-sub').textContent   = sub;
  const bar = document.getElementById('location-bar');
  bar.className = 'rounded-2xl p-4 mb-5 flex items-center gap-3 border';
  const map = {
    gray:   'bg-white dark:bg-charcoal-light border-gray-200/70 dark:border-white/[0.06]',
    emerald:'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20',
    amber:  'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/20',
    red:    'bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/20',
  };
  bar.className += ' ' + (map[color] || map.gray);
}

// ── Building tabs ─────────────────────────────────────────────────────────────
function renderBuildingTabs() {
  const tabsEl = document.getElementById('building-tabs');
  tabsEl.classList.remove('hidden');
  tabsEl.innerHTML = nearbyBuildings.slice(0, 6).map(b => `
    <button onclick="selectBuilding(${JSON.stringify(b).replace(/"/g,'&quot;')})"
            data-bid="${b.id}"
            class="tab-btn flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.03] text-charcoal-muted dark:text-gray-400 transition-all whitespace-nowrap">
      ${icon('building', 'w-3.5 h-3.5')}${escHtml(b.name)}${b.distance_label ? ` <span class="opacity-70">${b.distance_label}</span>` : ''}
    </button>`).join('');
}

function selectBuilding(building) {
  selectedBuilding = building;
  // Highlight active tab
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.toggle('active', parseInt(btn.dataset.bid) === building.id);
  });
  loadVendorsForBuilding(building.id);
}

// ── Vendor list ───────────────────────────────────────────────────────────────
async function loadVendorsForBuilding(buildingId) {
  if (!buildingId) {
    document.getElementById('vendor-grid').innerHTML = '';
    document.getElementById('empty').classList.remove('hidden');
    return;
  }

  document.getElementById('empty').classList.add('hidden');
  document.getElementById('vendor-grid').innerHTML = `
    <div class="skeleton h-32 rounded-2xl"></div>
    <div class="skeleton h-32 rounded-2xl"></div>`;

  try {
    const res   = await fetch(`${APP_URL}/api/v1/location/nearby-vendors.php?building_id=${buildingId}`,
                              { headers: { 'Authorization': 'Bearer ' + TOKEN } });
    const data  = await res.json();
    const vendors = data.data || [];

    if (!vendors.length) {
      document.getElementById('vendor-grid').innerHTML = '';
      document.getElementById('empty').classList.remove('hidden');
      return;
    }

    document.getElementById('vendor-grid').innerHTML = vendors.map((v, i) => {
      const sourceTag = v.source === 'gps'
        ? `<span class="badge bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/15 dark:bg-blue-500/10 dark:text-blue-300">${icon('navigation', 'w-3 h-3')}GPS matched</span>`
        : `<span class="badge bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-500/15 dark:bg-white/[0.06] dark:text-gray-300">${icon('building', 'w-3 h-3')}Registered here</span>`;
      return `
      <div class="card card-hover p-4 flex flex-col gap-3 animate-card-appear" style="animation-delay:${Math.min(i, 8) * 40}ms">
        <div class="flex items-start gap-3">
          ${LK.avatar(v.shop_name || v.name, 'w-12 h-12 text-sm')}
          <div class="flex-1 min-w-0">
            <p class="font-bold truncate">${escHtml(v.shop_name || v.name)}</p>
            <p class="text-xs text-charcoal-muted dark:text-gray-400 truncate flex items-center gap-1 mt-0.5">${icon('map-pin', 'w-3 h-3')}${escHtml(v.shop_location || 'Location not set')}</p>
          </div>
          ${v.distance_label ? `<span class="badge bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/15 dark:bg-emerald-500/10 dark:text-emerald-300 flex-shrink-0">${escHtml(v.distance_label)}</span>` : ''}
        </div>
        <div class="flex items-center justify-between gap-2">
          ${sourceTag}
          <a href="${APP_URL}/customer/order" class="btn-soft btn-sm">Order ${icon('arrow-right', 'w-3.5 h-3.5')}</a>
        </div>
      </div>`;
    }).join('');
  } catch {
    document.getElementById('vendor-grid').innerHTML = '<p class="text-sm text-red-500 p-4 sm:col-span-2">Failed to load vendors. Check connection.</p>';
  }
}

// ── Boot ──────────────────────────────────────────────────────────────────────
detectAndLoad();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
