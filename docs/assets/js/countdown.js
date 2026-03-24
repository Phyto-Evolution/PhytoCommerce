/**
 * PhytoLabs Launch Countdown
 * Target: April 5, 2026 00:00:00 IST (UTC+5:30)
 */
(function () {
  'use strict';

  var LAUNCH = new Date('2026-04-05T00:00:00+05:30').getTime();

  var elDays  = document.getElementById('cd-days');
  var elHours = document.getElementById('cd-hours');
  var elMins  = document.getElementById('cd-mins');
  var elSecs  = document.getElementById('cd-secs');
  var elWrap  = document.getElementById('countdown');
  var elLive  = document.getElementById('countdown-live');
  var elDaysRemaining = document.getElementById('days-remaining');

  function pad(n) {
    return n < 10 ? '0' + n : String(n);
  }

  function flash(el) {
    el.classList.add('tick');
    setTimeout(function () { el.classList.remove('tick'); }, 150);
  }

  function tick() {
    var now  = Date.now();
    var diff = LAUNCH - now;

    if (diff <= 0) {
      // Launch!
      if (elWrap)  elWrap.classList.add('hidden');
      if (elLive)  elLive.classList.remove('hidden');
      if (elDaysRemaining) elDaysRemaining.textContent = '0';
      return;
    }

    var totalSecs = Math.floor(diff / 1000);
    var days  = Math.floor(totalSecs / 86400);
    var hours = Math.floor((totalSecs % 86400) / 3600);
    var mins  = Math.floor((totalSecs % 3600) / 60);
    var secs  = totalSecs % 60;

    if (elDays)  elDays.textContent  = pad(days);
    if (elHours) elHours.textContent = pad(hours);
    if (elMins)  elMins.textContent  = pad(mins);

    if (elSecs && elSecs.textContent !== pad(secs)) {
      elSecs.textContent = pad(secs);
      flash(elSecs);
    }

    // Update the inline "X days" in the signup copy
    if (elDaysRemaining) {
      elDaysRemaining.textContent = days;
    }
  }

  // Run immediately, then every second
  tick();
  setInterval(tick, 1000);
})();
