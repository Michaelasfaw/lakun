<?php
$pageTitle = 'Chat';
$appUrl = APP_URL;
$taskIdParam = $taskId ?? 0;
ob_start();
?>
<div class="max-w-lg mx-auto flex flex-col" style="height: calc(100vh - 200px)">
  <!-- Header -->
  <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100 dark:border-charcoal-lighter">
    <a href="javascript:history.back()" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-charcoal-lighter transition-colors">
      <svg class="w-5 h-5 text-charcoal dark:text-cream" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
      <h1 class="text-lg font-bold text-charcoal dark:text-cream">Task Chat</h1>
      <p class="text-xs text-charcoal-muted dark:text-gray-400" id="chat-subtitle">Loading…</p>
    </div>
  </div>

  <!-- Messages -->
  <div id="messages-container" class="flex-1 overflow-y-auto space-y-3 pb-4 pr-1">
    <div class="text-center py-8">
      <div class="w-8 h-8 border-2 border-amber-400 border-t-transparent rounded-full animate-spin mx-auto"></div>
    </div>
  </div>

  <!-- Input -->
  <div class="pt-3 border-t border-gray-100 dark:border-charcoal-lighter">
    <div class="flex gap-2">
      <input type="text" id="msg-input" placeholder="Type a message…"
             class="flex-1 bg-gray-50 dark:bg-charcoal border border-gray-200 dark:border-charcoal-lighter rounded-xl px-4 py-2.5 text-charcoal dark:text-cream placeholder-gray-400 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 dark:focus:ring-amber-900/30"
             onkeypress="if(event.key==='Enter' && !event.shiftKey) { event.preventDefault(); sendMessage(); }">
      <button onclick="sendMessage()" class="btn-primary bg-amber-600 hover:bg-amber-700 text-white p-2.5 rounded-xl">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
      </button>
    </div>
  </div>
</div>

<style>
.msg-bubble{max-width:80%;word-break:break-word;}
.msg-mine{background:#D97706;color:white;border-radius:18px 18px 4px 18px;}
.msg-theirs{background:#f3f4f6;color:#1C1C1E;border-radius:18px 18px 18px 4px;}
.dark .msg-theirs{background:#2C2C2E;color:#FAF9F6;}
@keyframes msgAppear{from{transform:translateY(6px);opacity:0}to{transform:translateY(0);opacity:1}}
.msg-appear{animation:msgAppear .2s ease-out;}
</style>

<script src="<?= $appUrl ?>/assets/js/chat.js"></script>
<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN = localStorage.getItem('td_token');
const TASK_ID = <?= (int)$taskIdParam ?>;
const USER = JSON.parse(localStorage.getItem('td_user') || '{}');
// Expose globals for chat.js sendMessage()
window.APP_URL = APP_URL;
window.TASK_ID = TASK_ID;

initChat(APP_URL, TOKEN, TASK_ID, USER.id);
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
