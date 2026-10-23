<?php
$pageTitle = 'People';
$appUrl = APP_URL;
ob_start();
?>
<div class="max-w-6xl mx-auto">
  <div class="page-head">
    <div>
      <p class="page-eyebrow">Directory</p>
      <h1 class="page-title">People</h1>
      <p class="page-sub">Customers, vendors and riders across every building.</p>
    </div>
    <button onclick="openCreateModal()" class="btn-brand"><?= icon('plus', 'w-4 h-4') ?>Add person</button>
  </div>

  <!-- Stats row -->
  <div id="role-stats" class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-5">
    <div class="skeleton h-[88px] rounded-2xl"></div>
    <div class="skeleton h-[88px] rounded-2xl"></div>
    <div class="skeleton h-[88px] rounded-2xl"></div>
    <div class="skeleton h-[88px] rounded-2xl"></div>
  </div>

  <div class="card overflow-hidden">
    <!-- Filters & Search -->
    <div class="p-4 md:p-5 flex flex-col md:flex-row md:items-center gap-3 border-b border-gray-100 dark:border-white/[0.05]">
      <div class="segmented overflow-x-auto scrollbar-none">
        <?php foreach(['all'=>'Everyone','customer'=>'Customers','vendor'=>'Vendors','delivery_agent'=>'Riders'] as $v=>$l): ?>
          <button onclick="setRole('<?= $v ?>')" data-role="<?= $v ?>" class="role-btn whitespace-nowrap !px-3.5 !py-1.5 <?= $v==='all' ? 'is-active' : '' ?>"><?= $l ?></button>
        <?php endforeach; ?>
      </div>
      <div class="flex-1 relative md:max-w-sm md:ml-auto">
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"><?= icon('search', 'w-[18px] h-[18px]') ?></span>
        <input type="text" id="search-input" placeholder="Search name or phone…" class="input pl-11 py-2.5" oninput="debounceSearch()">
      </div>
    </div>

    <!-- Users table -->
    <div class="hidden md:grid grid-cols-[1fr_160px_130px_100px_24px] gap-4 px-5 py-2.5 bg-gray-50/80 dark:bg-white/[0.02] border-b border-gray-100 dark:border-white/[0.05] text-[11px] font-bold uppercase tracking-wider text-charcoal-muted dark:text-gray-500">
      <span>Person</span><span>Role</span><span>Credits</span><span>Status</span><span></span>
    </div>
    <div id="users-table" class="divide-y divide-gray-100 dark:divide-white/[0.04]">
      <?php for($i=0;$i<6;$i++): ?>
        <div class="p-4"><div class="skeleton h-10 rounded-xl"></div></div>
      <?php endfor; ?>
    </div>
    <div id="pagination" class="p-4 border-t border-gray-100 dark:border-white/[0.05] hidden flex items-center justify-between">
      <p id="page-info" class="text-xs text-charcoal-muted dark:text-gray-400"></p>
      <div class="flex gap-2">
        <button onclick="prevPage()" id="btn-prev" class="btn-outline btn-sm"><?= icon('chevron-left', 'w-3.5 h-3.5') ?>Prev</button>
        <button onclick="nextPage()" id="btn-next" class="btn-outline btn-sm">Next<?= icon('chevron-right', 'w-3.5 h-3.5') ?></button>
      </div>
    </div>
  </div>
</div>

<!-- User Detail / Edit Modal -->
<div id="user-modal" class="fixed inset-0 z-50 flex items-end md:items-center justify-center md:p-4 hidden">
  <div class="absolute inset-0 bg-charcoal/60 backdrop-blur-sm" onclick="closeUserModal()"></div>
  <div class="relative bg-white dark:bg-charcoal-light rounded-t-3xl md:rounded-3xl shadow-2xl border border-gray-200/70 dark:border-white/[0.06] w-full max-w-lg max-h-[92vh] overflow-y-auto animate-fade-slide-up">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-white/[0.06] flex items-center justify-between sticky top-0 bg-white/95 dark:bg-charcoal-light/95 backdrop-blur z-10">
      <h3 id="user-modal-title" class="font-bold text-lg">Person details</h3>
      <button onclick="closeUserModal()" class="icon-btn w-9 h-9 -mr-2" aria-label="Close"><?= icon('x', 'w-[18px] h-[18px]') ?></button>
    </div>
    <div id="user-modal-body" class="p-6 space-y-5">
      <div class="skeleton h-48 rounded-xl"></div>
    </div>
  </div>
</div>

<!-- Create User Modal -->
<div id="create-modal" class="fixed inset-0 z-50 flex items-end md:items-center justify-center md:p-4 hidden">
  <div class="absolute inset-0 bg-charcoal/60 backdrop-blur-sm" onclick="closeCreateModal()"></div>
  <div class="relative bg-white dark:bg-charcoal-light rounded-t-3xl md:rounded-3xl shadow-2xl border border-gray-200/70 dark:border-white/[0.06] w-full max-w-md max-h-[92vh] overflow-y-auto animate-fade-slide-up">
    <div class="px-6 pt-6 pb-2 flex items-start justify-between gap-3">
      <div class="flex items-center gap-3">
        <span class="icon-tile bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"><?= icon('user-check', 'w-5 h-5') ?></span>
        <div>
          <h3 class="font-bold text-lg">Add a person</h3>
          <p class="text-xs text-charcoal-muted dark:text-gray-400">Create an account on their behalf.</p>
        </div>
      </div>
      <button onclick="closeCreateModal()" class="icon-btn w-9 h-9 -mr-2" aria-label="Close"><?= icon('x', 'w-[18px] h-[18px]') ?></button>
    </div>
    <div class="px-6 py-4 space-y-4">
      <div class="grid grid-cols-2 gap-3">
        <div class="col-span-2">
          <label class="label">Full Name</label>
          <input id="c-name" type="text" placeholder="e.g. Amara Okafor" class="input">
        </div>
        <div>
          <label class="label">Phone</label>
          <input id="c-phone" type="text" placeholder="08011111111" class="input">
        </div>
        <div>
          <label class="label">Password</label>
          <input id="c-pass" type="password" placeholder="Min 6 chars" class="input">
        </div>
        <div class="col-span-2">
          <label class="label">Role</label>
          <select id="c-role" class="input" onchange="toggleRoleFields()">
            <option value="customer">Customer</option>
            <option value="vendor">Vendor</option>
            <option value="delivery_agent">Delivery Agent</option>
          </select>
        </div>
      </div>
      <div id="c-customer-fields" class="space-y-3">
        <div>
          <label class="label">Building</label>
          <select id="c-building" class="input" onchange="onBuildingChange()">
            <option value="">Select building…</option>
          </select>
        </div>
        <!-- Unit fields — shown after building selected -->
        <div id="c-unit-fields" class="hidden space-y-3">
          <div class="grid grid-cols-3 gap-2">
            <div>
              <label class="label">Block</label>
              <select id="c-block" class="input">
                <option value="">—</option>
              </select>
            </div>
            <div>
              <label class="label">Floor</label>
              <select id="c-floor" class="input" onchange="onFloorChange()">
                <option value="">—</option>
              </select>
            </div>
            <div>
              <label class="label">House No</label>
              <input id="c-houseno" type="text" class="input" placeholder="e.g. 5B" list="houseno-suggestions">
              <datalist id="houseno-suggestions"></datalist>
            </div>
          </div>
          <div id="c-taken-warning" class="hidden text-xs text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 ring-1 ring-inset ring-amber-500/20 rounded-xl px-3 py-2.5">
            Some units are already occupied. Check the house number.
          </div>
        </div>
        <!-- Loading state -->
        <div id="c-unit-loading" class="hidden">
          <div class="skeleton h-10 rounded-xl"></div>
        </div>
      </div>
      <div id="c-vendor-fields" class="space-y-3 hidden">
        <div><label class="label">Shop Name</label><input id="c-shopname" type="text" class="input" placeholder="Tunde Mart"></div>
        <div><label class="label">Shop Location (Building)</label><select id="c-shoploc" class="input"><option value="">Select building…</option></select></div>
      </div>
    </div>
    <div class="p-6 pt-2 flex gap-3">
      <button onclick="closeCreateModal()" class="btn-outline flex-1 py-3">Cancel</button>
      <button onclick="createUser()" class="btn-brand flex-1 py-3"><?= icon('check', 'w-4 h-4') ?>Create account</button>
    </div>
  </div>
</div>

<script>
const APP_URL = '<?= $appUrl ?>';
const TOKEN   = localStorage.getItem('td_token');
let currentRole = 'all', currentPage = 1, searchQ = '', totalPages = 1, searchTimer;
let buildings = [];

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function timeAgo(ts) {
  const d = Math.floor((Date.now()-new Date(ts))/1000);
  if(d<60)return'just now'; if(d<3600)return Math.floor(d/60)+'m ago';
  if(d<86400)return Math.floor(d/3600)+'h ago'; return Math.floor(d/86400)+'d ago';
}

function setRole(r) {
  currentRole = r; currentPage = 1;
  document.querySelectorAll('.role-btn').forEach(b => b.classList.toggle('is-active', b.dataset.role === r));
  loadUsers();
}

function debounceSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => { searchQ = document.getElementById('search-input').value; currentPage = 1; loadUsers(); }, 300);
}

function prevPage() { if(currentPage>1){currentPage--;loadUsers();} }
function nextPage() { if(currentPage<totalPages){currentPage++;loadUsers();} }

async function loadUsers() {
  let url = APP_URL + '/api/v1/admin/users.php?page=' + currentPage;
  if (currentRole !== 'all') url += '&role=' + currentRole;
  if (searchQ) url += '&search=' + encodeURIComponent(searchQ);
  const res  = await fetch(url, { headers:{'Authorization':'Bearer '+TOKEN} });
  const data = await res.json();
  const users = data.data?.users || [];
  totalPages  = data.data?.pages || 1;
  const total = data.data?.total || 0;
  const counts = data.data?.counts || {};
  currentPage = data.data?.page || 1;

  // Update role stats
  const totalUsers = (counts['customer']||0)+(counts['vendor']||0)+(counts['delivery_agent']||0);
  document.getElementById('role-stats').innerHTML = [
    { role:'customer',       label:'Customers', ic:'bag',   tile:'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300' },
    { role:'vendor',         label:'Vendors',   ic:'store', tile:'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300' },
    { role:'delivery_agent', label:'Riders',    ic:'bike',  tile:'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300' },
    { role:'all',            label:'Everyone',  ic:'users', tile:'bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-300', total: totalUsers },
  ].map(r => `
    <button onclick="setRole('${r.role}')" class="stat card-hover text-left flex items-center gap-4 ${currentRole === r.role ? 'ring-2 ring-amber-400/60' : ''}">
      <span class="icon-tile-lg ${r.tile}">${icon(r.ic, 'w-6 h-6')}</span>
      <span>
        <span class="block stat-value text-2xl">${r.total ?? (counts[r.role]||0)}</span>
        <span class="block stat-label mt-1">${r.label}</span>
      </span>
    </button>`).join('');

  const table = document.getElementById('users-table');
  if (!users.length) {
    table.innerHTML = LK.empty('users', 'No people found', 'Try a different name, phone number or filter.');
    document.getElementById('pagination').classList.add('hidden');
    return;
  }

  const roleMeta = {
    customer:       { label:'Customer', ic:'bag',   tone: LK.TONES.sky },
    vendor:         { label:'Vendor',   ic:'store', tone: LK.TONES.amber },
    delivery_agent: { label:'Rider',    ic:'bike',  tone: LK.TONES.emerald },
    admin:          { label:'Admin',    ic:'shield',tone: LK.TONES.violet },
  };

  table.innerHTML = users.map((u, i) => {
    const rm = roleMeta[u.role] || { label: u.role, ic: 'user', tone: LK.TONES.gray };
    const credits = u.credits || 0;
    return `
    <div class="group px-4 md:px-5 py-3.5 grid grid-cols-[1fr_auto] md:grid-cols-[1fr_160px_130px_100px_24px] items-center gap-3 md:gap-4 hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors cursor-pointer animate-card-appear" style="animation-delay:${i*20}ms" onclick="viewUser(${u.id})">
      <div class="flex items-center gap-3 min-w-0">
        ${LK.avatar(u.name, 'w-10 h-10 text-xs')}
        <div class="min-w-0">
          <p class="text-sm font-semibold truncate">${escHtml(u.name)}</p>
          <p class="text-xs text-charcoal-muted dark:text-gray-400 truncate">
            <span class="num">${escHtml(u.phone)}</span>${u.building_name ? ' · '+escHtml(u.building_name) : ''}${u.shop_name ? ' · '+escHtml(u.shop_name) : ''}
          </p>
        </div>
      </div>
      <div class="hidden md:block"><span class="badge ${rm.tone}">${icon(rm.ic, 'w-3.5 h-3.5')}${rm.label}</span></div>
      <div class="hidden md:block">
        ${u.role === 'delivery_agent' ? `
          <div class="flex items-center gap-2">
            <div class="w-14 h-1.5 rounded-full bg-gray-100 dark:bg-white/10 overflow-hidden"><div class="h-full rounded-full ${credits === 0 ? 'bg-red-500' : credits < 3 ? 'bg-amber-500' : 'bg-emerald-500'}" style="width:${Math.min(100, credits * 7)}%"></div></div>
            <span class="text-xs font-bold num">${credits}</span>
          </div>` : '<span class="text-gray-300 dark:text-gray-600">—</span>'}
      </div>
      <div class="flex md:block items-center gap-2">
        <span class="badge badge-dot ${u.is_active ? LK.TONES.emerald : LK.TONES.red}">${u.is_active ? 'Active' : 'Inactive'}</span>
      </div>
      <span class="hidden md:block">${icon('chevron-right', 'w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-amber-500 group-hover:translate-x-0.5 transition-all')}</span>
    </div>`; }).join('');

  const pag = document.getElementById('pagination');
  if (totalPages > 1) {
    pag.classList.remove('hidden');
    document.getElementById('page-info').textContent = `Page ${currentPage} of ${totalPages} (${total} users)`;
    document.getElementById('btn-prev').disabled = currentPage <= 1;
    document.getElementById('btn-next').disabled = currentPage >= totalPages;
  } else {
    pag.classList.add('hidden');
  }
}

async function viewUser(id) {
  document.getElementById('user-modal').classList.remove('hidden');
  document.getElementById('user-modal-title').textContent = 'User Details';
  document.getElementById('user-modal-body').innerHTML = '<div class="skeleton h-48 rounded-xl"></div>';

  const res  = await fetch(APP_URL + '/api/v1/admin/users.php?id=' + id, { headers:{'Authorization':'Bearer '+TOKEN} });
  const data = await res.json();
  const u    = data.data?.user;
  const txs  = data.data?.transactions || [];
  if (!u) { document.getElementById('user-modal-body').innerHTML = '<p class="text-center text-red-500">User not found</p>'; return; }

  document.getElementById('user-modal-title').textContent = u.name;

  const roleLabel = {customer:'Customer', vendor:'Vendor', delivery_agent:'Delivery rider', admin:'Administrator'};
  const isAgent = u.role === 'delivery_agent';
  const detail = (ic, label, value, sub = '', span = '') => `
    <div class="surface-muted p-3.5 ${span}">
      <p class="text-[11px] font-semibold text-charcoal-muted dark:text-gray-500 flex items-center gap-1.5">${icon(ic, 'w-3.5 h-3.5')}${label}</p>
      <p class="mt-1 text-sm font-semibold">${value}</p>
      ${sub ? `<p class="text-xs text-charcoal-muted dark:text-gray-400">${sub}</p>` : ''}
    </div>`;

  document.getElementById('user-modal-body').innerHTML = `
    <!-- Profile header -->
    <div class="flex items-center gap-4">
      ${LK.avatar(u.name, 'w-16 h-16 text-lg rounded-2xl')}
      <div class="min-w-0 flex-1">
        <h4 class="font-extrabold text-xl tracking-tight truncate">${escHtml(u.name)}</h4>
        <p class="text-sm text-charcoal-muted dark:text-gray-400">${roleLabel[u.role] || u.role}</p>
        <p class="text-xs text-charcoal-muted dark:text-gray-500 mt-0.5"><span class="num">${escHtml(u.phone)}</span> · Joined ${timeAgo(u.created_at)}</p>
      </div>
      <span class="badge badge-dot ${u.is_active ? LK.TONES.emerald : LK.TONES.red}">${u.is_active ? 'Active' : 'Inactive'}</span>
    </div>

    <!-- Details -->
    <div class="grid grid-cols-2 gap-2.5">
      ${u.building_name ? detail('building', 'Building', escHtml(u.building_name), '', u.block ? '' : 'col-span-2') : ''}
      ${u.block ? detail('home', 'Unit', `Block ${escHtml(u.block)} · Floor ${escHtml(u.floor)} · ${escHtml(u.house_no)}`) : ''}
      ${u.shop_name ? detail('store', 'Shop', escHtml(u.shop_name), escHtml(u.shop_location||''), 'col-span-2') : ''}
    </div>

    ${isAgent ? `
    <div class="relative overflow-hidden rounded-2xl hero-gradient text-white p-5 flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold text-white/80 flex items-center gap-1.5">${icon('zap', 'w-3.5 h-3.5')}Credit balance</p>
        <p class="text-3xl font-extrabold num mt-1">${u.credits} <span class="text-sm font-semibold text-white/80">credits</span></p>
      </div>
      <button onclick="adjustCredits(${u.id}, '${escHtml(u.name)}')" class="btn bg-white text-amber-700 hover:bg-amber-50 btn-sm shadow-md">${icon('edit', 'w-3.5 h-3.5')}Adjust</button>
    </div>` : ''}

    ${isAgent && txs.length ? `
    <div>
      <p class="text-[11px] font-bold uppercase tracking-wider text-charcoal-muted dark:text-gray-500 mb-2">Recent transactions</p>
      <div class="rounded-2xl border border-gray-200/80 dark:border-white/[0.07] divide-y divide-gray-100 dark:divide-white/[0.05]">
        ${txs.map(t => `<div class="flex items-center justify-between gap-3 text-sm px-3.5 py-2.5">
          <span class="flex items-center gap-2 min-w-0">
            <span class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 ${t.amount>0 ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-red-50 text-red-500 dark:bg-red-500/10 dark:text-red-300'}">${icon(t.amount>0 ? 'plus' : 'minus', 'w-3.5 h-3.5')}</span>
            <span class="truncate text-[13px]">${escHtml(t.description)}</span>
          </span>
          <span class="font-bold num ${t.amount>0?'text-emerald-600 dark:text-emerald-400':'text-red-500'}">${t.amount>0?'+':''}${t.amount}</span>
        </div>`).join('')}
      </div>
    </div>` : ''}

    <!-- Actions -->
    <div class="pt-4 border-t border-gray-100 dark:border-white/[0.06] flex flex-wrap gap-2">
      <button onclick="toggleUser(${u.id})" class="btn-outline btn-sm">${icon(u.is_active ? 'lock' : 'check-circle', 'w-3.5 h-3.5')}${u.is_active ? 'Deactivate' : 'Activate'}</button>
      <button onclick="resetPassword(${u.id}, '${escHtml(u.name)}')" class="btn-outline btn-sm">${icon('refresh', 'w-3.5 h-3.5')}Reset password</button>
      <button onclick="deleteUser(${u.id}, '${escHtml(u.name)}')" class="btn btn-sm ml-auto text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/25 hover:bg-red-50 dark:hover:bg-red-500/10">${icon('trash', 'w-3.5 h-3.5')}Delete</button>
    </div>`;
}

async function toggleUser(id) {
  const res  = await fetch(APP_URL + '/api/v1/admin/users.php', {
    method:'POST', headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json'},
    body: JSON.stringify({ action:'toggle_active', user_id: id })
  });
  const data = await res.json();
  if (data.status === 'success') { closeUserModal(); loadUsers(); showToast('Status updated', '', 'success'); }
  else showToast(data.message||'Error', '', 'error');
}

async function resetPassword(id, name) {
  const pass = prompt(`New password for ${name} (min 6 chars):`);
  if (!pass || pass.length < 6) { showToast('Password too short', '', 'error'); return; }
  const res  = await fetch(APP_URL + '/api/v1/admin/users.php', {
    method:'POST', headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json'},
    body: JSON.stringify({ action:'reset_password', user_id: id, password: pass })
  });
  const data = await res.json();
  if (data.status === 'success') showToast('Password reset!', '', 'success');
  else showToast(data.message||'Error', '', 'error');
}

async function deleteUser(id, name) {
  if (!confirm(`Delete "${name}"? This cannot be undone.`)) return;
  const res  = await fetch(APP_URL + '/api/v1/admin/users.php', {
    method:'POST', headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json'},
    body: JSON.stringify({ action:'delete_user', user_id: id })
  });
  const data = await res.json();
  if (data.status === 'success') { closeUserModal(); loadUsers(); showToast('User deleted', '', 'success'); }
  else showToast(data.message||'Error', '', 'error');
}

async function adjustCredits(userId, name) {
  const amountStr = prompt(`Adjust credits for ${name}\n(positive to add, negative to remove):`);
  if (amountStr === null) return;
  const amount = parseInt(amountStr);
  if (isNaN(amount) || amount === 0) { showToast('Invalid amount', '', 'error'); return; }
  const desc = prompt('Reason for adjustment:') || 'Manual admin adjustment';
  const res  = await fetch(APP_URL + '/api/v1/admin/adjust-credits.php', {
    method:'POST', headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json'},
    body: JSON.stringify({ user_id: userId, amount, description: desc })
  });
  const data = await res.json();
  if (data.status === 'success') {
    showToast('Credits adjusted!', `New balance: ${data.data.new_balance} credits`, 'success');
    viewUser(userId);
    loadUsers();
  } else showToast(data.message||'Error', '', 'error');
}

function closeUserModal() { document.getElementById('user-modal').classList.add('hidden'); }

// Create user
function openCreateModal() {
  // Reset form
  ['c-name','c-phone','c-pass','c-shopname','c-shoploc','c-houseno'].forEach(id => {
    const el = document.getElementById(id); if (el) el.value = '';
  });
  document.getElementById('c-role').value = 'customer';
  document.getElementById('c-building').value = '';
  document.getElementById('c-unit-fields').classList.add('hidden');
  document.getElementById('c-unit-loading').classList.add('hidden');
  document.getElementById('c-taken-warning').classList.add('hidden');
  toggleRoleFields();
  document.getElementById('create-modal').classList.remove('hidden');
  loadBuildingsForSelect();
}
function closeCreateModal() { document.getElementById('create-modal').classList.add('hidden'); }

function toggleRoleFields() {
  const role = document.getElementById('c-role').value;
  document.getElementById('c-customer-fields').classList.toggle('hidden', role !== 'customer');
  document.getElementById('c-vendor-fields').classList.toggle('hidden', role !== 'vendor');
}

let buildingUnitsCache = {};
let takenUnits = [];

async function loadBuildingsForSelect() {
  if (buildings.length) return renderBldgSelect();
  const res  = await fetch(APP_URL + '/api/v1/buildings/index.php');
  const data = await res.json();
  buildings  = Array.isArray(data.data) ? data.data : (data.data?.buildings || []);
  renderBldgSelect();
}

function renderBldgSelect() {
  const opts = '<option value="">Select building…</option>' +
    buildings.map(b => `<option value="${b.id}">${escHtml(b.name)}</option>`).join('');
  document.getElementById('c-building').innerHTML = opts;
  // Vendor shop location uses building name as value
  document.getElementById('c-shoploc').innerHTML =
    '<option value="">Select building…</option>' +
    buildings.map(b => `<option value="${escHtml(b.name)}">${escHtml(b.name)}</option>`).join('');
}

async function onBuildingChange() {
  const buildingId = document.getElementById('c-building').value;
  const unitFields  = document.getElementById('c-unit-fields');
  const loadingEl   = document.getElementById('c-unit-loading');

  // Reset
  unitFields.classList.add('hidden');
  document.getElementById('c-taken-warning').classList.add('hidden');
  if (!buildingId) return;

  // Show loading
  loadingEl.classList.remove('hidden');

  // Fetch from cache or API
  if (!buildingUnitsCache[buildingId]) {
    const res  = await fetch(APP_URL + '/api/v1/admin/building-units.php?building_id=' + buildingId,
      { headers: { 'Authorization': 'Bearer ' + TOKEN } });
    const data = await res.json();
    buildingUnitsCache[buildingId] = data.data || {};
  }

  const units = buildingUnitsCache[buildingId];
  takenUnits  = units.taken_units || [];

  // Populate Block dropdown
  const blockSel = document.getElementById('c-block');
  blockSel.innerHTML = '<option value="">Select block…</option>' +
    (units.blocks || []).map(b => `<option value="${escHtml(b)}">${escHtml(b)}</option>`).join('') +
    '<option value="__new__">+ Other (type below)</option>';

  // Populate Floor dropdown
  const floorSel = document.getElementById('c-floor');
  floorSel.innerHTML = '<option value="">Select floor…</option>' +
    (units.floors || []).map(f => `<option value="${escHtml(f)}">Floor ${escHtml(f)}</option>`).join('');

  // Clear house no
  document.getElementById('c-houseno').value = '';
  document.getElementById('houseno-suggestions').innerHTML = '';

  // Show taken warning if any units exist
  if (takenUnits.length > 0) {
    document.getElementById('c-taken-warning').classList.remove('hidden');
    document.getElementById('c-taken-warning').textContent =
      `${takenUnits.length} unit(s) already occupied: ${takenUnits.slice(0,8).join(', ')}${takenUnits.length > 8 ? '…' : ''}`;
  }

  loadingEl.classList.add('hidden');
  unitFields.classList.remove('hidden');
}

function onFloorChange() {
  const floor = document.getElementById('c-floor').value;
  if (!floor) return;
  // Auto-suggest house numbers for this floor: 5A, 5B, 5C, 5D, 5E, 5F
  const suggestions = ['A','B','C','D','E','F','G','H'].map(l => floor + l);
  const dl = document.getElementById('houseno-suggestions');
  dl.innerHTML = suggestions
    .filter(s => !takenUnits.includes(s))
    .map(s => `<option value="${s}">${s}</option>`).join('');
  // Pre-fill first available suggestion
  const input = document.getElementById('c-houseno');
  if (!input.value) {
    const first = suggestions.find(s => !takenUnits.includes(s));
    if (first) input.value = first;
  }
}

// Handle "Other" block selection — replace with text input temporarily
document.addEventListener('change', function(e) {
  if (e.target.id === 'c-block' && e.target.value === '__new__') {
    const newVal = prompt('Enter block letter or name:');
    if (newVal && newVal.trim()) {
      const opt = document.createElement('option');
      opt.value = newVal.trim();
      opt.textContent = newVal.trim();
      e.target.insertBefore(opt, e.target.lastElementChild);
      e.target.value = newVal.trim();
    } else {
      e.target.value = '';
    }
  }
});

async function createUser() {
  const role = document.getElementById('c-role').value;

  // Validate customer unit fields
  if (role === 'customer') {
    if (!document.getElementById('c-building').value) { showToast('Select a building', '', 'error'); return; }
    if (!document.getElementById('c-block').value)    { showToast('Select a block', '', 'error'); return; }
    if (!document.getElementById('c-floor').value)    { showToast('Select a floor', '', 'error'); return; }
    if (!document.getElementById('c-houseno').value.trim()) { showToast('Enter house number', '', 'error'); return; }
  }

  const body = {
    action:'create_user',
    name:     document.getElementById('c-name').value.trim(),
    phone:    document.getElementById('c-phone').value.trim(),
    password: document.getElementById('c-pass').value,
    role,
    building_id:  role==='customer' ? document.getElementById('c-building').value : null,
    block:        role==='customer' ? document.getElementById('c-block').value : null,
    floor:        role==='customer' ? document.getElementById('c-floor').value : null,
    house_no:     role==='customer' ? document.getElementById('c-houseno').value.trim() : null,
    shop_name:    role==='vendor'   ? document.getElementById('c-shopname').value : null,
    shop_location:role==='vendor'   ? document.getElementById('c-shoploc').value  : null,
  };
  const res  = await fetch(APP_URL + '/api/v1/admin/users.php', {
    method:'POST', headers:{'Authorization':'Bearer '+TOKEN,'Content-Type':'application/json'},
    body: JSON.stringify(body)
  });
  const data = await res.json();
  if (data.status === 'success') { closeCreateModal(); loadUsers(); showToast('User created!', '', 'success'); }
  else showToast(data.message||'Error', '', 'error');
}

loadUsers();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
