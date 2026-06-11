/* ══════════════════════════════════════════════
   ThurzShop — dashboard.js  (User Dashboard)
   ══════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Sidebar nav: switch main panel ─────────── */
  const navItems = document.querySelectorAll('.db-nav-item[data-panel]');
  const panels   = document.querySelectorAll('.db-panel');

  function switchPanel(panelId) {
    // Deactivate all
    navItems.forEach(n => n.classList.remove('active'));
    panels.forEach(p => p.classList.remove('active'));

    // Activate target
    const target = document.getElementById('panel-' + panelId);
    if (target) target.classList.add('active');

    const nav = document.querySelector(`.db-nav-item[data-panel="${panelId}"]`);
    if (nav) nav.classList.add('active');

    // Save last active panel in sessionStorage
    sessionStorage.setItem('db_panel', panelId);
  }

  navItems.forEach(item => {
    item.addEventListener('click', () => {
      const panelId = item.dataset.panel;
      switchPanel(panelId);
    });
  });

  // Restore last visited panel
  const savedPanel = sessionStorage.getItem('db_panel') || 'overview';
  switchPanel(savedPanel);

  /* ── Transaksi sub-tabs (Penjualan / Pembelian) ─ */
  const tabs      = document.querySelectorAll('.db-tab[data-tab]');
  const tabPanels = document.querySelectorAll('.db-tab-panel');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tabPanels.forEach(p => p.classList.remove('active'));

      tab.classList.add('active');
      const tp = document.getElementById('tab-' + tab.dataset.tab);
      if (tp) tp.classList.add('active');
    });
  });

  /* ── Image upload: preview & drag-drop ───────── */
  const imageInput    = document.getElementById('lst_image');
  const imagePreview  = document.getElementById('lstImagePreview');
  const dropText      = document.getElementById('lstDropText');
  const dropIcon      = document.getElementById('lstDropIcon');
  const imageError    = document.getElementById('lstImageError');
  const dropzone      = document.getElementById('lstImageDropzone');

  function showImagePreview(file) {
    const allowed = ['image/jpeg', 'image/png', 'image/webp'];
    const maxSize = 2 * 1024 * 1024; // 2MB

    if (!allowed.includes(file.type)) {
      imageError.textContent = 'Format tidak didukung. Gunakan JPG, PNG, atau WEBP.';
      imageError.style.display = 'block';
      return false;
    }
    if (file.size > maxSize) {
      imageError.textContent = 'Ukuran file terlalu besar. Maksimal 2MB.';
      imageError.style.display = 'block';
      return false;
    }

    imageError.style.display = 'none';
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.src = e.target.result;
      imagePreview.style.display = 'block';
      dropText.style.display     = 'none';
      dropIcon.style.display     = 'none';
      if (dropzone) {
        dropzone.style.borderColor = 'var(--blue)';
        dropzone.style.background  = 'var(--blue-lt)';
      }
    };
    reader.readAsDataURL(file);
    return true;
  }

  if (imageInput) {
    imageInput.addEventListener('change', () => {
      if (imageInput.files[0]) showImagePreview(imageInput.files[0]);
    });
  }

  // Expose for ondrop handler
  window.handleImageDrop = (e) => {
    e.preventDefault();
    if (dropzone) {
      dropzone.style.borderColor = '';
      dropzone.style.background  = 'var(--bg)';
    }
    const file = e.dataTransfer.files[0];
    if (file && imageInput) {
      // Assign file ke input supaya ikut terkirim saat form submit
      const dt = new DataTransfer();
      dt.items.add(file);
      imageInput.files = dt.files;
      showImagePreview(file);
    }
  };

  // Reset preview saat modal ditutup
  function resetImagePreview() {
    if (imageInput)   imageInput.value = '';
    if (imagePreview) { imagePreview.src = ''; imagePreview.style.display = 'none'; }
    if (dropText)     dropText.style.display  = '';
    if (dropIcon)     dropIcon.style.display  = '';
    if (dropzone)     { dropzone.style.borderColor = ''; dropzone.style.background = 'var(--bg)'; }
    if (imageError)   imageError.style.display = 'none';
  }


  const modalBackdrop = document.getElementById('addListingModal');
  const btnOpenModal  = document.getElementById('btnAddListing');
  const btnCloseModal = document.getElementById('btnCloseModal');
  const btnCloseModal2 = document.getElementById('btnCloseModal2');
  const listingForm = document.getElementById('formAddListing');
  const addListingAction = listingForm ? listingForm.getAttribute('action') : '';
  const updateListingAction = 'update_listing.php';
  const modalTitle = document.getElementById('modalTitle');
  const listingSubmitText = document.getElementById('listingSubmitText');
  const imageRequiredMark = document.getElementById('lstImageRequired');

  function setListingFormMode(mode = 'add', data = {}) {
    if (!listingForm) return;

    listingForm.reset();
    resetImagePreview();
    listingForm.dataset.mode = mode;
    listingForm.action = mode === 'edit' ? updateListingAction : addListingAction;

    if (modalTitle) modalTitle.textContent = mode === 'edit' ? 'Edit Listing' : 'Jual Akun Game';
    if (listingSubmitText) listingSubmitText.textContent = mode === 'edit' ? 'Simpan Perubahan' : 'Tambah Listing';
    if (imageInput) imageInput.required = mode !== 'edit';
    if (imageRequiredMark) imageRequiredMark.style.display = mode === 'edit' ? 'none' : '';

    if (mode !== 'edit') return;

    const values = {
      lst_listing_id: data.id || '',
      lst_title: data.title || '',
      lst_game: data.gameId || '',
      lst_price: data.price || '',
      lst_rank: data.rank || '',
      lst_level: data.level || '',
      lst_server: data.server || '',
      lst_login_type: data.loginType || '',
      lst_id: data.accountId || '',
      lst_desc: data.description || '',
    };

    Object.entries(values).forEach(([id, value]) => {
      const field = document.getElementById(id);
      if (field) field.value = value;
    });

    if (data.imageUrl && imagePreview) {
      imagePreview.src = data.imageUrl;
      imagePreview.style.display = 'block';
      if (dropText) dropText.style.display = 'none';
      if (dropIcon) dropIcon.style.display = 'none';
      if (dropzone) {
        dropzone.style.borderColor = 'var(--blue)';
        dropzone.style.background = 'var(--blue-lt)';
      }
    }
  }

  function openListingModal(mode = 'add', data = {}) {
    setListingFormMode(mode, data);
    if (modalBackdrop) {
      modalBackdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }

  if (btnOpenModal && modalBackdrop) {
    btnOpenModal.addEventListener('click', () => {
      openListingModal('add');
    });
  }

  function closeModal() {
    if (modalBackdrop) {
      modalBackdrop.classList.remove('open');
      document.body.style.overflow = '';
      setListingFormMode('add');
    }
  }

  if (btnCloseModal) btnCloseModal.addEventListener('click', closeModal);
  if (btnCloseModal2) btnCloseModal2.addEventListener('click', closeModal);

  // Close on backdrop click
  if (modalBackdrop) {
    modalBackdrop.addEventListener('click', (e) => {
      if (e.target === modalBackdrop) closeModal();
    });
  }

  // ESC closes modal
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });

  /* ── Form profil: toggle edit ─────────────────── */
  const btnEditProfile  = document.getElementById('btnEditProfile');
  const btnCancelEdit   = document.getElementById('btnCancelEdit');
  const profileForm     = document.getElementById('profileForm');
  const profileInputs   = profileForm ? profileForm.querySelectorAll('input:not([readonly]), select') : [];

  function setEditMode(on) {
    profileInputs.forEach(inp => {
      inp.disabled = !on;
    });
    if (btnEditProfile) btnEditProfile.style.display = on ? 'none' : '';
    if (btnCancelEdit)  btnCancelEdit.style.display  = on ? '' : 'none';
    const saveBtn = document.getElementById('btnSaveProfile');
    if (saveBtn) saveBtn.style.display = on ? '' : 'none';
  }

  // Start disabled
  setEditMode(false);

  if (btnEditProfile) {
    btnEditProfile.addEventListener('click', () => setEditMode(true));
  }
  if (btnCancelEdit) {
    btnCancelEdit.addEventListener('click', () => {
      setEditMode(false);
      if (profileForm) profileForm.reset();
    });
  }

  /* ── Listing form: submit via AJAX ───────────── */
  if (listingForm) {
    listingForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn  = listingForm.querySelector('[type="submit"]');
      const orig = btn.innerHTML;
      btn.innerHTML = '<span>Menyimpan...</span>';
      btn.disabled    = true;
      const isEdit = listingForm.dataset.mode === 'edit';

      try {
        const res = await fetch(listingForm.getAttribute('action'), {
          method: 'POST',
          body: new FormData(listingForm),
        });
        const data = await res.json();

        if (data.success) {
          closeModal();
          showToast(data.message || (isEdit ? 'Listing berhasil diperbarui!' : 'Listing berhasil ditambahkan!'), 'success');
          setTimeout(() => window.location.reload(), 1200);
        } else {
          showToast(data.message || 'Terjadi kesalahan.', 'error');
        }
      } catch {
        showToast('Gagal terhubung ke server.', 'error');
      } finally {
        btn.innerHTML = orig;
        btn.disabled    = false;
      }
    });
  }

  /* ── Profile form: submit via AJAX ──────────── */
  const profileFormEl = document.getElementById('profileForm');
  if (profileFormEl) {
    profileFormEl.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn  = profileFormEl.querySelector('#btnSaveProfile');
      const orig = btn.textContent;
      btn.textContent = 'Menyimpan...';
      btn.disabled    = true;

      try {
        const res  = await fetch(profileFormEl.action, {
          method: 'POST',
          body: new FormData(profileFormEl),
        });
        const data = await res.json();

        if (data.success) {
          showToast('Profil berhasil diperbarui!', 'success');
          setEditMode(false);
        } else {
          showToast(data.message || 'Terjadi kesalahan.', 'error');
        }
      } catch {
        showToast('Gagal terhubung ke server.', 'error');
      } finally {
        btn.textContent = orig;
        btn.disabled    = false;
      }
    });
  }

  /* ── Toast notification ───────────────────────── */
  function showToast(message, type = 'success') {
    // Remove existing
    const existing = document.getElementById('db-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'db-toast';
    toast.style.cssText = `
      position: fixed; bottom: 28px; right: 28px; z-index: 9999;
      display: flex; align-items: center; gap: 10px;
      background: ${type === 'success' ? '#12b76a' : '#dc2626'};
      color: #fff; padding: 12px 20px;
      border-radius: 10px; font-family: 'Outfit', sans-serif;
      font-size: 14px; font-weight: 600;
      box-shadow: 0 8px 24px rgba(0,0,0,.18);
      animation: fadeUp .25s ease;
      max-width: 320px;
    `;
    const icon = type === 'success'
      ? `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`
      : `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`;
    toast.innerHTML = icon + message;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(8px)';
      toast.style.transition = 'opacity .3s, transform .3s';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  /* ── Delete listing confirm ───────────────────── */
  document.querySelectorAll('.btn-edit-listing').forEach(btn => {
    btn.addEventListener('click', () => {
      openListingModal('edit', btn.dataset);
    });
  });

  document.querySelectorAll('.btn-delete-listing').forEach(btn => {
    btn.addEventListener('click', async () => {
      if (!confirm('Hapus listing ini? Tindakan tidak dapat dibatalkan.')) return;

      const id = btn.dataset.id;
      try {
        const res  = await fetch('delete_listing.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `listing_id=${id}`,
        });
        const data = await res.json();
        if (data.success) {
          btn.closest('.db-item').remove();
          showToast('Listing dihapus.', 'success');
        } else {
          showToast(data.message || 'Gagal menghapus.', 'error');
        }
      } catch {
        showToast('Gagal terhubung ke server.', 'error');
      }
    });
  });

});
