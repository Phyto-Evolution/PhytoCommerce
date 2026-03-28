/* Forest Studio Labs — Theme JS */
(function () {
  'use strict';

  // ── Sticky header shadow on scroll ───────────────────────
  var header = document.getElementById('header');
  if (header) {
    window.addEventListener('scroll', function () {
      header.style.boxShadow = window.scrollY > 10
        ? '0 4px 20px rgba(0,0,0,.08)'
        : '0 1px 3px rgba(0,0,0,.06)';
    }, { passive: true });
  }

  // ── Smooth image reveal on scroll ────────────────────────
  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('fsl-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.product-miniature, .fsl-category-card, .fsl-testimonial-card').forEach(function (el) {
      el.style.opacity = '0';
      el.style.transform = 'translateY(16px)';
      el.style.transition = 'opacity 400ms ease, transform 400ms ease';
      observer.observe(el);
    });

    document.addEventListener('animationstart', function () {}, false); // trigger reflow
  }

  document.addEventListener('DOMContentLoaded', function () {
    // reveal elements already in view
    document.querySelectorAll('.product-miniature, .fsl-category-card, .fsl-testimonial-card').forEach(function (el) {
      el.classList.add('fsl-visible');
    });
  });

  // ── fsl-visible class transitions ────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    var style = document.createElement('style');
    style.textContent = '.fsl-visible { opacity: 1 !important; transform: translateY(0) !important; }';
    document.head.appendChild(style);
  });

  // ── Qty input: prevent non-numeric ───────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="number"].js-cart-product-quantity').forEach(function (input) {
      input.addEventListener('change', function () {
        var val = parseInt(this.value);
        var min = parseInt(this.min) || 1;
        var max = parseInt(this.max) || 9999;
        if (isNaN(val) || val < min) this.value = min;
        if (val > max) this.value = max;
      });
    });
  });

  // ── Back to top button ────────────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    var btn = document.createElement('button');
    btn.id = 'fsl-back-top';
    btn.innerHTML = '<span class="material-icons">keyboard_arrow_up</span>';
    btn.setAttribute('aria-label', 'Back to top');
    btn.style.cssText = [
      'position:fixed', 'bottom:24px', 'right:24px',
      'width:44px', 'height:44px', 'border-radius:50%',
      'background:var(--fsl-forest)', 'color:#fff',
      'border:none', 'cursor:pointer', 'z-index:999',
      'display:flex', 'align-items:center', 'justify-content:center',
      'box-shadow:0 4px 14px rgba(0,0,0,.2)',
      'opacity:0', 'transition:opacity 220ms ease',
      'pointer-events:none'
    ].join(';');
    document.body.appendChild(btn);

    window.addEventListener('scroll', function () {
      var show = window.scrollY > 400;
      btn.style.opacity = show ? '1' : '0';
      btn.style.pointerEvents = show ? 'auto' : 'none';
    }, { passive: true });

    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

})();
