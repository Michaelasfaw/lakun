<?php
define('APP_NAME', getenv('APP_NAME') ?: 'Lakun');

if (!defined('APP_URL')) {
  if (getenv('APP_URL')) {
    define('APP_URL', getenv('APP_URL'));
  } elseif (php_sapi_name() !== 'cli' && isset($_SERVER['HTTP_HOST'])) {
    $__protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $__host     = $_SERVER['HTTP_HOST'];
    $__docRoot  = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $__appRoot  = rtrim(str_replace('\\', '/', realpath(dirname(__DIR__, 2))), '/');
    $__relPath  = str_replace($__docRoot, '', $__appRoot);
    define('APP_URL', $__protocol . '://' . $__host . $__relPath);
    unset($__protocol, $__host, $__docRoot, $__appRoot, $__relPath);
  } else {
    define('APP_URL', 'http://localhost/apartment');
  }
}

define('APP_ENV',  getenv('APP_ENV')  ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

define('JWT_SECRET', getenv('JWT_SECRET') ?: 'fallback_secret_change_me');
define('JWT_EXPIRY', (int)(getenv('JWT_EXPIRY') ?: 86400));

define('SESSION_NAME', getenv('SESSION_NAME') ?: 'lakun_session');

// Roles
define('ROLE_CUSTOMER',        'customer');
define('ROLE_VENDOR',          'vendor');
define('ROLE_DELIVERY_AGENT',  'delivery_agent');
define('ROLE_ADMIN',           'admin');

// Order statuses — full PDF lifecycle
define('STATUS_REQUESTED',             'requested');
define('STATUS_SENT_TO_VENDORS',       'sent_to_vendors');
define('STATUS_BIDS_RECEIVED',         'bids_received');
define('STATUS_SENT_TO_CUSTOMER',      'sent_to_customer');
define('STATUS_AWAITING_PAYMENT',      'awaiting_payment');
define('STATUS_PAYMENT_UNDER_REVIEW',  'payment_under_review');
define('STATUS_CONFIRMED',             'confirmed');
define('STATUS_ASSIGNED',              'assigned');
define('STATUS_OUT_FOR_DELIVERY',      'out_for_delivery');
define('STATUS_DELIVERED',             'delivered');
define('STATUS_COMPLETED',             'completed');
define('STATUS_CANCELLED',             'cancelled');
define('STATUS_EXPIRED',               'expired');
define('STATUS_FAILED_PAYMENT',        'failed_payment');
// Legacy aliases (backward compat)
define('STATUS_BIDDING',               'bidding');
define('STATUS_AWAITING_CONFIRMATION', 'awaiting_confirmation');
define('STATUS_PICKED_UP',             'picked_up');
define('STATUS_ON_THE_WAY',            'on_the_way');

// Vendor categories
define('VENDOR_CAT_PHARMACY', 'pharmacy');
define('VENDOR_CAT_SHOP',     'shop');
define('VENDOR_CAT_LIQUOR',   'liquor');
define('VENDOR_CAT_FOOD',     'food');
define('VENDOR_CAT_GENERAL',  'general');

// Payment methods
define('PAYMENT_BANK',    'bank_transfer');
define('PAYMENT_TELEBIRR','telebirr');

// Notification types
define('NOTIF_NEW_ORDER',              'new_order');
define('NOTIF_BID_RECEIVED',           'bid_received');
define('NOTIF_BID_SELECTED',           'bid_selected');
define('NOTIF_ORDER_CONFIRMED',        'order_confirmed');
define('NOTIF_ORDER_ASSIGNED',         'order_assigned');
define('NOTIF_OUT_FOR_DELIVERY',       'out_for_delivery');
define('NOTIF_DELIVERED',              'delivered');
define('NOTIF_NO_BIDS',                'no_bids');
define('NOTIF_PAYMENT_UPLOADED',       'payment_uploaded');
define('NOTIF_PAYMENT_VERIFIED',       'payment_verified');
define('NOTIF_PAYMENT_REJECTED',       'payment_rejected');
define('NOTIF_NEW_ASSIGNMENT',         'new_assignment');
// Legacy
define('NOTIF_PICKED_UP',  'picked_up');
define('NOTIF_ON_THE_WAY', 'on_the_way');
