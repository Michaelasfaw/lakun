<?php
require_once __DIR__ . '/components/icons.php';
$__role      = $user['role'] ?? '';
$__roleLabel = ['customer' => 'Customer', 'vendor' => 'Vendor', 'delivery_agent' => 'Delivery Partner', 'admin' => 'Administrator'][$__role] ?? 'Guest';
$__name      = $user['name'] ?? '';
$__initials  = strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), array_slice(preg_split('/\s+/', trim($__name ?: 'U')), 0, 2))));
$__avatarGradient = [
  'customer'       => 'from-sky-400 to-indigo-500',
  'vendor'         => 'from-amber-400 to-orange-600',
  'delivery_agent' => 'from-emerald-400 to-teal-600',
  'admin'          => 'from-fuchsia-500 to-violet-600',
][$__role] ?? 'from-gray-400 to-gray-600';
$__isDark = isset($_COOKIE['td_dark']) && $_COOKIE['td_dark'] === '1';
?>
<!DOCTYPE html>
<html lang="en" class="<?= $__isDark ? 'dark' : '' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="<?= $__isDark ? '#121110' : '#F7F5F0' ?>">
  <title><?= htmlspecialchars($pageTitle ?? 'Home') ?> · <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= lakunAsset('assets/css/app.css') ?>">
  <script src="<?= lakunAsset('assets/js/ui.js') ?>"></script>
</head>
<body class="min-h-screen bg-cream dark:bg-charcoal text-charcoal dark:text-cream font-sans">
<?php iconSprite(); ?>

<!-- Ambient background -->
<div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
  <div class="absolute -top-40 right-[-10%] w-[640px] h-[640px] rounded-full bg-amber-300/20 dark:bg-amber-500/[0.07] blur-3xl"></div>
  <div class="absolute top-1/3 -left-40 w-[420px] h-[420px] rounded-full bg-orange-200/20 dark:bg-orange-500/[0.04] blur-3xl"></div>
</div>

<!-- Toasts -->
<div id="toast-container" class="fixed top-4 right-4 left-4 sm:left-auto z-[60] flex flex-col gap-2 sm:max-w-sm sm:w-full pointer-events-none"></div>

<!-- ── Sidebar (desktop) ─────────────────────────────────────────────────── -->
<div class="lg:flex min-h-screen">
<aside id="sidebar" class="hidden lg:flex flex-col w-[264px] flex-shrink-0 relative z-30 ink-gradient text-white border-r border-black/20">
 <!-- Brand + menu stay pinned to the top while the page scrolls -->
 <div class="sticky top-0 flex flex-col">
  <div class="px-5 h-[72px] flex items-center gap-3 flex-shrink-0">
    <span class="brand-mark w-9 h-9"><?= icon('logo', 'w-5 h-5') ?></span>
    <div class="leading-tight">
      <p class="text-[17px] font-extrabold tracking-tight"><?= APP_NAME ?></p>
      <p class="text-[11px] font-medium text-white/40">Errands, delivered</p>
    </div>
  </div>

  <?php require __DIR__ . '/components/navbar.php'; ?>
 </div>

 <!-- Account card always sits at the bottom of the sidebar -->
 <div class="mt-auto">
  <div class="p-3 space-y-2">
    <?php if ($__role === 'delivery_agent'): ?>
    <a href="<?= APP_URL ?>/delivery/credits" class="group block rounded-2xl p-4 bg-gradient-to-br from-amber-500/20 to-amber-500/[0.04] border border-amber-400/20 hover:border-amber-400/40 transition-colors">
      <div class="flex items-center justify-between">
        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-amber-300/80">Task credits</p>
        <?= icon('zap', 'w-4 h-4 text-amber-300') ?>
      </div>
      <p class="mt-2 flex items-baseline gap-1.5"><span id="sidebar-credits" class="text-3xl font-extrabold text-white num">—</span><span class="text-xs text-white/50">remaining</span></p>
      <p class="mt-1 text-xs text-white/50 group-hover:text-white/70 transition-colors inline-flex items-center gap-1">Top up credits <?= icon('arrow-right', 'w-3 h-3') ?></p>
    </a>
    <?php endif; ?>
    <div class="flex items-center gap-3 rounded-2xl p-2.5 bg-white/[0.04] border border-white/[0.06]">
      <div class="w-9 h-9 rounded-xl bg-gradient-to-br <?= $__avatarGradient ?> flex items-center justify-center text-white font-bold text-[13px] shadow-md"><?= htmlspecialchars($__initials) ?></div>
      <div class="flex-1 min-w-0">
        <p class="text-[13px] font-semibold truncate"><?= htmlspecialchars($__name) ?></p>
        <p class="text-[11px] text-white/45"><?= $__roleLabel ?></p>
      </div>
      <a href="<?= APP_URL ?>/logout" class="p-2 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition-colors" title="Sign out" aria-label="Sign out"><?= icon('logout', 'w-4 h-4') ?></a>
    </div>
  </div>
 </div>
</aside>

<!-- ── Main ──────────────────────────────────────────────────────────────── -->
<main class="flex-1 min-w-0 min-h-screen flex flex-col">
  <header class="sticky top-0 z-20 bg-cream/75 dark:bg-charcoal/75 backdrop-blur-xl backdrop-saturate-150 border-b border-gray-200/60 dark:border-white/[0.05]">
    <div class="h-16 lg:h-[72px] px-3 sm:px-4 md:px-8 lg:px-10 flex items-center justify-between gap-3">
      <div class="flex items-center gap-2 min-w-0">
        <button id="mobile-menu-btn" onclick="openDrawer()" class="icon-btn lg:hidden" aria-label="Open navigation menu" aria-expanded="false" aria-controls="mobile-drawer">
          <?= icon('menu') ?>
        </button>
        <span class="brand-mark w-8 h-8 lg:hidden rounded-[10px]"><?= icon('logo', 'w-[18px] h-[18px]') ?></span>
        <div class="min-w-0 ml-1 lg:ml-0">
          <p class="hidden lg:block text-[11px] font-semibold uppercase tracking-[0.14em] text-charcoal-muted dark:text-gray-500"><?= $__roleLabel ?></p>
          <p class="text-[15px] lg:text-base font-bold tracking-tight truncate"><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></p>
        </div>
      </div>
      <div class="flex items-center gap-1 sm:gap-1.5">
        <button class="js-dark-toggle icon-btn" title="Toggle theme" aria-label="Toggle dark mode">
          <span id="icon-moon" class="<?= $__isDark ? 'hidden' : '' ?>"><?= icon('moon') ?></span>
          <span id="icon-sun" class="<?= $__isDark ? '' : 'hidden' ?>"><?= icon('sun', 'text-amber-400') ?></span>
        </button>
        <a href="<?= APP_URL ?>/notifications" class="icon-btn relative" aria-label="Notifications">
          <?= icon('bell') ?>
          <span id="notif-badge" class="absolute top-1.5 right-1.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold ring-2 ring-cream dark:ring-charcoal hidden">0</span>
        </a>
        <a href="<?= APP_URL ?>/profile" class="hidden sm:flex items-center gap-2.5 ml-1 pl-1.5 pr-1.5 lg:pr-3 py-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-white/[0.06] transition-colors">
          <span class="w-8 h-8 rounded-[10px] bg-gradient-to-br <?= $__avatarGradient ?> flex items-center justify-center text-white font-bold text-xs"><?= htmlspecialchars($__initials) ?></span>
          <span class="hidden lg:block text-[13px] font-semibold max-w-[140px] truncate"><?= htmlspecialchars(explode(' ', $__name)[0] ?? '') ?></span>
        </a>
      </div>
    </div>
  </header>

  <div class="flex-1 px-4 md:px-8 lg:px-10 py-6 md:py-8 pb-28 lg:pb-12 animate-fade-slide-up">
    <?= $content ?? '' ?>
  </div>
</main>
</div>

<!-- ── Bottom Nav (mobile) ────────────────────────────────────────────────── -->
<?php require __DIR__ . '/components/bottom-nav.php'; ?>

<!-- ── Mobile Drawer ─────────────────────────────────────────────────────── -->
<div id="drawer-overlay" class="lg:hidden fixed inset-0 z-40 bg-charcoal/60 backdrop-blur-sm opacity-0 pointer-events-none" onclick="closeDrawer()" aria-hidden="true"></div>
<aside id="mobile-drawer" class="lg:hidden fixed top-0 left-0 h-full w-[290px] max-w-[85vw] ink-gradient text-white z-50 shadow-2xl flex flex-col -translate-x-full" aria-label="Navigation drawer" role="dialog" aria-modal="true">
  <div class="flex items-center justify-between px-5 h-16 flex-shrink-0">
    <div class="flex items-center gap-3">
      <span class="brand-mark w-9 h-9"><?= icon('logo', 'w-5 h-5') ?></span>
      <span class="text-[17px] font-extrabold tracking-tight"><?= APP_NAME ?></span>
    </div>
    <button onclick="closeDrawer()" class="p-2 rounded-xl text-white/60 hover:text-white hover:bg-white/10 transition-colors" aria-label="Close navigation menu"><?= icon('x') ?></button>
  </div>

  <?php require __DIR__ . '/components/navbar.php'; ?>

  <div class="p-3 space-y-2 flex-shrink-0" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))">
    <?php if ($__role === 'delivery_agent'): ?>
    <a href="<?= APP_URL ?>/delivery/credits" onclick="closeDrawer()" class="flex items-center justify-between rounded-2xl px-4 py-3 bg-gradient-to-br from-amber-500/20 to-amber-500/[0.04] border border-amber-400/20">
      <span class="flex items-center gap-2 text-sm font-semibold text-amber-200"><?= icon('zap', 'w-4 h-4') ?> Task credits</span>
      <span id="drawer-credits" class="text-xl font-extrabold num">—</span>
    </a>
    <?php endif; ?>
    <div class="flex items-center gap-3 rounded-2xl p-2.5 bg-white/[0.04] border border-white/[0.06]">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-br <?= $__avatarGradient ?> flex items-center justify-center text-white font-bold text-sm"><?= htmlspecialchars($__initials) ?></div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold truncate"><?= htmlspecialchars($__name) ?></p>
        <p class="text-xs text-white/45"><?= $__roleLabel ?></p>
      </div>
      <a href="<?= APP_URL ?>/logout" class="p-2 rounded-lg text-red-300/80 hover:text-red-200 hover:bg-red-500/10 transition-colors" aria-label="Sign out"><?= icon('logout', 'w-[18px] h-[18px]') ?></a>
    </div>
  </div>
</aside>

<script>window.APP_URL = '<?= APP_URL ?>';</script>
<script src="<?= lakunAsset('assets/js/app.js') ?>"></script>
<script>
(function() {
  const drawer  = document.getElementById('mobile-drawer');
  const overlay = document.getElementById('drawer-overlay');
  const menuBtn = document.getElementById('mobile-menu-btn');
  let open = false;

  window.openDrawer = function() {
    open = true;
    drawer.style.transform = 'translateX(0)';
    overlay.style.opacity = '1';
    overlay.style.pointerEvents = 'auto';
    document.body.style.overflow = 'hidden';
    if (menuBtn) menuBtn.setAttribute('aria-expanded', 'true');
    setTimeout(() => drawer.querySelector('a, button')?.focus(), 50);
  };
  window.closeDrawer = function() {
    open = false;
    drawer.style.transform = 'translateX(-100%)';
    overlay.style.opacity = '0';
    overlay.style.pointerEvents = 'none';
    document.body.style.overflow = '';
    if (menuBtn) { menuBtn.setAttribute('aria-expanded', 'false'); menuBtn.focus(); }
  };
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && open) closeDrawer(); });

  let touchStartX = 0;
  drawer.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
  drawer.addEventListener('touchend', e => { if (e.changedTouches[0].clientX - touchStartX < -60) closeDrawer(); }, { passive: true });
})();
</script>
<?php if (isset($extraJs)) echo $extraJs; ?>
</body>
</html>
