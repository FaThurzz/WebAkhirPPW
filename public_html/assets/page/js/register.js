/* ══════════════════════════════════════════════
   ThurzShop — register.js
   JS khusus halaman Register
   ══════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Helper: set / clear field error ─────────── */
  function setError(groupId, errId, msg) {
    const group = document.getElementById(groupId);
    const err   = document.getElementById(errId);
    if (group) group.classList.add('has-error');
    if (err)   err.textContent = msg;
  }
  function clearError(groupId) {
    const group = document.getElementById(groupId);
    if (group) group.classList.remove('has-error');
  }

  /* ── Helper: set check icon ──────────────────── */
  function setCheck(checkId, state) {
    const el = document.getElementById(checkId);
    if (!el) return;
    el.className = 'form-input-check';
    if (state === 'valid')   { el.classList.add('valid');   el.textContent = '✓'; }
    if (state === 'invalid') { el.classList.add('invalid'); el.textContent = '✗'; }
    if (state === 'none')    { el.textContent = ''; }
  }

  /* ── Nama Lengkap: live validation ──────────── */
  const fullNameInput = document.getElementById('full_name');
  fullNameInput?.addEventListener('input', () => {
    const val = fullNameInput.value.trim();
    clearError('group-full_name');
    if (!val) {
      setCheck('check-full_name', 'none');
    } else if (val.length < 3) {
      setError('group-full_name', 'err-full_name', 'Minimal 3 karakter.');
      setCheck('check-full_name', 'invalid');
    } else {
      setCheck('check-full_name', 'valid');
    }
  });

  /* ── Nomor Telepon: live validation ──────────── */
  const phoneInput = document.getElementById('phone_number');
  phoneInput?.addEventListener('input', () => {
    const val = phoneInput.value.trim();
    clearError('group-phone_number');
    if (!val) {
      setCheck('check-phone_number', 'none');
    } else if (!/^[0-9+\-\s]{8,15}$/.test(val)) {
      setError('group-phone_number', 'err-phone_number', 'Format nomor telepon tidak valid.');
      setCheck('check-phone_number', 'invalid');
    } else {
      setCheck('check-phone_number', 'valid');
    }
  });

  /* ── Username: live validation ───────────────── */
  const usernameInput = document.getElementById('username');
  usernameInput?.addEventListener('input', () => {
    const val = usernameInput.value.trim();
    clearError('group-username');
    if (!val) {
      setCheck('check-username', 'none');
    } else if (val.length < 3) {
      setError('group-username', 'err-username', 'Minimal 3 karakter.');
      setCheck('check-username', 'invalid');
    } else if (!/^[a-zA-Z0-9_]+$/.test(val)) {
      setError('group-username', 'err-username', 'Hanya huruf, angka, dan underscore.');
      setCheck('check-username', 'invalid');
    } else {
      setCheck('check-username', 'valid');
    }
  });

  /* ── Email: live validation ──────────────────── */
  const emailInput = document.getElementById('email');
  emailInput?.addEventListener('input', () => {
    const val = emailInput.value.trim();
    clearError('group-email');
    if (!val) {
      setCheck('check-email', 'none');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
      setError('group-email', 'err-email', 'Format email tidak valid.');
      setCheck('check-email', 'invalid');
    } else {
      setCheck('check-email', 'valid');
    }
  });

  /* ── Password: strength meter ────────────────── */
  const passwordInput   = document.getElementById('password');
  const pwStrength      = document.getElementById('pwStrength');
  const pwStrengthFill  = document.getElementById('pwStrengthFill');
  const pwStrengthLabel = document.getElementById('pwStrengthLabel');

  function getStrength(pw) {
    let score = 0;
    if (pw.length >= 6)                   score++;
    if (pw.length >= 10)                  score++;
    if (/[A-Z]/.test(pw))                 score++;
    if (/[0-9]/.test(pw))                 score++;
    if (/[^a-zA-Z0-9]/.test(pw))         score++;
    if (score <= 1) return 'weak';
    if (score <= 3) return 'medium';
    return 'strong';
  }

  const strengthLabel = { weak: 'Lemah', medium: 'Sedang', strong: 'Kuat' };

  passwordInput?.addEventListener('input', () => {
    const val = passwordInput.value;
    clearError('group-password');

    if (!val) {
      pwStrength.style.display = 'none';
      return;
    }

    pwStrength.style.display = 'flex';
    const level = getStrength(val);
    pwStrengthFill.className  = `pw-strength-fill ${level}`;
    pwStrengthLabel.className = `pw-strength-label ${level}`;
    pwStrengthLabel.textContent = strengthLabel[level];

    if (val.length < 6) {
      setError('group-password', 'err-password', 'Minimal 6 karakter.');
    }

    // Re-validasi konfirmasi jika sudah diisi
    const val2 = document.getElementById('password2')?.value;
    if (val2) validatePassword2();
  });

  /* ── Konfirmasi password: live match check ────── */
  const password2Input = document.getElementById('password2');

  function validatePassword2() {
    const pw1 = passwordInput?.value   ?? '';
    const pw2 = password2Input?.value  ?? '';
    clearError('group-password2');
    if (!pw2) return;
    if (pw1 !== pw2) {
      setError('group-password2', 'err-password2', 'Password tidak cocok.');
    }
  }

  password2Input?.addEventListener('input', validatePassword2);

  /* ── Toggle show/hide password ───────────────── */
  function bindToggle(btnId, inputId, openId, offId) {
    const btn   = document.getElementById(btnId);
    const input = document.getElementById(inputId);
    const open  = document.getElementById(openId);
    const off   = document.getElementById(offId);
    if (!btn || !input) return;

    btn.addEventListener('click', () => {
      const isHidden   = input.type === 'password';
      input.type       = isHidden ? 'text' : 'password';
      open.style.display = isHidden ? 'none'  : 'block';
      off.style.display  = isHidden ? 'block' : 'none';
    });
  }

  bindToggle('togglePw',  'password',  'eyeOpen',  'eyeOff');
  bindToggle('togglePw2', 'password2', 'eyeOpen2', 'eyeOff2');

  /* ── Form submit: final validation + loading ─── */
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    const submitBtn  = document.getElementById('submitBtn');
    const btnText    = document.getElementById('btnText');
    const btnArrow   = document.getElementById('btnArrow');
    const btnSpinner = document.getElementById('btnSpinner');

    registerForm.addEventListener('submit', (e) => {
      let valid = true;

      const fullName    = fullNameInput?.value.trim()     ?? '';
      const phone       = phoneInput?.value.trim()         ?? '';
      const username = usernameInput?.value.trim()    ?? '';
      const email    = emailInput?.value.trim()        ?? '';
      const password = passwordInput?.value            ?? '';
      const pw2      = password2Input?.value           ?? '';
      const terms    = document.getElementById('terms')?.checked;

      // Reset semua error
      ['group-full_name','group-phone_number','group-username','group-email','group-password','group-password2','group-terms']
        .forEach(id => clearError(id));

      if (!fullName || fullName.length < 3) {
        setError('group-full_name', 'err-full_name', fullName ? 'Minimal 3 karakter.' : 'Nama lengkap tidak boleh kosong.');
        valid = false;
      }
      if (!phone || !/^[0-9+\-\s]{8,15}$/.test(phone)) {
        setError('group-phone_number', 'err-phone_number', phone ? 'Format nomor telepon tidak valid.' : 'Nomor telepon tidak boleh kosong.');
        valid = false;
      }
      if (!username || username.length < 3 || !/^[a-zA-Z0-9_]+$/.test(username)) {
        setError('group-username', 'err-username', username ? 'Username tidak valid.' : 'Username tidak boleh kosong.');
        valid = false;
      }
      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        setError('group-email', 'err-email', email ? 'Format email tidak valid.' : 'Email tidak boleh kosong.');
        valid = false;
      }
      if (!password || password.length < 6) {
        setError('group-password', 'err-password', password ? 'Minimal 6 karakter.' : 'Password tidak boleh kosong.');
        valid = false;
      }
      if (!pw2) {
        setError('group-password2', 'err-password2', 'Konfirmasi password tidak boleh kosong.');
        valid = false;
      } else if (password !== pw2) {
        setError('group-password2', 'err-password2', 'Password tidak cocok.');
        valid = false;
      }
      if (!terms) {
        setError('group-terms', 'err-terms', 'Kamu harus menyetujui syarat & ketentuan.');
        valid = false;
      }

      if (!valid) { e.preventDefault(); return; }

      // Loading state
      submitBtn.disabled       = true;
      btnText.textContent      = 'Membuat akun...';
      btnArrow.style.display   = 'none';
      btnSpinner.style.display = 'block';
    });
  }

});