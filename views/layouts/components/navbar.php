<?php
require_once __DIR__ . '/icons.php';
$role = $user['role'] ?? '';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(parse_url(APP_URL, PHP_URL_PATH), '/');

if (!function_exists('navLink')):
function navLink(string $href, string $label, string $icon, string $currentPath, string $basePath, bool $exact = false): void {
    $fullHref = $basePath . $href;
    $active = $exact ? rtrim($currentPath, '/') === $fullHref : str_starts_with($currentPath, $fullHref);
    $cls = $active ? 'nav-link is-active' : 'nav-link';
    $aria = $active ? ' aria-current="page"' : '';
    echo "<a href=\"{$fullHref}\" class=\"{$cls}\"{$aria}>" . icon($icon) . "<span>{$label}</span></a>";
}
endif;
?>
<nav class="flex-1 px-3 pb-3 flex flex-col gap-0.5 overflow-y-auto scrollbar-none">
  <p class="nav-section">Menu</p>
  <?php if ($role === 'customer'): ?>
    <?php navLink('/customer/dashboard', 'Home',         'home',    $currentPath, $basePath) ?>
    <?php navLink('/customer/order',     'New order',    'cart',    $currentPath, $basePath, true) ?>
    <?php navLink('/customer/orders',    'My orders',    'list',    $currentPath, $basePath) ?>
    <?php navLink('/customer/nearby',    'Nearby shops', 'map-pin', $currentPath, $basePath) ?>

  <?php elseif ($role === 'vendor'): ?>
    <?php navLink('/vendor/orders',  'Order requests', 'zap',     $currentPath, $basePath) ?>
    <?php navLink('/vendor/history', 'Bid history',    'history', $currentPath, $basePath) ?>

  <?php elseif ($role === 'delivery_agent'): ?>
    <?php navLink('/delivery/tasks',    'Deliveries', 'bike',   $currentPath, $basePath) ?>
    <?php navLink('/delivery/earnings', 'Earnings',   'wallet', $currentPath, $basePath) ?>
    <?php navLink('/delivery/credits',  'Credits',    'zap',    $currentPath, $basePath) ?>

  <?php elseif ($role === 'admin'): ?>
    <?php navLink('/admin',                  'Overview',  'dashboard', $currentPath, $basePath, true) ?>
    <?php navLink('/admin/orders',           'Orders',    'package',   $currentPath, $basePath) ?>
    <?php navLink('/admin/users',            'People',    'users',     $currentPath, $basePath) ?>
    <?php navLink('/admin/buildings',        'Buildings', 'building',  $currentPath, $basePath) ?>
    <p class="nav-section">Finance</p>
    <?php navLink('/admin/financial-report', 'Reports',   'trending',  $currentPath, $basePath) ?>
    <?php navLink('/admin/credit-packages',  'Credit packages', 'coins', $currentPath, $basePath) ?>
    <?php navLink('/admin/settings',         'Settings',  'settings',  $currentPath, $basePath) ?>
  <?php endif; ?>

  <p class="nav-section">Account</p>
  <?php navLink('/notifications', 'Notifications', 'bell', $currentPath, $basePath) ?>
  <?php navLink('/profile',       'Profile',       'user', $currentPath, $basePath) ?>
  <button class="js-dark-toggle nav-link w-full text-left" aria-label="Toggle dark mode">
    <span class="js-theme-moon"><?= icon('moon') ?></span><span class="js-theme-sun hidden"><?= icon('sun') ?></span>
    <span class="js-theme-label">Dark mode</span>
  </button>
  <a href="<?= APP_URL ?>/logout" class="nav-link hover:!text-red-300"><?= icon('logout') ?><span>Sign out</span></a>
</nav>
