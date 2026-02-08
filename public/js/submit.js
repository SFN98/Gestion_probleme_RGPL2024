(function () {
  var form = document.querySelector('form');
  if (!form) return;
  form.addEventListener('submit', function () {
    if (window.appLoaderWrapper) window.appLoaderWrapper.style.display = 'block';
  });
})();
