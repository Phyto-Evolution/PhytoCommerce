/* PhytoCommerce — Visitor Log
 * Collects IP, location, timestamp and stores in localStorage.
 * Optionally POSTs to a webhook (set window.PHYTO_LOG_WEBHOOK before this runs).
 * View logs: open browser console → PhytoCommerce.visitLog()
 */
(function () {
  var STORAGE_KEY = 'phyto_visit_log';
  var MAX_ENTRIES = 200;

  function stamp() {
    return new Date().toISOString();
  }

  function saveLocal(entry) {
    try {
      var log = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
      log.unshift(entry);
      if (log.length > MAX_ENTRIES) log = log.slice(0, MAX_ENTRIES);
      localStorage.setItem(STORAGE_KEY, JSON.stringify(log));
    } catch (e) {}
  }

  function postWebhook(entry) {
    var url = window.PHYTO_LOG_WEBHOOK;
    if (!url) return;
    try {
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(entry),
        keepalive: true
      });
    } catch (e) {}
  }

  function record(geo) {
    var entry = {
      ts:       stamp(),
      page:     window.location.pathname,
      referrer: document.referrer || '',
      ua:       navigator.userAgent,
      ip:       geo.ip        || '',
      city:     geo.city      || '',
      region:   geo.region    || '',
      country:  geo.country_name || geo.country || '',
      lat:      geo.latitude  || '',
      lon:      geo.longitude || ''
    };
    saveLocal(entry);
    postWebhook(entry);
  }

  /* Expose log viewer in console */
  window.PhytoCommerce = window.PhytoCommerce || {};
  window.PhytoCommerce.visitLog = function () {
    var log = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
    console.table(log);
    return log;
  };

  /* Fetch geo — ipapi.co free tier: 1 000 req/day */
  fetch('https://ipapi.co/json/')
    .then(function (r) { return r.json(); })
    .then(function (geo) { record(geo); })
    .catch(function () {
      record({ ip: 'unknown' });
    });
})();
