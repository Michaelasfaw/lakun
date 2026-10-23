<?php
require_once __DIR__ . '/app/config/db.php';
require_once __DIR__ . '/app/config/constants.php';
require_once __DIR__ . '/views/layouts/components/icons.php';

session_name(SESSION_NAME);
session_start();

$user = $_SESSION['user'] ?? null;

$requestUri = $_SERVER['REQUEST_URI'];
$basePath   = rtrim(parse_url(APP_URL, PHP_URL_PATH), '/');
$path       = parse_url($requestUri, PHP_URL_PATH);
$path       = rtrim(str_replace($basePath, '', $path), '/') ?: '/';

// Auth guard helpers
function requireLogin(?array $user, string $redirectTo = '/login'): void {
    if (!$user) { header('Location: ' . APP_URL . $redirectTo); exit; }
}

function requireRole(?array $user, string $role): void {
    requireLogin($user);
    if ($user['role'] !== $role && $user['role'] !== ROLE_ADMIN) {
        header('Location: ' . APP_URL . '/login'); exit;
    }
}

function requireAdmin(?array $user): void {
    requireLogin($user);
    if ($user['role'] !== ROLE_ADMIN) {
        header('Location: ' . APP_URL . '/login'); exit;
    }
}

function renderView(string $view, array $data = []): void {
    extract($data);
    $viewFile = __DIR__ . '/views/' . $view . '.php';
    if (!file_exists($viewFile)) {
        http_response_code(404);
        echo '<h1>404 — Page not found</h1>';
        return;
    }
    require $viewFile;
}

// Root redirect
if ($path === '/' || $path === '') {
    if ($user) {
        $redirect = match($user['role']) {
            'customer'       => '/customer/dashboard',
            'vendor'         => '/vendor/orders',
            'delivery_agent' => '/delivery/tasks',
            'admin'          => '/admin',
            default          => '/login',
        };
        header('Location: ' . APP_URL . $redirect); exit;
    }
    renderView('auth/login', ['user' => $user]); exit;
}

match(true) {

    // ── Auth ──────────────────────────────────────────────────────────────────
    $path === '/login' => (function() use ($user) {
        if ($user) {
            $redirect = match($user['role']) {
                'customer'       => '/customer/dashboard',
                'vendor'         => '/vendor/orders',
                'delivery_agent' => '/delivery/tasks',
                'admin'          => '/admin',
                default          => '/login',
            };
            header('Location: ' . APP_URL . $redirect); exit;
        }
        renderView('auth/login', ['user' => $user]);
    })(),

    $path === '/register/customer' => (function() use ($user) {
        if ($user) { header('Location: ' . APP_URL . '/'); exit; }
        renderView('auth/register-customer', ['user' => $user]);
    })(),

    $path === '/register/vendor' => (function() use ($user) {
        if ($user) { header('Location: ' . APP_URL . '/'); exit; }
        renderView('auth/register-vendor', ['user' => $user]);
    })(),

    $path === '/register/delivery' => (function() use ($user) {
        if ($user) { header('Location: ' . APP_URL . '/'); exit; }
        renderView('auth/register-delivery', ['user' => $user]);
    })(),

    $path === '/logout' => (function() {
        session_destroy();
        header('Location: ' . APP_URL . '/login'); exit;
    })(),

    // ── Customer ──────────────────────────────────────────────────────────────
    $path === '/customer/dashboard' => (function() use ($user) {
        requireRole($user, ROLE_CUSTOMER);
        renderView('customer/dashboard', ['user' => $user]);
    })(),

    $path === '/customer/order' => (function() use ($user) {
        requireRole($user, ROLE_CUSTOMER);
        renderView('customer/create-order', ['user' => $user]);
    })(),

    $path === '/customer/orders' => (function() use ($user) {
        requireRole($user, ROLE_CUSTOMER);
        renderView('customer/my-orders', ['user' => $user]);
    })(),

    str_starts_with($path, '/customer/track/') => (function() use ($user, $path) {
        requireRole($user, ROLE_CUSTOMER);
        $orderId = (int)str_replace('/customer/track/', '', $path);
        renderView('customer/track', ['user' => $user, 'orderId' => $orderId]);
    })(),

    $path === '/customer/nearby' => (function() use ($user) {
        requireRole($user, ROLE_CUSTOMER);
        renderView('customer/nearby-shops', ['user' => $user]);
    })(),

    // ── Vendor ────────────────────────────────────────────────────────────────
    $path === '/vendor/orders' => (function() use ($user) {
        requireRole($user, ROLE_VENDOR);
        renderView('vendor/orders', ['user' => $user]);
    })(),

    $path === '/vendor/history' => (function() use ($user) {
        requireRole($user, ROLE_VENDOR);
        renderView('vendor/history', ['user' => $user]);
    })(),

    // ── Delivery Agent ────────────────────────────────────────────────────────
    $path === '/delivery/tasks' => (function() use ($user) {
        requireRole($user, ROLE_DELIVERY_AGENT);
        renderView('delivery/tasks', ['user' => $user]);
    })(),

    $path === '/delivery/credits' => (function() use ($user) {
        requireRole($user, ROLE_DELIVERY_AGENT);
        renderView('delivery/credits', ['user' => $user]);
    })(),

    $path === '/delivery/earnings' => (function() use ($user) {
        requireRole($user, ROLE_DELIVERY_AGENT);
        renderView('delivery/earnings', ['user' => $user]);
    })(),

    str_starts_with($path, '/delivery/task/') => (function() use ($user, $path) {
        requireRole($user, ROLE_DELIVERY_AGENT);
        $orderId = (int)str_replace('/delivery/task/', '', $path);
        renderView('delivery/task-detail', ['user' => $user, 'orderId' => $orderId]);
    })(),

    // ── Shared ────────────────────────────────────────────────────────────────
    $path === '/notifications' => (function() use ($user) {
        requireLogin($user);
        renderView('shared/notifications', ['user' => $user]);
    })(),

    $path === '/profile' => (function() use ($user) {
        requireLogin($user);
        renderView('shared/profile', ['user' => $user]);
    })(),

    // ── Admin ─────────────────────────────────────────────────────────────────
    $path === '/admin' => (function() use ($user) {
        requireAdmin($user);
        renderView('admin/dashboard', ['user' => $user]);
    })(),

    $path === '/admin/orders' => (function() use ($user) {
        requireAdmin($user);
        renderView('admin/orders', ['user' => $user]);
    })(),

    $path === '/admin/users' => (function() use ($user) {
        requireAdmin($user);
        renderView('admin/users', ['user' => $user]);
    })(),

    $path === '/admin/buildings' => (function() use ($user) {
        requireAdmin($user);
        renderView('admin/buildings', ['user' => $user]);
    })(),

    $path === '/admin/settings' => (function() use ($user) {
        requireAdmin($user);
        renderView('admin/settings', ['user' => $user]);
    })(),

    $path === '/admin/credit-packages' => (function() use ($user) {
        requireAdmin($user);
        renderView('admin/credit-packages', ['user' => $user]);
    })(),

    $path === '/admin/financial-report' => (function() use ($user) {
        requireAdmin($user);
        renderView('admin/financial-report', ['user' => $user]);
    })(),

    default => (function() use ($user) {
        http_response_code(404);
        renderView('auth/login', ['user' => $user]);
    })(),
};
