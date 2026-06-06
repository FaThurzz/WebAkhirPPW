/* ══════════════════════════════════════════════
   ThurzShop — main.js
   JS khusus halaman Index / Home
   (Navbar JS → header.php | Toast JS → footer.php)
   ══════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Game cards: click → marketplace filter ─── */
  document.querySelectorAll('.game-card').forEach(card => {
    card.addEventListener('click', () => {
      const game = card.querySelector('.game-name')?.textContent.trim();
      if (game) {
        window.location.href = `pages/marketplace.php?game=${encodeURIComponent(game)}`;
      }
    });
  });

  /* ── Deal featured card hover: subtle scale ─── */
  const featured = document.querySelector('.deal-featured');
  if (featured) {
    featured.addEventListener('mouseenter', () => {
      featured.style.transform  = 'scale(1.01)';
      featured.style.transition = 'transform .3s ease';
    });
    featured.addEventListener('mouseleave', () => {
      featured.style.transform = 'scale(1)';
    });
  }

  /* ── Scroll-reveal for cards ─────────────────── */
  const revealEls = document.querySelectorAll('.game-card, .deal-card, .trust-card');

  const style = document.createElement('style');
  style.textContent = `
    .game-card, .deal-card, .trust-card {
      opacity: 0;
      transform: translateY(16px);
      transition: opacity .5s ease, transform .5s ease;
    }
    .revealed {
      opacity: 1 !important;
      transform: translateY(0) !important;
    }
  `;
  document.head.appendChild(style);

  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        entry.target.style.animationDelay = `${i * 0.05}s`;
        entry.target.classList.add('revealed');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  revealEls.forEach(el => revealObserver.observe(el));

});
