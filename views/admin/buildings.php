<?php
$pageTitle = 'Buildings';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-3xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Administration</p>
      <h1 class="page-title">Buildings</h1>
      <p class="page-sub">Manage properties and their vendor service radius.</p>
    </div>
    <button onclick="openAddModal()" class="btn-brand"><?= icon('plus', 'w-4 h-4') ?>Add building</button>
  </div>

  <div id="buildings-list" class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
    <div class="skeleton h-32 rounded-2xl"></div>
    <div class="skeleton h-32 rounded-2xl"></div>
    <div class="skeleton h-32 rounded-2xl"></div>
    <div class="skeleton h-32 rounded-2xl"></div>
  </div>
</div>

<!-- Add/Edit Modal -->
<div id="building-modal" class="hidden fixed inset-0 bg-ink-900/60 backdrop-blur-sm z-50 flex items-end md:items-center justify-center p-4">
  <div class="bg-white dark:bg-charcoal-light rounded-3xl p-5 md:p-6 w-full max-w-md shadow-2xl max-h-[92vh] overflow-y-auto border border-gray-200/60 dark:border-white/[0.06]">
    <div class="flex items-center gap-3 mb-5">
      <span class="icon-tile bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300"><?= icon('building', 'w-5 h-5') ?></span>
      <h3 class="text-lg font-extrabold text-charcoal dark:text-cream flex-1" id="modal-title">Add building</h3>
      <button type="button" onclick="closeModal()" class="icon-btn -mr-2" aria-label="Close"><?= icon('x', 'w-5 h-5') ?></button>
    </div>
    <form id="building-form" class="space-y-4">
      <input type="hidden" id="edit-id">
      <div>
        <label class="label" for="b-name">Name <span class="text-red-500">*</span></label>
        <input type="text" id="b-name" required placeholder="Bole Heights" class="input input-field">
      </div>
      <div>
        <label class="label" for="b-addr">Address</label>
        <input type="text" id="b-addr" placeholder="Bole Road, Addis Ababa" class="input input-field">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label" for="b-floors">Floors</label>
          <input type="number" id="b-floors" placeholder="18" min="1" class="input input-field">
        </div>
        <div>
          <label class="label" for="b-radius">Radius (km)</label>
          <input type="number" id="b-radius" placeholder="2.0" min="0.1" step="0.1" class="input input-field">
          <p class="hint mt-1">Vendor notification radius</p>
        </div>
      </div>

      <!-- GPS Section -->
      <div class="surface-muted p-4">
        <div class="flex items-center justify-between mb-3">
          <span class="label !mb-0 flex items-center gap-1.5"><?= icon('map-pin', 'w-4 h-4 text-amber-600') ?>GPS coordinates</span>
          <button type="button" onclick="detectBuildingGPS()" class="link !text-xs"><?= icon('navigation', 'w-3.5 h-3.5') ?>Use my location</button>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="hint block mb-1" for="b-lat">Latitude</label>
            <input type="number" id="b-lat" placeholder="9.00540" step="0.00001" class="input input-field font-mono !px-3">
          </div>
          <div>
            <label class="hint block mb-1" for="b-lng">Longitude</label>
            <input type="number" id="b-lng" placeholder="38.76360" step="0.00001" class="input input-field font-mono !px-3">
          </div>
        </div>
        <p class="hint mt-2.5">Without GPS, vendor matching falls back to manual building assignments only.</p>
      </div>

      <div class="flex gap-2 pt-1">
        <button type="button" onclick="closeModal()" class="btn-outline flex-1">Cancel</button>
        <button type="submit" class="btn-brand flex-1">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');
let _buildings = [];

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

async function loadBuildings() {
  const res  = await fetch(APP_URL + '/api/v1/admin/buildings.php', { headers: { 'Authorization': 'Bearer ' + TOKEN } });
  const data = await res.json();
  const buildings = data.data || [];
  _buildings = buildings;
  const list = document.getElementById('buildings-list');

  if (!buildings.length) {
    list.className = '';
    list.innerHTML = '<div class="card">' + LK.empty('building', 'No buildings yet', 'Add your first property to start accepting orders.', '<button onclick="openAddModal()" class="btn-brand">' + icon('plus','w-4 h-4') + 'Add building</button>') + '</div>';
    return;
  }
  list.className = 'grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4';

  list.innerHTML = buildings.map((b, i) => {
    const hasGps = b.latitude && b.longitude;
    const gpsTag = hasGps
      ? `<span class="badge ${LK.TONES.emerald}">${icon('map-pin','w-3 h-3')}${parseFloat(b.latitude).toFixed(4)}, ${parseFloat(b.longitude).toFixed(4)}</span>
         <span class="badge ${LK.TONES.gray}">${icon('scan','w-3 h-3')}${b.service_radius_km} km radius</span>`
      : `<span class="badge ${LK.TONES.amber}">${icon('alert','w-3 h-3')}No GPS coordinates</span>`;
    return `
    <div class="card card-hover p-5 animate-card-appear flex flex-col" style="animation-delay:${i*30}ms">
      <div class="flex items-start justify-between gap-3 mb-3">
        <div class="flex items-start gap-3 min-w-0">
          <span class="icon-tile bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">${icon('building','w-5 h-5')}</span>
          <div class="min-w-0">
            <h3 class="font-bold text-charcoal dark:text-cream leading-snug">${escHtml(b.name)}</h3>
            <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5">${escHtml(b.address||'No address')} &middot; ${b.total_floors} floors</p>
          </div>
        </div>
        <button onclick="editBuildingIdx(${i})" class="btn-ghost btn-sm flex-shrink-0">${icon('edit','w-3.5 h-3.5')}Edit</button>
      </div>
      <div class="flex items-center gap-1.5 flex-wrap mt-auto">${gpsTag}</div>
    </div>`;
  }).join('');
}

function editBuildingIdx(i) { if (_buildings[i]) editBuilding(_buildings[i]); }

function openAddModal() {
  document.getElementById('modal-title').textContent = 'Add building';
  ['edit-id','b-name','b-addr','b-floors','b-radius','b-lat','b-lng'].forEach(id => {
    document.getElementById(id).value = '';
  });
  document.getElementById('b-radius').value = '2.0';
  document.getElementById('building-modal').classList.remove('hidden');
}

function editBuilding(b) {
  document.getElementById('modal-title').textContent = 'Edit building';
  document.getElementById('edit-id').value   = b.id;
  document.getElementById('b-name').value    = b.name;
  document.getElementById('b-addr').value    = b.address || '';
  document.getElementById('b-floors').value  = b.total_floors || 1;
  document.getElementById('b-radius').value  = b.service_radius_km || 2.0;
  document.getElementById('b-lat').value     = b.latitude  || '';
  document.getElementById('b-lng').value     = b.longitude || '';
  document.getElementById('building-modal').classList.remove('hidden');
}

function closeModal() { document.getElementById('building-modal').classList.add('hidden'); }

function detectBuildingGPS() {
  if (!navigator.geolocation) { showToast('Geolocation not supported', '', 'error'); return; }
  showToast('Getting location…', '', 'info');
  navigator.geolocation.getCurrentPosition((pos) => {
    document.getElementById('b-lat').value = pos.coords.latitude.toFixed(6);
    document.getElementById('b-lng').value = pos.coords.longitude.toFixed(6);
    showToast('Location detected', `${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)}`, 'success');
  }, () => showToast('Location access denied', '', 'error'));
}

document.getElementById('building-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const id  = document.getElementById('edit-id').value;
  const lat = document.getElementById('b-lat').value;
  const lng = document.getElementById('b-lng').value;
  const body = {
    name:              document.getElementById('b-name').value,
    address:           document.getElementById('b-addr').value,
    total_floors:      parseInt(document.getElementById('b-floors').value) || 1,
    service_radius_km: parseFloat(document.getElementById('b-radius').value) || 2.0,
    latitude:          lat ? parseFloat(lat) : null,
    longitude:         lng ? parseFloat(lng) : null,
  };
  if (id) body.id = parseInt(id);

  const res  = await fetch(APP_URL + '/api/v1/admin/buildings.php', {
    method: id ? 'PUT' : 'POST',
    headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  });
  const data = await res.json();
  if (data.status === 'success') {
    closeModal();
    showToast('Building saved', '', 'success');
    loadBuildings();
  } else {
    showToast(data.message || 'Error', '', 'error');
  }
});

loadBuildings();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
