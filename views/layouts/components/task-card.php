<?php
/**
 * Reusable task card component.
 * $task array, $viewMode: 'runner_feed' | 'resident' | 'runner_active'
 */
$categoryIcons = [
    'grocery'  => '🛒',
    'package'  => '📦',
    'document' => '📄',
    'food'     => '🍔',
    'other'    => '📌',
];
$statusLabels = [
    'open'        => ['label' => 'Open',             'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
    'accepted'    => ['label' => 'In Progress',      'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
    'runner_done' => ['label' => 'Awaiting Confirm', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
    'completed'   => ['label' => 'Completed',        'class' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'],
    'cancelled'   => ['label' => 'Cancelled',        'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
];

$status  = $task['status'] ?? 'open';
$badge   = $statusLabels[$status] ?? $statusLabels['open'];
$catIcon = $categoryIcons[$task['category'] ?? 'other'] ?? '📌';
$isUrgent = ($task['urgency'] ?? '') === 'urgent';
$timeAgo = function(string $ts): string {
    $diff = time() - strtotime($ts);
    if ($diff < 60)   return 'just now';
    if ($diff < 3600) return floor($diff/60) . 'm ago';
    if ($diff < 86400)return floor($diff/3600) . 'h ago';
    return floor($diff/86400) . 'd ago';
};
?>
<div class="task-card bg-white dark:bg-charcoal-light rounded-2xl p-5 border-l-4 border-transparent status-<?= $status ?> shadow-sm border border-gray-100 dark:border-charcoal-lighter cursor-pointer animate-card-appear"
     data-task-id="<?= $task['id'] ?>" data-status="<?= $status ?>">
  <div class="flex items-start justify-between gap-3">
    <div class="flex items-start gap-3 flex-1 min-w-0">
      <!-- Category icon -->
      <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-xl flex-shrink-0">
        <?= $catIcon ?>
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
          <h3 class="font-semibold text-charcoal dark:text-cream truncate"><?= htmlspecialchars($task['title']) ?></h3>
          <?php if ($isUrgent): ?>
            <span class="inline-flex items-center gap-1 text-xs font-bold text-red-600 dark:text-red-400 animate-pulse-dot">
              <span class="w-1.5 h-1.5 bg-red-500 rounded-full inline-block"></span>URGENT
            </span>
          <?php endif; ?>
        </div>
        <?php if (!empty($task['description'])): ?>
          <p class="text-sm text-charcoal-muted dark:text-gray-400 mt-0.5 line-clamp-2"><?= htmlspecialchars($task['description']) ?></p>
        <?php endif; ?>
        <div class="flex items-center gap-3 mt-2 flex-wrap">
          <span class="text-xs px-2 py-0.5 rounded-full <?= $badge['class'] ?> font-medium"><?= $badge['label'] ?></span>
          <span class="text-xs text-charcoal-muted dark:text-gray-500 capitalize"><?= ucfirst($task['category'] ?? 'other') ?></span>
          <?php if (!empty($task['tip'])): ?>
            <span class="text-xs font-semibold text-amber-600 dark:text-amber-400">💰 <?= htmlspecialchars($task['tip']) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="text-xs text-charcoal-muted dark:text-gray-500 flex-shrink-0">
      <?= $timeAgo($task['created_at']) ?>
    </div>
  </div>

  <?php if (isset($viewMode) && $viewMode === 'runner_feed'): ?>
    <div class="mt-4 flex items-center justify-end">
      <button onclick="acceptTask(<?= $task['id'] ?>)" data-task-id="<?= $task['id'] ?>"
              class="btn-primary bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl accept-btn">
        Accept This Job ⚡
      </button>
    </div>
  <?php endif; ?>

  <?php if (isset($viewMode) && $viewMode === 'resident' && $status === 'runner_done'): ?>
    <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/10 rounded-xl border border-amber-200 dark:border-amber-800">
      <p class="text-sm text-amber-800 dark:text-amber-300 font-medium mb-2">🎉 Runner marked this as done!</p>
      <button onclick="confirmTask(<?= $task['id'] ?>)"
              class="btn-primary w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2.5 rounded-xl text-sm">
        Confirm & Close ✅
      </button>
    </div>
  <?php endif; ?>
</div>
