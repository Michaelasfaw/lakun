<?php $appUrl = APP_URL; ?>
<!DOCTYPE html>
<html lang="en" class="<?= isset($_COOKIE['td_dark']) && $_COOKIE['td_dark'] === '1' ? 'dark' : '' ?>">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resident Sign Up — TaskDoor</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $appUrl ?>/assets/css/app.css">
  <style>body{font-family:'Inter',sans-serif;}.btn-primary{transition:transform .15s ease,box-shadow .15s}.btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(217,119,6,.35)}.input-field{transition:border-color .2s,box-shadow .2s}.input-field:focus{border-color:#D97706;box-shadow:0 0 0 3px rgba(217,119,6,.15);outline:none}</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-amber-50 via-cream to-amber-50 dark:from-charcoal dark:via-charcoal-light dark:to-charcoal flex items-center justify-center p-4 font-sans">
<div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none"></div>

<div class="w-full max-w-md animate-fade-slide-up">
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-600 rounded-2xl shadow-lg mb-4">
      <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22" stroke="currentColor" stroke-width="2"/></svg>
    </div>
    <h1 class="text-3xl font-extrabold text-charcoal dark:text-cream tracking-tight">TaskDoor</h1>
    <p class="text-charcoal-muted dark:text-gray-400 mt-1 text-sm">Create your Resident account</p>
  </div>

  <div class="bg-white dark:bg-charcoal-light rounded-2xl shadow-xl p-8 border border-gray-100 dark:border-charcoal-lighter">
    <h2 class="text-xl font-bold text-charcoal dark:text-cream mb-1">Join as a Resident</h2>
    <p class="text-sm text-charcoal-muted dark:text-gray-400 mb-6">Post tasks and get them done without leaving your unit.</p>

    <form id="register-form" class="space-y-4">
      <input type="hidden" name="role" value="resident">

      <div class="grid grid-cols-1 gap-4">
        <div>
          <label class="block text-sm font-medium text-charcoal dark:text-cream mb-1.5">Full Name</label>
          <input type="text" name="name" placeholder="Amara Okafor" required
                 class="input-field w-full bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl px-4 py-3 text-charcoal dark:text-cream placeholder-gray-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-charcoal dark:text-cream mb-1.5">Phone Number</label>
          <input type="tel" name="phone" placeholder="08011111111" required
                 class="input-field w-full bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl px-4 py-3 text-charcoal dark:text-cream placeholder-gray-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-charcoal dark:text-cream mb-1.5">Apartment Building</label>
          <select name="building_id" required id="building-select"
                  class="input-field w-full bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl px-4 py-3 text-charcoal dark:text-cream text-sm">
            <option value="">Loading buildings…</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-charcoal dark:text-cream mb-1.5">Unit / Apartment Number</label>
          <input type="text" name="unit_number" placeholder="e.g. 5B, 12A" required
                 class="input-field w-full bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl px-4 py-3 text-charcoal dark:text-cream placeholder-gray-400 text-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-charcoal dark:text-cream mb-1.5">Password</label>
          <input type="password" name="password" placeholder="Min. 6 characters" required minlength="6"
                 class="input-field w-full bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl px-4 py-3 text-charcoal dark:text-cream placeholder-gray-400 text-sm">
        </div>
      </div>

      <button type="submit" id="submit-btn"
              class="btn-primary w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3.5 rounded-xl text-sm flex items-center justify-center gap-2 mt-2">
        <span id="btn-text">Create Account</span>
        <svg id="btn-spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
      </button>
    </form>

    <p class="text-sm text-center text-charcoal-muted dark:text-gray-400 mt-4">
      Already have an account? <a href="<?= $appUrl ?>/login" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline">Sign In</a>
    </p>
    <p class="text-sm text-center text-charcoal-muted dark:text-gray-400 mt-1">
      Want to be a runner? <a href="<?= $appUrl ?>/register/runner" class="text-charcoal dark:text-cream font-semibold hover:underline">Sign up here</a>
    </p>
  </div>
</div>

<script src="<?= $appUrl ?>/assets/js/app.js"></script>
<script>
const APP_URL = '<?= $appUrl ?>';

// Load buildings
fetch(APP_URL + '/api/v1/buildings/index.php')
  .then(r => r.json())
  .then(data => {
    const sel = document.getElementById('building-select');
    sel.innerHTML = '<option value="">Select your building…</option>';
    (data.data || []).forEach(b => {
      sel.innerHTML += `<option value="${b.id}">${b.name}</option>`;
    });
  }).catch(() => {
    document.getElementById('building-select').innerHTML = '<option value="">Failed to load buildings</option>';
  });

document.getElementById('register-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('submit-btn');
  const spinner = document.getElementById('btn-spinner');
  const btnText = document.getElementById('btn-text');
  btn.disabled = true; spinner.classList.remove('hidden'); btnText.textContent = 'Creating…';
  const fd = new FormData(e.target);
  const body = Object.fromEntries(fd);
  body.building_id = parseInt(body.building_id);
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
      showToast('Account created! 🎉', 'Welcome to TaskDoor!', 'success');
      setTimeout(() => window.location.href = APP_URL + '/resident/dashboard', 800);
    } else {
      showToast(data.message || 'Registration failed', '', 'error');
      btn.disabled = false; spinner.classList.add('hidden'); btnText.textContent = 'Create Account';
    }
  } catch {
    showToast('Network error. Try again.', '', 'error');
    btn.disabled = false; spinner.classList.add('hidden'); btnText.textContent = 'Create Account';
  }
});
</script>
</body>
</html>
