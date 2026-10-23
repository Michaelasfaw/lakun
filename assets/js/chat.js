/**
 * TaskDoor — Chat Module
 * Polls for new messages every 3 seconds, renders chat bubbles.
 */

function initChat(appUrl, token, taskId, userId) {
  let lastMessageId = 0;
  let pollTimer = null;

  const container = document.getElementById('messages-container');
  const subtitle  = document.getElementById('chat-subtitle');

  function formatTime(ts) {
    const d = new Date(ts);
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

  function renderMessage(msg) {
    const isMine = parseInt(msg.sender_id) === parseInt(userId);
    const div = document.createElement('div');
    div.className = `flex ${isMine ? 'justify-end' : 'justify-start'} msg-appear`;
    div.innerHTML = `
      <div class="flex flex-col ${isMine ? 'items-end' : 'items-start'} max-w-[80%]">
        ${!isMine ? `<span class="text-xs text-charcoal-muted dark:text-gray-400 font-semibold mb-1 ml-1">${msg.sender_name}</span>` : ''}
        <div class="msg-bubble px-4 py-2.5 text-sm ${isMine ? 'msg-mine' : 'msg-theirs'}">${escapeHtml(msg.message)}</div>
        <span class="text-xs text-charcoal-muted dark:text-gray-500 mt-1 mx-1">${formatTime(msg.created_at)}</span>
      </div>`;
    return div;
  }

  function escapeHtml(s) {
    return s.replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }

  function scrollBottom() {
    container.scrollTop = container.scrollHeight;
  }

  async function loadMessages(initial = false) {
    try {
      const url = `${appUrl}/api/v1/chat/messages.php?task_id=${taskId}&since_id=${lastMessageId}`;
      const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
      const data = await res.json();

      if (data.status !== 'success') {
        if (initial) {
          container.innerHTML = `<div class="text-center py-8 text-sm text-charcoal-muted dark:text-gray-400">
            <p class="text-4xl mb-2">🔒</p>
            <p>Chat is available after a task is accepted.</p>
          </div>`;
        }
        return;
      }

      const messages = data.data?.messages || [];

      if (initial) {
        container.innerHTML = '';
        if (messages.length === 0) {
          container.innerHTML = `<div class="text-center py-8">
            <p class="text-3xl mb-2">💬</p>
            <p class="text-sm text-charcoal-muted dark:text-gray-400">No messages yet. Say hello!</p>
          </div>`;
        }
      }

      messages.forEach(msg => {
        // Remove empty state if present
        const emptyEl = container.querySelector('.text-center');
        if (emptyEl && messages.length > 0) emptyEl.remove();

        const el = renderMessage(msg);
        container.appendChild(el);
        lastMessageId = Math.max(lastMessageId, parseInt(msg.id));
      });

      if (messages.length > 0) scrollBottom();

      // Update subtitle
      if (subtitle) subtitle.textContent = 'Task #' + taskId;

    } catch (e) {
      console.error('[Chat] Error:', e);
    }
  }

  // Initial load
  loadMessages(true).then(() => {
    // Start polling after initial load
    pollTimer = setInterval(() => loadMessages(false), 3000);
  });

  // Cleanup on page unload
  window.addEventListener('beforeunload', () => clearInterval(pollTimer));
}

// Send a message — called from chat.php
async function sendMessage() {
  const input = document.getElementById('msg-input');
  const msg = input.value.trim();
  if (!msg) return;

  // Get from globals set in chat.php
  const appUrl = window.APP_URL;
  const token  = localStorage.getItem('td_token');

  // Get TASK_ID from global set by chat.php script
  const taskId = window.TASK_ID;
  if (!taskId) return;

  input.value = '';
  input.disabled = true;

  try {
    const res = await fetch(appUrl + '/api/v1/chat/send.php', {
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' },
      body: JSON.stringify({ task_id: taskId, message: msg }),
    });
    const data = await res.json();
    if (data.status !== 'success') {
      showToast(data.message || 'Failed to send', '', 'error');
      input.value = msg;
    }
  } catch {
    showToast('Network error', '', 'error');
    input.value = msg;
  } finally {
    input.disabled = false;
    input.focus();
  }
}
