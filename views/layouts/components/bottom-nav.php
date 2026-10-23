<?php
require_once __DIR__ . '/icons.php';
$role = $user['role'] ?? '';
$base = APP_URL;
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (!function_exists('mobileNavItem')):
function mobileNavItem(string $href, string $icon, string $label, bool $active): void {
    $state = $active
        ? 'text-amber-600 dark:text-amber-400'
        : 'text-gray-400 dark:text-gray-500 hover:text-charcoal dark:hover:text-cream';
    $pill = $active ? 'bg-amber-100/80 dark:bg-amber-500/15' : '';
    $aria = $active ? ' aria-current="page"' : '';
    echo "<a href=\"{$href}\" class=\"flex-1 flex flex-col items-center justify-center gap-1 transition-colors {$state}\"{$aria}>
            <span class=\"flex items-center justify-center w-12 h-8 rounded-full transition-colors {$pill}\">" . icon($icon, 'w-[21px] h-[21px]') . "</span>
            <span class=\"text-[10.5px] " . ($active ? 'font-bold' : 'font-medium') . " tracking-tight leading-none\">{$label}</span>
          </a>";
}
endif;
?>
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white/85 dark:bg-charcoal-light/85 backdrop-blur-xl backdrop-saturate-150 border-t border-gray-200/70 dark:border-white/[0.06] safe-bottom" aria-label="Primary">
  <div class="flex items-stretch h-[68px] max-w-lg mx-auto px-1">
    <?php if ($role === 'customer'): ?>
      <?php mobileNavItem($base.'/customer/dashboard', 'home',    'Home',   str_contains($path, '/customer/dashboard')) ?>
      <?php mobileNavItem($base.'/customer/nearby',    'map-pin', 'Nearby', str_contains($path, '/customer/nearby')) ?>
      <?php mobileNavItem($base.'/customer/order',     'cart',    'Order',  str_contains($path, '/customer/order') && !str_contains($path, '/orders')) ?>
      <?php mobileNavItem($base.'/customer/orders',    'list',    'Orders', str_contains($path, '/customer/orders') || str_contains($path, '/customer/track')) ?>
      <?php mobileNavItem($base.'/notifications',      'bell',    'Alerts', str_contains($path, '/notifications')) ?>
    <?php elseif ($role === 'vendor'): ?>
      <?php mobileNavItem($base.'/vendor/orders',  'zap',     'Requests', str_contains($path, '/vendor/orders')) ?>
      <?php mobileNavItem($base.'/vendor/history', 'history', 'History',  str_contains($path, '/vendor/history')) ?>
      <?php mobileNavItem($base.'/notifications',  'bell',    'Alerts',   str_contains($path, '/notifications')) ?>
      <?php mobileNavItem($base.'/profile',        'user',    'Profile',  str_contains($path, '/profile')) ?>
    <?php elseif ($role === 'delivery_agent'): ?>
      <?php mobileNavItem($base.'/delivery/tasks',    'bike',   'Tasks',    str_contains($path, '/delivery/task')) ?>
      <?php mobileNavItem($base.'/delivery/earnings', 'wallet', 'Earnings', str_contains($path, '/delivery/earnings')) ?>
      <?php mobileNavItem($base.'/delivery/credits',  'zap',    'Credits',  str_contains($path, '/delivery/credits')) ?>
      <?php mobileNavItem($base.'/notifications',     'bell',   'Alerts',   str_contains($path, '/notifications')) ?>
    <?php elseif ($role === 'admin'): ?>
      <?php mobileNavItem($base.'/admin',                 'dashboard', 'Overview', str_ends_with(rtrim($path, '/'), '/admin')) ?>
      <?php mobileNavItem($base.'/admin/orders',          'package',   'Orders',   str_contains($path, '/admin/orders')) ?>
      <?php mobileNavItem($base.'/admin/users',           'users',     'People',   str_contains($path, '/admin/users')) ?>
      <?php mobileNavItem($base.'/admin/financial-report','trending',  'Finance',  str_contains($path, '/admin/financial-report') || str_contains($path, '/admin/credit-packages')) ?>
      <?php mobileNavItem($base.'/admin/settings',        'settings',  'More',     str_contains($path, '/admin/settings') || str_contains($path, '/admin/buildings') || str_contains($path, '/profile')) ?>
    <?php endif; ?>
  </div>
</nav>
