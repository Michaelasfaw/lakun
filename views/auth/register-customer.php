<?php
$appUrl = APP_URL;
require_once __DIR__ . '/../layouts/components/icons.php';
$isDark = isset($_COOKIE['td_dark']) && $_COOKIE['td_dark'] === '1';
?>
<!DOCTYPE html>
<html lang="en" class="<?= $isDark ? 'dark' : '' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#14120F">
  <title>Join as a customer · <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= lakunAsset('assets/css/app.css') ?>">
</head>
<body class="min-h-screen bg-cream dark:bg-charcoal text-charcoal dark:text-cream font-sans">
<?php iconSprite(); ?>

<div id="toast-container" class="fixed top-4 right-4 left-4 sm:left-auto z-50 flex flex-col gap-2 sm:max-w-sm sm:w-full pointer-events-none"></div>

<div class="min-h-screen grid lg:grid-cols-[1fr_1.1fr]">

  <!-- Brand panel -->
  <section class="relative hidden lg:flex flex-col justify-between p-12 xl:p-14 ink-gradient text-white overflow-hidden">
    <div class="absolute inset-0 bg-grid opacity-40 [mask-image:radial-gradient(ellipse_at_top_right,black_20%,transparent_70%)]" aria-hidden="true"></div>
    <div class="absolute -bottom-40 -left-24 w-[520px] h-[520px] rounded-full bg-amber-500/20 blur-3xl" aria-hidden="true"></div>

    <a href="<?= $appUrl ?>/login" class="relative flex items-center gap-3">
      <span class="brand-mark w-11 h-11 rounded-2xl"><?= icon('logo', 'w-6 h-6') ?></span>
      <span class="text-xl font-extrabold tracking-tight"><?= APP_NAME ?></span>
    </a>

    <div class="relative max-w-lg">
      <p class="inline-flex items-center gap-2 rounded-full bg-white/[0.06] border border-white/10 px-3 py-1.5 text-xs font-semibold text-amber-200">
        <?= icon('bag', 'w-3.5 h-3.5') ?> For residents
      </p>
      <h1 class="mt-6 text-[2.5rem] xl:text-[3rem] font-extrabold leading-[1.05] tracking-tightest text-balance">
        Get anything delivered
        <span class="bg-gradient-to-r from-amber-300 via-amber-400 to-orange-400 bg-clip-text text-transparent">to your floor.</span>
      </h1>
      <p class="mt-5 text-[15px] leading-relaxed text-white/60 max-w-md">
        Groceries, medicine, meals. List what you need, nearby shops send their best price, and a rider brings it to your door.
      </p>

      <div class="relative mt-9 w-[300px] rounded-2xl bg-white text-charcoal p-4 shadow-2xl animate-float">
        <div class="flex items-center justify-between">
          <p class="text-[11px] font-bold uppercase tracking-wider text-charcoal-muted">3 prices received</p>
          <span class="badge bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-600/20"><?= icon('clock', 'w-3 h-3') ?>2:41</span>
        </div>
        <?php foreach ([['CarePlus Pharmacy', '420', true], ['Tunde Mart', '465', false], ['Kemi Fresh Foods', '498', false]] as [$shop, $price, $best]): ?>
        <div class="mt-2.5 flex items-center justify-between rounded-xl px-3 py-2 <?= $best ? 'bg-emerald-50 ring-1 ring-emerald-500/30' : 'bg-gray-50' ?>">
          <span class="flex items-center gap-2 text-[13px] font-semibold"><?= icon('store', 'w-4 h-4 text-charcoal-muted') ?><?= $shop ?></span>
          <span class="text-[13px] font-extrabold num <?= $best ? 'text-emerald-700' : '' ?>">ETB <?= $price ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="relative grid grid-cols-3 gap-6 max-w-xl">
      <?php foreach ([['tag', 'Shops compete', 'Best price wins'], ['shield', 'Verified payments', 'Telebirr & bank'], ['navigation', 'Live tracking', 'Door-to-door']] as [$ic, $t, $s]): ?>
      <div>
        <span class="w-9 h-9 rounded-xl bg-white/[0.06] border border-white/10 flex items-center justify-center text-amber-300"><?= icon($ic, 'w-[18px] h-[18px]') ?></span>
        <p class="mt-3 text-sm font-bold"><?= $t ?></p>
        <p class="text-xs text-white/45 mt-0.5"><?= $s ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Form panel -->
  <section class="relative flex flex-col px-5 sm:px-10 py-6 sm:py-10">
    <div class="pointer-events-none absolute inset-0 overflow-hidden lg:hidden" aria-hidden="true">
      <div class="absolute -top-32 -right-24 w-80 h-80 rounded-full bg-amber-300/30 dark:bg-amber-500/10 blur-3xl"></div>
    </div>

    <div class="relative flex items-center justify-between">
      <a href="<?= $appUrl ?>/login" class="flex items-center gap-2.5 lg:invisible">
        <span class="brand-mark w-9 h-9"><?= icon('logo', 'w-5 h-5') ?></span>
        <span class="text-lg font-extrabold tracking-tight"><?= APP_NAME ?></span>
      </a>
      <button class="js-dark-toggle icon-btn border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/[0.04]" aria-label="Toggle dark mode">
        <span id="theme-moon" class="<?= $isDark ? 'hidden' : '' ?>"><?= icon('moon', 'w-[18px] h-[18px]') ?></span>
        <span id="theme-sun" class="<?= $isDark ? '' : 'hidden' ?>"><?= icon('sun', 'w-[18px] h-[18px] text-amber-400') ?></span>
      </button>
    </div>

    <div class="relative flex-1 flex items-center justify-center py-8">
      <div class="w-full max-w-[460px] animate-fade-slide-up">
        <span class="icon-tile-lg bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300"><?= icon('bag', 'w-6 h-6') ?></span>
        <h2 class="mt-4 text-[1.85rem] font-extrabold tracking-tight">Join as a customer</h2>
        <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5">Order items and get them delivered to your door.</p>

        <form id="register-form" class="mt-7 space-y-4">
          <input type="hidden" name="role" value="customer">

          <div>
            <label class="label" for="f-name">Full name</label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"><?= icon('user', 'w-[18px] h-[18px]') ?></span>
              <input type="text" id="f-name" name="name" placeholder="Amara Okafor" required autocomplete="name" class="input pl-11 py-3.5">
            </div>
          </div>
          <div>
            <label class="label" for="f-phone">Phone number</label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"><?= icon('phone', 'w-[18px] h-[18px]') ?></span>
              <input type="tel" id="f-phone" name="phone" placeholder="09 1122 3344" required autocomplete="tel" class="input pl-11 py-3.5">
            </div>
          </div>
          <div>
            <label class="label" for="building-select">Apartment building</label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"><?= icon('building', 'w-[18px] h-[18px]') ?></span>
              <select name="building_id" required id="building-select" class="input pl-11 py-3.5">
                <option value="">Loading buildings…</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="label" for="f-block">Block</label>
              <input type="text" id="f-block" name="block" placeholder="A" required class="input py-3.5">
            </div>
            <div>
              <label class="label" for="f-floor">Floor</label>
              <input type="number" id="f-floor" name="floor" placeholder="2" min="0" required class="input py-3.5">
            </div>
            <div>
              <label class="label" for="f-house">House no.</label>
              <input type="text" id="f-house" name="house_no" placeholder="5" required class="input py-3.5">
            </div>
          </div>

          <div>
            <label class="label" for="pw-input">Password</label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"><?= icon('lock', 'w-[18px] h-[18px]') ?></span>
              <input type="password" id="pw-input" name="password" placeholder="Min. 6 characters" required minlength="6" autocomplete="new-password" class="input pl-11 pr-12 py-3.5">
              <button type="button" tabindex="-1" aria-label="Show password"
                      onclick="const i=document.getElementById('pw-input');i.type=i.type==='password'?'text':'password'"
                      class="absolute right-2 top-1/2 -translate-y-1/2 p-2 rounded-lg text-gray-400 hover:text-charcoal dark:hover:text-cream transition-colors">
                <?= icon('eye', 'w-[18px] h-[18px]') ?>
              </button>
            </div>
          </div>

          <button type="submit" id="submit-btn" class="btn-brand btn-lg w-full !mt-6">
            <span id="btn-text">Create account</span>
            <svg id="btn-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <?= icon('arrow-right', 'w-4 h-4') ?>
          </button>
        </form>

        <p class="text-sm text-center text-charcoal-muted dark:text-gray-400 mt-6">
          Already have an account? <a href="<?= $appUrl ?>/login" class="link">Sign in</a>
        </p>
        <div class="mt-5 rounded-2xl bg-gray-100/70 dark:bg-white/[0.03] border border-gray-200/60 dark:border-white/[0.06] p-4">
          <p class="text-[13px] font-semibold">Want to work with <?= APP_NAME ?>?</p>
          <div class="mt-3 flex flex-wrap gap-2">
            <a href="<?= $appUrl ?>/register/vendor" class="btn-outline btn-sm"><?= icon('store', 'w-3.5 h-3.5') ?>Sell as vendor</a>
            <a href="<?= $appUrl ?>/register/delivery" class="btn-outline btn-sm"><?= icon('bike', 'w-3.5 h-3.5') ?>Deliver with us</a>
          </div>
        </div>
      </div>
    </div>

    <p class="relative text-center text-xs text-charcoal-muted dark:text-gray-500">&copy; <?= date('Y') ?> <?= APP_NAME ?> · Errands, delivered.</p>
  </section>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';

// ── Dark mode (standalone) ────────────────────────────────────────────────
(function() {
  var html = document.documentElement;
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.js-dark-toggle')) return;
    var dark = html.classList.toggle('dark');
    document.cookie = 'td_dark=' + (dark ? '1' : '0') + '; path=/; max-age=31536000; SameSite=Lax';
    document.getElementById('theme-moon').classList.toggle('hidden', dark);
    document.getElementById('theme-sun').classList.toggle('hidden', !dark);
  });
})();

// ── Inline toast ──────────────────────────────────────────────────────────
function showToast(title, message, type) {
  const c = document.getElementById('toast-container');
  if (!c) return;
  const tiles = {
    success: ['check-circle', 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300'],
    error:   ['x-circle',     'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300'],
    info:    ['zap',          'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300'],
  };
  const [ic, tile] = tiles[type] || tiles.info;
  const el = document.createElement('div');
  el.className = 'pointer-events-auto flex items-start gap-3 bg-white/95 dark:bg-charcoal-light/95 backdrop-blur-xl border border-gray-200/80 dark:border-white/[0.08] rounded-2xl shadow-xl p-3.5 w-full animate-slide-in-right';
  el.innerHTML = `<span class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 ${tile}"><svg class="ico w-[18px] h-[18px]"><use href="#i-${ic}"/></svg></span>
    <div class="flex-1 min-w-0 pt-0.5">
      <p class="text-sm font-semibold text-charcoal dark:text-cream">${title}</p>
      ${message ? `<p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5">${message}</p>` : ''}
    </div>`;
  c.appendChild(el);
  setTimeout(() => el.remove(), 4000);
}

fetch(APP_URL + '/api/v1/buildings/index.php')
  .then(r => r.json())
  .then(data => {
    const sel = document.getElementById('building-select');
    sel.innerHTML = '<option value="">Select your building…</option>';
    (data.data || []).forEach(b => {
      sel.innerHTML += `<option value="${b.id}">${b.name}</option>`;
    });
  }).catch(() => {
    document.getElementById('building-select').innerHTML = '<option value="">Failed to load buildings</option>';
  });

document.getElementById('register-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('submit-btn');
  const spinner = document.getElementById('btn-spinner');
  const btnText = document.getElementById('btn-text');
  btn.disabled = true; spinner.classList.remove('hidden'); btnText.textContent = 'Creating…';
  const fd = new FormData(e.target);
  const body = Object.fromEntries(fd);
  body.building_id = parseInt(body.building_id);
  body.floor = parseInt(body.floor);
  try {
    const res = await fetch(APP_URL + '/api/v1/auth/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await res.json();
    if (data.status === 'success') {
      localStorage.setItem('td_token', data.data.token);
      localStorage.setItem('td_user', JSON.stringify(data.data.user));
      showToast('Account created!', 'Welcome to Lakun.', 'success');
      setTimeout(() => window.location.href = APP_URL + '/customer/dashboard', 800);
    } else {
      showToast(data.message || 'Registration failed', '', 'error');
      btn.disabled = false; spinner.classList.add('hidden'); btnText.textContent = 'Create account';
    }
  } catch {
    showToast('Network error. Try again.', '', 'error');
    btn.disabled = false; spinner.classList.add('hidden'); btnText.textContent = 'Create account';
  }
});
</script>
</body>
</html>
