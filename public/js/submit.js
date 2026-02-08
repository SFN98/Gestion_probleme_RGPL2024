(function () {
  'use strict';

  const form = document.getElementById('form-submit');
  if (!form) return;

  const attachmentsInput = document.getElementById('attachments');
  const previewContainer = document.getElementById('attachments-preview');
  const maxSize = 5 * 1024 * 1024; // 5 Mo

  // Prévisualisation des fichiers sélectionnés
  if (attachmentsInput && previewContainer) {
    attachmentsInput.addEventListener('change', function (e) {
      previewContainer.innerHTML = '';
      const files = Array.from(e.target.files);
      
      files.forEach(function (file, index) {
        // Validation taille
        if (file.size > maxSize) {
          const errorDiv = document.createElement('div');
          errorDiv.className = 'alert alert--error';
          errorDiv.style.marginBottom = 'var(--space-2)';
          errorDiv.textContent = file.name + ' dépasse 5 Mo. Veuillez compresser le fichier.';
          previewContainer.appendChild(errorDiv);
          return;
        }

        // Prévisualisation pour images
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = function (event) {
            const div = document.createElement('div');
            div.className = 'img-thumb';
            div.style.position = 'relative';
            const img = document.createElement('img');
            img.src = event.target.result;
            img.alt = file.name;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn--sm btn--danger';
            removeBtn.style.position = 'absolute';
            removeBtn.style.top = '4px';
            removeBtn.style.right = '4px';
            removeBtn.textContent = '×';
            removeBtn.onclick = function () {
              removeFileFromInput(index);
              div.remove();
            };
            
            div.appendChild(img);
            div.appendChild(removeBtn);
            previewContainer.appendChild(div);
          };
          reader.readAsDataURL(file);
        } else {
          // Pour vidéos, afficher juste le nom
          const div = document.createElement('div');
          div.style.padding = 'var(--space-2)';
          div.style.background = 'var(--color-surface)';
          div.style.borderRadius = 'var(--radius-md)';
          div.style.marginBottom = 'var(--space-2)';
          div.style.display = 'flex';
          div.style.justifyContent = 'space-between';
          div.style.alignItems = 'center';
          
          const span = document.createElement('span');
          span.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
          
          const removeBtn = document.createElement('button');
          removeBtn.type = 'button';
          removeBtn.className = 'btn btn--sm btn--danger';
          removeBtn.textContent = '×';
          removeBtn.onclick = function () {
            removeFileFromInput(index);
            div.remove();
          };
          
          div.appendChild(span);
          div.appendChild(removeBtn);
          previewContainer.appendChild(div);
        }
      });
    });
  }

  // Supprimer un fichier de l'input
  function removeFileFromInput(index) {
    const dt = new DataTransfer();
    const files = Array.from(attachmentsInput.files);
    files.splice(index, 1);
    files.forEach(function (file) {
      dt.items.add(file);
    });
    attachmentsInput.files = dt.files;
    attachmentsInput.dispatchEvent(new Event('change'));
  }

  // Formatage taille fichier
  function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
  }

  // Validation avant soumission
  form.addEventListener('submit', function (e) {
    if (attachmentsInput && attachmentsInput.files.length > 0) {
      const files = Array.from(attachmentsInput.files);
      const oversized = files.filter(function (file) {
        return file.size > maxSize;
      });
      if (oversized.length > 0) {
        e.preventDefault();
        if (window.showAlertModal) {
          window.showAlertModal('Certains fichiers dépassent 5 Mo. Veuillez les compresser avant de soumettre.', 'error');
        } else {
          alert('Certains fichiers dépassent 5 Mo. Veuillez les compresser avant de soumettre.');
        }
        return false;
      }
    }
    if (window.appLoaderWrapper) {
      window.appLoaderWrapper.style.display = 'block';
    }
  });

  // Copier le code du ticket
  window.copyTicketId = function (ticketId) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(ticketId).then(function () {
        const btn = document.getElementById('copy-ticket-btn');
        if (btn) {
          const originalHtml = btn.innerHTML;
          btn.innerHTML = '<i class="fas fa-check"></i><span>Copié !</span>';
          btn.classList.add('btn--success');
          setTimeout(function () {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn--success');
          }, 2000);
        }
      }).catch(function () {
        fallbackCopyTicketId(ticketId);
      });
    } else {
      fallbackCopyTicketId(ticketId);
    }
  };

  function fallbackCopyTicketId(ticketId) {
    const textarea = document.createElement('textarea');
    textarea.value = ticketId;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
      document.execCommand('copy');
      const btn = document.getElementById('copy-ticket-btn');
      if (btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i><span>Copié !</span>';
        btn.classList.add('btn--success');
        setTimeout(function () {
          btn.innerHTML = originalHtml;
          btn.classList.remove('btn--success');
        }, 2000);
      }
    } catch (err) {
      if (window.showAlertModal) {
        window.showAlertModal('Impossible de copier le code. Veuillez le copier manuellement.', 'error');
      } else {
        alert('Impossible de copier le code. Veuillez le copier manuellement.');
      }
    }
    document.body.removeChild(textarea);
  }
})();
