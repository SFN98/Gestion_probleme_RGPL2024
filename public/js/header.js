/**
 * Header navigation toggle (mobile)
 */
(function () {
  'use strict';

  const toggle = document.getElementById('header-toggle');
  const nav = document.getElementById('header-nav');

  if (!toggle || !nav) return;

  toggle.addEventListener('click', function () {
    const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', !isExpanded);
    nav.classList.toggle('active');
    nav.setAttribute('aria-expanded', !isExpanded);
  });

  // Close menu when clicking outside
  document.addEventListener('click', function (e) {
    if (!nav.contains(e.target) && !toggle.contains(e.target)) {
      toggle.setAttribute('aria-expanded', 'false');
      nav.classList.remove('active');
      nav.setAttribute('aria-expanded', 'false');
    }
  });

  // Close menu on window resize (if resizing to desktop)
  window.addEventListener('resize', function () {
    if (window.innerWidth >= 640) {
      toggle.setAttribute('aria-expanded', 'false');
      nav.classList.remove('active');
      nav.setAttribute('aria-expanded', 'false');
    }
  });
})();
