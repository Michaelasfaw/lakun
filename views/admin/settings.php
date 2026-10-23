<?php
$pageTitle = 'Settings';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-3xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Administration</p>
      <h1 class="page-title">Platform settings</h1>
      <p class="page-sub">Configure fees, bid timers and payment details.</p>
    </div>
    <button onclick="saveSettings()" class="btn-brand hidden md:inline-flex"><?= icon('check', 'w-4 h-4') ?>Save settings</button>
  </div>

  <div class="space-y-4 md:space-y-5">
    <!-- Delivery settings -->
    <section class="card card-pad">
      <div class="flex items-center gap-3 mb-5">
        <span class="icon-tile bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"><?= icon('bike', 'w-5 h-5') ?></span>
        <div>
          <h2 class="section-title">Delivery</h2>
          <p class="hint">Fees and bid windows for orders</p>
        </div>
      </div>
      <div class="space-y-4">
        <div>
          <label class="label" for="delivery_fee">Delivery fee</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-charcoal-muted dark:text-gray-400 font-bold text-sm">ETB</span>
            <input type="number" id="delivery_fee" min="0" step="0.01" placeholder="50" class="input pl-14 num">
          </div>
          <p class="hint mt-1.5">Added to the item price and shown to the customer before they confirm.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label" for="bid_window_food_secs">Food bid window (secs)</label>
            <input type="number" id="bid_window_food_secs" min="10" max="600" step="10" placeholder="120" class="input num">
            <p class="hint mt-1.5">Max wait for first bid. Recommended: 120s</p>
          </div>
          <div>
            <label class="label" for="bid_window_other_secs">Other bid window (secs)</label>
            <input type="number" id="bid_window_other_secs" min="10" max="600" step="10" placeholder="120" class="input num">
            <p class="hint mt-1.5">Max wait for first bid. Recommended: 120s</p>
          </div>
        </div>
        <div>
          <label class="label" for="bid_grace_after_first_secs">Grace after first bid (secs)</label>
          <input type="number" id="bid_grace_after_first_secs" min="10" max="120" step="5" placeholder="30" class="input num">
          <p class="hint mt-1.5">Once a bid arrives, the window shortens to this. Default: 30s</p>
        </div>
      </div>
    </section>

    <!-- Payment Details -->
    <section class="card card-pad">
      <div class="flex items-center gap-3 mb-5">
        <span class="icon-tile bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300"><?= icon('card', 'w-5 h-5') ?></span>
        <div>
          <h2 class="section-title">Payment details</h2>
          <p class="hint">Shown to customers when they pay (bank transfer and Telebirr)</p>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="label" for="payment_bank_name">Bank name</label>
          <input type="text" id="payment_bank_name" placeholder="Commercial Bank of Ethiopia" class="input">
        </div>
        <div>
          <label class="label" for="payment_bank_account">Account number</label>
          <input type="text" id="payment_bank_account" placeholder="1000123456789" class="input num">
        </div>
        <div>
          <label class="label" for="payment_account_name">Account name</label>
          <input type="text" id="payment_account_name" placeholder="Lakun Technologies" class="input">
        </div>
        <div>
          <label class="label" for="payment_telebirr">Telebirr number</label>
          <input type="text" id="payment_telebirr" placeholder="0911000000" class="input num">
        </div>
      </div>
    </section>

    <!-- Credit settings -->
    <section class="card card-pad flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
      <div class="flex items-center gap-3">
        <span class="icon-tile bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-300"><?= icon('zap', 'w-5 h-5') ?></span>
        <div>
          <h2 class="section-title">Credit system</h2>
          <p class="hint">Rider credit packages are priced (ETB per bundle) on the Credit packages page.</p>
        </div>
      </div>
      <a href="<?= $appUrl ?>/admin/credit-packages" class="btn-soft flex-shrink-0">Manage packages<?= icon('arrow-right', 'w-4 h-4') ?></a>
    </section>

    <!-- Platform info -->
    <section class="card card-pad">
      <div class="flex items-center gap-3 mb-5">
        <span class="icon-tile bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300"><?= icon('info', 'w-5 h-5') ?></span>
        <div>
          <h2 class="section-title">Platform info</h2>
          <p class="hint">Read-only platform details</p>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div class="surface-muted p-3.5">
          <p class="hint font-medium">Platform name</p>
          <p class="font-bold text-charcoal dark:text-cream">Lakun</p>
        </div>
        <div class="surface-muted p-3.5">
          <p class="hint font-medium">Currency</p>
          <p class="font-bold text-charcoal dark:text-cream">Ethiopian Birr (ETB)</p>
        </div>
      </div>
    </section>

    <button onclick="saveSettings()" class="btn-brand btn-lg w-full md:hidden"><?= icon('check', 'w-5 h-5') ?>Save settings</button>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');

const SETTING_IDS = ['delivery_fee','bid_window_food_secs','bid_window_other_secs','bid_grace_after_first_secs',
                     'payment_bank_name','payment_bank_account','payment_account_name','payment_telebirr'];

async function loadSettings() {
  const res  = await fetch(APP_URL + '/api/v1/settings/index.php');
  const data = await res.json();
  if (data.status === 'success') {
    SETTING_IDS.forEach(k => {
      const el = document.getElementById(k);
      if (el && data.data[k]) el.value = data.data[k];
    });
  }
}

async function saveSettings() {
  const body = {};
  SETTING_IDS.forEach(k => {
    const el = document.getElementById(k);
    if (el && el.value) body[k] = el.value;
  });
  const res  = await fetch(APP_URL + '/api/v1/settings/index.php', {
    method:'POST',
    headers:{'Content-Type':'application/json','Authorization':'Bearer '+TOKEN},
    body: JSON.stringify(body),
  });
  const data = await res.json();
  if (data.status === 'success') showToast('Settings saved', '', 'success');
  else showToast(data.message || 'Error', '', 'error');
}

loadSettings();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
