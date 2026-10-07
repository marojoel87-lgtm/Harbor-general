// Harbor General - shared front-end behaviour

document.addEventListener('DOMContentLoaded', function () {
  // Public site mobile nav toggle
  var navToggle = document.getElementById('navToggle');
  var navLinks = document.getElementById('navLinks');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', function () {
      navLinks.classList.toggle('open');
    });
  }

  // Dashboard sidebar toggle (mobile)
  var sidebarToggle = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('appSidebar');
  var sidebarOverlay = document.getElementById('sidebarOverlay');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
      if (sidebarOverlay) {
        sidebarOverlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
      }
    });
  }
  if (sidebarOverlay && sidebar) {
    sidebarOverlay.addEventListener('click', function () {
      sidebar.classList.remove('open');
      sidebarOverlay.style.display = 'none';
    });
  }

  // Auto-dismiss alerts after a few seconds
  document.querySelectorAll('.alert[data-autohide]').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity 0.4s ease';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 400);
    }, 4000);
  });

  // Simple client-side confirm for delete actions
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(el.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });
});
