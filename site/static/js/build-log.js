/* PhytoCommerce — Live Build Log Terminal Widget
 * Fetches recent commits + workflow runs from GitHub API (no auth needed,
 * public repo — 60 req/hr limit is fine for a portfolio site).
 */
(function () {
  const REPO   = 'Phyto-Evolution/PhytoCommerce';
  const BODY   = document.getElementById('terminal-body');
  const STATUS = document.getElementById('term-live');
  if (!BODY) return;

  const API = 'https://api.github.com/repos/' + REPO;

  /* ── helpers ─────────────────────────────────────── */
  function timeAgo(iso) {
    const diff = (Date.now() - new Date(iso)) / 1000;
    if (diff < 90)   return 'just now';
    if (diff < 3600) return Math.round(diff / 60) + 'm ago';
    if (diff < 86400) return Math.round(diff / 3600) + 'h ago';
    return Math.round(diff / 86400) + 'd ago';
  }

  function esc(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  function line(html, cls) {
    const d = document.createElement('div');
    d.className = 'term-line' + (cls ? ' ' + cls : '');
    d.innerHTML = html;
    BODY.appendChild(d);
  }

  function prompt(cmd) {
    line('<span class="term-prompt">$</span> <span class="term-cmd">' + esc(cmd) + '</span>');
  }

  function statusBadge(conclusion, status) {
    if (status === 'in_progress') return '<span class="term-badge run">▶ running</span>';
    if (status === 'queued')      return '<span class="term-badge run">⧗ queued</span>';
    if (conclusion === 'success') return '<span class="term-badge ok">✓ passed</span>';
    if (conclusion === 'failure') return '<span class="term-badge fail">✗ failed</span>';
    return '<span class="term-badge dim">· ' + (conclusion || status) + '</span>';
  }

  /* ── render ──────────────────────────────────────── */
  function render(commits, runs) {
    BODY.innerHTML = '';

    /* --- commits block --- */
    prompt('git log --oneline -10');
    commits.forEach(function (c) {
      const sha  = c.sha.slice(0, 7);
      const msg  = esc(c.commit.message.split('\n')[0].slice(0, 72));
      const when = timeAgo(c.commit.committer.date);
      line(
        '<span class="term-sha">' + sha + '</span> ' +
        '<span class="term-msg">' + msg + '</span> ' +
        '<span class="term-when dim">' + when + '</span>'
      );
    });

    /* --- workflow runs block --- */
    if (runs && runs.length) {
      line('', 'spacer');
      prompt('gh run list --limit 5');
      runs.forEach(function (r) {
        const badge = statusBadge(r.conclusion, r.status);
        const name  = esc(r.name.slice(0, 48));
        const when  = timeAgo(r.updated_at);
        line(badge + ' <span class="term-msg">' + name + '</span> <span class="dim">' + when + '</span>');
      });
    }

    /* --- cursor --- */
    line('<span class="term-cursor">▌</span>');

    /* update live indicator */
    const hasRunning = runs && runs.some(function (r) { return r.status === 'in_progress'; });
    if (hasRunning) {
      STATUS.textContent = '● building';
      STATUS.style.color = '#e8a135';
    } else {
      STATUS.textContent = '● live';
      STATUS.style.color = '#3a9a6a';
    }
  }

  /* ── fetch ───────────────────────────────────────── */
  Promise.all([
    fetch(API + '/commits?per_page=10').then(function (r) { return r.json(); }),
    fetch(API + '/actions/runs?per_page=5').then(function (r) { return r.json(); }).catch(function () { return {}; })
  ])
  .then(function (results) {
    const commits = Array.isArray(results[0]) ? results[0] : [];
    const runs    = (results[1] && Array.isArray(results[1].workflow_runs)) ? results[1].workflow_runs : [];
    if (!commits.length) throw new Error('no data');
    render(commits, runs);
  })
  .catch(function () {
    BODY.innerHTML = '';
    line('<span class="dim">Could not reach GitHub API — rate limit or network.</span>');
    line('<span class="term-cursor">▌</span>');
    if (STATUS) { STATUS.textContent = '○ offline'; STATUS.style.color = '#888'; }
  });
})();
