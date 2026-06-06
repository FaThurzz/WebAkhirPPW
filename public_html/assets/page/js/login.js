/* ══════════════════════════════════════════════
   ThurzShop — login.js
   JS khusus halaman Login
   ══════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Toggle show/hide password ───────────────── */
  const togglePw = document.getElementById('togglePw');
  if (togglePw) {
    const pwInput = document.getElementById('password');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeOff  = document.getElementById('eyeOff');

    togglePw.addEventListener('click', () => {
      const isHidden        = pwInput.type === 'password';
      pwInput.type          = isHidden ? 'text' : 'password';
      eyeOpen.style.display = isHidden ? 'none'  : 'block';
      eyeOff.style.display  = isHidden ? 'block' : 'none';
    });
  }

  /* ── Form validation & loading state ─────────── */
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    const submitBtn  = document.getElementById('submitBtn');
    const btnText    = document.getElementById('btnText');
    const btnArrow   = document.getElementById('btnArrow');
    const btnSpinner = document.getElementById('btnSpinner');

    function setFieldError(groupId, errId, msg) {
      document.getElementById(groupId).classList.add('has-error');
      document.getElementById(errId).textContent = msg;
    }
    function clearFieldError(groupId) {
      document.getElementById(groupId).classList.remove('has-error');
    }

    document.getElementById('username')?.addEventListener('input', () => clearFieldError('group-username'));
    document.getElementById('password')?.addEventListener('input', () => clearFieldError('group-password'));

    loginForm.addEventListener('submit', (e) => {
      let valid = true;
      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value;

      clearFieldError('group-username');
      clearFieldError('group-password');

      if (!username) {
        setFieldError('group-username', 'err-username', 'Username tidak boleh kosong.');
        valid = false;
      }
      if (!password) {
        setFieldError('group-password', 'err-password', 'Password tidak boleh kosong.');
        valid = false;
      }

      if (!valid) { e.preventDefault(); return; }

      submitBtn.disabled       = true;
      btnText.textContent      = 'Masuk...';
      btnArrow.style.display   = 'none';
      btnSpinner.style.display = 'block';
    });
  }

});