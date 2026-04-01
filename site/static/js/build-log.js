/* PhytoCommerce — Live Build Log Terminal Widget
 * Fetches full commit history + workflow runs from GitHub API (no auth needed,
 * public repo — 60 req/hr unauthenticated limit).
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
    if (diff < 90)    return 'just now';
    if (diff < 3600)  return Math.round(diff / 60) + 'm ago';
    if (diff < 86400) return Math.round(diff / 3600) + 'h ago';
    const days = Math.round(diff / 86400);
    if (days < 30)    return days + 'd ago';
    if (days < 365)   return Math.round(days / 30) + 'mo ago';
    return Math.round(days / 365) + 'yr ago';
  }

  function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
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
    return '<span class="term-badge dim">· ' + esc(conclusion || status) + '</span>';
  }

  /* ── paginated commit fetch ──────────────────────── */
  function fetchAllCommits(url, acc) {
    acc = acc || [];
    return fetch(url).then(function (r) {
      var link = r.headers.get('Link') || '';
      return r.json().then(function (data) {
        var page = Array.isArray(data) ? data : [];
        acc = acc.concat(page);
        var nextMatch = link.match(/<([^>]+)>;\s*rel="next"/);
        /* safety cap — 500 commits max to stay within rate limits */
        if (nextMatch && acc.length < 500) {
          return fetchAllCommits(nextMatch[1], acc);
        }
        return acc;
      });
    });
  }

  /* ── render ──────────────────────────────────────── */
  function render(commits, runs) {
    BODY.innerHTML = '';

    /* --- commits block --- */
    prompt('git log --oneline');
    if (commits.length) {
      commits.forEach(function (c) {
        const sha  = c.sha.slice(0, 7);
        const msg  = esc(c.commit.message.split('\n')[0].slice(0, 90));
        const when = timeAgo(c.commit.committer.date);
        line(
          '<span class="term-sha">' + sha + '</span> ' +
          '<span class="term-msg">' + msg + '</span> ' +
          '<span class="term-when dim">' + when + '</span>'
        );
      });
      line(
        '<span class="dim">' + commits.length + ' commit' + (commits.length !== 1 ? 's' : '') + ' total</span>',
        'spacer'
      );
    } else {
      line('<span class="dim">No commits found.</span>');
    }

    /* --- workflow runs block --- */
    if (runs && runs.length) {
      line('', 'spacer');
      prompt('gh run list --limit 20');
      runs.forEach(function (r) {
        const badge = statusBadge(r.conclusion, r.status);
        const name  = esc((r.display_title || r.name || '').slice(0, 72));
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
    fetchAllCommits(API + '/commits?per_page=100'),
    fetch(API + '/actions/runs?per_page=20').then(function (r) { return r.json(); }).catch(function () { return {}; })
  ])
  .then(function (results) {
    const commits = results[0] || [];
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
