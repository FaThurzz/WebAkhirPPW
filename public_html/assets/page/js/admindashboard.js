/* ══════════════════════════════════════════════
   ThurzShop — admindashboard.js
   ══════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  /* ══════════════════════════════════════════════
     PANEL SWITCHING
  ══════════════════════════════════════════════ */
  const navItems = document.querySelectorAll('.adm-nav-item[data-panel]');
  const panels   = document.querySelectorAll('.adm-panel');

  function switchPanel(panelId) {
    navItems.forEach(n => n.classList.remove('active'));
    panels.forEach(p => p.classList.remove('active'));

    const target = document.getElementById('panel-' + panelId);
    if (target) target.classList.add('active');

    const nav = document.querySelector(`.adm-nav-item[data-panel="${panelId}"]`);
    if (nav) nav.classList.add('active');

    sessionStorage.setItem('adm_panel', panelId);
  }

  navItems.forEach(item => {
    item.addEventListener('click', () => switchPanel(item.dataset.panel));
  });

  // Link buttons inside panels (e.g. "Lihat Semua →")
  document.querySelectorAll('.adm-link-btn[data-panel]').forEach(btn => {
    btn.addEventListener('click', () => switchPanel(btn.dataset.panel));
  });

  // Restore last visited panel
  const savedPanel = sessionStorage.getItem('adm_panel') || 'overview';
  switchPanel(savedPanel);


  /* ══════════════════════════════════════════════
     SEARCH / FILTER TABLES
  ══════════════════════════════════════════════ */
  function bindSearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    input.addEventListener('input', () => {
      const q = input.value.trim().toLowerCase();
      table.querySelectorAll('tbody tr').forEach(row => {
        const text = (row.dataset.search || row.textContent).toLowerCase();
        row.classList.toggle('adm-hidden', q !== '' && !text.includes(q));
      });
    });
  }

  bindSearch('searchUsers',    'tableUsers');
  bindSearch('searchListings', 'tableListings');
  bindSearch('searchOrders',   'tableOrders');


  /* ══════════════════════════════════════════════
     ACTION HANDLERS (Ban, Delete, Confirm, etc.)
  ══════════════════════════════════════════════ */
  const ACTIONS = {
    'ban-user': {
      confirm : (name) => `Ban user "${name}"? Mereka tidak bisa login.`,
      url     : () => `${BASE_URL}pages/admin/action_user.php`,
      body    : (id)  => `action=ban&id=${id}`,
      onSuccess: (btn) => {
        const td = btn.closest('td');
        // Replace ban button with unban button
        const row  = btn.closest('tr');
        const name = btn.dataset.name;
        const newBtn = createActionBtn('unban-user', btn.dataset.id, name, 'success', unbanIcon(), 'Unban User');
        btn.replaceWith(newBtn);
        bindActionBtn(newBtn);
        // Update status badge in same row
        const statusTd = row.querySelector('.adm-badge--confirmed');
        if (statusTd) {
          statusTd.className = 'adm-badge adm-badge--cancelled';
          statusTd.textContent = 'Banned';
        }
        showToast(`User "${name}" berhasil di-ban.`, 'success');
      }
    },
    'unban-user': {
      confirm : (name) => `Unban user "${name}"?`,
      url     : () => `${BASE_URL}pages/admin/action_user.php`,
      body    : (id)  => `action=unban&id=${id}`,
      onSuccess: (btn) => {
        const row  = btn.closest('tr');
        const name = btn.dataset.name;
        const newBtn = createActionBtn('ban-user', btn.dataset.id, name, 'warn', banIcon(), 'Ban User');
        btn.replaceWith(newBtn);
        bindActionBtn(newBtn);
        const statusTd = row.querySelector('.adm-badge--cancelled');
        if (statusTd) {
          statusTd.className = 'adm-badge adm-badge--confirmed';
          statusTd.textContent = 'Aktif';
        }
        showToast(`User "${name}" berhasil di-unban.`, 'success');
      }
    },
    'delete-user': {
      confirm : (name) => `Hapus user "${name}" secara permanen? Semua data akan ikut terhapus.`,
      url     : () => `${BASE_URL}pages/admin/action_user.php`,
      body    : (id)  => `action=delete&id=${id}`,
      onSuccess: (btn) => {
        const name = btn.dataset.name;
        btn.closest('tr').remove();
        showToast(`User "${name}" berhasil dihapus.`, 'success');
      }
    },
    'delete-listing': {
      confirm : (name) => `Hapus listing "${name}"? Tindakan tidak bisa dibatalkan.`,
      url     : () => `${BASE_URL}pages/admin/action_listing.php`,
      body    : (id)  => `action=delete&id=${id}`,
      onSuccess: (btn) => {
        const name = btn.dataset.name;
        btn.closest('tr').remove();
        showToast(`Listing berhasil dihapus.`, 'success');
      }
    },
    'confirm-order': {
      confirm : () => `Konfirmasi pembayaran order ini?`,
      url     : () => `${BASE_URL}pages/admin/action_order.php`,
      body    : (id) => `action=confirm&id=${id}`,
      onSuccess: (btn) => {
        const row = btn.closest('tr');
        const badge = row.querySelector('.adm-badge');
        if (badge) {
          badge.className = 'adm-badge adm-badge--confirmed';
          badge.textContent = 'Dikonfirmasi';
        }
        btn.remove();
        showToast('Order berhasil dikonfirmasi.', 'success');
      }
    },
    'cancel-order': {
      confirm : () => `Batalkan order ini?`,
      url     : () => `${BASE_URL}pages/admin/action_order.php`,
      body    : (id) => `action=cancel&id=${id}`,
      onSuccess: (btn) => {
        const row = btn.closest('tr');
        const badge = row.querySelector('.adm-badge');
        if (badge) {
          badge.className = 'adm-badge adm-badge--cancelled';
          badge.textContent = 'Dibatalkan';
        }
        btn.remove();
        showToast('Order dibatalkan.', 'success');
      }
    },
    'delete-game': {
      confirm : (name) => `Hapus game "${name}"? Semua listing terkait bisa terpengaruh.`,
      url     : () => `${BASE_URL}pages/admin/action_game.php`,
      body    : (id)  => `action=delete&id=${id}`,
      onSuccess: (btn) => {
        const name = btn.dataset.name;
        btn.closest('.adm-game-card').remove();
        showToast(`Game "${name}" berhasil dihapus.`, 'success');
      }
    },
  };

  function bindActionBtn(btn) {
    btn.addEventListener('click', async () => {
      const action = btn.dataset.action;
      const id     = btn.dataset.id;
      const name   = btn.dataset.name || '';
      const def    = ACTIONS[action];
      if (!def) return;

      if (!confirm(def.confirm(name))) return;

      btn.disabled = true;
      btn.style.opacity = '.5';

      try {
        const res  = await fetch(def.url(), {
          method : 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body   : def.body(id),
        });
        const data = await res.json();

        if (data.success) {
          def.onSuccess(btn);
        } else {
          showToast(data.message || 'Gagal melakukan aksi.', 'error');
          btn.disabled = false;
          btn.style.opacity = '';
        }
      } catch {
        showToast('Gagal terhubung ke server.', 'error');
        btn.disabled = false;
        btn.style.opacity = '';
      }
    });
  }

  document.querySelectorAll('[data-action]').forEach(bindActionBtn);


  /* ══════════════════════════════════════════════
     MODAL: TAMBAH GAME
  ══════════════════════════════════════════════ */
  const addGameModal   = document.getElementById('addGameModal');
  const btnAddGame     = document.getElementById('btnAddGame');
  const btnCloseGameModal = document.getElementById('btnCloseGameModal');

  function openGameModal() {
    if (addGameModal) {
      addGameModal.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeGameModal() {
    if (addGameModal) {
      addGameModal.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  if (btnAddGame)       btnAddGame.addEventListener('click', openGameModal);
  if (btnCloseGameModal) btnCloseGameModal.addEventListener('click', closeGameModal);

  if (addGameModal) {
    addGameModal.addEventListener('click', (e) => {
      if (e.target === addGameModal) closeGameModal();
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeGameModal();
  });

  // Form submit: add game
  const formAddGame = document.getElementById('formAddGame');
  if (formAddGame) {
    formAddGame.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn  = formAddGame.querySelector('[type="submit"]');
      const orig = btn.innerHTML;
      btn.innerHTML = 'Menyimpan…';
      btn.disabled  = true;

      try {
        const res  = await fetch(formAddGame.action, {
          method: 'POST',
          body  : new FormData(formAddGame),
        });
        const data = await res.json();

        if (data.success) {
          closeGameModal();
          showToast('Game berhasil ditambahkan!', 'success');
          setTimeout(() => window.location.reload(), 1000);
        } else {
          showToast(data.message || 'Terjadi kesalahan.', 'error');
        }
      } catch {
        showToast('Gagal terhubung ke server.', 'error');
      } finally {
        btn.innerHTML = orig;
        btn.disabled  = false;
      }
    });
  }


  /* ══════════════════════════════════════════════
     TOAST NOTIFICATION
  ══════════════════════════════════════════════ */
  function showToast(message, type = 'success') {
    const existing = document.getElementById('adm-toast');
    if (existing) existing.remove();

    const colors = {
      success: '#12b76a',
      error  : '#dc2626',
      info   : 'var(--blue)',
    };

    const icons = {
      success: `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`,
      error  : `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
      info   : `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
    };

    const toast = document.createElement('div');
    toast.id = 'adm-toast';
    toast.style.cssText = `
      position: fixed; bottom: 28px; right: 28px; z-index: 9999;
      display: flex; align-items: center; gap: 10px;
      background: ${colors[type] || colors.info};
      color: #fff; padding: 12px 20px;
      border-radius: 10px; font-family: 'Outfit', sans-serif;
      font-size: 14px; font-weight: 600;
      box-shadow: 0 8px 24px rgba(0,0,0,.18);
      animation: fadeUp .25s ease;
      max-width: 340px;
    `;
    toast.innerHTML = (icons[type] || '') + message;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.transition = 'opacity .3s, transform .3s';
      toast.style.opacity    = '0';
      toast.style.transform  = 'translateY(8px)';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }


  /* ══════════════════════════════════════════════
     HELPER: CREATE DYNAMIC ACTION BUTTON
  ══════════════════════════════════════════════ */
  function createActionBtn(action, id, name, variant, iconHtml, title) {
    const btn = document.createElement('button');
    btn.className          = `adm-action-btn adm-action-btn--${variant}`;
    btn.dataset.action     = action;
    btn.dataset.id         = id;
    btn.dataset.name       = name;
    btn.title              = title;
    btn.innerHTML          = iconHtml;
    return btn;
  }

  function banIcon() {
    return `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>`;
  }

  function unbanIcon() {
    return `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`;
  }

});
