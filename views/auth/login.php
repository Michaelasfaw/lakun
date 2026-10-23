<?php
// APP_URL is defined by index.php (loads app/config/constants.php) before including this view
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
  <title>Sign in · <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= lakunAsset('assets/css/app.css') ?>">
  <style>
    .demo-card { transition: transform .15s ease, box-shadow .2s ease, border-color .15s ease, background-color .15s ease; }
    .demo-card:hover { transform: translateY(-2px); }
    .demo-card:active { transform: translateY(0) scale(.99); }
  </style>
</head>
<body class="min-h-screen bg-cream dark:bg-charcoal text-charcoal dark:text-cream font-sans">
<?php iconSprite(); ?>

<div id="toast-container" class="fixed top-4 right-4 left-4 sm:left-auto z-50 flex flex-col gap-2 sm:max-w-sm sm:w-full pointer-events-none"></div>

<div class="min-h-screen grid lg:grid-cols-[1.05fr_1fr]">

  <!-- ── Brand panel ─────────────────────────────────────────────────────── -->
  <section class="relative hidden lg:flex flex-col justify-between p-12 xl:p-14 ink-gradient text-white overflow-hidden">
    <div class="absolute inset-0 bg-grid opacity-40 [mask-image:radial-gradient(ellipse_at_top_right,black_20%,transparent_70%)]" aria-hidden="true"></div>
    <div class="absolute -bottom-40 -left-24 w-[520px] h-[520px] rounded-full bg-amber-500/20 blur-3xl" aria-hidden="true"></div>

    <div class="relative flex items-center gap-3">
      <span class="brand-mark w-11 h-11 rounded-2xl"><?= icon('logo', 'w-6 h-6') ?></span>
      <span class="text-xl font-extrabold tracking-tight"><?= APP_NAME ?></span>
    </div>

    <div class="relative max-w-xl">
      <p class="inline-flex items-center gap-2 rounded-full bg-white/[0.06] border border-white/10 px-3 py-1.5 text-xs font-semibold text-amber-200">
        <span class="relative flex w-2 h-2"><span class="absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75 animate-ping-slow"></span><span class="relative inline-flex rounded-full w-2 h-2 bg-amber-400"></span></span>
        Shops are bidding near you right now
      </p>
      <h1 class="mt-6 text-[2.75rem] xl:text-[3.25rem] font-extrabold leading-[1.05] tracking-tightest text-balance">
        Everything you need, at your door in
        <span class="bg-gradient-to-r from-amber-300 via-amber-400 to-orange-400 bg-clip-text text-transparent">minutes.</span>
      </h1>
      <p class="mt-5 text-[15px] leading-relaxed text-white/60 max-w-md">
        Tell us what you need. Nearby shops compete on price, you pick the best offer, and a rider brings it up to your floor.
      </p>

      <!-- Floating product preview -->
      <div class="relative mt-10 h-[210px]">
        <div class="absolute left-0 top-0 w-[300px] rounded-2xl bg-white text-charcoal p-4 shadow-2xl animate-float" style="animation-delay:-2s">
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
        <div class="absolute left-[270px] top-[96px] w-[250px] rounded-2xl bg-ink-700/90 backdrop-blur border border-white/10 p-4 shadow-2xl animate-float">
          <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-sky-500/20 text-sky-300 flex items-center justify-center"><?= icon('truck') ?></span>
            <div>
              <p class="text-sm font-bold">On the way</p>
              <p class="text-xs text-white/50">Arriving at Block A · Floor 5</p>
            </div>
          </div>
          <div class="mt-3 h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full w-3/4 rounded-full bg-gradient-to-r from-amber-400 to-orange-500"></div></div>
        </div>
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

  <!-- ── Sign-in panel ───────────────────────────────────────────────────── -->
  <section class="relative flex flex-col px-5 sm:px-10 py-6 sm:py-10">
    <div class="pointer-events-none absolute inset-0 overflow-hidden lg:hidden" aria-hidden="true">
      <div class="absolute -top-32 -right-24 w-80 h-80 rounded-full bg-amber-300/30 dark:bg-amber-500/10 blur-3xl"></div>
    </div>

    <div class="relative flex items-center justify-between">
      <div class="flex items-center gap-2.5 lg:invisible">
        <span class="brand-mark w-9 h-9"><?= icon('logo', 'w-5 h-5') ?></span>
        <span class="text-lg font-extrabold tracking-tight"><?= APP_NAME ?></span>
      </div>
      <button class="js-dark-toggle icon-btn border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/[0.04]" aria-label="Toggle dark mode">
        <span id="theme-moon" class="<?= $isDark ? 'hidden' : '' ?>"><?= icon('moon', 'w-[18px] h-[18px]') ?></span>
        <span id="theme-sun" class="<?= $isDark ? '' : 'hidden' ?>"><?= icon('sun', 'w-[18px] h-[18px] text-amber-400') ?></span>
      </button>
    </div>

    <div class="relative flex-1 flex items-center justify-center py-8">
      <div class="w-full max-w-[420px] animate-fade-slide-up">
        <h2 class="text-[1.85rem] font-extrabold tracking-tight">Welcome back</h2>
        <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5">Sign in with your phone number to continue.</p>

        <form id="login-form" class="mt-7 space-y-4" autocomplete="on">
          <div>
            <label class="label" for="phone-input">Phone number</label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"><?= icon('phone', 'w-[18px] h-[18px]') ?></span>
              <input type="tel" id="phone-input" name="phone" placeholder="09 1122 3344" required autocomplete="tel" class="input pl-11 py-3.5">
            </div>
          </div>
          <div>
            <div class="flex items-center justify-between">
              <label class="label" for="pw-input">Password</label>
            </div>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"><?= icon('lock', 'w-[18px] h-[18px]') ?></span>
              <input type="password" id="pw-input" name="password" placeholder="Enter your password" required autocomplete="current-password" class="input pl-11 pr-12 py-3.5">
              <button type="button" tabindex="-1" aria-label="Show password"
                      onclick="const i=document.getElementById('pw-input');i.type=i.type==='password'?'text':'password'"
                      class="absolute right-2 top-1/2 -translate-y-1/2 p-2 rounded-lg text-gray-400 hover:text-charcoal dark:hover:text-cream transition-colors">
                <?= icon('eye', 'w-[18px] h-[18px]') ?>
              </button>
            </div>
          </div>

          <button type="submit" id="submit-btn" class="btn-brand btn-lg w-full !mt-6">
            <span id="btn-text">Sign in</span>
            <svg id="btn-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span id="btn-arrow"><?= icon('arrow-right', 'w-4 h-4') ?></span>
          </button>
        </form>

        <!-- Demo accounts -->
        <div class="mt-8">
          <div class="flex items-center gap-3 mb-3">
            <div class="flex-1 h-px bg-gray-200 dark:bg-white/[0.08]"></div>
            <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-charcoal-muted dark:text-gray-500">Explore a demo account</span>
            <div class="flex-1 h-px bg-gray-200 dark:bg-white/[0.08]"></div>
          </div>
          <div class="grid grid-cols-2 gap-2.5">
            <?php
            $demos = [
              ['08011111111', 'password123', 'Customer', 'Amara · Orders', 'bag',    'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300'],
              ['08055555555', 'password123', 'Vendor',   'Tunde Mart',     'store',  'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300'],
              ['08088888888', 'password123', 'Delivery', 'Bola · Rider',   'bike',   'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300'],
              ['08000000000', 'admin123',    'Admin',    'Operations',     'shield', 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300'],
            ];
            foreach ($demos as [$ph, $pw, $role, $who, $ic, $tile]): ?>
            <button type="button" onclick="demoLogin('<?= $ph ?>','<?= $pw ?>')"
                    class="demo-card group flex items-center gap-3 text-left rounded-2xl p-3 bg-white dark:bg-white/[0.03] border border-gray-200/80 dark:border-white/[0.07] hover:border-amber-400/60 dark:hover:border-amber-500/40 hover:shadow-md">
              <span class="icon-tile <?= $tile ?>"><?= icon($ic, 'w-[18px] h-[18px]') ?></span>
              <span class="min-w-0 flex-1">
                <span class="block text-[13px] font-bold"><?= $role ?></span>
                <span class="block text-xs text-charcoal-muted dark:text-gray-400 truncate"><?= $who ?></span>
              </span>
              <?= icon('arrow-right', 'w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-amber-500 group-hover:translate-x-0.5 transition-all') ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="mt-8 rounded-2xl bg-gray-100/70 dark:bg-white/[0.03] border border-gray-200/60 dark:border-white/[0.06] p-4">
          <p class="text-[13px] font-semibold">New to <?= APP_NAME ?>?</p>
          <div class="mt-3 flex flex-wrap gap-2">
            <a href="<?= $appUrl ?>/register/customer" class="btn-outline btn-sm"><?= icon('bag', 'w-3.5 h-3.5') ?>Join as customer</a>
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

// ── Dark Mode (standalone, no app.js) ─────────────────────────────────────
(function() {
  var html = document.documentElement;
  function sync(dark) {
    document.getElementById('theme-moon').classList.toggle('hidden', dark);
    document.getElementById('theme-sun').classList.toggle('hidden', !dark);
  }
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.js-dark-toggle')) return;
    var dark = html.classList.toggle('dark');
    document.cookie = 'td_dark=' + (dark ? '1' : '0') + '; path=/; max-age=31536000; SameSite=Lax';
    sync(dark);
  });
})();

// ── Inline toast (no app.js dependency at login) ──────────────────────────
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

// ── Demo login ────────────────────────────────────────────────────────────
function demoLogin(phone, password) {
  document.getElementById('phone-input').value = phone;
  document.getElementById('pw-input').value    = password;
  // Small visual delay so user sees the fields fill before submit
  setTimeout(() => document.getElementById('login-form').requestSubmit(), 250);
}

// ── Sign-in form ──────────────────────────────────────────────────────────
document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn     = document.getElementById('submit-btn');
  const spinner = document.getElementById('btn-spinner');
  const arrow   = document.getElementById('btn-arrow');
  const btnText = document.getElementById('btn-text');
  const reset = () => {
    btn.disabled = false;
    spinner.classList.add('hidden');
    arrow.classList.remove('hidden');
    btnText.textContent = 'Sign in';
  };
  btn.disabled = true;
  spinner.classList.remove('hidden');
  arrow.classList.add('hidden');
  btnText.textContent = 'Signing in…';

  const phone    = document.getElementById('phone-input').value.trim();
  const password = document.getElementById('pw-input').value;

  try {
    const res = await fetch(APP_URL + '/api/v1/auth/login.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ phone, password }),
    });
    const data = await res.json();

    if (data.status === 'success') {
      localStorage.setItem('td_token', data.data.token);
      localStorage.setItem('td_user',  JSON.stringify(data.data.user));
      showToast('Welcome back!', 'Taking you to your dashboard…', 'success');
      const role = data.data.user.role;
      const dest = role === 'vendor' ? '/vendor/orders'
                 : role === 'delivery_agent' ? '/delivery/tasks'
                 : role === 'admin' ? '/admin'
                 : '/customer/dashboard';
      setTimeout(() => window.location.href = APP_URL + dest, 700);
    } else {
      showToast(data.message || 'Sign in failed', 'Check your phone number and password.', 'error');
      reset();
    }
  } catch {
    showToast('Network error', 'Could not reach the server. Please try again.', 'error');
    reset();
  }
});
</script>
</body>
</html>
