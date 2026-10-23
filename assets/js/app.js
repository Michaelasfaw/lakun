/**
 * Lakun — Core JS
 * Dark mode, toast notifications, notification polling, global utils
 */

// ─── Dark Mode ───────────────────────────────────────────────────────────────
(function initDarkMode() {
  const html   = document.documentElement;
  const isDark = document.cookie.split(';').some(c => c.trim().startsWith('td_dark=1'));

  // Apply immediately (before page paint)
  if (isDark) html.classList.add('dark');

  function toggle() {
    const dark = html.classList.toggle('dark');
    document.cookie = 'td_dark=' + (dark ? '1' : '0') + '; path=/; max-age=31536000; SameSite=Lax';
    updateAllDarkButtons(dark);
  }

  function updateAllDarkButtons(dark) {
    // Sidebar / drawer nav toggles
    document.querySelectorAll('.js-theme-moon').forEach(el => el.classList.toggle('hidden', dark));
    document.querySelectorAll('.js-theme-sun').forEach(el => el.classList.toggle('hidden', !dark));
    document.querySelectorAll('.js-theme-label').forEach(el => { el.textContent = dark ? 'Light mode' : 'Dark mode'; });

    // Header button (sun/moon swap)
    const sun  = document.getElementById('icon-sun');
    const moon = document.getElementById('icon-moon');
    if (sun)  sun.classList.toggle('hidden', !dark);
    if (moon) moon.classList.toggle('hidden', dark);

    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', dark ? '#121110' : '#F7F5F0');
  }

  document.addEventListener('click', function(e) {
    if (e.target.closest('.js-dark-toggle')) toggle();
  });

  updateAllDarkButtons(isDark);
})();

// ─── Toast Notification System ───────────────────────────────────────────────
function _toastIcon(name) {
  return '<svg class="ico w-[18px] h-[18px]" aria-hidden="true"><use href="#i-' + name + '"/></svg>';
}
function _toastEsc(s) {
  return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
var TOAST_STYLES = {
  success: { icon: 'check-circle', tile: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' },
  error:   { icon: 'x-circle',     tile: 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300' },
  info:    { icon: 'zap',          tile: 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300' },
  warning: { icon: 'alert',        tile: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-300' },
};

function showToast(title, message, type) {
  message = message || '';
  type    = type    || 'info';
  const container = document.getElementById('toast-container');
  if (!container) return;
  const st = TOAST_STYLES[type] || TOAST_STYLES.info;

  const toast = document.createElement('div');
  toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
  toast.className = 'toast pointer-events-auto flex items-start gap-3 bg-white/95 dark:bg-charcoal-light/95 backdrop-blur-xl border border-gray-200/80 dark:border-white/[0.08] rounded-2xl shadow-xl p-3.5 pr-3 w-full';
  toast.style.animation = 'slideInRight 0.35s cubic-bezier(.2,.8,.2,1)';
  toast.innerHTML = `
    <span class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 ${st.tile}">${_toastIcon(st.icon)}</span>
    <div class="flex-1 min-w-0 pt-0.5">
      <p class="text-sm font-semibold text-charcoal dark:text-cream leading-snug">${title}</p>
      ${message ? `<p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5 leading-snug">${message}</p>` : ''}
    </div>
    <button onclick="dismissToast(this)" class="text-gray-400 hover:text-charcoal dark:hover:text-cream transition-colors p-1 rounded-lg flex-shrink-0" aria-label="Dismiss">
      <svg class="ico w-4 h-4" aria-hidden="true"><use href="#i-x"/></svg>
    </button>`;

  container.appendChild(toast);
  setTimeout(function() { dismissToast(toast.querySelector('button')); }, 4500);
}

function dismissToast(btn) {
  const toast = btn && btn.closest ? btn.closest('.toast') : btn;
  if (!toast || !toast.parentNode) return;
  toast.style.animation = 'slideOutRight 0.3s ease-in forwards';
  setTimeout(function() { toast.remove(); }, 300);
}

// CSS keyframes injected once
const styleEl = document.createElement('style');
styleEl.textContent = `
  @keyframes slideInRight  { from { transform: translateX(110%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
  @keyframes slideOutRight { from { transform: translateX(0); opacity: 1; } to { transform: translateX(110%); opacity: 0; } }
  @keyframes bounceIn      { 0%{transform:scale(0.8);opacity:0} 80%{transform:scale(1.05)} 100%{transform:scale(1);opacity:1} }
  @keyframes cardAppear    { from { transform: translateY(10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
`;
document.head.appendChild(styleEl);

// ─── Notification Sounds (Web Audio API, no external files) ─────────────────
var _audioCtx = null;
function getAudioCtx() {
  if (!_audioCtx) _audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  return _audioCtx;
}

/**
 * Play a synthesised notification sound.
 * type: 'customer' | 'vendor' | 'delivery' | 'admin'
 */
function playNotificationSound(type) {
  try {
    var ctx = getAudioCtx();
    var profiles = {
      // Customer: bright rising double-chime
      customer:  [{ f:880, t:0,    d:0.12, g:0.4 }, { f:1108, t:0.14, d:0.15, g:0.35 }],
      // Vendor:   mid-range two-tone ping
      vendor:    [{ f:660, t:0,    d:0.12, g:0.35 }, { f:660,  t:0.15, d:0.12, g:0.3  }],
      // Delivery: low urgent buzz-pop
      delivery:  [{ f:440, t:0,    d:0.08, g:0.45 }, { f:330,  t:0.1,  d:0.12, g:0.4  }],
      // Admin:    professional triple tone
      admin:     [{ f:523, t:0,    d:0.1,  g:0.3  }, { f:659,  t:0.12, d:0.1,  g:0.3  }, { f:784, t:0.24, d:0.15, g:0.25 }],
    };
    var notes = profiles[type] || profiles.customer;
    var now   = ctx.currentTime;
    notes.forEach(function(n) {
      var osc  = ctx.createOscillator();
      var gain = ctx.createGain();
      osc.type            = type === 'delivery' ? 'sawtooth' : 'sine';
      osc.frequency.value = n.f;
      gain.gain.setValueAtTime(0, now + n.t);
      gain.gain.linearRampToValueAtTime(n.g, now + n.t + 0.01);
      gain.gain.exponentialRampToValueAtTime(0.001, now + n.t + n.d);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start(now + n.t);
      osc.stop(now + n.t + n.d + 0.05);
    });
  } catch(e) {}
}

// ─── Notification Badge Polling ───────────────────────────────────────────────
(function pollNotifications() {
  var token = localStorage.getItem('td_token');
  if (!token) return;
  var badge = document.getElementById('notif-badge');
  if (!badge) return;

  // Detect user role from stored profile
  var userRole = 'customer';
  try {
    var profile = JSON.parse(localStorage.getItem('td_user') || '{}');
    userRole = profile.role || 'customer';
  } catch(e) {}

  var prevCount = -1; // -1 = first load (don't sound on page open)

  function checkNotifs() {
    var appUrl = window.APP_URL;
    fetch(appUrl + '/api/v1/notifications/index.php', {
      headers: { 'Authorization': 'Bearer ' + token }
    }).then(function(r) { return r.json(); }).then(function(data) {
      var count = (data.data && data.data.unread_count) || 0;
      badge.textContent = count > 9 ? '9+' : count;
      badge.classList.toggle('hidden', count === 0);

      // Play sound + show actionable toast when new notifications arrive
      if (prevCount >= 0 && count > prevCount) {
        playNotificationSound(userRole);

        // For vendors: show actionable toast for new_order notifications
        if (userRole === 'vendor') {
          var notifications = (data.data && data.data.notifications) || [];
          var newOrders = notifications.filter(function(n) {
            return n.type === 'new_order' && !n.is_read;
          });
          if (newOrders.length > 0) {
            var n = newOrders[0];
            showActionableToast(
              'New order request',
              (n.body || n.title || 'A new order is waiting for your bid'),
              'Send your price →',
              appUrl + '/vendor/orders'
            );
          } else {
            showToast('New notification', '', 'info');
          }
        }
      }
      prevCount = count;
    }).catch(function() {});
  }

  function showActionableToast(title, message, actionLabel, actionUrl) {
    var container = document.getElementById('toast-container');
    if (!container) return;
    var el = document.createElement('div');
    el.className = 'toast pointer-events-auto flex items-start gap-3 bg-white/95 dark:bg-charcoal-light/95 backdrop-blur-xl border border-amber-200 dark:border-amber-500/30 rounded-2xl shadow-xl p-3.5 pr-3';
    el.style.animation = 'slideInRight 0.35s cubic-bezier(.2,.8,.2,1)';
    el.innerHTML =
      '<span class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-amber-500 text-white shadow-glow">' + _toastIcon('bell') + '</span>' +
      '<div class="flex-1 min-w-0 pt-0.5">' +
        '<p class="text-sm font-semibold text-charcoal dark:text-cream">' + _toastEsc(title) + '</p>' +
        '<p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5 truncate">' + _toastEsc(message) + '</p>' +
        '<a href="' + actionUrl + '" class="inline-flex items-center gap-1 mt-2 text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">' + _toastEsc(actionLabel) + '</a>' +
      '</div>' +
      '<button onclick="this.closest(\'.toast\').remove()" class="text-gray-400 hover:text-charcoal dark:hover:text-cream p-1 rounded-lg flex-shrink-0" aria-label="Dismiss"><svg class="ico w-4 h-4" aria-hidden="true"><use href="#i-x"/></svg></button>';
    container.appendChild(el);
    setTimeout(function() { el.remove(); }, 12000);
  }

  // Unlock AudioContext on first user interaction (browser policy)
  document.addEventListener('click', function() {
    if (_audioCtx && _audioCtx.state === 'suspended') _audioCtx.resume();
  }, { once: true });

  checkNotifs();
  setInterval(checkNotifs, 15000);
})();

// ─── Agent Credit Balance (sidebar + drawer) ─────────────────────────────────
(function pollCreditBalance() {
  var els = ['sidebar-credits', 'drawer-credits'].map(function(id) { return document.getElementById(id); }).filter(Boolean);
  if (!els.length) return;
  var token = localStorage.getItem('td_token');
  if (!token) return;

  function fetchBalance() {
    var appUrl = window.APP_URL;
    fetch(appUrl + '/api/v1/credits/balance.php', {
      headers: { 'Authorization': 'Bearer ' + token }
    }).then(function(r) { return r.json(); }).then(function(data) {
      if (data.status === 'success') {
        var credits = (data.data && data.data.credits !== undefined) ? data.data.credits : 0;
        els.forEach(function(el) {
          el.textContent = credits;
          el.classList.toggle('text-red-400', credits === 0);
          el.classList.toggle('text-amber-300', credits > 0 && credits < 3);
        });
      }
    }).catch(function() {});
  }

  fetchBalance();
  setInterval(fetchBalance, 30000);
})();

// ─── Auth Guard ──────────────────────────────────────────────────────────────
(function checkAuth() {
  var token = localStorage.getItem('td_token');
  var protectedPaths = ['/customer/', '/vendor/', '/delivery/', '/admin', '/notifications', '/profile'];
  var currentPath = window.location.pathname;
  var needsAuth = protectedPaths.some(function(p) { return currentPath.includes(p); });
  if (needsAuth && !token) {
    window.location.href = window.APP_URL + '/login';
  }
})();
