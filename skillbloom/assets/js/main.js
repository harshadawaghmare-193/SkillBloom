/* SkillBloom — Main JS */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  initDropdowns();
  initModals();
  initPasswordToggle();
  initLiveSearch();
  initProgressBars();
  initFormValidation();
  autoHideAlerts();
});

/* ── Dropdowns ── */
function initDropdowns() {
  document.querySelectorAll('.avatar-wrap').forEach(wrap => {
    const btn = wrap.querySelector('.avatar-btn');
    if (btn) btn.addEventListener('click', e => { e.stopPropagation(); wrap.classList.toggle('open'); });
  });
  document.addEventListener('click', () => document.querySelectorAll('.avatar-wrap.open').forEach(w => w.classList.remove('open')));
}

/* ── Modals ── */
function initModals() {
  // Open
  document.querySelectorAll('[data-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.modal;
      document.getElementById(id)?.classList.add('open');
    });
  });
  // Close buttons
  document.querySelectorAll('.modal-close, [data-modal-close]').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.modal-overlay')?.classList.remove('open'));
  });
  // Click outside
  document.querySelectorAll('.modal-overlay').forEach(ov => {
    ov.addEventListener('click', e => { if (e.target === ov) ov.classList.remove('open'); });
  });
}

function openModal(id) { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

/* ── Password Toggle ── */
function initPasswordToggle() {
  document.querySelectorAll('.pw-eye').forEach(btn => {
    btn.addEventListener('click', () => {
      const inp = btn.previousElementSibling;
      if (!inp) return;
      inp.type = inp.type === 'password' ? 'text' : 'password';
      btn.textContent = inp.type === 'password' ? '👁️' : '🙈';
    });
  });
}

/* ── Table Live Search ── */
function initLiveSearch() {
  const inp = document.getElementById('tblSearch');
  if (!inp) return;
  inp.addEventListener('input', () => {
    const q = inp.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}

/* ── Progress Bars ── */
function initProgressBars() {
  document.querySelectorAll('.progress-fill[data-w]').forEach(el => {
    setTimeout(() => el.style.width = el.dataset.w + '%', 150);
  });
}

/* ── Form Validation ── */
function initFormValidation() {
  document.querySelectorAll('form.needs-val').forEach(form => {
    form.addEventListener('submit', e => {
      if (!validateForm(form)) e.preventDefault();
    });
  });
}

function validateForm(form) {
  let ok = true;
  form.querySelectorAll('[required]').forEach(f => {
    clearErr(f);
    if (!f.value.trim()) { showErr(f, 'This field is required.'); ok = false; }
    else if (f.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f.value)) { showErr(f, 'Enter a valid email.'); ok = false; }
    else if (f.id === 'confirm_password') {
      const pw = form.querySelector('#password,#new_password');
      if (pw && f.value !== pw.value) { showErr(f, 'Passwords do not match.'); ok = false; }
    }
  });
  return ok;
}

function showErr(f, msg) {
  f.classList.add('error');
  let e = f.parentElement.querySelector('.err-msg');
  if (!e) { e = document.createElement('span'); e.className = 'err-msg'; f.parentElement.appendChild(e); }
  e.textContent = msg;
  f.addEventListener('input', () => clearErr(f), { once: true });
}
function clearErr(f) { f.classList.remove('error'); const e = f.parentElement.querySelector('.err-msg'); if(e) e.textContent = ''; }

/* ── Auto-hide Alerts ── */
function autoHideAlerts() {
  document.querySelectorAll('.alert').forEach(a => {
    setTimeout(() => { a.style.transition = 'opacity .5s'; a.style.opacity = '0'; setTimeout(() => a.remove(), 500); }, 4500);
  });
}

/* ── Confirm Delete ── */
function confirmDel(msg = 'Delete this item? This cannot be undone.') { return confirm(msg); }

/* ── Star Rating ── */
function initStars(containerId) {
  const c = document.getElementById(containerId);
  if (!c) return;
  const inp = c.querySelector('input');
  c.querySelectorAll('.star').forEach((s, i) => {
    s.addEventListener('click', () => { inp.value = i + 1; updateStars(c, i + 1); });
    s.addEventListener('mouseover', () => updateStars(c, i + 1));
  });
  c.addEventListener('mouseleave', () => updateStars(c, inp.value));
}
function updateStars(c, val) {
  c.querySelectorAll('.star').forEach((s, i) => { s.style.color = i < val ? 'var(--warning)' : 'var(--gray-300)'; });
}

/* ── Payment Timer (checkout) ── */
function startPayTimer(secs) {
  const el = document.getElementById('pay-timer');
  if (!el) return;
  const iv = setInterval(() => {
    secs--;
    el.textContent = `${Math.floor(secs/60)}:${String(secs%60).padStart(2,'0')}`;
    if (secs <= 0) { clearInterval(iv); el.closest('.timer-box')?.remove(); }
  }, 1000);
}
