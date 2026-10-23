<?php
$pageTitle = 'Profile';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-2xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Account</p>
      <h1 class="page-title">Profile &amp; settings</h1>
      <p class="page-sub">Manage how you appear and how the app looks.</p>
    </div>
  </div>

  <!-- Profile Card -->
  <div class="card overflow-hidden mb-4">
    <div class="relative h-24 hero-gradient">
      <div class="absolute inset-0 bg-grid opacity-[0.15]" aria-hidden="true"></div>
    </div>
    <div class="px-5 md:px-6 pb-6">
      <div class="flex items-end gap-4 -mt-10 mb-5">
        <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-amber-400 to-orange-600 ring-4 ring-white dark:ring-charcoal-light flex items-center justify-center text-white font-extrabold text-3xl shadow-lg flex-shrink-0" id="avatar">?</div>
        <div class="pb-1 min-w-0">
          <h2 class="text-lg font-extrabold truncate" id="profile-name">Loading…</h2>
          <p class="text-sm text-charcoal-muted dark:text-gray-400 capitalize" id="profile-role"></p>
        </div>
      </div>

      <div class="flex items-center gap-2 text-sm text-charcoal-muted dark:text-gray-400 mb-5">
        <?= icon('phone', 'w-4 h-4') ?><span id="profile-phone" class="num"></span>
      </div>

      <div class="grid grid-cols-2 gap-3 mb-6" id="profile-details"></div>

      <!-- Edit form -->
      <form id="edit-form" class="space-y-4">
        <div>
          <label class="label" for="edit-name">Display name</label>
          <input type="text" name="name" id="edit-name" class="input">
        </div>
        <div id="unit-field">
          <label class="label" for="edit-unit">Unit number</label>
          <input type="text" name="unit_number" id="edit-unit" class="input">
        </div>
        <button type="submit" class="btn-brand w-full sm:w-auto"><?= icon('check', 'w-4 h-4') ?>Save changes</button>
      </form>
    </div>
  </div>

  <!-- Appearance -->
  <div class="card card-pad mb-4">
    <div class="flex items-center gap-4">
      <span class="icon-tile bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300"><?= icon('moon', 'w-5 h-5') ?></span>
      <div class="flex-1">
        <p class="text-sm font-bold">Dark mode</p>
        <p class="text-xs text-charcoal-muted dark:text-gray-400">Easier on the eyes at night.</p>
      </div>
      <button id="dark-mode-btn" onclick="toggleDarkMode()" aria-label="Toggle dark mode"
              class="relative w-12 h-7 rounded-full transition-colors bg-gray-300 dark:bg-amber-600 flex-shrink-0">
        <span id="dark-thumb" class="absolute top-1 left-1 w-5 h-5 rounded-full bg-white shadow transition-transform dark:translate-x-5"></span>
      </button>
    </div>
  </div>

  <!-- Rating Card -->
  <div id="rating-card" class="hidden card card-pad mb-4">
    <div class="flex items-center gap-3 mb-4">
      <span class="icon-tile bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"><?= icon('star', 'w-5 h-5') ?></span>
      <h3 class="section-title">My ratings</h3>
    </div>
    <div id="rating-stats" class="py-1">
      <div class="skeleton h-12 rounded-xl mb-2"></div>
    </div>
  </div>

  <!-- Sign out -->
  <a href="<?= $appUrl ?>/logout" class="card card-hover card-pad !py-4 flex items-center gap-4 group">
    <span class="icon-tile bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300"><?= icon('logout', 'w-5 h-5') ?></span>
    <span class="flex-1 font-semibold text-sm text-red-600 dark:text-red-400">Sign out</span>
    <?= icon('chevron-right', 'w-5 h-5 text-gray-400 group-hover:translate-x-0.5 transition-transform') ?>
  </a>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN = localStorage.getItem('td_token');

async function loadProfile() {
  const res = await fetch(APP_URL + '/api/v1/users/profile.php', { headers:{'Authorization':'Bearer '+TOKEN} });
  const data = await res.json();
  const p = data.data?.profile;
  if (!p) return;

  document.getElementById('avatar').textContent = p.name.charAt(0).toUpperCase();
  document.getElementById('profile-name').textContent = p.name;
  document.getElementById('profile-role').textContent = p.role;
  document.getElementById('profile-phone').textContent = p.phone;
  document.getElementById('edit-name').value = p.name;

  if (p.role === 'resident') {
    document.getElementById('profile-details').innerHTML = `
      <div class="surface-muted p-3.5">
        <p class="text-xs text-charcoal-muted dark:text-gray-400 mb-0.5">Unit</p>
        <p class="text-sm font-bold">${p.unit_number || '—'}</p>
      </div>`;
    document.getElementById('edit-unit').value = p.unit_number || '';
  } else {
    document.getElementById('unit-field').style.display = 'none';
    const buildings = data.data?.buildings || [];
    document.getElementById('profile-details').innerHTML = `
      <div class="surface-muted p-3.5 col-span-2">
        <p class="text-xs text-charcoal-muted dark:text-gray-400 mb-1 flex items-center gap-1.5">${icon('building', 'w-3.5 h-3.5')}Buildings served</p>
        <p class="text-sm font-semibold">${buildings.map(b=>b.name).join(', ') || 'None assigned'}</p>
      </div>`;

    // Show rating card
    document.getElementById('rating-card').classList.remove('hidden');
    const stats = data.data?.stats || {};
    const avgScore = parseFloat(stats.avg_score) || 0;
    const rounded = Math.round(avgScore);
    const stars = [1,2,3,4,5].map(i => `<span class="${i <= rounded ? 'text-amber-500' : 'text-gray-300 dark:text-gray-600'}">${icon('star', 'w-5 h-5 ' + (i <= rounded ? 'fill-current' : ''))}</span>`).join('');
    document.getElementById('rating-stats').innerHTML = `
      <div class="flex items-center gap-4">
        <span class="text-4xl font-extrabold text-amber-500 num">${avgScore > 0 ? avgScore.toFixed(1) : '—'}</span>
        <div>
          <div class="flex gap-0.5">${stars}</div>
          <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-1">${stats.total_ratings || 0} review${(stats.total_ratings||0) !== 1 ? 's' : ''}</p>
        </div>
      </div>`;
  }
}

document.getElementById('edit-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const body = Object.fromEntries(fd);
  const res = await fetch(APP_URL + '/api/v1/users/update.php', {
    method: 'POST', headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json'},
    body: JSON.stringify(body),
  });
  const data = await res.json();
  if (data.status === 'success') showToast('Profile updated','','success');
  else showToast(data.message||'Error','','error');
});

function toggleDarkMode() {
  const html = document.documentElement;
  const isDark = html.classList.toggle('dark');
  document.cookie = 'td_dark=' + (isDark ? '1' : '0') + '; path=/; max-age=31536000';
}

loadProfile();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
