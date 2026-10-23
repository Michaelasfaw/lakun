<?php
// This file is not directly included — toast logic is driven by JavaScript app.js
// It serves as a reference for the toast HTML structure rendered by JS.
?>
<!--
Toast HTML structure (rendered by JS):
<div class="toast pointer-events-auto flex items-start gap-3 bg-white dark:bg-charcoal-light border border-gray-100 dark:border-charcoal-lighter rounded-2xl shadow-xl p-4 animate-slide-in-right" role="alert">
  <span class="text-xl">🔔</span>
  <div class="flex-1 min-w-0">
    <p class="text-sm font-semibold text-charcoal dark:text-cream truncate">{title}</p>
    <p class="text-xs text-charcoal-muted dark:text-gray-400 mt-0.5">{message}</p>
  </div>
  <button onclick="this.closest('.toast').remove()" class="text-gray-400 hover:text-gray-600 transition-colors">✕</button>
</div>
-->
