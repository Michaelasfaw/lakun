<?php
$pageTitle = 'New Task';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-lg mx-auto">
  <div class="mb-6">
    <h1 class="text-2xl font-extrabold text-charcoal dark:text-cream">Post a Task</h1>
    <p class="text-charcoal-muted dark:text-gray-400 text-sm mt-0.5">Tell runners what you need done.</p>
  </div>

  <form id="create-form" class="space-y-5 bg-white dark:bg-charcoal-light rounded-2xl p-6 border border-gray-100 dark:border-charcoal-lighter shadow-sm">

    <!-- Category -->
    <div>
      <label class="block text-sm font-semibold text-charcoal dark:text-cream mb-2">Category</label>
      <div class="grid grid-cols-5 gap-2" id="category-grid">
        <?php foreach ([
          ['grocery', '🛒', 'Grocery'],
          ['package', '📦', 'Package'],
          ['document','📄','Document'],
          ['food',    '🍔', 'Food'],
          ['other',   '📌', 'Other'],
        ] as [$val, $icon, $label]): ?>
          <label class="cursor-pointer">
            <input type="radio" name="category" value="<?= $val ?>" class="sr-only" <?= $val === 'other' ? 'checked' : '' ?>>
            <div class="category-btn flex flex-col items-center gap-1 p-2.5 border-2 border-gray-100 dark:border-charcoal-lighter rounded-xl hover:border-amber-400 transition-all text-center">
              <span class="text-xl"><?= $icon ?></span>
              <span class="text-xs font-medium text-charcoal-muted dark:text-gray-400"><?= $label ?></span>
            </div>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Urgency -->
    <div>
      <label class="block text-sm font-semibold text-charcoal dark:text-cream mb-2">Urgency</label>
      <div class="grid grid-cols-2 gap-2">
        <label class="cursor-pointer">
          <input type="radio" name="urgency" value="normal" class="sr-only" checked>
          <div class="urgency-btn flex items-center gap-2 p-3 border-2 border-gray-100 dark:border-charcoal-lighter rounded-xl hover:border-amber-400 transition-all">
            <span class="text-lg">🟢</span>
            <div>
              <p class="text-sm font-semibold text-charcoal dark:text-cream">Normal</p>
              <p class="text-xs text-charcoal-muted dark:text-gray-400">Flexible timing</p>
            </div>
          </div>
        </label>
        <label class="cursor-pointer">
          <input type="radio" name="urgency" value="urgent" class="sr-only">
          <div class="urgency-btn flex items-center gap-2 p-3 border-2 border-gray-100 dark:border-charcoal-lighter rounded-xl hover:border-red-400 transition-all">
            <span class="text-lg">🔴</span>
            <div>
              <p class="text-sm font-semibold text-charcoal dark:text-cream">Urgent</p>
              <p class="text-xs text-charcoal-muted dark:text-gray-400">Needed ASAP</p>
            </div>
          </div>
        </label>
      </div>
    </div>

    <!-- Title -->
    <div>
      <label class="block text-sm font-semibold text-charcoal dark:text-cream mb-1.5">Task Title <span class="text-red-500">*</span></label>
      <input type="text" name="title" placeholder="e.g. Buy groceries from Shoprite" required maxlength="150"
             class="input-field w-full bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl px-4 py-3 text-charcoal dark:text-cream placeholder-gray-400 text-sm">
    </div>

    <!-- Description -->
    <div>
      <label class="block text-sm font-semibold text-charcoal dark:text-cream mb-1.5">Details <span class="text-charcoal-muted">(optional)</span></label>
      <textarea name="description" rows="3" placeholder="Specific items, quantities, instructions for the runner…"
                class="input-field w-full bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl px-4 py-3 text-charcoal dark:text-cream placeholder-gray-400 text-sm resize-none"></textarea>
    </div>

    <!-- Tip -->
    <div>
      <label class="block text-sm font-semibold text-charcoal dark:text-cream mb-1.5">Tip / Reward <span class="text-charcoal-muted">(optional)</span></label>
      <div class="relative">
        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-charcoal-muted dark:text-gray-400 text-sm font-medium">💰</span>
        <input type="text" name="tip" placeholder="e.g. ₦500" maxlength="100"
               class="input-field w-full bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl pl-10 pr-4 py-3 text-charcoal dark:text-cream placeholder-gray-400 text-sm">
      </div>
    </div>

    <!-- Preview -->
    <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800 rounded-xl p-4">
      <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 mb-1">📍 Your unit</p>
      <p class="text-sm text-charcoal dark:text-cream font-medium"><?= htmlspecialchars($user['unit_number'] ?? 'Not set') ?></p>
      <p class="text-xs text-charcoal-muted dark:text-gray-400">Runners will see this after accepting your task.</p>
    </div>

    <button type="submit" id="submit-btn"
            class="btn-primary w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-4 rounded-xl flex items-center justify-center gap-2 text-base">
      <span id="btn-text">Post Task ⚡</span>
      <svg id="btn-spinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
    </button>
  </form>
</div>

<style>
input[type="radio"]:checked + .category-btn { border-color: #D97706; background: #FFFBEB; }
.dark input[type="radio"]:checked + .category-btn { border-color: #D97706; background: rgba(217,119,6,0.15); }
input[type="radio"]:checked + .urgency-btn { border-color: #D97706; background: #FFFBEB; }
.dark input[type="radio"]:checked + .urgency-btn { border-color: #D97706; background: rgba(217,119,6,0.15); }
input[value="urgent"]:checked + .urgency-btn { border-color: #EF4444; background: #FEF2F2; }
.input-field:focus { border-color: #D97706; box-shadow: 0 0 0 3px rgba(217,119,6,0.15); outline: none; }
.input-field { transition: border-color 0.2s, box-shadow 0.2s; }
</style>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN = localStorage.getItem('td_token');

document.getElementById('create-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('submit-btn');
  const spinner = document.getElementById('btn-spinner');
  const btnText = document.getElementById('btn-text');
  btn.disabled = true; spinner.classList.remove('hidden'); btnText.textContent = 'Posting…';

  const fd = new FormData(e.target);
  const body = Object.fromEntries(fd);

  try {
    const res = await fetch(APP_URL + '/api/v1/tasks/create.php', {
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + TOKEN, 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await res.json();
    if (data.status === 'success') {
      showToast('Task posted! 🎉', 'Runners are being notified.', 'success');
      setTimeout(() => window.location.href = APP_URL + '/resident/dashboard', 1000);
    } else {
      showToast(data.message || 'Failed to post task', '', 'error');
      btn.disabled = false; spinner.classList.add('hidden'); btnText.textContent = 'Post Task ⚡';
    }
  } catch {
    showToast('Network error. Try again.', '', 'error');
    btn.disabled = false; spinner.classList.add('hidden'); btnText.textContent = 'Post Task ⚡';
  }
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
