<?php
$currentYear = date("Y");
?>

<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="index.php" class="nav-logo">Thurz<span>Shop</span></a>

        <p>© <?php echo $currentYear; ?> ThurzShop. All rights reserved.<br/>Secure marketplace for gaming assets.<br/>Built by gamers, for gamers.</p>
      </div>
      <div class="footer-col">
        <h4>Marketplace</h4>
        <ul>
          <li><a href="#">All Accounts</a></li>
          <li><a href="#">Top Rated Sellers</a></li>
          <li><a href="#">New Listings</a></li>
          <li><a href="#">Sell Guide</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Company</h4>
        <ul>
          <li><a href="#">About Us</a></li>
          <li><a href="#">Terms of Service</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Payment Methods</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <p class="footer-support">Have questions? We're here to help.</p>
        <button class="footer-support-btn">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
          Contact Support
        </button>
        <div class="footer-social">
          <div class="social-btn" title="Instagram">
            <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
          </div>
          <div class="social-btn" title="Discord">
            <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24">
              <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span class="footer-copy">© <?php echo $currentYear; ?> ThurzShop Marketplace · Proyek Akhir Kuliah</span>
      <div class="footer-links">
        <a href="#">Terms</a>
        <a href="#">Privacy</a>
        <a href="#">Cookies</a>
      </div>
    </div>
  </div>
</footer>

<style>
  /* ── FOOTER ──────────────────────────────────── */
  footer {
    background: var(--white);
    border-top: 1px solid var(--border);
    padding: var(--space-6) var(--gutter) var(--space-4);
  }
  .footer-inner { max-width: var(--container); margin: 0 auto; }
  .footer-grid {
    display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr;
    gap: var(--space-5); margin-bottom: var(--space-5);
  }
  .footer-brand .nav-logo { font-size: 20px; margin-bottom: 10px; display: inline-block; }
  .footer-brand p { font-size: 13px; color: var(--muted); line-height: 1.7; max-width: 220px; }
  .footer-col h4 { font-size: 14px; font-weight: 600; margin-bottom: 14px; color: var(--text); }
  .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
  .footer-col ul a { font-size: 13px; color: var(--muted); transition: color .2s; }
  .footer-col ul a:hover { color: var(--blue); }
  .footer-support { font-size: 13px; color: var(--muted); margin-bottom: 14px; }

  .footer-support-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--white); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 9px 16px;
    font-size: 13px; font-weight: 600; color: var(--text); cursor: pointer;
    font-family: 'Outfit', sans-serif;
    transition: all .2s; width: 100%; justify-content: center;
  }
  .footer-support-btn:hover { border-color: var(--blue); color: var(--blue); }

  .footer-social { display: flex; gap: 10px; margin-top: 14px; }
  .social-btn {
    width: 36px; height: 36px; border-radius: var(--radius);
    background: var(--white); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .2s; color: var(--muted);
  }
  .social-btn:hover { border-color: var(--blue); color: var(--blue); }

  .footer-bottom {
    border-top: 1px solid var(--border); padding-top: var(--space-3);
    display: flex; justify-content: space-between; align-items: center;
  }
  .footer-copy { font-size: 12px; color: var(--muted); }
  .footer-links { display: flex; gap: 20px; }
  .footer-links a { font-size: 12px; color: var(--muted); transition: color .2s; }
  .footer-links a:hover { color: var(--blue); }

  @media (max-width: 768px) {
    .footer-grid { grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 480px) {
    .footer-grid { grid-template-columns: 1fr; }
  }
</style>

<script src="<?php echo $base_url; ?>assets/main.js"></script>

<script>
/* ── Toast notification helper ───────────────── */
window.showToast = (message, type = 'info') => {
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.textContent = message;

  if (!document.getElementById('toast-style')) {
    const toastStyle = document.createElement('style');
    toastStyle.id = 'toast-style';
    toastStyle.textContent = `
      .toast {
        position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        padding: 12px 20px; border-radius: 10px;
        font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 600;
        box-shadow: 0 8px 32px rgba(0,0,0,.15);
        animation: toastIn .3s ease, toastOut .3s ease 2.7s forwards;
        max-width: 320px;
      }
      .toast-info    { background: var(--blue);  color: #fff; }
      .toast-success { background: var(--green); color: #fff; }
      .toast-error   { background: #ef4444;      color: #fff; }
      @keyframes toastIn  { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
      @keyframes toastOut { from { opacity:1; } to { opacity:0; transform:translateY(10px); } }
    `;
    document.head.appendChild(toastStyle);
  }

  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
};
</script>
</body>
</html>
