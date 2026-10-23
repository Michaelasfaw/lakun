<?php
$pageTitle = 'New order';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-3xl mx-auto">

  <div class="page-head">
    <div>
      <p class="page-eyebrow">New order</p>
      <h1 class="page-title">What do you need?</h1>
      <p class="page-sub">List your items and nearby shops will send you their best price.</p>
    </div>
  </div>

  <!-- How it works -->
  <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-5">
    <?php foreach ([['list', 'Request', 'Tell us what you need'], ['card', 'Pay', 'Pick a price, pay upfront'], ['bike', 'Deliver', 'A rider brings it up']] as $i => [$ic, $t, $s]): ?>
    <div class="card p-3 sm:p-4 flex flex-col sm:flex-row items-center sm:items-start gap-2 sm:gap-3 text-center sm:text-left">
      <span class="relative icon-tile bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300">
        <?= icon($ic, 'w-[18px] h-[18px]') ?>
        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-charcoal dark:bg-cream text-white dark:text-charcoal text-[10px] font-bold flex items-center justify-center ring-2 ring-white dark:ring-charcoal-light"><?= $i + 1 ?></span>
      </span>
      <div>
        <p class="text-[13px] font-bold"><?= $t ?></p>
        <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5"><?= $s ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Location -->
  <div id="location-chip" class="hidden items-center gap-3 rounded-2xl border border-emerald-200/80 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10 p-3 mb-5 text-sm">
    <span class="icon-tile w-9 h-9 bg-white/80 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300"><?= icon('map-pin', 'w-[18px] h-[18px]') ?></span>
    <span id="location-text" class="text-emerald-800 dark:text-emerald-300 font-semibold flex-1 min-w-0"></span>
    <button onclick="detectLocation()" class="link text-xs">Refresh</button>
  </div>
  <button id="detect-btn" onclick="detectLocation()"
          class="w-full mb-5 py-3 border border-dashed border-amber-400/70 text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-2xl text-sm font-semibold transition-colors flex items-center justify-center gap-2">
    <?= icon('navigation', 'w-4 h-4') ?> Detect my location to find nearby shops
  </button>

  <div class="space-y-4">

    <!-- Category -->
    <section class="card card-pad">
      <div class="flex items-center gap-3 mb-4">
        <span class="icon-tile bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"><?= icon('store', 'w-5 h-5') ?></span>
        <div>
          <h2 class="section-title">Category</h2>
          <p class="hint">Pick the kind of shop that should answer.</p>
        </div>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5" id="category-grid">
        <?php foreach ([['shop', 'cart', 'Shop', 'General'], ['food', 'utensils', 'Food', 'Restaurant'], ['pharmacy', 'pill', 'Pharmacy', 'Medicine'], ['liquor', 'wine', 'Liquor', 'Age 18+']] as [$val, $ic, $label, $sub]): ?>
        <button type="button" onclick="selectCategory('<?= $val ?>')" data-cat="<?= $val ?>"
                class="category-btn group flex items-center sm:flex-col gap-3 sm:gap-1.5 p-3.5 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/[0.02] hover:border-amber-400 hover:shadow-md transition-all text-left sm:text-center">
          <span class="cat-icon icon-tile bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300 transition-colors"><?= icon($ic, 'w-5 h-5') ?></span>
          <span>
            <span class="block text-[13px] font-bold"><?= $label ?></span>
            <span class="block text-xs text-charcoal-muted dark:text-gray-400"><?= $sub ?></span>
          </span>
        </button>
        <?php endforeach; ?>
      </div>
      <input type="hidden" id="selected-category" value="shop">
      <div id="pharmacy-notice" class="hidden mt-3 text-xs items-start gap-2 bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-400/20 rounded-xl px-3 py-2.5 text-blue-700 dark:text-blue-300">
        <?= icon('pill', 'w-4 h-4 mt-px') ?><span>For prescription medications, include the prescription details in the notes field below.</span>
      </div>
      <div id="liquor-notice" class="hidden mt-3 text-xs items-start gap-2 bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-400/20 rounded-xl px-3 py-2.5 text-red-700 dark:text-red-300">
        <?= icon('alert', 'w-4 h-4 mt-px') ?><span>Restricted category. Age verification is required on delivery, and you must be 18 or older.</span>
      </div>
      <div id="food-notice" class="hidden mt-3 text-xs items-start gap-2 bg-orange-50 dark:bg-orange-500/10 border border-orange-100 dark:border-orange-400/20 rounded-xl px-3 py-2.5 text-orange-700 dark:text-orange-300">
        <?= icon('zap', 'w-4 h-4 mt-px') ?><span><strong>Express order.</strong> Your request goes directly to the restaurant and is processed immediately.</span>
      </div>
    </section>

    <!-- Items -->
    <section class="card card-pad">
      <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
          <span class="icon-tile bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300"><?= icon('list', 'w-5 h-5') ?></span>
          <div>
            <h2 class="section-title">Items <span class="text-red-500">*</span></h2>
            <p class="hint">Add everything you need, with brand and quantity.</p>
          </div>
        </div>
        <button type="button" onclick="addItem()" class="btn-soft btn-sm flex-shrink-0"><?= icon('plus', 'w-3.5 h-3.5') ?>Add item</button>
      </div>

      <p class="label">Quick add common items</p>
      <div id="catalog-chips" class="flex flex-wrap gap-1.5 mb-5"></div>

      <div id="items-list" class="space-y-2.5"></div>
    </section>

    <!-- Delivery address -->
    <section class="card card-pad">
      <div class="flex items-center gap-3 mb-4">
        <span class="icon-tile bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300"><?= icon('map-pin', 'w-5 h-5') ?></span>
        <div>
          <h2 class="section-title">Delivery address</h2>
          <p class="hint">Where should the rider bring your order?</p>
        </div>
      </div>
      <div class="grid grid-cols-3 gap-3 mb-3">
        <div>
          <label class="label" for="block">Block</label>
          <input type="text" id="block" placeholder="A" value="<?= htmlspecialchars($user['block'] ?? '') ?>" class="input">
        </div>
        <div>
          <label class="label" for="floor">Floor</label>
          <input type="text" id="floor" placeholder="5" value="<?= htmlspecialchars($user['floor'] ?? '') ?>" class="input">
        </div>
        <div>
          <label class="label" for="house_no">House no.</label>
          <input type="text" id="house_no" placeholder="5B" value="<?= htmlspecialchars($user['house_no'] ?? '') ?>" class="input">
        </div>
      </div>
      <div class="grid sm:grid-cols-2 gap-3">
        <div>
          <label class="label" for="customer_name">Name</label>
          <input type="text" id="customer_name" placeholder="Your name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" readonly class="input !bg-gray-100 dark:!bg-white/[0.05] cursor-not-allowed">
        </div>
        <div>
          <label class="label" for="customer_phone">Phone</label>
          <input type="tel" id="customer_phone" placeholder="09…" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="input">
        </div>
      </div>
      <div class="mt-3">
        <label class="label" for="notes">Notes <span class="font-normal text-charcoal-muted dark:text-gray-500">(optional)</span></label>
        <textarea id="notes" rows="2" placeholder="Brand preferences, special instructions, extra details…" class="input resize-none"></textarea>
      </div>
    </section>

    <!-- Pre-order -->
    <section class="card card-pad">
      <label class="flex items-center gap-3 cursor-pointer">
        <span class="icon-tile bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300"><?= icon('calendar', 'w-5 h-5') ?></span>
        <span class="flex-1">
          <span class="block section-title">Schedule for later</span>
          <span class="block hint">Order now, delivered at your chosen time.</span>
        </span>
        <input type="checkbox" id="preorder-toggle" class="w-5 h-5 accent-amber-600 rounded" onchange="togglePreorder(this)">
      </label>
      <div id="preorder-fields" class="hidden mt-4 grid grid-cols-2 gap-3">
        <div>
          <label class="label" for="scheduled-date">Date</label>
          <input type="date" id="scheduled-date" min="<?= date('Y-m-d') ?>" class="input">
        </div>
        <div>
          <label class="label" for="scheduled-time">Time</label>
          <input type="time" id="scheduled-time" class="input">
        </div>
      </div>
    </section>

    <input type="hidden" id="building_id" value="<?= (int)($user['building_id'] ?? 0) ?>">

    <button type="button" id="submit-btn" onclick="submitOrder()" class="btn-brand btn-lg w-full">
      <span id="btn-text">Send request</span>
      <svg id="btn-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
      </svg>
      <?= icon('send', 'w-4 h-4') ?>
    </button>
    <p class="text-center hint pb-2">Shops usually reply within a few minutes. You only pay after you choose a price.</p>
  </div>
</div>

<style>
.category-btn.active { border-color:#D97706; background:rgba(217,119,6,.07); box-shadow:0 0 0 3px rgba(217,119,6,.14); }
.dark .category-btn.active { background:rgba(217,119,6,.14); }
.category-btn.active .cat-icon { background:#D97706; color:#fff; }
#location-chip:not(.hidden), #pharmacy-notice:not(.hidden), #liquor-notice:not(.hidden), #food-notice:not(.hidden) { display:flex; }
</style>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');
let itemCount = 0;

// ── Product catalog (English / አማርኛ) ─────────────────────────────────────
const CATALOG = {
  shop: [
    { en:'Bread',         am:'ዳቦ' },
    { en:'Eggs (×12)',    am:'እንቁላል (×12)' },
    { en:'Milk 1L',       am:'ወተት 1ሊ' },
    { en:'Sugar 1kg',     am:'ስኳር 1ኪ' },
    { en:'Rice 5kg',      am:'ሩዝ 5ኪ' },
    { en:'Bottled Water', am:'ንፁህ ውሃ' },
    { en:'Butter',        am:'ቅቤ' },
    { en:'Noodles',       am:'ፓስታ/ኑድልስ' },
    { en:'Tomatoes',      am:'ቲማቲም' },
    { en:'Onions',        am:'ሽንኩርት' },
    { en:'Cooking Oil',   am:'የምግብ ዘይት' },
    { en:'Salt',          am:'ጨው' },
  ],
  food: [
    { en:'Injera & Wot',  am:'እንጀራ & ወጥ' },
    { en:'Tibs',          am:'ጥብስ' },
    { en:'Shiro',         am:'ሽሮ' },
    { en:'Firfir',        am:'ፍርፍር' },
    { en:'Burger',        am:'በርገር' },
    { en:'Pizza',         am:'ፒዛ' },
    { en:'Pasta',         am:'ፓስታ' },
    { en:'Sandwich',      am:'ሳንድዊች' },
    { en:'Juice',         am:'ጭማቂ' },
    { en:'Coffee',        am:'ቡና' },
  ],
  pharmacy: [
    { en:'Paracetamol',   am:'ፓራሲታሞል' },
    { en:'Ibuprofen',     am:'አይቡፕሮፌን' },
    { en:'Amoxicillin',   am:'አሞክሲሲሊን' },
    { en:'ORS',           am:'ORS' },
    { en:'Vitamin C',     am:'ቫይታሚን ሲ' },
    { en:'Bandages',      am:'ፋሻ' },
    { en:'Antiseptic',    am:'ፀረ-ቁስሎ' },
    { en:'Antacid',       am:'አንታሲድ' },
  ],
  liquor: [
    { en:'Beer (×6)',     am:'ቢራ (×6)' },
    { en:'Wine Bottle',   am:'የወይን ጠጅ' },
    { en:'Whisky',        am:'ዊስኪ' },
    { en:'Gin',           am:'ጂን' },
    { en:'Vodka',         am:'ቮድካ' },
    { en:'Rum',           am:'ረም' },
    { en:'Local Tej',     am:'ጠጅ' },
  ],
};

function selectCategory(cat) {
  document.getElementById('selected-category').value = cat;
  document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
  document.querySelector(`[data-cat="${cat}"]`)?.classList.add('active');
  ['pharmacy-notice','liquor-notice','food-notice'].forEach(id => document.getElementById(id)?.classList.add('hidden'));
  if (cat === 'pharmacy') document.getElementById('pharmacy-notice').classList.remove('hidden');
  if (cat === 'liquor')   document.getElementById('liquor-notice').classList.remove('hidden');
  if (cat === 'food')     document.getElementById('food-notice').classList.remove('hidden');
  renderCatalog(cat);
}

function renderCatalog(cat) {
  const items = CATALOG[cat] || CATALOG.shop;
  document.getElementById('catalog-chips').innerHTML = items.map(item =>
    `<button type="button" onclick="addItemWithName('${item.en.replace(/'/g,"\\'")}', 1)"
             class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-full bg-white dark:bg-white/[0.04] text-charcoal dark:text-cream hover:bg-amber-50 dark:hover:bg-amber-500/10 hover:border-amber-400 hover:text-amber-700 dark:hover:text-amber-300 transition-all font-semibold border border-gray-200 dark:border-white/10">
      ${icon('plus', 'w-3 h-3')}${item.en} <span class="text-amber-600 dark:text-amber-400 font-normal">/ ${item.am}</span>
    </button>`
  ).join('');
}

function togglePreorder(checkbox) {
  const f = document.getElementById('preorder-fields');
  f.classList.toggle('hidden', !checkbox.checked);
  if (checkbox.checked) {
    // Default to tomorrow at 08:00
    const tomorrow = new Date(); tomorrow.setDate(tomorrow.getDate() + 1);
    const ymd = tomorrow.toISOString().slice(0, 10);
    const dateEl = document.getElementById('scheduled-date');
    const timeEl = document.getElementById('scheduled-time');
    if (!dateEl.value) dateEl.value = ymd;
    if (!timeEl.value) timeEl.value = '08:00';
  }
}

function addItem(name='', qty=1, brand='') {
  itemCount++;
  const id = 'item-' + itemCount;
  const div = document.createElement('div');
  div.className = 'item-row flex gap-2 items-center animate-card-appear';
  div.id = id;
  div.innerHTML = `
    <div class="flex-1 grid grid-cols-5 gap-2">
      <input type="text" placeholder="Item name *" value="${escHtml(name)}"
             class="input col-span-5 sm:col-span-2 !px-3 !py-2.5 item-name" required>
      <input type="text" placeholder="Brand" value="${escHtml(brand)}"
             class="input col-span-3 sm:col-span-2 !px-3 !py-2.5 item-brand">
      <input type="number" placeholder="Qty" value="${qty}" min="1"
             class="input col-span-2 sm:col-span-1 !px-2 !py-2.5 text-center item-qty">
    </div>
    <button type="button" onclick="removeItem('${id}')" aria-label="Remove item" class="icon-btn !w-9 !h-9 hover:!text-red-500 flex-shrink-0">
      ${icon('x', 'w-4 h-4')}
    </button>`;
  document.getElementById('items-list').appendChild(div);
  if (!name) div.querySelector('.item-name').focus();
}

function addItemWithName(name, qty) {
  // If only 1 empty row exists, fill it instead of adding new
  const rows = document.querySelectorAll('.item-row');
  for (const row of rows) {
    const nameEl = row.querySelector('.item-name');
    if (nameEl && !nameEl.value.trim()) { nameEl.value = name; row.querySelector('.item-qty').value = qty; return; }
  }
  addItem(name, qty);
}

function removeItem(id) {
  const el = document.getElementById(id);
  if (el) el.remove();
}

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// ── Geolocation ───────────────────────────────────────────────────────────
const DETECT_IDLE = `${icon('navigation', 'w-4 h-4')} Detect my location`;
function detectLocation() {
  const btn = document.getElementById('detect-btn');
  if (!navigator.geolocation) { showToast('Geolocation not supported', '', 'error'); return; }
  if (btn) { btn.innerHTML = `${icon('hourglass', 'w-4 h-4')} Detecting…`; btn.disabled = true; }
  navigator.geolocation.getCurrentPosition(async (pos) => {
    const { latitude, longitude } = pos.coords;
    try {
      const res  = await fetch(`${APP_URL}/api/v1/location/nearby-buildings.php?lat=${latitude}&lng=${longitude}&radius=5`);
      const data = await res.json();
      const blds = data.data || [];
      const chip = document.getElementById('location-chip');
      const text = document.getElementById('location-text');
      if (btn) btn.classList.add('hidden');
      if (blds.length) {
        chip.classList.remove('hidden');
        text.textContent = `Nearest: ${blds[0].name} (${blds[0].distance_label || 'nearby'})`;
      } else {
        chip.classList.remove('hidden');
        text.textContent = 'No registered buildings found nearby.';
      }
    } catch { if (btn) { btn.innerHTML = DETECT_IDLE; btn.disabled = false; } }
  }, () => { if (btn) { btn.innerHTML = DETECT_IDLE; btn.disabled = false; } });
}

// ── Submit order ──────────────────────────────────────────────────────────
async function submitOrder() {
  // Collect items
  const items = [];
  document.querySelectorAll('.item-row').forEach(row => {
    const name  = row.querySelector('.item-name')?.value.trim();
    const brand = row.querySelector('.item-brand')?.value.trim() || '';
    const qty   = parseInt(row.querySelector('.item-qty')?.value) || 1;
    if (name) items.push({ name, qty, brand });
  });

  if (!items.length) { showToast('Add at least one item', '', 'error'); return; }
  const block    = document.getElementById('block').value.trim();
  const floor    = document.getElementById('floor').value.trim();
  const house_no = document.getElementById('house_no').value.trim();
  const phone    = document.getElementById('customer_phone').value.trim();
  const buildingId = parseInt(document.getElementById('building_id').value);

  if (!block || !floor || !house_no) { showToast('Fill in your delivery address', '', 'error'); return; }
  if (!phone) { showToast('Phone number required', '', 'error'); return; }
  if (!buildingId) { showToast('Building not set — check your profile', '', 'error'); return; }

  const btn = document.getElementById('submit-btn');
  btn.disabled = true;
  document.getElementById('btn-spinner').classList.remove('hidden');
  document.getElementById('btn-text').textContent = 'Sending…';

  // Collect pre-order scheduled time
  let scheduledAt = null;
  if (document.getElementById('preorder-toggle').checked) {
    const d = document.getElementById('scheduled-date').value;
    const t = document.getElementById('scheduled-time').value;
    if (d && t) {
      scheduledAt = d + 'T' + t + ':00';
    } else {
      showToast('Pick a date and time for your pre-order', '', 'error');
      btn.disabled = false;
      document.getElementById('btn-spinner').classList.add('hidden');
      document.getElementById('btn-text').textContent = 'Send request';
      return;
    }
  }

  try {
    const res = await fetch(APP_URL + '/api/v1/orders/create.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
      body: JSON.stringify({
        items,
        building_id:     buildingId,
        block, floor, house_no,
        customer_phone:  phone,
        notes:           document.getElementById('notes').value.trim(),
        category:        document.getElementById('selected-category').value,
        scheduled_at:    scheduledAt,
      }),
    });
    const data = await res.json();
    if (data.status === 'success') {
      const isfast = data.data.is_fast_order;
      const sched  = data.data.scheduled_at;
      let subtitle = isfast ? 'Express order — processing now.' : `Vendors have ${data.data.bid_window_seconds}s to bid.`;
      if (sched) subtitle = 'Pre-order scheduled for ' + sched.replace('T',' ').slice(0,16);
      showToast('Request sent! ' + (data.data.order_code || ''), subtitle, 'success');
      setTimeout(() => window.location.href = APP_URL + '/customer/track/' + data.data.order_id, 900);
    } else {
      showToast(data.message || 'Failed to place order', '', 'error');
      btn.disabled = false;
      document.getElementById('btn-spinner').classList.add('hidden');
      document.getElementById('btn-text').textContent = 'Send request';
    }
  } catch {
    showToast('Network error. Try again.', '', 'error');
    btn.disabled = false;
    document.getElementById('btn-spinner').classList.add('hidden');
    document.getElementById('btn-text').textContent = 'Send request';
  }
}

// Init
selectCategory('shop');
addItem(); // Start with one empty row
if (navigator.geolocation) detectLocation();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
