<!DOCTYPE html>
<html lang="en" class="<?= isset($_COOKIE['td_dark']) && $_COOKIE['td_dark'] === '1' ? 'dark' : '' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#D97706">
  <title><?= $pageTitle ?? 'TaskDoor' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
  <style>
    body { font-family: 'Inter', system-ui, sans-serif; }
    .btn-primary { transition: transform 0.15s ease, box-shadow 0.15s ease; }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(217,119,6,0.35); }
    .input-field { transition: border-color 0.2s, box-shadow 0.2s; }
    .input-field:focus { border-color: #D97706; box-shadow: 0 0 0 3px rgba(217,119,6,0.15); outline: none; }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-amber-50 via-cream to-amber-50 dark:from-charcoal dark:via-charcoal-light dark:to-charcoal flex items-center justify-center p-4 font-sans">

<!-- Toast -->
<div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none"></div>

<div class="w-full max-w-md animate-fade-slide-up">
  <!-- Logo -->
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-600 rounded-2xl shadow-lg mb-4">
      <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    </div>
    <h1 class="text-3xl font-extrabold text-charcoal dark:text-cream tracking-tight">TaskDoor</h1>
    <p class="text-charcoal-muted dark:text-gray-400 mt-1 text-sm">Errands, delivered to your door.</p>
  </div>

  <!-- Card -->
  <div class="bg-white dark:bg-charcoal-light rounded-2xl shadow-xl p-8 border border-gray-100 dark:border-charcoal-lighter">
    <?= $content ?? '' ?>
  </div>
</div>

<script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
