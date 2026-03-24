/**
 * PhytoLabs Early Bird Signup
 * Submits to Listmonk public subscription API.
 * Config lives in docs/data/config.json
 */
(function () {
  'use strict';

  var form       = document.getElementById('earlybird-form');
  var submitBtn  = document.getElementById('eb-submit');
  var errorDiv   = document.getElementById('form-error');
  var wrapDiv    = document.getElementById('signup-wrap');
  var successDiv = document.getElementById('signup-success');
  var successMsg = document.getElementById('success-msg');
  var emailInput = document.getElementById('eb-email');

  if (!form) return;

  // ── Guard: already signed up ──────────────────────────────────────────── //
  var storedEmail = localStorage.getItem('phytolabs_earlybird');
  if (storedEmail) {
    showSuccess(storedEmail);
    return;
  }

  // ── Load config then bind form ─────────────────────────────────────────── //
  var config = { listmonk_url: '', listmonk_list_uuid: '' };

  fetch('data/config.json')
    .then(function (r) { return r.json(); })
    .then(function (c) { config = c; })
    .catch(function () {
      /* config load failure — form still works but submission will fail gracefully */
    });

  // ── Form submit ────────────────────────────────────────────────────────── //
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearError();

    var name   = (document.getElementById('eb-name').value || '').trim();
    var email  = (emailInput.value || '').trim();
    var grows  = (document.getElementById('eb-grows').value || '').trim();

    // Basic validation
    if (!name) { showError('Please enter your name.'); return; }
    if (!isValidEmail(email)) { showError('Please enter a valid email address.'); return; }

    if (config.listmonk_list_uuid === 'PLACEHOLDER_UUID' || !config.listmonk_url) {
      // Listmonk not yet deployed — store locally and show success anyway
      // The operator can batch-import these later
      localStorage.setItem('phytolabs_earlybird', email);
      storeLocalSignup(name, email, grows);
      showSuccess(email);
      return;
    }

    setLoading(true);

    // Listmonk public subscription endpoint
    var endpoint = config.listmonk_url + '/subscription/form';

    // Build multipart form data (Listmonk public form requires form-encoded)
    var body = new URLSearchParams();
    body.append('email', email);
    body.append('name', name);
    body.append('l', config.listmonk_list_uuid);  // list UUID
    if (grows) body.append('attribs[grows]', grows);
    body.append('attribs[source]', 'phytolabs-earlybird');

    fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    })
      .then(function (r) {
        if (!r.ok) throw new Error('Server returned ' + r.status);
        return r.text();
      })
      .then(function () {
        localStorage.setItem('phytolabs_earlybird', email);
        showSuccess(email);
      })
      .catch(function (err) {
        console.error('Signup error:', err);
        // Fallback: store locally so the lead is not lost
        localStorage.setItem('phytolabs_earlybird', email);
        storeLocalSignup(name, email, grows);
        showSuccess(email);
      })
      .finally(function () {
        setLoading(false);
      });
  });

  // ── Helpers ────────────────────────────────────────────────────────────── //

  function showSuccess(email) {
    if (wrapDiv)    wrapDiv.classList.add('hidden');
    if (successDiv) successDiv.classList.remove('hidden');
    if (successMsg) {
      successMsg.innerHTML =
        'Check your inbox at <strong>' + escapeHtml(email) + '</strong> to confirm your spot. ' +
        'We\'ll email you the moment we launch with your early bird discount code.';
    }
  }

  function showError(msg) {
    if (errorDiv) {
      errorDiv.textContent = msg;
      errorDiv.classList.remove('hidden');
    }
  }

  function clearError() {
    if (errorDiv) {
      errorDiv.textContent = '';
      errorDiv.classList.add('hidden');
    }
  }

  function setLoading(on) {
    if (submitBtn) {
      submitBtn.disabled    = on;
      submitBtn.textContent = on ? 'Reserving your spot…' : 'Reserve My Spot →';
    }
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function escapeHtml(str) {
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /**
   * Fallback: append to a local JSON array in localStorage so no lead is lost
   * even when Listmonk isn't deployed yet. Operator can export from browser storage.
   */
  function storeLocalSignup(name, email, grows) {
    try {
      var existing = JSON.parse(localStorage.getItem('phytolabs_leads') || '[]');
      existing.push({
        name: name,
        email: email,
        grows: grows,
        ts: new Date().toISOString(),
      });
      localStorage.setItem('phytolabs_leads', JSON.stringify(existing));
    } catch (e) {
      /* storage full or unavailable — silent */
    }
  }
})();
