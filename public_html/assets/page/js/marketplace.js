/* ══════════════════════════════════════════════
   ThurzShop — marketplace.js
   JS khusus halaman Marketplace
   ══════════════════════════════════════════════ */

/* ── Set game filter via button click ──────────── */
function setGame(game) {
  document.getElementById('gameInput').value = game;
  document.getElementById('filterForm').submit();
}

document.addEventListener('DOMContentLoaded', () => {

  /* ── Navbar: sticky shadow on scroll ─────────── */
  const nav = document.querySelector('nav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.style.boxShadow = window.scrollY > 10
        ? '0 2px 20px rgba(0,0,0,.08)'
        : 'none';
    });
  }

  /* ── Mobile: toggle filter sidebar ───────────── */
  const toggleBtn = document.getElementById('filterToggle');
  const sidebar   = document.getElementById('filterSidebar');

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      toggleBtn.textContent = sidebar.classList.contains('open')
        ? '✕ Tutup Filter'
        : '⚙ Filter';
    });
  }

  /* ── Listing card reveal animation ───────────── */
  const cards = document.querySelectorAll('.listing-card');

  const revealObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity   = '1';
        entry.target.style.transform = 'translateY(0)';
        revealObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.05 });

  cards.forEach((card, i) => {
    card.style.opacity    = '0';
    card.style.transform  = 'translateY(20px)';
    card.style.transition = `opacity .4s ease ${i * 0.06}s, transform .4s ease ${i * 0.06}s, border-color .2s, box-shadow .2s`;
    revealObs.observe(card);
  });

});