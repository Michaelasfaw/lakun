<?php $appUrl = APP_URL; ?>
<!DOCTYPE html>
<html lang="en" class="<?= isset($_COOKIE['td_dark']) && $_COOKIE['td_dark'] === '1' ? 'dark' : '' ?>">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Runner Sign Up — TaskDoor</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $appUrl ?>/assets/css/app.css">
  <style>body{font-family:'Inter',sans-serif;}.btn-primary{transition:transform .15s ease,box-shadow .15s}.btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(217,119,6,.35)}.input-field{transition:border-color .2s,box-shadow .2s}.input-field:focus{border-color:#D97706;box-shadow:0 0 0 3px rgba(217,119,6,.15);outline:none}.building-check:checked+label{border-color:#D97706;background:#FFFBEB;}</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-amber-50 via-cream to-amber-50 dark:from-charcoal dark:via-charcoal-light dark:to-charcoal flex items-center justify-center p-4 font-sans">
<div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none"></div>

<div class="w-full max-w-md animate-fade-slide-up">
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-charcoal dark:bg-charcoal-lighter rounded-2xl shadow-lg mb-4">
      <span class="text-3xl">🏃</span>
    </div>
    <h1 class="text-3xl font-extrabold text-charcoal dark:text-cream tracking-tight">TaskDoor</h1>
    <p class="text-charcoal-muted dark:text-gray-400 mt-1 text-sm">Earn by completing tasks in your building.</p>
  </div>

  <div class="bg-white dark:bg-charcoal-light rounded-2xl shadow-xl p-8 border border-gray-100 dark:border-charcoal-lighter">
    <h2 class="text-xl font-bold text-charcoal dark:text-cream mb-1">Join as a Runner</h2>
    <p class="text-sm text-charcoal-muted dark:text-gray-400 mb-6">Accept tasks, earn tips, be the hero of the building.</p>

    <form id="register-form" class="space-y-4">
      <input type="hidden" name="role" value="runner">

      <div>
        <label class="block text-sm font-medium text-charcoal dark:text-cream mb-1.5">Full Name</label>
        <input type="text" name="name" placeholder="Tunde Badmus" required
               class="input-field w-full bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl px-4 py-3 text-charcoal dark:text-cream placeholder-gray-400 text-sm">
      </div>
      <div>
        <label class="block text-sm font-medium text-charcoal dark:text-cream mb-1.5">Phone Number</label>
        <input type="tel" name="phone" placeholder="08055555555" required
               class="input-field w-full bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl px-4 py-3 text-charcoal dark:text-cream placeholder-gray-400 text-sm">
      </div>
      <div>
        <label class="block text-sm font-medium text-charcoal dark:text-cream mb-1.5">Password</label>
        <input type="password" name="password" placeholder="Min. 6 characters" required minlength="6"
               class="input-field w-full bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl px-4 py-3 text-charcoal dark:text-cream placeholder-gray-400 text-sm">
      </div>

      <div>
        <label class="block text-sm font-medium text-charcoal dark:text-cream mb-2">Buildings I serve <span class="text-charcoal-muted">(select all that apply)</span></label>
        <div id="buildings-list" class="grid grid-cols-1 gap-2">
          <div class="skeleton h-10 rounded-xl"></div>
          <div class="skeleton h-10 rounded-xl"></div>
          <div class="skeleton h-10 rounded-xl"></div>
        </div>
      </div>

      <button type="submit" id="submit-btn"
              class="btn-primary w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3.5 rounded-xl text-sm flex items-center justify-center gap-2 mt-2">
        <span id="btn-text">Create Runner Account</span>
        <svg id="btn-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
      </button>
    </form>

    <p class="text-sm text-center text-charcoal-muted dark:text-gray-400 mt-4">
      Already have an account? <a href="<?= $appUrl ?>/login" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline">Sign In</a>
    </p>
    <p class="text-sm text-center text-charcoal-muted dark:text-gray-400 mt-1">
      Looking to post tasks? <a href="<?= $appUrl ?>/register/resident" class="text-charcoal dark:text-cream font-semibold hover:underline">Resident sign up</a>
    </p>
  </div>
</div>

<script src="<?= $appUrl ?>/assets/js/app.js"></script>
<script>
const APP_URL = '<?= $appUrl ?>';
let buildings = [];

fetch(APP_URL + '/api/v1/buildings/index.php')
  .then(r => r.json())
  .then(data => {
    buildings = data.data || [];
    const container = document.getElementById('buildings-list');
    container.innerHTML = buildings.map(b => `
      <label class="flex items-center gap-3 p-3 border-2 border-gray-100 dark:border-charcoal-lighter rounded-xl cursor-pointer hover:border-amber-400 transition-colors has-[:checked]:border-amber-600 has-[:checked]:bg-amber-50 dark:has-[:checked]:bg-amber-900/20">
        <input type="checkbox" name="building_ids[]" value="${b.id}" class="w-4 h-4 accent-amber-600">
        <div>
          <p class="text-sm font-semibold text-charcoal dark:text-cream">${b.name}</p>
          <p class="text-xs text-charcoal-muted dark:text-gray-400">${b.address || ''}</p>
        </div>
      </label>
    `).join('');
  }).catch(() => {
    document.getElementById('buildings-list').innerHTML = '<p class="text-sm text-red-500">Failed to load buildings</p>';
  });

document.getElementById('register-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('submit-btn');
  const spinner = document.getElementById('btn-spinner');
  const btnText = document.getElementById('btn-text');

  const checked = [...document.querySelectorAll('input[name="building_ids[]"]:checked')];
  if (checked.length === 0) {
    showToast('Please select at least one building', '', 'error');
    return;
  }

  btn.disabled = true; spinner.classList.remove('hidden'); btnText.textContent = 'Creating…';
  const fd = new FormData(e.target);
  const body = {
    name: fd.get('name'),
    phone: fd.get('phone'),
    password: fd.get('password'),
    role: 'runner',
    building_ids: checked.map(c => parseInt(c.value)),
  };

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
      showToast('You\'re in! 🏃', 'Welcome, runner!', 'success');
      setTimeout(() => window.location.href = APP_URL + '/runner/feed', 800);
    } else {
      showToast(data.message || 'Registration failed', '', 'error');
      btn.disabled = false; spinner.classList.add('hidden'); btnText.textContent = 'Create Runner Account';
    }
  } catch {
    showToast('Network error. Try again.', '', 'error');
    btn.disabled = false; spinner.classList.add('hidden'); btnText.textContent = 'Create Runner Account';
  }
});
</script>
</body>
</html>
