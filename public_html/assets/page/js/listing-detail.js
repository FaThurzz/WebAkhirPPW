/* ══════════════════════════════════════════════
   ThurzShop — listing-detail.js
   JS khusus halaman Detail Listing
   ══════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Modal Beli ──────────────────────────────── */
  const overlay     = document.getElementById('modalBuy');
  const btnBeli     = document.getElementById('btnBeli');
  const btnChat     = document.getElementById('btnChat');
  const modalClose  = document.getElementById('modalClose');
  const modalCancel = document.getElementById('modalCancel');
  const modalConfirm = document.getElementById('modalConfirm');
  const paymentGroup = document.getElementById('paymentMethodGroup');

  /* ── Payment method radio cards ──────────────── */
  if (paymentGroup) {
    const options = paymentGroup.querySelectorAll('.ld-pay-option');
    options.forEach(opt => {
      opt.addEventListener('click', () => {
        options.forEach(o => o.classList.remove('selected'));
        opt.classList.add('selected');
        const input = opt.querySelector('input[type="radio"]');
        if (input) input.checked = true;
      });
    });
  }

  function getSelectedPaymentMethod() {
    if (!paymentGroup) return '';
    const checked = paymentGroup.querySelector('input[type="radio"]:checked');
    return checked ? checked.value : '';
  }

  function showDetailToast(message, type = 'info') {
    const existing = document.getElementById('ld-toast');
    if (existing) existing.remove();

    const colors = {
      success: '#12b76a',
      error: '#dc2626',
      info: 'var(--blue)',
    };

    const toast = document.createElement('div');
    toast.id = 'ld-toast';
    toast.style.cssText = `
      position: fixed; bottom: 28px; right: 28px; z-index: 9999;
      background: ${colors[type] || colors.info};
      color: #fff; padding: 12px 18px;
      border-radius: 10px; font-family: 'Outfit', sans-serif;
      font-size: 14px; font-weight: 600;
      box-shadow: 0 8px 24px rgba(0,0,0,.18);
      max-width: 340px;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.transition = 'opacity .3s, transform .3s';
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(8px)';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  function openModal() {
    if (!overlay) return;
    overlay.classList.add('open');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    if (!overlay) return;
    overlay.classList.remove('open');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  if (btnBeli)     btnBeli.addEventListener('click', openModal);
  if (modalClose)  modalClose.addEventListener('click', closeModal);
  if (modalCancel) modalCancel.addEventListener('click', closeModal);

  // Close on overlay click (outside modal box)
  if (overlay) {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeModal();
    });
  }

  // Close on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay?.classList.contains('open')) closeModal();
  });

  if (modalConfirm) {
    modalConfirm.addEventListener('click', async () => {
      const listingId = modalConfirm.dataset.listingId;
      const method = getSelectedPaymentMethod();
      const original = modalConfirm.innerHTML;

      modalConfirm.disabled = true;
      modalConfirm.innerHTML = '<span>Memproses...</span>';

      try {
        const res = await fetch('create_order.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            listing_id: listingId,
            payment_method: method,
          }),
        });
        const data = await res.json();

        if (data.success) {
          closeModal();
          sessionStorage.setItem('db_panel', 'transaksi');
          sessionStorage.setItem('db_purchase_tab', 'pembelian');
          showDetailToast(data.message || 'Order berhasil dibuat.', 'success');
          setTimeout(() => {
            window.location.href = data.redirect_url || 'users/dashboard.php';
          }, 700);
        } else {
          showDetailToast(data.message || 'Gagal membuat order.', 'error');
          modalConfirm.disabled = false;
          modalConfirm.innerHTML = original;
        }
      } catch {
        showDetailToast('Gagal terhubung ke server.', 'error');
        modalConfirm.disabled = false;
        modalConfirm.innerHTML = original;
      }
    });
  }

  /* ── Chat / Hubungi penjual ──────────────────── */
  if (btnChat) {
    btnChat.addEventListener('click', () => {
      if (window.showToast) {
        window.showToast('Fitur chat segera hadir!', 'info');
      }
    });
  }

  /* ── Related listing card reveal animation ───── */
  const relatedCards = document.querySelectorAll('.ld-related-grid .listing-card');

  const revealObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity   = '1';
        entry.target.style.transform = 'translateY(0)';
        revealObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.05 });

  relatedCards.forEach((card, i) => {
    card.style.opacity    = '0';
    card.style.transform  = 'translateY(20px)';
    card.style.transition = `opacity .4s ease ${i * 0.08}s, transform .4s ease ${i * 0.08}s, border-color .2s, box-shadow .2s`;
    revealObs.observe(card);
  });

  /* ── Page entry animation ────────────────────── */
  const entryEls = document.querySelectorAll('.ld-gallery, .ld-price-card, .ld-seller-card');
  entryEls.forEach((el, i) => {
    el.style.opacity   = '0';
    el.style.transform = 'translateY(18px)';
    el.style.transition = `opacity .45s ease ${i * 0.07}s, transform .45s ease ${i * 0.07}s`;
    // Trigger reflow then animate in
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        el.style.opacity   = '1';
        el.style.transform = 'translateY(0)';
      });
    });
  });

  /* ── Gallery image lightbox (simple zoom) ──────── */
  const galleryImg = document.querySelector('.ld-gallery-img');
  if (galleryImg) {
    galleryImg.style.cursor = 'zoom-in';
    galleryImg.addEventListener('click', () => {
      // Simple fullscreen overlay
      const lb = document.createElement('div');
      lb.style.cssText = `
        position:fixed;inset:0;z-index:9000;
        background:rgba(0,0,0,.88);
        display:flex;align-items:center;justify-content:center;
        cursor:zoom-out;padding:24px;
      `;
      const img = document.createElement('img');
      img.src = galleryImg.src;
      img.style.cssText = 'max-width:90vw;max-height:85vh;border-radius:12px;object-fit:contain;box-shadow:0 20px 60px rgba(0,0,0,.5);';
      lb.appendChild(img);
      lb.addEventListener('click', () => lb.remove());
      document.body.appendChild(lb);
    });
  }

});
