/**
 * Lakun — UI helpers (loaded in <head> so inline page scripts can use them).
 * Exposes: icon(name, cls) and window.LK = { status, statusBadge, money, initials, avatar, timeAgo, empty }
 */
(function (w) {
  'use strict';

  function icon(name, cls) {
    return '<svg class="ico ' + (cls || '') + '" aria-hidden="true"><use href="#i-' + name + '"/></svg>';
  }

  var esc = function (s) {
    return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  };

  // Tone → badge classes (kept literal so Tailwind picks them up)
  var TONES = {
    gray:    'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-500/15 dark:bg-white/[0.06] dark:text-gray-300 dark:ring-white/10',
    blue:    'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/15 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-400/20',
    indigo:  'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/15 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-400/20',
    violet:  'bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-600/15 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-400/20',
    amber:   'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/20',
    orange:  'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/15 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-400/20',
    emerald: 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/15 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/20',
    teal:    'bg-teal-50 text-teal-700 ring-1 ring-inset ring-teal-600/15 dark:bg-teal-500/10 dark:text-teal-300 dark:ring-teal-400/20',
    sky:     'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/15 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-400/20',
    red:     'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/15 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-400/20',
  };

  // Accent colour per tone (left bars, dots, icon tiles)
  var ACCENTS = {
    gray:    { bar: 'bg-gray-300 dark:bg-gray-600',   tile: 'bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300' },
    blue:    { bar: 'bg-blue-500',    tile: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300' },
    indigo:  { bar: 'bg-indigo-500',  tile: 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300' },
    violet:  { bar: 'bg-violet-500',  tile: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300' },
    amber:   { bar: 'bg-amber-500',   tile: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300' },
    orange:  { bar: 'bg-orange-500',  tile: 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-300' },
    emerald: { bar: 'bg-emerald-500', tile: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300' },
    teal:    { bar: 'bg-teal-500',    tile: 'bg-teal-50 text-teal-600 dark:bg-teal-500/10 dark:text-teal-300' },
    sky:     { bar: 'bg-sky-500',     tile: 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300' },
    red:     { bar: 'bg-red-500',     tile: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300' },
  };

  var STATUS = {
    requested:             { label: 'Requested',          tone: 'gray',    icon: 'clock' },
    sent_to_vendors:       { label: 'Collecting prices',  tone: 'blue',    icon: 'hourglass' },
    bidding:               { label: 'Collecting prices',  tone: 'blue',    icon: 'hourglass' },
    bids_received:         { label: 'Prices received',    tone: 'indigo',  icon: 'tag' },
    sent_to_customer:      { label: 'Choose a price',     tone: 'amber',   icon: 'tag' },
    awaiting_confirmation: { label: 'Choose a price',     tone: 'amber',   icon: 'tag' },
    awaiting_payment:      { label: 'Awaiting payment',   tone: 'amber',   icon: 'card' },
    payment_under_review:  { label: 'Verifying payment',  tone: 'orange',  icon: 'shield' },
    confirmed:             { label: 'Confirmed',          tone: 'emerald', icon: 'check-circle' },
    vendor_paid:           { label: 'Vendor paid',        tone: 'emerald', icon: 'check-circle' },
    assigned:              { label: 'Rider assigned',     tone: 'teal',    icon: 'bike' },
    picked_up:             { label: 'Picked up',          tone: 'sky',     icon: 'package' },
    out_for_delivery:      { label: 'On the way',         tone: 'sky',     icon: 'truck' },
    on_the_way:            { label: 'On the way',         tone: 'sky',     icon: 'truck' },
    delivered:             { label: 'Delivered',          tone: 'emerald', icon: 'check' },
    completed:             { label: 'Completed',          tone: 'gray',    icon: 'check-circle' },
    cancelled:             { label: 'Cancelled',          tone: 'red',     icon: 'x-circle' },
    expired:               { label: 'Expired',            tone: 'red',     icon: 'clock' },
    failed_payment:        { label: 'Payment failed',     tone: 'red',     icon: 'alert' },
  };

  function status(s) {
    var st = STATUS[s] || { label: String(s || '').replace(/_/g, ' '), tone: 'gray', icon: 'circle-dot' };
    return {
      key: s, label: st.label, tone: st.tone, icon: st.icon,
      badge: TONES[st.tone], bar: ACCENTS[st.tone].bar, tile: ACCENTS[st.tone].tile,
    };
  }

  function statusBadge(s, opts) {
    var st = status(s);
    var withIcon = !opts || opts.icon !== false;
    return '<span class="badge ' + st.badge + '">' + (withIcon ? icon(st.icon, 'w-3.5 h-3.5') : '') + esc(st.label) + '</span>';
  }

  function money(v, decimals) {
    var n = parseFloat(v || 0);
    var d = decimals == null ? (n % 1 === 0 ? 0 : 2) : decimals;
    return 'ETB ' + n.toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
  }

  function initials(name) {
    return String(name || '?').trim().split(/\s+/).slice(0, 2).map(function (p) { return p.charAt(0); }).join('').toUpperCase();
  }

  var AVATAR_GRADIENTS = ['from-sky-400 to-indigo-500', 'from-amber-400 to-orange-600', 'from-emerald-400 to-teal-600', 'from-fuchsia-500 to-violet-600', 'from-rose-400 to-red-500', 'from-lime-400 to-emerald-600'];
  function avatar(name, size) {
    var s = String(name || '?'), h = 0;
    for (var i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) >>> 0;
    var g = AVATAR_GRADIENTS[h % AVATAR_GRADIENTS.length];
    var sz = size || 'w-9 h-9 text-xs';
    return '<span class="' + sz + ' rounded-xl bg-gradient-to-br ' + g + ' text-white font-bold flex items-center justify-center flex-shrink-0">' + esc(initials(s)) + '</span>';
  }

  function timeAgo(ts) {
    var d = Math.floor((Date.now() - new Date(String(ts).replace(' ', 'T'))) / 1000);
    if (isNaN(d)) return '';
    if (d < 60) return 'just now';
    if (d < 3600) return Math.floor(d / 60) + 'm ago';
    if (d < 86400) return Math.floor(d / 3600) + 'h ago';
    if (d < 604800) return Math.floor(d / 86400) + 'd ago';
    return new Date(String(ts).replace(' ', 'T')).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  }

  function empty(iconName, title, text, actionHtml) {
    return '<div class="empty">' +
      '<div class="empty-icon">' + icon(iconName, 'w-7 h-7') + '</div>' +
      '<h3 class="text-base font-bold text-charcoal dark:text-cream">' + esc(title) + '</h3>' +
      (text ? '<p class="text-sm text-charcoal-muted dark:text-gray-400 mt-1.5 max-w-xs">' + esc(text) + '</p>' : '') +
      (actionHtml ? '<div class="mt-5">' + actionHtml + '</div>' : '') +
    '</div>';
  }

  w.icon = w.icon || icon;
  w.LK = { icon: icon, esc: esc, status: status, statusBadge: statusBadge, money: money, initials: initials, avatar: avatar, timeAgo: timeAgo, empty: empty, TONES: TONES, ACCENTS: ACCENTS };
})(window);
